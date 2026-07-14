<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_enqueue_dashboard_script($hook) {
    // Only load on our specific settings page
    if ($hook !== 'settings_page_mtech-coursedog') {
        return;
    }
    wp_enqueue_script(
        'mtech-coursedog-dashboard',
        plugin_dir_url(__FILE__) . 'dashboard.js',
        array(), // dependencies, e.g. array('jquery') if needed
        '1.0',
        true // load in footer
    );
}
add_action('admin_enqueue_scripts', 'mtech_coursedog_enqueue_dashboard_script');

function display_admin_dashboard() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Close PHP
    ?>

    <div class="wp-tabs-wrapper">
        <!-- Tab Navigation Links -->
        <nav class="nav-tab-wrapper">
            <a href="#tab-general" class="nav-tab nav-tab-active" data-tab="general">API</a>
            <a href="#tab-advanced" class="nav-tab" data-tab="advanced">Shortcodes</a>
            <a href="#tab-extensions" class="nav-tab" data-tab="extensions">Extensions</a>
        </nav>

        <!-- Tab Content Sections -->
        <div class="tab-content-container">
            <div id="tab-general" class="tab-content active">
                <h2>General Settings</h2>
                <p>Your general configuration forms go here.</p>
            </div>

            <div id="tab-advanced" class="tab-content">
                <h2>Advanced Settings</h2>
                <p>Your advanced tweak tools go here.</p>
            </div>

            <div id="tab-extensions" class="tab-content">
                <h2>Extensions</h2>
                <p>Manage your plugin add-ons here.</p>
            </div>
        </div>
    </div>

    <?php
    // Open PHP
}