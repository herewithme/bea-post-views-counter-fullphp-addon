<?php
/**
 * This script will increment the counter without loading the core of WordPress,
 * It allows gains performance due to the memory/CPU usage of WordPress.
 *
 * This script try to load only the wp-config.php file of WP to retrieve MySQL crendentials.
 */

// Enable or not debug mode, with error message
$debug = 0;
if ( $debug == 1 ) {
	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );
} else {
	ini_set( 'display_errors', 0 );
}

// Define current mode
define( 'BEA_PVC_PHP_MODE', true );

// Shortinit, if bootstrap filter failed
define( 'SHORTINIT', true );

/*
  Define a filter class to strip out the wp-settings require
 */

class stopwpbootstrap_filter extends php_user_filter {

	function filter( $in, $out, &$consumed, $closing ) {
		while ( $bucket = stream_bucket_make_writeable( $in ) ) {
			$bucket->data = str_replace( "require_once(ABSPATH . 'wp-settings.php');", "", $bucket->data );
			$consumed += $bucket->datalen;
			stream_bucket_append( $out, $bucket );
		}

		return PSFS_PASS_ON;
	}

}

// Load WP config file, without WP !
// this filter will strip out the wp-settings require line
// preventing the full WP stack from bootstrapping
stream_filter_register( "stopwpbootstrap", "stopwpbootstrap_filter" );

// Detect wp-config location
// Inspiration : http://boiteaweb.fr/wordpress-bootstraps-ou-comment-bien-charger-wordpress-6717.html
$wp_location = 'wp-config.php';
while ( ! is_file( $wp_location ) ) {
	if ( is_dir( '..' ) ) {
		chdir( '..' );
	} else {
		die( '-9' ); // Config file not exist, stop script
	}
}

// by reading this file via the php filter protocol,
// we can safely include wp-config.php in our function scope now 
include( "php://filter/read=stopwpbootstrap/resource=" . $wp_location );

// Constant are defined ? WP & Plugin is loaded ?
if ( ! defined( 'DB_NAME' ) ) {
	die( '-8' );
}

// Timezone
date_default_timezone_set( 'UTC' );
// Load PHP MySQL Lib
require( dirname( __FILE__ ) . '/../libraries/ezsql/shared/ez_sql_core.php' );
require( dirname( __FILE__ ) . '/../libraries/ezsql/mysqli/ez_sql_mysqli.php' );

// Load counter class for extend it
if ( is_file( dirname( __FILE__ ) . '/../../bea-post-views-counter/classes/counter.php' ) ) {
	require( dirname( __FILE__ ) . '/../../bea-post-views-counter/classes/counter.php' );
} else {
	die( '-7' );
}

/**
 * Pure PHP class
 */
class BEA_PVC_Counter_Full_PHP extends BEA_PVC_Counter {

	/**
	 * The database connector
	 *
	 * @var ezSQL_mysqli|null
	 */
	protected $_db = null;

	/**
	 * The blog id.
	 *
	 * @var int
	 */
	protected $_blog_id = 0;

	/**
	 * BEA_PVC_Counter_Full_PHP constructor.
	 *
	 * @param int $post_id
	 * @param int $blog_id
	 */
	public function __construct( $post_id = 0, $blog_id = 0 ) {
		$blog_id = (int) $blog_id;
		if ( 0 === $blog_id ) {
			return;
		}

		// Keep blog id.
		$this->_blog_id = $blog_id;

		// Init SQL connection.
		$this->_db = new ezSQL_mysqli( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );

		// Init parent.
		parent::__construct( $post_id, false );

		$this->_fill_data();
	}

	/**
	 * Get the table name for the database.
	 *
	 * @return string
	 * @author Nicolas JUEN
	 */
	protected function _get_table_name() {
		return $this->_get_prefix() . 'post_views_counter';
	}

	/**
	 * Get prefix for the table name.
	 *
	 * @return string
	 * @author Nicolas JUEN
	 */
	private function _get_prefix() {
		global $table_prefix, $wpdb;

		if ( defined( 'WPINC' ) ) { // Shortinit, but config_only not work
			return $wpdb->prefix;
		}

		// PURE PURE PHP.
		return 1 === $this->_blog_id ? $table_prefix : $table_prefix . $this->_blog_id . '_';
	}

	/**
	 * Get the row.
	 *
	 * @param string $query
	 *
	 * @return array
	 * @author Nicolas JUEN
	 */
	protected function _get_row( $query = '' ) {
		$result = $this->_db->get_row( $query, ARRAY_A );
		$result = ( is_bool( $result ) ) ? false : $result; // TRUE = FALSE for WP.
		return $result;
	}

	/**
	 * Insert the data into the database.
	 *
	 * @param string $table_name
	 * @param array $values
	 *
	 * @return bool|int
	 * @author Nicolas JUEN
	 */
	protected function _insert( $table_name = '', $values = array() ) {
		return $this->_db->query( "INSERT INTO {$table_name} SET " . $this->_db->get_set( $values ) );
	}

	/**
	 * Update the rows
	 *
	 * @param string $table_name
	 * @param array $values
	 * @param array $where
	 *
	 * @return bool|int
	 * @author Nicolas JUEN
	 */
	protected function _update( $table_name = '', $values = array(), $where = array() ) {
		return $this->_db->query( "UPDATE {$table_name} SET " . $this->_db->get_set( $values ) . " WHERE " . $this->_db->get_set( $where ) );
	}

	/**
	 * Get the options from the database.
	 *
	 * @param string $option_name
	 *
	 * @return bool|mixed
	 * @author Nicolas JUEN
	 */
	protected function get_option( $option_name = '' ) {
		$result = $this->_get_row( sprintf( "SELECT option_value FROM " . $this->_get_prefix() . "options WHERE option_name = '%s'", $this->_db->escape( $option_name ) ) );
		return false !== $result ? unserialize( $result['option_value'] ) : false;
	}
}

if ( isset( $_GET['post_id'] ) && (int) $_GET['post_id'] > 0 && isset( $_GET['blog_id'] ) && (int) $_GET['blog_id'] > 0 ) {
	$counter = new BEA_PVC_Counter_Full_PHP( (int) $_GET['post_id'], (int) $_GET['blog_id'] );
	$result  = $counter->increment();

	die( ( $result === true ) ? '1' : '-1' );
}

die( '0' ); // Invalid call
