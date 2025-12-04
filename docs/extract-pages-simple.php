<?php
/**
 * Простой скрипт для извлечения содержимого страниц из базы данных
 * Работает напрямую с БД, без WordPress
 */

// Настройки базы данных (из wp-config.php)
$db_host = 'localhost';
$db_user = 'u850527203';
$db_password = 'hiLKov15!';
$db_name = 'u850527203_5vYEq';
$table_prefix = 'stg_';

// Подключаемся к базе данных
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($mysqli->connect_error) {
    die('Ошибка подключения к базе данных: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

// Ищем страницы
$pages = array('test', 'custom');
$results = array();

foreach ($pages as $page_slug) {
    $query = $mysqli->prepare("
        SELECT ID, post_title, post_content, post_name 
        FROM {$table_prefix}posts 
        WHERE post_name = ? 
        AND post_type = 'page' 
        AND post_status = 'publish'
        LIMIT 1
    ");
    
    if ($query) {
        $query->bind_param('s', $page_slug);
        $query->execute();
        $result = $query->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $results[$page_slug] = $row;
        }
        
        $query->close();
    }
}

$mysqli->close();

// Выводим результаты
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Извлечение содержимого страниц</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f0f0f1; }
        .page-content { background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #0073aa; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .page-title { color: #0073aa; font-size: 20px; font-weight: bold; margin-bottom: 15px; }
        pre { background: #f5f5f5; padding: 15px; border: 1px solid #ddd; overflow-x: auto; white-space: pre-wrap; font-size: 14px; line-height: 1.6; }
        .not-found { color: #d63638; border-left-color: #d63638; }
        .info { color: #666; font-size: 14px; margin: 5px 0; }
        h1 { color: #23282d; }
    </style>
</head>
<body>
    <h1>Содержимое страниц "test" и "custom"</h1>
    
    <?php foreach (array('test', 'custom') as $slug): ?>
        <?php if (isset($results[$slug])): 
            $page = $results[$slug];
        ?>
            <div class="page-content">
                <div class="page-title">Страница "<?php echo htmlspecialchars($slug); ?>"</div>
                <p class="info"><strong>ID:</strong> <?php echo $page['ID']; ?></p>
                <p class="info"><strong>Заголовок:</strong> <?php echo htmlspecialchars($page['post_title']); ?></p>
                <p class="info"><strong>Slug:</strong> <?php echo htmlspecialchars($page['post_name']); ?></p>
                <h3>Содержимое (для вставки в HTML блок в админке):</h3>
                <pre><?php echo htmlspecialchars($page['post_content']); ?></pre>
            </div>
        <?php else: ?>
            <div class="page-content not-found">
                <div class="page-title">Страница "<?php echo htmlspecialchars($slug); ?>" не найдена</div>
                <p>Страница с slug "<?php echo htmlspecialchars($slug); ?>" не существует в базе данных или не опубликована.</p>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <hr>
    <p><small>Если страницы не найдены, проверьте в админке WordPress: <strong>Страницы → Все страницы</strong></small></p>
</body>
</html>

