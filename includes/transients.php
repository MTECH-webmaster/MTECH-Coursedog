<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Function to set a transient

// Function to clear a transient

// Function to get program blob transient

function mtech_coursedog_get_program_blob_transient($program_slug) {
    $blob_transient_name = $program_slug . "_blob";
    $transient = get_transient($blob_transient_name);
    mtech_coursedog_log("get_program_blob_transient function ran for " . $program_slug);
    return $transient;
}

function mtech_coursedog_set_program_blob_transient($program_slug, $blob_program_data) {
    $blob_transient_name = $program_slug . "_blob";
    set_transient($blob_transient_name, $blob_program_data, 7200);
    mtech_coursedog_log("set_program_blob_transient function ran for " . $program_slug);
}