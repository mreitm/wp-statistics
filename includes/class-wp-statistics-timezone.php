<?php

namespace WP_STATISTICS;

class TimeZone {

	public $timezone_offset;

	public function __construct() {
		$this->timezone_offset = $this->set_timezone();
	}

	/**
	 * Set WordPress TimeZone offset
	 */
	public function set_timezone() {
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
	public function strtotimetz( $timestring ) {
		return strtotime( $timestring ) + $this->timezone_offset;
	}

	/**
	 * Adds current time to timezone offset
	 *
	 * @return int
	 */
	public function timetz() {
		return time() + $this->timezone_offset;
	}

	/**
	 * Returns a date string in the desired format with a passed in timestamp.
	 *
	 * @param $format
	 * @param $timestamp
	 * @return bool|string
	 */
	public function Local_Date( $format, $timestamp ) {
		return date( $format, $timestamp + $this->timezone_offset );
	}

	/**
	 * @param string $format
	 * @param null $strtotime
	 * @param null $relative
	 *
	 * @return bool|string
	 */
	public function Current_Date( $format = 'Y-m-d H:i:s', $strtotime = null, $relative = null ) {

		if ( $strtotime ) {
			if ( $relative ) {
				return date( $format, strtotime( "{$strtotime} day", $relative ) + $this->timezone_offset );
			} else {
				return date( $format, strtotime( "{$strtotime} day" ) + $this->timezone_offset );
			}
		} else {
			return date( $format, time() + $this->timezone_offset );
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
	public function Real_Current_Date( $format = 'Y-m-d H:i:s', $strtotime = null, $relative = null ) {

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
	public function Current_Date_i18n( $format = 'Y-m-d H:i:s', $strtotime = null, $day = ' day' ) {
		if ( $strtotime ) {
			return date_i18n( $format, strtotime( "{$strtotime}{$day}" ) + $this->timezone_offset );
		} else {
			return date_i18n( $format, time() + $this->timezone_offset );
		}
	}

}