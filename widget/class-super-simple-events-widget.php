<?php
/**
 * Super Simple Events
 *
 * @package   Super_Simple_Events_Admin
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-2.0+
 * @link      http://www.jonathandavidharris.co.uk/
 * @copyright 2014 Spacedmonkey
 */

/**
 * Widget class. 
 *
 *
 * @package  Super_Simple_Events_Widget
 * @abstract WP_Widget
 * @author   Jonathan Harris <jon@spacedmonkey.co.uk>
 */
class Super_Simple_Events_Widget extends WP_Widget {
	
	/**
	 * Create a string that is a unique string for this widget
	 * 
	 * @var 	  String
	 * @since     1.0.0
	 */

	protected $widget_id = null;
	
	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	
	protected $plugin = null;

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {
		
		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$this->plugin = Super_Simple_Events::get_instance();
		$this->widget_id = $this->plugin->get_plugin_slug().'-id';

		parent::__construct(
			$this->widget_id, 
			sprintf(__( 'Upcoming Events (%s)', $this->plugin->get_plugin_slug() ), $this->plugin->get_plugin_name()),
			array(
				'classname'  => $this->plugin->get_plugin_slug().'-class',
				'description' => sprintf(__( 'Display a list of upcoming events created in the %s plugin', $this->plugin->get_plugin_slug()), $this->plugin->get_plugin_name())
			)
		);
		$this->alt_option_name = 'widget_'.$this->plugin->get_plugin_slug();


		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

	} // end constructor

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 * @since     1.0.0
	 */
	public function widget( $args, $instance ) {

		
		// Check if there is a cached output
		$cache = wp_cache_get( $this->widget_id, 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset ( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset ( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];
		
		// go on with your widget logic, put everything into a string and …

		if ( ! isset( $instance['number'] ) )
			$instance['number'] = '10';

		if ( ! $number = absint( $instance['number'] ) )
 			$number = 10;

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Upcoming Events', $this->plugin->get_plugin_slug() ) : $instance['title'], $instance, $this->id_base);		

		$event_args = array(
			'post_type' => $this->plugin->get_plugin_slug(),
			'posts_per_page' => $number,
			'orderby' => 'meta_value',
			'order' => 'ASC',
 			'meta_key' => 'sse_start_date_alt',
			'meta_query' => array(
							  array(
								'key' => 'between_dates',
								'value' => date( 'Y-m-d H:i:s', strtotime( '+1 days',current_time( 'timestamp'))),
								'compare' => '>='
						 	  )
							)
				
		);
		$events = new WP_Query( $event_args );

		extract( $args, EXTR_SKIP );

		$widget_string  = $before_widget;
		$widget_string .= $before_title;
		$widget_string .= $title; // Can set this with a widget option, or omit altogether
		$widget_string .= $after_title;
		ob_start();
		include( plugin_dir_path( __FILE__ ) . 'views/widget.php' );
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;


		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set($this->widget_id, $cache, 'widget' );

		echo $widget_string;

	} // end widget
	
	/**
	 * Flush Widget Cache
	 * 
	 * @since     1.0.0
	 */
	public function flush_widget_cache() 
	{
    	wp_cache_delete( $this->widget_id, 'widget' );
	}
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		
		$instance = wp_parse_args( $new_instance, $old_instance );
		$this->flush_widget_cache();
		
		$instance['display_link'] = ( ! empty( $new_instance['display_link'] ) ) ? strip_tags( $new_instance['display_link'] ) : '';
		
		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 * @since     1.0.0
	 */
	public function form( $instance ) {

		$instance = wp_parse_args(
			(array) $instance
		);

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'views/admin.php' );

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/


	/**
	 * Registers and enqueues widget-specific styles.
	 * 
	 * @since     1.0.0
	 */
	public function register_widget_styles() {

		wp_enqueue_style( $this->plugin->get_plugin_slug().'-widget-styles', plugins_url( 'css/widget.css', __FILE__ ) );

	} // end register_widget_styles


} // end class


