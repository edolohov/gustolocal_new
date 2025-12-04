<?php
/**
 * Диагностический скрипт для WordPress
 * 
 * ИСПОЛЬЗОВАНИЕ:
 * 1. Загрузите этот файл в корень WordPress
 * 2. Откройте в браузере: ваш-сайт.com/diagnostic-script.php
 * 3. УДАЛИТЕ файл после использования (безопасность!)
 * 
 * ВНИМАНИЕ: Этот файл содержит чувствительную информацию!
 * НЕ оставляйте его на сервере после диагностики!
 */

// Безопасность: проверка, что файл запускается напрямую
if (!defined('ABSPATH')) {
    // Попытка загрузить WordPress
    if (file_exists('./wp-load.php')) {
        define('WP_USE_THEMES', false);
        require_once('./wp-load.php');
    } else {
        die('Файл wp-load.php не найден. Убедитесь, что скрипт находится в корне WordPress.');
    }
}

// Простая защита от несанкционированного доступа (замените на свой пароль)
$DIAGNOSTIC_PASSWORD = 'CHANGE_THIS_PASSWORD'; // ИЗМЕНИТЕ ПЕРЕД ИСПОЛЬЗОВАНИЕМ!

if (!isset($_GET['key']) || $_GET['key'] !== $DIAGNOSTIC_PASSWORD) {
    die('Неверный ключ доступа. Добавьте ?key=YOUR_PASSWORD к URL.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Диагностика WordPress</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .success { color: green; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
        .error-box { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; border: 1px solid #ddd; }
        .plugin-list { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Диагностика WordPress</h1>
        <p><strong>Время проверки:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        
        <?php
        // Проверка базовой информации
        echo '<h2>1. Базовая информация</h2>';
        echo '<table>';
        echo '<tr><th>Параметр</th><th>Значение</th></tr>';
        echo '<tr><td>Версия WordPress</td><td>' . (function_exists('get_bloginfo') ? get_bloginfo('version') : 'Не загружена') . '</td></tr>';
        echo '<tr><td>Версия PHP</td><td>' . phpversion() . '</td></tr>';
        echo '<tr><td>Версия MySQL</td><td>' . (function_exists('mysql_get_server_info') ? mysql_get_server_info() : 'N/A') . '</td></tr>';
        echo '<tr><td>Лимит памяти PHP</td><td>' . ini_get('memory_limit') . '</td></tr>';
        echo '<tr><td>Максимальное время выполнения</td><td>' . ini_get('max_execution_time') . ' сек</td></tr>';
        echo '<tr><td>Размер загрузки файлов</td><td>' . ini_get('upload_max_filesize') . '</td></tr>';
        echo '<tr><td>WP_DEBUG</td><td>' . (defined('WP_DEBUG') && WP_DEBUG ? '<span class="success">Включен</span>' : '<span class="error">Выключен</span>') . '</td></tr>';
        echo '<tr><td>WP_DEBUG_LOG</td><td>' . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? '<span class="success">Включен</span>' : '<span class="error">Выключен</span>') . '</td></tr>';
        echo '</table>';

        // Проверка путей к логам
        echo '<h2>2. Пути к логам</h2>';
        $log_paths = array(
            'debug.log (WordPress)' => ABSPATH . 'wp-content/debug.log',
            'error_log (PHP)' => ABSPATH . 'error_log',
            'error_log (в корне)' => dirname(ABSPATH) . '/error_log',
        );
        
        echo '<table>';
        echo '<tr><th>Лог</th><th>Путь</th><th>Статус</th><th>Размер</th></tr>';
        foreach ($log_paths as $name => $path) {
            $exists = file_exists($path);
            $size = $exists ? filesize($path) : 0;
            $status = $exists ? '<span class="success">Существует</span>' : '<span class="error">Не найден</span>';
            $size_str = $exists ? ($size > 1024 * 1024 ? round($size / 1024 / 1024, 2) . ' MB' : round($size / 1024, 2) . ' KB') : '-';
            echo "<tr><td>$name</td><td><code>$path</code></td><td>$status</td><td>$size_str</td></tr>";
        }
        echo '</table>';

        // Проверка последних ошибок
        echo '<h2>3. Последние ошибки из debug.log</h2>';
        $debug_log = ABSPATH . 'wp-content/debug.log';
        if (file_exists($debug_log)) {
            $lines = file($debug_log);
            $last_lines = array_slice($lines, -50); // Последние 50 строк
            if (!empty($last_lines)) {
                echo '<div class="error-box">';
                echo '<pre>' . htmlspecialchars(implode('', $last_lines)) . '</pre>';
                echo '</div>';
            } else {
                echo '<p class="success">Лог пуст (нет ошибок)</p>';
            }
        } else {
            echo '<p class="warning">Файл debug.log не найден. Включите WP_DEBUG_LOG в wp-config.php</p>';
        }

        // Проверка активных плагинов
        if (function_exists('get_option')) {
            echo '<h2>4. Активные плагины</h2>';
            $active_plugins = get_option('active_plugins', array());
            if (!empty($active_plugins)) {
                echo '<div class="plugin-list">';
                echo '<table>';
                echo '<tr><th>Плагин</th><th>Версия</th><th>Статус</th></tr>';
                foreach ($active_plugins as $plugin) {
                    $plugin_path = ABSPATH . 'wp-content/plugins/' . $plugin;
                    $exists = file_exists($plugin_path);
                    $status = $exists ? '<span class="success">Активен</span>' : '<span class="error">Файл не найден!</span>';
                    
                    // Попытка получить версию плагина
                    $version = 'N/A';
                    if ($exists) {
                        $plugin_data = get_file_data($plugin_path, array('Version' => 'Version'));
                        if (!empty($plugin_data['Version'])) {
                            $version = $plugin_data['Version'];
                        }
                    }
                    
                    echo "<tr><td><code>$plugin</code></td><td>$version</td><td>$status</td></tr>";
                }
                echo '</table>';
                echo '</div>';
            } else {
                echo '<p class="info">Нет активных плагинов</p>';
            }
        }

        // Проверка использования памяти
        echo '<h2>5. Использование памяти</h2>';
        $memory_usage = memory_get_usage(true);
        $memory_peak = memory_get_peak_usage(true);
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = return_bytes($memory_limit);
        
        $usage_percent = ($memory_usage / $memory_limit_bytes) * 100;
        $peak_percent = ($memory_peak / $memory_limit_bytes) * 100;
        
        echo '<table>';
        echo '<tr><th>Параметр</th><th>Значение</th><th>Процент</th></tr>';
        echo '<tr><td>Текущее использование</td><td>' . format_bytes($memory_usage) . '</td><td>' . round($usage_percent, 2) . '%</td></tr>';
        echo '<tr><td>Пиковое использование</td><td>' . format_bytes($memory_peak) . '</td><td>' . round($peak_percent, 2) . '%</td></tr>';
        echo '<tr><td>Лимит</td><td>' . $memory_limit . '</td><td>100%</td></tr>';
        echo '</table>';
        
        if ($peak_percent > 80) {
            echo '<div class="error-box"><strong>Внимание:</strong> Использование памяти близко к лимиту!</div>';
        }

        // Проверка подключения к БД
        echo '<h2>6. Подключение к базе данных</h2>';
        global $wpdb;
        if ($wpdb) {
            echo '<p class="success">Подключение к БД установлено</p>';
            echo '<table>';
            echo '<tr><th>Параметр</th><th>Значение</th></tr>';
            echo '<tr><td>Имя БД</td><td>' . DB_NAME . '</td></tr>';
            echo '<tr><td>Хост</td><td>' . DB_HOST . '</td></tr>';
            echo '<tr><td>Пользователь</td><td>' . DB_USER . '</td></tr>';
            echo '<tr><td>Префикс таблиц</td><td>' . $wpdb->prefix . '</td></tr>';
            
            // Проверка количества запросов
            if (defined('SAVEQUERIES') && SAVEQUERIES) {
                echo '<tr><td>Количество запросов</td><td>' . count($wpdb->queries) . '</td></tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">Не удалось подключиться к БД</p>';
        }

        // Рекомендации
        echo '<h2>7. Рекомендации</h2>';
        echo '<div class="info">';
        echo '<ul>';
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            echo '<li><strong>Включите WP_DEBUG</strong> в wp-config.php для детального логирования</li>';
        }
        if ($peak_percent > 80) {
            echo '<li><strong>Увеличьте лимит памяти</strong> в wp-config.php: <code>define(\'WP_MEMORY_LIMIT\', \'256M\');</code></li>';
        }
        if (count($active_plugins) > 20) {
            echo '<li><strong>Слишком много плагинов</strong> (' . count($active_plugins) . '). Проверьте, все ли нужны.</li>';
        }
        echo '<li><strong>Проверьте логи</strong> на наличие ошибок выше</li>';
        echo '<li><strong>Отключите плагины по одному</strong>, чтобы найти проблемный</li>';
        echo '</ul>';
        echo '</div>';

        // ВАЖНОЕ ПРЕДУПРЕЖДЕНИЕ
        echo '<div class="error-box">';
        echo '<h3>⚠️ ВАЖНО: Удалите этот файл после диагностики!</h3>';
        echo '<p>Этот файл содержит чувствительную информацию и должен быть удален сразу после использования.</p>';
        echo '</div>';
        ?>

    </div>
</body>
</html>

<?php
// Вспомогательные функции
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>




