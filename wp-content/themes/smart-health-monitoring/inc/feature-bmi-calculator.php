<?php
/**
 * Feature: BMI Calculator & Tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_bmi_tracker_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_bmi_records';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			record_date date NOT NULL,
			weight decimal(5,2) NOT NULL,
			height decimal(5,2) NOT NULL,
			bmi decimal(4,2) NOT NULL,
			bmi_category varchar(50) NOT NULL,
			unit_system varchar(20) DEFAULT 'metric',
			notes text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY record_date (record_date)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_bmi_tracker_init' );

function shm_bmi_calculate_category( $bmi ) {
	if ( $bmi < 18.5 ) {
		return __( 'Underweight', 'smart-health-monitoring' );
	} elseif ( $bmi < 25 ) {
		return __( 'Normal weight', 'smart-health-monitoring' );
	} elseif ( $bmi < 30 ) {
		return __( 'Overweight', 'smart-health-monitoring' );
	} else {
		return __( 'Obese', 'smart-health-monitoring' );
	}
}

function shm_bmi_ajax_add() {
	check_ajax_referer( 'shm_bmi_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_bmi_records';
	
	$weight = floatval( $_POST['weight'] );
	$height = floatval( $_POST['height'] );
	$bmi = round( $weight / ( ( $height / 100 ) * ( $height / 100 ) ), 2 );
	
	$data = array(
		'user_id' => get_current_user_id(),
		'record_date' => sanitize_text_field( $_POST['record_date'] ),
		'weight' => $weight,
		'height' => $height,
		'bmi' => $bmi,
		'bmi_category' => shm_bmi_calculate_category( $bmi ),
		'unit_system' => sanitize_text_field( $_POST['unit_system'] ),
		'notes' => sanitize_textarea_field( $_POST['notes'] )
	);
	
	$result = $wpdb->insert( $table_name, $data );
	
	if ( $result ) {
		wp_send_json_success( array( 
			'message' => __( 'BMI record saved', 'smart-health-monitoring' ),
			'bmi' => $bmi,
			'category' => $data['bmi_category']
		) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to save BMI record', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_add_bmi_record', 'shm_bmi_ajax_add' );

function shm_bmi_ajax_get() {
	check_ajax_referer( 'shm_bmi_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_bmi_records';
	$user_id = get_current_user_id();
	
	$records = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d ORDER BY record_date DESC LIMIT 30",
		$user_id
	) );
	
	$latest = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d ORDER BY record_date DESC LIMIT 1",
		$user_id
	) );
	
	wp_send_json_success( array(
		'records' => $records,
		'latest' => $latest
	) );
}
add_action( 'wp_ajax_shm_get_bmi_records', 'shm_bmi_ajax_get' );

function shm_bmi_calculator_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-bmi-calculator-widget">
		<h3><?php _e( 'BMI Calculator & Tracker', 'smart-health-monitoring' ); ?></h3>
		
		<div class="bmi-current" id="bmi-current-display">
			<h4><?php _e( 'Current BMI', 'smart-health-monitoring' ); ?></h4>
			<div class="bmi-value">
				<h2 id="current-bmi">--</h2>
				<span id="current-category"></span>
			</div>
		</div>
		
		<div class="bmi-calculator">
			<h4><?php _e( 'Calculate BMI', 'smart-health-monitoring' ); ?></h4>
			<form id="bmi-form">
				<div class="form-group">
					<label><?php _e( 'Date', 'smart-health-monitoring' ); ?></label>
					<input type="date" name="record_date" required>
				</div>
				<div class="form-group">
					<label><?php _e( 'Weight (kg)', 'smart-health-monitoring' ); ?></label>
					<input type="number" step="0.1" name="weight" id="weight-input" required>
				</div>
				<div class="form-group">
					<label><?php _e( 'Height (cm)', 'smart-health-monitoring' ); ?></label>
					<input type="number" step="0.1" name="height" id="height-input" required>
				</div>
				<div class="form-group">
					<label><?php _e( 'Unit System', 'smart-health-monitoring' ); ?></label>
					<select name="unit_system">
						<option value="metric"><?php _e( 'Metric (kg, cm)', 'smart-health-monitoring' ); ?></option>
						<option value="imperial"><?php _e( 'Imperial (lb, in)', 'smart-health-monitoring' ); ?></option>
					</select>
				</div>
				<div class="bmi-result" id="bmi-instant-result" style="display:none;">
					<p><strong><?php _e( 'BMI:', 'smart-health-monitoring' ); ?></strong> <span id="instant-bmi"></span></p>
					<p><strong><?php _e( 'Category:', 'smart-health-monitoring' ); ?></strong> <span id="instant-category"></span></p>
				</div>
				<div class="form-group">
					<label><?php _e( 'Notes', 'smart-health-monitoring' ); ?></label>
					<textarea name="notes" rows="2"></textarea>
				</div>
				<button type="submit" class="shm-btn shm-btn-primary"><?php _e( 'Save Record', 'smart-health-monitoring' ); ?></button>
			</form>
		</div>
		
		<div class="bmi-history">
			<h4><?php _e( 'BMI History', 'smart-health-monitoring' ); ?></h4>
			<div id="bmi-records-list"></div>
		</div>
		
		<div class="bmi-info">
			<h4><?php _e( 'BMI Categories', 'smart-health-monitoring' ); ?></h4>
			<ul>
				<li><strong>&lt; 18.5:</strong> <?php _e( 'Underweight', 'smart-health-monitoring' ); ?></li>
				<li><strong>18.5 - 24.9:</strong> <?php _e( 'Normal weight', 'smart-health-monitoring' ); ?></li>
				<li><strong>25 - 29.9:</strong> <?php _e( 'Overweight', 'smart-health-monitoring' ); ?></li>
				<li><strong>≥ 30:</strong> <?php _e( 'Obese', 'smart-health-monitoring' ); ?></li>
			</ul>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($) {
		function calculateBMI(weight, height) {
			return (weight / ((height / 100) * (height / 100))).toFixed(2);
		}
		
		function getBMICategory(bmi) {
			if (bmi < 18.5) return 'Underweight';
			if (bmi < 25) return 'Normal weight';
			if (bmi < 30) return 'Overweight';
			return 'Obese';
		}
		
		$('#weight-input, #height-input').on('input', function() {
			var weight = parseFloat($('#weight-input').val());
			var height = parseFloat($('#height-input').val());
			
			if (weight && height) {
				var bmi = calculateBMI(weight, height);
				var category = getBMICategory(parseFloat(bmi));
				
				$('#instant-bmi').text(bmi);
				$('#instant-category').text(category);
				$('#bmi-instant-result').show();
			}
		});
		
		function loadBMIRecords() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_bmi_records',
					nonce: '<?php echo wp_create_nonce( 'shm_bmi_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						if (response.data.latest) {
							$('#current-bmi').text(response.data.latest.bmi);
							$('#current-category').text(response.data.latest.bmi_category);
						}
						
						var html = '<table class="bmi-records-table">';
						html += '<thead><tr><th>Date</th><th>Weight</th><th>BMI</th><th>Category</th></tr></thead>';
						html += '<tbody>';
						response.data.records.forEach(function(record) {
							html += '<tr>';
							html += '<td>' + record.record_date + '</td>';
							html += '<td>' + record.weight + ' kg</td>';
							html += '<td>' + record.bmi + '</td>';
							html += '<td>' + record.bmi_category + '</td>';
							html += '</tr>';
						});
						html += '</tbody></table>';
						$('#bmi-records-list').html(html);
					}
				}
			});
		}
		
		$('input[name="record_date"]').val(new Date().toISOString().split('T')[0]);
		
		$('#bmi-form').submit(function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({name: 'action', value: 'shm_add_bmi_record'});
			formData.push({name: 'nonce', value: '<?php echo wp_create_nonce( 'shm_bmi_nonce' ); ?>'});
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $.param(formData), function(response) {
				if (response.success) {
					alert(response.data.message + '\nBMI: ' + response.data.bmi + '\nCategory: ' + response.data.category);
					$('#bmi-form')[0].reset();
					$('#bmi-instant-result').hide();
					$('input[name="record_date"]').val(new Date().toISOString().split('T')[0]);
					loadBMIRecords();
				}
			});
		});
		
		loadBMIRecords();
	});
	</script>
	<?php
}
