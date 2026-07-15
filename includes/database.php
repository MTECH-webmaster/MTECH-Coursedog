<?php

if (!defined('ABSPATH')) {
    exit;
}

function mtech_coursedog_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $table_schools = $wpdb->prefix . 'mtech_coursedog_schools';
    $sql_schools = "CREATE TABLE $table_schools (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY name (name)
    ) $charset_collate;";
    dbDelta( $sql_schools );

    $table_programs = $wpdb->prefix . 'mtech_coursedog_programs';
    $sql_programs = "CREATE TABLE $table_programs (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        school_id mediumint(9) NOT NULL,
        name varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        KEY school_id (school_id)
    ) $charset_collate;";
    dbDelta( $sql_programs );

    $table_shortcode_items = $wpdb->prefix . 'mtech_coursedog_shortcodes';
    $sql_shortcodes = "CREATE TABLE $table_shortcode_items (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        program_id mediumint(9) NOT NULL,
        name varchar(255) NOT NULL,
        field varchar(255) NOT NULL,
        type varchar(255) NOT NULL,
        search tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        UNIQUE KEY name (name),
        KEY program_id (program_id)
    ) $charset_collate;";
    dbDelta( $sql_shortcodes );

}