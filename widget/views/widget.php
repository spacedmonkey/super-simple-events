<!-- This file is used to markup the public-facing widget. -->


<?php if($events->have_posts()):?>

	<?php while ( $events->have_posts() ) : $events->the_post(); ?>
		<div class="sse-widget-entry-title">
			<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark"><?php the_title(); ?></a>
		</div>
	<?php endwhile; ?>


<?php else : ?>

	<div class="sse-widget-entry-title">
		<?php _e('No upcoming events', $this->plugin->get_plugin_slug());?>
	</div>

<?php endif; ?>


<?php if (isset ( $instance['display_link'] ) && $instance['display_link'] == true ) : ?>

	<?php
		$class = '';
		
		if ( isset ( $instance['link_classes'] ) ) {
			$class = 'class="' . $instance['link_classes'] . '"';
		}
	?>

	<a href="<?php echo esc_url ( home_url( $this->plugin->options['post_type_slug'] . '/' ) ); ?>" <?php echo $class ?>>All events</a>
<?php endif; ?>
