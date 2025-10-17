<?php
/**
 * GustoLocal Child Theme Functions - DEBUG VERSION
 * 
 * Minimal version with error logging for debugging
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define theme constants
define('GUSTOLOCAL_VERSION', '1.0.0');
define('GUSTOLOCAL_PATH', get_stylesheet_directory());
define('GUSTOLOCAL_URL', get_stylesheet_directory_uri());

// Log that we're starting
error_log('GustoLocal: Starting theme initialization');

// Initialize theme
add_action('after_setup_theme', 'gustolocal_debug_init');
function gustolocal_debug_init() {
    error_log('GustoLocal: after_setup_theme hook called');
    
    try {
        // Load text domain for translations
        load_child_theme_textdomain('gustolocal', GUSTOLOCAL_PATH . '/languages');
        error_log('GustoLocal: Text domain loaded');
        
        // Add theme support
        add_theme_support('menus');
        add_theme_support('post-thumbnails');
        error_log('GustoLocal: Theme support added');
        
        // Register navigation menus
        register_nav_menus(array(
            'primary' => __('Primary Menu', 'gustolocal'),
        ));
        error_log('GustoLocal: Navigation menus registered');
        
    } catch (Exception $e) {
        error_log('GustoLocal: Error in gustolocal_debug_init: ' . $e->getMessage());
    }
}

// Test loading configuration
add_action('init', 'gustolocal_test_config', 1);
function gustolocal_test_config() {
    error_log('GustoLocal: Testing configuration loading');
    
    try {
        if (file_exists(GUSTOLOCAL_PATH . '/inc/config.php')) {
            require_once GUSTOLOCAL_PATH . '/inc/config.php';
            error_log('GustoLocal: Config file loaded');
            
            if (class_exists('GustoLocal_Config')) {
                error_log('GustoLocal: GustoLocal_Config class exists');
                
                $config = GustoLocal_Config::get_config();
                error_log('GustoLocal: Config loaded: ' . print_r($config, true));
            } else {
                error_log('GustoLocal: GustoLocal_Config class NOT found');
            }
        } else {
            error_log('GustoLocal: Config file NOT found at: ' . GUSTOLOCAL_PATH . '/inc/config.php');
        }
    } catch (Exception $e) {
        error_log('GustoLocal: Error loading config: ' . $e->getMessage());
    }
}

// Test loading multilang module
add_action('init', 'gustolocal_test_multilang', 5);
function gustolocal_test_multilang() {
    error_log('GustoLocal: Testing multilang module loading');
    
    try {
        if (file_exists(GUSTOLOCAL_PATH . '/inc/multilang.php')) {
            require_once GUSTOLOCAL_PATH . '/inc/multilang.php';
            error_log('GustoLocal: Multilang file loaded');
            
            if (class_exists('GustoLocal_Multilang')) {
                error_log('GustoLocal: GustoLocal_Multilang class exists');
                
                // Don't instantiate yet, just test class exists
                error_log('GustoLocal: Multilang class ready for instantiation');
            } else {
                error_log('GustoLocal: GustoLocal_Multilang class NOT found');
            }
        } else {
            error_log('GustoLocal: Multilang file NOT found at: ' . GUSTOLOCAL_PATH . '/inc/multilang.php');
        }
    } catch (Exception $e) {
        error_log('GustoLocal: Error loading multilang: ' . $e->getMessage());
    }
}

// Add admin notice
add_action('admin_notices', 'gustolocal_debug_admin_notice');
function gustolocal_debug_admin_notice() {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>GustoLocal Debug Mode</strong> 🔍</p>';
        echo '<p>Check error logs for detailed information about module loading.</p>';
        echo '<p>Config exists: ' . (file_exists(GUSTOLOCAL_PATH . '/inc/config.php') ? 'YES' : 'NO') . '</p>';
        echo '<p>Multilang exists: ' . (file_exists(GUSTOLOCAL_PATH . '/inc/multilang.php') ? 'YES' : 'NO') . '</p>';
        echo '<p>Config class: ' . (class_exists('GustoLocal_Config') ? 'YES' : 'NO') . '</p>';
        echo '<p>Multilang class: ' . (class_exists('GustoLocal_Multilang') ? 'YES' : 'NO') . '</p>';
        echo '</div>';
    }
}

// Add frontend indicator
add_action('wp_footer', 'gustolocal_debug_frontend_indicator');
function gustolocal_debug_frontend_indicator() {
    if (current_user_can('manage_options')) {
        echo '<!-- GustoLocal Debug Mode - Check error logs -->';
    }
}
