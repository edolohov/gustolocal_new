<?php
/**
 * GustoLocal theme functions.
 */

if ( ! defined( 'GUSTOLOCAL_VERSION' ) ) {
    define( 'GUSTOLOCAL_VERSION', '0.5.1' );
}

add_action( 'after_setup_theme', function () {
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'custom-logo', [
        'height'      => 120,
        'width'       => 120,
        'flex-height' => true,
        'flex-width'  => true,
    ] );
    add_theme_support( 'custom-spacing' );
    add_theme_support( 'custom-units', [ 'px', 'em', 'rem', '%' ] );
    add_theme_support( 'align-wide' );
} );

add_action( 'wp_enqueue_scripts', function () {
    $theme_dir = get_template_directory_uri();
    wp_enqueue_style( 'gustolocal-main', $theme_dir . '/style.css', [], GUSTOLOCAL_VERSION );
    wp_enqueue_script( 'gustolocal-navigation', $theme_dir . '/assets/js/navigation.js', [], GUSTOLOCAL_VERSION, true );
    
    // Load gallery script only on rico page
    if ( is_page( 'rico' ) || ( is_page() && get_post_field( 'post_name' ) === 'rico' ) ) {
        wp_enqueue_script( 'gustolocal-rico-gallery', $theme_dir . '/assets/js/rico-gallery.js', [], GUSTOLOCAL_VERSION, true );
    }
} );

add_action( 'enqueue_block_editor_assets', function () {
    $theme_dir = get_template_directory_uri();
    wp_enqueue_style( 'gustolocal-editor', $theme_dir . '/style.css', [], GUSTOLOCAL_VERSION );
} );
