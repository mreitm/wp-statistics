<?php

namespace WP_STATISTICS;

class Admin_Templates {
	/**
	 * Show Page title
	 *
	 * @param string $title
	 */
	public static function show_page_title( $title = '' ) {

		//Check if $title not Set
		if ( empty( $title ) and function_exists( 'get_admin_page_title' ) ) {
			$title = get_admin_page_title();
		}

		//show Page title
		echo '<img src="' . WP_STATISTICS_URL . '/assets/images/title-logo.png" class="wps_page_title"><h2 class="wps_title">' . $title . '</h2>';

		//do_action after wp_statistics
		do_action( 'wp_statistics_after_title' );
	}

	/**
	 * Show MetaBox button Refresh/Direct Button Link in Top of Meta Box
	 *
	 * @param string $export
	 * @return string
	 */
	public static function meta_box_button( $export = 'all' ) {

		//Prepare button
		$refresh = '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . wp_statistics_icons( 'dashicons-update' ) . '<span class="screen-reader-text">' . __( 'Reload', 'wp-statistics' ) . '</span></button>';
		$more    = '<button class="handlediv button-link wps-more" type="button" id="{{moreid}}">' . wp_statistics_icons( 'dashicons-external' ) . '<span class="screen-reader-text">' . __( 'More Details', 'wp-statistics' ) . '</span></button>';

		//Export
		if ( $export == 'all' ) {
			return $refresh . $more;
		} else {
			return $$export;
		}
	}

	/**
	 * Show Loading Meta Box
	 */
	public static function loading_meta_box() {
		$loading = '<div class="wps_loading_box"><img src=" ' . plugins_url( 'wp-statistics/assets/images/' ) . 'loading.svg" alt="' . __( 'Reloading...', 'wp-statistics' ) . '"></div>';
		return $loading;
	}

	/**
	 * Donate
	 */
	public static function donate() {
		echo "<script>window.location.href='http://wp-statistics.com/donate';</script>";
	}
}