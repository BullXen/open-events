<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Redirect di errore verso la pagina Community Auth configurata (o al login
 * WordPress di default se non ne è stata scelta una), con il tab giusto
 * riaperto e un codice errore che il widget legge da $_GET per mostrare il
 * messaggio (vedi templates/auth-form.php).
 */
function open_events_community_redirect_with_error( $error_code, $tab = 'accedi' ) {
	$settings = open_events_get_community_settings();
	$back = $settings['login_page_id'] ? get_permalink( $settings['login_page_id'] ) : wp_login_url();
	$back = add_query_arg( [ 'oe_error' => $error_code, 'tab' => $tab ], $back );
	wp_safe_redirect( $back );
	exit;
}

/**
 * Verifica il token reCAPTCHA v3 lato server (wp_remote_post, nessun SDK,
 * stesso approccio homemade già usato per Stripe/OAuth). true solo se Google
 * conferma la richiesta come genuina ("success"), l'azione dichiarata
 * corrisponde ("register", evita il riuso di un token ottenuto altrove) e il
 * punteggio antibot è pari o sopra la soglia configurata.
 */
function open_events_community_verify_recaptcha( $token ) {
	$settings = open_events_get_community_settings();
	if ( empty( $settings['recaptcha']['enabled'] ) || empty( $settings['recaptcha']['site_key'] ) ) {
		return true;
	}
	if ( empty( $settings['recaptcha']['secret_key'] ) || '' === trim( (string) $token ) ) {
		return false;
	}

	$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', [
		'body'    => [
			'secret'   => $settings['recaptcha']['secret_key'],
			'response' => $token,
			'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
		],
		'timeout' => 10,
	] );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	return ! empty( $data['success'] )
		&& 'register' === ( $data['action'] ?? '' )
		&& (float) ( $data['score'] ?? 0 ) >= (float) $settings['recaptcha']['threshold'];
}

/**
 * user_login non è richiesto nel form pubblico (solo email, come da
 * documento di feature): genera uno username interno univoco a partire
 * dalla parte locale dell'email.
 */
function open_events_community_generate_username_from_email( $email ) {
	$base = sanitize_user( current( explode( '@', $email ) ), true );
	if ( '' === $base ) {
		$base = 'utente';
	}

	$username = $base;
	$i = 1;
	while ( username_exists( $username ) ) {
		$i++;
		$username = $base . $i;
	}

	return $username;
}

function open_events_community_handle_register() {
	if ( ! isset( $_POST['oe_community_nonce'] ) || ! wp_verify_nonce( $_POST['oe_community_nonce'], 'oe_community_register' ) ) {
		open_events_community_redirect_with_error( 'session_expired', 'registrati' );
	}

	$settings = open_events_get_community_settings();
	if ( empty( $settings['registration_enabled'] ) ) {
		open_events_community_redirect_with_error( 'registration_disabled', 'registrati' );
	}

	// Anti-bot: honeypot (campo nascosto che solo un bot compila) + time-trap
	// (un form inviato troppo velocemente non è stato compilato da un
	// umano). Errore generico di proposito, per non rivelare a chi ci
	// prova quale dei due controlli ha fatto scattare il blocco.
	if ( '' !== trim( (string) ( $_POST['website'] ?? '' ) ) ) {
		open_events_community_redirect_with_error( 'registration_failed', 'registrati' );
	}
	$submitted_at = absint( $_POST['oe_reg_ts'] ?? 0 );
	if ( ! $submitted_at || ( time() - $submitted_at ) < 3 ) {
		open_events_community_redirect_with_error( 'registration_failed', 'registrati' );
	}
	if ( ! open_events_community_verify_recaptcha( $_POST['recaptcha_token'] ?? '' ) ) {
		open_events_community_redirect_with_error( 'recaptcha_failed', 'registrati' );
	}

	$email      = sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) );
	$password   = (string) ( $_POST['user_pass'] ?? '' );
	$first_name = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
	$last_name  = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
	$city       = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );

	$fields = $settings['fields'];
	if ( 'required' === ( $fields['first_name'] ?? 'optional' ) && '' === $first_name ) {
		open_events_community_redirect_with_error( 'missing_first_name', 'registrati' );
	}
	if ( 'required' === ( $fields['last_name'] ?? 'optional' ) && '' === $last_name ) {
		open_events_community_redirect_with_error( 'missing_last_name', 'registrati' );
	}
	if ( 'required' === ( $fields['city'] ?? 'optional' ) && '' === $city ) {
		open_events_community_redirect_with_error( 'missing_city', 'registrati' );
	}

	if ( ! is_email( $email ) ) {
		open_events_community_redirect_with_error( 'invalid_email', 'registrati' );
	}
	if ( email_exists( $email ) ) {
		open_events_community_redirect_with_error( 'email_exists', 'registrati' );
	}
	if ( strlen( $password ) < 8 ) {
		open_events_community_redirect_with_error( 'weak_password', 'registrati' );
	}

	$user_id = wp_insert_user( [
		'user_login' => open_events_community_generate_username_from_email( $email ),
		'user_email' => $email,
		'user_pass'  => $password,
		'first_name' => $first_name,
		'last_name'  => $last_name,
		'role'       => $settings['default_role'],
	] );

	if ( is_wp_error( $user_id ) ) {
		open_events_community_redirect_with_error( 'registration_failed', 'registrati' );
	}
	// La città (non un campo nativo di wp_insert_user) viene salvata dall'hook
	// user_register più sotto, che legge $_POST['city'] con lo stesso guard
	// 'oe_community_action'. L'azione seguente è invece il punto unico e
	// condiviso con il login social (community-oauth.php) per l'invio
	// dell'email di benvenuto, vedi community-emails.php.
	do_action( 'oe_community_user_registered', $user_id );

	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	$redirect = $settings['redirect_page_id'] ? get_permalink( $settings['redirect_page_id'] ) : home_url( '/' );
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_nopriv_oe_community_register', __NAMESPACE__ . '\\open_events_community_handle_register' );
add_action( 'admin_post_oe_community_register', __NAMESPACE__ . '\\open_events_community_handle_register' );

function open_events_community_handle_login() {
	if ( ! isset( $_POST['oe_community_nonce'] ) || ! wp_verify_nonce( $_POST['oe_community_nonce'], 'oe_community_login' ) ) {
		open_events_community_redirect_with_error( 'session_expired', 'accedi' );
	}

	$creds = [
		'user_login'    => sanitize_text_field( wp_unslash( $_POST['user_login'] ?? '' ) ),
		'user_password' => (string) ( $_POST['user_pass'] ?? '' ),
		'remember'      => ! empty( $_POST['remember'] ),
	];

	$user = wp_signon( $creds, is_ssl() );
	if ( is_wp_error( $user ) ) {
		open_events_community_redirect_with_error( 'login_failed', 'accedi' );
	}

	// Se si arriva al login perché un'altra pagina lo richiedeva esplicitamente
	// (es. wp_login_url($pagina_corrente) chiamato da un altro plugin/tema),
	// si torna lì invece che sempre alla pagina di redirect configurata.
	$requested_redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
	if ( $requested_redirect ) {
		wp_safe_redirect( $requested_redirect );
		exit;
	}

	$settings = open_events_get_community_settings();
	$redirect = $settings['redirect_page_id'] ? get_permalink( $settings['redirect_page_id'] ) : home_url( '/' );
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_nopriv_oe_community_login', __NAMESPACE__ . '\\open_events_community_handle_login' );
add_action( 'admin_post_oe_community_login', __NAMESPACE__ . '\\open_events_community_handle_login' );

/**
 * Salva la città sull'utente appena creato dal form Community (guard su
 * 'oe_community_action' cosi' non scatta anche per utenti creati da
 * wp-admin o da altri plugin). Punto d'aggancio riservato anche per il
 * collegamento account social nelle fasi successive.
 */
function open_events_community_on_user_register( $user_id ) {
	if ( ! isset( $_POST['oe_community_action'] ) || 'register' !== $_POST['oe_community_action'] ) {
		return;
	}

	if ( isset( $_POST['city'] ) ) {
		update_user_meta( $user_id, 'oe_city', sanitize_text_field( wp_unslash( $_POST['city'] ) ) );
	}
}
add_action( 'user_register', __NAMESPACE__ . '\\open_events_community_on_user_register' );

/**
 * Punta login/registrazione WordPress "di default" alla pagina Community
 * configurata (se scelta in Impostazioni > Community), cosi' anche i link
 * generati da temi/altri plugin (wp_login_url(), wp_registration_url())
 * finiscono sul widget invece che su wp-login.php.
 */
add_filter( 'login_url', function( $login_url, $redirect ) {
	$settings = open_events_get_community_settings();
	if ( ! $settings['login_page_id'] ) {
		return $login_url;
	}

	$url = get_permalink( $settings['login_page_id'] );
	if ( $redirect ) {
		$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
	}
	return $url;
}, 10, 2 );

add_filter( 'register_url', function( $register_url ) {
	$settings = open_events_get_community_settings();
	if ( ! $settings['login_page_id'] ) {
		return $register_url;
	}
	return add_query_arg( 'tab', 'registrati', get_permalink( $settings['login_page_id'] ) );
} );

add_filter( 'lostpassword_url', function( $lostpassword_url, $redirect ) {
	$settings = open_events_get_community_settings();
	if ( ! $settings['forgot_password_page_id'] ) {
		return $lostpassword_url;
	}
	return get_permalink( $settings['forgot_password_page_id'] );
}, 10, 2 );

/**
 * Dopo il login, manda l'utente alla pagina Community configurata invece
 * che a wp-admin — a meno che non fosse già stato richiesto esplicitamente
 * un altro redirect, o che l'utente sia un amministratore (che continua a
 * finire su wp-admin come comportamento nativo, utile per chi gestisce il
 * sito da wp-admin e non vuole ritrovarsi sbattuto sul front-end).
 */
add_filter( 'login_redirect', function( $redirect_to, $requested_redirect_to, $user ) {
	if ( ! empty( $requested_redirect_to ) ) {
		return $redirect_to;
	}
	if ( $user instanceof \WP_User && user_can( $user, 'manage_options' ) ) {
		return $redirect_to;
	}

	$settings = open_events_get_community_settings();
	if ( $settings['redirect_page_id'] ) {
		return get_permalink( $settings['redirect_page_id'] );
	}
	return $redirect_to;
}, 10, 3 );

/**
 * Permette di accedere anche con l'indirizzo email oltre allo username
 * (nel form Community c'è solo il campo email). Agganciato a priorità 5,
 * PRIMA del controllo username/password nativo di WordPress (priorità 20):
 * se l'input è un'email nota, rilancia il controllo nativo con lo username
 * reale corrispondente invece di reimplementare a mano la verifica password.
 */
add_filter( 'authenticate', function( $user, $username, $password ) {
	if ( $user instanceof \WP_User || empty( $username ) || empty( $password ) || ! is_email( $username ) ) {
		return $user;
	}

	$by_email = get_user_by( 'email', $username );
	if ( ! $by_email ) {
		return $user;
	}

	remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
	$result = wp_authenticate_username_password( null, $by_email->user_login, $password );
	add_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );

	return $result;
}, 5, 3 );
