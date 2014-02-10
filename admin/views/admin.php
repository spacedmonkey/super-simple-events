<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Super_Simple_Events
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Spacedmonkey
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form action="options.php" method="POST">
            <?php 
            	
            	settings_fields( $this->plugin->option_group );   
                do_settings_sections( $this->plugin->get_plugin_slug() );
				submit_button(); 
			?>
        </form>
</div>
