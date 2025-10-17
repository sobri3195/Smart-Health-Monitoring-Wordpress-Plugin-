<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap shm-dashboard">
	<h1><?php esc_html_e( 'Health Dashboard', 'shm-data-integrations' ); ?></h1>

	<div class="shm-notice">
		<p><strong><?php esc_html_e( 'Disclaimer:', 'shm-data-integrations' ); ?></strong> <?php esc_html_e( 'This is not a medical device. Always consult with healthcare professionals for medical advice.', 'shm-data-integrations' ); ?></p>
	</div>

	<div class="shm-metrics-grid">
		<div class="shm-metric-card">
			<h3><?php esc_html_e( 'Blood Pressure', 'shm-data-integrations' ); ?></h3>
			<?php if ( ! empty( $summary['bp']['count'] ) ) : ?>
				<div class="shm-metric-value">
					<?php echo esc_html( round( $summary['bp']['avg_systolic'] ) ); ?>/<?php echo esc_html( round( $summary['bp']['avg_diastolic'] ) ); ?>
					<span class="shm-unit">mmHg</span>
				</div>
				<div class="shm-metric-label"><?php esc_html_e( 'Average (30 days)', 'shm-data-integrations' ); ?></div>
			<?php else : ?>
				<p><?php esc_html_e( 'No data available', 'shm-data-integrations' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="shm-metric-card">
			<h3><?php esc_html_e( 'Blood Glucose', 'shm-data-integrations' ); ?></h3>
			<?php if ( ! empty( $summary['glucose']['count'] ) ) : ?>
				<div class="shm-metric-value">
					<?php echo esc_html( round( $summary['glucose']['avg_value'], 1 ) ); ?>
					<span class="shm-unit">mg/dL</span>
				</div>
				<div class="shm-metric-label"><?php esc_html_e( 'Average (30 days)', 'shm-data-integrations' ); ?></div>
			<?php else : ?>
				<p><?php esc_html_e( 'No data available', 'shm-data-integrations' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="shm-metric-card">
			<h3><?php esc_html_e( 'Activity', 'shm-data-integrations' ); ?></h3>
			<?php if ( ! empty( $summary['activity']['count'] ) ) : ?>
				<div class="shm-metric-value">
					<?php echo esc_html( number_format( $summary['activity']['avg_steps'] ) ); ?>
					<span class="shm-unit">steps/day</span>
				</div>
				<div class="shm-metric-label"><?php esc_html_e( 'Average (30 days)', 'shm-data-integrations' ); ?></div>
			<?php else : ?>
				<p><?php esc_html_e( 'No data available', 'shm-data-integrations' ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="shm-charts">
		<canvas id="shm-bp-chart" width="400" height="200"></canvas>
	</div>

	<script>
	jQuery(document).ready(function($) {
		const ctx = document.getElementById('shm-bp-chart');
		if (ctx) {
			fetch(shmAdmin.apiUrl + '/metrics/series?metric=bp&from=<?php echo esc_js( date( 'Y-m-d', strtotime( '-30 days' ) ) ); ?>&to=<?php echo esc_js( date( 'Y-m-d' ) ); ?>', {
				headers: {
					'X-WP-Nonce': shmAdmin.nonce
				}
			})
			.then(response => response.json())
			.then(data => {
				new Chart(ctx, {
					type: 'line',
					data: {
						labels: data.map(d => d.taken_at),
						datasets: [{
							label: 'Systolic',
							data: data.map(d => d.systolic),
							borderColor: 'rgb(255, 99, 132)',
							tension: 0.1
						}, {
							label: 'Diastolic',
							data: data.map(d => d.diastolic),
							borderColor: 'rgb(54, 162, 235)',
							tension: 0.1
						}]
					},
					options: {
						responsive: true,
						plugins: {
							title: {
								display: true,
								text: '<?php esc_html_e( 'Blood Pressure Trend', 'shm-data-integrations' ); ?>'
							}
						}
					}
				});
			});
		}
	});
	</script>
</div>
