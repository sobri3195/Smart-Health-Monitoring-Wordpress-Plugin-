<?php
/**
 * Feature: Symptom Checker & Tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_symptom_tracker_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_symptoms';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			symptom_name varchar(255) NOT NULL,
			severity int DEFAULT 0,
			body_part varchar(100) DEFAULT '',
			onset_date date NOT NULL,
			duration varchar(100) DEFAULT '',
			frequency varchar(100) DEFAULT '',
			description text,
			triggers text,
			relieving_factors text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_symptom_tracker_init' );

function shm_symptom_tracker_ajax_add() {
	check_ajax_referer( 'shm_symptom_tracker_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_symptoms';
	
	$data = array(
		'user_id' => get_current_user_id(),
		'symptom_name' => sanitize_text_field( $_POST['symptom_name'] ),
		'severity' => intval( $_POST['severity'] ),
		'body_part' => sanitize_text_field( $_POST['body_part'] ),
		'onset_date' => sanitize_text_field( $_POST['onset_date'] ),
		'duration' => sanitize_text_field( $_POST['duration'] ),
		'frequency' => sanitize_text_field( $_POST['frequency'] ),
		'description' => sanitize_textarea_field( $_POST['description'] ),
		'triggers' => sanitize_textarea_field( $_POST['triggers'] ),
		'relieving_factors' => sanitize_textarea_field( $_POST['relieving_factors'] )
	);
	
	$result = $wpdb->insert( $table_name, $data );
	
	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Symptom logged successfully', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to log symptom', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_add_symptom', 'shm_symptom_tracker_ajax_add' );

function shm_symptom_tracker_ajax_get() {
	check_ajax_referer( 'shm_symptom_tracker_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_symptoms';
	$user_id = get_current_user_id();
	
	$symptoms = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d ORDER BY onset_date DESC",
		$user_id
	) );
	
	wp_send_json_success( $symptoms );
}
add_action( 'wp_ajax_shm_get_symptoms', 'shm_symptom_tracker_ajax_get' );

function shm_symptom_tracker_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-symptom-tracker-widget">
		<h3><?php _e( 'Symptom Tracker', 'smart-health-monitoring' ); ?></h3>
		<button id="add-symptom-btn" class="shm-btn shm-btn-warning">
			<?php _e( 'Log Symptom', 'smart-health-monitoring' ); ?>
		</button>
		<div id="symptoms-list"></div>
		
		<div id="symptom-modal" class="shm-modal" style="display:none;">
			<div class="shm-modal-content">
				<span class="shm-modal-close">&times;</span>
				<h3><?php _e( 'Log Symptom', 'smart-health-monitoring' ); ?></h3>
				<form id="symptom-form">
					<div class="form-group">
						<label><?php _e( 'Symptom Name', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="symptom_name" placeholder="e.g., Headache, Fever, Cough" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Severity (1-10)', 'smart-health-monitoring' ); ?></label>
						<input type="range" name="severity" min="1" max="10" value="5">
						<output id="severity-output">5</output>
					</div>
					<div class="form-group">
						<label><?php _e( 'Body Part/Location', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="body_part" placeholder="e.g., Head, Chest, Abdomen">
					</div>
					<div class="form-group">
						<label><?php _e( 'Onset Date', 'smart-health-monitoring' ); ?></label>
						<input type="date" name="onset_date" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Duration', 'smart-health-monitoring' ); ?></label>
						<select name="duration">
							<option value="Less than 1 hour"><?php _e( 'Less than 1 hour', 'smart-health-monitoring' ); ?></option>
							<option value="1-6 hours"><?php _e( '1-6 hours', 'smart-health-monitoring' ); ?></option>
							<option value="6-24 hours"><?php _e( '6-24 hours', 'smart-health-monitoring' ); ?></option>
							<option value="1-3 days"><?php _e( '1-3 days', 'smart-health-monitoring' ); ?></option>
							<option value="More than 3 days"><?php _e( 'More than 3 days', 'smart-health-monitoring' ); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label><?php _e( 'Frequency', 'smart-health-monitoring' ); ?></label>
						<select name="frequency">
							<option value="Constant"><?php _e( 'Constant', 'smart-health-monitoring' ); ?></option>
							<option value="Intermittent"><?php _e( 'Intermittent', 'smart-health-monitoring' ); ?></option>
							<option value="Occasional"><?php _e( 'Occasional', 'smart-health-monitoring' ); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label><?php _e( 'Description', 'smart-health-monitoring' ); ?></label>
						<textarea name="description" rows="3" placeholder="Describe the symptom in detail"></textarea>
					</div>
					<div class="form-group">
						<label><?php _e( 'Triggers', 'smart-health-monitoring' ); ?></label>
						<textarea name="triggers" rows="2" placeholder="What makes it worse?"></textarea>
					</div>
					<div class="form-group">
						<label><?php _e( 'Relieving Factors', 'smart-health-monitoring' ); ?></label>
						<textarea name="relieving_factors" rows="2" placeholder="What makes it better?"></textarea>
					</div>
					<button type="submit" class="shm-btn shm-btn-primary"><?php _e( 'Save', 'smart-health-monitoring' ); ?></button>
				</form>
			</div>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('input[name="severity"]').on('input', function() {
			$('#severity-output').text($(this).val());
		});
		
		function loadSymptoms() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_symptoms',
					nonce: '<?php echo wp_create_nonce( 'shm_symptom_tracker_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						var html = '<div class="symptoms-list">';
						response.data.forEach(function(symptom) {
							html += '<div class="symptom-card severity-' + symptom.severity + '">';
							html += '<h4>' + symptom.symptom_name + '</h4>';
							html += '<p><strong>Severity:</strong> ' + symptom.severity + '/10</p>';
							html += '<p><strong>Date:</strong> ' + symptom.onset_date + '</p>';
							html += '<p>' + symptom.description + '</p>';
							html += '</div>';
						});
						html += '</div>';
						$('#symptoms-list').html(html);
					}
				}
			});
		}
		
		$('#add-symptom-btn').click(function() {
			$('input[name="onset_date"]').val(new Date().toISOString().split('T')[0]);
			$('#symptom-modal').show();
		});
		
		$('.shm-modal-close').click(function() {
			$('#symptom-modal').hide();
		});
		
		$('#symptom-form').submit(function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({name: 'action', value: 'shm_add_symptom'});
			formData.push({name: 'nonce', value: '<?php echo wp_create_nonce( 'shm_symptom_tracker_nonce' ); ?>'});
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $.param(formData), function(response) {
				if (response.success) {
					alert(response.data.message);
					$('#symptom-modal').hide();
					$('#symptom-form')[0].reset();
					loadSymptoms();
				}
			});
		});
		
		loadSymptoms();
	});
	</script>
	<?php
}
