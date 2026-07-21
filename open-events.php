<?php
/*
Plugin Name: Open Events
Plugin URI: https://github.com/BullXen/open-events
Description: Plugin per la gestione di eventi. Aggiunge a Elementor un widget che permette agli utenti loggati di gestire da front-end eventi, luoghi e organizzatori (The Events Calendar) come un portale.
Version: 1.0.3
Author: BullXen
GitHub Plugin URI: BullXen/open-events
Primary Branch: main
Text Domain: open-events
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OPEN_EVENTS_VERSION', '1.0.3' );
define( 'OPEN_EVENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPEN_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Richiesto sempre (non solo in admin): il widget front-end legge
// open_events_get_default_event_status() quando un utente salva un evento.
require_once OPEN_EVENTS_PLUGIN_DIR . 'includes/admin-settings.php';

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
