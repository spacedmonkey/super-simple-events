<?php
/**
 * Super Simple Events
 *
 * @package   Super_Simple_Events
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-2.0+
 * @link      http://www.jonathandavidharris.co.uk/
 * @copyright 2014 Spacedmonkey
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 *
 * @package Super_Simple_Events
 * @author  Jonathan Harris <jon@spacedmonkey.co.uk>
 */
class Super_Simple_Events {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	const PLUGIN_SLUG = 'super-simple-events';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	
	public $plugin_name;
	
	protected $post_type;
	
	protected $taxonomy;
	
	
	/**
     * Capibility for accessing this panel 
     * 
     * @since    1.0
     * 
     * @var String
     */
    public $plugin_capibility = 'manage_options';
     
     
    /**
     * Name of the option group used in registering settings 
     *
     * @since    1.0
     *
     * @var String
     */
     
    public $option_group;
     
    /**
     * Name of the option name used in saving settings in db and form inputs
     * This will be loaded from the Super_Simple_Events class and stored in the variable.
     *
     * @since    1.0
     *
     * @var String
     */
     
    public $option_name;
     
    
    /**
     * Name of the setting section id used in registering settings
     *
     * @since    1.0
     *
     * @var String
     */
     
    public $setting_section_id;
     
     
    /**
     * Local store of options stored in DB.
     * This will be loaded from the Super_Simple_Events class and stored in the variable.
     *
     * @since    1.0
     *
     * @var String
     */
    public $options;
    
    /**
     * Local store of options stored in DB.
     * This will be loaded from the Super_Simple_Events class and stored in the variable.
     *
     * @since    1.0
     *
     * @var String
     */
    private $default_options = array();
    
    
    /**
     * List of added query variables 
     *
     * @since    1.0
     *
     * @var String
     */
    protected $query_vars = array('sse_year','sse_month','sse_day');
	
	private function __construct() {

		$this->plugin_name = __('Super Simple Events', $this->get_plugin_slug());
		
		$this->option_name = $this->get_plugin_slug().'_option_names';
		$this->option_group = $this->get_plugin_slug().'_option_group';
        $this->setting_section_id = $this->get_plugin_slug().'_setting_section_id';
		
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Register register taxonomy and post type
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		
		// Register URL rewrites
		add_filter( 'query_vars', array( $this, 'query_vars' ) , 10, 1 );
		//add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		
		// Filter posts
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		if($this->get_option('display_meta') === "1"){
			add_filter( 'the_content', array( $this, 'the_content' ), 99, 1 );
		}

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return self::PLUGIN_SLUG;
	}
	
	/**
	 * Return the plugin name.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin name variable.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}
	
	public function get_default_options(){
		$this->default_options = array(
										'post_type_slug' => 'events', 
										'taxonomy_slug' => 'eventtype', 
										'roles' => array('administrator','editor', 'author'),
										'display_meta' => '1', 
										'hide_old_events' => '1', 
										'date_format' => 'dS M Y'
								);
		return apply_filters($this->get_plugin_slug().'_default_options', $this->default_options);
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		update_option( self::PLUGIN_SLUG . '-version', self::VERSION );
		flush_rewrite_rules();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		update_option( self::PLUGIN_SLUG . '-version', 0 );
		flush_rewrite_rules();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = self::PLUGIN_SLUG;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 * 
	 * @author   Jonathan Harris <jon@spacedmonkey.co.uk>
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
	
		if(!$this->is_higher_38()){
			wp_enqueue_style( 'dashicons', plugins_url( 'assets/css/dashicons.css', __FILE__ ), array(), self::VERSION );
		}
		
		wp_enqueue_style( self::PLUGIN_SLUG . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array( 'dashicons' ), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 * 
	 * @author   Jonathan Harris <jon@spacedmonkey.co.uk>
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( self::PLUGIN_SLUG . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}
	
	/**
	 * Is the wordpress version higher than 3.8.
	 * 
	 * @author   Jonathan Harris <jon@spacedmonkey.co.uk>
	 * @since    1.0.0 
	 * @return boolean 
	 */
	public function is_higher_38(){
		return !( version_compare( $GLOBALS['wp_version'], '3.8-alpha', '<' ) );
	}

	/**
	 * @author   Jonathan Harris <jon@spacedmonkey.co.uk>
	 * @since    1.0.0
	 * @return   String
	 */
	public function get_icon(){
		return ($this->is_higher_38()) ? 'dashicons-calendar' : plugins_url( 'assets/images/calendar.png' , __FILE__ );
	}
	
	
	/**
     * Load the options out of the DB using the wp get_options function
     * @since    1.0
     * @author   Jonathan Harris <jon@spacedmonkey.co.uk>
     * @return   array|false 
     */
    public function get_options(){
        // if not set, get from DB. 
        if(!$this->options){
            $this->options = get_option($this->option_name, $this->get_default_options());
        }
        
        return $this->options;
    }
     
    /**
     * As all the options are stored in the db as an array, 
     * to get each value out, first load all the options into a variable and get option by the key name.
     * @since    1.0
     * @author   Jonathan Harris <jon@spacedmonkey.co.uk>
     * @return   string
     */
    public function get_option($key){
        $options = $this->get_options();
        return $options[$key];
    }
	
	/**
     * Register Post Type
	 *
	 * @since    1.0.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'                => _x( 'Events', 'Post Type General Name', $this->get_plugin_slug() ),
			'singular_name'       => _x( 'Event', 'Post Type Singular Name', $this->get_plugin_slug() ),
			'menu_name'           => __( 'Events', $this->get_plugin_slug() ),
			'parent_item_colon'   => __( 'Parent Event:', $this->get_plugin_slug() ),
			'all_items'           => __( 'All Events', $this->get_plugin_slug() ),
			'view_item'           => __( 'View Event', $this->get_plugin_slug() ),
			'add_new_item'        => __( 'Add New Event', $this->get_plugin_slug() ),
			'add_new'             => __( 'New Event', $this->get_plugin_slug() ),
			'edit_item'           => __( 'Edit Event', $this->get_plugin_slug() ),
			'update_item'         => __( 'Update Event', $this->get_plugin_slug() ),
			'search_items'        => __( 'Search events', $this->get_plugin_slug() ),
			'not_found'           => __( 'No events found', $this->get_plugin_slug() ),
			'not_found_in_trash'  => __( 'No events found in Trash', $this->get_plugin_slug() ),
		);
		$capabilities = array(
			'edit_post'           => 'edit_post',
			'read_post'           => 'read_post',
			'delete_post'         => 'delete_post',
			'edit_posts'          => 'edit_posts',
			'edit_others_posts'   => 'edit_others_posts',
			'publish_posts'       => 'publish_posts',
			'read_private_posts'  => 'read_private_posts',
		);
		
		$args = array(
			'label'               => __( 'super-simply-event', $this->get_plugin_slug() ),
			'description'         => __( 'Events information pages', $this->get_plugin_slug() ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'publicize', 'wpcom-markdown' ),
			'taxonomies'          => array( 'super-simply-event-type' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => $this->get_icon(),
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => array( 'slug' => $this->get_option('post_type_slug') )
			//'capabilities'        => $capabilities
		);
		
		$this->post_type = register_post_type( $this->get_plugin_slug(), $args );
	}

	/**
	 * Register Taxonomy
	 *
	 * @since    1.0.0
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'              => _x( 'Event Types', 'taxonomy general name', $this->get_plugin_slug() ),
			'singular_name'     => _x( 'Event Type', 'taxonomy singular name', $this->get_plugin_slug() ),
			'search_items'      => __( 'Search Event Types',$this->get_plugin_slug() ),
			'all_items'         => __( 'All Event Types', $this->get_plugin_slug() ),
			'parent_item'       => __( 'Parent Event Type', $this->get_plugin_slug() ),
			'parent_item_colon' => __( 'Parent Event Type:', $this->get_plugin_slug() ),
			'edit_item'         => __( 'Edit Event Type', $this->get_plugin_slug() ),
			'update_item'       => __( 'Update Event Type', $this->get_plugin_slug() ),
			'add_new_item'      => __( 'Add New Event Type', $this->get_plugin_slug() ),
			'new_item_name'     => __( 'New Event Type Name', $this->get_plugin_slug() ),
			'menu_name'         => __( 'Event Type', $this->get_plugin_slug() ),
		);
	
		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => $this->get_option('taxonomy_slug') )
		);
	
		$this->taxonomy = register_taxonomy( 'super-simply-event-type', array( $this->get_plugin_slug() ), $args );
	}
	
	/**
	 * 
	 * Add a query variables
	 * 
	 * @param    array $vars
	 * @since    1.0.0
	 * @return   array $new_vars
	 */
	public function query_vars($vars){
		//print_r($vars);
		//print_r(wp_parse_args($this->query_vars, $vars));
		return wp_parse_args($this->query_vars, $vars);
	}
	
	/**
	 * Add rewrite rule
	 * 
	 * @since    1.0.0
	 */
	public function add_rewrite_rule(){
		$count = 1;
		$query = "";
		$rule = "index.php?post_type=".$this->get_plugin_slug();
		foreach($this->query_vars as $var){
			$query .= "([^/]+)/";
			$final_query = $this->get_option('post_type_slug')."/".$query."?";
			$rule  .= '&'.$var.'=$matches['.$count.']';
			add_rewrite_rule($final_query, $rule);  
			$count++;
   		
		}
	}
	
	/**
	 * 
	 * Add filter content for event archives.
	 * 
	 * @since    1.0
     * @author   Jonathan Harris <jon@spacedmonkey.co.uk>
	 * @param object $query
	 */
	public function pre_get_posts($query){
		if($query->is_post_type_archive($this->get_plugin_slug()) && $query->is_main_query() && !is_admin()){
			$meta_query = $query->get('meta_query');

			$this_year = get_query_var('sse_year');
			$this_month = get_query_var('sse_month');
			$this_day = get_query_var('sse_day');
			
			$start_year = $end_year = "";
			
			if(!empty($this_year)){
				$start_year = $end_year = $this_year;
				$start_month = 1;
				$end_month = 12;
				$start_day = 1;
				$end_day = 31;
			}
			
			if(!empty($this_month)){
				$start_month = $end_month = $this_month;
				$start_day = 1;
				$end_day = date('t', mktime(0, 0, 0, $end_month, 1, $end_year));
			}
			
			if(!empty($this_day)){
				$start_day = $end_day = $this_day;		
			}
			
			if(!empty($start_year)){
				$start_date = "$start_year-$start_month-$start_day";
				$end_date   = "$end_year-$end_month-$end_day";
				
				$start_date_unix = strtotime($start_date);
				$end_date_unix   = strtotime($end_date);				
				
			}else{
				if($this->get_option('hide_old_events') == "1"){
					$meta_query[] = array(
							'key' => 'between_dates',
							'value' => current_time( 'mysql'),
							'compare' => '>='
						);
				}
				
			}
			$query->set('meta_query', $meta_query);
			$query->set('orderby','meta_value');
			$query->set('order','ASC');
			$query->set('meta_key','sse_start_date_alt');

			
		}
		return $query;
	}
	
	/**
	 * Filter the content and add the meta to start
	 * 
	 * @param string $content
	 */
	public function the_content($content){
		global $post;

		$new_content = $content;
		if($post->post_type == $this->get_plugin_slug()){
			$time = $date = $location = "";
			
			$date_format = $this->get_option('date_format');
			

			$start_date_post = get_post_meta($post->ID,'sse_start_date_alt',true);
			if(!empty($start_date_post)){
				$display_date = '<span class="dtstart">'.date($date_format, strtotime($start_date_post)).'</span>';
				$end_date_post = get_post_meta($post->ID,'sse_end_date_alt',true);
				if($start_date_post != $end_date_post){
					$display_date .= ' - <span class="dtend">' .date($date_format, strtotime($end_date_post)).'</span>';
				}
				$date = sprintf('<span class="sse-section"><span class="dashicons dashicons-calendar"></span> %s</span>&nbsp;&nbsp;&nbsp;', $display_date);
			}

			$time_post = get_post_meta($post->ID,'sse_time',true);
			if(!empty($time_post)){
				$time = sprintf('<span class="sse-section"><span class="dashicons dashicons-clock"></span> %s</span>&nbsp;&nbsp;&nbsp;', $time_post);
			}

			$location_post = get_post_meta($post->ID,'sse_location',true);
			if(!empty($location_post)){
				$location = sprintf('<span class="sse-section"><span class="dashicons dashicons-location-alt"></span> <span class="location">%s</span></span>&nbsp', $location_post);
			}

			
			
			$meta = sprintf('<div class="sse-meta">%1$s%2$s%3$s</div>',$time, $date, $location );
			$meta = apply_filters($this->get_plugin_slug()."_meta", $meta, $time, $date, $location);
			$new_content = $meta . $content;
		}
		return $new_content;
	}
}
