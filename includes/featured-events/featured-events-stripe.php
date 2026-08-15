<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Crea una Stripe Checkout Session via REST API dirette (wp_remote_post),
 * nessun SDK/Composer — stesso approccio già usato per Google/Facebook.
 * wp_remote_post() codifica automaticamente un array PHP annidato in
 * application/x-www-form-urlencoded con notazione a parentesi quadre
 * (line_items[0][price_data][...]), esattamente il formato che l'API di
 * Stripe si aspetta.
 *
 * Ritorna ['id' => ..., 'url' => ...] o un WP_Error.
 */
function open_events_featured_create_checkout_session( $post_id, $success_url, $cancel_url ) {
	$settings = open_events_get_featured_events_settings();
	if ( empty( $settings['stripe_secret_key'] ) ) {
		return new \WP_Error( 'stripe_not_configured', esc_html__( 'Stripe non è configurato nelle impostazioni del plugin.', 'open-events' ) );
	}

	$post = get_post( $post_id );
	$product_name = $post ? $post->post_title : esc_html__( 'Evento Consigliato', 'open-events' );

	$body = [
		'mode'        => 'payment',
		'success_url' => $success_url,
		'cancel_url'  => $cancel_url,
		'metadata'    => [ 'post_id' => (string) $post_id ],
		'line_items'  => [
			[
				'quantity'   => 1,
				'price_data' => [
					'currency'     => strtolower( $settings['stripe_price_currency'] ),
					'unit_amount'  => absint( $settings['stripe_price_amount'] ),
					'product_data' => [
						'name' => sprintf( esc_html__( 'Evento Consigliato: %s', 'open-events' ), $product_name ),
					],
				],
			],
		],
	];

	$response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', [
		'headers' => [ 'Authorization' => 'Bearer ' . $settings['stripe_secret_key'] ],
		'body'    => $body,
		'timeout' => 15,
	] );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $data['url'] ) || empty( $data['id'] ) ) {
		$message = $data['error']['message'] ?? esc_html__( 'Errore sconosciuto creando la sessione di pagamento Stripe.', 'open-events' );
		return new \WP_Error( 'stripe_checkout_failed', $message );
	}

	return [ 'id' => $data['id'], 'url' => $data['url'] ];
}

/**
 * Recupera l'URL della ricevuta Stripe per un pagamento riuscito. Il Checkout
 * Session del webhook contiene solo l'ID del PaymentIntent (stringa, non
 * espanso): serve una chiamata GET separata con expand su latest_charge per
 * arrivare al receipt_url, che vive sull'oggetto Charge, non sul PaymentIntent.
 * Ritorna la URL (string) o '' se non disponibile/errore.
 */
function open_events_featured_fetch_receipt_url( $payment_intent_id ) {
	if ( ! $payment_intent_id ) {
		return '';
	}

	$settings = open_events_get_featured_events_settings();
	if ( empty( $settings['stripe_secret_key'] ) ) {
		return '';
	}

	$response = wp_remote_get(
		add_query_arg( [ 'expand' => [ 'latest_charge' ] ], 'https://api.stripe.com/v1/payment_intents/' . rawurlencode( $payment_intent_id ) ),
		[
			'headers' => [ 'Authorization' => 'Bearer ' . $settings['stripe_secret_key'] ],
			'timeout' => 15,
		]
	);

	if ( is_wp_error( $response ) ) {
		return '';
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	return $data['latest_charge']['receipt_url'] ?? '';
}

/**
 * URL ricevuta per un evento pagato, con cache su meta e recupero "pigro":
 * il webhook (round corrente) la salva subito su _illi_featured_receipt_url,
 * ma i pagamenti confermati PRIMA di questa modifica non ce l'hanno — qui la
 * recuperiamo al volo dalla Checkout Session (_illi_featured_payment_id) e la
 * mettiamo in cache, cosi' la chiamata a Stripe scatta una sola volta a evento.
 */
function open_events_featured_get_receipt_url( $post_id ) {
	$cached = get_post_meta( $post_id, '_illi_featured_receipt_url', true );
	if ( $cached ) {
		return $cached;
	}

	if ( 'paid' !== get_post_meta( $post_id, '_illi_featured_status', true ) ) {
		return '';
	}

	$session_id = get_post_meta( $post_id, '_illi_featured_payment_id', true );
	if ( ! $session_id ) {
		return '';
	}

	$settings = open_events_get_featured_events_settings();
	if ( empty( $settings['stripe_secret_key'] ) ) {
		return '';
	}

	$response = wp_remote_get(
		add_query_arg( [ 'expand' => [ 'payment_intent.latest_charge' ] ], 'https://api.stripe.com/v1/checkout/sessions/' . rawurlencode( $session_id ) ),
		[
			'headers' => [ 'Authorization' => 'Bearer ' . $settings['stripe_secret_key'] ],
			'timeout' => 15,
		]
	);

	if ( is_wp_error( $response ) ) {
		return '';
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	$receipt_url = $data['payment_intent']['latest_charge']['receipt_url'] ?? '';
	if ( $receipt_url ) {
		update_post_meta( $post_id, '_illi_featured_receipt_url', esc_url_raw( $receipt_url ) );
	}

	return $receipt_url;
}

/**
 * Punto unico per avviare/riprendere il pagamento "Consigliato" su un
 * evento: verifica stato e slot, salva la data, crea la Checkout Session e
 * aggiorna i meta. Usato sia dal salvataggio del form evento sia
 * dall'azione rapida "Rendi Consigliato"/"Completa il pagamento" nell'elenco
 * eventi — un solo posto che sa come farlo, non due copie della stessa logica.
 *
 * Ritorna l'URL della Checkout Session (string) o un WP_Error.
 */
function open_events_featured_start_checkout_flow( $post_id, $return_to ) {
	if ( 'paid' === get_post_meta( $post_id, '_illi_featured_status', true ) ) {
		return new \WP_Error( 'already_featured', esc_html__( 'Questo evento è già Consigliato.', 'open-events' ) );
	}

	$event_date = get_post_meta( $post_id, '_EventStartDate', true );
	if ( ! $event_date ) {
		return new \WP_Error( 'no_date', esc_html__( 'Imposta prima una data per l\'evento.', 'open-events' ) );
	}

	if ( ! open_events_featured_slot_available_for_date( $event_date, $post_id ) ) {
		return new \WP_Error( 'slot_unavailable', esc_html__( 'Non ci sono più disponibilità per pubblicizzare eventi in queste date.', 'open-events' ) );
	}

	open_events_featured_save_event_date( $post_id, $event_date );
	update_post_meta( $post_id, '_illi_featured_status', 'pending_payment' );

	$checkout_return_base = admin_url( 'admin-post.php' );
	$session = open_events_featured_create_checkout_session(
		$post_id,
		add_query_arg( [ 'action' => 'oe_featured_checkout_return', 'status' => 'success', 'redirect_to' => rawurlencode( $return_to ) ], $checkout_return_base ),
		add_query_arg( [ 'action' => 'oe_featured_checkout_return', 'status' => 'cancelled', 'redirect_to' => rawurlencode( $return_to ) ], $checkout_return_base )
	);

	if ( is_wp_error( $session ) ) {
		update_post_meta( $post_id, '_illi_featured_status', 'none' );
		return $session;
	}

	update_post_meta( $post_id, '_illi_featured_payment_id', $session['id'] );
	return $session['url'];
}

/**
 * Pagina di atterraggio dopo il redirect da Stripe Checkout (success_url/
 * cancel_url) — mostra solo un messaggio e riporta l'utente da dove veniva.
 * NON è la fonte di verità sul pagamento (quella è sempre il webhook, vedi
 * featured-events-webhook.php): un utente che chiude il browser prima di
 * tornare qui avrebbe comunque lo stato aggiornato correttamente dal webhook.
 */
function open_events_featured_handle_checkout_return() {
	$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
	$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/' );

	wp_safe_redirect( add_query_arg( 'oe_featured_checkout', $status, $redirect_to ) );
	exit;
}
add_action( 'admin_post_nopriv_oe_featured_checkout_return', __NAMESPACE__ . '\\open_events_featured_handle_checkout_return' );
add_action( 'admin_post_oe_featured_checkout_return', __NAMESPACE__ . '\\open_events_featured_handle_checkout_return' );
