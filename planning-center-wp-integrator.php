<?php
/*
Plugin Name: Planning Center WordPress Integrator
Plugin URI: https://github.com/corneels/planning-center-wp-integrator
Description: Connect with your Planning Center account and display information
Author: Corneels de Waard
Version: 0.1.0
Author URI: https://github.com/corneels
Text Domain: planning-center-wp-integrator
Domain Path: /languages/
*/

// Copyright (c) 2017 Endo Creative. All rights reserved.
// Copyright (c) 2021 Corneels de Waard. All rights reserved.
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************


// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-planning-center-wp.php';
require plugin_dir_path(__FILE__) . 'includes/libraries/pco-php/pco-php.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_planning_center_wp()
{
	$plugin = new Planning_Center_WP();
	$plugin->run();
}

// Schedule the recurring task

function planning_center_cron()
{
	$plugin = new PCO_PHP_API();
	$plugin->get_and_store_upcoming_service_art('series_art');
}

function pcwp_plugin_deactivation()
{
	wp_clear_scheduled_hook('planning_center_cron');
}

register_deactivation_hook(__FILE__, 'pcwp_plugin_deactivation');

register_activation_hook(__FILE__, 'pwcp_plugin_activation');

function pwcp_plugin_activation()
{
	run_planning_center_wp();
	if (!wp_next_scheduled('planning_center_cron')) {
		wp_schedule_event(time(), '30min', 'planning_center_cron');
	}
}