<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

const FEATURED_EVENTS_SETTINGS_OPTION = 'open_events_featured_events_settings';

/**
 * Un'unica option strutturata (array associativo), stesso pattern di
 * open_events_get_community_settings() — qui per lo stesso motivo: un
 * modulo a sé stante (pagamenti Stripe) più semplice da esportare/importare
 * per intero invece di tante option separate.
 */
function open_events_get_featured_events_settings() {
	$defaults = [
		'stripe_mode'            => 'test',
		'stripe_publishable_key' => '',
		'stripe_secret_key'      => '',
		'stripe_webhook_secret'  => '',
		'stripe_price_amount'    => 0,
		'stripe_price_currency'  => 'EUR',
		'info_page_id'           => 0,
		'max_slots_per_week'     => 3,
		'featured_badge_color'   => '#b45309',
		'benefits_enabled'       => false,
		'benefits_list'          => '',
	];

	$saved = get_option( FEATURED_EVENTS_SETTINGS_OPTION, [] );
	if ( ! is_array( $saved ) ) {
		$saved = [];
	}

	return array_merge( $defaults, $saved );
}

function open_events_update_featured_events_settings( array $data ) {
	update_option( FEATURED_EVENTS_SETTINGS_OPTION, $data );
}

function open_events_register_featured_events_admin_menu() {
	add_submenu_page(
		'open-events',
		esc_html__( 'Eventi Consigliati', 'open-events' ),
		esc_html__( 'Eventi Consigliati', 'open-events' ),
		'manage_options',
		'open-events-featured',
		__NAMESPACE__ . '\\open_events_render_featured_events_settings_page'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\\open_events_register_featured_events_admin_menu', 20 );

function open_events_render_featured_events_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved = false;

	if ( isset( $_POST['open_events_featured_settings_submit'] ) && wp_verify_nonce( $_POST['open_events_featured_settings_nonce'] ?? '', 'open_events_save_featured_settings' ) ) {
		$allowed_modes = [ 'test', 'live' ];
		$stripe_mode = sanitize_text_field( wp_unslash( $_POST['stripe_mode'] ?? 'test' ) );
		if ( ! in_array( $stripe_mode, $allowed_modes, true ) ) {
			$stripe_mode = 'test';
		}

		$allowed_currencies = [ 'EUR', 'USD' ];
		$currency = sanitize_text_field( wp_unslash( $_POST['stripe_price_currency'] ?? 'EUR' ) );
		if ( ! in_array( $currency, $allowed_currencies, true ) ) {
			$currency = 'EUR';
		}

		$data = [
			'stripe_mode'            => $stripe_mode,
			'stripe_publishable_key' => sanitize_text_field( wp_unslash( $_POST['stripe_publishable_key'] ?? '' ) ),
			'stripe_secret_key'      => sanitize_text_field( wp_unslash( $_POST['stripe_secret_key'] ?? '' ) ),
			'stripe_webhook_secret'  => sanitize_text_field( wp_unslash( $_POST['stripe_webhook_secret'] ?? '' ) ),
			'stripe_price_amount'    => absint( wp_unslash( $_POST['stripe_price_amount'] ?? 0 ) ),
			'stripe_price_currency'  => $currency,
			'info_page_id'           => absint( wp_unslash( $_POST['info_page_id'] ?? 0 ) ),
			'max_slots_per_week'     => absint( wp_unslash( $_POST['max_slots_per_week'] ?? 3 ) ),
			'featured_badge_color'   => sanitize_hex_color( wp_unslash( $_POST['featured_badge_color'] ?? '' ) ) ?: '#b45309',
			'benefits_enabled'       => isset( $_POST['benefits_enabled'] ),
			'benefits_list'          => sanitize_textarea_field( wp_unslash( $_POST['benefits_list'] ?? '' ) ),
		];

		open_events_update_featured_events_settings( $data );
		$saved = true;
	}

	$settings = open_events_get_featured_events_settings();
	$webhook_url = rest_url( 'open-events/v1/stripe-webhook' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Open Events - Eventi Consigliati', 'open-events' ); ?></h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Impostazioni salvate.', 'open-events' ); ?></p></div>
		<?php endif; ?>

		<form method="POST">
			<?php wp_nonce_field( 'open_events_save_featured_settings', 'open_events_featured_settings_nonce' ); ?>
			<input type="hidden" name="open_events_featured_settings_submit" value="1">

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Modalità Stripe', 'open-events' ); ?></th>
					<td>
						<label><input type="radio" name="stripe_mode" value="test" <?php checked( $settings['stripe_mode'], 'test' ); ?>> <?php esc_html_e( 'Test', 'open-events' ); ?></label><br>
						<label><input type="radio" name="stripe_mode" value="live" <?php checked( $settings['stripe_mode'], 'live' ); ?>> <?php esc_html_e( 'Live', 'open-events' ); ?></label>
						<p class="description"><?php esc_html_e( 'Solo etichetta informativa: usa le chiavi pubblica/segreta del modo corrispondente (test o live) qui sotto.', 'open-events' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_stripe_publishable_key"><?php esc_html_e( 'Chiave pubblicabile Stripe', 'open-events' ); ?></label></th>
					<td><input type="text" id="oe_stripe_publishable_key" name="stripe_publishable_key" class="regular-text" value="<?php echo esc_attr( $settings['stripe_publishable_key'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_stripe_secret_key"><?php esc_html_e( 'Chiave segreta Stripe', 'open-events' ); ?></label></th>
					<td><input type="password" id="oe_stripe_secret_key" name="stripe_secret_key" class="regular-text" value="<?php echo esc_attr( $settings['stripe_secret_key'] ); ?>" autocomplete="off"></td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_stripe_webhook_secret"><?php esc_html_e( 'Segreto webhook Stripe', 'open-events' ); ?></label></th>
					<td>
						<input type="text" id="oe_stripe_webhook_secret" name="stripe_webhook_secret" class="regular-text" value="<?php echo esc_attr( $settings['stripe_webhook_secret'] ); ?>" autocomplete="off" spellcheck="false">
						<p class="description">
							<?php esc_html_e( 'Su Stripe → Sviluppatori → Webhook, crea un endpoint con questo indirizzo esatto e ascolta almeno gli eventi checkout.session.completed e checkout.session.expired. Copia qui il "Signing secret" mostrato da Stripe:', 'open-events' ); ?><br>
							<code><?php echo esc_html( $webhook_url ); ?></code>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_stripe_price_amount"><?php esc_html_e( 'Prezzo (in centesimi)', 'open-events' ); ?></label></th>
					<td>
						<input type="number" id="oe_stripe_price_amount" name="stripe_price_amount" class="small-text" min="0" step="1" value="<?php echo esc_attr( $settings['stripe_price_amount'] ); ?>">
						<p class="description"><?php esc_html_e( 'Es. 1000 = 10,00. Mostrato all\'utente già formattato nel form evento.', 'open-events' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_stripe_price_currency"><?php esc_html_e( 'Valuta', 'open-events' ); ?></label></th>
					<td>
						<select name="stripe_price_currency" id="oe_stripe_price_currency">
							<option value="EUR" <?php selected( $settings['stripe_price_currency'], 'EUR' ); ?>>EUR</option>
							<option value="USD" <?php selected( $settings['stripe_price_currency'], 'USD' ); ?>>USD</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_info_page"><?php esc_html_e( 'Pagina informativa offerta', 'open-events' ); ?></label></th>
					<td>
						<?php
						wp_dropdown_pages( [
							'name'              => 'info_page_id',
							'id'                => 'oe_info_page',
							'selected'          => $settings['info_page_id'],
							'show_option_none'  => esc_html__( '-- Nessuna --', 'open-events' ),
							'option_none_value' => 0,
						] );
						?>
						<p class="description"><?php esc_html_e( 'Pagina che spiega il servizio "Evento Consigliato", linkata dal form di invio evento.', 'open-events' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_max_slots_per_week"><?php esc_html_e( 'Slot massimi per settimana', 'open-events' ); ?></label></th>
					<td>
						<input type="number" id="oe_max_slots_per_week" name="max_slots_per_week" class="small-text" min="0" step="1" value="<?php echo esc_attr( $settings['max_slots_per_week'] ); ?>">
						<p class="description"><?php esc_html_e( 'Numero massimo di eventi Consigliati (pagati) per settimana solare. Usa 0 per nessun limite.', 'open-events' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_featured_badge_color"><?php esc_html_e( 'Colore badge "Consigliato"', 'open-events' ); ?></label></th>
					<td><input type="text" id="oe_featured_badge_color" name="featured_badge_color" value="<?php echo esc_attr( $settings['featured_badge_color'] ); ?>" placeholder="#b45309"></td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_benefits_enabled"><?php esc_html_e( 'Elenco vantaggi nel form', 'open-events' ); ?></label></th>
					<td>
						<label><input type="checkbox" id="oe_benefits_enabled" name="benefits_enabled" value="1" <?php checked( $settings['benefits_enabled'] ); ?>> <?php esc_html_e( 'Mostra un elenco vantaggi quando l\'utente seleziona "Rendi il mio evento Consigliato"', 'open-events' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_benefits_list"><?php esc_html_e( 'Vantaggi (uno per riga)', 'open-events' ); ?></label></th>
					<td>
						<textarea id="oe_benefits_list" name="benefits_list" class="large-text" rows="6"><?php echo esc_textarea( $settings['benefits_list'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Un vantaggio per riga, mostrato come elenco puntato (2 colonne su mobile, 3 su desktop).', 'open-events' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button( esc_html__( 'Salva Modifiche', 'open-events' ) ); ?>
		</form>
	</div>
	<?php
}
