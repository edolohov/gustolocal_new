<?php
/**
 * Twenty Twenty-Four functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Twenty Twenty-Four
 * @since Twenty Twenty-Four 1.0
 */

/**
 * Register block styles.
 */

if ( ! function_exists( 'twentytwentyfour_block_styles' ) ) :
	/**
	 * Register custom block styles
	 *
	 * @since Twenty Twenty-Four 1.0
	 * @return void
	 */
	function twentytwentyfour_block_styles() {

		register_block_style(
			'core/details',
			array(
				'name'         => 'arrow-icon-details',
				'label'        => __( 'Arrow icon', 'twentytwentyfour' ),
				/*
				 * Styles for the custom Arrow icon style of the Details block
				 */
				'inline_style' => '
				.is-style-arrow-icon-details {
					padding-top: var(--wp--preset--spacing--10);
					padding-bottom: var(--wp--preset--spacing--10);
				}

				.is-style-arrow-icon-details summary {
					list-style-type: "\2193\00a0\00a0\00a0";
				}

				.is-style-arrow-icon-details[open]>summary {
					list-style-type: "\2192\00a0\00a0\00a0";
				}',
			)
		);
		register_block_style(
			'core/post-terms',
			array(
				'name'         => 'pill',
				'label'        => __( 'Pill', 'twentytwentyfour' ),
				'inline_style' => '
				.is-style-pill a,
				.is-style-pill span:not([class*="color"]),
				.is-style-pill span:not([class*="background-color"]) {
					display: inline-block;
					background-color: var(--wp--preset--color--contrast-2, currentColor);
					color: var(--wp--preset--color--base, #fff);
					padding: 0.375rem 0.875rem;
					border-radius: 9999px;
					font-size: 0.875rem;
					font-weight: 600;
				}

				.is-style-pill a:hover {
					background-color: var(--wp--preset--color--contrast-3, currentColor);
					color: var(--wp--preset--color--base, #fff);
				}',
			)
		);
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark list', 'twentytwentyfour' ),
				'inline_style' => '
				.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
		register_block_style(
			'core/navigation-link',
			array(
				'name'         => 'arrow-link',
				'label'        => __( 'Arrow', 'twentytwentyfour' ),
				'inline_style' => '
				.is-style-arrow-link .wp-block-navigation-item__content:after {
					content: "\2197";
					padding-inline-start: 0.25rem;
					vertical-align: middle;
					text-decoration: none;
					display: inline-block;
				}',
			)
		);
		register_block_style(
			'core/heading',
			array(
				'name'         => 'asterisk',
				'label'        => __( 'With asterisk', 'twentytwentyfour' ),
				'inline_style' => "
				.is-style-asterisk:before {
					content: '';
					width: 1.5rem;
					height: 3rem;
					background: var(--wp--preset--color--contrast-2, currentColor);
					clip-path: path('M11.93.684v8.039l5.633-5.633 1.216 1.23-5.66 5.66h8.04v1.737H13.2l5.701 5.701-1.23 1.23-5.742-5.742V21h-1.737v-8.094l-5.77 5.77-1.23-1.217 5.743-5.742H.842V9.98h8.162l-5.701-5.7 1.23-1.231 5.66 5.66V.684h1.737Z');
					display: block;
				}

				/* Hide the asterisk if the heading has no content, to avoid using empty headings to display the asterisk only, which is an A11Y issue */
				.is-style-asterisk:empty:before {
					content: none;
				}

				.is-style-asterisk:-moz-only-whitespace:before {
					content: none;
				}

				.is-style-asterisk.has-text-align-center:before {
					margin: 0 auto;
				}

				.is-style-asterisk.has-text-align-right:before {
					margin-left: auto;
				}

				.rtl .is-style-asterisk.has-text-align-left:before {
					margin-right: auto;
				}",
			)
		);
	}
endif;

add_action( 'init', 'twentytwentyfour_block_styles' );

/**
 * Enqueue block stylesheets.
 */

if ( ! function_exists( 'twentytwentyfour_block_stylesheets' ) ) :
	/**
	 * Enqueue custom block stylesheets
	 *
	 * @since Twenty Twenty-Four 1.0
	 * @return void
	 */
	function twentytwentyfour_block_stylesheets() {
		/**
		 * The wp_enqueue_block_style() function allows us to enqueue a stylesheet
		 * for a specific block. These will only get loaded when the block is rendered
		 * (both in the editor and on the front end), improving performance
		 * and reducing the amount of data requested by visitors.
		 *
		 * See https://make.wordpress.org/core/2021/12/15/using-multiple-stylesheets-per-block/ for more info.
		 */
		wp_enqueue_block_style(
			'core/button',
			array(
				'handle' => 'twentytwentyfour-button-style-outline',
				'src'    => get_parent_theme_file_uri( 'assets/css/button-outline.css' ),
				'ver'    => wp_get_theme( get_template() )->get( 'Version' ),
				'path'   => get_parent_theme_file_path( 'assets/css/button-outline.css' ),
			)
		);
	}
endif;

add_action( 'init', 'twentytwentyfour_block_stylesheets' );

/**
 * Register pattern categories.
 */

if ( ! function_exists( 'twentytwentyfour_pattern_categories' ) ) :
	/**
	 * Register pattern categories
	 *
	 * @since Twenty Twenty-Four 1.0
	 * @return void
	 */
	function twentytwentyfour_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfour_page',
			array(
				'label'       => _x( 'Pages', 'Block pattern category', 'twentytwentyfour' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfour' ),
			)
		);
	}
endif;

add_action( 'init', 'twentytwentyfour_pattern_categories' );

/**
 * Simple WooCommerce customizations
 */

// Simple cart page styling
function simple_cart_styling() {
    if ( is_cart() ) {
        ?>
        <style>
        /* Basic cart improvements */
        .woocommerce-cart-form__contents .product-thumbnail {
            display: none !important;
        }
        
        .woocommerce-cart-form__contents .product-name {
            width: 50% !important;
        }
        
        /* Hide page title */
        .woocommerce-cart .entry-title,
        .woocommerce-cart h1.entry-title,
        .woocommerce-cart .page-title,
        .woocommerce-cart h1.page-title,
        .woocommerce-cart h1 {
            display: none !important;
        }
        
        /* Simple button styling */
        .woocommerce-cart .wc-proceed-to-checkout a {
            background-color: #6a5eb7 !important;
            color: white !important;
            padding: 12px 24px !important;
            border-radius: 4px !important;
            text-decoration: none !important;
            display: inline-block !important;
            width: 100% !important;
            text-align: center !important;
            box-sizing: border-box !important;
        }
        
        /* Remove borders and improve layout */
        .woocommerce-cart .woocommerce-cart-form__contents {
            border: none !important;
        }
        
        .woocommerce-cart .woocommerce-cart-form__contents tr {
            border-bottom: 1px solid #eee !important;
        }
        
        .woocommerce-cart .woocommerce-cart-form__contents td {
            border: none !important;
            vertical-align: top !important;
        }
        
        /* Remove gray borders around cart sections */
        .woocommerce-cart .woocommerce-cart-form {
            border: none !important;
            background: transparent !important;
        }
        
        .woocommerce-cart .coupon {
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .woocommerce-cart .cart-actions {
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        /* Mobile responsive fixes */
        @media (max-width: 768px) {
            .woocommerce-cart-form__contents {
                display: block !important;
            }
            
            .woocommerce-cart-form__contents thead {
                display: none !important;
            }
            
            .woocommerce-cart-form__contents tbody {
                display: block !important;
            }
            
            .woocommerce-cart-form__contents tr {
                display: block !important;
                border: 1px solid #ddd !important;
                margin-bottom: 15px !important;
                padding: 15px !important;
                border-radius: 8px !important;
                background: #f9f9f9 !important;
            }
            
            .woocommerce-cart-form__contents td {
                display: block !important;
                width: 100% !important;
                padding: 8px 0 !important;
                border: none !important;
                text-align: left !important;
            }
            
            .woocommerce-cart-form__contents .product-remove {
                position: absolute !important;
                top: 10px !important;
                right: 10px !important;
                width: auto !important;
            }
            
            .woocommerce-cart-form__contents .product-name {
                width: 100% !important;
                padding-right: 40px !important;
                margin-bottom: 15px !important;
                display: block !important;
            }
            
            .woocommerce-cart-form__contents .product-price,
            .woocommerce-cart-form__contents .product-quantity,
            .woocommerce-cart-form__contents .product-subtotal {
                width: 100% !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-bottom: 5px !important;
            }
            
            .woocommerce-cart-form__contents .product-price:before {
                content: "Цена: " !important;
                font-weight: bold !important;
            }
            
            .woocommerce-cart-form__contents .product-quantity:before {
                content: "Количество: " !important;
                font-weight: bold !important;
            }
            
            .woocommerce-cart-form__contents .product-subtotal:before {
                content: "Подытог: " !important;
                font-weight: bold !important;
            }
            
            /* Fix buttons on mobile */
            .woocommerce-cart .coupon,
            .woocommerce-cart .cart-actions {
                display: block !important;
                width: 100% !important;
                margin-bottom: 15px !important;
            }
            
            .woocommerce-cart .coupon input[type="text"] {
                width: 100% !important;
                margin-bottom: 10px !important;
            }
            
            .woocommerce-cart .coupon button,
            .woocommerce-cart .cart-actions button {
                width: 100% !important;
                margin-bottom: 10px !important;
            }
            
            .woocommerce-cart .wc-proceed-to-checkout {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                text-align: center !important;
                display: block !important;
            }
            
            .woocommerce-cart .wc-proceed-to-checkout a {
                width: 100% !important;
                text-align: center !important;
                display: block !important;
                margin: 0 !important;
                padding: 12px 24px !important;
                box-sizing: border-box !important;
            }
            
            /* Remove borders on mobile too */
            .woocommerce-cart .woocommerce-cart-form {
                border: none !important;
                background: transparent !important;
            }
            
            .woocommerce-cart .coupon {
                border: none !important;
                background: transparent !important;
            }
            
            .woocommerce-cart .cart-actions {
                border: none !important;
                background: transparent !important;
            }
        }
        </style>
        <?php
    }
}
add_action( 'wp_head', 'simple_cart_styling' );

// Simple checkout page styling
function simple_checkout_styling() {
    if ( is_checkout() ) {
        ?>
        <style>
        /* Hide page title */
        .woocommerce-checkout .entry-title {
            display: none !important;
        }
        
        /* Hide duplicate site title */
        .woocommerce-checkout .wp-block-site-title,
        .woocommerce-checkout .site-title,
        .woocommerce-checkout h1.wp-block-site-title {
            display: none !important;
        }
        </style>
        <?php
    }
}
add_action( 'wp_head', 'simple_checkout_styling' );

// Order received page styling
function order_received_styling() {
    if ( is_wc_endpoint_url( 'order-received' ) ) {
        ?>
        <style>
        /* Center content on mobile */
        @media (max-width: 768px) {
            .woocommerce-order {
                max-width: 100% !important;
                margin: 0 auto !important;
                padding: 15px !important;
                box-sizing: border-box !important;
            }
            
            .woocommerce-order .woocommerce-order-details {
                max-width: 100% !important;
                margin: 0 auto !important;
                padding: 15px !important;
                box-sizing: border-box !important;
            }
            
            .woocommerce-order .woocommerce-customer-details {
                max-width: 100% !important;
                margin: 0 auto !important;
                padding: 15px !important;
                box-sizing: border-box !important;
            }
            
            /* Ensure text doesn't overflow */
            .woocommerce-order * {
                max-width: 100% !important;
                word-wrap: break-word !important;
                box-sizing: border-box !important;
            }
            
            /* Center the main content area */
            .entry-content {
                max-width: 100% !important;
                margin: 0 auto !important;
                padding: 15px !important;
            }
            
            /* Center any tables or lists */
            .woocommerce-order table,
            .woocommerce-order ul,
            .woocommerce-order ol {
                max-width: 100% !important;
                margin: 0 auto !important;
            }
        }
        
        /* Desktop centering */
        @media (min-width: 769px) {
            .woocommerce-order {
                max-width: 800px !important;
                margin: 0 auto !important;
            }
        }
        </style>
        <?php
    }
}
add_action( 'wp_head', 'order_received_styling' );

// Remove delivery information from cart item data
function remove_delivery_from_cart_item_data( $item_data, $cart_item ) {
    // Remove all delivery-related information
    if ( isset( $item_data['delivery'] ) ) {
        unset( $item_data['delivery'] );
    }
    return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'remove_delivery_from_cart_item_data', 10, 2 );

// Add automatic delivery fee for Valencia
add_action('woocommerce_cart_calculate_fees', 'add_valencia_delivery_fee');
function add_valencia_delivery_fee($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    
    // Add 10 euro delivery fee
    $delivery_fee = 10.00;
    $cart->add_fee(__('Доставка до двери в Валенсии', 'woocommerce'), $delivery_fee);
}

// Hide shipping fields on checkout since we're using fixed delivery
add_filter('woocommerce_checkout_fields', 'hide_shipping_fields');
function hide_shipping_fields($fields) {
    // Hide shipping address fields
    unset($fields['shipping']['shipping_first_name']);
    unset($fields['shipping']['shipping_last_name']);
    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_address_1']);
    unset($fields['shipping']['shipping_address_2']);
    unset($fields['shipping']['shipping_city']);
    unset($fields['shipping']['shipping_postcode']);
    unset($fields['shipping']['shipping_country']);
    unset($fields['shipping']['shipping_state']);
    
    return $fields;
}

// Set default shipping address to Valencia
add_filter('woocommerce_checkout_get_value', 'set_default_shipping_address', 10, 2);
function set_default_shipping_address($value, $input) {
    if (strpos($input, 'shipping_') === 0) {
        switch ($input) {
            case 'shipping_city':
                return 'Валенсия';
            case 'shipping_country':
                return 'ES';
            case 'shipping_state':
                return 'VC'; // Valencia province code
            case 'shipping_postcode':
                return '46000';
        }
    }
    return $value;
}

// Force clear cart after successful order
add_action('woocommerce_thankyou', 'clear_cart_after_order', 10, 1);
function clear_cart_after_order($order_id) {
    if ($order_id) {
        WC()->cart->empty_cart();
    }
}

// Clear cart when user logs in (remove persistent cart items)
add_action('wp_login', 'clear_cart_on_login', 10, 2);
function clear_cart_on_login($user_login, $user) {
    // Clear cart for ALL users to prevent persistent items
    WC()->cart->empty_cart();
    
    // Also clear from database
    global $wpdb;
    $wpdb->delete(
        $wpdb->usermeta,
        array(
            'user_id' => $user->ID,
            'meta_key' => '_woocommerce_persistent_cart'
        )
    );
    
    // Clear user meta for this user
    delete_user_meta($user->ID, '_woocommerce_persistent_cart');
    delete_user_meta($user->ID, '_woocommerce_persistent_cart_hash');
}

// Force clear cart on every page load for logged-in users
add_action('wp_loaded', 'force_clear_cart_on_load');
function force_clear_cart_on_load() {
    if (is_user_logged_in() && is_cart()) {
        // Check if cart has invalid items
        $cart_items = WC()->cart->get_cart();
        $has_invalid_items = false;
        
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if (!empty($cart_item['wmb_payload']['items_list'])) {
                foreach ($cart_item['wmb_payload']['items_list'] as $row) {
                    $name = isset($row['name']) ? trim($row['name']) : '';
                    if (empty($name)) {
                        $has_invalid_items = true;
                        break 2;
                    }
                }
            }
        }
        
        if ($has_invalid_items) {
            WC()->cart->empty_cart();
        }
    }
}

// Clear cart on order completion
add_action('woocommerce_order_status_completed', 'clear_cart_on_order_completion');
add_action('woocommerce_order_status_processing', 'clear_cart_on_order_completion');
function clear_cart_on_order_completion($order_id) {
    WC()->cart->empty_cart();
}

// Debug functions removed - cart clearing now works automatically

// Disable persistent cart completely
add_filter('woocommerce_persistent_cart_enabled', '__return_false');

// Remove clickable links from Weekly Meal Plan product
function remove_weekly_meal_plan_links() {
    ?>
    <style>
    /* Remove links from Weekly Meal Plan product everywhere */
    a[href*="weekly-meal-plan"],
    a[href*="product/weekly-meal-plan"] {
        pointer-events: none !important;
        cursor: default !important;
        text-decoration: none !important;
        color: inherit !important;
    }
    
    /* Remove hover effects */
    a[href*="weekly-meal-plan"]:hover,
    a[href*="product/weekly-meal-plan"]:hover {
        text-decoration: none !important;
        color: inherit !important;
    }
    
    /* In cart - make product name not clickable */
    .woocommerce-cart .product-name a[href*="weekly-meal-plan"] {
        pointer-events: none !important;
        cursor: default !important;
    }
    
    /* In meal builder - make product name not clickable */
    .wmb-item-name a[href*="weekly-meal-plan"] {
        pointer-events: none !important;
        cursor: default !important;
    }
    </style>
    <?php
}
add_action('wp_head', 'remove_weekly_meal_plan_links');

// JavaScript to prevent clicks on Weekly Meal Plan links
function prevent_weekly_meal_plan_clicks() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Find all links to Weekly Meal Plan
        var links = document.querySelectorAll('a[href*="weekly-meal-plan"], a[href*="product/weekly-meal-plan"]');
        
        links.forEach(function(link) {
            // Remove href attribute
            link.removeAttribute('href');
            
            // Add click prevention
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            // Remove link styling
            link.style.cursor = 'default';
            link.style.textDecoration = 'none';
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'prevent_weekly_meal_plan_clicks');

// Contact Form 7 styling
function contact_form_7_styling() {
    ?>
    <style>
    /* Contact Form 7 styling to match site design */
    .wpcf7-form {
        max-width: 600px;
        margin: 0 auto;
        padding: 10px 20px;
    }
    
    .wpcf7-form .form-group {
        margin-bottom: 12px;
    }
    
    /* Remove default paragraph margins from Contact Form 7 */
    .wpcf7-form p {
        margin: 0 0 12px 0 !important;
        padding: 0 !important;
    }
    
    /* Remove all default spacing from form elements */
    .wpcf7-form br {
        display: none !important;
    }
    
    .wpcf7-form .wpcf7-form-control-wrap {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Ensure consistent spacing between form groups */
    .wpcf7-form p:last-child {
        margin-bottom: 0 !important;
    }
    
    .wpcf7-form label {
        display: block;
        font-weight: 600;
        margin-bottom: 4px;
        color: #333;
        font-size: 16px;
    }
    
    .wpcf7-form input[type="text"],
    .wpcf7-form input[type="email"],
    .wpcf7-form input[type="tel"],
    .wpcf7-form textarea,
    .wpcf7-form select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
        box-sizing: border-box;
    }
    
    .wpcf7-form input[type="text"]:focus,
    .wpcf7-form input[type="email"]:focus,
    .wpcf7-form input[type="tel"]:focus,
    .wpcf7-form textarea:focus,
    .wpcf7-form select:focus {
        outline: none;
        border-color: #6a5eb7;
        box-shadow: 0 0 0 3px rgba(106, 94, 183, 0.1);
    }
    
    .wpcf7-form textarea {
        min-height: 120px;
        resize: vertical;
    }
    
    .wpcf7-form input[type="submit"] {
        background-color: #6a5eb7;
        color: white;
        padding: 14px 32px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: 100%;
        margin-top: 10px;
    }
    
    .wpcf7-form input[type="submit"]:hover {
        background-color: #5a4ea6;
    }
    
    .wpcf7-form input[type="submit"]:active {
        transform: translateY(1px);
    }
    
    /* Error and success messages */
    .wpcf7-response-output {
        margin-top: 20px;
        padding: 12px 16px;
        border-radius: 8px;
        font-weight: 500;
    }
    
    .wpcf7-mail-sent-ok {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .wpcf7-validation-errors,
    .wpcf7-spam-blocked {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Checkbox and radio styling */
    .wpcf7-form input[type="checkbox"],
    .wpcf7-form input[type="radio"] {
        margin-right: 8px;
        transform: scale(1.2);
    }
    
    .wpcf7-form .wpcf7-list-item {
        margin-bottom: 10px;
    }
    
    .wpcf7-form .wpcf7-list-item label {
        font-weight: normal;
        margin-bottom: 0;
        cursor: pointer;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .wpcf7-form {
            padding: 8px 15px;
        }
        
        .wpcf7-form .form-group {
            margin-bottom: 10px;
        }
        
        .wpcf7-form input[type="text"],
        .wpcf7-form input[type="email"],
        .wpcf7-form input[type="tel"],
        .wpcf7-form textarea,
        .wpcf7-form select {
            font-size: 16px; /* Prevents zoom on iOS */
        }
    }
    </style>
    <?php
}
add_action('wp_head', 'contact_form_7_styling');
