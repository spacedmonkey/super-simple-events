<?php
/**
 * Super Simple Events
 *
 * @package   Super_Simple_Events_Admin
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Spacedmonkey
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-super-simple-events.php`
 *
 *
 * @package Super_Simple_Events_Admin
 * @author  Jonathan Harris <jon@spacedmonkey.co.uk>
 */
class Super_Simple_Events_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	
	protected $plugin = null;
	
	private function __construct() {

		if( ! is_super_admin() ) {
			return;
		} 

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$this->plugin = Super_Simple_Events::get_instance();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_post_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_post_scripts' ) );


		// Register settings
        add_action('admin_init', array($this, 'page_init'));
        
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin->get_plugin_slug() . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );


		add_action('add_meta_boxes', array($this,'add_meta_boxes'), 1);
		add_action( 'save_post', array($this,'save_post') );
		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		
		if(!$this->plugin->is_higher_38())
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		if( ! is_super_admin() ) {
			return;
		}

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		global $post;
		
		
		if ( ! isset( $this->plugin_screen_hook_suffix )) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin->get_plugin_slug() .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Super_Simple_Events::VERSION );
		}
				
			
	}

/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_post_styles() {
		global $post;
		
		
		if ( ! isset( $post->post_type )) {
			return;
		}

				
		if ( $this->plugin->get_plugin_slug() == $post->post_type ) {
			wp_enqueue_style( $this->plugin->get_plugin_slug() .'-post-styles', plugins_url( 'assets/css/jquery-ui-1.10.4.custom.min.css', __FILE__ ), array(), Super_Simple_Events::VERSION );
			
			
		}
	}
	
	public function strip_array_indices( $ArrayToStrip ) {
	    foreach( $ArrayToStrip as $objArrayItem) {
	        $NewArray[] =  $objArrayItem;
	    }
	 
	    return( $NewArray );
	}
	public function date_format_php_to_js( $php_format ) {

	    $SYMBOLS_MATCHING = array(
	        // Day
	        'd' => 'dd',
	        'D' => 'D',
	        'j' => 'd',
	        'l' => 'DD',
	        'N' => '',
	        'S' => '',
	        'w' => '',
	        'z' => 'o',
	        // Week
	        'W' => '',
	        // Month
	        'F' => 'MM',
	        'm' => 'mm',
	        'M' => 'M',
	        'n' => 'm',
	        't' => '',
	        // Year
	        'L' => '',
	        'o' => '',
	        'Y' => 'yy',
	        'y' => 'y',
	        // Time
	        'a' => '',
	        'A' => '',
	        'B' => '',
	        'g' => '',
	        'G' => '',
	        'h' => '',
	        'H' => '',
	        'i' => '',
	        's' => '',
	        'u' => ''
	    );
	    $jqueryui_format = "";
	    $escaping = false;
	    for($i = 0; $i < strlen($php_format); $i++)
	    {
	        $char = $php_format[$i];
	        if($char === '\\') // PHP date format escaping character
	        {
	            $i++;
	            if($escaping) $jqueryui_format .= $php_format[$i];
	            else $jqueryui_format .= '\'' . $php_format[$i];
	            $escaping = true;
	        }
	        else
	        {
	            if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
	            if(isset($SYMBOLS_MATCHING[$char]))
	                $jqueryui_format .= $SYMBOLS_MATCHING[$char];
	            else
	                $jqueryui_format .= $char;
	        }
	    }
	    return $jqueryui_format;
	}
	
	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		global $post, $wp_locale;
		

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin->get_plugin_slug() . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Super_Simple_Events::VERSION );
		}
		
	

	}



	
	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_post_scripts() {
		global $post, $wp_locale;
		

		if ( ! isset( $post->post_type )) {
			return;
		}


		
		if ( $this->plugin->get_plugin_slug() == $post->post_type ) {
			wp_enqueue_script( $this->plugin->get_plugin_slug() . '-post-type-script', plugins_url( 'assets/js/post.js', __FILE__ ), array( 'jquery','jquery-ui-core','jquery-ui-position','jquery-ui-datepicker' ), Super_Simple_Events::VERSION );
			$aryArgs = array(
		        'closeText'         => __( 'Done', $this->plugin->get_plugin_slug() ),
		        'currentText'       => __( 'Today', $this->plugin->get_plugin_slug() ),
		        'monthNames'        => $this->strip_array_indices( $wp_locale->month ),
		        'monthNamesShort'   => $this->strip_array_indices( $wp_locale->month_abbrev ),
		        'monthStatus'       => __( 'Show a different month', $this->plugin->get_plugin_slug() ),
		        'dayNames'          => $this->strip_array_indices( $wp_locale->weekday ),
		        'dayNamesShort'     => $this->strip_array_indices( $wp_locale->weekday_abbrev ),
		        'dayNamesMin'       => $this->strip_array_indices( $wp_locale->weekday_initial ),
		        // set the date format to match the WP general date settings
		        'dateFormat'        => $this->date_format_php_to_js( get_option( 'date_format' ) ),
		        // get the start of week from WP general setting
		        'firstDay'          => get_option( 'start_of_week' ),
		        // is Right to left language? default is false
		        'isRTL'             => (boolean)$wp_locale->is_rtl(),
		    );
		 
		    // Pass the array to the enqueued JS
		    wp_localize_script( $this->plugin->get_plugin_slug() .'-post-type-script', 'datePickerOb', $aryArgs );
		}

	}
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 */
		
		$this->plugin_screen_hook_suffix = add_options_page(
			__( $this->plugin->get_plugin_name(), $this->plugin->get_plugin_slug() ),
			__( $this->plugin->get_plugin_name(), $this->plugin->get_plugin_slug() ),
			'manage_options',
			$this->plugin->get_plugin_slug(),
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		$this->plugin->get_options();
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin->get_plugin_slug() ) . '">' . __( 'Settings', $this->plugin->get_plugin_slug() ) . '</a>'
			),
			$links
		);

	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function admin_notices() {
    ?>
	    <div class="updated">
	        <p><?php printf(__('The plugin %s requires Wordpress 3.8',  $this->plugin->get_plugin_slug()), $this->plugin->get_plugin_name());?></p>
	    </div>
    <?php
	}


	public function add_meta_boxes(){
		add_meta_box(
            $this->plugin->get_plugin_slug().'_sectionid',
            __( 'Event settings',  $this->plugin->get_plugin_slug()),
            array($this,'inner_custom_box'),
            $this->plugin->get_plugin_slug(),
            'advanced',
			'high'
        );
	}
	
	
	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	function save_post( $post_id ) {
	
	  /*
	   * We need to verify this came from the our screen and with proper authorization,
	   * because save_post can be triggered at other times.
	   */
	
	
	  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
	  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	      return $post_id;
	
	  if(!isset($_POST['post_type']))
		  return $post_id;

	  // Check the user's permissions.
	  if ( $this->plugin->get_plugin_slug() != $_POST['post_type'] ) {
	
	   	        return $post_id;
	  }
	
	  $list = $this->list_inputs();
	  
	  
	  $start_date = $_POST['sse_start_date_alt'];
	  $end_date = $_POST['sse_end_date_alt'];
	  
	  if($start_date > $end_date){
		  $_POST['sse_end_date_alt'] = $_POST['sse_start_date_alt'];
		  $_POST['sse_end_date'] = $_POST['sse_start_date'];
		  $end_date = $_POST['sse_end_date_alt'];
	  }
	  $start_date_unix = strtotime($start_date);
	  $end_date_unix = strtotime($end_date);	  

	  $between_dates = $this->dateRange($start_date,$end_date);
	  $key = 'between_dates';
	  delete_post_meta( $post_id, $key);
	  
	  foreach($between_dates as $between_date){
		  add_post_meta( $post_id, $key, $between_date );
	  }
	  
	  foreach($list as $em){
		  extract($em);			
		  // Sanitize user input.
		  $mydata = sanitize_text_field( $_POST[$key] );
		
		  // Update the meta field in the database.
		  update_post_meta( $post_id, $key, $mydata );

	  }
	  
	  

	  return $post_id;
	}
	
	/**
	 * creating between two date
	 * @param string since
	 * @param string until
	 * @param string step
	 * @param string date format
	 * @return array
	 * @author Ali OYGUR <alioygur@gmail.com>
	 */
	public function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d' ) { 
	
	    $dates = array();
	    $current = strtotime($first);
	    $last = strtotime($last);
	
	    while( $current <= $last ) { 
	
	        $dates[] = date($format, $current);
	        $current = strtotime($step, $current);
	    }
	
	    return $dates;
	}
	
	public function list_inputs(){
		$list = array(
					 array('key' => 'sse_start_date', 'label' => 'Start date', 'type' => 'date'),
					 array('key' => 'sse_end_date', 'label' => 'End date', 'type' => 'date'),
					 array('key' => 'sse_time', 'label' => 'Time', 'type' => 'text'),
					 array('key' => 'sse_start_date_alt', 'label' => 'Start date', 'type' => 'hidden'),
					 array('key' => 'sse_end_date_alt', 'label' => 'End date', 'type' => 'hidden'),
					 array('key' => 'sse_location', 'label' => 'Location', 'type' => 'text')
				);
		return $list;
	}
	
	public function inner_custom_box(){
		
		$list = $this->list_inputs();
		foreach($list as $em){
			extract($em);
			$this->settings_post($type, $label, $key);
		}
	}
	
	public function settings_post($type, $label, $key){
		switch($type){
			case 'date':
				$this->settings_post_date($label, $key);
				break;
			case 'hidden':
				$this->settings_post_hidden($key);
				break;
			default:
				$this->settings_post_text($label, $key);
				break;
		}
		
	}
	
	public function settings_post_text($label, $key){
		global $post;
		
		$value = get_post_meta( $post->ID, $key, true );
		
        printf(
            __('<p><labal for="%1$s">%2$s</label><br /><input type="text" id="%1$s" name="%1$s" value="%3$s" class="regular-text" /></p>', $this->plugin->get_plugin_slug()),
            $key,
            $label,
            $value
        );
    }
    
    public function settings_post_date($label, $key){
		global $post;
		$value = get_post_meta( $post->ID, $key, true );
        printf(
            __('<p><labal for="%1$s">%2$s</label><br /><input type="text" class="add_date_picker" id="%1$s" name="%1$s" value="%3$s" /></p>', $this->plugin->get_plugin_slug()),
            $key,
            $label,
            $value
        );
    }
    
    public function settings_post_hidden($key){
		global $post;
		$value = get_post_meta( $post->ID, $key, true );
        printf(
            __('<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />', $this->plugin->get_plugin_slug()),
            $key,
            $value
        );
    }
	
	   /**
         * Register and add settings
         * @author  Jonathan Harris <jon@spacedmonkey.co.uk>
         * @since    2.0
         */
        public function page_init()
        {
            if(!$this->plugin->option_group || !$this->plugin->option_name || !$this->plugin->setting_section_id){
                return false;
            }
            
            // Register a setting and its sanitization callback
            register_setting(
                $this->plugin->option_group, // Option group
                $this->plugin->option_name, // Option name
                array( $this, 'sanitize' ) // Sanitize
            );
             
            //  Add a new section to a settings page.
            add_settings_section(
                $this->plugin->setting_section_id, // ID
                $this->plugin->plugin_name.' Settings', // Title
                array( $this, 'print_section_info' ), // Callback
                $this->plugin->get_plugin_slug() // Page
            );
         
            // Add the site id field to the section of the settings page
            add_settings_field(
                'post_type_slug', // ID
                'Post Type Slug', // Title
                array( $this, 'settings_text' ), // Callback
                $this->plugin->get_plugin_slug(), // Page
                $this->plugin->setting_section_id, // Section,
                array('id' => 'post_type_slug')
            );
            
            // Add the site name field to the section of the settings page
            add_settings_field(
                'taxonomy_slug',
                'Taxonomy slug',
                array( $this, 'settings_text' ),
                $this->plugin->get_plugin_slug(),
                $this->plugin->setting_section_id,
                array('id' => 'taxonomy_slug')
            );
            
             // Add the site name field to the section of the settings page
            add_settings_field(
                'roles_checkbox',
                'Roles',
                array( $this, 'roles_checkbox' ),
                $this->plugin->get_plugin_slug(),
                $this->plugin->setting_section_id,
                array('id' => 'roles')
            );
            
            // Add the site name field to the section of the settings page
          /*  add_settings_field(
                'override_templete',
                'Override Templete',
                array( $this, 'settings_checkbox' ),
                $this->plugin->get_plugin_slug(),
                $this->plugin->setting_section_id,
                array('id' => 'override_templete')
            );*/
            
            // Add the site name field to the section of the settings page
            add_settings_field(
                'display_meta',
                'Display Meta',
                array( $this, 'settings_checkbox' ),
                $this->plugin->get_plugin_slug(),
                $this->plugin->setting_section_id,
                array('id' => 'display_meta')
            );
            
        }
         
        /**
         * Sanitize each setting field as needed
         *
         * @param array $input Contains all settings fields as array keys
         * @author  Jonathan Harris <jon@spacedmonkey.co.uk>
         * @since    2.0
         */
        public function sanitize( $input )
        {
            $new_input = $input;

            $new_input['override_templete'] = isset( $input['override_templete'] );
			$new_input['display_meta'] = isset( $input['display_meta'] );
            
         
            return $new_input;
        }
         
        /**
         * Print the Section text that is displayed before the settings
         * @author  Jonathan Harris <jon@spacedmonkey.co.uk>
         * @since    2.0
         */
        public function print_section_info()
        {
            printf(__('Set the configuration options for the %s Plugin:', $this->plugin->get_plugin_slug()), $this->plugin->get_plugin_name());
        }
         
        
        public function settings_text($args)
        {
			$id = $args['id'];
            printf(
                __('<input type="text" id="%s" name="%s[%s]" value="%s" />', $this->plugin->get_plugin_slug()),
                $id,
                $this->plugin->option_name,
                $id,
                isset( $this->plugin->options[$id] ) ? esc_attr( $this->plugin->options[$id]) : ''
            );
        }
        
        public function settings_checkbox($args)
        {
			$id = $args['id'];
            printf(
                __('<input type="checkbox" id="%s" name="%s[%s]" value="1" %s />', $this->plugin->get_plugin_slug()),
                $id,
                $this->plugin->option_name,
                $id,
                checked( $this->plugin->options[$id], "1" , false)
            );
        }
         
       
       public function roles_checkbox($args){
	       global $wp_roles;
	       $roles = $wp_roles->get_names();
		   $id = $args['id'];

		   foreach($roles as $role_id => $name){
		   	
			   printf(
	                __('<label for="%s"><input type="checkbox" id="%s" name="%s[%s][]" value="%s" %s/>%s</label><br />', $this->plugin->get_plugin_slug()),
	                $role_id,
	                $role_id,
	                $this->plugin->option_name,
	                $id,
	                $role_id,
	                checked( true, in_array($role_id,$this->plugin->options[$id]), false),
	                $name
	            );
		   }
		   
       }

}
