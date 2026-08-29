<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Sostituisce i segnaposto {chiave} nel testo con i valori forniti. Usata
 * sia per l'oggetto sia per il corpo di ogni template email configurato in
 * Open Events → Community.
 */
function open_events_community_email_fill_placeholders( $text, array $placeholders ) {
	foreach ( $placeholders as $key => $value ) {
		$text = str_replace( '{' . $key . '}', $value, $text );
	}
	return $text;
}

// Log diagnostico: PHPMailer scrive qui il motivo esatto quando l'invio
// fallisce (es. nessun server SMTP raggiungibile, comune in locale).
add_action( 'wp_mail_failed', function( $wp_error ) {
	error_log( '[Open Events Community] wp_mail_failed: ' . $wp_error->get_error_message() );
} );

/**
 * Invia una delle email configurabili (registration/event_submitted/
 * event_published), col mittente personalizzato se impostato. Non fa nulla
 * silenziosamente se manca il destinatario o il template — chiamata da
 * hook, non da codice che debba sapere se l'invio è andato a buon fine.
 */
function open_events_community_send_templated_email( $template_key, $to, array $placeholders ) {
	if ( ! $to ) {
		return;
	}

	$settings = open_events_get_community_settings();
	$template = $settings['emails'][ $template_key ] ?? null;
	if ( ! $template || empty( $template['subject'] ) ) {
		return;
	}

	$subject = open_events_community_email_fill_placeholders( $template['subject'], $placeholders );
	$body    = open_events_community_email_fill_placeholders( $template['body'], $placeholders );

	$sender_name  = $settings['emails']['sender_name'] ?? '';
	$sender_email = $settings['emails']['sender_email'] ?? '';

	$from_email_filter = function() use ( $sender_email ) {
		return $sender_email;
	};
	$from_name_filter = function() use ( $sender_name ) {
		return $sender_name;
	};

	if ( $sender_email ) {
		add_filter( 'wp_mail_from', $from_email_filter );
	}
	if ( $sender_name ) {
		add_filter( 'wp_mail_from_name', $from_name_filter );
	}

	$sent = wp_mail( $to, $subject, $body );
	// wp_mail() ritorna solo "consegnata al server di invio", non "arrivata
	// in casella": utile per capire se il nostro codice ha effettivamente
	// tentato l'invio quando l'email poi non risulta ricevuta (tipico in
	// locale, dove spesso non c'è un vero SMTP configurato).
	error_log( sprintf( '[Open Events Community] Invio email "%s" a %s: %s', $template_key, $to, $sent ? 'OK (passata a wp_mail)' : 'FALLITA' ) );

	if ( $sender_email ) {
		remove_filter( 'wp_mail_from', $from_email_filter );
	}
	if ( $sender_name ) {
		remove_filter( 'wp_mail_from_name', $from_name_filter );
	}
}

/**
 * Email di benvenuto: stesso hook sia per la registrazione classica
 * (community-auth.php) sia per quella via social (community-oauth.php),
 * cosi' non si duplica la logica di invio per ciascun percorso.
 */
add_action( 'oe_community_user_registered', function( $user_id ) {
	$user = get_user_by( 'id', $user_id );
	if ( ! $user ) {
		return;
	}

	$settings = open_events_get_community_settings();
	$login_url = $settings['login_page_id'] ? get_permalink( $settings['login_page_id'] ) : wp_login_url();

	open_events_community_send_templated_email( 'registration', $user->user_email, [
		'nome'      => $user->first_name ? $user->first_name : $user->display_name,
		'sito_nome' => get_bloginfo( 'name' ),
		'login_url' => $login_url,
	] );
} );

/**
 * Notifica all'admin quando un evento viene inserito dal portale ed è in
 * attesa di revisione. Sostituisce il vecchio wp_mail() con testo fisso in
 * includes/events-manager/class-widget-events-manager.php.
 */
add_action( 'oe_community_event_submitted', function( $post_id, $event_title, $author_display_name ) {
	open_events_community_send_templated_email( 'event_submitted', get_option( 'admin_email' ), [
		'evento_titolo' => $event_title,
		'evento_link'   => open_events_get_event_edit_url( $post_id ),
		'autore_nome'   => $author_display_name,
	] );
}, 10, 3 );

/**
 * Notifica all'autore quando un admin pubblica il suo evento dal portale
 * (pulsante "Pubblica" nell'elenco eventi).
 */
add_action( 'oe_community_event_published', function( $post_id, $event_title, $author_id ) {
	$author = get_user_by( 'id', $author_id );
	if ( ! $author ) {
		return;
	}

	open_events_community_send_templated_email( 'event_published', $author->user_email, [
		'evento_titolo' => $event_title,
		'evento_link'   => get_permalink( $post_id ),
		'autore_nome'   => $author->first_name ? $author->first_name : $author->display_name,
	] );
}, 10, 3 );
