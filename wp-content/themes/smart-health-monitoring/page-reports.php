<?php
/**
 * Template Name: Reports
 * Template for health reports and data export
 */

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

get_header();
?>

<main id="main-content" class="shm-main shm-reports-page" role="main">
	<div class="container-wide">
		<div class="shm-dashboard-layout">
			<aside class="shm-dashboard-sidebar">
				<nav class="shm-dashboard-nav" aria-label="<?php esc_attr_e( 'Dashboard Navigation', 'smart-health-monitoring' ); ?>">
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'dashboard' ) ) ); ?>" class="shm-nav-item">
						<span class="shm-nav-icon">📊</span>
						<?php esc_html_e( 'Dashboard', 'smart-health-monitoring' ); ?>
					</a>
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="shm-nav-item active">
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
			</aside>

			<div class="shm-dashboard-content">
				<div class="shm-page-header">
					<h1><?php esc_html_e( 'Health Reports', 'smart-health-monitoring' ); ?></h1>
					<p class="shm-page-description">
						<?php esc_html_e( 'View, filter, and export your health data.', 'smart-health-monitoring' ); ?>
					</p>
				</div>

				<div class="shm-report-filters bg-white p-6 rounded-lg shadow mt-6">
					<h3><?php esc_html_e( 'Filter & Export', 'smart-health-monitoring' ); ?></h3>
					<form id="report-filter-form" class="shm-filter-form">
						<div class="shm-form-row grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
							<div class="shm-form-group">
								<label for="filter-from"><?php esc_html_e( 'From Date', 'smart-health-monitoring' ); ?></label>
								<input type="date" id="filter-from" name="from" class="shm-input" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-30 days' ) ) ); ?>">
							</div>

							<div class="shm-form-group">
								<label for="filter-to"><?php esc_html_e( 'To Date', 'smart-health-monitoring' ); ?></label>
								<input type="date" id="filter-to" name="to" class="shm-input" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
							</div>

							<div class="shm-form-group">
								<label for="filter-metric"><?php esc_html_e( 'Metric Type', 'smart-health-monitoring' ); ?></label>
								<select id="filter-metric" name="metric" class="shm-select">
									<option value="all"><?php esc_html_e( 'All Metrics', 'smart-health-monitoring' ); ?></option>
									<option value="bp"><?php esc_html_e( 'Blood Pressure', 'smart-health-monitoring' ); ?></option>
									<option value="glucose"><?php esc_html_e( 'Blood Glucose', 'smart-health-monitoring' ); ?></option>
									<option value="activity"><?php esc_html_e( 'Activity', 'smart-health-monitoring' ); ?></option>
								</select>
							</div>
						</div>

						<div class="shm-form-actions flex gap-4 mt-4">
							<button type="submit" class="button button-primary">
								<?php esc_html_e( 'Apply Filters', 'smart-health-monitoring' ); ?>
							</button>
							<button type="button" id="export-csv" class="button button-secondary">
								<?php esc_html_e( 'Export CSV', 'smart-health-monitoring' ); ?>
							</button>
							<button type="button" id="export-pdf" class="button button-secondary">
								<?php esc_html_e( 'Export PDF', 'smart-health-monitoring' ); ?>
							</button>
						</div>
					</form>
				</div>

				<div class="shm-report-summary grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
					<div class="shm-summary-card bg-white p-6 rounded-lg shadow">
						<h4><?php esc_html_e( 'Total Readings', 'smart-health-monitoring' ); ?></h4>
						<div class="shm-summary-value text-4xl font-bold text-primary mt-2">
							<span id="total-readings">-</span>
						</div>
					</div>

					<div class="shm-summary-card bg-white p-6 rounded-lg shadow">
						<h4><?php esc_html_e( 'Average BP', 'smart-health-monitoring' ); ?></h4>
						<div class="shm-summary-value text-4xl font-bold text-primary mt-2">
							<span id="avg-bp">-</span>
						</div>
					</div>

					<div class="shm-summary-card bg-white p-6 rounded-lg shadow">
						<h4><?php esc_html_e( 'Average Glucose', 'smart-health-monitoring' ); ?></h4>
						<div class="shm-summary-value text-4xl font-bold text-primary mt-2">
							<span id="avg-glucose">-</span>
						</div>
					</div>
				</div>

				<div class="shm-report-data bg-white p-6 rounded-lg shadow mt-8">
					<h3><?php esc_html_e( 'Data Table', 'smart-health-monitoring' ); ?></h3>
					<div id="report-table" class="mt-4">
						<div class="skeleton" style="height: 400px;"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
