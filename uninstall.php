<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Super_Simple_Events
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-2.0+
 * @link      http://www.jonathandavidharris.co.uk/
 * @copyright 2014 Spacedmonkey
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

flush_rewrite_rules();
