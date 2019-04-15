<?php
// Load the classes.
use GeoIp2\Database\Reader;

class WP_Statistics_GEO_IP_Hits extends \WP_Statistics_Hits {
	public function __construct() {
		global $WP_Statistics;
		// Call the parent constructor (WP_Statistics::__constructor).
		parent::__construct();

		// We may have set the location based on a private IP address in the hits class, if so, don't bother looking it up again.
		if ( $this->location == \WP_STATISTICS\GeoIP::$private_country ) {

			// Store the location in the protected $location variable from the parent class.
			$this->location = \WP_STATISTICS\GeoIP::getCountry();
		}

		// Check to see if we are excluded by the GeoIP rules.
		if ( ! $this->exclusion_match ) {

		}
	}
}