<?php
/**
 * Скрипт для проверки файлов Elementor на сервере
 * 
 * ИСПОЛЬЗОВАНИЕ:
 * 1. Загрузите в корень WordPress через SFTP
 * 2. Откройте: https://gustolocal.es/check-elementor-files.php
 * 3. Скрипт покажет, есть ли файлы Elementor и их содержимое
 * 4. УДАЛИТЕ файл после использования!
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Проверка файлов Elementor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
        .error-box { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; border: 1px solid #ddd; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Проверка файлов Elementor на сервере</h1>
        <p><strong>Время проверки:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        
        <?php
        $plugins_dir = __DIR__ . '/wp-content/plugins';
        $elementor_path = $plugins_dir . '/elementor';
        $animations_file = $elementor_path . '/assets/lib/animations/animations/index.php';
        
        // Проверка 1: Существует ли папка Elementor
        echo '<h2>1. Проверка наличия папки Elementor</h2>';
        if (is_dir($elementor_path)) {
            echo '<div class="error-box">';
            echo '<p class="error">❌ ПАПКА ELEMENTOR СУЩЕСТВУЕТ НА СЕРВЕРЕ!</p>';
            echo '<p>Путь: <code>' . htmlspecialchars($elementor_path) . '</code></p>';
            echo '<p><strong>ВАЖНО:</strong> Отключение плагина через админку НЕ удаляет файлы. Нужно удалить папку через SFTP!</p>';
            echo '</div>';
            
            // Показываем размер папки
            $size = 0;
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($elementor_path));
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
            echo '<p>Размер папки: ' . round($size / 1024 / 1024, 2) . ' MB</p>';
            
        } else {
            echo '<div class="info">';
            echo '<p class="success">✅ Папка Elementor не найдена (хорошо, если вы её удалили)</p>';
            echo '</div>';
        }
        
        // Проверка 2: Проверка файла animations/index.php
        echo '<h2>2. Проверка файла animations/index.php</h2>';
        if (file_exists($animations_file)) {
            echo '<div class="error-box">';
            echo '<p class="error">❌ ФАЙЛ animations/index.php СУЩЕСТВУЕТ!</p>';
            echo '<p>Путь: <code>' . htmlspecialchars($animations_file) . '</code></p>';
            echo '<p>Размер: ' . filesize($animations_file) . ' байт</p>';
            echo '<p>Последнее изменение: ' . date('Y-m-d H:i:s', filemtime($animations_file)) . '</p>';
            
            // Показываем содержимое файла
            $content = file_get_contents($animations_file);
            echo '<h3>Содержимое файла:</h3>';
            echo '<pre>' . htmlspecialchars($content) . '</pre>';
            
            // Проверка на подозрительный код
            $suspicious_patterns = array(
                'eval(' => 'Использование eval() - ОПАСНО!',
                'base64_decode' => 'Использование base64_decode - подозрительно',
                'exec(' => 'Использование exec() - ОПАСНО!',
                'system(' => 'Использование system() - ОПАСНО!',
                'shell_exec' => 'Использование shell_exec - ОПАСНО!',
                'passthru' => 'Использование passthru - ОПАСНО!',
                'preg_replace.*\/e' => 'Использование preg_replace с /e - ОПАСНО!',
            );
            
            $found_suspicious = false;
            foreach ($suspicious_patterns as $pattern => $message) {
                if (stripos($content, $pattern) !== false) {
                    echo '<div class="error-box">';
                    echo '<p class="error">⚠️ ' . $message . '</p>';
                    echo '</div>';
                    $found_suspicious = true;
                }
            }
            
            // Нормальное содержимое должно быть только "Silence is golden"
            $normal_content = '<?php' . "\n" . '// Silence is golden.';
            if (trim($content) !== trim($normal_content) && !$found_suspicious) {
                echo '<div class="warning">';
                echo '<p class="warning">⚠️ Содержимое файла отличается от нормального!</p>';
                echo '<p>Ожидается: <code>&lt;?php // Silence is golden.</code></p>';
                echo '</div>';
            }
            
            if (!$found_suspicious && trim($content) === trim($normal_content)) {
                echo '<p class="success">✅ Содержимое файла нормальное</p>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="info">';
            echo '<p class="success">✅ Файл animations/index.php не найден</p>';
            echo '</div>';
        }
        
        // Проверка 3: Проверка активных плагинов в БД
        echo '<h2>3. Проверка активных плагинов в базе данных</h2>';
        if (file_exists(__DIR__ . '/wp-load.php')) {
            define('WP_USE_THEMES', false);
            require_once(__DIR__ . '/wp-load.php');
            
            if (function_exists('get_option')) {
                $active_plugins = get_option('active_plugins', array());
                echo '<table>';
                echo '<tr><th>Активные плагины</th></tr>';
                
                $elementor_found = false;
                foreach ($active_plugins as $plugin) {
                    $is_elementor = (strpos($plugin, 'elementor') !== false);
                    if ($is_elementor) {
                        $elementor_found = true;
                        echo '<tr><td class="error">❌ ' . htmlspecialchars($plugin) . ' - АКТИВЕН!</td></tr>';
                    } else {
                        echo '<tr><td>' . htmlspecialchars($plugin) . '</td></tr>';
                    }
                }
                echo '</table>';
                
                if ($elementor_found) {
                    echo '<div class="error-box">';
                    echo '<p class="error">❌ Elementor все еще активен в базе данных!</p>';
                    echo '<p>Нужно отключить через админку или базу данных.</p>';
                    echo '</div>';
                } else {
                    echo '<p class="success">✅ Elementor не найден в активных плагинах</p>';
                }
            }
        } else {
            echo '<p class="warning">⚠️ Не удалось загрузить WordPress для проверки БД</p>';
        }
        
        // Проверка 4: Поиск других файлов Elementor
        echo '<h2>4. Поиск других файлов Elementor</h2>';
        $elementor_files = array();
        if (is_dir($elementor_path)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($elementor_path),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $elementor_files[] = $file->getPathname();
                }
            }
            
            echo '<p>Найдено PHP файлов в папке Elementor: ' . count($elementor_files) . '</p>';
            
            if (count($elementor_files) > 0) {
                echo '<div class="warning">';
                echo '<p>⚠️ Найдены файлы Elementor на сервере. Рекомендуется удалить папку полностью.</p>';
                echo '</div>';
            }
        }
        
        // Рекомендации
        echo '<h2>5. Рекомендации</h2>';
        echo '<div class="info">';
        echo '<h3>Что делать:</h3>';
        echo '<ol>';
        
        if (is_dir($elementor_path)) {
            echo '<li class="error"><strong>СРОЧНО:</strong> Удалите папку Elementor через SFTP: <code>' . htmlspecialchars($elementor_path) . '</code></li>';
        }
        
        if (file_exists($animations_file)) {
            $content_check = file_get_contents($animations_file);
            if (trim($content_check) !== '<?php' . "\n" . '// Silence is golden.') {
                echo '<li class="error"><strong>СРОЧНО:</strong> Файл animations/index.php содержит подозрительный код! Удалите папку Elementor полностью.</li>';
            }
        }
        
        echo '<li>Добавьте правила блокировки в .htaccess (см. инструкцию)</li>';
        echo '<li>Проверьте настройки Wordfence (Firewall должен быть включен)</li>';
        echo '<li>Запустите полное сканирование Wordfence</li>';
        echo '<li>Проверьте другие ваши сайты на наличие Elementor</li>';
        echo '</ol>';
        echo '</div>';
        ?>
        
        <div class="error-box">
            <h3>⚠️ ВАЖНО: Удалите этот файл после использования!</h3>
            <p>Этот файл содержит диагностическую информацию и должен быть удален из соображений безопасности.</p>
        </div>
    </div>
</body>
</html>




