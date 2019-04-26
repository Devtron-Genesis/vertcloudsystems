<?php
/**
 * Theme sprecific functions and definitions
 */

/* Theme setup section
------------------------------------------------------------------- */

// Set the content width based on the theme's design and stylesheet.
if ( ! isset( $content_width ) ) $content_width = 1170; /* pixels */

// Prepare demo data
$invetex_demo_data_url = esc_url('http://invetex.themerex.net/demo/');


// Add theme specific actions and filters
// Attention! Function were add theme specific actions and filters handlers must have priority 1
if ( !function_exists( 'invetex_theme_setup' ) ) {
	add_action( 'invetex_action_before_init_theme', 'invetex_theme_setup', 1 );
	function invetex_theme_setup() {

        // Add default posts and comments RSS feed links to head
        add_theme_support( 'automatic-feed-links' );

        // Enable support for Post Thumbnails
        add_theme_support( 'post-thumbnails' );

        // Custom header setup
        add_theme_support( 'custom-header', array('header-text'=>false));

        // Custom backgrounds setup
        add_theme_support( 'custom-background');

        // Supported posts formats
        add_theme_support( 'post-formats', array('gallery', 'video', 'audio', 'link', 'quote', 'image', 'status', 'aside', 'chat') );

        // Autogenerate title tag
        add_theme_support('title-tag');

        // Add user menu
        add_theme_support('nav-menus');

        // WooCommerce Support
        add_theme_support( 'woocommerce' );

        // Add wide and full blocks support
        add_theme_support( 'align-wide' );

		// Register theme menus
		add_filter( 'invetex_filter_add_theme_menus',		'invetex_add_theme_menus' );

		// Register theme sidebars
		add_filter( 'invetex_filter_add_theme_sidebars',	'invetex_add_theme_sidebars' );

		// Set options for importer
		add_filter( 'invetex_filter_importer_options',		'invetex_set_importer_options' );

		// Add theme required plugins
		add_filter( 'invetex_filter_required_plugins',		'invetex_add_required_plugins' );
		
		// Add preloader styles
		add_filter('invetex_filter_add_styles_inline',		'invetex_head_add_page_preloader_styles');

		// Init theme after WP is created
		add_action( 'wp',									'invetex_core_init_theme' );

		// Add theme specified classes into the body
		add_filter( 'body_class', 							'invetex_body_classes' );

		// Add data to the head and to the beginning of the body
		add_action('wp_head',								'invetex_head_add_page_meta', 1);
		add_action('before',								'invetex_body_add_gtm');
		add_action('before',								'invetex_body_add_toc');
		add_action('before',								'invetex_body_add_page_preloader');

		// Add data to the footer (priority 1, because priority 2 used for localize scripts)
		add_action('wp_footer',								'invetex_footer_add_views_counter', 1);
		add_action('wp_footer',								'invetex_footer_add_login_register', 1);
		add_action('wp_footer',								'invetex_footer_add_theme_customizer', 1);
		add_action('wp_footer',								'invetex_footer_add_scroll_to_top', 1);
		add_action('wp_footer',								'invetex_footer_add_custom_html', 1);
		add_action('wp_footer',								'invetex_footer_add_gtm2', 1);

		// Set list of the theme required plugins
		invetex_storage_set('required_plugins', array(
			'booked',
			'essgrids',
			'revslider',
			'trx_utils',
			'visual_composer',
			'woocommerce',
			'instagram_widget',
			'instagram_feed',
            'wp-gdpr-compliance',
            'contact-form-7',
            'mailchimp-for-wp'
			)
		);
		
	}
}


// Add/Remove theme nav menus
if ( !function_exists( 'invetex_add_theme_menus' ) ) {
	function invetex_add_theme_menus($menus) {
		return $menus;
	}
}


// Add theme specific widgetized areas
if ( !function_exists( 'invetex_add_theme_sidebars' ) ) {
	function invetex_add_theme_sidebars($sidebars=array()) {
		if (is_array($sidebars)) {
			$theme_sidebars = array(
				'sidebar_main'		=> esc_html__( 'Main Sidebar', 'invetex' ),
				'sidebar_outer'		=> esc_html__( 'Outer Sidebar', 'invetex' ),
				'sidebar_footer'	=> esc_html__( 'Footer Sidebar', 'invetex' )
			);
			if (function_exists('invetex_exists_woocommerce') && invetex_exists_woocommerce()) {
				$theme_sidebars['sidebar_cart']  = esc_html__( 'WooCommerce Cart Sidebar', 'invetex' );
			}
			$sidebars = array_merge($theme_sidebars, $sidebars);
		}
		return $sidebars;
	}
}


// Add theme required plugins
if ( !function_exists( 'invetex_add_required_plugins' ) ) {
	function invetex_add_required_plugins($plugins) {
		$plugins[] = array(
			'name' 		=> 'Invetex Utilities',
			'version'	=> '3.1',					// Minimal required version
			'slug' 		=> 'trx_utils',
			'source'	=> invetex_get_file_dir('plugins/install/trx_utils.zip'),
			'required' 	=> true
		);
		return $plugins;
	}
}


//------------------------------------------------------------------------
// One-click import support
//------------------------------------------------------------------------

// Set theme specific importer options
if ( ! function_exists( 'invetex_set_importer_options' ) ) {
    add_filter( 'trx_utils_filter_importer_options', 'invetex_set_importer_options', 9 );
    function invetex_set_importer_options( $options=array() ) {
        if ( is_array( $options ) ) {
            // Save or not installer's messages to the log-file
            $options['debug'] = false;
            // Prepare demo data
            if ( is_dir( INVETEX_THEME_PATH . 'demo/' ) ) {
                $options['demo_url'] = INVETEX_THEME_PATH . 'demo/';
            } else {
                $options['demo_url'] = esc_url( invetex_get_protocol().'://demofiles.themerex.net/invetex/' ); // Demo-site domain
            }

            // Required plugins
            $options['required_plugins'] =  array(
                'woocommerce',
                'the-events-calendar',
                'js_composer',
                'essential-grid',
                'revslider',
                'wp-booking-calendar',
                'mailchimp-for-wp'
            );

            $options['theme_slug'] = 'invetex';

            // Set number of thumbnails to regenerate when its imported (if demo data was zipped without cropped images)
            // Set 0 to prevent regenerate thumbnails (if demo data archive is already contain cropped images)
            $options['regenerate_thumbnails'] = 3;
            // Default demo
            $options['files']['default']['title'] = esc_html__( 'Invetex Demo', 'invetex' );
            $options['files']['default']['domain_dev'] = esc_url(invetex_get_protocol().'://invetex.themerex.net'); // Developers domain
            $options['files']['default']['domain_demo']= esc_url(invetex_get_protocol().'://invetex.themerex.net'); // Demo-site domain

        }
        return $options;
    }
}


// Add data to the head and to the beginning of the body
//------------------------------------------------------------------------

// Add theme specified classes to the body tag
if ( !function_exists('invetex_body_classes') ) {
	function invetex_body_classes( $classes ) {

		$classes[] = 'invetex_body';
		$classes[] = 'body_style_' . trim(invetex_get_custom_option('body_style'));
		$classes[] = 'body_' . (invetex_get_custom_option('body_filled')=='yes' ? 'filled' : 'transparent');
		$classes[] = 'article_style_' . trim(invetex_get_custom_option('article_style'));
		
		$blog_style = invetex_get_custom_option(is_singular() && !invetex_storage_get('blog_streampage') ? 'single_style' : 'blog_style');
		$classes[] = 'layout_' . trim($blog_style);
		$classes[] = 'template_' . trim(invetex_get_template_name($blog_style));
		
		$body_scheme = invetex_get_custom_option('body_scheme');
		if (empty($body_scheme)  || invetex_is_inherit_option($body_scheme)) $body_scheme = 'original';
		$classes[] = 'scheme_' . $body_scheme;

		$top_panel_position = invetex_get_custom_option('top_panel_position');
		if (!invetex_param_is_off($top_panel_position)) {
			$classes[] = 'top_panel_show';
			$classes[] = 'top_panel_' . trim($top_panel_position);
		} else 
			$classes[] = 'top_panel_hide';
		$classes[] = invetex_get_sidebar_class();

		if (invetex_get_custom_option('show_video_bg')=='yes' && (invetex_get_custom_option('video_bg_youtube_code')!='' || invetex_get_custom_option('video_bg_url')!=''))
			$classes[] = 'video_bg_show';

		if (!invetex_param_is_off(invetex_get_theme_option('page_preloader')))
			$classes[] = 'preloader';

		return $classes;
	}
}


// Add page meta to the head
if (!function_exists('invetex_head_add_page_meta')) {
	function invetex_head_add_page_meta() {
		?>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1<?php if (invetex_get_theme_option('responsive_layouts')=='yes') echo ', maximum-scale=1'; ?>">
		<meta name="format-detection" content="telephone=no">
	
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<?php
	}
}

// Add page preloader styles to the head
if (!function_exists('invetex_head_add_page_preloader_styles')) {
	function invetex_head_add_page_preloader_styles($css) {
		if (($preloader=invetex_get_theme_option('page_preloader'))!='none') {
			$image = invetex_get_theme_option('page_preloader_image');
			$bg_clr = invetex_get_scheme_color('bg_color');
			$link_clr = invetex_get_scheme_color('text_link');
			$css .= '
				#page_preloader {
					background-color: '. esc_attr($bg_clr) . ';'
					. ($preloader=='custom' && $image
						? 'background-image:url('.esc_url($image).');'
						: ''
						)
				    . '
				}
				.preloader_wrap > div {
					background-color: '.esc_attr($link_clr).';
				}';
		}
		return $css;
	}
}

// Add gtm code to the beginning of the body 
if (!function_exists('invetex_body_add_gtm')) {
	function invetex_body_add_gtm() {
        invetex_show_layout(invetex_get_custom_option('gtm_code'));
	}
}

// Add TOC anchors to the beginning of the body
if (!function_exists('invetex_body_add_toc')) {
	function invetex_body_add_toc() {
		// Add TOC items 'Home' and "To top"
		if (invetex_get_custom_option('menu_toc_home')=='yes')
		    if (function_exists('invetex_sc_anchor'))
			invetex_show_layout(invetex_sc_anchor(array(
				'id' => "toc_home",
				'title' => esc_html__('Home', 'invetex'),
				'description' => esc_html__('{{Return to Home}} - ||navigate to home page of the site', 'invetex'),
				'icon' => "icon-home",
				'separator' => "yes",
				'url' => esc_url(home_url('/'))
				)
			));
		if (invetex_get_custom_option('menu_toc_top')=='yes')
            if (function_exists('invetex_sc_anchor'))
			invetex_show_layout(invetex_sc_anchor(array(
				'id' => "toc_top",
				'title' => esc_html__('To Top', 'invetex'),
				'description' => esc_html__('{{Back to top}} - ||scroll to top of the page', 'invetex'),
				'icon' => "icon-double-up",
				'separator' => "yes")
				));
	}
}

// Add page preloader to the beginning of the body
if (!function_exists('invetex_body_add_page_preloader')) {
	function invetex_body_add_page_preloader() {
		if ( ($preloader=invetex_get_theme_option('page_preloader')) != 'none' && ( $preloader != 'custom' || ($image=invetex_get_theme_option('page_preloader_image')) != '')) {
			?><div id="page_preloader"><?php
				if ($preloader == 'circle') {
					?><div class="preloader_wrap preloader_<?php echo esc_attr($preloader); ?>"><div class="preloader_circ1"></div><div class="preloader_circ2"></div><div class="preloader_circ3"></div><div class="preloader_circ4"></div></div><?php
				} else if ($preloader == 'square') {
					?><div class="preloader_wrap preloader_<?php echo esc_attr($preloader); ?>"><div class="preloader_square1"></div><div class="preloader_square2"></div></div><?php
				}
			?></div><?php
		}
	}
}


// Add data to the footer
//------------------------------------------------------------------------

// Add post/page views counter
if (!function_exists('invetex_footer_add_views_counter')) {
	function invetex_footer_add_views_counter() {
		// Post/Page views counter
		get_template_part(invetex_get_file_slug('templates/_parts/views-counter.php'));
	}
}

// Add Login/Register popups
if (!function_exists('invetex_footer_add_login_register')) {
	function invetex_footer_add_login_register() {
		if (invetex_get_theme_option('show_login')=='yes') {
			invetex_enqueue_popup();
			// Anyone can register ?
			if ( (int) get_option('users_can_register') > 0) {
				get_template_part(invetex_get_file_slug('templates/_parts/popup-register.php'));
			}
			get_template_part(invetex_get_file_slug('templates/_parts/popup-login.php'));
			?><div class="popup_wrap_bg"></div><?php
		}
	}
}

// Add theme customizer
if (!function_exists('invetex_footer_add_theme_customizer')) {
	function invetex_footer_add_theme_customizer() {
		// Front customizer
		if (invetex_get_custom_option('show_theme_customizer')=='yes') {
			get_template_part(invetex_get_file_slug('core/core.customizer/front.customizer.php'));
		}
	}
}

// Add scroll to top button
if (!function_exists('invetex_footer_add_scroll_to_top')) {
	function invetex_footer_add_scroll_to_top() {
		?><a href="#" class="scroll_to_top icon-up" title="<?php esc_attr_e('Scroll to top', 'invetex'); ?>"></a><?php
	}
}

// Add custom html
if (!function_exists('invetex_footer_add_custom_html')) {
	function invetex_footer_add_custom_html() {
		?><div class="custom_html_section"><?php
        invetex_show_layout(invetex_get_custom_option('custom_code'));
		?></div><?php
	}
}

// Add gtm code
if (!function_exists('invetex_footer_add_gtm2')) {
	function invetex_footer_add_gtm2() {
        invetex_show_layout(invetex_get_custom_option('gtm_code2'));
	}
}

// Add theme required plugins
if ( !function_exists( 'invetex_add_trx_utils' ) ) {
    add_filter( 'trx_utils_active', 'invetex_add_trx_utils' );
    function invetex_add_trx_utils($enable=true) {
        return true;
    }
}

// Return text for the Privacy Policy checkbox
if ( ! function_exists('invetex_get_privacy_text' ) ) {
    function invetex_get_privacy_text() {
        $page = get_option( 'wp_page_for_privacy_policy' );
        $privacy_text = invetex_get_theme_option( 'privacy_text' );
        return apply_filters( 'invetex_filter_privacy_text', wp_kses_post(
                $privacy_text
                . ( ! empty( $page ) && ! empty( $privacy_text )
                    // Translators: Add url to the Privacy Policy page
                    ? ' ' . sprintf( __( 'For further details on handling user data, see our %s', 'invetex' ),
                        '<a href="' . esc_url( get_permalink( $page ) ) . '" target="_blank">'
                        . __( 'Privacy Policy', 'invetex' )
                        . '</a>' )
                    : ''
                )
            )
        );
    }
}

// Return text for the "I agree ..." checkbox
if ( ! function_exists( 'invetex_trx_utils_privacy_text' ) ) {
    add_filter( 'trx_utils_filter_privacy_text', 'invetex_trx_utils_privacy_text' );
    function invetex_trx_utils_privacy_text( $text='' ) {
        return invetex_get_privacy_text();
    }
}



// Include framework core files
//-------------------------------------------------------------------
require_once trailingslashit( get_template_directory() ) . 'fw/loader.php';
?>