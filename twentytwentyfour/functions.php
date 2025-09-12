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
				/*
				 * Styles variation for post terms
				 * https://github.com/WordPress/gutenberg/issues/24956
				 */
				'inline_style' => '
				.is-style-pill a,
				.is-style-pill span:not([class], [data-rich-text-placeholder]) {
					display: inline-block;
					background-color: var(--wp--preset--color--base-2);
					padding: 0.375rem 0.875rem;
					border-radius: var(--wp--preset--spacing--20);
				}

				.is-style-pill a:hover {
					background-color: var(--wp--preset--color--contrast-3);
				}',
			)
		);
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfour' ),
				/*
				 * Styles for the custom checkmark list block style
				 * https://github.com/WordPress/gutenberg/issues/51480
				 */
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
		register_block_style(
			'core/navigation-link',
			array(
				'name'         => 'arrow-link',
				'label'        => __( 'With arrow', 'twentytwentyfour' ),
				/*
				 * Styles for the custom arrow nav link block style
				 */
				'inline_style' => '
				.is-style-arrow-link .wp-block-navigation-item__label:after {
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
 * WooCommerce customizations
 */

// Remove Stripe payment buttons from cart page
function remove_stripe_buttons_from_cart() {
    if ( is_cart() ) {
        // Remove Stripe Express Checkout buttons from cart
        remove_action( 'woocommerce_cart_actions', 'woocommerce_cart_totals', 10 );
        
        // Remove any Stripe buttons that might be added via hooks
        remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
        
        // Remove Stripe Express Checkout buttons
        if ( class_exists( 'WC_Stripe_Express_Checkout_Button_Handler' ) ) {
            remove_action( 'woocommerce_cart_actions', array( WC_Stripe_Express_Checkout_Button_Handler::instance(), 'display_cart_page_express_checkout_buttons' ), 10 );
        }
        
        // Ensure proceed to checkout button is added
        add_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
    }
}
add_action( 'wp', 'remove_stripe_buttons_from_cart' );

// Ensure Stripe buttons only appear on checkout page
function ensure_stripe_buttons_only_on_checkout() {
    if ( ! is_checkout() ) {
        // Remove all Stripe payment buttons from non-checkout pages
        remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
        
        // Remove Stripe Express Checkout buttons from cart
        if ( class_exists( 'WC_Stripe_Express_Checkout_Button_Handler' ) ) {
            remove_action( 'woocommerce_cart_actions', array( WC_Stripe_Express_Checkout_Button_Handler::instance(), 'display_cart_page_express_checkout_buttons' ), 10 );
            remove_action( 'woocommerce_cart_collaterals', array( WC_Stripe_Express_Checkout_Button_Handler::instance(), 'display_cart_page_express_checkout_buttons' ), 10 );
        }
    }
}
add_action( 'wp', 'ensure_stripe_buttons_only_on_checkout' );

// Add CSS to hide Stripe buttons on cart page
function hide_stripe_buttons_on_cart_css() {
    if ( is_cart() ) {
        ?>
        <style>
        /* Hide Stripe payment buttons on cart page */
        .wc-stripe-express-checkout-buttons,
        .stripe-express-checkout-buttons,
        .woocommerce-checkout-payment,
        .payment_method_stripe,
        .stripe-payment-button,
        .apple-pay-button,
        .google-pay-button,
        .link-payment-button,
        .wc-stripe-payment-request-wrapper,
        .wc-stripe-payment-request-button-separator {
            display: none !important;
        }
        
        /* Ensure "Proceed to checkout" button is visible */
        .wc-proceed-to-checkout,
        .checkout-button,
        .woocommerce-checkout-button {
            display: block !important;
        }
        
        /* Cart table improvements */
        .woocommerce-cart-form__contents .product-name {
            width: 50% !important;
        }
        
        .woocommerce-cart-form__contents .product-thumbnail {
            display: none !important;
        }
        
        /* Make product name column wider */
        .woocommerce-cart-form__contents td.product-name {
            width: 50% !important;
            max-width: none !important;
        }
        
        /* Remove colons from cart item data */
        .woocommerce-cart-form__contents .cart_item .variation dt,
        .woocommerce-cart-form__contents .cart_item .variation dd {
            display: inline !important;
        }
        
        .woocommerce-cart-form__contents .cart_item .variation dt:after {
            content: '' !important;
        }
        
        /* Hide page title - target specific classes */
        .woocommerce-cart .entry-title,
        .woocommerce-cart h1.entry-title,
        .woocommerce-cart .page-title,
        .woocommerce-cart h1.page-title,
        .woocommerce-cart .woocommerce-cart-form h2,
        .woocommerce-cart .woocommerce-cart-form h1,
        .woocommerce-cart .woocommerce-cart-form .entry-title,
        .woocommerce-cart h1.alignwide.wp-block-post-title,
        .woocommerce-cart h1.wp-block-post-title,
        .woocommerce-cart h1.alignwide,
        .woocommerce-cart .wp-block-post-title,
        .woocommerce-cart h1 {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 0 !important;
            line-height: 0 !important;
        }
        
        /* Make cart table full width */
        .woocommerce-cart .woocommerce {
            max-width: 100% !important;
            width: 100% !important;
        }
        
        .woocommerce-cart .woocommerce-cart-form {
            width: 100% !important;
        }
        
        /* Fix column headers - prevent wrapping */
        .woocommerce-cart-form__contents th {
            white-space: nowrap !important;
            font-size: 14px !important;
            padding: 12px 8px !important;
        }
        
        /* Adjust column widths - desktop */
        .woocommerce-cart-form__contents .product-name {
            width: 52% !important;
        }
        
        .woocommerce-cart-form__contents .product-price {
            width: 15% !important;
        }
        
        .woocommerce-cart-form__contents .product-quantity {
            width: 15% !important;
        }
        
        .woocommerce-cart-form__contents .product-subtotal {
            width: 15% !important;
        }
        
        .woocommerce-cart-form__contents .product-remove {
            width: 3% !important;
        }
        
        /* Remove unnecessary borders on desktop */
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
        
        /* Better product name display on desktop */
        .woocommerce-cart-form__contents .product-name {
            line-height: 1.4 !important;
        }
        
        .woocommerce-cart-form__contents .product-name .variation {
            margin-top: 8px !important;
        }
        
        .woocommerce-cart-form__contents .product-name .variation dd {
            margin: 0 !important;
            padding: 0 !important;
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
                margin-bottom: 10px !important;
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
                max-width: 100% !important;
                text-align: center !important;
                display: block !important;
                margin: 0 auto !important;
                padding: 12px 24px !important;
                box-sizing: border-box !important;
            }
            
            /* Remove borders between cart sections */
            .woocommerce-cart .woocommerce-cart-form__contents {
                border: none !important;
            }
            
            .woocommerce-cart .woocommerce-cart-form__contents tr {
                border-bottom: none !important;
            }
            
            .woocommerce-cart .woocommerce-cart-form__contents td {
                border: none !important;
            }
            
            /* Make product name take full width */
            .woocommerce-cart-form__contents .product-name {
                width: 100% !important;
                padding-right: 40px !important;
                margin-bottom: 15px !important;
                display: block !important;
                position: relative !important;
            }
            
            .woocommerce-cart-form__contents .product-name br {
                display: none !important;
            }
            
            /* Better spacing for product details - make them full width */
            .woocommerce-cart-form__contents .product-name .variation {
                margin-top: 8px !important;
                width: 100% !important;
                display: block !important;
                float: none !important;
                clear: both !important;
            }
            
            .woocommerce-cart-form__contents .product-name .variation dd {
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                word-wrap: break-word !important;
                white-space: normal !important;
                float: none !important;
                clear: both !important;
            }
            
            /* Force product details to use full width */
            .woocommerce-cart-form__contents .product-name .variation dd p {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                float: none !important;
                clear: both !important;
            }
            
            /* Override any WooCommerce table cell constraints */
            .woocommerce-cart-form__contents .product-name {
                table-layout: fixed !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            
            /* Force all text content to use full width */
            .woocommerce-cart-form__contents .product-name * {
                max-width: 100% !important;
                word-wrap: break-word !important;
                white-space: normal !important;
            }
        }
        
        /* Force remove colons with JavaScript */
        .woocommerce-cart-form__contents .cart_item .variation dt:after {
            content: '' !important;
            display: none !important;
        }
        
        /* Hide any remaining colons */
        .woocommerce-cart-form__contents .cart_item .variation dt {
            display: none !important;
        }
        
        .woocommerce-cart-form__contents .cart_item .variation dd {
            display: block !important;
            margin: 0 !important;
        }
        </style>
        <?php
    }
}
add_action( 'wp_head', 'hide_stripe_buttons_on_cart_css' );

// Add JavaScript to remove colons and page title
function remove_cart_colons_and_title() {
    if ( is_cart() ) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Remove colons from cart item data
            var cartItems = document.querySelectorAll('.woocommerce-cart-form__contents .cart_item .variation');
            cartItems.forEach(function(item) {
                var dt = item.querySelector('dt');
                var dd = item.querySelector('dd');
                if (dt && dd) {
                    // Remove the dt element completely
                    dt.remove();
                    // Make dd display as block
                    dd.style.display = 'block';
                    dd.style.margin = '0';
                }
            });
            
            // Remove page title - try multiple selectors
            var selectors = [
                '.woocommerce-cart .entry-title',
                '.woocommerce-cart h1.entry-title', 
                '.woocommerce-cart .page-title',
                '.woocommerce-cart h1.alignwide.wp-block-post-title',
                '.woocommerce-cart h1.wp-block-post-title',
                '.woocommerce-cart h1.alignwide',
                '.woocommerce-cart .wp-block-post-title',
                '.woocommerce-cart h1'
            ];
            
            selectors.forEach(function(selector) {
                var element = document.querySelector(selector);
                if (element) {
                    element.remove();
                }
            });
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'remove_cart_colons_and_title' );

// Universal function to hide page titles on WooCommerce pages
function hide_woocommerce_page_titles() {
    if ( is_cart() || is_checkout() || is_wc_endpoint_url() ) {
        ?>
        <style>
        /* Hide page titles on all WooCommerce pages */
        .woocommerce .entry-title,
        .woocommerce h1.entry-title,
        .woocommerce .page-title,
        .woocommerce h1.page-title,
        .woocommerce h1.alignwide.wp-block-post-title,
        .woocommerce h1.wp-block-post-title,
        .woocommerce h1.alignwide,
        .woocommerce .wp-block-post-title,
        .woocommerce h1,
        .woocommerce h2.entry-title,
        .woocommerce h2.page-title {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 0 !important;
            line-height: 0 !important;
        }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Remove page titles on WooCommerce pages
            var selectors = [
                '.woocommerce .entry-title',
                '.woocommerce h1.entry-title', 
                '.woocommerce .page-title',
                '.woocommerce h1.alignwide.wp-block-post-title',
                '.woocommerce h1.wp-block-post-title',
                '.woocommerce h1.alignwide',
                '.woocommerce .wp-block-post-title',
                '.woocommerce h1',
                '.woocommerce h2.entry-title',
                '.woocommerce h2.page-title'
            ];
            
            selectors.forEach(function(selector) {
                var elements = document.querySelectorAll(selector);
                elements.forEach(function(element) {
                    element.remove();
                });
            });
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'hide_woocommerce_page_titles' );

// Force add proceed to checkout button on cart page
function force_proceed_to_checkout_button() {
    if ( is_cart() ) {
        ?>
        <div class="wc-proceed-to-checkout" style="margin-top: 20px;">
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button button alt wc-forward" style="display: inline-block; padding: 12px 24px; background-color: #6a5eb7; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                <?php esc_html_e( 'Proceed to checkout', 'woocommerce' ); ?>
            </a>
        </div>
        <?php
    }
}
add_action( 'woocommerce_after_cart', 'force_proceed_to_checkout_button' );

// Remove delivery information from cart item data
function remove_delivery_from_cart_item_data( $item_data, $cart_item ) {
    // Remove all delivery-related information
    if ( isset( $item_data['delivery'] ) ) {
        unset( $item_data['delivery'] );
    }
    return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'remove_delivery_from_cart_item_data', 10, 2 );

// ===== CHECKOUT PAGE FIXES =====

// Move Stripe buttons to bottom of checkout page
function move_stripe_buttons_to_bottom() {
    if ( is_checkout() ) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find Stripe buttons at the top
            var stripeButtons = document.querySelectorAll('.wc-stripe-express-checkout-buttons, .stripe-express-checkout-buttons, .woocommerce-checkout-payment .stripe-express-checkout-buttons');
            
            stripeButtons.forEach(function(buttonContainer) {
                if (buttonContainer) {
                    // Find the payment method section
                    var paymentSection = document.querySelector('.woocommerce-checkout-payment');
                    if (paymentSection) {
                        // Move buttons to the top of payment section
                        paymentSection.insertBefore(buttonContainer, paymentSection.firstChild);
                    }
                }
            });
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'move_stripe_buttons_to_bottom' );

// Remove colons from checkout order details
function remove_checkout_colons() {
    if ( is_checkout() ) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Remove colons from order details
            var orderDetails = document.querySelectorAll('.woocommerce-checkout-review-order-table .variation dt');
            orderDetails.forEach(function(dt) {
                if (dt.textContent.includes(':')) {
                    dt.textContent = dt.textContent.replace(':', '');
                }
            });
            
            // Also remove colons from cart item data
            var cartItemData = document.querySelectorAll('.woocommerce-checkout-review-order-table .cart_item .variation dt');
            cartItemData.forEach(function(dt) {
                if (dt.textContent.includes(':')) {
                    dt.textContent = dt.textContent.replace(':', '');
                }
            });
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'remove_checkout_colons' );

// Add checkout page styling
function add_checkout_styling() {
    if ( is_checkout() ) {
        ?>
        <style>
        /* Hide Stripe buttons at the top of checkout */
        .woocommerce-checkout .woocommerce-before-checkout-form .wc-stripe-express-checkout-buttons,
        .woocommerce-checkout .woocommerce-before-checkout-form .stripe-express-checkout-buttons {
            display: none !important;
        }
        
        /* Show Stripe buttons only in payment section */
        .woocommerce-checkout-payment .wc-stripe-express-checkout-buttons,
        .woocommerce-checkout-payment .stripe-express-checkout-buttons {
            display: block !important;
            margin-bottom: 20px !important;
            padding: 15px !important;
            background: #f9f9f9 !important;
            border: 1px solid #ddd !important;
            border-radius: 5px !important;
        }
        
        /* Style the payment section better */
        .woocommerce-checkout-payment {
            background: #f9f9f9 !important;
            padding: 20px !important;
            border-radius: 8px !important;
            margin-top: 20px !important;
        }
        
        /* Remove colons from order details */
        .woocommerce-checkout-review-order-table .variation dt:after {
            content: '' !important;
        }
        
        .woocommerce-checkout-review-order-table .variation dt {
            display: none !important;
        }
        
        .woocommerce-checkout-review-order-table .variation dd {
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Ensure header is visible on checkout */
        .woocommerce-checkout-wrapper {
            margin-top: 0 !important;
        }
        
        /* Make sure checkout page has proper spacing */
        .woocommerce-checkout .entry-content {
            margin-top: 0 !important;
        }
        </style>
        <?php
    }
}
add_action( 'wp_head', 'add_checkout_styling' );

// Force header to show on checkout page
function force_header_on_checkout() {
    if ( is_checkout() ) {
        // Remove any actions that might hide the header
        remove_action( 'wp_head', 'hide_woocommerce_page_titles' );
        
        // Ensure header is loaded
        if ( ! did_action( 'get_header' ) ) {
            get_header();
        }
    }
}
add_action( 'woocommerce_before_checkout_form', 'force_header_on_checkout', 1 );
