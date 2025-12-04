<?php
/**
 * УПРОЩЕННЫЙ СКРИПТ ПОИСКА БЭКДОРОВ
 * 
 * ИСПОЛЬЗОВАНИЕ:
 * 1. Переименуйте этот файл в одно из имен из белого списка .htaccess
 *    Например: admin-ajax.php (если файл не существует)
 * 2. Загрузите в корень WordPress
 * 3. Откройте в браузере с паролем: ?key=CHANGE_THIS_PASSWORD
 * 4. УДАЛИТЕ файл после использования!
 */

// Защита от несанкционированного доступа
$ACCESS_KEY = 'CHANGE_THIS_PASSWORD';

if (!isset($_GET['key']) || $_GET['key'] !== $ACCESS_KEY) {
    die('Неверный ключ доступа. Добавьте ?key=CHANGE_THIS_PASSWORD к URL.');
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
        .file-item { margin: 5px 0; padding: 5px; border-left: 3px solid #ddd; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Поиск бэкдоров и вредоносных файлов</h1>
    
    <?php
    $root = dirname(__FILE__);
    $found_files = array();
    
    // Известные вредоносные файлы
    $known_malware = array(
        'postnews.php',
        'eDE9CW.php',
        'cgi-bin',
        'images/images',
        'wp-admin/css/css',
    );
    
    // Подозрительные функции
    $suspicious_functions = array(
        'eval(',
        'base64_decode(',
        'gzinflate(',
        'str_rot13(',
        'exec(',
        'system(',
        'shell_exec(',
        'passthru(',
        'file_get_contents(\'http',
        'curl_exec',
        'fopen(\'http',
        'mkdir.*cgi-bin',
        'file_put_contents.*cgi-bin',
    );
    
    echo '<h2>1. Поиск известных вредоносных файлов</h2>';
    
    // Функция рекурсивного поиска
    function findFiles($dir, $pattern, $results = array()) {
        if (!is_dir($dir)) return $results;
        
        $files = @scandir($dir);
        if ($files === false) return $results;
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            
            $path = $dir . '/' . $file;
            
            // Пропускаем большие файлы
            if (is_file($path) && filesize($path) > 5 * 1024 * 1024) continue;
            
            if (is_dir($path)) {
                // Проверяем имя папки
                foreach ($pattern as $p) {
                    if (stripos($file, $p) !== false) {
                        $results[] = $path;
                        break;
                    }
                }
                // Рекурсивный поиск (ограничение глубины)
                if (substr_count($path, '/') - substr_count($root, '/') < 10) {
                    $results = findFiles($path, $pattern, $results);
                }
            } else {
                // Проверяем имя файла
                foreach ($pattern as $p) {
                    if (stripos($file, $p) !== false) {
                        $results[] = $path;
                        break;
                    }
                }
            }
        }
        
        return $results;
    }
    
    // Поиск известных вредоносных файлов
    $malware_files = findFiles($root, $known_malware);
    
    if (count($malware_files) > 0) {
        echo '<div class="danger">';
        echo '<strong>Найдено ' . count($malware_files) . ' подозрительных файлов/папок:</strong><br>';
        foreach ($malware_files as $file) {
            $relative = str_replace($root . '/', '', $file);
            echo '<div class="file-item">' . htmlspecialchars($relative) . '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="success">Известные вредоносные файлы не найдены.</div>';
    }
    
    echo '<h2>2. Поиск файлов с подозрительным кодом</h2>';
    
    // Поиск PHP файлов с подозрительным кодом
    $suspicious_files = array();
    $checked = 0;
    $max_files = 500; // Ограничение для производительности
    
    function scanForSuspicious($dir, $suspicious_functions, $results = array(), $checked = 0, $max_files = 500) {
        if ($checked >= $max_files) return array($results, $checked);
        if (!is_dir($dir)) return array($results, $checked);
        
        $files = @scandir($dir);
        if ($files === false) return array($results, $checked);
        
        foreach ($files as $file) {
            if ($checked >= $max_files) break;
            if ($file == '.' || $file == '..') continue;
            
            $path = $dir . '/' . $file;
            
            if (is_file($path) && preg_match('/\.php$/i', $file)) {
                $checked++;
                if (filesize($path) > 1024 * 1024) continue; // Пропускаем файлы > 1MB
                
                $content = @file_get_contents($path);
                if ($content === false) continue;
                
                // Проверяем на подозрительные функции
                foreach ($suspicious_functions as $func) {
                    if (stripos($content, $func) !== false) {
                        $results[] = array(
                            'file' => $path,
                            'suspicious' => $func
                        );
                        break; // Нашли одно - достаточно
                    }
                }
            } elseif (is_dir($path)) {
                // Пропускаем некоторые папки
                if (in_array($file, array('node_modules', '.git', 'vendor'))) continue;
                if (substr_count($path, '/') - substr_count(dirname(__FILE__), '/') < 8) {
                    list($results, $checked) = scanForSuspicious($path, $suspicious_functions, $results, $checked, $max_files);
                }
            }
        }
        
        return array($results, $checked);
    }
    
    list($suspicious_files, $checked) = scanForSuspicious($root, $suspicious_functions, array(), 0, 500);
    
    if (count($suspicious_files) > 0) {
        echo '<div class="warning">';
        echo '<strong>Найдено ' . count($suspicious_files) . ' файлов с подозрительным кодом (проверено ' . $checked . ' файлов):</strong><br>';
        foreach ($suspicious_files as $item) {
            $relative = str_replace($root . '/', '', $item['file']);
            echo '<div class="file-item suspicious">';
            echo htmlspecialchars($relative) . ' <small>(найдено: ' . htmlspecialchars($item['suspicious']) . ')</small>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="success">Подозрительный код не найден (проверено ' . $checked . ' файлов).</div>';
    }
    
    echo '<h2>3. Проверка подозрительных папок</h2>';
    
    // Проверка вложенных папок (признак вредоносного ПО)
    $nested_folders = array();
    $suspicious_nested = array('images/images', 'css/css', 'library/library', 'textcolor/textcolor', 'SecretStream/SecretStream');
    
    foreach ($suspicious_nested as $pattern) {
        $parts = explode('/', $pattern);
        $search_path = $root;
        foreach ($parts as $part) {
            $search_path .= '/' . $part;
            if (is_dir($search_path)) {
                $nested_folders[] = $search_path;
            }
        }
    }
    
    if (count($nested_folders) > 0) {
        echo '<div class="danger">';
        echo '<strong>Найдены подозрительные вложенные папки:</strong><br>';
        foreach ($nested_folders as $folder) {
            $relative = str_replace($root . '/', '', $folder);
            echo '<div class="file-item">' . htmlspecialchars($relative) . '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="success">Подозрительные вложенные папки не найдены.</div>';
    }
    
    echo '<h2>4. Список файлов для удаления</h2>';
    echo '<div class="warning">';
    echo '<strong>Рекомендуется удалить следующие файлы через SFTP:</strong><br><br>';
    
    $to_delete = array_merge($malware_files, array_column($suspicious_files, 'file'));
    $to_delete = array_unique($to_delete);
    
    if (count($to_delete) > 0) {
        echo '<pre>';
        foreach ($to_delete as $file) {
            $relative = str_replace($root . '/', '', $file);
            echo htmlspecialchars($relative) . "\n";
        }
        echo '</pre>';
    } else {
        echo 'Файлы для удаления не найдены.';
    }
    echo '</div>';
    
    echo '<h2>5. Информация о системе</h2>';
    echo '<div class="success">';
    echo 'Корневая директория: ' . htmlspecialchars($root) . '<br>';
    echo 'PHP версия: ' . phpversion() . '<br>';
    echo 'Время выполнения: ' . date('Y-m-d H:i:s') . '<br>';
    echo '</div>';
    ?>
    
    <div class="warning" style="margin-top: 30px;">
        <strong>⚠️ ВАЖНО:</strong> После использования этого скрипта обязательно удалите его с сервера!
    </div>
</div>
</body>
</html>
