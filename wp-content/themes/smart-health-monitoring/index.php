<?php
/**
 * The main template file
 */

get_header();
?>

<main id="main-content" class="shm-main" role="main">
	<div class="container">
		<?php shm_theme_breadcrumbs(); ?>

		<?php if ( have_posts() ) : ?>
			<div class="shm-posts-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
				endwhile;
				?>
			</div>

			<?php shm_theme_pagination(); ?>

		<?php else : ?>
			<div class="shm-no-content">
				<h2><?php esc_html_e( 'Nothing Found', 'smart-health-monitoring' ); ?></h2>
				<p><?php esc_html_e( 'It seems we can\'t find what you\'re looking for. Perhaps try a search?', 'smart-health-monitoring' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
