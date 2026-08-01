<?php
namespace OpenEvents;
/**
 * Vista elenco (I Miei/Tutti gli Eventi|Luoghi|Organizzatori). Incluso da
 * Widget_Events_Manager::render() con `include`: condivide lo scope locale
 * del metodo chiamante ($this, $show_sidebar, $action_mode, $label_plural,
 * $label_singular, $label_icon, $post_type, $is_admin_view, $user_posts).
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
                ?>
                <div class="em-item-row">
                    <a href="<?php echo esc_url( $item_edit_url ); ?>" class="em-item-thumb">
                        <?php if ( $thumb_url ) : ?>
                            <img src="<?php echo esc_url( $thumb_url ); ?>" alt="">
                        <?php else : ?>
                            <?php $this->render_icon( $label_icon ); ?>
                        <?php endif; ?>
                    </a>
                    <div class="em-item-info">
                        <strong class="em-item-title">
                            <a href="<?php echo esc_url( $item_edit_url ); ?>" class="em-item-title-link"><?php echo esc_html( $p->post_title ); ?></a>
                            <?php if ( 'tribe_events' === $post_type && '1' === get_post_meta( $p->ID, '_tribe_featured', true ) ) : ?>
                                <span class="em-featured-badge"><?php $this->render_icon( 'star' ); ?> <?php echo esc_html( open_events_get_featured_label() ); ?></span>
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
                    <a href="<?php echo esc_url( $item_edit_url ); ?>" class="em-action-btn edit-btn" title="<?php esc_attr_e( 'Modifica', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Modifica', 'open-events' ); ?>">
                        <?php $this->render_icon( 'edit' ); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
