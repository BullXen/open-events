<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Endpoint webhook Stripe — prima REST route del plugin (finora tutto il
 * resto usa admin-post.php/admin-ajax.php). Qui serve davvero: Stripe firma
 * il body RAW della richiesta, cosa scomoda da leggere sotto admin-ajax.php,
 * e non c'è nessun nonce/cookie coinvolto (chiamata server-to-server), quindi
 * permission_callback resta aperta e la sicurezza è tutta nella verifica
 * della firma dentro il callback.
 */
add_action( 'rest_api_init', function() {
	register_rest_route( 'open-events/v1', '/stripe-webhook', [
		'methods'             => 'POST',
		'callback'            => __NAMESPACE__ . '\\open_events_featured_handle_stripe_webhook',
		'permission_callback' => '__return_true',
	] );
} );

/**
 * Verifica manuale della firma Stripe (HMAC-SHA256), algoritmo documentato
 * da Stripe — nessuna libreria necessaria. L'header ha il formato
 * "t=<timestamp>,v1=<firma>[,v0=...]"; la firma attesa è
 * hmac_sha256("{t}.{payload_raw}", webhook_secret). Tolleranza di 5 minuti
 * sul timestamp per limitare il replay di richieste intercettate.
 */
function open_events_featured_verify_stripe_signature( $payload, $signature_header, $webhook_secret ) {
	if ( ! $signature_header || ! $webhook_secret ) {
		return false;
	}

	$parts = [];
	foreach ( explode( ',', $signature_header ) as $pair ) {
		$kv = explode( '=', $pair, 2 );
		if ( 2 === count( $kv ) ) {
			$parts[ trim( $kv[0] ) ] = trim( $kv[1] );
		}
	}

	if ( empty( $parts['t'] ) || empty( $parts['v1'] ) ) {
		return false;
	}

	if ( abs( time() - (int) $parts['t'] ) > 5 * MINUTE_IN_SECONDS ) {
		return false;
	}

	$expected = hash_hmac( 'sha256', $parts['t'] . '.' . $payload, $webhook_secret );

	return hash_equals( $expected, $parts['v1'] );
}

function open_events_featured_handle_stripe_webhook( \WP_REST_Request $request ) {
	$settings = open_events_get_featured_events_settings();
	$payload = $request->get_body();
	$signature_header = $request->get_header( 'stripe-signature' );

	error_log( '[Open Events Featured] Webhook ricevuto. Header firma presente: ' . ( $signature_header ? 'sì' : 'NO' ) . '; webhook secret configurato: ' . ( $settings['stripe_webhook_secret'] ? 'sì' : 'NO' ) . '; lunghezza payload: ' . strlen( (string) $payload ) );

	if ( ! open_events_featured_verify_stripe_signature( $payload, $signature_header, $settings['stripe_webhook_secret'] ) ) {
		error_log( '[Open Events Featured] Webhook RIFIUTATO: firma non valida. Header ricevuto: ' . substr( (string) $signature_header, 0, 60 ) );
		return new \WP_REST_Response( [ 'error' => 'invalid_signature' ], 400 );
	}

	$event = json_decode( $payload, true );
	if ( empty( $event['type'] ) ) {
		error_log( '[Open Events Featured] Webhook: firma valida ma nessun "type" nel payload JSON.' );
		return new \WP_REST_Response( [ 'received' => true ], 200 );
	}

	$session = $event['data']['object'] ?? [];
	$post_id = absint( $session['metadata']['post_id'] ?? 0 );

	error_log( '[Open Events Featured] Webhook tipo="' . $event['type'] . '" post_id=' . $post_id . ' post_type=' . get_post_type( $post_id ) );

	if ( $post_id && 'tribe_events' === get_post_type( $post_id ) ) {
		if ( 'checkout.session.completed' === $event['type'] ) {
			update_post_meta( $post_id, '_illi_featured_status', 'paid' );
			update_post_meta( $post_id, '_illi_featured_payment_id', sanitize_text_field( $session['id'] ?? '' ) );
			update_post_meta( $post_id, '_illi_featured_amount', absint( $session['amount_total'] ?? 0 ) );
			update_post_meta( $post_id, '_illi_featured_paid_at', current_time( 'mysql' ) );

			$receipt_url = open_events_featured_fetch_receipt_url( $session['payment_intent'] ?? '' );
			if ( $receipt_url ) {
				update_post_meta( $post_id, '_illi_featured_receipt_url', esc_url_raw( $receipt_url ) );
			}

			error_log( '[Open Events Featured] Evento ' . $post_id . ' marcato come "paid".' );
		} elseif ( 'checkout.session.expired' === $event['type'] ) {
			// Libera lo slot pre-riservato solo se il pagamento non è mai
			// arrivato a buon fine nel frattempo (evita di "smontare" per
			// errore un evento già confermato da un webhook precedente).
			if ( 'pending_payment' === get_post_meta( $post_id, '_illi_featured_status', true ) ) {
				update_post_meta( $post_id, '_illi_featured_status', 'none' );
			}
		}
	} else {
		error_log( '[Open Events Featured] Webhook: nessun post_id valido in metadata, nessun aggiornamento.' );
	}

	return new \WP_REST_Response( [ 'received' => true ], 200 );
}
