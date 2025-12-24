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
    wp_enqueue_script( 'gustolocal-tooltip', $theme_dir . '/assets/js/tooltip.js', [], GUSTOLOCAL_VERSION, true );
    
    // Load gallery script only on rico page
    if ( is_page( 'rico' ) || ( is_page() && get_post_field( 'post_name' ) === 'rico' ) ) {
        wp_enqueue_script( 'gustolocal-rico-gallery', $theme_dir . '/assets/js/rico-gallery.js', [], GUSTOLOCAL_VERSION, true );
    }
} );

// Inline стили для формы Contact Form 7 - принудительное применение
add_action( 'wp_head', function () {
    ?>
    <style id="gustolocal-form-fix">
    /* Принудительные стили для формы Contact Form 7 */
    .gl-form-grid,
    .wpcf7-form .gl-form-grid {
        display: grid !important;
        gap: 0.5rem !important;
        margin-bottom: 0.75rem !important;
    }
    .gl-form-grid-single {
        grid-template-columns: 1fr !important;
    }
    .gl-form-group,
    .wpcf7-form .gl-form-group {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.2rem !important;
        margin-bottom: 0 !important;
    }
    .gl-form-grid-single .gl-form-group + .gl-form-group {
        margin-top: 0.3rem !important;
    }
    .gl-form-group p,
    .gl-form-actions p,
    .wpcf7-form p {
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1.2 !important;
    }
    .gl-form-group br,
    .wpcf7-form br {
        display: none !important;
        line-height: 0 !important;
        height: 0 !important;
    }
    .gl-form-group .wpcf7-form-control-wrap {
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .gl-form-group label {
        margin-bottom: 0.2rem !important;
        display: block !important;
    }
    .gl-form-group .wpcf7-form-control,
    .gl-form-group input,
    .gl-form-group textarea {
        width: 100% !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }
    .gl-form-actions {
        display: flex !important;
        justify-content: center !important;
        text-align: center !important;
        margin-top: 0.75rem !important;
    }
    .gl-form-actions p {
        margin: 0 !important;
        padding: 0 !important;
    }
    .gl-form-actions .gl-button--primary,
    .gl-form-actions input[type="submit"],
    .gl-form-actions .wpcf7-submit {
        background: rgb(216, 228, 160) !important;
        color: #1a1a1a !important;
    }
    .gl-form-actions .gl-button--primary:hover,
    .gl-form-actions input[type="submit"]:hover,
    .gl-form-actions .wpcf7-submit:hover {
        background: rgb(23, 129, 94) !important;
        color: #fff !important;
    }
    </style>
    <?php
}, 999 );

add_action( 'enqueue_block_editor_assets', function () {
    $theme_dir = get_template_directory_uri();
    wp_enqueue_style( 'gustolocal-editor', $theme_dir . '/style.css', [], GUSTOLOCAL_VERSION );
} );

/* ============ Автоматическое создание таблиц WooCommerce ============ */
// Проверяем и создаем недостающие таблицы WooCommerce
add_action('woocommerce_loaded', 'gustolocal_check_wc_tables', 20);
function gustolocal_check_wc_tables() {
    // Проверяем только если WooCommerce активен
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Загружаем класс установки WooCommerce, если он еще не загружен
    if (!class_exists('WC_Install')) {
        $wc_install_file = WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-install.php';
        if (file_exists($wc_install_file)) {
            require_once($wc_install_file);
        } else {
            return; // Не можем создать таблицы без класса установки
        }
    }
    
    global $wpdb;
    $table_prefix = $wpdb->prefix;
    
    // Проверяем наличие критических таблиц
    $critical_tables = array(
        'wc_orders_meta',
        'wc_order_addresses',
    );
    
    $missing_tables = array();
    foreach ($critical_tables as $table) {
        $full_table_name = $table_prefix . $table;
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $full_table_name));
        if (!$exists) {
            $missing_tables[] = $table;
        }
    }
    
    // Если есть недостающие таблицы, создаем их
    if (!empty($missing_tables) && class_exists('WC_Install') && method_exists('WC_Install', 'create_tables')) {
        try {
            // Запускаем создание таблиц WooCommerce
            WC_Install::create_tables();
            
            // Логируем для отладки
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('GustoLocal: Созданы недостающие таблицы WooCommerce: ' . implode(', ', $missing_tables));
            }
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('GustoLocal: Ошибка при создании таблиц WooCommerce: ' . $e->getMessage());
            }
        }
    }
}

/* ============ Контент главной страницы ============ */
// Если страница "Главная" пустая, заполняем ее шаблоном-паттерном
add_action('init', 'gustolocal_seed_front_page_content', 30);
function gustolocal_seed_front_page_content() {
    if (is_admin() && !wp_doing_ajax()) {
        // В админке не вмешиваемся, чтобы не мешать редактированию
        return;
    }
    
    $already_seeded = get_option('gustolocal_front_page_seeded', false);
    $front_page_id = (int) get_option('page_on_front');
    if (!$front_page_id) {
        return;
    }
    
    $front_page = get_post($front_page_id);
    if (!$front_page || 'page' !== $front_page->post_type) {
        return;
    }
    
    // Если на странице уже есть контент, фиксируем флаг и выходим
    if (!empty(trim($front_page->post_content))) {
        if (!$already_seeded) {
            update_option('gustolocal_front_page_seeded', 1);
        }
        return;
    }
    
    if ($already_seeded) {
        // Контент пустой, но мы уже заполняли раньше — не перезаписываем
        return;
    }
    
    if (!class_exists('WP_Block_Patterns_Registry')) {
        return;
    }
    
    $registry = WP_Block_Patterns_Registry::get_instance();
    if (!$registry->is_registered('gustolocal/homepage')) {
        return;
    }
    
    $pattern = $registry->get_registered('gustolocal/homepage');
    if (empty($pattern['content'])) {
        return;
    }
    
    wp_update_post(array(
        'ID' => $front_page_id,
        'post_content' => $pattern['content'],
    ));
    
    update_option('gustolocal_front_page_seeded', 1);
}

// Фолбэк: если по какой-то причине контент пустой, показываем паттерн на фронте
add_filter('render_block', 'gustolocal_front_page_fallback_pattern', 20, 2);
function gustolocal_front_page_fallback_pattern($block_content, $block) {
    if (!is_front_page()) {
        return $block_content;
    }
    
    if ($block['blockName'] !== 'core/post-content') {
        return $block_content;
    }
    
    if (trim($block_content) !== '') {
        return $block_content;
    }
    
    $pattern_content = gustolocal_get_pattern_content('gustolocal/homepage');

    return $pattern_content ? $pattern_content : $block_content;
}

// Утилита для заполнения страниц контентом из паттернов (одноразово)
add_action('init', 'gustolocal_register_theme_patterns', 9);
function gustolocal_register_theme_patterns() {
    if (!function_exists('register_block_pattern')) {
        return;
    }

    $patterns_dir = get_theme_file_path('patterns');
    if (!is_dir($patterns_dir)) {
        return;
    }

    $files = glob($patterns_dir . '/*.php');
    if (!$files) {
        return;
    }

    foreach ($files as $file_path) {
        $data = get_file_data($file_path, array(
            'title'      => 'Title',
            'slug'       => 'Slug',
            'categories' => 'Categories',
        ));

        $slug = !empty($data['slug']) ? trim($data['slug']) : 'gustolocal/' . basename($file_path, '.php');
        $title = !empty($data['title']) ? $data['title'] : ucwords(str_replace('-', ' ', basename($file_path, '.php')));
        $categories = array();

        if (!empty($data['categories'])) {
            $categories = array_map('trim', explode(',', $data['categories']));
        }

        if (empty($categories)) {
            $categories = array('featured');
        }

        $content = gustolocal_load_pattern_file($file_path);
        if (empty($content)) {
            continue;
        }

        register_block_pattern($slug, array(
            'title'      => $title,
            'categories' => $categories,
            'content'    => $content,
        ));
    }
}

add_action('wp_loaded', 'gustolocal_seed_static_pages');
function gustolocal_seed_static_pages() {
    $pages = array(
        array(
            'slug'        => 'como-preparamos',
            'pattern'     => 'gustolocal/como-preparamos',
            'option_name' => 'gustolocal_seed_como_preparamos',
        ),
        array(
            'slug'        => 'test',
            'pattern'     => 'gustolocal/test-page',
            'option_name' => 'gustolocal_seed_test',
        ),
        array(
            'slug'        => 'custom',
            'pattern'     => 'gustolocal/custom-page',
            'option_name' => 'gustolocal_seed_custom',
        ),
        array(
            'slug'        => 'pan-sandwiches-valencia',
            'pattern'     => 'gustolocal/pan-sandwiches',
            'option_name' => 'gustolocal_seed_pan_sandwiches',
        ),
        array(
            'slug'        => 'corporate-meals',
            'pattern'     => 'gustolocal/corporate-meals',
            'option_name' => 'gustolocal_seed_corporate_meals',
        ),
        array(
            'slug'        => 'catering',
            'pattern'     => 'gustolocal/catering',
            'option_name' => 'gustolocal_seed_catering',
        ),
    );
    
    $registry = WP_Block_Patterns_Registry::get_instance();
    
    foreach ($pages as $item) {
        $option_name  = $item['option_name'];
        $option_value = get_option($option_name);
        
        $page = get_page_by_path($item['slug']);
        if (!$page) {
            continue;
        }
        
        $has_blocks = strpos($page->post_content, '<!-- wp:') !== false;

        if ($option_value === 'done' && $has_blocks) {
            continue;
        }

        if (!empty(trim($page->post_content))) {
            // Если контент уже заполнен блоками, пропускаем
            if ($has_blocks) {
                update_option($option_name, 'done');
                continue;
            }
        }
        
        $pattern_content = gustolocal_get_pattern_content($item['pattern']);
        if (!$pattern_content) {
            continue;
        }
        
        wp_update_post(array(
            'ID'           => $page->ID,
            'post_content' => $pattern_content,
        ));
        
        update_option($option_name, 'done');
    }
}

function gustolocal_get_pattern_content($pattern_slug) {
    $content = '';

    if (class_exists('WP_Block_Patterns_Registry')) {
        $registry = WP_Block_Patterns_Registry::get_instance();
        if ($registry->is_registered($pattern_slug)) {
            $pattern = $registry->get_registered($pattern_slug);
            if (!empty($pattern['content'])) {
                $content = $pattern['content'];
            }
        }
    }

    if ($content) {
        return $content;
    }

    $parts = explode('/', $pattern_slug);
    $file  = end($parts);
    $path  = get_theme_file_path('patterns/' . $file . '.php');

    return gustolocal_load_pattern_file($path);
}

function gustolocal_load_pattern_file($path) {
    if (!file_exists($path)) {
        return '';
    }
    ob_start();
    include $path;
    return trim(ob_get_clean());
}

/* ============ WooCommerce упрощенная форма оформления ============ */
// Упрощаем форму чекаута - оставляем только необходимые поля
// Приоритет 999 - выполняется ПОСЛЕ плагина Checkout Field Editor, чтобы не переопределять его настройки
add_filter('woocommerce_checkout_fields', 'gustolocal_simplify_checkout_fields', 999);
function gustolocal_simplify_checkout_fields($fields) {
    // Полностью скрываем shipping поля (доставка не используется)
    unset($fields['shipping']);
    
    // Удаляем ненужные поля
    unset($fields['billing']['billing_company']);
    
    // Скрываем поля, которые заполняются автоматически (делаем необязательными и скрываем через CSS)
    if (isset($fields['billing']['billing_country'])) {
        $fields['billing']['billing_country']['required'] = false;
        $fields['billing']['billing_country']['class'][] = 'hidden-field';
        $fields['billing']['billing_country']['validate'] = array(); // Убираем валидацию
    }
    
    if (isset($fields['billing']['billing_state'])) {
        $fields['billing']['billing_state']['required'] = false;
        $fields['billing']['billing_state']['class'][] = 'hidden-field';
        $fields['billing']['billing_state']['validate'] = array(); // Убираем валидацию
    }
    
    if (isset($fields['billing']['billing_city'])) {
        $fields['billing']['billing_city']['required'] = false;
        $fields['billing']['billing_city']['class'][] = 'hidden-field';
        $fields['billing']['billing_city']['validate'] = array(); // Убираем валидацию
    }
    
    if (isset($fields['billing']['billing_postcode'])) {
        $fields['billing']['billing_postcode']['required'] = false;
        $fields['billing']['billing_postcode']['class'][] = 'hidden-field';
        $fields['billing']['billing_postcode']['validate'] = array(); // Убираем валидацию
    }
    
    // Настраиваем видимые поля согласно дизайну
    if (isset($fields['billing']['billing_first_name'])) {
        $fields['billing']['billing_first_name']['label'] = 'Ваше имя';
        $fields['billing']['billing_first_name']['required'] = true;
        $fields['billing']['billing_first_name']['placeholder'] = '';
        $fields['billing']['billing_first_name']['priority'] = 10;
        $fields['billing']['billing_first_name']['class'] = array('form-row-first');
    }
    
    if (isset($fields['billing']['billing_last_name'])) {
        $fields['billing']['billing_last_name']['label'] = 'и фамилия';
        $fields['billing']['billing_last_name']['required'] = true;
        $fields['billing']['billing_last_name']['placeholder'] = '';
        $fields['billing']['billing_last_name']['priority'] = 20;
        $fields['billing']['billing_last_name']['class'] = array('form-row-last');
    }
    
    if (isset($fields['billing']['billing_address_1'])) {
        // Не переопределяем label и placeholder - пусть плагин Checkout Field Editor управляет этим
        // $fields['billing']['billing_address_1']['label'] = 'Адрес';
        // $fields['billing']['billing_address_1']['placeholder'] = 'Номер дома и название улицы';
        $fields['billing']['billing_address_1']['priority'] = 30;
    }
    
    if (isset($fields['billing']['billing_email'])) {
        // Не переопределяем required, label и placeholder - пусть плагин Checkout Field Editor управляет этим
        // $fields['billing']['billing_email']['label'] = 'Ваш e-mail';
        // $fields['billing']['billing_email']['required'] = false;
        // $fields['billing']['billing_email']['placeholder'] = '';
        $fields['billing']['billing_email']['priority'] = 40;
    }
    
    if (isset($fields['billing']['billing_address_2'])) {
        // Не переопределяем required, label и placeholder - пусть плагин Checkout Field Editor управляет этим
        // $fields['billing']['billing_address_2']['required'] = false;
        // $fields['billing']['billing_address_2']['label'] = 'Как к вам попасть';
        // $fields['billing']['billing_address_2']['placeholder'] = 'укажите домофон, этаж и квартиру';
        $fields['billing']['billing_address_2']['priority'] = 50;
    }
    
    if (isset($fields['billing']['billing_phone'])) {
        // Не переопределяем required, label и placeholder - пусть плагин Checkout Field Editor управляет этим
        // $fields['billing']['billing_phone']['required'] = false;
        // $fields['billing']['billing_phone']['label'] = 'Как с вами связаться';
        // $fields['billing']['billing_phone']['placeholder'] = 'телеграм, whatsApp, телефон или факс';
        $fields['billing']['billing_phone']['priority'] = 60;
    }
    
    return $fields;
}

// Отключаем обязательную валидацию email, так как поле необязательное
// ОТКЛЮЧЕНО: пусть плагин Checkout Field Editor управляет этим
// add_filter('woocommerce_checkout_fields', 'gustolocal_make_email_optional', 20);
// function gustolocal_make_email_optional($fields) {
//     if (isset($fields['billing']['billing_email'])) {
//         $fields['billing']['billing_email']['required'] = false;
//         $fields['billing']['billing_email']['validate'] = array('email'); // Валидация формата, но не обязательность
//     }
//     return $fields;
// }

// Отключаем валидацию скрытых полей (провинция, страна, город, почтовый индекс)
add_action('woocommerce_checkout_process', 'gustolocal_disable_hidden_fields_validation', 1);
function gustolocal_disable_hidden_fields_validation() {
    // Устанавливаем значения по умолчанию для скрытых полей перед валидацией
    if (empty($_POST['billing_country']) || !isset($_POST['billing_country'])) {
        $_POST['billing_country'] = 'ES';
    }
    if (empty($_POST['billing_state']) || !isset($_POST['billing_state'])) {
        $_POST['billing_state'] = 'VC';
    }
    if (empty($_POST['billing_city']) || !isset($_POST['billing_city'])) {
        $_POST['billing_city'] = 'Валенсия';
    }
    if (empty($_POST['billing_postcode']) || !isset($_POST['billing_postcode'])) {
        $_POST['billing_postcode'] = '46000';
    }
}

// Удаляем ошибки валидации для скрытых полей
add_filter('woocommerce_checkout_fields', 'gustolocal_remove_hidden_fields_errors', 999);
function gustolocal_remove_hidden_fields_errors($fields) {
    // Удаляем ошибки валидации для скрытых полей
    $hidden_fields = array('billing_country', 'billing_state', 'billing_city', 'billing_postcode');
    foreach ($hidden_fields as $field_key) {
        if (isset($fields['billing'][$field_key])) {
            // Убираем все валидации
            $fields['billing'][$field_key]['validate'] = array();
            $fields['billing'][$field_key]['required'] = false;
        }
    }
    return $fields;
}

// Удаляем уведомления об ошибках для скрытых полей
add_action('woocommerce_after_checkout_validation', 'gustolocal_remove_hidden_fields_notices', 10, 2);
function gustolocal_remove_hidden_fields_notices($data, $errors) {
    $hidden_fields = array('billing_country', 'billing_state', 'billing_city', 'billing_postcode');
    foreach ($hidden_fields as $field_key) {
        if ($errors->get_error_message($field_key)) {
            $errors->remove($field_key);
        }
    }
}

// ИСПРАВЛЕНИЕ: Переопределяем перевод строки "Billing" на пустую строку только для страницы чекаута
// WooCommerce использует __() для перевода "Billing" и добавляет его к сообщению об ошибке
add_filter('gettext', 'gustolocal_remove_billing_translation', 999, 3);
add_filter('ngettext', 'gustolocal_remove_billing_translation', 999, 5);
function gustolocal_remove_billing_translation($translated_text, $text, $domain = '', $number = null) {
    // Проверяем, что это перевод строки "Billing" из WooCommerce
    // И проверяем, что мы на странице чекаута (чтобы не сломать другие страницы)
    if (!is_admin() && is_checkout() && $domain === 'woocommerce' && strtolower(trim($text)) === 'billing') {
        // Возвращаем пустую строку вместо "Выставление счета"
        return '';
    }
    return $translated_text;
}

// Более простой и надежный способ: перехватываем формирование сообщения об ошибке
// WooCommerce формирует сообщение как: sprintf('%s %s', $section_label, $field_label . ' is a required field')
// Перехватываем это через фильтр woocommerce_checkout_field_error_message
add_filter('woocommerce_checkout_field_error_message', 'gustolocal_fix_billing_error_message', 1, 2);
function gustolocal_fix_billing_error_message($message, $field_key) {
    // Поля, для которых нужно убрать префикс
    $billing_fields = array('billing_first_name', 'billing_last_name', 'billing_phone');
    
    if (in_array($field_key, $billing_fields)) {
        // Убираем префикс "Выставление счета" или "Выставление счёта" из начала сообщения
        $cleaned_message = preg_replace('/^Выставление\s+счета\s+/iu', '', $message);
        $cleaned_message = preg_replace('/^Выставление\s+счёта\s+/iu', '', $cleaned_message);
        return $cleaned_message;
    }
    
    return $message;
}

// Убираем префикс "Выставление счета" из сообщений об ошибках для полей billing
// Используем несколько хуков для максимальной надежности
add_action('woocommerce_after_checkout_validation', 'gustolocal_remove_billing_prefix_from_errors', 25, 2);
function gustolocal_remove_billing_prefix_from_errors($data, $errors) {
    // Поля, для которых нужно убрать префикс
    $billing_fields = array('billing_first_name', 'billing_last_name', 'billing_phone');
    
    foreach ($billing_fields as $field_key) {
        $error_message = $errors->get_error_message($field_key);
        if ($error_message) {
            // Убираем префикс "Выставление счета" или "Выставление счёта" из начала сообщения
            $cleaned_message = preg_replace('/^Выставление\s+счета\s+/iu', '', $error_message);
            $cleaned_message = preg_replace('/^Выставление\s+счёта\s+/iu', '', $cleaned_message);
            
            // Если сообщение изменилось, заменяем его
            if ($cleaned_message !== $error_message) {
                $errors->remove($field_key);
                $errors->add($field_key, $cleaned_message);
            }
        }
    }
}

// Альтернативный способ: фильтр для перехвата сообщений об ошибках на этапе их формирования
add_filter('woocommerce_checkout_field_error_message', 'gustolocal_clean_billing_error_message', 10, 2);
function gustolocal_clean_billing_error_message($message, $field_key) {
    // Поля, для которых нужно убрать префикс
    $billing_fields = array('billing_first_name', 'billing_last_name', 'billing_phone');
    
    if (in_array($field_key, $billing_fields)) {
        // Убираем префикс "Выставление счета" или "Выставление счёта" из начала сообщения
        $cleaned_message = preg_replace('/^Выставление\s+счета\s+/iu', '', $message);
        $cleaned_message = preg_replace('/^Выставление\s+счёта\s+/iu', '', $cleaned_message);
        return $cleaned_message;
    }
    
    return $message;
}

// Обработка всех уведомлений об ошибках через фильтр wc_add_notice
// Используем фильтр для всех уведомлений WooCommerce
add_filter('woocommerce_add_error', 'gustolocal_clean_billing_notice_message', 999, 1);
function gustolocal_clean_billing_notice_message($message) {
    // Проверяем, содержит ли сообщение префикс "Выставление счета" и относится ли к нужным полям
    $billing_field_patterns = array(
        '/Выставление\s+счета\s+Ваше\s+имя/iu',
        '/Выставление\s+счёта\s+Ваше\s+имя/iu',
        '/Выставление\s+счета\s+и\s+фамилия/iu',
        '/Выставление\s+счёта\s+и\s+фамилия/iu',
        '/Выставление\s+счета\s+Как\s+с\s+вами\s+связаться/iu',
        '/Выставление\s+счёта\s+Как\s+с\s+вами\s+связаться/iu'
    );
    
    foreach ($billing_field_patterns as $pattern) {
        if (preg_match($pattern, $message)) {
            // Убираем префикс "Выставление счета" или "Выставление счёта"
            $cleaned_message = preg_replace('/^Выставление\s+счета\s+/iu', '', $message);
            $cleaned_message = preg_replace('/^Выставление\s+счёта\s+/iu', '', $cleaned_message);
            return $cleaned_message;
        }
    }
    
    return $message;
}

// Дополнительная обработка через хук после валидации (на случай, если плагин Checkout Field Editor добавляет ошибки позже)
add_action('woocommerce_after_checkout_validation', 'gustolocal_clean_all_billing_errors_final', 999, 2);
function gustolocal_clean_all_billing_errors_final($data, $errors) {
    if (!$errors || !is_wp_error($errors)) {
        return;
    }
    
    $error_codes = $errors->get_error_codes();
    foreach ($error_codes as $code) {
        $error_message = $errors->get_error_message($code);
        if ($error_message) {
            // Проверяем, содержит ли сообщение префикс "Выставление счета"
            if (preg_match('/^Выставление\s+счета\s+/iu', $error_message) || 
                preg_match('/^Выставление\s+счёта\s+/iu', $error_message)) {
                // Убираем префикс
                $cleaned_message = preg_replace('/^Выставление\s+счета\s+/iu', '', $error_message);
                $cleaned_message = preg_replace('/^Выставление\s+счёта\s+/iu', '', $cleaned_message);
                
                // Заменяем сообщение
                $errors->remove($code);
                $errors->add($code, $cleaned_message);
            }
        }
    }
}

// JavaScript решение для обработки сообщений об ошибках на фронтенде (резервный вариант)
add_action('wp_footer', 'gustolocal_clean_billing_errors_js', 999);
function gustolocal_clean_billing_errors_js() {
    if (!is_checkout()) {
        return;
    }
    ?>
    <script type="text/javascript">
    (function($) {
        // Функция для очистки префикса "Выставление счета" из сообщений об ошибках
        function cleanBillingErrorMessages() {
            // Обрабатываем все сообщения об ошибках
            $('.woocommerce-error, .woocommerce-error li, .woocommerce .error').each(function() {
                var $element = $(this);
                var text = $element.text() || $element.html();
                
                // Убираем префикс "Выставление счета" или "Выставление счёта"
                if (text && (text.indexOf('Выставление счета') === 0 || text.indexOf('Выставление счёта') === 0)) {
                    var cleaned = text.replace(/^Выставление\s+счета\s+/i, '').replace(/^Выставление\s+счёта\s+/i, '');
                    if ($element.is('li')) {
                        $element.text(cleaned);
                    } else {
                        $element.html(cleaned);
                    }
                }
            });
            
            // Обрабатываем сообщения об ошибках в полях формы
            $('.woocommerce form.checkout .form-row .woocommerce-error').each(function() {
                var $element = $(this);
                var text = $element.text() || $element.html();
                
                if (text && (text.indexOf('Выставление счета') === 0 || text.indexOf('Выставление счёта') === 0)) {
                    var cleaned = text.replace(/^Выставление\s+счета\s+/i, '').replace(/^Выставление\s+счёта\s+/i, '');
                    $element.text(cleaned);
                }
            });
        }
        
        // Выполняем очистку при загрузке страницы
        $(document).ready(function() {
            cleanBillingErrorMessages();
        });
        
        // Выполняем очистку после AJAX запросов (когда форма отправляется)
        $(document.body).on('checkout_error', function() {
            setTimeout(cleanBillingErrorMessages, 100);
        });
        
        // Выполняем очистку при изменении DOM (MutationObserver для динамически добавляемых элементов)
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function(mutations) {
                cleanBillingErrorMessages();
            });
            
            $(document).ready(function() {
                var targetNode = document.querySelector('.woocommerce-checkout') || document.body;
                if (targetNode) {
                    observer.observe(targetNode, {
                        childList: true,
                        subtree: true
                    });
                }
            });
        }
    })(jQuery);
    </script>
    <?php
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
// Используем более ранний хук, чтобы значения были установлены до валидации
// ВАЖНО: Устанавливаем значения только в $_POST, не трогая процесс создания заказа
add_action('woocommerce_before_checkout_process', 'gustolocal_set_checkout_defaults_before_process', 1);
function gustolocal_set_checkout_defaults_before_process() {
    // Проверяем, что это действительно запрос чекаута
    if (!isset($_POST['woocommerce-process-checkout-nonce'])) {
        return;
    }
    
    // Устанавливаем значения только если они действительно пустые
    // Это нужно для корректной работы платежных систем
    if (empty($_POST['billing_country']) || !isset($_POST['billing_country'])) {
        $_POST['billing_country'] = 'ES';
    }
    if (empty($_POST['billing_state']) || !isset($_POST['billing_state'])) {
        $_POST['billing_state'] = 'VC';
    }
    if (empty($_POST['billing_city']) || !isset($_POST['billing_city'])) {
        $_POST['billing_city'] = 'Валенсия';
    }
    if (empty($_POST['billing_postcode']) || !isset($_POST['billing_postcode'])) {
        $_POST['billing_postcode'] = '46000';
    }
    
    // Если email пустой, устанавливаем дефолтный для создания заказа
    // WooCommerce требует email для создания заказа, но мы делаем поле необязательным для пользователя
    if (empty($_POST['billing_email']) || !isset($_POST['billing_email'])) {
        // Используем email текущего пользователя, если он залогинен, иначе дефолтный
        $user_email = '';
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $user_email = $current_user->user_email;
        }
        if (empty($user_email)) {
            // Дефолтный email для анонимных пользователей
            $user_email = 'noreply@gustolocal.es';
        }
        $_POST['billing_email'] = $user_email;
    }
}

// Проверяем успешность создания заказа
add_filter('woocommerce_checkout_create_order', 'gustolocal_ensure_order_creation', 10, 2);
function gustolocal_ensure_order_creation($order, $data) {
    if (!$order || is_wp_error($order)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('GustoLocal: Ошибка создания заказа: ' . (is_wp_error($order) ? $order->get_error_message() : 'Order is false'));
            error_log('GustoLocal: Данные заказа: ' . print_r($data, true));
        }
    }
    return $order;
}

// Также устанавливаем значения в заказ, если они не были установлены
// Используем хук ПОСЛЕ создания заказа, чтобы не конфликтовать с плагином Checkout Field Editor
add_action('woocommerce_new_order', 'gustolocal_set_order_defaults_after_creation', 20, 1);
add_action('woocommerce_checkout_order_processed', 'gustolocal_set_order_defaults_after_creation', 20, 1);
function gustolocal_set_order_defaults_after_creation($order_id) {
    // Проверяем, что это действительно ID заказа
    if (!$order_id || !is_numeric($order_id)) {
        return;
    }
    
    // Используем небольшую задержку, чтобы плагин успел обработать заказ
    // Но делаем это синхронно, чтобы не было проблем с AJAX
    gustolocal_apply_order_defaults($order_id);
}

function gustolocal_apply_order_defaults($order_id) {
    try {
        $order = wc_get_order($order_id);
        if (!$order || !is_a($order, 'WC_Order')) {
            return;
        }
        
        $needs_save = false;
        
        // Сохраняем delivery_type если его еще нет
        $delivery_type = $order->get_meta('_delivery_type', true);
        if (empty($delivery_type) && WC()->session) {
            $session_delivery_type = WC()->session->get('delivery_type', 'delivery');
            $order->update_meta_data('_delivery_type', $session_delivery_type);
            $needs_save = true;
        }
        
        if (!$order->get_billing_country()) {
            $order->set_billing_country('ES');
            $needs_save = true;
        }
        if (!$order->get_billing_state()) {
            $order->set_billing_state('VC');
            $needs_save = true;
        }
        if (!$order->get_billing_city()) {
            $order->set_billing_city('Валенсия');
            $needs_save = true;
        }
        if (!$order->get_billing_postcode()) {
            $order->set_billing_postcode('46000');
            $needs_save = true;
        }
        
        if ($needs_save) {
            $order->save();
        }
    } catch (Exception $e) {
        // Логируем ошибку, но не прерываем процесс
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('GustoLocal: Ошибка при установке значений по умолчанию для заказа ' . $order_id . ': ' . $e->getMessage());
        }
    }
}

// Логирование ошибок чекаута для отладки
add_action('woocommerce_checkout_process', 'gustolocal_log_checkout_errors', 999);
function gustolocal_log_checkout_errors() {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $errors = wc_get_notices('error');
        if (!empty($errors)) {
            error_log('GustoLocal: Ошибки валидации чекаута: ' . print_r($errors, true));
        }
    }
}

// ФИКС: Исправляем проблему с плагином Checkout Field Editor
// Плагин получает false вместо объекта заказа и пытается вызвать save() на false
// Перехватываем хук с максимальным приоритетом и исправляем проблему ДО плагина
add_action('woocommerce_checkout_update_order_meta', 'gustolocal_fix_checkout_field_editor_order', 0, 2);
function gustolocal_fix_checkout_field_editor_order($order_id, $data) {
    // Проверяем, что order_id валидный
    if (!$order_id || !is_numeric($order_id)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('GustoLocal: Невалидный order_id в woocommerce_checkout_update_order_meta: ' . var_export($order_id, true));
        }
        // Прерываем выполнение, чтобы плагин не получил false
        return;
    }
    
    // Получаем заказ и проверяем, что он существует
    $order = wc_get_order($order_id);
    if (!$order || !is_a($order, 'WC_Order')) {
        // Если заказ не найден, логируем ошибку и прерываем выполнение
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('GustoLocal: Заказ ' . $order_id . ' не найден при вызове woocommerce_checkout_update_order_meta. Данные: ' . print_r($data, true));
        }
        // Прерываем выполнение, чтобы плагин не получил false
        return;
    }
    
    // Убеждаемся, что заказ сохранен и имеет все необходимые данные
    $needs_save = false;
    if (!$order->get_billing_country()) {
        $order->set_billing_country('ES');
        $needs_save = true;
    }
    if (!$order->get_billing_state()) {
        $order->set_billing_state('VC');
        $needs_save = true;
    }
    if (!$order->get_billing_city()) {
        $order->set_billing_city('Валенсия');
        $needs_save = true;
    }
    if (!$order->get_billing_postcode()) {
        $order->set_billing_postcode('46000');
        $needs_save = true;
    }
    
    // Сохраняем заказ, чтобы плагин получил валидный объект
    if ($needs_save) {
        $order->save();
    }
    
    // КРИТИЧНО: Убеждаемся, что заказ доступен в глобальном контексте для плагина
    // Плагин может пытаться получить заказ из глобальной переменной
    global $thwcfd_order;
    if (!isset($thwcfd_order) || !$thwcfd_order) {
        $thwcfd_order = $order;
    }
}

// Дополнительный фикс: перехватываем создание заказа и сохраняем его в глобальной переменной
add_action('woocommerce_new_order', 'gustolocal_store_order_for_plugin', 1, 1);
function gustolocal_store_order_for_plugin($order_id) {
    if ($order_id && is_numeric($order_id)) {
        $order = wc_get_order($order_id);
        if ($order && is_a($order, 'WC_Order')) {
            global $thwcfd_order;
            $thwcfd_order = $order;
        }
    }
}

// КРИТИЧЕСКИЙ ФИКС: Перехватываем вызов метода плагина и исправляем проблему
// Плагин получает false вместо объекта заказа, поэтому перехватываем его метод
// Используем несколько хуков, чтобы перехватить плагин в любом случае
add_action('init', 'gustolocal_fix_checkout_field_editor_plugin', 999);
add_action('wp_loaded', 'gustolocal_fix_checkout_field_editor_plugin', 999);
function gustolocal_fix_checkout_field_editor_plugin() {
    try {
        // Проверяем, что плагин активен
        if (!class_exists('THWCFD_Public_Checkout')) {
            return;
        }
        
        // Получаем экземпляр класса плагина безопасно
        $plugin_instance = null;
        if (method_exists('THWCFD_Public_Checkout', 'instance')) {
            $plugin_instance = THWCFD_Public_Checkout::instance();
        } elseif (method_exists('THWCFD_Public_Checkout', 'get_instance')) {
            $plugin_instance = THWCFD_Public_Checkout::get_instance();
        }
        
        if (!$plugin_instance) {
            return;
        }
        
        // Перехватываем метод checkout_update_order_meta
        // Удаляем оригинальный хук (пробуем разные приоритеты)
        if (method_exists($plugin_instance, 'checkout_update_order_meta')) {
            remove_action('woocommerce_checkout_update_order_meta', array($plugin_instance, 'checkout_update_order_meta'), 10);
            remove_action('woocommerce_checkout_update_order_meta', array($plugin_instance, 'checkout_update_order_meta'));
        }
        
        // Добавляем наш исправленный метод с более высоким приоритетом
        if (!has_action('woocommerce_checkout_update_order_meta', 'gustolocal_safe_checkout_update_order_meta')) {
            add_action('woocommerce_checkout_update_order_meta', 'gustolocal_safe_checkout_update_order_meta', 5, 2);
        }
    } catch (Exception $e) {
        // Логируем ошибку, но не прерываем работу сайта
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('GustoLocal: Ошибка при перехвате Checkout Field Editor: ' . $e->getMessage());
        }
    } catch (Error $e) {
        // Логируем фатальную ошибку
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('GustoLocal: Фатальная ошибка при перехвате Checkout Field Editor: ' . $e->getMessage());
        }
    }
}

// Безопасная версия метода плагина, которая проверяет заказ перед использованием
function gustolocal_safe_checkout_update_order_meta($order_id, $data) {
    try {
        // Проверяем, что order_id валидный
        if (!$order_id || !is_numeric($order_id)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('GustoLocal: Пропущен Checkout Field Editor - невалидный order_id: ' . var_export($order_id, true));
            }
            return;
        }
        
        // Получаем заказ и проверяем, что он существует
        $order = wc_get_order($order_id);
        if (!$order || !is_a($order, 'WC_Order')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('GustoLocal: Пропущен Checkout Field Editor - заказ ' . $order_id . ' не найден');
            }
            return;
        }
        
        // Теперь вызываем оригинальный метод плагина, но с гарантией, что заказ существует
        if (!class_exists('THWCFD_Public_Checkout')) {
            return;
        }
        
        $plugin_instance = null;
        if (method_exists('THWCFD_Public_Checkout', 'instance')) {
            $plugin_instance = THWCFD_Public_Checkout::instance();
        } elseif (method_exists('THWCFD_Public_Checkout', 'get_instance')) {
            $plugin_instance = THWCFD_Public_Checkout::get_instance();
        }
        
        if ($plugin_instance && method_exists($plugin_instance, 'checkout_update_order_meta')) {
            // Устанавливаем заказ в глобальной переменной для плагина
            global $thwcfd_order;
            $thwcfd_order = $order;
            
            // Вызываем метод плагина
            $plugin_instance->checkout_update_order_meta($order_id, $data);
        }
    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('GustoLocal: Ошибка в безопасном методе Checkout Field Editor: ' . $e->getMessage());
        }
    } catch (Error $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('GustoLocal: Фатальная ошибка в безопасном методе Checkout Field Editor: ' . $e->getMessage());
        }
    }
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

// Сохраняем delivery_type в мета-данные заказа при создании
add_action('woocommerce_checkout_update_order_meta', 'gustolocal_save_delivery_type_to_order', 10, 2);
function gustolocal_save_delivery_type_to_order($order_id, $data) {
    if (isset($data['delivery_type'])) {
        $delivery_type = sanitize_text_field($data['delivery_type']);
        if (in_array($delivery_type, array('delivery', 'pickup'))) {
            update_post_meta($order_id, '_delivery_type', $delivery_type);
        }
    } elseif (WC()->session) {
        // Если delivery_type не передан в данных, берем из сессии
        $delivery_type = WC()->session->get('delivery_type', 'delivery');
        update_post_meta($order_id, '_delivery_type', $delivery_type);
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

/* ============ Убираем ссылки на товары в корзине, чекауте и заказах ============ */
// Убираем ссылки на товары в корзине
add_filter('woocommerce_cart_item_permalink', '__return_empty_string', 10, 3);

// Убираем ссылки на товары в заказах (чекаут, подтверждение заказа, письма)
add_filter('woocommerce_order_item_permalink', '__return_empty_string', 10, 3);

// Дополнительно: убираем ссылки из названия товара, если они там есть
add_filter('woocommerce_cart_item_name', 'gustolocal_remove_product_links_from_name', 10, 3);
function gustolocal_remove_product_links_from_name($name, $cart_item, $cart_item_key) {
    // Удаляем все ссылки <a> из названия товара
    $name = preg_replace('/<a[^>]*>(.*?)<\/a>/i', '$1', $name);
    return $name;
}

// Убираем ссылки из названия товара в заказах
add_filter('woocommerce_order_item_name', 'gustolocal_remove_product_links_from_order_name', 10, 2);
function gustolocal_remove_product_links_from_order_name($name, $item) {
    // Удаляем все ссылки <a> из названия товара
    $name = preg_replace('/<a[^>]*>(.*?)<\/a>/i', '$1', $name);
    return $name;
}
/* ============ Настройки минимального заказа ============ */
// Добавляем страницу настроек в админке
add_action('admin_menu', 'gustolocal_add_minimum_order_settings_page');
function gustolocal_add_minimum_order_settings_page() {
    add_submenu_page(
        'woocommerce',
        'Минимальный заказ',
        'Минимальный заказ',
        'manage_options',
        'gustolocal-minimum-order',
        'gustolocal_minimum_order_settings_page'
    );
}

// Страница настроек минимального заказа
function gustolocal_minimum_order_settings_page() {
    // Сохранение настроек
    if (isset($_POST['gustolocal_save_minimum_order']) && check_admin_referer('gustolocal_minimum_order_settings')) {
        $enabled = isset($_POST['minimum_order_enabled']) ? 1 : 0;
        $amount = floatval($_POST['minimum_order_amount']);
        $message = sanitize_text_field($_POST['minimum_order_message']);
        
        update_option('gustolocal_minimum_order_enabled', $enabled);
        update_option('gustolocal_minimum_order_amount', $amount);
        update_option('gustolocal_minimum_order_message', $message);
        
        echo '<div class="notice notice-success"><p>Настройки сохранены!</p></div>';
    }
    
    // Получаем текущие настройки
    $enabled = get_option('gustolocal_minimum_order_enabled', 0);
    $amount = get_option('gustolocal_minimum_order_amount', 60.00);
    $message = get_option('gustolocal_minimum_order_message', 'Минимальная сумма заказа: {amount} €. Добавьте товаров на {remaining} €.');
    
    ?>
    <div class="wrap">
        <h1>Настройки минимального заказа</h1>
        <form method="post" action="">
            <?php wp_nonce_field('gustolocal_minimum_order_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="minimum_order_enabled">Включить минимальный заказ</label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="minimum_order_enabled" value="1" <?php checked($enabled, 1); ?>>
                            Включить проверку минимальной суммы заказа
                        </label>
                        <p class="description">Когда включено, пользователи не смогут оформить заказ, если сумма меньше указанной.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="minimum_order_amount">Минимальная сумма заказа (€)</label>
                    </th>
                    <td>
                        <input type="number" 
                               name="minimum_order_amount" 
                               id="minimum_order_amount" 
                               value="<?php echo esc_attr($amount); ?>" 
                               step="0.01" 
                               min="0" 
                               class="regular-text">
                        <p class="description">Минимальная сумма заказа в евро (без учета доставки).</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="minimum_order_message">Сообщение об ошибке</label>
                    </th>
                    <td>
                        <textarea name="minimum_order_message" 
                                  id="minimum_order_message" 
                                  rows="3" 
                                  class="large-text"><?php echo esc_textarea($message); ?></textarea>
                        <p class="description">
                            Сообщение, которое увидит пользователь, если сумма заказа меньше минимальной.<br>
                            Используйте <code>{amount}</code> для суммы минимального заказа и <code>{remaining}</code> для недостающей суммы.
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Сохранить настройки', 'primary', 'gustolocal_save_minimum_order'); ?>
        </form>
        
        <hr>
        
        <h2>Текущие настройки</h2>
        <table class="form-table">
            <tr>
                <th>Статус:</th>
                <td><strong><?php echo $enabled ? 'Включено' : 'Выключено'; ?></strong></td>
            </tr>
            <tr>
                <th>Минимальная сумма:</th>
                <td><strong><?php echo number_format($amount, 2, ',', ' '); ?> €</strong></td>
            </tr>
            <tr>
                <th>Сообщение:</th>
                <td><?php echo esc_html($message); ?></td>
            </tr>
        </table>
    </div>
    <?php
}

// Валидация минимального заказа при оформлении
add_action('woocommerce_checkout_process', 'gustolocal_validate_minimum_order');
function gustolocal_validate_minimum_order() {
    // Проверяем, включена ли функция
    $enabled = get_option('gustolocal_minimum_order_enabled', 0);
    if (!$enabled) {
        return; // Функция выключена, не проверяем
    }
    
    // Проверяем, что WooCommerce активен
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }
    
    $minimum_amount = floatval(get_option('gustolocal_minimum_order_amount', 60.00));
    $message_template = get_option('gustolocal_minimum_order_message', 'Минимальная сумма заказа: {amount} €. Добавьте товаров на {remaining} €.');
    
    // Получаем сумму корзины БЕЗ доставки (subtotal)
    $cart_subtotal = WC()->cart->get_subtotal();
    
    if ($cart_subtotal < $minimum_amount) {
        $remaining = $minimum_amount - $cart_subtotal;
        
        $message = str_replace(
            array('{amount}', '{remaining}'),
            array(number_format($minimum_amount, 2, ',', ' ') . ' €', number_format($remaining, 2, ',', ' ') . ' €'),
            $message_template
        );
        
        wc_add_notice($message, 'error');
    }
}

// Показываем уведомление в корзине
add_action('woocommerce_before_cart', 'gustolocal_show_minimum_order_notice_cart');
function gustolocal_show_minimum_order_notice_cart() {
    // Проверяем, включена ли функция
    $enabled = get_option('gustolocal_minimum_order_enabled', 0);
    if (!$enabled) {
        return; // Функция выключена, не показываем
    }
    
    // Проверяем, что WooCommerce активен
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }
    
    $minimum_amount = floatval(get_option('gustolocal_minimum_order_amount', 60.00));
    $cart_subtotal = WC()->cart->get_subtotal();
    
    if ($cart_subtotal < $minimum_amount) {
        $remaining = $minimum_amount - $cart_subtotal;
        
        $message = sprintf(
            'Минимальная сумма заказа: <strong>%s €</strong>. Добавьте товаров на <strong>%s €</strong>.',
            number_format($minimum_amount, 2, ',', ' '),
            number_format($remaining, 2, ',', ' ')
        );
        
        echo '<div class="gl-minimum-order-notice" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-bottom: 20px; border-radius: 4px;">';
        echo '<strong>⚠️ Минимальный заказ:</strong> ' . $message;
        echo '</div>';
    }
}

// Показываем уведомление на странице чекаута
add_action('woocommerce_before_checkout_form', 'gustolocal_show_minimum_order_notice_checkout');
function gustolocal_show_minimum_order_notice_checkout() {
    // Проверяем, включена ли функция
    $enabled = get_option('gustolocal_minimum_order_enabled', 0);
    if (!$enabled) {
        return; // Функция выключена, не показываем
    }
    
    // Проверяем, что WooCommerce активен
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }
    
    $minimum_amount = floatval(get_option('gustolocal_minimum_order_amount', 60.00));
    $cart_subtotal = WC()->cart->get_subtotal();
    
    if ($cart_subtotal < $minimum_amount) {
        $remaining = $minimum_amount - $cart_subtotal;
        
        $message = sprintf(
            'Минимальная сумма заказа: <strong>%s €</strong>. Добавьте товаров на <strong>%s €</strong>.',
            number_format($minimum_amount, 2, ',', ' '),
            number_format($remaining, 2, ',', ' ')
        );
        
        echo '<div class="gl-minimum-order-notice" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-bottom: 20px; border-radius: 4px;">';
        echo '<strong>⚠️ Минимальный заказ:</strong> ' . $message;
        echo '</div>';
    }
}

/* ========================================
   УПРАВЛЕНИЕ КАТЕГОРИЯМИ МЕНЮ
   ======================================== */

// Регистрация страницы настроек категорий - ОТКЛЮЧЕНО
// Используем настройки порядка категорий в Meal Builder вместо этого
// add_action('admin_menu', 'gustolocal_add_category_settings_page');
// function gustolocal_add_category_settings_page() {
//     add_submenu_page(
//         'woocommerce',
//         'Категории меню',
//         'Категории меню',
//         'manage_options',
//         'gustolocal-categories',
//         'gustolocal_category_settings_page'
//     );
// }

// Функция для получения всех существующих категорий из таксономии
function gustolocal_get_all_categories() {
    $terms = get_terms(array(
        'taxonomy' => 'wmb_section',
        'hide_empty' => false,
    ));
    
    if (is_wp_error($terms)) {
        return array();
    }
    
    $categories = array();
    foreach ($terms as $term) {
        $categories[] = $term->name;
    }
    
    return $categories;
}

// Функция для получения настроек категорий
function gustolocal_get_category_settings() {
    $settings = get_option('gustolocal_category_settings', array());
    
    // Если настройки пустые, инициализируем из существующих категорий
    if (empty($settings)) {
        $default_order = array(
            'Завтраки и сладкое',
            'Авторские сэндвичи и перекусы',
            'Паста ручной работы',
            'Основные блюда',
            'Гарниры и зелень',
            'Супы и крем-супы',
            'Для запаса / в морозильник',
        );
        
        $all_categories = gustolocal_get_all_categories();
        $order = 1;
        
        foreach ($default_order as $cat_name) {
            if (in_array($cat_name, $all_categories)) {
                $settings[$cat_name] = array(
                    'original' => $cat_name,
                    'display' => $cat_name,
                    'order' => $order++,
                    'aliases' => array(),
                );
            }
        }
        
        // Добавляем остальные категории, которых нет в дефолтном списке
        foreach ($all_categories as $cat_name) {
            if (!isset($settings[$cat_name])) {
                $settings[$cat_name] = array(
                    'original' => $cat_name,
                    'display' => $cat_name,
                    'order' => $order++,
                    'aliases' => array(),
                );
            }
        }
        
        update_option('gustolocal_category_settings', $settings);
    }
    
    return $settings;
}

// Функция для получения категории по синониму
function gustolocal_map_category_by_alias($category_name) {
    $settings = gustolocal_get_category_settings();
    $category_name_lower = mb_strtolower(trim($category_name));
    
    // Сначала проверяем точное совпадение
    foreach ($settings as $original => $config) {
        if (mb_strtolower($original) === $category_name_lower || 
            mb_strtolower($config['display']) === $category_name_lower) {
            return $original;
        }
    }
    
    // Затем проверяем синонимы
    foreach ($settings as $original => $config) {
        foreach ($config['aliases'] as $alias) {
            if (mb_strtolower(trim($alias)) === $category_name_lower) {
                return $original;
            }
        }
    }
    
    // Проверяем частичное совпадение (для обратной совместимости)
    foreach ($settings as $original => $config) {
        $original_lower = mb_strtolower($original);
        if (strpos($original_lower, $category_name_lower) !== false || 
            strpos($category_name_lower, $original_lower) !== false) {
            return $original;
        }
    }
    
    return $category_name; // Возвращаем как есть, если не найдено
}

// Функция для получения отображаемого названия категории
function gustolocal_get_category_display_name($category_name) {
    $settings = gustolocal_get_category_settings();
    
    if (isset($settings[$category_name]) && !empty($settings[$category_name]['display'])) {
        return $settings[$category_name]['display'];
    }
    
    return $category_name;
}

// Функция для получения отсортированного списка категорий
function gustolocal_get_ordered_categories() {
    $settings = gustolocal_get_category_settings();
    
    // Сортируем по порядку
    uasort($settings, function($a, $b) {
        $order_a = isset($a['order']) ? (int)$a['order'] : 999;
        $order_b = isset($b['order']) ? (int)$b['order'] : 999;
        
        if ($order_a === $order_b) {
            return strcmp($a['original'], $b['original']);
        }
        
        return $order_a - $order_b;
    });
    
    return $settings;
}

// Страница настроек категорий
function gustolocal_category_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Обработка сохранения
    if (isset($_POST['gustolocal_save_categories']) && check_admin_referer('gustolocal_categories_nonce')) {
        $settings = array();
        
        if (isset($_POST['categories']) && is_array($_POST['categories'])) {
            foreach ($_POST['categories'] as $original => $data) {
                $settings[$original] = array(
                    'original' => sanitize_text_field($original),
                    'display' => sanitize_text_field($data['display'] ?? $original),
                    'order' => isset($data['order']) ? (int)$data['order'] : 999,
                    'aliases' => !empty($data['aliases']) 
                        ? array_filter(array_map('trim', explode(',', sanitize_text_field($data['aliases']))))
                        : array(),
                );
            }
        }
        
        // Добавляем новые категории из формы
        if (isset($_POST['new_categories']) && is_array($_POST['new_categories'])) {
            foreach ($_POST['new_categories'] as $new_cat) {
                $new_cat = trim($new_cat);
                if (!empty($new_cat) && !isset($settings[$new_cat])) {
                    $max_order = 0;
                    foreach ($settings as $cat) {
                        if (isset($cat['order']) && $cat['order'] > $max_order) {
                            $max_order = $cat['order'];
                        }
                    }
                    
                    $settings[$new_cat] = array(
                        'original' => $new_cat,
                        'display' => $new_cat,
                        'order' => $max_order + 1,
                        'aliases' => array(),
                    );
                }
            }
        }
        
        update_option('gustolocal_category_settings', $settings);
        echo '<div class="notice notice-success"><p>Настройки категорий сохранены!</p></div>';
    }
    
    // Обработка удаления
    if (isset($_POST['gustolocal_delete_category']) && check_admin_referer('gustolocal_categories_nonce')) {
        $category_to_delete = sanitize_text_field($_POST['category_to_delete'] ?? '');
        if (!empty($category_to_delete)) {
            $settings = gustolocal_get_category_settings();
            unset($settings[$category_to_delete]);
            update_option('gustolocal_category_settings', $settings);
            echo '<div class="notice notice-success"><p>Категория удалена из настроек!</p></div>';
        }
    }
    
    $settings = gustolocal_get_category_settings();
    $all_categories = gustolocal_get_all_categories();
    $ordered_categories = gustolocal_get_ordered_categories();
    
    ?>
    <div class="wrap">
        <h1>Управление категориями меню</h1>
        <p>Здесь вы можете настроить порядок отображения категорий, переименовать их для отображения на сайте и добавить синонимы для автоматического маппинга при импорте CSV.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('gustolocal_categories_nonce'); ?>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">Порядок</th>
                        <th>Оригинальное название</th>
                        <th>Отображаемое название</th>
                        <th>Синонимы (через запятую)</th>
                        <th style="width: 100px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordered_categories as $original => $config): ?>
                        <tr>
                            <td>
                                <input type="number" 
                                       name="categories[<?php echo esc_attr($original); ?>][order]" 
                                       value="<?php echo esc_attr($config['order'] ?? 999); ?>" 
                                       min="1" 
                                       style="width: 60px;">
                            </td>
                            <td>
                                <strong><?php echo esc_html($original); ?></strong>
                                <?php if (!in_array($original, $all_categories)): ?>
                                    <span style="color: #d63638;">(не найдена в таксономии)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="text" 
                                       name="categories[<?php echo esc_attr($original); ?>][display]" 
                                       value="<?php echo esc_attr($config['display'] ?? $original); ?>" 
                                       class="regular-text">
                            </td>
                            <td>
                                <input type="text" 
                                       name="categories[<?php echo esc_attr($original); ?>][aliases]" 
                                       value="<?php echo esc_attr(implode(', ', $config['aliases'] ?? array())); ?>" 
                                       class="large-text" 
                                       placeholder="Авторская паста, Паста, Макароны">
                                <p class="description">Синонимы используются для автоматического маппинга при импорте CSV</p>
                            </td>
                            <td>
                                <button type="submit" 
                                        name="gustolocal_delete_category" 
                                        value="1" 
                                        onclick="return confirm('Удалить категорию из настроек?');"
                                        class="button button-small">
                                    Удалить
                                </button>
                                <input type="hidden" name="category_to_delete" value="<?php echo esc_attr($original); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h2>Добавить новую категорию</h2>
            <p>Добавьте категорию, которая будет использоваться при импорте CSV (например, если в CSV указана категория, которой еще нет в настройках):</p>
            <div id="new-categories-container">
                <p>
                    <input type="text" 
                           name="new_categories[]" 
                           class="regular-text" 
                           placeholder="Название категории">
                    <button type="button" class="button" onclick="addNewCategoryField()">+ Добавить еще</button>
                </p>
            </div>
            
            <p class="submit">
                <input type="submit" 
                       name="gustolocal_save_categories" 
                       class="button button-primary" 
                       value="Сохранить настройки">
            </p>
        </form>
        
        <hr>
        
        <h2>Справка</h2>
        <ul>
            <li><strong>Порядок</strong> — определяет последовательность отображения категорий на странице меню (меньше = выше)</li>
            <li><strong>Отображаемое название</strong> — название, которое будет показано пользователям на сайте (может отличаться от оригинального)</li>
            <li><strong>Синонимы</strong> — варианты названий категории, которые будут автоматически маппиться к основной категории при импорте CSV</li>
            <li>Если категория не найдена в таксономии, она будет создана автоматически при импорте CSV</li>
        </ul>
        
        <h2>Все существующие категории в таксономии</h2>
        <ul>
            <?php foreach ($all_categories as $cat): ?>
                <li><?php echo esc_html($cat); ?></li>
            <?php endforeach; ?>
            <?php if (empty($all_categories)): ?>
                <li><em>Категории не найдены</em></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <script>
    function addNewCategoryField() {
        var container = document.getElementById('new-categories-container');
        var p = document.createElement('p');
        p.innerHTML = '<input type="text" name="new_categories[]" class="regular-text" placeholder="Название категории"> ' +
                      '<button type="button" class="button" onclick="this.parentElement.remove()">Удалить</button>';
        container.appendChild(p);
    }
    </script>
    <?php
}

/* ========================================
   РАЗБОР ЗАКАЗОВ ПО ПОЗИЦИЯМ
   ======================================== */

// Регистрация страницы разбора заказов
add_action('admin_menu', 'gustolocal_add_order_breakdown_page');
function gustolocal_add_order_breakdown_page() {
    add_submenu_page(
        'woocommerce',
        'Разбор заказов',
        'Разбор заказов',
        'manage_options',
        'gustolocal-order-breakdown',
        'gustolocal_order_breakdown_page'
    );
}

// Хелпер: определение самовывоза по данным заказа
function gustolocal_is_pickup_order($order) {
    if (!$order) return false;

    // 1) Явное мета-поле _delivery_type
    $delivery_type = $order->get_meta('_delivery_type', true);
    if ($delivery_type === 'pickup') return true;
    if ($delivery_type === 'delivery') return false;

    // 2) Проверка позиций fee по стоимости (ключевой способ для вашей системы):
    // - fee с total = 0 → самовывоз (независимо от названия)
    // - fee с total = 10 → доставка (независимо от названия)
    // Также проверяем название fee и мета-данные
    foreach ($order->get_items('fee') as $fee_item) {
        $fee_name = $fee_item->get_name();
        $fee_total = floatval($fee_item->get_total());
        
        // Проверяем по стоимости (самый надежный способ)
        // Если total = 0 → самовывоз
        if (abs($fee_total) < 0.01) {
            return true;
        }
        
        // Если total = 10 → доставка
        if (abs($fee_total - 10.0) < 0.01) {
            return false;
        }
        
        // Дополнительная проверка по названию (на случай если стоимость отличается)
        // Если fee "Самовывоз" → самовывоз
        if (stripos($fee_name, 'самовывоз') !== false ||
            stripos($fee_name, 'pickup') !== false ||
            stripos($fee_name, 'самостоятельно') !== false) {
            return true;
        }
        
        // Если fee "Доставка" → доставка
        if (stripos($fee_name, 'доставка') !== false || 
            stripos($fee_name, 'delivery') !== false) {
            return false;
        }
        
        // Проверяем мета-данные fee (может быть поле "комиссионные" или другие)
        $fee_meta = $fee_item->get_meta_data();
        foreach ($fee_meta as $meta) {
            $meta_key = $meta->key;
            $meta_value = $meta->value;
            
            // Если в мета есть упоминание самовывоза
            if (is_string($meta_value) && (
                stripos($meta_value, 'самовывоз') !== false ||
                stripos($meta_value, 'pickup') !== false
            )) {
                return true;
            }
            
            // Если в мета есть упоминание доставки
            if (is_string($meta_value) && (
                stripos($meta_value, 'доставка') !== false ||
                stripos($meta_value, 'delivery') !== false
            )) {
                return false;
            }
        }
    }

    // 3) Проверка способов доставки (shipping methods)
    $shipping_methods = $order->get_shipping_methods();
    foreach ($shipping_methods as $method) {
        $method_title = $method->get_method_title();
        $method_id    = $method->get_method_id();
        if (stripos($method_title, 'самовывоз') !== false ||
            stripos($method_title, 'pickup') !== false ||
            stripos($method_title, 'самостоятельно') !== false ||
            stripos($method_id, 'local_pickup') !== false) {
            return true;
        }
        if (stripos($method_title, 'доставка') !== false) {
            return false;
        }
    }

    // 4) Проверка агрегированного метода доставки
    $shipping_method = $order->get_shipping_method();
    if (!empty($shipping_method)) {
        if (stripos($shipping_method, 'самовывоз') !== false ||
            stripos($shipping_method, 'pickup') !== false ||
            stripos($shipping_method, 'самостоятельно') !== false) {
            return true;
        }
        if (stripos($shipping_method, 'доставка') !== false) {
            return false;
        }
    }

    // 5) По суммам: если shipping_total > 0 — считаем доставкой
    $shipping_total = floatval($order->get_shipping_total());
    if ($shipping_total > 0.0001) return false;

    // 6) Ничего не нашли — по умолчанию Доставка (чтобы не промахнуться)
    return false;
}

// Функция для получения категории блюда по названию
function gustolocal_get_dish_category($dish_name) {
    // Ищем блюдо в таксономии wmb_section
    $dishes = get_posts(array(
        'post_type' => 'wmb_dish',
        'title' => $dish_name,
        'posts_per_page' => 1,
        'post_status' => 'any',
    ));
    
    if (!empty($dishes)) {
        $dish_id = $dishes[0]->ID;
        $terms = wp_get_post_terms($dish_id, 'wmb_section', array('fields' => 'names'));
        if (!empty($terms) && !is_wp_error($terms)) {
            $category = $terms[0];
            // Используем отображаемое название категории, если доступно
            if (function_exists('gustolocal_get_category_display_name')) {
                return gustolocal_get_category_display_name($category);
            }
            return $category;
        }
    }
    
    return 'Прочее';
}

// Функция для получения sale_type блюда по названию
function gustolocal_get_dish_sale_type($dish_name) {
    $dishes = get_posts(array(
        'post_type' => 'wmb_dish',
        'title' => $dish_name,
        'posts_per_page' => 1,
        'post_status' => 'any',
    ));
    
    if (!empty($dishes)) {
        $dish_id = $dishes[0]->ID;
        $sale_type = get_post_meta($dish_id, 'wmb_sale_type', true);
        if ($sale_type === 'smart_food' || $sale_type === 'both') {
            return 'superfood';
        } elseif ($sale_type === 'mercat') {
            return 'mercat';
        }
    }
    
    return 'superfood'; // По умолчанию superfood
}

// Функция для получения всех блюд с их данными
function gustolocal_get_all_dishes() {
    static $all_dishes_cache = null;
    
    if ($all_dishes_cache !== null) {
        return $all_dishes_cache;
    }
    
    $dishes = get_posts(array(
        'post_type' => 'wmb_dish',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_query' => array(
            array(
                'key' => 'wmb_active',
                'value' => '1',
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    
    $all_dishes_cache = array();
    
    foreach ($dishes as $dish_post) {
        $dish_name = $dish_post->post_title;
        $unit = get_post_meta($dish_post->ID, 'wmb_unit', true);
        $sale_type = get_post_meta($dish_post->ID, 'wmb_sale_type', true);
        
        // Определяем sale_type
        if ($sale_type === 'smart_food' || $sale_type === 'both') {
            $dish_sale_type = 'superfood';
        } elseif ($sale_type === 'mercat') {
            $dish_sale_type = 'mercat';
        } else {
            $dish_sale_type = 'superfood'; // По умолчанию
        }
        
        $category = gustolocal_get_dish_category($dish_name);
        $key = $dish_name . ($unit ? ' (' . $unit . ')' : '');
        
        $all_dishes_cache[$key] = array(
            'name' => $dish_name,
            'unit' => $unit ? $unit : '',
            'category' => $category,
            'category_order' => gustolocal_get_category_order($category),
            'sale_type' => $dish_sale_type,
        );
    }
    
    return $all_dishes_cache;
}

// Функция для получения порядка категории
function gustolocal_get_category_order($category_name) {
    if (function_exists('gustolocal_get_ordered_categories')) {
        $ordered = gustolocal_get_ordered_categories();
        foreach ($ordered as $original => $config) {
            $display = !empty($config['display']) ? $config['display'] : $original;
            if (mb_strtolower($display) === mb_strtolower($category_name) || 
                mb_strtolower($original) === mb_strtolower($category_name)) {
                return isset($config['order']) ? (int)$config['order'] : 999;
            }
        }
    }
    return 999;
}

// Функция для извлечения блюд из заказа
function gustolocal_extract_dishes_from_order($order) {
    $dishes = array();
    
    foreach ($order->get_items() as $item_id => $item) {
        // Проверяем, есть ли payload от meal-builder
        $payload_meta = $item->get_meta('_wmb_payload', true);
        if (!$payload_meta) {
            $payload_meta = $item->get_meta('Meal plan payload', true);
        }
        
        if ($payload_meta) {
            $payload = json_decode($payload_meta, true);
            if ($payload && isset($payload['items_list']) && is_array($payload['items_list'])) {
                foreach ($payload['items_list'] as $dish_item) {
                    $name = isset($dish_item['name']) ? trim($dish_item['name']) : '';
                    $qty = isset($dish_item['qty']) ? intval($dish_item['qty']) : 0;
                    $unit = isset($dish_item['unit']) ? trim($dish_item['unit']) : '';
                    $price = isset($dish_item['price']) ? floatval($dish_item['price']) : 0;
                    
                    if (empty($name) || $qty <= 0) continue;
                    
                    // Формируем ключ: название + единица
                    $key = $name . ($unit ? ' (' . $unit . ')' : '');
                    
                    if (!isset($dishes[$key])) {
                        $category = gustolocal_get_dish_category($name);
                        $dishes[$key] = array(
                            'name' => $name,
                            'unit' => $unit,
                            'category' => $category,
                            'category_order' => gustolocal_get_category_order($category),
                            'total_qty' => 0,
                            'total_price' => 0,
                        );
                    }
                    
                    $dishes[$key]['total_qty'] += $qty;
                    $dishes[$key]['total_price'] += $price * $qty;
                }
            }
        } else {
            // Обычный товар (не из meal-builder)
            $product_name = $item->get_name();
            $qty = $item->get_quantity();
            $price = $item->get_total();
            
            $key = $product_name;
            if (!isset($dishes[$key])) {
                $dishes[$key] = array(
                    'name' => $product_name,
                    'unit' => '',
                    'category' => 'Прочее',
                    'category_order' => 999,
                    'total_qty' => 0,
                    'total_price' => 0,
                );
            }
            
            $dishes[$key]['total_qty'] += $qty;
            $dishes[$key]['total_price'] += $price;
        }
    }
    
    return $dishes;
}

// Страница разбора заказов
function gustolocal_order_breakdown_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Проверяем, что WooCommerce активен
    if (!function_exists('wc_get_orders')) {
        echo '<div class="wrap"><h1>Разбор заказов</h1><div class="error"><p>WooCommerce не активирован!</p></div></div>';
        return;
    }
    
    $selected_orders = isset($_POST['order_ids']) && is_array($_POST['order_ids']) 
        ? array_map('intval', $_POST['order_ids']) 
        : array();
    
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : date('Y-m-d', strtotime('-7 days'));
    $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : date('Y-m-d');
    $status_filter = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    
    // Получаем заказы для выбора
    $orders_query = array(
        'limit' => 500,
        'orderby' => 'date',
        'order' => 'DESC',
        'date_created' => $date_from . '...' . $date_to,
    );
    
    if ($status_filter) {
        $orders_query['status'] = $status_filter;
    }
    
    $all_orders = wc_get_orders($orders_query);
    
    // Если выбраны заказы, формируем сводку
    $breakdown_data = null;
    if (!empty($selected_orders)) {
        $breakdown_data = gustolocal_generate_breakdown($selected_orders);
    }
    
    ?>
    <div class="wrap">
        <h1>Разбор заказов по позициям</h1>
        
        <form method="post" action="" id="breakdown-form">
            <div class="postbox" style="margin-top: 20px; padding: 20px;">
                <h2>Фильтры</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="date_from">Дата от:</label></th>
                        <td><input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="date_to">Дата до:</label></th>
                        <td><input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="status">Статус:</label></th>
                        <td>
                            <select id="status" name="status" class="regular-text">
                                <option value="">Все статусы</option>
                                <?php
                                $statuses = wc_get_order_statuses();
                                foreach ($statuses as $status_key => $status_label) {
                                    $selected = ($status_filter === $status_key) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($status_key) . '" ' . $selected . '>' . esc_html($status_label) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="filter_orders" class="button button-primary" value="Применить фильтры">
                </p>
            </div>
            
            <div class="postbox" style="margin-top: 20px; padding: 20px;">
                <h2>Выберите заказы</h2>
                <p>
                    <button type="button" class="button" onclick="selectAllOrders()">Выбрать все</button>
                    <button type="button" class="button" onclick="deselectAllOrders()">Снять выбор</button>
                </p>
                
                <?php if (empty($all_orders)): ?>
                    <p>Заказы не найдены.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 30px;"><input type="checkbox" id="select-all-checkbox" onclick="toggleAllOrders(this)"></th>
                                <th>№ заказа</th>
                                <th>Дата</th>
                                <th>Клиент</th>
                                <th>Статус</th>
                                <th>Способ получения</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_orders as $order): 
                                $is_selected = in_array($order->get_id(), $selected_orders);
                                $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                                if (trim($customer_name) === '') {
                                    $customer_name = $order->get_billing_company() ?: 'Гость';
                                }
                                // Определяем самовывоз с учетом fee/мета/метода доставки
                                $is_pickup = gustolocal_is_pickup_order($order);
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="order_ids[]" 
                                               value="<?php echo esc_attr($order->get_id()); ?>"
                                               <?php echo $is_selected ? 'checked' : ''; ?>>
                                    </td>
                                    <td><strong>#<?php echo esc_html($order->get_id()); ?></strong></td>
                                    <td><?php echo esc_html($order->get_date_created()->date_i18n('d.m.Y H:i')); ?></td>
                                    <td><?php echo esc_html($customer_name); ?></td>
                                    <td><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></td>
                                    <td><?php echo $is_pickup ? '<strong>Самовывоз</strong>' : 'Доставка'; ?></td>
                                    <td><?php echo $order->get_formatted_order_total(); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <p class="submit" style="margin-top: 20px;">
                        <input type="submit" name="generate_breakdown" class="button button-primary button-large" value="Сформировать сводку">
                    </p>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if ($breakdown_data): ?>
            <div class="postbox" style="margin-top: 20px; padding: 20px;">
                <h2>Сводная таблица</h2>
                <?php gustolocal_display_breakdown_table($breakdown_data); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
    function toggleAllOrders(checkbox) {
        var checkboxes = document.querySelectorAll('input[name="order_ids[]"]');
        checkboxes.forEach(function(cb) {
            cb.checked = checkbox.checked;
        });
    }
    
    function selectAllOrders() {
        var checkboxes = document.querySelectorAll('input[name="order_ids[]"]');
        checkboxes.forEach(function(cb) {
            cb.checked = true;
        });
        document.getElementById('select-all-checkbox').checked = true;
    }
    
    function deselectAllOrders() {
        var checkboxes = document.querySelectorAll('input[name="order_ids[]"]');
        checkboxes.forEach(function(cb) {
            cb.checked = false;
        });
        document.getElementById('select-all-checkbox').checked = false;
    }
    </script>
    <?php
}

// Функция для генерации сводки
function gustolocal_generate_breakdown($order_ids) {
    $dishes_by_category = array(); // [sale_type][category][dish_key] = dish_data
    $customers = array(); // [order_id] = customer_data
    $total_sum = 0;
    $total_portions = 0;
    
    // Получаем все блюда из базы
    $all_dishes = gustolocal_get_all_dishes();
    
    // Инициализируем структуру для всех блюд
    foreach ($all_dishes as $dish_key => $dish_data) {
        $sale_type = $dish_data['sale_type'];
        $category = $dish_data['category'];
        
        if (!isset($dishes_by_category[$sale_type])) {
            $dishes_by_category[$sale_type] = array();
        }
        
        if (!isset($dishes_by_category[$sale_type][$category])) {
            $dishes_by_category[$sale_type][$category] = array();
        }
        
        if (!isset($dishes_by_category[$sale_type][$category][$dish_key])) {
            $dishes_by_category[$sale_type][$category][$dish_key] = array(
                'name' => $dish_data['name'],
                'unit' => $dish_data['unit'],
                'category' => $category,
                'category_order' => $dish_data['category_order'],
                'sale_type' => $sale_type,
                'quantities' => array(), // [order_id] => qty
            );
        }
    }
    
    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) continue;
        
        // Информация о клиенте
        $customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        if ($customer_name === '') {
            $customer_name = $order->get_billing_company() ?: 'Гость';
        }
        
        // Определяем самовывоз с учетом fee/мета/метода доставки
        $is_pickup = gustolocal_is_pickup_order($order);
        
        // Получаем примечания к заказу
        $customer_note = $order->get_customer_note();
        $additional_note = '';
        
        // Пробуем разные варианты названий мета-поля "Дополнительно"
        // Поле настроено через Checkout Field Editor с именем "else" и этикеткой "Дополнительно"
        $meta_keys_to_check = array(
            'else',              // Прямое имя поля из Checkout Field Editor (самый вероятный)
            '_else',             // С префиксом подчеркивания
            'billing_else',      // С префиксом billing
            '_billing_else',     // С префиксом billing и подчеркиванием
            'additional_else',   // С префиксом additional
            '_additional_else',  // С префиксом additional и подчеркиванием
            '_additional',
            'Дополнительно',
            'additional',
            'order_additional',
            '_order_additional',
            'billing_additional',
            '_billing_additional',
            'billing_Дополнительно',
            '_billing_Дополнительно',
            'Дополнительная информация',
            '_Дополнительная информация'
        );
        
        // Сначала пробуем конкретные ключи (самые вероятные)
        foreach ($meta_keys_to_check as $meta_key) {
            $meta_value = $order->get_meta($meta_key, true);
            if (!empty($meta_value) && is_string($meta_value)) {
                $additional_note = $meta_value;
                break;
            }
        }
        
        // Если не нашли через конкретные ключи, проверяем все мета-данные заказа
        if (empty($additional_note)) {
            $all_meta = $order->get_meta_data();
            foreach ($all_meta as $meta) {
                $meta_key = $meta->key;
                $meta_value = $meta->value;
                
                // Пропускаем системные поля WooCommerce
                if (strpos($meta_key, '_billing_') === 0 || 
                    strpos($meta_key, '_shipping_') === 0 ||
                    strpos($meta_key, '_order_') === 0 ||
                    in_array($meta_key, array('_payment_method', '_payment_method_title', '_transaction_id'))) {
                    continue;
                }
                
                // Проверяем, содержит ли ключ или значение слово "дополнительно", "else" или похожие
                if ((stripos($meta_key, 'дополнительно') !== false || 
                     stripos($meta_key, 'additional') !== false ||
                     stripos($meta_key, 'note') !== false ||
                     stripos($meta_key, 'comment') !== false ||
                     stripos($meta_key, 'else') !== false) &&
                    !empty($meta_value) && is_string($meta_value)) {
                    $additional_note = $meta_value;
                    break;
                }
            }
        }
        
        // Объединяем примечания
        $order_notes = trim($customer_note . ($additional_note ? ($customer_note ? ' | ' : '') . $additional_note : ''));
        
        $customers[$order_id] = array(
            'name' => $customer_name,
            'order_id' => $order_id,
            'is_pickup' => $is_pickup,
            'total' => $order->get_total(),
            'notes' => $order_notes,
        );
        
        $total_sum += $order->get_total();
        
        // Извлекаем блюда из заказа
        $order_dishes = gustolocal_extract_dishes_from_order($order);
        
        foreach ($order_dishes as $dish_key => $dish_data) {
            $category = $dish_data['category'];
            $sale_type = gustolocal_get_dish_sale_type($dish_data['name']);
            
            // Если блюдо не найдено в базе, добавляем его
            if (!isset($dishes_by_category[$sale_type][$category][$dish_key])) {
                if (!isset($dishes_by_category[$sale_type])) {
                    $dishes_by_category[$sale_type] = array();
                }
                if (!isset($dishes_by_category[$sale_type][$category])) {
                    $dishes_by_category[$sale_type][$category] = array();
                }
                $dishes_by_category[$sale_type][$category][$dish_key] = array(
                    'name' => $dish_data['name'],
                    'unit' => $dish_data['unit'],
                    'category' => $category,
                    'category_order' => $dish_data['category_order'],
                    'sale_type' => $sale_type,
                    'quantities' => array(),
                );
            }
            
            $dishes_by_category[$sale_type][$category][$dish_key]['quantities'][$order_id] = $dish_data['total_qty'];
            $total_portions += $dish_data['total_qty'];
        }
    }
    
    // Сортируем по sale_type (superfood первый, mercat второй)
    $sorted_dishes = array();
    if (isset($dishes_by_category['superfood'])) {
        $sorted_dishes['superfood'] = $dishes_by_category['superfood'];
    }
    if (isset($dishes_by_category['mercat'])) {
        $sorted_dishes['mercat'] = $dishes_by_category['mercat'];
    }
    
    // Сортируем категории внутри каждого sale_type по порядку
    foreach ($sorted_dishes as $sale_type => $categories) {
        uasort($sorted_dishes[$sale_type], function($a, $b) {
        $order_a = !empty($a) ? reset($a)['category_order'] : 999;
        $order_b = !empty($b) ? reset($b)['category_order'] : 999;
        return $order_a - $order_b;
    });
    }
    
    return array(
        'dishes_by_sale_type' => $sorted_dishes,
        'customers' => $customers,
        'total_sum' => $total_sum,
        'total_portions' => $total_portions,
        'order_ids' => $order_ids,
    );
}

// Функция для умножения всех чисел в строке на множитель
function gustolocal_multiply_numbers_in_string($unit, $multiplier) {
    if (empty($unit) || $multiplier <= 0) {
        return $unit;
    }
    
    // Заменяем все числа на умноженные значения
    $result = preg_replace_callback(
        '/\d+(?:[.,]\d+)?/',
        function($matches) use ($multiplier) {
            $number = floatval(str_replace(',', '.', $matches[0]));
            $multiplied = $number * $multiplier;
            // Если было целое число, возвращаем целое, иначе с десятичными
            if (strpos($matches[0], '.') === false && strpos($matches[0], ',') === false) {
                return (string)intval($multiplied);
            }
            return number_format($multiplied, 2, '.', '');
        },
        $unit
    );
    
    return $result;
}

// Функция для генерации формулы Google Sheets для расчета итогового веса
// Возвращает массив с формулами для разных случаев
function gustolocal_generate_weight_formula($unit, $qty_cell_ref, $unit_cell_ref = '') {
    if (empty($unit)) {
        return array('formula' => '', 'type' => 'empty', 'description' => '');
    }
    
    // Проверяем, является ли формат сложным (содержит "/" или скобки с числами)
    $has_slashes = (strpos($unit, '/') !== false);
    $has_brackets_with_numbers = preg_match('/\([^)]*\d+[^)]*\)/', $unit);
    
    // Для сложных случаев - создаем формулу с использованием Apps Script функции
    if ($has_slashes || $has_brackets_with_numbers) {
        // Извлекаем все числа из единицы измерения для создания формулы
        preg_match_all('/\d+(?:[.,]\d+)?/', $unit, $matches);
        
        if (!empty($matches[0])) {
            // Создаем формулу, которая будет использовать пользовательскую функцию Apps Script
            // MULTIPLY_NUMBERS_IN_STRING(unit, multiplier)
            // Если единица хранится в ячейке A (название блюда), извлекаем её оттуда
            // Или используем прямое значение
            $unit_escaped = str_replace('"', '""', $unit);
            
            // Формула с использованием Apps Script функции
            // Используем INDIRECT для фиксированной ссылки на колонку B, чтобы она не менялась при перемещении колонки
            // Извлекаем номер строки из ссылки (например, $B25 -> 25)
            $row_num = preg_replace('/[^0-9]/', '', $qty_cell_ref);
            $b_cell_ref = 'INDIRECT("B' . $row_num . '")';
            // Добавляем проверку на ноль, как в простых случаях
            $formula_apps_script = '=IF(' . $b_cell_ref . '=0,"",MULTIPLY_NUMBERS_IN_STRING("' . $unit_escaped . '", ' . $b_cell_ref . '))';
            
            // Альтернатива: если единица хранится в отдельной колонке или извлекается из названия
            // Можно использовать REGEXEXTRACT для извлечения единицы из названия блюда
            // Но проще использовать прямое значение
            
            $numbers_list = implode(', ', $matches[0]);
            $description = 'Сложная единица: ' . $unit . ' (числа: ' . $numbers_list . '). Используйте Apps Script функцию.';
            
            return array(
                'formula' => $formula_apps_script,
                'type' => 'complex',
                'description' => $description,
                'numbers' => $matches[0],
                'unit' => $unit,
                'instruction' => 'Добавьте функцию MULTIPLY_NUMBERS_IN_STRING в Apps Script (см. инструкцию выше)'
            );
        }
        
        return array('formula' => '', 'type' => 'complex', 'description' => 'Не удалось извлечь числа из: ' . $unit);
    }
    
    // Простые случаи: "200 г", "1200 мл" - просто умножаем число
    if (preg_match('/^(\d+(?:[.,]\d+)?)\s*(г|мл|кг|л|шт|пор)/ui', $unit, $matches)) {
        $value = floatval(str_replace(',', '.', $matches[1]));
        $unit_type = $matches[2];
        // Формула: количество * значение единицы измерения
        // Используем INDIRECT для фиксированной ссылки на колонку B, чтобы она не менялась при перемещении колонки
        // Извлекаем номер строки из ссылки (например, $B25 -> 25)
        $row_num = preg_replace('/[^0-9]/', '', $qty_cell_ref);
        $b_cell_ref = 'INDIRECT("B' . $row_num . '")';
        $formula = '=IF(' . $b_cell_ref . '=0,"",' . $b_cell_ref . '*' . $value . '&" ' . $unit_type . '")';
        return array(
            'formula' => $formula,
            'type' => 'simple',
            'description' => 'Простая единица: ' . $value . ' ' . $unit_type
        );
    }
    
    return array('formula' => '', 'type' => 'unknown', 'description' => 'Неизвестный формат: ' . $unit);
}

// Функция для вычисления итогового веса блюда
function gustolocal_calculate_dish_weight($dish_data, $quantities) {
    if (empty($dish_data['unit'])) {
        return array('total' => null, 'display' => '');
    }
    
    $total_qty = array_sum($quantities);
    
    if ($total_qty <= 0) {
        return array('total' => null, 'display' => '');
    }
    
    // Проверяем, является ли формат сложным (содержит "/" или скобки с числами)
    $has_slashes = (strpos($dish_data['unit'], '/') !== false);
    $has_brackets_with_numbers = preg_match('/\([^)]*\d+[^)]*\)/', $dish_data['unit']);
    
    // Если это сложный формат - умножаем все числа
    if ($has_slashes || $has_brackets_with_numbers) {
        // Сложные случаи: умножаем все числа в строке
        // "250/ 400/ 60 (2 пор)" -> "750/ 1200/ 180 (6 пор)"
        // "200 г (8 шт)" -> "400 г (16 шт)"
        $multiplied_unit = gustolocal_multiply_numbers_in_string($dish_data['unit'], $total_qty);
        
        // Для расчета общего веса в сложных случаях берем первое число
        preg_match('/^(\d+(?:[.,]\d+)?)/', $dish_data['unit'], $first_num_match);
        $total_weight = null;
        if (!empty($first_num_match)) {
            $first_value = floatval(str_replace(',', '.', $first_num_match[1]));
            $total_weight = $first_value * $total_qty;
        }
        
        return array(
            'total' => $total_weight,
            'display' => $multiplied_unit
        );
    }
    
    // Простые случаи: "200 г", "1200 мл" - просто умножаем число
    if (preg_match('/^(\d+(?:[.,]\d+)?)\s*(г|мл|кг|л|шт|пор)/ui', $dish_data['unit'], $matches)) {
        $value = floatval(str_replace(',', '.', $matches[1]));
        $unit_type = $matches[2];
        $total_weight = $value * $total_qty;
        return array(
            'total' => $total_weight,
            'display' => number_format($total_weight, 0, ',', ' ') . ' ' . $unit_type
        );
    }
    
    // Если ничего не подошло
    return array('total' => null, 'display' => '');
}

// Функция для отображения сводной таблицы
function gustolocal_display_breakdown_table($data) {
    $dishes_by_sale_type = isset($data['dishes_by_sale_type']) ? $data['dishes_by_sale_type'] : array();
    $customers = $data['customers'];
    $total_sum = $data['total_sum'];
    $total_portions = $data['total_portions'];
    $order_ids = $data['order_ids'];
    
    // Определяем количество колонок для формул
    $num_customer_cols = count($customers);
    $first_customer_col = 3; // A=0 (Блюдо), B=1 (ИТОГО), C=2 (Итоговый вес), D=3 (первый клиент)
    
    // Пересчитываем суммы из заказов для проверки
    $recalculated_sum = 0;
    $recalculated_portions = 0;
    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $recalculated_sum += $order->get_total();
            $order_dishes = gustolocal_extract_dishes_from_order($order);
            foreach ($order_dishes as $dish_data) {
                $recalculated_portions += $dish_data['total_qty'];
            }
        }
    }
    
    ?>
    <style>
    .breakdown-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 13px;
    }
    .breakdown-table th,
    .breakdown-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .breakdown-table th {
        background-color: #f5f5f5;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .breakdown-table .category-header {
        background-color: #e8f4f8;
        font-weight: bold;
        font-size: 14px;
    }
    .breakdown-table .category-header-cell {
        background-color: #e8f4f8;
        font-weight: bold;
        font-size: 14px;
    }
    .breakdown-table .sale-type-header {
        background-color: #cfe2f3;
        font-weight: bold;
        font-size: 15px;
    }
    .breakdown-table .dish-row {
        background-color: #fff;
    }
    .breakdown-table .total-row {
        background-color: #fff3cd;
        font-weight: bold;
    }
    .breakdown-table .customer-col {
        min-width: 150px;
        text-align: center;
    }
    .breakdown-table .dish-col {
        min-width: 200px;
    }
    .breakdown-table .qty-cell {
        text-align: center;
        font-weight: bold;
    }
    .breakdown-table .pickup-badge {
        display: inline-block;
        background-color: #d1ecf1;
        color: #0c5460;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        margin-left: 5px;
    }
    .breakdown-table .delivery-badge {
        display: inline-block;
        background-color: #d4edda;
        color: #155724;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        margin-left: 5px;
    }
    .breakdown-table .notes-cell {
        font-size: 11px;
        color: #666;
        font-style: italic;
        max-width: 200px;
    }
    .breakdown-verification {
        margin-top: 20px;
        padding: 15px;
        background-color: #f0f0f0;
        border-left: 4px solid #0073aa;
    }
    .breakdown-verification.ok {
        border-left-color: #46b450;
    }
    .breakdown-verification.error {
        border-left-color: #dc3232;
    }
    </style>
    
    <div class="breakdown-verification <?php echo ($recalculated_sum == $total_sum && $recalculated_portions == $total_portions) ? 'ok' : 'error'; ?>">
        <h3>Проверка данных</h3>
        <p><strong>Сумма заказов:</strong> <?php echo wc_price($total_sum); ?> 
        <?php if ($recalculated_sum != $total_sum): ?>
            <span style="color: #dc3232;">(Ожидалось: <?php echo wc_price($recalculated_sum); ?>)</span>
        <?php else: ?>
            <span style="color: #46b450;">✓</span>
        <?php endif; ?>
        </p>
        <p><strong>Общее количество порций:</strong> <?php echo number_format($total_portions, 0, ',', ' '); ?> 
        <?php if ($recalculated_portions != $total_portions): ?>
            <span style="color: #dc3232;">(Ожидалось: <?php echo number_format($recalculated_portions, 0, ',', ' '); ?>)</span>
        <?php else: ?>
            <span style="color: #46b450;">✓</span>
        <?php endif; ?>
        </p>
    </div>
    
    <div style="margin-bottom: 15px; text-align: right;">
        <button id="copy-table-btn" style="background-color: #0073aa; color: white; border: none; padding: 10px 20px; font-size: 14px; cursor: pointer; border-radius: 3px; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.backgroundColor='#005a87'" onmouseout="this.style.backgroundColor='#0073aa'">
            <span style="font-size: 16px;">📋</span>
            <span>Скопировать таблицу</span>
        </button>
    </div>
    
    <div style="display: none;">
        <!-- Скрытый блок инструкции для справки -->
        <div style="margin-bottom: 20px; padding: 15px; background-color: #e3f2fd; border-left: 4px solid #2196f3;">
            <h3 style="margin-top: 0;">📋 Инструкция по использованию формул в Google Sheets</h3>
        <p><strong>Для сложных единиц измерения</strong> (например, "250/150 г (2 пор)") нужно использовать пользовательскую функцию Apps Script:</p>
        <ol style="line-height: 1.8;">
            <li>В Google Sheets откройте <strong>Расширения → Apps Script</strong> (или <strong>Инструменты → Редактор скриптов</strong>)</li>
            <li>Удалите весь код по умолчанию (если есть)</li>
            <li>Вставьте следующий код:</li>
        </ol>
        <pre style="background: #fff; padding: 15px; border: 1px solid #ddd; overflow-x: auto; font-size: 12px; line-height: 1.5;"><code>function MULTIPLY_NUMBERS_IN_STRING(unit, multiplier) {
  // Проверяем входные данные
  if (!unit || unit === "") return "";
  if (!multiplier || multiplier <= 0) return unit || "";
  
  // Заменяем все числа на умноженные значения
  return unit.replace(/\d+(?:[.,]\d+)?/g, function(match) {
    var num = parseFloat(match.replace(',', '.'));
    if (isNaN(num)) return match; // Если не число, оставляем как есть
    var multiplied = num * multiplier;
    // Если было целое число, возвращаем целое
    if (match.indexOf('.') === -1 && match.indexOf(',') === -1) {
      return Math.round(multiplied).toString();
    }
    return multiplied.toFixed(2);
  });
}</code></pre>
        <ol start="4" style="line-height: 1.8;">
            <li>Нажмите <strong>Сохранить</strong> (Ctrl+S или Cmd+S) - появится кнопка "Сохранить проект" вверху</li>
            <li><strong>ВАЖНО:</strong> При первом использовании функции Google может запросить разрешения:
                <ul style="margin-top: 5px;">
                    <li>Нажмите <strong>"Проверить разрешения"</strong> или <strong>"Review permissions"</strong></li>
                    <li>Выберите свой аккаунт Google</li>
                    <li>Нажмите <strong>"Разрешить"</strong> или <strong>"Allow"</strong> (функция безопасна, она работает только в вашей таблице)</li>
                </ul>
            </li>
            <li>Закройте вкладку Apps Script и вернитесь в Google Sheets</li>
            <li>Теперь вы можете использовать формулу из колонки "Формула Вес" в ячейках колонки C (Итоговый вес)</li>
            <li>Формула будет выглядеть так: <code>=MULTIPLY_NUMBERS_IN_STRING("250/150 г (2 пор)", B5)</code></li>
            <li>При изменении количества в колонке B, вес в колонке C будет автоматически пересчитываться</li>
        </ol>
        <p style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border-left: 3px solid #ffc107;"><strong>💡 Совет:</strong> Скопируйте формулу из колонки "Формула Вес" и вставьте её в соответствующую ячейку колонки C. Формула уже содержит правильные ссылки на ячейки.</p>
        <p style="margin-top: 10px; padding: 10px; background-color: #e8f5e9; border-left: 3px solid #4caf50;"><strong>✅ Проверка:</strong> Если функция не работает, убедитесь что:
            <ul style="margin-top: 5px;">
                <li>Код сохранен в Apps Script (кнопка "Сохранить проект" должна быть неактивна)</li>
                <li>Вы дали разрешения при первом использовании (может появиться предупреждение при вводе формулы)</li>
                <li>Формула в ячейке начинается с <code>=</code> (знак равенства)</li>
                <li>Название функции написано точно: <code>MULTIPLY_NUMBERS_IN_STRING</code> (регистр важен!)</li>
                <li>Попробуйте перезагрузить страницу Google Sheets после сохранения функции</li>
            </ul>
        </p>
        <p style="margin-top: 10px; padding: 10px; background-color: #e8f5e9; border-left: 3px solid #4caf50;"><strong>✅ Проверка:</strong> Если функция не работает, убедитесь что:
            <ul style="margin-top: 5px;">
                <li>Код сохранен в Apps Script (кнопка "Сохранить проект" должна быть неактивна)</li>
                <li>Вы дали разрешения при первом использовании</li>
                <li>Формула в ячейке начинается с <code>=</code> (знак равенства)</li>
                <li>Название функции написано точно: <code>MULTIPLY_NUMBERS_IN_STRING</code> (регистр важен!)</li>
            </ul>
        </p>
    </div>
    </div>
    
    <div style="margin-bottom: 15px; text-align: right;">
        <button id="copy-table-btn" style="background-color: #0073aa; color: white; border: none; padding: 10px 20px; font-size: 14px; cursor: pointer; border-radius: 3px; display: inline-flex; align-items: center; gap: 8px;" onmouseover="this.style.backgroundColor='#005a87'" onmouseout="this.style.backgroundColor='#0073aa'">
            <span style="font-size: 16px;">📋</span>
            <span>Скопировать таблицу</span>
        </button>
    </div>
    
    <div style="overflow-x: auto; max-width: 100%;">
        <table class="breakdown-table" id="breakdown-table">
            <thead>
                <tr>
                    <th class="dish-col">Блюдо</th>
                    <th class="total-row">ИТОГО</th>
                    <th class="total-row">Итоговый вес</th>
                    <?php 
                    $col_index = 0;
                    foreach ($customers as $order_id => $customer): 
                        $col_index++;
                    ?>
                        <th class="customer-col">
                            <?php echo esc_html($customer['name']); ?><br>
                            <small>#<?php echo esc_html($order_id); ?></small><br>
                            <?php if ($customer['is_pickup']): ?>
                                <span class="pickup-badge">Самовывоз</span>
                            <?php else: ?>
                                <span class="delivery-badge">Доставка</span>
                            <?php endif; ?><br>
                            <strong style="font-size: 12px; margin-top: 5px; display: block;"><?php echo wc_price($customer['total']); ?></strong>
                        </th>
                    <?php endforeach; ?>
                    <th class="formula-col" style="background-color: #e8f5e9; min-width: 120px; font-size: 12px; text-align: center; font-weight: bold;">
                        Итого
                    </th>
                    <th class="formula-col" style="background-color: #fff3cd; min-width: 150px; font-size: 12px; text-align: center; font-weight: bold;">
                        ВЕС
                    </th>
                </tr>
                <tr>
                    <th class="dish-col"></th>
                    <th class="total-row"></th>
                    <th class="total-row"></th>
                    <?php foreach ($customers as $order_id => $customer): ?>
                        <th class="customer-col notes-header-cell" style="text-align: center; font-weight: normal; font-size: 11px; padding: 5px;">
                            <?php if (!empty($customer['notes'])): ?>
                                <div class="notes-cell" style="font-style: italic; color: #666; word-wrap: break-word; max-width: 200px; margin: 0 auto;">
                                    <?php echo esc_html($customer['notes']); ?>
                                </div>
                            <?php else: ?>
                                &nbsp;
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                    <th class="formula-col" style="background-color: #e8f5e9;"></th>
                    <th class="formula-col" style="background-color: #fff3cd;"></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Обрабатываем сначала superfood, потом mercat
                $sale_types_order = array('superfood', 'mercat');
                $row_index = 0; // Индекс строки в tbody (начинается с 0)
                $excel_base_row = 2; // Базовый номер строки в Excel (заголовок = 1, примечания = 2, данные начинаются с 3)
                
                foreach ($sale_types_order as $sale_type):
                    if (!isset($dishes_by_sale_type[$sale_type]) || empty($dishes_by_sale_type[$sale_type])) {
                        continue;
                    }
                    
                    $sale_type_label = ($sale_type === 'superfood') ? 'Superfood' : 'Mercat';
                    $row_index++; // Секция занимает строку
                ?>
                    <tr class="sale-type-header">
                        <?php 
                        // Заголовок секции - каждая ячейка отдельно для избежания объединения
                        echo '<td class="sale-type-header"><strong>' . esc_html($sale_type_label) . '</strong></td>';
                        // Остальные ячейки пустые, но не объединенные
                        for ($i = 0; $i < $num_customer_cols + 2; $i++) {
                            echo '<td class="sale-type-header"></td>';
                        }
                        // Колонки формул (2 колонки)
                        echo '<td class="formula-col" style="background-color: #e8f5e9;"></td>';
                        echo '<td class="formula-col" style="background-color: #fff3cd;"></td>';
                        ?>
                    </tr>
                    
                <?php 
                $current_category = '';
                    foreach ($dishes_by_sale_type[$sale_type] as $category => $dishes): 
                    if ($current_category !== $category):
                        $current_category = $category;
                            $row_index++; // Категория занимает строку
                ?>
                    <tr class="category-header">
                            <?php 
                            // Категория - каждая ячейка отдельно
                            echo '<td class="category-header-cell"><strong>' . esc_html($category) . '</strong></td>';
                            // Остальные ячейки пустые, но не объединенные
                            for ($i = 0; $i < $num_customer_cols + 2; $i++) {
                                echo '<td class="category-header-cell"></td>';
                            }
                            // Колонки формул (2 колонки)
                            echo '<td class="formula-col" style="background-color: #e8f5e9;"></td>';
                            echo '<td class="formula-col" style="background-color: #fff3cd;"></td>';
                            ?>
                    </tr>
                <?php endif; ?>
                
                <?php foreach ($dishes as $dish_key => $dish_data): 
                    $dish_total = array_sum($dish_data['quantities']);
                    $weight_info = gustolocal_calculate_dish_weight($dish_data, $dish_data['quantities']);
                        $row_index++; // Блюдо занимает строку
                        
                        // Формируем формулу для ИТОГО (сумма всех колонок клиентов для этой строки)
                        // Используем относительные ссылки: сумма колонок клиентов в текущей строке
                        // ВАЖНО: колонки клиентов начинаются с D (индекс 3), колонки формул находятся после них
                        $customer_cols = array();
                        $col_idx = 0;
                        foreach ($customers as $order_id => $customer) {
                            // Колонка клиента = first_customer_col (3 = D) + col_idx (0, 1, 2, ...)
                            $col_letter = gustolocal_get_column_letter($first_customer_col + $col_idx);
                            $customer_cols[] = $col_letter; // Будем использовать в формуле
                            $col_idx++;
                        }
                        
                        // Номер строки в Google Sheets
                        // Структура: строка 1 = заголовок, строка 2 = примечания, строка 3+ = данные (tbody)
                        // row_index начинается с 0 и увеличивается для каждой строки в tbody (секции, категории, блюда)
                        // excel_base_row = 2 (примечания), данные начинаются с 3
                        // Для первой строки данных (секция): row_index = 1, excel_row = 1 + 2 = 3 ✓
                        // Для второй строки данных (категория): row_index = 2, excel_row = 2 + 2 = 4 ✓
                        // Для третьей строки данных (блюдо): row_index = 3, excel_row = 3 + 2 = 5 ✓
                        $excel_row = $row_index + $excel_base_row;
                        
                        // Формируем формулу для ИТОГО
                        // После перемещения колонки на место B, формула должна суммировать все колонки клиентов начиная с D
                        // Используем большой диапазон D:ZZ чтобы покрыть все возможные колонки клиентов
                        $total_formula = '=SUM(D' . $excel_row . ':ZZ' . $excel_row . ')';
                        
                        // Генерируем формулу для веса
                        // После перемещения колонки на место C, формула должна ссылаться на колонку B (Итого)
                        $weight_formula_info = array('formula' => '', 'description' => '');
                        if (!empty($dish_data['unit'])) {
                            // Используем смешанную ссылку: $B{row} (абсолютная колонка B, относительная строка)
                            // Это позволит при копировании сохранить ссылку на колонку B, но адаптировать строку
                            $qty_cell = '$B' . $excel_row; // Колонка ИТОГО с абсолютной ссылкой на колонку
                            $weight_formula_info = gustolocal_generate_weight_formula($dish_data['unit'], $qty_cell);
                        }
                    ?>
                        <tr class="dish-row" data-row-index="<?php echo $row_index; ?>">
                        <td class="dish-col">
                            <?php echo esc_html($dish_data['name']); ?>
                            <?php if ($dish_data['unit']): ?>
                                <small style="color: #666;">(<?php echo esc_html($dish_data['unit']); ?>)</small>
                            <?php endif; ?>
                        </td>
                            <td class="qty-cell total-row" data-formula-template="<?php echo esc_attr($total_formula); ?>" data-customer-cols="<?php echo esc_attr(implode(',', $customer_cols)); ?>">
                                <?php echo $dish_total; ?>
                            </td>
                        <td class="qty-cell total-row" style="text-align: left;">
                            <?php if ($weight_info['display']): ?>
                                <?php echo esc_html($weight_info['display']); ?>
                            <?php else: ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                            <?php 
                            $col_idx = 0;
                            foreach ($customers as $order_id => $customer): 
                            $qty = isset($dish_data['quantities'][$order_id]) ? $dish_data['quantities'][$order_id] : 0;
                                $col_idx++;
                        ?>
                                <td class="qty-cell" data-col-letter="<?php echo esc_attr(gustolocal_get_column_letter($first_customer_col + $col_idx - 1)); ?>">
                                    <?php echo $qty > 0 ? $qty : ''; ?>
                                </td>
                        <?php endforeach; ?>
                            <!-- Колонка формул для ИТОГО -->
                            <td class="formula-col total-row" style="background-color: #fff3cd; font-size: 12px; font-weight: bold; text-align: center; padding: 5px;" data-formula="<?php echo esc_attr($total_formula); ?>">
                                <?php if (!empty($total_formula)): ?>
                                    <div style="font-family: monospace; background: #fff; padding: 4px; border: 1px solid #c8e6c9; font-size: 10px; text-align: left;">
                                        <?php echo esc_html($total_formula); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <!-- Колонка формул для Веса -->
                            <td class="formula-col total-row" style="background-color: #fff3cd; font-size: 12px; font-weight: bold; text-align: center; padding: 5px;" data-formula="<?php echo !empty($weight_formula_info['formula']) ? esc_attr($weight_formula_info['formula']) : ''; ?>">
                                <?php if (!empty($weight_formula_info['formula'])): ?>
                                    <div style="font-family: monospace; background: #fff; padding: 4px; border: 1px solid #ffcc02; font-size: 10px; text-align: left;">
                                        <?php echo esc_html($weight_formula_info['formula']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <?php endforeach; ?>
                
                    <?php 
                // Строка ИТОГО
                $row_index++; // Итоговая строка занимает строку
                
                    // Подсчитываем общее количество порций для каждого клиента
                    $customer_totals = array();
                foreach ($dishes_by_sale_type as $sale_type => $categories) {
                    foreach ($categories as $category => $dishes) {
                        foreach ($dishes as $dish_data) {
                            foreach ($dish_data['quantities'] as $order_id => $qty) {
                                if (!isset($customer_totals[$order_id])) {
                                    $customer_totals[$order_id] = 0;
                                }
                                $customer_totals[$order_id] += $qty;
                            }
                        }
                    }
                }
                
                // Формируем формулы для итоговой строки
                // После перемещения колонки на место B, формула должна суммировать все колонки клиентов в этой строке
                $excel_total_row = $row_index + $excel_base_row; // Номер строки итогов в Google Sheets
                // Формула суммирует все колонки клиентов начиная с D в текущей строке
                $grand_total_formula = '=SUM(D' . $excel_total_row . ':ZZ' . $excel_total_row . ')';
                
                // Собираем колонки клиентов для итоговой строки
                $total_customer_cols = array();
                $col_idx = 0;
                foreach ($customers as $order_id => $customer) {
                    $col_letter = gustolocal_get_column_letter($first_customer_col + $col_idx);
                    $total_customer_cols[] = $col_letter;
                    $col_idx++;
                }
                
                // Формируем строку с формулами для итогов
                $formulas_for_total = array();
                $formulas_for_total[] = 'B' . $excel_total_row . ': ' . $grand_total_formula;
                $col_idx = 0;
                foreach ($customers as $order_id => $customer) {
                    $col_idx++;
                    $customer_col_letter = gustolocal_get_column_letter($first_customer_col + $col_idx - 1);
                    $customer_total_formula = '=SUM(' . $customer_col_letter . $first_data_row . ':' . $customer_col_letter . $last_data_row . ')';
                    $formulas_for_total[] = $customer_col_letter . $excel_total_row . ': ' . $customer_total_formula;
                }
                ?>
                <tr class="total-row" data-is-total-row="1">
                    <td><strong>ИТОГО</strong></td>
                    <td class="qty-cell" data-formula-template="<?php echo esc_attr($grand_total_formula); ?>">
                        <strong><?php echo number_format($total_portions, 0, ',', ' '); ?></strong>
                    </td>
                    <td></td>
                    <?php 
                    $col_idx = 0;
                    foreach ($customers as $order_id => $customer): 
                        $col_idx++;
                        $customer_total = isset($customer_totals[$order_id]) ? $customer_totals[$order_id] : 0;
                        $customer_col_letter = gustolocal_get_column_letter($first_customer_col + $col_idx - 1);
                        // Формула для суммы колонки клиента от начала данных до текущей строки
                        $customer_total_formula = '=SUM(' . $customer_col_letter . $first_data_row . ':' . $customer_col_letter . $last_data_row . ')';
                    ?>
                        <td class="qty-cell" data-formula-template="<?php echo esc_attr($customer_total_formula); ?>" data-col-letter="<?php echo esc_attr($customer_col_letter); ?>">
                            <strong><?php echo $customer_total > 0 ? number_format($customer_total, 0, ',', ' ') : ''; ?></strong>
                        </td>
                    <?php endforeach; ?>
                    <!-- Колонка формул для ИТОГО итоговой строки -->
                    <td class="formula-col total-row" style="background-color: #fff3cd; font-size: 12px; font-weight: bold; text-align: center; padding: 5px;" data-formula="<?php echo esc_attr($grand_total_formula); ?>">
                        <?php if (!empty($grand_total_formula)): ?>
                            <div style="font-family: monospace; background: #fff; padding: 4px; border: 1px solid #c8e6c9; font-size: 10px; text-align: left;">
                                <?php echo esc_html($grand_total_formula); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <!-- Колонка формул для Веса итоговой строки (пустая) -->
                    <td class="formula-col total-row" style="background-color: #fff3cd;"></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <script>
    // Функция для добавления формул при копировании в Google Sheets
    document.addEventListener('DOMContentLoaded', function() {
        var table = document.getElementById('breakdown-table');
        if (!table) return;
        
        // Кнопка копирования таблицы
        var copyBtn = document.getElementById('copy-table-btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                // Выделяем всю таблицу
                var range = document.createRange();
                range.selectNode(table);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
                
                // Копируем
                try {
                    document.execCommand('copy');
                    copyBtn.innerHTML = '<span style="font-size: 16px;">✅</span><span>Скопировано!</span>';
                    copyBtn.style.backgroundColor = '#46b450';
                    
                    // Возвращаем обратно через 2 секунды
                    setTimeout(function() {
                        copyBtn.innerHTML = '<span style="font-size: 16px;">📋</span><span>Скопировать таблицу</span>';
                        copyBtn.style.backgroundColor = '#0073aa';
                    }, 2000);
                } catch (err) {
                    alert('Не удалось скопировать таблицу. Попробуйте выделить таблицу вручную и нажать Ctrl+C');
                }
            });
        }
        
        // Функция для получения буквы колонки по индексу (0=A, 1=B, ...)
        function getColumnLetter(colIndex) {
            var letter = '';
            while (colIndex >= 0) {
                letter = String.fromCharCode(65 + (colIndex % 26)) + letter;
                colIndex = Math.floor(colIndex / 26) - 1;
            }
            return letter;
        }
        
        // Добавляем обработчик копирования
        table.addEventListener('copy', function(e) {
            var selection = window.getSelection();
            if (!selection.rangeCount) return;
            
            var range = selection.getRangeAt(0);
            var selectedCells = [];
            
            // Находим все выбранные ячейки
            var walker = document.createTreeWalker(
                range.commonAncestorContainer,
                NodeFilter.SHOW_ELEMENT,
                function(node) {
                    return (node.tagName === 'TD' || node.tagName === 'TH') ? 
                        NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
                }
            );
            
            var node;
            while (node = walker.nextNode()) {
                if (range.intersectsNode(node)) {
                    selectedCells.push(node);
                }
            }
            
            // Проверяем, есть ли ячейки с формулами
            var hasFormulas = false;
            selectedCells.forEach(function(cell) {
                if (cell.hasAttribute('data-formula-template')) {
                    hasFormulas = true;
                }
            });
            
            if (hasFormulas) {
                // Создаем карту ячеек по их позиции в таблице
                var cellMap = {};
                var minRow = Infinity, maxRow = -Infinity;
                var minCol = Infinity, maxCol = -Infinity;
                
                // Получаем thead и tbody для правильного подсчета строк
                var thead = table.querySelector('thead');
                var tbody = table.querySelector('tbody');
                var headerRowCount = thead ? thead.rows.length : 1;
                
                selectedCells.forEach(function(cell) {
                    var row = cell.parentElement;
                    var tbody = row.parentElement;
                    var rowIndex = Array.from(tbody.children).indexOf(row);
                    var colIndex = Array.from(row.children).indexOf(cell);
                    
                    if (!cellMap[rowIndex]) {
                        cellMap[rowIndex] = {};
                    }
                    cellMap[rowIndex][colIndex] = cell;
                    
                    minRow = Math.min(minRow, rowIndex);
                    maxRow = Math.max(maxRow, rowIndex);
                    minCol = Math.min(minCol, colIndex);
                    maxCol = Math.max(maxCol, colIndex);
                });
                
                // Создаем TSV с формулами
                var tsv = '';
                for (var r = minRow; r <= maxRow; r++) {
                    var rowData = [];
                    for (var c = minCol; c <= maxCol; c++) {
                        var cell = cellMap[r] && cellMap[r][c] ? cellMap[r][c] : null;
                        if (cell) {
                            // Проверяем наличие формулы в атрибутах
                            var formula = null;
                            if (cell.hasAttribute('data-formula-template')) {
                                var formulaTemplate = cell.getAttribute('data-formula-template');
                                // Номер строки в Excel/Google Sheets (начинается с 1, +1 для заголовка)
                                var excelRowNum = r + headerRowCount + 1;
                                
                                // Обрабатываем формулу
                                formula = formulaTemplate;
                                
                                // Для формул SUM с диапазоном заменяем номера строк
                                if (formula.indexOf('SUM') !== -1) {
                                    // Формула вида =SUM(B3:B10) или =SUM(D3:D10)
                                    var match = formula.match(/SUM\(([A-Z]+)(\d+):([A-Z]+)(\d+)\)/);
                                    if (match) {
                                        var startCol = match[1];
                                        var startRowNum = parseInt(match[2]);
                                        var endCol = match[3];
                                        var endRowNum = parseInt(match[4]);
                                        
                                        // Если это итоговая строка, используем диапазон от начала данных до текущей строки
                                        if (cell.parentElement && cell.parentElement.hasAttribute('data-is-total-row')) {
                                            // Для итоговой строки: от строки 3 (начало данных) до строки перед итоговой
                                            formula = '=SUM(' + startCol + '3:' + endCol + (excelRowNum - 1) + ')';
                                        } else {
                                            // Для обычных строк с ИТОГО: сумма колонок клиентов в текущей строке
                                            // Формула должна быть =SUM(D2:E2) где D и E - колонки клиентов
                                            var customerCols = cell.getAttribute('data-customer-cols');
                                            if (customerCols) {
                                                var cols = customerCols.split(',');
                                                if (cols.length > 0) {
                                                    var firstCol = cols[0];
                                                    var lastCol = cols[cols.length - 1];
                                                    formula = '=SUM(' + firstCol + excelRowNum + ':' + lastCol + excelRowNum + ')';
                                                }
                                            }
                                        }
                                    }
                                }
                            } else if (cell.hasAttribute('data-formula')) {
                                // Для колонок формул просто копируем формулу как есть, без обработки
                                // Пусть Google Sheets сам обрабатывает ссылки при вставке
                                formula = cell.getAttribute('data-formula');
                            }
                            
                            if (formula) {
                                rowData.push(formula);
                            } else {
                                rowData.push(cell.textContent.trim());
                            }
                        } else {
                            rowData.push('');
                        }
                    }
                    tsv += rowData.join('\t') + '\n';
                }
                
                e.clipboardData.setData('text/plain', tsv);
                e.preventDefault();
            }
        });
    });
    </script>
    <?php
}

// Вспомогательная функция для получения буквы колонки Excel (A, B, C, ..., Z, AA, AB, ...)
function gustolocal_get_column_letter($col_num) {
    $letter = '';
    while ($col_num >= 0) {
        $letter = chr(65 + ($col_num % 26)) . $letter;
        $col_num = intval($col_num / 26) - 1;
    }
    return $letter;
}

// Функция для отображения сводной таблицы разбора для печати (только первые 3 колонки)
function gustolocal_display_breakdown_table_print($data) {
    $dishes_by_sale_type = isset($data['dishes_by_sale_type']) ? $data['dishes_by_sale_type'] : array();
    $total_portions = $data['total_portions'];
    
    ?>
    <style>
    .breakdown-table-print {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 13px;
    }
    .breakdown-table-print th,
    .breakdown-table-print td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .breakdown-table-print th {
        background-color: #f5f5f5;
        font-weight: bold;
    }
    .breakdown-table-print .category-header {
        background-color: #e8f4f8;
        font-weight: bold;
        font-size: 14px;
    }
    .breakdown-table-print .sale-type-header {
        background-color: #cfe2f3;
        font-weight: bold;
        font-size: 15px;
    }
    .breakdown-table-print .dish-row {
        background-color: #fff;
    }
    .breakdown-table-print .total-row {
        background-color: #fff3cd;
        font-weight: bold;
    }
    .breakdown-table-print .dish-col {
        min-width: 200px;
    }
    .breakdown-table-print .qty-cell {
        text-align: center;
        font-weight: bold;
    }
    </style>
    
    <div style="overflow-x: auto; max-width: 100%;">
        <table class="breakdown-table-print" id="breakdown-table-print">
            <thead>
                <tr>
                    <th class="dish-col">Блюдо</th>
                    <th class="total-row">ИТОГО</th>
                    <th class="total-row">Итоговый вес</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Обрабатываем сначала superfood, потом mercat
                $sale_types_order = array('superfood', 'mercat');
                
                foreach ($sale_types_order as $sale_type):
                    if (!isset($dishes_by_sale_type[$sale_type]) || empty($dishes_by_sale_type[$sale_type])) {
                        continue;
                    }
                    
                    $sale_type_label = ($sale_type === 'superfood') ? 'Superfood' : 'Mercat';
                ?>
                    <tr class="sale-type-header">
                        <td class="sale-type-header" colspan="3"><strong><?php echo esc_html($sale_type_label); ?></strong></td>
                    </tr>
                    
                <?php 
                $current_category = '';
                foreach ($dishes_by_sale_type[$sale_type] as $category => $dishes): 
                    if ($current_category !== $category):
                        $current_category = $category;
                ?>
                    <tr class="category-header">
                        <td class="category-header" colspan="3"><strong><?php echo esc_html($category); ?></strong></td>
                    </tr>
                <?php endif; ?>
                
                <?php foreach ($dishes as $dish_key => $dish_data): 
                    $dish_total = array_sum($dish_data['quantities']);
                    $weight_info = gustolocal_calculate_dish_weight($dish_data, $dish_data['quantities']);
                ?>
                    <tr class="dish-row">
                        <td class="dish-col">
                            <?php echo esc_html($dish_data['name']); ?>
                            <?php if ($dish_data['unit']): ?>
                                <small style="color: #666;">(<?php echo esc_html($dish_data['unit']); ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td class="qty-cell total-row">
                            <?php echo $dish_total; ?>
                        </td>
                        <td class="qty-cell total-row" style="text-align: left;">
                            <?php if ($weight_info['display']): ?>
                                <?php echo esc_html($weight_info['display']); ?>
                            <?php else: ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endforeach; ?>
                <?php endforeach; ?>
                
                <!-- Строка ИТОГО -->
                <tr class="total-row">
                    <td><strong>ИТОГО</strong></td>
                    <td class="qty-cell">
                        <strong><?php echo number_format($total_portions, 0, ',', ' '); ?></strong>
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

/* ========================================
   СИСТЕМА ОБРАТНОЙ СВЯЗИ О БЛЮДАХ
   ======================================== */

// Создание таблицы для хранения отзывов при активации темы
add_action('after_switch_theme', 'gustolocal_create_feedback_table');
add_action('admin_init', 'gustolocal_create_feedback_table'); // Также при загрузке админки
function gustolocal_create_feedback_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dish_feedback';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        token varchar(64) NOT NULL,
        order_id bigint(20) UNSIGNED NOT NULL,
        customer_name varchar(255) DEFAULT '',
        dish_name varchar(255) NOT NULL,
        dish_unit varchar(100) DEFAULT '',
        rating int(1) NOT NULL COMMENT '1=😞, 2=😐, 3=😊, 4=😍',
        comment text DEFAULT '',
        general_comment text DEFAULT '',
        shared_instagram tinyint(1) DEFAULT 0,
        shared_google tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY token (token),
        KEY order_id (order_id),
        KEY dish_name (dish_name)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Создание таблиц для кастомных опросов
    $custom_requests_table = $wpdb->prefix . 'custom_feedback_requests';
    $custom_entries_table = $wpdb->prefix . 'custom_feedback_entries';
    
    $sql_requests = "CREATE TABLE IF NOT EXISTS $custom_requests_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        token varchar(100) NOT NULL,
        client_name varchar(255) NOT NULL,
        client_contact varchar(255) DEFAULT '',
        dishes longtext NOT NULL,
        status varchar(20) DEFAULT 'pending',
        general_comment text DEFAULT '',
        shared_instagram tinyint(1) DEFAULT 0,
        shared_google tinyint(1) DEFAULT 0,
        submitted_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY token (token),
        KEY status (status)
    ) $charset_collate;";
    
    $sql_entries = "CREATE TABLE IF NOT EXISTS $custom_entries_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        request_id bigint(20) UNSIGNED NOT NULL,
        dish_name varchar(255) NOT NULL,
        dish_unit varchar(100) DEFAULT '',
        rating int(1) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY request_id (request_id),
        KEY dish_name (dish_name)
    ) $charset_collate;";
    
    dbDelta($sql_requests);
    dbDelta($sql_entries);
}

add_action('init', 'gustolocal_ensure_feedback_table_columns');
function gustolocal_ensure_feedback_table_columns() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dish_feedback';
    $required_columns = array(
        'shared_instagram' => "ALTER TABLE {$table_name} ADD COLUMN shared_instagram tinyint(1) DEFAULT 0",
        'shared_google'    => "ALTER TABLE {$table_name} ADD COLUMN shared_google tinyint(1) DEFAULT 0",
    );
    
    foreach ($required_columns as $column => $alter_sql) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SHOW COLUMNS FROM {$table_name} LIKE %s",
            $column
        ));
        if (!$exists) {
            $wpdb->query($alter_sql);
        }
    }
    
    // Проверяем колонку dish_unit в таблице кастомных опросов
    $custom_entries_table = $wpdb->prefix . 'custom_feedback_entries';
    $exists = $wpdb->get_var($wpdb->prepare(
        "SHOW COLUMNS FROM {$custom_entries_table} LIKE %s",
        'dish_unit'
    ));
    if (!$exists) {
        $wpdb->query("ALTER TABLE {$custom_entries_table} ADD COLUMN dish_unit varchar(100) DEFAULT '' AFTER dish_name");
    }
    
    // Добавляем индексы для быстрого поиска по клиентам (для будущего экспорта)
    // Индекс на customer_name в dish_feedback
    $index_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.statistics 
         WHERE table_schema = DATABASE() 
         AND table_name = %s 
         AND index_name = 'idx_customer_name'",
        $table_name
    ));
    if (!$index_exists) {
        $wpdb->query("ALTER TABLE {$table_name} ADD INDEX idx_customer_name (customer_name(100))");
    }
    
    // Индекс на client_name в custom_feedback_requests
    $custom_requests_table = $wpdb->prefix . 'custom_feedback_requests';
    $index_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.statistics 
         WHERE table_schema = DATABASE() 
         AND table_name = %s 
         AND index_name = 'idx_client_name'",
        $custom_requests_table
    ));
    if (!$index_exists) {
        $wpdb->query("ALTER TABLE {$custom_requests_table} ADD INDEX idx_client_name (client_name(100))");
    }
    
    // Индекс на created_at для фильтрации по датам
    $index_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.statistics 
         WHERE table_schema = DATABASE() 
         AND table_name = %s 
         AND index_name = 'idx_created_at'",
        $table_name
    ));
    if (!$index_exists) {
        $wpdb->query("ALTER TABLE {$table_name} ADD INDEX idx_created_at (created_at)");
    }
    
    // Индекс на submitted_at в custom_feedback_requests
    $index_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.statistics 
         WHERE table_schema = DATABASE() 
         AND table_name = %s 
         AND index_name = 'idx_submitted_at'",
        $custom_requests_table
    ));
    if (!$index_exists) {
        $wpdb->query("ALTER TABLE {$custom_requests_table} ADD INDEX idx_submitted_at (submitted_at)");
    }
}

// Регистрация страницы управления опросами
add_action('admin_menu', 'gustolocal_add_feedback_management_page');
function gustolocal_add_feedback_management_page() {
    // Получаем счетчик новых отзывов
    $new_feedback_count = gustolocal_get_new_feedback_count();
    $menu_title = 'Обратная связь';
    if ($new_feedback_count > 0) {
        $menu_title .= ' <span class="awaiting-mod">' . $new_feedback_count . '</span>';
    }
    
    add_submenu_page(
        'woocommerce',
        'Обратная связь',
        $menu_title,
        'manage_options',
        'gustolocal-feedback',
        'gustolocal_feedback_management_page'
    );
    
    // Получаем счетчик новых кастомных отзывов
    $new_custom_count = gustolocal_get_new_custom_feedback_count();
    $custom_menu_title = 'Кастомные опросы';
    if ($new_custom_count > 0) {
        $custom_menu_title .= ' <span class="awaiting-mod">' . $new_custom_count . '</span>';
    }
    
    add_submenu_page(
        'woocommerce',
        'Кастомные опросы',
        $custom_menu_title,
        'manage_options',
        'gustolocal-custom-feedback',
        'gustolocal_custom_feedback_management_page'
    );
}

// Функция для подсчета новых отзывов (не просмотренных за последние 7 дней)
function gustolocal_get_new_feedback_count() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dish_feedback';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return 0;
    }
    
    // Получаем список просмотренных токенов текущего пользователя
    $viewed_tokens = get_user_meta(get_current_user_id(), '_gustolocal_viewed_feedbacks', true);
    $viewed_tokens_array = !empty($viewed_tokens) ? json_decode($viewed_tokens, true) : array();
    
    // Считаем уникальные токены с отзывами за последние 7 дней, исключая просмотренные
    $query = "
        SELECT COUNT(DISTINCT token)
        FROM $table_name
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ";
    
    if (!empty($viewed_tokens_array)) {
        $placeholders = implode(',', array_fill(0, count($viewed_tokens_array), '%s'));
        $query .= $wpdb->prepare(" AND token NOT IN ($placeholders)", $viewed_tokens_array);
    }
    
    $count = $wpdb->get_var($query);
    
    return intval($count);
}

// Функция для подсчета новых кастомных отзывов
function gustolocal_get_new_custom_feedback_count() {
    global $wpdb;
    $requests_table = $wpdb->prefix . 'custom_feedback_requests';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$requests_table'") != $requests_table) {
        return 0;
    }
    
    // Получаем список просмотренных токенов текущего пользователя
    $viewed_tokens = get_user_meta(get_current_user_id(), '_gustolocal_viewed_custom_feedbacks', true);
    $viewed_tokens_array = !empty($viewed_tokens) ? json_decode($viewed_tokens, true) : array();
    
    // Считаем заполненные опросы за последние 7 дней, исключая просмотренные
    $query = "
        SELECT COUNT(*)
        FROM $requests_table
        WHERE status = 'submitted' 
        AND submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ";
    
    if (!empty($viewed_tokens_array)) {
        $placeholders = implode(',', array_fill(0, count($viewed_tokens_array), '%s'));
        $query .= $wpdb->prepare(" AND token NOT IN ($placeholders)", $viewed_tokens_array);
    }
    
    $count = $wpdb->get_var($query);
    
    return intval($count);
}

// AJAX обработчик для сохранения просмотренного отзыва
add_action('wp_ajax_gustolocal_mark_feedback_viewed', 'gustolocal_mark_feedback_viewed');
function gustolocal_mark_feedback_viewed() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Доступ запрещен');
    }
    
    $token = sanitize_text_field($_POST['token'] ?? '');
    $type = sanitize_text_field($_POST['type'] ?? 'regular'); // 'regular' или 'custom'
    
    if (empty($token)) {
        wp_send_json_error('Токен не указан');
    }
    
    $user_id = get_current_user_id();
    $meta_key = $type === 'custom' ? '_gustolocal_viewed_custom_feedbacks' : '_gustolocal_viewed_feedbacks';
    
    // Получаем текущий список просмотренных токенов
    $viewed_tokens = get_user_meta($user_id, $meta_key, true);
    
    // Обрабатываем разные форматы данных
    if (empty($viewed_tokens)) {
        $viewed_tokens_array = array();
    } elseif (is_array($viewed_tokens)) {
        $viewed_tokens_array = $viewed_tokens;
    } else {
        $decoded = json_decode($viewed_tokens, true);
        $viewed_tokens_array = is_array($decoded) ? $decoded : array();
    }
    
    // Добавляем новый токен, если его еще нет
    if (!in_array($token, $viewed_tokens_array, true)) {
        $viewed_tokens_array[] = $token;
        update_user_meta($user_id, $meta_key, json_encode($viewed_tokens_array));
    }
    
    wp_send_json_success('Отзыв помечен как просмотренный');
}

// AJAX обработчик для получения актуального счетчика новых отзывов
add_action('wp_ajax_gustolocal_get_feedback_count', 'gustolocal_get_feedback_count_ajax');
function gustolocal_get_feedback_count_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Доступ запрещен');
    }
    
    $type = sanitize_text_field($_POST['type'] ?? 'regular');
    
    if ($type === 'custom') {
        $count = gustolocal_get_new_custom_feedback_count();
    } else {
        $count = gustolocal_get_new_feedback_count();
    }
    
    wp_send_json_success(array('count' => $count));
}

// Функция для определения клиентов для опроса
function gustolocal_get_customers_for_feedback($date_from = null, $date_to = null, $status_filter = '') {
    if (!function_exists('wc_get_orders')) {
        return array();
    }
    
    $orders_query = array(
        'limit' => 500,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    if ($date_from && !$date_to) {
        $date_to = $date_from;
    } elseif ($date_to && !$date_from) {
        $date_from = $date_to;
    }
    
    if ($date_from && $date_to) {
        $orders_query['date_created'] = $date_from . '...' . $date_to;
    }
    
    if ($status_filter) {
        $orders_query['status'] = $status_filter;
    } else {
        $orders_query['status'] = array('processing', 'completed', 'on-hold');
    }
    
    $orders = wc_get_orders($orders_query);
    
    $customers_data = array();
    
    foreach ($orders as $order) {
        $order_id = $order->get_id();
        
        // Проверяем, есть ли уже токен для этого заказа
        $token = $order->get_meta('_feedback_token', true);
        
        if (!$token) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'dish_feedback';
            $existing_token = $wpdb->get_var($wpdb->prepare(
                "SELECT token FROM $table_name WHERE order_id = %d LIMIT 1",
                $order_id
            ));
            
            if ($existing_token) {
                $token = $existing_token;
            } else {
                // Генерируем новый токен
                $token = wp_generate_password(32, false);
            }
            
            // Сохраняем токен в мета заказа
            $order->update_meta_data('_feedback_token', $token);
            $order->save();
        }
        
        // Извлекаем блюда из заказа
        $dishes = gustolocal_extract_dishes_from_order($order);
        
        if (empty($dishes)) {
            continue;
        }
        
        $customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        if ($customer_name === '') {
            $customer_name = $order->get_billing_company() ?: 'Гость';
        }
        
        $phone = $order->get_billing_phone();
        $whatsapp_link = $phone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone) : '';
        
        $customers_data[] = array(
            'order_id' => $order_id,
            'customer_name' => $customer_name,
            'phone' => $phone,
            'whatsapp_link' => $whatsapp_link,
            'token' => $token,
            'dishes_count' => count($dishes),
            'order_date' => $order->get_date_created()->date_i18n('d.m.Y H:i'),
            'order_status' => $order->get_status(),
            'order_status_label' => wc_get_order_status_name($order->get_status()),
        );
    }
    
    return $customers_data;
}

// Страница управления опросами (объединенная с результатами)
function gustolocal_feedback_management_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Определяем активную вкладку
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'orders';
    if (!in_array($active_tab, array('orders', 'results'))) {
        $active_tab = 'orders';
    }
    
    $site_url = home_url();
    $page_url = admin_url('admin.php?page=gustolocal-feedback');
    
    // Данные для вкладки "Заказы"
    if ($active_tab === 'orders') {
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
    $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
    $status_filter = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'wc-on-hold';
    
    $customers = gustolocal_get_customers_for_feedback(
        $date_from ? $date_from . ' 00:00:00' : null,
        $date_to ? $date_to . ' 23:59:59' : null,
        $status_filter
    );
    }
    
    // Данные для вкладки "Результаты"
    if ($active_tab === 'results') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dish_feedback';
        
        // Фильтры для результатов
        $results_date_from = isset($_GET['results_date_from']) ? sanitize_text_field($_GET['results_date_from']) : '';
        $results_date_to = isset($_GET['results_date_to']) ? sanitize_text_field($_GET['results_date_to']) : '';
        $results_customer = isset($_GET['results_customer']) ? sanitize_text_field($_GET['results_customer']) : '';
        
        $where_clauses = array();
        if ($results_date_from) {
            $where_clauses[] = $wpdb->prepare("DATE(f.created_at) >= %s", $results_date_from);
        }
        if ($results_date_to) {
            $where_clauses[] = $wpdb->prepare("DATE(f.created_at) <= %s", $results_date_to);
        }
        if ($results_customer) {
            $where_clauses[] = $wpdb->prepare("f.customer_name LIKE %s", '%' . $wpdb->esc_like($results_customer) . '%');
        }
        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
        
        // Последние заказы с отзывами
        $recent_feedback = $wpdb->get_results("
            SELECT 
                MIN(f.id) as id,
                f.token,
                f.order_id,
                f.customer_name,
                DATE_FORMAT(MAX(f.created_at), '%d.%m.%Y %H:%i') as last_date,
                MAX(f.general_comment) as general_comment,
                MAX(f.shared_instagram) as shared_instagram,
                MAX(f.shared_google) as shared_google,
                COUNT(*) as dishes_count,
                ROUND(AVG(f.rating), 2) as avg_rating,
                GROUP_CONCAT(
                    CONCAT(
                        f.dish_name,
                        IF(f.dish_unit != '', CONCAT(' (', f.dish_unit, ')'), ''),
                        '::',
                        f.rating
                    )
                    ORDER BY f.created_at DESC
                    SEPARATOR '||'
                ) as dishes_list
            FROM $table_name f
            $where_sql
            GROUP BY f.token, f.order_id, f.customer_name
            ORDER BY MAX(f.created_at) DESC
            LIMIT 100
        ", ARRAY_A);
        
        $delete_nonce = wp_create_nonce('gustolocal_feedback_delete');
    }
    
    ?>
    <div class="wrap">
        <h1>Обратная связь о блюдах</h1>
        
        <nav class="nav-tab-wrapper" style="margin: 20px 0;">
            <a href="<?php echo esc_url($page_url . '&tab=orders'); ?>" class="nav-tab <?php echo $active_tab === 'orders' ? 'nav-tab-active' : ''; ?>">
                Заказы
            </a>
            <a href="<?php echo esc_url($page_url . '&tab=results'); ?>" class="nav-tab <?php echo $active_tab === 'results' ? 'nav-tab-active' : ''; ?>">
                Результаты
            </a>
        </nav>
        
        <?php if ($active_tab === 'orders'): ?>
        <p>Выберите заказы и отправьте клиентам ссылку на опросник через WhatsApp или Telegram.</p>
        
        <form method="post" action="" style="margin: 20px 0; padding: 20px; background: #f5f5f5; border-radius: 5px;">
            <table class="form-table">
                <tr>
                    <th><label for="date_from">Дата от:</label></th>
                    <td><input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="date_to">Дата до:</label></th>
                    <td><input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="status">Статус:</label></th>
                    <td>
                        <select id="status" name="status" class="regular-text">
                            <option value="">Все статусы</option>
                            <?php
                            $statuses = wc_get_order_statuses();
                            foreach ($statuses as $status_key => $status_label) {
                                $selected = ($status_filter === $status_key) ? 'selected' : '';
                                echo '<option value="' . esc_attr($status_key) . '" ' . $selected . '>' . esc_html($status_label) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button button-primary" value="Применить фильтры">
                <button type="button" class="button" onclick="document.getElementById('date_from').value=''; document.getElementById('date_to').value=''; document.getElementById('status').value=''; this.form.submit();">
                    Показать все заказы
                </button>
            </p>
        </form>
        
        <?php if (empty($customers)): ?>
            <div class="notice notice-info">
                <p>Нет заказов для выбранного периода.</p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">№ заказа</th>
                        <th>Клиент</th>
                        <th>Телефон</th>
                        <th>Дата заказа</th>
                            <th>Статус</th>
                        <th>Блюд</th>
                            <th style="width: 350px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): 
                        $feedback_url = $site_url . '/feedback/' . $customer['token'];
                            $order_edit_url = admin_url('post.php?post=' . $customer['order_id'] . '&action=edit');
                    ?>
                        <tr>
                            <td><strong>#<?php echo esc_html($customer['order_id']); ?></strong></td>
                            <td><?php echo esc_html($customer['customer_name']); ?></td>
                            <td><?php echo esc_html($customer['phone']); ?></td>
                            <td><?php echo esc_html($customer['order_date']); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($customer['order_status']); ?>">
                                        <?php echo esc_html($customer['order_status_label']); ?>
                                    </span>
                                </td>
                            <td><?php echo esc_html($customer['dishes_count']); ?></td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <input type="text" 
                                           id="feedback-link-<?php echo esc_attr($customer['order_id']); ?>" 
                                           value="<?php echo esc_attr($feedback_url); ?>" 
                                           readonly 
                                           style="flex: 1; min-width: 200px; font-size: 11px;">
                                    <button type="button" 
                                            class="button button-small copy-link-btn" 
                                            data-target="feedback-link-<?php echo esc_attr($customer['order_id']); ?>">
                                        Копировать
                                    </button>
                                    <?php if ($customer['whatsapp_link']): ?>
                                        <a href="<?php echo esc_url($customer['whatsapp_link']); ?>" 
                                           target="_blank" 
                                           class="button button-small">
                                            WhatsApp
                                        </a>
                                    <?php endif; ?>
                                        <a href="<?php echo esc_url($order_edit_url); ?>" 
                                           class="button button-small">
                                            Заказ
                                        </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
            
        <?php elseif ($active_tab === 'results'): ?>
            <p>Просмотр всех полученных отзывов. Кликните на строку для просмотра деталей.</p>
            
            <form method="get" action="" style="margin: 20px 0; padding: 20px; background: #f5f5f5; border-radius: 5px;">
                <input type="hidden" name="page" value="gustolocal-feedback">
                <input type="hidden" name="tab" value="results">
                <table class="form-table">
                    <tr>
                        <th><label for="results_date_from">Дата от:</label></th>
                        <td><input type="date" id="results_date_from" name="results_date_from" value="<?php echo esc_attr($results_date_from); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="results_date_to">Дата до:</label></th>
                        <td><input type="date" id="results_date_to" name="results_date_to" value="<?php echo esc_attr($results_date_to); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="results_customer">Клиент:</label></th>
                        <td><input type="text" id="results_customer" name="results_customer" value="<?php echo esc_attr($results_customer); ?>" class="regular-text" placeholder="Поиск по имени"></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Применить фильтры">
                    <a href="<?php echo esc_url($page_url . '&tab=results'); ?>" class="button">Сбросить</a>
                </p>
            </form>
            
            <?php if (empty($recent_feedback)): ?>
                <div class="notice notice-info">
                    <p>Нет отзывов для выбранного периода.</p>
    </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped" id="feedback-results-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;"></th>
                            <th>Дата</th>
                            <th>Клиент</th>
                            <th>Заказ</th>
                            <th>Блюд</th>
                            <th>Средняя</th>
                            <th>Комментарий</th>
                            <th>Instagram</th>
                            <th>Google</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Получаем список просмотренных токенов для текущего пользователя
                        $viewed_tokens = get_user_meta(get_current_user_id(), '_gustolocal_viewed_feedbacks', true);
                        $viewed_tokens_array = !empty($viewed_tokens) ? json_decode($viewed_tokens, true) : array();
                        
                        foreach ($recent_feedback as $feedback): 
                            $order_edit_url = admin_url('post.php?post=' . $feedback['order_id'] . '&action=edit');
                            $is_new = strtotime($feedback['last_date']) >= strtotime('-7 days');
                            $is_viewed = in_array($feedback['token'], $viewed_tokens_array);
                            $show_badge = $is_new && !$is_viewed;
                        ?>
                            <tr class="feedback-row clickable-row" data-token="<?php echo esc_attr($feedback['token']); ?>" style="cursor: pointer;">
                                <td>
                                    <span class="toggle-icon" style="font-size: 18px; color: #0073aa;">▼</span>
                                </td>
                                <td>
                                    <?php echo esc_html($feedback['last_date']); ?>
                                    <span class="new-badge" data-token="<?php echo esc_attr($feedback['token']); ?>" style="color: #f0ad4e; font-size: 14px; display: <?php echo $show_badge ? 'inline' : 'none'; ?>;" title="Новый отзыв">⭐</span>
                                </td>
                                <td><strong><?php echo esc_html($feedback['customer_name']); ?></strong></td>
                                <td>
                                    <a href="<?php echo esc_url($order_edit_url); ?>" target="_blank" onclick="event.stopPropagation();">
                                        #<?php echo esc_html($feedback['order_id']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($feedback['dishes_count']); ?></td>
                                <td>
                                    <strong><?php echo number_format((float) $feedback['avg_rating'], 2); ?></strong>
                                    <?php
                                    $avg = (float) $feedback['avg_rating'];
                                    if ($avg >= 3.5) echo '😍';
                                    elseif ($avg >= 2.5) echo '😊';
                                    elseif ($avg >= 1.5) echo '😐';
                                    else echo '😞';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($feedback['general_comment']): ?>
                                        <?php echo esc_html(wp_trim_words($feedback['general_comment'], 10)); ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo !empty($feedback['shared_instagram']) ? '<span style="color: #E4405F;">Да</span>' : '—'; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo !empty($feedback['shared_google']) ? '<span style="color: #4285F4;">Да</span>' : '—'; ?>
                                </td>
                                <td>
                                    <button class="button button-small delete-feedback-btn" data-token="<?php echo esc_attr($feedback['token']); ?>" onclick="event.stopPropagation();">
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                            <tr class="feedback-details-row" style="display: none;">
                                <td colspan="10" style="background: #f9f9f9; padding: 20px;">
                                    <div style="max-width: 800px;">
                                        <h3 style="margin-top: 0;">Детали отзыва</h3>
                                        
                                        <div style="margin-bottom: 15px;">
                                            <strong>Оценки по блюдам:</strong>
                                            <div style="margin-top: 10px;">
                                                <?php
                                                if (!empty($feedback['dishes_list'])) {
                                                    $items = explode('||', $feedback['dishes_list']);
                                                    foreach ($items as $item) {
                                                        list($name, $rating) = array_pad(explode('::', $item), 2, '');
                                                        $emoji = array('1' => '😞', '2' => '😐', '3' => '😊', '4' => '😍');
                                                        echo '<div style="padding: 5px 0; border-bottom: 1px solid #eee;">';
                                                        echo '<strong>' . esc_html($name) . '</strong>: ';
                                                        echo '<span style="font-size: 20px;">' . ($emoji[$rating] ?? $rating) . '</span>';
                                                        echo '</div>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($feedback['general_comment']): ?>
                                            <div style="margin-bottom: 15px;">
                                                <strong>Комментарий:</strong>
                                                <div style="margin-top: 5px; padding: 10px; background: white; border-radius: 4px;">
                                                    <?php echo nl2br(esc_html($feedback['general_comment'])); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div>
                                            <strong>Поделились:</strong>
                                            <?php if ($feedback['shared_instagram']): ?>
                                                <span style="color: #E4405F;">Instagram ✓</span>
                                            <?php endif; ?>
                                            <?php if ($feedback['shared_google']): ?>
                                                <span style="color: #4285F4;">Google ✓</span>
                                            <?php endif; ?>
                                            <?php if (!$feedback['shared_instagram'] && !$feedback['shared_google']): ?>
                                                Нет
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <style>
    .status-on-hold { color: #f0ad4e; font-weight: bold; }
    .status-processing { color: #5bc0de; font-weight: bold; }
    .status-completed { color: #5cb85c; font-weight: bold; }
    .status-cancelled { color: #d9534f; font-weight: bold; }
    .status-refunded { color: #999; font-weight: bold; }
    .clickable-row:hover { background-color: #f0f6fc !important; }
    .toggle-icon { transition: transform 0.2s; display: inline-block; }
    .toggle-icon.rotated { transform: rotate(180deg); }
    .feedback-details-row td { border-top: 2px solid #0073aa !important; }
    .feedback-details-row { background-color: #f9f9f9 !important; }
    </style>
    
    <script>
    // Глобальные функции для работы с просмотренными отзывами
    function markFeedbackAsViewed(token, type) {
        type = type || 'regular';
        
        // Сохраняем в localStorage для быстрого скрытия звездочки
        var storageKey = type === 'custom' ? 'gustolocal_viewed_custom_feedbacks' : 'gustolocal_viewed_feedbacks';
        var viewed = JSON.parse(localStorage.getItem(storageKey) || '[]');
        if (viewed.indexOf(token) === -1) {
            viewed.push(token);
            localStorage.setItem(storageKey, JSON.stringify(viewed));
        }
        
        // Отправляем на сервер для обновления счетчика в меню
        var formData = new FormData();
        formData.append('action', 'gustolocal_mark_feedback_viewed');
        formData.append('token', token);
        formData.append('type', type);
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                // Обновляем счетчик в меню
                updateMenuCounter(type);
            }
        })
        .catch(function(error) {
            console.error('Ошибка при сохранении просмотренного отзыва:', error);
        });
    }
    
    // Функция для обновления счетчика в меню
    function updateMenuCounter(type) {
        // Получаем актуальный счетчик с сервера
        var formData = new FormData();
        formData.append('action', 'gustolocal_get_feedback_count');
        formData.append('type', type);
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var count = data.data.count || 0;
                var menuSlug = type === 'custom' ? 'gustolocal-custom-feedback' : 'gustolocal-feedback';
                
                // Пробуем разные способы найти элемент меню
                var menuItem = null;
                
                // Способ 1: поиск по href с полным URL
                var allLinks = document.querySelectorAll('a[href*="' + menuSlug + '"]');
                if (allLinks.length > 0) {
                    menuItem = allLinks[0];
                }
                
                // Способ 2: поиск по page параметру
                if (!menuItem) {
                    allLinks = document.querySelectorAll('a[href*="page=' + menuSlug + '"]');
                    if (allLinks.length > 0) {
                        menuItem = allLinks[0];
                    }
                }
                
                // Способ 3: поиск в меню WooCommerce по тексту
                if (!menuItem && type === 'custom') {
                    var wooMenu = document.querySelector('#toplevel_page_woocommerce');
                    if (wooMenu) {
                        var links = wooMenu.querySelectorAll('a');
                        for (var i = 0; i < links.length; i++) {
                            if (links[i].textContent.indexOf('Кастомные опросы') !== -1) {
                                menuItem = links[i];
                                break;
                            }
                        }
                    }
                }
                
                if (!menuItem && type === 'regular') {
                    var wooMenu = document.querySelector('#toplevel_page_woocommerce');
                    if (wooMenu) {
                        var links = wooMenu.querySelectorAll('a');
                        for (var i = 0; i < links.length; i++) {
                            if (links[i].textContent.indexOf('Обратная связь') !== -1) {
                                menuItem = links[i];
                                break;
                            }
                        }
                    }
                }
                
                if (menuItem) {
                    var badge = menuItem.querySelector('.awaiting-mod');
                    if (count > 0) {
                        if (badge) {
                            badge.textContent = count;
                        } else {
                            // Создаем новый badge
                            var span = document.createElement('span');
                            span.className = 'awaiting-mod';
                            span.textContent = count;
                            menuItem.appendChild(document.createTextNode(' '));
                            menuItem.appendChild(span);
                        }
                    } else {
                        // Удаляем badge если счетчик = 0
                        if (badge) {
                            badge.remove();
                        }
                    }
                } else {
                    console.warn('Элемент меню не найден для типа:', type);
                }
            }
        })
        .catch(function(error) {
            console.error('Ошибка при получении счетчика:', error);
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Копирование ссылок
        document.querySelectorAll('.copy-link-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);
                if (input) {
                input.select();
                    input.setSelectionRange(0, 99999);
                document.execCommand('copy');
                
                var originalText = this.textContent;
                this.textContent = 'Скопировано!';
                this.classList.add('button-primary');
                
                setTimeout(function() {
                    this.textContent = originalText;
                    this.classList.remove('button-primary');
                }.bind(this), 2000);
                }
            });
        });
        
        // Раскрытие/сворачивание строк с деталями
        document.querySelectorAll('.clickable-row').forEach(function(row) {
            row.addEventListener('click', function(e) {
                // Не раскрываем если кликнули на ссылку или кнопку
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
                    return;
                }
                
                var detailsRow = this.nextElementSibling;
                var toggleIcon = this.querySelector('.toggle-icon');
                var token = this.getAttribute('data-token');
                var newBadge = this.querySelector('.new-badge');
                
                if (detailsRow && detailsRow.classList.contains('feedback-details-row')) {
                    if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
                        detailsRow.style.display = 'table-row';
                        if (toggleIcon) {
                            toggleIcon.classList.add('rotated');
                        }
                        // Помечаем отзыв как просмотренный и скрываем звездочку
                        if (token && newBadge) {
                            markFeedbackAsViewed(token, 'regular');
                            newBadge.style.display = 'none';
                        }
                    } else {
                        detailsRow.style.display = 'none';
                        if (toggleIcon) {
                            toggleIcon.classList.remove('rotated');
                        }
                    }
                }
            });
        });
        
        // Удаление отзывов
        document.querySelectorAll('.delete-feedback-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var token = this.getAttribute('data-token');
                if (!token) {
                    return;
                }
                
                if (!confirm('Удалить отзыв полностью? Это действие нельзя отменить.')) {
                    return;
                }
                
                var formData = new FormData();
                formData.append('action', 'gustolocal_delete_feedback');
                formData.append('token', token);
                formData.append('nonce', '<?php echo isset($delete_nonce) ? esc_js($delete_nonce) : ''; ?>');
                
                var btnElement = this;
                btnElement.disabled = true;
                btnElement.textContent = 'Удаление...';
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.data || 'Не удалось удалить отзыв');
                        btnElement.disabled = false;
                        btnElement.textContent = 'Удалить';
                    }
                })
                .catch(function() {
                    alert('Ошибка при удалении отзыва');
                    btnElement.disabled = false;
                    btnElement.textContent = 'Удалить';
                });
            });
        });
    });
    </script>
    <?php
}

// Страница просмотра результатов отзывов
function gustolocal_feedback_results_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'dish_feedback';
    
    // Получаем статистику по блюдам
    $dish_stats = $wpdb->get_results("
        SELECT 
            dish_name,
            dish_unit,
            COUNT(*) as total_reviews,
            AVG(rating) as avg_rating,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
        FROM $table_name
        GROUP BY dish_name, dish_unit
        ORDER BY avg_rating DESC, total_reviews DESC
    ", ARRAY_A);
    
    // Последние заказы с отзывами
    $recent_feedback = $wpdb->get_results("
        SELECT 
            MIN(f.id) as id,
            f.token,
            f.order_id,
            f.customer_name,
            DATE_FORMAT(MAX(f.created_at), '%d.%m.%Y %H:%i') as last_date,
            MAX(f.general_comment) as general_comment,
            MAX(f.shared_instagram) as shared_instagram,
            MAX(f.shared_google) as shared_google,
            COUNT(*) as dishes_count,
            ROUND(AVG(f.rating), 2) as avg_rating,
            GROUP_CONCAT(
                CONCAT(
                    f.dish_name,
                    IF(f.dish_unit != '', CONCAT(' (', f.dish_unit, ')'), ''),
                    '::',
                    f.rating
                )
                ORDER BY f.created_at DESC
                SEPARATOR '||'
            ) as dishes_list
        FROM $table_name f
        GROUP BY f.token, f.order_id, f.customer_name
        ORDER BY MAX(f.created_at) DESC
        LIMIT 50
    ", ARRAY_A);
    
    $delete_nonce = wp_create_nonce('gustolocal_feedback_delete');
    
    ?>
    <div class="wrap">
        <h1>Результаты отзывов о блюдах</h1>
        
        <h2>Статистика по блюдам</h2>
        <p class="description">Таблица автоматически группирует отзывы по названию блюда и единице измерения. Кликните на строку, чтобы увидеть все отзывы по этому блюду.</p>
        
        <table class="wp-list-table widefat fixed striped" id="feedback-stats-table">
            <thead>
                <tr>
                    <th>Блюдо</th>
                    <th>Отзывов</th>
                    <th>Средняя оценка</th>
                    <th>😍</th>
                    <th>😊</th>
                    <th>😐</th>
                    <th>😞</th>
                    <th style="width: 100px;">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dish_stats as $stat): 
                    $avg = round($stat['avg_rating'], 2);
                    $dish_full = $stat['dish_name'] . ($stat['dish_unit'] ? ' (' . $stat['dish_unit'] . ')' : '');
                    $dish_key = esc_attr($stat['dish_name'] . '|' . $stat['dish_unit']);
                ?>
                    <tr data-dish-name="<?php echo esc_attr($stat['dish_name']); ?>" data-dish-unit="<?php echo esc_attr($stat['dish_unit']); ?>">
                        <td><strong><?php echo esc_html($dish_full); ?></strong></td>
                        <td><?php echo esc_html($stat['total_reviews']); ?></td>
                        <td>
                            <strong><?php echo number_format($avg, 2); ?></strong>
                            <span style="font-size: 20px;">
                                <?php 
                                if ($avg >= 3.5) echo '😍';
                                elseif ($avg >= 2.5) echo '😊';
                                elseif ($avg >= 1.5) echo '😐';
                                else echo '😞';
                                ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($stat['rating_4']); ?></td>
                        <td><?php echo esc_html($stat['rating_3']); ?></td>
                        <td><?php echo esc_html($stat['rating_2']); ?></td>
                        <td><?php echo esc_html($stat['rating_1']); ?></td>
                        <td>
                            <button type="button" class="button button-small view-details-btn" 
                                    data-dish-name="<?php echo esc_attr($stat['dish_name']); ?>" 
                                    data-dish-unit="<?php echo esc_attr($stat['dish_unit']); ?>">
                                Детали
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <style>
        .feedback-modal {
            display: none;
            position: fixed;
            z-index: 100000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .feedback-modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .feedback-modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .feedback-modal-close:hover {
            color: #000;
        }
        .feedback-detail-item {
            padding: 15px;
            margin-bottom: 10px;
            background: #f9f9f9;
            border-left: 4px solid #0073aa;
            border-radius: 4px;
        }
        .feedback-detail-item .rating {
            font-size: 24px;
            margin-right: 10px;
        }
        </style>
        
        <div id="feedback-modal" class="feedback-modal">
            <div class="feedback-modal-content">
                <span class="feedback-modal-close">&times;</span>
                <h2 id="modal-dish-name"></h2>
                <div id="modal-feedback-list"></div>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('feedback-modal');
            var closeBtn = document.querySelector('.feedback-modal-close');
            var viewDetailsBtns = document.querySelectorAll('.view-details-btn');
            
            viewDetailsBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var dishName = this.getAttribute('data-dish-name');
                    var dishUnit = this.getAttribute('data-dish-unit');
                    showFeedbackDetails(dishName, dishUnit);
                });
            });
            
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            };
            
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            };
            
            function showFeedbackDetails(dishName, dishUnit) {
                document.getElementById('modal-dish-name').textContent = dishName + (dishUnit ? ' (' + dishUnit + ')' : '');
                document.getElementById('modal-feedback-list').innerHTML = '<p>Загрузка...</p>';
                modal.style.display = 'block';
                
                // AJAX запрос для получения детальных отзывов
                var formData = new FormData();
                formData.append('action', 'get_feedback_details');
                formData.append('dish_name', dishName);
                formData.append('dish_unit', dishUnit);
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        var html = '';
                        if (data.data.length === 0) {
                            html = '<p>Нет детальных отзывов для этого блюда.</p>';
                        } else {
                            data.data.forEach(function(feedback) {
                                var ratingEmoji = {'1': '😞', '2': '😐', '3': '😊', '4': '😍'};
                                html += '<div class="feedback-detail-item">';
                                html += '<div style="display: flex; align-items: center; margin-bottom: 10px;">';
                                html += '<span class="rating">' + ratingEmoji[feedback.rating] + '</span>';
                                html += '<strong>' + feedback.customer_name + '</strong>';
                                html += '<span style="margin-left: auto; color: #666; font-size: 12px;">Заказ #' + feedback.order_id + ' • ' + feedback.date + '</span>';
                                html += '</div>';
                                if (feedback.general_comment) {
                                    html += '<p style="margin: 10px 0; padding: 10px; background: white; border-radius: 4px;">' + escapeHtml(feedback.general_comment) + '</p>';
                                }
                                html += '</div>';
                            });
                        }
                        document.getElementById('modal-feedback-list').innerHTML = html;
                    } else {
                        document.getElementById('modal-feedback-list').innerHTML = '<p>Ошибка: ' + (data.data || 'Не удалось загрузить отзывы') + '</p>';
                    }
                })
                .catch(function(error) {
                    document.getElementById('modal-feedback-list').innerHTML = '<p>Ошибка: ' + error + '</p>';
                });
            }
            
            function escapeHtml(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            document.querySelectorAll('.delete-feedback-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var token = this.getAttribute('data-token');
                    if (!token) {
                        return;
                    }
                    
                    if (!confirm('Удалить отзыв полностью? Это действие нельзя отменить.')) {
                        return;
                    }
                    
                    var formData = new FormData();
                    formData.append('action', 'gustolocal_delete_feedback');
                    formData.append('token', token);
                    formData.append('nonce', '<?php echo esc_js($delete_nonce); ?>');
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.data || 'Не удалось удалить отзыв');
                        }
                    })
                    .catch(function() {
                        alert('Ошибка при удалении отзыва');
                    });
                });
            });
        });
        </script>
        
        <h2>Последние комментарии и активности</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Клиент</th>
                    <th>Заказ</th>
                    <th>Блюд</th>
                    <th>Средняя</th>
                    <th>Отзывы</th>
                    <th>Комментарий</th>
                    <th>Instagram</th>
                    <th>Google</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_feedback)): ?>
                    <?php foreach ($recent_feedback as $feedback): ?>
                        <tr>
                            <td><?php echo esc_html($feedback['last_date']); ?></td>
                            <td><?php echo esc_html($feedback['customer_name']); ?></td>
                            <td>#<?php echo esc_html($feedback['order_id']); ?></td>
                            <td><?php echo esc_html($feedback['dishes_count']); ?></td>
                            <td><?php echo esc_html(number_format((float) $feedback['avg_rating'], 2)); ?></td>
                            <td>
                                <?php
                                if (!empty($feedback['dishes_list'])) {
                                    $items = explode('||', $feedback['dishes_list']);
                                    foreach ($items as $item) {
                                        list($name, $rating) = array_pad(explode('::', $item), 2, '');
                                        $emoji = array('1' => '😞', '2' => '😐', '3' => '😊', '4' => '😍');
                                        echo '<div>' . esc_html($name) . ': ' . ($emoji[$rating] ?? $rating) . '</div>';
                                    }
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td><?php echo $feedback['general_comment'] ? nl2br(esc_html($feedback['general_comment'])) : '—'; ?></td>
                            <td><?php echo !empty($feedback['shared_instagram']) ? '✅' : '—'; ?></td>
                            <td><?php echo !empty($feedback['shared_google']) ? '✅' : '—'; ?></td>
                            <td>
                                <button class="button delete-feedback-btn" data-token="<?php echo esc_attr($feedback['token']); ?>">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Нет комментариев</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <h2>Экспорт данных</h2>
        <p>
            <a href="<?php echo admin_url('admin-post.php?action=export_feedback'); ?>" class="button button-primary">
                Экспортировать в CSV
            </a>
        </p>
    </div>
    <?php
}

// Экспорт отзывов в CSV
add_action('admin_post_export_feedback', 'gustolocal_export_feedback_csv');
function gustolocal_export_feedback_csv() {
    if (!current_user_can('manage_options')) {
        wp_die('Доступ запрещен');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'dish_feedback';
    
    $results = $wpdb->get_results("
        SELECT 
            dish_name,
            dish_unit,
            customer_name,
            order_id,
            rating,
            general_comment,
            shared_instagram,
            created_at
        FROM $table_name
        ORDER BY created_at DESC
    ", ARRAY_A);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=feedback_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // BOM для правильного отображения кириллицы в Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Заголовки
    fputcsv($output, array('Блюдо', 'Единица', 'Клиент', 'Заказ', 'Оценка', 'Комментарий', 'Поделились Instagram', 'Дата'), ';');
    
    // Данные
    foreach ($results as $row) {
        $rating_emoji = array(1 => '😞', 2 => '😐', 3 => '😊', 4 => '😍');
        fputcsv($output, array(
            $row['dish_name'],
            $row['dish_unit'],
            $row['customer_name'],
            $row['order_id'],
            $rating_emoji[$row['rating']] ?? $row['rating'],
            $row['general_comment'],
            $row['shared_instagram'] ? 'Да' : 'Нет',
            $row['created_at']
        ), ';');
    }
    
    fclose($output);
    exit;
}

// Регистрация кастомного эндпоинта для опросника
add_action('init', 'gustolocal_register_feedback_endpoint');
function gustolocal_register_feedback_endpoint() {
    add_rewrite_rule('^feedback/([^/]+)/?$', 'index.php?feedback_token=$matches[1]', 'top');
    add_rewrite_tag('%feedback_token%', '([^&]+)');
}

function gustolocal_feedback_rewrite_exists() {
    $rules = get_option('rewrite_rules');
    return is_array($rules) && array_key_exists('^feedback/([^/]+)/?$', $rules);
}

add_action('init', 'gustolocal_ensure_feedback_rewrite', 19);
function gustolocal_ensure_feedback_rewrite() {
    if (!gustolocal_feedback_rewrite_exists()) {
        gustolocal_register_feedback_endpoint();
        flush_rewrite_rules(false);
    }
}

// Перезапись правил при активации
add_action('after_switch_theme', 'gustolocal_flush_rewrite_rules');
function gustolocal_flush_rewrite_rules() {
    gustolocal_register_feedback_endpoint();
    flush_rewrite_rules();
}

// Обработка запроса опросника
add_action('template_redirect', 'gustolocal_handle_feedback_page');
function gustolocal_handle_feedback_page() {
    $token = get_query_var('feedback_token');
    
    if (!$token) {
        return;
    }
    
    global $wpdb;
    $custom_requests_table = $wpdb->prefix . 'custom_feedback_requests';
    
    // Сначала проверяем, это кастомный опрос?
    $custom_request = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $custom_requests_table WHERE token = %s",
        $token
    ), ARRAY_A);
    
    if ($custom_request) {
        // Это кастомный опрос
        gustolocal_display_custom_feedback_form($token, $custom_request);
        exit;
    }
    
    // Иначе это обычный опрос по заказу
    $order_id = null;
    
    // Сначала проверяем в мета заказа
    if (function_exists('wc_get_orders')) {
        $orders = wc_get_orders(array(
            'limit' => 100,
            'meta_key' => '_feedback_token',
            'meta_value' => $token,
        ));
        
        if (!empty($orders)) {
            $order_id = $orders[0]->get_id();
        }
    }
    
    // Если не нашли, проверяем в БД
    if (!$order_id) {
        $table_name = $wpdb->prefix . 'dish_feedback';
        $order_id = $wpdb->get_var($wpdb->prepare(
            "SELECT order_id FROM $table_name WHERE token = %s LIMIT 1",
            $token
        ));
    }
    
    if (!$order_id) {
        wp_die('Неверная ссылка на опросник.', 'Ошибка', array('response' => 404));
    }
    
    // Показываем опросник
    gustolocal_display_feedback_form($token, $order_id);
    exit;
}

// Отображение формы опросника
function gustolocal_display_feedback_form($token, $order_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
        wp_die('Заказ не найден.', 'Ошибка', array('response' => 404));
    }
    
    $dishes = gustolocal_extract_dishes_from_order($order);
    
    if (empty($dishes)) {
        wp_die('Блюда не найдены в заказе.', 'Ошибка', array('response' => 404));
    }
    
    $customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
    if ($customer_name === '') {
        $customer_name = $order->get_billing_company() ?: 'Дорогой клиент';
    }
    
    // Проверяем, не заполнен ли уже опрос
    global $wpdb;
    $table_name = $wpdb->prefix . 'dish_feedback';
    $already_submitted = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE token = %s",
        $token
    ));
    
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Оцените наши блюда</title>
        <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .feedback-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 30px;
            margin: 20px auto;
        }
        .feedback-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .feedback-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .feedback-header p {
            color: #666;
            font-size: 16px;
        }
        .dish-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .dish-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        .rating-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .rating-btn {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 32px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .rating-btn:hover {
            transform: scale(1.1);
            border-color: #667eea;
        }
        .rating-btn.selected {
            border-color: #667eea;
            background: #667eea;
            transform: scale(1.1);
        }
        .rating-label {
            text-align: center;
            margin-top: 8px;
            font-size: 12px;
            color: #666;
        }
        .general-comment {
            margin-top: 30px;
        }
        .general-comment label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .general-comment textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }
        .share-section {
            margin-top: 30px;
            padding: 20px;
            background: #f0f4ff;
            border-radius: 12px;
            text-align: center;
        }
        .share-section h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .share-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin: 5px;
            transition: transform 0.2s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .share-button:hover {
            transform: scale(1.05);
        }
        .share-button:active {
            transform: scale(0.98);
        }
        .share-icon {
            font-size: 18px;
        }
        .share-button--google {
            background: linear-gradient(120deg, #4285F4, #34A853, #FBBC05, #EA4335);
            color: #fff;
        }
        .share-button--google .share-icon {
            font-size: 16px;
        }
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .success-message {
            text-align: center;
            padding: 40px;
            color: #46b450;
        }
        .success-message h2 {
            font-size: 32px;
            margin-bottom: 15px;
        }
        @media (max-width: 600px) {
            .feedback-container {
                padding: 20px;
            }
            .rating-btn {
                width: 50px;
                height: 50px;
                font-size: 28px;
            }
        }
        </style>
    </head>
    <body>
        <div class="feedback-container">
            <?php if ($already_submitted > 0): ?>
                <div class="success-message">
                    <h2>✅ Спасибо!</h2>
                    <p>Вы уже оставили отзыв. Мы ценим ваше мнение!</p>
                </div>
            <?php else: ?>
                <div class="feedback-header">
                    <h1>Нам важно ваше мнение! 🙏</h1>
                    <p>Пожалуйста, оцените блюда из последнего заказа (пропускайте, если не успели попробовать):</p>
                </div>
                <form id="feedback-form">
                    <input type="hidden" name="action" value="guest_feedback_submit">
                    <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                    <input type="hidden" name="shared_instagram" id="shared-instagram-field" value="0">
                    <input type="hidden" name="shared_google" id="shared-google-field" value="0">
                    
                    <?php foreach ($dishes as $dish_key => $dish_data): ?>
                        <div class="dish-item" data-dish="<?php echo esc_attr($dish_key); ?>">
                            <div class="dish-name">
                                <?php echo esc_html($dish_data['name']); ?>
                                <?php if ($dish_data['unit']): ?>
                                    <small style="color: #666;">(<?php echo esc_html($dish_data['unit']); ?>)</small>
                                <?php endif; ?>
                            </div>
                            <div class="rating-buttons">
                                <button type="button" class="rating-btn" data-rating="1" data-dish="<?php echo esc_attr($dish_key); ?>">
                                    😞
                                </button>
                                <button type="button" class="rating-btn" data-rating="2" data-dish="<?php echo esc_attr($dish_key); ?>">
                                    😐
                                </button>
                                <button type="button" class="rating-btn" data-rating="3" data-dish="<?php echo esc_attr($dish_key); ?>">
                                    😊
                                </button>
                                <button type="button" class="rating-btn" data-rating="4" data-dish="<?php echo esc_attr($dish_key); ?>">
                                    😍
                                </button>
                            </div>
                            <input type="hidden" name="ratings[<?php echo esc_attr($dish_key); ?>]" value="">
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="general-comment">
                        <label for="general-comment">Хотите что-то добавить?</label>
                        <textarea id="general-comment" name="general_comment" placeholder="Ваши пожелания, замечания, предложения..."></textarea>
                    </div>
                    
                    <div class="share-section">
                        <h3>Понравилось? Расскажите друзьям! 👥</h3>
                        <button type="button" class="share-button" id="share-btn" onclick="shareInstagram()">
                            <span class="share-icon">↗️</span>
                            <span>Поделиться нашим Instagram</span>
                        </button>
                        <a class="share-button share-button--google"
                           href="https://maps.app.goo.gl/6rmjMdquG5vcVFry6"
                           target="_blank"
                           rel="noopener noreferrer"
                           onclick="markShareField('shared-google-field')">
                            <span class="share-icon">★</span>
                            <span>Оставить отзыв в Google Maps</span>
                        </a>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submit-btn">
                        Отправить отзыв
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('feedback-form');
            if (!form) return;
            
            // Обработка кликов по смайликам
            document.querySelectorAll('.rating-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var dish = this.getAttribute('data-dish');
                    var rating = this.getAttribute('data-rating');
                    
                    // Убираем выделение с других кнопок этого блюда
                    document.querySelectorAll('.rating-btn[data-dish="' + dish + '"]').forEach(function(b) {
                        b.classList.remove('selected');
                    });
                    
                    // Выделяем выбранную кнопку
                    this.classList.add('selected');
                    
                    // Сохраняем рейтинг в скрытое поле
                    document.querySelector('input[name="ratings[' + dish + ']"]').value = rating;
                    
                    checkFormComplete();
                });
            });
            
            function checkFormComplete() {
                var anyRated = false;
                document.querySelectorAll('.dish-item').forEach(function(item) {
                    var dish = item.getAttribute('data-dish');
                    var rating = document.querySelector('input[name="ratings[' + dish + ']"]').value;
                    if (rating) {
                        anyRated = true;
                    }
                });
                
                document.getElementById('submit-btn').disabled = !anyRated;
            }
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                var submitBtn = document.getElementById('submit-btn');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Отправка...';
                
                var formData = new FormData(form);
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    if (!response.ok) {
                        return response.text().then(function(text) {
                            throw new Error('HTTP ' + response.status + ': ' + text);
                        });
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        var header = document.querySelector('.feedback-header');
                        if (header) {
                            header.remove();
                        }
                        form.innerHTML = '<div class="success-message"><h2>✅ Спасибо!</h2><p>Ваш отзыв сохранен. Мы ценим ваше мнение!</p></div>';
                    } else {
                        var errorMsg = data.data || 'Не удалось сохранить отзыв';
                        console.error('Ошибка сохранения:', data);
                        alert('Ошибка: ' + errorMsg);
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Отправить отзыв';
                    }
                })
                .catch(function(error) {
                    console.error('Ошибка запроса:', error);
                    alert('Ошибка: ' + error.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Отправить отзыв';
                });
            });
        });
        
        function shareInstagram() {
            var instagramUrl = 'https://www.instagram.com/llevatelo_vlc/';
            var shareText = 'Попробуйте вкусную еду от Llévatelo! 🍽️';
            
            // Проверяем поддержку Web Share API
            if (navigator.share) {
                navigator.share({
                    title: 'Llévatelo - Вкусная еда в Валенсии',
                    text: shareText,
                    url: instagramUrl
                })
                .then(function() {
                    console.log('Успешно поделились');
                    // Отмечаем, что поделились
                    trackShare();
                })
                .catch(function(error) {
                    console.log('Ошибка при попытке поделиться:', error);
                    // Fallback: открываем Instagram в новой вкладке
                    window.open(instagramUrl, '_blank');
                    trackShare();
                });
            } else {
                // Fallback для браузеров без поддержки Web Share API
                // Показываем диалог с ссылкой для копирования
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(instagramUrl).then(function() {
                        alert('Ссылка на Instagram скопирована! Вставьте её в любое приложение.');
                        trackShare();
                    }).catch(function() {
                        // Если не удалось скопировать, просто открываем
                        window.open(instagramUrl, '_blank');
                        trackShare();
                    });
                } else {
                    // Последний fallback: открываем Instagram
                    window.open(instagramUrl, '_blank');
                    trackShare();
                }
            }
        }
        
        function markShareField(fieldId) {
            var field = document.getElementById(fieldId);
            if (field) {
                field.value = '1';
            }
        }
        
        function trackShare() {
            markShareField('shared-instagram-field');
        }
        </script>
    </body>
    </html>
    <?php
}

// AJAX обработчик для сохранения отзывов
add_action('wp_ajax_guest_feedback_submit', 'gustolocal_handle_feedback_submit');
add_action('wp_ajax_nopriv_guest_feedback_submit', 'gustolocal_handle_feedback_submit');
function gustolocal_handle_feedback_submit() {
    // Проверяем action
    $action = sanitize_text_field($_POST['action'] ?? '');
    if (empty($action) || $action !== 'guest_feedback_submit') {
        wp_send_json_error('Неверный запрос');
    }
    
    $token = sanitize_text_field($_POST['token'] ?? '');
    $order_id = intval($_POST['order_id'] ?? 0);
    
    // Правильно обрабатываем массив ratings из FormData
    $ratings = array();
    if (isset($_POST['ratings']) && is_array($_POST['ratings'])) {
        $ratings = $_POST['ratings'];
    } else {
        // Пробуем получить из строки (если пришло как строка)
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'ratings[') === 0) {
                $dish_key = str_replace(array('ratings[', ']'), '', $key);
                $ratings[$dish_key] = intval($value);
            }
        }
    }
    
    $general_comment = sanitize_textarea_field($_POST['general_comment'] ?? '');
    $shared_instagram = !empty($_POST['shared_instagram']) ? 1 : 0;
    $shared_google = !empty($_POST['shared_google']) ? 1 : 0;
    
    if (empty($token) || empty($order_id) || empty($ratings)) {
        wp_send_json_error('Неверные данные: токен, заказ или оценки отсутствуют');
    }
    
    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error('Заказ не найден');
    }
    
    $customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
    if ($customer_name === '') {
        $customer_name = $order->get_billing_company() ?: 'Гость';
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'dish_feedback';
    
    // Проверяем существование таблицы
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Создаем таблицу, если её нет
        gustolocal_create_feedback_table();
    }
    
    $saved_count = 0;
    $errors = array();
    
    // Сохраняем отзывы по каждому блюду
    foreach ($ratings as $dish_key => $rating) {
        $rating = intval($rating);
        if ($rating < 1 || $rating > 4) continue;
        
        // Извлекаем название блюда и единицу из ключа
        $dish_parts = explode(' (', $dish_key);
        $dish_name = $dish_parts[0];
        $dish_unit = isset($dish_parts[1]) ? rtrim($dish_parts[1], ')') : '';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'token' => $token,
                'order_id' => $order_id,
                'customer_name' => $customer_name,
                'dish_name' => $dish_name,
                'dish_unit' => $dish_unit,
                'rating' => $rating,
                'general_comment' => '', // Общий комментарий сохраним отдельно
                'shared_instagram' => 0,
                'shared_google' => 0,
            ),
            array('%s', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d')
        );
        
        if ($result === false) {
            $errors[] = 'Ошибка при сохранении отзыва для ' . $dish_name . ': ' . $wpdb->last_error;
        } else {
            $saved_count++;
        }
    }
    
    if ($saved_count === 0) {
        wp_send_json_error('Не удалось сохранить ни одного отзыва. ' . implode(' ', $errors));
    }
    
    // Сохраняем общий комментарий и флаг поделились в Instagram в первой записи
    if (!empty($general_comment) || $shared_instagram) {
        $first_feedback_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE token = %s ORDER BY id ASC LIMIT 1",
            $token
        ));
        
        if ($first_feedback_id) {
            $wpdb->update(
                $table_name,
                array(
                    'general_comment' => $general_comment,
                    'shared_instagram' => $shared_instagram,
                    'shared_google' => $shared_google,
                ),
                array('id' => $first_feedback_id),
                array('%s', '%d', '%d'),
                array('%d')
            );
        }
    }
    
    wp_send_json_success('Отзыв сохранен');
}

// AJAX обработчик для получения детальных отзывов по блюду
add_action('wp_ajax_get_feedback_details', 'gustolocal_get_feedback_details');
function gustolocal_get_feedback_details() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Доступ запрещен');
    }
    
    $dish_name = sanitize_text_field($_POST['dish_name'] ?? '');
    $dish_unit = sanitize_text_field($_POST['dish_unit'] ?? '');
    
    if (empty($dish_name)) {
        wp_send_json_error('Название блюда не указано');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'dish_feedback';
    
    $query = $wpdb->prepare(
        "SELECT 
            f.*,
            DATE_FORMAT(f.created_at, '%%d.%%m.%%Y %%H:%%i') as date
        FROM $table_name f
        WHERE f.dish_name = %s",
        $dish_name
    );
    
    if (!empty($dish_unit)) {
        $query .= $wpdb->prepare(" AND f.dish_unit = %s", $dish_unit);
    }
    
    $query .= " ORDER BY f.created_at DESC LIMIT 100";
    
    $feedbacks = $wpdb->get_results($query, ARRAY_A);
    
    // Группируем по заказам, чтобы показать общий комментарий один раз
    $grouped_feedbacks = array();
    foreach ($feedbacks as $feedback) {
        $order_id = $feedback['order_id'];
        if (!isset($grouped_feedbacks[$order_id])) {
            $grouped_feedbacks[$order_id] = array(
                'order_id' => $order_id,
                'customer_name' => $feedback['customer_name'],
                'date' => $feedback['date'],
                'general_comment' => $feedback['general_comment'],
                'shared_instagram' => $feedback['shared_instagram'],
                'dishes' => array(),
            );
        }
        $grouped_feedbacks[$order_id]['dishes'][] = array(
            'dish_name' => $feedback['dish_name'],
            'dish_unit' => $feedback['dish_unit'],
            'rating' => $feedback['rating'],
        );
    }
    
    // Преобразуем в простой массив для отображения
    $result = array();
    foreach ($grouped_feedbacks as $order_feedback) {
        // Находим рейтинг для нужного блюда
        $rating = null;
        foreach ($order_feedback['dishes'] as $dish) {
            if ($dish['dish_name'] === $dish_name && 
                (empty($dish_unit) || $dish['dish_unit'] === $dish_unit)) {
                $rating = $dish['rating'];
                break;
            }
        }
        
        if ($rating) {
            $result[] = array(
                'order_id' => $order_feedback['order_id'],
                'customer_name' => $order_feedback['customer_name'],
                'date' => $order_feedback['date'],
                'rating' => $rating,
                'general_comment' => $order_feedback['general_comment'],
                'shared_instagram' => $order_feedback['shared_instagram'],
            );
        }
    }
    
    wp_send_json_success($result);
}

add_action('wp_ajax_gustolocal_delete_feedback', 'gustolocal_delete_feedback');
function gustolocal_delete_feedback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Доступ запрещен');
    }
    
    check_ajax_referer('gustolocal_feedback_delete', 'nonce');
    
    $token = sanitize_text_field($_POST['token'] ?? '');
    if (empty($token)) {
        wp_send_json_error('Токен не указан');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'dish_feedback';
    
    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM $table_name WHERE token = %s",
        $token
    ));
    
    if ($deleted === false) {
        wp_send_json_error('Ошибка удаления: ' . $wpdb->last_error);
    }
    
    wp_send_json_success(array('deleted' => $deleted));
}

/* ========================================
   КАСТОМНЫЕ ОПРОСЫ (БЕЗ ЗАКАЗОВ)
   ======================================== */

// Отображение формы кастомного опроса
function gustolocal_display_custom_feedback_form($token, $custom_request) {
    // Проверяем, не заполнен ли уже опрос (только если статус submitted)
    $already_submitted = $custom_request['status'] === 'submitted';
    
    // Парсим блюда из текста
    $dishes_lines = explode("\n", $custom_request['dishes']);
    $dishes = array();
    foreach ($dishes_lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Пытаемся извлечь название и единицу измерения
        if (preg_match('/^(.+?)\s*\((.+?)\)$/', $line, $matches)) {
            $dishes[] = array(
                'name' => trim($matches[1]),
                'unit' => trim($matches[2])
            );
        } else {
            $dishes[] = array(
                'name' => $line,
                'unit' => ''
            );
        }
    }
    
    if (empty($dishes)) {
        wp_die('Блюда не найдены.', 'Ошибка', array('response' => 404));
    }
    
    $customer_name = $custom_request['client_name'] ?: 'Дорогой клиент';
    
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Оцените наши блюда</title>
        <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .feedback-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 30px;
            margin: 20px auto;
        }
        .feedback-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .feedback-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .feedback-header p {
            color: #666;
            font-size: 16px;
        }
        .dish-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .dish-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        .rating-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .rating-btn {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 32px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .rating-btn:hover {
            transform: scale(1.1);
            border-color: #667eea;
        }
        .rating-btn.selected {
            border-color: #667eea;
            background: #667eea;
            transform: scale(1.1);
        }
        .general-comment {
            margin-top: 30px;
        }
        .general-comment label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .general-comment textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }
        .share-section {
            margin-top: 30px;
            padding: 20px;
            background: #f0f4ff;
            border-radius: 12px;
            text-align: center;
        }
        .share-section h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .share-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin: 5px;
            transition: transform 0.2s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .share-button:hover {
            transform: scale(1.05);
        }
        .share-button--google {
            background: linear-gradient(120deg, #4285F4, #34A853, #FBBC05, #EA4335);
            color: #fff;
        }
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 30px;
            transition: transform 0.2s;
        }
        .submit-btn:hover {
            transform: scale(1.02);
        }
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            margin-top: 20px;
        }
        </style>
    </head>
    <body>
        <div class="feedback-container">
            <?php if ($already_submitted): ?>
                <div class="success-message">
                    Спасибо! Ваш отзыв уже был отправлен. 🙏
                </div>
            <?php else: ?>
                <div class="feedback-header">
                    <h1>Нам важно ваше мнение! 🙏</h1>
                    <p>Пожалуйста, оцените блюда из последнего заказа (пропускайте, если не успели попробовать):</p>
                </div>
                
                <form id="custom-feedback-form">
                    <input type="hidden" name="action" value="guest_custom_feedback_submit">
                    <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
                    <input type="hidden" name="request_id" value="<?php echo esc_attr($custom_request['id']); ?>">
                    
                    <?php foreach ($dishes as $index => $dish): 
                        $dish_full = $dish['name'] . ($dish['unit'] ? ' (' . $dish['unit'] . ')' : '');
                    ?>
                        <div class="dish-item">
                            <div class="dish-name"><?php echo esc_html($dish_full); ?></div>
                            <div class="rating-buttons">
                                <button type="button" class="rating-btn" data-rating="1" data-dish-index="<?php echo $index; ?>">
                                    😞
                                </button>
                                <button type="button" class="rating-btn" data-rating="2" data-dish-index="<?php echo $index; ?>">
                                    😐
                                </button>
                                <button type="button" class="rating-btn" data-rating="3" data-dish-index="<?php echo $index; ?>">
                                    😊
                                </button>
                                <button type="button" class="rating-btn" data-rating="4" data-dish-index="<?php echo $index; ?>">
                                    😍
                                </button>
                            </div>
                            <input type="hidden" name="ratings[<?php echo $index; ?>]" value="0">
                            <input type="hidden" name="dish_name_<?php echo $index; ?>" value="<?php echo esc_attr($dish['name']); ?>">
                            <input type="hidden" name="dish_unit_<?php echo $index; ?>" value="<?php echo esc_attr($dish['unit']); ?>">
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="general-comment">
                        <label for="general_comment">Общий комментарий (необязательно)</label>
                        <textarea id="general_comment" name="general_comment" placeholder="Поделитесь своими впечатлениями..."></textarea>
                    </div>
                    
                    <div class="share-section">
                        <h3>Поделитесь с друзьями</h3>
                        <button type="button" class="share-button" onclick="shareInstagram()">
                            <span class="share-icon">📷</span>
                            Поделиться нашим Instagram
                        </button>
                        <button type="button" class="share-button share-button--google" onclick="shareGoogle()">
                            <span class="share-icon">⭐</span>
                            Оставить отзыв в Google Maps
                        </button>
                        <input type="hidden" name="shared_instagram" value="0" id="shared-instagram-field">
                        <input type="hidden" name="shared_google" value="0" id="shared-google-field">
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submit-btn">Отправить отзыв</button>
                </form>
            <?php endif; ?>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('custom-feedback-form');
            if (!form) return;
            
            var ratings = {};
            var ratingButtons = document.querySelectorAll('.rating-btn');
            
            ratingButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var dishIndex = this.getAttribute('data-dish-index');
                    var rating = parseInt(this.getAttribute('data-rating'));
                    
                    // Убираем выделение с других кнопок этого блюда
                    var dishItem = this.closest('.dish-item');
                    dishItem.querySelectorAll('.rating-btn').forEach(function(b) {
                        b.classList.remove('selected');
                    });
                    
                    // Выделяем текущую кнопку
                    this.classList.add('selected');
                    
                    // Сохраняем рейтинг
                    ratings[dishIndex] = rating;
                    var hiddenInput = dishItem.querySelector('input[type="hidden"][name^="ratings"]');
                    if (hiddenInput) {
                        hiddenInput.value = rating;
                    }
                    
                    updateSubmitButton();
                });
            });
            
            function updateSubmitButton() {
                var hasRating = Object.keys(ratings).some(function(key) {
                    return ratings[key] > 0;
                });
                var submitBtn = document.getElementById('submit-btn');
                if (submitBtn) {
                    submitBtn.disabled = !hasRating;
                }
            }
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                var submitBtn = document.getElementById('submit-btn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Отправка...';
                }
                
                var formData = new FormData(form);
                
                // Убеждаемся, что все скрытые поля с названиями блюд передаются
                document.querySelectorAll('input[type="hidden"][name^="dish_name_"]').forEach(function(input) {
                    formData.append(input.name, input.value);
                });
                document.querySelectorAll('input[type="hidden"][name^="dish_unit_"]').forEach(function(input) {
                    formData.append(input.name, input.value);
                });
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        // Заменяем весь контент контейнера, чтобы убрать заголовок
                        var container = document.querySelector('.feedback-container');
                        if (container) {
                            container.innerHTML = '<div class="success-message">Спасибо! Ваш отзыв сохранен. Мы ценим ваше мнение!</div>';
                        }
                    } else {
                        alert('Ошибка: ' + (data.data || 'Не удалось сохранить отзыв'));
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Отправить отзыв';
                        }
                    }
                })
                .catch(function(error) {
                    alert('Ошибка: ' + error);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Отправить отзыв';
                    }
                });
            });
            
            updateSubmitButton();
        });
        
        function shareInstagram() {
            var sharedInput = document.getElementById('shared-instagram-field');
            if (sharedInput) {
                sharedInput.value = '1';
            }
            
            if (navigator.share) {
                navigator.share({
                    title: 'Llévatelo - Готовая еда в Валенсии',
                    text: 'Попробуйте готовую еду от Llévatelo!',
                    url: 'https://www.instagram.com/llevatelo_vlc/'
                }).catch(function(err) {
                    console.log('Error sharing:', err);
                });
            } else {
                window.open('https://www.instagram.com/llevatelo_vlc/', '_blank');
            }
        }
        
        function shareGoogle() {
            var sharedInput = document.getElementById('shared-google-field');
            if (sharedInput) {
                sharedInput.value = '1';
            }
            
            var link = document.createElement('a');
            link.href = 'https://maps.app.goo.gl/6rmjMdquG5vcVFry6';
            link.target = '_blank';
            link.click();
        }
        </script>
    </body>
    </html>
    <?php
}

// AJAX обработчик для сохранения кастомных отзывов
add_action('wp_ajax_guest_custom_feedback_submit', 'gustolocal_handle_custom_feedback_submit');
add_action('wp_ajax_nopriv_guest_custom_feedback_submit', 'gustolocal_handle_custom_feedback_submit');
function gustolocal_handle_custom_feedback_submit() {
    $action = sanitize_text_field($_POST['action'] ?? '');
    if (empty($action) || $action !== 'guest_custom_feedback_submit') {
        wp_send_json_error('Неверный запрос');
    }
    
    $token = sanitize_text_field($_POST['token'] ?? '');
    $request_id = intval($_POST['request_id'] ?? 0);
    
    if (empty($token) || empty($request_id)) {
        wp_send_json_error('Неверные параметры');
    }
    
    global $wpdb;
    $requests_table = $wpdb->prefix . 'custom_feedback_requests';
    $entries_table = $wpdb->prefix . 'custom_feedback_entries';
    
    // Проверяем, что запрос существует
    $request = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $requests_table WHERE id = %d AND token = %s",
        $request_id,
        $token
    ), ARRAY_A);
    
    if (!$request) {
        wp_send_json_error('Запрос не найден');
    }
    
    // Обрабатываем рейтинги
    $ratings = array();
    
    // Сначала пробуем получить из массива ratings
    if (isset($_POST['ratings']) && is_array($_POST['ratings'])) {
        foreach ($_POST['ratings'] as $index => $rating) {
            $rating = intval($rating);
            if ($rating > 0) {
                // Получаем название и единицу блюда из скрытых полей
                $dish_name = sanitize_text_field($_POST["dish_name_{$index}"] ?? '');
                $dish_unit = sanitize_text_field($_POST["dish_unit_{$index}"] ?? '');
                
                if (empty($dish_name)) {
                    // Если не нашли в POST, получаем из исходного списка блюд
                    $dishes_lines = explode("\n", $request['dishes']);
                    $line = trim($dishes_lines[intval($index)] ?? '');
                    if (preg_match('/^(.+?)\s*\((.+?)\)$/', $line, $matches)) {
                        $dish_name = trim($matches[1]);
                        $dish_unit = trim($matches[2]);
                    } else {
                        $dish_name = $line;
                        $dish_unit = '';
                    }
                }
                $ratings[] = array(
                    'dish_name' => $dish_name,
                    'dish_unit' => $dish_unit,
                    'rating' => $rating
                );
            }
        }
    } else {
        // Альтернативный способ: ищем все поля, начинающиеся с dish_name_
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'dish_name_') === 0) {
                $index = str_replace('dish_name_', '', $key);
                $rating = intval($_POST["ratings[{$index}]"] ?? 0);
                if ($rating > 0) {
                    $dish_name = sanitize_text_field($value);
                    $dish_unit = sanitize_text_field($_POST["dish_unit_{$index}"] ?? '');
                    $ratings[] = array(
                        'dish_name' => $dish_name,
                        'dish_unit' => $dish_unit,
                        'rating' => $rating
                    );
                }
            }
        }
    }
    
    if (empty($ratings)) {
        wp_send_json_error('Необходимо оценить хотя бы одно блюдо');
    }
    
    // Сохраняем рейтинги
    foreach ($ratings as $rating_data) {
        $insert_result = $wpdb->insert(
            $entries_table,
            array(
                'request_id' => $request_id,
                'dish_name' => $rating_data['dish_name'],
                'dish_unit' => $rating_data['dish_unit'],
                'rating' => $rating_data['rating'],
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%d', '%s')
        );
        
        if ($insert_result === false) {
            error_log('Custom feedback insert error: ' . $wpdb->last_error);
            wp_send_json_error('Ошибка сохранения: ' . $wpdb->last_error);
        }
    }
    
    // Обновляем статус запроса и сохраняем общий комментарий
    $general_comment = sanitize_textarea_field($_POST['general_comment'] ?? '');
    $shared_instagram = intval($_POST['shared_instagram'] ?? 0);
    $shared_google = intval($_POST['shared_google'] ?? 0);
    
    $update_result = $wpdb->update(
        $requests_table,
        array(
            'status' => 'submitted',
            'general_comment' => $general_comment,
            'shared_instagram' => $shared_instagram,
            'shared_google' => $shared_google,
            'submitted_at' => current_time('mysql')
        ),
        array('id' => $request_id),
        array('%s', '%s', '%d', '%d', '%s'),
        array('%d')
    );
    
    if ($update_result === false) {
        error_log('Custom feedback update error: ' . $wpdb->last_error);
        wp_send_json_error('Ошибка обновления: ' . $wpdb->last_error);
    }
    
    wp_send_json_success('Отзыв сохранен');
}

// AJAX обработчик для удаления кастомных отзывов
add_action('wp_ajax_gustolocal_delete_custom_feedback', 'gustolocal_delete_custom_feedback');
function gustolocal_delete_custom_feedback() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Доступ запрещен');
    }
    
    check_ajax_referer('gustolocal_custom_feedback_delete', 'nonce');
    
    $token = sanitize_text_field($_POST['token'] ?? '');
    if (empty($token)) {
        wp_send_json_error('Токен не указан');
    }
    
    global $wpdb;
    $requests_table = $wpdb->prefix . 'custom_feedback_requests';
    $entries_table = $wpdb->prefix . 'custom_feedback_entries';
    
    // Получаем request_id по токену
    $request = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM $requests_table WHERE token = %s",
        $token
    ), ARRAY_A);
    
    if (!$request) {
        wp_send_json_error('Запрос не найден');
    }
    
    $request_id = $request['id'];
    
    // Удаляем все записи оценок
    $deleted_entries = $wpdb->query($wpdb->prepare(
        "DELETE FROM $entries_table WHERE request_id = %d",
        $request_id
    ));
    
    // Сбрасываем статус запроса в pending и очищаем данные
    $updated = $wpdb->update(
        $requests_table,
        array(
            'status' => 'pending',
            'general_comment' => '',
            'shared_instagram' => 0,
            'shared_google' => 0,
            'submitted_at' => null
        ),
        array('id' => $request_id),
        array('%s', '%s', '%d', '%d', '%s'),
        array('%d')
    );
    
    if ($updated === false) {
        wp_send_json_error('Ошибка обновления: ' . $wpdb->last_error);
    }
    
    wp_send_json_success(array('deleted_entries' => $deleted_entries, 'updated' => $updated));
}

// AJAX обработчик для получения детальных отзывов по блюду из кастомных опросов
add_action('wp_ajax_get_custom_feedback_details', 'gustolocal_get_custom_feedback_details');
function gustolocal_get_custom_feedback_details() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Доступ запрещен');
    }
    
    $dish_name = sanitize_text_field($_POST['dish_name'] ?? '');
    $dish_unit = sanitize_text_field($_POST['dish_unit'] ?? '');
    
    if (empty($dish_name)) {
        wp_send_json_error('Название блюда не указано');
    }
    
    global $wpdb;
    $entries_table = $wpdb->prefix . 'custom_feedback_entries';
    $requests_table = $wpdb->prefix . 'custom_feedback_requests';
    
    $query = $wpdb->prepare(
        "SELECT 
            e.*,
            r.client_name,
            r.general_comment,
            DATE_FORMAT(e.created_at, '%%d.%%m.%%Y %%H:%%i') as date
        FROM $entries_table e
        INNER JOIN $requests_table r ON r.id = e.request_id
        WHERE e.dish_name = %s",
        $dish_name
    );
    
    if (!empty($dish_unit)) {
        $query .= $wpdb->prepare(" AND e.dish_unit = %s", $dish_unit);
    } else {
        $query .= " AND (e.dish_unit = '' OR e.dish_unit IS NULL)";
    }
    
    $query .= " AND e.rating > 0 ORDER BY e.created_at DESC";
    
    $results = $wpdb->get_results($query, ARRAY_A);
    
    $result = array();
    foreach ($results as $row) {
        $result[] = array(
            'client_name' => $row['client_name'],
            'date' => $row['date'],
            'rating' => $row['rating'],
            'general_comment' => $row['general_comment'],
        );
    }
    
    wp_send_json_success($result);
}

// Страница управления кастомными опросами (объединенная с результатами)
function gustolocal_custom_feedback_management_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Определяем активную вкладку
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'requests';
    if (!in_array($active_tab, array('requests', 'results'))) {
        $active_tab = 'requests';
    }
    
    global $wpdb;
    $requests_table = $wpdb->prefix . 'custom_feedback_requests';
    $site_url = home_url();
    $page_url = admin_url('admin.php?page=gustolocal-custom-feedback');
    
    // Обработка создания нового опроса
    if (isset($_POST['create_custom_feedback']) && check_admin_referer('create_custom_feedback')) {
        $client_name = sanitize_text_field($_POST['client_name']);
        $client_contact = sanitize_text_field($_POST['client_contact']);
        $dishes_text = sanitize_textarea_field($_POST['dishes']);
        
        if (empty($client_name) || empty($dishes_text)) {
            echo '<div class="notice notice-error"><p>Заполните имя клиента и список блюд.</p></div>';
        } else {
            // Генерируем токен
            $token = wp_generate_password(32, false);
            
            // Сохраняем запрос
            $wpdb->insert(
                $requests_table,
                array(
                    'token' => $token,
                    'client_name' => $client_name,
                    'client_contact' => $client_contact,
                    'dishes' => $dishes_text,
                    'status' => 'pending',
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s')
            );
            
            if ($wpdb->last_error) {
                echo '<div class="notice notice-error"><p>Ошибка: ' . esc_html($wpdb->last_error) . '</p></div>';
            } else {
                $feedback_url = $site_url . '/feedback/' . $token;
                echo '<div class="notice notice-success"><p><strong>Опрос создан!</strong> Ссылка: <a href="' . esc_url($feedback_url) . '" target="_blank">' . esc_html($feedback_url) . '</a></p></div>';
            }
        }
    }
    
    // Данные для вкладки "Созданные опросы"
    if ($active_tab === 'requests') {
    $requests = $wpdb->get_results(
        "SELECT * FROM $requests_table ORDER BY created_at DESC LIMIT 100",
        ARRAY_A
    );
    }
    
    // Данные для вкладки "Результаты"
    if ($active_tab === 'results') {
        $custom_entries_table = $wpdb->prefix . 'custom_feedback_entries';
        
        // Фильтры для результатов
        $results_date_from = isset($_GET['results_date_from']) ? sanitize_text_field($_GET['results_date_from']) : '';
        $results_date_to = isset($_GET['results_date_to']) ? sanitize_text_field($_GET['results_date_to']) : '';
        $results_client = isset($_GET['results_client']) ? sanitize_text_field($_GET['results_client']) : '';
        
        $where_clauses = array("r.status = 'submitted'");
        if ($results_date_from) {
            $where_clauses[] = $wpdb->prepare("DATE(r.submitted_at) >= %s", $results_date_from);
        }
        if ($results_date_to) {
            $where_clauses[] = $wpdb->prepare("DATE(r.submitted_at) <= %s", $results_date_to);
        }
        if ($results_client) {
            $where_clauses[] = $wpdb->prepare("r.client_name LIKE %s", '%' . $wpdb->esc_like($results_client) . '%');
        }
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        
        // Последние опросы с отзывами
        $recent_feedback = $wpdb->get_results("
            SELECT 
                r.id,
                r.token,
                r.client_name,
                r.client_contact,
                DATE_FORMAT(MAX(r.submitted_at), '%d.%m.%Y %H:%i') as last_date,
                r.general_comment,
                r.shared_instagram,
                r.shared_google,
                COUNT(e.id) as dishes_count,
                ROUND(AVG(e.rating), 2) as avg_rating,
                GROUP_CONCAT(
                    CONCAT(
                        e.dish_name,
                        IF(e.dish_unit != '', CONCAT(' (', e.dish_unit, ')'), ''),
                        '::',
                        e.rating
                    )
                    ORDER BY e.created_at DESC
                    SEPARATOR '||'
                ) as dishes_list
            FROM $requests_table r
            INNER JOIN $custom_entries_table e ON e.request_id = r.id
            $where_sql
            AND e.rating > 0
            GROUP BY r.id, r.token, r.client_name, r.client_contact
            ORDER BY MAX(r.submitted_at) DESC
            LIMIT 100
        ", ARRAY_A);
        
        $delete_nonce = wp_create_nonce('gustolocal_custom_feedback_delete');
    }
    
    $delete_nonce = wp_create_nonce('gustolocal_custom_feedback_delete');
    
    ?>
    <div class="wrap">
        <h1>Кастомные опросы</h1>
        
        <nav class="nav-tab-wrapper" style="margin: 20px 0;">
            <a href="<?php echo esc_url($page_url . '&tab=requests'); ?>" class="nav-tab <?php echo $active_tab === 'requests' ? 'nav-tab-active' : ''; ?>">
                Созданные опросы
            </a>
            <a href="<?php echo esc_url($page_url . '&tab=results'); ?>" class="nav-tab <?php echo $active_tab === 'results' ? 'nav-tab-active' : ''; ?>">
                Результаты
            </a>
        </nav>
        
        <?php if ($active_tab === 'requests'): ?>
        <p>Создайте опрос для клиентов, которым вы отправили кастомное меню (без формального заказа в системе).</p>
        
        <h2>Создать новый опрос</h2>
        <form method="post" action="" style="max-width: 800px; margin: 20px 0; padding: 20px; background: #f5f5f5; border-radius: 5px;">
            <?php wp_nonce_field('create_custom_feedback'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="client_name">Имя клиента *</label></th>
                    <td>
                        <input type="text" id="client_name" name="client_name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="client_contact">Контакт (телефон/email)</label></th>
                    <td>
                        <input type="text" id="client_contact" name="client_contact" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="dishes">Список блюд *</label></th>
                    <td>
                        <textarea id="dishes" name="dishes" rows="10" class="large-text" required placeholder="Введите блюда по одному на строку, например:&#10;Хумус (150 г)&#10;Сэндвич с пастрами (200 г)&#10;Паста с индейкой (250 г)"></textarea>
                        <p class="description">Введите блюда по одному на строку. Можно указать единицу измерения в скобках.</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="create_custom_feedback" class="button button-primary" value="Создать опрос и получить ссылку">
            </p>
        </form>
        
        <h2>Созданные опросы</h2>
        <?php if (empty($requests)): ?>
            <div class="notice notice-info">
                <p>Опросы не созданы.</p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Дата создания</th>
                        <th>Клиент</th>
                        <th>Контакт</th>
                        <th>Блюд</th>
                        <th>Статус</th>
                            <th style="width: 550px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($requests as $request): 
                        $dishes_list = explode("\n", $request['dishes']);
                        $dishes_count = count(array_filter($dishes_list, 'trim'));
                        $feedback_url = $site_url . '/feedback/' . $request['token'];
                        $status_label = $request['status'] === 'submitted' ? 'Заполнен' : 'Ожидает';
                        $status_class = $request['status'] === 'submitted' ? 'success' : 'warning';
                        
                        // Формируем WhatsApp ссылку
                        $whatsapp_link = '';
                        if (!empty($request['client_contact'])) {
                            $phone = preg_replace('/[^0-9]/', '', $request['client_contact']);
                            if ($phone) {
                                $whatsapp_link = 'https://wa.me/' . $phone;
                            }
                        }
                    ?>
                        <tr>
                            <td><?php echo esc_html(date('d.m.Y H:i', strtotime($request['created_at']))); ?></td>
                            <td><strong><?php echo esc_html($request['client_name']); ?></strong></td>
                            <td><?php echo esc_html($request['client_contact'] ?: '—'); ?></td>
                            <td><?php echo esc_html($dishes_count); ?></td>
                            <td><span class="status-<?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <input type="text" 
                                           id="custom-feedback-link-<?php echo esc_attr($request['id']); ?>" 
                                           value="<?php echo esc_attr($feedback_url); ?>" 
                                           readonly 
                                           style="flex: 1; min-width: 200px; font-size: 11px;">
                                    <button type="button" 
                                            class="button button-small copy-link-btn" 
                                            data-target="custom-feedback-link-<?php echo esc_attr($request['id']); ?>">
                                        Копировать
                                    </button>
                                    <?php if ($whatsapp_link): ?>
                                        <a href="<?php echo esc_url($whatsapp_link); ?>" 
                                           target="_blank" 
                                           class="button button-small">
                                            WhatsApp
                                        </a>
                                    <?php endif; ?>
                                        <?php if ($request['status'] === 'submitted'): ?>
                                            <a href="<?php echo esc_url($page_url . '&tab=results&token=' . $request['token']); ?>" 
                                               class="button button-small button-primary">
                                                Результаты
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" 
                                            class="button button-small delete-custom-feedback-manage-btn" 
                                            data-token="<?php echo esc_attr($request['token']); ?>"
                                            style="color: #dc3232;">
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
            
        <?php elseif ($active_tab === 'results'): ?>
            <p>Просмотр всех полученных отзывов. Кликните на строку для просмотра деталей.</p>
            
            <form method="get" action="" style="margin: 20px 0; padding: 20px; background: #f5f5f5; border-radius: 5px;">
                <input type="hidden" name="page" value="gustolocal-custom-feedback">
                <input type="hidden" name="tab" value="results">
                <table class="form-table">
                    <tr>
                        <th><label for="results_date_from">Дата от:</label></th>
                        <td><input type="date" id="results_date_from" name="results_date_from" value="<?php echo esc_attr($results_date_from); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="results_date_to">Дата до:</label></th>
                        <td><input type="date" id="results_date_to" name="results_date_to" value="<?php echo esc_attr($results_date_to); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="results_client">Клиент:</label></th>
                        <td><input type="text" id="results_client" name="results_client" value="<?php echo esc_attr($results_client); ?>" class="regular-text" placeholder="Поиск по имени"></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Применить фильтры">
                    <a href="<?php echo esc_url($page_url . '&tab=results'); ?>" class="button">Сбросить</a>
                </p>
            </form>
            
            <?php if (empty($recent_feedback)): ?>
                <div class="notice notice-info">
                    <p>Нет отзывов для выбранного периода.</p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped" id="custom-feedback-results-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;"></th>
                            <th>Дата</th>
                            <th>Клиент</th>
                            <th>Контакт</th>
                            <th>Блюд</th>
                            <th>Средняя</th>
                            <th>Комментарий</th>
                            <th>Instagram</th>
                            <th>Google</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Получаем список просмотренных токенов для текущего пользователя
                        $viewed_custom_tokens = get_user_meta(get_current_user_id(), '_gustolocal_viewed_custom_feedbacks', true);
                        
                        // Обрабатываем разные форматы данных
                        if (empty($viewed_custom_tokens)) {
                            $viewed_custom_tokens_array = array();
                        } elseif (is_array($viewed_custom_tokens)) {
                            $viewed_custom_tokens_array = $viewed_custom_tokens;
                        } else {
                            $decoded = json_decode($viewed_custom_tokens, true);
                            $viewed_custom_tokens_array = is_array($decoded) ? $decoded : array();
                        }
                        
                        foreach ($recent_feedback as $feedback): 
                            $is_new = strtotime($feedback['last_date']) >= strtotime('-7 days');
                            $is_viewed = in_array($feedback['token'], $viewed_custom_tokens_array, true);
                            $show_badge = $is_new && !$is_viewed;
                        ?>
                            <tr class="custom-feedback-row clickable-row" data-token="<?php echo esc_attr($feedback['token']); ?>" style="cursor: pointer;">
                                <td>
                                    <span class="toggle-icon" style="font-size: 18px; color: #0073aa;">▼</span>
                                </td>
                                <td>
                                    <?php echo esc_html($feedback['last_date']); ?>
                                    <span class="new-badge" data-token="<?php echo esc_attr($feedback['token']); ?>" style="color: #f0ad4e; font-size: 14px; display: <?php echo $show_badge ? 'inline' : 'none'; ?>;" title="Новый отзыв">⭐</span>
                                </td>
                                <td><strong><?php echo esc_html($feedback['client_name']); ?></strong></td>
                                <td><?php echo esc_html($feedback['client_contact'] ?: '—'); ?></td>
                                <td><?php echo esc_html($feedback['dishes_count']); ?></td>
                                <td>
                                    <strong><?php echo number_format((float) $feedback['avg_rating'], 2); ?></strong>
                                    <?php
                                    $avg = (float) $feedback['avg_rating'];
                                    if ($avg >= 3.5) echo '😍';
                                    elseif ($avg >= 2.5) echo '😊';
                                    elseif ($avg >= 1.5) echo '😐';
                                    else echo '😞';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($feedback['general_comment']): ?>
                                        <?php echo esc_html(wp_trim_words($feedback['general_comment'], 10)); ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo !empty($feedback['shared_instagram']) ? '<span style="color: #E4405F;">Да</span>' : '—'; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo !empty($feedback['shared_google']) ? '<span style="color: #4285F4;">Да</span>' : '—'; ?>
                                </td>
                                <td>
                                    <button class="button button-small delete-custom-feedback-btn" data-token="<?php echo esc_attr($feedback['token']); ?>" onclick="event.stopPropagation();">
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                            <tr class="custom-feedback-details-row" style="display: none;">
                                <td colspan="10" style="background: #f9f9f9; padding: 20px;">
                                    <div style="max-width: 800px;">
                                        <h3 style="margin-top: 0;">Детали отзыва</h3>
                                        
                                        <div style="margin-bottom: 15px;">
                                            <strong>Оценки по блюдам:</strong>
                                            <div style="margin-top: 10px;">
                                                <?php
                                                if (!empty($feedback['dishes_list'])) {
                                                    $items = explode('||', $feedback['dishes_list']);
                                                    foreach ($items as $item) {
                                                        list($name, $rating) = array_pad(explode('::', $item), 2, '');
                                                        $emoji = array('1' => '😞', '2' => '😐', '3' => '😊', '4' => '😍');
                                                        echo '<div style="padding: 5px 0; border-bottom: 1px solid #eee;">';
                                                        echo '<strong>' . esc_html($name) . '</strong>: ';
                                                        echo '<span style="font-size: 20px;">' . ($emoji[$rating] ?? $rating) . '</span>';
                                                        echo '</div>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($feedback['general_comment']): ?>
                                            <div style="margin-bottom: 15px;">
                                                <strong>Комментарий:</strong>
                                                <div style="margin-top: 5px; padding: 10px; background: white; border-radius: 4px;">
                                                    <?php echo nl2br(esc_html($feedback['general_comment'])); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div>
                                            <strong>Поделились:</strong>
                                            <?php if ($feedback['shared_instagram']): ?>
                                                <span style="color: #E4405F;">Instagram ✓</span>
                                            <?php endif; ?>
                                            <?php if ($feedback['shared_google']): ?>
                                                <span style="color: #4285F4;">Google ✓</span>
                                            <?php endif; ?>
                                            <?php if (!$feedback['shared_instagram'] && !$feedback['shared_google']): ?>
                                                Нет
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
        
        <style>
        .status-success { color: #46b450; font-weight: bold; }
        .status-warning { color: #f56e28; font-weight: bold; }
    .clickable-row:hover { background-color: #f0f6fc !important; }
    .toggle-icon { transition: transform 0.2s; display: inline-block; }
    .toggle-icon.rotated { transform: rotate(180deg); }
    .custom-feedback-details-row td { border-top: 2px solid #0073aa !important; }
    .custom-feedback-details-row { background-color: #f9f9f9 !important; }
        </style>
    
    <script>
    // Глобальные функции для работы с просмотренными отзывами (дублируем для кастомных опросов)
    if (typeof markFeedbackAsViewed === 'undefined') {
        function markFeedbackAsViewed(token, type) {
            type = type || 'regular';
            
            // Сохраняем в localStorage для быстрого скрытия звездочки
            var storageKey = type === 'custom' ? 'gustolocal_viewed_custom_feedbacks' : 'gustolocal_viewed_feedbacks';
            var viewed = JSON.parse(localStorage.getItem(storageKey) || '[]');
            if (viewed.indexOf(token) === -1) {
                viewed.push(token);
                localStorage.setItem(storageKey, JSON.stringify(viewed));
            }
            
            // Отправляем на сервер для обновления счетчика в меню
            var formData = new FormData();
            formData.append('action', 'gustolocal_mark_feedback_viewed');
            formData.append('token', token);
            formData.append('type', type);
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    // Обновляем счетчик в меню
                    updateMenuCounter(type);
                }
            })
            .catch(function(error) {
                console.error('Ошибка при сохранении просмотренного отзыва:', error);
            });
        }
    }
    
    if (typeof updateMenuCounter === 'undefined') {
        // Функция для обновления счетчика в меню
        function updateMenuCounter(type) {
            // Получаем актуальный счетчик с сервера
            var formData = new FormData();
            formData.append('action', 'gustolocal_get_feedback_count');
            formData.append('type', type);
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    var count = data.data.count || 0;
                    var menuSlug = type === 'custom' ? 'gustolocal-custom-feedback' : 'gustolocal-feedback';
                    
                    // Пробуем разные способы найти элемент меню
                    var menuItem = null;
                    
                    // Способ 1: поиск по href с полным URL
                    var allLinks = document.querySelectorAll('a[href*="' + menuSlug + '"]');
                    if (allLinks.length > 0) {
                        menuItem = allLinks[0];
                    }
                    
                    // Способ 2: поиск по page параметру
                    if (!menuItem) {
                        allLinks = document.querySelectorAll('a[href*="page=' + menuSlug + '"]');
                        if (allLinks.length > 0) {
                            menuItem = allLinks[0];
                        }
                    }
                    
                    // Способ 3: поиск в меню WooCommerce по тексту
                    if (!menuItem && type === 'custom') {
                        var wooMenu = document.querySelector('#toplevel_page_woocommerce');
                        if (wooMenu) {
                            var links = wooMenu.querySelectorAll('a');
                            for (var i = 0; i < links.length; i++) {
                                if (links[i].textContent.indexOf('Кастомные опросы') !== -1) {
                                    menuItem = links[i];
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (!menuItem && type === 'regular') {
                        var wooMenu = document.querySelector('#toplevel_page_woocommerce');
                        if (wooMenu) {
                            var links = wooMenu.querySelectorAll('a');
                            for (var i = 0; i < links.length; i++) {
                                if (links[i].textContent.indexOf('Обратная связь') !== -1) {
                                    menuItem = links[i];
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (menuItem) {
                        var badge = menuItem.querySelector('.awaiting-mod');
                        if (count > 0) {
                            if (badge) {
                                badge.textContent = count;
                            } else {
                                // Создаем новый badge
                                var span = document.createElement('span');
                                span.className = 'awaiting-mod';
                                span.textContent = count;
                                menuItem.appendChild(document.createTextNode(' '));
                                menuItem.appendChild(span);
                            }
                        } else {
                            // Удаляем badge если счетчик = 0
                            if (badge) {
                                badge.remove();
                            }
                        }
                    } else {
                        console.warn('Элемент меню не найден для типа:', type);
                    }
                }
            })
            .catch(function(error) {
                console.error('Ошибка при получении счетчика:', error);
            });
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Копирование ссылок
        document.querySelectorAll('.copy-link-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);
                if (input) {
                input.select();
                input.setSelectionRange(0, 99999);
                document.execCommand('copy');
                
                var originalText = this.textContent;
                this.textContent = 'Скопировано!';
                this.classList.add('button-primary');
                
                setTimeout(function() {
                    this.textContent = originalText;
                    this.classList.remove('button-primary');
                }.bind(this), 2000);
                }
            });
        });
        
        // Раскрытие/сворачивание строк с деталями для кастомных опросов
        document.querySelectorAll('.custom-feedback-row.clickable-row').forEach(function(row) {
            row.addEventListener('click', function(e) {
                // Не раскрываем если кликнули на кнопку или ссылку
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.closest('button') || e.target.closest('a')) {
                    return;
                }
                
                var detailsRow = this.nextElementSibling;
                var toggleIcon = this.querySelector('.toggle-icon');
                var token = this.getAttribute('data-token');
                // Ищем все звездочки в строке
                var newBadges = this.querySelectorAll('.new-badge');
                
                if (detailsRow && detailsRow.classList.contains('custom-feedback-details-row')) {
                    if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
                        detailsRow.style.display = 'table-row';
                        if (toggleIcon) {
                            toggleIcon.classList.add('rotated');
                        }
                        // Помечаем отзыв как просмотренный и скрываем все звездочки
                        if (token) {
                            // Скрываем все найденные звездочки
                            newBadges.forEach(function(badge) {
                                if (badge.style.display !== 'none' && badge.offsetParent !== null) {
                                    badge.style.display = 'none';
                                }
                            });
                            // Помечаем как просмотренный на сервере
                            markFeedbackAsViewed(token, 'custom');
                        }
                    } else {
                        detailsRow.style.display = 'none';
                        if (toggleIcon) {
                            toggleIcon.classList.remove('rotated');
                        }
                    }
                }
            });
        });
        
        // Удаление опросов
        document.querySelectorAll('.delete-custom-feedback-manage-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var token = this.getAttribute('data-token');
                if (!token) {
                    return;
                }
                
                if (!confirm('Удалить опрос полностью? После удаления ссылка снова станет активной. Это действие нельзя отменить.')) {
                    return;
                }
                
                var formData = new FormData();
                formData.append('action', 'gustolocal_delete_custom_feedback');
                formData.append('token', token);
                formData.append('nonce', '<?php echo esc_js($delete_nonce); ?>');
                
                var btnElement = this;
                btnElement.disabled = true;
                btnElement.textContent = 'Удаление...';
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        btnElement.closest('tr').remove();
                    } else {
                        alert(data.data || 'Не удалось удалить опрос');
                        btnElement.disabled = false;
                        btnElement.textContent = 'Удалить';
                    }
                })
                .catch(function() {
                    alert('Ошибка при удалении опроса');
                    btnElement.disabled = false;
                    btnElement.textContent = 'Удалить';
                });
            });
        });
        
        // Удаление отзывов
        document.querySelectorAll('.delete-custom-feedback-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var token = this.getAttribute('data-token');
                if (!token) {
                    return;
                }
                
                if (!confirm('Удалить отзыв полностью? После удаления ссылка снова станет активной. Это действие нельзя отменить.')) {
                    return;
                }
                
                var formData = new FormData();
                formData.append('action', 'gustolocal_delete_custom_feedback');
                formData.append('token', token);
                formData.append('nonce', '<?php echo esc_js($delete_nonce); ?>');
                
                var btnElement = this;
                btnElement.disabled = true;
                btnElement.textContent = 'Удаление...';
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.data || 'Не удалось удалить отзыв');
                        btnElement.disabled = false;
                        btnElement.textContent = 'Удалить';
                    }
                })
                .catch(function() {
                    alert('Ошибка при удалении отзыва');
                    btnElement.disabled = false;
                    btnElement.textContent = 'Удалить';
                });
            });
        });
    });
    </script>
    <?php
}

// Страница результатов кастомных опросов (аналог "Результаты отзывов")
function gustolocal_custom_feedback_results_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $wpdb;
    $custom_entries_table = $wpdb->prefix . 'custom_feedback_entries';
    
    // Получаем статистику по блюдам из кастомных опросов
    $dish_stats = $wpdb->get_results("
        SELECT 
            dish_name,
            dish_unit,
            COUNT(*) as total_reviews,
            AVG(rating) as avg_rating,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
        FROM $custom_entries_table
        WHERE rating > 0
        GROUP BY dish_name, dish_unit
        ORDER BY avg_rating DESC, total_reviews DESC
    ", ARRAY_A);
    
    // Последние опросы с отзывами
    $custom_requests_table = $wpdb->prefix . 'custom_feedback_requests';
    $recent_feedback = $wpdb->get_results("
        SELECT 
            r.id,
            r.token,
            r.client_name,
            r.client_contact,
            DATE_FORMAT(MAX(r.submitted_at), '%d.%m.%Y %H:%i') as last_date,
            r.general_comment,
            r.shared_instagram,
            r.shared_google,
            COUNT(e.id) as dishes_count,
            ROUND(AVG(e.rating), 2) as avg_rating,
            GROUP_CONCAT(
                CONCAT(
                    e.dish_name,
                    IF(e.dish_unit != '', CONCAT(' (', e.dish_unit, ')'), ''),
                    '::',
                    e.rating
                )
                ORDER BY e.created_at DESC
                SEPARATOR '||'
            ) as dishes_list
        FROM $custom_requests_table r
        INNER JOIN $custom_entries_table e ON e.request_id = r.id
        WHERE r.status = 'submitted' AND e.rating > 0
        GROUP BY r.id, r.token, r.client_name, r.client_contact
        ORDER BY MAX(r.submitted_at) DESC
        LIMIT 50
    ", ARRAY_A);
    
    $delete_nonce = wp_create_nonce('gustolocal_custom_feedback_delete');
    
    ?>
    <div class="wrap">
        <h1>Результаты кастомных опросов</h1>
        
        <h2>Статистика по блюдам</h2>
        <p class="description">Таблица автоматически группирует отзывы по названию блюда и единице измерения. Кликните на строку, чтобы увидеть все отзывы по этому блюду.</p>
        
        <table class="wp-list-table widefat fixed striped" id="custom-feedback-stats-table">
            <thead>
                <tr>
                    <th>Блюдо</th>
                    <th>Отзывов</th>
                    <th>Средняя оценка</th>
                    <th>😍</th>
                    <th>😊</th>
                    <th>😐</th>
                    <th>😞</th>
                    <th style="width: 100px;">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dish_stats)): ?>
                    <?php foreach ($dish_stats as $stat): 
                        $avg = round($stat['avg_rating'], 2);
                        $dish_full = $stat['dish_name'] . ($stat['dish_unit'] ? ' (' . $stat['dish_unit'] . ')' : '');
                    ?>
                        <tr data-dish-name="<?php echo esc_attr($stat['dish_name']); ?>" data-dish-unit="<?php echo esc_attr($stat['dish_unit']); ?>">
                            <td><strong><?php echo esc_html($dish_full); ?></strong></td>
                            <td><?php echo esc_html($stat['total_reviews']); ?></td>
                            <td>
                                <strong><?php echo number_format($avg, 2); ?></strong>
                                <span style="font-size: 20px;">
                                    <?php 
                                    if ($avg >= 3.5) echo '😍';
                                    elseif ($avg >= 2.5) echo '😊';
                                    elseif ($avg >= 1.5) echo '😐';
                                    else echo '😞';
                                    ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($stat['rating_4']); ?></td>
                            <td><?php echo esc_html($stat['rating_3']); ?></td>
                            <td><?php echo esc_html($stat['rating_2']); ?></td>
                            <td><?php echo esc_html($stat['rating_1']); ?></td>
                            <td>
                                <button type="button" class="button button-small view-custom-details-btn" 
                                        data-dish-name="<?php echo esc_attr($stat['dish_name']); ?>" 
                                        data-dish-unit="<?php echo esc_attr($stat['dish_unit']); ?>">
                                    Детали
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Нет отзывов</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <h2>Последние комментарии и активности</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Клиент</th>
                    <th>Контакт</th>
                    <th>Блюд</th>
                    <th>Средняя</th>
                    <th>Отзывы</th>
                    <th>Комментарий</th>
                    <th>Instagram</th>
                    <th>Google</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_feedback)): ?>
                    <?php foreach ($recent_feedback as $feedback): ?>
                        <tr>
                            <td><?php echo esc_html($feedback['last_date']); ?></td>
                            <td><?php echo esc_html($feedback['client_name']); ?></td>
                            <td><?php echo esc_html($feedback['client_contact'] ?: '—'); ?></td>
                            <td><?php echo esc_html($feedback['dishes_count']); ?></td>
                            <td><?php echo esc_html(number_format((float) $feedback['avg_rating'], 2)); ?></td>
                            <td>
                                <?php
                                if (!empty($feedback['dishes_list'])) {
                                    $items = explode('||', $feedback['dishes_list']);
                                    foreach ($items as $item) {
                                        list($name, $rating) = array_pad(explode('::', $item), 2, '');
                                        $emoji = array('1' => '😞', '2' => '😐', '3' => '😊', '4' => '😍');
                                        echo '<div>' . esc_html($name) . ': ' . ($emoji[$rating] ?? $rating) . '</div>';
                                    }
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td><?php echo $feedback['general_comment'] ? nl2br(esc_html($feedback['general_comment'])) : '—'; ?></td>
                            <td><?php echo !empty($feedback['shared_instagram']) ? '✅' : '—'; ?></td>
                            <td><?php echo !empty($feedback['shared_google']) ? '✅' : '—'; ?></td>
                            <td>
                                <button class="button delete-custom-feedback-btn" data-token="<?php echo esc_attr($feedback['token']); ?>">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">Нет комментариев</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <style>
    .feedback-modal {
        display: none;
        position: fixed;
        z-index: 100000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    .feedback-modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 90%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .feedback-modal-close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .feedback-modal-close:hover {
        color: #000;
    }
    .feedback-detail-item {
        padding: 15px;
        margin-bottom: 10px;
        background: #f9f9f9;
        border-left: 4px solid #0073aa;
        border-radius: 4px;
    }
    </style>
    
    <div id="custom-feedback-modal" class="feedback-modal">
        <div class="feedback-modal-content">
            <span class="feedback-modal-close">&times;</span>
            <h2 id="custom-modal-dish-name"></h2>
            <div id="custom-modal-feedback-list"></div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('custom-feedback-modal');
        var closeBtn = modal.querySelector('.feedback-modal-close');
        var viewDetailsBtns = document.querySelectorAll('.view-custom-details-btn');
        
        viewDetailsBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var dishName = this.getAttribute('data-dish-name');
                var dishUnit = this.getAttribute('data-dish-unit');
                showCustomFeedbackDetails(dishName, dishUnit);
            });
        });
        
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        };
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
        
        function showCustomFeedbackDetails(dishName, dishUnit) {
            document.getElementById('custom-modal-dish-name').textContent = dishName + (dishUnit ? ' (' + dishUnit + ')' : '');
            document.getElementById('custom-modal-feedback-list').innerHTML = '<p>Загрузка...</p>';
            modal.style.display = 'block';
            
            var formData = new FormData();
            formData.append('action', 'get_custom_feedback_details');
            formData.append('dish_name', dishName);
            formData.append('dish_unit', dishUnit);
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    var html = '';
                    if (data.data.length === 0) {
                        html = '<p>Нет детальных отзывов для этого блюда.</p>';
                    } else {
                        data.data.forEach(function(feedback) {
                            var ratingEmoji = {'1': '😞', '2': '😐', '3': '😊', '4': '😍'};
                            html += '<div class="feedback-detail-item">';
                            html += '<div style="display: flex; align-items: center; margin-bottom: 10px;">';
                            html += '<span class="rating" style="font-size: 24px; margin-right: 10px;">' + ratingEmoji[feedback.rating] + '</span>';
                            html += '<strong>' + feedback.client_name + '</strong>';
                            html += '<span style="margin-left: auto; color: #666; font-size: 12px;">' + feedback.date + '</span>';
                            html += '</div>';
                            if (feedback.general_comment) {
                                html += '<p style="margin: 10px 0; padding: 10px; background: white; border-radius: 4px;">' + escapeHtml(feedback.general_comment) + '</p>';
                            }
                            html += '</div>';
                        });
                    }
                    document.getElementById('custom-modal-feedback-list').innerHTML = html;
                } else {
                    document.getElementById('custom-modal-feedback-list').innerHTML = '<p>Ошибка: ' + (data.data || 'Не удалось загрузить отзывы') + '</p>';
                }
            })
            .catch(function(error) {
                document.getElementById('custom-modal-feedback-list').innerHTML = '<p>Ошибка: ' + error + '</p>';
            });
        }
        
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        document.querySelectorAll('.delete-custom-feedback-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var token = this.getAttribute('data-token');
                if (!token) {
                    return;
                }
                
                if (!confirm('Удалить отзыв полностью? После удаления ссылка снова станет активной. Это действие нельзя отменить.')) {
                    return;
                }
                
                var formData = new FormData();
                formData.append('action', 'gustolocal_delete_custom_feedback');
                formData.append('token', token);
                formData.append('nonce', '<?php echo esc_js($delete_nonce); ?>');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.data || 'Не удалось удалить отзыв');
                    }
                })
                .catch(function() {
                    alert('Ошибка при удалении отзыва');
                });
            });
        });
    });
    </script>
    <?php
}

/* ========================================
   ПЕЧАТЬ ЗАКАЗОВ НА ТЕРМОПРИНТЕРЕ
   ======================================== */

// Добавляем кнопку печати в админке заказов WooCommerce
add_filter('woocommerce_order_actions', 'gustolocal_add_print_order_action', 10, 2);
function gustolocal_add_print_order_action($actions, $order) {
    if (!is_a($order, 'WC_Order')) {
        return $actions;
    }
    
    $actions['gustolocal_print_order'] = __('Печать на термобумаге', 'gustolocal');
    return $actions;
}

// Обработка действия печати
add_action('woocommerce_order_action_gustolocal_print_order', 'gustolocal_handle_print_order_action');
function gustolocal_handle_print_order_action($order) {
    if (!is_a($order, 'WC_Order')) {
        return;
    }
    
    // Перенаправляем на страницу печати
    $print_url = admin_url('admin.php?page=gustolocal-print-order&order_id=' . $order->get_id());
    wp_redirect($print_url);
    exit;
}

// Добавляем страницу печати в админке
add_action('admin_menu', 'gustolocal_add_print_order_page');
function gustolocal_add_print_order_page() {
    add_submenu_page(
        null, // Скрытая страница
        'Печать заказа',
        'Печать заказа',
        'manage_woocommerce',
        'gustolocal-print-order',
        'gustolocal_print_order_page'
    );
}

// Страница печати заказа
function gustolocal_print_order_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die(__('У вас нет прав для доступа к этой странице.'));
    }
    
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    
    if (!$order_id) {
        echo '<div class="wrap"><h1>Ошибка</h1><p>Не указан ID заказа.</p></div>';
        return;
    }
    
    $order = wc_get_order($order_id);
    
    if (!$order) {
        echo '<div class="wrap"><h1>Ошибка</h1><p>Заказ не найден.</p></div>';
        return;
    }
    
    // Получаем данные заказа для печати
    $print_data = gustolocal_get_order_print_data($order);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Печать заказа #<?php echo esc_html($order_id); ?></title>
        <!-- QZ Tray JS Library -->
        <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
        <style>
            @media print {
                body { margin: 0; padding: 0; }
                .no-print { display: none !important; }
                @page { margin: 0; size: 80mm auto; }
            }
            body {
                font-family: 'Courier New', monospace;
                font-size: 12px;
                line-height: 1.4;
                max-width: 80mm;
                margin: 0 auto;
                padding: 10px;
            }
            .print-header {
                text-align: center;
                border-bottom: 1px dashed #000;
                padding-bottom: 10px;
                margin-bottom: 10px;
            }
            .print-header h1 {
                font-size: 16px;
                margin: 5px 0;
                font-weight: bold;
            }
            .print-section {
                margin: 10px 0;
                padding: 5px 0;
            }
            .print-section-title {
                font-weight: bold;
                text-transform: uppercase;
                border-bottom: 1px solid #000;
                margin-bottom: 5px;
                padding-bottom: 3px;
            }
            .print-line {
                margin: 3px 0;
            }
            .print-item {
                margin: 5px 0;
                padding: 3px 0;
            }
            .print-item-name {
                font-weight: bold;
            }
            .print-item-meta {
                font-size: 10px;
                color: #666;
                margin-left: 10px;
            }
            .print-footer {
                border-top: 1px dashed #000;
                margin-top: 15px;
                padding-top: 10px;
                text-align: center;
                font-size: 10px;
            }
            .no-print {
                text-align: center;
                margin: 20px 0;
            }
            .print-btn {
                background: #0073aa;
                color: white;
                padding: 10px 20px;
                border: none;
                cursor: pointer;
                font-size: 16px;
                border-radius: 3px;
            }
            .print-btn:hover {
                background: #005a87;
            }
            .print-btn:disabled {
                background: #ccc;
                cursor: not-allowed;
            }
            .qz-status {
                margin: 10px 0;
                padding: 8px;
                border-radius: 4px;
                font-size: 12px;
            }
            .qz-status.connected {
                background: #d4edda;
                color: #155724;
            }
            .qz-status.disconnected {
                background: #f8d7da;
                color: #721c24;
            }
            .qz-status.connecting {
                background: #fff3cd;
                color: #856404;
            }
        </style>
    </head>
    <body>
        <div class="no-print">
            <div id="qz-status" class="qz-status disconnected">QZ Tray: Не подключен</div>
            <button class="print-btn" id="qz-print-btn" onclick="printViaQZTray()">🖨️ Печать через QZ Tray</button>
            <button class="print-btn" onclick="window.print()">🖨️ Печать (браузер)</button>
            <button class="print-btn" onclick="window.close()" style="background: #666; margin-left: 10px;">Закрыть</button>
        </div>
        
        <div class="print-content">
            <div class="print-header">
                <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
                <div class="print-line"><?php echo esc_html(get_bloginfo('description')); ?></div>
            </div>
            
            <div class="print-section">
                <div class="print-section-title"><?php echo esc_html($print_data['customer_name']); ?></div>
            </div>
            
            <div class="print-section">
                <div class="print-section-title">Содержимое заказа</div>
                <?php foreach ($print_data['items'] as $item): ?>
                <div class="print-item">
                    <div class="print-item-name">
                        <?php echo esc_html($item['name']); ?>
                        <?php if (!empty($item['unit'])): ?>
                        <span style="font-size: 10px;"> (<?php echo esc_html($item['unit']); ?>)</span>
                        <?php endif; ?>
                        <?php if ($item['quantity'] > 1): ?>
                        <strong> x<?php echo esc_html($item['quantity']); ?></strong>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="print-section">
                <div class="print-section-title">
                    <?php if ($print_data['shipping_address']): ?>
                        Доставка: <?php echo esc_html($print_data['shipping_address']); ?>
                    <?php else: ?>
                        Самовывоз
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script>
        // Функция декодирования unicode escape последовательностей
        function decodeUnicode(str) {
            if (typeof str !== 'string') return str;
            return str.replace(/\\u([0-9a-fA-F]{4})/g, function(match, code) {
                return String.fromCharCode(parseInt(code, 16));
            });
        }
        
        // Функция рекурсивного декодирования объекта
        function decodeUnicodeRecursive(obj) {
            if (typeof obj === 'string') {
                return decodeUnicode(obj);
            } else if (Array.isArray(obj)) {
                return obj.map(decodeUnicodeRecursive);
            } else if (obj !== null && typeof obj === 'object') {
                const decoded = {};
                for (let key in obj) {
                    decoded[decodeUnicode(key)] = decodeUnicodeRecursive(obj[key]);
                }
                return decoded;
            }
            return obj;
        }
        
        // Данные заказа для печати (с декодированием unicode escape)
        const orderDataRaw = <?php echo json_encode($print_data, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS); ?>;
        const orderData = decodeUnicodeRecursive(orderDataRaw);
        
        const siteNameRaw = <?php echo json_encode(get_bloginfo('name'), JSON_UNESCAPED_UNICODE); ?>;
        const siteName = decodeUnicode(siteNameRaw);
        
        const siteDescriptionRaw = <?php echo json_encode(get_bloginfo('description'), JSON_UNESCAPED_UNICODE); ?>;
        const siteDescription = decodeUnicode(siteDescriptionRaw);
        
        // QZ Tray подключение
        let qzConnected = false;
        const PRINTER_NAME = 'Printer POS-80';
        
        // Подключение к QZ Tray при загрузке страницы
        window.addEventListener('load', function() {
            connectToQZTray();
        });
        
        // Функция подключения к QZ Tray
        async function connectToQZTray() {
            const statusEl = document.getElementById('qz-status');
            const printBtn = document.getElementById('qz-print-btn');
            
            try {
                statusEl.textContent = 'QZ Tray: Подключение...';
                statusEl.className = 'qz-status connecting';
                
                // Проверяем, доступен ли QZ Tray
                if (typeof qz === 'undefined') {
                    throw new Error('QZ Tray библиотека не загружена');
                }
                
                // Подключаемся к QZ Tray
                await qz.websocket.connect().then(function() {
                    qzConnected = true;
                    statusEl.textContent = 'QZ Tray: Подключен ✓';
                    statusEl.className = 'qz-status connected';
                    printBtn.disabled = false;
                }).catch(function(err) {
                    throw err;
                });
            } catch (err) {
                qzConnected = false;
                statusEl.textContent = 'QZ Tray: Не подключен (убедитесь, что QZ Tray запущен)';
                statusEl.className = 'qz-status disconnected';
                printBtn.disabled = true;
                console.error('QZ Tray connection error:', err);
            }
        }
        
        // Функция конвертации UTF-8 в CP866 (DOS Cyrillic) для ESC-POS
        function utf8ToCp866(str) {
            if (!str) return str;
            
            // Таблица соответствия основных кириллических символов UTF-8 -> CP866
            const utf8ToCp866Map = {
                'А': '\x80', 'Б': '\x81', 'В': '\x82', 'Г': '\x83', 'Д': '\x84', 'Е': '\x85', 'Ж': '\x86', 'З': '\x87',
                'И': '\x88', 'Й': '\x89', 'К': '\x8A', 'Л': '\x8B', 'М': '\x8C', 'Н': '\x8D', 'О': '\x8E', 'П': '\x8F',
                'Р': '\x90', 'С': '\x91', 'Т': '\x92', 'У': '\x93', 'Ф': '\x94', 'Х': '\x95', 'Ц': '\x96', 'Ч': '\x97',
                'Ш': '\x98', 'Щ': '\x99', 'Ъ': '\x9A', 'Ы': '\x9B', 'Ь': '\x9C', 'Э': '\x9D', 'Ю': '\x9E', 'Я': '\x9F',
                'а': '\xA0', 'б': '\xA1', 'в': '\xA2', 'г': '\xA3', 'д': '\xA4', 'е': '\xA5', 'ж': '\xA6', 'з': '\xA7',
                'и': '\xA8', 'й': '\xA9', 'к': '\xAA', 'л': '\xAB', 'м': '\xAC', 'н': '\xAD', 'о': '\xAE', 'п': '\xAF',
                'р': '\xE0', 'с': '\xE1', 'т': '\xE2', 'у': '\xE3', 'ф': '\xE4', 'х': '\xE5', 'ц': '\xE6', 'ч': '\xE7',
                'ш': '\xE8', 'щ': '\xE9', 'ъ': '\xEA', 'ы': '\xEB', 'ь': '\xEC', 'э': '\xED', 'ю': '\xEE', 'я': '\xEF',
                'Ё': '\xF0', 'ё': '\xF1'
            };
            
            let result = '';
            for (let i = 0; i < str.length; i++) {
                const char = str[i];
                if (utf8ToCp866Map[char]) {
                    result += utf8ToCp866Map[char];
                } else if (char.charCodeAt(0) < 128) {
                    // ASCII символы остаются без изменений
                    result += char;
                } else {
                    // Для остальных символов используем '?' как fallback
                    result += '?';
                }
            }
            return result;
        }
        
        // Функция генерации ESC-POS команд с CP866
        function generateESCPOS(data) {
            let commands = [];
            
            // ESC-POS команды
            const ESC = '\x1B';
            const GS = '\x1D';
            const LF = '\x0A';
            
            // Инициализация принтера
            commands.push(ESC + '@'); // Сброс принтера
            
            // Устанавливаем кодовую таблицу CP866 для кириллицы
            commands.push(ESC + '\x74' + '\x11'); // ESC t 17 = CP866 (Cyrillic)
            
            // КРИТИЧЕСКИ ВАЖНО: Добавляем пустые строки после инициализации
            // Это дает принтеру время полностью обработать команды инициализации
            // перед началом печати текста
            commands.push(LF + LF + LF + LF + LF);
            
            // Клиент
            commands.push(ESC + '!' + '\x08'); // Полужирный
            commands.push(utf8ToCp866(data.customer_name) + LF);
            commands.push(ESC + '!' + '\x00'); // Обычный
            commands.push(LF);
            
            // Содержимое заказа
            commands.push('--------------------------------' + LF);
            data.items.forEach(function(item) {
                let itemLine = utf8ToCp866(item.name);
                if (item.unit) {
                    itemLine += ' (' + utf8ToCp866(item.unit) + ')';
                }
                if (item.quantity > 1) {
                    itemLine += ' x' + item.quantity;
                }
                commands.push(itemLine + LF);
            });
            commands.push('--------------------------------' + LF);
            commands.push(LF);
            
            // Доставка/Самовывоз (только тип, без адреса)
            commands.push(utf8ToCp866(data.shipping_type || (data.shipping_address ? 'Доставка' : 'Самовывоз')) + LF);
            commands.push(LF);
            
            // Стоимость заказа
            commands.push(ESC + '!' + '\x08'); // Полужирный
            const totalText = data.total_formatted || (data.total + ' ' + (data.currency_symbol || 'руб.'));
            commands.push(utf8ToCp866('Сумма: ' + totalText) + LF);
            commands.push(ESC + '!' + '\x00'); // Обычный
            
            // Статус оплаты (без символов галочки/крестика, т.к. они не поддерживаются в CP866)
            if (data.is_paid) {
                commands.push(utf8ToCp866(data.payment_status || 'Оплачено') + LF);
            } else {
                commands.push(utf8ToCp866(data.payment_status || 'Не оплачено') + LF);
            }
            commands.push(LF);
            commands.push(LF);
            commands.push(LF);
            
            // Отрезка бумаги (автоотрез между чеками)
            commands.push(GS + 'V' + '\x41' + '\x03'); // Частичная отрезка
            
            return commands.join('');
        }
        
        // Функция генерации содержимого одного заказа (без инициализации принтера)
        function generateOrderContent(data) {
            let commands = [];
            const ESC = '\x1B';
            const GS = '\x1D';
            const LF = '\x0A';
            
            // Клиент
            commands.push(ESC + '!' + '\x08'); // Полужирный
            commands.push(utf8ToCp866(data.customer_name) + LF);
            commands.push(ESC + '!' + '\x00'); // Обычный
            commands.push(LF);
            
            // Содержимое заказа
            commands.push('--------------------------------' + LF);
            data.items.forEach(function(item) {
                let itemLine = utf8ToCp866(item.name);
                if (item.unit) {
                    itemLine += ' (' + utf8ToCp866(item.unit) + ')';
                }
                if (item.quantity > 1) {
                    itemLine += ' x' + item.quantity;
                }
                commands.push(itemLine + LF);
            });
            commands.push('--------------------------------' + LF);
            commands.push(LF);
            
            // Доставка/Самовывоз (только тип, без адреса)
            commands.push(utf8ToCp866(data.shipping_type || (data.shipping_address ? 'Доставка' : 'Самовывоз')) + LF);
            commands.push(LF);
            
            // Стоимость заказа
            commands.push(ESC + '!' + '\x08'); // Полужирный
            const totalText = data.total_formatted || (data.total + ' ' + (data.currency_symbol || 'руб.'));
            commands.push(utf8ToCp866('Сумма: ' + totalText) + LF);
            commands.push(ESC + '!' + '\x00'); // Обычный
            
            // Статус оплаты (без символов галочки/крестика, т.к. они не поддерживаются в CP866)
            if (data.is_paid) {
                commands.push(utf8ToCp866(data.payment_status || 'Оплачено') + LF);
            } else {
                commands.push(utf8ToCp866(data.payment_status || 'Не оплачено') + LF);
            }
            commands.push(LF);
            commands.push(LF);
            commands.push(LF);
            
            // Отрезка бумаги (автоотрез между чеками)
            commands.push(GS + 'V' + '\x41' + '\x03'); // Частичная отрезка
            
            return commands.join('');
        }
        
        // Функция генерации ESC-POS команд для нескольких заказов подряд
        // УДАЛЕНА - используется версия ниже в коде
        
        // Функция печати через QZ Tray
        async function printViaQZTray() {
            if (!qzConnected) {
                alert('QZ Tray не подключен. Убедитесь, что QZ Tray запущен и доступен.');
                await connectToQZTray();
                if (!qzConnected) {
                    return;
                }
            }
            
            const printBtn = document.getElementById('qz-print-btn');
            printBtn.disabled = true;
            printBtn.textContent = 'Печать...';
            
            try {
                // Генерируем ESC-POS команды с CP866
                const escposData = generateESCPOS(orderData);
                console.log('Generated ESC-POS data length:', escposData.length);
                
                // Печать через QZ Tray как RAW данные
                const config = qz.configs.create(PRINTER_NAME);
                console.log('Printer config:', config);
                
                // Проверяем доступность принтера
                const printers = await qz.printers.find();
                console.log('Available printers:', printers);
                
                if (!printers.includes(PRINTER_NAME)) {
                    throw new Error('Принтер ' + PRINTER_NAME + ' не найден. Доступные принтеры: ' + printers.join(', '));
                }
                
                // Конвертируем строку ESC-POS в массив байтов
                // Важно: CP866 символы уже в строке, нужно правильно извлечь байты
                const bytes = [];
                for (let i = 0; i < escposData.length; i++) {
                    const char = escposData[i];
                    const charCode = char.charCodeAt(0);
                    // Если это однобайтовый символ (0-255), используем его как есть
                    if (charCode < 256) {
                        bytes.push(charCode);
                    } else {
                        // Для многобайтовых символов берем только младший байт
                        bytes.push(charCode & 0xFF);
                    }
                }
                
                // Конвертируем массив байтов в base64
                let binary = '';
                for (let i = 0; i < bytes.length; i++) {
                    binary += String.fromCharCode(bytes[i]);
                }
                const base64Data = btoa(binary);
                
                console.log('Sending RAW print job (base64 length):', base64Data.length);
                
                // Используем RAW печать
                await qz.print(config, [
                    {
                        type: 'raw',
                        format: 'base64',
                        data: base64Data
                    }
                ]).then(function() {
                    console.log('Print job sent successfully');
                    alert('Заказ успешно отправлен на печать!');
                }).catch(function(err) {
                    console.error('QZ Tray print error:', err);
                    console.error('Error details:', JSON.stringify(err, null, 2));
                    throw err;
                });
            } catch (err) {
                console.error('Print error:', err);
                alert('Ошибка при печати: ' + (err.message || err.toString()) + '\n\nПроверьте консоль браузера (F12) для деталей.');
            } finally {
                printBtn.disabled = false;
                printBtn.textContent = '🖨️ Печать через QZ Tray';
            }
        }
        
        // Отключение от QZ Tray при закрытии страницы
        window.addEventListener('beforeunload', function() {
            if (qzConnected && typeof qz !== 'undefined') {
                qz.websocket.disconnect();
            }
        });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Функция декодирования unicode escape последовательностей
function gustolocal_decode_unicode($str) {
    if (!is_string($str)) {
        return $str;
    }
    // Декодируем unicode escape последовательности вида \u0421
    return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($matches) {
        return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
    }, $str);
}

// Функция рекурсивного декодирования массива
function gustolocal_decode_unicode_recursive($data) {
    if (is_string($data)) {
        return gustolocal_decode_unicode($data);
    } elseif (is_array($data)) {
        $decoded = array();
        foreach ($data as $key => $value) {
            $decoded[gustolocal_decode_unicode_recursive($key)] = gustolocal_decode_unicode_recursive($value);
        }
        return $decoded;
    }
    return $data;
}

// Функция для получения данных заказа для печати
function gustolocal_get_order_print_data($order) {
    if (!is_a($order, 'WC_Order')) {
        return array();
    }
    
    // Информация о заказе
    $customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
    if (empty($customer_name)) {
        $customer_name = $order->get_billing_company() ?: 'Гость';
    }
    
    // Товары - парсим JSON payload если есть
    $items = array();
    foreach ($order->get_items() as $item_id => $item) {
        // Проверяем наличие _wmb_payload (JSON с деталями заказа)
        $wmb_payload = $item->get_meta('_wmb_payload', true);
        if (!$wmb_payload) {
            $wmb_payload = $item->get_meta('Meal plan payload', true);
        }
        
        if ($wmb_payload) {
            // Парсим JSON payload
            $payload = json_decode($wmb_payload, true);
            if ($payload && isset($payload['items_list']) && is_array($payload['items_list'])) {
                // Извлекаем товары из items_list
                foreach ($payload['items_list'] as $payload_item) {
                    if (isset($payload_item['qty']) && $payload_item['qty'] > 0) {
                        $item_name = isset($payload_item['name']) ? $payload_item['name'] : 'Неизвестное блюдо';
                        $item_name = gustolocal_decode_unicode($item_name);
                        $item_qty = intval($payload_item['qty']);
                        $item_unit = isset($payload_item['unit']) ? $payload_item['unit'] : '';
                        
                        $items[] = array(
                            'name' => $item_name,
                            'quantity' => $item_qty,
                            'unit' => $item_unit
                        );
                    }
                }
            }
        } else {
            // Обычный товар без payload
            $item_name = $item->get_name();
            $item_name = gustolocal_decode_unicode($item_name);
            
            $items[] = array(
                'name' => $item_name,
                'quantity' => $item->get_quantity(),
                'unit' => ''
            );
        }
    }
    
    // Адрес доставки
    $shipping_address = '';
    if ($order->has_shipping_address()) {
        $address_parts = array();
        if ($order->get_shipping_address_1()) {
            $address_parts[] = $order->get_shipping_address_1();
        }
        if ($order->get_shipping_address_2()) {
            $address_parts[] = $order->get_shipping_address_2();
        }
        if ($order->get_shipping_city()) {
            $address_parts[] = $order->get_shipping_city();
        }
        if ($order->get_shipping_postcode()) {
            $address_parts[] = $order->get_shipping_postcode();
        }
        $shipping_address = implode(', ', $address_parts);
    }
    
    // Проверка оплаты заказа
    $is_paid = $order->is_paid() || $order->get_date_paid() !== null;
    $payment_status = $is_paid ? 'Оплачено' : 'Не оплачено';
    
    // Форматируем сумму без HTML тегов
    $order_total = floatval($order->get_total());
    $currency_symbol_raw = get_woocommerce_currency_symbol();
    // Заменяем HTML entities на обычные символы
    $currency_symbol = html_entity_decode($currency_symbol_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Если символ евро не поддерживается, заменяем на "EUR"
    if ($currency_symbol === '€' || $currency_symbol === '&euro;' || $currency_symbol === '€') {
        $currency_symbol = 'EUR';
    }
    $total_formatted = number_format($order_total, 2, '.', ' ') . ' ' . $currency_symbol;
    
    // Определяем тип доставки (только текст, без адреса)
    $shipping_type = $order->has_shipping_address() ? 'Доставка' : 'Самовывоз';
    
    return array(
        'order_id' => $order->get_id(),
        'order_date' => $order->get_date_created()->date_i18n('d.m.Y H:i'),
        'order_status' => wc_get_order_status_name($order->get_status()),
        'customer_name' => $customer_name,
        'customer_phone' => $order->get_billing_phone(),
        'customer_email' => $order->get_billing_email(),
        'shipping_method' => $order->get_shipping_method() ?: 'Самовывоз',
        'shipping_type' => $shipping_type,
        'shipping_address' => $shipping_address,
        'items' => $items,
        'total' => $order_total,
        'total_formatted' => $total_formatted,
        'currency_symbol' => $currency_symbol,
        'is_paid' => $is_paid,
        'payment_status' => $payment_status,
        'order_note' => $order->get_customer_note()
    );
}

// Добавляем кнопку печати в список заказов (быстрая печать)
add_filter('woocommerce_admin_order_actions', 'gustolocal_add_quick_print_action', 10, 2);
function gustolocal_add_quick_print_action($actions, $order) {
    if (!is_a($order, 'WC_Order')) {
        return $actions;
    }
    
    $print_url = admin_url('admin.php?page=gustolocal-print-order&order_id=' . $order->get_id());
    
    $actions['print'] = array(
        'url' => $print_url,
        'name' => __('Печать', 'gustolocal'),
        'action' => 'print',
    );
    
    return $actions;
}

// Добавляем стили для кнопки печати в списке заказов
add_action('admin_head', 'gustolocal_print_order_admin_styles');
function gustolocal_print_order_admin_styles() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'edit-shop_order') {
        ?>
        <style>
        .wc-action-button.print::after {
            content: "\f464"; /* dashicons-printer */
            font-family: dashicons;
        }
        </style>
        <?php
    }
}

/* ========================================
   РАБОЧЕЕ МЕСТО ДЛЯ СОТРУДНИКА ПЕЧАТИ
   ======================================== */

// Создаем кастомную роль для сотрудника печати
add_action('init', 'gustolocal_create_printer_operator_role');
function gustolocal_create_printer_operator_role() {
    // Проверяем, существует ли роль
    if (!get_role('printer_operator')) {
        // Создаем роль на основе subscriber с доступом к админке
        add_role('printer_operator', 'Оператор печати', array(
            'read' => true,
            'level_0' => true,
        ));
    }
    
    // Обновляем права для существующей роли, чтобы гарантировать доступ к админке
    $role = get_role('printer_operator');
    if ($role) {
        // Минимальные права для доступа к админке
        $role->add_cap('read');
        $role->add_cap('level_0');
    }
}

// Убеждаемся, что пользователи с ролью printer_operator могут войти в админку
add_filter('user_has_cap', 'gustolocal_printer_operator_caps', 10, 4);
function gustolocal_printer_operator_caps($allcaps, $caps, $args, $user) {
    if (isset($user->ID) && in_array('printer_operator', $user->roles)) {
        // Гарантируем доступ к админке
        $allcaps['read'] = true;
        $allcaps['level_0'] = true;
    }
    return $allcaps;
}

// Убираем редирект для printer_operator после входа
add_filter('login_redirect', 'gustolocal_printer_operator_login_redirect', 999, 3);
function gustolocal_printer_operator_login_redirect($redirect_to, $requested_redirect_to, $user) {
    if (isset($user->roles) && in_array('printer_operator', $user->roles)) {
        // Перенаправляем на рабочее место печати
        return admin_url('admin.php?page=gustolocal-printer-workstation');
    }
    return $redirect_to;
}

// Убираем редирект на главную для printer_operator при попытке доступа к админке
add_action('admin_init', 'gustolocal_prevent_admin_redirect_for_printer_operator', 1);
function gustolocal_prevent_admin_redirect_for_printer_operator() {
    $user = wp_get_current_user();
    if (in_array('printer_operator', $user->roles)) {
        // Убираем стандартный редирект subscriber на главную
        remove_action('admin_init', '_maybe_update_core');
        remove_action('admin_init', '_maybe_update_plugins');
        remove_action('admin_init', '_maybe_update_themes');
    }
}

// Убираем редирект на главную страницу для printer_operator
add_filter('show_admin_bar', 'gustolocal_show_admin_bar_for_printer_operator');
function gustolocal_show_admin_bar_for_printer_operator($show) {
    $user = wp_get_current_user();
    if (in_array('printer_operator', $user->roles)) {
        return true; // Показываем админ-бар
    }
    return $show;
}

// Убираем редирект после логина, если пользователь printer_operator
add_action('wp_login', 'gustolocal_printer_operator_wp_login', 999, 2);
function gustolocal_printer_operator_wp_login($user_login, $user) {
    if (in_array('printer_operator', $user->roles)) {
        // Убираем все редиректы на главную
        remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);
    }
}

// Принудительно разрешаем доступ к админке для printer_operator
add_action('admin_page_access_denied', 'gustolocal_allow_printer_operator_admin', 1);
function gustolocal_allow_printer_operator_admin() {
    $user = wp_get_current_user();
    if (in_array('printer_operator', $user->roles)) {
        // Не показываем ошибку доступа
        return;
    }
}

// Добавляем кастомную страницу для рабочего места печати
add_action('admin_menu', 'gustolocal_add_printer_workstation_page');
function gustolocal_add_printer_workstation_page() {
    add_menu_page(
        'Рабочее место печати',
        'Печать',
        'read', // Минимальные права доступа
        'gustolocal-printer-workstation',
        'gustolocal_printer_workstation_page',
        'dashicons-printer',
        30
    );
}

// Страница рабочего места печати
function gustolocal_printer_workstation_page() {
    if (!current_user_can('read')) {
        wp_die(__('У вас нет прав для доступа к этой странице.'));
    }
    
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'orders';
    if (!in_array($active_tab, array('orders', 'dishes', 'breakdown'))) {
        $active_tab = 'orders';
    }
    
    ?>
    <div class="wrap">
        <h1>🖨️ Рабочее место печати</h1>
        
        <nav class="nav-tab-wrapper">
            <a href="?page=gustolocal-printer-workstation&tab=orders" class="nav-tab <?php echo $active_tab === 'orders' ? 'nav-tab-active' : ''; ?>">
                📋 Заказы (ценники)
            </a>
            <a href="?page=gustolocal-printer-workstation&tab=dishes" class="nav-tab <?php echo $active_tab === 'dishes' ? 'nav-tab-active' : ''; ?>">
                🏷️ Блюда (этикетки)
            </a>
            <a href="?page=gustolocal-printer-workstation&tab=breakdown" class="nav-tab <?php echo $active_tab === 'breakdown' ? 'nav-tab-active' : ''; ?>">
                📊 Разбор (чеки)
            </a>
        </nav>
        
        <?php if ($active_tab === 'orders'): ?>
            <?php gustolocal_render_orders_tab(); ?>
        <?php elseif ($active_tab === 'dishes'): ?>
            <?php gustolocal_render_dishes_tab(); ?>
        <?php else: ?>
            <?php gustolocal_render_breakdown_tab(); ?>
        <?php endif; ?>
    </div>
    <?php
}

// Вкладка заказов для печати ценников
function gustolocal_render_orders_tab() {
    // Получаем заказы за последние 7 дней
    $orders_query = array(
        'limit' => 100,
        'orderby' => 'date',
        'order' => 'DESC',
        'date_created' => date('Y-m-d', strtotime('-7 days')) . '...' . date('Y-m-d'),
        'status' => array('processing', 'on-hold', 'completed')
    );
    
    $orders = wc_get_orders($orders_query);
    
    ?>
    <div class="printer-workstation-orders">
        <div style="margin: 20px 0;">
            <button id="print-selected-orders" class="button button-primary button-large" style="background: #28a745; border-color: #28a745; font-size: 16px; padding: 10px 20px; height: auto;">
                🖨️ Печать выбранных заказов
            </button>
            <span id="selected-count" style="margin-left: 15px; font-weight: bold; color: #28a745;"></span>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="select-all-orders">
                    </th>
                    <th>№ заказа</th>
                    <th>Дата</th>
                    <th>Клиент</th>
                    <th>Сумма</th>
                    <th>Доставка</th>
                    <th>Статус</th>
                    <th style="width: 100px;">Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">
                            Заказы не найдены
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): 
                        $customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
                        if (empty($customer_name)) {
                            $customer_name = $order->get_billing_company() ?: 'Гость';
                        }
                        $shipping_method = $order->get_shipping_method() ?: 'Самовывоз';
                        $order_total = $order->get_total();
                        $currency_symbol = get_woocommerce_currency_symbol();
                    ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="order-checkbox" value="<?php echo esc_attr($order->get_id()); ?>">
                            </td>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $order->get_id() . '&action=edit')); ?>" target="_blank" style="text-decoration: none;">
                                        #<?php echo esc_html($order->get_id()); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html($order->get_date_created()->date_i18n('d.m.Y H:i')); ?></td>
                            <td><?php echo esc_html($customer_name); ?></td>
                            <td><?php echo esc_html(number_format($order_total, 2, '.', ' ') . ' ' . $currency_symbol); ?></td>
                            <td><?php echo esc_html($shipping_method); ?></td>
                            <td><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('post.php?post=' . $order->get_id() . '&action=edit')); ?>" target="_blank" class="button button-small">
                                    Открыть
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Выделить все
        $('#select-all-orders').on('change', function() {
            $('.order-checkbox').prop('checked', this.checked);
            updateSelectedCount();
        });
        
        // Обновление счетчика выбранных
        $('.order-checkbox').on('change', function() {
            updateSelectedCount();
            $('#select-all-orders').prop('checked', $('.order-checkbox:checked').length === $('.order-checkbox').length);
        });
        
        function updateSelectedCount() {
            const count = $('.order-checkbox:checked').length;
            $('#selected-count').text(count > 0 ? 'Выбрано: ' + count : '');
        }
        
        // Печать выбранных заказов
        $('#print-selected-orders').on('click', function() {
            const selected = $('.order-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selected.length === 0) {
                alert('Выберите хотя бы один заказ для печати');
                return;
            }
            
            // Открываем страницу массовой печати с ID заказов
            const printUrl = '<?php echo admin_url('admin.php?page=gustolocal-print-multiple-orders'); ?>' + 
                '&order_ids=' + selected.join(',');
            window.open(printUrl, '_blank');
        });
        
        updateSelectedCount();
    });
    </script>
    
    <style>
    .printer-workstation-orders {
        margin-top: 20px;
    }
    .printer-workstation-orders table {
        margin-top: 20px;
    }
    #print-selected-orders:hover {
        background: #218838 !important;
        border-color: #218838 !important;
    }
    </style>
    <?php
}

// Вкладка блюд для печати этикеток
function gustolocal_render_dishes_tab() {
    // Получаем все активные блюда
    $dishes = get_posts(array(
        'post_type' => 'wmb_dish',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'wmb_active',
                'value' => '1',
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    
    ?>
    <div class="printer-workstation-dishes">
        <div style="margin: 20px 0;">
            <p style="font-size: 14px; color: #666;">
                Выберите блюдо и укажите количество этикеток для печати (80x50 мм)
            </p>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 200px;">Блюдо</th>
                    <th>Состав</th>
                    <th>Срок хранения</th>
                    <th style="width: 150px;">Количество этикеток</th>
                    <th style="width: 120px;">Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dishes)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">
                            Блюда не найдены
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dishes as $dish): 
                        $ingredients = get_post_meta($dish->ID, 'wmb_ingredients', true);
                        $shelf_life = get_post_meta($dish->ID, 'wmb_shelf_life', true);
                        $allergens = get_post_meta($dish->ID, 'wmb_allergens', true);
                        $nutrition = get_post_meta($dish->ID, 'wmb_nutrition', true);
                    ?>
                        <tr data-dish-id="<?php echo esc_attr($dish->ID); ?>">
                            <td><strong><?php echo esc_html($dish->post_title); ?></strong></td>
                            <td><?php echo esc_html($ingredients ?: '—'); ?></td>
                            <td><?php echo esc_html($shelf_life ?: '—'); ?></td>
                            <td>
                                <input type="number" 
                                       class="dish-label-quantity" 
                                       min="1" 
                                       max="100" 
                                       value="1" 
                                       style="width: 80px;">
                            </td>
                            <td>
                                <button class="button button-primary print-dish-labels" 
                                        data-dish-id="<?php echo esc_attr($dish->ID); ?>"
                                        data-dish-name="<?php echo esc_attr($dish->post_title); ?>"
                                        data-ingredients="<?php echo esc_attr($ingredients); ?>"
                                        data-shelf-life="<?php echo esc_attr($shelf_life); ?>"
                                        data-allergens="<?php echo esc_attr($allergens); ?>"
                                        data-nutrition="<?php echo esc_attr($nutrition); ?>">
                                    🖨️ Печать
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.print-dish-labels').on('click', function() {
            const dishId = $(this).data('dish-id');
            const dishName = $(this).data('dish-name');
            const ingredients = $(this).data('ingredients');
            const shelfLife = $(this).data('shelf-life');
            const allergens = $(this).data('allergens');
            const nutrition = $(this).data('nutrition');
            const quantity = $(this).closest('tr').find('.dish-label-quantity').val();
            
            if (!quantity || quantity < 1) {
                alert('Укажите количество этикеток');
                return;
            }
            
            // Открываем страницу печати этикеток
            const printUrl = '<?php echo admin_url('admin.php?page=gustolocal-print-label'); ?>' + 
                '&dish_id=' + dishId + 
                '&quantity=' + quantity +
                '&dish_name=' + encodeURIComponent(dishName) +
                '&ingredients=' + encodeURIComponent(ingredients || '') +
                '&shelf_life=' + encodeURIComponent(shelfLife || '') +
                '&allergens=' + encodeURIComponent(allergens || '') +
                '&nutrition=' + encodeURIComponent(nutrition || '');
            
            window.open(printUrl, '_blank');
        });
    });
    </script>
    <?php
}

// Вкладка разбора заказов для печати на кухню
function gustolocal_render_breakdown_tab() {
    // Проверяем, что WooCommerce активен
    if (!function_exists('wc_get_orders')) {
        echo '<div class="wrap"><h1>Разбор заказов</h1><div class="error"><p>WooCommerce не активирован!</p></div></div>';
        return;
    }
    
    $selected_orders = isset($_POST['order_ids']) && is_array($_POST['order_ids']) 
        ? array_map('intval', $_POST['order_ids']) 
        : array();
    
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : date('Y-m-d', strtotime('-7 days'));
    $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : date('Y-m-d');
    $status_filter = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    
    // Получаем заказы для выбора
    $orders_query = array(
        'limit' => 500,
        'orderby' => 'date',
        'order' => 'DESC',
        'date_created' => $date_from . '...' . $date_to,
    );
    
    if ($status_filter) {
        $orders_query['status'] = $status_filter;
    }
    
    $all_orders = wc_get_orders($orders_query);
    
    // Если выбраны заказы, формируем сводку
    $breakdown_data = null;
    if (!empty($selected_orders)) {
        $breakdown_data = gustolocal_generate_breakdown($selected_orders);
    }
    
    ?>
    <div class="printer-workstation-breakdown">
        <form method="post" action="" id="breakdown-form">
            <div class="postbox" style="margin-top: 20px; padding: 20px;">
                <h2>Фильтры</h2>
                <table class="form-table">
                    <tr>
                        <th><label for="date_from">Дата от:</label></th>
                        <td><input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="date_to">Дата до:</label></th>
                        <td><input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="status">Статус:</label></th>
                        <td>
                            <select id="status" name="status" class="regular-text">
                                <option value="">Все статусы</option>
                                <?php
                                $statuses = wc_get_order_statuses();
                                foreach ($statuses as $status_key => $status_label) {
                                    $selected = ($status_filter === $status_key) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($status_key) . '" ' . $selected . '>' . esc_html($status_label) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="filter_orders" class="button button-primary" value="Применить фильтры">
                </p>
            </div>
            
            <div class="postbox" style="margin-top: 20px; padding: 20px;">
                <h2>Выберите заказы</h2>
                <p>
                    <button type="button" class="button" onclick="selectAllOrdersBreakdown()">Выбрать все</button>
                    <button type="button" class="button" onclick="deselectAllOrdersBreakdown()">Снять выбор</button>
                </p>
                
                <?php if (empty($all_orders)): ?>
                    <p>Заказы не найдены.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 30px;"><input type="checkbox" id="select-all-checkbox-breakdown" onclick="toggleAllOrdersBreakdown(this)"></th>
                                <th>№ заказа</th>
                                <th>Дата</th>
                                <th>Клиент</th>
                                <th>Статус</th>
                                <th>Способ получения</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_orders as $order): 
                                $is_selected = in_array($order->get_id(), $selected_orders);
                                $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                                if (trim($customer_name) === '') {
                                    $customer_name = $order->get_billing_company() ?: 'Гость';
                                }
                                $is_pickup = gustolocal_is_pickup_order($order);
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="order_ids[]" 
                                               value="<?php echo esc_attr($order->get_id()); ?>"
                                               <?php echo $is_selected ? 'checked' : ''; ?>>
                                    </td>
                                    <td><strong>#<?php echo esc_html($order->get_id()); ?></strong></td>
                                    <td><?php echo esc_html($order->get_date_created()->date_i18n('d.m.Y H:i')); ?></td>
                                    <td><?php echo esc_html($customer_name); ?></td>
                                    <td><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></td>
                                    <td><?php echo $is_pickup ? '<strong>Самовывоз</strong>' : 'Доставка'; ?></td>
                                    <td><?php echo $order->get_formatted_order_total(); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <p class="submit" style="margin-top: 20px;">
                        <input type="submit" name="generate_breakdown" class="button button-primary button-large" value="Сформировать сводку">
                    </p>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if ($breakdown_data): ?>
            <div class="postbox" style="margin-top: 20px; padding: 20px;">
                <h2>Сводная таблица для кухни</h2>
                <div style="margin: 20px 0;">
                    <button id="print-breakdown-btn" class="button button-primary button-large" style="background: #28a745; border-color: #28a745; font-size: 16px; padding: 10px 20px; height: auto;">
                        🖨️ Печать на кухню (80 мм)
                    </button>
                </div>
                <?php gustolocal_display_breakdown_table_print($breakdown_data); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
    function toggleAllOrdersBreakdown(checkbox) {
        var checkboxes = document.querySelectorAll('#breakdown-form input[name="order_ids[]"]');
        checkboxes.forEach(function(cb) {
            cb.checked = checkbox.checked;
        });
    }
    
    function selectAllOrdersBreakdown() {
        var checkboxes = document.querySelectorAll('#breakdown-form input[name="order_ids[]"]');
        checkboxes.forEach(function(cb) {
            cb.checked = true;
        });
        document.getElementById('select-all-checkbox-breakdown').checked = true;
    }
    
    function deselectAllOrdersBreakdown() {
        var checkboxes = document.querySelectorAll('#breakdown-form input[name="order_ids[]"]');
        checkboxes.forEach(function(cb) {
            cb.checked = false;
        });
        document.getElementById('select-all-checkbox-breakdown').checked = false;
    }
    
    jQuery(document).ready(function($) {
        $('#print-breakdown-btn').on('click', function() {
            const orderIds = <?php echo json_encode($breakdown_data['order_ids']); ?>;
            if (!orderIds || orderIds.length === 0) {
                alert('Нет заказов для печати');
                return;
            }
            
            const printUrl = '<?php echo admin_url('admin.php?page=gustolocal-print-breakdown'); ?>' + 
                '&order_ids=' + orderIds.join(',');
            window.open(printUrl, '_blank');
        });
    });
    </script>
    <?php
}

// Добавляем страницу печати этикеток
add_action('admin_menu', 'gustolocal_add_print_label_page');
function gustolocal_add_print_label_page() {
    add_submenu_page(
        null, // Скрытая страница
        'Печать этикетки',
        'Печать этикетки',
        'read',
        'gustolocal-print-label',
        'gustolocal_print_label_page'
    );
}

// Страница печати этикетки
function gustolocal_print_label_page() {
    if (!current_user_can('read')) {
        wp_die(__('У вас нет прав для доступа к этой странице.'));
    }
    
    $dish_id = isset($_GET['dish_id']) ? intval($_GET['dish_id']) : 0;
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
    $dish_name = isset($_GET['dish_name']) ? sanitize_text_field($_GET['dish_name']) : '';
    $ingredients = isset($_GET['ingredients']) ? sanitize_textarea_field($_GET['ingredients']) : '';
    $shelf_life = isset($_GET['shelf_life']) ? sanitize_text_field($_GET['shelf_life']) : '';
    $allergens = isset($_GET['allergens']) ? sanitize_text_field($_GET['allergens']) : '';
    $nutrition = isset($_GET['nutrition']) ? sanitize_text_field($_GET['nutrition']) : '';
    
    if (!$dish_id || !$dish_name) {
        echo '<div class="wrap"><h1>Ошибка</h1><p>Не указаны данные блюда.</p></div>';
        return;
    }
    
    // Рассчитываем срок годности от текущей даты
    $expiry_date = '';
    $print_date = current_time('d.m.Y');
    if ($shelf_life) {
        // Парсим срок хранения (например, "2-3 дня", "до 2х дней", "2 дня", "3 суток")
        // Ищем максимальное число в строке
        preg_match_all('/(\d+)/', $shelf_life, $matches);
        if (!empty($matches[1])) {
            // Берем максимальное значение (если указан диапазон "2-3 дня", берем 3)
            $days = max(array_map('intval', $matches[1]));
            if ($days > 0) {
                $expiry_date = date('d.m.Y', strtotime('+' . $days . ' days'));
            }
        }
    }
    
    // Подготавливаем данные для печати
    $label_data = array(
        'dish_name' => $dish_name,
        'ingredients' => $ingredients,
        'shelf_life' => $shelf_life,
        'expiry_date' => $expiry_date,
        'print_date' => $print_date,
        'allergens' => $allergens,
        'nutrition' => $nutrition,
        'quantity' => $quantity
    );
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Печать этикеток - <?php echo esc_html($dish_name); ?></title>
        <!-- QZ Tray JS Library -->
        <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
        <style>
            @media print {
                body { margin: 0; padding: 0; }
                .no-print { display: none !important; }
                @page { margin: 0; size: 80mm 50mm; }
            }
            body {
                font-family: Arial, sans-serif;
                font-size: 10px;
                margin: 0;
                padding: 10px;
            }
            .label-preview {
                width: 80mm;
                height: 50mm;
                border: 1px solid #000;
                padding: 5mm;
                box-sizing: border-box;
                margin: 0 auto;
            }
            .label-title {
                font-weight: bold;
                font-size: 12px;
                margin-bottom: 3px;
                text-align: center;
            }
            .label-content {
                font-size: 9px;
                line-height: 1.3;
            }
            .label-expiry {
                font-weight: bold;
                margin-top: 3px;
                text-align: center;
                font-size: 10px;
            }
            .no-print {
                text-align: center;
                margin: 20px 0;
            }
            .print-btn {
                background: #0073aa;
                color: white;
                padding: 10px 20px;
                border: none;
                cursor: pointer;
                font-size: 16px;
                border-radius: 3px;
                margin: 5px;
            }
            .print-btn:hover {
                background: #005a87;
            }
            .qz-status {
                margin: 10px 0;
                padding: 8px;
                border-radius: 4px;
                font-size: 12px;
            }
            .qz-status.connected {
                background: #d4edda;
                color: #155724;
            }
            .qz-status.disconnected {
                background: #f8d7da;
                color: #721c24;
            }
        </style>
    </head>
    <body>
        <div class="no-print">
            <div id="qz-status" class="qz-status disconnected">QZ Tray: Не подключен</div>
            <button class="print-btn" id="qz-print-btn" onclick="printViaQZTray()">🖨️ Печать через QZ Tray (<?php echo $quantity; ?> шт.)</button>
            <button class="print-btn" onclick="window.print()">🖨️ Печать (браузер)</button>
            <button class="print-btn" onclick="window.close()" style="background: #666;">Закрыть</button>
        </div>
        
        <?php for ($i = 0; $i < $quantity; $i++): ?>
        <div class="label-preview" style="<?php echo $i > 0 ? 'page-break-before: always;' : ''; ?>">
            <div class="label-title"><?php echo esc_html($dish_name); ?></div>
            <?php if ($ingredients): ?>
            <div class="label-content"><strong>Состав:</strong> <?php echo esc_html($ingredients); ?></div>
            <?php endif; ?>
            <?php if ($allergens): ?>
            <div class="label-content"><strong>Аллергены:</strong> <?php echo esc_html($allergens); ?></div>
            <?php endif; ?>
            <?php if ($nutrition): ?>
            <div class="label-content"><strong>КБЖУ:</strong> <?php echo esc_html($nutrition); ?></div>
            <?php endif; ?>
            <?php if ($expiry_date): ?>
            <div class="label-expiry">Годен до: <?php echo esc_html($expiry_date); ?></div>
            <?php elseif ($shelf_life): ?>
            <div class="label-content"><strong>Срок хранения:</strong> <?php echo esc_html($shelf_life); ?></div>
            <?php endif; ?>
        </div>
        <?php endfor; ?>
        
        <script>
        const labelData = <?php echo json_encode($label_data, JSON_UNESCAPED_UNICODE); ?>;
        const PRINTER_NAME = 'Printer POS-80';
        let qzConnected = false;
        
        // Подключение к QZ Tray
        window.addEventListener('load', function() {
            connectToQZTray();
        });
        
        async function connectToQZTray() {
            const statusEl = document.getElementById('qz-status');
            const printBtn = document.getElementById('qz-print-btn');
            
            try {
                statusEl.textContent = 'QZ Tray: Подключение...';
                statusEl.className = 'qz-status connecting';
                
                if (typeof qz === 'undefined') {
                    throw new Error('QZ Tray библиотека не загружена');
                }
                
                await qz.websocket.connect().then(function() {
                    qzConnected = true;
                    statusEl.textContent = 'QZ Tray: Подключен ✓';
                    statusEl.className = 'qz-status connected';
                    printBtn.disabled = false;
                }).catch(function(err) {
                    throw err;
                });
            } catch (err) {
                qzConnected = false;
                statusEl.textContent = 'QZ Tray: Не подключен';
                statusEl.className = 'qz-status disconnected';
                printBtn.disabled = true;
                console.error('QZ Tray connection error:', err);
            }
        }
        
        // Функция конвертации UTF-8 в CP866 (DOS Cyrillic) для ESC-POS
        function utf8ToCp866(str) {
            if (!str) return str;
            
            // Таблица соответствия основных кириллических символов UTF-8 -> CP866
            const utf8ToCp866Map = {
                'А': '\x80', 'Б': '\x81', 'В': '\x82', 'Г': '\x83', 'Д': '\x84', 'Е': '\x85', 'Ж': '\x86', 'З': '\x87',
                'И': '\x88', 'Й': '\x89', 'К': '\x8A', 'Л': '\x8B', 'М': '\x8C', 'Н': '\x8D', 'О': '\x8E', 'П': '\x8F',
                'Р': '\x90', 'С': '\x91', 'Т': '\x92', 'У': '\x93', 'Ф': '\x94', 'Х': '\x95', 'Ц': '\x96', 'Ч': '\x97',
                'Ш': '\x98', 'Щ': '\x99', 'Ъ': '\x9A', 'Ы': '\x9B', 'Ь': '\x9C', 'Э': '\x9D', 'Ю': '\x9E', 'Я': '\x9F',
                'а': '\xA0', 'б': '\xA1', 'в': '\xA2', 'г': '\xA3', 'д': '\xA4', 'е': '\xA5', 'ж': '\xA6', 'з': '\xA7',
                'и': '\xA8', 'й': '\xA9', 'к': '\xAA', 'л': '\xAB', 'м': '\xAC', 'н': '\xAD', 'о': '\xAE', 'п': '\xAF',
                'р': '\xE0', 'с': '\xE1', 'т': '\xE2', 'у': '\xE3', 'ф': '\xE4', 'х': '\xE5', 'ц': '\xE6', 'ч': '\xE7',
                'ш': '\xE8', 'щ': '\xE9', 'ъ': '\xEA', 'ы': '\xEB', 'ь': '\xEC', 'э': '\xED', 'ю': '\xEE', 'я': '\xEF',
                'Ё': '\xF0', 'ё': '\xF1'
            };
            
            let result = '';
            for (let i = 0; i < str.length; i++) {
                const char = str[i];
                if (utf8ToCp866Map[char]) {
                    result += utf8ToCp866Map[char];
                } else if (char.charCodeAt(0) < 128) {
                    // ASCII символы остаются без изменений
                    result += char;
                } else {
                    // Для остальных символов используем '?' как fallback
                    result += '?';
                }
            }
            return result;
        }
        
        // Функция генерации ESC-POS команд для этикетки 80x50 мм
        function generateLabelESCPOS(data) {
            let commands = [];
            const ESC = '\x1B';
            const GS = '\x1D';
            const LF = '\x0A';
            
            // Инициализация принтера
            commands.push(ESC + '@'); // Сброс принтера
            commands.push(ESC + '\x74' + '\x11'); // ESC t 17 = CP866 (Cyrillic)
            
            // Настройка для этикетки 80x50 мм
            // Устанавливаем минимальный левый отступ (2 мм = ~16 точек)
            commands.push(GS + 'L' + '\x10' + '\x00'); // Левый отступ 16 точек (~2 мм)
            
            // Устанавливаем компактный межстрочный интервал
            commands.push(ESC + '\x33' + '\x10'); // Межстрочный интервал 16 точек (компактнее)
            
            // Минимальные пустые строки для обработки инициализации
            commands.push(LF + LF);
            
            // Название блюда (полужирный, без переноса строк)
            commands.push(ESC + '!' + '\x08'); // Полужирный
            // Устанавливаем режим без автоматического переноса (если поддерживается)
            // ESC ! 0x00 - обычный шрифт, но мы уже установили полужирный
            commands.push(utf8ToCp866(data.dish_name) + LF);
            commands.push(ESC + '!' + '\x00'); // Обычный
            commands.push(LF);
            
            // Разделитель
            commands.push('--------------------------------' + LF);
            commands.push(LF);
            
            // Состав
            if (data.ingredients) {
                commands.push(utf8ToCp866('Состав: ') + LF);
                // Для этикетки 80мм используем более длинные строки (до 48 символов)
                const ingredients = data.ingredients;
                const maxLineLength = 48; // Максимальная длина строки для этикетки 80мм
                let currentLine = '';
                const words = ingredients.split(/[,\s]+/);
                for (let w = 0; w < words.length; w++) {
                    const word = words[w].trim();
                    if (!word) continue;
                    if (currentLine.length + word.length + 1 <= maxLineLength) {
                        currentLine += (currentLine ? ', ' : '') + word;
                    } else {
                        if (currentLine) {
                            commands.push(utf8ToCp866(currentLine) + LF);
                        }
                        currentLine = word;
                    }
                }
                if (currentLine) {
                    commands.push(utf8ToCp866(currentLine) + LF);
                }
                commands.push(LF);
            }
            
            // Аллергены
            if (data.allergens) {
                commands.push(utf8ToCp866('Аллергены: ' + data.allergens) + LF);
                commands.push(LF);
            }
            
            // КБЖУ
            if (data.nutrition) {
                commands.push(utf8ToCp866('КБЖУ: ' + data.nutrition) + LF);
                commands.push(LF);
            }
            
            // Разделитель
            commands.push('--------------------------------' + LF);
            commands.push(LF);
            
            // Срок годности (полужирный, по левому краю)
            commands.push(ESC + '!' + '\x08'); // Полужирный
            if (data.expiry_date) {
                commands.push(utf8ToCp866('Годен до: ' + data.expiry_date) + LF);
            } else if (data.shelf_life) {
                commands.push(utf8ToCp866('Срок хранения: ' + data.shelf_life) + LF);
            }
            commands.push(ESC + '!' + '\x00'); // Обычный
            commands.push(LF);
            
            // Отрезка бумаги
            commands.push(GS + 'V' + '\x41' + '\x03'); // Частичная отрезка
            
            return commands.join('');
        }
        
        // Функция печати этикеток через QZ Tray
        async function printViaQZTray() {
            if (!qzConnected) {
                alert('QZ Tray не подключен');
                await connectToQZTray();
                if (!qzConnected) return;
            }
            
            const printBtn = document.getElementById('qz-print-btn');
            printBtn.disabled = true;
            printBtn.textContent = 'Печать...';
            
            try {
                const config = qz.configs.create(PRINTER_NAME);
                const printers = await qz.printers.find();
                
                if (!printers.includes(PRINTER_NAME)) {
                    throw new Error('Принтер "' + PRINTER_NAME + '" не найден. Доступные принтеры: ' + printers.join(', '));
                }
                
                console.log('Начало печати этикеток, количество:', labelData.quantity);
                
                // Печатаем каждую этикетку отдельно через RAW/ESC-POS
                for (let i = 0; i < labelData.quantity; i++) {
                    console.log('Печать этикетки', i + 1, 'из', labelData.quantity);
                    
                    // Генерируем ESC-POS команды для этикетки
                    const escposData = generateLabelESCPOS(labelData);
                    
                    // Конвертируем в байты
                    const bytes = [];
                    for (let j = 0; j < escposData.length; j++) {
                        const char = escposData[j];
                        const charCode = char.charCodeAt(0);
                        if (charCode < 256) {
                            bytes.push(charCode);
                        } else {
                            bytes.push(charCode & 0xFF);
                        }
                    }
                    
                    // Конвертируем в base64
                    let binary = '';
                    for (let j = 0; j < bytes.length; j++) {
                        binary += String.fromCharCode(bytes[j]);
                    }
                    const base64Data = btoa(binary);
                    
                    console.log('Отправка этикетки', i + 1, 'на печать (base64 длина:', base64Data.length, ')');
                    
                    try {
                        await qz.print(config, [{
                            type: 'raw',
                            format: 'base64',
                            data: base64Data
                        }]);
                        console.log('Этикетка', i + 1, 'отправлена на печать успешно');
                    } catch (printErr) {
                        console.error('Ошибка при печати этикетки', i + 1, ':', printErr);
                        console.error('Детали ошибки:', printErr.message);
                        throw printErr;
                    }
                    
                    // Небольшая задержка между этикетками
                    if (i < labelData.quantity - 1) {
                        await new Promise(resolve => setTimeout(resolve, 300));
                    }
                }
                
                alert('Этикетки успешно отправлены на печать! (' + labelData.quantity + ' шт.)');
                setTimeout(function() {
                    window.close();
                }, 1000);
            } catch (err) {
                console.error('Print error:', err);
                alert('Ошибка при печати: ' + err.message);
            } finally {
                printBtn.disabled = false;
                printBtn.textContent = '🖨️ Печать через QZ Tray (' + labelData.quantity + ' шт.)';
            }
        }
        
        window.addEventListener('beforeunload', function() {
            if (qzConnected && typeof qz !== 'undefined') {
                qz.websocket.disconnect();
            }
        });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Скрываем лишние элементы админки для роли printer_operator
add_action('admin_menu', 'gustolocal_hide_admin_menu_for_printer_operator', 999);
function gustolocal_hide_admin_menu_for_printer_operator() {
    $user = wp_get_current_user();
    if (in_array('printer_operator', $user->roles)) {
        // Оставляем только нужные пункты меню
        remove_menu_page('index.php'); // Dashboard
        remove_menu_page('edit.php'); // Posts
        remove_menu_page('upload.php'); // Media
        remove_menu_page('edit.php?post_type=page'); // Pages
        remove_menu_page('edit-comments.php'); // Comments
        remove_menu_page('themes.php'); // Appearance
        remove_menu_page('plugins.php'); // Plugins
        remove_menu_page('users.php'); // Users
        remove_menu_page('tools.php'); // Tools
        remove_menu_page('options-general.php'); // Settings
        
        // Скрываем все подменю WooCommerce кроме заказов
        remove_submenu_page('woocommerce', 'wc-admin');
        remove_submenu_page('woocommerce', 'wc-settings');
        remove_submenu_page('woocommerce', 'wc-addons');
        remove_submenu_page('woocommerce', 'wc-status');
        remove_submenu_page('woocommerce', 'wc-reports');
        // Оставляем только заказы
    }
}

// Автопечать при открытии страницы печати заказа (если auto_print=1)
add_action('admin_init', 'gustolocal_auto_print_order');
function gustolocal_auto_print_order() {
    if (isset($_GET['page']) && $_GET['page'] === 'gustolocal-print-order' && isset($_GET['auto_print']) && $_GET['auto_print'] === '1') {
        add_action('admin_footer', function() {
            ?>
            <script>
            window.addEventListener('load', function() {
                setTimeout(function() {
                    const printBtn = document.getElementById('qz-print-btn');
                    if (printBtn && !printBtn.disabled) {
                        printBtn.click();
                    }
                }, 1000);
            });
            </script>
            <?php
        });
    }
}

// Добавляем страницу массовой печати заказов
add_action('admin_menu', 'gustolocal_add_print_multiple_orders_page');
function gustolocal_add_print_multiple_orders_page() {
    add_submenu_page(
        null,
        'Печать нескольких заказов',
        'Печать нескольких заказов',
        'read',
        'gustolocal-print-multiple-orders',
        'gustolocal_print_multiple_orders_page'
    );
}

// Страница массовой печати заказов
function gustolocal_print_multiple_orders_page() {
    if (!current_user_can('read')) {
        wp_die(__('У вас нет прав для доступа к этой странице.'));
    }
    
    $order_ids_str = isset($_GET['order_ids']) ? sanitize_text_field($_GET['order_ids']) : '';
    if (empty($order_ids_str)) {
        echo '<div class="wrap"><h1>Ошибка</h1><p>Не указаны ID заказов.</p></div>';
        return;
    }
    
    $order_ids = array_map('intval', explode(',', $order_ids_str));
    $orders_data = array();
    
    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $orders_data[] = gustolocal_get_order_print_data($order);
        }
    }
    
    if (empty($orders_data)) {
        echo '<div class="wrap"><h1>Ошибка</h1><p>Заказы не найдены.</p></div>';
        return;
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Печать заказов</title>
        <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
    </head>
    <body>
        <div style="text-align: center; margin: 20px;">
            <div id="qz-status" style="margin: 10px 0; padding: 8px; border-radius: 4px; font-size: 12px; background: #f8d7da; color: #721c24;">
                QZ Tray: Не подключен
            </div>
            <button id="qz-print-btn" onclick="printMultipleOrders()" style="background: #28a745; color: white; padding: 15px 30px; border: none; cursor: pointer; font-size: 18px; border-radius: 5px; font-weight: bold;">
                🖨️ Печать всех заказов (<?php echo count($orders_data); ?> шт.)
            </button>
        </div>
        
        <script>
        const ordersData = <?php echo json_encode($orders_data, JSON_UNESCAPED_UNICODE); ?>;
        const PRINTER_NAME = 'Printer POS-80';
        let qzConnected = false;
        
        // Подключение к QZ Tray
        window.addEventListener('load', function() {
            connectToQZTray();
        });
        
        async function connectToQZTray() {
            const statusEl = document.getElementById('qz-status');
            const printBtn = document.getElementById('qz-print-btn');
            
            try {
                if (typeof qz === 'undefined') {
                    throw new Error('QZ Tray библиотека не загружена');
                }
                
                await qz.websocket.connect().then(function() {
                    qzConnected = true;
                    statusEl.textContent = 'QZ Tray: Подключен ✓';
                    statusEl.style.background = '#d4edda';
                    statusEl.style.color = '#155724';
                    printBtn.disabled = false;
                });
            } catch (err) {
                qzConnected = false;
                statusEl.textContent = 'QZ Tray: Не подключен';
                printBtn.disabled = true;
            }
        }
        
        // Функции конвертации UTF-8 в CP866 (скопированы из основного файла)
        function utf8ToCp866(str) {
            if (!str) return str;
            const utf8ToCp866Map = {
                'А': '\x80', 'Б': '\x81', 'В': '\x82', 'Г': '\x83', 'Д': '\x84', 'Е': '\x85', 'Ж': '\x86', 'З': '\x87',
                'И': '\x88', 'Й': '\x89', 'К': '\x8A', 'Л': '\x8B', 'М': '\x8C', 'Н': '\x8D', 'О': '\x8E', 'П': '\x8F',
                'Р': '\x90', 'С': '\x91', 'Т': '\x92', 'У': '\x93', 'Ф': '\x94', 'Х': '\x95', 'Ц': '\x96', 'Ч': '\x97',
                'Ш': '\x98', 'Щ': '\x99', 'Ъ': '\x9A', 'Ы': '\x9B', 'Ь': '\x9C', 'Э': '\x9D', 'Ю': '\x9E', 'Я': '\x9F',
                'а': '\xA0', 'б': '\xA1', 'в': '\xA2', 'г': '\xA3', 'д': '\xA4', 'е': '\xA5', 'ж': '\xA6', 'з': '\xA7',
                'и': '\xA8', 'й': '\xA9', 'к': '\xAA', 'л': '\xAB', 'м': '\xAC', 'н': '\xAD', 'о': '\xAE', 'п': '\xAF',
                'р': '\xE0', 'с': '\xE1', 'т': '\xE2', 'у': '\xE3', 'ф': '\xE4', 'х': '\xE5', 'ц': '\xE6', 'ч': '\xE7',
                'ш': '\xE8', 'щ': '\xE9', 'ъ': '\xEA', 'ы': '\xEB', 'ь': '\xEC', 'э': '\xED', 'ю': '\xEE', 'я': '\xEF',
                'Ё': '\xF0', 'ё': '\xF1'
            };
            let result = '';
            for (let i = 0; i < str.length; i++) {
                const char = str[i];
                if (utf8ToCp866Map[char]) {
                    result += utf8ToCp866Map[char];
                } else if (char.charCodeAt(0) < 128) {
                    result += char;
                } else {
                    result += '?';
                }
            }
            return result;
        }
        
        function generateESCPOS(data) {
            let commands = [];
            const ESC = '\x1B';
            const GS = '\x1D';
            const LF = '\x0A';
            
            // Инициализация принтера
            commands.push(ESC + '@'); // Сброс принтера
            commands.push(ESC + '\x74' + '\x11'); // ESC t 17 = CP866 (Cyrillic)
            
            // КРИТИЧЕСКИ ВАЖНО: Много пустых строк после инициализации
            // Принтер должен полностью обработать команды инициализации перед началом печати
            // Увеличиваем до 10 пустых строк для надежности
            commands.push(LF + LF + LF + LF + LF + LF + LF + LF + LF + LF);
            
            // Клиент
            commands.push(ESC + '!' + '\x08'); // Полужирный
            commands.push(utf8ToCp866(data.customer_name) + LF);
            commands.push(ESC + '!' + '\x00'); // Обычный
            commands.push(LF);
            
            commands.push('--------------------------------' + LF);
            data.items.forEach(function(item) {
                let itemLine = utf8ToCp866(item.name);
                if (item.unit) {
                    itemLine += ' (' + utf8ToCp866(item.unit) + ')';
                }
                if (item.quantity > 1) {
                    itemLine += ' x' + item.quantity;
                }
                commands.push(itemLine + LF);
            });
            commands.push('--------------------------------' + LF);
            commands.push(LF);
            
            // Доставка/Самовывоз (только тип, без адреса)
            commands.push(utf8ToCp866(data.shipping_type || (data.shipping_address ? 'Доставка' : 'Самовывоз')) + LF);
            commands.push(LF);
            
            // Стоимость заказа
            commands.push(ESC + '!' + '\x08'); // Полужирный
            const totalText = data.total_formatted || (data.total + ' ' + (data.currency_symbol || 'руб.'));
            commands.push(utf8ToCp866('Сумма: ' + totalText) + LF);
            commands.push(ESC + '!' + '\x00'); // Обычный
            
            // Статус оплаты (без символов галочки/крестика, т.к. они не поддерживаются в CP866)
            if (data.is_paid) {
                commands.push(utf8ToCp866(data.payment_status || 'Оплачено') + LF);
            } else {
                commands.push(utf8ToCp866(data.payment_status || 'Не оплачено') + LF);
            }
            commands.push(LF);
            commands.push(LF);
            commands.push(LF);
            
            // Автоотрез между чеками
            commands.push(GS + 'V' + '\x41' + '\x03');
            
            return commands.join('');
        }
        
        function generateMultipleESCPOS(ordersData) {
            let allCommands = [];
            const ESC = '\x1B';
            const LF = '\x0A';
            
            ordersData.forEach(function(orderData, index) {
                if (index === 0) {
                    // Для первого заказа: инициализация + пустые строки + имя клиента
                    allCommands.push(ESC + '@'); // Сброс принтера
                    allCommands.push(ESC + '\x74' + '\x11'); // ESC t 17 = CP866 (Cyrillic)
                    // Пустые строки для обработки инициализации (увеличено до максимума)
                    allCommands.push(LF + LF + LF + LF + LF + LF + LF + LF + LF);
                    
                    // Теперь добавляем содержимое заказа БЕЗ повторной инициализации
                    const GS = '\x1D';
                    // Клиент
                    allCommands.push(ESC + '!' + '\x08'); // Полужирный
                    // Пустая строка после команды форматирования
                    allCommands.push(LF);
                    allCommands.push(utf8ToCp866(orderData.customer_name) + LF);
                    allCommands.push(ESC + '!' + '\x00'); // Обычный
                    allCommands.push(LF);
                    
                    // Содержимое заказа
                    allCommands.push('--------------------------------' + LF);
                    orderData.items.forEach(function(item) {
                        let itemLine = utf8ToCp866(item.name);
                        if (item.unit) {
                            itemLine += ' (' + utf8ToCp866(item.unit) + ')';
                        }
                        if (item.quantity > 1) {
                            itemLine += ' x' + item.quantity;
                        }
                        allCommands.push(itemLine + LF);
                    });
                    allCommands.push('--------------------------------' + LF);
                    allCommands.push(LF);
                    
                    // Доставка/Самовывоз
                    allCommands.push(utf8ToCp866(orderData.shipping_type || (orderData.shipping_address ? 'Доставка' : 'Самовывоз')) + LF);
                    allCommands.push(LF);
                    
                    // Стоимость заказа
                    allCommands.push(ESC + '!' + '\x08'); // Полужирный
                    const totalText = orderData.total_formatted || (orderData.total + ' ' + (orderData.currency_symbol || 'руб.'));
                    allCommands.push(utf8ToCp866('Сумма: ' + totalText) + LF);
                    allCommands.push(ESC + '!' + '\x00'); // Обычный
                    
                    // Статус оплаты
                    if (orderData.is_paid) {
                        allCommands.push(utf8ToCp866(orderData.payment_status || 'Оплачено') + LF);
                    } else {
                        allCommands.push(utf8ToCp866(orderData.payment_status || 'Не оплачено') + LF);
                    }
                    allCommands.push(LF);
                    allCommands.push(LF);
                    allCommands.push(LF);
                    
                    // Автоотрез
                    allCommands.push(GS + 'V' + '\x41' + '\x03');
                } else {
                    // Для остальных заказов: минимальная инициализация (принтер уже готов)
                    allCommands.push(ESC + '@'); // Сброс принтера
                    allCommands.push(ESC + '\x74' + '\x11'); // ESC t 17 = CP866 (Cyrillic)
                    // Минимальные пустые строки (принтер уже инициализирован)
                    allCommands.push(LF);
                    
                    // Содержимое заказа
                    const GS = '\x1D';
                    // Клиент
                    allCommands.push(ESC + '!' + '\x08'); // Полужирный
                    allCommands.push(utf8ToCp866(orderData.customer_name) + LF);
                    allCommands.push(ESC + '!' + '\x00'); // Обычный
                    allCommands.push(LF);
                    
                    // Содержимое заказа
                    allCommands.push('--------------------------------' + LF);
                    orderData.items.forEach(function(item) {
                        let itemLine = utf8ToCp866(item.name);
                        if (item.unit) {
                            itemLine += ' (' + utf8ToCp866(item.unit) + ')';
                        }
                        if (item.quantity > 1) {
                            itemLine += ' x' + item.quantity;
                        }
                        allCommands.push(itemLine + LF);
                    });
                    allCommands.push('--------------------------------' + LF);
                    allCommands.push(LF);
                    
                    // Доставка/Самовывоз
                    allCommands.push(utf8ToCp866(orderData.shipping_type || (orderData.shipping_address ? 'Доставка' : 'Самовывоз')) + LF);
                    allCommands.push(LF);
                    
                    // Стоимость заказа
                    allCommands.push(ESC + '!' + '\x08'); // Полужирный
                    const totalText = orderData.total_formatted || (orderData.total + ' ' + (orderData.currency_symbol || 'руб.'));
                    allCommands.push(utf8ToCp866('Сумма: ' + totalText) + LF);
                    allCommands.push(ESC + '!' + '\x00'); // Обычный
                    
                    // Статус оплаты
                    if (orderData.is_paid) {
                        allCommands.push(utf8ToCp866(orderData.payment_status || 'Оплачено') + LF);
                    } else {
                        allCommands.push(utf8ToCp866(orderData.payment_status || 'Не оплачено') + LF);
                    }
                    allCommands.push(LF);
                    allCommands.push(LF);
                    allCommands.push(LF);
                    
                    // Автоотрез
                    allCommands.push(GS + 'V' + '\x41' + '\x03');
                }
                
                // Минимальный отступ между заказами (кроме последнего)
                if (index < ordersData.length - 1) {
                    allCommands.push(LF);
                }
            });
            return allCommands.join('');
        }
        
        async function printMultipleOrders() {
            if (!qzConnected) {
                alert('QZ Tray не подключен');
                return;
            }
            
            const printBtn = document.getElementById('qz-print-btn');
            printBtn.disabled = true;
            printBtn.textContent = 'Печать...';
            
            try {
                // Генерируем все заказы одним блоком ESC-POS команд
                const escposData = generateMultipleESCPOS(ordersData);
                
                const bytes = [];
                for (let i = 0; i < escposData.length; i++) {
                    const char = escposData[i];
                    const charCode = char.charCodeAt(0);
                    if (charCode < 256) {
                        bytes.push(charCode);
                    } else {
                        bytes.push(charCode & 0xFF);
                    }
                }
                
                let binary = '';
                for (let i = 0; i < bytes.length; i++) {
                    binary += String.fromCharCode(bytes[i]);
                }
                const base64Data = btoa(binary);
                
                const config = qz.configs.create(PRINTER_NAME);
                const printers = await qz.printers.find();
                
                if (!printers.includes(PRINTER_NAME)) {
                    throw new Error('Принтер не найден');
                }
                
                // Печатаем все заказы одним запросом
                await qz.print(config, [{
                    type: 'raw',
                    format: 'base64',
                    data: base64Data
                }]).then(function() {
                    alert('Все заказы успешно отправлены на печать!');
                    window.close();
                });
            } catch (err) {
                console.error('Print error:', err);
                alert('Ошибка при печати: ' + err.message);
            } finally {
                printBtn.disabled = false;
                printBtn.textContent = '🖨️ Печать всех заказов (' + ordersData.length + ' шт.)';
            }
        }
        
        window.addEventListener('beforeunload', function() {
            if (qzConnected && typeof qz !== 'undefined') {
                qz.websocket.disconnect();
            }
        });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Добавляем страницу печати разбора заказов
add_action('admin_menu', 'gustolocal_add_print_breakdown_page');
function gustolocal_add_print_breakdown_page() {
    add_submenu_page(
        null,
        'Печать разбора заказов',
        'Печать разбора заказов',
        'read',
        'gustolocal-print-breakdown',
        'gustolocal_print_breakdown_page'
    );
}

// Страница печати разбора заказов
function gustolocal_print_breakdown_page() {
    if (!current_user_can('read')) {
        wp_die(__('У вас нет прав для доступа к этой странице.'));
    }
    
    $order_ids_str = isset($_GET['order_ids']) ? sanitize_text_field($_GET['order_ids']) : '';
    if (empty($order_ids_str)) {
        echo '<div class="wrap"><h1>Ошибка</h1><p>Не указаны ID заказов.</p></div>';
        return;
    }
    
    $order_ids = array_map('intval', explode(',', $order_ids_str));
    $breakdown_data = gustolocal_generate_breakdown($order_ids);
    
    if (!$breakdown_data || empty($breakdown_data['dishes_by_sale_type'])) {
        echo '<div class="wrap"><h1>Ошибка</h1><p>Не удалось сформировать разбор заказов.</p></div>';
        return;
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Печать разбора заказов</title>
        <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2/qz-tray.min.js"></script>
    </head>
    <body>
        <div style="text-align: center; margin: 20px;">
            <div id="qz-status" style="margin: 10px 0; padding: 8px; border-radius: 4px; font-size: 12px; background: #f8d7da; color: #721c24;">
                QZ Tray: Не подключен
            </div>
            <button id="qz-print-btn" onclick="printBreakdown()" style="background: #28a745; color: white; padding: 15px 30px; border: none; cursor: pointer; font-size: 18px; border-radius: 5px; font-weight: bold;">
                🖨️ Печать разбора на кухню
            </button>
        </div>
        
        <script>
        const breakdownData = <?php echo json_encode($breakdown_data, JSON_UNESCAPED_UNICODE); ?>;
        const PRINTER_NAME = 'Printer POS-80';
        let qzConnected = false;
        
        // Подключение к QZ Tray
        window.addEventListener('load', function() {
            connectToQZTray();
        });
        
        async function connectToQZTray() {
            const statusEl = document.getElementById('qz-status');
            const printBtn = document.getElementById('qz-print-btn');
            
            try {
                if (typeof qz === 'undefined') {
                    throw new Error('QZ Tray библиотека не загружена');
                }
                
                await qz.websocket.connect().then(function() {
                    qzConnected = true;
                    statusEl.textContent = 'QZ Tray: Подключен ✓';
                    statusEl.style.background = '#d4edda';
                    statusEl.style.color = '#155724';
                    printBtn.disabled = false;
                });
            } catch (err) {
                qzConnected = false;
                statusEl.textContent = 'QZ Tray: Не подключен';
                printBtn.disabled = true;
            }
        }
        
        // Функции конвертации UTF-8 в CP866
        function utf8ToCp866(str) {
            if (!str) return str;
            const utf8ToCp866Map = {
                'А': '\x80', 'Б': '\x81', 'В': '\x82', 'Г': '\x83', 'Д': '\x84', 'Е': '\x85', 'Ж': '\x86', 'З': '\x87',
                'И': '\x88', 'Й': '\x89', 'К': '\x8A', 'Л': '\x8B', 'М': '\x8C', 'Н': '\x8D', 'О': '\x8E', 'П': '\x8F',
                'Р': '\x90', 'С': '\x91', 'Т': '\x92', 'У': '\x93', 'Ф': '\x94', 'Х': '\x95', 'Ц': '\x96', 'Ч': '\x97',
                'Ш': '\x98', 'Щ': '\x99', 'Ъ': '\x9A', 'Ы': '\x9B', 'Ь': '\x9C', 'Э': '\x9D', 'Ю': '\x9E', 'Я': '\x9F',
                'а': '\xA0', 'б': '\xA1', 'в': '\xA2', 'г': '\xA3', 'д': '\xA4', 'е': '\xA5', 'ж': '\xA6', 'з': '\xA7',
                'и': '\xA8', 'й': '\xA9', 'к': '\xAA', 'л': '\xAB', 'м': '\xAC', 'н': '\xAD', 'о': '\xAE', 'п': '\xAF',
                'р': '\xE0', 'с': '\xE1', 'т': '\xE2', 'у': '\xE3', 'ф': '\xE4', 'х': '\xE5', 'ц': '\xE6', 'ч': '\xE7',
                'ш': '\xE8', 'щ': '\xE9', 'ъ': '\xEA', 'ы': '\xEB', 'ь': '\xEC', 'э': '\xED', 'ю': '\xEE', 'я': '\xEF',
                'Ё': '\xF0', 'ё': '\xF1'
            };
            let result = '';
            for (let i = 0; i < str.length; i++) {
                const char = str[i];
                if (utf8ToCp866Map[char]) {
                    result += utf8ToCp866Map[char];
                } else if (char.charCodeAt(0) < 128) {
                    result += char;
                } else {
                    result += '?';
                }
            }
            return result;
        }
        
        function generateBreakdownESCPOS(breakdownData) {
            let commands = [];
            const ESC = '\x1B';
            const GS = '\x1D';
            const LF = '\x0A';
            
            // Инициализация принтера
            commands.push(ESC + '@'); // Сброс принтера
            commands.push(ESC + '\x74' + '\x11'); // ESC t 17 = CP866 (Cyrillic)
            commands.push(LF + LF + LF + LF + LF + LF + LF + LF + LF + LF);
            
            // Заголовок
            commands.push(ESC + '!' + '\x08'); // Полужирный
            commands.push(utf8ToCp866('РАЗБОР ЗАКАЗОВ') + LF);
            commands.push(ESC + '!' + '\x00'); // Обычный
            commands.push(LF);
            commands.push('--------------------------------' + LF);
            commands.push(LF);
            
            const dishesBySaleType = breakdownData.dishes_by_sale_type || {};
            const saleTypesOrder = ['superfood', 'mercat'];
            
            saleTypesOrder.forEach(function(saleType) {
                if (!dishesBySaleType[saleType]) return;
                
                const saleTypeLabel = saleType === 'superfood' ? 'Superfood' : 'Mercat';
                commands.push(ESC + '!' + '\x08'); // Полужирный
                commands.push(utf8ToCp866(saleTypeLabel) + LF);
                commands.push(ESC + '!' + '\x00'); // Обычный
                commands.push(LF);
                
                const categories = dishesBySaleType[saleType];
                Object.keys(categories).forEach(function(category) {
                    const dishes = categories[category];
                    
                    // Категория
                    commands.push(utf8ToCp866(category) + LF);
                    
                    // Блюда
                    Object.keys(dishes).forEach(function(dishKey) {
                        const dish = dishes[dishKey];
                        const dishTotal = Object.values(dish.quantities || {}).reduce(function(sum, qty) { return sum + qty; }, 0);
                        
                        // Вычисляем вес
                        let weightDisplay = '';
                        if (dish.unit) {
                            const totalQty = dishTotal;
                            if (dish.unit.match(/^(\d+(?:[.,]\d+)?)\s*(г|мл|кг|л|шт|пор)/ui)) {
                                const match = dish.unit.match(/^(\d+(?:[.,]\d+)?)\s*(г|мл|кг|л|шт|пор)/ui);
                                const value = parseFloat(match[1].replace(',', '.'));
                                const unitType = match[2];
                                const totalWeight = value * totalQty;
                                weightDisplay = Math.round(totalWeight) + ' ' + unitType;
                            }
                        }
                        
                        // Название блюда, количество и вес в одну строку для экономии бумаги
                        let dishLine = dish.name;
                        if (dish.unit) {
                            dishLine += ' (' + dish.unit + ')';
                        }
                        // Добавляем количество и вес в ту же строку
                        if (weightDisplay) {
                            dishLine += '  ' + dishTotal + ' шт. / ' + weightDisplay;
                        } else {
                            dishLine += '  ' + dishTotal + ' шт.';
                        }
                        // Конвертируем всю строку в CP866
                        commands.push(utf8ToCp866(dishLine) + LF);
                        commands.push(LF);
                    });
                });
            });
            
            // Итоговая строка
            commands.push('--------------------------------' + LF);
            commands.push(ESC + '!' + '\x08'); // Полужирный
            commands.push(utf8ToCp866('ИТОГО: ' + breakdownData.total_portions + ' шт.') + LF);
            commands.push(ESC + '!' + '\x00'); // Обычный
            commands.push(LF);
            commands.push(LF);
            commands.push(LF);
            
            // Автоотрез
            commands.push(GS + 'V' + '\x41' + '\x03');
            
            return commands.join('');
        }
        
        async function printBreakdown() {
            if (!qzConnected) {
                alert('QZ Tray не подключен');
                return;
            }
            
            const printBtn = document.getElementById('qz-print-btn');
            printBtn.disabled = true;
            printBtn.textContent = 'Печать...';
            
            try {
                const escposData = generateBreakdownESCPOS(breakdownData);
                
                const bytes = [];
                for (let i = 0; i < escposData.length; i++) {
                    const char = escposData[i];
                    const charCode = char.charCodeAt(0);
                    if (charCode < 256) {
                        bytes.push(charCode);
                    } else {
                        bytes.push(charCode & 0xFF);
                    }
                }
                
                let binary = '';
                for (let i = 0; i < bytes.length; i++) {
                    binary += String.fromCharCode(bytes[i]);
                }
                const base64Data = btoa(binary);
                
                const config = qz.configs.create(PRINTER_NAME);
                const printers = await qz.printers.find();
                
                if (!printers.includes(PRINTER_NAME)) {
                    throw new Error('Принтер не найден');
                }
                
                await qz.print(config, [{
                    type: 'raw',
                    format: 'base64',
                    data: base64Data
                }]).then(function() {
                    alert('Разбор заказов успешно отправлен на печать!');
                    window.close();
                });
            } catch (err) {
                console.error('Print error:', err);
                alert('Ошибка при печати: ' + err.message);
            } finally {
                printBtn.disabled = false;
                printBtn.textContent = '🖨️ Печать разбора на кухню';
            }
        }
        
        window.addEventListener('beforeunload', function() {
            if (qzConnected && typeof qz !== 'undefined') {
                qz.websocket.disconnect();
            }
        });
        </script>
    </body>
    </html>
    <?php
}

