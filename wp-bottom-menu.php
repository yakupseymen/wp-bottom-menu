<?php
/**
 * Plugin Name: WP Bottom Menu
 * Description: WP Bottom Menu allows you to add a woocommerce supported bottom menu to your site.
 * Version: 1.2
 * Author: J4
 * Author URI: https://j4cob.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-bottom-menu
 * Domain Path: /languages
 * 
 * WP Bottom Menu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * WP Bottom Menu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'WP_BOTTOM_MENU_VERSION', '1.2' );
define( 'WP_BOTTOM_MENU_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_BOTTOM_MENU_DIR_PATH', plugin_dir_path( __FILE__ ) );


if ( class_exists( 'WPBottomMenu' ) ){
    $wpbottommenu = new WPBottomMenu;
}

class WPBottomMenu{

    public function __construct() {
        $this->includes();
        $this->_hooks();
    }

    function _hooks(){
        add_action( 'wp_enqueue_scripts', array( $this, 'add_theme_scripts' ) );
        add_action( 'wp_footer', array($this, 'wp_bottom_menu' ) );
        add_action( 'customize_register', array($this, 'wp_bottom_menu_customize_register' ) );
        add_action( 'wp_footer', array($this, 'wpbottommenu_customize_css') );
        add_filter( 'plugin_action_links', array($this, 'wp_bottom_menu_action_links'), 10, 2 );
        if (class_exists( 'WooCommerce' )){
            add_filter( 'woocommerce_add_to_cart_fragments', array($this, 'wp_bottom_menu_add_to_cart_fragment'), 10, 1 );
        }
    } 
    
    function includes(){
        require_once(WP_BOTTOM_MENU_DIR_PATH.'inc/customizer-repeater/functions.php');
    }

    // enqueue scripts
    function add_theme_scripts() {
        wp_enqueue_style( 'wp-bottom-menu-style', WP_BOTTOM_MENU_DIR_URL . 'inc/style.css', array(), WP_BOTTOM_MENU_VERSION, 'all');
        wp_enqueue_script( 'wp-bottom-menu-js', WP_BOTTOM_MENU_DIR_URL . 'inc/main.js', array(), WP_BOTTOM_MENU_VERSION, true);
        if(get_option( 'wpbottommenu_iconset', 'fontawesome' ) == 'fontawesome'){
            wp_enqueue_style( 'font-awesome', WP_BOTTOM_MENU_DIR_URL . 'inc/customizer-repeater/css/font-awesome.min.css', array(), CUSTOMIZER_REPEATER_VERSION );
        }
    }

    // wp bottom menu
    function wp_bottom_menu() {
        ?>
        <div class="wp-bottom-menu" id="wp-bottom-menu">

        <?php
        $customizer_repeater_wpbm = get_option('customizer_repeater_wpbm', json_encode( array(
            array("choice" => "wpbm-homepage" ,"subtitle" => "fa-home", "title" => "Home", "id" => "customizer_repeater_1" ),
            array("choice" => "wpbm-woo-account" ,"subtitle" => "fa-user", "title" => "Account", "id" => "customizer_repeater_2" ),
            array("choice" => "wpbm-woo-cart" ,"subtitle" => "fa-shopping-cart", "title" => "Cart", "id" => "customizer_repeater_3" ),
            array("choice" => "wpbm-woo-search" ,"subtitle" => "fa-search", "title" => "Search", "id" => "customizer_repeater_4" ),
        ) ) );
        /*This returns a json so we have to decode it*/

        $customizer_repeater_wpbm_decoded = json_decode($customizer_repeater_wpbm);
        $wpbmsf;
        foreach($customizer_repeater_wpbm_decoded as $repeater_item){

            if($repeater_item->choice == "wpbm-woo-search" or $repeater_item->choice == "wpbm-post-search"):?>
                <a href="javascript:void(0);" title="<?php echo $repeater_item->title; ?>" class="wp-bottom-menu-item wp-bottom-menu-search-form-trigger">
            <?php else: ?>
                <a href="<?php 
                    switch($repeater_item->choice){
                        case "wpbm-homepage":
                            echo esc_url( home_url() );
                        break;
                        case "wpbm-woo-cart":
                            if ( class_exists( 'WooCommerce' ) ) {
								echo esc_url( wc_get_page_permalink( 'cart' ) );
							} else {
								echo '#';
							}   
                        break;
                        case "wpbm-woo-account":
                            if ( class_exists( 'WooCommerce' ) ) {
								echo esc_url( wc_get_page_permalink( 'myaccount' ) );
							} else {
								echo '#';
							}  
                        break;
        
                        default:
                            echo esc_url( $repeater_item->link );
                    }
                ?>" class="wp-bottom-menu-item">
            <?php endif; ?>
                    
                    <div class="wp-bottom-menu-icon-wrapper">
                        <?php if(get_option( 'wpbottommenu_show_cart_count', false )): ?>
                            <?php if ( class_exists( 'WooCommerce' ) and $repeater_item->choice == "wpbm-woo-cart") : ?>
                                <div class="wp-bottom-menu-cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if(get_option( 'wpbottommenu_iconset', 'fontawesome' ) == 'fontawesome'): ?>
                            <i class="wp-bottom-menu-item-icons fa <?php echo $repeater_item->subtitle; ?>"></i>
                        <?php else: ?>
                        <?php echo html_entity_decode($repeater_item->subtitle); ?>
                        <?php endif; ?>
                    </div>
                    <?php if(!get_option( 'wpbottommenu_disable_title', false )): ?>
                        <?php if(get_option( 'wpbottommenu_show_cart_total', false ) and $repeater_item->choice == "wpbm-woo-cart" and class_exists( 'WooCommerce' )): ?>
                                <span class="wp-bottom-menu-cart-total"><?php WC()->cart->get_cart_total(); ?></span>
                            <?php else: ?>
                                <span><?php echo $repeater_item->title; ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                </a>
            <?php
            $wpbmsf = $repeater_item->choice;           

        }
        ?>
    </div>

    <div class="wp-bottom-menu-search-form-wrapper" id="wp-bottom-menu-search-form-wrapper">
    <form role="search" method="get" action="<?php echo esc_url( home_url( '/'  ) ); ?>" class="wp-bottom-menu-search-form">
        <i class="fa fa-search"></i>
	    <input type="hidden" name="post_type" value="<?php if($wpbmsf=="wpbm-woo-search" and class_exists( 'WooCommerce' )) echo esc_attr("product"); else echo esc_attr("post"); ?>" />
        <input type="search" class="search-field" placeholder="<?php if(get_option( 'wpbottommenu_placeholder_text', 'Search' )) echo get_option( 'wpbottommenu_placeholder_text', 'Search' ); else echo esc_attr_x( 'Search', 'wp-bottom-menu' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
    </form>
    </div><?php       
        
    } 
    // customizer api
    function wp_bottom_menu_customize_register( $wp_customize ) {
                
        // Add Theme Options Panel.
        $wp_customize->add_panel( 'wpbottommenu_panel',
            array(
                'title'      => esc_html__( 'WP Bottom Menu', 'wp-bottom-menu' ),
                'priority'   => 20
            )
        );
    
        //
        // Section: Settings
        //

        $wp_customize->add_section( 'wpbottommenu_section_settings', array(
            'title'      => esc_html__( 'Settings', 'wp-bottom-menu' ),
            'priority'   => 120,
            'panel'      => 'wpbottommenu_panel', 
        ));

                $wp_customize->add_setting( 'wpbottommenu_iconset', array(
                    'default' => 'fontawesome',
                    'type' => 'option',
                ) );
                
                $wp_customize->add_control( 'wpbottommenu_iconset', array(
                    'type' => 'select',
                    'section' => 'wpbottommenu_section_settings', 
                    'label' => __( 'Select Icon Type', 'wp-bottom-menu' ),
                    'description' => __( '<u>Custom SVG:</u> Paste SVG Icon code.<br><u>FontAwesome:</u> Enable FontAwesome Library.', 'wp-bottom-menu' ),
                    'choices' => array(
                    'svg' => __( 'Custom SVG' ),
                    'fontawesome' => __( 'FontAwesome (v4.7)' ),
                    ),
                ) );

                $wp_customize->add_setting( 'wpbottommenu_display_px' , array(
                    'default'     => '1024',
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control('wpbottommenu_display_px', array(
                    'label'    => __( 'Active The Menu (px)', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_settings',
                    'settings' => 'wpbottommenu_display_px',
                    'type' => 'number',
                ));
                
                $wp_customize->add_setting( 'wpbottommenu_display_always' , array(
                    'default'     => false,
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control('wpbottommenu_display_always', array(
                    'label'    => __( 'Active for any screen size?', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_settings',
                    'settings' => 'wpbottommenu_display_always',
                    'type' => 'checkbox',
                ));
                
                $wp_customize->add_setting( 'wpbottommenu_zindex' , array(
                    'default'     => '9999',
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control('wpbottommenu_zindex', array(
                    'label'    => __( 'Menu Z-Index', 'wp-bottom-menu' ), 
                    'description' => esc_html__( 'Recommended value: 9999', 'wp-bottom-menu' ),
                    'section'  => 'wpbottommenu_section_settings',
                    'settings' => 'wpbottommenu_zindex',
                    'type' => 'number',
                ));
                
        //
        // Section: Customize
        //

        $wp_customize->add_section('wpbottommenu_section_customize', array(
            'title' => __('Customize', 'wp-bottom-menu'),
            'priority' => 130,
            'panel'      => 'wpbottommenu_panel' 
        ));

                $wp_customize->add_setting( 'wpbottommenu_placeholder_text' , array(
                    'default'     => 'Search',
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control( 'wpbottommenu_placeholder_text', array(
                    'label'    => __( 'Search Input Placeholder Text', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_customize',
                    'settings' => 'wpbottommenu_placeholder_text',
                    'type' => 'text',
                ));

                $wp_customize->add_setting( 'wpbottommenu_fontsize' , array(
                    'default'     => '12',
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control( 'wpbottommenu_fontsize', array(
                    'label'    => __( 'Menu Font Size (px)', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_customize',
                    'settings' => 'wpbottommenu_fontsize',
                    'type' => 'number',
                ));

                $wp_customize->add_setting( 'wpbottommenu_iconsize' , array(
                    'default'     => '24',
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control( 'wpbottommenu_iconsize', array(
                    'label'    => __( 'Menu Icon Size (px)', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_customize',
                    'settings' => 'wpbottommenu_iconsize',
                    'type' => 'number',
                ));

                $wp_customize->add_setting( 'wpbottommenu_iconcolor', array(
                    'default' => '#000000',
                    'section'  => 'wpbottommenu_section_customize',
                    'sanitize_callback' => 'sanitize_hex_color',
                    'type' => 'option'
                ));

                $wp_customize->add_setting( 'wpbottommenu_textcolor', array(
                    'default' => '#000000',
                    'section'  => 'wpbottommenu_section_customize',
                    'sanitize_callback' => 'sanitize_hex_color',
                    'type' => 'option'
                ));

                $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wpbottommenu_textcolor', array(
                    'label'    => __( 'Menu Text Color', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_customize',
                )));   

                 $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wpbottommenu_iconcolor', array(
                    'label'    => __( 'Menu Icon Color', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_customize',
                )));

                $wp_customize->add_setting( 'wpbottommenu_bgcolor', array(
                    'default' => '#ffffff',
                    'section'  => 'wpbottommenu_section_customize',
                    'sanitize_callback' => 'sanitize_hex_color',
                    'type' => 'option'
                ));

                $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wpbottommenu_bgcolor', array(
                    'label'    => __( 'Menu Background Color', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_customize',
                )));

                $wp_customize->add_setting( 'wpbottommenu_disable_title' , array(
                    'default'     => false,
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control('wpbottommenu_disable_title', array(
                    'label'    => __( 'Disable title?', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_customize',
                    'settings' => 'wpbottommenu_disable_title',
                    'type' => 'checkbox',
                ));

                $wp_customize->add_setting( 'wpbottommenu_cart_separator' , array(
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control('wpbottommenu_cart_separator', array(
                    'label'    => __( 'Customize Cart Item', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_customize',
                    'settings' => 'wpbottommenu_cart_separator',
                    'type' => 'hidden',
                ));

                $wp_customize->add_setting( 'wpbottommenu_show_cart_count' , array(
                    'default'     => false,
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control('wpbottommenu_show_cart_count', array(
                    'label'    => __( 'Show Cart Count', 'wp-bottom-menu' ), 
                    'section'  => 'wpbottommenu_section_customize',
                    'settings' => 'wpbottommenu_show_cart_count',
                    'type' => 'checkbox',
                ));
                
                $wp_customize->add_setting( 'wpbottommenu_cart_count_bgcolor', array(
                    'default' => '#ff0000',
                    'section'  => 'wpbottommenu_section_customize',
                    'sanitize_callback' => 'sanitize_hex_color',
                    'type' => 'option'
                ));

                $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wpbottommenu_cart_count_bgcolor', array( 
                    'description' => __('Cart Count Background Color', 'wp-bottom-menu'),
                    'section'  => 'wpbottommenu_section_customize',
                )));

                $wp_customize->add_setting( 'wpbottommenu_show_cart_total' , array(
                    'default'     => false,
                    'type'        => 'option',
                ));
                
                $wp_customize->add_control('wpbottommenu_show_cart_total', array(
                    'label'    => __( 'Show Cart Total', 'wp-bottom-menu' ), 
                    'description' => 'This option override cart menu title.',
                    'section'  => 'wpbottommenu_section_customize',
                    'settings' => 'wpbottommenu_show_cart_total',
                    'type' => 'checkbox',
                ));

        //
        // Section: Menu Items
        //
        
        $wp_customize->add_section('wpbottommenu_section_menuitems', array(
            'title' => __('Menu Items', 'wp-bottom-menu'),
            'priority' => 140,
            'panel'      => 'wpbottommenu_panel' 
        ));

                $wp_customize->add_setting( 'customizer_repeater_wpbm', array(
                    'sanitize_callback' => 'customizer_repeater_sanitize',
                    'type' => 'option',
                    'default' => json_encode( array(
                       array("choice" => "wpbm-homepage" ,"subtitle" => "fa-home", "title" => "Home", "id" => "customizer_repeater_1" ),
                       array("choice" => "wpbm-woo-account" ,"subtitle" => "fa-user", "title" => "Account", "id" => "customizer_repeater_2" ),
                       array("choice" => "wpbm-woo-cart" ,"subtitle" => "fa-shopping-cart", "title" => "Cart", "id" => "customizer_repeater_3" ),
                       array("choice" => "wpbm-woo-search" ,"subtitle" => "fa-search", "title" => "Search", "id" => "customizer_repeater_4" ),
                       ))
                ));

                $wp_customize->add_control( new Customizer_Repeater( $wp_customize, 'customizer_repeater_wpbm', array(
                    'label'   => esc_html__('Menu Item','customizer-repeater'),
                    'section' => 'wpbottommenu_section_menuitems',
                    'customizer_repeater_title_control' => true,
                    'customizer_repeater_link_control' => true,
                    'customizer_repeater_subtitle_control' => true,
                )));

                $wp_customize->add_setting( 'wpbottommenu_howuseicon' , array(
                    'type'        => 'option',
                ));

                $wp_customize->add_control( 'wpbottommenu_howuseicon', array(
                    'label'    => __( 'How to use Icons?', 'wp-bottom-menu' ), 
                    'description' => sprintf(
                        __( '<u>For FontAwesome:</u> Add the names from (%1$s) to the "Icon" field.<br>Example:<code>fa-home</code><hr><u>For SVG Icons:</u> simply paste your SVG code in the "Icon" field. SVG Icon Library: %2$s<br>Enable to use SVG <code>Settings > Select Icon Type > Custom SVG</code> ', 'wp-bottom-menu' ),
                        sprintf( '<a target="_blank" href="https://fontawesome.com/v4.7.0/icons/" rel="nofollow">%s</a>', esc_html__( 'FontAwesome', 'wp-bottom-menu' ) ),
                        sprintf( '<a target="_blank" href="https://remixicon.com" rel="nofollow">%s</a>', esc_html__( 'Remix Icon', 'wp-bottom-menu' ) )
                    ),
                    'section'  => 'wpbottommenu_section_menuitems',
                    'settings' => 'wpbottommenu_howuseicon',
                    'type' => 'hidden',
                ));
                

    }

    // woocommerce cart fragment
    function wp_bottom_menu_add_to_cart_fragment( $fragments ) {
        $fragments['div.wp-bottom-menu-cart-count'] = '<div class="wp-bottom-menu-cart-count">' . WC()->cart->get_cart_contents_count() . '</div>'; 
        $fragments['span.wp-bottom-menu-cart-total'] = '<span class="wp-bottom-menu-cart-total">' . WC()->cart->get_cart_total() . '</span>';
        return $fragments;
    }

    // plugin action links
    function wp_bottom_menu_action_links( $links_array, $plugin_file_name ){
        if( strpos( $plugin_file_name, basename(__FILE__) ) ) {
            array_unshift( $links_array, '<a href="' . admin_url( 'customize.php?autofocus[panel]=wpbottommenu_panel' ) . '">Settings</a>' );
        }
        return $links_array;
    }

    // customize css
    function wpbottommenu_customize_css(){
        ?>
        <style type="text/css">
            <?php if (!get_option( 'wpbottommenu_display_always', false )): ?>
                @media (max-width: <?php echo get_option( 'wpbottommenu_display_px', '1024' ); ?>px){
                    .wp-bottom-menu{
                        display:flex;
                    }
                    .wp-bottom-menu-search-form-wrapper{
                        display: block;
                    }
                }
            <?php else: ?>
                    .wp-bottom-menu{
                        display:flex;
                    }
                    .wp-bottom-menu-search-form-wrapper{
                        display: block;
                    }
            <?php endif; ?>

            :root{
                --wpbottommenu-font-size: <?php echo get_option( 'wpbottommenu_fontsize', '12' );?>px;
                --wpbottommenu-icon-size: <?php echo get_option( 'wpbottommenu_iconsize', '24' );?>px;
                --wpbottommenu-text-color: <?php echo get_option( 'wpbottommenu_textcolor', '#000000' );?>;
                --wpbottommenu-icon-color: <?php echo get_option( 'wpbottommenu_iconcolor', '#000000' );?>;
                --wpbottommenu-bgcolor: <?php echo get_option( 'wpbottommenu_bgcolor', '#ffffff' );?>;
                --wpbottommenu-zindex: <?php echo get_option( 'wpbottommenu_zindex', '9999' ); ?>;
                --wpbottommenu-cart-count-bgcolor: <?php echo get_option( 'wpbottommenu_cart_count_bgcolor', '#ff0000' );?>;
            }

        </style>
        <?php
    }
    
} // class


function wp_bottom_menu_pluginprefix_activate() { 
    flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'wp_bottom_menu_pluginprefix_activate' );

function wp_bottom_menu_pluginprefix_deactivate() {
	/* If you want all settings to be deleted when the plugin is deactive, activate this field. 

    delete_option(' customizer_repeater_wpbm' );
    delete_option( 'wpbottommenu_display_px' );
    delete_option( 'wpbottommenu_display_always' );
    delete_option( 'wpbottommenu_fontsize' );
    delete_option( 'wpbottommenu_iconsize' );
    delete_option( 'wpbottommenu_textcolor' );
    delete_option( 'wpbottommenu_iconcolor' );
    delete_option( 'wpbottommenu_bgcolor' );
    delete_option( 'wpbottommenu_zindex' );
    delete_option( 'wpbottommenu_disable_title' );
    delete_option( 'wpbottommenu_iconset' );
    delete_option( 'wpbottommenu_placeholder_text' );
    delete_option( 'wpbottommenu_show_cart_count' );
    delete_option( 'wpbottommenu_show_cart_total' );
    delete_option( 'wpbottommenu_cart_count_bgcolor' );
	*/
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wp_bottom_menu_pluginprefix_deactivate' );