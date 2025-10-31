<?php
/**
 * Feature: Emergency Contact Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_emergency_contacts_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_emergency_contacts';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			contact_name varchar(255) NOT NULL,
			relationship varchar(100) NOT NULL,
			phone_primary varchar(50) NOT NULL,
			phone_secondary varchar(50) DEFAULT '',
			email varchar(255) DEFAULT '',
			address text,
			is_primary tinyint(1) DEFAULT 0,
			notes text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_emergency_contacts_init' );

function shm_emergency_contacts_ajax_add() {
	check_ajax_referer( 'shm_emergency_contacts_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_emergency_contacts';
	
	$data = array(
		'user_id' => get_current_user_id(),
		'contact_name' => sanitize_text_field( $_POST['contact_name'] ),
		'relationship' => sanitize_text_field( $_POST['relationship'] ),
		'phone_primary' => sanitize_text_field( $_POST['phone_primary'] ),
		'phone_secondary' => sanitize_text_field( $_POST['phone_secondary'] ),
		'email' => sanitize_email( $_POST['email'] ),
		'address' => sanitize_textarea_field( $_POST['address'] ),
		'is_primary' => intval( $_POST['is_primary'] ),
		'notes' => sanitize_textarea_field( $_POST['notes'] )
	);
	
	$result = $wpdb->insert( $table_name, $data );
	
	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Contact added successfully', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to add contact', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_add_emergency_contact', 'shm_emergency_contacts_ajax_add' );

function shm_emergency_contacts_ajax_get() {
	check_ajax_referer( 'shm_emergency_contacts_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_emergency_contacts';
	$user_id = get_current_user_id();
	
	$contacts = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d ORDER BY is_primary DESC, created_at DESC",
		$user_id
	) );
	
	wp_send_json_success( $contacts );
}
add_action( 'wp_ajax_shm_get_emergency_contacts', 'shm_emergency_contacts_ajax_get' );

function shm_emergency_contacts_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-emergency-contacts-widget">
		<h3><?php _e( 'Emergency Contacts', 'smart-health-monitoring' ); ?></h3>
		<button id="add-emergency-contact-btn" class="shm-btn shm-btn-danger">
			<?php _e( 'Add Emergency Contact', 'smart-health-monitoring' ); ?>
		</button>
		<div id="emergency-contacts-list"></div>
		
		<div id="emergency-contact-modal" class="shm-modal" style="display:none;">
			<div class="shm-modal-content">
				<span class="shm-modal-close">&times;</span>
				<h3><?php _e( 'Add Emergency Contact', 'smart-health-monitoring' ); ?></h3>
				<form id="emergency-contact-form">
					<div class="form-group">
						<label><?php _e( 'Contact Name', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="contact_name" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Relationship', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="relationship" placeholder="e.g., Spouse, Parent, Sibling" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Primary Phone', 'smart-health-monitoring' ); ?></label>
						<input type="tel" name="phone_primary" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Secondary Phone', 'smart-health-monitoring' ); ?></label>
						<input type="tel" name="phone_secondary">
					</div>
					<div class="form-group">
						<label><?php _e( 'Email', 'smart-health-monitoring' ); ?></label>
						<input type="email" name="email">
					</div>
					<div class="form-group">
						<label><?php _e( 'Address', 'smart-health-monitoring' ); ?></label>
						<textarea name="address" rows="2"></textarea>
					</div>
					<div class="form-group">
						<label>
							<input type="checkbox" name="is_primary" value="1">
							<?php _e( 'Set as primary contact', 'smart-health-monitoring' ); ?>
						</label>
					</div>
					<div class="form-group">
						<label><?php _e( 'Notes', 'smart-health-monitoring' ); ?></label>
						<textarea name="notes" rows="2"></textarea>
					</div>
					<button type="submit" class="shm-btn shm-btn-primary"><?php _e( 'Save', 'smart-health-monitoring' ); ?></button>
				</form>
			</div>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($) {
		function loadContacts() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_emergency_contacts',
					nonce: '<?php echo wp_create_nonce( 'shm_emergency_contacts_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						var html = '<div class="contacts-list">';
						response.data.forEach(function(contact) {
							html += '<div class="contact-card' + (contact.is_primary == 1 ? ' primary-contact' : '') + '">';
							html += '<h4>' + contact.contact_name + (contact.is_primary == 1 ? ' <span class="badge">PRIMARY</span>' : '') + '</h4>';
							html += '<p><strong>Relationship:</strong> ' + contact.relationship + '</p>';
							html += '<p><strong>Phone:</strong> ' + contact.phone_primary + '</p>';
							if (contact.email) {
								html += '<p><strong>Email:</strong> ' + contact.email + '</p>';
							}
							html += '</div>';
						});
						html += '</div>';
						$('#emergency-contacts-list').html(html);
					}
				}
			});
		}
		
		$('#add-emergency-contact-btn').click(function() {
			$('#emergency-contact-modal').show();
		});
		
		$('.shm-modal-close').click(function() {
			$('#emergency-contact-modal').hide();
		});
		
		$('#emergency-contact-form').submit(function(e) {
			e.preventDefault();
			var formData = $(this).serializeArray();
			formData.push({name: 'action', value: 'shm_add_emergency_contact'});
			formData.push({name: 'nonce', value: '<?php echo wp_create_nonce( 'shm_emergency_contacts_nonce' ); ?>'});
			
			$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $.param(formData), function(response) {
				if (response.success) {
					alert(response.data.message);
					$('#emergency-contact-modal').hide();
					$('#emergency-contact-form')[0].reset();
					loadContacts();
				}
			});
		});
		
		loadContacts();
	});
	</script>
	<?php
}
