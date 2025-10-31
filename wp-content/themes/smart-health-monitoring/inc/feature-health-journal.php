<?php
/**
 * Feature: Health Journal & Daily Notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_health_journal_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_journal';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			entry_date date NOT NULL,
			mood varchar(50) DEFAULT '',
			energy_level int DEFAULT 0,
			sleep_quality int DEFAULT 0,
			notes text,
			tags varchar(255) DEFAULT '',
			is_private tinyint(1) DEFAULT 1,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY entry_date (entry_date)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_health_journal_init' );

function shm_health_journal_ajax_add() {
	check_ajax_referer( 'shm_health_journal_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_journal';
	
	$data = array(
		'user_id' => get_current_user_id(),
		'entry_date' => sanitize_text_field( $_POST['entry_date'] ),
		'mood' => sanitize_text_field( $_POST['mood'] ),
		'energy_level' => intval( $_POST['energy_level'] ),
		'sleep_quality' => intval( $_POST['sleep_quality'] ),
		'notes' => sanitize_textarea_field( $_POST['notes'] ),
		'tags' => sanitize_text_field( $_POST['tags'] ),
		'is_private' => 1
	);
	
	$result = $wpdb->insert( $table_name, $data );
	
	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Journal entry added', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to add entry', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_add_journal_entry', 'shm_health_journal_ajax_add' );

function shm_health_journal_ajax_get() {
	check_ajax_referer( 'shm_health_journal_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_journal';
	$user_id = get_current_user_id();
	
	$entries = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d ORDER BY entry_date DESC LIMIT 30",
		$user_id
	) );
	
	wp_send_json_success( $entries );
}
add_action( 'wp_ajax_shm_get_journal_entries', 'shm_health_journal_ajax_get' );

function shm_health_journal_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-health-journal-widget">
		<h3><?php _e( 'Health Journal', 'smart-health-monitoring' ); ?></h3>
		<button id="add-journal-btn" class="shm-btn shm-btn-primary">
			<?php _e( 'New Entry', 'smart-health-monitoring' ); ?>
		</button>
		<div id="journal-entries-list"></div>
		
		<div id="journal-modal" class="shm-modal" style="display:none;">
			<div class="shm-modal-content">
				<span class="shm-modal-close">&times;</span>
				<h3><?php _e( 'New Journal Entry', 'smart-health-monitoring' ); ?></h3>
				<form id="journal-form">
					<div class="form-group">
						<label><?php _e( 'Date', 'smart-health-monitoring' ); ?></label>
						<input type="date" name="entry_date" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Mood', 'smart-health-monitoring' ); ?></label>
						<select name="mood">
							<option value="excellent"><?php _e( 'Excellent', 'smart-health-monitoring' ); ?></option>
							<option value="good"><?php _e( 'Good', 'smart-health-monitoring' ); ?></option>
							<option value="neutral"><?php _e( 'Neutral', 'smart-health-monitoring' ); ?></option>
							<option value="low"><?php _e( 'Low', 'smart-health-monitoring' ); ?></option>
							<option value="poor"><?php _e( 'Poor', 'smart-health-monitoring' ); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label><?php _e( 'Energy Level (1-10)', 'smart-health-monitoring' ); ?></label>
						<input type="range" name="energy_level" min="1" max="10" value="5">
						<output id="energy-output">5</output>
					</div>
					<div class="form-group">
						<label><?php _e( 'Sleep Quality (1-10)', 'smart-health-monitoring' ); ?></label>
						<input type="range" name="sleep_quality" min="1" max="10" value="5">
						<output id="sleep-output">5</output>
					</div>
					<div class="form-group">
						<label><?php _e( 'Notes', 'smart-health-monitoring' ); ?></label>
						<textarea name="notes" rows="5" placeholder="How do you feel today? Any symptoms or observations?"></textarea>
					</div>
					<div class="form-group">
						<label><?php _e( 'Tags (comma separated)', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="tags" placeholder="exercise, diet, stress">
					</div>
					<button type="submit" class="shm-btn shm-btn-primary"><?php _e( 'Save', 'smart-health-monitoring' ); ?></button>
				</form>
			</div>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('input[name="energy_level"]').on('input', function() {
			$('#energy-output').text($(this).val());
		});
		
		$('input[name="sleep_quality"]').on('input', function() {
			$('#sleep-output').text($(this).val());
		});
		
		function loadEntries() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_journal_entries',
					nonce: '<?php echo wp_create_nonce( 'shm_health_journal_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						var html = '<div class="journal-entries">';
						response.data.forEach(function(entry) {
							html += '<div class="journal-entry">';
							html += '<h4>' + entry.entry_date + ' - ' + entry.mood + '</h4>';
							html += '<p>' + entry.notes + '</p>';
							html += '<small>Energy: ' + entry.energy_level + '/10 | Sleep: ' + entry.sleep_quality + '/10</small>';
							html += '</div>';
						});
						html += '</div>';
						$('#journal-entries-list').html(html);
					}
				}
			});
		}
		
		$('#add-journal-btn').click(function() {
			$('input[name="entry_date"]').val(new Date().toISOString().split('T')[0]);
			$('#journal-modal').show();
		});
		
		$('.shm-modal-close').click(function() {
			$('#journal-modal').hide();
		});
		
		$('#journal-form').submit(function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({name: 'action', value: 'shm_add_journal_entry'});
			formData.push({name: 'nonce', value: '<?php echo wp_create_nonce( 'shm_health_journal_nonce' ); ?>'});
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $.param(formData), function(response) {
				if (response.success) {
					alert(response.data.message);
					$('#journal-modal').hide();
					$('#journal-form')[0].reset();
					loadEntries();
				}
			});
		});
		
		loadEntries();
	});
	</script>
	<?php
}
