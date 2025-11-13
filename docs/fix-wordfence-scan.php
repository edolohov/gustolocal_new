<?php
/**
 * Диагностика таблицы Wordfence (wffilemods)
 *
 * Использование:
 * 1. Перейдите в браузере по адресу https://gustolocal.es/fix-wordfence-scan.php
 * 2. Авторизуйтесь как администратор (если не авторизованы)
 * 3. Просмотрите диагностическую информацию и рекомендации
 */

// Включаем отображение ошибок, чтобы увидеть причину 500, если она возникнет
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Подключаем WordPress
$wp_load_path = __DIR__ . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
    $wp_load_path = dirname( __DIR__ ) . '/wp-load.php';
}

if ( ! file_exists( $wp_load_path ) ) {
    header('Content-Type: text/plain; charset=utf-8');
    die('Не удалось найти wp-load.php. Поместите файл fix-wordfence-scan.php в корень WordPress.');
}

require_once $wp_load_path;

// Требуем авторизацию администратора
if ( ! is_user_logged_in() ) {
    auth_redirect();
}

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Недостаточно прав для просмотра этой страницы.' );
}

global $wpdb;
$table_name = $wpdb->prefix . 'wffilemods';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Wordfence Scan Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1100px; margin: 20px auto; padding: 20px; line-height: 1.6; }
        h1 { margin-top: 0; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 12px 16px; overflow-x: auto; border-radius: 4px; }
        code { background: #f1f1f1; padding: 2px 4px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #ddd; }
        th { background: #fafafa; }
    </style>
</head>
<body>
    <h1>Диагностика Wordfence: таблица wffilemods</h1>
    <p>Скрипт помогает найти причины ошибки <code>INSERT INTO ... stg_wffilemods ...</code>, которая возникает при сканировании Wordfence.</p>

    <?php
    // Проверка существования таблицы
    $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

    if ( $table_exists !== $table_name ) {
        echo '<div class="error">Таблица <code>' . esc_html( $table_name ) . '</code> не найдена. Убедитесь, что Wordfence установлен корректно.</div>';
        echo '</body></html>';
        exit;
    }

    echo '<div class="info">Таблица <code>' . esc_html( $table_name ) . '</code> обнаружена.</div>';

    // Получаем структуру таблицы
    $columns = $wpdb->get_results( "DESCRIBE {$table_name}", ARRAY_A );
    if ( empty( $columns ) ) {
        echo '<div class="error">Не удалось получить структуру таблицы. Проверьте права пользователя базы данных.</div>';
        echo '</body></html>';
        exit;
    }

    echo '<h2>Структура таблицы</h2>';
    echo '<table><thead><tr><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th><th>По умолчанию</th><th>Дополнительно</th></tr></thead><tbody>';
    foreach ( $columns as $column ) {
        echo '<tr>';
        echo '<td>' . esc_html( $column['Field'] ) . '</td>';
        echo '<td>' . esc_html( $column['Type'] ) . '</td>';
        echo '<td>' . esc_html( $column['Null'] ) . '</td>';
        echo '<td>' . esc_html( $column['Key'] ) . '</td>';
        echo '<td>' . esc_html( $column['Default'] ) . '</td>';
        echo '<td>' . esc_html( $column['Extra'] ) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    // Проверяем длину полей filename и real_path
    $issues = [];

    foreach ( $columns as $column ) {
        if ( $column['Field'] === 'filename' || $column['Field'] === 'real_path' ) {
            if ( preg_match( '/varchar\((\d+)\)/i', $column['Type'], $matches ) ) {
                $length = (int) $matches[1];
                if ( $length < 1000 ) {
                    $issues[] = "Поле <code>{$column['Field']}</code> имеет длину {$length}. Рекомендуется увеличить до <code>VARCHAR(1000)</code>.";
                }
            }
        }
    }

    if ( empty( $issues ) ) {
        echo '<div class="success">Размеры полей <code>filename</code> и <code>real_path</code> достаточны.</div>';
    } else {
        echo '<div class="warning"><strong>Найдены потенциальные проблемы:</strong><ul>';
        foreach ( $issues as $issue ) {
            echo '<li>' . wp_kses_post( $issue ) . '</li>';
        }
        echo '</ul></div>';
    }

    // Количество записей в таблице
    $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
    echo '<div class="info">Количество записей: <strong>' . number_format_i18n( $count ) . '</strong></div>';

    if ( $count > 100000 ) {
        echo '<div class="warning">В таблице более 100 000 записей. Рекомендуется удалить старые записи, чтобы ускорить сканирование.</div>';
    }

    // max_allowed_packet
    $max_packet_row = $wpdb->get_row( "SHOW VARIABLES LIKE 'max_allowed_packet'", ARRAY_A );
    if ( $max_packet_row ) {
        $max_packet = (int) $max_packet_row['Value'];
        echo '<div class="info">Текущее значение <code>max_allowed_packet</code>: <strong>' . number_format_i18n( $max_packet ) . '</strong> байт (' . round( $max_packet / 1024 / 1024, 2 ) . ' MB)</div>';
        if ( $max_packet < 16777216 ) {
            echo '<div class="warning">Значение <code>max_allowed_packet</code> меньше 16MB. Попросите хостинг увеличить его или выполните команду в phpMyAdmin:<br><pre>SET GLOBAL max_allowed_packet = 16777216;</pre></div>';
        }
    } else {
        echo '<div class="warning">Не удалось получить значение <code>max_allowed_packet</code>. Возможно, недостаточно привилегий.</div>';
    }

    // Статистика по индексам
    $indexes = $wpdb->get_results( "SHOW INDEXES FROM {$table_name}", ARRAY_A );
    if ( $indexes ) {
        echo '<h2>Индексы таблицы</h2>';
        echo '<pre>' . esc_html( print_r( $indexes, true ) ) . '</pre>';
    } else {
        echo '<div class="warning">Не удалось получить информацию об индексах. Возможно, недостаточно привилегий.</div>';
    }
    ?>

    <hr>
    <h2>Что делать дальше</h2>
    <ol>
        <li>Если <code>max_allowed_packet</code> меньше 16MB, увеличьте его через phpMyAdmin или обратитесь в поддержку хостинга.</li>
        <li>Очистите устаревшие записи (старше 30 дней):
            <pre>DELETE FROM <?php echo esc_html( $table_name ); ?>
WHERE mtime &lt; UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));</pre>
        </li>
        <li>Оптимизируйте таблицу после очистки:
            <pre>OPTIMIZE TABLE <?php echo esc_html( $table_name ); ?>;</pre>
        </li>
        <li>Убедитесь, что в настройках Wordfence (Scan → Manage Scan) установлены значения:
            <ul>
                <li>Максимальное время этапа сканирования: <strong>60 секунд</strong></li>
                <li>Память, запрашиваемая Wordfence: <strong>512 MB</strong></li>
                <li>Сканирование с низким уровнем ресурсов: <strong>включено</strong></li>
            </ul>
        </li>
    </ol>
</body>
</html>

