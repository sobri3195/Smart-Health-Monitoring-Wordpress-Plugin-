<?php
/**
 * Feature: Water Intake Tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_water_tracker_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_water_intake';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			intake_date date NOT NULL,
			amount_ml int NOT NULL,
			intake_time time DEFAULT NULL,
			daily_goal int DEFAULT 2000,
			notes varchar(255) DEFAULT '',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY intake_date (intake_date)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_water_tracker_init' );

function shm_water_tracker_ajax_add() {
	check_ajax_referer( 'shm_water_tracker_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_water_intake';
	
	$data = array(
		'user_id' => get_current_user_id(),
		'intake_date' => sanitize_text_field( $_POST['intake_date'] ),
		'amount_ml' => intval( $_POST['amount_ml'] ),
		'intake_time' => sanitize_text_field( $_POST['intake_time'] ),
		'daily_goal' => intval( $_POST['daily_goal'] ),
		'notes' => sanitize_text_field( $_POST['notes'] )
	);
	
	$result = $wpdb->insert( $table_name, $data );
	
	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Water intake logged', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to log water intake', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_add_water_intake', 'shm_water_tracker_ajax_add' );

function shm_water_tracker_ajax_get_today() {
	check_ajax_referer( 'shm_water_tracker_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_water_intake';
	$user_id = get_current_user_id();
	$today = date( 'Y-m-d' );
	
	$total = $wpdb->get_var( $wpdb->prepare(
		"SELECT SUM(amount_ml) FROM $table_name WHERE user_id = %d AND intake_date = %s",
		$user_id,
		$today
	) );
	
	$goal = $wpdb->get_var( $wpdb->prepare(
		"SELECT daily_goal FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 1",
		$user_id
	) );
	
	if ( ! $goal ) {
		$goal = 2000;
	}
	
	wp_send_json_success( array(
		'total' => intval( $total ),
		'goal' => intval( $goal ),
		'percentage' => round( ( intval( $total ) / intval( $goal ) ) * 100 )
	) );
}
add_action( 'wp_ajax_shm_get_water_today', 'shm_water_tracker_ajax_get_today' );

function shm_water_tracker_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-water-tracker-widget">
		<h3><?php _e( 'Water Intake Tracker', 'smart-health-monitoring' ); ?></h3>
		<div id="water-progress">
			<div class="water-stats">
				<div class="water-icon">💧</div>
				<div class="water-amount">
					<h2 id="water-total">0</h2>
					<small><?php _e( 'ml today', 'smart-health-monitoring' ); ?></small>
				</div>
			</div>
			<div class="progress-bar-water">
				<div id="water-progress-fill" class="progress-fill-water" style="width: 0%"></div>
			</div>
			<p id="water-goal-text"><?php _e( 'Goal: 2000ml', 'smart-health-monitoring' ); ?></p>
		</div>
		
		<div class="water-quick-add">
			<button class="water-btn" data-amount="250">250ml</button>
			<button class="water-btn" data-amount="500">500ml</button>
			<button class="water-btn" data-amount="750">750ml</button>
			<button class="water-btn" data-amount="1000">1L</button>
		</div>
		
		<button id="add-custom-water-btn" class="shm-btn shm-btn-primary">
			<?php _e( 'Custom Amount', 'smart-health-monitoring' ); ?>
		</button>
		
		<div id="water-modal" class="shm-modal" style="display:none;">
			<div class="shm-modal-content">
				<span class="shm-modal-close">&times;</span>
				<h3><?php _e( 'Log Water Intake', 'smart-health-monitoring' ); ?></h3>
				<form id="water-form">
					<div class="form-group">
						<label><?php _e( 'Amount (ml)', 'smart-health-monitoring' ); ?></label>
						<input type="number" name="amount_ml" min="1" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Date', 'smart-health-monitoring' ); ?></label>
						<input type="date" name="intake_date" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Time', 'smart-health-monitoring' ); ?></label>
						<input type="time" name="intake_time">
					</div>
					<div class="form-group">
						<label><?php _e( 'Daily Goal (ml)', 'smart-health-monitoring' ); ?></label>
						<input type="number" name="daily_goal" value="2000">
					</div>
					<div class="form-group">
						<label><?php _e( 'Notes', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="notes">
					</div>
					<button type="submit" class="shm-btn shm-btn-primary"><?php _e( 'Save', 'smart-health-monitoring' ); ?></button>
				</form>
			</div>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($) {
		function loadWaterToday() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_water_today',
					nonce: '<?php echo wp_create_nonce( 'shm_water_tracker_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						$('#water-total').text(response.data.total);
						$('#water-progress-fill').css('width', response.data.percentage + '%');
						$('#water-goal-text').text('Goal: ' + response.data.goal + 'ml');
					}
				}
			});
		}
		
		$('.water-btn').click(function() {
			var amount = $(this).data('amount');
			var now = new Date();
			var formData = {
				action: 'shm_add_water_intake',
				nonce: '<?php echo wp_create_nonce( 'shm_water_tracker_nonce' ); ?>',
				amount_ml: amount,
				intake_date: now.toISOString().split('T')[0],
				intake_time: now.toTimeString().split(' ')[0].substring(0, 5),
				daily_goal: 2000,
				notes: ''
			};
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', formData, function(response) {
				if (response.success) {
					loadWaterToday();
				}
			});
		});
		
		$('#add-custom-water-btn').click(function() {
			var now = new Date();
			$('input[name="intake_date"]').val(now.toISOString().split('T')[0]);
			$('input[name="intake_time"]').val(now.toTimeString().split(' ')[0].substring(0, 5));
			$('#water-modal').show();
		});
		
		$('.shm-modal-close').click(function() {
			$('#water-modal').hide();
		});
		
		$('#water-form').submit(function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({name: 'action', value: 'shm_add_water_intake'});
			formData.push({name: 'nonce', value: '<?php echo wp_create_nonce( 'shm_water_tracker_nonce' ); ?>'});
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $.param(formData), function(response) {
				if (response.success) {
					$('#water-modal').hide();
					$('#water-form')[0].reset();
					loadWaterToday();
				}
			});
		});
		
		loadWaterToday();
	});
	</script>
	<?php
}
