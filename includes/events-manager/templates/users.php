<?php
namespace OpenEvents;
/**
 * Vista "Utenti iscritti" (solo amministratori). Incluso da
 * Widget_Events_Manager::render() con `include`: condivide lo scope locale
 * del metodo chiamante ($this, $all_users, $editable_roles, $admin_count,
 * $users_notice, $users_error, $current_user_id, $date_format, ecc.).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="em-form-container em-dashboard-view em-users-view">
    <?php $this->render_breadcrumbs( [
        [ 'label' => esc_html__( 'Dashboard', 'open-events' ), 'url' => remove_query_arg( [ 'view', 'oe_user_action', 'user_id', '_wpnonce' ] ) ],
        [ 'label' => esc_html__( 'Utenti', 'open-events' ), 'url' => '' ],
    ] ); ?>

    <div class="em-back-link">
        <a href="<?php echo esc_url( remove_query_arg( [ 'view', 'oe_user_action', 'user_id', '_wpnonce' ] ) ); ?>">&larr; <?php esc_html_e( 'Torna alla Dashboard', 'open-events' ); ?></a>
    </div>

    <div class="em-dashboard-header">
        <h2><?php $this->render_icon( 'users' ); ?> <?php esc_html_e( 'Utenti iscritti', 'open-events' ); ?> <span class="em-count-badge"><?php echo count( $all_users ); ?></span></h2>
    </div>

    <?php if ( $users_notice ) : ?>
        <div class="em-alert success"><?php echo esc_html( $users_notice ); ?></div>
    <?php endif; ?>
    <?php if ( $users_error ) : ?>
        <div class="em-alert error"><?php echo esc_html( $users_error ); ?></div>
    <?php endif; ?>

    <div class="em-items-list em-users-list">
        <?php foreach ( $all_users as $u ) :
            $u_roles = (array) $u->roles;
            $primary_role = $u_roles[0] ?? '';
            $role_label = isset( $editable_roles[ $primary_role ] ) ? translate_user_role( $editable_roles[ $primary_role ]['name'] ) : $primary_role;
            $event_count = count_user_posts( $u->ID, 'tribe_events' );
            $is_self = ( $u->ID === $current_user_id );
            $is_admin_user = in_array( 'administrator', $u_roles, true );
            $edit_link = admin_url( 'user-edit.php?user_id=' . $u->ID );
            // Provider di registrazione: oe_{provider}_id viene salvato solo
            // al primo login social (vedi open_events_community_social_login_or_register()
            // in community-oauth.php). Nessuno dei due -> registrazione classica via email.
            if ( get_user_meta( $u->ID, 'oe_google_id', true ) ) {
                $reg_provider = 'google';
                $reg_provider_label = __( 'Registrato con Google', 'open-events' );
            } elseif ( get_user_meta( $u->ID, 'oe_facebook_id', true ) ) {
                $reg_provider = 'facebook';
                $reg_provider_label = __( 'Registrato con Facebook', 'open-events' );
            } else {
                $reg_provider = 'email';
                $reg_provider_label = __( 'Registrato con email', 'open-events' );
            }
            $delete_url = wp_nonce_url(
                add_query_arg( [ 'oe_user_action' => 'delete', 'user_id' => $u->ID ], remove_query_arg( [ 'oe_user_action', 'user_id', '_wpnonce' ] ) ),
                'oe_user_delete_' . $u->ID
            );
            ?>
            <div class="em-item-row em-user-row">
                <span class="em-item-thumb em-user-avatar">
                    <?php echo get_avatar( $u->ID, 44 ); ?>
                    <span class="em-user-provider-badge" title="<?php echo esc_attr( $reg_provider_label ); ?>"><?php echo $this->registration_provider_icon( $reg_provider ); ?></span>
                </span>
                <div class="em-item-info">
                    <strong class="em-item-title">
                        <?php echo esc_html( $u->display_name ); ?>
                        <span class="em-user-login">@<?php echo esc_html( $u->user_login ); ?></span>
                        <?php if ( $is_self ) : ?>
                            <span class="em-featured-badge"><?php esc_html_e( 'Tu', 'open-events' ); ?></span>
                        <?php endif; ?>
                    </strong>
                    <span class="em-item-meta">
                        <a href="mailto:<?php echo esc_attr( $u->user_email ); ?>"><?php echo esc_html( $u->user_email ); ?></a>
                        · <?php echo esc_html( $role_label ); ?>
                        · <?php printf( esc_html__( 'iscritto il %s', 'open-events' ), esc_html( date_i18n( $date_format, strtotime( $u->user_registered ) ) ) ); ?>
                        · <?php printf( esc_html( _n( '%d evento', '%d eventi', (int) $event_count, 'open-events' ) ), (int) $event_count ); ?>
                    </span>
                </div>

                <?php // Cambio ruolo (bloccato per se stessi e per l'ultimo admin)
                $lock_role = $is_self || ( $is_admin_user && $admin_count <= 1 );
                ?>
                <form method="post" class="em-user-role-form">
                    <?php wp_nonce_field( 'oe_user_role', 'oe_user_role_nonce' ); ?>
                    <input type="hidden" name="oe_user_id" value="<?php echo esc_attr( $u->ID ); ?>">
                    <select name="oe_user_role" class="em-user-role-select" <?php disabled( $lock_role ); ?> aria-label="<?php esc_attr_e( 'Ruolo utente', 'open-events' ); ?>">
                        <?php foreach ( $editable_roles as $role_key => $role_data ) : ?>
                            <option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( $primary_role, $role_key ); ?>>
                                <?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ( ! $lock_role ) : ?>
                        <button type="submit" name="oe_user_role_submit" value="1" class="em-action-btn" title="<?php esc_attr_e( 'Salva ruolo', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Salva ruolo', 'open-events' ); ?>">
                            <?php $this->render_icon( 'check' ); ?>
                        </button>
                    <?php endif; ?>
                </form>

                <a href="<?php echo esc_url( $edit_link ); ?>" class="em-action-btn edit-btn" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Modifica in wp-admin', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Modifica in wp-admin', 'open-events' ); ?>">
                    <?php $this->render_icon( 'edit' ); ?>
                </a>

                <?php if ( ! $is_self && ! $is_admin_user ) : ?>
                    <a href="<?php echo esc_url( $delete_url ); ?>" class="em-action-btn delete-btn" onclick="return confirm('<?php echo esc_js( __( 'Eliminare questo utente? I suoi eventi/luoghi/organizzatori verranno riassegnati al tuo account. Operazione non annullabile.', 'open-events' ) ); ?>');" title="<?php esc_attr_e( 'Elimina utente', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Elimina utente', 'open-events' ); ?>">
                        <?php $this->render_icon( 'trash' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
