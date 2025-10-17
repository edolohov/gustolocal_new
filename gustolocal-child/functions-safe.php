<?php
/**
 * GustoLocal Child Theme Functions - SAFE VERSION
 * 
 * Minimal version for testing without breaking the site
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('GUSTOLOCAL_VERSION', '1.0.0');
define('GUSTOLOCAL_PATH', get_stylesheet_directory());
define('GUSTOLOCAL_URL', get_stylesheet_directory_uri());

// Initialize theme
add_action('after_setup_theme', 'gustolocal_safe_init');
function gustolocal_safe_init() {
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

// Add a simple admin notice to confirm child theme is working
add_action('admin_notices', 'gustolocal_safe_admin_notice');
function gustolocal_safe_admin_notice() {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>GustoLocal Child Theme</strong> is active and working! 🎉</p>';
        echo '<p>This is the safe version for testing. Ready to load full modules.</p>';
        echo '</div>';
    }
}

// Add a simple frontend indicator
add_action('wp_footer', 'gustolocal_safe_frontend_indicator');
function gustolocal_safe_frontend_indicator() {
    if (current_user_can('manage_options')) {
        echo '<!-- GustoLocal Child Theme is active and working! -->';
    }
}
