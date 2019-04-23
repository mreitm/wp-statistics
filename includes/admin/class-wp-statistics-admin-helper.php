<?php

namespace WP_STATISTICS;

class Admin_Helper {
	/**
	 * Check in admin page
	 *
	 * @param $page | For Get List
	 * @return bool
	 */
	public static function in_page( $page ) {
		global $pagenow;

		//Check is custom page
		if ( $pagenow == "admin.php" and isset( $_REQUEST['page'] ) and $_REQUEST['page'] == Admin_Menus::get_page_slug( $page ) ) {
			return true;
		}

		return false;
	}

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
		echo '<img src="' . plugins_url( 'wp-statistics/assets/images/' ) . '/title-logo.png" class="wps_page_title"><h2 class="wps_title">' . $title . '</h2>';

		//do_action after wp_statistics
		do_action( 'wp_statistics_after_title' );
	}

	/**
	 * Get Admin Url
	 *
	 * @param null $page
	 * @param array $arg
	 * @area is_admin
	 * @return string
	 */
	public static function admin_url( $page = null, $arg = array() ) {

		//Check If Pages is in Wp-statistics
		if ( array_key_exists( $page, Admin_Menus::get_admin_page_list() ) ) {
			$page = Admin_Menus::get_page_slug( $page );
		}

		return add_query_arg( array_merge( array( 'page' => $page ), $arg ), admin_url( 'admin.php' ) );
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