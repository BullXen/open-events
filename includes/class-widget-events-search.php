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

		// =====================================================================
		// TAB CONTENUTO — Sezione: Barra di Ricerca
		// =====================================================================
		$this->start_controls_section(
			'section_search_bar',
			[
				'label' => esc_html__( 'Barra di Ricerca', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_bar',
			[
				'label'        => esc_html__( 'Mostra barra di ricerca', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'show_text',
			[
				'label'        => esc_html__( 'Mostra campo ricerca testo', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'condition'    => [ 'show_bar' => 'yes' ],
			]
		);

		$this->add_control(
			'text_placeholder',
			[
				'label'     => esc_html__( 'Testo segnaposto ricerca', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Cerca un evento…', 'open-events' ),
				'condition' => [ 'show_bar' => 'yes', 'show_text' => 'yes' ],
			]
		);

		$this->add_control(
			'show_comune',
			[
				'label'        => esc_html__( 'Mostra filtro Comune', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'condition'    => [ 'show_bar' => 'yes' ],
			]
		);

		$this->add_control(
			'show_category',
			[
				'label'        => esc_html__( 'Mostra filtro Categoria', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
				'condition'    => [ 'show_bar' => 'yes' ],
			]
		);

		$this->add_control(
			'show_date_picker',
			[
				'label'        => esc_html__( 'Mostra selettore data', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
				'condition'    => [ 'show_bar' => 'yes' ],
			]
		);

		$this->end_controls_section();

		// =====================================================================
		// TAB CONTENUTO — Sezione: Layout
		// =====================================================================
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'          => esc_html__( 'Colonne griglia', 'open-events' ),
				'type'           => \Elementor\Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				],
				'selectors'      => [
					'{{WRAPPER}} .oes-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
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

		$this->add_control(
			'max_events',
			[
				'label'       => esc_html__( 'Numero massimo eventi', 'open-events' ),
				'description' => esc_html__( '0 = mostra tutti (fino a 48).', 'open-events' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'max'         => 200,
				'separator'   => 'before',
			]
		);

		$this->end_controls_section();

		// =====================================================================
		// TAB STILE — Sezione: Barra di Ricerca
		// =====================================================================
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
				'label'       => esc_html__( 'Colore principale (accent)', 'open-events' ),
				'description' => esc_html__( 'Chip attivo, badge categoria, focus campi.', 'open-events' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'selectors'   => [
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

		$this->add_control(
			'heading_chips',
			[
				'label'     => esc_html__( 'Pulsanti Filtri', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'chip_active_bg',
			[
				'label'     => esc_html__( 'Sfondo pulsante attivo', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-chip.is-active' => 'background: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'chip_active_text',
			[
				'label'     => esc_html__( 'Testo pulsante attivo', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-chip.is-active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'chip_inactive_bg',
			[
				'label'     => esc_html__( 'Sfondo pulsanti inattivi', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-chip:not(.is-active)' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'chip_hover_bg',
			[
				'label'     => esc_html__( 'Sfondo pulsanti al passaggio del mouse (hover)', 'open-events' ),
				'description' => esc_html__( 'Si applica anche a "Azzera filtri" e alle icone griglia/lista.', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-chip:not(.is-active):hover' => 'background: {{VALUE}};',
					'{{WRAPPER}} .oes-reset:hover'                => 'background: {{VALUE}};',
					'{{WRAPPER}} .oes-view-btn:hover'             => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'chip_hover_text',
			[
				'label'     => esc_html__( 'Testo pulsanti al passaggio del mouse (hover)', 'open-events' ),
				'description' => esc_html__( 'Si applica anche a "Azzera filtri" e alle icone griglia/lista.', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-chip:not(.is-active):hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .oes-reset:hover'                => 'color: {{VALUE}};',
					'{{WRAPPER}} .oes-view-btn:hover'             => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'view_btn_active_color',
			[
				'label'     => esc_html__( 'Icona vista attiva', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-view-btn.is-active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_calendar',
			[
				'label'     => esc_html__( 'Calendario', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'date_field_hover_bg',
			[
				'label'     => esc_html__( 'Sfondo campo "Seleziona data" al passaggio del mouse (hover)', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-date-trigger:hover' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'date_field_hover_text',
			[
				'label'     => esc_html__( 'Testo campo "Seleziona data" al passaggio del mouse (hover)', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-date-trigger:hover'               => 'color: {{VALUE}};',
					'{{WRAPPER}} .oes-date-trigger:hover .oes-date-label'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .oes-date-trigger:hover .oes-date-chevron' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'dp_day_hover_bg',
			[
				'label'     => esc_html__( 'Sfondo numero giorno hover', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-dp-day:not(.is-empty):not(.is-start):not(.is-end):not(.is-single):hover' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'dp_day_hover_color',
			[
				'label'     => esc_html__( 'Testo numero giorno hover', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-dp-day:not(.is-empty):not(.is-start):not(.is-end):not(.is-single):hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'dp_day_active_bg',
			[
				'label'     => esc_html__( 'Sfondo data attiva (selezionata)', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-dp-day.is-single, {{WRAPPER}} .oes-dp-day.is-start, {{WRAPPER}} .oes-dp-day.is-end' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'dp_day_active_text',
			[
				'label'     => esc_html__( 'Testo data attiva (selezionata)', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oes-dp-day.is-single, {{WRAPPER}} .oes-dp-day.is-start, {{WRAPPER}} .oes-dp-day.is-end' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// =====================================================================
		// TAB STILE — Sezione: Schede Evento
		// =====================================================================
		$this->start_controls_section(
			'section_style_cards',
			[
				'label' => esc_html__( 'Schede Evento', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
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

		$this->add_control(
			'heading_gradient',
			[
				'label'     => esc_html__( 'Gradiente sfondo', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'card_gradient_color',
			[
				'label'     => esc_html__( 'Colore e trasparenza', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(10, 12, 18, 0.96)',
				'selectors' => [
					'{{WRAPPER}} .oes-card' => '--oes-grad-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_gradient_height',
			[
				'label'      => esc_html__( 'Altezza gradiente', 'open-events' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range'      => [ '%' => [ 'min' => 10, 'max' => 100 ] ],
				'default'    => [ 'size' => 40, 'unit' => '%' ],
				'selectors'  => [
					'{{WRAPPER}} .oes-card' => '--oes-grad-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings         = $this->get_settings_for_display();
		$show_bar         = ( 'yes' === ( $settings['show_bar']         ?? 'yes' ) );
		$show_text        = ( 'yes' === ( $settings['show_text']        ?? 'yes' ) );
		$show_comune      = ( 'yes' === ( $settings['show_comune']      ?? 'yes' ) );
		$show_category    = ( 'yes' === ( $settings['show_category']    ?? 'yes' ) );
		$show_date_picker = ( 'yes' === ( $settings['show_date_picker'] ?? 'yes' ) );
		$placeholder      = $settings['text_placeholder'] ?? esc_html__( 'Cerca un evento…', 'open-events' );
		$max_events       = max( 0, intval( $settings['max_events'] ?? 0 ) );

		$cities     = open_events_search_get_active_cities();
		$categories = open_events_search_get_active_categories();

		$initial_html = open_events_search_render_results(
			open_events_search_normalize_filters( [ 'date_mode' => 'upcoming', 'max_events' => $max_events ] )
		);
		?>
		<div class="oes-search">

			<?php if ( $show_bar ) : ?>
			<form class="oes-search-bar" role="search" onsubmit="return false;">
				<?php if ( $show_text ) : ?>
					<div class="oes-field oes-field-text">
						<svg class="oes-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
						<input type="search" class="oes-input oes-input-text" name="text" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off">
					</div>
				<?php endif; ?>

				<?php if ( $show_comune && ! empty( $cities ) ) : ?>
				<div class="oes-field">
					<svg class="oes-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 5.5-8 12-8 12s-8-6.5-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
					<select class="oes-input oes-input-comune" name="comune" aria-label="<?php esc_attr_e( 'Comune', 'open-events' ); ?>">
						<option value=""><?php esc_html_e( 'Tutti i Comuni', 'open-events' ); ?></option>
						<?php foreach ( $cities as $city ) : ?>
							<option value="<?php echo esc_attr( $city ); ?>"><?php echo esc_html( $city ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php endif; ?>

				<?php if ( $show_category && ! empty( $categories ) ) : ?>
				<div class="oes-field">
					<svg class="oes-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
					<select class="oes-input oes-input-category" name="category" aria-label="<?php esc_attr_e( 'Categoria', 'open-events' ); ?>">
						<option value="0"><?php esc_html_e( 'Tutte le categorie', 'open-events' ); ?></option>
						<?php foreach ( $categories as $term ) : ?>
							<option value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php endif; ?>

				<?php if ( $show_date_picker ) : ?>
				<div class="oes-field oes-field-date">
					<svg class="oes-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
					<button type="button" class="oes-input oes-date-trigger" aria-haspopup="true" aria-expanded="false">
						<span class="oes-date-label"><?php esc_html_e( 'Qualsiasi data', 'open-events' ); ?></span>
						<svg class="oes-date-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
					</button>
				</div>
				<?php endif; ?>
			</form>

			<div class="oes-quick" role="group" aria-label="<?php esc_attr_e( 'Filtri rapidi data', 'open-events' ); ?>">
				<button type="button" class="oes-chip is-active" data-date-mode="upcoming"><?php esc_html_e( 'Prossimi', 'open-events' ); ?></button>
				<button type="button" class="oes-chip" data-date-mode="today"><?php esc_html_e( 'Oggi', 'open-events' ); ?></button>
				<button type="button" class="oes-chip" data-date-mode="week"><?php esc_html_e( 'Questa settimana', 'open-events' ); ?></button>
				<button type="button" class="oes-chip" data-date-mode="month"><?php esc_html_e( 'Questo mese', 'open-events' ); ?></button>

				<button type="button" class="oes-reset" hidden><?php esc_html_e( 'Azzera filtri', 'open-events' ); ?></button>

				<div class="oes-view-toggle" role="group" aria-label="<?php esc_attr_e( 'Modalità visualizzazione', 'open-events' ); ?>">
					<button type="button" class="oes-view-btn is-active" data-view="grid" aria-pressed="true" aria-label="<?php esc_attr_e( 'Vista griglia', 'open-events' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
					</button>
					<button type="button" class="oes-view-btn" data-view="list" aria-pressed="false" aria-label="<?php esc_attr_e( 'Vista lista', 'open-events' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
					</button>
				</div>
			</div>
			<?php endif; ?>

			<div class="oes-results" aria-live="polite"
				data-max-events="<?php echo esc_attr( $max_events ); ?>"
				data-loading="<?php esc_attr_e( 'Caricamento…', 'open-events' ); ?>">
				<?php echo $initial_html; // già sanificato in open_events_search_render_results() ?>
			</div>
		</div>
		<?php
	}
}
