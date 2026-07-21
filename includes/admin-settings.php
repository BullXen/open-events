<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

const DEFAULT_EVENT_STATUS_OPTION = 'open_events_default_event_status';
const DESCRIPTION_EDITOR_OPTION   = 'open_events_description_editor';
const AVAILABLE_CITIES_OPTION     = 'open_events_available_cities';

/**
 * Trova la posizione del menu "Eventi" (tribe_events) cosi' da inserire
 * il nostro menu subito dopo, senza doverla ricalcolare a mano.
 */
function open_events_admin_menu_position() {
	global $menu;
	$fallback = 25.1;

	if ( ! is_array( $menu ) ) {
		return $fallback;
	}

	foreach ( $menu as $position => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=tribe_events' === $item[2] ) {
			return $position + 0.1;
		}
	}

	return $fallback;
}

function open_events_register_admin_menu() {
	$position = open_events_admin_menu_position();

	add_menu_page(
		esc_html__( 'Open Events', 'open-events' ),
		esc_html__( 'Open Events', 'open-events' ),
		'manage_options',
		'open-events',
		__NAMESPACE__ . '\\open_events_render_settings_page',
		'dashicons-calendar-alt',
		$position
	);

	add_submenu_page(
		'open-events',
		esc_html__( 'Impostazioni', 'open-events' ),
		esc_html__( 'Impostazioni', 'open-events' ),
		'manage_options',
		'open-events',
		__NAMESPACE__ . '\\open_events_render_settings_page'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\\open_events_register_admin_menu', 20 );

function open_events_get_default_event_status() {
	$status  = get_option( DEFAULT_EVENT_STATUS_OPTION, 'pending' );
	$allowed = [ 'draft', 'pending', 'publish' ];

	return in_array( $status, $allowed, true ) ? $status : 'pending';
}

function open_events_get_description_editor_mode() {
	$mode    = get_option( DESCRIPTION_EDITOR_OPTION, 'visual' );
	$allowed = [ 'classic', 'visual' ];

	return in_array( $mode, $allowed, true ) ? $mode : 'visual';
}

function open_events_get_available_cities() {
	$cities = get_option( AVAILABLE_CITIES_OPTION, [] );

	return is_array( $cities ) ? $cities : [];
}

function open_events_parse_cities_input( $raw ) {
	$lines  = preg_split( '/[\r\n]+/', (string) $raw );
	$cities = [];

	foreach ( $lines as $line ) {
		$city = sanitize_text_field( trim( $line ) );
		if ( '' !== $city && ! in_array( $city, $cities, true ) ) {
			$cities[] = $city;
		}
	}

	return $cities;
}

function open_events_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved = false;

	if ( isset( $_POST['open_events_settings_submit'] ) && wp_verify_nonce( $_POST['open_events_settings_nonce'] ?? '', 'open_events_save_settings' ) ) {
		$allowed_statuses = [ 'draft', 'pending', 'publish' ];
		$status           = sanitize_text_field( wp_unslash( $_POST['default_event_status'] ?? '' ) );

		if ( in_array( $status, $allowed_statuses, true ) ) {
			update_option( DEFAULT_EVENT_STATUS_OPTION, $status );
			$saved = true;
		}

		$allowed_editor_modes = [ 'classic', 'visual' ];
		$editor_mode          = sanitize_text_field( wp_unslash( $_POST['description_editor'] ?? '' ) );

		if ( in_array( $editor_mode, $allowed_editor_modes, true ) ) {
			update_option( DESCRIPTION_EDITOR_OPTION, $editor_mode );
			$saved = true;
		}

		$cities = open_events_parse_cities_input( wp_unslash( $_POST['available_cities'] ?? '' ) );
		update_option( AVAILABLE_CITIES_OPTION, $cities );
		$saved = true;
	}

	$current_status = open_events_get_default_event_status();
	$current_editor_mode = open_events_get_description_editor_mode();
	$current_cities = open_events_get_available_cities();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Open Events - Impostazioni', 'open-events' ); ?></h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Impostazioni salvate.', 'open-events' ); ?></p></div>
		<?php endif; ?>

		<form method="POST">
			<?php wp_nonce_field( 'open_events_save_settings', 'open_events_settings_nonce' ); ?>
			<input type="hidden" name="open_events_settings_submit" value="1">

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Stato default nuovi eventi', 'open-events' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php esc_html_e( 'Stato default nuovi eventi', 'open-events' ); ?></legend>

							<label>
								<input type="radio" name="default_event_status" value="draft" <?php checked( $current_status, 'draft' ); ?>>
								<?php esc_html_e( 'Bozza', 'open-events' ); ?>
							</label><br>

							<label>
								<input type="radio" name="default_event_status" value="pending" <?php checked( $current_status, 'pending' ); ?>>
								<?php esc_html_e( 'In attesa di revisione', 'open-events' ); ?>
							</label><br>

							<label>
								<input type="radio" name="default_event_status" value="publish" <?php checked( $current_status, 'publish' ); ?>>
								<?php esc_html_e( 'Pubblicato', 'open-events' ); ?>
							</label>

							<p class="description">
								<?php esc_html_e( 'Stato assegnato agli eventi inseriti o modificati dagli utenti tramite il widget Front-end Events Manager.', 'open-events' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Descrizione Evento', 'open-events' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php esc_html_e( 'Descrizione Evento', 'open-events' ); ?></legend>

							<label>
								<input type="radio" name="description_editor" value="classic" <?php checked( $current_editor_mode, 'classic' ); ?>>
								<?php esc_html_e( 'Classico campo', 'open-events' ); ?>
							</label><br>

							<label>
								<input type="radio" name="description_editor" value="visual" <?php checked( $current_editor_mode, 'visual' ); ?>>
								<?php esc_html_e( 'Editor visuale', 'open-events' ); ?>
							</label>

							<p class="description">
								<?php esc_html_e( 'Tipo di campo mostrato agli utenti per la descrizione di eventi, luoghi e organizzatori nel widget Front-end Events Manager: un semplice campo di testo o l\'editor visuale di WordPress.', 'open-events' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="open_events_available_cities"><?php esc_html_e( 'Città disponibili', 'open-events' ); ?></label></th>
					<td>
						<textarea name="available_cities" id="open_events_available_cities" rows="8" class="large-text" placeholder="<?php esc_attr_e( "Iseo\nSulzano\nMonte Isola\n...", 'open-events' ); ?>"><?php echo esc_textarea( implode( "\n", $current_cities ) ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Una città per riga. Se presenti, gli utenti potranno scegliere la città del luogo da questa lista (invece di scriverla liberamente) quando inseriscono un evento o un luogo.', 'open-events' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button( esc_html__( 'Salva Modifiche', 'open-events' ) ); ?>
		</form>
	</div>
	<?php
}
