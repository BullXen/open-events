<?php
namespace OpenEvents;
/**
 * Vista elenco (I Miei/Tutti gli Eventi|Luoghi|Organizzatori). Incluso da
 * Widget_Events_Manager::render() con `include`: condivide lo scope locale
 * del metodo chiamante ($this, $show_sidebar, $action_mode, $label_plural,
 * $label_singular, $label_icon, $post_type, $is_admin_view, $user_posts,
 * $current_user_id, $list_status, $total_pages, $current_page, $sort_by,
 * $sort_dir — $list_status/$total_pages/$current_page validi solo per
 * 'tribe_events' === $post_type && $is_admin_view ('' o 0 altrimenti);
 * $sort_by/$sort_dir sempre validi per 'tribe_events' (default
 * post_date/desc), ignorati per gli altri post type.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
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

    <?php if ( 'tribe_events' === $post_type ) : ?>
        <div class="em-list-toolbar">
            <div class="em-consigliati-filters">
                <a href="<?php echo esc_url( remove_query_arg( [ 'list_status', 'epage' ] ) ); ?>" class="em-filter-pill<?php echo '' === $list_status ? ' active' : ''; ?>"><?php esc_html_e( 'Tutti', 'open-events' ); ?></a>
                <a href="<?php echo esc_url( add_query_arg( 'list_status', 'publish', remove_query_arg( 'epage' ) ) ); ?>" class="em-filter-pill<?php echo 'publish' === $list_status ? ' active' : ''; ?>"><?php esc_html_e( 'Pubblicati', 'open-events' ); ?></a>
                <a href="<?php echo esc_url( add_query_arg( 'list_status', 'expired', remove_query_arg( 'epage' ) ) ); ?>" class="em-filter-pill<?php echo 'expired' === $list_status ? ' active' : ''; ?>"><?php esc_html_e( 'Scaduti', 'open-events' ); ?></a>
            </div>
            <?php if ( $is_admin_view ) :
                $pub_active     = ( 'post_date' === $sort_by );
                $pub_next_dir   = $pub_active ? ( 'desc' === $sort_dir ? 'asc' : 'desc' ) : 'desc';
                $event_active   = ( 'event_date' === $sort_by );
                $event_next_dir = $event_active ? ( 'desc' === $sort_dir ? 'asc' : 'desc' ) : 'desc';
                ?>
                <div class="em-sort-controls">
                    <a href="<?php echo esc_url( add_query_arg( [ 'sort_by' => 'post_date', 'sort_dir' => $pub_next_dir ], remove_query_arg( 'epage' ) ) ); ?>" class="em-sort-btn<?php echo $pub_active ? ' active' : ''; ?>" title="<?php esc_attr_e( 'Ordina per data di pubblicazione', 'open-events' ); ?>">
                        <?php $this->render_icon( 'upload' ); ?> <?php esc_html_e( 'Pubblicazione', 'open-events' ); ?>
                        <?php if ( $pub_active ) : ?><span class="em-sort-arrow"><?php echo 'asc' === $sort_dir ? '↑' : '↓'; ?></span><?php endif; ?>
                    </a>
                    <a href="<?php echo esc_url( add_query_arg( [ 'sort_by' => 'event_date', 'sort_dir' => $event_next_dir ], remove_query_arg( 'epage' ) ) ); ?>" class="em-sort-btn<?php echo $event_active ? ' active' : ''; ?>" title="<?php esc_attr_e( 'Ordina per data evento', 'open-events' ); ?>">
                        <?php $this->render_icon( 'calendar' ); ?> <?php esc_html_e( 'Data evento', 'open-events' ); ?>
                        <?php if ( $event_active ) : ?><span class="em-sort-arrow"><?php echo 'asc' === $sort_dir ? '↑' : '↓'; ?></span><?php endif; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

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
                $item_edit_url = add_query_arg( 'edit_id', $p->ID );
                // Un evento già pubblicato non è più modificabile dal proprietario
                // (solo un admin può farlo) — vedi guardia lato server in render().
                $can_edit_item = $is_admin_view || 'tribe_events' !== $post_type || 'publish' !== $p->post_status;
                ?>
                <div class="em-item-row">
                    <?php if ( $can_edit_item ) : ?>
                        <a href="<?php echo esc_url( $item_edit_url ); ?>" class="em-item-thumb">
                            <?php if ( $thumb_url ) : ?>
                                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="">
                            <?php else : ?>
                                <?php $this->render_icon( $label_icon ); ?>
                            <?php endif; ?>
                        </a>
                    <?php else : ?>
                        <span class="em-item-thumb">
                            <?php if ( $thumb_url ) : ?>
                                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="">
                            <?php else : ?>
                                <?php $this->render_icon( $label_icon ); ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                    <div class="em-item-info">
                        <strong class="em-item-title">
                            <?php if ( $can_edit_item ) : ?>
                                <a href="<?php echo esc_url( $item_edit_url ); ?>" class="em-item-title-link"><?php echo esc_html( $p->post_title ); ?></a>
                            <?php else : ?>
                                <?php echo esc_html( $p->post_title ); ?>
                            <?php endif; ?>
                            <?php if ( 'tribe_events' === $post_type && '1' === get_post_meta( $p->ID, '_tribe_featured', true ) ) : ?>
                                <span class="em-featured-badge em-featured-badge-label"><?php $this->render_icon( 'star' ); ?> <?php echo esc_html( open_events_get_featured_label() ); ?></span>
                            <?php endif; ?>
                            <?php
                            $consigliato_status = ( 'tribe_events' === $post_type ) ? get_post_meta( $p->ID, '_illi_featured_status', true ) : '';
                            if ( 'tribe_events' === $post_type && open_events_featured_is_active( $p->ID ) ) :
                                $consigliato_amount = get_post_meta( $p->ID, '_illi_featured_amount', true );
                                ?>
                                <span class="em-consigliato-badge" title="<?php echo $consigliato_amount ? esc_attr( sprintf( __( 'Pagato: %s', 'open-events' ), number_format_i18n( $consigliato_amount / 100, 2 ) ) ) : ''; ?>">
                                    <?php $this->render_icon( 'star' ); ?> <?php esc_html_e( 'Consigliato', 'open-events' ); ?>
                                </span>
                            <?php endif;
                            $consigliato_receipt_url = ( 'paid' === $consigliato_status ) ? open_events_featured_get_receipt_url( $p->ID ) : '';
                            if ( $consigliato_receipt_url ) : ?>
                                <a href="<?php echo esc_url( $consigliato_receipt_url ); ?>" class="em-receipt-link" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Ricevuta Stripe', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Ricevuta Stripe', 'open-events' ); ?>">
                                    <?php $this->render_icon( 'link' ); ?> <?php esc_html_e( 'Ricevuta', 'open-events' ); ?>
                                </a>
                            <?php endif; ?>
                        </strong>
                        <?php if ( $meta_line || $is_admin_view ) : ?>
                            <span class="em-item-meta">
                                <?php echo esc_html( $meta_line ); ?>
                                <?php if ( $is_admin_view ) :
                                    $post_author_data = get_userdata( $p->post_author );
                                    $author_name = $post_author_data ? $post_author_data->display_name : esc_html__( 'Sconosciuto', 'open-events' );

                                    $published_by_mode = open_events_get_published_by_display();
                                    $organizer_id = ( 'tribe_events' === $post_type ) ? get_post_meta( $p->ID, '_EventOrganizerID', true ) : '';
                                    $organizer_title = $organizer_id ? get_the_title( $organizer_id ) : '';
                                    ?>
                                    <?php echo $meta_line ? ' · ' : ''; ?>
                                    <?php if ( 'organizer' === $published_by_mode && $organizer_title ) :
                                        $organizer_edit_url = add_query_arg( [ 'view' => 'tribe_organizer', 'edit_id' => $organizer_id ], remove_query_arg( [ 'edit_id', 'action', 'type' ] ) );
                                        ?>
                                        <a href="<?php echo esc_url( $organizer_edit_url ); ?>" class="em-organizer-link"><?php echo esc_html( $organizer_title ); ?></a>
                                    <?php else :
                                        $author_suffix = $post_author_data ? '@' . $post_author_data->user_login : '—';
                                        if ( 'email' === $published_by_mode ) {
                                            $author_suffix = $post_author_data ? $post_author_data->user_email : '—';
                                        }
                                        printf( esc_html__( 'di %1$s (%2$s)', 'open-events' ), esc_html( $author_name ), esc_html( $author_suffix ) );
                                    endif; ?>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo esc_url( 'publish' === $p->post_status ? get_permalink( $p->ID ) : get_preview_post_link( $p ) ); ?>" class="em-action-btn preview-btn" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Anteprima', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Anteprima', 'open-events' ); ?>">
                        <?php $this->render_icon( 'eye' ); ?>
                    </a>
                    <?php if ( 'tribe_events' === $post_type && (int) $p->post_author === (int) $current_user_id && empty( $p->is_expired ) && in_array( $consigliato_status, [ '', 'none', 'pending_payment' ], true ) ) :
                        $consigliato_action_url = wp_nonce_url(
                            add_query_arg( [ 'oe_featured_checkout' => 'start', 'post_id' => $p->ID ] ),
                            'oe_featured_start_' . $p->ID
                        );
                        $consigliato_action_label = 'pending_payment' === $consigliato_status
                            ? esc_html__( 'Completa il pagamento', 'open-events' )
                            : esc_html__( 'Rendi Consigliato', 'open-events' );
                        ?>
                        <a href="<?php echo esc_url( $consigliato_action_url ); ?>" class="em-action-btn em-consigliato-cta consigliato-btn" title="<?php echo esc_attr( $consigliato_action_label ); ?>" aria-label="<?php echo esc_attr( $consigliato_action_label ); ?>">
                            <?php $this->render_icon( 'credit-card' ); ?>
                            <span><?php echo esc_html( $consigliato_action_label ); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ( $is_admin_view && 'publish' !== $p->post_status ) : ?>
                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'em_action' => 'publish', 'post_id' => $p->ID ] ), 'em_publish_' . $p->ID ) ); ?>" class="em-action-btn publish-btn" onclick="return confirm('<?php echo esc_js( __( 'Pubblicare questo elemento online?', 'open-events' ) ); ?>');" title="<?php esc_attr_e( 'Pubblica', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Pubblica', 'open-events' ); ?>">
                            <?php $this->render_icon( 'check' ); ?>
                        </a>
                    <?php endif; ?>
                    <span class="em-status-badge <?php echo esc_attr( $p->post_status ); ?>"><?php echo esc_html( get_post_status_object( $p->post_status )->label ); ?></span>
                    <?php if ( $is_admin_view ) : ?>
                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'em_action' => 'delete', 'post_id' => $p->ID ] ), 'em_delete_' . $p->ID ) ); ?>" class="em-action-btn delete-btn" onclick="return confirm('<?php echo esc_js( __( 'Eliminare questo elemento? Verrà spostato nel cestino.', 'open-events' ) ); ?>');" title="<?php esc_attr_e( 'Elimina', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Elimina', 'open-events' ); ?>">
                            <?php $this->render_icon( 'trash' ); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( $can_edit_item ) : ?>
                        <a href="<?php echo esc_url( $item_edit_url ); ?>" class="em-action-btn edit-btn" title="<?php esc_attr_e( 'Modifica', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Modifica', 'open-events' ); ?>">
                            <?php $this->render_icon( 'edit' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ( 'tribe_events' === $post_type && $is_admin_view && $total_pages > 1 ) : ?>
            <div class="em-pagination">
                <a href="<?php echo esc_url( add_query_arg( 'epage', max( 1, $current_page - 1 ) ) ); ?>" class="em-page-link em-page-prev<?php echo 1 === $current_page ? ' is-disabled' : ''; ?>">&larr; <?php esc_html_e( 'Indietro', 'open-events' ); ?></a>
                <?php for ( $page_num = 1; $page_num <= $total_pages; $page_num++ ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'epage', $page_num ) ); ?>" class="em-page-link<?php echo $page_num === $current_page ? ' active' : ''; ?>"><?php echo esc_html( $page_num ); ?></a>
                <?php endfor; ?>
                <a href="<?php echo esc_url( add_query_arg( 'epage', min( $total_pages, $current_page + 1 ) ) ); ?>" class="em-page-link em-page-next<?php echo $current_page === $total_pages ? ' is-disabled' : ''; ?>"><?php esc_html_e( 'Avanti', 'open-events' ); ?> &rarr;</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
