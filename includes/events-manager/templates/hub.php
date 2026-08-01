<?php
/**
 * Vista "Dashboard" (hub home). Incluso da Widget_Events_Manager::render()
 * con `include`: condivide lo scope locale del metodo chiamante ($this,
 * $current_user_id, $current_user, $is_admin_view, $settings,
 * $hub_new_events/venues/organizers).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
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
            <?php if ( $hub_new_events > 0 ) : ?>
                <span class="em-hub-badge"><?php echo esc_html( $hub_new_events ); ?></span>
            <?php endif; ?>
            <div class="em-card-icon">
                <?php $this->render_icon( 'calendar' ); ?>
            </div>
            <h4><?php esc_html_e( 'I Miei Eventi', 'open-events' ); ?></h4>
            <p><?php esc_html_e( 'Crea e gestisci i tuoi eventi del calendario, date e descrizioni.', 'open-events' ); ?></p>
            <span class="em-card-btn"><?php esc_html_e( 'Accedi', 'open-events' ); ?> &rarr;</span>
        </a>

        <!-- I Miei Luoghi -->
        <a href="<?php echo esc_url( add_query_arg( 'view', 'tribe_venue' ) ); ?>" class="em-hub-card">
            <?php if ( $hub_new_venues > 0 ) : ?>
                <span class="em-hub-badge"><?php echo esc_html( $hub_new_venues ); ?></span>
            <?php endif; ?>
            <div class="em-card-icon">
                <?php $this->render_icon( 'map-pin' ); ?>
            </div>
            <h4><?php esc_html_e( 'I Miei Luoghi', 'open-events' ); ?></h4>
            <p><?php esc_html_e( 'Gestisci indirizzi, città, CAP e dettagli dei tuoi luoghi.', 'open-events' ); ?></p>
            <span class="em-card-btn"><?php esc_html_e( 'Accedi', 'open-events' ); ?> &rarr;</span>
        </a>

        <!-- I Miei Organizzatori -->
        <a href="<?php echo esc_url( add_query_arg( 'view', 'tribe_organizer' ) ); ?>" class="em-hub-card">
            <?php if ( $hub_new_organizers > 0 ) : ?>
                <span class="em-hub-badge"><?php echo esc_html( $hub_new_organizers ); ?></span>
            <?php endif; ?>
            <div class="em-card-icon">
                <?php $this->render_icon( 'person' ); ?>
            </div>
            <h4><?php esc_html_e( 'I Miei Organizzatori', 'open-events' ); ?></h4>
            <p><?php esc_html_e( 'Gestisci dettagli dei tuoi organizzatori, telefono, sito web e loghi.', 'open-events' ); ?></p>
            <span class="em-card-btn"><?php esc_html_e( 'Accedi', 'open-events' ); ?> &rarr;</span>
        </a>

        <?php if ( $is_admin_view ) : ?>
            <!-- Utenti (solo admin) -->
            <a href="<?php echo esc_url( add_query_arg( 'view', 'users' ) ); ?>" class="em-hub-card em-users-card">
                <div class="em-card-icon">
                    <?php $this->render_icon( 'users' ); ?>
                </div>
                <h4><?php esc_html_e( 'Utenti', 'open-events' ); ?></h4>
                <p><?php esc_html_e( 'Vedi tutti gli utenti iscritti, cambia il loro ruolo o eliminali.', 'open-events' ); ?></p>
                <span class="em-card-btn"><?php esc_html_e( 'Accedi', 'open-events' ); ?> &rarr;</span>
            </a>

            <!-- Statistiche (solo admin) -->
            <a href="<?php echo esc_url( add_query_arg( 'view', 'stats' ) ); ?>" class="em-hub-card em-stats-card">
                <div class="em-card-icon">
                    <?php $this->render_icon( 'chart' ); ?>
                </div>
                <h4><?php esc_html_e( 'Statistiche', 'open-events' ); ?></h4>
                <p><?php esc_html_e( 'Eventi pubblicati, online, visualizzazioni schede e altri numeri chiave.', 'open-events' ); ?></p>
                <span class="em-card-btn"><?php esc_html_e( 'Accedi', 'open-events' ); ?> &rarr;</span>
            </a>
        <?php endif; ?>

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
