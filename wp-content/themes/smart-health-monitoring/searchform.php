<?php
/**
 * Search form template
 */
?>

<form role="search" method="get" class="shm-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="search-field" class="sr-only">
		<?php esc_html_e( 'Search', 'smart-health-monitoring' ); ?>
	</label>
	<div class="shm-search-wrapper">
		<input 
			type="search" 
			id="search-field" 
			class="shm-search-input" 
			placeholder="<?php esc_attr_e( 'Search...', 'smart-health-monitoring' ); ?>" 
			value="<?php echo get_search_query(); ?>" 
			name="s" 
			required
		/>
		<button type="submit" class="shm-search-submit" aria-label="<?php esc_attr_e( 'Submit search', 'smart-health-monitoring' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="11" cy="11" r="8"></circle>
				<path d="m21 21-4.35-4.35"></path>
			</svg>
		</button>
	</div>
</form>
