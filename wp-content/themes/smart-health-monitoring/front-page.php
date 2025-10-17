<?php
/**
 * Front page template
 */

get_header();
?>

<main id="main-content" class="shm-main shm-front-page" role="main">
	<?php while ( have_posts() ) : the_post(); ?>
		<section class="shm-hero">
			<div class="container">
				<div class="shm-hero-content">
					<h1 class="shm-hero-title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="shm-hero-description"><?php the_excerpt(); ?></p>
					<?php endif; ?>
					
					<?php if ( is_user_logged_in() ) : ?>
						<div class="shm-hero-actions">
							<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'dashboard' ) ) ); ?>" class="button button-primary button-lg">
								<?php esc_html_e( 'Go to Dashboard', 'smart-health-monitoring' ); ?>
							</a>
							<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'reports' ) ) ); ?>" class="button button-secondary button-lg">
								<?php esc_html_e( 'View Reports', 'smart-health-monitoring' ); ?>
							</a>
						</div>
					<?php else : ?>
						<div class="shm-hero-actions">
							<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="button button-primary button-lg">
								<?php esc_html_e( 'Get Started', 'smart-health-monitoring' ); ?>
							</a>
							<a href="<?php echo esc_url( wp_login_url() ); ?>" class="button button-secondary button-lg">
								<?php esc_html_e( 'Login', 'smart-health-monitoring' ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="shm-features">
			<div class="container">
				<h2 class="text-center mb-8"><?php esc_html_e( 'Monitor Your Health', 'smart-health-monitoring' ); ?></h2>
				
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
					<div class="shm-feature-card">
						<div class="shm-feature-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
							</svg>
						</div>
						<h3><?php esc_html_e( 'Blood Pressure', 'smart-health-monitoring' ); ?></h3>
						<p><?php esc_html_e( 'Track your blood pressure readings over time', 'smart-health-monitoring' ); ?></p>
					</div>

					<div class="shm-feature-card">
						<div class="shm-feature-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<line x1="12" y1="20" x2="12" y2="10"></line>
								<line x1="18" y1="20" x2="18" y2="4"></line>
								<line x1="6" y1="20" x2="6" y2="16"></line>
							</svg>
						</div>
						<h3><?php esc_html_e( 'Blood Glucose', 'smart-health-monitoring' ); ?></h3>
						<p><?php esc_html_e( 'Monitor glucose levels and identify patterns', 'smart-health-monitoring' ); ?></p>
					</div>

					<div class="shm-feature-card">
						<div class="shm-feature-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="10"></circle>
								<polyline points="12 6 12 12 16 14"></polyline>
							</svg>
						</div>
						<h3><?php esc_html_e( 'Heart Rate', 'smart-health-monitoring' ); ?></h3>
						<p><?php esc_html_e( 'Keep track of your heart rate and activity', 'smart-health-monitoring' ); ?></p>
					</div>

					<div class="shm-feature-card">
						<div class="shm-feature-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
								<circle cx="8.5" cy="7" r="4"></circle>
								<polyline points="17 11 19 13 23 9"></polyline>
							</svg>
						</div>
						<h3><?php esc_html_e( 'Activity Tracking', 'smart-health-monitoring' ); ?></h3>
						<p><?php esc_html_e( 'Log steps, calories, and daily activities', 'smart-health-monitoring' ); ?></p>
					</div>
				</div>
			</div>
		</section>

		<section class="shm-content">
			<div class="container">
				<?php the_content(); ?>
			</div>
		</section>
	<?php endwhile; ?>
</main>

<?php
get_footer();
