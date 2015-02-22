<?php
/**
 * Super Simple Events
 *
 *
 * @package   Super_Simple_Events
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-2.0+
 * @link      http://www.jonathandavidharris.co.uk/
 * @copyright 2014 Spacedmonkey
 *
 * @wordpress-plugin
 * Plugin Name:       Super Simple Events
 * Plugin URI:        http://www.jonathandavidharris.co.uk/scripts/super-simple-events/
 * Description:       Super Simple Events
 * Version:           1.0.4
 * Author:            Jonathan Harris
 * Author URI:        http://www.jonathandavidharris.co.uk/
 * Text Domain:       super-simple-events
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI:  https://github.com/spacedmonkey/super-simple-events
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('SSE_FILE',__FILE__);

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-super-simple-events.php' );

register_activation_hook( __FILE__, array( 'Super_Simple_Events', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Super_Simple_Events', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Super_Simple_Events', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Widget Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'widget/class-super-simple-events-widget.php' );

add_action( 'widgets_init', create_function( '', 'register_widget("Super_Simple_Events_Widget");' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-super-simple-events-admin.php' );
	add_action( 'plugins_loaded', array( 'Super_Simple_Events_Admin', 'get_instance' ) );

}
