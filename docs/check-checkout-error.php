<?php
/**
 * Диагностический скрипт для проверки ошибок чекаута
 * Загрузите в корень сайта и откройте в браузере
 */

// Включить отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение WordPress
require_once('wp-load.php');

echo "<h1>Диагностика ошибок чекаута</h1>";

// 1. Проверка логов ошибок
$debug_log = WP_CONTENT_DIR . '/debug.log';
if (file_exists($debug_log)) {
    echo "<h2>Последние ошибки из debug.log:</h2>";
    $lines = file($debug_log);
    $last_lines = array_slice($lines, -50); // Последние 50 строк
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 400px; overflow: auto;'>";
    echo htmlspecialchars(implode('', $last_lines));
    echo "</pre>";
} else {
    echo "<p style='color: orange;'>⚠ Файл debug.log не найден. Убедитесь, что WP_DEBUG_LOG включен в wp-config.php</p>";
}

// 2. Проверка настроек WooCommerce
echo "<h2>Настройки WooCommerce:</h2>";
echo "<ul>";
echo "<li>WooCommerce активен: " . (class_exists('WooCommerce') ? '✓ Да' : '✗ Нет') . "</li>";
echo "<li>Страница чекаута: " . (wc_get_page_id('checkout') ? get_permalink(wc_get_page_id('checkout')) : 'Не установлена') . "</li>";
echo "</ul>";

// 3. Проверка активных платежных методов
echo "<h2>Активные платежные методы:</h2>";
if (class_exists('WC_Payment_Gateways')) {
    $gateways = WC_Payment_Gateways::instance()->get_available_payment_gateways();
    if (empty($gateways)) {
        echo "<p style='color: red;'>✗ Нет активных платежных методов!</p>";
    } else {
        echo "<ul>";
        foreach ($gateways as $gateway) {
            echo "<li>" . $gateway->get_title() . " (ID: " . $gateway->id . ")</li>";
        }
        echo "</ul>";
    }
}

// 4. Проверка обязательных полей чекаута
echo "<h2>Поля чекаута:</h2>";
if (function_exists('WC')) {
    $checkout = WC()->checkout();
    $fields = $checkout->get_checkout_fields();
    
    echo "<h3>Billing поля:</h3>";
    echo "<ul>";
    if (isset($fields['billing'])) {
        foreach ($fields['billing'] as $key => $field) {
            $required = isset($field['required']) && $field['required'] ? '✓ Обязательное' : '- Необязательное';
            echo "<li><strong>{$key}</strong>: {$required} (Label: " . (isset($field['label']) ? $field['label'] : 'нет') . ")</li>";
        }
    }
    echo "</ul>";
}

// 5. Проверка PHP ошибок
echo "<h2>Проверка PHP конфигурации:</h2>";
echo "<ul>";
echo "<li>PHP версия: " . PHP_VERSION . "</li>";
echo "<li>Memory limit: " . ini_get('memory_limit') . "</li>";
echo "<li>Max execution time: " . ini_get('max_execution_time') . "</li>";
echo "<li>Display errors: " . (ini_get('display_errors') ? 'Включено' : 'Выключено') . "</li>";
echo "</ul>";

// 6. Тест обработки чекаута (симуляция)
echo "<h2>Тест обработки данных чекаута:</h2>";
echo "<p>Попробуйте оформить заказ и проверьте логи выше на наличие ошибок.</p>";

