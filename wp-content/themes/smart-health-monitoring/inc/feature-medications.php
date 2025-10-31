<?php
/**
 * Feature: Medication Reminder System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_medications_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_medications';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			medication_name varchar(255) NOT NULL,
			dosage varchar(100) NOT NULL,
			frequency varchar(100) NOT NULL,
			start_date date NOT NULL,
			end_date date,
			reminder_times text,
			instructions text,
			active tinyint(1) DEFAULT 1,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_medications_init' );

function shm_medications_ajax_add() {
	check_ajax_referer( 'shm_medications_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_medications';
	
	$data = array(
		'user_id' => get_current_user_id(),
		'medication_name' => sanitize_text_field( $_POST['medication_name'] ),
		'dosage' => sanitize_text_field( $_POST['dosage'] ),
		'frequency' => sanitize_text_field( $_POST['frequency'] ),
		'start_date' => sanitize_text_field( $_POST['start_date'] ),
		'end_date' => sanitize_text_field( $_POST['end_date'] ),
		'reminder_times' => sanitize_text_field( $_POST['reminder_times'] ),
		'instructions' => sanitize_textarea_field( $_POST['instructions'] ),
		'active' => 1
	);
	
	$result = $wpdb->insert( $table_name, $data );
	
	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Medication added successfully', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to add medication', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_add_medication', 'shm_medications_ajax_add' );

function shm_medications_ajax_get() {
	check_ajax_referer( 'shm_medications_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_medications';
	$user_id = get_current_user_id();
	
	$medications = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d AND active = 1 ORDER BY created_at DESC",
		$user_id
	) );
	
	wp_send_json_success( $medications );
}
add_action( 'wp_ajax_shm_get_medications', 'shm_medications_ajax_get' );

function shm_medications_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-medications-widget">
		<h3><?php _e( 'Medications', 'smart-health-monitoring' ); ?></h3>
		<button id="add-medication-btn" class="shm-btn shm-btn-primary">
			<?php _e( 'Add Medication', 'smart-health-monitoring' ); ?>
		</button>
		<div id="medications-list"></div>
		
		<div id="medication-modal" class="shm-modal" style="display:none;">
			<div class="shm-modal-content">
				<span class="shm-modal-close">&times;</span>
				<h3><?php _e( 'Add Medication', 'smart-health-monitoring' ); ?></h3>
				<form id="medication-form">
					<div class="form-group">
						<label><?php _e( 'Medication Name', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="medication_name" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Dosage', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="dosage" placeholder="e.g., 500mg" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Frequency', 'smart-health-monitoring' ); ?></label>
						<select name="frequency">
							<option value="Once daily"><?php _e( 'Once daily', 'smart-health-monitoring' ); ?></option>
							<option value="Twice daily"><?php _e( 'Twice daily', 'smart-health-monitoring' ); ?></option>
							<option value="Three times daily"><?php _e( 'Three times daily', 'smart-health-monitoring' ); ?></option>
							<option value="As needed"><?php _e( 'As needed', 'smart-health-monitoring' ); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label><?php _e( 'Start Date', 'smart-health-monitoring' ); ?></label>
						<input type="date" name="start_date" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'End Date', 'smart-health-monitoring' ); ?></label>
						<input type="date" name="end_date">
					</div>
					<div class="form-group">
						<label><?php _e( 'Reminder Times (comma separated)', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="reminder_times" placeholder="08:00, 20:00">
					</div>
					<div class="form-group">
						<label><?php _e( 'Instructions', 'smart-health-monitoring' ); ?></label>
						<textarea name="instructions" rows="3"></textarea>
					</div>
					<button type="submit" class="shm-btn shm-btn-primary"><?php _e( 'Save', 'smart-health-monitoring' ); ?></button>
				</form>
			</div>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($) {
		function loadMedications() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_medications',
					nonce: '<?php echo wp_create_nonce( 'shm_medications_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						var html = '<ul class="medications-list">';
						response.data.forEach(function(med) {
							html += '<li><strong>' + med.medication_name + '</strong> - ' + med.dosage + '<br><small>' + med.frequency + '</small></li>';
						});
						html += '</ul>';
						$('#medications-list').html(html);
					}
				}
			});
		}
		
		$('#add-medication-btn').click(function() {
			$('#medication-modal').show();
		});
		
		$('.shm-modal-close').click(function() {
			$('#medication-modal').hide();
		});
		
		$('#medication-form').submit(function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({name: 'action', value: 'shm_add_medication'});
			formData.push({name: 'nonce', value: '<?php echo wp_create_nonce( 'shm_medications_nonce' ); ?>'});
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $.param(formData), function(response) {
				if (response.success) {
					alert(response.data.message);
					$('#medication-modal').hide();
					$('#medication-form')[0].reset();
					loadMedications();
				}
			});
		});
		
		loadMedications();
	});
	</script>
	<?php
}
