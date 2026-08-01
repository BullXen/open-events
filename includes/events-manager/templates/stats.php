<?php
/**
 * Vista "Statistiche" (solo amministratori). Incluso da
 * Widget_Events_Manager::render() con `include`: condivide lo scope locale
 * del metodo chiamante ($this, $stat_tiles, $top_viewed).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="em-form-container em-dashboard-view em-stats-view">
    <?php $this->render_breadcrumbs( [
        [ 'label' => esc_html__( 'Dashboard', 'open-events' ), 'url' => remove_query_arg( 'view' ) ],
        [ 'label' => esc_html__( 'Statistiche', 'open-events' ), 'url' => '' ],
    ] ); ?>

    <div class="em-back-link">
        <a href="<?php echo esc_url( remove_query_arg( 'view' ) ); ?>">&larr; <?php esc_html_e( 'Torna alla Dashboard', 'open-events' ); ?></a>
    </div>

    <div class="em-dashboard-header">
        <h2><?php $this->render_icon( 'chart' ); ?> <?php esc_html_e( 'Statistiche', 'open-events' ); ?></h2>
    </div>

    <div class="em-stats-grid">
        <?php foreach ( $stat_tiles as $tile ) : ?>
            <div class="em-stat-tile">
                <div class="em-stat-icon"><?php $this->render_icon( $tile['icon'] ); ?></div>
                <span class="em-stat-value"><?php echo esc_html( number_format_i18n( $tile['value'] ) ); ?></span>
                <span class="em-stat-label"><?php echo esc_html( $tile['label'] ); ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ( $top_viewed ) : ?>
        <h3 class="em-stats-subheading"><?php esc_html_e( 'Eventi più visualizzati', 'open-events' ); ?></h3>
        <div class="em-items-list em-stats-top-list">
            <?php foreach ( $top_viewed as $row ) : ?>
                <div class="em-item-row">
                    <div class="em-item-info">
                        <strong class="em-item-title"><?php echo esc_html( $row->post_title ); ?></strong>
                    </div>
                    <span class="em-count-badge"><?php echo esc_html( number_format_i18n( (int) $row->views ) ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
