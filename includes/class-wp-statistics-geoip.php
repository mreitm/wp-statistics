<?php

namespace WP_STATISTICS;

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
			} catch ( \MaxMind\Db\Reader\InvalidDatabaseException $e ) {
				return false;
			}
		} else {
			return false;
		}

		return $reader;
	}
}