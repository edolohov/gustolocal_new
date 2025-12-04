<?php
/**
 * Тестовый скрипт для поиска и настройки error_log
 * 
 * ИСПОЛЬЗОВАНИЕ:
 * 1. Загрузите в корень WordPress через SFTP
 * 2. Откройте: https://gustolocal.es/test-error-log.php
 * 3. Скрипт покажет, где находятся/создаются логи
 * 4. УДАЛИТЕ файл после использования!
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Поиск и настройка error_log</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
        .error-box { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Поиск и настройка error_log</h1>
        <p><strong>Время проверки:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        
        <?php
        // Текущие настройки PHP
        echo '<h2>1. Текущие настройки PHP</h2>';
        echo '<table>';
        echo '<tr><th>Параметр</th><th>Значение</th></tr>';
        echo '<tr><td>log_errors</td><td>' . (ini_get('log_errors') ? '✅ Включено' : '❌ Выключено') . '</td></tr>';
        echo '<tr><td>error_log</td><td><code>' . htmlspecialchars(ini_get('error_log') ?: 'Не настроен') . '</code></td></tr>';
        echo '<tr><td>error_reporting</td><td>' . ini_get('error_reporting') . '</td></tr>';
        echo '<tr><td>display_errors</td><td>' . (ini_get('display_errors') ? '✅ Включено' : '❌ Выключено') . '</td></tr>';
        echo '<tr><td>max_execution_time</td><td>' . ini_get('max_execution_time') . ' сек</td></tr>';
        echo '</table>';
        
        // Попытка найти существующие логи
        echo '<h2>2. Поиск существующих логов</h2>';
        $search_paths = array(
            'Корень сайта' => __DIR__ . '/error_log',
            'Папка logs в корне' => __DIR__ . '/logs/error_log',
            'Родительская папка' => dirname(__DIR__) . '/error_log',
            'Папка logs в родительской' => dirname(__DIR__) . '/logs/error_log',
            'Системная временная папка' => sys_get_temp_dir() . '/php_errors.log',
            'Текущая настройка error_log' => ini_get('error_log') ?: 'Не настроен',
        );
        
        echo '<table>';
        echo '<tr><th>Путь</th><th>Статус</th><th>Размер</th><th>Последнее изменение</th></tr>';
        $found_logs = array();
        
        foreach ($search_paths as $name => $path) {
            if ($path === 'Не настроен') {
                echo "<tr><td><code>$name</code></td><td>-</td><td>-</td><td>-</td></tr>";
                continue;
            }
            
            $exists = file_exists($path);
            $readable = $exists && is_readable($path);
            
            if ($exists) {
                $size = filesize($path);
                $mtime = filemtime($path);
                $size_str = $size > 1024 * 1024 ? round($size / 1024 / 1024, 2) . ' MB' : round($size / 1024, 2) . ' KB';
                $date_str = date('Y-m-d H:i:s', $mtime);
                $status = $readable ? '<span class="success">✅ Найден</span>' : '<span class="error">❌ Нечитаемый</span>';
                $found_logs[] = $path;
            } else {
                $size_str = '-';
                $date_str = '-';
                $status = '<span class="warning">❌ Не найден</span>';
            }
            
            echo "<tr>";
            echo "<td><code>" . htmlspecialchars($path) . "</code><br><small>$name</small></td>";
            echo "<td>$status</td>";
            echo "<td>$size_str</td>";
            echo "<td>$date_str</td>";
            echo "</tr>";
        }
        echo '</table>';
        
        // Показываем содержимое найденных логов
        if (!empty($found_logs)) {
            echo '<h2>3. Содержимое найденных логов (последние 50 строк)</h2>';
            foreach ($found_logs as $log_path) {
                if (is_readable($log_path)) {
                    echo '<h3>' . htmlspecialchars($log_path) . '</h3>';
                    $lines = file($log_path);
                    $last_lines = array_slice($lines, -50);
                    echo '<pre>' . htmlspecialchars(implode('', $last_lines)) . '</pre>';
                }
            }
        } else {
            echo '<div class="info">';
            echo '<h3>Логи не найдены</h3>';
            echo '<p>Файлы error_log не найдены в стандартных местах. Это нормально, если:</p>';
            echo '<ul>';
            echo '<li>Ошибок еще не было</li>';
            echo '<li>Логирование не настроено</li>';
            echo '<li>Логи находятся в другом месте (проверьте панель хостинга)</li>';
            echo '</ul>';
            echo '</div>';
        }
        
        // Попытка создать error_log
        echo '<h2>4. Создание тестового error_log</h2>';
        $test_log_path = __DIR__ . '/error_log';
        
        // Настраиваем логирование
        ini_set('log_errors', 1);
        ini_set('error_log', $test_log_path);
        ini_set('error_reporting', E_ALL);
        
        if (!file_exists($test_log_path)) {
            if (touch($test_log_path)) {
                chmod($test_log_path, 0644);
                echo '<p class="success">✅ Файл error_log создан: <code>' . htmlspecialchars($test_log_path) . '</code></p>';
            } else {
                echo '<p class="error">❌ Не удалось создать файл error_log</p>';
                echo '<p>Возможные причины:</p>';
                echo '<ul>';
                echo '<li>Нет прав на запись в папку</li>';
                echo '<li>Папка защищена от записи</li>';
                echo '</ul>';
                echo '<p>Попробуйте создать файл вручную через SFTP с правами 644</p>';
            }
        } else {
            echo '<p class="success">✅ Файл error_log уже существует</p>';
        }
        
        // Генерируем тестовую ошибку
        echo '<h2>5. Генерация тестовой ошибки</h2>';
        $test_message = "ТЕСТОВАЯ ОШИБКА для проверки логирования - " . date('Y-m-d H:i:s');
        trigger_error($test_message, E_USER_WARNING);
        
        echo '<p>✅ Тестовая ошибка сгенерирована</p>';
        echo '<p>Сообщение: <code>' . htmlspecialchars($test_message) . '</code></p>';
        
        // Проверяем, записалось ли в лог
        sleep(1); // Небольшая задержка для записи
        if (file_exists($test_log_path) && filesize($test_log_path) > 0) {
            echo '<p class="success">✅ Ошибка записана в error_log!</p>';
            echo '<h3>Последние строки error_log:</h3>';
            $lines = file($test_log_path);
            $last_lines = array_slice($lines, -10);
            echo '<pre>' . htmlspecialchars(implode('', $last_lines)) . '</pre>';
        } else {
            echo '<p class="warning">⚠️ Ошибка не записалась в error_log</p>';
            echo '<p>Возможные причины:</p>';
            echo '<ul>';
            echo '<li>Логирование отключено на уровне сервера</li>';
            echo '<li>Логи пишутся в другое место (проверьте настройки PHP)</li>';
            echo '<li>Нет прав на запись</li>';
            echo '</ul>';
        }
        
        // Рекомендации
        echo '<h2>6. Рекомендации</h2>';
        echo '<div class="info">';
        echo '<h3>Что делать дальше:</h3>';
        echo '<ol>';
        echo '<li><strong>Проверьте панель хостинга</strong> - раздел "Логи" или "Error Logs"</li>';
        echo '<li><strong>Добавьте в wp-config.php</strong> (в самое начало, после &lt;?php):</li>';
        echo '</ol>';
        echo '<pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd;">';
        echo htmlspecialchars('ini_set(\'log_errors\', 1);
ini_set(\'error_log\', \'/home/s1149026/gustolocal.es/error_log\');
ini_set(\'error_reporting\', E_ALL);
ini_set(\'display_errors\', 0);');
        echo '</pre>';
        echo '<li><strong>Создайте файл error_log вручную</strong> через SFTP в корне сайта с правами 644</li>';
        echo '<li><strong>Попробуйте открыть сайт</strong> - файл должен создаться автоматически при ошибке</li>';
        echo '</div>';
        ?>
        
        <div class="error-box">
            <h3>⚠️ ВАЖНО: Удалите этот файл после использования!</h3>
            <p>Этот файл содержит диагностическую информацию и должен быть удален из соображений безопасности.</p>
        </div>
    </div>
</body>
</html>




