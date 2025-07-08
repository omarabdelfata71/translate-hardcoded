<?php
/**
 * Plugin Name: Custom Text Translation Manager
 * Description: Translate hardcoded text using WPML by adding custom strings to WPML's string translation system
 * Version: 1.1.0
 * Author: Omar Helal
 * Text Domain: wpml-hardcoded-translator
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WPML_Hardcoded_Translator {
    private static $instance = null;
    private $plugin_path;
    private $plugin_url;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_action('wp_ajax_delete_hardcoded_text', array($this, 'delete_hardcoded_text'));
        add_action('wp_ajax_nopriv_delete_hardcoded_text', array($this, 'delete_hardcoded_text'));
    }

    public function init() {
        // Check if WPML is active
        if (!function_exists('icl_object_id')) {
            add_action('admin_notices', array($this, 'wpml_missing_notice'));
            return;
        }

        // Initialize plugin functionality
        $this->init_database();
        $this->register_strings_with_wpml();
    }

    public function init_database() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'wpml_hardcoded_texts';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            original_text text NOT NULL,
            context varchar(255) NOT NULL,
            domain varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function register_strings_with_wpml() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpml_hardcoded_texts';
        $texts = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active'");

        foreach ($texts as $text) {
            do_action('wpml_register_single_string', $text->domain, $text->context, $text->original_text);
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Hardcoded Text Translator', 'wpml-hardcoded-translator'),
            __('Hardcoded Texts', 'wpml-hardcoded-translator'),
            'manage_options',
            'wpml-hardcoded-translator',
            array($this, 'admin_page'),
            'dashicons-translation'
        );
    }

    public function admin_page() {
        require_once($this->plugin_path . 'admin/admin-page.php');
    }

    public function admin_enqueue_scripts($hook) {
        if ('toplevel_page_wpml-hardcoded-translator' !== $hook) {
            return;
        }

        wp_enqueue_style('wpml-hardcoded-translator-admin', 
            $this->plugin_url . 'admin/css/admin.css', 
            array(), 
            '1.0.0'
        );

        wp_enqueue_script('wpml-hardcoded-translator-admin', 
            $this->plugin_url . 'admin/js/admin.js', 
            array('jquery'), 
            '1.0.0', 
            true
        );

        wp_localize_script('wpml-hardcoded-translator-admin', 'wpmlHardcodedTranslator', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpml_hardcoded_translator_nonce')
        ));
    }

    public function frontend_enqueue_scripts() {
        wp_enqueue_script('wpml-hardcoded-translator-frontend', 
            $this->plugin_url . 'public/js/frontend.js', 
            array('jquery'), 
            '1.0.0', 
            true
        );

        // Pass translations to JavaScript
        $translations = $this->get_translations();
        wp_localize_script('wpml-hardcoded-translator-frontend', 'wpmlHardcodedTranslations', $translations);
    }

    private function get_translations() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpml_hardcoded_texts';
        $texts = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active'");
        $translations = array();

        foreach ($texts as $text) {
            $translations[$text->original_text] = apply_filters('wpml_translate_single_string', 
                $text->original_text, 
                $text->domain, 
                $text->context
            );
        }

        return $translations;
    }

    public function wpml_missing_notice() {
        echo '<div class="notice notice-error"><p>' . 
            __('WPML Hardcoded Text Translator requires WPML to be installed and activated.', 'wpml-hardcoded-translator') . 
            '</p></div>';
    }

    public function delete_hardcoded_text() {
        // Check nonce and permissions
        if (!isset($_POST['id']) || !isset($_POST['nonce'])) {
            wp_send_json_error(array('message' => 'Invalid request'));
        }

        // Verify the nonce
        if (!wp_verify_nonce($_POST['nonce'], 'delete_string_' . $_POST['id'])) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wpml_hardcoded_texts';
        $id = intval($_POST['id']);

        // Delete the string
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to delete the string'));
        }

        wp_send_json_success(array('message' => 'String deleted successfully'));
    }
}

// Initialize the plugin
function wpml_hardcoded_translator_init() {
    return WPML_Hardcoded_Translator::get_instance();
}

add_action('plugins_loaded', 'wpml_hardcoded_translator_init');