<?php
/**
 * Template helper functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_theme_get_user_avatar( $user_id = null, $size = 60 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$avatar = get_avatar( $user_id, $size, '', '', array( 'class' => 'shm-avatar' ) );
	
	return $avatar;
}

function shm_theme_user_greeting() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user = wp_get_current_user();
	$hour = date( 'G' );

	if ( $hour >= 5 && $hour < 12 ) {
		$greeting = __( 'Good morning', 'smart-health-monitoring' );
	} elseif ( $hour >= 12 && $hour < 17 ) {
		$greeting = __( 'Good afternoon', 'smart-health-monitoring' );
	} else {
		$greeting = __( 'Good evening', 'smart-health-monitoring' );
	}

	printf(
		'<div class="shm-user-greeting"><h2>%s, %s</h2></div>',
		esc_html( $greeting ),
		esc_html( $user->display_name )
	);
}

function shm_theme_dark_mode_toggle() {
	?>
	<button 
		id="theme-toggle" 
		class="shm-theme-toggle" 
		aria-label="<?php esc_attr_e( 'Toggle dark mode', 'smart-health-monitoring' ); ?>"
		title="<?php esc_attr_e( 'Toggle dark mode', 'smart-health-monitoring' ); ?>"
	>
		<span class="theme-toggle-icon light-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="5"></circle>
				<line x1="12" y1="1" x2="12" y2="3"></line>
				<line x1="12" y1="21" x2="12" y2="23"></line>
				<line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
				<line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
				<line x1="1" y1="12" x2="3" y2="12"></line>
				<line x1="21" y1="12" x2="23" y2="12"></line>
				<line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
				<line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
			</svg>
		</span>
		<span class="theme-toggle-icon dark-icon" style="display: none;">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
			</svg>
		</span>
	</button>
	<?php
}

function shm_theme_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$separator = '<span class="breadcrumb-separator">/</span>';
	
	echo '<nav class="shm-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'smart-health-monitoring' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'smart-health-monitoring' ) . '</a>';
	
	if ( is_category() || is_single() ) {
		echo $separator;
		the_category( ' ' . $separator . ' ' );
		if ( is_single() ) {
			echo $separator;
			the_title();
		}
	} elseif ( is_page() ) {
		if ( $post = get_post() ) {
			if ( $post->post_parent ) {
				$parent_id  = $post->post_parent;
				$breadcrumbs = array();
				
				while ( $parent_id ) {
					$page = get_post( $parent_id );
					$breadcrumbs[] = '<a href="' . esc_url( get_permalink( $page->ID ) ) . '">' . esc_html( get_the_title( $page->ID ) ) . '</a>';
					$parent_id = $page->post_parent;
				}
				
				$breadcrumbs = array_reverse( $breadcrumbs );
				foreach ( $breadcrumbs as $crumb ) {
					echo $separator . $crumb;
				}
			}
			echo $separator;
			the_title();
		}
	} elseif ( is_search() ) {
		echo $separator . esc_html__( 'Search Results', 'smart-health-monitoring' );
	} elseif ( is_404() ) {
		echo $separator . esc_html__( '404', 'smart-health-monitoring' );
	}
	
	echo '</nav>';
}

function shm_theme_format_date_range( $from, $to ) {
	$from_date = date_i18n( get_option( 'date_format' ), strtotime( $from ) );
	$to_date   = date_i18n( get_option( 'date_format' ), strtotime( $to ) );
	
	return sprintf( '%s - %s', $from_date, $to_date );
}

function shm_theme_status_badge( $status, $label = '' ) {
	if ( empty( $label ) ) {
		$label = ucfirst( $status );
	}

	$class = 'shm-badge shm-badge-' . esc_attr( $status );
	
	printf(
		'<span class="%s">%s</span>',
		esc_attr( $class ),
		esc_html( $label )
	);
}

function shm_theme_pagination() {
	global $wp_query;

	if ( $wp_query->max_num_pages <= 1 ) {
		return;
	}

	$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	$max   = intval( $wp_query->max_num_pages );

	if ( $paged >= 1 ) {
		$links = paginate_links( array(
			'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
			'format'    => '?paged=%#%',
			'current'   => $paged,
			'total'     => $max,
			'type'      => 'array',
			'prev_text' => __( '&laquo; Previous', 'smart-health-monitoring' ),
			'next_text' => __( 'Next &raquo;', 'smart-health-monitoring' ),
		) );

		if ( is_array( $links ) ) {
			echo '<nav class="shm-pagination" aria-label="' . esc_attr__( 'Pagination', 'smart-health-monitoring' ) . '"><ul>';
			foreach ( $links as $link ) {
				echo '<li>' . $link . '</li>';
			}
			echo '</ul></nav>';
		}
	}
}
