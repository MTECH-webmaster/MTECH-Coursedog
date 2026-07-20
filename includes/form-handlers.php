<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_save_shortcode_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    $program_id   = isset($_POST['program_id']) ? absint($_POST['program_id']) : 0;
    $shortcode_id = isset($_POST['shortcode_id']) && $_POST['shortcode_id'] !== '' ? absint($_POST['shortcode_id']) : 0;

    if (!$program_id || !isset($_POST['mtech_coursedog_nonce']) ||
        !wp_verify_nonce($_POST['mtech_coursedog_nonce'], 'mtech_coursedog_save_shortcode_' . $program_id)) {
        wp_die('Invalid request', 400);
    }

    global $wpdb;
    $table_programs   = $wpdb->prefix . 'mtech_coursedog_programs';
    $table_shortcodes = $wpdb->prefix . 'mtech_coursedog_shortcodes';

    // Confirm the program actually exists before attaching a shortcode to it
    $program_exists = $wpdb->get_var(
        $wpdb->prepare("SELECT id FROM $table_programs WHERE id = %d", $program_id)
    );
    if (!$program_exists) {
        wp_die('Invalid program', 400);
    }

    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';

    // type and field are required columns (NOT NULL) — reject rather than
    // let $wpdb->insert() fail silently on a missing required value
    if ($type === '' || $field === '') {
        wp_die('Type and Field are required', 400);
    }

    $data = array(
        'program_id'             => $program_id,
        'type'                   => $type,
        'field'                  => $field,
        'search'                 => !empty($_POST['search']) ? 1 : 0,
        'search_query'           => isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '',
        'effective_dates_range'  => isset($_POST['effective_dates_range']) ? sanitize_text_field($_POST['effective_dates_range']) : '',
    );

    $formats = array('%d', '%s', '%s', '%d', '%s', '%s');

    if ($shortcode_id) {
        // Updating an existing row
        $result = $wpdb->update(
            $table_shortcodes,
            $data,
            array('id' => $shortcode_id),
            $formats,
            array('%d')
        );
    } else {
        // Inserting a new row — check the (program_id, type) unique constraint
        // ourselves first, so we can show a clear error rather than a raw DB failure
        $duplicate = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_shortcodes WHERE program_id = %d AND type = %s",
                $program_id,
                $type
            )
        );
        if ($duplicate) {
            wp_die('A shortcode of this type already exists for this program.', 400);
        }

        $result = $wpdb->insert($table_shortcodes, $data, $formats);
    }

    if ($result === false) {
        wp_die('Database error while saving shortcode.', 500);
    }

    $redirect = wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=mtech-coursedog');
    $redirect = add_query_arg('mtech_saved', '1', remove_query_arg('mtech_saved', $redirect));

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_mtech_coursedog_save_shortcode', 'mtech_coursedog_save_shortcode_handler');

function mtech_coursedog_delete_shortcode_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    $program_id   = isset($_POST['program_id']) ? absint($_POST['program_id']) : 0;
    $shortcode_id = isset($_POST['shortcode_id']) ? absint($_POST['shortcode_id']) : 0;

    if (!$shortcode_id || !isset($_POST['mtech_coursedog_delete_nonce']) ||
        !wp_verify_nonce($_POST['mtech_coursedog_delete_nonce'], 'mtech_coursedog_delete_shortcode_' . $shortcode_id)) {
        wp_die('Invalid request', 400);
    }

    global $wpdb;
    $table_shortcodes = $wpdb->prefix . 'mtech_coursedog_shortcodes';

    $result = $wpdb->delete(
        $table_shortcodes,
        array('id' => $shortcode_id),
        array('%d')
    );

    if ($result === false) {
        wp_die('Database error while deleting shortcode.', 500);
    }

    $redirect = wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=mtech-coursedog');
    $redirect = add_query_arg('mtech_deleted', '1', remove_query_arg(array('mtech_saved', 'mtech_deleted'), $redirect));

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_mtech_coursedog_delete_shortcode', 'mtech_coursedog_delete_shortcode_handler');