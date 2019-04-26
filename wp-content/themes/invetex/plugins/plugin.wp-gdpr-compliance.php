<?php
/* The GDPR compliance support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('invetex_gdpr_compliance_theme_setup')) {
    add_action( 'invetex_action_before_init_theme', 'invetex_gdpr_compliance_theme_setup', 1 );
    function invetex_gdpr_compliance_theme_setup() {
        if (is_admin()) {
            add_filter( 'invetex_filter_required_plugins', 'invetex_gdpr_compliance_required_plugins' );
        }
    }
}

// Check if WP GDPR Compliance installed and activated
if ( !function_exists( 'invetex_exists_gdpr_compliance' ) ) {
    function invetex_exists_gdpr_compliance() {
        return defined( 'WP_GDPR_C_SLUG' );
    }
}

// Filter to add in the required plugins list
if ( !function_exists( 'invetex_gdpr_compliance_required_plugins' ) ) {
    function invetex_gdpr_compliance_required_plugins($list=array()) {
        if (in_array('wp-gdpr-compliance', (array)invetex_storage_get('required_plugins')))
            $list[] = array(
                'name'         => esc_html__('WP GDPR Compliance', 'invetex'),
                'slug'         => 'wp-gdpr-compliance',
                'required'     => false
            );
        return $list;
    }
}

