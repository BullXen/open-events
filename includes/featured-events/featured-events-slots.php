<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Salva la data che conta ai fini slot/settimana per un evento Consigliato:
 * _illi_featured_event_dates è l'array "storico" richiesto dalla spec
 * (singolo evento o prima occorrenza per un ricorrente); _illi_featured_first_date
 * è la stessa data ma come stringa semplice 'Y-m-d', per poterla interrogare
 * via SQL diretto senza fare LIKE su un meta_value serializzato (fragile).
 */
function open_events_featured_save_event_date( $post_id, $date ) {
	$date = substr( $date, 0, 10 );
	update_post_meta( $post_id, '_illi_featured_event_dates', [ $date ] );
	update_post_meta( $post_id, '_illi_featured_first_date', $date );
}

/**
 * Lunedì e domenica (settimana solare) che contengono $date, entrambi 'Y-m-d'.
 */
function open_events_featured_week_bounds( $date ) {
	$day = new \DateTime( substr( $date, 0, 10 ) );
	$weekday = (int) $day->format( 'N' ); // 1 (lunedì) .. 7 (domenica)
	$monday = ( clone $day )->modify( '-' . ( $weekday - 1 ) . ' days' );
	$sunday = ( clone $monday )->modify( '+6 days' );

	return [ $monday->format( 'Y-m-d' ), $sunday->format( 'Y-m-d' ) ];
}

/**
 * True se c'è ancora uno slot Consigliato libero nella settimana solare di
 * $date (0 = nessun limite). Query diretta $wpdb, non get_posts()/WP_Query:
 * The Events Calendar forza post_status a 'publish' a livello SQL su ogni
 * query tribe_events, stesso problema già risolto altrove nel plugin.
 */
function open_events_featured_slot_available_for_date( $date, $exclude_post_id = 0 ) {
	$settings = open_events_get_featured_events_settings();
	$max = absint( $settings['max_slots_per_week'] );
	if ( 0 === $max ) {
		return true;
	}

	[ $week_start, $week_end ] = open_events_featured_week_bounds( $date );

	global $wpdb;
	$sql = "SELECT COUNT(*) FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} status_meta ON status_meta.post_id = p.ID AND status_meta.meta_key = '_illi_featured_status' AND status_meta.meta_value = 'paid'
		INNER JOIN {$wpdb->postmeta} date_meta ON date_meta.post_id = p.ID AND date_meta.meta_key = '_illi_featured_first_date'
		WHERE p.post_type = 'tribe_events' AND p.post_status != 'trash' AND date_meta.meta_value BETWEEN %s AND %s AND p.ID != %d";

	$count = (int) $wpdb->get_var( $wpdb->prepare( $sql, $week_start, $week_end, $exclude_post_id ) );

	return $count < $max;
}

/**
 * Badge "nuovi" per la sezione admin Consigliati, stesso pattern di
 * hub_new_count() in class-widget-events-manager.php (stessa convenzione
 * di meta '_oe_hub_seen_{chiave}' e prima-visita-mai-conta-come-nuovo), ma
 * qui su un meta invece che su un post_type — conta i pagamenti confermati
 * (_illi_featured_paid_at) da quando l'admin ha aperto l'ultima volta la
 * sezione. Usa il fuso orario locale del sito ovunque (current_time()),
 * coerente col formato con cui _illi_featured_paid_at viene salvato nel
 * webhook (anch'esso current_time('mysql'), non GMT).
 */
function open_events_featured_hub_new_count( $current_user_id ) {
	$meta_key = '_oe_hub_seen_consigliati';
	$last_seen = get_user_meta( $current_user_id, $meta_key, true );

	if ( '' === $last_seen ) {
		update_user_meta( $current_user_id, $meta_key, current_time( 'timestamp' ) );
		return 0;
	}

	global $wpdb;
	$sql = $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_illi_featured_paid_at'
		WHERE p.post_type = 'tribe_events' AND p.post_status != 'trash' AND pm.meta_value > %s",
		date( 'Y-m-d H:i:s', (int) $last_seen )
	);

	return (int) $wpdb->get_var( $sql );
}

/**
 * Un evento pagato resta "Consigliato" (badge in "I Miei Eventi") solo fino
 * alla fine della settimana solare promossa — coerente con lo slot a
 * pagamento settimanale, non uno stato 'paid' permanente. Non tocca il meta
 * (resta storicizzato); è solo un check di visualizzazione.
 */
function open_events_featured_is_active( $post_id ) {
	if ( 'paid' !== get_post_meta( $post_id, '_illi_featured_status', true ) ) {
		return false;
	}

	$date = get_post_meta( $post_id, '_illi_featured_first_date', true );
	if ( ! $date ) {
		return true;
	}

	[ , $week_end ] = open_events_featured_week_bounds( $date );
	return current_time( 'Y-m-d' ) <= $week_end;
}

/**
 * Il boost pubblico "in primo piano" (badge + priorità di ordinamento nel
 * calendario pubblico di TEC, vedi open_events_pin_featured_events_clauses()
 * in open-events.php) per un evento Consigliato scatta solo negli ultimi 7
 * giorni prima dell'inizio evento, anche se il pagamento è avvenuto molto
 * prima — a differenza del badge nella dashboard "I Miei Eventi"
 * (open_events_featured_is_active(), sopra), visibile da subito dopo il
 * pagamento fino a fine settimana promossa. Sono due finestre temporali
 * diverse di proposito.
 */
function open_events_featured_is_public_active( $post_id ) {
	if ( 'paid' !== get_post_meta( $post_id, '_illi_featured_status', true ) ) {
		return false;
	}

	$start = get_post_meta( $post_id, '_EventStartDate', true );
	if ( ! $start ) {
		return false;
	}

	$start_ts = strtotime( $start );
	$now_ts   = current_time( 'timestamp' );

	return $start_ts >= $now_ts && $start_ts <= strtotime( '+7 days', $now_ts );
}

function open_events_featured_hub_mark_seen( $current_user_id ) {
	update_user_meta( $current_user_id, '_oe_hub_seen_consigliati', current_time( 'timestamp' ) );
}
