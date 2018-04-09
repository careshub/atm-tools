<?php
/**
 * @package ATM_Tools_CPT
 * @wordpress-plugin
 * Plugin Name:       All Things Missouri Tools CPT
 * Version:           1.0.0
 * Description:       Adds a "tools" CPT to store info about internal or external .
 * Author:            dcavins
 * Text Domain:       cares-atm-tools
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/careshub/atm-tools-cpt
 * @copyright 2018 CARES, University of Missouri
 */

namespace ATM_Tools;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

$basepath = plugin_dir_path( __FILE__ );

// The main goer.
require_once( $basepath . 'public/public.php' );

// The Custom Post Type definition and extras.
require_once( $basepath . 'includes/class-cpt-atm-tools.php' );
$tools_cpt = new CPT_Tax\Tools_CPT();
$tools_cpt->add_hooks();

/**
 * Helper function.
 * @return Fully-qualified URI to the root of the plugin.
 */
function get_plugin_base_uri() {
	return plugin_dir_url( __FILE__ );
}

/**
 * Helper function.
 * @return Fully-qualified URI to the root of the plugin.
 */
function get_plugin_base_path() {
	return trailingslashit( dirname( __FILE__ ) );
}

/**
 * Helper function.
 * @return string Slug for the plugin.
 */
function get_plugin_slug() {
	return 'atm-tools-cpt';
}

/**
 * Helper function.
 * @TODO: Update this when you update the plugin's version above.
 *
 * @return string Current version of plugin.
 */
function get_plugin_version() {
	return '1.0.0';
}

/**
 * Helper function.
 * @return string Slug for the new custom post type.
 */
function get_cpt_name() {
	return 'atm_tool';
}
