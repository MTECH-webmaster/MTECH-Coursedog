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


    $programData = mtech_coursedog_search_and_fetch_program_data($program, $type);

    // Future logic goes here: check transient, fall back to wp_options,
    // query Coursedog API, format, cache, return value.

    return esc_html("Program data: {$programData}");
}
add_shortcode('mtech-coursedog', 'mtech_coursedog_shortcode_handler');