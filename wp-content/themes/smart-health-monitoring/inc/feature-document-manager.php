<?php
/**
 * Feature: Health Document Upload & Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_document_manager_init() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_documents';
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			document_name varchar(255) NOT NULL,
			document_type varchar(100) NOT NULL,
			document_date date NOT NULL,
			file_url varchar(500) NOT NULL,
			file_type varchar(100) NOT NULL,
			file_size int NOT NULL,
			description text,
			tags varchar(255) DEFAULT '',
			is_private tinyint(1) DEFAULT 1,
			uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
add_action( 'after_setup_theme', 'shm_document_manager_init' );

function shm_document_manager_ajax_upload() {
	check_ajax_referer( 'shm_document_manager_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}
	
	$uploadedfile = $_FILES['document_file'];
	$upload_overrides = array( 'test_form' => false );
	$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
	
	if ( $movefile && ! isset( $movefile['error'] ) ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shm_health_documents';
		
		$data = array(
			'user_id' => get_current_user_id(),
			'document_name' => sanitize_text_field( $_POST['document_name'] ),
			'document_type' => sanitize_text_field( $_POST['document_type'] ),
			'document_date' => sanitize_text_field( $_POST['document_date'] ),
			'file_url' => esc_url_raw( $movefile['url'] ),
			'file_type' => sanitize_text_field( $movefile['type'] ),
			'file_size' => filesize( $movefile['file'] ),
			'description' => sanitize_textarea_field( $_POST['description'] ),
			'tags' => sanitize_text_field( $_POST['tags'] ),
			'is_private' => 1
		);
		
		$result = $wpdb->insert( $table_name, $data );
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Document uploaded successfully', 'smart-health-monitoring' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save document info', 'smart-health-monitoring' ) ) );
		}
	} else {
		wp_send_json_error( array( 'message' => $movefile['error'] ) );
	}
}
add_action( 'wp_ajax_shm_upload_document', 'shm_document_manager_ajax_upload' );

function shm_document_manager_ajax_get() {
	check_ajax_referer( 'shm_document_manager_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_documents';
	$user_id = get_current_user_id();
	
	$documents = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table_name WHERE user_id = %d ORDER BY uploaded_at DESC",
		$user_id
	) );
	
	wp_send_json_success( $documents );
}
add_action( 'wp_ajax_shm_get_documents', 'shm_document_manager_ajax_get' );

function shm_document_manager_ajax_delete() {
	check_ajax_referer( 'shm_document_manager_nonce', 'nonce' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-health-monitoring' ) ) );
	}
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'shm_health_documents';
	$doc_id = intval( $_POST['document_id'] );
	$user_id = get_current_user_id();
	
	$result = $wpdb->delete( $table_name, array( 'id' => $doc_id, 'user_id' => $user_id ) );
	
	if ( $result ) {
		wp_send_json_success( array( 'message' => __( 'Document deleted', 'smart-health-monitoring' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to delete document', 'smart-health-monitoring' ) ) );
	}
}
add_action( 'wp_ajax_shm_delete_document', 'shm_document_manager_ajax_delete' );

function shm_document_manager_widget() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="shm-document-manager-widget">
		<h3><?php _e( 'Health Documents', 'smart-health-monitoring' ); ?></h3>
		<button id="add-document-btn" class="shm-btn shm-btn-primary">
			<?php _e( 'Upload Document', 'smart-health-monitoring' ); ?>
		</button>
		<div id="documents-list"></div>
		
		<div id="document-modal" class="shm-modal" style="display:none;">
			<div class="shm-modal-content">
				<span class="shm-modal-close">&times;</span>
				<h3><?php _e( 'Upload Health Document', 'smart-health-monitoring' ); ?></h3>
				<form id="document-form" enctype="multipart/form-data">
					<div class="form-group">
						<label><?php _e( 'Document Name', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="document_name" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Document Type', 'smart-health-monitoring' ); ?></label>
						<select name="document_type">
							<option value="lab_report"><?php _e( 'Lab Report', 'smart-health-monitoring' ); ?></option>
							<option value="prescription"><?php _e( 'Prescription', 'smart-health-monitoring' ); ?></option>
							<option value="xray"><?php _e( 'X-Ray/Scan', 'smart-health-monitoring' ); ?></option>
							<option value="vaccination"><?php _e( 'Vaccination Record', 'smart-health-monitoring' ); ?></option>
							<option value="insurance"><?php _e( 'Insurance Document', 'smart-health-monitoring' ); ?></option>
							<option value="discharge_summary"><?php _e( 'Discharge Summary', 'smart-health-monitoring' ); ?></option>
							<option value="other"><?php _e( 'Other', 'smart-health-monitoring' ); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label><?php _e( 'Document Date', 'smart-health-monitoring' ); ?></label>
						<input type="date" name="document_date" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'File (PDF, JPG, PNG - Max 5MB)', 'smart-health-monitoring' ); ?></label>
						<input type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png" required>
					</div>
					<div class="form-group">
						<label><?php _e( 'Description', 'smart-health-monitoring' ); ?></label>
						<textarea name="description" rows="3"></textarea>
					</div>
					<div class="form-group">
						<label><?php _e( 'Tags (comma separated)', 'smart-health-monitoring' ); ?></label>
						<input type="text" name="tags" placeholder="blood test, cardiology">
					</div>
					<button type="submit" class="shm-btn shm-btn-primary"><?php _e( 'Upload', 'smart-health-monitoring' ); ?></button>
				</form>
			</div>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($) {
		function loadDocuments() {
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: {
					action: 'shm_get_documents',
					nonce: '<?php echo wp_create_nonce( 'shm_document_manager_nonce' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						var html = '<div class="documents-grid">';
						response.data.forEach(function(doc) {
							var fileSize = (doc.file_size / 1024).toFixed(2);
							html += '<div class="document-card">';
							html += '<div class="doc-icon">📄</div>';
							html += '<h4>' + doc.document_name + '</h4>';
							html += '<p><strong>Type:</strong> ' + doc.document_type + '</p>';
							html += '<p><strong>Date:</strong> ' + doc.document_date + '</p>';
							html += '<p><strong>Size:</strong> ' + fileSize + ' KB</p>';
							html += '<a href="' + doc.file_url + '" target="_blank" class="shm-btn shm-btn-small">View</a> ';
							html += '<button class="shm-btn shm-btn-small shm-btn-danger delete-doc" data-id="' + doc.id + '">Delete</button>';
							html += '</div>';
						});
						html += '</div>';
						$('#documents-list').html(html);
					}
				}
			});
		}
		
		$('#add-document-btn').click(function() {
			$('#document-modal').show();
		});
		
		$('.shm-modal-close').click(function() {
			$('#document-modal').hide();
		});
		
		$('#document-form').submit(function(e) {
			e.preventDefault();
			
			var formData = new FormData(this);
			formData.append('action', 'shm_upload_document');
			formData.append('nonce', '<?php echo wp_create_nonce( 'shm_document_manager_nonce' ); ?>');
			
			$.ajax({
				url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				success: function(response) {
					if (response.success) {
						alert(response.data.message);
						$('#document-modal').hide();
						$('#document-form')[0].reset();
						loadDocuments();
					} else {
						alert(response.data.message);
					}
				}
			});
		});
		
		$(document).on('click', '.delete-doc', function() {
			if (confirm('Are you sure you want to delete this document?')) {
				var docId = $(this).data('id');
				$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
					action: 'shm_delete_document',
					nonce: '<?php echo wp_create_nonce( 'shm_document_manager_nonce' ); ?>',
					document_id: docId
				}, function(response) {
					if (response.success) {
						loadDocuments();
					}
				});
			}
		});
		
		loadDocuments();
	});
	</script>
	<?php
}
