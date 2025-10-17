<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Wearable Integrations', 'shm-data-integrations' ); ?></h1>

	<p><?php esc_html_e( 'Connect your wearable devices to automatically sync your health data.', 'shm-data-integrations' ); ?></p>

	<div class="shm-integrations-grid">
		<?php foreach ( $connectors as $name => $connector ) : ?>
			<?php
			$connection = SHM_Connectors::get_user_connection( $user_id, $name );
			$is_connected = ! empty( $connection );
			?>
			<div class="shm-integration-card">
				<h3><?php echo esc_html( $connector->get_label() ); ?></h3>

				<?php if ( $is_connected ) : ?>
					<div class="shm-status shm-status-connected">
						<?php esc_html_e( 'Connected', 'shm-data-integrations' ); ?>
					</div>
					<?php if ( $connection->last_sync_at ) : ?>
						<p class="shm-last-sync">
							<?php
							printf(
								/* translators: %s: date and time */
								esc_html__( 'Last sync: %s', 'shm-data-integrations' ),
								esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $connection->last_sync_at ) ) )
							);
							?>
						</p>
					<?php endif; ?>
					<div class="shm-integration-actions">
						<button class="button shm-sync-now" data-connector="<?php echo esc_attr( $name ); ?>">
							<?php esc_html_e( 'Sync Now', 'shm-data-integrations' ); ?>
						</button>
						<button class="button shm-disconnect" data-connector="<?php echo esc_attr( $name ); ?>">
							<?php esc_html_e( 'Disconnect', 'shm-data-integrations' ); ?>
						</button>
					</div>
				<?php else : ?>
					<div class="shm-status shm-status-disconnected">
						<?php esc_html_e( 'Not Connected', 'shm-data-integrations' ); ?>
					</div>
					<div class="shm-integration-actions">
						<?php
						$auth_url = $connector->get_auth_url( $user_id );
						if ( '#' === $auth_url ) :
							?>
							<button class="button" disabled>
								<?php esc_html_e( 'Not Available', 'shm-data-integrations' ); ?>
							</button>
						<?php else : ?>
							<a href="<?php echo esc_url( $auth_url ); ?>" class="button button-primary">
								<?php esc_html_e( 'Connect', 'shm-data-integrations' ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('.shm-sync-now').on('click', function() {
		const $btn = $(this);
		const connector = $btn.data('connector');

		$btn.prop('disabled', true).text('<?php esc_html_e( 'Syncing...', 'shm-data-integrations' ); ?>');

		$.ajax({
			url: shmAdmin.ajaxUrl,
			method: 'POST',
			data: {
				action: 'shm_sync_manual',
				connector: connector,
				nonce: shmAdmin.nonce
			},
			success: function(response) {
				if (response.success) {
					alert('<?php esc_html_e( 'Sync completed successfully!', 'shm-data-integrations' ); ?>');
					location.reload();
				} else {
					alert('<?php esc_html_e( 'Sync failed. Please try again.', 'shm-data-integrations' ); ?>');
				}
			},
			complete: function() {
				$btn.prop('disabled', false).text('<?php esc_html_e( 'Sync Now', 'shm-data-integrations' ); ?>');
			}
		});
	});

	$('.shm-disconnect').on('click', function() {
		if (!confirm('<?php esc_html_e( 'Are you sure you want to disconnect this device?', 'shm-data-integrations' ); ?>')) {
			return;
		}

		const connector = $(this).data('connector');
		location.href = '<?php echo esc_url( admin_url( 'admin.php?page=shm-integrations&action=disconnect&connector=' ) ); ?>' + connector;
	});
});
</script>
