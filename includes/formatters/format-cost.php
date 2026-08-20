<?php
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_format_cost($raw_value) {
    return esc_html($raw_value);
}