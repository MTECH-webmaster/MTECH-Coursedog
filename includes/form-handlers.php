<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_save_shortcode_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    $program_id   = isset($_POST['program_id']) ? absint($_POST['program_id']) : 0;
    $shortcode_id = isset($_POST['shortcode_id']) && $_POST['shortcode_id'] !== '' ? absint($_POST['shortcode_id']) : 0;

    if (!$program_id || !isset($_POST['mtech_coursedog_nonce']) ||
        !wp_verify_nonce($_POST['mtech_coursedog_nonce'], 'mtech_coursedog_save_shortcode_' . $program_id)) {
        wp_die('Invalid request', 400);
    }

    global $wpdb;
    $table_programs   = $wpdb->prefix . 'mtech_coursedog_programs';
    $table_shortcodes = $wpdb->prefix . 'mtech_coursedog_shortcodes';

    // Confirm the program actually exists before attaching a shortcode to it
    $program_exists = $wpdb->get_var(
        $wpdb->prepare("SELECT id FROM $table_programs WHERE id = %d", $program_id)
    );
    if (!$program_exists) {
        wp_die('Invalid program', 400);
    }

    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';

    // type and field are required columns (NOT NULL) — reject rather than
    // let $wpdb->insert() fail silently on a missing required value
    if ($type === '' || $field === '') {
        wp_die('Type and Field are required', 400);
    }

    $data = array(
        'program_id'             => $program_id,
        'type'                   => $type,
        'field'                  => $field,
        'search'                 => !empty($_POST['search']) ? 1 : 0,
        'search_query'           => isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '',
        'effective_dates_range'  => isset($_POST['effective_dates_range']) ? sanitize_text_field($_POST['effective_dates_range']) : '',
    );

    $formats = array('%d', '%s', '%s', '%d', '%s', '%s');

    if ($shortcode_id) {
        // Updating an existing row
        $result = $wpdb->update(
            $table_shortcodes,
            $data,
            array('id' => $shortcode_id),
            $formats,
            array('%d')
        );
    } else {
        // Inserting a new row — check the (program_id, type) unique constraint
        // ourselves first, so we can show a clear error rather than a raw DB failure
        $duplicate = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_shortcodes WHERE program_id = %d AND type = %s",
                $program_id,
                $type
            )
        );
        if ($duplicate) {
            wp_die('A shortcode of this type already exists for this program.', 400);
        }

        $result = $wpdb->insert($table_shortcodes, $data, $formats);
    }

    if ($result === false) {
        wp_die('Database error while saving shortcode.', 500);
    }

    $redirect = wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=mtech-coursedog');
    $redirect = add_query_arg('mtech_saved', '1', remove_query_arg('mtech_saved', $redirect));

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_mtech_coursedog_save_shortcode', 'mtech_coursedog_save_shortcode_handler');

function mtech_coursedog_delete_shortcode_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    $program_id   = isset($_POST['program_id']) ? absint($_POST['program_id']) : 0;
    $shortcode_id = isset($_POST['shortcode_id']) ? absint($_POST['shortcode_id']) : 0;

    if (!$shortcode_id || !isset($_POST['mtech_coursedog_delete_nonce']) ||
        !wp_verify_nonce($_POST['mtech_coursedog_delete_nonce'], 'mtech_coursedog_delete_shortcode_' . $shortcode_id)) {
        wp_die('Invalid request', 400);
    }

    global $wpdb;
    $table_shortcodes = $wpdb->prefix . 'mtech_coursedog_shortcodes';

    $result = $wpdb->delete(
        $table_shortcodes,
        array('id' => $shortcode_id),
        array('%d')
    );

    if ($result === false) {
        wp_die('Database error while deleting shortcode.', 500);
    }

    $redirect = wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=mtech-coursedog');
    $redirect = add_query_arg('mtech_deleted', '1', remove_query_arg(array('mtech_saved', 'mtech_deleted'), $redirect));

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_mtech_coursedog_delete_shortcode', 'mtech_coursedog_delete_shortcode_handler');

function mtech_coursedog_add_program_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    $school_id = isset($_POST['school_id']) ? absint($_POST['school_id']) : 0;

    if (!$school_id || !isset($_POST['mtech_coursedog_add_program_nonce']) ||
        !wp_verify_nonce($_POST['mtech_coursedog_add_program_nonce'], 'mtech_coursedog_add_program_' . $school_id)) {
        wp_die('Invalid request', 400);
    }

    global $wpdb;
    $table_schools  = $wpdb->prefix . 'mtech_coursedog_schools';
    $table_programs = $wpdb->prefix . 'mtech_coursedog_programs';

    // Confirm the school actually exists before attaching a program to it
    $school_exists = $wpdb->get_var(
        $wpdb->prepare("SELECT id FROM $table_schools WHERE id = %d", $school_id)
    );
    if (!$school_exists) {
        wp_die('Invalid school', 400);
    }

    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    if ($name === '') {
        wp_die('Program name is required', 400);
    }

    $slug = isset($_POST['slug']) ? mtech_coursedog_sanitize_program_slug($_POST['slug']) : '';
    if ($slug === '') {
        wp_die('A valid program slug is required', 400);
    }

    $duplicate_slug = $wpdb->get_var(
        $wpdb->prepare("SELECT id FROM $table_programs WHERE slug = %s", $slug)
    );
    if ($duplicate_slug) {
        wp_die('This slug is already in use by another program.', 400);
    }

    $coursedog_program_id = isset($_POST['coursedog_program_id']) ? sanitize_text_field($_POST['coursedog_program_id']) : '';

    $data = array(
        'school_id' => $school_id,
        'name'      => $name,
        'slug'      => $slug,
        // Store NULL rather than an empty string when no Coursedog ID is given,
        // so the column's UNIQUE-among-non-null behavior stays meaningful
        'coursedog_program_id' => $coursedog_program_id !== '' ? $coursedog_program_id : null,
    );
    $formats = array('%d', '%s', '%s', $coursedog_program_id !== '' ? '%s' : null);

    $result = $wpdb->insert($table_programs, $data, $formats);

    if ($result === false) {
        wp_die('Database error while adding program.', 500);
    }

    $redirect = wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=mtech-coursedog');
    $redirect = add_query_arg(
        'mtech_program_added', '1',
        remove_query_arg(array('mtech_saved', 'mtech_deleted', 'mtech_program_added', 'mtech_program_removed'), $redirect)
    );

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_mtech_coursedog_add_program', 'mtech_coursedog_add_program_handler');

function mtech_coursedog_remove_program_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    $program_id = isset($_POST['program_id']) ? absint($_POST['program_id']) : 0;

    if (!$program_id || !isset($_POST['mtech_coursedog_remove_program_nonce']) ||
        !wp_verify_nonce($_POST['mtech_coursedog_remove_program_nonce'], 'mtech_coursedog_remove_program_' . $program_id)) {
        wp_die('Invalid request', 400);
    }

    global $wpdb;
    $table_programs   = $wpdb->prefix . 'mtech_coursedog_programs';
    $table_shortcodes = $wpdb->prefix . 'mtech_coursedog_shortcodes';

    // Cascade: remove this program's shortcodes first, since there's no
    // database-level foreign key to do this automatically. Without this step,
    // deleting the program would leave orphaned shortcode rows behind.
    $wpdb->delete($table_shortcodes, array('program_id' => $program_id), array('%d'));

    $result = $wpdb->delete($table_programs, array('id' => $program_id), array('%d'));

    if ($result === false) {
        wp_die('Database error while removing program.', 500);
    }

    $redirect = wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=mtech-coursedog');
    $redirect = add_query_arg(
        'mtech_program_removed', '1',
        remove_query_arg(array('mtech_saved', 'mtech_deleted', 'mtech_program_added', 'mtech_program_removed'), $redirect)
    );

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_mtech_coursedog_remove_program', 'mtech_coursedog_remove_program_handler');

function mtech_coursedog_save_api_credentials_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    if (!isset($_POST['mtech_coursedog_api_nonce']) ||
        !wp_verify_nonce($_POST['mtech_coursedog_api_nonce'], 'mtech_coursedog_save_api_credentials')) {
        wp_die('Invalid request', 400);
    }

    $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '') {
        wp_die('Username is required', 400);
    }

    update_option('mtech_coursedog_username', $username, false);

    // Only overwrite the stored password if the user actually typed a new one —
    // an empty submission means "keep the existing password unchanged"
    if ($password !== '') {
        $encrypted_password = mtech_coursedog_encrypt_data($password);

        if (is_wp_error($encrypted_password)) {
            mtech_coursedog_log($encrypted_password->get_error_message());
            wp_die('Failed to encrypt password. Check the error log for details.', 500);
        }

        update_option('mtech_coursedog_encrypted_password', $encrypted_password, false);
    }

    $redirect = wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=mtech-coursedog');
    $redirect = add_query_arg(
        'mtech_api_saved', '1',
        remove_query_arg(array('mtech_saved', 'mtech_deleted', 'mtech_program_added', 'mtech_program_removed', 'mtech_api_saved'), $redirect)
    );

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_mtech_coursedog_save_api_credentials', 'mtech_coursedog_save_api_credentials_handler');

function mtech_coursedog_sanitize_program_slug($raw_slug) {
    // sanitize_title() is WordPress's built-in slug normalizer — same logic
    // used for post/page slugs. Turns "Automation Technology!" into "automation-technology".
    return sanitize_title($raw_slug);
}

function mtech_coursedog_edit_program_handler() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }

    $program_id = isset($_POST['program_id']) ? absint($_POST['program_id']) : 0;

    if (!$program_id || !isset($_POST['mtech_coursedog_edit_program_nonce']) ||
        !wp_verify_nonce($_POST['mtech_coursedog_edit_program_nonce'], 'mtech_coursedog_edit_program_' . $program_id)) {
        wp_die('Invalid request', 400);
    }

    global $wpdb;
    $table_programs = $wpdb->prefix . 'mtech_coursedog_programs';

    $program_exists = $wpdb->get_var(
        $wpdb->prepare("SELECT id FROM $table_programs WHERE id = %d", $program_id)
    );
    if (!$program_exists) {
        wp_die('Invalid program', 400);
    }

    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    if ($name === '') {
        wp_die('Program name is required', 400);
    }

    $slug = isset($_POST['slug']) ? mtech_coursedog_sanitize_program_slug($_POST['slug']) : '';
    if ($slug === '') {
        wp_die('A valid program slug is required', 400);
    }

    // Confirm the slug isn't already used by a *different* program
    $duplicate = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table_programs WHERE slug = %s AND id != %d",
            $slug,
            $program_id
        )
    );
    if ($duplicate) {
        wp_die('This slug is already in use by another program.', 400);
    }

    $coursedog_program_id = isset($_POST['coursedog_program_id']) ? sanitize_text_field($_POST['coursedog_program_id']) : '';

    $data = array(
        'name'                 => $name,
        'slug'                 => $slug,
        'coursedog_program_id' => $coursedog_program_id !== '' ? $coursedog_program_id : null,
    );
    $formats = array('%s', '%s', $coursedog_program_id !== '' ? '%s' : null);

    $result = $wpdb->update($table_programs, $data, array('id' => $program_id), $formats, array('%d'));

    if ($result === false) {
        wp_die('Database error while updating program.', 500);
    }

    $redirect = wp_get_referer() ? wp_get_referer() : admin_url('options-general.php?page=mtech-coursedog');
    $redirect = add_query_arg(
        'mtech_program_updated', '1',
        remove_query_arg(array('mtech_saved', 'mtech_deleted', 'mtech_program_added', 'mtech_program_removed', 'mtech_program_updated'), $redirect)
    );

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_mtech_coursedog_edit_program', 'mtech_coursedog_edit_program_handler');