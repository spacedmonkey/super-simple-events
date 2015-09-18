<?php
/**
 * Super Simple Events
 *
 * @package   Super_Simple_Events_Admin
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-1.0.0+
 * @link      http://www.jonathandavidharris.co.uk/
 * @copyright 2014 Spacedmonkey
 */

/**
 * Admin Class. This is where admin panels are registered,
 * as is all the post meta boxes
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

		if( ! is_admin() ) {
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

		// Events meta box
		add_action('add_meta_boxes', array($this,'add_meta_boxes'), 1);
		add_action( 'save_post', array($this,'save_post') );

		add_action('update_option_'.$this->plugin->option_name, array($this, 'update_option_callback'), 10, 2);

		// Add column in event post list
		add_action( 'manage_'.$this->plugin->get_plugin_slug().'_posts_custom_column' , array( $this, 'custom_columns'), 10, 2 );
		add_filter( 'manage_edit-'.$this->plugin->get_plugin_slug().'_columns' , array( $this, 'add_column') );

		// Display upgrade messages
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

		if( ! is_admin() ) {
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

	/**
	 * @since     1.0.0
	 *
	 * @param Array $ArrayToStrip
	 */
	public function strip_array_indices( $ArrayToStrip ) {
	    foreach( $ArrayToStrip as $objArrayItem) {
	        $NewArray[] =  $objArrayItem;
	    }

	    return( $NewArray );
	}

	/**
	 * @since     1.0.0
	 *
	 * @param string $php_format
	 */
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
	 * Add upgrade message
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

	/**
	 * Add Column to array
	 *
	 * @author   Jonathan Harris
	 * @since    1.0.0
	 */
	public function add_column( $columns ) {
	    return array_merge( $columns,
	        array( 'sse_date' => __( 'Event Date', $this->plugin->get_plugin_slug() ) ) );
	}

	/**
	 * Add column return value
	 *
	 * @author   Jonathan Harris
	 * @since    1.0.0
	 * @param 	 String $column
	 * @param 	 int $post_id
	 */
	public function custom_columns( $column, $post_id ) {
	    switch ( $column ) {
			case 'sse_date' :
		    $date = get_post_meta($post_id, 'sse_start_date_alt', true);
	        if ( !empty( $date ) )
			    echo date($this->plugin->get_option('date_format'),strtotime($date));
			else
			    _e( 'No Date Set', $this->plugin->get_plugin_slug() );
			break;
	    }
	}

	/**
	 * Add meta box to post type
	 *
	 * @author   Jonathan Harris
	 * @since    1.0.0
	 */
	public function add_meta_boxes(){
		add_meta_box(
            $this->plugin->get_plugin_slug().'_sectionid',
            __( 'Event settings',  $this->plugin->get_plugin_slug()),
            array($this,'inner_custom_box'),
            $this->plugin->get_plugin_slug(),
            'side',
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
	 * Creating between two date
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
	/**
	 * List of inputs to display on in events meta box.
	 *
	 * @return array $list
	 */
	public function list_inputs(){
		$list = array(
					 array('key' => 'sse_start_date', 'label' => __('Start Date', $this->plugin->get_plugin_slug()), 'type' => 'date'),
					 array('key' => 'sse_end_date', 'label' => __('End Date', $this->plugin->get_plugin_slug()), 'type' => 'date'),
					 array('key' => 'sse_time', 'label' => __('Time', $this->plugin->get_plugin_slug()), 'type' => 'text'),
					 array('key' => 'sse_start_date_alt', 'label' => ' ', 'type' => 'hidden'),
					 array('key' => 'sse_end_date_alt', 'label' => ' ', 'type' => 'hidden'),
					 array('key' => 'sse_location', 'label' => __('Location', $this->plugin->get_plugin_slug()), 'type' => 'text')
				);
		return $list;
	}

	/**
	 *
	 * Diplsay function for meta box
	 */
	public function inner_custom_box(){

		$list = $this->list_inputs();
		foreach($list as $em){
			extract($em);
			$this->settings_post($type, $label, $key);
		}
	}

	/**
	 * Generate html markup for inputs
	 *
	 * @param string $type
	 * @param string $label
	 * @param string $key
	 */
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

	/**
	 * HTML markup for text input
	 *
	 * @param string $label
	 * @param string $key
	 */
	public function settings_post_text($label, $key){
		global $post;

		$value = get_post_meta( $post->ID, $key, true );

        printf(
            '<p><label for="%1$s">%2$s</label><br /><input type="text" id="%1$s" name="%1$s" value="%3$s" class="large-text" /></p>',
            $key,
        	$label,
            $value
        );
    }

    /**
	 * HTML markup for date input
	 *
	 * @param string $label
	 * @param string $key
	 */
    public function settings_post_date($label, $key){
		global $post;
		$value = get_post_meta( $post->ID, $key, true );
        printf(
            '<p><label for="%1$s">%2$s</label><br /><input type="text" class="add_date_picker large-text" id="%1$s" name="%1$s" value="%3$s" /></p>',
            $key,
            $label,
            $value
        );
    }

    /**
	 * HTML markup for hidden input
	 *
	 * @param string $label
	 * @param string $key
	 */
    public function settings_post_hidden($key){
		global $post;
		$value = get_post_meta( $post->ID, $key, true );
        printf(
            '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />',
            $key,
            $value
        );
    }

    /**
     * When update post_type_slug or taxonomy_slug, flush rewrite rules
     *
     * @param $old_value
     * @param $value
     * @since 1.0.1
     */
    public function update_option_callback($old_value, $value){

        if($old_value['post_type_slug'] != $value['post_type_slug'] || $old_value['taxonomy_slug'] != $value['taxonomy_slug']){
            Super_Simple_Events::flush_rewrite_rules();
        }

    }

	   /**
         * Register and add settings
         * @author  Jonathan Harris <jon@spacedmonkey.co.uk>
         * @since    1.0.0
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
                sprintf(__('%s Settings:', $this->plugin->get_plugin_slug()), $this->plugin->get_plugin_name()),
                array( $this, 'print_section_info' ), // Callback
                $this->plugin->get_plugin_slug() // Page
            );

            // Add the post_type_slug field to the section of the settings page
            add_settings_field(
                'post_type_slug', // ID
                __('Event Slug',$this->plugin->get_plugin_slug()),
                array( $this, 'settings_text' ), // Callback
                $this->plugin->get_plugin_slug(), // Page
                $this->plugin->setting_section_id, // Section,
                array('id' => 'post_type_slug')
            );

            // Add the taxonomy_slug field to the section of the settings page
            add_settings_field(
                'taxonomy_slug',
           		 __('Taxonomy slug',$this->plugin->get_plugin_slug()),
                array( $this, 'settings_text' ),
                $this->plugin->get_plugin_slug(),
                $this->plugin->setting_section_id,
                array('id' => 'taxonomy_slug')
            );

			// Add the date_format field to the section of the settings page
            add_settings_field(
                'date_format',
            	__('Display Date Format',$this->plugin->get_plugin_slug()),
                array( $this, 'settings_text' ),
                $this->plugin->get_plugin_slug(),
                $this->plugin->setting_section_id,
                array('id' => 'date_format')
            );

             // Add the roles_checkbox field to the section of the settings page
            add_settings_field(
                'roles_checkbox',
            	__('Access Roles',$this->plugin->get_plugin_slug()),
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

            // Add the display_meta field to the section of the settings page
            add_settings_field(
                'display_meta',
                __('Display event data before content',$this->plugin->get_plugin_slug()),
                array( $this, 'settings_checkbox' ),
                $this->plugin->get_plugin_slug(),
                $this->plugin->setting_section_id,
                array('id' => 'display_meta')
            );

            // Add the hide_old_events field to the section of the settings page
			add_settings_field(
                'hide_old_events',
                __('Hide Past Events',$this->plugin->get_plugin_slug()),
                array( $this, 'settings_checkbox' ),
                $this->plugin->get_plugin_slug(),
                $this->plugin->setting_section_id,
                array('id' => 'hide_old_events')
            );


        }

        /**
         * Sanitize each setting field as needed
         *
         * @param array $input Contains all settings fields as array keys
         * @author  Jonathan Harris <jon@spacedmonkey.co.uk>
         * @since    1.0.0
         */
        public function sanitize( $input )
        {
            $new_input = $input;

           // $new_input['override_templete'] = intval( $input['override_templete'] );
			$new_input['display_meta'] = isset( $input['display_meta'] ) ? $input['display_meta'] : 0;
            $new_input['hide_old_events'] = isset( $input['hide_old_events'] ) ? $input['hide_old_events'] : 0;

            return $new_input;
        }

        /**
         * Print the Section text that is displayed before the settings
         * @author  Jonathan Harris <jon@spacedmonkey.co.uk>
         * @since    1.0.0
         */
        public function print_section_info()
        {
            printf(__('Set the configuration options for the %s Plugin:', $this->plugin->get_plugin_slug()), $this->plugin->get_plugin_name());
        }

	   /**
		 * HTML markup for text input
		 *
		 * @param string $label
		 * @param string $key
		 */
        public function settings_text($args)
        {
			$id = $args['id'];
            printf(
                '<input type="text" id="%s" name="%s[%s]" value="%s" />',
                $id,
                $this->plugin->option_name,
                $id,
                isset( $this->plugin->options[$id] ) ? esc_attr( $this->plugin->options[$id]) : ''
            );
        }

        /**
		 * HTML markup for checkbox input
		 *
		 * @param string $label
		 * @param string $key
		 */
        public function settings_checkbox($args)
        {

			$id = $args['id'];
            printf(
                '<input type="checkbox" id="%s" name="%s[%s]" value="1" %s />',
                $id,
                $this->plugin->option_name,
                $id,
                checked( !empty($this->plugin->options[$id]), "1" , false)
            );
        }

       /**
		 * List out the wp roles as checkboxs
		 *
		 * @param string $label
		 * @param string $key
		 */
       public function roles_checkbox($args){
	       global $wp_roles;
	       $roles = $wp_roles->get_names();
		   $id = $args['id'];

		   foreach($roles as $role_id => $name){

			   printf(
	                '<label for="%s"><input type="checkbox" id="%s" name="%s[%s][]" value="%s" %s/>%s</label><br />',
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
