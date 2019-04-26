<?php
/* Contact Form 7 support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('invetex_cf7_theme_setup')) {
    add_action( 'after_setup_theme', 'invetex_cf7_theme_setup', 9 );
    function invetex_cf7_theme_setup() {

        // One-click importer support
        if (invetex_exists_cf7()) {
            add_action( 'wp_enqueue_scripts', 								'invetex_cf7_frontend_scripts', 1100);
            add_filter( 'invetex_filter_merge_styles',						'invetex_cf7_merge_styles' );


            if (is_admin()) {
                add_filter( 'invetex_filter_importer_options',				'invetex_cf7_importer_set_options' );
            }
        }
        if (is_admin()) {
            add_filter( 'invetex_filter_importer_required_plugins',			'invetex_cf7_importer_required_plugins', 10, 2 );
            add_filter( 'invetex_filter_required_plugins',			'invetex_cf7_required_plugins' );
        }
    }
}




// Check if cf7 installed and activated
if ( !function_exists( 'invetex_exists_cf7' ) ) {
    function invetex_exists_cf7() {
        return class_exists('WPCF7');
    }
}





// Filter to add in the required plugins list
if ( !function_exists( 'invetex_cf7_required_plugins' ) ) {
    //Handler of the add_filter('invetex_filter_required_plugins',	'invetex_cf7_required_plugins');
    function invetex_cf7_required_plugins($list=array()) {

        $list[] = array(
            'name' 		=> esc_html__('Contact Form 7', 'invetex'),
            'slug' 		=> 'contact-form-7',
            'required' 	=> false
        );

        return $list;
    }
}





// Enqueue custom styles
if ( !function_exists( 'invetex_cf7_frontend_scripts' ) ) {
    function invetex_cf7_frontend_scripts() {
        if (file_exists(invetex_get_file_dir('css/contact-form-7.css')))
            wp_enqueue_style( 'invetex-contact-form-7',  invetex_get_file_url('css/contact-form-7.css'), array(), null );
    }
}

// Merge custom styles
if ( !function_exists( 'invetex_cf7_merge_styles' ) ) {
    //Handler of the add_filter('invetex_filter_merge_styles', 'invetex_cf7_merge_styles');
    function invetex_cf7_merge_styles($css) {
        return $css . invetex_fgc(invetex_get_file_dir('css/contact-form-7.css'));
    }
}




// One-click import support
//------------------------------------------------------------------------

// Check cf7 in the required plugins
if ( !function_exists( 'invetex_cf7_importer_required_plugins' ) ) {
    //Handler of the add_filter( 'invetex_filter_importer_required_plugins',	'invetex_cf7_importer_required_plugins', 10, 2 );
    function invetex_cf7_importer_required_plugins($not_installed='', $list='') {
        if (invetex_strpos($list, 'contact-form-7')!==false && !invetex_exists_cf7() )
            $not_installed .= '<br>' . esc_html__('Contact Form 7', 'invetex');
        return $not_installed;
    }
}

// Set options for one-click importer
if ( !function_exists( 'invetex_cf7_importer_set_options' ) ) {
    //Handler of the add_filter( 'invetex_filter_importer_options',	'invetex_cf7_importer_set_options' );
    function invetex_cf7_importer_set_options($options=array()) {
        if ( in_array('contact-form-7', invetex_storage_get('required_plugins')) && invetex_exists_cf7() ) {
            $options['additional_options'][] = 'wpcf7';
        }
        return $options;
    }
}



?>