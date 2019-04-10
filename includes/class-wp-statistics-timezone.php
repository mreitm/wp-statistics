<?php

namespace WP_STATISTICS;

class TimeZone {
	/**
	 * Set WordPress TimeZone offset
	 */
	public static function set_timezone() {
		if ( get_option( 'timezone_string' ) ) {
			return timezone_offset_get( timezone_open( get_option( 'timezone_string' ) ), new \DateTime() );
		} elseif ( get_option( 'gmt_offset' ) ) {
			return get_option( 'gmt_offset' ) * 60 * 60;
		}

		return 0;
	}

	/**
	 * Adds the timezone offset to the given time string
	 *
	 * @param $timestring
	 *
	 * @return int
	 */
	public static function strtotimetz( $timestring ) {
		return strtotime( $timestring ) + self::set_timezone();
	}

	/**
	 * Adds current time to timezone offset
	 *
	 * @return int
	 */
	public static function timetz() {
		return time() + self::set_timezone();
	}

	/**
	 * Returns a date string in the desired format with a passed in timestamp.
	 *
	 * @param $format
	 * @param $timestamp
	 * @return bool|string
	 */
	public static function getLocalDate( $format, $timestamp ) {
		return date( $format, $timestamp + self::set_timezone() );
	}

	/**
	 * @param string $format
	 * @param null $strtotime
	 * @param null $relative
	 *
	 * @return bool|string
	 */
	public static function getCurrentDate( $format = 'Y-m-d H:i:s', $strtotime = null, $relative = null ) {
		if ( $strtotime ) {
			if ( $relative ) {
				return date( $format, strtotime( "{$strtotime} day", $relative ) + self::set_timezone() );
			} else {
				return date( $format, strtotime( "{$strtotime} day" ) + self::set_timezone() );
			}
		} else {
			return date( $format, time() + self::set_timezone() );
		}
	}

	/**
	 * Returns a date string in the desired format.
	 *
	 * @param string $format
	 * @param null $strtotime
	 * @param null $relative
	 *
	 * @return bool|string
	 */
	public static function getRealCurrentDate( $format = 'Y-m-d H:i:s', $strtotime = null, $relative = null ) {
		if ( $strtotime ) {
			if ( $relative ) {
				return date( $format, strtotime( "{$strtotime} day", $relative ) );
			} else {
				return date( $format, strtotime( "{$strtotime} day" ) );
			}
		} else {
			return date( $format, time() );
		}
	}

	/**
	 * Returns an internationalized date string in the desired format.
	 *
	 * @param string $format
	 * @param null $strtotime
	 * @param string $day
	 *
	 * @return string
	 */
	public static function getCurrentDate_i18n( $format = 'Y-m-d H:i:s', $strtotime = null, $day = ' day' ) {
		if ( $strtotime ) {
			return date_i18n( $format, strtotime( "{$strtotime}{$day}" ) + self::set_timezone() );
		} else {
			return date_i18n( $format, time() + self::set_timezone() );
		}
	}

}