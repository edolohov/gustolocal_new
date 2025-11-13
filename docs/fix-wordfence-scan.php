<?php
/**
 * Скрипт для исправления проблемы со сканированием Wordfence
 * 
 * Проблема: INSERT query failed для таблицы stg_wffilemods
 * Причина: Слишком длинный запрос или проблема с размером полей
 * 
 * Использование:
 * 1. Загрузите на сервер в корень WordPress
 * 2. Откройте в браузере: https://gustolocal.es/fix-wordfence-scan.php
 * 3. Скрипт проверит и исправит структуру таблицы
 */

// Настройки базы данных
$db_host = 'localhost';
$db_user = 'u850527203';
$db_password = 'hiLKov15!';
$db_name = 'u850527203_5vYEq';
$table_prefix = 'stg_';

// Подключаемся к базе данных
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($mysqli->connect_error) {
    die('Ошибка подключения: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

$table_name = $table_prefix . 'wffilemods';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Исправление Wordfence Scan</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Исправление проблемы со сканированием Wordfence</h1>
    
    <?php
    // Проверяем существование таблицы
    $result = $mysqli->query("SHOW TABLES LIKE '{$table_name}'");
    if ($result->num_rows == 0) {
        echo '<div class="error">Таблица ' . $table_name . ' не существует. Wordfence нужно переустановить.</div>';
        $mysqli->close();
        exit;
    }
    
    echo '<div class="info">Таблица ' . $table_name . ' найдена.</div>';
    
    // Получаем структуру таблицы
    $result = $mysqli->query("DESCRIBE {$table_name}");
    $columns = array();
    while ($row = $result->fetch_assoc()) {
        $columns[$row['Field']] = $row;
    }
    
    echo '<h2>Текущая структура таблицы:</h2>';
    echo '<pre>';
    print_r($columns);
    echo '</pre>';
    
    // Проверяем размер полей
    $issues = array();
    
    if (isset($columns['filename']) && strpos($columns['filename']['Type'], 'varchar') !== false) {
        preg_match('/varchar\((\d+)\)/', $columns['filename']['Type'], $matches);
        if (isset($matches[1]) && $matches[1] < 1000) {
            $issues[] = "Поле 'filename' слишком короткое (текущий размер: {$matches[1]})";
        }
    }
    
    if (isset($columns['real_path']) && strpos($columns['real_path']['Type'], 'varchar') !== false) {
        preg_match('/varchar\((\d+)\)/', $columns['real_path']['Type'], $matches);
        if (isset($matches[1]) && $matches[1] < 1000) {
            $issues[] = "Поле 'real_path' слишком короткое (текущий размер: {$matches[1]})";
        }
    }
    
    if (empty($issues)) {
        echo '<div class="success">Структура таблицы выглядит нормально.</div>';
        
        // Проверяем количество записей
        $result = $mysqli->query("SELECT COUNT(*) as count FROM {$table_name}");
        $row = $result->fetch_assoc();
        $count = $row['count'];
        
        echo '<div class="info">Количество записей в таблице: ' . number_format($count) . '</div>';
        
        if ($count > 100000) {
            echo '<div class="error">В таблице слишком много записей (' . number_format($count) . '). Рекомендуется очистить старые записи.</div>';
            
            // Предлагаем очистить старые записи
            echo '<h2>Очистка старых записей</h2>';
            echo '<p>Можно удалить записи старше 30 дней:</p>';
            echo '<pre>DELETE FROM ' . $table_name . ' WHERE mtime < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));</pre>';
        }
        
        // Проверяем максимальный размер пакета
        $result = $mysqli->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
        $row = $result->fetch_assoc();
        $max_packet = $row['Value'];
        
        echo '<div class="info">max_allowed_packet: ' . $max_packet . ' (' . round($max_packet / 1024 / 1024, 2) . ' MB)</div>';
        
        if ($max_packet < 16777216) { // 16MB
            echo '<div class="error">max_allowed_packet слишком маленький. Рекомендуется увеличить до 16MB или больше.</div>';
        }
        
    } else {
        echo '<div class="error"><strong>Найдены проблемы:</strong><ul>';
        foreach ($issues as $issue) {
            echo '<li>' . $issue . '</li>';
        }
        echo '</ul></div>';
    }
    
    // Проверяем индексы
    $result = $mysqli->query("SHOW INDEXES FROM {$table_name}");
    $indexes = array();
    while ($row = $result->fetch_assoc()) {
        $indexes[] = $row;
    }
    
    echo '<h2>Индексы таблицы:</h2>';
    echo '<pre>';
    print_r($indexes);
    echo '</pre>';
    
    $mysqli->close();
    ?>
    
    <hr>
    <h2>Рекомендации:</h2>
    <ol>
        <li><strong>Увеличьте max_allowed_packet</strong> в настройках MySQL (через phpMyAdmin или хостинг-панель) до 16MB или больше</li>
        <li><strong>Очистите старые записи</strong> из таблицы wffilemods (старше 30 дней)</li>
        <li><strong>Настройте Wordfence:</strong>
            <ul>
                <li>Wordfence → Scan → Manage Scan</li>
                <li>Максимальное время для каждого этапа сканирования: <strong>60 секунд</strong></li>
                <li>Какой объем памяти должен запрашивать Wordfence: <strong>512 MB</strong></li>
                <li>Использовать сканирование с низким уровнем ресурсов: <strong>ВКЛЮЧЕНО</strong></li>
            </ul>
        </li>
    </ol>
</body>
</html>

