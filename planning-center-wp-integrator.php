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
run_planning_center_wp();

// Schedule the recurring task

function planning_center_cron()
{
	$args = array(
		'method' => '',
		'parameters' => '',
	);
	$api_conn = new PCO_PHP_API();
	$result = $api_conn->get_upcoming_service_details($args);
}

function pcwp_plugin_deactivation()
{
	wp_clear_scheduled_hook('planning_center_cron');
	delete_transient('time');
	delete_transient('title');
	delete_transient('series');
	delete_transient('series_art');
	delete_transient('scripture');
	delete_transient('plan_id');
}

function pwcp_plugin_activation()
{
	if (!wp_next_scheduled('5min_event')) {
		wp_schedule_event(time(), '5min', '5min_event');
	}
	planning_center_cron();
}

register_deactivation_hook(__FILE__, 'pcwp_plugin_deactivation');

register_activation_hook(__FILE__, 'pwcp_plugin_activation');

add_action('5min_event', 'planning_center_cron');
