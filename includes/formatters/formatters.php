<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/format-cost.php';
require_once __DIR__ . '/format-length.php';
require_once __DIR__ . '/format-certs.php';
require_once __DIR__ . '/format-registration.php';

function mtech_coursedog_get_formatters() {
    return array(
        'cost' => 'mtech_coursedog_format_cost',
        'program_length' => 'mtech_coursedog_format_program_length',
        'program_length_mobile' => 'mtech_coursedog_format_program_length_mobile',
        'certs' => 'mtech_coursedog_format_certs',
        'registration_range' => 'mtech_coursedog_format_registration_range',
        'registration_table' => 'mtech_coursedog_format_registration_table',
        'prereqs' => 'mtech_coursedog_format_prereqs',
    );
}

function mtech_coursedog_format_program_data($blob, $field, $type) {
    $formatters = mtech_coursedog_get_formatters();

    if (!isset($formatters[$type])) {
        mtech_coursedog_log("No formatter registered for type '{$type}'");
        return '';
    }

    // Prereqs needs the whole customFields object, not a single extracted field
    if ($type === 'prereqs') {
        $custom_fields = isset($blob['customFields']) && is_array($blob['customFields'])
            ? $blob['customFields']
            : array();
        return call_user_func($formatters[$type], $custom_fields);
    }

    $raw_value = isset($blob['customFields'][$field]) ? $blob['customFields'][$field] : null;

    if ($raw_value === null) {
        mtech_coursedog_log("Field '{$field}' not found in customFields for type '{$type}'");
        return '';
    }

    return call_user_func($formatters[$type], $raw_value);
}