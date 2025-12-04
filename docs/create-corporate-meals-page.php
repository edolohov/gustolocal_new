<?php
/**
 * Скрипт для создания страницы "Горячие обеды для офисов и детских садов"
 * Запустить через WordPress CLI: wp eval-file create-corporate-meals-page.php
 * Или через браузер (временно): добавить в корень WordPress и открыть
 */

// Подключаем WordPress
require_once(__DIR__ . '/../../../../wp-load.php');

if (!current_user_can('edit_pages')) {
    die('Недостаточно прав для создания страниц');
}

// Проверяем, существует ли уже страница
$existing_page = get_page_by_path('corporate-meals');

if ($existing_page) {
    echo "Страница уже существует (ID: {$existing_page->ID})<br>";
    $page_id = $existing_page->ID;
} else {
    // Создаём новую страницу
    $page_data = array(
        'post_title'    => 'Горячие обеды для офисов и детских садов',
        'post_name'     => 'corporate-meals',
        'post_content'  => '', // Будет заполнено паттерном
        'post_status'   => 'draft', // Черновик для проверки
        'post_type'      => 'page',
        'post_author'    => 1,
    );
    
    $page_id = wp_insert_post($page_data);
    
    if (is_wp_error($page_id)) {
        die('Ошибка создания страницы: ' . $page_id->get_error_message());
    }
    
    echo "Страница создана (ID: {$page_id})<br>";
}

// Получаем контент паттерна
$pattern_slug = 'gustolocal/corporate-meals';

// Используем функцию из темы для загрузки паттерна
if (function_exists('gustolocal_get_pattern_content')) {
    $pattern_content = gustolocal_get_pattern_content($pattern_slug);
} else {
    // Fallback: загружаем напрямую из файла
    $pattern_file = get_theme_file_path('patterns/corporate-meals.php');
    if (file_exists($pattern_file) && function_exists('gustolocal_load_pattern_file')) {
        $pattern_content = gustolocal_load_pattern_file($pattern_file);
    } else {
        $pattern_content = '';
    }
}

if (empty($pattern_content)) {
    echo "Предупреждение: Не удалось загрузить паттерн. Примените его вручную через редактор.<br>";
} else {
    // Обновляем контент страницы
    wp_update_post(array(
        'ID'           => $page_id,
        'post_content' => $pattern_content,
    ));
    
    echo "Паттерн применён к странице<br>";
}

echo "<br><strong>Готово! Страница создана как черновик.</strong><br>";
echo "ID страницы: {$page_id}<br>";
echo "<a href='" . admin_url("post.php?post={$page_id}&action=edit") . "'>Редактировать страницу</a><br>";
echo "<a href='" . get_preview_post_link($page_id) . "'>Предпросмотр страницы</a><br>";

