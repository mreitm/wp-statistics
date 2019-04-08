<?php

namespace WP_STATISTICS;

class User {

	public $ID;

	public function __construct() {
		$this->ID = $this->get_user_id();
	}

	/**
	 * Check User is Logged in WordPress
	 *
	 * @return mixed
	 */
	public function is_login() {
		return is_user_logged_in();
	}

	/**
	 * Get Current User ID
	 *
	 * @return int
	 */
	public function get_user_id() {
		$user_id = 0;
		if ( $this->is_login() === true ) {
			$user_id = get_current_user_id();
			$user_id = apply_filters( 'wp_statistics_user_id', $user_id );
		}

		return $user_id;
	}

	/**
	 * Get User Data
	 *
	 * @param bool $user_id
	 * @return array
	 */
	public function get( $user_id = false ) {

		# Get User ID
		$user_id = $user_id ? $user_id : get_current_user_id();

		# Get User Data
		$user_info = get_object_vars( get_userdata( $user_id ) );

		# Get User Meta
		$user_info['meta'] = array_map( function ( $a ) {
			return $a[0];
		}, get_user_meta( $user_id ) );

		return $user_info;
	}

	/**
	 * Get Full name of User
	 *
	 * @param $user_id
	 * @return string
	 */
	public function get_name( $user_id ) {

		# Get User Info
		$user_info = $this->get( $user_id );

		# check display name
		if ( $user_info['display_name'] != "" ) {
			return $user_info['display_name'];
		}

		# Check First and Last name
		if ( $user_info['first_name'] != "" ) {
			return $user_info['first_name'] . " " . $user_info['last_name'];
		}

		# return Username
		return $user_info['user_login'];
	}

	/**
	 * Check User Exist By id
	 *
	 * @param $user_id
	 * @return bool
	 * We Don`t Use get_userdata or get_user_by function, because We need only count nor UserData object.
	 */
	public static function exists( $user_id ) {
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $user_id ) );
		if ( $count > 0 ) {
			return true;
		} else {
			return false;
		}
	}

}