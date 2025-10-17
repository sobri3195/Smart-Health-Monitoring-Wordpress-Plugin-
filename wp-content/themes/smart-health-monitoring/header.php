<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-theme="<?php echo esc_attr( get_theme_mod( 'shm_theme_color_scheme', 'light' ) ); ?>">
<?php wp_body_open(); ?>

<a class="skip-link sr-only" href="#main-content"><?php esc_html_e( 'Skip to content', 'smart-health-monitoring' ); ?></a>

<header id="site-header" class="shm-header" role="banner">
	<div class="container">
		<div class="shm-header-inner">
			<div class="shm-header-left">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				} else {
					?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="shm-site-title" rel="home">
						<?php bloginfo( 'name' ); ?>
					</a>
					<?php
				}
				?>
			</div>

			<nav id="site-navigation" class="shm-nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'smart-health-monitoring' ); ?>">
				<button class="shm-nav-toggle" aria-expanded="false" aria-controls="primary-menu">
					<span class="sr-only"><?php esc_html_e( 'Menu', 'smart-health-monitoring' ); ?></span>
					<span class="shm-nav-icon"></span>
				</button>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'shm-menu',
					'container'      => false,
					'fallback_cb'    => false,
				) );
				?>
			</nav>

			<div class="shm-header-right">
				<?php shm_theme_dark_mode_toggle(); ?>

				<?php if ( is_user_logged_in() ) : ?>
					<div class="shm-user-menu">
						<button class="shm-user-menu-toggle" aria-expanded="false">
							<?php echo shm_theme_get_user_avatar( null, 40 ); ?>
							<span class="sr-only"><?php esc_html_e( 'User menu', 'smart-health-monitoring' ); ?></span>
						</button>
						<div class="shm-user-dropdown">
							<div class="shm-user-info">
								<?php echo shm_theme_get_user_avatar( null, 60 ); ?>
								<div class="shm-user-details">
									<strong><?php echo esc_html( wp_get_current_user()->display_name ); ?></strong>
									<span class="shm-user-email"><?php echo esc_html( wp_get_current_user()->user_email ); ?></span>
								</div>
							</div>
							<ul class="shm-user-menu-list">
								<li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'dashboard' ) ) ); ?>"><?php esc_html_e( 'Dashboard', 'smart-health-monitoring' ); ?></a></li>
								<li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'reports' ) ) ); ?>"><?php esc_html_e( 'Reports', 'smart-health-monitoring' ); ?></a></li>
								<li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'integrations' ) ) ); ?>"><?php esc_html_e( 'Integrations', 'smart-health-monitoring' ); ?></a></li>
								<li><a href="<?php echo esc_url( get_edit_profile_url() ); ?>"><?php esc_html_e( 'Settings', 'smart-health-monitoring' ); ?></a></li>
								<li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Logout', 'smart-health-monitoring' ); ?></a></li>
							</ul>
						</div>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Login', 'smart-health-monitoring' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>

<div id="page-content" class="shm-page-wrapper">
