<?php
/**
 * Single post template
 */

get_header();
?>

<main id="main-content" class="shm-main shm-single" role="main">
	<div class="container">
		<?php shm_theme_breadcrumbs(); ?>

		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'shm-article' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="shm-article-thumbnail">
						<?php the_post_thumbnail( 'shm-featured', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
					</div>
				<?php endif; ?>

				<header class="shm-article-header">
					<h1 class="shm-article-title"><?php the_title(); ?></h1>
					
					<div class="shm-article-meta">
						<span class="shm-meta-item">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="10"></circle>
								<polyline points="12 6 12 12 16 14"></polyline>
							</svg>
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
								<?php echo esc_html( get_the_date() ); ?>
							</time>
						</span>

						<span class="shm-meta-item">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
								<circle cx="12" cy="7" r="4"></circle>
							</svg>
							<?php the_author(); ?>
						</span>

						<?php if ( has_category() ) : ?>
							<span class="shm-meta-item">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
									<line x1="7" y1="7" x2="7.01" y2="7"></line>
								</svg>
								<?php the_category( ', ' ); ?>
							</span>
						<?php endif; ?>
					</div>
				</header>

				<div class="shm-article-content">
					<?php the_content(); ?>
				</div>

				<?php if ( has_tag() ) : ?>
					<footer class="shm-article-footer">
						<div class="shm-tags">
							<?php the_tags( '<strong>' . __( 'Tags:', 'smart-health-monitoring' ) . '</strong> ', ', ' ); ?>
						</div>
					</footer>
				<?php endif; ?>
			</article>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>

			<nav class="shm-post-navigation" aria-label="<?php esc_attr_e( 'Post navigation', 'smart-health-monitoring' ); ?>">
				<div class="shm-nav-links">
					<?php
					$prev = get_previous_post();
					$next = get_next_post();

					if ( $prev ) :
						?>
						<div class="shm-nav-previous">
							<span class="shm-nav-label"><?php esc_html_e( 'Previous', 'smart-health-monitoring' ); ?></span>
							<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" rel="prev">
								<?php echo esc_html( get_the_title( $prev ) ); ?>
							</a>
						</div>
					<?php endif; ?>

					<?php
					if ( $next ) :
						?>
						<div class="shm-nav-next">
							<span class="shm-nav-label"><?php esc_html_e( 'Next', 'smart-health-monitoring' ); ?></span>
							<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" rel="next">
								<?php echo esc_html( get_the_title( $next ) ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</nav>
		<?php endwhile; ?>
	</div>
</main>

<?php
get_footer();
