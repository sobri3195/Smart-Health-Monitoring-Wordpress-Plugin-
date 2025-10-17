<?php
/**
 * Template part for displaying posts
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'shm-post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="shm-post-thumbnail">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'shm-thumbnail', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
			</a>
		</div>
	<?php endif; ?>

	<div class="shm-post-content">
		<?php if ( has_category() ) : ?>
			<div class="shm-post-category">
				<?php the_category( ', ' ); ?>
			</div>
		<?php endif; ?>

		<header class="shm-post-header">
			<h2 class="shm-post-title">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h2>

			<div class="shm-post-meta">
				<span class="shm-meta-date">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
				</span>
				<span class="shm-meta-author">
					<?php esc_html_e( 'by', 'smart-health-monitoring' ); ?> <?php the_author(); ?>
				</span>
			</div>
		</header>

		<div class="shm-post-excerpt">
			<?php the_excerpt(); ?>
		</div>

		<footer class="shm-post-footer">
			<a href="<?php the_permalink(); ?>" class="shm-read-more">
				<?php esc_html_e( 'Read More', 'smart-health-monitoring' ); ?> &rarr;
			</a>
		</footer>
	</div>
</article>
