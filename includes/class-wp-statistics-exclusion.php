<?php

namespace WP_STATISTICS;

class Exclusion {
	/**
	 * Check to see if the user wants us to record why we're excluding hits.
	 *
	 * @return mixed
	 */
	public static function is_record_exclusion() {
		return $GLOBALS['WP_Statistics']->option->get( 'record_exclusions' );
	}


}