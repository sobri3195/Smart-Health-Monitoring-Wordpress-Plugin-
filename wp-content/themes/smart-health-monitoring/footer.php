</div><!-- #page-content -->

<footer id="site-footer" class="shm-footer" role="contentinfo">
	<div class="container">
		<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
			<div class="shm-footer-widgets grid grid-cols-1 md:grid-cols-3 gap-6">
				<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
					<div class="shm-footer-widget">
						<?php dynamic_sidebar( 'footer-1' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
					<div class="shm-footer-widget">
						<?php dynamic_sidebar( 'footer-2' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
					<div class="shm-footer-widget">
						<?php dynamic_sidebar( 'footer-3' ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="shm-footer-bottom">
			<div class="shm-footer-info">
				<p class="shm-disclaimer">
					<?php esc_html_e( 'This is not a medical device. Always consult with healthcare professionals for medical advice.', 'smart-health-monitoring' ); ?>
				</p>
				<p class="shm-copyright">
					&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. 
					<?php esc_html_e( 'All rights reserved.', 'smart-health-monitoring' ); ?>
				</p>
			</div>

			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'menu_class'     => 'shm-footer-menu',
					'container'      => 'nav',
					'depth'          => 1,
				) );
			}
			?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
