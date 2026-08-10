<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_shortcode_handler($atts) {
    $atts = shortcode_atts(
        array(
            'program_slug' => '',
            'type'    => '',
        ),
        $atts,
        'mtech-coursedog'
    );

    $program_slug = sanitize_text_field($atts['program_slug']);
    $type    = sanitize_text_field($atts['type']);

    if (empty($program_slug) || empty($type)) {
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

     

    $program_row = mtech_coursedog_db_get_program_by_slug($program_slug);

    $program_id_from_db = $program_row->id;
    $coursedog_program_id_from_db = $program_row->coursedog_program_id;
    
    $shortcode_object_from_db = mtech_coursedog_db_get_shortcode($program_id_from_db, $type);

    if (is_null($coursedog_program_id_from_db)) {
        $blob_program_data = mtech_coursedog_search_and_fetch_program_data($shortcode_object_from_db->search_query, $shortcode_object_from_db->effective_dates_range, $token);
        if (is_wp_error($blob_program_data)) {
            mtech_coursedog_log($blob_program_data->get_error_message());
            return '';
        }
    }
    else {
        $blob_program_data = "Test blob: prog ID not null";
    }


    $blob_transient_name = $shortcode_object_from_db->search_query . $program_id_from_db;
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