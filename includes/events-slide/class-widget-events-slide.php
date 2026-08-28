<?php
namespace OpenEvents;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget "Slide Eventi Consigliati": carousel hero con priorità ai Consigliati
 * attivi/eventi in primo piano, poi ai prossimi eventi in ordine di data.
 * Riusa la query di Ricerca Eventi (vedi includes/events-slide/events-slide.php),
 * qui c'è solo il rendering del carousel.
 */
class Widget_Events_Slide extends \Elementor\Widget_Base {

	public function get_name() {
		return 'open_events_slide';
	}

	public function get_title() {
		return esc_html__( 'Slide Eventi Consigliati', 'open-events' );
	}

	public function get_icon() {
		return 'eicon-slider-push';
	}

	public function get_categories() {
		return [ 'open-events', 'general' ];
	}

	public function get_keywords() {
		return [ 'eventi', 'slide', 'carousel', 'consigliati', 'featured', 'slider' ];
	}

	public function get_style_depends() {
		return [ 'open-events-slide-style' ];
	}

	public function get_script_depends() {
		return [ 'open-events-slide-script' ];
	}

	protected function register_controls() {

		// =====================================================================
		// TAB CONTENUTO — Sezione: Contenuto
		// =====================================================================
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Contenuto', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'max_items',
			[
				'label'   => esc_html__( 'Numero massimo eventi', 'open-events' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 20,
			]
		);

		$this->add_control(
			'only_featured',
			[
				'label'        => esc_html__( 'Solo eventi Consigliati/in primo piano', 'open-events' ),
				'description'  => esc_html__( 'Se attivo, mostra solo eventi Consigliati (pagati) o "in primo piano" — se non ce ne sono, la slide non appare.', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'fallback_message',
			[
				'label'       => esc_html__( 'Messaggio se non ci sono eventi', 'open-events' ),
				'description' => esc_html__( 'Lascia vuoto per non mostrare nulla quando non ci sono eventi da visualizzare.', 'open-events' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'separator'   => 'before',
			]
		);

		$this->end_controls_section();

		// =====================================================================
		// TAB CONTENUTO — Sezione: Comportamento
		// =====================================================================
		$this->start_controls_section(
			'section_behavior',
			[
				'label' => esc_html__( 'Comportamento', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => esc_html__( 'Autoplay', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'autoplay_delay',
			[
				'label'     => esc_html__( 'Velocità autoplay (ms)', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 5000,
				'min'       => 2000,
				'max'       => 10000,
				'step'      => 500,
				'condition' => [ 'autoplay' => 'yes' ],
			]
		);

		$this->add_control(
			'loop',
			[
				'label'        => esc_html__( 'Loop infinito', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();

		// =====================================================================
		// TAB STILE — Sezione: Aspetto
		// =====================================================================
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Aspetto', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label'      => esc_html__( 'Altezza slide', 'open-events' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 200, 'max' => 700, 'step' => 10 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 420 ],
				'tablet_default' => [ 'unit' => 'px', 'size' => 360 ],
				'mobile_default'  => [ 'unit' => 'px', 'size' => 300 ],
				'selectors'  => [
					'{{WRAPPER}} .oe-slide-card' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'overlay_opacity',
			[
				'label'     => esc_html__( 'Opacità overlay scuro', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 100, 'step' => 5 ] ],
				'default'   => [ 'unit' => 'px', 'size' => 45 ],
				'selectors' => [
					'{{WRAPPER}} .oe-slide-overlay' => 'opacity: {{SIZE}}%;',
				],
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label'      => esc_html__( 'Bordo arrotondato', 'open-events' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40, 'step' => 2 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 14 ],
				'selectors'  => [
					'{{WRAPPER}} .oe-slide-card' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Colore testo', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .oe-slide-content' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label'     => esc_html__( 'Colore accento (badge, pulsante)', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0284c7',
				'selectors' => [
					'{{WRAPPER}} .oe-slide-badge'  => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .oe-slide-cta'    => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .oe-slide-dot.is-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		echo open_events_slide_render(
			[
				'max_items'      => intval( $settings['max_items'] ?? 6 ),
				'only_featured'  => ( 'yes' === ( $settings['only_featured'] ?? '' ) ),
				'autoplay'       => ( 'yes' === ( $settings['autoplay'] ?? 'yes' ) ),
				'autoplay_delay' => intval( $settings['autoplay_delay'] ?? 5000 ),
				'loop'           => ( 'yes' === ( $settings['loop'] ?? 'yes' ) ),
				'fallback'       => $settings['fallback_message'] ?? '',
			]
		);
	}
}
