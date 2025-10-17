<?php
/**
 * Template Name: Dashboard
 * Template for displaying user health dashboard
 */

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

get_header();

$user_id = get_current_user_id();
?>

<main id="main-content" class="shm-main shm-dashboard-page" role="main">
	<div class="container-wide">
		<div class="shm-dashboard-layout">
			<aside class="shm-dashboard-sidebar">
				<nav class="shm-dashboard-nav" aria-label="<?php esc_attr_e( 'Dashboard Navigation', 'smart-health-monitoring' ); ?>">
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="shm-nav-item active">
						<span class="shm-nav-icon">📊</span>
						<?php esc_html_e( 'Dashboard', 'smart-health-monitoring' ); ?>
					</a>
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'reports' ) ) ); ?>" class="shm-nav-item">
						<span class="shm-nav-icon">📈</span>
						<?php esc_html_e( 'Reports', 'smart-health-monitoring' ); ?>
					</a>
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'integrations' ) ) ); ?>" class="shm-nav-item">
						<span class="shm-nav-icon">🔗</span>
						<?php esc_html_e( 'Integrations', 'smart-health-monitoring' ); ?>
					</a>
					<a href="<?php echo esc_url( get_edit_profile_url() ); ?>" class="shm-nav-item">
						<span class="shm-nav-icon">⚙️</span>
						<?php esc_html_e( 'Settings', 'smart-health-monitoring' ); ?>
					</a>
				</nav>

				<?php if ( is_active_sidebar( 'dashboard-sidebar' ) ) : ?>
					<div class="shm-sidebar-widgets">
						<?php dynamic_sidebar( 'dashboard-sidebar' ); ?>
					</div>
				<?php endif; ?>
			</aside>

			<div class="shm-dashboard-content">
				<div class="shm-dashboard-header">
					<?php shm_theme_user_greeting(); ?>
					<div class="shm-date-selector">
						<label for="date-range"><?php esc_html_e( 'Date Range:', 'smart-health-monitoring' ); ?></label>
						<select id="date-range" class="shm-select">
							<option value="7d"><?php esc_html_e( 'Last 7 days', 'smart-health-monitoring' ); ?></option>
							<option value="30d" selected><?php esc_html_e( 'Last 30 days', 'smart-health-monitoring' ); ?></option>
							<option value="90d"><?php esc_html_e( 'Last 90 days', 'smart-health-monitoring' ); ?></option>
						</select>
					</div>
				</div>

				<div class="shm-metrics-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
					<?php
					echo do_shortcode( '[shm_metric metric="bp" range="30d"]' );
					echo do_shortcode( '[shm_metric metric="glucose" range="30d"]' );
					echo do_shortcode( '[shm_metric metric="activity" range="30d"]' );
					?>
					
					<div class="shm-metric-card shm-metric-hr">
						<div class="shm-metric-icon">
							<span class="dashicons dashicons-heart"></span>
						</div>
						<div class="shm-metric-content">
							<h3><?php esc_html_e( 'Heart Rate', 'smart-health-monitoring' ); ?></h3>
							<div class="shm-metric-value">
								<span class="shm-loading skeleton" style="width: 80px; height: 40px; display: inline-block;"></span>
								<span class="shm-unit">bpm</span>
							</div>
							<div class="shm-metric-status">
								<?php esc_html_e( 'Resting Average', 'smart-health-monitoring' ); ?>
							</div>
						</div>
					</div>
				</div>

				<div class="shm-alerts-section mt-8">
					<h3><?php esc_html_e( 'Recent Alerts', 'smart-health-monitoring' ); ?></h3>
					<?php echo do_shortcode( '[shm_alerts]' ); ?>
				</div>

				<div class="shm-charts-section mt-8">
					<div class="shm-chart-card">
						<h3><?php esc_html_e( 'Blood Pressure Trend', 'smart-health-monitoring' ); ?></h3>
						<?php echo do_shortcode( '[shm_chart metric="bp" range="30d"]' ); ?>
					</div>

					<div class="shm-chart-card mt-6">
						<h3><?php esc_html_e( 'Glucose Levels', 'smart-health-monitoring' ); ?></h3>
						<?php echo do_shortcode( '[shm_chart metric="glucose" range="30d"]' ); ?>
					</div>

					<div class="shm-chart-card mt-6">
						<h3><?php esc_html_e( 'Activity Overview', 'smart-health-monitoring' ); ?></h3>
						<?php echo do_shortcode( '[shm_chart metric="activity" range="30d"]' ); ?>
					</div>
				</div>

				<div class="shm-recent-logs mt-8">
					<div class="shm-section-header flex justify-between items-center">
						<h3><?php esc_html_e( 'Recent Entries', 'smart-health-monitoring' ); ?></h3>
						<button class="button button-sm" id="add-entry">
							<?php esc_html_e( '+ Add Entry', 'smart-health-monitoring' ); ?>
						</button>
					</div>
					<div class="shm-logs-table mt-4" id="recent-logs">
						<div class="skeleton" style="height: 200px;"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
