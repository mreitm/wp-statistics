<?php
/**
 * Plugin Name: WP Statistics
 * Plugin URI: https://wp-statistics.com/
 * Description: Complete WordPress Analytics and Statistics for your site!
 * Version: 12.6.1
 * Author: VeronaLabs
 * Author URI: http://veronalabs.com/
 * Text Domain: wp-statistics
 * Domain Path: /languages/
 */

# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

# Load Plugin Defines
require_once 'includes/defines.php';

# Load Plugin
require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics.php';

# Run WP-STATISTICS
$GLOBALS['WP_Statistics'] = new WP_Statistics;

add_action('init', function(){


});