<?php

namespace WP_STATISTICS;

class Visit {
	/**
	 * Check Active Record Visits
	 *
	 * @return mixed
	 */
	public static function active() {
		return ( has_filter( 'wp_statistics_active_visits' ) ) ? apply_filters( 'wp_statistics_active_visits', true ) : $GLOBALS['WP_Statistics']->option->get( 'visits' );
	}

	/**
	 * Record Users Visit in DB
	 */
	public static function record() {
		global $wpdb;

		// Check to see if we're a returning visitor.
		$result = $wpdb->get_row( "SELECT * FROM `" . DB::table( 'visit' ) . "` ORDER BY `" . DB::table( 'visit' ) . "`.`ID` DESC" );

		// if we have not a Visitor in This Day then create new row or Update before row in DB
		if ( $result->last_counter != TimeZone::getCurrentDate( 'Y-m-d' ) ) {

			$sql = $wpdb->prepare( 'INSERT INTO `' . DB::table( 'visit' ) . '` (last_visit, last_counter, visit) VALUES ( %s, %s, %d) ON DUPLICATE KEY UPDATE visit = visit + ' . Visitor::getCoefficient(), TimeZone::getCurrentDate(), TimeZone::getCurrentDate( 'Y-m-d' ), Visitor::getCoefficient() );
			$wpdb->query( $sql );

		} else {

			$sql = $wpdb->prepare( 'UPDATE `' . DB::table( 'visit' ) . '` SET `visit` = `visit` + %d, `last_visit` = %s WHERE `last_counter` = %s', Visitor::getCoefficient(), TimeZone::getCurrentDate(), $result->last_counter );
			$wpdb->query( $sql );
		}

	}

}