<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

const DEFAULT_EVENT_STATUS_OPTION = 'open_events_default_event_status';
const DESCRIPTION_EDITOR_OPTION   = 'open_events_description_editor';
const AVAILABLE_CITIES_OPTION     = 'open_events_available_cities';
const FEATURED_LABEL_OPTION       = 'open_events_featured_label';
const FEATURED_LIMIT_OPTION       = 'open_events_featured_limit';
const PUBLISHED_BY_DISPLAY_OPTION = 'open_events_published_by_display';

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

function open_events_get_featured_label() {
	$label = get_option( FEATURED_LABEL_OPTION, '' );
	$label = is_string( $label ) ? trim( $label ) : '';

	return '' !== $label ? $label : esc_html__( 'In Primo Piano', 'open-events' );
}

function open_events_get_featured_limit() {
	$limit = absint( get_option( FEATURED_LIMIT_OPTION, 3 ) );

	return $limit;
}

function open_events_get_published_by_display() {
	$mode    = get_option( PUBLISHED_BY_DISPLAY_OPTION, 'organizer' );
	$allowed = [ 'organizer', 'username', 'email' ];

	return in_array( $mode, $allowed, true ) ? $mode : 'organizer';
}

/**
 * Conta gli eventi attualmente in primo piano, escludendo opzionalmente
 * un post (usato in fase di salvataggio per non contare l'evento che si
 * sta proprio modificando).
 */
function open_events_count_featured_events( $exclude_id = 0 ) {
	global $wpdb;

	$sql = "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_tribe_featured' AND pm.meta_value = '1'
		AND p.post_type = 'tribe_events' AND p.post_status != 'trash'";

	if ( $exclude_id ) {
		$sql .= $wpdb->prepare( ' AND p.ID != %d', $exclude_id );
	}

	return (int) $wpdb->get_var( $sql );
}

/**
 * Forza lo status di un post via query diretta, per i casi in cui
 * wp_update_post()/wp_trash_post() falliscono in silenzio (es. capability
 * di pubblicazione mappate in modo non standard da un CPT di terze parti
 * come tribe_events). Rilancia comunque gli hook di transizione standard
 * cosi' plugin come The Events Calendar possono aggiornare la propria
 * cache/indice interno.
 */
function open_events_force_post_status( $post_id, $new_status ) {
	global $wpdb;

	$post = get_post( $post_id );
	if ( ! $post ) {
		return;
	}

	$old_status = $post->post_status;
	$wpdb->update( $wpdb->posts, [ 'post_status' => $new_status ], [ 'ID' => $post_id ] );
	clean_post_cache( $post_id );

	$updated_post = get_post( $post_id );
	wp_transition_post_status( $new_status, $old_status, $updated_post );
	do_action( 'save_post', $post_id, $updated_post, true );
	do_action( 'save_post_' . $updated_post->post_type, $post_id, $updated_post, true );
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

		$featured_label = sanitize_text_field( wp_unslash( $_POST['featured_label'] ?? '' ) );
		update_option( FEATURED_LABEL_OPTION, $featured_label );

		$featured_limit = absint( wp_unslash( $_POST['featured_limit'] ?? 0 ) );
		update_option( FEATURED_LIMIT_OPTION, $featured_limit );
		$saved = true;

		$allowed_published_by = [ 'organizer', 'username', 'email' ];
		$published_by          = sanitize_text_field( wp_unslash( $_POST['published_by_display'] ?? '' ) );

		if ( in_array( $published_by, $allowed_published_by, true ) ) {
			update_option( PUBLISHED_BY_DISPLAY_OPTION, $published_by );
			$saved = true;
		}
	}

	$current_status = open_events_get_default_event_status();
	$current_editor_mode = open_events_get_description_editor_mode();
	$current_cities = open_events_get_available_cities();
	$current_featured_label = open_events_get_featured_label();
	$current_featured_limit = open_events_get_featured_limit();
	$current_featured_count = open_events_count_featured_events();
	$current_published_by = open_events_get_published_by_display();
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
				<tr>
					<th scope="row"><label for="open_events_featured_label"><?php esc_html_e( 'Testo In Primo Piano', 'open-events' ); ?></label></th>
					<td>
						<input type="text" name="featured_label" id="open_events_featured_label" class="regular-text" value="<?php echo esc_attr( $current_featured_label ); ?>" placeholder="<?php esc_attr_e( 'In Primo Piano', 'open-events' ); ?>">
						<p class="description">
							<?php esc_html_e( 'Etichetta mostrata accanto agli eventi contrassegnati come "in primo piano" nell\'elenco eventi.', 'open-events' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="open_events_featured_limit"><?php esc_html_e( 'Limite eventi in primo piano', 'open-events' ); ?></label></th>
					<td>
						<input type="number" name="featured_limit" id="open_events_featured_limit" class="small-text" min="0" step="1" value="<?php echo esc_attr( $current_featured_limit ); ?>">
						<p class="description">
							<?php printf(
								esc_html__( 'Numero massimo di eventi che possono essere messi in primo piano contemporaneamente. Usa 0 per nessun limite. Attualmente in primo piano: %d.', 'open-events' ),
								intval( $current_featured_count )
							); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Visualizzazione "pubblicato da"', 'open-events' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php esc_html_e( 'Visualizzazione "pubblicato da"', 'open-events' ); ?></legend>

							<label>
								<input type="radio" name="published_by_display" value="organizer" <?php checked( $current_published_by, 'organizer' ); ?>>
								<?php esc_html_e( 'Nome Organizzatore', 'open-events' ); ?>
							</label><br>

							<label>
								<input type="radio" name="published_by_display" value="username" <?php checked( $current_published_by, 'username' ); ?>>
								<?php esc_html_e( 'Username', 'open-events' ); ?>
							</label><br>

							<label>
								<input type="radio" name="published_by_display" value="email" <?php checked( $current_published_by, 'email' ); ?>>
								<?php esc_html_e( 'Email', 'open-events' ); ?>
							</label>

							<p class="description">
								<?php esc_html_e( 'Cosa mostrare, accanto al nome di chi ha inserito l\'evento, nell\'elenco eventi lato admin. "Nome Organizzatore" mostra l\'organizzatore collegato all\'evento (se presente), altrimenti ricade sullo username.', 'open-events' ); ?>
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
