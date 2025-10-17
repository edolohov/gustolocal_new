<?php
/**
 * GustoLocal Child Theme Functions - MULTILANG ONLY VERSION
 * 
 * Version with only multilanguage module for testing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('GUSTOLOCAL_VERSION', '1.0.0');
define('GUSTOLOCAL_PATH', get_stylesheet_directory());
define('GUSTOLOCAL_URL', get_stylesheet_directory_uri());

// Load configuration first
require_once GUSTOLOCAL_PATH . '/inc/config.php';

// Load only multilanguage module
if (class_exists('GustoLocal_Config')) {
    require_once GUSTOLOCAL_PATH . '/inc/multilang.php';
    if (class_exists('GustoLocal_Multilang')) {
        new GustoLocal_Multilang();
    }
}

// Initialize theme
add_action('after_setup_theme', 'gustolocal_multilang_init');
function gustolocal_multilang_init() {
    // Load text domain for translations
    load_child_theme_textdomain('gustolocal', GUSTOLOCAL_PATH . '/languages');
    
    // Add theme support
    add_theme_support('menus');
    add_theme_support('post-thumbnails');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'gustolocal'),
    ));
}

// Add admin notice
add_action('admin_notices', 'gustolocal_multilang_admin_notice');
function gustolocal_multilang_admin_notice() {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>GustoLocal Child Theme</strong> - Multilanguage module loaded! 🌐</p>';
        echo '<p>Testing: multilanguage functionality only.</p>';
        echo '</div>';
    }
}

// Add frontend indicator
add_action('wp_footer', 'gustolocal_multilang_frontend_indicator');
function gustolocal_multilang_frontend_indicator() {
    if (current_user_can('manage_options')) {
        echo '<!-- GustoLocal Multilanguage module is active! -->';
    }
}
