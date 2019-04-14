<?php

namespace WP_STATISTICS;

class SearchEngine {
	/**
	 * Get List Of Search engine in WP-Statistics
	 *
	 * @param bool $all
	 * @return array
	 */
	public static function getList( $all = false ) {
		global $WP_Statistics;

		$default = $engines = array(
			'ask'        => array(
				'name'         => 'Ask.com',
				'translated'   => __( 'Ask.com', 'wp-statistics' ),
				'tag'          => 'ask',
				'sqlpattern'   => '%ask.com%',
				'regexpattern' => 'ask\.com',
				'querykey'     => 'q',
				'image'        => 'ask.png',
			),
			'baidu'      => array(
				'name'         => 'Baidu',
				'translated'   => __( 'Baidu', 'wp-statistics' ),
				'tag'          => 'baidu',
				'sqlpattern'   => '%baidu.com%',
				'regexpattern' => 'baidu\.com',
				'querykey'     => 'wd',
				'image'        => 'baidu.png',
			),
			'bing'       => array(
				'name'         => 'Bing',
				'translated'   => __( 'Bing', 'wp-statistics' ),
				'tag'          => 'bing',
				'sqlpattern'   => '%bing.com%',
				'regexpattern' => 'bing\.com',
				'querykey'     => 'q',
				'image'        => 'bing.png',
			),
			'clearch'    => array(
				'name'         => 'clearch.org',
				'translated'   => __( 'clearch.org', 'wp-statistics' ),
				'tag'          => 'clearch',
				'sqlpattern'   => '%clearch.org%',
				'regexpattern' => 'clearch\.org',
				'querykey'     => 'q',
				'image'        => 'clearch.png',
			),
			'duckduckgo' => array(
				'name'         => 'DuckDuckGo',
				'translated'   => __( 'DuckDuckGo', 'wp-statistics' ),
				'tag'          => 'duckduckgo',
				'sqlpattern'   => array( '%duckduckgo.com%', '%ddg.gg%' ),
				'regexpattern' => array( 'duckduckgo\.com', 'ddg\.gg' ),
				'querykey'     => 'q',
				'image'        => 'duckduckgo.png',
			),
			'google'     => array(
				'name'         => 'Google',
				'translated'   => __( 'Google', 'wp-statistics' ),
				'tag'          => 'google',
				'sqlpattern'   => '%google.%',
				'regexpattern' => 'google\.',
				'querykey'     => 'q',
				'image'        => 'google.png',
			),
			'yahoo'      => array(
				'name'         => 'Yahoo!',
				'translated'   => __( 'Yahoo!', 'wp-statistics' ),
				'tag'          => 'yahoo',
				'sqlpattern'   => '%yahoo.com%',
				'regexpattern' => 'yahoo\.com',
				'querykey'     => 'p',
				'image'        => 'yahoo.png',
			),
			'yandex'     => array(
				'name'         => 'Yandex',
				'translated'   => __( 'Yandex', 'wp-statistics' ),
				'tag'          => 'yandex',
				'sqlpattern'   => '%yandex.ru%',
				'regexpattern' => 'yandex\.ru',
				'querykey'     => 'text',
				'image'        => 'yandex.png',
			),
			'qwant'      => array(
				'name'         => 'Qwant',
				'translated'   => __( 'Qwant', 'wp-statistics' ),
				'tag'          => 'qwant',
				'sqlpattern'   => '%qwant.com%',
				'regexpattern' => 'qwant\.com',
				'querykey'     => 'q',
				'image'        => 'qwant.png',
			)
		);

		if ( $all == false ) {
			foreach ( $engines as $key => $engine ) {
				if ( $WP_Statistics->option->get( 'disable_se_' . $engine['tag'] ) ) {
					unset( $engines[ $key ] );
				}
			}

			// If we've disabled all the search engines, reset the list back to default.
			if ( count( $engines ) == 0 ) {
				$engines = $default;
			}
		}

		return $engines;
	}

	/**
	 * Return Default Value if Search Engine Not Exist
	 *
	 * @return array
	 */
	public static function default_engine() {
		return array(
			'name'         => _x( 'Unknown', 'Search Engine', 'wp-statistics' ),
			'tag'          => '',
			'sqlpattern'   => '',
			'regexpattern' => '',
			'querykey'     => 'q',
			'image'        => 'unknown.png',
		);
	}

	/**
	 * Get Information About Custom Search Engine
	 *
	 * @param bool|false $engine
	 * @return array|bool
	 */
	public static function get( $engine = false ) {

		// If there is no URL and no referrer, always return false.
		if ( $engine == false ) {
			return false;
		}

		// Get the list of search engines
		$search_engines = self::getList();

		// Search Key in List
		if ( array_key_exists( $engine, $search_engines ) ) {
			return $search_engines[ $engine ];
		}

		// If no SE matched, return some defaults.
		return self::default_engine();
	}

	/**
	 * Get Search Engine Regex From List
	 *
	 * @param string $search_engine
	 * @return string
	 */
	public static function regex( $search_engine = 'all' ) {

		// Get a complete list of search engines
		$search_engine_list = self::getList();
		$search_query       = '';

		// Are we getting results for all search engines or a specific one?
		if ( strtolower( $search_engine ) == 'all' ) {
			foreach ( $search_engine_list as $se ) {
				if ( is_array( $se['regexpattern'] ) ) {
					foreach ( $se['regexpattern'] as $subse ) {
						$search_query .= "{$subse}|";
					}
				} else {
					$search_query .= "{$se['regexpattern']}|";
				}
			}

			// Trim off the last '|' for the loop above.
			$search_query = substr( $search_query, 0, strlen( $search_query ) - 1 );
		} else {
			if ( is_array( $search_engine_list[ $search_engine ]['regexpattern'] ) ) {
				foreach ( $search_engine_list[ $search_engine ]['regexpattern'] as $se ) {
					$search_query .= "{$se}|";
				}

				// Trim off the last '|' for the loop above.
				$search_query = substr( $search_query, 0, strlen( $search_query ) - 1 );
			} else {
				$search_query .= $search_engine_list[ $search_engine ]['regexpattern'];
			}
		}

		return "({$search_query})";
	}

	/**
	 * Get Search Engine information Bt Url Regex
	 *
	 * @param bool|false $url
	 * @return array|bool
	 */
	public static function getByUrl( $url = false ) {

		// If no URL was passed in, get the current referrer for the session.
		if ( ! $url ) {
			$url = ! empty( $_SERVER['HTTP_REFERER'] ) ? Referred::get() : false;
		}

		// If there is no URL and no referrer, always return false.
		if ( $url == false ) {
			return false;
		}

		// Parse the URL in to it's component parts.
		$parts = @parse_url( $url );

		// Get the list of search engines we currently support.
		$search_engines = self::getList();

		// Loop through the SE list until we find which search engine matches.
		foreach ( $search_engines as $key => $value ) {
			$search_regex = self::regex( $key );
			preg_match( '/' . $search_regex . '/', $parts['host'], $matches );
			if ( isset( $matches[1] ) ) {
				// Return the first matched SE.
				return $value;
			}
		}

		// If no SE matched, return some defaults.
		return self::default_engine();
	}

	/**
	 * Parses a URL from a referrer and return the search query words used.
	 *
	 * @param bool|false $url
	 * @return bool|string
	 */
	public static function getByQueryString( $url = false ) {
		//Error
		$error = 'No search query found!';

		// Get Referred Url
		$referred_url = Referred::getRefererURL();

		// If no URL was passed in, get the current referrer for the session.
		if ( ! $url ) {
			$url = ( $referred_url == "" ? false : $referred_url );
		}

		// If there is no URL and no referrer, always return false.
		if ( $url == false ) {
			return false;
		}

		// Parse the URL in to it's component parts.
		$parts = @parse_url( $url );

		// Check query exist
		if ( array_key_exists( 'query', $parts ) ) {
			parse_str( $parts['query'], $query );
		} else {
			$query = array();
		}

		// Get the list of search engines we currently support.
		$search_engines = self::getList();

		// Loop through the SE list until we find which search engine matches.
		foreach ( $search_engines as $key => $value ) {
			$search_regex = self::regex( $key );
			preg_match( '/' . $search_regex . '/', $parts['host'], $matches );
			if ( isset( $matches[1] ) ) {
				if ( array_key_exists( $search_engines[ $key ]['querykey'], $query ) ) {
					$words = strip_tags( $query[ $search_engines[ $key ]['querykey'] ] );
				} else {
					$words = '';
				}

				return ( $words == "" ? $error : $words );
			}
		}

		return $error;
	}


}