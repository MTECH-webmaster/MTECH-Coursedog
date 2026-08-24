<?php
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_format_program_length($raw_value) {
    return esc_html($raw_value);
}

function mtech_coursedog_format_program_length_mobile($raw_value) {
    if (empty($raw_value)) {
        return '';
    }

    $replacements = array(
        'years'  => 'yrs',
        'year'   => 'yr',
        'months' => 'mos',
        'month'  => 'mo',
        'hours'  => 'hrs',
        'hour'   => 'hr',
        'weeks'  => 'wks',
        'week'   => 'wk',
    );

    $result = $raw_value;
    foreach ($replacements as $search => $replace) {
        $result = preg_replace('/\b' . preg_quote($search, '/') . '\b/i', $replace, $result);
    }

    return esc_html($result);
}