<?php
/**
 * Search results template
 */

get_header();
?>

<main id="main-content" class="shm-main shm-search" role="main">
	<div class="container">
		<?php shm_theme_breadcrumbs(); ?>

		<header class="shm-search-header">
			<h1 class="shm-search-title">
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Search Results for: %s', 'smart-health-monitoring' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
			
			<?php if ( have_posts() ) : ?>
				<p class="shm-search-count">
					<?php
					printf(
						/* translators: %s: number of results */
						esc_html( _n( 'Found %s result', 'Found %s results', $wp_query->found_posts, 'smart-health-monitoring' ) ),
						'<strong>' . number_format_i18n( $wp_query->found_posts ) . '</strong>'
					);
					?>
				</p>
			<?php endif; ?>

			<div class="shm-search-form">
				<?php get_search_form(); ?>
			</div>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="shm-search-results">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'shm-search-result' ); ?>>
						<header class="shm-result-header">
							<h2 class="shm-result-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
							
							<div class="shm-result-meta">
								<span class="shm-meta-type"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></span>
								<span class="shm-meta-date"><?php echo esc_html( get_the_date() ); ?></span>
							</div>
						</header>

						<?php if ( has_post_thumbnail() ) : ?>
							<div class="shm-result-thumbnail">
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'shm-thumbnail' ); ?>
								</a>
							</div>
						<?php endif; ?>

						<div class="shm-result-excerpt">
							<?php the_excerpt(); ?>
						</div>

						<footer class="shm-result-footer">
							<a href="<?php the_permalink(); ?>" class="shm-read-more">
								<?php esc_html_e( 'Read More', 'smart-health-monitoring' ); ?> &rarr;
							</a>
						</footer>
					</article>
				<?php endwhile; ?>
			</div>

			<?php shm_theme_pagination(); ?>

		<?php else : ?>
			<div class="shm-no-results">
				<h2><?php esc_html_e( 'No Results Found', 'smart-health-monitoring' ); ?></h2>
				<p><?php esc_html_e( 'Sorry, no results were found for your search. Try different keywords or browse our content below.', 'smart-health-monitoring' ); ?></p>

				<div class="shm-no-results-suggestions">
					<h3><?php esc_html_e( 'Browse Categories', 'smart-health-monitoring' ); ?></h3>
					<ul class="shm-category-list">
						<?php
						$categories = get_categories( array( 'number' => 10 ) );
						foreach ( $categories as $category ) :
							?>
							<li>
								<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
									<?php echo esc_html( $category->name ); ?>
									<span class="shm-category-count">(<?php echo esc_html( $category->count ); ?>)</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
