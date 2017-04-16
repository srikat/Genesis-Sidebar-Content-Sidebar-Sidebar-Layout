<?php
/**
 * Plugin Name: Genesis Sidebar Content Sidebar Sidebar Layout
 * Plugin URI: https://github.com/srikat/Genesis-Sidebar-Content-Sidebar-Sidebar-Layout
 * Description: Makes available an additional Primary Sidebar | Content | Secondary Sidebar | Tertiary Sidebar Layout in Genesis.
 * Version: 1.0.0
 * Author: Sridhar Katakam
 * Author URI: https://sridharkatakam.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: srikat/Genesis-Sidebar-Content-Sidebar-Sidebar-Layout
 * Text Domain: genesis-scss-layout
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

// Prevent direct access to the plugin.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Sorry, you are not allowed to access this page directly.' );
}

register_activation_hook( __FILE__, 'gshg_activation_check' );
/**
 * Check if Genesis is the parent theme.
 */
function gshg_activation_check() {
	$theme_info = wp_get_theme();
	$genesis_flavors = array(
		'genesis',
		'genesis-trunk',
	);
	if ( ! in_array( $theme_info->Template, $genesis_flavors, true ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself.
		$message = sprintf(
			/* translators: %s: URL to Genesis Framework. */
			__( 'Sorry, you can\'t activate this plugin unless you have installed <a href="%s">Genesis</a>.', 'genesis-simple-hook-guide' ),
			esc_url( 'https://my.studiopress.com/themes/genesis/' )
		);
		wp_die( $message );
	}
}

add_action( 'init', 'gscss_sidebar_content_sidebar_sidebar_layout' );
/**
 * Create Sidebar Content Sidebar Sidebar Layout
 *
 * @link http://www.billerickson.net/wordpress-genesis-custom-layout/
 */
function gscss_sidebar_content_sidebar_sidebar_layout() {
	genesis_register_layout( 'sidebar-content-sidebar-sidebar', array(
		'label' => __( 'Primary Sidebar, Content, Secondary Sidebar, Tertiary Sidebar', 'genesis' ),
		'img'   => plugins_url( 'assets/images/scss.gif', __FILE__ ),
	) );
}

add_action( 'wp_enqueue_scripts', 'gscss_hooks_script_and_styles' );
/**
 * Load stylesheet.
 */
function gscss_hooks_script_and_styles() {
	$site_layout = genesis_site_layout();

	if ( 'sidebar-content-sidebar-sidebar' !== $site_layout ) {
		return;
	}

	$gscss_plugin_css_url = plugins_url( 'assets/css/style.css', __FILE__ );

	wp_enqueue_style( 'gscss-styles', $gscss_plugin_css_url );
}

add_action( 'widgets_init', 'gscss_register_tertiary_sidebar' );
/**
 * Register Tertiary Sidebar widget area.
 */
function gscss_register_tertiary_sidebar() {
	genesis_register_widget_area(
		array(
			'id'               => 'sidebar-alt2',
			'name'             => __( 'Tertiary Sidebar', 'genesis' ),
			'description'      => __( 'This is the tertiary sidebar if you are using a four column site layout option.', 'genesis-scss-layout' ),
		)
	);
}

add_filter( 'genesis_attr_sidebar-tertiary', 'gscss_attributes_sidebar_tertiary' );
/**
 * Add attributes for tertiary sidebar element.
 *
 * @param array $attributes Existing attributes for tertiary sidebar element.
 * @return array Amended attributes for tertiary sidebar element.
 */
function gscss_attributes_sidebar_tertiary( $attributes ) {
	$attributes['class']     = 'sidebar sidebar-tertiary widget-area';
	$attributes['role']      = 'complementary';
	$attributes['aria-label']  = __( 'Tertiary Sidebar', 'genesis' );
	$attributes['itemscope'] = true;
	$attributes['itemtype']  = 'http://schema.org/WPSideBar';

	return $attributes;
}

add_action( 'genesis_sidebar_alt2', 'gscss_do_sidebar_alt2' );
/**
 * Echo second alternate sidebar default content.
 *
 * Only shows if sidebar is empty, and current user has the ability to edit theme options (manage widgets).
 *
 * @since 1.2.0
 */
function gscss_do_sidebar_alt2() {
	if ( ! dynamic_sidebar( 'sidebar-alt2' ) && current_user_can( 'edit_theme_options' ) ) {
		genesis_default_widget_area_content( __( 'Tertiary Sidebar Widget Area', 'genesis' ) );
	}
}

add_action( 'genesis_after_loop', 'gscss_remove_primary_sidebar' );
/**
 * Remove Primary Sidebar from being output after content.
 */
function gscss_remove_primary_sidebar() {
	$site_layout = genesis_site_layout();

	if ( 'sidebar-content-sidebar-sidebar' !== $site_layout ) {
		return;
	}

	remove_action( 'genesis_after_content', 'genesis_get_sidebar' );
}

add_action( 'genesis_before_content', 'gscss_display_primary_sidebar' );
/**
 * Output Primary Sidebar before content.
 */
function gscss_display_primary_sidebar() {
	$site_layout = genesis_site_layout();

	if ( 'sidebar-content-sidebar-sidebar' !== $site_layout ) {
		return;
	}

	get_sidebar();
}

add_action( 'genesis_after_content', 'gscss_display_sidebars' );
/**
 * Output Secondary and Tertiary Sidebars after content.
 *
 * @since 0.2.0
 */
function gscss_display_sidebars() {
	$site_layout = genesis_site_layout();

	if ( 'sidebar-content-sidebar-sidebar' !== $site_layout ) {
		return;
	}

	get_sidebar( 'alt' );

	// Output tertiary sidebar structure.
	genesis_markup( array(
		'open'    => '<aside %s>' . genesis_sidebar_title( 'sidebar-alt2' ),
		'context' => 'sidebar-tertiary',
	) );

	do_action( 'genesis_sidebar_alt2' );

	// End .sidebar-tertiary.
	genesis_markup( array(
		'close'   => '</aside>',
		'context' => 'sidebar-tertiary',
	) );
}
