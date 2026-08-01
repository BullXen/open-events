<?php
namespace OpenEvents;
/**
 * Vista form Inserisci/Modifica (Evento/Luogo/Organizzatore). Incluso da
 * Widget_Events_Manager::render() con `include`: condivide lo scope locale
 * del metodo chiamante ($this, $show_sidebar, $label_plural, $label_singular,
 * $current_action, $form_list_url, $form_back_label, $success_msg, $error_msg,
 * $post_type, $edit_post, $is_admin_view, $categories, $venues, $organizers,
 * $current_user_id).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
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
                <?php
                echo 'edit' === $current_action
                    ? sprintf( esc_html__( 'Modifica %s', 'open-events' ), $label_singular )
                    : sprintf( esc_html__( 'Inserisci Nuovo %s', 'open-events' ), $label_singular );
                ?>
            </h2>
            <p class="em-section-subtitle"><?php printf( esc_html__( 'Compila tutti i dettagli relativi al tuo %s per pubblicarlo sul portale.', 'open-events' ), esc_html( strtolower( $label_singular ) ) ); ?></p>

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
                                $title_placeholder = __( 'Es. Concerto sotto le stelle, Aperitivo al Castello...', 'open-events' );
                            } elseif ( 'tribe_organizer' === $post_type ) {
                                esc_html_e( 'Nome Organizzatore *', 'open-events' );
                                $title_placeholder = __( 'Es. Pro Loco, Associazione, Comune...', 'open-events' );
                            } else {
                                esc_html_e( 'Nome Luogo *', 'open-events' );
                                $title_placeholder = __( 'Es. Comune Iseo, Campo Sportivo di, Chiesa di...', 'open-events' );
                            }
                            ?>
                        </label>
                        <input type="text" name="post_title" required value="<?php echo esc_attr( $edit_post ? $edit_post->post_title : '' ); ?>" placeholder="<?php echo esc_attr( $title_placeholder ); ?>">
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

                    $start_hour = '18';
                    $start_min = '00';
                    if ( ! empty( $start_time ) && strpos( $start_time, ':' ) !== false ) {
                        list( $start_hour, $start_min ) = explode( ':', $start_time );
                    }

                    $end_hour = '22';
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
                                    <input type="checkbox" name="recurring_event" class="em-recurring-switch" value="yes" <?php checked( $is_recurring_val, 'yes' ); ?> <?php disabled( 'edit' === $current_action ); ?>>
                                    <span><?php esc_html_e( 'Evento con più date (ricorrente)', 'open-events' ); ?></span>
                                </label>
                                <?php if ( 'edit' === $current_action ) : ?>
                                    <small class="em-field-help"><?php esc_html_e( 'Non modificabile in modifica: questa data fa parte di una serie, modifichi solo questa occorrenza.', 'open-events' ); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="em-form-row-grid">
                            <div class="em-form-group">
                                <label><?php esc_html_e( 'Data Inizio *', 'open-events' ); ?></label>
                                <?php $this->render_date_field( 'EventStartDate', 'em_event_start_date', $start_date ); ?>
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
                            <div class="em-form-group" id="em_end_date_group">
                                <label><?php esc_html_e( 'Data Fine *', 'open-events' ); ?></label>
                                <?php $this->render_date_field( 'EventEndDate', 'em_event_end_date', $end_date ); ?>
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

                        <div class="em-recurring-wrapper em-hidden" id="em-recurring-details">
                            <div class="em-divider"></div>

                            <div class="em-field-label-sm"><?php esc_html_e( 'Come si ripete?', 'open-events' ); ?></div>

                            <div class="em-pattern-grid">
                                <button type="button" class="em-pattern-btn active" data-pattern="daily">
                                    <?php esc_html_e( 'Ogni giorno', 'open-events' ); ?>
                                </button>
                                <button type="button" class="em-pattern-btn" data-pattern="weekly">
                                    <?php esc_html_e( 'Settimanale', 'open-events' ); ?>
                                </button>
                                <button type="button" class="em-pattern-btn" data-pattern="custom">
                                    <?php esc_html_e( 'Date libere', 'open-events' ); ?>
                                </button>
                            </div>
                            <input type="hidden" name="em_series_pattern" id="em_series_pattern" value="daily">

                            <div id="em_weekly_days_wrapper" class="em-hidden">
                                <label class="em-field-label-sm"><?php esc_html_e( 'Ripeti nei giorni:', 'open-events' ); ?></label>
                                <div class="em-days" id="em_weekly_days">
                                    <?php foreach ( [ 1 => 'L', 2 => 'M', 3 => 'M', 4 => 'G', 5 => 'V', 6 => 'S', 7 => 'D' ] as $dow => $label ) : ?>
                                        <button type="button" class="em-day-chip" data-day="<?php echo esc_attr( $dow ); ?>"><?php echo esc_html( $label ); ?></button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="em-form-row-grid" id="em_series_until_wrapper">
                                <div class="em-form-group">
                                    <label><?php esc_html_e( 'Ripeti fino al', 'open-events' ); ?></label>
                                    <?php $this->render_date_field( '', 'em_series_until', '' ); ?>
                                </div>
                            </div>

                            <div class="em-preview" id="em_series_preview">
                                <div class="em-preview-title"><?php esc_html_e( 'Anteprima date', 'open-events' ); ?></div>
                                <div id="em_preview_list"></div>
                                <p class="em-field-help"><?php esc_html_e( 'Clicca × per escludere una data dalla pubblicazione.', 'open-events' ); ?></p>
                            </div>

                            <label class="em-field-label-sm"><?php esc_html_e( 'Inserisci data extra', 'open-events' ); ?></label>
                            <div class="em-recurrence-actions">
                                <?php $this->render_date_field( '', 'em_add_custom_date', '' ); ?>
                                <button type="button" class="em-secondary-btn" id="em_add_custom_date_btn"><?php esc_html_e( '+ Aggiungi data', 'open-events' ); ?></button>
                            </div>

                            <input type="hidden" name="em_series_dates" id="em_series_dates" value="[]">
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
                                    // Prima i luoghi inseriti dall'utente stesso, poi tutti gli altri.
                                    $own_venues   = [];
                                    $other_venues = [];
                                    foreach ( $venues as $v ) {
                                        if ( (int) $v->post_author === (int) $current_user_id ) {
                                            $own_venues[] = $v;
                                        } else {
                                            $other_venues[] = $v;
                                        }
                                    }
                                    ?>
                                    <?php if ( $own_venues ) : ?>
                                        <optgroup label="<?php esc_attr_e( 'I tuoi luoghi', 'open-events' ); ?>">
                                            <?php foreach ( $own_venues as $v ) : ?>
                                                <option value="<?php echo esc_attr( $v->ID ); ?>" <?php selected( $current_venue, $v->ID ); ?>><?php echo esc_html( $v->post_title ); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                    <?php if ( $other_venues ) : ?>
                                        <optgroup label="<?php esc_attr_e( 'Altri luoghi', 'open-events' ); ?>">
                                            <?php foreach ( $other_venues as $v ) : ?>
                                                <option value="<?php echo esc_attr( $v->ID ); ?>" <?php selected( $current_venue, $v->ID ); ?>><?php echo esc_html( $v->post_title ); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
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

                <?php if ( $is_admin_view && in_array( $post_type, [ 'tribe_venue', 'tribe_organizer' ], true ) ):
                    $assign_users = get_users( [ 'orderby' => 'display_name', 'order' => 'ASC' ] );
                    $current_assigned_id = $edit_post ? intval( $edit_post->post_author ) : 0;
                    ?>
                    <div class="em-form-section">
                        <h3 class="em-form-section-title"><?php esc_html_e( 'Assegnazione (Admin)', 'open-events' ); ?></h3>
                        <div class="em-form-group">
                            <label for="em_assign_to_user"><?php esc_html_e( 'Assegna a', 'open-events' ); ?></label>
                            <select name="assign_to_user" id="em_assign_to_user" class="em-form-select">
                                <option value=""><?php esc_html_e( '-- Amministratore (predefinito) --', 'open-events' ); ?></option>
                                <?php foreach ( $assign_users as $u ): ?>
                                    <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $current_assigned_id, $u->ID ); ?>>
                                        <?php echo esc_html( $u->display_name . ' (@' . $u->user_login . ')' ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="em-field-help"><?php esc_html_e( 'Se lasci vuoto resta assegnato al tuo account admin. Altrimenti scegli a quale utente assegnare questo elemento.', 'open-events' ); ?></small>
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
