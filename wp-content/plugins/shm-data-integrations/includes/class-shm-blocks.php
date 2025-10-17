<?php
/**
 * Gutenberg blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_Blocks {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
	}

	public static function register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'shm/metric-card',
			array(
				'render_callback' => array( __CLASS__, 'render_metric_card' ),
				'attributes'      => array(
					'metric' => array(
						'type'    => 'string',
						'default' => 'bp',
					),
					'range'  => array(
						'type'    => 'string',
						'default' => '7d',
					),
				),
			)
		);

		register_block_type(
			'shm/weekly-trend',
			array(
				'render_callback' => array( __CLASS__, 'render_weekly_trend' ),
				'attributes'      => array(
					'metric'  => array(
						'type'    => 'string',
						'default' => 'bp',
					),
					'range'   => array(
						'type'    => 'string',
						'default' => '7d',
					),
					'showAvg' => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
			)
		);

		register_block_type(
			'shm/alerts-list',
			array(
				'render_callback' => array( __CLASS__, 'render_alerts_list' ),
			)
		);

		register_block_type(
			'shm/integration-status',
			array(
				'render_callback' => array( __CLASS__, 'render_integration_status' ),
			)
		);
	}

	public static function render_metric_card( $attributes ) {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your health data.', 'shm-data-integrations' ) . '</p>';
		}

		$user_id = get_current_user_id();
		$metric  = $attributes['metric'];
		$range   = $attributes['range'];

		$summary = SHM_Database::get_summary( $user_id, $range );

		ob_start();
		?>
		<div class="shm-metric-card shm-metric-<?php echo esc_attr( $metric ); ?>">
			<?php
			switch ( $metric ) {
				case 'bp':
					if ( ! empty( $summary['bp']['count'] ) ) {
						?>
						<div class="shm-metric-icon">
							<span class="dashicons dashicons-heart"></span>
						</div>
						<div class="shm-metric-content">
							<h3><?php esc_html_e( 'Blood Pressure', 'shm-data-integrations' ); ?></h3>
							<div class="shm-metric-value">
								<?php echo esc_html( round( $summary['bp']['avg_systolic'] ) ); ?>/<?php echo esc_html( round( $summary['bp']['avg_diastolic'] ) ); ?>
								<span class="shm-unit">mmHg</span>
							</div>
							<div class="shm-metric-status <?php echo esc_attr( self::get_bp_status_class( $summary['bp']['avg_systolic'], $summary['bp']['avg_diastolic'] ) ); ?>">
								<?php echo esc_html( self::get_bp_status_label( $summary['bp']['avg_systolic'], $summary['bp']['avg_diastolic'] ) ); ?>
							</div>
						</div>
						<?php
					} else {
						echo '<p>' . esc_html__( 'No data available', 'shm-data-integrations' ) . '</p>';
					}
					break;

				case 'glucose':
					if ( ! empty( $summary['glucose']['count'] ) ) {
						?>
						<div class="shm-metric-icon">
							<span class="dashicons dashicons-analytics"></span>
						</div>
						<div class="shm-metric-content">
							<h3><?php esc_html_e( 'Blood Glucose', 'shm-data-integrations' ); ?></h3>
							<div class="shm-metric-value">
								<?php echo esc_html( round( $summary['glucose']['avg_value'], 1 ) ); ?>
								<span class="shm-unit">mg/dL</span>
							</div>
							<div class="shm-metric-status <?php echo esc_attr( self::get_glucose_status_class( $summary['glucose']['avg_value'] ) ); ?>">
								<?php echo esc_html( self::get_glucose_status_label( $summary['glucose']['avg_value'] ) ); ?>
							</div>
						</div>
						<?php
					} else {
						echo '<p>' . esc_html__( 'No data available', 'shm-data-integrations' ) . '</p>';
					}
					break;

				case 'activity':
					if ( ! empty( $summary['activity']['count'] ) ) {
						?>
						<div class="shm-metric-icon">
							<span class="dashicons dashicons-universal-access"></span>
						</div>
						<div class="shm-metric-content">
							<h3><?php esc_html_e( 'Daily Steps', 'shm-data-integrations' ); ?></h3>
							<div class="shm-metric-value">
								<?php echo esc_html( number_format( $summary['activity']['avg_steps'] ) ); ?>
								<span class="shm-unit">steps</span>
							</div>
							<div class="shm-metric-status">
								<?php
								printf(
									/* translators: %s: number of calories */
									esc_html__( '%s cal burned', 'shm-data-integrations' ),
									esc_html( number_format( $summary['activity']['total_calories'] ) )
								);
								?>
							</div>
						</div>
						<?php
					} else {
						echo '<p>' . esc_html__( 'No data available', 'shm-data-integrations' ) . '</p>';
					}
					break;
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_weekly_trend( $attributes ) {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your health data.', 'shm-data-integrations' ) . '</p>';
		}

		$user_id = get_current_user_id();
		$metric  = $attributes['metric'];
		$range   = $attributes['range'];

		$chart_id = 'shm-chart-' . wp_rand();

		ob_start();
		?>
		<div class="shm-chart-container">
			<canvas id="<?php echo esc_attr( $chart_id ); ?>" width="400" height="200"></canvas>
		</div>
		<script>
		(function() {
			const ctx = document.getElementById('<?php echo esc_js( $chart_id ); ?>');
			if (ctx && typeof Chart !== 'undefined') {
				const days = parseInt('<?php echo esc_js( str_replace( 'd', '', $range ) ); ?>');
				const from = new Date();
				from.setDate(from.getDate() - days);
				const to = new Date();

				fetch('/wp-json/shm/v1/metrics/series?metric=<?php echo esc_js( $metric ); ?>&from=' + from.toISOString().split('T')[0] + '&to=' + to.toISOString().split('T')[0], {
					headers: {
						'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
					}
				})
				.then(response => response.json())
				.then(data => {
					const config = <?php echo wp_json_encode( self::get_chart_config( $metric ) ); ?>;
					config.data.labels = data.map(d => d.taken_at);
					
					<?php if ( 'bp' === $metric ) : ?>
						config.data.datasets[0].data = data.map(d => d.systolic);
						config.data.datasets[1].data = data.map(d => d.diastolic);
					<?php elseif ( 'glucose' === $metric ) : ?>
						config.data.datasets[0].data = data.map(d => d.value);
					<?php else : ?>
						config.data.datasets[0].data = data.map(d => d.steps);
					<?php endif; ?>

					new Chart(ctx, config);
				});
			}
		})();
		</script>
		<?php
		return ob_get_clean();
	}

	public static function render_alerts_list() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your alerts.', 'shm-data-integrations' ) . '</p>';
		}

		$user_id = get_current_user_id();
		$alerts  = SHM_Database::get_alerts( $user_id, false );

		ob_start();
		?>
		<div class="shm-alerts-list">
			<?php if ( empty( $alerts ) ) : ?>
				<p class="shm-no-alerts"><?php esc_html_e( 'No active alerts.', 'shm-data-integrations' ); ?></p>
			<?php else : ?>
				<ul>
					<?php foreach ( $alerts as $alert ) : ?>
						<li class="shm-alert shm-alert-<?php echo esc_attr( $alert->severity ); ?>">
							<span class="shm-alert-icon"></span>
							<div class="shm-alert-content">
								<strong><?php echo esc_html( $alert->message ); ?></strong>
								<span class="shm-alert-time"><?php echo esc_html( human_time_diff( strtotime( $alert->created_at ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'ago', 'shm-data-integrations' ); ?></span>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_integration_status() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your integrations.', 'shm-data-integrations' ) . '</p>';
		}

		$user_id    = get_current_user_id();
		$connectors = SHM_Connectors::get_all_connectors();

		ob_start();
		?>
		<div class="shm-integration-status">
			<?php foreach ( $connectors as $name => $connector ) : ?>
				<?php
				$connection   = SHM_Connectors::get_user_connection( $user_id, $name );
				$is_connected = ! empty( $connection );
				?>
				<div class="shm-integration-item <?php echo $is_connected ? 'connected' : 'disconnected'; ?>">
					<span class="shm-integration-name"><?php echo esc_html( $connector->get_label() ); ?></span>
					<span class="shm-integration-badge">
						<?php echo $is_connected ? esc_html__( 'Connected', 'shm-data-integrations' ) : esc_html__( 'Not Connected', 'shm-data-integrations' ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function get_bp_status_class( $systolic, $diastolic ) {
		if ( $systolic >= 140 || $diastolic >= 90 ) {
			return 'status-high';
		} elseif ( $systolic <= 90 || $diastolic <= 60 ) {
			return 'status-low';
		}
		return 'status-normal';
	}

	private static function get_bp_status_label( $systolic, $diastolic ) {
		if ( $systolic >= 140 || $diastolic >= 90 ) {
			return __( 'High', 'shm-data-integrations' );
		} elseif ( $systolic <= 90 || $diastolic <= 60 ) {
			return __( 'Low', 'shm-data-integrations' );
		}
		return __( 'Normal', 'shm-data-integrations' );
	}

	private static function get_glucose_status_class( $value ) {
		if ( $value >= 180 ) {
			return 'status-high';
		} elseif ( $value <= 70 ) {
			return 'status-low';
		}
		return 'status-normal';
	}

	private static function get_glucose_status_label( $value ) {
		if ( $value >= 180 ) {
			return __( 'High', 'shm-data-integrations' );
		} elseif ( $value <= 70 ) {
			return __( 'Low', 'shm-data-integrations' );
		}
		return __( 'Normal', 'shm-data-integrations' );
	}

	private static function get_chart_config( $metric ) {
		$base_config = array(
			'type'    => 'line',
			'data'    => array(
				'labels'   => array(),
				'datasets' => array(),
			),
			'options' => array(
				'responsive' => true,
				'plugins'    => array(
					'legend' => array(
						'display' => true,
					),
				),
			),
		);

		switch ( $metric ) {
			case 'bp':
				$base_config['data']['datasets'] = array(
					array(
						'label'       => __( 'Systolic', 'shm-data-integrations' ),
						'borderColor' => 'rgb(255, 99, 132)',
						'data'        => array(),
						'tension'     => 0.1,
					),
					array(
						'label'       => __( 'Diastolic', 'shm-data-integrations' ),
						'borderColor' => 'rgb(54, 162, 235)',
						'data'        => array(),
						'tension'     => 0.1,
					),
				);
				break;
			case 'glucose':
				$base_config['data']['datasets'] = array(
					array(
						'label'       => __( 'Glucose', 'shm-data-integrations' ),
						'borderColor' => 'rgb(75, 192, 192)',
						'data'        => array(),
						'tension'     => 0.1,
					),
				);
				break;
			case 'activity':
				$base_config['data']['datasets'] = array(
					array(
						'label'       => __( 'Steps', 'shm-data-integrations' ),
						'borderColor' => 'rgb(153, 102, 255)',
						'data'        => array(),
						'tension'     => 0.1,
					),
				);
				break;
		}

		return $base_config;
	}
}
