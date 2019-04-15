<?php

namespace WP_STATISTICS;

class Pages {
	/**
	 * Get WordPress Page Type
	 */
	public static function get_page_type() {

		//Set Default Option
		$current_page = array( "type" => "unknown", "id" => 0 );

		//Check Query object
		$id = get_queried_object_id();
		if ( is_numeric( $id ) and $id > 0 ) {
			$current_page['id'] = $id;
		}

		//WooCommerce Product
		if ( class_exists( 'WooCommerce' ) ) {
			if ( is_product() ) {
				return wp_parse_args( array( "type" => "product" ), $current_page );
			}
		}

		//Home Page or Front Page
		if ( is_front_page() || is_home() ) {
			return wp_parse_args( array( "type" => "home" ), $current_page );
		}

		//attachment View
		if ( is_attachment() ) {
			$current_page['type'] = "attachment";
		}

		//is Archive Page
		if ( is_archive() ) {
			$current_page['type'] = "archive";
		}

		//Single Post Fro All Post Type
		if ( is_singular() ) {
			$current_page['type'] = "post";
		}

		//Single Page
		if ( is_page() ) {
			$current_page['type'] = "page";
		}

		//Category Page
		if ( is_category() ) {
			$current_page['type'] = "category";
		}

		//Tag Page
		if ( is_tag() ) {
			$current_page['type'] = "post_tag";
		}

		//is Custom Term From Taxonomy
		if ( is_tax() ) {
			$current_page['type'] = "tax";
		}

		//is Author Page
		if ( is_author() ) {
			$current_page['type'] = "author";
		}

		//is search page
		$search_query = filter_var( get_search_query( false ), FILTER_SANITIZE_STRING );
		if ( trim( $search_query ) != "" ) {
			return array( "type" => "search", "id" => 0, "search_query" => $search_query );
		}

		//is 404 Page
		if ( is_404() ) {
			$current_page['type'] = "404";
		}

		return apply_filters( 'wp_statistics_current_page', $current_page );
	}

	/**
	 * Check Track All Page WP-Statistics
	 *
	 * @return bool
	 */
	public static function is_track_all_page() {
		return $GLOBALS['WP_Statistics']->option->get( 'track_all_pages' ) || is_single() || is_page() || is_front_page();
	}

	/**
	 * Get Page Url
	 *
	 * @return bool|mixed|string
	 */
	public static function get_page_uri() {

		// Get the site's path from the URL.
		$site_uri     = parse_url( site_url(), PHP_URL_PATH );
		$site_uri_len = strlen( $site_uri );

		// Get the site's path from the URL.
		$home_uri     = parse_url( home_url(), PHP_URL_PATH );
		$home_uri_len = strlen( $home_uri );

		// Get the current page URI.
		$page_uri = $_SERVER["REQUEST_URI"];

		/*
		 * We need to check which URI is longer in case one contains the other.
		 * For example home_uri might be "/site/wp" and site_uri might be "/site".
		 * In that case we want to check to see if the page_uri starts with "/site/wp" before
		 * we check for "/site", but in the reverse case, we need to swap the order of the check.
		 */
		if ( $site_uri_len > $home_uri_len ) {
			if ( substr( $page_uri, 0, $site_uri_len ) == $site_uri ) {
				$page_uri = substr( $page_uri, $site_uri_len );
			}

			if ( substr( $page_uri, 0, $home_uri_len ) == $home_uri ) {
				$page_uri = substr( $page_uri, $home_uri_len );
			}
		} else {
			if ( substr( $page_uri, 0, $home_uri_len ) == $home_uri ) {
				$page_uri = substr( $page_uri, $home_uri_len );
			}

			if ( substr( $page_uri, 0, $site_uri_len ) == $site_uri ) {
				$page_uri = substr( $page_uri, $site_uri_len );
			}
		}

		//Sanitize Xss injection
		$page_uri = filter_var( $page_uri, FILTER_SANITIZE_STRING );

		// If we're at the root (aka the URI is blank), let's make sure to indicate it.
		if ( $page_uri == '' ) {
			$page_uri = '/';
		}

		return apply_filters( 'wp_statistics_page_uri', $page_uri );
	}

}