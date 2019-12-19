<?php
/**
 * Plugin Name: DSS Hogan Module: Feed
 * Plugin URI: https://github.com/dss-web/dss-hogan-feed
 * GitHub Plugin URI: https://github.com/dss-web/dss-hogan-feed
 * Description: DSS Feed Module for Hogan.
 * Version: 1.0.0
 * Author: Per Soderlind
 * Author URI: https://soderlind.no
 * License: GPL-3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Text Domain: dss-hogan-feed
 * Domain Path: /languages/
 *
 * @package Hogan
 * @author Dekode
 */

declare( strict_types = 1 );

namespace DSS\Hogan\Feed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\hogan_load_textdomain' );
add_action( 'hogan/include_modules', __NAMESPACE__ . '\hogan_register_module' );
add_filter( 'hogan/module/feed/heading/enabled', '__return_true' );
add_filter( 'acf/update_value/key=hogan_module_expandable_list_item_id', __NAMESPACE__ . '\sanitize_item_id_on_save', 10, 3 );

/**
 * Register module text domain
 */
function hogan_load_textdomain() {
	\load_plugin_textdomain( 'dss-hogan-feed', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Register module in Hogan
 *
 * @param \Dekode\Hogan\Core $core Hogan Core instance.
 * @return void
 */
function hogan_register_module( \Dekode\Hogan\Core $core ) {
	require_once 'class-feed.php';
	$core->register_module( new \Dekode\Hogan\Feed() );
}

/**
 * Sanitize item id name to URL friendly string.
 *
 * @param string  $value Item name.
 * @param integer $id Item id.
 * @param array   $field Sanitized item name.
 * @return string
 */
function sanitize_item_id_on_save( string $value, int $id, array $field ) : string {
	return sanitize_title( $value );
}

/**
 * Add class name to outer wrapper.
 *
 * @param array                $classes Array with class names.
 * @param \Dekode\Hogan\Module $module Hogan module object.
 * @return array
 */
function on_hogan_outer_wrapper_classes( array $classes, \Dekode\Hogan\Module $module ) : array {
	if ( 'feed' === $module->name ) {
		$classes[] = 'hogan-module-simple_posts';
	}

	return $classes;
}
