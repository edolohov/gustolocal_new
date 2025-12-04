<?php
/**
 * Скрипт для активации всех блюд
 * Использование: загрузите на сервер в корень сайта и откройте в браузере
 * После использования УДАЛИТЕ этот файл!
 */

// Подключаем WordPress
require_once(__DIR__ . '/../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Недостаточно прав для выполнения этого действия');
}

// Получаем все блюда
$dishes = get_posts([
    'post_type' => 'wmb_dish',
    'numberposts' => -1,
    'post_status' => 'any',
    'fields' => 'ids',
]);

$activated = 0;
foreach ($dishes as $dish_id) {
    $current_active = get_post_meta($dish_id, 'wmb_active', true);
    // Активируем только если неактивно или пусто
    if ($current_active !== '1') {
        update_post_meta($dish_id, 'wmb_active', '1');
        $activated++;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Активация блюд</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 50px auto; }
        .success { color: #4caf50; font-weight: bold; }
        .info { color: #666; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Активация блюд</h1>
    <p class="success">✅ Активировано блюд: <?php echo $activated; ?> из <?php echo count($dishes); ?></p>
    <p class="info">📝 Теперь все блюда должны отображаться на сайте.</p>
    <p class="info">⚠️ <strong>ВАЖНО:</strong> Удалите этот файл после использования!</p>
    <p><a href="/wp-admin/admin.php?page=wmb_items">Перейти к списку блюд</a></p>
</body>
</html>
<?php

