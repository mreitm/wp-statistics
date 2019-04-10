<?php

namespace WP_STATISTICS;

class Menu {
	/**
	 * List Of Admin Page Slug WP-statistics
	 *
	 * -- Array Arg ---
	 * key   : page key for using another methods
	 * value : Admin Page Slug
	 *
	 * @var array
	 */
	public static $pages = array(
		'overview'     => 'overview',
		'browser'      => 'browsers',
		'countries'    => 'countries',
		'exclusions'   => 'exclusions',
		'hits'         => 'hits',
		'online'       => 'online',
		'pages'        => 'pages',
		'categories'   => 'categories',
		'authors'      => 'authors',
		'tags'         => 'tags',
		'referrers'    => 'referrers',
		'searches'     => 'searches',
		'words'        => 'words',
		'top-visitors' => 'top_visitors',
		'visitors'     => 'visitors',
		'optimization' => 'optimization',
		'settings'     => 'settings',
		'plugins'      => 'plugins',
		'donate'       => 'donate',
	);
	/**
	 * Admin Page Slug
	 *
	 * @var string
	 */
	public static $admin_menu_slug = 'wps_[slug]_page';

	/**
	 * Get List Admin Pages
	 */
	public static function get_admin_page_list() {

		/**
		 * WP-Statistics Admin Page List
		 *
		 * @example add_filter('wp_statistics_admin_page_list', function( $list ){ unset( $list['searches'] ); return $list; });
		 */
		$list = apply_filters( 'wp_statistics_admin_page_list', self::$pages );

		/**
		 * Get List Page
		 */
		foreach ( $list as $page_key => $page_slug ) {
			$admin_list_page[ $page_key ] = str_ireplace( "[slug]", self::$admin_menu_slug, $page_slug );
		}

		return isset( $admin_list_page ) ? $admin_list_page : array();
	}



	public function __construct() {

	}


}