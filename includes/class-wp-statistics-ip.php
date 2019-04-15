<?php

namespace WP_STATISTICS;

class IP {
	/**
	 * Default User IP
	 *
	 * @var string
	 */
	public static $default_ip = '127.0.0.1';

	/**
	 * Default Private SubNets
	 *
	 * @var array
	 */
	public static $private_SubNets = array( '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.1/24', 'fc00::/7' );

	/**
	 * Get Real User IP in Whip Package
	 *
	 * @return false|string
	 */
	public static function get_Whip_ip() {
		$whip = new \Vectorface\Whip\Whip( \Vectorface\Whip\Whip::PROXY_HEADERS | \Vectorface\Whip\Whip::REMOTE_ADDR );
		return $whip->getValidIpAddress();
	}

	/**
	 * Returns the current IP address of the remote client.
	 *
	 * @return bool|string
	 */
	public static function getIP() {

		// Set Default
		$ip = false;

		//Check If Rest Api Request //TODO Remove At Last
		if ( \WP_Statistics_Rest::is_rest() ) {
			return \WP_Statistics_Rest::params( 'ip' );
		}

		// Get User IP
		$user_ip = self::get_Whip_ip();
		if ( $user_ip != false ) {
			$ip = $user_ip;
		}

		// If no valid ip address has been found, use 127.0.0.1 (aka localhost).
		if ( false === $ip ) {
			$ip = self::$default_ip;
		}

		return apply_filters( 'wp_statistics_user_ip', $ip );
	}

	/**
	 * Generate hash string
	 */
	public static function getHashIP() {
		global $WP_Statistics;

		// Check Enabled Options
		if ( $WP_Statistics->option->get( 'hash_ips' ) == true ) {

			//Check If Rest Api Request //TODO Remove At Last
			if ( \WP_Statistics_Rest::is_rest() ) {
				return \WP_Statistics_Rest::params( 'hash_ip' );
			}

			return apply_filters( 'wp_statistics_hash_ip', '#hash#' . sha1( self::getIP() . ( UserAgent::getHttpUserAgent() == '' ? 'Unknown' : UserAgent::getHttpUserAgent() ) ) );
		}

		return false;
	}

	/**
	 * Store User IP To Database
	 */
	public static function StoreIP() {
		global $WP_Statistics;

		//Get User ip
		$user_ip = self::getIP();

		// use 127.0.0.1 If no valid ip address has been found.
		if ( false === $user_ip ) {
			return self::$default_ip;
		}

		// If the anonymize IP enabled for GDPR.
		if ( $WP_Statistics->option->get( 'anonymize_ips' ) == true ) {
			$user_ip = substr( $user_ip, 0, strrpos( $user_ip, '.' ) ) . '.0';
		}

		return $user_ip;
	}

}