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

Class MTECH_Coursedog {

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
        // Items for activation
    }

    public static function deactivate_plugin() {
        // Items for deactivation
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_options_page(
            'MTECH Coursedog',
            'Coursedog API',
            'manage_options',
            'coursedog-api',
            array($this, 'display_settings_page')
        );
    }

    public function display_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <p>Hello</p>
        <?php>
    }

}