<?php
/*
Plugin Name: Open Events
Plugin URI: https://github.com/BullXen/open-events
Description: Plugin per la gestione di eventi. Aggiunge a Elementor un widget che permette agli utenti loggati di gestire da front-end eventi, luoghi e organizzatori (The Events Calendar) come un portale.
Version: 1.0.7
Author: BullXen
GitHub Plugin URI: BullXen/open-events
Primary Branch: main
Text Domain: open-events
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OPEN_EVENTS_VERSION', '1.0.7' );
define( 'OPEN_EVENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPEN_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Richiesto sempre (non solo in admin): il widget front-end legge
// open_events_get_default_event_status() quando un utente salva un evento.
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/admin-settings.php';

/**
 * Porta in cima gli eventi "in primo piano" (_tribe_featured) anche nel
 * calendario pubblico di The Events Calendar, non solo nella dashboard
 * del portale. Aggiunge un LEFT JOIN + un criterio di ordinamento in
 * testa a quello già presente (non lo sostituisce), cosi' l'ordinamento
 * per data che usa TEC internamente resta intatto per gli eventi non
 * in primo piano. Agganciato a posts_clauses (non pre_get_posts) perché
 * altri filtri di TEC intervengono a quel livello e $query->set() da solo
 * non basta a garantire la precedenza. NON limitato a is_main_query(): la
 * Lista Eventi di TEC (Views v2) costruisce la propria query internamente
 * e non risulta la query principale della pagina, quindi quel controllo
 * la escludeva sempre (verificato: il filtro non aveva alcun effetto sul
 * front-end finché non è stato tolto).
 */
function open_events_pin_featured_events_clauses( $clauses, $query ) {
	if ( is_admin() ) {
		return $clauses;
	}

	$is_event_query = function_exists( 'tribe_is_event_query' )
		? tribe_is_event_query( $query )
		: ( 'tribe_events' === $query->get( 'post_type' ) );

	if ( ! $is_event_query ) {
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
	require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/class-widget-events-manager.php';
	$widgets_manager->register( new \OpenEvents\Widget_Events_Manager() );
}

function open_events_register_assets() {
	wp_register_style(
		'open-events-manager-style',
		OPEN_EVENTS_PLUGIN_URL . 'assets/css/events-manager.css',
		[],
		OPEN_EVENTS_VERSION
	);
	wp_register_script(
		'open-events-manager-script',
		OPEN_EVENTS_PLUGIN_URL . 'assets/js/events-manager.js',
		[ 'jquery', 'elementor-frontend' ],
		OPEN_EVENTS_VERSION,
		true
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
