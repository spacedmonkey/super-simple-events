<?php


/
class Super_Simple_Events_Widget extends WP_Widget {
	

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

		// TODO: update widget-name-id, classname and description
		// TODO: replace 'widget-name-locale' to be named more plugin specific. Other instances exist throughout the code, too.
		parent::__construct(
			$this->widget_id, 
			__( $this->plugin->get_plugin_name(), $this->plugin->get_plugin_slug() ),
			array(
				'classname'  => $this->plugin->get_plugin_slug().'-class',
				'description' => __( 'Short description of the widget goes here.', $this->plugin->get_plugin_slug() )
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
								'value' => current_time( 'mysql'),
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
		// TODO: Here is where you manipulate your widget's values based on their input fields
		ob_start();
		include( plugin_dir_path( __FILE__ ) . 'views/widget.php' );
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;


		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set($this->widget_id, $cache, 'widget' );

		print $widget_string;

	} // end widget
	
	
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

		$instance = $old_instance;

		// TODO: Here is where you update your widget's old values with the new, incoming values

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		// TODO: Define default values for your variables
		$instance = wp_parse_args(
			(array) $instance
		);

		// TODO: Store the values of the widget in their own variable

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'views/admin.php' );

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/


	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

		// TODO: Change 'widget-name' to the name of your plugin
		wp_enqueue_style( $this->plugin->get_plugin_slug().'-widget-styles', plugins_url( 'css/widget.css', __FILE__ ) );

	} // end register_widget_styles


} // end class


