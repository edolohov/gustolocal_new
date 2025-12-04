<?php
/**
 * Скрипт для поиска бэкдоров, которые воссоздают зараженные файлы
 * 
 * ИСПОЛЬЗОВАНИЕ:
 * 1. Загрузите этот файл в корень WordPress: /home/s1149026/gustolocal.es/
 * 2. Откройте в браузере: https://gustolocal.es/find-backdoor-script.php?key=YOUR_PASSWORD
 * 3. УДАЛИТЕ файл после использования!
 * 
 * ВНИМАНИЕ: Замените YOUR_PASSWORD на свой пароль!
 */

// Защита от несанкционированного доступа
$ACCESS_KEY = 'CHANGE_THIS_PASSWORD'; // ИЗМЕНИТЕ ПЕРЕД ИСПОЛЬЗОВАНИЕM!

if (!isset($_GET['key']) || $_GET['key'] !== $ACCESS_KEY) {
    die('Неверный ключ доступа. Добавьте ?key=YOUR_PASSWORD к URL.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Поиск бэкдоров</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #d32f2f; }
        h2 { color: #1976d2; margin-top: 30px; }
        .danger { background: #ffebee; border-left: 4px solid #d32f2f; padding: 15px; margin: 10px 0; }
        .warning { background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 10px 0; }
        .success { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .file-list { background: #fafafa; padding: 10px; margin: 5px 0; border-radius: 4px; }
        .suspicious { color: #d32f2f; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Поиск бэкдоров, воссоздающих зараженные файлы</h1>
    
    <?php
    $root = dirname(__FILE__);
    $found_backdoors = array();
    $suspicious_files = array();
    
    // 1. Поиск файлов с подозрительными функциями
    echo '<h2>1. Поиск файлов с подозрительными функциями</h2>';
    
    $suspicious_patterns = array(
        'mkdir.*cgi-bin' => 'Создает папку cgi-bin',
        'file_put_contents.*cgi-bin' => 'Записывает файлы в cgi-bin',
        'fwrite.*cgi-bin' => 'Записывает в cgi-bin',
        'copy.*cgi-bin' => 'Копирует в cgi-bin',
        'move_uploaded_file.*cgi-bin' => 'Перемещает в cgi-bin',
        'exec.*cgi-bin' => 'Выполняет команды с cgi-bin',
        'system.*cgi-bin' => 'Выполняет системные команды с cgi-bin',
        'shell_exec.*cgi-bin' => 'Выполняет shell команды с cgi-bin',
        'eval.*cgi-bin' => 'Выполняет eval с cgi-bin',
        'base64_decode.*cgi-bin' => 'Декодирует base64 для cgi-bin',
        'gzinflate.*cgi-bin' => 'Распаковывает для cgi-bin',
        'str_rot13.*cgi-bin' => 'ROT13 декодирование для cgi-bin',
        'preg_replace.*\/e.*cgi-bin' => 'preg_replace с /e для cgi-bin',
        'create_function.*cgi-bin' => 'Создает функцию для cgi-bin',
    );
    
    function scanDirectory($dir, $patterns, &$found, $maxDepth = 10, $currentDepth = 0) {
        if ($currentDepth >= $maxDepth) return;
        if (!is_dir($dir)) return;
        
        $files = @scandir($dir);
        if (!$files) return;
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            
            $path = $dir . '/' . $file;
            
            // Пропускаем некоторые папки
            if (is_dir($path)) {
                if (in_array($file, array('node_modules', '.git', 'vendor', 'wp-content/cache', 'wp-content/uploads'))) {
                    continue;
                }
                scanDirectory($path, $patterns, $found, $maxDepth, $currentDepth + 1);
                continue;
            }
            
            // Проверяем только PHP файлы
            if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') continue;
            
            // Читаем файл
            $content = @file_get_contents($path);
            if (!$content) continue;
            
            // Проверяем паттерны
            foreach ($patterns as $pattern => $description) {
                if (preg_match('/' . $pattern . '/i', $content)) {
                    $found[] = array(
                        'file' => $path,
                        'pattern' => $pattern,
                        'description' => $description,
                        'size' => filesize($path),
                        'modified' => date('Y-m-d H:i:s', filemtime($path))
                    );
                }
            }
        }
    }
    
    scanDirectory($root, $suspicious_patterns, $found_backdoors);
    
    if (empty($found_backdoors)) {
        echo '<div class="success">✅ Не найдено файлов с явными признаками создания cgi-bin</div>';
    } else {
        echo '<div class="danger">';
        echo '<p class="suspicious">🚨 НАЙДЕНО ' . count($found_backdoors) . ' ПОДОЗРИТЕЛЬНЫХ ФАЙЛОВ:</p>';
        foreach ($found_backdoors as $item) {
            echo '<div class="file-list">';
            echo '<strong>Файл:</strong> ' . htmlspecialchars($item['file']) . '<br>';
            echo '<strong>Причина:</strong> ' . htmlspecialchars($item['description']) . '<br>';
            echo '<strong>Размер:</strong> ' . $item['size'] . ' байт<br>';
            echo '<strong>Изменен:</strong> ' . $item['modified'] . '<br>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    // 2. Поиск файлов с подозрительными именами
    echo '<h2>2. Поиск файлов с подозрительными именами</h2>';
    
    $suspicious_names = array(
        'postnews.php',
        'eDE9CW.php',
        'cache.php',
        'index.php' // в нестандартных местах
    );
    
    function findSuspiciousFiles($dir, $names, &$found, $maxDepth = 10, $currentDepth = 0) {
        if ($currentDepth >= $maxDepth) return;
        if (!is_dir($dir)) return;
        
        $files = @scandir($dir);
        if (!$files) return;
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                findSuspiciousFiles($path, $names, $found, $maxDepth, $currentDepth + 1);
                continue;
            }
            
            foreach ($names as $suspicious_name) {
                if (basename($path) === $suspicious_name) {
                    // Проверяем, не в стандартных ли местах
                    $is_standard = false;
                    if ($suspicious_name === 'index.php') {
                        // index.php может быть в корне, wp-admin, wp-content/themes и т.д.
                        if (strpos($path, '/wp-admin/') !== false && 
                            strpos($path, '/css/css/') === false &&
                            strpos($path, '/cgi-bin/') === false) {
                            $is_standard = true;
                        }
                        if (strpos($path, '/wp-content/themes/') !== false) {
                            $is_standard = true;
                        }
                        if (basename(dirname($path)) === 'gustolocal.es' && $suspicious_name === 'index.php') {
                            // Корневой index.php - нужно проверить содержимое
                            $is_standard = true;
                        }
                    }
                    
                    if (!$is_standard) {
                        $found[] = array(
                            'file' => $path,
                            'size' => filesize($path),
                            'modified' => date('Y-m-d H:i:s', filemtime($path)),
                            'content_preview' => substr(@file_get_contents($path), 0, 200)
                        );
                    }
                }
            }
        }
    }
    
    findSuspiciousFiles($root, $suspicious_names, $suspicious_files);
    
    if (empty($suspicious_files)) {
        echo '<div class="success">✅ Не найдено файлов с подозрительными именами в нестандартных местах</div>';
    } else {
        echo '<div class="warning">';
        echo '<p>⚠️ НАЙДЕНО ' . count($suspicious_files) . ' ФАЙЛОВ С ПОДОЗРИТЕЛЬНЫМИ ИМЕНАМИ:</p>';
        foreach ($suspicious_files as $item) {
            echo '<div class="file-list">';
            echo '<strong>Файл:</strong> ' . htmlspecialchars($item['file']) . '<br>';
            echo '<strong>Размер:</strong> ' . $item['size'] . ' байт<br>';
            echo '<strong>Изменен:</strong> ' . $item['modified'] . '<br>';
            echo '<strong>Начало файла:</strong><br>';
            echo '<pre>' . htmlspecialchars($item['content_preview']) . '</pre>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    // 3. Проверка cron задач
    echo '<h2>3. Проверка cron задач</h2>';
    
    if (function_exists('shell_exec')) {
        $cron = @shell_exec('crontab -l 2>&1');
        if ($cron && strpos($cron, 'cgi-bin') !== false) {
            echo '<div class="danger">';
            echo '<p class="suspicious">🚨 НАЙДЕНЫ ПОДОЗРИТЕЛЬНЫЕ CRON ЗАДАЧИ:</p>';
            echo '<pre>' . htmlspecialchars($cron) . '</pre>';
            echo '</div>';
        } else {
            echo '<div class="success">✅ Подозрительных cron задач не найдено (или нет доступа к shell_exec)</div>';
        }
    } else {
        echo '<div class="warning">⚠️ Функция shell_exec недоступна. Проверьте cron задачи вручную через панель хостинга.</div>';
    }
    
    // 4. Проверка .htaccess файлов
    echo '<h2>4. Проверка .htaccess файлов</h2>';
    
    function findHtaccessFiles($dir, &$found, $maxDepth = 5, $currentDepth = 0) {
        if ($currentDepth >= $maxDepth) return;
        if (!is_dir($dir)) return;
        
        $htaccess = $dir . '/.htaccess';
        if (file_exists($htaccess)) {
            $content = file_get_contents($htaccess);
            if (strpos($content, 'cgi-bin') !== false || 
                strpos($content, 'eval') !== false ||
                strpos($content, 'base64') !== false) {
                $found[] = array(
                    'file' => $htaccess,
                    'content' => $content
                );
            }
        }
        
        $files = @scandir($dir);
        if (!$files) return;
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..' || $file == '.htaccess') continue;
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                findHtaccessFiles($path, $found, $maxDepth, $currentDepth + 1);
            }
        }
    }
    
    $htaccess_files = array();
    findHtaccessFiles($root, $htaccess_files);
    
    if (empty($htaccess_files)) {
        echo '<div class="success">✅ Подозрительных .htaccess файлов не найдено</div>';
    } else {
        echo '<div class="warning">';
        echo '<p>⚠️ НАЙДЕНО ' . count($htaccess_files) . ' ПОДОЗРИТЕЛЬНЫХ .htaccess ФАЙЛОВ:</p>';
        foreach ($htaccess_files as $item) {
            echo '<div class="file-list">';
            echo '<strong>Файл:</strong> ' . htmlspecialchars($item['file']) . '<br>';
            echo '<strong>Содержимое:</strong><br>';
            echo '<pre>' . htmlspecialchars($item['content']) . '</pre>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    // 5. Проверка недавно измененных PHP файлов
    echo '<h2>5. Недавно измененные PHP файлы (последние 7 дней)</h2>';
    
    $recent_files = array();
    function findRecentFiles($dir, &$found, $days = 7, $maxDepth = 10, $currentDepth = 0) {
        if ($currentDepth >= $maxDepth) return;
        if (!is_dir($dir)) return;
        
        $files = @scandir($dir);
        if (!$files) return;
        
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                // Пропускаем некоторые папки
                if (in_array($file, array('node_modules', '.git', 'vendor', 'wp-content/cache', 'wp-content/uploads'))) {
                    continue;
                }
                findRecentFiles($path, $found, $days, $maxDepth, $currentDepth + 1);
                continue;
            }
            
            if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') continue;
            
            $mtime = filemtime($path);
            if ($mtime > $cutoff) {
                $found[] = array(
                    'file' => $path,
                    'modified' => date('Y-m-d H:i:s', $mtime),
                    'size' => filesize($path)
                );
            }
        }
    }
    
    findRecentFiles($root, $recent_files);
    
    if (empty($recent_files)) {
        echo '<div class="success">✅ Не найдено недавно измененных PHP файлов</div>';
    } else {
        echo '<div class="warning">';
        echo '<p>⚠️ НАЙДЕНО ' . count($recent_files) . ' НЕДАВНО ИЗМЕНЕННЫХ PHP ФАЙЛОВ (последние 7 дней):</p>';
        // Сортируем по дате изменения
        usort($recent_files, function($a, $b) {
            return strcmp($b['modified'], $a['modified']);
        });
        foreach (array_slice($recent_files, 0, 50) as $item) { // Показываем только первые 50
            echo '<div class="file-list">';
            echo '<strong>Файл:</strong> ' . htmlspecialchars($item['file']) . '<br>';
            echo '<strong>Изменен:</strong> ' . $item['modified'] . '<br>';
            echo '<strong>Размер:</strong> ' . $item['size'] . ' байт<br>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    ?>
    
    <h2>📋 Рекомендации</h2>
    <div class="warning">
        <p><strong>Если найдены подозрительные файлы:</strong></p>
        <ol>
            <li>Сделайте бэкап перед удалением</li>
            <li>Удалите все найденные подозрительные файлы</li>
            <li>Проверьте cron задачи в панели хостинга</li>
            <li>Проверьте права доступа к файлам (должны быть 644 для файлов, 755 для папок)</li>
            <li>Смените все пароли (WordPress, FTP, база данных)</li>
            <li>Установите/обновите Wordfence и запустите полное сканирование</li>
        </ol>
    </div>
    
    <div class="danger">
        <p><strong>⚠️ ВАЖНО:</strong> После использования УДАЛИТЕ этот скрипт с сервера!</p>
    </div>
</div>
</body>
</html>

