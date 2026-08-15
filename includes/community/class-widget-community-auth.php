<?php
namespace OpenEvents;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget "Community Auth": form di Accesso/Registrazione con toggle via JS
 * (nessun reload), configurato interamente da Impostazioni > Community
 * (campi obbligatori/opzionali, ruolo assegnato, pagine di redirect).
 */
class Widget_Community_Auth extends \Elementor\Widget_Base {

	public function get_name() {
		return 'open_events_community_auth';
	}

	public function get_title() {
		return esc_html__( 'Community Auth', 'open-events' );
	}

	public function get_icon() {
		return 'eicon-lock-user';
	}

	public function get_categories() {
		return [ 'open-events', 'general' ];
	}

	public function get_style_depends() {
		return [ 'open-events-community-style' ];
	}

	public function get_script_depends() {
		return [ 'open-events-community-script' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Community Auth', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'oe_community_note',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => esc_html__( 'Campi obbligatori/opzionali, ruolo assegnato ai nuovi utenti e pagine di redirect si configurano da Open Events → Community.', 'open-events' ),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->add_control(
			'default_tab',
			[
				'label'   => esc_html__( 'Tab predefinito', 'open-events' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'accedi',
				'options' => [
					'accedi'    => esc_html__( 'Accedi', 'open-events' ),
					'registrati' => esc_html__( 'Registrati', 'open-events' ),
				],
				'description' => esc_html__( 'Il parametro nell\'URL (?tab=registrati) ha comunque la precedenza su questa scelta.', 'open-events' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_register_tab',
			[
				'label'        => esc_html__( 'Mostra tab "Registrati"', 'open-events' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Sì', 'open-events' ),
				'label_off'    => esc_html__( 'No', 'open-events' ),
				'return_value' => 'yes',
				'description'  => esc_html__( 'Utile per una pagina di solo login. La registrazione resta comunque disponibile altrove se attiva nelle Impostazioni Community.', 'open-events' ),
			]
		);

		$this->add_control(
			'side_image',
			[
				'label'     => esc_html__( 'Immagine laterale', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'default'   => [ 'url' => '' ],
				'separator' => 'before',
				'description' => esc_html__( 'Se impostata, il modulo diventa un box a tutta larghezza diviso in due: form da un lato, immagine dall\'altro. Senza immagine il form resta centrato.', 'open-events' ),
			]
		);

		$this->add_control(
			'side_image_position',
			[
				'label'     => esc_html__( 'Posizione immagine', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'destra',
				'options'   => [
					'destra'  => esc_html__( 'Destra', 'open-events' ),
					'sinistra' => esc_html__( 'Sinistra', 'open-events' ),
				],
				'condition' => [ 'side_image[url]!' => '' ],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Stile', 'open-events' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label'     => esc_html__( 'Colore principale (accent)', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oe-community' => '--oe-community-accent: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_button_style',
			[
				'label'     => esc_html__( 'Pulsante Accedi/Registrati', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'submit_bg_color',
			[
				'label'     => esc_html__( 'Sfondo', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oe-community-submit' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'submit_text_color',
			[
				'label'     => esc_html__( 'Testo', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oe-community-submit' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'submit_hover_bg_color',
			[
				'label'     => esc_html__( 'Sfondo al passaggio del mouse', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oe-community-submit:hover' => 'background-color: {{VALUE}}; filter: none;',
				],
			]
		);

		$this->add_control(
			'submit_hover_text_color',
			[
				'label'     => esc_html__( 'Testo al passaggio del mouse', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oe-community-submit:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_tab_style',
			[
				'label'     => esc_html__( 'Tab Accedi/Registrati (sopra)', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'tab_active_bg_color',
			[
				'label'     => esc_html__( 'Sfondo tab attivo', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oe-community-tab.is-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tab_active_text_color',
			[
				'label'     => esc_html__( 'Testo tab attivo', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oe-community-tab.is-active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tab_hover_bg_color',
			[
				'label'     => esc_html__( 'Sfondo tab al passaggio del mouse', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oe-community-tab:hover:not(.is-active)' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tab_hover_text_color',
			[
				'label'     => esc_html__( 'Testo tab al passaggio del mouse', 'open-events' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .oe-community-tab:hover:not(.is-active)' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Stessa select comune di Impostazioni > Community, qui serve nel
	 * markup pubblico del form invece che nella pagina admin.
	 */
	private function render_city_field( $field_name, $current_value ) {
		$cities = open_events_get_available_cities();

		if ( empty( $cities ) ) {
			printf(
				'<input type="text" name="%s" value="%s" placeholder="%s">',
				esc_attr( $field_name ),
				esc_attr( $current_value ),
				esc_attr__( 'Es. Iseo', 'open-events' )
			);
			return;
		}
		?>
		<select name="<?php echo esc_attr( $field_name ); ?>" class="oe-community-select">
			<option value=""><?php esc_html_e( '-- Scegli una città --', 'open-events' ); ?></option>
			<?php foreach ( $cities as $city ) : ?>
				<option value="<?php echo esc_attr( $city ); ?>"><?php echo esc_html( $city ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Icona del provider sui pulsanti "Accedi/Registrati con Google/Facebook".
	 * SVG inline (nessun font/icon-set esterno da caricare), stesso approccio
	 * delle icone del widget Front-end Events Manager.
	 */
	private function render_social_icon( $provider ) {
		if ( 'google' === $provider ) {
			echo '<svg class="oe-social-icon" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/><path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/><path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/><path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/></svg>';
		} elseif ( 'facebook' === $provider ) {
			echo '<svg class="oe-social-icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="#1877F2" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>';
		}
	}

	protected function render() {
		$widget_settings = $this->get_settings_for_display();
		$settings        = open_events_get_community_settings();

		$default_tab     = ( 'registrati' === ( $widget_settings['default_tab'] ?? 'accedi' ) ) ? 'registrati' : 'accedi';
		$initial_tab     = ( isset( $_GET['tab'] ) && 'registrati' === $_GET['tab'] ) ? 'registrati' : $default_tab;
		$error_code      = isset( $_GET['oe_error'] ) ? sanitize_key( wp_unslash( $_GET['oe_error'] ) ) : '';
		$redirect_to     = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';

		// Il tab Registrati compare solo se attivo sia globalmente (Impostazioni
		// Community) sia su questa istanza del widget (utile per una pagina di
		// solo login, senza dover disattivare la registrazione ovunque).
		$show_register_tab = $settings['registration_enabled'] && ( 'yes' === ( $widget_settings['show_register_tab'] ?? 'yes' ) );
		if ( ! $show_register_tab ) {
			$initial_tab = 'accedi';
		}

		$side_image_url = trim( $widget_settings['side_image']['url'] ?? '' );
		$side_image_position = ( 'sinistra' === ( $widget_settings['side_image_position'] ?? 'destra' ) ) ? 'sinistra' : 'destra';

		// Script caricato solo qui (non in open_events_register_assets(), che
		// registra asset statici): l'URL di reCAPTCHA v3 incorpora la chiave
		// sito, quindi va costruito con le impostazioni correnti.
		$recaptcha_enabled = $show_register_tab
			&& ! empty( $settings['recaptcha']['enabled'] )
			&& ! empty( $settings['recaptcha']['site_key'] );
		if ( $recaptcha_enabled ) {
			wp_enqueue_script(
				'open-events-recaptcha',
				'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $settings['recaptcha']['site_key'] ),
				[],
				null,
				true
			);
		}

		// Nell'editor di Elementor si è sempre loggati (serve un account per
		// modificare la pagina): senza questo controllo non si vedrebbero mai
		// i form, solo il messaggio "hai già effettuato l'accesso", rendendo
		// impossibile vedere/stilizzare Accedi e Registrati durante l'editing.
		$is_editor_preview = \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| \Elementor\Plugin::$instance->preview->is_preview_mode();

		include OPEN_EVENTS_PLUGIN_DIR . 'includes/community/templates/auth-form.php';
	}
}
