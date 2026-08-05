<?php
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_generate_api_token() {
    $api_auth_url = "https://app.coursedog.com/api/v1/sessions";

    // Temporary: restrict token generation to production only
    // if (!defined('DB_NAME') || DB_NAME !== 'wp_mtec1') {
    //     return mtech_coursedog_error('generate_api_token__error_0', 'Not on production environment', 'Token gen: Exited early, not on prod environment.');
    // }

    $username = get_option('mtech_coursedog_username');
    $encrypted_password = get_option('mtech_coursedog_encrypted_password');

    if ($username === false || $encrypted_password === false) {
        return mtech_coursedog_error('generate_api_token__error_1', 'Missing API credentials');
    }

    $password = mtech_coursedog_decrypt_data($encrypted_password);
    if (is_wp_error($password)) {
        mtech_coursedog_log($password->get_error_message());
        return $password;
    }

    $response = wp_remote_post($api_auth_url, array(
        'timeout' => 30,
        'headers' => array(
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'email'    => $username,
            'password' => $password,
        )),
    ));

    if (is_wp_error($response)) {
        mtech_coursedog_log($response->get_error_message());
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    if ($body === '') {
        return mtech_coursedog_error('generate_api_token__error_2', 'Empty response body');
    }

    $data = json_decode($body, true);
    if (!isset($data['token'])) {
        return mtech_coursedog_error('generate_api_token__error_3', 'No token in response');
    }

    $encrypted_token = mtech_coursedog_encrypt_data($data['token']);
    if (is_wp_error($encrypted_token)) {
        mtech_coursedog_log($encrypted_token->get_error_message());
        return $encrypted_token;
    }

    mtech_coursedog_save_token($encrypted_token);
    return true;
}

function mtech_coursedog_save_token($encrypted_token) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mtech_coursedog_tokens';

    $exists = $wpdb->get_var(
        $wpdb->prepare("SELECT id FROM $table_name WHERE name = %s", 'cd_token')
    );

    if ($exists) {
        $wpdb->update(
            $table_name,
            array(
                'time' => current_time('mysql'),
                'text' => $encrypted_token,
            ),
            array('name' => 'cd_token')
        );
    } else {
        $wpdb->insert(
            $table_name,
            array(
                'name' => 'cd_token',
                'time' => current_time('mysql'),
                'text' => $encrypted_token,
            )
        );
    }
}

function mtech_coursedog_get_api_token_from_db() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mtech_coursedog_tokens';
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

function mtech_coursedog_run_scheduled_token_generation() {
    $result = mtech_coursedog_generate_api_token();

    if (is_wp_error($result)) {
        $consecutive_failures = (int) get_option('mtech_coursedog_token_failures', 0) + 1;
        update_option('mtech_coursedog_token_failures', $consecutive_failures, false);

        // After 2 consecutive failed attempts (1 full day), escalate beyond just the log
        if ($consecutive_failures >= 2) {
            mtech_coursedog_log('Token generation has failed ' . $consecutive_failures . ' consecutive times: ' . $result->get_error_message());
            // Consider: wp_mail() to an admin address here, or a dashboard notice (see below)
        }
    } else {
        // Reset the counter on success
        update_option('mtech_coursedog_token_failures', 0, false);
    }
}

function mtech_coursedog_search_and_fetch_program_data($program_data_array, $token) {
    $api_base_url = 'https://app.coursedog.com/api/v1/cm/mtech';

    $search_query = $program_data_array[2];
    $effective_dates_range = $program_data_array[3];

    $api_url = trailingslashit($api_base_url) . 'programs/search/' . $search_query . '?effectiveDatesRange=' . $effective_dates_range;
    date_default_timezone_set('America/Denver');

    $response = wp_remote_get($api_url, array(
        'timeout' => 30,
        'headers' => array(
            'Accept' => 'application/json',  
            'Authorization' => 'Bearer ' . $token,
        ),
    ));

    if (is_wp_error($response)) {
        return new \WP_Error('mtech_coursedog_search_and_fetch_program_data - API request resulted in an error');
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        // error_log( date("Y-m-d H:i:s") . ' response code: ' . $response_code . ' fpde12 plain token that failed: ' . $token . "\n", 3, CD_PLUGIN_BASE_DIR . 'cd-error.log' ); // temporary
        // return new \WP_Error('fetch_program_data__error_1', 'Data not found', 'Coursedog: Response code !== 200');
        return 'error 2';
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        // return new \WP_Error('fetch_program_data__error_2', 'Data not found', 'Coursedog: Response body is empty');
        return 'error 3';
    }

    $data = json_decode($body, true);
    if (is_null($data)) {
        // return new \WP_Error('fetch_program_data__error_3', 'Data not found', 'Coursedog: Response data is null');
        return 'error 4';
    }

    if (json_last_error() !== JSON_ERROR_NONE) {
        // $error_message = 'Coursedog: JSON parsing error: ' . json_last_error_msg();
        // return new \WP_Error('fetch_program_data__error_4', 'Data not found', $error_message);
        return 'error 5';
    }

    return $data['data'][0];
}