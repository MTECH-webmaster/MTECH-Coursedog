<?php
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_encrypt_data($data) {
    if (!defined('MTECH_COURSEDOG_ENCRYPTION_KEY') || !defined('MTECH_COURSEDOG_ENCRYPTION_SALT')) {
        return new \WP_Error('encrypt_data__error_1', 'Data not found', 'Coursedog: Encryption constants not defined in wp-config.php');
    }

    if (!extension_loaded('openssl')) {
        return new \WP_Error('encrypt_data__error_2', 'Data not found', 'Coursedog: OpenSSL extension not loaded');
    }

    $key = MTECH_COURSEDOG_ENCRYPTION_KEY;
    $salt = MTECH_COURSEDOG_ENCRYPTION_SALT;

    $method = 'aes-256-ctr';
    $ivlen = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivlen);

    $encrypted = openssl_encrypt($data . $salt, $method, $key, 0, $iv);

    if (!$encrypted) {
        return new \WP_Error('encrypt_data__error_3', 'Data not found', 'Coursedog: Encryption failed');
    }

    return base64_encode($iv . $encrypted);
}

function mtech_coursedog_decrypt_data($encrypted_data) {
    if (!defined('MTECH_COURSEDOG_ENCRYPTION_KEY') || !defined('MTECH_COURSEDOG_ENCRYPTION_SALT')) {
        return new \WP_Error('decrypt_data__error_1', 'Data not found', 'Coursedog: Encryption constants not defined in wp-config.php');
    }

    if (!extension_loaded('openssl')) {
        return new \WP_Error('decrypt_data__error_2', 'Data not found', 'Coursedog: OpenSSL extension not loaded');
    }

    $key = MTECH_COURSEDOG_ENCRYPTION_KEY;
    $salt = MTECH_COURSEDOG_ENCRYPTION_SALT;

    $data = base64_decode($encrypted_data);
    if (!$data) {
        return new \WP_Error('decrypt_data__error_3', 'Data not found', 'Coursedog: base64_decode failed');
    }

    $method = 'aes-256-ctr';
    $ivlen = openssl_cipher_iv_length($method);
    if (!$ivlen) {
        return new \WP_Error('decrypt_data__error_4', 'Data not found', 'Coursedog: openssl_cipher_iv_length failed');
    }

    $iv = substr($data, 0, $ivlen);
    $encrypted = substr($data, $ivlen);

    $decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
    if (!$decrypted) {
        return new \WP_Error('decrypt_data__error_5', 'Data not found', 'Coursedog: openssl_decrypt failed');
    }

    // Remove the salt from the end
    return substr($decrypted, 0, -strlen($salt));
}
// A very helpful blogpost that was referenced in the making of these encryption functions:
// https://felix-arntz.me/blog/storing-confidential-data-in-wordpress/