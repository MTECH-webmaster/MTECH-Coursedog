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

    // Future logic goes here: check transient, fall back to wp_options,
    // query Coursedog API, format, cache, return value.

    return esc_html("Program: {$program}, Type: {$type}");
}
add_shortcode('mtech-coursedog', 'mtech_coursedog_shortcode_handler');