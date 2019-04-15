<?php

namespace WP_STATISTICS;

use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoIP {
	/**
	 * List Geo ip Library
	 *
	 * @var array
	 */
	public static $library = array(
		'country' => array(
			'cdn'    => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz',
			'github' => 'https://raw.githubusercontent.com/wp-statistics/GeoLite2-Country/master/GeoLite2-Country.mmdb.gz',
			'file'   => 'GeoLite2-Country',
			'opt'    => 'geoip'
		),
		'city'    => array(
			'cdn'    => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz',
			'github' => 'https://raw.githubusercontent.com/wp-statistics/GeoLite2-City/master/GeoLite2-City.mmdb.gz',
			'file'   => 'GeoLite2-City',
			'opt'    => 'geoip_city'
		)
	);

	/**
	 * Geo IP file Extension
	 *
	 * @var string
	 */
	public static $file_extension = 'mmdb';

	/**
	 * Default Private Country
	 *
	 * @var String
	 */
	public static $private_country = '000';

	/**
	 * Get Geo IP Path
	 *
	 * @param $pack
	 * @return mixed
	 */
	public static function get_geo_ip_path( $pack ) {
		return path_join( Helper::get_uploads_dir( WP_STATISTICS_UPLOADS_DIR ), self::$library[ $pack ]['file'] . '.' . self::$file_extension );
	}

	/**
	 * geo ip Loader
	 *
	 * @param $pack
	 * @return bool|\GeoIp2\Database\Reader
	 */
	public static function Loader( $pack ) {

		// Check file Exist
		$file = self::get_geo_ip_path( $pack );
		if ( file_exists( $file ) ) {
			try {

				//Load GeoIP Reader
				$reader = new \GeoIp2\Database\Reader( $file );
			} catch ( InvalidDatabaseException $e ) {
				return false;
			}
		} else {
			return false;
		}

		return $reader;
	}

	/**
	 * Get Default Country Code
	 *
	 * @return String
	 */
	public static function getDefaultCountryCode() {
		global $WP_Statistics;

		$opt = $WP_Statistics->option->get( 'private_country_code' );
		if ( isset( $opt ) and ! empty( $opt ) ) {
			return trim( $opt );
		}

		return self::$private_country;
	}

	/**
	 * Get Country Detail By User IP
	 *
	 * @param bool $ip
	 * @param string $return
	 * @return String|null
	 * @see https://github.com/maxmind/GeoIP2-php
	 */
	public static function getCountry( $ip = false, $return = 'isoCode' ) {

		// Default Country Name
		$default_country = self::getDefaultCountryCode();

		// Get User IP
		$ip = ! isset( $ip ) ? IP::getIP() : $ip;

		// Load GEO-IP
		$reader = self::Loader( 'country' );

		//Get Country name
		if ( $reader != false ) {

			try {
				//Search in Geo-IP
				$record = $reader->country( $ip );

				//Get Country
				if ( $return == "all" ) {
					$location = $record->country;
				} else {
					$location = $record->country->{$return};
				}
			} catch ( AddressNotFoundException $e ) {
				//Don't Staff
			} catch ( InvalidDatabaseException $e ) {
				//Don't Staff
			}
		}

		if ( isset( $location ) and ! empty( $location ) ) {
			return $location;
		}

		return $default_country;
	}

}