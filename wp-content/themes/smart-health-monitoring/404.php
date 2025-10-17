<?php
/**
 * 404 Error Page template
 */

get_header();
?>

<main id="main-content" class="shm-main shm-404" role="main">
	<div class="container">
		<div class="shm-404-content">
			<div class="shm-404-number">404</div>
			
			<h1 class="shm-404-title"><?php esc_html_e( 'Page Not Found', 'smart-health-monitoring' ); ?></h1>
			
			<p class="shm-404-description">
				<?php esc_html_e( 'Sorry, the page you are looking for does not exist or has been moved.', 'smart-health-monitoring' ); ?>
			</p>

			<div class="shm-404-search">
				<?php get_search_form(); ?>
			</div>

			<div class="shm-404-actions">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button button-primary button-lg">
					<?php esc_html_e( 'Go to Homepage', 'smart-health-monitoring' ); ?>
				</a>
				
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'dashboard' ) ) ); ?>" class="button button-secondary button-lg">
						<?php esc_html_e( 'Go to Dashboard', 'smart-health-monitoring' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="shm-404-suggestions">
				<h3><?php esc_html_e( 'Popular Pages', 'smart-health-monitoring' ); ?></h3>
				<ul class="shm-suggestion-list">
					<?php
					$popular_pages = get_pages( array(
						'number' => 5,
						'sort_column' => 'post_modified',
					) );

					foreach ( $popular_pages as $page ) :
						?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>">
								<?php echo esc_html( $page->post_title ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
