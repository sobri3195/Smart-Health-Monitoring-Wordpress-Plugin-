<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Patients', 'shm-data-integrations' ); ?></h1>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'shm-data-integrations' ); ?></th>
				<th><?php esc_html_e( 'Email', 'shm-data-integrations' ); ?></th>
				<th><?php esc_html_e( 'Role', 'shm-data-integrations' ); ?></th>
				<th><?php esc_html_e( 'Integrations', 'shm-data-integrations' ); ?></th>
				<th><?php esc_html_e( 'Last Sync', 'shm-data-integrations' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'shm-data-integrations' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $patients as $patient ) : ?>
				<?php
				global $wpdb;
				$connections = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT connector, last_sync_at FROM {$wpdb->prefix}shm_connections WHERE user_id = %d AND status = 'active'",
						$patient->ID
					)
				);
				?>
				<tr>
					<td><?php echo esc_html( $patient->display_name ); ?></td>
					<td><?php echo esc_html( $patient->user_email ); ?></td>
					<td><?php echo esc_html( implode( ', ', $patient->roles ) ); ?></td>
					<td>
						<?php
						if ( $connections ) {
							echo esc_html( implode( ', ', wp_list_pluck( $connections, 'connector' ) ) );
						} else {
							esc_html_e( 'None', 'shm-data-integrations' );
						}
						?>
					</td>
					<td>
						<?php
						if ( $connections ) {
							$last_sync = max( wp_list_pluck( $connections, 'last_sync_at' ) );
							echo $last_sync ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $last_sync ) ) ) : '-';
						} else {
							echo '-';
						}
						?>
					</td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=shm-dashboard&user_id=' . $patient->ID ) ); ?>" class="button button-small">
							<?php esc_html_e( 'View', 'shm-data-integrations' ); ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
