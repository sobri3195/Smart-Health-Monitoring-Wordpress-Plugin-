<?php
/**
 * Feature: Appointment Scheduler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_appointments_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_appointments';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			doctor_name varchar(255) NOT NULL,
			appointment_date datetime NOT NULL,
			appointment_type varchar(100) DEFAULT 'General Checkup',
			location varchar(255) DEFAULT '',
			notes text,
			status varchar(50) DEFAULT 'scheduled',
			reminder_sent tinyint(1) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY appointment_date (appointment_date)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_appointments_init' );

function shm_appointments_ajax_add() {
	check_ajax_referer( 'shm_appointments_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_appointments';
	
	$data = array(
		'user_id' => get_current_user_id(),
		'doctor_name' => sanitize_text_field( $_POST['doctor_name'] ),
		'appointment_date' => sanitize_text_field( $_POST['appointment_date'] ),
		'appointment_type' => sanitize_text_field( $_POST['appointment_type'] ),
		'location' => sanitize_text_field( $_POST['location'] ),
		'notes' => sanitize_textarea_field( $_POST['notes'] ),
		'status' => 'scheduled'
	);
	
	$result = $wpdb->insert( $table_name, $data );
	
	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Appointment scheduled successfully', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to schedule appointment', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_add_appointment', 'shm_appointments_ajax_add' );

function shm_appointments_ajax_get() {
	check_ajax_referer( 'shm_appointments_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_appointments';
	$user_id = get_current_user_id();
	
	$appointments = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d ORDER BY appointment_date ASC",
		$user_id
	) );
	
	wp_send_json_success( $appointments );
}
add_action( 'wp_ajax_shm_get_appointments', 'shm_appointments_ajax_get' );

function shm_appointments_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-appointments-widget">
		<h3><?php _e( 'Appointments', 'smart-health-monitoring' ); ?></h3>
		<button id="add-appointment-btn" class="shm-btn shm-btn-primary">
			<?php _e( 'Schedule Appointment', 'smart-health-monitoring' ); ?>
		</button>
		<div id="appointments-list"></div>
		
		<div id="appointment-modal" class="shm-modal" style="display:none;">
			<div class="shm-modal-content">
				<span class="shm-modal-close">&times;</span>
				<h3><?php _e( 'Schedule Appointment', 'smart-health-monitoring' ); ?></h3>
				<form id="appointment-form">
					<div class="form-group">
						<label><?php _e( 'Doctor Name', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="doctor_name" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Appointment Date & Time', 'smart-health-monitoring' ); ?></label>
						<input type="datetime-local" name="appointment_date" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Type', 'smart-health-monitoring' ); ?></label>
						<select name="appointment_type">
							<option value="General Checkup"><?php _e( 'General Checkup', 'smart-health-monitoring' ); ?></option>
							<option value="Follow-up"><?php _e( 'Follow-up', 'smart-health-monitoring' ); ?></option>
							<option value="Lab Test"><?php _e( 'Lab Test', 'smart-health-monitoring' ); ?></option>
							<option value="Consultation"><?php _e( 'Consultation', 'smart-health-monitoring' ); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label><?php _e( 'Location', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="location">
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
		function loadAppointments() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_appointments',
					nonce: '<?php echo wp_create_nonce( 'shm_appointments_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						var html = '<ul class="appointments-list">';
						response.data.forEach(function(apt) {
							html += '<li><strong>' + apt.doctor_name + '</strong> - ' + apt.appointment_date + '<br><small>' + apt.appointment_type + '</small></li>';
						});
						html += '</ul>';
						$('#appointments-list').html(html);
					}
				}
			});
		}
		
		$('#add-appointment-btn').click(function() {
			$('#appointment-modal').show();
		});
		
		$('.shm-modal-close').click(function() {
			$('#appointment-modal').hide();
		});
		
		$('#appointment-form').submit(function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({name: 'action', value: 'shm_add_appointment'});
			formData.push({name: 'nonce', value: '<?php echo wp_create_nonce( 'shm_appointments_nonce' ); ?>'});
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $.param(formData), function(response) {
				if (response.success) {
					alert(response.data.message);
					$('#appointment-modal').hide();
					$('#appointment-form')[0].reset();
					loadAppointments();
				}
			});
		});
		
		loadAppointments();
	});
	</script>
	<?php
}
