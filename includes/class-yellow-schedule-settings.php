<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Yellow_Schedule_Settings {

	/**
	 * The single instance of Yellow_Schedule_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'wpt_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Yellow Schedule', 'yellow-schedule' ) , __( 'Yellow Schedule', 'yellow-schedule' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'yellow-schedule' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['ys_standard_settings'] = array(
			'title'					=> __( 'Plugin Setup', 'yellow-schedule' ),
			'description'			=> __( '', 'yellow-schedule' ),
			'fields'				=> array(
				array(
					'id' 			=> 'ys_master_act',
					'label'			=> __( 'Enter your Business Key' , 'yellow-schedule' ),
					'description'	=> __( 'If you do not have a Yellow Schedule account <a href="http://www.yellowschedule.com" target="_blank">register here</a>.', 'yellow-schedule' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'yellow-schedule' )
				),
				array(
					'id' 			=> 'ys_num_days',
					'label'			=> __( 'Days to display' , 'yellow-schedule' ),
					'description'	=> __( 'Number of days to display ranging from 3 to 7 (this also controls the width of the box).', 'yellow-schedule' ),
					'type'			=> 'number',
					'min'			=> '1',
					'max'			=> '7',
					'default'		=> '',
					'placeholder'	=> __( 'default', 'yellow-schedule' )
				),
				array(
					'id' 			=> 'ys_display_user',
					'label'			=> __( 'Show name and picture', 'yellow-schedule' ),
					'description'	=> __( 'Shows user name & image on the booking form.', 'yellow-schedule' ),
					'type'			=> 'checkbox',
					'default'		=> 'on'
				)
			)
		);
		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
		$html .= '<h2>' . __( '<img src="../wp-content/plugins/yellow-schedule/includes/ys-logo2.png"/>' , 'yellow-schedule' ) . '</h2>' . "\n";

		$tab = '';
		if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
			$tab .= $_GET['tab'];
		}

		// Show page tabs
		if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

			$html .= '<h2 class="nav-tab-wrapper">' . "\n";

			$c = 0;
			foreach ( $this->settings as $section => $data ) {

				// Set tab class
				$class = 'nav-tab';
				if ( ! isset( $_GET['tab'] ) ) {
					if ( 0 == $c ) {
						$class .= ' nav-tab-active';
					}
				} else {
					if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
						$class .= ' nav-tab-active';
					}
				}

				// Set tab link
				$tab_link = add_query_arg( array( 'tab' => $section ) );
				if ( isset( $_GET['settings-updated'] ) ) {
					$tab_link = remove_query_arg( 'settings-updated', $tab_link );
				}

				// Output tab
				$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

				++$c;
			}

			$html .= '</h2>' . "\n";
		}

		$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

		// Get settings fields
		ob_start();
		settings_fields( $this->parent->_token . '_settings' );
		do_settings_sections( $this->parent->_token . '_settings' );
		$options_act = get_option('wpt_ys_master_act');
		$options_day = get_option('wpt_ys_num_days');
		$options_display = get_option('wpt_ys_display_user');
		$user_option = "";
		$day_option = "";
		if($options_day == "") {
		
		} else{$day_option ='daysToDisplay: '.$options_day.',';}
		if($options_display == "on") {
			$user_option = 'showUsers: true';
		} else {$user_option = 'showUsers: false';}
		$html .= ob_get_clean();

		$html .= '<p class="submit">' . "\n";
			$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
			$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'yellow-schedule' ) ) . '" />' . "\n";
		$html .= '</p>' . "\n";
		$html .= '</form>' . "\n";
		if($options_act != null) {
		$html .= '<h3>If you can see the widget below then copy and paste this shortcode into your page: <span style="background:yellow"> [yellow_schedule] </span></h3>' . "\n";
		$html .= '<link href="https://www.yellowschedule.com/utils/widget/ys-style.css" rel="stylesheet" type="text/css"/> 
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
				<script src="https://www.yellowschedule.com/_javascript/dm.booking.min.js"></script>
				<script type="text/javascript">
				jQuery.noConflict();
				jQuery(document).ready(function() {	
					jQuery().jBookingAvailability("'.$options_act.'",
						{
							'.$day_option.'
							'.$user_option.'
						}
					);
				});
				</script>
				<div id="bookingAvailabilityContainer"><a href="https://www.yellowschedule.com">Online Appointment Scheduling</a> by YellowSchedule.com</div>';
		} else {
				$html .= '<h3>This is a demonstration only. Enter your Business Key above to see your personalised widget.</h3>' . "\n";
				$html .= '<link href="https://www.yellowschedule.com/utils/widget/ys-style.css" rel="stylesheet" type="text/css"/> 
							<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
							<script src="https://www.yellowschedule.com/_javascript/dm.booking.min.js"></script>
							<script type="text/javascript">
							jQuery.noConflict();
							jQuery(document).ready(function() {	
								jQuery().jBookingAvailability(" ",
									{
										daysToDisplay: 5,
										fakeAccount: true
									}
								);
							});
							</script>
							<div id="bookingAvailabilityContainer"><a href="https://www.yellowschedule.com">Online Appointment Scheduling</a> by YellowSchedule.com</div>';
		}
		$html .= '</div>' . "\n";
		echo $html;
	}

	/**
	 * Main Yellow_Schedule_Settings Instance
	 *
	 * Ensures only one instance of Yellow_Schedule_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Yellow_Schedule()
	 * @return Main Yellow_Schedule_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}