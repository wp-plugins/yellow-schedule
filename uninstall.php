<?php

/**
 * 
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 * Ideally you will add all your clean-up scripts here
 * that will clean-up unused meta, options, etc. in the database.
 *
 */

// If plugin is not being uninstalled, exit (do nothing)
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all options when plugin is being uninstalled.
delete_option('wpt_ys_master_act');
delete_option('wpt_ys_num_days');
delete_option('wpt_ys_display_user');
delete_option('wpt_ys_display_hidden');