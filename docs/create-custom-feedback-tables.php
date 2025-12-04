<?php
/**
 * Скрипт для создания таблиц кастомных опросов на продакшене
 * Запустите через браузер: https://gustolocal.es/docs/create-custom-feedback-tables.php
 */

// Загружаем WordPress
$wp_load_paths = array(
    dirname(__FILE__) . '/wp-load.php',
    dirname(__FILE__) . '/../wp-load.php',
    dirname(__FILE__) . '/../../wp-load.php',
    dirname(__FILE__) . '/../../../wp-load.php',
    dirname(__FILE__) . '/../../../../wp-load.php',
);

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('Не удалось загрузить WordPress. Проверьте путь к wp-load.php');
}

// Проверяем права администратора
if (!current_user_can('manage_options')) {
    die('Доступ запрещен. Войдите как администратор.');
}

global $wpdb;

echo '<h1>Создание таблиц кастомных опросов</h1>';
echo '<style>body { font-family: Arial, sans-serif; padding: 20px; } .success { color: green; } .error { color: red; }</style>';

$charset_collate = $wpdb->get_charset_collate();

// Таблица запросов
$custom_requests_table = $wpdb->prefix . 'custom_feedback_requests';
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

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
$result1 = dbDelta($sql_requests);

echo '<h2>Таблица запросов (custom_feedback_requests)</h2>';
if ($wpdb->last_error) {
    echo '<p class="error">Ошибка: ' . esc_html($wpdb->last_error) . '</p>';
} else {
    echo '<p class="success">✓ Таблица создана или уже существует</p>';
    echo '<pre>' . print_r($result1, true) . '</pre>';
}

// Таблица записей
$custom_entries_table = $wpdb->prefix . 'custom_feedback_entries';
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

$result2 = dbDelta($sql_entries);

echo '<h2>Таблица записей (custom_feedback_entries)</h2>';
if ($wpdb->last_error) {
    echo '<p class="error">Ошибка: ' . esc_html($wpdb->last_error) . '</p>';
} else {
    echo '<p class="success">✓ Таблица создана или уже существует</p>';
    echo '<pre>' . print_r($result2, true) . '</pre>';
}

// Проверяем наличие колонки dish_unit
$exists = $wpdb->get_var($wpdb->prepare(
    "SHOW COLUMNS FROM {$custom_entries_table} LIKE %s",
    'dish_unit'
));

echo '<h2>Проверка колонки dish_unit</h2>';
if (!$exists) {
    $wpdb->query("ALTER TABLE {$custom_entries_table} ADD COLUMN dish_unit varchar(100) DEFAULT '' AFTER dish_name");
    if ($wpdb->last_error) {
        echo '<p class="error">Ошибка добавления колонки: ' . esc_html($wpdb->last_error) . '</p>';
    } else {
        echo '<p class="success">✓ Колонка dish_unit добавлена</p>';
    }
} else {
    echo '<p class="success">✓ Колонка dish_unit уже существует</p>';
}

// Проверяем, что таблицы существуют
echo '<h2>Проверка существования таблиц</h2>';
$tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}custom_feedback%'", ARRAY_N);
if (!empty($tables)) {
    echo '<p class="success">✓ Найдены таблицы:</p><ul>';
    foreach ($tables as $table) {
        echo '<li>' . esc_html($table[0]) . '</li>';
    }
    echo '</ul>';
} else {
    echo '<p class="error">✗ Таблицы не найдены</p>';
}

echo '<hr>';
echo '<p><strong>Готово!</strong> Теперь проверьте меню в админке WooCommerce → "Кастомные опросы"</p>';
echo '<p><a href="' . admin_url('admin.php?page=gustolocal-custom-feedback') . '">Перейти к кастомным опросам</a></p>';

