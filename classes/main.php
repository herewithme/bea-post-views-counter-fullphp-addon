<?php
class BEA_PVC_FPA_Main {
	/**
	 * Register hooks
	 */
	public function __construct() {
		if ( !defined('BEA_PVC_VERSION') ) {
			return false;
		}

		add_action('bea_pvc_counter_url', array(__CLASS__, 'bea_pvc_counter_url'), 10, 2 );
		add_action('bea_pvc_get_settings_fields', array(__CLASS__, 'bea_pvc_get_settings_fields'), 10, 1 );

		return true;
	}
	

	public static function bea_pvc_counter_url( $url = '', $current_options = array() ) {
		global $wpdb;

		if ( isset($current_options['mode']) && $current_options['mode'] == 'js-php' ) { // Pure PHP
			$blog_id = $wpdb->blogid === 0 ? 1 : $wpdb->blogid;
			$url = add_query_arg( array( 'blog_id' => $blog_id, 'post_id' => get_queried_object_id() ), BEA_PVC_FPA_URL.'tools/counter.php' );
		}

		return $url;
	}
	
	public static function bea_pvc_get_settings_fields( $settings_fields ) {
		$settings_fields['bea-pvc-main'][0]['options']['js-php'] = __('JS call with pure PHP script (best performance)', 'bea-post-views-counter');
		$settings_fields['bea-pvc-main'][0]['desc'] = __('Mode <strong>inline</strong> is the simplest, most reliable, but it is not compatible with plugins static cache.<br />The two modes "JS Call" add asynchronous JavaScript code in the footer of your site for compatibilizing the number of views. The difference between <strong>WordPress</strong> and <strong>PHP Pure</strong> is the mechanism used to update the counters in the database. The <strong>pure PHP</strong> mode is on average 10 times more efficient than the WP mode because it does not load WordPress!<br />However, the <strong>pure PHP</strong> mode sometimes have problems operating in some accommodation, this is the reason why this is not the default mode.', 'bea-post-views-counter');
		
		return $settings_fields;
	}
}