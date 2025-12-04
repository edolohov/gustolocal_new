<?php
/**
 * Диагностический скрипт для проверки страницы /menu/
 * Загрузите этот файл в корень сайта и откройте в браузере
 */

// Подключение WordPress
require_once('wp-load.php');

echo "<h1>Диагностика страницы /menu/</h1>";

// 1. Проверка существования страницы
$menu_page = get_page_by_path('menu');
if ($menu_page) {
    echo "<p style='color: green;'>✓ Страница 'menu' найдена (ID: {$menu_page->ID}, статус: {$menu_page->post_status})</p>";
} else {
    echo "<p style='color: red;'>✗ Страница 'menu' НЕ найдена!</p>";
}

// 2. Проверка постоянных ссылок
$permalink_structure = get_option('permalink_structure');
echo "<p>Структура постоянных ссылок: <strong>" . ($permalink_structure ?: 'не установлена') . "</strong></p>";

// 3. Проверка главной страницы
$page_on_front = get_option('page_on_front');
echo "<p>Главная страница (page_on_front): <strong>" . ($page_on_front ?: 'не установлена') . "</strong></p>";

// 4. Проверка rewrite rules
global $wp_rewrite;
echo "<h2>Rewrite Rules для 'menu':</h2>";
$menu_rules = array_filter($wp_rewrite->rules, function($key) {
    return strpos($key, 'menu') !== false;
}, ARRAY_FILTER_USE_KEY);
if (empty($menu_rules)) {
    echo "<p style='color: orange;'>⚠ Нет специальных rewrite rules для 'menu'</p>";
} else {
    echo "<pre>" . print_r($menu_rules, true) . "</pre>";
}

// 5. Проверка query vars
echo "<h2>Текущие query vars:</h2>";
global $wp_query;
echo "<pre>" . print_r($wp_query->query_vars, true) . "</pre>";

// 6. Попытка получить страницу через WP_Query
$test_query = new WP_Query(array(
    'pagename' => 'menu',
    'post_type' => 'page'
));
if ($test_query->have_posts()) {
    echo "<p style='color: green;'>✓ WP_Query находит страницу 'menu'</p>";
    $test_query->the_post();
    echo "<p>Заголовок: <strong>" . get_the_title() . "</strong></p>";
    echo "<p>URL: <strong>" . get_permalink() . "</strong></p>";
    wp_reset_postdata();
} else {
    echo "<p style='color: red;'>✗ WP_Query НЕ находит страницу 'menu'</p>";
}

// 7. Проверка активных плагинов
echo "<h2>Активные плагины:</h2>";
$active_plugins = get_option('active_plugins');
echo "<ul>";
foreach ($active_plugins as $plugin) {
    echo "<li>" . $plugin . "</li>";
}
echo "</ul>";

// 8. Проверка .htaccess
$htaccess_path = ABSPATH . '.htaccess';
if (file_exists($htaccess_path)) {
    echo "<h2>Содержимое .htaccess:</h2>";
    $htaccess_content = file_get_contents($htaccess_path);
    if (strpos($htaccess_content, 'menu') !== false || strpos($htaccess_content, 'RewriteRule') !== false) {
        echo "<pre>" . htmlspecialchars($htaccess_content) . "</pre>";
    } else {
        echo "<p>В .htaccess нет упоминаний 'menu' или RewriteRule</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ Файл .htaccess не найден</p>";
}



