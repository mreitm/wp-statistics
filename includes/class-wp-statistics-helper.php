<?php

namespace WP_STATISTICS;

use WP_STATISTICS;

class Helper {
	/**
	 * WP-Statistics WordPress Log
	 *
	 * @param $function
	 * @param $message
	 * @param $version
	 */
	public static function doing_it_wrong( $function, $message, $version ) {
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();
		if ( is_ajax() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
	}

	/**
	 * Returns an array of site id's
	 *
	 * @return array
	 */
	public static function get_wp_sites_list() {
		$site_list = array();
		$sites     = get_sites();
		foreach ( $sites as $site ) {
			$site_list[] = $site->blog_id;
		}
		return $site_list;
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public static function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'wp-cli':
				return defined( 'WP_CLI' ) && WP_CLI;
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! self::is_rest_request();
		}
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * @return bool
	 */
	public static function is_rest_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}
		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		return ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );
	}

	/**
	 * Show Admin Wordpress Ui Notice
	 *
	 * @param $text
	 * @param string $model
	 * @param bool $close_button
	 * @param bool $echo
	 * @param string $style_extra
	 * @return string
	 */
	public static function wp_admin_notice( $text, $model = "info", $close_button = true, $echo = true, $style_extra = 'padding:12px;' ) {
		$text = '
        <div class="notice notice-' . $model . '' . ( $close_button === true ? " is-dismissible" : "" ) . '">
           <div style="' . $style_extra . '">' . $text . '</div>
        </div>
        ';
		if ( $echo ) {
			echo $text;
		} else {
			return $text;
		}
	}

	/**
	 * Check User is Used Cache Plugin
	 *
	 * @return array
	 */
	public static function is_active_cache_plugin() {
		$use = array( 'status' => false, 'plugin' => '' );

		/* Wordpress core */
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			return array( 'status' => true, 'plugin' => 'core' );
		}

		/* WP Rocket */
		if ( function_exists( 'get_rocket_cdn_url' ) ) {
			return array( 'status' => true, 'plugin' => 'WP Rocket' );
		}

		/* WP Super Cache */
		if ( function_exists( 'wpsc_init' ) ) {
			return array( 'status' => true, 'plugin' => 'WP Super Cache' );
		}

		/* Comet Cache */
		if ( function_exists( '___wp_php_rv_initialize' ) ) {
			return array( 'status' => true, 'plugin' => 'Comet Cache' );
		}

		/* WP Fastest Cache */
		if ( class_exists( 'WpFastestCache' ) ) {
			return array( 'status' => true, 'plugin' => 'WP Fastest Cache' );
		}

		/* Cache Enabler */
		if ( defined( 'CE_MIN_WP' ) ) {
			return array( 'status' => true, 'plugin' => 'Cache Enabler' );
		}

		/* W3 Total Cache */
		if ( defined( 'W3TC' ) ) {
			return array( 'status' => true, 'plugin' => 'W3 Total Cache' );
		}

		return $use;
	}

	/**
	 * Get WordPress Uploads DIR
	 *
	 * @param string $path
	 * @return mixed
	 * @default For WP-Statistics Plugin is 'wp-statistics' dir
	 */
	public static function get_uploads_dir( $path = '' ) {
		$upload_dir = wp_upload_dir();
		return path_join( $upload_dir['basedir'], $path );
	}

	/**
	 * Get country codes
	 *
	 * @return array|bool|string
	 */
	public static function get_country_codes() {
		global $WP_Statistics;

		# Load From global
		if ( isset( $WP_Statistics->country_codes ) ) {
			return $WP_Statistics->country_codes;
		}

		# Load From file
		require_once WP_STATISTICS_DIR . "includes/defines/country-codes.php";
		if ( isset( $ISOCountryCode ) ) {
			return $ISOCountryCode;
		}

		return array();
	}

	/**
	 * Get Robots List
	 *
	 * @param string $type
	 * @return array|bool|string
	 */
	public static function get_robots_list( $type = 'list' ) {
		global $WP_Statistics;

		# Set Default
		$list = array();

		# Load From global
		if ( isset( $WP_Statistics->robots_list ) ) {
			$list = $WP_Statistics->robots_list;
		}

		# Load From file
		require_once WP_STATISTICS_DIR . "includes/defines/robots-list.php";
		if ( isset( $wps_robots_list_array ) ) {
			$list = $wps_robots_list_array;
		}

		return ( $type == "array" ? $list : implode( "\n", $list ) );
	}

	/**
	 * Get Number Days From install this plugin
	 * this method used for `ALL` Option in Time Range Pages
	 */
	public static function get_number_days_install_plugin() {
		global $wpdb;

		//Create Empty default Option
		$first_day = '';

		//First Check Visitor Table , if not exist Web check Pages Table
		$list_tbl = array(
			'visitor' => array( 'order_by' => 'ID', 'column' => 'last_counter' ),
			'pages'   => array( 'order_by' => 'page_id', 'column' => 'date' ),
		);
		foreach ( $list_tbl as $tbl => $val ) {
			$first_day = $wpdb->get_var( "SELECT `" . $val['column'] . "` FROM `" . WP_STATISTICS\DB::table( $tbl ) . "` ORDER BY `" . $val['order_by'] . "` ASC LIMIT 1" );
			if ( ! empty( $first_day ) ) {
				break;
			}
		}

		//Calculate hit day if range is exist
		if ( empty( $first_day ) ) {
			$result = array(
				'days' => 1,
				'date' => current_time( 'timestamp' )
			);
		} else {
			$earlier = new \DateTime( $first_day );
			$later   = new \DateTime( WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ) );
			$result  = array(
				'days'      => $later->diff( $earlier )->format( "%a" ),
				'timestamp' => strtotime( $first_day ),
				'first_day' => $first_day,
			);
		}

		return $result;
	}

	/**
	 * Check User Is Using Gutenberg Editor
	 */
	public static function is_gutenberg() {
		$current_screen = get_current_screen();
		if ( ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) || ( function_exists( 'is_gutenberg_page' ) ) && is_gutenberg_page() ) {
			return true;
		}
		return false;
	}

	/**
	 * Get List WordPress Post Type
	 *
	 * @return array
	 */
	public static function get_list_post_type() {
		$post_types     = array( 'post', 'page' );
		$get_post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
		foreach ( $get_post_types as $name ) {
			$post_types[] = $name;
		}

		return $post_types;
	}


}