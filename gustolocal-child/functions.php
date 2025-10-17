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

// Load configuration first
require_once GUSTOLOCAL_PATH . '/inc/config.php';

// Load core modules only if configuration loaded successfully
if (class_exists('GustoLocal_Config')) {
    // Load multilanguage module (always safe)
    require_once GUSTOLOCAL_PATH . '/inc/multilang.php';
    if (class_exists('GustoLocal_Multilang')) {
        new GustoLocal_Multilang();
    }
    
    // Load other modules with error handling
    try {
        require_once GUSTOLOCAL_PATH . '/inc/woocommerce.php';
        if (class_exists('GustoLocal_WooCommerce')) {
            new GustoLocal_WooCommerce();
        }
    } catch (Exception $e) {
        // Log error but don't break the site
        error_log('GustoLocal WooCommerce module error: ' . $e->getMessage());
    }
    
    try {
        require_once GUSTOLOCAL_PATH . '/inc/meal-builder.php';
        if (class_exists('GustoLocal_MealBuilder')) {
            new GustoLocal_MealBuilder();
        }
    } catch (Exception $e) {
        // Log error but don't break the site
        error_log('GustoLocal Meal Builder module error: ' . $e->getMessage());
    }
    
    try {
        require_once GUSTOLOCAL_PATH . '/inc/admin.php';
        if (class_exists('GustoLocal_Admin')) {
            new GustoLocal_Admin();
        }
    } catch (Exception $e) {
        // Log error but don't break the site
        error_log('GustoLocal Admin module error: ' . $e->getMessage());
    }
}

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
