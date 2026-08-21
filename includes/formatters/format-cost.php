<?php
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_format_cost($raw_value) {
    $numberCleaned = preg_replace('/[^\d.]/', '', $raw_value);
    $float = floatval($numberCleaned);
    $intValue = intval(round($float));
    return "$" . number_format($intValue);
}