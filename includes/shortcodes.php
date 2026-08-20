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

    // Create shortcode transient name
    $shortcode_transient_name = $program_slug . $type;

    // Check shortcode transient
    $shortcode_transient = get_transient($shortcode_transient_name);
    if ($shortcode_transient !== false) {
        return $shortcode_transient;
    }

    // RETRIEVE SHORTCODE DATA FROM DATABASE HERE BECAUSE IT WILL BE NEEDED REGARDLESS OF BLOB TANSIENT EXISINT OR A COURSEDOG PROGRAM ID BEING USED.
    /////////////////////////////////////
    // Get program row using program slug
    $program_row = mtech_coursedog_db_get_program_by_slug($program_slug);
    if (is_null($program_row)) {
        mtech_coursedog_log("Program with slug '{$program_slug}' not found in database.");
        return '';
    }

    // Get coursedog program ID for query
    $coursedog_program_id_from_db = $program_row->coursedog_program_id;

    // Get program ID (auto-increment, not Coursedog)
    $program_id_from_db = $program_row->id;
    if (empty($program_id_from_db)) {
        mtech_coursedog_log("Program ID for slug '{$program_slug}' not found in database.");
        return '';
    }
    
    // Get shortcode row/object
    $shortcode_object_from_db = mtech_coursedog_db_get_shortcode($program_id_from_db, $type);
    if (is_null($shortcode_object_from_db)) {
        mtech_coursedog_log("Shortcode for program ID '{$program_id_from_db}' and type '{$type}' not found in database.");
        return '';
    }

    // Get field name for query
    $field = $shortcode_object_from_db->field;
    if (empty($field)) {
        mtech_coursedog_log("Field for program ID '{$program_id_from_db}' and type '{$type}' not found in database.");
        return '';
    }
    /////////////////////////////////////

    // Create blob transient name
    $blob_transient_name = $program_slug . "_blob";

    $blob_program_data = "";

    // Check blob transient and retrieve if it doesn't exist
    $blob_program_data_transient = get_transient($blob_transient_name);
    if ($blob_program_data_transient !== false) {
        $blob_program_data = $blob_program_data_transient;
    }
    else {
        // If no coursedog program ID is present, search and fetch
        if (is_null($coursedog_program_id_from_db)) {
            $blob_program_data = mtech_coursedog_search_and_fetch_program_data($shortcode_object_from_db->search_query, $shortcode_object_from_db->effective_dates_range, $token);
            if (is_wp_error($blob_program_data)) {
                mtech_coursedog_log($blob_program_data->get_error_message());
                return '';
            }
        }
        // If coursedog program ID is present, fetch by ID
        else {
            $blob_program_data = mtech_coursedog_fetch_program_data_by_id($coursedog_program_id_from_db, $token);
            if (is_wp_error($blob_program_data)) {
                mtech_coursedog_log($blob_program_data->get_error_message());
                return '';
            }
        }
        
        set_transient($blob_transient_name, $blob_program_data, 7200);
    }

    $field_data = mtech_coursedog_format_program_data($blob_program_data, $field, $type);




    // Turn on output buffering
    ob_start();

    echo '<pre>';
    // print_r($blob_program_data);
    print_r($field_data);
    echo '</pre>';

    // Save the captured output to a variable and clean the buffer
    $debug_output = ob_get_clean();

    return $debug_output;


    // return esc_html($blob_program_data);
}
add_shortcode('mtech-coursedog', 'mtech_coursedog_shortcode_handler');