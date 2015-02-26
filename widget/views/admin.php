<?php
		$title = isset( $instance['title']) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$display_link = isset( $instance['display_link'] ) ? filter_var($instance['display_link'], FILTER_VALIDATE_BOOLEAN) : 0;
		$link_classes = isset( $instance['link_classes']) ? esc_attr( $instance['link_classes'] ) : '';
?>

	<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', $this->plugin->get_plugin_slug() ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

	<p><label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php _e( 'Number of events to show:', $this->plugin->get_plugin_slug() ); ?></label>
	<input id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3" /></p>
	
	<p><input id="<?php echo esc_attr( $this->get_field_id( 'display_link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_link' ) ); ?>" type="checkbox" value="true" <?php checked( true, $display_link ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'display_link' ) ); ?>"><?php _e( 'Display link to event page', $this->plugin->get_plugin_slug() ); ?></label></p>

	<p><label for="<?php echo esc_attr( $this->get_field_id( 'link_classes' ) ); ?>"><?php _e( 'Optional class(es) for event page link:', $this->plugin->get_plugin_slug() ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link_classes' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link_classes' ) ); ?>" type="text" value="<?php echo esc_attr( $link_classes ); ?>" /></p>

<?php
	
?>