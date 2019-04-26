<?php
/* Gutenberg support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('invetex_gutenberg_theme_setup')) {
    add_action( 'invetex_action_before_init_theme', 'invetex_gutenberg_theme_setup', 1 );
    function invetex_gutenberg_theme_setup() {
        if (is_admin()) {
            add_filter( 'invetex_filter_required_plugins', 'invetex_gutenberg_required_plugins' );
        }
    }
}

// Check if Instagram Widget installed and activated
if ( !function_exists( 'invetex_exists_gutenberg' ) ) {
    function invetex_exists_gutenberg() {
        return function_exists( 'the_gutenberg_project' ) && function_exists( 'register_block_type' );
    }
}

// Filter to add in the required plugins list
if ( !function_exists( 'invetex_gutenberg_required_plugins' ) ) {
    function invetex_gutenberg_required_plugins($list=array()) {
        if (in_array('gutenberg', (array)invetex_storage_get('required_plugins')))
            $list[] = array(
                    'name'         => esc_html__('Gutenberg', 'invetex'),
                    'slug'         => 'gutenberg',
                    'required'     => false
                );
        return $list;
    }
}