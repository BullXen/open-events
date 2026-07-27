<?php
namespace OpenEvents;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget "Ricerca Eventi": barra di ricerca moderna (testo, comune, categoria,
 * date rapide) con griglia risultati aggiornata live via AJAX. Sostituisce a
 * livello grafico/funzionale la tribe-bar nativa di The Events Calendar.
 */
class Widget_Events_Search extends \Elementor\Widget_Base {

	public function get_name() {
		return 'open_events_search';
	}

	public function get_title() {
		return esc_html__( 'Ricerca Eventi', 'open-events' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return [ 'open-events', 'general' ];
	}

	public function get_style_depends() {
		return [ 'open-events-search-style' ];
	}

	public function get_script_depends() {
		return [ 'open-events-search-script' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_config',
			[
				'label' => esc_html__( 'Configurazione', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_text',
			[
				'label'        => esc_html__( 'Mostra campo di ricerca testo', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'text_placeholder',
			[
				'label'     => esc_html__( 'Testo segnaposto ricerca', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Cerca un evento…', 'open-events' ),
				'condition' => [ 'show_text' => 'yes' ],
			]
		);

		$this->end_controls_section();

		// --- Stile: Barra di Ricerca ---
		$this->start_controls_section(
			'section_style_bar',
			[
				'label' => esc_html__( 'Barra di Ricerca', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label'     => esc_html__( 'Colore principale (accent)', 'open-events' ),
				'description' => esc_html__( 'Usato per chip attivo, badge categoria e focus dei campi.', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-search' => '--oes-accent: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'bar_bg_color',
			[
				'label'     => esc_html__( 'Sfondo barra', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .oes-search-bar' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'bar_border_radius',
			[
				'label'      => esc_html__( 'Angoli arrotondati barra', 'open-events' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 14, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .oes-search-bar' => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .oes-input'       => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- Stile: Schede Evento ---
		$this->start_controls_section(
			'section_style_cards',
			[
				'label' => esc_html__( 'Schede Evento', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'     => esc_html__( 'Colonne griglia', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '3',
				'options'   => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				],
				'selectors' => [
					'{{WRAPPER}} .oes-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				],
			]
		);

		$this->add_responsive_control(
			'cards_gap',
			[
				'label'      => esc_html__( 'Spazio tra schede', 'open-events' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 5, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .oes-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'card_aspect_ratio',
			[
				'label'     => esc_html__( 'Proporzioni scheda', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '3/4',
				'options'   => [
					'3/4'  => esc_html__( 'Verticale (3:4)', 'open-events' ),
					'1/1'  => esc_html__( 'Quadrata (1:1)', 'open-events' ),
					'4/3'  => esc_html__( 'Orizzontale (4:3)', 'open-events' ),
					'16/9' => esc_html__( 'Panoramica (16:9)', 'open-events' ),
				],
				'selectors' => [
					'{{WRAPPER}} .oes-card' => 'aspect-ratio: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Angoli arrotondati schede', 'open-events' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 32 ] ],
				'default'    => [ 'size' => 14, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .oes-card' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings    = $this->get_settings_for_display();
		$show_text   = ( 'yes' === ( $settings['show_text'] ?? 'yes' ) );
		$placeholder = $settings['text_placeholder'] ?? esc_html__( 'Cerca un evento…', 'open-events' );

		$cities     = open_events_get_available_cities();
		$categories = open_events_search_get_categories();

		// Risultati iniziali (prossimi eventi) resi lato server: la barra
		// funziona anche senza JavaScript e non parte da vuota.
		$initial_html = open_events_search_render_results(
			open_events_search_normalize_filters( [ 'date_mode' => 'upcoming' ] )
		);
		?>
		<div class="oes-search">
			<form class="oes-search-bar" role="search" onsubmit="return false;">
				<?php if ( $show_text ) : ?>
					<div class="oes-field oes-field-text">
						<svg class="oes-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
						<input type="search" class="oes-input oes-input-text" name="text" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off">
					</div>
				<?php endif; ?>

				<div class="oes-field">
					<svg class="oes-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 5.5-8 12-8 12s-8-6.5-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<select class="oes-input oes-input-comune" name="comune" aria-label="<?php esc_attr_e( 'Comune', 'open-events' ); ?>">
						<option value=""><?php esc_html_e( 'Tutti i Comuni', 'open-events' ); ?></option>
						<?php foreach ( $cities as $city ) : ?>
							<option value="<?php echo esc_attr( $city ); ?>"><?php echo esc_html( $city ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="oes-field">
					<svg class="oes-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
					<select class="oes-input oes-input-category" name="category" aria-label="<?php esc_attr_e( 'Categoria', 'open-events' ); ?>">
						<option value="0"><?php esc_html_e( 'Tutte le categorie', 'open-events' ); ?></option>
						<?php foreach ( $categories as $term ) : ?>
							<option value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="oes-field oes-field-date">
					<svg class="oes-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
					<input type="date" class="oes-input oes-input-date" name="date" aria-label="<?php esc_attr_e( 'Data', 'open-events' ); ?>">
				</div>
			</form>

			<div class="oes-quick" role="group" aria-label="<?php esc_attr_e( 'Filtri rapidi data', 'open-events' ); ?>">
				<button type="button" class="oes-chip is-active" data-date-mode="upcoming"><?php esc_html_e( 'Prossimi', 'open-events' ); ?></button>
				<button type="button" class="oes-chip" data-date-mode="today"><?php esc_html_e( 'Oggi', 'open-events' ); ?></button>
				<button type="button" class="oes-chip" data-date-mode="week"><?php esc_html_e( 'Questa settimana', 'open-events' ); ?></button>
				<button type="button" class="oes-reset" hidden><?php esc_html_e( 'Azzera filtri', 'open-events' ); ?></button>
			</div>

			<div class="oes-results" aria-live="polite" data-loading="<?php esc_attr_e( 'Caricamento…', 'open-events' ); ?>">
				<?php echo $initial_html; // già sanificato in open_events_search_render_results() ?>
			</div>
		</div>
		<?php
	}
}
