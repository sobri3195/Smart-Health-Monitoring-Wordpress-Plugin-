<?php
/**
 * Archive template
 */

get_header();
?>

<main id="main-content" class="shm-main shm-archive" role="main">
	<div class="container">
		<?php shm_theme_breadcrumbs(); ?>

		<header class="shm-archive-header">
			<?php
			the_archive_title( '<h1 class="shm-archive-title">', '</h1>' );
			the_archive_description( '<div class="shm-archive-description">', '</div>' );
			?>
		</header>

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
				<p><?php esc_html_e( 'No posts found in this archive.', 'smart-health-monitoring' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
