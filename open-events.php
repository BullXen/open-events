<?php
/*
Plugin Name: Open Events
Plugin URI: https://github.com/BullXen/open-events
Description: Widget Elementor che trasforma una pagina in un portale front-end per gestire eventi, luoghi e organizzatori di The Events Calendar.
Version: 1.10.1
Author: BullXen
Requires Plugins: elementor, the-events-calendar
Requires at least: 5.8
Requires PHP: 7.4
GitHub Plugin URI: BullXen/open-events
Primary Branch: main
Text Domain: open-events
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OPEN_EVENTS_VERSION', '1.10.1' );
define( 'OPEN_EVENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPEN_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Richiesto sempre (non solo in admin): il widget front-end legge
// open_events_get_default_event_status() quando un utente salva un evento.
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/core/admin-settings.php';

// Richiesto sempre: registra gli handler AJAX della Ricerca Eventi, che
// vengono serviti da admin-ajax.php (dove il widget Elementor non è caricato).
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/events-search/events-search.php';

// Richiesto sempre (non solo in admin): la Slide Eventi registra lo shortcode
// [open_events_slide] e l'auto-inserimento in homepage, entrambi utilizzabili
// a prescindere da dove/se Elementor carica il widget sulla pagina.
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/events-slide/events-slide.php';

/**
 * La cache "Elementi" di Elementor (Impostazioni → Performance) mette in
 * cache l'HTML dell'intera pagina fino a 24h, IDENTICO per ogni visitatore,
 * senza distinguere loggato/anonimo. Sulle pagine coi widget Community Auth
 * o Front-end Events Manager (contenuto diverso per ogni utente) questo fa
 * sembrare che il login "non tenga la sessione": in realtà il cookie è
 * valido, è la pagina che serve uno stato congelato a chi ce l'ha già
 * generato per primo. Bypassiamo la cache SOLO per i visitatori loggati:
 * chi non ha effettuato l'accesso continua a beneficiarne normalmente.
 */
add_filter( 'pre_option_elementor_element_cache_ttl', function( $value ) {
	return is_user_logged_in() ? 'disable' : $value;
} );

// Richiesto sempre (non solo in admin): i filtri login_url/register_url/
// login_redirect/authenticate e gli handler admin-post.php di Community
// devono essere attivi su ogni pagina del sito, non solo dove c'è il widget.
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/community/community-settings.php';
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/community/community-auth.php';
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/community/community-oauth.php';
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/community/community-emails.php';

// Richiesto sempre: il checkbox "Consigliato" nel form evento, il webhook
// REST di Stripe e la pagina di ritorno dal Checkout devono funzionare su
// ogni pagina del sito, non solo dove Elementor carica il widget.
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/featured-events/featured-events-settings.php';
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/featured-events/featured-events-slots.php';
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/featured-events/featured-events-stripe.php';
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/featured-events/featured-events-webhook.php';

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
 *
 * Stesso boost anche per un evento "Consigliato" (pagamento Stripe andato a
 * buon fine, _illi_featured_status = 'paid'), ma SOLO negli ultimi 7 giorni
 * prima dell'inizio evento — non da subito dopo il pagamento, che può
 * avvenire settimane prima (vedi open_events_featured_is_public_active() in
 * includes/featured-events/featured-events-slots.php per lo stesso check
 * lato PHP, usato per il badge).
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
	// principale + conteggio): senza questo guard i LEFT JOIN verrebbero aggiunti
	// due volte con lo stesso alias, generando un errore SQL "Not unique table".
	if ( false !== strpos( $clauses['join'], 'oe_featured_meta' ) ) {
		return $clauses;
	}

	global $wpdb;
	$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS oe_featured_meta ON ( {$wpdb->posts}.ID = oe_featured_meta.post_id AND oe_featured_meta.meta_key = '_tribe_featured' )";
	$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS oe_recommended_status ON ( {$wpdb->posts}.ID = oe_recommended_status.post_id AND oe_recommended_status.meta_key = '_illi_featured_status' )";
	$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS oe_recommended_start ON ( {$wpdb->posts}.ID = oe_recommended_start.post_id AND oe_recommended_start.meta_key = '_EventStartDate' )";

	$now        = current_time( 'mysql' );
	$window_end = date( 'Y-m-d H:i:s', strtotime( $now . ' +7 days' ) );

	$recommended_window = $wpdb->prepare(
		"(oe_recommended_status.meta_value = 'paid' AND oe_recommended_start.meta_value BETWEEN %s AND %s)",
		$now,
		$window_end
	);

	$featured_order = "(oe_featured_meta.meta_value = '1' OR {$recommended_window}) DESC";
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
		. 'border-radius:20px;font-size:11px;font-weight:700;line-height:1.4;text-transform:uppercase;'
		. 'background-color:#fef3c7;color:#b45309;vertical-align:middle;';

	return '<span class="oe-public-featured-badge" style="' . esc_attr( $style ) . '">'
		. '<span aria-hidden="true">&#9733;</span> ' . esc_html( $label )
		. '</span>';
}

/**
 * Badge pubblico "Consigliato", stessa grafica del badge "in primo piano" ma
 * col colore configurato in Open Events → Eventi Consigliati. Visibile da
 * subito dopo il pagamento (open_events_featured_is_active(), stessa regola
 * del badge nella dashboard "I Miei Eventi") fino a fine settimana promossa —
 * a differenza della PRIORITÀ DI ORDINAMENTO (vedi
 * open_events_pin_featured_events_clauses() sopra), che invece scatta solo
 * negli ultimi 7 giorni prima dell'inizio evento. Sono due cose diverse di
 * proposito: il badge premia subito chi ha pagato, il boost in cima
 * all'elenco arriva solo quando l'evento è imminente.
 */
function open_events_recommended_badge_html() {
	$color = '#b45309';
	if ( function_exists( 'OpenEvents\\open_events_get_featured_events_settings' ) ) {
		$settings = \OpenEvents\open_events_get_featured_events_settings();
		if ( ! empty( $settings['featured_badge_color'] ) ) {
			$color = $settings['featured_badge_color'];
		}
	}

	$style = 'display:inline-flex;align-items:center;gap:4px;margin-left:6px;padding:2px 8px;'
		. 'border-radius:20px;font-size:11px;font-weight:700;line-height:1.4;text-transform:uppercase;'
		. 'background-color:#fef3c7;color:' . $color . ';vertical-align:middle;';

	return '<span class="oe-public-recommended-badge" style="' . esc_attr( $style ) . '">'
		. '<span aria-hidden="true">&#9733;</span> ' . esc_html__( 'Consigliato', 'open-events' )
		. '</span>';
}

/**
 * Aggiunge l'etichetta "in primo piano"/"Consigliato" subito dopo il titolo
 * dell'evento nelle card del calendario pubblico di The Events Calendar
 * (Views v2, incluse le viste Pro). TEC di suo mostra solo un'icona con testo
 * per screen-reader: qui rendiamo il testo visibile a tutti i visitatori.
 * Agganciato al filtro `tribe_template_after_include_html:{template}` che
 * passa l'HTML gia' generato del titolo e l'istanza del template (da cui
 * leggiamo l'evento, il suo flag ->featured e, per "Consigliato", il post ID).
 */
function open_events_append_featured_label_to_title( $html, $file, $name, $template ) {
	if ( ! is_object( $template ) || ! method_exists( $template, 'get' ) ) {
		return $html;
	}

	$event = $template->get( 'event' );
	if ( empty( $event ) || empty( $event->ID ) ) {
		return $html;
	}

	if ( ! empty( $event->featured ) ) {
		$html .= open_events_featured_badge_html();
	}

	if ( function_exists( 'OpenEvents\\open_events_featured_is_active' ) && \OpenEvents\open_events_featured_is_active( $event->ID ) ) {
		$html .= open_events_recommended_badge_html();
	}

	return $html;
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

/**
 * A differenza di Elementor (senza cui Widget_Base non esiste e il sito
 * andrebbe in errore fatale), il plugin non ha bisogno di bloccarsi senza
 * The Events Calendar: i CPT tribe_events/tribe_venue/tribe_organizer sono
 * referenziati solo per stringa (query, post_type), quindi senza TEC il
 * portale resta semplicemente vuoto invece di generare un fatal error.
 * Questo controllo serve solo ad avvisare l'admin, non a bloccare nulla.
 */
function open_events_is_tec_active() {
	return class_exists( 'Tribe__Events__Main' );
}

function open_events_admin_notice_missing_tec() {
	echo '<div class="notice notice-warning"><p>';
	esc_html_e( 'Open Events richiede il plugin "The Events Calendar" attivo: senza, il portale front-end non ha eventi/luoghi/organizzatori su cui lavorare.', 'open-events' );
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

	require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/community/class-widget-community-auth.php';
	$widgets_manager->register( new \OpenEvents\Widget_Community_Auth() );

	require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/events-slide/class-widget-events-slide.php';
	$widgets_manager->register( new \OpenEvents\Widget_Events_Slide() );
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

	// Slide Eventi Consigliati: stile + script del carousel. Registrati sempre
	// (non solo se il widget è in pagina) perché servono anche a shortcode e
	// auto-inserimento in homepage, che non passano dal caricamento asset di
	// Elementor legato al widget.
	wp_register_style(
		'open-events-slide-style',
		OPEN_EVENTS_PLUGIN_URL . 'assets/events-slide/events-slide.css',
		[],
		OPEN_EVENTS_VERSION
	);
	wp_register_script(
		'open-events-slide-script',
		OPEN_EVENTS_PLUGIN_URL . 'assets/events-slide/events-slide.js',
		[ 'jquery' ],
		OPEN_EVENTS_VERSION,
		true
	);

	// Community Auth: stile + script del widget Accedi/Registrati.
	wp_register_style(
		'open-events-community-style',
		OPEN_EVENTS_PLUGIN_URL . 'assets/community/community.css',
		[],
		OPEN_EVENTS_VERSION
	);
	wp_register_script(
		'open-events-community-script',
		OPEN_EVENTS_PLUGIN_URL . 'assets/community/community.js',
		[],
		OPEN_EVENTS_VERSION,
		true
	);
}

function open_events_init() {
	if ( ! open_events_is_tec_active() ) {
		add_action( 'admin_notices', 'open_events_admin_notice_missing_tec' );
	}

	if ( ! open_events_is_elementor_active() ) {
		add_action( 'admin_notices', 'open_events_admin_notice_missing_elementor' );
		return;
	}

	add_action( 'elementor/elements/categories_registered', 'open_events_register_category' );
	add_action( 'elementor/widgets/register', 'open_events_register_widgets' );
	add_action( 'wp_enqueue_scripts', 'open_events_register_assets' );
}
add_action( 'plugins_loaded', 'open_events_init' );
