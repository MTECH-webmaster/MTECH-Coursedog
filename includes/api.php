<?php
if (!defined('ABSPATH')) {
    exit;
}

// function mtech_coursedog_generate_api_token() {

// }

function mtech_coursedog_get_api_token_from_db() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'coursedog'; // TODO: Replace with mtech_coursedog_tokens table.
    $encrypted_token_from_db = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE name = %s",
            'cd_token'
        )
    );

    if ($encrypted_token_from_db === null) { // Changed from false to null for wpdb check
        return new \WP_Error('get_api_token_from_db__error_1', 'Data not found', 'Coursedog: cd_token not found');
    }
    
    $token_plain = mtech_coursedog_decrypt_data($encrypted_token_from_db->text);

    return $token_plain; // intentionally, this could return a WP_Error from decrypt_data. Error handled one level up from this.
}
