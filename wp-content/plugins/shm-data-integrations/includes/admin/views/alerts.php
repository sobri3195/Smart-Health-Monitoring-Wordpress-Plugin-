<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Health Alerts', 'shm-data-integrations' ); ?></h1>

	<?php if ( empty( $alerts ) ) : ?>
		<p><?php esc_html_e( 'No unresolved alerts.', 'shm-data-integrations' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'shm-data-integrations' ); ?></th>
					<th><?php esc_html_e( 'Type', 'shm-data-integrations' ); ?></th>
					<th><?php esc_html_e( 'Message', 'shm-data-integrations' ); ?></th>
					<th><?php esc_html_e( 'Severity', 'shm-data-integrations' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'shm-data-integrations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $alerts as $alert ) : ?>
					<tr class="shm-alert-<?php echo esc_attr( $alert->severity ); ?>">
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $alert->created_at ) ) ); ?></td>
						<td><?php echo esc_html( $alert->type ); ?></td>
						<td><?php echo esc_html( $alert->message ); ?></td>
						<td><span class="shm-badge shm-badge-<?php echo esc_attr( $alert->severity ); ?>"><?php echo esc_html( $alert->severity ); ?></span></td>
						<td>
							<button class="button button-small shm-resolve-alert" data-alert-id="<?php echo esc_attr( $alert->id ); ?>">
								<?php esc_html_e( 'Resolve', 'shm-data-integrations' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('.shm-resolve-alert').on('click', function() {
		const alertId = $(this).data('alert-id');
		const $row = $(this).closest('tr');

		$.ajax({
			url: shmAdmin.apiUrl + '/alerts/' + alertId + '/resolve',
			method: 'POST',
			beforeSend: function(xhr) {
				xhr.setRequestHeader('X-WP-Nonce', shmAdmin.nonce);
			},
			success: function() {
				$row.fadeOut();
			}
		});
	});
});
</script>
