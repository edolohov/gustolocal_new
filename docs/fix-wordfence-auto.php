<?php
/**
 * Автоматическое исправление проблем Wordfence
 * 
 * ВНИМАНИЕ: Этот скрипт выполняет SQL-запросы напрямую!
 * Используйте только если уверены в своих действиях.
 * 
 * Использование:
 * 1. Перейдите: https://gustolocal.es/fix-wordfence-auto.php?action=check
 * 2. Просмотрите результаты проверки
 * 3. Если всё ОК, перейдите: https://gustolocal.es/fix-wordfence-auto.php?action=fix
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

$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'check';
$table_name = $wpdb->prefix . 'wffilemods';

// Ищем таблицу с любым префиксом
$all_tables = $wpdb->get_col( "SHOW TABLES" );
$found_table = null;
foreach ( $all_tables as $table ) {
    if ( strpos( $table, 'wffilemods' ) !== false ) {
        $found_table = $table;
        break;
    }
}

if ( ! $found_table ) {
    wp_die( 'Таблица wffilemods не найдена. Убедитесь, что Wordfence установлен и активирован.' );
}

$table_name = $found_table;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Автоматическое исправление Wordfence</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 12px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 3px; margin: 10px 5px 10px 0; }
        .btn:hover { background: #005a87; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
    <h1>Автоматическое исправление Wordfence</h1>
    <p>Таблица: <code><?php echo esc_html( $table_name ); ?></code></p>
    
    <?php if ( $action === 'check' ): ?>
        <h2>Проверка текущего состояния</h2>
        
        <?php
        // Проверка max_allowed_packet
        $max_packet_row = $wpdb->get_row( "SHOW VARIABLES LIKE 'max_allowed_packet'", ARRAY_A );
        $max_packet = $max_packet_row ? (int) $max_packet_row['Value'] : 0;
        $max_packet_mb = round( $max_packet / 1024 / 1024, 2 );
        
        if ( $max_packet >= 16777216 ) {
            echo '<div class="success">✓ max_allowed_packet: ' . number_format_i18n( $max_packet ) . ' байт (' . $max_packet_mb . ' MB) — ОК</div>';
        } else {
            echo '<div class="warning">⚠ max_allowed_packet: ' . number_format_i18n( $max_packet ) . ' байт (' . $max_packet_mb . ' MB) — рекомендуется увеличить до 16MB</div>';
        }
        
        // Количество записей
        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
        $old_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE mtime < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))" );
        
        echo '<div class="info">Всего записей: <strong>' . number_format_i18n( $count ) . '</strong></div>';
        if ( $old_count > 0 ) {
            echo '<div class="info">Записей старше 30 дней: <strong>' . number_format_i18n( $old_count ) . '</strong> (можно удалить)</div>';
        } else {
            echo '<div class="success">✓ Старых записей нет</div>';
        }
        ?>
        
        <hr>
        <h2>Что будет сделано при исправлении:</h2>
        <ol>
            <li>Попытка увеличить max_allowed_packet до 16MB (может не сработать без прав администратора БД)</li>
            <li>Удаление записей старше 30 дней (<?php echo number_format_i18n( $old_count ); ?> записей)</li>
            <li>Оптимизация таблицы</li>
        </ol>
        
        <p>
            <a href="?action=fix" class="btn btn-danger" onclick="return confirm('Вы уверены? Это удалит старые записи из таблицы.');">Выполнить исправление</a>
            <a href="fix-wordfence-scan.php" class="btn">Вернуться к диагностике</a>
        </p>
        
    <?php elseif ( $action === 'fix' ): ?>
        <h2>Выполнение исправлений</h2>
        
        <?php
        $results = [];
        
        // 1. Попытка увеличить max_allowed_packet
        echo '<h3>1. Увеличение max_allowed_packet</h3>';
        $result = $wpdb->query( "SET GLOBAL max_allowed_packet = 16777216" );
        if ( $result !== false ) {
            echo '<div class="success">✓ max_allowed_packet увеличен до 16MB</div>';
        } else {
            $error = $wpdb->last_error;
            if ( empty( $error ) ) {
                echo '<div class="warning">⚠ Не удалось изменить max_allowed_packet (недостаточно прав). Обратитесь в поддержку хостинга.</div>';
            } else {
                echo '<div class="error">✗ Ошибка: ' . esc_html( $error ) . '</div>';
            }
        }
        
        // 2. Удаление старых записей
        echo '<h3>2. Удаление старых записей</h3>';
        $old_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE mtime < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))" );
        
        if ( $old_count > 0 ) {
            $deleted = $wpdb->query( "DELETE FROM {$table_name} WHERE mtime < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))" );
            if ( $deleted !== false ) {
                echo '<div class="success">✓ Удалено записей: ' . number_format_i18n( $deleted ) . '</div>';
            } else {
                echo '<div class="error">✗ Ошибка при удалении: ' . esc_html( $wpdb->last_error ) . '</div>';
            }
        } else {
            echo '<div class="info">Нет старых записей для удаления</div>';
        }
        
        // 3. Оптимизация таблицы
        echo '<h3>3. Оптимизация таблицы</h3>';
        $result = $wpdb->query( "OPTIMIZE TABLE {$table_name}" );
        if ( $result !== false ) {
            echo '<div class="success">✓ Таблица оптимизирована</div>';
        } else {
            echo '<div class="warning">⚠ Предупреждение: ' . esc_html( $wpdb->last_error ) . '</div>';
        }
        
        // Финальная проверка
        echo '<hr><h2>Результат</h2>';
        $final_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
        echo '<div class="info">Записей в таблице после очистки: <strong>' . number_format_i18n( $final_count ) . '</strong></div>';
        ?>
        
        <p>
            <a href="?action=check" class="btn">Проверить снова</a>
            <a href="fix-wordfence-scan.php" class="btn">Вернуться к диагностике</a>
        </p>
        
    <?php endif; ?>
</body>
</html>

