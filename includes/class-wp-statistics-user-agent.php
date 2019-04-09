<?php

namespace WP_STATISTICS;

class UserAgent {
	/**
	 * Get User Agent
	 *
	 * @return mixed
	 */
	public static function getHttpUserAgent() {
		if ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ) {
			$UserAgent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$UserAgent = false;
		}

		return apply_filters( 'wp_statistics_user_agent', $UserAgent );
	}


}