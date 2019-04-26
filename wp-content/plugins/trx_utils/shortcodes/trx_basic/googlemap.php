<?php

/* Theme setup section
-------------------------------------------------------------------- */
if (!function_exists('invetex_sc_googlemap_theme_setup')) {
	add_action( 'invetex_action_before_init_theme', 'invetex_sc_googlemap_theme_setup' );
	function invetex_sc_googlemap_theme_setup() {
		add_action('invetex_action_shortcodes_list', 		'invetex_sc_googlemap_reg_shortcodes');
		if (function_exists('invetex_exists_visual_composer') && invetex_exists_visual_composer())
			add_action('invetex_action_shortcodes_list_vc','invetex_sc_googlemap_reg_shortcodes_vc');
	}
}



/* Shortcode implementation
-------------------------------------------------------------------- */

//[trx_googlemap id="unique_id" width="width_in_pixels_or_percent" height="height_in_pixels"]
//	[trx_googlemap_marker address="your_address"]
//[/trx_googlemap]

if (!function_exists('invetex_sc_googlemap')) {
    function invetex_sc_googlemap($atts, $content = null) {
        if (invetex_in_shortcode_blogger()) return '';
        extract(invetex_html_decode(shortcode_atts(array(
            // Individual params
            "zoom" => 16,
            "style" => 'default',
            // Common params
            "id" => "",
            "class" => "",
            "css" => "",
            "animation" => "",
            "width" => "100%",
            "height" => "400",
            "top" => "",
            "bottom" => "",
            "left" => "",
            "right" => ""
        ), $atts)));
        $class .= ($class ? ' ' : '') . invetex_get_css_position_as_classes($top, $right, $bottom, $left);
        $css .= invetex_get_css_dimensions_from_values($width, $height);
        if (empty($id)) $id = 'sc_googlemap_'.str_replace('.', '', mt_rand());
        if (empty($style)) $style = invetex_get_custom_option('googlemap_style');
        if (trx_utils_get_option('api_google') != '') {
            trx_utils_enqueue_googlemap();
            if (trx_utils_is_on(trx_utils_get_option('debug_mode'))) {
                wp_enqueue_script('googlemap', invetex_get_protocol() . '://maps.google.com/maps/api/js' . ($api_key ? '?key=' . $api_key : ''), array(), null, true);
                wp_enqueue_script('invetex-googlemap-script', invetex_get_file_url('js/core.googlemap.js'), array(), null, true);
            }
        }
        global $THEMEREX_GLOBALS;
        $THEMEREX_GLOBALS['sc_googlemap_markers'] = array();
        $content = do_shortcode($content);
        $output = '';
        if (count($THEMEREX_GLOBALS['sc_googlemap_markers']) == 0) {
            $THEMEREX_GLOBALS['sc_googlemap_markers'][] = array(
                'title' => invetex_get_custom_option('googlemap_title'),
                'description' => invetex_strmacros(invetex_get_custom_option('googlemap_description')),
                'latlng' => invetex_get_custom_option('googlemap_latlng'),
                'address' => invetex_get_custom_option('googlemap_address'),
                'point' => invetex_get_custom_option('googlemap_marker')
            );
        }
        $output .= '<div id="'.esc_attr($id).'"'
            . ' class="sc_googlemap'. (!empty($class) ? ' '.esc_attr($class) : '').'"'
            . ($css!='' ? ' style="'.esc_attr($css).'"' : '')
            . (!invetex_param_is_off($animation) ? ' data-animation="'.esc_attr(invetex_get_animation_classes($animation)).'"' : '')
            . ' data-zoom="'.esc_attr($zoom).'"'
            . ' data-style="'.esc_attr($style).'"'
            . '>';

        $cnt = 0;
        foreach ($THEMEREX_GLOBALS['sc_googlemap_markers'] as $marker) {
            $cnt++;
            if (empty($marker['id'])) $marker['id'] = $id.'_'.intval($cnt);
            if (invetex_get_theme_option('api_google') != '') {
                $output .= '<div id="'.esc_attr($marker['id']).'" class="sc_googlemap_marker"'
                    . ' data-title="'.esc_attr($marker['title']).'"'
                    . ' data-description="'.esc_attr(invetex_strmacros($marker['description'])).'"'
                    . ' data-address="'.esc_attr($marker['address']).'"'
                    . ' data-latlng="'.esc_attr($marker['latlng']).'"'
                    . ' data-point="'.esc_attr($marker['point']).'"'
                    . '></div>';
            } else {
                $output .= '<iframe src="https://maps.google.com/maps?t=m&output=embed&iwloc=near&z='.esc_attr($zoom > 0 ? $zoom : 14).'&q='
                    . esc_attr(!empty($marker['address']) ? urlencode($marker['address']) : '')
                    . ( !empty($marker['latlng'])
                        ? ( !empty($marker['address']) ? '@' : '' ) . str_replace(' ', '', $marker['latlng'])
                        : ''
                    )
                    . '" scrolling="no" marginheight="0" marginwidth="0" frameborder="0"'
                    . ' aria-label="' . esc_attr(!empty($marker['title']) ? $marker['title'] : '') . '"></iframe>';
                break; // Remove this line if you want display separate iframe for each marker (otherwise only first marker shown)
            }
        }
        $output .= '</div>';
        return apply_filters('invetex_shortcode_output', $output, 'trx_googlemap', $atts, $content);
    }
    if (function_exists('invetex_require_shortcode')) invetex_require_shortcode("trx_googlemap", "invetex_sc_googlemap");
}


if (!function_exists('invetex_sc_googlemap_marker')) {	
	function invetex_sc_googlemap_marker($atts, $content = null) {
		if (invetex_in_shortcode_blogger()) return '';
		extract(invetex_html_decode(shortcode_atts(array(
			// Individual params
			"title" => "",
			"address" => "",
			"latlng" => "",
			"point" => "",
			// Common params
			"id" => ""
		), $atts)));
		if (!empty($point)) {
			if ($point > 0) {
				$attach = wp_get_attachment_image_src( $point, 'full' );
				if (isset($attach[0]) && $attach[0]!='')
					$point = $attach[0];
			}
		}
		$content = do_shortcode($content);
		invetex_storage_set_array('sc_googlemap_markers', '', array(
			'id' => $id,
			'title' => $title,
			'description' => !empty($content) ? $content : $address,
			'latlng' => $latlng,
			'address' => $address,
			'point' => $point ? $point : invetex_get_custom_option('googlemap_marker')
			)
		);
		return '';
	}
	invetex_require_shortcode("trx_googlemap_marker", "invetex_sc_googlemap_marker");
}



/* Register shortcode in the internal SC Builder
-------------------------------------------------------------------- */
if ( !function_exists( 'invetex_sc_googlemap_reg_shortcodes' ) ) {
	//add_action('invetex_action_shortcodes_list', 'invetex_sc_googlemap_reg_shortcodes');
	function invetex_sc_googlemap_reg_shortcodes() {
	
		invetex_sc_map("trx_googlemap", array(
			"title" => esc_html__("Google map", 'trx_utils'),
			"desc" => wp_kses_data( __("Insert Google map with specified markers", 'trx_utils') ),
			"decorate" => false,
			"container" => true,
			"params" => array(
				"zoom" => array(
					"title" => esc_html__("Zoom", 'trx_utils'),
					"desc" => wp_kses_data( __("Map zoom factor", 'trx_utils') ),
					"divider" => true,
					"value" => 16,
					"min" => 1,
					"max" => 20,
					"type" => "spinner"
				),
				"style" => array(
					"title" => esc_html__("Map style", 'trx_utils'),
					"desc" => wp_kses_data( __("Select map style", 'trx_utils') ),
					"value" => "default",
					"type" => "checklist",
					"options" => invetex_get_sc_param('googlemap_styles')
				),
				"scheme" => array(
					"title" => esc_html__("Color scheme", 'trx_utils'),
					"desc" => wp_kses_data( __("Select color scheme for this block", 'trx_utils') ),
					"value" => "",
					"type" => "checklist",
					"options" => invetex_get_sc_param('schemes')
				),
				"width" => invetex_shortcodes_width('100%'),
				"height" => invetex_shortcodes_height(240),
				"top" => invetex_get_sc_param('top'),
				"bottom" => invetex_get_sc_param('bottom'),
				"left" => invetex_get_sc_param('left'),
				"right" => invetex_get_sc_param('right'),
				"id" => invetex_get_sc_param('id'),
				"class" => invetex_get_sc_param('class'),
				"animation" => invetex_get_sc_param('animation'),
				"css" => invetex_get_sc_param('css')
			),
			"children" => array(
				"name" => "trx_googlemap_marker",
				"title" => esc_html__("Google map marker", 'trx_utils'),
				"desc" => wp_kses_data( __("Google map marker", 'trx_utils') ),
				"decorate" => false,
				"container" => true,
				"params" => array(
					"address" => array(
						"title" => esc_html__("Address", 'trx_utils'),
						"desc" => wp_kses_data( __("Address of this marker", 'trx_utils') ),
						"value" => "",
						"type" => "text"
					),
					"latlng" => array(
						"title" => esc_html__("Latitude and Longitude", 'trx_utils'),
						"desc" => wp_kses_data( __("Comma separated marker's coorditanes (instead Address)", 'trx_utils') ),
						"value" => "",
						"type" => "text"
					),
					"point" => array(
						"title" => esc_html__("URL for marker image file", 'trx_utils'),
						"desc" => wp_kses_data( __("Select or upload image or write URL from other site for this marker. If empty - use default marker", 'trx_utils') ),
						"readonly" => false,
						"value" => "",
						"type" => "media"
					),
					"title" => array(
						"title" => esc_html__("Title", 'trx_utils'),
						"desc" => wp_kses_data( __("Title for this marker", 'trx_utils') ),
						"value" => "",
						"type" => "text"
					),
					"_content_" => array(
						"title" => esc_html__("Description", 'trx_utils'),
						"desc" => wp_kses_data( __("Description for this marker", 'trx_utils') ),
						"rows" => 4,
						"value" => "",
						"type" => "textarea"
					),
					"id" => invetex_get_sc_param('id')
				)
			)
		));
	}
}


/* Register shortcode in the VC Builder
-------------------------------------------------------------------- */
if ( !function_exists( 'invetex_sc_googlemap_reg_shortcodes_vc' ) ) {
	//add_action('invetex_action_shortcodes_list_vc', 'invetex_sc_googlemap_reg_shortcodes_vc');
	function invetex_sc_googlemap_reg_shortcodes_vc() {
	
		vc_map( array(
			"base" => "trx_googlemap",
			"name" => esc_html__("Google map", 'trx_utils'),
			"description" => wp_kses_data( __("Insert Google map with desired address or coordinates", 'trx_utils') ),
			"category" => esc_html__('Content', 'trx_utils'),
			'icon' => 'icon_trx_googlemap',
			"class" => "trx_sc_collection trx_sc_googlemap",
			"content_element" => true,
			"is_container" => true,
			"as_parent" => array('only' => 'trx_googlemap_marker,trx_form,trx_section,trx_block,trx_promo'),
			"show_settings_on_create" => true,
			"params" => array(
				array(
					"param_name" => "zoom",
					"heading" => esc_html__("Zoom", 'trx_utils'),
					"description" => wp_kses_data( __("Map zoom factor", 'trx_utils') ),
					"admin_label" => true,
					"class" => "",
					"value" => "16",
					"type" => "textfield"
				),
				array(
					"param_name" => "style",
					"heading" => esc_html__("Style", 'trx_utils'),
					"description" => wp_kses_data( __("Map custom style", 'trx_utils') ),
					"admin_label" => true,
					"class" => "",
					"value" => array_flip(invetex_get_sc_param('googlemap_styles')),
					"type" => "dropdown"
				),
				array(
					"param_name" => "scheme",
					"heading" => esc_html__("Color scheme", 'trx_utils'),
					"description" => wp_kses_data( __("Select color scheme for this block", 'trx_utils') ),
					"class" => "",
					"value" => array_flip(invetex_get_sc_param('schemes')),
					"type" => "dropdown"
				),
				invetex_get_vc_param('id'),
				invetex_get_vc_param('class'),
				invetex_get_vc_param('animation'),
				invetex_get_vc_param('css'),
				invetex_vc_width('100%'),
				invetex_vc_height(240),
				invetex_get_vc_param('margin_top'),
				invetex_get_vc_param('margin_bottom'),
				invetex_get_vc_param('margin_left'),
				invetex_get_vc_param('margin_right')
			)
		) );
		
		vc_map( array(
			"base" => "trx_googlemap_marker",
			"name" => esc_html__("Googlemap marker", 'trx_utils'),
			"description" => wp_kses_data( __("Insert new marker into Google map", 'trx_utils') ),
			"class" => "trx_sc_collection trx_sc_googlemap_marker",
			'icon' => 'icon_trx_googlemap_marker',
			"show_settings_on_create" => true,
			"content_element" => true,
			"is_container" => true,
			"as_child" => array('only' => 'trx_googlemap'), // Use only|except attributes to limit parent (separate multiple values with comma)
			"params" => array(
				array(
					"param_name" => "address",
					"heading" => esc_html__("Address", 'trx_utils'),
					"description" => wp_kses_data( __("Address of this marker", 'trx_utils') ),
					"admin_label" => true,
					"class" => "",
					"value" => "",
					"type" => "textfield"
				),
				array(
					"param_name" => "latlng",
					"heading" => esc_html__("Latitude and Longitude", 'trx_utils'),
					"description" => wp_kses_data( __("Comma separated marker's coorditanes (instead Address)", 'trx_utils') ),
					"admin_label" => true,
					"class" => "",
					"value" => "",
					"type" => "textfield"
				),
				array(
					"param_name" => "title",
					"heading" => esc_html__("Title", 'trx_utils'),
					"description" => wp_kses_data( __("Title for this marker", 'trx_utils') ),
					"admin_label" => true,
					"class" => "",
					"value" => "",
					"type" => "textfield"
				),
				array(
					"param_name" => "point",
					"heading" => esc_html__("URL for marker image file", 'trx_utils'),
					"description" => wp_kses_data( __("Select or upload image or write URL from other site for this marker. If empty - use default marker", 'trx_utils') ),
					"class" => "",
					"value" => "",
					"type" => "attach_image"
				),
				invetex_get_vc_param('id')
			)
		) );
		
		class WPBakeryShortCode_Trx_Googlemap extends INVETEX_VC_ShortCodeCollection {}
		class WPBakeryShortCode_Trx_Googlemap_Marker extends INVETEX_VC_ShortCodeCollection {}
	}
}
?>