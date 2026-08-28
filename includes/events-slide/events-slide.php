<?php
/**
 * Slide Eventi Consigliati — carousel "hero" con priorità agli eventi
 * Consigliati/in primo piano. Riusa deliberatamente la query e l'ordinamento
 * già scritti per la Ricerca Eventi (open_events_search_query()) invece di
 * duplicarli: stessa logica di "prossimi eventi" e stessa regola di boost
 * (vedi open_events_search_is_boosted() in includes/events-search/events-search.php),
 * qui serve solo un markup diverso (una slide grande invece di una griglia
 * di card).
 */

namespace OpenEvents;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ID evento da mostrare nello slider, in ordine di priorità (Consigliati/in
 * primo piano prima, poi data di inizio crescente — stessa regola della
 * Ricerca Eventi).
 *
 * @param int  $max_items     Numero massimo di slide.
 * @param bool $only_featured Se true, tiene solo gli eventi "boosted"
 *                             (Consigliati attivi o in primo piano manuale).
 */
function open_events_slide_get_events( $max_items, $only_featured = false ) {
	$ids = open_events_search_query(
		open_events_search_normalize_filters( [ 'date_mode' => 'upcoming', 'max_events' => $max_items ] )
	);

	if ( $only_featured ) {
		$ids = array_values( array_filter( $ids, __NAMESPACE__ . '\\open_events_search_is_boosted' ) );
	}

	return array_slice( $ids, 0, max( 1, (int) $max_items ) );
}

/**
 * Markup di una singola slide (immagine a piena larghezza, testo in overlay).
 */
function open_events_slide_render_item( $event_id ) {
	$permalink = get_permalink( $event_id );
	$title     = get_the_title( $event_id );
	$thumb     = get_the_post_thumbnail_url( $event_id, 'large' );

	$featured    = '1' === get_post_meta( $event_id, '_tribe_featured', true );
	$recommended = open_events_featured_is_active( $event_id );

	$date_label = '';
	$start      = get_post_meta( $event_id, '_EventStartDate', true );
	if ( $start ) {
		$all_day    = '1' === (string) get_post_meta( $event_id, '_EventAllDay', true );
		$start_ts   = strtotime( $start );
		$date_label = date_i18n( 'j F Y', $start_ts );
		if ( ! $all_day ) {
			$date_label .= ' · ' . date_i18n( 'H:i', $start_ts );
		}
	}

	$comune   = '';
	$venue_id = (int) get_post_meta( $event_id, '_EventVenueID', true );
	if ( $venue_id ) {
		$comune = get_post_meta( $venue_id, '_VenueCity', true );
	}

	$badge_label = $featured ? open_events_get_featured_label() : ( $recommended ? esc_html__( 'Consigliato', 'open-events' ) : '' );

	ob_start();
	?>
	<div class="oe-slide-item" role="group" aria-roledescription="slide">
		<a class="oe-slide-card" href="<?php echo esc_url( $permalink ); ?>">
			<?php if ( $thumb ) : ?>
				<img class="oe-slide-bg" src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
			<?php endif; ?>
			<span class="oe-slide-overlay" aria-hidden="true"></span>
			<div class="oe-slide-content">
				<?php if ( $badge_label ) : ?>
					<span class="oe-slide-badge">
						<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
						<?php echo esc_html( $badge_label ); ?>
					</span>
				<?php endif; ?>
				<h3 class="oe-slide-title"><?php echo esc_html( $title ); ?></h3>
				<?php if ( $date_label || $comune ) : ?>
					<div class="oe-slide-meta">
						<?php if ( $date_label ) : ?><span><?php echo esc_html( $date_label ); ?></span><?php endif; ?>
						<?php if ( $comune ) : ?><span><?php echo esc_html( $comune ); ?></span><?php endif; ?>
					</div>
				<?php endif; ?>
				<span class="oe-slide-cta"><?php esc_html_e( 'Scopri di più', 'open-events' ); ?> →</span>
			</div>
		</a>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Slider completo (o messaggio di fallback se non ci sono eventi da mostrare).
 */
function open_events_slide_render( array $atts = [] ) {
	$atts = wp_parse_args(
		$atts,
		[
			'max_items'      => 6,
			'only_featured'  => false,
			'autoplay'       => true,
			'autoplay_delay' => 5000,
			'loop'           => true,
			'fallback'       => '',
		]
	);

	$max_items = max( 1, min( 20, (int) $atts['max_items'] ) );
	$ids       = open_events_slide_get_events( $max_items, ! empty( $atts['only_featured'] ) );

	if ( empty( $ids ) ) {
		$fallback = trim( (string) $atts['fallback'] );
		if ( '' === $fallback ) {
			return '';
		}
		return '<div class="oe-slide-empty">' . esc_html( $fallback ) . '</div>';
	}

	$items = '';
	foreach ( $ids as $event_id ) {
		$items .= open_events_slide_render_item( $event_id );
	}

	$data = [
		'autoplay'      => ! empty( $atts['autoplay'] ),
		'autoplayDelay' => max( 2000, (int) $atts['autoplay_delay'] ),
		'loop'          => ! empty( $atts['loop'] ) && count( $ids ) > 1,
	];

	$show_controls = count( $ids ) > 1;

	ob_start();
	?>
	<div class="oe-slide-wrapper" data-settings="<?php echo esc_attr( wp_json_encode( $data ) ); ?>">
		<div class="oe-slide-track-wrap">
			<div class="oe-slide-track"><?php echo $items; // già sanificato in open_events_slide_render_item() ?></div>
		</div>
		<?php if ( $show_controls ) : ?>
			<button type="button" class="oe-slide-nav oe-slide-prev" aria-label="<?php esc_attr_e( 'Slide precedente', 'open-events' ); ?>">&#10094;</button>
			<button type="button" class="oe-slide-nav oe-slide-next" aria-label="<?php esc_attr_e( 'Slide successiva', 'open-events' ); ?>">&#10095;</button>
			<div class="oe-slide-dots" role="tablist" aria-label="<?php esc_attr_e( 'Vai alla slide', 'open-events' ); ?>">
				<?php foreach ( $ids as $i => $event_id ) : ?>
					<button type="button" class="oe-slide-dot<?php echo 0 === $i ? ' is-active' : ''; ?>" role="tab" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: numero slide */ __( 'Slide %d', 'open-events' ), $i + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode(
	'open_events_slide',
	function ( $raw_atts ) {
		$raw_atts = shortcode_atts(
			[
				'max_items'     => 6,
				'only_featured' => 'false',
			],
			$raw_atts,
			'open_events_slide'
		);

		return open_events_slide_render(
			[
				'max_items'     => intval( $raw_atts['max_items'] ),
				'only_featured' => filter_var( $raw_atts['only_featured'], FILTER_VALIDATE_BOOLEAN ),
			]
		);
	}
);

/**
 * Inserimento automatico in homepage (opzione "Slide eventi in homepage" in
 * Open Events → Impostazioni). Agganciato a the_content e non a wp_head/altro
 * perché deve finire dentro il flusso del tema; i guard su is_main_query()/
 * in_the_loop() evitano che scatti anche su widget, excerpt o loop secondari
 * che richiamano the_content sulla stessa pagina.
 */
function open_events_slide_maybe_prepend_home( $content ) {
	if ( ! is_front_page() || ! in_the_loop() || ! is_main_query() || is_admin() ) {
		return $content;
	}

	if ( ! open_events_is_slide_auto_home_enabled() ) {
		return $content;
	}

	remove_filter( 'the_content', __NAMESPACE__ . '\\open_events_slide_maybe_prepend_home' );

	return open_events_slide_render( [ 'max_items' => open_events_get_slide_home_max_items() ] ) . $content;
}
add_filter( 'the_content', __NAMESPACE__ . '\\open_events_slide_maybe_prepend_home' );

/**
 * Carica CSS/JS del carousel solo dove serve davvero: shortcode [open_events_slide]
 * nel contenuto del post corrente, o auto-inserimento in homepage. Il widget
 * Elementor non passa da qui — Elementor carica da solo gli asset dichiarati in
 * get_style_depends()/get_script_depends() quando il widget è effettivamente
 * in pagina (stesso meccanismo degli altri widget del plugin); qui serve solo
 * per i due casi che NON passano dal rendering di un widget Elementor.
 * Priorità 20 (dopo open_events_register_assets(), priorità default 10 in
 * open-events.php) cosi' gli handle 'open-events-slide-*' sono già registrati.
 */
function open_events_slide_maybe_enqueue_assets() {
	$needs_assets = open_events_is_slide_auto_home_enabled() && is_front_page();

	if ( ! $needs_assets ) {
		global $post;
		$needs_assets = ( $post instanceof \WP_Post ) && has_shortcode( $post->post_content, 'open_events_slide' );
	}

	if ( $needs_assets ) {
		wp_enqueue_style( 'open-events-slide-style' );
		wp_enqueue_script( 'open-events-slide-script' );
	}
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\open_events_slide_maybe_enqueue_assets', 20 );
