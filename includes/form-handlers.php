<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_save_shortcode_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    $program_id = isset($_POST['program_id']) ? absint($_POST['program_id']) : 0;

    if (!$program_id || !isset($_POST['mtech_coursedog_nonce']) ||
        !wp_verify_nonce($_POST['mtech_coursedog_nonce'], 'mtech_coursedog_save_shortcode_' . $program_id)) {
        wp_die('Invalid request', 400);
    }

    $value = array(
        'name'   => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
        'field'  => isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '',
        'type'   => isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '',
        'search' => !empty($_POST['search']),
    );

    update_option('mtech_coursedog_shortcode_' . $program_id, $value, true);

    $redirect = wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=mtech-coursedog');
    $redirect = add_query_arg('mtech_saved', '1', remove_query_arg('mtech_saved', $redirect));

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_mtech_coursedog_save_shortcode', 'mtech_coursedog_save_shortcode_handler');