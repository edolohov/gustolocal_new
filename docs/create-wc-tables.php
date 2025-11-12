<?php
/**
 * Скрипт для создания недостающих таблиц WooCommerce
 * Запустите этот файл через браузер: https://gustolocal.es/wp-content/themes/gustolocal/../docs/create-wc-tables.php
 * Или через командную строку: php create-wc-tables.php
 */

// Подключаем WordPress
require_once(dirname(__FILE__) . '/../../wp-load.php');

// Проверяем, что WooCommerce установлен
if (!class_exists('WooCommerce')) {
    die('WooCommerce не установлен!');
}

// Проверяем права доступа (только для администраторов)
if (!current_user_can('manage_options')) {
    die('Недостаточно прав для выполнения этого действия!');
}

echo "<h1>Создание таблиц WooCommerce</h1>";
echo "<pre>";

// Получаем префикс таблиц из wp-config
global $wpdb;
$table_prefix = $wpdb->prefix;

echo "Префикс таблиц: {$table_prefix}\n\n";

// Список таблиц, которые должны существовать
$required_tables = array(
    'wc_orders',
    'wc_orders_meta',
    'wc_order_addresses',
    'wc_order_operational_data',
    'wc_products',
    'wc_product_meta_lookup',
    'wc_tax_rate_classes',
    'wc_reserved_stock',
    'wc_download_log',
);

// Проверяем существующие таблицы
echo "Проверка существующих таблиц:\n";
$existing_tables = array();
$missing_tables = array();

foreach ($required_tables as $table) {
    $full_table_name = $table_prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '{$full_table_name}'");
    
    if ($exists) {
        $existing_tables[] = $table;
        echo "✓ {$full_table_name} - существует\n";
    } else {
        $missing_tables[] = $table;
        echo "✗ {$full_table_name} - отсутствует\n";
    }
}

echo "\n";

// Если есть недостающие таблицы, создаем их
if (!empty($missing_tables)) {
    echo "Создание недостающих таблиц...\n\n";
    
    // Используем встроенную функцию WooCommerce для создания таблиц
    if (class_exists('WC_Install')) {
        // Запускаем создание таблиц
        WC_Install::create_tables();
        echo "Запущено создание таблиц через WC_Install::create_tables()\n\n";
    } else {
        echo "Класс WC_Install не найден. Пробуем альтернативный метод...\n\n";
        
        // Альтернативный метод - через установщик WooCommerce
        if (function_exists('wc_install')) {
            wc_install();
            echo "Запущено создание таблиц через wc_install()\n\n";
        } else {
            echo "Функция wc_install() не найдена. Пробуем напрямую...\n\n";
            
            // Прямое создание через установщик
            if (file_exists(WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-install.php')) {
                require_once(WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-install.php');
                WC_Install::create_tables();
                echo "Загружен класс WC_Install и запущено создание таблиц\n\n";
            }
        }
    }
    
    // Проверяем снова после создания
    echo "Повторная проверка таблиц:\n";
    foreach ($missing_tables as $table) {
        $full_table_name = $table_prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '{$full_table_name}'");
        
        if ($exists) {
            echo "✓ {$full_table_name} - создана успешно\n";
        } else {
            echo "✗ {$full_table_name} - не удалось создать\n";
        }
    }
} else {
    echo "Все необходимые таблицы существуют!\n";
}

echo "\n";
echo "Готово!\n";
echo "</pre>";

