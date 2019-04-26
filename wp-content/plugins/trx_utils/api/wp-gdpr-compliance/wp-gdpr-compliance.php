<?php
/**
 * Plugin support: WP GDPR Compliance
 *
 * @package WordPress
 * @subpackage ThemeREX Addons
 * @since v1.6.49
 */

file_put_contents('test.txt', '1 ', FILE_APPEND);
// Check if plugin installed and activated
if ( !function_exists( 'trx_utils_exists_wp_gdpr_compliance' ) ) {
	function trx_utils_exists_wp_gdpr_compliance() {
		file_put_contents('test.txt', '2 ' . class_exists( 'WPGDPRC\WPGDPRC' ), FILE_APPEND);
		return class_exists( 'WPGDPRC\WPGDPRC' );
	}
}


// One-click import support
//------------------------------------------------------------------------

// Check plugin in the required plugins
if ( !function_exists( 'trx_utils_wp_gdpr_compliance_importer_required_plugins' ) ) {
	if (is_admin()) add_filter( 'trx_utils_filter_importer_required_plugins',	'trx_utils_wp_gdpr_compliance_importer_required_plugins', 10, 2 );
	function trx_utils_wp_gdpr_compliance_importer_required_plugins($not_installed='', $list='') {
		
		file_put_contents('test.txt', '3 list=' . var_export($list,  true) , FILE_APPEND);
		if (strpos($list, 'wp-gdpr-compliance')!==false && !trx_utils_exists_wp_gdpr_compliance() )
			$not_installed .= '<br>' . esc_html__('WP GDPR Compliance', 'trx_utils');
		return $not_installed;
	}
}


// Set plugin's specific importer options
if ( !function_exists( 'trx_utils_wp_gdpr_compliance_importer_set_options' ) ) {
	if (is_admin()) add_filter( 'trx_utils_filter_importer_options',	'trx_utils_wp_gdpr_compliance_importer_set_options' );
	function trx_utils_wp_gdpr_compliance_importer_set_options($options=array()) {
		
		file_put_contents('test.txt', ' 4  ' , FILE_APPEND);
		if ( trx_utils_exists_wp_gdpr_compliance() && in_array('wp-gdpr-compliance', $options['required_plugins']) ) {
			if (is_array($options)) {
				$options['additional_options'][] = 'wpgdprc_%';
			}
		}
		return $options;
	}
}

