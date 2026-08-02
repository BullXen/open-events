<?php
/*
Plugin Name: Open Events
Plugin URI: https://github.com/BullXen/open-events
Description: Plugin per la gestione di eventi. Aggiunge a Elementor un widget che permette agli utenti loggati di gestire da front-end eventi, luoghi e organizzatori (The Events Calendar) come un portale.
Version: 1.2.0
Author: BullXen
GitHub Plugin URI: BullXen/open-events
Primary Branch: main
Text Domain: open-events
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OPEN_EVENTS_VERSION', '1.2.16' );
define( 'OPEN_EVENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPEN_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Richiesto sempre (non solo in admin): il widget front-end legge
// open_events_get_default_event_status() quando un utente salva un evento.
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/core/admin-settings.php';

// Richiesto sempre: registra gli handler AJAX della Ricerca Eventi, che
// vengono serviti da admin-ajax.php (dove il widget Elementor non è caricato).
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/events-search/events-search.php';

/**
 * Porta in cima gli eventi "in primo piano" (_tribe_featured) anche nel
 * calendario pubblico di The Events Calendar, non solo nella dashboard
 * del portale. Aggiunge un LEFT JOIN + un criterio di ordinamento in
 * testa a quello già presente (non lo sostituisce), cosi' l'ordinamento
 * per data che usa TEC internamente resta intatto per gli eventi non
 * in primo piano. Agganciato a posts_clauses (non pre_get_posts) perché
 * altri filtri di TEC intervengono a quel livello e $query->set() da solo
 * non basta a garantire la precedenza.
 *
 * La query va riconosciuta dal SOLO post_type: NON si puo' usare
 * tribe_is_event_query(), che con TEC 6 (custom tables / Views v2) ritorna
 * FALSE per le query costruite dalla ORM del calendario pubblico — proprio
 * quelle che ci interessano (verificato: con quel controllo il filtro non
 * veniva mai applicato e l'evento in primo piano non finiva mai in testa).
 */
function open_events_pin_featured_events_clauses( $clauses, $query ) {
	if ( is_admin() ) {
		return $clauses;
	}

	$post_type = $query->get( 'post_type' );
	$is_event_query = ( 'tribe_events' === $post_type )
		|| ( is_array( $post_type ) && in_array( 'tribe_events', $post_type, true ) );

	if ( ! $is_event_query ) {
		return $clauses;
	}

	// posts_clauses puo' scattare piu' volte sulla stessa query (es. query
	// principale + conteggio): senza questo guard il LEFT JOIN verrebbe aggiunto
	// due volte con lo stesso alias, generando un errore SQL "Not unique table".
	if ( false !== strpos( $clauses['join'], 'oe_featured_meta' ) ) {
		return $clauses;
	}

	global $wpdb;
	$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS oe_featured_meta ON ( {$wpdb->posts}.ID = oe_featured_meta.post_id AND oe_featured_meta.meta_key = '_tribe_featured' )";

	$featured_order = "(oe_featured_meta.meta_value = '1') DESC";
	$clauses['orderby'] = $clauses['orderby'] ? $featured_order . ', ' . $clauses['orderby'] : $featured_order;

	return $clauses;
}
add_filter( 'posts_clauses', 'open_events_pin_featured_events_clauses', 100, 2 );

/**
 * Restituisce l'HTML del badge "in primo piano" con il testo configurato
 * nell'opzione "Testo In Primo Piano". Stile inline (stessa grafica ambra del
 * badge nel portale) perche' il CSS del widget non e' caricato sulle pagine del
 * calendario pubblico di TEC.
 */
function open_events_featured_badge_html() {
	$label = function_exists( 'OpenEvents\\open_events_get_featured_label' )
		? \OpenEvents\open_events_get_featured_label()
		: '';

	if ( '' === trim( (string) $label ) ) {
		return '';
	}

	$style = 'display:inline-flex;align-items:center;gap:4px;margin-left:6px;padding:2px 8px;'
		. 'border-radius:20px;font-size:11px;font-weight:700;line-height:1.4;'
		. 'background-color:#fef3c7;color:#b45309;vertical-align:middle;';

	return '<span class="oe-public-featured-badge" style="' . esc_attr( $style ) . '">'
		. '<span aria-hidden="true">&#9733;</span> ' . esc_html( $label )
		. '</span>';
}

/**
 * Aggiunge l'etichetta "in primo piano" subito dopo il titolo dell'evento nelle
 * card del calendario pubblico di The Events Calendar (Views v2, incluse le
 * viste Pro). TEC di suo mostra solo un'icona con testo per screen-reader: qui
 * rendiamo il testo visibile a tutti i visitatori. Agganciato al filtro
 * `tribe_template_after_include_html:{template}` che passa l'HTML gia' generato
 * del titolo e l'istanza del template (da cui leggiamo l'evento e il suo
 * flag ->featured).
 */
function open_events_append_featured_label_to_title( $html, $file, $name, $template ) {
	if ( ! is_object( $template ) || ! method_exists( $template, 'get' ) ) {
		return $html;
	}

	$event = $template->get( 'event' );
	if ( empty( $event ) || empty( $event->featured ) ) {
		return $html;
	}

	return $html . open_events_featured_badge_html();
}

/**
 * Registra il badge su tutte le viste "a card/lista" del calendario pubblico
 * dove l'etichetta ha spazio per essere leggibile (list, day, photo, summary,
 * eventi passati recenti, widget lista, e i tooltip di month/week/map). Le
 * viste a griglia compatta (barre di month/week) sono escluse di proposito per
 * non rompere il layout. Un hook name non esistente viene semplicemente
 * ignorato, quindi la lista e' sicura anche se una vista non e' attiva.
 */
function open_events_register_featured_label_hooks() {
	$title_templates = [
		// The Events Calendar (core)
		'events/v2/list/event/title',
		'events/v2/day/event/title',
		'events/v2/latest-past/event/title',
		'events/v2/widgets/widget-events-list/event/title',
		'events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/title',
		// Events Calendar Pro
		'events-pro/v2/photo/event/title',
		'events-pro/v2/summary/date-group/event/title',
		'events-pro/v2/map/event-cards/event-card/event/title',
		'events-pro/v2/map/event-cards/event-card/tooltip/title',
		'events-pro/v2/week/grid-body/events-day/event/tooltip/title',
	];

	foreach ( $title_templates as $template_name ) {
		add_filter( "tribe_template_after_include_html:{$template_name}", 'open_events_append_featured_label_to_title', 10, 4 );
	}
}
add_action( 'init', 'open_events_register_featured_label_hooks' );

/**
 * Verifica che Elementor sia attivo prima di caricare il widget.
 * Senza Elementor la classe Widget_Base non esiste e il sito andrebbe in errore fatale.
 */
function open_events_is_elementor_active() {
	return did_action( 'elementor/loaded' );
}

function open_events_admin_notice_missing_elementor() {
	echo '<div class="notice notice-warning"><p>';
	esc_html_e( 'Open Events richiede il plugin Elementor attivo per funzionare.', 'open-events' );
	echo '</p></div>';
}

function open_events_register_category( $elements_manager ) {
	$elements_manager->add_category(
		'open-events',
		[
			'title' => esc_html__( 'Open Events', 'open-events' ),
			'icon'  => 'eicon-calendar',
		]
	);
}

function open_events_register_widgets( $widgets_manager ) {
	require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/events-manager/class-widget-events-manager.php';
	$widgets_manager->register( new \OpenEvents\Widget_Events_Manager() );

	require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/events-search/class-widget-events-search.php';
	$widgets_manager->register( new \OpenEvents\Widget_Events_Search() );
}

function open_events_register_assets() {
	wp_register_style(
		'open-events-manager-style',
		OPEN_EVENTS_PLUGIN_URL . 'assets/events-manager/events-manager.css',
		[],
		OPEN_EVENTS_VERSION
	);
	wp_register_script(
		'open-events-manager-script',
		OPEN_EVENTS_PLUGIN_URL . 'assets/events-manager/events-manager.js',
		[ 'jquery', 'elementor-frontend' ],
		OPEN_EVENTS_VERSION,
		true
	);
	wp_localize_script(
		'open-events-manager-script',
		'openEventsManager',
		[
			'allowPastDates' => \OpenEvents\open_events_is_past_dates_enabled(),
		]
	);

	// Ricerca Eventi: stile + script della barra di ricerca live.
	wp_register_style(
		'open-events-search-style',
		OPEN_EVENTS_PLUGIN_URL . 'assets/events-search/events-search.css',
		[],
		OPEN_EVENTS_VERSION
	);
	wp_register_script(
		'open-events-search-script',
		OPEN_EVENTS_PLUGIN_URL . 'assets/events-search/events-search.js',
		[ 'jquery', 'elementor-frontend' ],
		OPEN_EVENTS_VERSION,
		true
	);
	wp_localize_script(
		'open-events-search-script',
		'openEventsSearch',
		[
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'open_events_search' ),
		]
	);
}

function open_events_init() {
	if ( ! open_events_is_elementor_active() ) {
		add_action( 'admin_notices', 'open_events_admin_notice_missing_elementor' );
		return;
	}

	add_action( 'elementor/elements/categories_registered', 'open_events_register_category' );
	add_action( 'elementor/widgets/register', 'open_events_register_widgets' );
	add_action( 'wp_enqueue_scripts', 'open_events_register_assets' );
}
add_action( 'plugins_loaded', 'open_events_init' );
