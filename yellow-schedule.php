<?php
/*
 * Plugin Name: Yellow Schedule
 * Version: 1.1
 * Plugin URI: http://www.yellowschedule.com
 * Description: A Yellow Schedule appointment plugin.
 * Author: Yellow Schedule
 * Author URI: http://www.yellowschedule.com
 * Requires at least: 3.9
 * Tested up to: 4.1.1
 *
 * Text Domain: yellow-schedule
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Yellow Schedule
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-yellow-schedule.php' );
require_once( 'includes/class-yellow-schedule-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-yellow-schedule-admin-api.php' );
require_once( 'includes/lib/class-yellow-schedule-post-type.php' );
require_once( 'includes/lib/class-yellow-schedule-taxonomy.php' );

/**
 * Returns the main instance of Yellow_Schedule to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Yellow_Schedule
 */
function Yellow_Schedule () {
	$instance = Yellow_Schedule::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Yellow_Schedule_Settings::instance( $instance );
		
	}

	return $instance;
}

add_shortcode('yellow_schedule', 'ys_embed_script');

function ys_embed_script() {

	$ys_options_act = get_option('wpt_ys_master_act');
	$ys_options_day = get_option('wpt_ys_num_days');
	$ys_options_display = get_option('wpt_ys_display_user');
	
	$ys_user_option = "";
	$ys_day_option = "";
	if($ys_options_day != "") {
		$ys_day_option ='daysToDisplay: '.$ys_options_day.',';
	}
	if($ys_options_display == "on") {
		$ys_user_option = 'showUsers: true';
	} else {$ys_user_option = 'showUsers: false';}
	
	if($ys_options_act != null) {  // THIS IS THE REAL BOOKING WIDGET - THIS ONLY SHOWS WHEN THE CUSTOMER HAS ENTERED THEIR BUSINESS CODE
		$ys_html = '<link href="https://www.yellowschedule.com/utils/widget/ys-style.css" rel="stylesheet" type="text/css"/> 
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
			<script src="https://www.yellowschedule.com/_javascript/dm.booking.min.js"></script>
			<script type="text/javascript">
			jQuery.noConflict();
			jQuery(document).ready(function() {	
				jQuery().jBookingAvailability("'.$ys_options_act.'",
					{
						'.$ys_day_option.'
						'.$ys_user_option.'
					}
				);
			});
			</script>
			<div id="bookingAvailabilityContainer"><a href="https://www.yellowschedule.com">Online Appointment Scheduling</a> by YellowSchedule.com</div>';
	} else {   // Before the customer enters their business code they will see a "fake account" view. This is just to show how it the booking widget appears - It's not usable.
		$ys_html .= '<h3>This is a demonstration only. Enter your Business Key in the settings page to see your personalised widget.</h3>' . "\n";
		$ys_html .= '<link href="https://www.yellowschedule.com/utils/widget/ys-style.css" rel="stylesheet" type="text/css"/> 
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
	return $ys_html;
}

Yellow_Schedule();