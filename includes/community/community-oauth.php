<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * OAuth "fatto in casa" con wp_remote_get/post, niente SDK/Composer: solo
 * due provider (Google, Facebook), entrambi Authorization Code flow molto
 * simile, non vale la pena introdurre una dipendenza esterna per questo.
 */

function open_events_community_oauth_redirect_uri( $provider ) {
	return admin_url( 'admin-post.php?action=oe_community_' . $provider . '_callback' );
}

/**
 * Token "state" anti-CSRF: un valore casuale legato al provider via
 * transient (non un nonce, perché qui l'utente è anonimo per definizione
 * e passa da un dominio esterno che rimanda il token cosi' com'è).
 */
function open_events_community_oauth_create_state( $provider ) {
	$token = wp_generate_password( 32, false );
	set_transient( 'oe_oauth_state_' . $token, $provider, 10 * MINUTE_IN_SECONDS );
	return $token;
}

function open_events_community_oauth_validate_state( $token, $provider ) {
	$key = 'oe_oauth_state_' . $token;
	$stored_provider = get_transient( $key );
	delete_transient( $key );
	return $stored_provider === $provider;
}

/**
 * Punto unico per login/registrazione via social, condiviso da Google e
 * Facebook: se il provider_id è già collegato a un account, accede; se
 * l'email corrisponde a un account esistente, collega quello invece di
 * creare un duplicato (come richiesto dal documento di feature); altrimenti
 * crea un nuovo utente col ruolo configurato.
 */
function open_events_community_social_login_or_register( $provider, $provider_user_id, $email, $first_name, $last_name, $avatar_url ) {
	$meta_key = 'oe_' . $provider . '_id';

	$linked_users = get_users( [
		'meta_key'   => $meta_key,
		'meta_value' => $provider_user_id,
		'number'     => 1,
	] );
	$user = $linked_users[0] ?? null;

	$is_new_user = false;

	if ( ! $user && $email ) {
		$user = get_user_by( 'email', $email );
	}

	if ( ! $user ) {
		if ( ! $email ) {
			open_events_community_redirect_with_error( 'social_email_required', 'accedi' );
		}
		if ( email_exists( $email ) ) {
			// Non dovrebbe succedere (già cercato sopra), ma per sicurezza:
			// evita un fatal error di wp_insert_user su email duplicata.
			$user = get_user_by( 'email', $email );
		} else {
			$settings = open_events_get_community_settings();
			$user_id = wp_insert_user( [
				'user_login' => open_events_community_generate_username_from_email( $email ),
				'user_email' => $email,
				'user_pass'  => wp_generate_password( 32 ),
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'role'       => $settings['default_role'],
			] );
			if ( is_wp_error( $user_id ) ) {
				open_events_community_redirect_with_error( 'registration_failed', 'registrati' );
			}
			$user = get_user_by( 'id', $user_id );
			$is_new_user = true;
		}
	}

	update_user_meta( $user->ID, $meta_key, $provider_user_id );

	$settings = open_events_get_community_settings();
	if ( $avatar_url && ! empty( $settings['import_social_avatar'] ) ) {
		update_user_meta( $user->ID, 'oe_avatar_url', esc_url_raw( $avatar_url ) );
	}

	if ( $is_new_user ) {
		do_action( 'oe_community_user_registered', $user->ID );
	}

	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );

	$redirect = $settings['redirect_page_id'] ? get_permalink( $settings['redirect_page_id'] ) : home_url( '/' );
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Filtra l'avatar con quello importato dal provider social, se presente
 * (WordPress di suo mostra solo Gravatar). Attivo sempre, non solo quando
 * Google/Facebook sono abilitati: un utente già collegato in passato deve
 * continuare a vedere la propria foto anche se il login social viene
 * disattivato in seguito.
 */
add_filter( 'get_avatar_url', function( $url, $id_or_email, $args ) {
	$user = false;
	if ( is_numeric( $id_or_email ) ) {
		$user = get_user_by( 'id', $id_or_email );
	} elseif ( $id_or_email instanceof \WP_User ) {
		$user = $id_or_email;
	} elseif ( $id_or_email instanceof \WP_Comment && ! empty( $id_or_email->user_id ) ) {
		$user = get_user_by( 'id', $id_or_email->user_id );
	} elseif ( is_string( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
	}

	if ( ! $user ) {
		return $url;
	}

	$avatar_url = get_user_meta( $user->ID, 'oe_avatar_url', true );
	return $avatar_url ? $avatar_url : $url;
}, 10, 3 );

// =============================================================================
// Google
// =============================================================================

function open_events_community_google_start() {
	$settings = open_events_get_community_settings();
	if ( empty( $settings['google']['enabled'] ) || empty( $settings['google']['client_id'] ) ) {
		open_events_community_redirect_with_error( 'oauth_not_configured', 'accedi' );
	}

	$state = open_events_community_oauth_create_state( 'google' );
	$url = add_query_arg( [
		'client_id'     => rawurlencode( $settings['google']['client_id'] ),
		'redirect_uri'  => rawurlencode( open_events_community_oauth_redirect_uri( 'google' ) ),
		'response_type' => 'code',
		'scope'         => rawurlencode( 'openid email profile' ),
		'state'         => $state,
		'access_type'   => 'online',
		'prompt'        => 'select_account',
	], 'https://accounts.google.com/o/oauth2/v2/auth' );

	wp_redirect( $url );
	exit;
}
add_action( 'admin_post_nopriv_oe_community_google_start', __NAMESPACE__ . '\\open_events_community_google_start' );
add_action( 'admin_post_oe_community_google_start', __NAMESPACE__ . '\\open_events_community_google_start' );

function open_events_community_google_callback() {
	$settings = open_events_get_community_settings();
	$state = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) );
	$code  = sanitize_text_field( wp_unslash( $_GET['code'] ?? '' ) );

	if ( ! $state || ! open_events_community_oauth_validate_state( $state, 'google' ) ) {
		open_events_community_redirect_with_error( 'oauth_state_invalid', 'accedi' );
	}
	if ( ! $code ) {
		open_events_community_redirect_with_error( 'oauth_token_exchange_failed', 'accedi' );
	}

	$token_response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
		'body' => [
			'client_id'     => $settings['google']['client_id'],
			'client_secret' => $settings['google']['client_secret'],
			'code'          => $code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => open_events_community_oauth_redirect_uri( 'google' ),
		],
		'timeout' => 15,
	] );

	if ( is_wp_error( $token_response ) ) {
		open_events_community_redirect_with_error( 'oauth_token_exchange_failed', 'accedi' );
	}
	$token_body = json_decode( wp_remote_retrieve_body( $token_response ), true );
	$access_token = $token_body['access_token'] ?? '';
	if ( ! $access_token ) {
		open_events_community_redirect_with_error( 'oauth_token_exchange_failed', 'accedi' );
	}

	$userinfo_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v3/userinfo', [
		'headers' => [ 'Authorization' => 'Bearer ' . $access_token ],
		'timeout' => 15,
	] );
	if ( is_wp_error( $userinfo_response ) ) {
		open_events_community_redirect_with_error( 'oauth_userinfo_failed', 'accedi' );
	}
	$userinfo = json_decode( wp_remote_retrieve_body( $userinfo_response ), true );
	if ( empty( $userinfo['sub'] ) ) {
		open_events_community_redirect_with_error( 'oauth_userinfo_failed', 'accedi' );
	}

	open_events_community_social_login_or_register(
		'google',
		$userinfo['sub'],
		sanitize_email( $userinfo['email'] ?? '' ),
		sanitize_text_field( $userinfo['given_name'] ?? '' ),
		sanitize_text_field( $userinfo['family_name'] ?? '' ),
		esc_url_raw( $userinfo['picture'] ?? '' )
	);
}
add_action( 'admin_post_nopriv_oe_community_google_callback', __NAMESPACE__ . '\\open_events_community_google_callback' );
add_action( 'admin_post_oe_community_google_callback', __NAMESPACE__ . '\\open_events_community_google_callback' );

// =============================================================================
// Facebook
// =============================================================================

function open_events_community_facebook_start() {
	$settings = open_events_get_community_settings();
	if ( empty( $settings['facebook']['enabled'] ) || empty( $settings['facebook']['app_id'] ) ) {
		open_events_community_redirect_with_error( 'oauth_not_configured', 'accedi' );
	}

	$state = open_events_community_oauth_create_state( 'facebook' );
	$url = add_query_arg( [
		'client_id'    => rawurlencode( $settings['facebook']['app_id'] ),
		'redirect_uri' => rawurlencode( open_events_community_oauth_redirect_uri( 'facebook' ) ),
		'state'        => $state,
		'scope'        => 'email,public_profile',
	], 'https://www.facebook.com/v19.0/dialog/oauth' );

	wp_redirect( $url );
	exit;
}
add_action( 'admin_post_nopriv_oe_community_facebook_start', __NAMESPACE__ . '\\open_events_community_facebook_start' );
add_action( 'admin_post_oe_community_facebook_start', __NAMESPACE__ . '\\open_events_community_facebook_start' );

function open_events_community_facebook_callback() {
	$settings = open_events_get_community_settings();
	$state = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) );
	$code  = sanitize_text_field( wp_unslash( $_GET['code'] ?? '' ) );

	if ( ! $state || ! open_events_community_oauth_validate_state( $state, 'facebook' ) ) {
		open_events_community_redirect_with_error( 'oauth_state_invalid', 'accedi' );
	}
	if ( ! $code ) {
		open_events_community_redirect_with_error( 'oauth_token_exchange_failed', 'accedi' );
	}

	// A differenza di Google, lo scambio codice->token di Facebook avviene
	// con una GET (query string), non una POST col body.
	$token_url = add_query_arg( [
		'client_id'     => $settings['facebook']['app_id'],
		'client_secret' => $settings['facebook']['app_secret'],
		'redirect_uri'  => open_events_community_oauth_redirect_uri( 'facebook' ),
		'code'          => $code,
	], 'https://graph.facebook.com/v19.0/oauth/access_token' );

	$token_response = wp_remote_get( $token_url, [ 'timeout' => 15 ] );
	if ( is_wp_error( $token_response ) ) {
		open_events_community_redirect_with_error( 'oauth_token_exchange_failed', 'accedi' );
	}
	$token_body = json_decode( wp_remote_retrieve_body( $token_response ), true );
	$access_token = $token_body['access_token'] ?? '';
	if ( ! $access_token ) {
		open_events_community_redirect_with_error( 'oauth_token_exchange_failed', 'accedi' );
	}

	$userinfo_url = add_query_arg( [
		'fields'       => 'id,email,first_name,last_name,picture.type(large)',
		'access_token' => $access_token,
	], 'https://graph.facebook.com/me' );

	$userinfo_response = wp_remote_get( $userinfo_url, [ 'timeout' => 15 ] );
	if ( is_wp_error( $userinfo_response ) ) {
		open_events_community_redirect_with_error( 'oauth_userinfo_failed', 'accedi' );
	}
	$userinfo = json_decode( wp_remote_retrieve_body( $userinfo_response ), true );
	if ( empty( $userinfo['id'] ) ) {
		open_events_community_redirect_with_error( 'oauth_userinfo_failed', 'accedi' );
	}

	open_events_community_social_login_or_register(
		'facebook',
		$userinfo['id'],
		sanitize_email( $userinfo['email'] ?? '' ),
		sanitize_text_field( $userinfo['first_name'] ?? '' ),
		sanitize_text_field( $userinfo['last_name'] ?? '' ),
		esc_url_raw( $userinfo['picture']['data']['url'] ?? '' )
	);
}
add_action( 'admin_post_nopriv_oe_community_facebook_callback', __NAMESPACE__ . '\\open_events_community_facebook_callback' );
add_action( 'admin_post_oe_community_facebook_callback', __NAMESPACE__ . '\\open_events_community_facebook_callback' );
