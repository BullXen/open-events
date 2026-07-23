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
            'home'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>',
            'exit'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
            'link'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>',
            'save'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>',
            'upload'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>',
            'star'     => '<svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        ];

        return $icons[ $key ] ?? '';
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
            'profile'         => [ 'label' => esc_html__( 'Profilo', 'open-events' ), 'icon' => 'person' ],
        ];
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
            if ( ! in_array( $post_type, [ 'tribe_events', 'tribe_organizer', 'tribe_venue', 'profile' ] ) ) {
                $post_type = '';
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
                if ( 'delete' === $_GET['em_action'] && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'em_delete_' . $target_id ) ) {
                    wp_trash_post( $target_id );
                    echo '<script type="text/javascript">window.location.href = "' . esc_url_raw( $redirect_back ) . '";</script>';
                    return;
                } elseif ( 'publish' === $_GET['em_action'] && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'em_publish_' . $target_id ) ) {
                    wp_update_post( [ 'ID' => $target_id, 'post_status' => 'publish' ] );
                    echo '<script type="text/javascript">window.location.href = "' . esc_url_raw( $redirect_back ) . '";</script>';
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
            ?>
            <div class="em-form-container em-form-view">
                <?php
                $this->render_breadcrumbs( [
                    [ 'label' => esc_html__( 'Dashboard', 'open-events' ), 'url' => remove_query_arg( [ 'view' ] ) ],
                    [ 'label' => esc_html__( 'Profilo', 'open-events' ), 'url' => '' ],
                ] );
                ?>
                <div class="em-back-link">
                    <a href="<?php echo esc_url( remove_query_arg( [ 'view' ] ) ); ?>">← <?php esc_html_e( 'Torna alla Dashboard', 'open-events' ); ?></a>
                </div>

                <h2><?php esc_html_e( 'Modifica Profilo', 'open-events' ); ?></h2>

                <?php if ( $profile_success ): ?>
                    <div class="em-alert success"><?php echo esc_html( $profile_success ); ?></div>
                <?php endif; ?>
                <?php if ( $profile_error ): ?>
                    <div class="em-alert error"><?php echo esc_html( $profile_error ); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?php wp_nonce_field( 'profile_save', 'profile_nonce' ); ?>
                    <input type="hidden" name="profile_submit" value="1">

                    <div class="em-form-group">
                        <label><?php esc_html_e( 'Username (non modificabile)', 'open-events' ); ?></label>
                        <input type="text" value="<?php echo esc_attr( $current_user->user_login ); ?>" disabled style="background-color:#f0f2f5;">
                    </div>

                    <div class="em-form-group">
                        <label><?php esc_html_e( 'Nome', 'open-events' ); ?></label>
                        <input type="text" name="first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>">
                    </div>

                    <div class="em-form-group">
                        <label><?php esc_html_e( 'Cognome', 'open-events' ); ?></label>
                        <input type="text" name="last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>">
                    </div>

                    <div class="em-form-group">
                        <label><?php esc_html_e( 'Email', 'open-events' ); ?></label>
                        <input type="email" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
                    </div>

                    <div class="em-form-group">
                        <label><?php esc_html_e( 'Sito Web', 'open-events' ); ?></label>
                        <input type="url" name="website" value="<?php echo esc_attr( $current_user->user_url ); ?>">
                    </div>

                    <div class="em-form-group">
                        <label><?php esc_html_e( 'Nuova Password (lascia vuoto per non cambiare)', 'open-events' ); ?></label>
                        <input type="password" name="pass1" autocomplete="new-password">
                    </div>

                    <div class="em-form-group">
                        <label><?php esc_html_e( 'Conferma Nuova Password', 'open-events' ); ?></label>
                        <input type="password" name="pass2" autocomplete="new-password">
                    </div>

                    <button type="submit" class="em-submit-btn">
                        <?php esc_html_e( 'Salva Profilo', 'open-events' ); ?>
                    </button>
                </form>
            </div>
            <?php
            if ( $show_sidebar ) {
                echo '</div></div>';
            }
            return;
        }

        // Handle Portal Hub Home
        if ( 'hub' === $action_mode && empty( $post_type ) ) {
            ?>
            <div class="em-form-container em-hub-view">
                <div class="em-hub-header">
                    <div class="em-hub-user">
                        <?php echo get_avatar( $current_user_id, 48 ); ?>
                        <div class="em-hub-user-meta">
                            <h3><?php printf( esc_html__( 'Ciao, %s!', 'open-events' ), esc_html( $current_user->display_name ) ); ?></h3>
                            <p><?php esc_html_e( 'Benvenuto nella tua Dashboard', 'open-events' ); ?></p>
                        </div>
                    </div>
                </div>

                <div class="em-hub-grid">
                    <!-- I Miei Eventi -->
                    <a href="<?php echo esc_url( add_query_arg( 'view', 'tribe_events' ) ); ?>" class="em-hub-card em-events-card">
                        <div class="em-card-icon">
                            <?php $this->render_icon( 'calendar' ); ?>
                        </div>
                        <h4><?php esc_html_e( 'I Miei Eventi', 'open-events' ); ?></h4>
                        <p><?php esc_html_e( 'Crea e gestisci i tuoi eventi del calendario, date e descrizioni.', 'open-events' ); ?></p>
                        <span class="em-card-btn"><?php esc_html_e( 'Accedi', 'open-events' ); ?> &rarr;</span>
                    </a>

                    <!-- I Miei Luoghi -->
                    <a href="<?php echo esc_url( add_query_arg( 'view', 'tribe_venue' ) ); ?>" class="em-hub-card">
                        <div class="em-card-icon">
                            <?php $this->render_icon( 'map-pin' ); ?>
                        </div>
                        <h4><?php esc_html_e( 'I Miei Luoghi', 'open-events' ); ?></h4>
                        <p><?php esc_html_e( 'Gestisci indirizzi, città, CAP e dettagli dei tuoi luoghi.', 'open-events' ); ?></p>
                        <span class="em-card-btn"><?php esc_html_e( 'Accedi', 'open-events' ); ?> &rarr;</span>
                    </a>

                    <!-- I Miei Organizzatori -->
                    <a href="<?php echo esc_url( add_query_arg( 'view', 'tribe_organizer' ) ); ?>" class="em-hub-card">
                        <div class="em-card-icon">
                            <?php $this->render_icon( 'person' ); ?>
                        </div>
                        <h4><?php esc_html_e( 'I Miei Organizzatori', 'open-events' ); ?></h4>
                        <p><?php esc_html_e( 'Gestisci dettagli dei tuoi organizzatori, telefono, sito web e loghi.', 'open-events' ); ?></p>
                        <span class="em-card-btn"><?php esc_html_e( 'Accedi', 'open-events' ); ?> &rarr;</span>
                    </a>

                    <?php if ( ! empty( $settings['custom_buttons'] ) ) : ?>
                        <?php foreach ( $settings['custom_buttons'] as $item ) : 
                            $target = $item['button_link']['is_external'] ? ' target="_blank"' : '';
                            $nofollow = $item['button_link']['nofollow'] ? ' rel="nofollow"' : '';
                            ?>
                            <a href="<?php echo esc_url( $item['button_link']['url'] ); ?>" class="em-hub-card em-custom-card"<?php echo $target . $nofollow; ?>>
                                <div class="em-card-icon">
                                    <?php if ( ! empty( $item['button_icon']['value'] ) ) : ?>
                                        <?php \Elementor\Icons_Manager::render_icon( $item['button_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                    <?php else: ?>
                                        <?php $this->render_icon( 'link' ); ?>
                                    <?php endif; ?>
                                </div>
                                <h4><?php echo esc_html( $item['button_text'] ); ?></h4>
                                <p><?php echo esc_html( $item['button_desc'] ); ?></p>
                                <span class="em-card-btn"><?php esc_html_e( 'Apri', 'open-events' ); ?> &rarr;</span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php
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

            if ( 'tribe_events' === $post_type ) {
                usort( $user_posts, function( $a, $b ) {
                    $a_featured = '1' === get_post_meta( $a->ID, '_tribe_featured', true ) ? 1 : 0;
                    $b_featured = '1' === get_post_meta( $b->ID, '_tribe_featured', true ) ? 1 : 0;
                    if ( $a_featured !== $b_featured ) {
                        return $b_featured - $a_featured;
                    }
                    return strtotime( $b->post_date ) - strtotime( $a->post_date );
                } );
            }
            ?>
            <div class="em-form-container em-dashboard-view">
                <?php if ( $show_sidebar ) : ?>
                    <?php
                    $this->render_breadcrumbs( [
                        [ 'label' => esc_html__( 'Dashboard', 'open-events' ), 'url' => remove_query_arg( [ 'view', 'edit_id', 'action', 'type' ] ) ],
                        [ 'label' => $label_plural, 'url' => '' ],
                    ] );
                    ?>
                <?php endif; ?>
                <div class="em-back-link">
                    <?php if ( 'hub' === $action_mode ) : ?>
                        <a href="<?php echo esc_url( remove_query_arg( [ 'view', 'edit_id', 'action', 'type' ] ) ); ?>">← <?php esc_html_e( 'Torna alla Dashboard', 'open-events' ); ?></a>
                    <?php endif; ?>
                </div>

                <div class="em-dashboard-header">
                    <h2><?php echo esc_html( $label_plural ); ?></h2>
                    <a href="<?php echo esc_url( add_query_arg( [ 'action' => 'add', 'type' => $post_type ] ) ); ?>" class="em-submit-btn link-btn">
                        + <?php printf( esc_html__( 'Nuovo %s', 'open-events' ), $label_singular ); ?>
                    </a>
                </div>

                <?php if ( empty( $user_posts ) ): ?>
                    <div class="em-empty-state">
                        <?php $this->render_icon( $label_icon ); ?>
                        <p class="em-empty-msg"><?php printf( esc_html__( 'Non hai ancora creato nessun %s.', 'open-events' ), strtolower( $label_singular ) ); ?></p>
                    </div>
                <?php else: ?>
                    <div class="em-items-list">
                        <?php foreach ( $user_posts as $p ):
                            $thumb_url = get_the_post_thumbnail_url( $p->ID, 'thumbnail' );
                            $meta_line = '';

                            if ( 'tribe_events' === $post_type ) {
                                $start_date = get_post_meta( $p->ID, '_EventStartDate', true );
                                if ( $start_date ) {
                                    $meta_line = date_i18n( get_option( 'date_format' ) . ' - H:i', strtotime( $start_date ) );
                                }
                            } elseif ( 'tribe_venue' === $post_type ) {
                                $address = get_post_meta( $p->ID, '_VenueAddress', true );
                                $city    = get_post_meta( $p->ID, '_VenueCity', true );
                                $meta_line = trim( implode( ', ', array_filter( [ $address, $city ] ) ) );
                            } else {
                                $meta_line = get_post_meta( $p->ID, '_OrganizerEmail', true );
                            }
                            ?>
                            <div class="em-item-row">
                                <div class="em-item-thumb">
                                    <?php if ( $thumb_url ) : ?>
                                        <img src="<?php echo esc_url( $thumb_url ); ?>" alt="">
                                    <?php else : ?>
                                        <?php $this->render_icon( $label_icon ); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="em-item-info">
                                    <strong class="em-item-title">
                                        <?php echo esc_html( $p->post_title ); ?>
                                        <?php if ( 'tribe_events' === $post_type && '1' === get_post_meta( $p->ID, '_tribe_featured', true ) ) : ?>
                                            <span class="em-featured-badge"><?php $this->render_icon( 'star' ); ?> <?php echo esc_html( open_events_get_featured_label() ); ?></span>
                                        <?php endif; ?>
                                    </strong>
                                    <?php if ( $meta_line || $is_admin_view ) : ?>
                                        <span class="em-item-meta">
                                            <?php echo esc_html( $meta_line ); ?>
                                            <?php if ( $is_admin_view ) :
                                                $post_author_data = get_userdata( $p->post_author );
                                                ?>
                                                <?php echo $meta_line ? ' · ' : ''; ?><?php printf( esc_html__( 'di %1$s (%2$s)', 'open-events' ), esc_html( $post_author_data ? $post_author_data->display_name : esc_html__( 'Sconosciuto', 'open-events' ) ), esc_html( $post_author_data ? '@' . $post_author_data->user_login : '—' ) ); ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="em-status-badge <?php echo esc_attr( $p->post_status ); ?>"><?php echo esc_html( get_post_status_object( $p->post_status )->label ); ?></span>
                                <a href="<?php echo esc_url( add_query_arg( 'edit_id', $p->ID ) ); ?>" class="em-action-btn edit-btn">
                                    <?php esc_html_e( 'Modifica', 'open-events' ); ?>
                                </a>
                                <a href="<?php echo esc_url( 'publish' === $p->post_status ? get_permalink( $p->ID ) : get_preview_post_link( $p ) ); ?>" class="em-action-btn preview-btn" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e( 'Anteprima', 'open-events' ); ?>
                                </a>
                                <?php if ( $is_admin_view ) : ?>
                                    <?php if ( 'publish' !== $p->post_status ) : ?>
                                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'em_action' => 'publish', 'post_id' => $p->ID ] ), 'em_publish_' . $p->ID ) ); ?>" class="em-action-btn publish-btn" onclick="return confirm('<?php echo esc_js( __( 'Pubblicare questo elemento online?', 'open-events' ) ); ?>');">
                                            <?php esc_html_e( 'Pubblica', 'open-events' ); ?>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'em_action' => 'delete', 'post_id' => $p->ID ] ), 'em_delete_' . $p->ID ) ); ?>" class="em-action-btn delete-btn" onclick="return confirm('<?php echo esc_js( __( 'Eliminare questo elemento? Verrà spostato nel cestino.', 'open-events' ) ); ?>');">
                                        <?php esc_html_e( 'Elimina', 'open-events' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
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
        }

        $success_msg = '';
        $error_msg = '';

        if ( isset( $_POST['action_submit'] ) && wp_verify_nonce( $_POST['em_nonce'], 'em_save' ) ) {
            $title = isset( $_POST['post_title'] ) ? sanitize_text_field( $_POST['post_title'] ) : '';
            $content = isset( $_POST['post_content'] ) ? wp_kses_post( $_POST['post_content'] ) : '';

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
                    $post_id = wp_update_post( $post_data, true );
                } else {
                    $post_data['post_status'] = ( 'tribe_events' === $post_type ) ? open_events_get_default_event_status() : 'draft';
                    $post_data['post_author'] = $current_user_id;
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

                        if ( ! empty( $_FILES['logo_image']['name'] ) ) {
                            require_once( ABSPATH . 'wp-admin/includes/image.php' );
                            require_once( ABSPATH . 'wp-admin/includes/file.php' );
                            require_once( ABSPATH . 'wp-admin/includes/media.php' );

                            $attachment_id = media_handle_upload( 'logo_image', $post_id );
                            if ( ! is_wp_error( $attachment_id ) ) {
                                set_post_thumbnail( $post_id, $attachment_id );
                            }
                        }

                        $admin_email = get_option( 'admin_email' );
                        $subject = sprintf( esc_html__( 'Nuovo Evento Inserito: %s', 'open-events' ), $title );
                        $body = sprintf( "Un nuovo evento è stato inserito sul portale ed è in attesa di revisione.\n\nTitolo: %s\nAutore: %s\nData Inizio: %s\nData Fine: %s\n\nPuoi revisionarlo qui: %s", 
                            $title, 
                            $current_user->display_name, 
                            sanitize_text_field( $_POST['EventStartDate'] ), 
                            sanitize_text_field( $_POST['EventEndDate'] ),
                            admin_url( 'post.php?post=' . $post_id . '&action=edit' )
                        );
                        wp_mail( $admin_email, $subject, $body );

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
        $venues = get_posts( [ 'post_type' => 'tribe_venue', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        $organizers = get_posts( [ 'post_type' => 'tribe_organizer', 'posts_per_page' => -1, 'post_status' => 'publish', 'author' => $current_user_id ] );

        $form_list_url = remove_query_arg( [ 'edit_id', 'action', 'type' ] );
        $form_back_label = ! empty( $label_plural ) ? $label_plural : esc_html__( 'Dashboard', 'open-events' );
        ?>
        <div class="em-form-container em-form-view">
            <?php if ( $show_sidebar ) : ?>
                <?php
                $this->render_breadcrumbs( [
                    [ 'label' => esc_html__( 'Dashboard', 'open-events' ), 'url' => remove_query_arg( [ 'view', 'edit_id', 'action', 'type' ] ) ],
                    [ 'label' => $label_plural, 'url' => $form_list_url ],
                    [
                        'label' => 'edit' === $current_action
                            ? sprintf( esc_html__( 'Modifica %s', 'open-events' ), $label_singular )
                            : sprintf( esc_html__( 'Nuovo %s', 'open-events' ), $label_singular ),
                        'url'   => '',
                    ],
                ] );
                ?>
            <?php endif; ?>
            <div class="em-back-link">
                <a href="<?php echo esc_url( $form_list_url ); ?>">← <?php printf( esc_html__( 'Torna a %s', 'open-events' ), $form_back_label ); ?></a>
            </div>

            <h2 class="em-section-title">
                <?php echo 'edit' === $current_action ? esc_html__( 'Modifica Evento / Contenuto', 'open-events' ) : esc_html__( 'Inserisci Nuovo Evento', 'open-events' ); ?>
            </h2>
            <p class="em-section-subtitle"><?php esc_html_e( 'Compila tutti i dettagli relativi al tuo evento per pubblicarlo sul portale.', 'open-events' ); ?></p>

            <?php if ( $success_msg ): ?>
                <div class="em-alert success"><?php echo esc_html( $success_msg ); ?></div>
            <?php endif; ?>
            <?php if ( $error_msg ): ?>
                <div class="em-alert error"><?php echo esc_html( $error_msg ); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="em-modern-form">
                <?php wp_nonce_field( 'em_save', 'em_nonce' ); ?>
                <input type="hidden" name="action_submit" value="1">

                <div class="em-form-section">
                    <h3 class="em-form-section-title"><?php esc_html_e( '1. Informazioni Base', 'open-events' ); ?></h3>
                    
                    <div class="em-form-group">
                        <label>
                            <?php 
                            if ( 'tribe_events' === $post_type ) {
                                esc_html_e( 'Titolo Evento *', 'open-events' );
                            } elseif ( 'tribe_organizer' === $post_type ) {
                                esc_html_e( 'Nome Organizzatore *', 'open-events' );
                            } else {
                                esc_html_e( 'Nome Luogo *', 'open-events' );
                            }
                            ?>
                        </label>
                        <input type="text" name="post_title" required value="<?php echo esc_attr( $edit_post ? $edit_post->post_title : '' ); ?>" placeholder="<?php esc_attr_e( 'Es. Concerto sotto le stelle, Aperitivo al Castello...', 'open-events' ); ?>">
                    </div>

                    <?php if ( 'tribe_events' === $post_type && $is_admin_view ):
                        $is_featured_val = $edit_post ? get_post_meta( $edit_post->ID, '_tribe_featured', true ) : '';
                        $featured_limit_display = open_events_get_featured_limit();
                        ?>
                        <div class="em-form-group em-featured-checkbox-group">
                            <label class="em-checkbox-label">
                                <input type="checkbox" name="is_featured" value="yes" <?php checked( $is_featured_val, '1' ); ?>>
                                <span><?php esc_html_e( 'Evento in Primo Piano (resta in cima all\'elenco eventi)', 'open-events' ); ?></span>
                            </label>
                            <?php if ( $featured_limit_display > 0 ): ?>
                                <small class="em-field-help"><?php printf( esc_html__( 'Massimo %d eventi contemporaneamente in primo piano (modificabile nelle impostazioni del plugin).', 'open-events' ), $featured_limit_display ); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( 'tribe_events' === $post_type && ! is_wp_error( $categories ) && ! empty( $categories ) ): ?>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'Categoria Evento *', 'open-events' ); ?></label>
                            <select name="event_category[]" required multiple class="em-form-select em-category-select" style="height: auto; min-height: 120px;">
                                <?php
                                $current_cats = $edit_post ? wp_get_object_terms( $edit_post->ID, 'tribe_events_cat', [ 'fields' => 'ids' ] ) : [];
                                foreach ( $categories as $cat ): ?>
                                    <option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php echo in_array( $cat->term_id, $current_cats ) ? 'selected' : ''; ?>>
                                        <?php echo esc_html( $cat->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="em-field-help"><?php esc_html_e( 'Cerca e clicca per aggiungere una categoria. Clicca la × su un tag per rimuoverla.', 'open-events' ); ?></small>
                        </div>
                    <?php endif; ?>

                    <div class="em-form-group">
                        <label><?php esc_html_e( 'Descrizione Evento *', 'open-events' ); ?></label>
                        <?php
                        $content_value = $edit_post ? $edit_post->post_content : '';
                        if ( 'classic' === open_events_get_description_editor_mode() ) :
                            ?>
                            <textarea name="post_content" rows="8" class="em-form-textarea"><?php echo esc_textarea( $content_value ); ?></textarea>
                            <?php
                        else :
                            wp_editor( $content_value, 'post_content', [
                                'media_buttons' => false,
                                'textarea_rows' => 8,
                                'teeny'         => true,
                                'quicktags'     => true,
                            ] );
                        endif;
                        ?>
                    </div>
                </div>

                <?php if ( 'tribe_events' === $post_type ): 
                    $start_full = $edit_post ? get_post_meta( $edit_post->ID, '_EventStartDate', true ) : '';
                    $end_full = $edit_post ? get_post_meta( $edit_post->ID, '_EventEndDate', true ) : '';
                    $is_all_day_val = $edit_post ? get_post_meta( $edit_post->ID, '_EventAllDay', true ) : '';
                    $is_recurring_val = $edit_post ? get_post_meta( $edit_post->ID, '_is_recurring', true ) : '';
                    $recurrence_desc_val = $edit_post ? get_post_meta( $edit_post->ID, '_recurrence_description', true ) : '';

                    $start_parts = explode( ' ', $start_full );
                    $end_parts = explode( ' ', $end_full );
                    $start_date = $start_parts[0] ?? '';
                    $start_time = isset( $start_parts[1] ) ? substr($start_parts[1], 0, 5) : '';
                    $end_date = $end_parts[0] ?? '';
                    $end_time = isset( $end_parts[1] ) ? substr($end_parts[1], 0, 5) : '';

                    $start_hour = '08';
                    $start_min = '00';
                    if ( ! empty( $start_time ) && strpos( $start_time, ':' ) !== false ) {
                        list( $start_hour, $start_min ) = explode( ':', $start_time );
                    }

                    $end_hour = '17';
                    $end_min = '00';
                    if ( ! empty( $end_time ) && strpos( $end_time, ':' ) !== false ) {
                        list( $end_hour, $end_min ) = explode( ':', $end_time );
                    }

                    $hours_options = [];
                    for ( $i = 0; $i < 24; $i++ ) {
                        $val = str_pad( $i, 2, '0', STR_PAD_LEFT );
                        $hours_options[] = $val;
                    }
                    $minutes_options = [ '00', '15', '30', '45' ];
                    ?>
                    <div class="em-form-section">
                        <h3 class="em-form-section-title"><?php esc_html_e( '2. Date e Orari', 'open-events' ); ?></h3>
                        
                        <div class="em-form-row-grid">
                            <div class="em-form-group em-all-day-checkbox-group">
                                <label class="em-checkbox-label">
                                    <input type="checkbox" name="all_day_event" class="em-all-day-switch" value="yes" <?php checked( $is_all_day_val, 'yes' ); ?>>
                                    <span><?php esc_html_e( 'Evento Giornaliero (Tutto il giorno)', 'open-events' ); ?></span>
                                </label>
                            </div>

                            <div class="em-form-group em-recurring-checkbox-group">
                                <label class="em-checkbox-label">
                                    <input type="checkbox" name="recurring_event" class="em-recurring-switch" value="yes" <?php checked( $is_recurring_val, 'yes' ); ?>>
                                    <span><?php esc_html_e( 'Evento Ricorrente', 'open-events' ); ?></span>
                                </label>
                            </div>
                        </div>

                        <div class="em-recurring-wrapper em-hidden" id="em-recurring-details">
                            <div class="em-recurrence-rule">
                                <div class="em-recurrence-rule-row">
                                    <span class="em-label"><?php esc_html_e( 'Ricorre', 'open-events' ); ?></span>
                                    <select name="recurrence_rule_type" class="em-form-select-sm" style="width: auto; display: inline-block; margin-right: 10px;">
                                        <option value="once"><?php esc_html_e( 'una volta', 'open-events' ); ?></option>
                                        <option value="daily"><?php esc_html_e( 'ogni giorno', 'open-events' ); ?></option>
                                        <option value="weekly"><?php esc_html_e( 'ogni settimana', 'open-events' ); ?></option>
                                        <option value="monthly"><?php esc_html_e( 'ogni mese', 'open-events' ); ?></option>
                                        <option value="yearly"><?php esc_html_e( 'ogni anno', 'open-events' ); ?></option>
                                    </select>
                                    <span class="em-label"><?php esc_html_e( 'il', 'open-events' ); ?></span>
                                    <input type="date" name="recurrence_rule_date" class="em-form-input-sm" style="width: auto; display: inline-block; margin: 0 10px;">
                                    <span class="em-label"><?php esc_html_e( 'dalle', 'open-events' ); ?></span>
                                    <select name="recurrence_start_hour" class="em-form-select-sm" style="width: auto; display: inline-block;">
                                        <?php foreach ( $hours_options as $hr ): ?>
                                            <option value="<?php echo esc_attr($hr); ?>" <?php selected($hr, '08'); ?>><?php echo esc_html($hr); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    :
                                    <select name="recurrence_start_minute" class="em-form-select-sm" style="width: auto; display: inline-block; margin-right: 10px;">
                                        <?php foreach ( $minutes_options as $mn ): ?>
                                            <option value="<?php echo esc_attr($mn); ?>" <?php selected($mn, '00'); ?>><?php echo esc_html($mn); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="em-label"><?php esc_html_e( 'alle', 'open-events' ); ?></span>
                                    <select name="recurrence_end_hour" class="em-form-select-sm" style="width: auto; display: inline-block;">
                                        <?php foreach ( $hours_options as $hr ): ?>
                                            <option value="<?php echo esc_attr($hr); ?>" <?php selected($hr, '17'); ?>><?php echo esc_html($hr); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    :
                                    <select name="recurrence_end_minute" class="em-form-select-sm" style="width: auto; display: inline-block; margin-right: 10px;">
                                        <?php foreach ( $minutes_options as $mn ): ?>
                                            <option value="<?php echo esc_attr($mn); ?>" <?php selected($mn, '00'); ?>><?php echo esc_html($mn); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="em-label"><?php esc_html_e( 'nel', 'open-events' ); ?></span>
                                    <select name="recurrence_duration_type" class="em-form-select-sm" style="width: auto; display: inline-block;">
                                        <option value="same_day"><?php esc_html_e( 'stesso giorno', 'open-events' ); ?></option>
                                        <option value="next_day"><?php esc_html_e( 'giorno successivo', 'open-events' ); ?></option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="em-recurrence-actions" style="margin-top: 15px;">
                                <button type="button" class="em-secondary-btn" style="margin-right: 10px;"><?php esc_html_e( 'AGGIUNGI ALTRI EVENTI', 'open-events' ); ?></button>
                                <button type="button" class="em-secondary-btn btn-danger"><?php esc_html_e( 'AGGIUNGI ECCEZIONE', 'open-events' ); ?></button>
                            </div>
                        </div>

                        <div class="em-form-row-grid">
                            <div class="em-form-group">
                                <label><?php esc_html_e( 'Data Inizio *', 'open-events' ); ?></label>
                                <input type="date" name="EventStartDate" value="<?php echo esc_attr( $start_date ); ?>" required>
                            </div>
                            <div class="em-form-group em-time-field-group">
                                <label><?php esc_html_e( 'Ora Inizio', 'open-events' ); ?></label>
                                <div class="em-custom-time-selects">
                                    <select name="EventStartHour" class="em-time-select">
                                        <?php foreach ( $hours_options as $hr ): ?>
                                            <option value="<?php echo esc_attr($hr); ?>" <?php selected($hr, $start_hour); ?>><?php echo esc_html($hr); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="em-time-sep">:</span>
                                    <select name="EventStartMinute" class="em-time-select">
                                        <?php foreach ( $minutes_options as $mn ): ?>
                                            <option value="<?php echo esc_attr($mn); ?>" <?php selected($mn, $start_min); ?>><?php echo esc_html($mn); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="em-form-row-grid">
                            <div class="em-form-group">
                                <label><?php esc_html_e( 'Data Fine *', 'open-events' ); ?></label>
                                <input type="date" name="EventEndDate" value="<?php echo esc_attr( $end_date ); ?>" required>
                            </div>
                            <div class="em-form-group em-time-field-group">
                                <label><?php esc_html_e( 'Ora Fine', 'open-events' ); ?></label>
                                <div class="em-custom-time-selects">
                                    <select name="EventEndHour" class="em-time-select">
                                        <?php foreach ( $hours_options as $hr ): ?>
                                            <option value="<?php echo esc_attr($hr); ?>" <?php selected($hr, $end_hour); ?>><?php echo esc_html($hr); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="em-time-sep">:</span>
                                    <select name="EventEndMinute" class="em-time-select">
                                        <?php foreach ( $minutes_options as $mn ): ?>
                                            <option value="<?php echo esc_attr($mn); ?>" <?php selected($mn, $end_min); ?>><?php echo esc_html($mn); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="em-form-section">
                        <h3 class="em-form-section-title"><?php esc_html_e( '3. Luogo e Organizzazione', 'open-events' ); ?></h3>

                        <div class="em-form-row-grid-vertical">
                            <div class="em-form-group">
                                <label><?php esc_html_e( 'Seleziona Luogo', 'open-events' ); ?></label>
                                <select name="event_venue" class="em-form-select em-venue-select">
                                    <option value=""><?php esc_html_e( '-- Scegli un Luogo --', 'open-events' ); ?></option>
                                    <option value="__create_new__"><?php esc_html_e( '+ Crea Nuovo Luogo...', 'open-events' ); ?></option>
                                    <?php 
                                    $current_venue = $edit_post ? get_post_meta( $edit_post->ID, '_EventVenueID', true ) : '';
                                    foreach ( $venues as $v ): ?>
                                        <option value="<?php echo esc_attr( $v->ID ); ?>" <?php selected( $current_venue, $v->ID ); ?>>
                                            <?php echo esc_html( $v->post_title ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <div class="em-inline-creator em-hidden" id="em-inline-venue-creator">
                                    <h5><?php esc_html_e( 'Crea Nuovo Luogo', 'open-events' ); ?></h5>
                                    <div class="em-form-group">
                                        <label><?php esc_html_e( 'Nome Luogo *', 'open-events' ); ?></label>
                                        <input type="text" name="new_venue_title" placeholder="Es. Caffè Centrale">
                                    </div>
                                    <div class="em-form-group">
                                        <label><?php esc_html_e( 'Indirizzo', 'open-events' ); ?></label>
                                        <input type="text" name="new_venue_address" placeholder="Es. Via Roma, 10">
                                    </div>
                                    <div class="em-form-group">
                                        <label><?php esc_html_e( 'Città', 'open-events' ); ?></label>
                                        <?php $this->render_city_field( 'new_venue_city', '' ); ?>
                                    </div>
                                    <div class="em-form-group">
                                        <label><?php esc_html_e( 'CAP', 'open-events' ); ?></label>
                                        <input type="text" name="new_venue_zip" placeholder="Es. 25049">
                                    </div>
                                </div>
                            </div>
                            <div class="em-form-group">
                                <label><?php esc_html_e( 'Seleziona Organizzatore', 'open-events' ); ?></label>
                                <select name="event_organizer" class="em-form-select em-organizer-select">
                                    <option value=""><?php esc_html_e( '-- Scegli un Organizzatore --', 'open-events' ); ?></option>
                                    <option value="__create_new__"><?php esc_html_e( '+ Crea Nuovo Organizzatore...', 'open-events' ); ?></option>
                                    <?php 
                                    $current_org = $edit_post ? get_post_meta( $edit_post->ID, '_EventOrganizerID', true ) : '';
                                    foreach ( $organizers as $org ): ?>
                                        <option value="<?php echo esc_attr( $org->ID ); ?>" <?php selected( $current_org, $org->ID ); ?>>
                                            <?php echo esc_html( $org->post_title ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <div class="em-inline-creator em-hidden" id="em-inline-organizer-creator">
                                    <h5><?php esc_html_e( 'Crea Nuovo Organizzatore', 'open-events' ); ?></h5>
                                    <div class="em-form-group">
                                        <label><?php esc_html_e( 'Nome Organizzatore *', 'open-events' ); ?></label>
                                        <input type="text" name="new_organizer_title" placeholder="Es. Associazione Pro Loco">
                                    </div>
                                    <div class="em-form-group">
                                        <label><?php esc_html_e( 'Email', 'open-events' ); ?></label>
                                        <input type="email" name="new_organizer_email" placeholder="Es. info@proloco.it">
                                    </div>
                                    <div class="em-form-group">
                                        <label><?php esc_html_e( 'Telefono', 'open-events' ); ?></label>
                                        <input type="text" name="new_organizer_phone" placeholder="Es. 030123456">
                                    </div>
                                    <div class="em-form-group">
                                        <label><?php esc_html_e( 'Sito Web', 'open-events' ); ?></label>
                                        <input type="url" name="new_organizer_website" placeholder="https://...">
                                    </div>
                                    <div class="em-form-group">
                                        <label><?php esc_html_e( 'Logo Organizzatore', 'open-events' ); ?></label>
                                        <input type="file" name="new_organizer_logo" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="em-form-section">
                        <h3 class="em-form-section-title"><?php esc_html_e( '4. Costo e Collegamenti', 'open-events' ); ?></h3>
                        
                        <div class="em-form-row-grid">
                            <div class="em-form-group">
                                <label><?php esc_html_e( 'Prezzo (€)', 'open-events' ); ?></label>
                                <input type="text" name="_EventCost" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_EventCost', true ) : '' ); ?>" placeholder="Es. 15 (lascia vuoto se gratuito)">
                            </div>
                            <div class="em-form-group">
                                <label><?php esc_html_e( 'Link Sito Evento', 'open-events' ); ?></label>
                                <input type="url" name="_EventURL" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_EventURL', true ) : '' ); ?>" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <div class="em-form-section">
                        <h3 class="em-form-section-title"><?php esc_html_e( '5. Immagine dell\'Evento', 'open-events' ); ?></h3>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'Immagine Copertina Evento', 'open-events' ); ?></label>
                            <?php $this->render_image_dropzone( $edit_post, esc_html__( 'Formato consigliato JPG o PNG. Dimensione massima 2MB.', 'open-events' ) ); ?>
                        </div>
                    </div>
                <?php elseif ( 'tribe_organizer' === $post_type ): ?>
                    <div class="em-form-section">
                        <h3 class="em-form-section-title"><?php esc_html_e( '2. Dettagli Organizzatore', 'open-events' ); ?></h3>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'Telefono', 'open-events' ); ?></label>
                            <input type="text" name="_OrganizerPhone" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_OrganizerPhone', true ) : '' ); ?>">
                        </div>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'Sito Web', 'open-events' ); ?></label>
                            <input type="url" name="_OrganizerWebsite" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_OrganizerWebsite', true ) : '' ); ?>">
                        </div>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'Email', 'open-events' ); ?></label>
                            <input type="email" name="_OrganizerEmail" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_OrganizerEmail', true ) : '' ); ?>">
                        </div>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'Logo / Foto (Immagine in evidenza)', 'open-events' ); ?></label>
                            <?php $this->render_image_dropzone( $edit_post ); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="em-form-section">
                        <h3 class="em-form-section-title"><?php esc_html_e( '2. Posizione del Luogo', 'open-events' ); ?></h3>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'Indirizzo', 'open-events' ); ?></label>
                            <input type="text" name="_VenueAddress" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_VenueAddress', true ) : '' ); ?>">
                        </div>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'Città', 'open-events' ); ?></label>
                            <?php $this->render_city_field( '_VenueCity', $edit_post ? get_post_meta( $edit_post->ID, '_VenueCity', true ) : '' ); ?>
                        </div>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'Paese', 'open-events' ); ?></label>
                            <input type="text" name="_VenueCountry" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_VenueCountry', true ) : '' ); ?>">
                        </div>
                        <div class="em-form-group">
                            <label><?php esc_html_e( 'CAP', 'open-events' ); ?></label>
                            <input type="text" name="_VenueZip" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_VenueZip', true ) : '' ); ?>">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="em-form-actions">
                    <a href="<?php echo esc_url( $form_list_url ); ?>" class="em-cancel-btn">
                        <?php esc_html_e( 'Annulla', 'open-events' ); ?>
                    </a>
                    <button type="submit" class="em-submit-btn">
                        <?php $this->render_icon( 'save' ); ?> <?php echo 'edit' === $current_action ? esc_html__( 'Salva Modifiche', 'open-events' ) : esc_html__( 'Invia Evento per Revisione', 'open-events' ); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
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
