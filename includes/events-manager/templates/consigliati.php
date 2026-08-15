<?php
namespace OpenEvents;
/**
 * Vista "Consigliati" (eventi con promozione a pagamento, solo
 * amministratori). Incluso da Widget_Events_Manager::render() con
 * `include`: condivide lo scope locale del metodo chiamante ($this,
 * $consigliati_events, $consigliati_notice, $consigliati_error,
 * $consigliati_filter).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$status_labels = [
	'paid'            => esc_html__( 'Pagato', 'open-events' ),
	'pending_payment' => esc_html__( 'In attesa di pagamento', 'open-events' ),
	'expired'         => esc_html__( 'Scaduto', 'open-events' ),
];
$clean_url = remove_query_arg( [ 'view', 'status', 'oe_featured_action', 'post_id', '_wpnonce' ] );
?>
<div class="em-form-container em-dashboard-view em-consigliati-view">
	<?php $this->render_breadcrumbs( [
		[ 'label' => esc_html__( 'Dashboard', 'open-events' ), 'url' => $clean_url ],
		[ 'label' => esc_html__( 'Consigliati', 'open-events' ), 'url' => '' ],
	] ); ?>

	<div class="em-back-link">
		<a href="<?php echo esc_url( $clean_url ); ?>">&larr; <?php esc_html_e( 'Torna alla Dashboard', 'open-events' ); ?></a>
	</div>

	<div class="em-dashboard-header">
		<h2><?php $this->render_icon( 'star' ); ?> <?php esc_html_e( 'Eventi Consigliati', 'open-events' ); ?> <span class="em-count-badge"><?php echo count( $consigliati_events ); ?></span></h2>
	</div>

	<?php if ( $consigliati_notice ) : ?>
		<div class="em-alert success"><?php echo esc_html( $consigliati_notice ); ?></div>
	<?php endif; ?>
	<?php if ( $consigliati_error ) : ?>
		<div class="em-alert error"><?php echo esc_html( $consigliati_error ); ?></div>
	<?php endif; ?>

	<div class="em-consigliati-filters">
		<a href="<?php echo esc_url( remove_query_arg( 'status' ) ); ?>" class="em-filter-pill<?php echo '' === $consigliati_filter ? ' active' : ''; ?>"><?php esc_html_e( 'Tutti', 'open-events' ); ?></a>
		<?php foreach ( $status_labels as $status_key => $status_label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'status', $status_key ) ); ?>" class="em-filter-pill<?php echo $consigliati_filter === $status_key ? ' active' : ''; ?>"><?php echo esc_html( $status_label ); ?></a>
		<?php endforeach; ?>
	</div>

	<?php if ( empty( $consigliati_events ) ) : ?>
		<div class="em-empty-state">
			<?php $this->render_icon( 'star' ); ?>
			<p class="em-empty-msg"><?php esc_html_e( 'Nessun evento Consigliato al momento.', 'open-events' ); ?></p>
		</div>
	<?php else : ?>
		<div class="em-items-list">
			<?php foreach ( $consigliati_events as $row ) :
				$author = get_userdata( $row->post_author );
				$amount_display = $row->featured_amount ? number_format_i18n( $row->featured_amount / 100, 2 ) : '—';
				$date_display = $row->featured_date ? date_i18n( get_option( 'date_format' ), strtotime( $row->featured_date ) ) : '—';
				$post_status_obj = get_post_status_object( $row->post_status );

				$confirm_url = wp_nonce_url(
					add_query_arg( [ 'oe_featured_action' => 'confirm', 'post_id' => $row->ID ], $clean_url ),
					'oe_featured_confirm_' . $row->ID
				);
				$revoke_url = wp_nonce_url(
					add_query_arg( [ 'oe_featured_action' => 'revoke', 'post_id' => $row->ID ], $clean_url ),
					'oe_featured_revoke_' . $row->ID
				);
				?>
				<div class="em-item-row">
					<div class="em-item-info">
						<strong class="em-item-title">
							<a href="<?php echo esc_url( add_query_arg( [ 'view' => 'tribe_events', 'edit_id' => $row->ID ], remove_query_arg( [ 'status', 'oe_featured_action', 'post_id', '_wpnonce' ] ) ) ); ?>" class="em-item-title-link"><?php echo esc_html( $row->post_title ); ?></a>
						</strong>
						<span class="em-item-meta">
							<?php echo esc_html( $author ? $author->display_name : esc_html__( 'Sconosciuto', 'open-events' ) ); ?>
							· <?php echo esc_html( $date_display ); ?>
							· <?php echo esc_html( $amount_display ); ?>
							· <?php echo esc_html( $post_status_obj ? $post_status_obj->label : $row->post_status ); ?>
						</span>
					</div>
					<span class="em-status-badge <?php echo esc_attr( $row->featured_status ); ?>"><?php echo esc_html( $status_labels[ $row->featured_status ] ?? $row->featured_status ); ?></span>
					<?php
					$row_receipt_url = ( 'paid' === $row->featured_status ) ? open_events_featured_get_receipt_url( $row->ID ) : '';
					if ( $row_receipt_url ) : ?>
						<a href="<?php echo esc_url( $row_receipt_url ); ?>" class="em-receipt-link" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Ricevuta Stripe', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Ricevuta Stripe', 'open-events' ); ?>">
							<?php $this->render_icon( 'link' ); ?> <?php esc_html_e( 'Ricevuta', 'open-events' ); ?>
						</a>
					<?php endif; ?>
					<?php if ( 'paid' !== $row->featured_status ) : ?>
						<a href="<?php echo esc_url( $confirm_url ); ?>" class="em-action-btn publish-btn" onclick="return confirm('<?php echo esc_js( __( 'Confermare manualmente questo evento come Consigliato?', 'open-events' ) ); ?>');" title="<?php esc_attr_e( 'Conferma manualmente', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Conferma manualmente', 'open-events' ); ?>">
							<?php $this->render_icon( 'check' ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $revoke_url ); ?>" class="em-action-btn delete-btn" onclick="return confirm('<?php echo esc_js( __( 'Revocare lo stato Consigliato per questo evento?', 'open-events' ) ); ?>');" title="<?php esc_attr_e( 'Revoca', 'open-events' ); ?>" aria-label="<?php esc_attr_e( 'Revoca', 'open-events' ); ?>">
							<?php $this->render_icon( 'trash' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
