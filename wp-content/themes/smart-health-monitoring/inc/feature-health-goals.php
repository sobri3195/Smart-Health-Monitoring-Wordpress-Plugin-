<?php
/**
 * Feature: Health Goals Tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_health_goals_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_goals';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			goal_type varchar(100) NOT NULL,
			goal_title varchar(255) NOT NULL,
			target_value decimal(10,2) NOT NULL,
			current_value decimal(10,2) DEFAULT 0,
			unit varchar(50) DEFAULT '',
			start_date date NOT NULL,
			target_date date NOT NULL,
			status varchar(50) DEFAULT 'in_progress',
			notes text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_health_goals_init' );

function shm_health_goals_ajax_add() {
	check_ajax_referer( 'shm_health_goals_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_goals';
	
	$data = array(
		'user_id' => get_current_user_id(),
		'goal_type' => sanitize_text_field( $_POST['goal_type'] ),
		'goal_title' => sanitize_text_field( $_POST['goal_title'] ),
		'target_value' => floatval( $_POST['target_value'] ),
		'current_value' => floatval( $_POST['current_value'] ),
		'unit' => sanitize_text_field( $_POST['unit'] ),
		'start_date' => sanitize_text_field( $_POST['start_date'] ),
		'target_date' => sanitize_text_field( $_POST['target_date'] ),
		'notes' => sanitize_textarea_field( $_POST['notes'] )
	);
	
	$result = $wpdb->insert( $table_name, $data );
	
	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Goal added successfully', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to add goal', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_add_health_goal', 'shm_health_goals_ajax_add' );

function shm_health_goals_ajax_update() {
	check_ajax_referer( 'shm_health_goals_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_goals';
	
	$result = $wpdb->update(
		$table_name,
		array( 'current_value' => floatval( $_POST['current_value'] ) ),
		array( 'id' => intval( $_POST['goal_id'] ), 'user_id' => get_current_user_id() )
	);
	
	if ( $result !== false ) {
		wp_send_json_success( array( 'message' => __( 'Progress updated', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to update progress', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_update_health_goal', 'shm_health_goals_ajax_update' );

function shm_health_goals_ajax_get() {
	check_ajax_referer( 'shm_health_goals_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_goals';
	$user_id = get_current_user_id();
	
	$goals = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC",
		$user_id
	) );
	
	wp_send_json_success( $goals );
}
add_action( 'wp_ajax_shm_get_health_goals', 'shm_health_goals_ajax_get' );

function shm_health_goals_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-health-goals-widget">
		<h3><?php _e( 'Health Goals', 'smart-health-monitoring' ); ?></h3>
		<button id="add-goal-btn" class="shm-btn shm-btn-primary">
			<?php _e( 'Add New Goal', 'smart-health-monitoring' ); ?>
		</button>
		<div id="goals-list"></div>
		
		<div id="goal-modal" class="shm-modal" style="display:none;">
			<div class="shm-modal-content">
				<span class="shm-modal-close">&times;</span>
				<h3><?php _e( 'Add Health Goal', 'smart-health-monitoring' ); ?></h3>
				<form id="goal-form">
					<div class="form-group">
						<label><?php _e( 'Goal Type', 'smart-health-monitoring' ); ?></label>
						<select name="goal_type">
							<option value="weight"><?php _e( 'Weight Loss/Gain', 'smart-health-monitoring' ); ?></option>
							<option value="exercise"><?php _e( 'Exercise', 'smart-health-monitoring' ); ?></option>
							<option value="steps"><?php _e( 'Daily Steps', 'smart-health-monitoring' ); ?></option>
							<option value="blood_pressure"><?php _e( 'Blood Pressure', 'smart-health-monitoring' ); ?></option>
							<option value="glucose"><?php _e( 'Blood Glucose', 'smart-health-monitoring' ); ?></option>
							<option value="other"><?php _e( 'Other', 'smart-health-monitoring' ); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label><?php _e( 'Goal Title', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="goal_title" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Target Value', 'smart-health-monitoring' ); ?></label>
						<input type="number" step="0.01" name="target_value" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Current Value', 'smart-health-monitoring' ); ?></label>
						<input type="number" step="0.01" name="current_value" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Unit', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="unit" placeholder="kg, steps, mmHg">
					</div>
					<div class="form-group">
						<label><?php _e( 'Start Date', 'smart-health-monitoring' ); ?></label>
						<input type="date" name="start_date" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Target Date', 'smart-health-monitoring' ); ?></label>
						<input type="date" name="target_date" required>
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
		function loadGoals() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_health_goals',
					nonce: '<?php echo wp_create_nonce( 'shm_health_goals_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						var html = '<div class="goals-grid">';
						response.data.forEach(function(goal) {
							var progress = (goal.current_value / goal.target_value * 100).toFixed(1);
							html += '<div class="goal-card">';
							html += '<h4>' + goal.goal_title + '</h4>';
							html += '<div class="progress-bar"><div class="progress-fill" style="width:' + progress + '%"></div></div>';
							html += '<p>' + goal.current_value + ' / ' + goal.target_value + ' ' + goal.unit + '</p>';
							html += '<small>Target: ' + goal.target_date + '</small>';
							html += '</div>';
						});
						html += '</div>';
						$('#goals-list').html(html);
					}
				}
			});
		}
		
		$('#add-goal-btn').click(function() {
			$('#goal-modal').show();
		});
		
		$('.shm-modal-close').click(function() {
			$('#goal-modal').hide();
		});
		
		$('#goal-form').submit(function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({name: 'action', value: 'shm_add_health_goal'});
			formData.push({name: 'nonce', value: '<?php echo wp_create_nonce( 'shm_health_goals_nonce' ); ?>'});
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $.param(formData), function(response) {
				if (response.success) {
					alert(response.data.message);
					$('#goal-modal').hide();
					$('#goal-form')[0].reset();
					loadGoals();
				}
			});
		});
		
		loadGoals();
	});
	</script>
	<?php
}
