<?php

/**
 * Class BEA_PVC_FPA_Main
 *
 * Add the settings and replace the url for the ajax call
 */
class BEA_PVC_FPA_Main {
	/**
	 * Register hooks
	 */
	public function __construct() {
		add_action( 'bea_pvc_counter_url', array( __CLASS__, 'bea_pvc_counter_url' ), 10, 2 );
		add_action( 'bea_pvc_get_settings_fields', array( __CLASS__, 'bea_pvc_get_settings_fields' ), 10, 1 );
	}

	/**
	 * Filter the counter url to change for the custom one.
	 *
	 * @param string $url
	 * @param array $current_options
	 *
	 * @return string
	 * @author Nicolas JUEN
	 */
	public static function bea_pvc_counter_url( $url = '', $current_options = array() ) {
		if ( ! isset( $current_options['mode'] ) || 'js-php' !== $current_options['mode'] ) {
			return $url;
		}
		$parts_url = wp_parse_url( $url );
		return add_query_arg( wp_parse_args( $parts_url['query'] ), BEA_PVC_FPA_URL . 'tools/counter.php' );
	}

	/**
	 * Add the settings fields into the settings page
	 *
	 * @param array $settings_fields
	 *
	 * @return array
	 * @author Nicolas JUEN
	 */
	public static function bea_pvc_get_settings_fields( $settings_fields = array() ) {
		$settings_fields['bea-pvc-main'][0]['options']['js-php'] = __( 'JS call with pure PHP script (best performance)', 'bea-post-views-counter' );
		$settings_fields['bea-pvc-main'][0]['desc']              = __( 'Mode <strong>inline</strong> is the simplest, most reliable, but it is not compatible with plugins static cache.<br />The two modes "JS Call" add asynchronous JavaScript code in the footer of your site for compatibilizing the number of views. The difference between <strong>WordPress</strong> and <strong>PHP Pure</strong> is the mechanism used to update the counters in the database. The <strong>pure PHP</strong> mode is on average 10 times more efficient than the WP mode because it does not load WordPress!<br />However, the <strong>pure PHP</strong> mode sometimes have problems operating in some accommodation, this is the reason why this is not the default mode.', 'bea-post-views-counter' );

		return $settings_fields;
	}
}