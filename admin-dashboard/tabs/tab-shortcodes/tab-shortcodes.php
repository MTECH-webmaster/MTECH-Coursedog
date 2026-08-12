<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
$table_schools    = $wpdb->prefix . 'mtech_coursedog_schools';
$table_programs   = $wpdb->prefix . 'mtech_coursedog_programs';
$table_shortcodes = $wpdb->prefix . 'mtech_coursedog_shortcodes';
$schools = $wpdb->get_results("SELECT id, name FROM $table_schools ORDER BY name ASC");
?>
<h2>Shortcodes</h2>
<?php if (isset($_GET['mtech_saved']) && $_GET['mtech_saved'] === '1') : ?>
    <div class="notice notice-success is-dismissible"><p>Shortcode values saved.</p></div>
<?php endif; ?>
<?php if (isset($_GET['mtech_deleted']) && $_GET['mtech_deleted'] === '1') : ?>
    <div class="notice notice-success is-dismissible"><p>Shortcode deleted.</p></div>
<?php endif; ?>
<?php if (isset($_GET['mtech_program_added']) && $_GET['mtech_program_added'] === '1') : ?>
    <div class="notice notice-success is-dismissible"><p>Program added.</p></div>
<?php endif; ?>
<?php if (isset($_GET['mtech_program_removed']) && $_GET['mtech_program_removed'] === '1') : ?>
    <div class="notice notice-success is-dismissible"><p>Program removed.</p></div>
<?php endif; ?>
<?php if (isset($_GET['mtech_program_updated']) && $_GET['mtech_program_updated'] === '1') : ?>
    <div class="notice notice-success is-dismissible"><p>Program updated.</p></div>
<?php endif; ?>
<ul id="mtech-coursedog-tree">
<?php foreach ($schools as $school) :
    // $programs = $wpdb->get_results(
    //     $wpdb->prepare(
    //         "SELECT id, name, coursedog_program_id FROM $table_programs WHERE school_id = %d ORDER BY name ASC",
    //         $school->id
    //     )
    // );
    $programs = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, name, slug, coursedog_program_id FROM $table_programs WHERE school_id = %d ORDER BY name ASC",
            $school->id
        )
    );
?>
    <li>
        <span class="mtech-caret" data-type="school"><?php echo esc_html($school->name); ?></span>
        <ul class="mtech-nested">
            <?php foreach ($programs as $program) :
                $shortcode_rows = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT id, type, field, search, search_query, effective_dates_range
                         FROM $table_shortcodes
                         WHERE program_id = %d
                         ORDER BY type ASC",
                        $program->id
                    )
                );
            ?>
                <li>
                    <div class="mtech-program-row">
                        <span class="mtech-caret" data-type="program"><?php echo esc_html($program->name); ?></span>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mtech-program-form-delete" onsubmit="return confirm('Remove this program? All of its shortcodes will also be deleted. This cannot be undone.');">
                            <input type="hidden" name="action" value="mtech_coursedog_remove_program">
                            <input type="hidden" name="program_id" value="<?php echo esc_attr($program->id); ?>">
                            <?php wp_nonce_field('mtech_coursedog_remove_program_' . $program->id, 'mtech_coursedog_remove_program_nonce'); ?>
                            <button type="submit" class="button-link mtech-remove-program-link">Remove Program</button>
                        </form>
                    </div>
                    
                    <ul class="mtech-nested">
                        <li>
                            <div class="mtech-program-edit-section">
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mtech-program-form-edit">
                                    <input type="hidden" name="action" value="mtech_coursedog_edit_program">
                                    <input type="hidden" name="program_id" value="<?php echo esc_attr($program->id); ?>">
                                    <?php wp_nonce_field('mtech_coursedog_edit_program_' . $program->id, 'mtech_coursedog_edit_program_nonce'); ?>
                                    <p>
                                        <label>
                                            Program Name<br>
                                            <input type="text" name="name" value="<?php echo esc_attr($program->name); ?>" required>
                                        </label>
                                    </p>
                                    <p>
                                        <label>
                                            Program Slug<br>
                                            <input type="text" name="slug" value="<?php echo esc_attr($program->slug); ?>" required>
                                        </label>
                                    </p>
                                    <p>
                                        <label>
                                            Coursedog Program ID <span class="mtech-optional">(optional)</span><br>
                                            <input type="text" name="coursedog_program_id" value="<?php echo esc_attr($program->coursedog_program_id); ?>">
                                        </label>
                                    </p>
                                    <p>
                                        <button type="submit" class="button button-primary">Update Program</button>
                                    </p>
                                </form>
                            </div>
                        </li>

                        <?php foreach ($shortcode_rows as $shortcode) : ?>
                            <li>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mtech-shortcode-form">
                                    <input type="hidden" name="action" value="mtech_coursedog_save_shortcode">
                                    <input type="hidden" name="program_id" value="<?php echo esc_attr($program->id); ?>">
                                    <input type="hidden" name="shortcode_id" value="<?php echo esc_attr($shortcode->id); ?>">
                                    <?php wp_nonce_field('mtech_coursedog_save_shortcode_' . $program->id, 'mtech_coursedog_nonce'); ?>
                                    <p>
                                        <label>
                                            Type<br>
                                            <input type="text" name="type" value="<?php echo esc_attr($shortcode->type); ?>">
                                        </label>
                                    </p>
                                    <p>
                                        <label>
                                            Field<br>
                                            <input type="text" name="field" value="<?php echo esc_attr($shortcode->field); ?>">
                                        </label>
                                    </p>
                                    <p>
                                        <label>
                                            <input type="checkbox" name="search" value="1" <?php checked((bool) $shortcode->search, true); ?>>
                                            Search
                                        </label>
                                    </p>
                                    <p>
                                        <label>
                                            Search Query<br>
                                            <input type="text" name="search_query" value="<?php echo esc_attr($shortcode->search_query); ?>">
                                        </label>
                                    </p>
                                    <p>
                                        <label>
                                            Effective Dates Range<br>
                                            <input type="text" name="effective_dates_range" value="<?php echo esc_attr($shortcode->effective_dates_range); ?>">
                                        </label>
                                    </p>
                                    <p>
                                        <button type="submit" class="button button-primary">Save</button>
                                    </p>
                                </form>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mtech-shortcode-form-delete" onsubmit="return confirm('Delete this shortcode? This cannot be undone.');">
                                    <input type="hidden" name="action" value="mtech_coursedog_delete_shortcode">
                                    <input type="hidden" name="program_id" value="<?php echo esc_attr($program->id); ?>">
                                    <input type="hidden" name="shortcode_id" value="<?php echo esc_attr($shortcode->id); ?>">
                                    <?php wp_nonce_field('mtech_coursedog_delete_shortcode_' . $shortcode->id, 'mtech_coursedog_delete_nonce'); ?>
                                    <p>
                                        <button type="submit" class="button button-secondary">Delete</button>
                                    </p>
                                </form>
                            </li>
                        <?php endforeach; ?>
                        <!-- Add new shortcode for this program -->
                        <li class="li-new-shortcode-section">
                            <p><strong>Add new shortcode</strong></p>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mtech-shortcode-form mtech-shortcode-form-new">
                                <input type="hidden" name="action" value="mtech_coursedog_save_shortcode">
                                <input type="hidden" name="program_id" value="<?php echo esc_attr($program->id); ?>">
                                <input type="hidden" name="shortcode_id" value="">
                                <?php wp_nonce_field('mtech_coursedog_save_shortcode_' . $program->id, 'mtech_coursedog_nonce'); ?>
                                <p>
                                    <label>
                                        Type<br>
                                        <input type="text" name="type" value="">
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        Field<br>
                                        <input type="text" name="field" value="">
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input type="checkbox" name="search" value="1">
                                        Search
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        Search Query<br>
                                        <input type="text" name="search_query" value="">
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        Effective Dates Range<br>
                                        <input type="text" name="effective_dates_range" value="">
                                    </label>
                                </p>
                                <p>
                                    <button type="submit" class="button button-primary">Add Shortcode</button>
                                </p>
                            </form>
                        </li>
                    </ul>
                </li>
            <?php endforeach; ?>
            <!-- Add new program for this school -->
            <li class="li-new-program-section">
                <p><strong>Add new program</strong></p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mtech-program-form-new">
                    <input type="hidden" name="action" value="mtech_coursedog_add_program">
                    <input type="hidden" name="school_id" value="<?php echo esc_attr($school->id); ?>">
                    <?php wp_nonce_field('mtech_coursedog_add_program_' . $school->id, 'mtech_coursedog_add_program_nonce'); ?>
                    <p>
                        <label>
                            Program Name<br>
                            <input type="text" name="name" value="" required>
                        </label>
                    </p>
                    <p>
                        <label>
                            Program Slug<span class="mtech-optional">(letters, numbers, hyphens only)</span><br>
                            <input type="text" name="slug" value="" required>
                        </label>
                    </p>
                    <p>
                        <label>
                            Coursedog Program ID <span class="mtech-optional">(optional)</span><br>
                            <input type="text" name="coursedog_program_id" value="">
                        </label>
                    </p>
                    <p>
                        <button type="submit" class="button button-primary">Add Program</button>
                    </p>
                </form>
            </li>
        </ul>
    </li>
<?php endforeach; ?>
</ul>