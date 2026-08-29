<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

const DEFAULT_EVENT_STATUS_OPTION = 'open_events_default_event_status';
const DESCRIPTION_EDITOR_OPTION   = 'open_events_description_editor';
const AVAILABLE_CITIES_OPTION     = 'open_events_available_cities';
const FEATURED_LABEL_OPTION       = 'open_events_featured_label';
const FEATURED_LIMIT_OPTION       = 'open_events_featured_limit';
const PUBLISHED_BY_DISPLAY_OPTION = 'open_events_published_by_display';
const VENUE_VISIBILITY_OPTION     = 'open_events_venue_visibility';
const ALL_DAY_EVENT_OPTION        = 'open_events_enable_all_day_event';
const RECURRING_EVENT_OPTION      = 'open_events_enable_recurring_event';
const PAST_DATES_OPTION           = 'open_events_enable_past_dates';
const SLIDE_AUTO_HOME_OPTION      = 'open_events_slide_auto_home';
const SLIDE_HOME_MAX_ITEMS_OPTION = 'open_events_slide_home_max_items';
const PORTAL_PAGE_OPTION          = 'open_events_portal_page';

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
 * Visibilità dei luoghi nel menu a tendina del form evento:
 * 'all'  = l'utente vede tutti i luoghi pubblicati (default);
 * 'own'  = l'utente vede solo i luoghi che ha inserito lui stesso.
 */
function open_events_get_venue_visibility() {
	$mode    = get_option( VENUE_VISIBILITY_OPTION, 'all' );
	$allowed = [ 'all', 'own' ];

	return in_array( $mode, $allowed, true ) ? $mode : 'all';
}

/**
 * Attiva/disattiva i campo "Evento Giornaliero" e "Evento con più date
 * (ricorrente)" nel form di inserimento/modifica evento del widget
 * front-end. Entrambi abilitati di default.
 */
function open_events_is_all_day_event_enabled() {
	return '0' !== get_option( ALL_DAY_EVENT_OPTION, '1' );
}

function open_events_is_recurring_event_enabled() {
	return '0' !== get_option( RECURRING_EVENT_OPTION, '1' );
}

/**
 * Se disattivo (default), nei calendari del form evento non si possono
 * selezionare/navigare mesi, anni o giorni precedenti a oggi.
 */
function open_events_is_past_dates_enabled() {
	return '1' === get_option( PAST_DATES_OPTION, '0' );
}

/**
 * Se attivo, la Slide Eventi (con priorità ai Consigliati/in primo piano)
 * viene inserita automaticamente in cima al contenuto della homepage, senza
 * bisogno di trascinare il widget Elementor sulla pagina.
 */
function open_events_is_slide_auto_home_enabled() {
	return '1' === get_option( SLIDE_AUTO_HOME_OPTION, '0' );
}

function open_events_get_slide_home_max_items() {
	$max = absint( get_option( SLIDE_HOME_MAX_ITEMS_OPTION, 6 ) );

	return $max > 0 ? min( 20, $max ) : 6;
}

/**
 * ID della pagina dove è installato il widget "Front-end Events Manager" in
 * modalità Hub — usata per costruire link diretti al portale (es. nelle
 * email di notifica) invece che verso wp-admin.
 */
function open_events_get_portal_page_id() {
	return absint( get_option( PORTAL_PAGE_OPTION, 0 ) );
}

/**
 * URL della schermata "Modifica Evento" del portale front-end per un dato
 * evento (stessa rotta letta da Widget_Events_Manager::render(): ?view=
 * seleziona il post_type, ?edit_id= il post da aprire in modifica — vedi
 * class-widget-events-manager.php intorno alla riga 485/911). Se la Pagina
 * Portale non è stata configurata in Impostazioni, ricade sul link di
 * modifica di wp-admin invece di restituire un link rotto.
 */
function open_events_get_event_edit_url( $post_id ) {
	$portal_page_id = open_events_get_portal_page_id();

	if ( $portal_page_id && get_post( $portal_page_id ) ) {
		return add_query_arg(
			[ 'view' => 'tribe_events', 'edit_id' => $post_id ],
			get_permalink( $portal_page_id )
		);
	}

	return admin_url( 'post.php?post=' . $post_id . '&action=edit' );
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
	$data = [ 'post_status' => $new_status ];

	if ( 'publish' === $new_status ) {
		if ( empty( $post->post_name ) ) {
			$data['post_name'] = wp_unique_post_slug( sanitize_title( $post->post_title ), $post_id, $new_status, $post->post_type, $post->post_parent );
		}
		// Bypassando wp_update_post() qui non rischiamo la conversione automatica
		// in 'future' per un post_date fuori dall'ordinario, ma lo forziamo comunque
		// cosi' resta coerente con quanto ci si aspetta da un evento "pubblicato ora".
		$data['post_date'] = current_time( 'mysql' );
		$data['post_date_gmt'] = current_time( 'mysql', true );
	}

	$wpdb->update( $wpdb->posts, $data, [ 'ID' => $post_id ] );
	clean_post_cache( $post_id );

	$updated_post = get_post( $post_id );
	wp_transition_post_status( $new_status, $old_status, $updated_post );
	do_action( 'save_post', $post_id, $updated_post, true );
	do_action( 'save_post_' . $updated_post->post_type, $post_id, $updated_post, true );

	if ( 'tribe_events' === $updated_post->post_type ) {
		// Stesso motivo del rilancio in class-widget-events-manager.php: TEC deve
		// risincronizzare le sue tabelle interne (occorrenze) dopo un cambio di
		// stato forzato via query diretta, altrimenti l'evento risulta "publish"
		// in wp_posts ma resta invisibile lato TEC.
		open_events_sync_event_custom_tables( $post_id );
	}
}

/**
 * Sincronizza un evento con le "custom tables" di The Events Calendar 6+
 * (tabelle tec_events / tec_occurrences), da cui dipende la visibilita' nel
 * calendario pubblico.
 *
 * PERCHE' SERVE: il widget front-end crea l'evento con wp_insert_post() +
 * update_post_meta() scrivendo solo le date LOCALI (_EventStartDate/_EventEndDate).
 * TEC 6, per costruire un'occorrenza, pretende invece anche i meta derivati
 * _EventStartDateUTC, _EventEndDateUTC, _EventTimezone e _EventDuration: senza,
 * TEC\...\Models\Builder::upsert() fallisce ("The start_date_utc requires a value")
 * e NON crea alcuna occorrenza. Risultato: l'evento e' 'publish' in wp_posts ma
 * resta invisibile nel calendario pubblico (occorrenze = 0). Il metabox nativo di
 * TEC in wp-admin scrive quei meta prima del save, il widget no: da qui il bug.
 *
 * Questa funzione calcola i meta UTC/timezone/durata dalle date locali e forza
 * TEC a ricostruire subito (sincrono) evento + occorrenze.
 */
function open_events_sync_event_custom_tables( $post_id ) {
	if ( 'tribe_events' !== get_post_type( $post_id ) ) {
		return;
	}

	$start_local = get_post_meta( $post_id, '_EventStartDate', true );
	$end_local   = get_post_meta( $post_id, '_EventEndDate', true );
	if ( empty( $start_local ) || empty( $end_local ) ) {
		return;
	}

	// Timezone: rispetta quella gia' impostata sull'evento, altrimenti quella del sito.
	$tz_string = get_post_meta( $post_id, '_EventTimezone', true );
	if ( empty( $tz_string ) ) {
		$tz_string = get_option( 'timezone_string' );
		if ( empty( $tz_string ) ) {
			$tz_string = 'UTC';
		}
	}

	try {
		$tz = new \DateTimeZone( $tz_string );
	} catch ( \Exception $e ) {
		$tz        = new \DateTimeZone( 'UTC' );
		$tz_string = 'UTC';
	}

	try {
		$start_obj = new \DateTime( $start_local, $tz );
		$end_obj   = new \DateTime( $end_local, $tz );
	} catch ( \Exception $e ) {
		return;
	}

	$utc       = new \DateTimeZone( 'UTC' );
	$start_utc = ( clone $start_obj )->setTimezone( $utc )->format( 'Y-m-d H:i:s' );
	$end_utc   = ( clone $end_obj )->setTimezone( $utc )->format( 'Y-m-d H:i:s' );
	$duration  = $end_obj->getTimestamp() - $start_obj->getTimestamp();

	update_post_meta( $post_id, '_EventTimezone', $tz_string );
	update_post_meta( $post_id, '_EventTimezoneAbbr', $start_obj->format( 'T' ) );
	update_post_meta( $post_id, '_EventStartDateUTC', $start_utc );
	update_post_meta( $post_id, '_EventEndDateUTC', $end_utc );
	update_post_meta( $post_id, '_EventDuration', (string) $duration );

	// Forza TEC 6 a (ri)costruire evento + occorrenze SUBITO. Senza questo, la
	// sincronizzazione delle custom tables e' rimandata a 'shutdown' e, per un
	// evento salvato dal front-end, puo' non avvenire affatto.
	if ( class_exists( '\TEC\Events\Custom_Tables\V1\Updates\Events' ) ) {
		\tribe( \TEC\Events\Custom_Tables\V1\Updates\Events::class )->update( $post_id );
	} else {
		// Fallback per versioni di TEC precedenti alle custom tables.
		do_action( 'tribe_events_update_meta', $post_id, [] );
	}

	clean_post_cache( $post_id );
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

		$venue_visibility = sanitize_text_field( wp_unslash( $_POST['venue_visibility'] ?? '' ) );
		if ( in_array( $venue_visibility, [ 'all', 'own' ], true ) ) {
			update_option( VENUE_VISIBILITY_OPTION, $venue_visibility );
			$saved = true;
		}

		update_option( ALL_DAY_EVENT_OPTION, isset( $_POST['enable_all_day_event'] ) ? '1' : '0' );
		update_option( RECURRING_EVENT_OPTION, isset( $_POST['enable_recurring_event'] ) ? '1' : '0' );
		update_option( PAST_DATES_OPTION, isset( $_POST['enable_past_dates'] ) ? '1' : '0' );

		update_option( PORTAL_PAGE_OPTION, absint( wp_unslash( $_POST['portal_page_id'] ?? 0 ) ) );

		update_option( SLIDE_AUTO_HOME_OPTION, isset( $_POST['slide_auto_home'] ) ? '1' : '0' );
		$slide_home_max_items = absint( wp_unslash( $_POST['slide_home_max_items'] ?? 6 ) );
		update_option( SLIDE_HOME_MAX_ITEMS_OPTION, $slide_home_max_items > 0 ? $slide_home_max_items : 6 );
		$saved = true;
	}

	$current_status = open_events_get_default_event_status();
	$current_editor_mode = open_events_get_description_editor_mode();
	$current_cities = open_events_get_available_cities();
	$current_featured_label = open_events_get_featured_label();
	$current_featured_limit = open_events_get_featured_limit();
	$current_featured_count = open_events_count_featured_events();
	$current_published_by = open_events_get_published_by_display();
	$current_venue_visibility = open_events_get_venue_visibility();
	$current_all_day_enabled = open_events_is_all_day_event_enabled();
	$current_recurring_enabled = open_events_is_recurring_event_enabled();
	$current_past_dates_enabled = open_events_is_past_dates_enabled();
	$current_portal_page_id = open_events_get_portal_page_id();
	$current_slide_auto_home = open_events_is_slide_auto_home_enabled();
	$current_slide_home_max_items = open_events_get_slide_home_max_items();
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
					<th scope="row"><label for="oe_portal_page"><?php esc_html_e( 'Pagina Portale', 'open-events' ); ?></label></th>
					<td>
						<?php
						wp_dropdown_pages( [
							'name'              => 'portal_page_id',
							'id'                => 'oe_portal_page',
							'selected'          => $current_portal_page_id,
							'show_option_none'  => esc_html__( '-- Nessuna --', 'open-events' ),
							'option_none_value' => 0,
						] );
						?>
						<p class="description">
							<?php esc_html_e( 'La pagina dove hai messo il widget "Front-end Events Manager" in modalità Hub/Dashboard. Usata per generare link diretti al portale (es. nell\'email "Nuovo evento da revisionare") invece che a wp-admin. Senza questa pagina impostata, quei link continuano a puntare a wp-admin.', 'open-events' ); ?>
						</p>
					</td>
				</tr>
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
				<tr>
					<th scope="row"><?php esc_html_e( 'Visibilità luoghi nel form evento', 'open-events' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php esc_html_e( 'Visibilità luoghi nel form evento', 'open-events' ); ?></legend>

							<label>
								<input type="radio" name="venue_visibility" value="all" <?php checked( $current_venue_visibility, 'all' ); ?>>
								<?php esc_html_e( 'Tutti i luoghi', 'open-events' ); ?>
							</label><br>

							<label>
								<input type="radio" name="venue_visibility" value="own" <?php checked( $current_venue_visibility, 'own' ); ?>>
								<?php esc_html_e( 'Solo i luoghi inseriti dall\'utente', 'open-events' ); ?>
							</label>

							<p class="description">
								<?php esc_html_e( 'Quali luoghi può scegliere l\'utente nel menu a tendina "Seleziona Luogo" quando inserisce un evento. "Tutti i luoghi" (predefinito) mostra ogni luogo pubblicato; "Solo i luoghi inseriti dall\'utente" limita la scelta ai luoghi creati da lui. Gli amministratori vedono comunque tutti i luoghi.', 'open-events' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Funzioni form Inserisci Evento', 'open-events' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php esc_html_e( 'Funzioni form Inserisci Evento', 'open-events' ); ?></legend>

							<label>
								<input type="checkbox" name="enable_all_day_event" value="1" <?php checked( $current_all_day_enabled ); ?>>
								<?php esc_html_e( 'Evento Giornaliero (Tutto il giorno)', 'open-events' ); ?>
							</label><br>

							<label>
								<input type="checkbox" name="enable_recurring_event" value="1" <?php checked( $current_recurring_enabled ); ?>>
								<?php esc_html_e( 'Evento ricorrente (con più date)', 'open-events' ); ?>
							</label>

							<p class="description">
								<?php esc_html_e( 'Mostra o nasconde queste due opzioni nel form di inserimento/modifica evento del widget front-end.', 'open-events' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Date antecedenti', 'open-events' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php esc_html_e( 'Date antecedenti', 'open-events' ); ?></legend>

							<label>
								<input type="checkbox" name="enable_past_dates" value="1" <?php checked( $current_past_dates_enabled ); ?>>
								<?php esc_html_e( 'Attivo', 'open-events' ); ?>
							</label>

							<p class="description">
								<?php esc_html_e( 'Se non attivo (default), nei calendari del form Inserisci Evento si vedono solo mesi e anni dal giorno corrente in poi: non è possibile selezionare o navigare a date precedenti a oggi.', 'open-events' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Slide eventi in homepage', 'open-events' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="slide_auto_home" value="1" <?php checked( $current_slide_auto_home ); ?>>
							<?php esc_html_e( 'Inserisci automaticamente la Slide Eventi in cima alla homepage', 'open-events' ); ?>
						</label>
						<p class="description">
							<label for="open_events_slide_home_max_items">
								<?php esc_html_e( 'Numero eventi mostrati:', 'open-events' ); ?>
								<input type="number" name="slide_home_max_items" id="open_events_slide_home_max_items" class="small-text" min="1" max="20" step="1" value="<?php echo esc_attr( $current_slide_home_max_items ); ?>">
							</label>
							<br>
							<?php esc_html_e( 'In alternativa (o in aggiunta), la Slide Eventi è disponibile anche come widget Elementor "Slide Eventi Consigliati" o come shortcode [open_events_slide] da inserire in qualunque pagina.', 'open-events' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button( esc_html__( 'Salva Modifiche', 'open-events' ) ); ?>
		</form>
	</div>
	<?php
}
