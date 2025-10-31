<?php
/**
 * Feature: Sleep Quality Tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_sleep_tracker_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_sleep_records';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			sleep_date date NOT NULL,
			bedtime time NOT NULL,
			wake_time time NOT NULL,
			total_hours decimal(4,2) NOT NULL,
			quality_rating int DEFAULT 0,
			interruptions int DEFAULT 0,
			dream_recall tinyint(1) DEFAULT 0,
			mood_on_wake varchar(50) DEFAULT '',
			notes text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY sleep_date (sleep_date)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_sleep_tracker_init' );

function shm_sleep_tracker_ajax_add() {
	check_ajax_referer( 'shm_sleep_tracker_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_sleep_records';
	
	$data = array(
		'user_id' => get_current_user_id(),
		'sleep_date' => sanitize_text_field( $_POST['sleep_date'] ),
		'bedtime' => sanitize_text_field( $_POST['bedtime'] ),
		'wake_time' => sanitize_text_field( $_POST['wake_time'] ),
		'total_hours' => floatval( $_POST['total_hours'] ),
		'quality_rating' => intval( $_POST['quality_rating'] ),
		'interruptions' => intval( $_POST['interruptions'] ),
		'dream_recall' => intval( $_POST['dream_recall'] ),
		'mood_on_wake' => sanitize_text_field( $_POST['mood_on_wake'] ),
		'notes' => sanitize_textarea_field( $_POST['notes'] )
	);
	
	$result = $wpdb->insert( $table_name, $data );
	
	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Sleep data recorded', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to record sleep data', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_add_sleep_record', 'shm_sleep_tracker_ajax_add' );

function shm_sleep_tracker_ajax_get() {
	check_ajax_referer( 'shm_sleep_tracker_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_sleep_records';
	$user_id = get_current_user_id();
	
	$records = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d ORDER BY sleep_date DESC LIMIT 30",
		$user_id
	) );
	
	$avg_hours = $wpdb->get_var( $wpdb->prepare(
		"SELECT AVG(total_hours) FROM $table_name WHERE user_id = %d AND sleep_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
		$user_id
	) );
	
	wp_send_json_success( array(
		'records' => $records,
		'avg_hours' => round( floatval( $avg_hours ), 1 )
	) );
}
add_action( 'wp_ajax_shm_get_sleep_records', 'shm_sleep_tracker_ajax_get' );

function shm_sleep_tracker_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-sleep-tracker-widget">
		<h3><?php _e( 'Sleep Tracker', 'smart-health-monitoring' ); ?></h3>
		<div class="sleep-summary">
			<div class="sleep-stat">
				<span class="sleep-icon">😴</span>
				<div>
					<h2 id="avg-sleep-hours">0</h2>
					<small><?php _e( 'Avg hours (7 days)', 'smart-health-monitoring' ); ?></small>
				</div>
			</div>
		</div>
		<button id="add-sleep-btn" class="shm-btn shm-btn-primary">
			<?php _e( 'Log Sleep', 'smart-health-monitoring' ); ?>
		</button>
		<div id="sleep-records-list"></div>
		
		<div id="sleep-modal" class="shm-modal" style="display:none;">
			<div class="shm-modal-content">
				<span class="shm-modal-close">&times;</span>
				<h3><?php _e( 'Log Sleep Record', 'smart-health-monitoring' ); ?></h3>
				<form id="sleep-form">
					<div class="form-group">
						<label><?php _e( 'Sleep Date', 'smart-health-monitoring' ); ?></label>
						<input type="date" name="sleep_date" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Bedtime', 'smart-health-monitoring' ); ?></label>
						<input type="time" name="bedtime" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Wake Time', 'smart-health-monitoring' ); ?></label>
						<input type="time" name="wake_time" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Total Hours', 'smart-health-monitoring' ); ?></label>
						<input type="number" step="0.5" name="total_hours" min="0" max="24" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Sleep Quality (1-10)', 'smart-health-monitoring' ); ?></label>
						<input type="range" name="quality_rating" min="1" max="10" value="5">
						<output id="quality-output">5</output>
					</div>
					<div class="form-group">
						<label><?php _e( 'Number of Interruptions', 'smart-health-monitoring' ); ?></label>
						<input type="number" name="interruptions" min="0" value="0">
					</div>
					<div class="form-group">
						<label>
							<input type="checkbox" name="dream_recall" value="1">
							<?php _e( 'Remember dreams', 'smart-health-monitoring' ); ?>
						</label>
					</div>
					<div class="form-group">
						<label><?php _e( 'Mood on Wake', 'smart-health-monitoring' ); ?></label>
						<select name="mood_on_wake">
							<option value="refreshed"><?php _e( 'Refreshed', 'smart-health-monitoring' ); ?></option>
							<option value="good"><?php _e( 'Good', 'smart-health-monitoring' ); ?></option>
							<option value="tired"><?php _e( 'Tired', 'smart-health-monitoring' ); ?></option>
							<option value="groggy"><?php _e( 'Groggy', 'smart-health-monitoring' ); ?></option>
							<option value="exhausted"><?php _e( 'Exhausted', 'smart-health-monitoring' ); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label><?php _e( 'Notes', 'smart-health-monitoring' ); ?></label>
						<textarea name="notes" rows="3"></textarea>
					</div>
					<button type="submit" class="shm-btn shm-btn-primary"><?php _e( 'Save', 'smart-health-monitoring' ); ?></button>
				</form>
			</div>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('input[name="quality_rating"]').on('input', function() {
			$('#quality-output').text($(this).val());
		});
		
		function loadSleepRecords() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_sleep_records',
					nonce: '<?php echo wp_create_nonce( 'shm_sleep_tracker_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						$('#avg-sleep-hours').text(response.data.avg_hours + 'h');
						var html = '<div class="sleep-records">';
						response.data.records.forEach(function(record) {
							html += '<div class="sleep-record">';
							html += '<h4>' + record.sleep_date + '</h4>';
							html += '<p>' + record.total_hours + ' hours | Quality: ' + record.quality_rating + '/10</p>';
							html += '<small>' + record.bedtime + ' - ' + record.wake_time + '</small>';
							html += '</div>';
						});
						html += '</div>';
						$('#sleep-records-list').html(html);
					}
				}
			});
		}
		
		$('#add-sleep-btn').click(function() {
			var yesterday = new Date();
			yesterday.setDate(yesterday.getDate() - 1);
			$('input[name="sleep_date"]').val(yesterday.toISOString().split('T')[0]);
			$('#sleep-modal').show();
		});
		
		$('.shm-modal-close').click(function() {
			$('#sleep-modal').hide();
		});
		
		$('#sleep-form').submit(function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({name: 'action', value: 'shm_add_sleep_record'});
			formData.push({name: 'nonce', value: '<?php echo wp_create_nonce( 'shm_sleep_tracker_nonce' ); ?>'});
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $.param(formData), function(response) {
				if (response.success) {
					alert(response.data.message);
					$('#sleep-modal').hide();
					$('#sleep-form')[0].reset();
					loadSleepRecords();
				}
			});
		});
		
		loadSleepRecords();
	});
	</script>
	<?php
}
