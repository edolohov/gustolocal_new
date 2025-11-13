<?php
/**
 * Проверка настроек MySQL для Wordfence
 * 
 * Ошибка: MySQL server has gone away
 * Причины: max_allowed_packet, wait_timeout, interactive_timeout
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$wp_load_path = __DIR__ . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
    $wp_load_path = dirname( __DIR__ ) . '/wp-load.php';
}

if ( ! file_exists( $wp_load_path ) ) {
    header('Content-Type: text/plain; charset=utf-8');
    die('Не удалось найти wp-load.php. Поместите файл в корень WordPress.');
}

require_once $wp_load_path;

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
    auth_redirect();
}

global $wpdb;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Проверка настроек MySQL</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; line-height: 1.6; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 12px; overflow-x: auto; border-radius: 4px; }
        code { background: #f1f1f1; padding: 2px 6px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid #ddd; }
        th { background: #fafafa; font-weight: bold; }
        .value-ok { color: #28a745; font-weight: bold; }
        .value-warning { color: #ff9800; font-weight: bold; }
        .value-error { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Диагностика: MySQL server has gone away</h1>
    <p>Эта ошибка обычно возникает из-за недостаточного размера <code>max_allowed_packet</code> или слишком коротких таймаутов.</p>
    
    <?php
    // Проверяем все важные переменные MySQL
    $variables = array(
        'max_allowed_packet' => array(
            'name' => 'max_allowed_packet',
            'recommended' => 16777216, // 16MB
            'description' => 'Максимальный размер одного пакета данных',
            'unit' => 'байт'
        ),
        'wait_timeout' => array(
            'name' => 'wait_timeout',
            'recommended' => 600, // 10 минут
            'description' => 'Время ожидания неактивного соединения (секунды)',
            'unit' => 'сек'
        ),
        'interactive_timeout' => array(
            'name' => 'interactive_timeout',
            'recommended' => 600, // 10 минут
            'description' => 'Время ожидания интерактивного соединения (секунды)',
            'unit' => 'сек'
        ),
        'connect_timeout' => array(
            'name' => 'connect_timeout',
            'recommended' => 10,
            'description' => 'Таймаут подключения (секунды)',
            'unit' => 'сек'
        ),
        'net_read_timeout' => array(
            'name' => 'net_read_timeout',
            'recommended' => 60,
            'description' => 'Таймаут чтения из сети (секунды)',
            'unit' => 'сек'
        ),
        'net_write_timeout' => array(
            'name' => 'net_write_timeout',
            'recommended' => 60,
            'description' => 'Таймаут записи в сеть (секунды)',
            'unit' => 'сек'
        ),
    );
    
    echo '<h2>Текущие значения переменных MySQL</h2>';
    echo '<table>';
    echo '<thead><tr><th>Переменная</th><th>Текущее значение</th><th>Рекомендуемое</th><th>Статус</th><th>Описание</th></tr></thead>';
    echo '<tbody>';
    
    $issues = array();
    
    foreach ( $variables as $key => $var ) {
        $result = $wpdb->get_row( $wpdb->prepare( "SHOW VARIABLES LIKE %s", $var['name'] ), ARRAY_A );
        
        if ( $result ) {
            $current_value = (int) $result['Value'];
            $recommended = $var['recommended'];
            $status = '';
            $status_class = '';
            
            if ( $current_value >= $recommended ) {
                $status = '✓ OK';
                $status_class = 'value-ok';
            } else {
                $status = '⚠ Низкое';
                $status_class = 'value-warning';
                $issues[] = $var;
            }
            
            // Для max_allowed_packet показываем в MB
            if ( $var['name'] === 'max_allowed_packet' ) {
                $current_display = number_format_i18n( $current_value ) . ' (' . round( $current_value / 1024 / 1024, 2 ) . ' MB)';
                $recommended_display = number_format_i18n( $recommended ) . ' (' . round( $recommended / 1024 / 1024, 2 ) . ' MB)';
            } else {
                $current_display = number_format_i18n( $current_value ) . ' ' . $var['unit'];
                $recommended_display = number_format_i18n( $recommended ) . ' ' . $var['unit'];
            }
            
            echo '<tr>';
            echo '<td><code>' . esc_html( $var['name'] ) . '</code></td>';
            echo '<td class="' . $status_class . '">' . $current_display . '</td>';
            echo '<td>' . $recommended_display . '</td>';
            echo '<td class="' . $status_class . '">' . $status . '</td>';
            echo '<td>' . esc_html( $var['description'] ) . '</td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td><code>' . esc_html( $var['name'] ) . '</code></td>';
            echo '<td colspan="4" class="value-error">Не удалось получить значение</td>';
            echo '</tr>';
        }
    }
    
    echo '</tbody></table>';
    
    if ( ! empty( $issues ) ) {
        echo '<h2>⚠ Найдены проблемы</h2>';
        
        foreach ( $issues as $issue ) {
            echo '<div class="warning">';
            echo '<strong>' . esc_html( $issue['name'] ) . '</strong>: ';
            echo 'Текущее значение слишком мало. Рекомендуется увеличить до ' . number_format_i18n( $issue['recommended'] ) . ' ' . $issue['unit'] . '.';
            echo '</div>';
        }
        
        echo '<h2>Как исправить</h2>';
        echo '<ol>';
        
        if ( in_array( $variables['max_allowed_packet'], $issues ) ) {
            echo '<li><strong>max_allowed_packet</strong> — это самая частая причина ошибки "MySQL server has gone away":';
            echo '<ul>';
            echo '<li>Обратитесь в поддержку хостинга Hostinger</li>';
            echo '<li>Попросите увеличить <code>max_allowed_packet</code> до <strong>16MB (16777216 байт)</strong> для базы данных <code>u850527203_5vYEq</code></li>';
            echo '<li>Или попросите добавить в <code>my.cnf</code>: <code>max_allowed_packet = 16M</code></li>';
            echo '</ul></li>';
        }
        
        if ( in_array( $variables['wait_timeout'], $issues ) || in_array( $variables['interactive_timeout'], $issues ) ) {
            echo '<li><strong>Таймауты соединения</strong>:';
            echo '<ul>';
            echo '<li>Обратитесь в поддержку хостинга</li>';
            echo '<li>Попросите увеличить <code>wait_timeout</code> и <code>interactive_timeout</code> до <strong>600 секунд (10 минут)</strong></li>';
            echo '</ul></li>';
        }
        
        echo '</ol>';
        
        echo '<h3>Временное решение (если нет доступа к настройкам сервера)</h3>';
        echo '<p>Можно попробовать увеличить таймауты через WordPress. Добавьте в <code>wp-config.php</code> (перед строкой "That\'s all, stop editing!"):</p>';
        echo '<pre>';
        if ( in_array( $variables['wait_timeout'], $issues ) || in_array( $variables['interactive_timeout'], $issues ) ) {
            echo "// Увеличиваем таймауты MySQL\n";
            echo "ini_set('mysql.connect_timeout', 300);\n";
            echo "ini_set('default_socket_timeout', 300);\n";
        }
        echo '</pre>';
        echo '<p class="warning"><strong>Внимание:</strong> Это не решит проблему с <code>max_allowed_packet</code> — для этого нужен доступ к настройкам MySQL сервера.</p>';
    } else {
        echo '<div class="success">✓ Все значения в норме. Проблема может быть в другом.</div>';
        echo '<h2>Дополнительные рекомендации</h2>';
        echo '<ul>';
        echo '<li>Проверьте логи ошибок WordPress: <code>wp-content/debug.log</code></li>';
        echo '<li>Проверьте логи сервера на наличие ошибок MySQL</li>';
        echo '<li>Убедитесь, что база данных не перегружена</li>';
        echo '<li>Попробуйте перезапустить Wordfence сканирование</li>';
        echo '</ul>';
    }
    
    // Дополнительная информация
    echo '<hr>';
    echo '<h2>Дополнительная информация</h2>';
    
    // Проверяем размер самой большой записи в wffilemods
    $max_size_query = "SELECT MAX(LENGTH(filename) + LENGTH(real_path)) as max_size FROM {$wpdb->prefix}wffilemods";
    $max_size = $wpdb->get_var( $max_size_query );
    
    if ( $max_size ) {
        echo '<div class="info">Размер самой большой записи в wffilemods: <strong>' . number_format_i18n( $max_size ) . '</strong> байт (' . round( $max_size / 1024, 2 ) . ' KB)</div>';
        
        $max_packet_result = $wpdb->get_row( "SHOW VARIABLES LIKE 'max_allowed_packet'", ARRAY_A );
        if ( $max_packet_result ) {
            $max_packet = (int) $max_packet_result['Value'];
            if ( $max_size > $max_packet * 0.8 ) {
                echo '<div class="warning">⚠ Размер записи близок к лимиту max_allowed_packet. Рекомендуется увеличить max_allowed_packet.</div>';
            }
        }
    }
    ?>
    
    <p><a href="fix-wordfence-scan.php" style="display:inline-block;padding:10px 20px;background:#0073aa;color:white;text-decoration:none;border-radius:3px;margin-top:20px;">Вернуться к диагностике Wordfence</a></p>
</body>
</html>

