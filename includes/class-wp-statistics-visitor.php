<?php

namespace WP_STATISTICS;

class Visitor {
	/**
	 * For each visit to account for several hits.
	 *
	 * @var int
	 */
	public static $coefficient = 1;

	/**
	 * Visitor constructor.
	 */
	public function __construct() {

	}

	/**
	 * Get Coefficient
	 */
	public static function get_coefficient() {
		$coefficient = $GLOBALS['WP_Statistics']->option->get( 'coefficient', self::$coefficient );
		return is_numeric( $coefficient ) and $coefficient > 0 ? $coefficient : self::$coefficient;
	}


}