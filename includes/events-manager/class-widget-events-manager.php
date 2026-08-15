<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Widget_Events_Manager extends \Elementor\Widget_Base {
    public function get_name() { return 'open_events_manager'; }
    public function get_title() { return esc_html__( 'Front-end Events Manager', 'open-events' ); }
    public function get_icon() { return 'eicon-form-horizontal'; }
    public function get_categories() { return [ 'open-events', 'general' ]; }
    public function get_style_depends() { return [ 'eicons', 'open-events-manager-style' ]; }
    public function get_script_depends() { return [ 'open-events-manager-script' ]; }

    protected function register_controls() {
        $this->start_controls_section(
            'section_config',
            [
                'label' => esc_html__( 'Configurazione', 'open-events' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'action_mode',
            [
                'label' => esc_html__( 'Azione / Modalità', 'open-events' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'hub',
                'options' => [
                    'hub' => esc_html__( 'Portale Completo (Hub + Sotto-sezioni)', 'open-events' ),
                    'dashboard' => esc_html__( 'Dashboard Singola (Solo un Tipo di Post)', 'open-events' ),
                    'add' => esc_html__( 'Aggiungi Nuovo (Solo Form)', 'open-events' ),
                    'edit' => esc_html__( 'Modifica Esistente (Solo Form)', 'open-events' ),
                ],
            ]
        );

        $this->add_control(
            'post_type_mode',
            [
                'label' => esc_html__( 'Tipo di Contenuto (Se Dashboard/Form Singoli)', 'open-events' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'tribe_events',
                'options' => [
                    'tribe_events' => esc_html__( 'Eventi (tribe_events)', 'open-events' ),
                    'tribe_organizer' => esc_html__( 'Organizzatore (tribe_organizer)', 'open-events' ),
                    'tribe_venue' => esc_html__( 'Luogo (tribe_venue)', 'open-events' ),
                ],
                'condition' => [
                    'action_mode!' => 'hub',
                ],
            ]
        );

        $this->add_control(
            'redirect_url',
            [
                'label' => esc_html__( 'URL di Reindirizzamento (Dopo salvataggio)', 'open-events' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://example.com/grazie',
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Custom Buttons Repeater
        $this->start_controls_section(
            'section_custom_buttons',
            [
                'label' => esc_html__( 'Link Personalizzati Hub', 'open-events' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'action_mode' => 'hub',
                ],
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'button_text',
            [
                'label' => esc_html__( 'Titolo Scheda', 'open-events' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Nuovo Link', 'open-events' ),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'button_desc',
            [
                'label' => esc_html__( 'Descrizione Scheda', 'open-events' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__( 'Gestisci o visita questo link personalizzato.', 'open-events' ),
                'rows' => 3,
            ]
        );

        $repeater->add_control(
            'button_link',
            [
                'label' => esc_html__( 'Link', 'open-events' ),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'open-events' ),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'button_icon',
            [
                'label' => esc_html__( 'Icona', 'open-events' ),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-link',
                    'library' => 'solid',
                ],
            ]
        );

        $this->add_control(
            'custom_buttons',
            [
                'label' => esc_html__( 'I miei Link', 'open-events' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '{{{ button_text }}}',
            ]
        );

        $this->end_controls_section();

        // Style controls
        $this->start_controls_section(
            'section_style_form',
            [
                'label' => esc_html__( 'Stile Modulo e Dashboard', 'open-events' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'dashboard_max_width',
            [
                'label' => esc_html__( 'Larghezza Massima Moduli e Dashboards', 'open-events' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 1600,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .em-form-container.em-dashboard-view' => 'max-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .em-form-container.em-form-view' => 'max-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .em-hub-view' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'form_bg_color',
            [
                'label' => esc_html__( 'Colore Sfondo Contenitore', 'open-events' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .em-form-container' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_padding',
            [
                'label' => esc_html__( 'Padding', 'open-events' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .em-form-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Icone SVG inline per l'interfaccia del portale: non dipendono dal
     * caricamento del font eicons di Elementor, che su alcuni siti/pagine
     * non viene incluso perché queste icone non passano dal suo Icon
     * control (rendendole invisibili anche dopo aver forzato lo stile
     * come dipendenza del widget).
     */
    private function icon_svg( $key ) {
        $icons = [
            'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="9" x2="21" y2="9"/></svg>',
            'map-pin'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5.5-8 12-8 12s-8-6.5-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>',
            'person'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>',
            'users'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'home'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>',
            'exit'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
            'link'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>',
            'save'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>',
            'upload'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>',
            'star'     => '<svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
            'edit'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
            'eye'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
            'check'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
            'trash'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
            'chart'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>',
            'close'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
            'credit-card' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
        ];

        return $icons[ $key ] ?? '';
    }

    /**
     * Logo del metodo di registrazione (badge sull'avatar in "Utenti
     * iscritti"). A differenza di icon_svg() questi sono a colori reali
     * (brand Google/Facebook), non currentColor: non hanno senso monocromi.
     */
    private function registration_provider_icon( $provider ) {
        $icons = [
            'google'   => '<svg viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.9 32.7 29.4 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l6-6C34.1 5.1 29.3 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21c10.5 0 20-7.6 20-21 0-1.4-.1-2.3-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 15.1 18.9 12 24 12c3.1 0 5.8 1.1 8 3l6-6C34.1 5.1 29.3 3 24 3c-7.4 0-13.8 4.2-17.1 10.3z"/><path fill="#4CAF50" d="M24 45c5.2 0 9.9-1.7 13.6-4.6l-6.3-5.3c-2 1.5-4.6 2.4-7.3 2.4-5.4 0-9.9-3.4-11.5-8.2l-6.5 5C9.9 40.5 16.4 45 24 45z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.9 2.5-2.5 4.6-4.7 6.1l6.3 5.3C39.9 37 44 31 44 24c0-1.4-.1-2.3-.4-3.5z"/></svg>',
            'facebook' => '<svg viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
            'email'    => '<svg viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg>',
        ];

        return $icons[ $provider ] ?? $icons['email'];
    }

    private function render_icon( $key, $extra_class = '' ) {
        echo '<span class="em-icon' . ( $extra_class ? ' ' . esc_attr( $extra_class ) : '' ) . '" aria-hidden="true">' . $this->icon_svg( $key ) . '</span>';
    }

    private function render_portal_sidebar( $active_view, $current_user ) {
        $current_user_id = $current_user->ID;
        $nav_items = [
            ''                => [ 'label' => esc_html__( 'Dashboard', 'open-events' ), 'icon' => 'home' ],
            'tribe_events'    => [ 'label' => esc_html__( 'I Miei Eventi', 'open-events' ), 'icon' => 'calendar' ],
            'tribe_venue'     => [ 'label' => esc_html__( 'I Miei Luoghi', 'open-events' ), 'icon' => 'map-pin' ],
            'tribe_organizer' => [ 'label' => esc_html__( 'I Miei Organizzatori', 'open-events' ), 'icon' => 'person' ],
        ];

        // Gestione utenti, statistiche e Consigliati sono riservate agli amministratori.
        if ( current_user_can( 'manage_options' ) ) {
            $nav_items['users'] = [ 'label' => esc_html__( 'Utenti', 'open-events' ), 'icon' => 'users' ];
            $nav_items['stats'] = [ 'label' => esc_html__( 'Statistiche', 'open-events' ), 'icon' => 'chart' ];
            $nav_items['consigliati'] = [ 'label' => esc_html__( 'Consigliati', 'open-events' ), 'icon' => 'star' ];
        }

        $nav_items['profile'] = [ 'label' => esc_html__( 'Profilo', 'open-events' ), 'icon' => 'person' ];
        ?>
        <aside class="em-portal-sidebar">
            <div class="em-portal-sidebar-user">
                <?php echo get_avatar( $current_user_id, 40 ); ?>
                <span><?php echo esc_html( $current_user->display_name ); ?></span>
            </div>
            <nav class="em-portal-sidebar-nav">
                <?php foreach ( $nav_items as $view_key => $item ) :
                    $url = ( '' === $view_key )
                        ? remove_query_arg( [ 'view', 'edit_id', 'action', 'type' ] )
                        : add_query_arg( 'view', $view_key, remove_query_arg( [ 'edit_id', 'action', 'type' ] ) );
                    $is_active = ( $active_view === $view_key );
                    ?>
                    <a href="<?php echo esc_url( $url ); ?>" class="em-portal-sidebar-link<?php echo $is_active ? ' is-active' : ''; ?>">
                        <?php $this->render_icon( $item['icon'] ); ?>
                        <?php echo esc_html( $item['label'] ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="em-portal-sidebar-logout">
                <?php $this->render_icon( 'exit' ); ?> <?php esc_html_e( 'Esci', 'open-events' ); ?>
            </a>
        </aside>
        <?php
    }

    private function render_breadcrumbs( array $trail ) {
        ?>
        <nav class="em-breadcrumbs" aria-label="<?php esc_attr_e( 'Percorso di navigazione', 'open-events' ); ?>">
            <?php foreach ( $trail as $i => $crumb ) : ?>
                <?php if ( $i > 0 ) : ?><span class="em-breadcrumb-sep">/</span><?php endif; ?>
                <?php if ( ! empty( $crumb['url'] ) ) : ?>
                    <a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
                <?php else : ?>
                    <span class="em-breadcrumb-current"><?php echo esc_html( $crumb['label'] ); ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    private function render_image_dropzone( $edit_post, $help_text = '' ) {
        $thumb_url = ( $edit_post && has_post_thumbnail( $edit_post->ID ) ) ? get_the_post_thumbnail_url( $edit_post->ID, 'thumbnail' ) : '';
        ?>
        <div class="em-dropzone">
            <input type="file" name="logo_image" accept="image/*" class="em-dropzone-input">
            <div class="em-dropzone-preview" <?php echo $thumb_url ? '' : 'style="display:none;"'; ?>>
                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="">
                <button type="button" class="em-dropzone-remove" aria-label="<?php esc_attr_e( 'Rimuovi immagine', 'open-events' ); ?>">&times;</button>
            </div>
            <div class="em-dropzone-empty" <?php echo $thumb_url ? 'style="display:none;"' : ''; ?>>
                <?php $this->render_icon( 'upload' ); ?>
                <p>
                    <strong><?php esc_html_e( 'Trascina un\'immagine qui', 'open-events' ); ?></strong><br>
                    <?php esc_html_e( 'oppure clicca per scegliere un file', 'open-events' ); ?>
                </p>
            </div>
        </div>
        <?php if ( $help_text ) : ?>
            <small class="em-field-help"><?php echo esc_html( $help_text ); ?></small>
        <?php endif; ?>
        <?php
    }

    private function render_city_field( $field_name, $current_value ) {
        $cities = open_events_get_available_cities();

        if ( empty( $cities ) ) {
            ?>
            <input type="text" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $current_value ); ?>" placeholder="<?php esc_attr_e( 'Es. Iseo', 'open-events' ); ?>">
            <?php
            return;
        }
        ?>
        <select name="<?php echo esc_attr( $field_name ); ?>" class="em-form-select">
            <option value=""><?php esc_html_e( '-- Scegli una città --', 'open-events' ); ?></option>
            <?php foreach ( $cities as $city ) : ?>
                <option value="<?php echo esc_attr( $city ); ?>" <?php selected( $current_value, $city ); ?>><?php echo esc_html( $city ); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Campo data con lo stesso stile "trigger + calendario popup" della barra
     * di ricerca: l'input nativo resta nel DOM (stessa posizione, invisibile)
     * cosi' name/value/required continuano a funzionare come prima; sopra ci
     * clicca il bottone che apre il calendario custom (vedi EmDatePicker in
     * events-manager.js).
     */
    private function render_date_field( $name, $id, $value ) {
        $required = in_array( $name, [ 'EventStartDate', 'EventEndDate' ], true );
        ?>
        <div class="em-date-field">
            <input type="date" <?php echo $name ? 'name="' . esc_attr( $name ) . '"' : ''; ?> id="<?php echo esc_attr( $id ); ?>" class="em-date-native" value="<?php echo esc_attr( $value ); ?>" <?php echo $required ? 'required' : ''; ?>>
            <button type="button" class="em-date-trigger" data-for="<?php echo esc_attr( $id ); ?>" aria-haspopup="true" aria-expanded="false">
                <svg class="em-date-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
                <span class="em-date-label"><?php esc_html_e( 'Scegli una data', 'open-events' ); ?></span>
                <svg class="em-date-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
        </div>
        <?php
    }

    /**
     * Quanti elementi di $post_type sono stati aggiunti dall'ultima visita
     * dell'utente a quella sezione. Alla primissima visita in assoluto (nessun
     * meta salvato) non mostriamo mai un conteggio: inizializza e basta,
     * altrimenti ogni contenuto esistente comparirebbe come "nuovo".
     */
    private function hub_new_count( $post_type, $is_admin_view, $current_user_id ) {
        $meta_key  = '_oe_hub_seen_' . $post_type;
        $last_seen = get_user_meta( $current_user_id, $meta_key, true );

        if ( '' === $last_seen ) {
            update_user_meta( $current_user_id, $meta_key, time() );
            return 0;
        }

        // Bypassa get_posts()/WP_Query: come altrove in questo file, The Events
        // Calendar forza post_status a 'publish' a livello SQL su ogni query
        // tribe_events, quindi i nuovi eventi ancora in attesa di revisione non
        // verrebbero mai contati nel badge. Query diretta al DB, nessun filtro
        // di terze parti può interferire.
        global $wpdb;
        $statuses = [ 'publish', 'pending', 'draft', 'future' ];
        $status_placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );

        $sql = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ($status_placeholders) AND post_date_gmt > %s";
        $params = array_merge( [ $post_type ], $statuses, [ gmdate( 'Y-m-d H:i:s', (int) $last_seen ) ] );

        if ( ! $is_admin_view ) {
            $sql .= ' AND post_author = %d';
            $params[] = $current_user_id;
        }

        return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
    }

    /** Segna $post_type come "visto adesso" per l'utente: azzera il badge. */
    private function hub_mark_seen( $post_type, $current_user_id ) {
        update_user_meta( $current_user_id, '_oe_hub_seen_' . $post_type, time() );
    }

    /**
     * Stesso pattern di hub_new_count(), ma sugli utenti WordPress (non un
     * post_type): conta le nuove registrazioni dall'ultima visita admin alla
     * sezione "Utenti". user_registered è già in GMT (come post_date_gmt),
     * stesso confronto con gmdate( time() ) usato sopra.
     */
    private function hub_new_users_count( $current_user_id ) {
        $meta_key  = '_oe_hub_seen_users';
        $last_seen = get_user_meta( $current_user_id, $meta_key, true );

        if ( '' === $last_seen ) {
            update_user_meta( $current_user_id, $meta_key, time() );
            return 0;
        }

        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered > %s",
            gmdate( 'Y-m-d H:i:s', (int) $last_seen )
        ) );
    }

    /** Pubblica subito un singolo post (usato sia per un elemento singolo che per ogni data di una serie). */
    private function publish_post_now( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return;
        }

        if ( empty( $post->post_name ) ) {
            // Un post inserito come "in attesa" può non avere mai avuto uno
            // slug/permalink generato: senza, l'URL pubblico dell'evento
            // risulta rotto (404) anche a stato correttamente "publish".
            $new_slug = wp_unique_post_slug( sanitize_title( $post->post_title ), $post_id, 'publish', $post->post_type, $post->post_parent );
            wp_update_post( [ 'ID' => $post_id, 'post_name' => $new_slug ] );
        }

        // Se post_date è nel passato/futuro rispetto a "adesso" per qualsiasi
        // motivo, wp_update_post() converte 'publish' in 'future' (post
        // programmato, invisibile pubblicamente) invece di pubblicarlo
        // davvero. Forziamo post_date a questo istante per evitarlo.
        wp_update_post( [
            'ID'            => $post_id,
            'post_status'   => 'publish',
            'post_date'     => current_time( 'mysql' ),
            'post_date_gmt' => current_time( 'mysql', true ),
        ] );

        if ( 'publish' !== get_post_status( $post_id ) ) {
            // Alcuni CPT (es. tribe_events di The Events Calendar) mappano le
            // capability di pubblicazione in modo non standard e possono far
            // fallire wp_update_post() in silenzio anche per un amministratore
            // già verificato. Scrittura diretta + hook di transizione rilanciati
            // a mano cosi' TEC resta sincronizzato.
            open_events_force_post_status( $post_id, 'publish' );
        } elseif ( 'tribe_events' === $post->post_type ) {
            // Ricostruisce evento + occorrenze nelle custom tables di TEC 6.
            open_events_sync_event_custom_tables( $post_id );
        }
    }

    protected function render() {
        if ( ! is_user_logged_in() ) {
            echo '<div class="em-alert error">' . esc_html__( 'Devi aver effettuato l\'accesso per inserire un evento.', 'open-events' ) . '</div>';
            return;
        }

        $settings = $this->get_settings_for_display();
        $action_mode = $settings['action_mode'];
        $redirect = $settings['redirect_url'];
        $current_user = wp_get_current_user();
        $current_user_id = $current_user->ID;
        $is_admin_view = current_user_can( 'manage_options' );

        // Resolve active post_type
        $post_type = '';
        if ( 'hub' === $action_mode ) {
            $post_type = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : '';
            if ( ! in_array( $post_type, [ 'tribe_events', 'tribe_organizer', 'tribe_venue', 'profile', 'users', 'stats', 'consigliati' ] ) ) {
                $post_type = '';
            }
            // L'utente sta aprendo la sezione: il badge "nuovi" si azzera.
            if ( in_array( $post_type, [ 'tribe_events', 'tribe_organizer', 'tribe_venue', 'users' ], true ) ) {
                $this->hub_mark_seen( $post_type, $current_user_id );
            }
        } else {
            $post_type = $settings['post_type_mode'];
        }

        // Admin quick actions: pubblica / elimina (cestino) qualsiasi elemento del tipo corrente
        if ( $is_admin_view && ! empty( $post_type ) && isset( $_GET['em_action'], $_GET['post_id'] ) ) {
            $target_id = intval( $_GET['post_id'] );
            $target_post = get_post( $target_id );
            if ( $target_post && $target_post->post_type === $post_type ) {
                $redirect_back = remove_query_arg( [ 'em_action', 'post_id', '_wpnonce' ] );
                $requested_action = $_GET['em_action'];
                $nonce_action = 'delete' === $requested_action ? 'em_delete_' . $target_id : 'em_publish_' . $target_id;

                if ( in_array( $requested_action, [ 'delete', 'publish' ], true ) && ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', $nonce_action ) ) {
                    echo '<div class="em-alert error">' . esc_html__( 'Link di azione scaduto o non valido. Torna alla lista e riprova.', 'open-events' ) . '</div>';
                } elseif ( 'delete' === $requested_action ) {
                    wp_trash_post( $target_id );
                    if ( 'trash' !== get_post_status( $target_id ) ) {
                        open_events_force_post_status( $target_id, 'trash' );
                    }
                    echo '<script type="text/javascript">window.location.href = "' . esc_url_raw( $redirect_back ) . '";</script>';
                    return;
                } elseif ( 'publish' === $requested_action ) {
                    $this->publish_post_now( $target_id );

                    // Evento con più date: pubblicandone una si confermano tutte le
                    // altre della stessa serie, cosi' l'utente non deve ripetere
                    // l'azione manualmente per ognuna.
                    if ( 'tribe_events' === $target_post->post_type ) {
                        // Notifica all'autore: template configurabile in
                        // Open Events → Community (vedi community-emails.php).
                        do_action( 'oe_community_event_published', $target_id, $target_post->post_title, $target_post->post_author );

                        $series_id = get_post_meta( $target_id, '_oe_series_id', true );
                        if ( $series_id ) {
                            // Bypassa get_posts()/WP_Query: come nella lista "I Miei
                            // Eventi" più sopra, The Events Calendar forza post_status
                            // a 'publish' a livello SQL su ogni query tribe_events,
                            // quindi i "fratelli" ancora in attesa non verrebbero mai
                            // trovati. Query diretta al DB, nessun filtro di terze
                            // parti può interferire.
                            global $wpdb;
                            $sibling_ids = $wpdb->get_col( $wpdb->prepare(
                                "SELECT p.ID FROM {$wpdb->posts} p
                                INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_oe_series_id' AND pm.meta_value = %s
                                WHERE p.post_type = 'tribe_events' AND p.post_status IN ( 'draft', 'pending', 'future' ) AND p.ID != %d",
                                $series_id,
                                $target_id
                            ) );
                            foreach ( $sibling_ids as $sibling_id ) {
                                $this->publish_post_now( (int) $sibling_id );
                            }
                        }
                    }

                    echo '<script type="text/javascript">window.location.href = "' . esc_url_raw( $redirect_back ) . '";</script>';
                    return;
                }
            }
        }

        // Azione utente: attiva/riprendi il pagamento "Consigliato" su un
        // proprio evento dall'elenco, senza dover riaprire il form. Non è
        // gated da $is_admin_view (è un'azione sul proprio contenuto), ma un
        // admin può farla per conto di chiunque (stesso spirito della sezione
        // Consigliati che gestisce comunque anche le conferme manuali).
        if ( 'tribe_events' === $post_type && isset( $_GET['oe_featured_checkout'], $_GET['post_id'] ) && 'start' === $_GET['oe_featured_checkout'] ) {
            $target_id = intval( $_GET['post_id'] );
            $target_post = get_post( $target_id );
            $redirect_back = remove_query_arg( [ 'oe_featured_checkout', 'post_id', '_wpnonce' ] );

            if ( ! $target_post || 'tribe_events' !== $target_post->post_type ) {
                echo '<div class="em-alert error">' . esc_html__( 'Evento non trovato.', 'open-events' ) . '</div>';
            } elseif ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'oe_featured_start_' . $target_id ) ) {
                echo '<div class="em-alert error">' . esc_html__( 'Link scaduto o non valido. Torna alla lista e riprova.', 'open-events' ) . '</div>';
            } elseif ( (int) $target_post->post_author !== (int) $current_user_id && ! $is_admin_view ) {
                echo '<div class="em-alert error">' . esc_html__( 'Non hai i permessi per questa azione.', 'open-events' ) . '</div>';
            } else {
                $checkout_result = open_events_featured_start_checkout_flow( $target_id, $redirect_back );
                if ( is_wp_error( $checkout_result ) ) {
                    echo '<div class="em-alert error">' . esc_html( $checkout_result->get_error_message() ) . '</div>';
                } else {
                    // Nessun wrapper $show_sidebar da chiudere qui: a questo punto
                    // del metodo non è ancora stato aperto (viene definito e aperto
                    // più sotto), stesso motivo per cui il blocco pubblica/elimina
                    // qui sopra non lo controlla.
                    echo '<script type="text/javascript">window.location.href = "' . esc_url_raw( $checkout_result ) . '";</script>';
                    echo '<div class="em-form-container em-form-view"><div class="em-alert success">' . esc_html__( 'Reindirizzamento al pagamento in corso...', 'open-events' ) . '</div></div>';
                    return;
                }
            }
        }

        $post_type_labels = [
            'tribe_events'    => [
                'plural'   => $is_admin_view ? esc_html__( 'Tutti gli Eventi', 'open-events' ) : esc_html__( 'I Miei Eventi', 'open-events' ),
                'singular' => esc_html__( 'Evento', 'open-events' ),
                'icon'     => 'calendar',
            ],
            'tribe_organizer' => [
                'plural'   => $is_admin_view ? esc_html__( 'Tutti gli Organizzatori', 'open-events' ) : esc_html__( 'I Miei Organizzatori', 'open-events' ),
                'singular' => esc_html__( 'Organizzatore', 'open-events' ),
                'icon'     => 'person',
            ],
            'tribe_venue'     => [
                'plural'   => $is_admin_view ? esc_html__( 'Tutti i Luoghi', 'open-events' ) : esc_html__( 'I Miei Luoghi', 'open-events' ),
                'singular' => esc_html__( 'Luogo', 'open-events' ),
                'icon'     => 'map-pin',
            ],
        ];
        $label_plural   = $post_type_labels[ $post_type ]['plural'] ?? '';
        $label_singular = $post_type_labels[ $post_type ]['singular'] ?? '';
        $label_icon     = $post_type_labels[ $post_type ]['icon'] ?? 'link';

        // La sidebar/breadcrumb persistenti hanno senso solo nel Portale Completo:
        // le altre modalità sono pensate per essere embeddate isolate in pagine dedicate.
        $show_sidebar = ( 'hub' === $action_mode );
        if ( $show_sidebar ) {
            echo '<div class="em-portal-layout">';
            $this->render_portal_sidebar( $post_type, $current_user );
            echo '<div class="em-portal-main">';
        }

        // Handle profile page
        if ( 'profile' === $post_type ) {
            $profile_success = '';
            $profile_error = '';

            if ( isset( $_POST['profile_submit'] ) && wp_verify_nonce( $_POST['profile_nonce'], 'profile_save' ) ) {
                $first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
                $last_name = sanitize_text_field( $_POST['last_name'] ?? '' );
                $email = sanitize_email( $_POST['email'] ?? '' );
                $website = esc_url_raw( $_POST['website'] ?? '' );
                $pass1 = $_POST['pass1'] ?? '';
                $pass2 = $_POST['pass2'] ?? '';

                if ( ! is_email( $email ) ) {
                    $profile_error = esc_html__( 'L\'indirizzo email inserito non è valido.', 'open-events' );
                } elseif ( email_exists( $email ) && email_exists( $email ) !== $current_user_id ) {
                    $profile_error = esc_html__( 'Questo indirizzo email è già in uso.', 'open-events' );
                } else {
                    $user_data = [
                        'ID'         => $current_user_id,
                        'first_name' => $first_name,
                        'last_name'  => $last_name,
                        'user_email' => $email,
                        'user_url'   => $website,
                    ];

                    if ( ! empty( $pass1 ) ) {
                        if ( $pass1 !== $pass2 ) {
                            $profile_error = esc_html__( 'Le password inserite non coincidono.', 'open-events' );
                        } else {
                            $user_data['user_pass'] = $pass1;
                        }
                    }

                    if ( empty( $profile_error ) ) {
                        $update_res = wp_update_user( $user_data );
                        if ( is_wp_error( $update_res ) ) {
                            $profile_error = $update_res->get_error_message();
                        } else {
                            $profile_success = esc_html__( 'Profilo aggiornato con successo!', 'open-events' );
                            $current_user = wp_get_current_user(); 
                        }
                    }
                }
            }
            include OPEN_EVENTS_PLUGIN_DIR . 'includes/events-manager/templates/profile.php';

            if ( $show_sidebar ) {
                echo '</div></div>';
            }
            return;
        }

        // Handle Utenti (gestione utenti iscritti — solo amministratori)
        if ( 'users' === $post_type ) {
            if ( ! $is_admin_view ) {
                echo '<div class="em-alert error">' . esc_html__( 'Non hai i permessi per gestire gli utenti.', 'open-events' ) . '</div>';
                if ( $show_sidebar ) {
                    echo '</div></div>';
                }
                return;
            }

            require_once ABSPATH . 'wp-admin/includes/user.php';

            $users_notice = '';
            $users_error  = '';
            $editable_roles = get_editable_roles();
            $editable_role_keys = array_keys( $editable_roles );
            $admin_count = count( get_users( [ 'role' => 'administrator', 'fields' => 'ID' ] ) );

            // Azione: cambio ruolo (POST)
            if ( isset( $_POST['oe_user_role_submit'], $_POST['oe_user_id'] )
                && wp_verify_nonce( $_POST['oe_user_role_nonce'] ?? '', 'oe_user_role' ) ) {
                $target_uid = intval( $_POST['oe_user_id'] );
                $new_role   = sanitize_text_field( wp_unslash( $_POST['oe_user_role'] ?? '' ) );
                $target     = get_userdata( $target_uid );

                if ( ! current_user_can( 'promote_users' ) ) {
                    $users_error = esc_html__( 'Non hai i permessi per cambiare i ruoli.', 'open-events' );
                } elseif ( $target_uid === $current_user_id ) {
                    $users_error = esc_html__( 'Non puoi cambiare il tuo stesso ruolo da qui.', 'open-events' );
                } elseif ( ! $target ) {
                    $users_error = esc_html__( 'Utente non trovato.', 'open-events' );
                } elseif ( ! in_array( $new_role, $editable_role_keys, true ) ) {
                    $users_error = esc_html__( 'Ruolo non valido.', 'open-events' );
                } elseif ( in_array( 'administrator', (array) $target->roles, true ) && 'administrator' !== $new_role && $admin_count <= 1 ) {
                    $users_error = esc_html__( 'Non puoi rimuovere il ruolo all\'unico amministratore rimasto.', 'open-events' );
                } else {
                    $target->set_role( $new_role );
                    $users_notice = sprintf( esc_html__( 'Ruolo di %s aggiornato.', 'open-events' ), $target->display_name );
                }
            }

            // Azione: elimina utente (GET con nonce). I contenuti vengono riassegnati all'admin corrente.
            if ( isset( $_GET['oe_user_action'], $_GET['user_id'] ) && 'delete' === $_GET['oe_user_action'] ) {
                $target_uid = intval( $_GET['user_id'] );

                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'oe_user_delete_' . $target_uid ) ) {
                    $users_error = esc_html__( 'Link di eliminazione scaduto o non valido. Riprova.', 'open-events' );
                } elseif ( ! current_user_can( 'delete_users' ) ) {
                    $users_error = esc_html__( 'Non hai i permessi per eliminare utenti.', 'open-events' );
                } elseif ( $target_uid === $current_user_id ) {
                    $users_error = esc_html__( 'Non puoi eliminare il tuo stesso account.', 'open-events' );
                } else {
                    $target = get_userdata( $target_uid );
                    if ( ! $target ) {
                        $users_error = esc_html__( 'Utente non trovato.', 'open-events' );
                    } elseif ( in_array( 'administrator', (array) $target->roles, true ) ) {
                        $users_error = esc_html__( 'Non puoi eliminare un altro amministratore.', 'open-events' );
                    } else {
                        $deleted_name = $target->display_name;
                        wp_delete_user( $target_uid, $current_user_id );
                        $users_notice = sprintf( esc_html__( 'Utente %s eliminato; i suoi contenuti sono stati riassegnati al tuo account.', 'open-events' ), $deleted_name );
                    }
                }
            }

            $all_users = get_users( [ 'orderby' => 'registered', 'order' => 'DESC' ] );
            $date_format = get_option( 'date_format' );

            include OPEN_EVENTS_PLUGIN_DIR . 'includes/events-manager/templates/users.php';

            if ( $show_sidebar ) {
                echo '</div></div>';
            }
            return;
        }

        // Handle Statistiche (solo amministratori)
        if ( 'stats' === $post_type ) {
            if ( ! $is_admin_view ) {
                echo '<div class="em-alert error">' . esc_html__( 'Non hai i permessi per vedere le statistiche.', 'open-events' ) . '</div>';
                if ( $show_sidebar ) {
                    echo '</div></div>';
                }
                return;
            }

            global $wpdb;

            $events_counts     = wp_count_posts( 'tribe_events' );
            $venues_counts     = wp_count_posts( 'tribe_venue' );
            $organizers_counts = wp_count_posts( 'tribe_organizer' );

            $published_events     = (int) ( $events_counts->publish ?? 0 );
            $published_venues     = (int) ( $venues_counts->publish ?? 0 );
            $published_organizers = (int) ( $organizers_counts->publish ?? 0 );

            // "Online" = pubblicati e non ancora conclusi (visibili ora sul sito).
            $upcoming_query = new \WP_Query( [
                'post_type'      => 'tribe_events',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_query'     => [
                    [
                        'key'     => '_EventStartDate',
                        'value'   => current_time( 'mysql' ),
                        'compare' => '>=',
                        'type'    => 'DATETIME',
                    ],
                ],
            ] );
            $online_events = (int) $upcoming_query->found_posts;
            $past_events    = max( 0, $published_events - $online_events );

            $users_count = count_users();
            $total_users = (int) ( $users_count['total_users'] ?? 0 );

            $total_views = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT SUM(CAST(pm.meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_oe_card_views' AND p.post_type = %s",
                'tribe_events'
            ) );

            $top_viewed = $wpdb->get_results( $wpdb->prepare(
                "SELECT p.ID, p.post_title, CAST(pm.meta_value AS UNSIGNED) AS views
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_oe_card_views'
                 WHERE p.post_type = %s AND p.post_status = 'publish'
                 ORDER BY views DESC
                 LIMIT 5",
                'tribe_events'
            ) );

            $stat_tiles = [
                [ 'icon' => 'calendar', 'label' => esc_html__( 'Eventi pubblicati', 'open-events' ), 'value' => $published_events ],
                [ 'icon' => 'eye',      'label' => esc_html__( 'Eventi online', 'open-events' ),      'value' => $online_events ],
                [ 'icon' => 'calendar', 'label' => esc_html__( 'Eventi passati', 'open-events' ),     'value' => $past_events ],
                [ 'icon' => 'map-pin',  'label' => esc_html__( 'Luoghi pubblicati', 'open-events' ),  'value' => $published_venues ],
                [ 'icon' => 'person',   'label' => esc_html__( 'Organizzatori pubblicati', 'open-events' ), 'value' => $published_organizers ],
                [ 'icon' => 'users',    'label' => esc_html__( 'Utenti iscritti', 'open-events' ),    'value' => $total_users ],
                [ 'icon' => 'eye',      'label' => esc_html__( 'Visualizzazioni schede evento', 'open-events' ), 'value' => $total_views ],
            ];

            include OPEN_EVENTS_PLUGIN_DIR . 'includes/events-manager/templates/stats.php';

            if ( $show_sidebar ) {
                echo '</div></div>';
            }
            return;
        }

        // Handle Consigliati (eventi con promozione a pagamento — solo amministratori)
        if ( 'consigliati' === $post_type ) {
            if ( ! $is_admin_view ) {
                echo '<div class="em-alert error">' . esc_html__( 'Non hai i permessi per vedere gli eventi Consigliati.', 'open-events' ) . '</div>';
                if ( $show_sidebar ) {
                    echo '</div></div>';
                }
                return;
            }

            open_events_featured_hub_mark_seen( $current_user_id );

            $consigliati_notice = '';
            $consigliati_error  = '';

            // Azione: conferma/revoca manuale (GET con nonce) — per pagamenti
            // gestiti fuori piattaforma, rimborsi, ecc.
            if ( isset( $_GET['oe_featured_action'], $_GET['post_id'] ) && in_array( $_GET['oe_featured_action'], [ 'confirm', 'revoke' ], true ) ) {
                $target_id  = intval( $_GET['post_id'] );
                $action_key = sanitize_text_field( wp_unslash( $_GET['oe_featured_action'] ) );

                if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'oe_featured_' . $action_key . '_' . $target_id ) ) {
                    $consigliati_error = esc_html__( 'Link di azione scaduto o non valido. Riprova.', 'open-events' );
                } elseif ( 'tribe_events' !== get_post_type( $target_id ) ) {
                    $consigliati_error = esc_html__( 'Evento non trovato.', 'open-events' );
                } elseif ( 'confirm' === $action_key ) {
                    update_post_meta( $target_id, '_illi_featured_status', 'paid' );
                    if ( ! get_post_meta( $target_id, '_illi_featured_paid_at', true ) ) {
                        update_post_meta( $target_id, '_illi_featured_paid_at', current_time( 'mysql' ) );
                    }
                    $consigliati_notice = esc_html__( 'Evento confermato come Consigliato.', 'open-events' );
                } else {
                    update_post_meta( $target_id, '_illi_featured_status', 'none' );
                    $consigliati_notice = esc_html__( 'Stato Consigliato revocato.', 'open-events' );
                }
            }

            $consigliati_filter = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
            if ( ! in_array( $consigliati_filter, [ 'paid', 'pending_payment', 'expired' ], true ) ) {
                $consigliati_filter = '';
            }

            // Query diretta $wpdb: MAI get_posts()/WP_Query su tribe_events per
            // stati diversi da 'publish' — The Events Calendar li forza a
            // livello SQL (stesso problema già risolto altrove nel plugin).
            global $wpdb;
            $status_values = $consigliati_filter ? [ $consigliati_filter ] : [ 'paid', 'pending_payment', 'expired' ];
            $status_placeholders = implode( ', ', array_fill( 0, count( $status_values ), '%s' ) );

            $consigliati_events = $wpdb->get_results( $wpdb->prepare(
                "SELECT p.ID, p.post_title, p.post_author, p.post_status,
                    status_meta.meta_value AS featured_status,
                    amount_meta.meta_value AS featured_amount,
                    date_meta.meta_value AS featured_date
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} status_meta ON status_meta.post_id = p.ID AND status_meta.meta_key = '_illi_featured_status'
                 LEFT JOIN {$wpdb->postmeta} amount_meta ON amount_meta.post_id = p.ID AND amount_meta.meta_key = '_illi_featured_amount'
                 LEFT JOIN {$wpdb->postmeta} date_meta ON date_meta.post_id = p.ID AND date_meta.meta_key = '_illi_featured_first_date'
                 WHERE p.post_type = 'tribe_events' AND p.post_status != 'trash' AND status_meta.meta_value IN ($status_placeholders)
                 ORDER BY p.ID DESC",
                $status_values
            ) );

            include OPEN_EVENTS_PLUGIN_DIR . 'includes/events-manager/templates/consigliati.php';

            if ( $show_sidebar ) {
                echo '</div></div>';
            }
            return;
        }

        // Handle Portal Hub Home
        if ( 'hub' === $action_mode && empty( $post_type ) ) {
            $hub_new_events     = $this->hub_new_count( 'tribe_events', $is_admin_view, $current_user_id );
            $hub_new_venues     = $this->hub_new_count( 'tribe_venue', $is_admin_view, $current_user_id );
            $hub_new_organizers = $this->hub_new_count( 'tribe_organizer', $is_admin_view, $current_user_id );
            $hub_new_consigliati = $is_admin_view ? open_events_featured_hub_new_count( $current_user_id ) : 0;
            $hub_new_users      = $is_admin_view ? $this->hub_new_users_count( $current_user_id ) : 0;

            include OPEN_EVENTS_PLUGIN_DIR . 'includes/events-manager/templates/hub.php';

            if ( $show_sidebar ) {
                echo '</div></div>';
            }
            return;
        }

        // Set action dynamically inside CPT dashboard / individual views
        $current_action = $action_mode;
        $edit_post_id = 0;

        if ( 'hub' === $action_mode || 'dashboard' === $action_mode ) {
            if ( isset( $_GET['edit_id'] ) ) {
                $check_post = get_post( intval( $_GET['edit_id'] ) );
                if ( $check_post && $check_post->post_type === $post_type ) {
                    $current_action = 'edit';
                    $edit_post_id = intval( $_GET['edit_id'] );
                } else {
                    $current_action = 'list';
                }
            } elseif ( isset( $_GET['action'] ) && 'add' === $_GET['action'] && isset( $_GET['type'] ) && $_GET['type'] === $post_type ) {
                $current_action = 'add';
            } else {
                $current_action = 'list';
            }
        } elseif ( 'edit' === $action_mode ) {
            $edit_post_id = isset( $_GET['edit_id'] ) ? intval( $_GET['edit_id'] ) : 0;
            $check_post = get_post( $edit_post_id );
            if ( ! $check_post || $check_post->post_type !== $post_type ) {
                return;
            }
        }

        // Handle list view
        if ( 'list' === $current_action ) {
            // Bypassa completamente WP_Query: un altro plugin (probabile The
            // Events Calendar) filtra a livello SQL (posts_where/posts_clauses)
            // le query su tribe_events forzando post_status a 'publish', quindi
            // nessun $query->set() su pre_get_posts riesce a vincere. Query
            // diretta al DB cosi' nessun filtro di terze parti puo' interferire.
            global $wpdb;
            $allowed_statuses = [ 'publish', 'draft', 'pending' ];
            $status_placeholders = implode( ', ', array_fill( 0, count( $allowed_statuses ), '%s' ) );
            if ( $is_admin_view ) {
                $sql = $wpdb->prepare(
                    "SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ($status_placeholders) ORDER BY post_date DESC",
                    array_merge( [ $post_type ], $allowed_statuses )
                );
            } else {
                $sql = $wpdb->prepare(
                    "SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d AND post_status IN ($status_placeholders) ORDER BY post_date DESC",
                    array_merge( [ $post_type, $current_user_id ], $allowed_statuses )
                );
            }
            $user_posts = $wpdb->get_results( $sql );

            $list_status  = '';
            $total_pages  = 0;
            $current_page = 1;

            $sort_by  = 'post_date';
            $sort_dir = 'desc';

            if ( 'tribe_events' === $post_type ) {
                // Un evento e' "scaduto" quando la sua fine (o l'inizio, se
                // manca la fine) e' nel passato — usato sia per il filtro
                // Tutti/Pubblicati/Scaduti sia per non tenere in cima alla
                // lista un evento Consigliato ormai concluso.
                $now = current_time( 'mysql' );
                foreach ( $user_posts as $p ) {
                    $p->event_start = get_post_meta( $p->ID, '_EventStartDate', true );
                    $end = get_post_meta( $p->ID, '_EventEndDate', true ) ?: $p->event_start;
                    $p->is_expired = $end && $end < $now;
                }

                // Ordinamento scelto dall'utente (icone in "Tutti gli Eventi"):
                // per data di pubblicazione o per data evento, entrambe con
                // verso configurabile. Resta comunque secondario al "pin" degli
                // eventi in primo piano/Consigliati attivi, sopra.
                $sort_by = isset( $_GET['sort_by'] ) ? sanitize_key( wp_unslash( $_GET['sort_by'] ) ) : 'post_date';
                if ( ! in_array( $sort_by, [ 'post_date', 'event_date' ], true ) ) {
                    $sort_by = 'post_date';
                }
                $sort_dir = isset( $_GET['sort_dir'] ) ? sanitize_key( wp_unslash( $_GET['sort_dir'] ) ) : 'desc';
                if ( ! in_array( $sort_dir, [ 'asc', 'desc' ], true ) ) {
                    $sort_dir = 'desc';
                }
                $sort_multiplier = 'asc' === $sort_dir ? 1 : -1;

                usort( $user_posts, function( $a, $b ) use ( $sort_by, $sort_multiplier ) {
                    $a_pinned = ! $a->is_expired && ( '1' === get_post_meta( $a->ID, '_tribe_featured', true ) || open_events_featured_is_active( $a->ID ) ) ? 1 : 0;
                    $b_pinned = ! $b->is_expired && ( '1' === get_post_meta( $b->ID, '_tribe_featured', true ) || open_events_featured_is_active( $b->ID ) ) ? 1 : 0;
                    if ( $a_pinned !== $b_pinned ) {
                        return $b_pinned - $a_pinned;
                    }

                    if ( 'event_date' === $sort_by ) {
                        $a_val = strtotime( (string) $a->event_start );
                        $b_val = strtotime( (string) $b->event_start );
                    } else {
                        $a_val = strtotime( $a->post_date );
                        $b_val = strtotime( $b->post_date );
                    }

                    return ( $a_val - $b_val ) * $sort_multiplier;
                } );

                // Filtro Tutti/Pubblicati/Scaduti: sia in "Tutti gli Eventi"
                // (admin) sia in "I Miei Eventi" (utente).
                $list_status = isset( $_GET['list_status'] ) ? sanitize_key( wp_unslash( $_GET['list_status'] ) ) : '';
                if ( ! in_array( $list_status, [ 'publish', 'expired' ], true ) ) {
                    $list_status = '';
                }

                if ( 'publish' === $list_status ) {
                    $user_posts = array_values( array_filter( $user_posts, function( $p ) {
                        return 'publish' === $p->post_status && ! $p->is_expired;
                    } ) );
                } elseif ( 'expired' === $list_status ) {
                    $user_posts = array_values( array_filter( $user_posts, function( $p ) {
                        return $p->is_expired;
                    } ) );
                }

                // Paginazione: solo nella pagina admin "Tutti gli Eventi", che
                // su un sito attivo puo' avere molti eventi.
                if ( $is_admin_view ) {
                    $per_page     = 30;
                    $total_items  = count( $user_posts );
                    $total_pages  = (int) ceil( $total_items / $per_page );
                    $current_page = isset( $_GET['epage'] ) ? max( 1, intval( $_GET['epage'] ) ) : 1;
                    if ( $total_pages && $current_page > $total_pages ) {
                        $current_page = $total_pages;
                    }
                    $user_posts = array_slice( $user_posts, ( $current_page - 1 ) * $per_page, $per_page );
                }
            }

            include OPEN_EVENTS_PLUGIN_DIR . 'includes/events-manager/templates/items-list.php';

            if ( $show_sidebar ) {
                echo '</div></div>';
            }
            return;
        }

        // Edit validation
        $edit_post = null;
        if ( 'edit' === $current_action ) {
            if ( ! $edit_post_id ) {
                echo '<div class="em-alert warning">' . esc_html__( 'Nessun elemento specificato per la modifica.', 'open-events' ) . '</div>';
                if ( $show_sidebar ) {
                    echo '</div></div>';
                }
                return;
            }

            $edit_post = get_post( $edit_post_id );
            if ( ! $edit_post || $edit_post->post_type !== $post_type ) {
                echo '<div class="em-alert error">' . esc_html__( 'Elemento non trovato.', 'open-events' ) . '</div>';
                if ( $show_sidebar ) {
                    echo '</div></div>';
                }
                return;
            }

            if ( ! $is_admin_view && intval( $edit_post->post_author ) !== $current_user_id ) {
                echo '<div class="em-alert error">' . esc_html__( 'Non hai i permessi per modificare questo elemento.', 'open-events' ) . '</div>';
                if ( $show_sidebar ) {
                    echo '</div></div>';
                }
                return;
            }

            // Un evento già pubblicato non è più modificabile dal proprietario:
            // solo un amministratore può intervenire su un evento live.
            if ( ! $is_admin_view && 'tribe_events' === $post_type && 'publish' === $edit_post->post_status ) {
                echo '<div class="em-alert error">' . esc_html__( 'Questo evento è già pubblicato: solo un amministratore può modificarlo.', 'open-events' ) . '</div>';
                if ( $show_sidebar ) {
                    echo '</div></div>';
                }
                return;
            }
        }

        $success_msg = '';
        $error_msg = '';

        if ( isset( $_POST['action_submit'] ) && wp_verify_nonce( $_POST['em_nonce'], 'em_save' ) ) {
            $title = isset( $_POST['post_title'] ) ? sanitize_text_field( $_POST['post_title'] ) : '';
            $content = isset( $_POST['post_content'] ) ? wp_kses_post( $_POST['post_content'] ) : '';

            $assign_to_user_id = 0;
            if ( $is_admin_view && in_array( $post_type, [ 'tribe_venue', 'tribe_organizer' ], true ) && ! empty( $_POST['assign_to_user'] ) ) {
                $candidate_user_id = intval( $_POST['assign_to_user'] );
                if ( get_userdata( $candidate_user_id ) ) {
                    $assign_to_user_id = $candidate_user_id;
                }
            }

            $featured_limit = open_events_get_featured_limit();
            $wants_featured = $is_admin_view && 'tribe_events' === $post_type && isset( $_POST['is_featured'] );
            $featured_limit_hit = false;
            if ( $wants_featured && $featured_limit > 0 ) {
                $already_featured = 'edit' === $current_action && $edit_post && '1' === get_post_meta( $edit_post_id, '_tribe_featured', true );
                if ( ! $already_featured && open_events_count_featured_events( $edit_post_id ) >= $featured_limit ) {
                    $featured_limit_hit = true;
                }
            }

            if ( empty( $title ) ) {
                $error_msg = esc_html__( 'Il titolo/nome è obbligatorio.', 'open-events' );
            } elseif ( $featured_limit_hit ) {
                $error_msg = sprintf( esc_html__( 'Limite di eventi in primo piano raggiunto (massimo %d). Rimuovi il segno da un altro evento prima di aggiungerne uno nuovo.', 'open-events' ), $featured_limit );
            } else {
                $post_data = [
                    'post_title'   => $title,
                    'post_content' => $content,
                    'post_type'    => $post_type,
                ];

                if ( 'edit' === $current_action ) {
                    $post_data['ID'] = $edit_post_id;
                    if ( ! $is_admin_view ) {
                        // Le modifiche di un utente normale tornano in revisione; l'admin può
                        // modificare un elemento senza fargli perdere lo stato pubblicato.
                        $post_data['post_status'] = ( 'tribe_events' === $post_type ) ? open_events_get_default_event_status() : 'draft';
                    }
                    if ( $assign_to_user_id ) {
                        $post_data['post_author'] = $assign_to_user_id;
                    }
                    $post_id = wp_update_post( $post_data, true );
                } else {
                    $post_data['post_status'] = ( 'tribe_events' === $post_type ) ? open_events_get_default_event_status() : 'draft';
                    $post_data['post_author'] = $assign_to_user_id ? $assign_to_user_id : $current_user_id;
                    $post_id = wp_insert_post( $post_data, true );
                }

                if ( is_wp_error( $post_id ) ) {
                    $error_msg = $post_id->get_error_message();
                } else {
                    if ( 'tribe_events' === $post_type ) {
                        if ( $is_admin_view ) {
                            if ( isset( $_POST['is_featured'] ) ) {
                                update_post_meta( $post_id, '_tribe_featured', '1' );
                            } else {
                                delete_post_meta( $post_id, '_tribe_featured' );
                            }
                        }

                        $is_all_day = isset( $_POST['all_day_event'] ) ? 'yes' : 'no';
                        update_post_meta( $post_id, '_EventAllDay', $is_all_day );

                        $is_recurring = isset( $_POST['recurring_event'] ) ? 'yes' : 'no';
                        update_post_meta( $post_id, '_is_recurring', $is_recurring );
                        update_post_meta( $post_id, '_recurrence_description', sanitize_text_field( $_POST['recurrence_description'] ?? '' ) );

                        if ( ! empty( $_POST['EventStartDate'] ) ) {
                            $start_hour = sanitize_text_field( $_POST['EventStartHour'] ?? '00' );
                            $start_minute = sanitize_text_field( $_POST['EventStartMinute'] ?? '00' );
                            $start_time_val = ( 'yes' === $is_all_day ) ? '00:00:00' : "{$start_hour}:{$start_minute}:00";
                            update_post_meta( $post_id, '_EventStartDate', sanitize_text_field( $_POST['EventStartDate'] ) . ' ' . $start_time_val );
                        }
                        if ( ! empty( $_POST['EventEndDate'] ) ) {
                            $end_hour = sanitize_text_field( $_POST['EventEndHour'] ?? '00' );
                            $end_minute = sanitize_text_field( $_POST['EventEndMinute'] ?? '00' );
                            $end_time_val = ( 'yes' === $is_all_day ) ? '23:59:59' : "{$end_hour}:{$end_minute}:00";
                            update_post_meta( $post_id, '_EventEndDate', sanitize_text_field( $_POST['EventEndDate'] ) . ' ' . $end_time_val );
                        }
                        update_post_meta( $post_id, '_EventCost', sanitize_text_field( $_POST['_EventCost'] ?? '' ) );
                        update_post_meta( $post_id, '_EventURL', esc_url_raw( $_POST['_EventURL'] ?? '' ) );
                        
                        if ( ! empty( $_POST['event_category'] ) ) {
                            $cat_ids = array_map( 'intval', (array) $_POST['event_category'] );
                            wp_set_object_terms( $post_id, $cat_ids, 'tribe_events_cat' );
                        }

                        $venue_id = isset( $_POST['event_venue'] ) ? sanitize_text_field( $_POST['event_venue'] ) : '';
                        if ( '__create_new__' === $venue_id ) {
                            $new_venue_title = isset( $_POST['new_venue_title'] ) ? sanitize_text_field( $_POST['new_venue_title'] ) : '';
                            if ( ! empty( $new_venue_title ) ) {
                                $venue_id = wp_insert_post([
                                    'post_title'   => $new_venue_title,
                                    'post_type'    => 'tribe_venue',
                                    'post_status'  => 'publish',
                                    'post_author'  => $current_user_id,
                                ]);
                                if ( ! is_wp_error( $venue_id ) ) {
                                    update_post_meta( $venue_id, '_VenueAddress', sanitize_text_field( $_POST['new_venue_address'] ?? '' ) );
                                    update_post_meta( $venue_id, '_VenueCity', sanitize_text_field( $_POST['new_venue_city'] ?? '' ) );
                                    update_post_meta( $venue_id, '_VenueZip', sanitize_text_field( $_POST['new_venue_zip'] ?? '' ) );
                                } else {
                                    $venue_id = '';
                                }
                            }
                        }
                        if ( ! empty( $venue_id ) ) {
                            update_post_meta( $post_id, '_EventVenueID', intval( $venue_id ) );
                        }

                        $org_id = isset( $_POST['event_organizer'] ) ? sanitize_text_field( $_POST['event_organizer'] ) : '';
                        if ( '__create_new__' === $org_id ) {
                            $new_org_title = isset( $_POST['new_organizer_title'] ) ? sanitize_text_field( $_POST['new_organizer_title'] ) : '';
                            if ( ! empty( $new_org_title ) ) {
                                $org_id = wp_insert_post([
                                    'post_title'   => $new_org_title,
                                    'post_type'    => 'tribe_organizer',
                                    'post_status'  => 'publish',
                                    'post_author'  => $current_user_id,
                                ]);
                                if ( ! is_wp_error( $org_id ) ) {
                                    update_post_meta( $org_id, '_OrganizerPhone', sanitize_text_field( $_POST['new_organizer_phone'] ?? '' ) );
                                    update_post_meta( $org_id, '_OrganizerEmail', sanitize_email( $_POST['new_organizer_email'] ?? '' ) );
                                    update_post_meta( $org_id, '_OrganizerWebsite', esc_url_raw( $_POST['new_organizer_website'] ?? '' ) );

                                    if ( ! empty( $_FILES['new_organizer_logo']['name'] ) ) {
                                        require_once( ABSPATH . 'wp-admin/includes/image.php' );
                                        require_once( ABSPATH . 'wp-admin/includes/file.php' );
                                        require_once( ABSPATH . 'wp-admin/includes/media.php' );
                                        $new_org_logo_id = media_handle_upload( 'new_organizer_logo', $org_id );
                                        if ( ! is_wp_error( $new_org_logo_id ) ) {
                                            set_post_thumbnail( $org_id, $new_org_logo_id );
                                        }
                                    }
                                } else {
                                    $org_id = '';
                                }
                            }
                        }
                        if ( ! empty( $org_id ) ) {
                            update_post_meta( $post_id, '_EventOrganizerID', intval( $org_id ) );
                        }

                        // wp_insert_post()/wp_update_post() lanciano save_post_tribe_events
                        // SUBITO, prima che i update_post_meta() qui sopra scrivano data/ora/
                        // luogo/organizzatore. The Events Calendar sincronizza le sue tabelle
                        // interne (occorrenze) proprio agganciandosi a quell'hook: se scatta a
                        // meta ancora vuoti, l'evento resta "non pronto" per TEC anche se
                        // post_status in wp_posts è corretto — invisibile sia in anteprima che
                        // pubblicato, finché qualcuno non lo risalva da wp-admin (dove il
                        // metabox nativo scrive i meta PRIMA del save). Rilanciamo qui gli
                        // stessi hook ora che i meta sono completi, cosi' TEC si allinea subito.
                        $synced_event_post = get_post( $post_id );
                        do_action( 'save_post_tribe_events', $post_id, $synced_event_post, 'edit' === $current_action );

                        // Calcola i meta UTC/timezone/durata richiesti dalle custom tables di
                        // TEC 6 e forza la ricostruzione di evento + occorrenze. Senza questo
                        // passaggio l'evento non genera alcuna occorrenza e resta invisibile
                        // nel calendario pubblico anche dopo la pubblicazione.
                        open_events_sync_event_custom_tables( $post_id );

                        if ( ! empty( $_FILES['logo_image']['name'] ) ) {
                            require_once( ABSPATH . 'wp-admin/includes/image.php' );
                            require_once( ABSPATH . 'wp-admin/includes/file.php' );
                            require_once( ABSPATH . 'wp-admin/includes/media.php' );

                            $attachment_id = media_handle_upload( 'logo_image', $post_id );
                            if ( ! is_wp_error( $attachment_id ) ) {
                                set_post_thumbnail( $post_id, $attachment_id );
                            }
                        }

                        // Evento con più date: $post_id copre già la prima data; per le
                        // altre cloniamo titolo/descrizione/luogo/organizzatore/categoria/
                        // immagine come nuovi eventi indipendenti, stessi orari, data
                        // diversa — collegati da _oe_series_id. Alla creazione parte
                        // sempre da zero; in modifica si può ancora convertire un evento
                        // singolo in ricorrente (aggiungendo le date mancanti), ma MAI
                        // rigenerare una serie che esiste già, altrimenti ogni salvataggio
                        // clonerebbe di nuovo tutte le date.
                        $editing_existing_series = 'edit' === $current_action && $edit_post && get_post_meta( $edit_post->ID, '_oe_series_id', true );
                        if ( ! $editing_existing_series && ! empty( $_POST['em_series_dates'] ) ) {
                            $series_dates = json_decode( stripslashes( $_POST['em_series_dates'] ), true );
                            if ( is_array( $series_dates ) ) {
                                $active_dates = array_values( array_filter( $series_dates, function( $d ) {
                                    return empty( $d['excluded'] ) && ! empty( $d['date'] );
                                } ) );
                                usort( $active_dates, function( $a, $b ) {
                                    return strcmp( $a['date'], $b['date'] );
                                } );

                                if ( $active_dates ) {
                                    $start_tod = ( 'yes' === $is_all_day ) ? '00:00:00' : sprintf(
                                        '%s:%s:00',
                                        sanitize_text_field( $_POST['EventStartHour'] ?? '00' ),
                                        sanitize_text_field( $_POST['EventStartMinute'] ?? '00' )
                                    );
                                    $end_tod = ( 'yes' === $is_all_day ) ? '23:59:59' : sprintf(
                                        '%s:%s:00',
                                        sanitize_text_field( $_POST['EventEndHour'] ?? '00' ),
                                        sanitize_text_field( $_POST['EventEndMinute'] ?? '00' )
                                    );

                                    // La prima data in ordine cronologico è sempre quella del
                                    // post corrente: la fissiamo qui invece di fidarci del campo
                                    // "Data Inizio" perché con "Date libere" quel campo è
                                    // indipendente dall'elenco date e poteva disallinearsi,
                                    // facendo sparire la prima data della serie.
                                    $primary_date = sanitize_text_field( $active_dates[0]['date'] );
                                    update_post_meta( $post_id, '_EventStartDate', $primary_date . ' ' . $start_tod );
                                    update_post_meta( $post_id, '_EventEndDate', $primary_date . ' ' . $end_tod );
                                    do_action( 'save_post_tribe_events', $post_id, get_post( $post_id ), true );
                                    open_events_sync_event_custom_tables( $post_id );
                                }

                                $extra_dates = array_slice( $active_dates, 1 );

                                if ( $extra_dates ) {
                                    update_post_meta( $post_id, '_oe_series_id', $post_id );
                                    $primary_author = get_post_field( 'post_author', $post_id );

                                    foreach ( $extra_dates as $d ) {
                                        $clone_date = sanitize_text_field( $d['date'] );
                                        if ( ! $clone_date ) {
                                            continue;
                                        }

                                        $clone_id = wp_insert_post( [
                                            'post_title'   => $title,
                                            'post_content' => $content,
                                            'post_type'    => 'tribe_events',
                                            'post_status'  => get_post_status( $post_id ),
                                            'post_author'  => $primary_author,
                                        ], true );
                                        if ( is_wp_error( $clone_id ) ) {
                                            continue;
                                        }

                                        update_post_meta( $clone_id, '_EventAllDay', $is_all_day );
                                        update_post_meta( $clone_id, '_EventStartDate', $clone_date . ' ' . $start_tod );
                                        update_post_meta( $clone_id, '_EventEndDate', $clone_date . ' ' . $end_tod );
                                        update_post_meta( $clone_id, '_EventCost', get_post_meta( $post_id, '_EventCost', true ) );
                                        update_post_meta( $clone_id, '_EventURL', get_post_meta( $post_id, '_EventURL', true ) );
                                        update_post_meta( $clone_id, '_oe_series_id', $post_id );
                                        if ( ! empty( $venue_id ) ) {
                                            update_post_meta( $clone_id, '_EventVenueID', intval( $venue_id ) );
                                        }
                                        if ( ! empty( $org_id ) ) {
                                            update_post_meta( $clone_id, '_EventOrganizerID', intval( $org_id ) );
                                        }
                                        if ( ! empty( $cat_ids ) ) {
                                            wp_set_object_terms( $clone_id, $cat_ids, 'tribe_events_cat' );
                                        }
                                        if ( isset( $attachment_id ) && ! is_wp_error( $attachment_id ) ) {
                                            set_post_thumbnail( $clone_id, $attachment_id );
                                        } elseif ( has_post_thumbnail( $post_id ) ) {
                                            // Conversione in ricorrente durante una modifica: se non è
                                            // stata caricata una nuova immagine in questo salvataggio,
                                            // le nuove date clonate ereditano quella già presente
                                            // sull'evento originale invece di restare senza copertina.
                                            set_post_thumbnail( $clone_id, get_post_thumbnail_id( $post_id ) );
                                        }

                                        do_action( 'save_post_tribe_events', $clone_id, get_post( $clone_id ), false );
                                        open_events_sync_event_custom_tables( $clone_id );
                                    }
                                }
                            }
                        }

                        // Notifica di revisione all'admin: template configurabile in
                        // Open Events → Community (vedi community-emails.php).
                        do_action( 'oe_community_event_submitted', $post_id, $title, $current_user->display_name );

                    } elseif ( 'tribe_organizer' === $post_type ) {
                        update_post_meta( $post_id, '_OrganizerPhone', sanitize_text_field( $_POST['_OrganizerPhone'] ?? '' ) );
                        update_post_meta( $post_id, '_OrganizerWebsite', esc_url_raw( $_POST['_OrganizerWebsite'] ?? '' ) );
                        update_post_meta( $post_id, '_OrganizerEmail', sanitize_email( $_POST['_OrganizerEmail'] ?? '' ) );

                        if ( ! empty( $_FILES['logo_image']['name'] ) ) {
                            require_once( ABSPATH . 'wp-admin/includes/image.php' );
                            require_once( ABSPATH . 'wp-admin/includes/file.php' );
                            require_once( ABSPATH . 'wp-admin/includes/media.php' );

                            $attachment_id = media_handle_upload( 'logo_image', $post_id );
                            if ( ! is_wp_error( $attachment_id ) ) {
                                set_post_thumbnail( $post_id, $attachment_id );
                            }
                        }
                    } else {
                        update_post_meta( $post_id, '_VenueAddress', sanitize_text_field( $_POST['_VenueAddress'] ?? '' ) );
                        update_post_meta( $post_id, '_VenueCity', sanitize_text_field( $_POST['_VenueCity'] ?? '' ) );
                        update_post_meta( $post_id, '_VenueCountry', sanitize_text_field( $_POST['_VenueCountry'] ?? '' ) );
                        update_post_meta( $post_id, '_VenueZip', sanitize_text_field( $_POST['_VenueZip'] ?? '' ) );
                    }

                    $redirect_target = '';
                    if ( ! empty( $redirect ) ) {
                        $redirect_target = $redirect;
                    } elseif ( 'hub' === $action_mode ) {
                        $redirect_target = remove_query_arg( [ 'edit_id', 'action', 'type' ] );
                    } elseif ( 'dashboard' === $action_mode ) {
                        $redirect_target = remove_query_arg( [ 'edit_id', 'action', 'type' ] );
                    }

                    // "Evento Consigliato" (Stripe): se spuntato e non già attivo/in
                    // attesa, verifica lo slot settimanale e avvia il pagamento.
                    // Riusa $redirect_target come destinazione di ritorno dopo
                    // Stripe (o la pagina corrente se non ce n'era una), poi lo
                    // sovrascrive con l'URL della Checkout Session cosi' il blocco
                    // di redirect qui sotto — già esistente — ci porta l'utente
                    // senza bisogno di una nuova logica di redirect.
                    if ( 'tribe_events' === $post_type && isset( $_POST['want_featured'] )
                        && ! in_array( get_post_meta( $post_id, '_illi_featured_status', true ), [ 'paid', 'pending_payment' ], true ) ) {
                        $return_to = ! empty( $redirect_target ) ? $redirect_target : remove_query_arg( [ 'edit_id', 'action', 'type' ] );
                        $checkout_result = open_events_featured_start_checkout_flow( $post_id, $return_to );

                        if ( is_wp_error( $checkout_result ) ) {
                            // Forza la visualizzazione del messaggio d'errore invece di un
                            // eventuale redirect automatico già previsto (hub/dashboard),
                            // che altrimenti lo farebbe sparire senza che l'utente lo veda.
                            $redirect_target = '';
                            $error_msg = sprintf( esc_html__( 'Evento salvato, ma non è stato possibile avviare il pagamento per "Consigliato": %s', 'open-events' ), $checkout_result->get_error_message() );
                        } else {
                            $redirect_target = $checkout_result;
                        }
                    }

                    if ( ! empty( $redirect_target ) ) {
                        echo '<script type="text/javascript">window.location.href = "' . esc_url_raw( $redirect_target ) . '";</script>';
                        echo '<div class="em-form-container em-form-view"><div class="em-alert success">' . esc_html__( 'Salvataggio completato! Reindirizzamento in corso...', 'open-events' ) . '</div></div>';
                        if ( $show_sidebar ) {
                            echo '</div></div>';
                        }
                        return;
                    }

                    $success_msg = 'edit' === $current_action ? esc_html__( 'Aggiornato con successo!', 'open-events' ) : esc_html__( 'Creato con successo!', 'open-events' );
                    if ( 'edit' === $current_action ) {
                        $edit_post = get_post( $edit_post_id );
                    }
                }
            }
        }

        $categories = get_terms( [ 'taxonomy' => 'tribe_events_cat', 'hide_empty' => false ] );
        $venue_query_args = [ 'post_type' => 'tribe_venue', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ];
        // Opzione "Visibilità luoghi": se impostata su "solo i propri", l'utente
        // normale vede nel menu solo i luoghi che ha inserito lui. L'admin vede
        // sempre tutti i luoghi.
        if ( ! $is_admin_view && 'own' === open_events_get_venue_visibility() ) {
            $venue_query_args['author'] = $current_user_id;
        }
        $venues = get_posts( $venue_query_args );
        $organizer_query_args = [ 'post_type' => 'tribe_organizer', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ];
        // L'admin vede sempre tutti gli organizzatori, l'utente normale solo i propri.
        if ( ! $is_admin_view ) {
            $organizer_query_args['author'] = $current_user_id;
        }
        $organizers = get_posts( $organizer_query_args );

        $form_list_url = remove_query_arg( [ 'edit_id', 'action', 'type' ] );
        $form_back_label = ! empty( $label_plural ) ? $label_plural : esc_html__( 'Dashboard', 'open-events' );

        include OPEN_EVENTS_PLUGIN_DIR . 'includes/events-manager/templates/event-form.php';

        if ( $show_sidebar ) {
            echo '</div></div>';
        }
    }

    protected function content_template() {
        ?>
        <div class="em-form-container">
            <h3>Front-end Events Manager (Editor Preview)</h3>
            <p><strong>Modalità:</strong> {{{ settings.action_mode === 'hub' ? 'Portale Completo' : 'Dashboard Singola' }}}</p>
            <p><em>La dashboard reale, i pulsanti personalizzati e il logout sono attivi solo nel front-end per gli utenti loggati.</em></p>
        </div>
        <?php
    }
}
