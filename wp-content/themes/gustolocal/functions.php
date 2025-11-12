<?php
/**
 * GustoLocal theme functions.
 */

if ( ! defined( 'GUSTOLOCAL_VERSION' ) ) {
    define( 'GUSTOLOCAL_VERSION', '0.5.3' );
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

/* ============ WooCommerce упрощенная форма оформления ============ */
// Минимальные изменения - плагин Checkout Field Editor управляет полями
add_filter('woocommerce_checkout_fields', 'gustolocal_simplify_checkout_fields');
function gustolocal_simplify_checkout_fields($fields) {
    // Полностью скрываем shipping поля (доставка не используется)
    unset($fields['shipping']);
    
    // Удаляем только company поле (не используется)
    unset($fields['billing']['billing_company']);
    
    // ВАЖНО: Не изменяем другие поля - за них отвечает плагин Checkout Field Editor
    // Плагин сам управляет labels, required, visibility и т.д.
    
    return $fields;
}

// Устанавливаем значения по умолчанию только если они пустые (резервное заполнение)
// Плагин Checkout Field Editor управляет полями, но если значения не установлены,
// заполняем их для корректной работы платежных систем
add_filter('woocommerce_checkout_get_value', 'gustolocal_set_default_checkout_values', 10, 2);
function gustolocal_set_default_checkout_values($value, $input) {
    if (empty($value)) {
        switch ($input) {
            case 'billing_country':
                return 'ES';
            case 'billing_state':
                return 'VC';
            case 'billing_city':
                return 'Валенсия';
            case 'billing_postcode':
                return '46000';
        }
    }
    return $value;
}

// Устанавливаем значения по умолчанию ПЕРЕД обработкой заказа (критично для платежных систем)
// Только если значения не были установлены пользователем или плагином
add_action('woocommerce_checkout_process', 'gustolocal_set_checkout_defaults_before_process');
function gustolocal_set_checkout_defaults_before_process() {
    if (empty($_POST['billing_country'])) {
        $_POST['billing_country'] = 'ES';
    }
    if (empty($_POST['billing_state'])) {
        $_POST['billing_state'] = 'VC';
    }
    if (empty($_POST['billing_city'])) {
        $_POST['billing_city'] = 'Валенсия';
    }
    if (empty($_POST['billing_postcode'])) {
        $_POST['billing_postcode'] = '46000';
    }
}

// Также устанавливаем значения в заказ, если они не были установлены
add_action('woocommerce_checkout_update_order_meta', 'gustolocal_set_order_defaults', 10, 2);
function gustolocal_set_order_defaults($order_id, $data) {
    $order = wc_get_order($order_id);
    
    if (!$order->get_billing_country()) {
        $order->set_billing_country('ES');
    }
    if (!$order->get_billing_state()) {
        $order->set_billing_state('VC');
    }
    if (!$order->get_billing_city()) {
        $order->set_billing_city('Валенсия');
    }
    if (!$order->get_billing_postcode()) {
        $order->set_billing_postcode('46000');
    }
    
    $order->save();
}

/* ============ WooCommerce опции доставки ============ */
// Обработка изменения типа доставки через AJAX
add_action('wp_ajax_update_delivery_type', 'gustolocal_update_delivery_type');
add_action('wp_ajax_nopriv_update_delivery_type', 'gustolocal_update_delivery_type');
function gustolocal_update_delivery_type() {
    check_ajax_referer('gustolocal_delivery', 'nonce');
    
    $delivery_type = sanitize_text_field($_POST['delivery_type']);
    
    if (in_array($delivery_type, array('delivery', 'pickup'))) {
        WC()->session->set('delivery_type', $delivery_type);
        if (function_exists('WC') && WC()->cart) {
            WC()->cart->calculate_totals();
        }
        wp_send_json_success();
    }
    
    wp_send_json_error();
}

// Обработка изменения типа доставки при обновлении корзины
add_action('woocommerce_update_cart_action_cart_updated', 'gustolocal_update_delivery_type_on_cart_update');
function gustolocal_update_delivery_type_on_cart_update() {
    if (isset($_POST['delivery_type'])) {
        $delivery_type = sanitize_text_field($_POST['delivery_type']);
        if (in_array($delivery_type, array('delivery', 'pickup'))) {
            WC()->session->set('delivery_type', $delivery_type);
        }
    }
}

// Добавляем плату за доставку если выбрана доставка
add_action('woocommerce_cart_calculate_fees', 'gustolocal_add_delivery_fee');
function gustolocal_add_delivery_fee() {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    
    $delivery_type = WC()->session->get('delivery_type', 'delivery');
    
    // Remove previously added delivery fees to avoid duplicates
    $fees_api = WC()->cart->fees_api();
    foreach ( $fees_api->get_fees() as $key => $fee ) {
        if ( in_array( $fee->name, array( 'Доставка', 'Самовывоз' ), true ) ) {
            $fees_api->remove_fee( $fee );
        }
    }
    
    if ($delivery_type === 'delivery') {
        WC()->cart->add_fee(__('Доставка', 'woocommerce'), 10.00, false);
    } else {
        // Отображаем строку «Самовывоз» с нулевой стоимостью
        WC()->cart->add_fee(__('Самовывоз', 'woocommerce'), 0, false);
    }
}

// Подключаем JavaScript для обработки опций доставки
add_action('wp_enqueue_scripts', 'gustolocal_enqueue_delivery_scripts');
function gustolocal_enqueue_delivery_scripts() {
    if (is_cart() || is_checkout()) {
        wp_enqueue_script(
            'gustolocal-delivery',
            get_template_directory_uri() . '/assets/js/delivery-options.js',
            array('jquery'),
            GUSTOLOCAL_VERSION,
            true
        );
        
        wp_localize_script('gustolocal-delivery', 'gustolocal_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gustolocal_delivery'),
        ));
    }
}

/* ============ Переводы WooCommerce на русский ============ */
// Принудительно устанавливаем русский язык для WooCommerce
add_filter('plugin_locale', 'gustolocal_force_woocommerce_locale', 10, 2);
function gustolocal_force_woocommerce_locale($locale, $domain) {
    if ($domain === 'woocommerce') {
        return 'ru_RU';
    }
    return $locale;
}

// Переводим основные строки WooCommerce
add_filter('gettext', 'gustolocal_translate_woocommerce_strings', 20, 3);
function gustolocal_translate_woocommerce_strings($translated_text, $text, $domain) {
    if ($domain !== 'woocommerce' || is_admin()) {
        return $translated_text;
    }
    
    $translations = array(
        'Product' => 'Товар',
        'Price' => 'Цена',
        'Quantity' => 'Количество',
        'Subtotal' => 'Подытог',
        'Total' => 'Итого',
        'Update cart' => 'Обновить корзину',
        'Coupon code' => 'Код купона',
        'Apply coupon' => 'Применить купон',
        'Your order' => 'Ваш заказ',
        'Place order' => 'Оформить заказ',
        'Checkout' => 'Оформление заказа',
        'Cart' => 'Корзина',
        'Remove item' => 'Удалить товар',
        'Order total' => 'Сумма заказов',
        'Cart totals' => 'Сумма заказа',
        'Proceed to checkout' => 'Перейти к оформлению',
        'Update cart' => 'Обновить корзину',
        'Delivery' => 'Доставка',
        'Coupon code applied successfully.' => 'Купон успешно применён.',
        'Coupon removed.' => 'Купон удалён.',
        'Coupon code removed successfully.' => 'Купон удалён.',
        'Coupon:' => 'Купон:',
        'Your cart is currently empty.' => 'Ваша корзина пуста.',
        'Return to shop' => 'Вернуться в магазин',
        'Remove' => 'Удалить',
    );
    
    if (isset($translations[$text])) {
        return $translations[$text];
    }
    
    return $translated_text;
}

add_filter('woocommerce_return_to_shop_redirect', function() {
    return home_url('/');
});

// Принудительно использовать правильный header для checkout
add_filter('render_block_core/template-part', function($block_content, $block) {
    if (is_checkout() && isset($block['attrs']['slug']) && $block['attrs']['slug'] === 'header') {
        // Загружаем правильный header из файла
        $header_file = get_template_directory() . '/parts/header.html';
        if (file_exists($header_file)) {
            $header_content = file_get_contents($header_file);
            // Рендерим блоки из файла
            $parsed_blocks = parse_blocks($header_content);
            if (!empty($parsed_blocks)) {
                $rendered = '';
                foreach ($parsed_blocks as $parsed_block) {
                    $rendered .= render_block($parsed_block);
                }
                return $rendered;
            }
        }
    }
    return $block_content;
}, 999, 2);

// Альтернативный подход: перехватываем через get_block_template
add_filter('get_block_template', function($template, $id, $template_type) {
    if ($template_type === 'wp_template_part' && is_checkout()) {
        if (($id === 'gustolocal//header' || $id === 'header') && isset($template->slug) && $template->slug === 'header') {
            $header_file = get_template_directory() . '/parts/header.html';
            if (file_exists($header_file)) {
                $template->content = file_get_contents($header_file);
            }
        }
    }
    return $template;
}, 999, 3);
