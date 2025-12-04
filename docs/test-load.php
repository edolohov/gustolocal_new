<?php
/**
 * Тестовый скрипт для диагностики зависания WordPress
 * 
 * ИСПОЛЬЗОВАНИЕ:
 * 1. Загрузите в корень WordPress через SFTP
 * 2. Откройте: https://gustolocal.es/test-load.php
 * 3. Скрипт покажет, на каком этапе происходит зависание
 * 4. УДАЛИТЕ файл после использования!
 */

// Настройки для диагностики
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log');
ini_set('max_execution_time', 60);
set_time_limit(60);

// Функция для вывода с буферизацией
function debug_output($message) {
    echo $message . "<br>\n";
    flush();
    ob_flush();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Диагностика загрузки WordPress</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .step { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #2196F3; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Диагностика загрузки WordPress</h1>
        <p><strong>Время начала:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <hr>
        
        <?php
        $start_time = microtime(true);
        $steps = array();
        
        // Шаг 1: Проверка базовых файлов
        debug_output("<div class='step'><strong>Шаг 1:</strong> Проверка файлов WordPress...");
        if (file_exists('./wp-load.php')) {
            debug_output("✅ Файл wp-load.php найден");
            $steps[] = "wp-load.php найден";
        } else {
            debug_output("❌ Файл wp-load.php НЕ найден!");
            $steps[] = "wp-load.php НЕ найден";
        }
        if (file_exists('./wp-config.php')) {
            debug_output("✅ Файл wp-config.php найден");
            $steps[] = "wp-config.php найден";
        } else {
            debug_output("❌ Файл wp-config.php НЕ найден!");
            $steps[] = "wp-config.php НЕ найден";
        }
        debug_output("</div>");
        flush();
        
        // Шаг 2: Проверка папки плагинов
        debug_output("<div class='step'><strong>Шаг 2:</strong> Проверка плагинов...");
        $plugins_dir = './wp-content/plugins';
        if (is_dir($plugins_dir)) {
            $plugins = glob($plugins_dir . '/*', GLOB_ONLYDIR);
            debug_output("✅ Найдено плагинов: " . count($plugins));
            $steps[] = "Плагинов: " . count($plugins);
            
            // Проверка mu-plugins
            $mu_plugins_dir = './wp-content/mu-plugins';
            if (is_dir($mu_plugins_dir)) {
                $mu_plugins = glob($mu_plugins_dir . '/*.php');
                if (!empty($mu_plugins)) {
                    debug_output("⚠️ Найдено must-use плагинов: " . count($mu_plugins));
                    foreach ($mu_plugins as $mu_plugin) {
                        debug_output("   - " . basename($mu_plugin));
                    }
                    $steps[] = "MU-плагинов: " . count($mu_plugins);
                }
            }
        } else {
            debug_output("❌ Папка плагинов не найдена!");
        }
        debug_output("</div>");
        flush();
        
        // Шаг 3: Проверка темы
        debug_output("<div class='step'><strong>Шаг 3:</strong> Проверка темы...");
        $themes_dir = './wp-content/themes';
        if (is_dir($themes_dir)) {
            $themes = glob($themes_dir . '/*', GLOB_ONLYDIR);
            debug_output("✅ Найдено тем: " . count($themes));
            foreach ($themes as $theme) {
                if (file_exists($theme . '/functions.php')) {
                    $size = filesize($theme . '/functions.php');
                    debug_output("   - " . basename($theme) . " (functions.php: " . round($size/1024, 2) . " KB)");
                }
            }
        }
        debug_output("</div>");
        flush();
        
        // Шаг 4: Попытка загрузить wp-config.php (только чтение, без выполнения)
        debug_output("<div class='step'><strong>Шаг 4:</strong> Проверка wp-config.php...");
        $config_content = file_get_contents('./wp-config.php');
        if ($config_content) {
            debug_output("✅ wp-config.php прочитан (" . round(strlen($config_content)/1024, 2) . " KB)");
            
            // Проверка настроек отладки
            if (strpos($config_content, "WP_DEBUG', true") !== false) {
                debug_output("✅ WP_DEBUG включен");
            } else {
                debug_output("⚠️ WP_DEBUG выключен или не найден");
            }
            
            if (strpos($config_content, "WP_DEBUG_LOG', true") !== false) {
                debug_output("✅ WP_DEBUG_LOG включен");
            } else {
                debug_output("⚠️ WP_DEBUG_LOG выключен или не найден");
            }
        }
        debug_output("</div>");
        flush();
        
        // Шаг 5: Попытка загрузить WordPress
        debug_output("<div class='step'><strong>Шаг 5:</strong> Попытка загрузить WordPress...");
        debug_output("⏳ Загрузка wp-load.php (это может занять время)...");
        flush();
        
        $load_start = microtime(true);
        
        try {
            // Устанавливаем флаг перед загрузкой
            define('WP_USE_THEMES', false);
            define('SHORTINIT', false); // Загрузить все
            
            // Пытаемся загрузить WordPress
            require_once('./wp-load.php');
            
            $load_time = microtime(true) - $load_start;
            
            if (defined('ABSPATH')) {
                debug_output("<span class='success'>✅ WordPress загружен успешно!</span>");
                debug_output("⏱️ Время загрузки: " . round($load_time, 2) . " секунд");
                
                if (function_exists('get_bloginfo')) {
                    debug_output("📌 Версия WordPress: " . get_bloginfo('version'));
                }
                
                if (function_exists('get_option')) {
                    $active_plugins = get_option('active_plugins', array());
                    debug_output("📌 Активных плагинов: " . count($active_plugins));
                }
                
                $steps[] = "WordPress загружен за " . round($load_time, 2) . " сек";
            } else {
                debug_output("<span class='error'>❌ WordPress загружен, но ABSPATH не определён</span>");
            }
            
        } catch (Exception $e) {
            $load_time = microtime(true) - $load_start;
            debug_output("<span class='error'>❌ Исключение при загрузке WordPress</span>");
            debug_output("Сообщение: " . htmlspecialchars($e->getMessage()));
            debug_output("Файл: " . $e->getFile());
            debug_output("Строка: " . $e->getLine());
            $steps[] = "Ошибка: " . $e->getMessage();
            
        } catch (Error $e) {
            $load_time = microtime(true) - $load_start;
            debug_output("<span class='error'>❌ Фатальная ошибка при загрузке WordPress</span>");
            debug_output("Сообщение: " . htmlspecialchars($e->getMessage()));
            debug_output("Файл: " . $e->getFile());
            debug_output("Строка: " . $e->getLine());
            $steps[] = "Фатальная ошибка: " . $e->getMessage();
            
        } catch (Throwable $e) {
            $load_time = microtime(true) - $load_start;
            debug_output("<span class='error'>❌ Критическая ошибка</span>");
            debug_output("Сообщение: " . htmlspecialchars($e->getMessage()));
            $steps[] = "Критическая ошибка: " . $e->getMessage();
        }
        
        debug_output("</div>");
        flush();
        
        // Итоговая информация
        $total_time = microtime(true) - $start_time;
        ?>
        
        <hr>
        <h2>📊 Результаты диагностики</h2>
        <div class="step">
            <p><strong>Общее время выполнения:</strong> <?php echo round($total_time, 2); ?> секунд</p>
            
            <?php if ($total_time > 30): ?>
                <p class="error">⚠️ ВНИМАНИЕ: Загрузка заняла больше 30 секунд! Возможна проблема с производительностью.</p>
            <?php elseif ($total_time > 10): ?>
                <p class="warning">⚠️ Загрузка заняла больше 10 секунд. Это медленно, но не критично.</p>
            <?php else: ?>
                <p class="success">✅ Время загрузки в норме.</p>
            <?php endif; ?>
            
            <h3>Выполненные шаги:</h3>
            <ul>
                <?php foreach ($steps as $step): ?>
                    <li><?php echo htmlspecialchars($step); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="step" style="background: #fff3cd; border-left-color: #ffc107;">
            <h3>⚠️ ВАЖНО</h3>
            <p><strong>Удалите этот файл после диагностики!</strong></p>
            <p>Этот файл содержит диагностическую информацию и должен быть удален из соображений безопасности.</p>
        </div>
        
        <div class="step">
            <h3>📝 Что делать дальше:</h3>
            <ol>
                <li>Если скрипт завис на шаге 5 - проблема в загрузке WordPress или плагинов</li>
                <li>Проверьте файл <code>error_log</code> в корне сайта</li>
                <li>Отключите плагины через базу данных (см. инструкцию)</li>
                <li>Проверьте логи Apache через панель хостинга</li>
            </ol>
        </div>
    </div>
</body>
</html>




