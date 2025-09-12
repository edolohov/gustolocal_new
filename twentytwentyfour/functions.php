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
