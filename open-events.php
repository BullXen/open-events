<?php
/**
 * Plugin Name:       Open Events
 * Plugin URI:         https://example.com/open-events
 * Description:        Plugin di esempio che stampa "Plugin Attivo" nei log e tramite shortcode.
 * Version:            1.0.0
 * Requires at least:  5.8
 * Requires PHP:       7.4
 * Author:             Michel
 * Author URI:         https://example.com
 * License:             GPL v2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         open-events
 */

// Impedisce l'accesso diretto al file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scrive "Plugin Attivo" nel log di WordPress (debug.log) se WP_DEBUG_LOG è attivo.
 */
function open_events_log_attivo() {
	if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( 'Plugin Attivo' );
	}
}
add_action( 'plugins_loaded', 'open_events_log_attivo' );

/**
 * Shortcode [open_events_attivo] che stampa "Plugin Attivo".
 * Uso: [open_events_attivo]
 */
function open_events_shortcode_attivo() {
	return esc_html__( 'Plugin Attivo', 'open-events' );
}
add_shortcode( 'open_events_attivo', 'open_events_shortcode_attivo' );
