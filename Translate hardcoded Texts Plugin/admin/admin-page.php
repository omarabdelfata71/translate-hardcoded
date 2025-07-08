<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit']) && check_admin_referer('wpml_hardcoded_translator_add_text')) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpml_hardcoded_texts';

    $original_text = sanitize_textarea_field($_POST['original_text']);
    $context = sanitize_text_field($_POST['context']);
    $domain = sanitize_text_field($_POST['domain']);

    $wpdb->insert(
        $table_name,
        array(
            'original_text' => $original_text,
            'context' => $context,
            'domain' => $domain
        ),
        array('%s', '%s', '%s')
    );

    // Register the string with WPML
    do_action('wpml_register_single_string', $domain, $context, $original_text);

    echo '<div class="notice notice-success"><p>' . __('Text added successfully!', 'wpml-hardcoded-translator') . '</p></div>';
}

// Get existing texts
global $wpdb;
$table_name = $wpdb->prefix . 'wpml_hardcoded_texts';
$texts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
?>

<div class="wrap">
    <h1><?php _e('Hardcoded Text Translator', 'wpml-hardcoded-translator'); ?></h1>

    <div class="wpml-hardcoded-translator-add-form">
        <h2><?php _e('Add New Text', 'wpml-hardcoded-translator'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('wpml_hardcoded_translator_add_text'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="original_text"><?php _e('Original Text', 'wpml-hardcoded-translator'); ?></label>
                    </th>
                    <td>
                        <textarea name="original_text" id="original_text" class="large-text" rows="3" required></textarea>
                        <p class="description"><?php _e('Enter the hardcoded text that needs translation', 'wpml-hardcoded-translator'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="context"><?php _e('Context', 'wpml-hardcoded-translator'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="context" id="context" class="regular-text" required>
                        <p class="description"><?php _e('Enter a context to help identify where this text appears (e.g., header, footer, sidebar)', 'wpml-hardcoded-translator'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain"><?php _e('Domain', 'wpml-hardcoded-translator'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="domain" id="domain" class="regular-text" value="wpml-hardcoded-translator" required>
                        <p class="description"><?php _e('Translation domain for the text (default: wpml-hardcoded-translator)', 'wpml-hardcoded-translator'); ?></p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Add Text', 'wpml-hardcoded-translator'); ?>">
            </p>
        </form>
    </div>

    <div class="wpml-hardcoded-translator-list">
        <h2><?php _e('Existing Texts', 'wpml-hardcoded-translator'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php _e('Original Text', 'wpml-hardcoded-translator'); ?></th>
                    <th scope="col"><?php _e('Context', 'wpml-hardcoded-translator'); ?></th>
                    <th scope="col"><?php _e('Domain', 'wpml-hardcoded-translator'); ?></th>
                    <th scope="col"><?php _e('Status', 'wpml-hardcoded-translator'); ?></th>
                    <th scope="col"><?php _e('Actions', 'wpml-hardcoded-translator'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($texts as $text): ?>
                <tr>
                    <td><?php echo esc_html($text->original_text); ?></td>
                    <td><?php echo esc_html($text->context); ?></td>
                    <td><?php echo esc_html($text->domain); ?></td>
                    <td><?php echo esc_html($text->status); ?></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpml-string-translation/menu/string-translation.php&context=' . urlencode($text->context))); ?>" class="button">
                            <?php _e('Translate', 'wpml-hardcoded-translator'); ?>
                        </a>
                        <button type="button" class="button button-link-delete delete-string" data-id="<?php echo esc_attr($text->id); ?>" data-nonce="<?php echo wp_create_nonce('delete_string_' . $text->id); ?>">
                            <?php _e('Delete', 'wpml-hardcoded-translator'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>