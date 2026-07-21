<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

const DEFAULT_EVENT_STATUS_OPTION = 'open_events_default_event_status';

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

function open_events_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved = false;

	if ( isset( $_POST['open_events_settings_submit'] ) && wp_verify_nonce( $_POST['open_events_settings_nonce'] ?? '', 'open_events_save_settings' ) ) {
		$allowed = [ 'draft', 'pending', 'publish' ];
		$status  = sanitize_text_field( wp_unslash( $_POST['default_event_status'] ?? '' ) );

		if ( in_array( $status, $allowed, true ) ) {
			update_option( DEFAULT_EVENT_STATUS_OPTION, $status );
			$saved = true;
		}
	}

	$current_status = open_events_get_default_event_status();
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
			</table>

			<?php submit_button( esc_html__( 'Salva Modifiche', 'open-events' ) ); ?>
		</form>
	</div>
	<?php
}
