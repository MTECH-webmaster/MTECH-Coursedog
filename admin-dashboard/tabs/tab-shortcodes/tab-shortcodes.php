<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_schools  = $wpdb->prefix . 'mtech_coursedog_schools';
$table_programs = $wpdb->prefix . 'mtech_coursedog_programs';

$schools = $wpdb->get_results("SELECT id, name FROM $table_schools ORDER BY name ASC");
?>
<h2>Shortcodes</h2>

<?php if (isset($_GET['mtech_saved']) && $_GET['mtech_saved'] === '1') : ?>
    <div class="notice notice-success is-dismissible"><p>Shortcode values saved.</p></div>
<?php endif; ?>

<ul id="mtech-coursedog-tree">
<?php foreach ($schools as $school) :
    $programs = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, name FROM $table_programs WHERE school_id = %d ORDER BY name ASC",
            $school->id
        )
    );
?>
    <li>
        <span class="mtech-caret" data-type="school"><?php echo esc_html($school->name); ?></span>
        <ul class="mtech-nested">
            <?php foreach ($programs as $program) :
                $option_key = 'mtech_coursedog_shortcode_' . $program->id;
                $saved = get_option($option_key, array());

                $name_val   = isset($saved['name']) ? $saved['name'] : '';
                $field_val  = isset($saved['field']) ? $saved['field'] : '';
                $type_val   = isset($saved['type']) ? $saved['type'] : '';
                $search_val = !empty($saved['search']);
            ?>
                <li>
                    <span class="mtech-caret" data-type="program"><?php echo esc_html($program->name); ?></span>
                    <ul class="mtech-nested">
                        <li>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mtech-shortcode-form">
                                <input type="hidden" name="action" value="mtech_coursedog_save_shortcode">
                                <input type="hidden" name="program_id" value="<?php echo esc_attr($program->id); ?>">
                                <?php wp_nonce_field('mtech_coursedog_save_shortcode_' . $program->id, 'mtech_coursedog_nonce'); ?>

                                <p>
                                    <label>
                                        Name (test)<br>
                                        <input type="text" name="name" value="<?php echo esc_attr($name_val); ?>">
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        Field<br>
                                        <input type="text" name="field" value="<?php echo esc_attr($field_val); ?>">
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        Type<br>
                                        <input type="text" name="type" value="<?php echo esc_attr($type_val); ?>">
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input type="checkbox" name="search" value="1" <?php checked($search_val, true); ?>>
                                        Search
                                    </label>
                                </p>
                                <p>
                                    <button type="submit" class="button button-primary">Save</button>
                                </p>
                            </form>
                        </li>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    </li>
<?php endforeach; ?>
</ul>