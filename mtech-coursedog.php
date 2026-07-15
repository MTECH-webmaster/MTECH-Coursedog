<?php
/*
 * Plugin Name: MTECH Coursedog
 * Description: MTECH website integration with Coursedog Curriculum
 * Version: 1.0
 * Author: MTECH
 * Author URI: https://mtec.edu
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/admin-dashboard/dashboard.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/form-handlers.php';

class MTECH_Coursedog {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Initialize the plugin
        add_action('plugins_loaded', array($this, 'init'));
    }

    public static function activate_plugin() {
        error_log('MTECH Coursedog: activate_plugin() called');
        mtech_coursedog_create_tables(); // from database.php
    }

    public static function deactivate_plugin() {
        // Items for deactivation goes here
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_options_page(
            'MTECH Coursedog',
            'MTECH Coursedog',
            'manage_options',
            'mtech-coursedog',
            'display_admin_dashboard' // from dashboard.php
        );
    }

}

///////////// End of class /////////////

register_activation_hook(__FILE__, array('MTECH_Coursedog', 'activate_plugin'));
register_deactivation_hook(__FILE__, array('MTECH_Coursedog', 'deactivate_plugin'));

// Initialize the plugin
function mtech_coursedog_init() {
    return MTECH_Coursedog::get_instance();
}

// Start the plugin
$mtech_coursedog = mtech_coursedog_init();