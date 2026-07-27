<?php
/**
 * Ricerca Eventi — logica condivisa fra il widget (rendering iniziale) e
 * l'handler AJAX (aggiornamento live dei risultati). Tenuta separata dalla
 * classe del widget cosi' entrambe usano la stessa query e lo stesso markup
 * delle card, senza duplicazioni.
 */

namespace OpenEvents;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Numero massimo di card mostrate in un colpo solo. Cap prudente: la ricerca
 * e' pensata per un portale locale (pochi comuni), non per cataloghi enormi.
 */
const SEARCH_MAX_RESULTS = 48;

/**
 * Categorie evento (tassonomia di The Events Calendar) disponibili per il
 * menu a tendina. Nasconde le categorie vuote per non offrire filtri che
 * darebbero sempre zero risultati.
 */
function open_events_search_get_categories() {
	if ( ! taxonomy_exists( 'tribe_events_cat' ) ) {
		return [];
	}

	$terms = get_terms(
		[
			'taxonomy'   => 'tribe_events_cat',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		]
	);

	return is_wp_error( $terms ) ? [] : $terms;
}

/**
 * Normalizza i filtri grezzi (da $_POST o dai default del widget) in una
 * struttura sicura e prevedibile. Ogni valore e' gia' sanificato qui, cosi'
 * chi chiama non deve ripensarci.
 */
function open_events_search_normalize_filters( array $raw ) {
	$text   = isset( $raw['text'] ) ? sanitize_text_field( wp_unslash( $raw['text'] ) ) : '';
	$comune = isset( $raw['comune'] ) ? sanitize_text_field( wp_unslash( $raw['comune'] ) ) : '';
	$cat    = isset( $raw['category'] ) ? intval( $raw['category'] ) : 0;

	$date_mode = isset( $raw['date_mode'] ) ? sanitize_key( $raw['date_mode'] ) : 'upcoming';
	if ( ! in_array( $date_mode, [ 'upcoming', 'today', 'week', 'day' ], true ) ) {
		$date_mode = 'upcoming';
	}

	// La data singola vale solo in modalita' 'day' e deve essere un Y-m-d reale.
	$date = '';
	if ( 'day' === $date_mode ) {
		$candidate = isset( $raw['date'] ) ? sanitize_text_field( wp_unslash( $raw['date'] ) ) : '';
		$parsed    = \DateTime::createFromFormat( 'Y-m-d', $candidate );
		if ( $parsed && $parsed->format( 'Y-m-d' ) === $candidate ) {
			$date = $candidate;
		} else {
			$date_mode = 'upcoming';
		}
	}

	return [
		'text'      => $text,
		'comune'    => $comune,
		'category'  => $cat,
		'date_mode' => $date_mode,
		'date'      => $date,
	];
}

/**
 * Traduce la modalita' data nei due estremi [inizio, fine] usati per capire
 * se un evento cade nella finestra. Ritorna 'end' = null per "prossimi
 * eventi" (nessun limite superiore). Le date sono in ora locale del sito,
 * come i meta _EventStartDate/_EventEndDate scritti da The Events Calendar.
 */
function open_events_search_date_range( $date_mode, $date ) {
	$now_ts = current_time( 'timestamp' );
	$today  = date( 'Y-m-d', $now_ts );

	switch ( $date_mode ) {
		case 'today':
			return [ $today . ' 00:00:00', $today . ' 23:59:59' ];

		case 'week':
			// Da oggi fino a domenica della settimana corrente (N: 1=lun..7=dom).
			$days_to_sunday = 7 - (int) date( 'N', $now_ts );
			$sunday         = date( 'Y-m-d', $now_ts + ( $days_to_sunday * DAY_IN_SECONDS ) );
			return [ $today . ' 00:00:00', $sunday . ' 23:59:59' ];

		case 'day':
			return [ $date . ' 00:00:00', $date . ' 23:59:59' ];

		case 'upcoming':
		default:
			return [ current_time( 'mysql' ), null ];
	}
}

/**
 * Esegue la ricerca e ritorna gli ID evento gia' ordinati: prima gli eventi
 * "in primo piano", poi per data di inizio crescente. L'ordinamento e' fatto
 * in PHP di proposito — durante una richiesta admin-ajax is_admin() e' true,
 * quindi il filtro che fissa i featured in cima al calendario pubblico non
 * scatta, e non possiamo affidarci ad esso qui.
 */
function open_events_search_query( array $filters ) {
	list( $range_start, $range_end ) = open_events_search_date_range( $filters['date_mode'], $filters['date'] );

	// Un evento cade nella finestra se inizia entro la fine dell'intervallo e
	// finisce dopo l'inizio (sovrapposizione). Le date TEC sono stringhe
	// 'Y-m-d H:i:s', confrontabili direttamente come DATETIME.
	$meta_query = [ 'relation' => 'AND' ];

	$meta_query['end_clause'] = [
		'key'     => '_EventEndDate',
		'value'   => $range_start,
		'compare' => '>=',
		'type'    => 'DATETIME',
	];

	if ( null !== $range_end ) {
		$meta_query['start_clause'] = [
			'key'     => '_EventStartDate',
			'value'   => $range_end,
			'compare' => '<=',
			'type'    => 'DATETIME',
		];
	}

	// Filtro comune: gli eventi non hanno la citta' direttamente, ce l'ha il
	// luogo (_VenueCity). Trovo prima i luoghi del comune, poi limito agli
	// eventi che puntano a uno di quei luoghi via _EventVenueID.
	if ( '' !== $filters['comune'] ) {
		$venue_ids = get_posts(
			[
				'post_type'      => 'tribe_venue',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_VenueCity',
				'meta_value'     => $filters['comune'],
			]
		);

		if ( empty( $venue_ids ) ) {
			return [];
		}

		$meta_query['venue_clause'] = [
			'key'     => '_EventVenueID',
			'value'   => $venue_ids,
			'compare' => 'IN',
		];
	}

	$args = [
		'post_type'        => 'tribe_events',
		'post_status'      => 'publish',
		'posts_per_page'   => 200,
		'fields'           => 'ids',
		'no_found_rows'    => true,
		'suppress_filters' => false,
		'meta_query'       => $meta_query,
	];

	if ( '' !== $filters['text'] ) {
		$args['s'] = $filters['text'];
	}

	if ( $filters['category'] > 0 ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'tribe_events_cat',
				'field'    => 'term_id',
				'terms'    => $filters['category'],
			],
		];
	}

	$query = new \WP_Query( $args );
	$ids   = $query->posts;

	if ( empty( $ids ) ) {
		return [];
	}

	// Ordina: featured prima, poi data inizio crescente.
	usort(
		$ids,
		function ( $a, $b ) {
			$a_featured = '1' === get_post_meta( $a, '_tribe_featured', true ) ? 1 : 0;
			$b_featured = '1' === get_post_meta( $b, '_tribe_featured', true ) ? 1 : 0;
			if ( $a_featured !== $b_featured ) {
				return $b_featured - $a_featured;
			}
			$a_start = strtotime( (string) get_post_meta( $a, '_EventStartDate', true ) );
			$b_start = strtotime( (string) get_post_meta( $b, '_EventStartDate', true ) );
			return $a_start - $b_start;
		}
	);

	return array_slice( $ids, 0, SEARCH_MAX_RESULTS );
}

/**
 * Markup di una singola card evento. Grafica nostra (classi oes-*) per avere
 * pieno controllo estetico, indipendente dal tema TEC.
 */
function open_events_search_render_card( $event_id ) {
	$permalink = get_permalink( $event_id );
	$title     = get_the_title( $event_id );
	$thumb     = get_the_post_thumbnail_url( $event_id, 'medium_large' );
	$featured  = '1' === get_post_meta( $event_id, '_tribe_featured', true );

	$start = get_post_meta( $event_id, '_EventStartDate', true );
	$all_day = '1' === (string) get_post_meta( $event_id, '_EventAllDay', true );
	$date_display = '';
	if ( $start ) {
		$start_ts = strtotime( $start );
		$date_display = $all_day
			? date_i18n( 'D j M', $start_ts )
			: date_i18n( 'D j M · H:i', $start_ts );
	}

	// Comune dal luogo collegato.
	$comune   = '';
	$venue_id = (int) get_post_meta( $event_id, '_EventVenueID', true );
	if ( $venue_id ) {
		$comune = get_post_meta( $venue_id, '_VenueCity', true );
	}

	// Prima categoria come badge.
	$cat_name = '';
	$terms    = get_the_terms( $event_id, 'tribe_events_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$cat_name = $terms[0]->name;
	}

	$featured_label = open_events_get_featured_label();

	ob_start();
	?>
	<a class="oes-card<?php echo $featured ? ' is-featured' : ''; ?>" href="<?php echo esc_url( $permalink ); ?>">
		<?php if ( $thumb ) : ?>
			<img class="oes-card-bg" src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
		<?php else : ?>
			<span class="oes-card-bg oes-card-bg-empty" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
			</span>
		<?php endif; ?>

		<div class="oes-card-badges">
			<?php if ( $cat_name ) : ?>
				<span class="oes-card-cat"><?php echo esc_html( $cat_name ); ?></span>
			<?php endif; ?>
			<?php if ( $featured ) : ?>
				<span class="oes-card-featured">
					<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
					<?php echo esc_html( $featured_label ); ?>
				</span>
			<?php endif; ?>
		</div>

		<div class="oes-card-body">
			<?php if ( $date_display ) : ?>
				<span class="oes-card-date"><?php echo esc_html( $date_display ); ?></span>
			<?php endif; ?>
			<h3 class="oes-card-title"><?php echo esc_html( $title ); ?></h3>
			<?php if ( $comune ) : ?>
				<span class="oes-card-place">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 5.5-8 12-8 12s-8-6.5-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<?php echo esc_html( $comune ); ?>
				</span>
			<?php endif; ?>
		</div>
	</a>
	<?php
	return ob_get_clean();
}

/**
 * Griglia completa dei risultati (o stato vuoto). Ritorna una stringa HTML,
 * usata sia al primo caricamento sia nella risposta AJAX.
 */
function open_events_search_render_results( array $filters ) {
	$ids = open_events_search_query( $filters );

	if ( empty( $ids ) ) {
		return '<div class="oes-empty">'
			. '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>'
			. '<p>' . esc_html__( 'Nessun evento trovato con questi filtri.', 'open-events' ) . '</p>'
			. '</div>';
	}

	$cards = '';
	foreach ( $ids as $id ) {
		$cards .= open_events_search_render_card( $id );
	}

	return '<div class="oes-grid">' . $cards . '</div>';
}

/**
 * Handler AJAX (utenti loggati e non). Verifica il nonce, normalizza i filtri
 * e restituisce l'HTML dei risultati piu' il conteggio.
 */
function open_events_search_ajax_handler() {
	check_ajax_referer( 'open_events_search', 'nonce' );

	$filters = open_events_search_normalize_filters( $_POST );
	$ids     = open_events_search_query( $filters );

	$cards = '';
	foreach ( $ids as $id ) {
		$cards .= open_events_search_render_card( $id );
	}

	if ( empty( $ids ) ) {
		$html = '<div class="oes-empty">'
			. '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>'
			. '<p>' . esc_html__( 'Nessun evento trovato con questi filtri.', 'open-events' ) . '</p>'
			. '</div>';
	} else {
		$html = '<div class="oes-grid">' . $cards . '</div>';
	}

	wp_send_json_success(
		[
			'html'  => $html,
			'count' => count( $ids ),
		]
	);
}
add_action( 'wp_ajax_open_events_search', __NAMESPACE__ . '\\open_events_search_ajax_handler' );
add_action( 'wp_ajax_nopriv_open_events_search', __NAMESPACE__ . '\\open_events_search_ajax_handler' );
