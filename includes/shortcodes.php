<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_shortcode_handler($atts) {
    $atts = shortcode_atts(
        array(
            'program' => '',
            'type'    => '',
        ),
        $atts,
        'mtech-coursedog'
    );

    $program = sanitize_text_field($atts['program']);
    $type    = sanitize_text_field($atts['type']);

    if (empty($program) || empty($type)) {
        return '';
    }

    $token = mtech_coursedog_get_api_token_from_db();
    if (is_wp_error($token)) {
        mtech_coursedog_log($token->get_error_message());
        return '';
    }

    // Check transient - field

    // If transient field doesn't exist:
    // Check transient - blob

     

    $program_id_from_db = mtech_coursedog_db_get_program_id($program);

    $shortcode_array_from_db = mtech_coursedog_db_get_shortcode($program_id_from_db, $type);

    $blob_program_data = mtech_coursedog_search_and_fetch_program_data($shortcode_array_from_db, $token);
    if (is_wp_error($blob_program_data)) {
        mtech_coursedog_log($blob_program_data->get_error_message());
        return '';
    }

    $blob_transient_name = $shortcode_array_from_db->search_query . $program_id_from_db;
    set_transient($blob_transient_name, $blob_program_data, 7200);



    // Turn on output buffering
    ob_start();

    echo '<pre>';
    print_r($blob_program_data);
    echo '</pre>';

    // Save the captured output to a variable and clean the buffer
    $debug_output = ob_get_clean();

    return $debug_output;


    // return esc_html($blob_program_data);
}
add_shortcode('mtech-coursedog', 'mtech_coursedog_shortcode_handler');