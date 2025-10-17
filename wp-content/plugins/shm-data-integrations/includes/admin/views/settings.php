<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'SHM Settings', 'shm-data-integrations' ); ?></h1>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'shm_settings' );
		do_settings_sections( 'shm_settings' );
		?>

		<table class="form-table">
			<tr>
				<th colspan="2">
					<h2><?php esc_html_e( 'Fitbit Integration', 'shm-data-integrations' ); ?></h2>
				</th>
			</tr>
			<tr>
				<th scope="row">
					<label for="shm_fitbit_client_id"><?php esc_html_e( 'Client ID', 'shm-data-integrations' ); ?></label>
				</th>
				<td>
					<input type="text" id="shm_fitbit_client_id" name="shm_fitbit_client_id" value="<?php echo esc_attr( get_option( 'shm_fitbit_client_id' ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="shm_fitbit_client_secret"><?php esc_html_e( 'Client Secret', 'shm-data-integrations' ); ?></label>
				</th>
				<td>
					<input type="password" id="shm_fitbit_client_secret" name="shm_fitbit_client_secret" value="<?php echo esc_attr( get_option( 'shm_fitbit_client_secret' ) ); ?>" class="regular-text" />
				</td>
			</tr>

			<tr>
				<th colspan="2">
					<h2><?php esc_html_e( 'Garmin Integration', 'shm-data-integrations' ); ?></h2>
				</th>
			</tr>
			<tr>
				<th scope="row">
					<label for="shm_garmin_consumer_key"><?php esc_html_e( 'Consumer Key', 'shm-data-integrations' ); ?></label>
				</th>
				<td>
					<input type="text" id="shm_garmin_consumer_key" name="shm_garmin_consumer_key" value="<?php echo esc_attr( get_option( 'shm_garmin_consumer_key' ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="shm_garmin_consumer_secret"><?php esc_html_e( 'Consumer Secret', 'shm-data-integrations' ); ?></label>
				</th>
				<td>
					<input type="password" id="shm_garmin_consumer_secret" name="shm_garmin_consumer_secret" value="<?php echo esc_attr( get_option( 'shm_garmin_consumer_secret' ) ); ?>" class="regular-text" />
				</td>
			</tr>
		</table>

		<div class="shm-disclaimer">
			<h3><?php esc_html_e( 'Important Disclaimer', 'shm-data-integrations' ); ?></h3>
			<p><?php esc_html_e( 'This plugin and theme are NOT medical devices and should not be used for medical diagnosis or treatment. All health data displayed is for informational purposes only. Always consult with qualified healthcare professionals for medical advice, diagnosis, and treatment.', 'shm-data-integrations' ); ?></p>
		</div>

		<?php submit_button(); ?>
	</form>
</div>
