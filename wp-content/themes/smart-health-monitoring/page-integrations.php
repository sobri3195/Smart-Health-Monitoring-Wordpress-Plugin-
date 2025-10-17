<?php
/**
 * Template Name: Integrations
 * Template for wearable device integrations
 */

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

get_header();
?>

<main id="main-content" class="shm-main shm-integrations-page" role="main">
	<div class="container-wide">
		<div class="shm-dashboard-layout">
			<aside class="shm-dashboard-sidebar">
				<nav class="shm-dashboard-nav" aria-label="<?php esc_attr_e( 'Dashboard Navigation', 'smart-health-monitoring' ); ?>">
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'dashboard' ) ) ); ?>" class="shm-nav-item">
						<span class="shm-nav-icon">📊</span>
						<?php esc_html_e( 'Dashboard', 'smart-health-monitoring' ); ?>
					</a>
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'reports' ) ) ); ?>" class="shm-nav-item">
						<span class="shm-nav-icon">📈</span>
						<?php esc_html_e( 'Reports', 'smart-health-monitoring' ); ?>
					</a>
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="shm-nav-item active">
						<span class="shm-nav-icon">🔗</span>
						<?php esc_html_e( 'Integrations', 'smart-health-monitoring' ); ?>
					</a>
					<a href="<?php echo esc_url( get_edit_profile_url() ); ?>" class="shm-nav-item">
						<span class="shm-nav-icon">⚙️</span>
						<?php esc_html_e( 'Settings', 'smart-health-monitoring' ); ?>
					</a>
				</nav>
			</aside>

			<div class="shm-dashboard-content">
				<div class="shm-page-header">
					<h1><?php esc_html_e( 'Wearable Integrations', 'smart-health-monitoring' ); ?></h1>
					<p class="shm-page-description">
						<?php esc_html_e( 'Connect your wearable devices to automatically sync your health data.', 'smart-health-monitoring' ); ?>
					</p>
				</div>

				<div class="shm-integration-status mt-6">
					<?php echo do_shortcode( '[shm_integrations]' ); ?>
				</div>

				<div class="shm-integrations-grid grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
					<?php
					if ( function_exists( 'SHM_Connectors::get_all_connectors' ) ) {
						$connectors = SHM_Connectors::get_all_connectors();
						$user_id    = get_current_user_id();

						foreach ( $connectors as $name => $connector ) :
							$connection   = SHM_Connectors::get_user_connection( $user_id, $name );
							$is_connected = ! empty( $connection );
							?>
							<div class="shm-integration-card bg-white p-6 rounded-lg shadow">
								<div class="shm-integration-header flex justify-between items-start">
									<div>
										<h3><?php echo esc_html( $connector->get_label() ); ?></h3>
										<?php if ( $is_connected ) : ?>
											<span class="shm-badge shm-badge-success">
												<?php esc_html_e( 'Connected', 'smart-health-monitoring' ); ?>
											</span>
										<?php else : ?>
											<span class="shm-badge shm-badge-secondary">
												<?php esc_html_e( 'Not Connected', 'smart-health-monitoring' ); ?>
											</span>
										<?php endif; ?>
									</div>
									<div class="shm-integration-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<rect x="9" y="2" width="6" height="20" rx="2" ry="2"></rect>
										</svg>
									</div>
								</div>

								<?php if ( $is_connected ) : ?>
									<div class="shm-connection-info mt-4">
										<?php if ( $connection->last_sync_at ) : ?>
											<p class="text-sm text-light">
												<?php
												printf(
													/* translators: %s: date and time */
													esc_html__( 'Last synced: %s', 'smart-health-monitoring' ),
													esc_html( human_time_diff( strtotime( $connection->last_sync_at ), current_time( 'timestamp' ) ) ) . ' ' . esc_html__( 'ago', 'smart-health-monitoring' )
												);
												?>
											</p>
										<?php endif; ?>

										<div class="shm-integration-actions flex gap-2 mt-4">
											<button class="button button-primary button-sm shm-sync-now" data-connector="<?php echo esc_attr( $name ); ?>">
												<?php esc_html_e( 'Sync Now', 'smart-health-monitoring' ); ?>
											</button>
											<button class="button button-secondary button-sm shm-disconnect" data-connector="<?php echo esc_attr( $name ); ?>">
												<?php esc_html_e( 'Disconnect', 'smart-health-monitoring' ); ?>
											</button>
										</div>
									</div>
								<?php else : ?>
									<div class="shm-connection-info mt-4">
										<p class="text-sm text-light">
											<?php esc_html_e( 'Connect to automatically sync your health data.', 'smart-health-monitoring' ); ?>
										</p>

										<div class="shm-integration-actions mt-4">
											<?php
											$auth_url = $connector->get_auth_url( $user_id );
											if ( '#' !== $auth_url ) :
												?>
												<a href="<?php echo esc_url( $auth_url ); ?>" class="button button-primary">
													<?php esc_html_e( 'Connect', 'smart-health-monitoring' ); ?>
												</a>
											<?php else : ?>
												<button class="button button-secondary" disabled>
													<?php esc_html_e( 'Coming Soon', 'smart-health-monitoring' ); ?>
												</button>
											<?php endif; ?>
										</div>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach;
					} else {
						?>
						<div class="shm-notice bg-warning p-4 rounded">
							<p><?php esc_html_e( 'Please ensure the SHM Data & Integrations plugin is installed and activated.', 'smart-health-monitoring' ); ?></p>
						</div>
						<?php
					}
					?>
				</div>

				<div class="shm-integration-help bg-white p-6 rounded-lg shadow mt-8">
					<h3><?php esc_html_e( 'Need Help?', 'smart-health-monitoring' ); ?></h3>
					<p><?php esc_html_e( 'Learn how to connect your devices and sync your data.', 'smart-health-monitoring' ); ?></p>
					<ul class="shm-help-list mt-4">
						<li><?php esc_html_e( 'Make sure you have an account with the wearable service', 'smart-health-monitoring' ); ?></li>
						<li><?php esc_html_e( 'Click Connect and authorize the app', 'smart-health-monitoring' ); ?></li>
						<li><?php esc_html_e( 'Your data will sync automatically every 30 minutes', 'smart-health-monitoring' ); ?></li>
						<li><?php esc_html_e( 'You can manually sync at any time', 'smart-health-monitoring' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
