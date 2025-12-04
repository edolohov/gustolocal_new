<?php
/**
 * Скрипт для извлечения содержимого страниц "test" и "custom" из базы данных
 * 
 * Использование:
 * 1. Загрузите этот файл на сервер в корень WordPress
 * 2. Откройте в браузере: https://gustolocal.es/extract-pages.php
 * 3. Скопируйте содержимое и вставьте в HTML блок в админке
 */

// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем WordPress
$wp_load_path = dirname(__FILE__) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    // Пробуем альтернативный путь
    $wp_load_path = dirname(dirname(__FILE__)) . '/wp-load.php';
}

if (!file_exists($wp_load_path)) {
    die('Ошибка: не найден файл wp-load.php. Убедитесь, что скрипт находится в корне WordPress.');
}

require_once($wp_load_path);

// Проверяем, что WordPress загружен
if (!function_exists('get_page_by_path')) {
    die('Ошибка: WordPress не загружен корректно.');
}

// Проверяем права доступа (только для администраторов)
if (!is_user_logged_in() || !current_user_can('administrator')) {
    die('Доступ запрещен. Войдите как администратор.');
}

// Ищем страницы
$test_page = get_page_by_path('test');
$custom_page = get_page_by_path('custom');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Извлечение содержимого страниц</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
        .page-content { background: #f5f5f5; padding: 15px; margin: 20px 0; border-left: 4px solid #0073aa; }
        .page-title { color: #0073aa; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        pre { background: #fff; padding: 15px; border: 1px solid #ddd; overflow-x: auto; white-space: pre-wrap; }
        .not-found { color: #d63638; }
    </style>
</head>
<body>
    <h1>Содержимое страниц "test" и "custom"</h1>
    
    <?php if ($test_page): ?>
        <div class="page-content">
            <div class="page-title">Страница "test" (ID: <?php echo $test_page->ID; ?>)</div>
            <p><strong>Заголовок:</strong> <?php echo esc_html($test_page->post_title); ?></p>
            <p><strong>URL:</strong> <?php echo esc_url(get_permalink($test_page->ID)); ?></p>
            <h3>Содержимое (для вставки в HTML блок):</h3>
            <pre><?php echo esc_html($test_page->post_content); ?></pre>
        </div>
    <?php else: ?>
        <div class="page-content not-found">
            <div class="page-title">Страница "test" не найдена</div>
            <p>Страница с slug "test" не существует в базе данных.</p>
        </div>
    <?php endif; ?>
    
    <?php if ($custom_page): ?>
        <div class="page-content">
            <div class="page-title">Страница "custom" (ID: <?php echo $custom_page->ID; ?>)</div>
            <p><strong>Заголовок:</strong> <?php echo esc_html($custom_page->post_title); ?></p>
            <p><strong>URL:</strong> <?php echo esc_url(get_permalink($custom_page->ID)); ?></p>
            <h3>Содержимое (для вставки в HTML блок):</h3>
            <pre><?php echo esc_html($custom_page->post_content); ?></pre>
        </div>
    <?php else: ?>
        <div class="page-content not-found">
            <div class="page-title">Страница "custom" не найдена</div>
            <p>Страница с slug "custom" не существует в базе данных.</p>
        </div>
    <?php endif; ?>
    
    <hr>
    <p><small>Если страницы не найдены, возможно, они были удалены или имеют другие slug. Проверьте в админке WordPress: Страницы → Все страницы</small></p>
</body>
</html>

