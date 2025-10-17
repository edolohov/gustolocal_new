<?php
/**
 * GustoLocal Child Theme Functions
 * 
 * This file contains all custom functionality for the GustoLocal site.
 * It's organized in modules for better maintainability.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('GUSTOLOCAL_VERSION', '1.0.0');
define('GUSTOLOCAL_PATH', get_stylesheet_directory());
define('GUSTOLOCAL_URL', get_stylesheet_directory_uri());

// Load configuration
require_once GUSTOLOCAL_PATH . '/inc/config.php';

// Load core modules
require_once GUSTOLOCAL_PATH . '/inc/multilang.php';
require_once GUSTOLOCAL_PATH . '/inc/woocommerce.php';
require_once GUSTOLOCAL_PATH . '/inc/meal-builder.php';
require_once GUSTOLOCAL_PATH . '/inc/admin.php';

// Initialize theme
add_action('after_setup_theme', 'gustolocal_init');
function gustolocal_init() {
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
