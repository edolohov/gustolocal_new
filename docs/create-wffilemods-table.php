<?php
/**
 * Создание таблицы stg_wffilemods для Wordfence
 * 
 * Проблема: Wordfence использует префикс stg_, но таблица создана с префиксом wp_
 * Решение: Создаём таблицу stg_wffilemods на основе структуры wp_wffilemods
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
    <title>Создание таблицы stg_wffilemods</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 12px; overflow-x: auto; border-radius: 4px; }
        code { background: #f1f1f1; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Создание таблицы stg_wffilemods для Wordfence</h1>
    
    <?php
    $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'check';
    
    // Ищем все таблицы wffilemods
    $all_tables = $wpdb->get_col( "SHOW TABLES" );
    $wp_table = null;
    $stg_table = null;
    
    foreach ( $all_tables as $table ) {
        if ( $table === 'wp_wffilemods' ) {
            $wp_table = $table;
        }
        if ( $table === 'stg_wffilemods' ) {
            $stg_table = $table;
        }
    }
    
    echo '<div class="info">Префикс таблиц в wp-config.php: <code>' . esc_html( $wpdb->prefix ) . '</code></div>';
    echo '<div class="info">Найдена таблица wp_wffilemods: ' . ( $wp_table ? '✓ Да' : '✗ Нет' ) . '</div>';
    echo '<div class="info">Найдена таблица stg_wffilemods: ' . ( $stg_table ? '✓ Да' : '✗ Нет' ) . '</div>';
    
    if ( $action === 'check' ) {
        if ( $stg_table ) {
            echo '<div class="success">Таблица <code>stg_wffilemods</code> уже существует. Всё в порядке!</div>';
        } elseif ( $wp_table ) {
            echo '<div class="warning">Таблица <code>stg_wffilemods</code> не найдена, но найдена <code>wp_wffilemods</code>.</div>';
            echo '<p>Wordfence использует префикс <code>stg_</code>, поэтому нужно создать таблицу <code>stg_wffilemods</code>.</p>';
            echo '<p><a href="?action=create" class="btn" style="display:inline-block;padding:10px 20px;background:#0073aa;color:white;text-decoration:none;border-radius:3px;">Создать таблицу stg_wffilemods</a></p>';
        } else {
            echo '<div class="error">Не найдена ни одна таблица wffilemods. Убедитесь, что Wordfence установлен и активирован.</div>';
        }
    } elseif ( $action === 'create' ) {
        if ( $stg_table ) {
            echo '<div class="info">Таблица <code>stg_wffilemods</code> уже существует.</div>';
        } elseif ( ! $wp_table ) {
            echo '<div class="error">Не найдена таблица <code>wp_wffilemods</code> для копирования структуры.</div>';
        } else {
            // Получаем структуру таблицы wp_wffilemods
            $create_table_query = $wpdb->get_row( "SHOW CREATE TABLE {$wp_table}", ARRAY_A );
            
            if ( ! $create_table_query ) {
                echo '<div class="error">Не удалось получить структуру таблицы <code>wp_wffilemods</code>.</div>';
            } else {
                $create_sql = $create_table_query['Create Table'];
                
                // Заменяем wp_wffilemods на stg_wffilemods в SQL
                $create_sql = str_replace( '`wp_wffilemods`', '`stg_wffilemods`', $create_sql );
                $create_sql = str_replace( 'wp_wffilemods', 'stg_wffilemods', $create_sql );
                
                echo '<h2>Создание таблицы stg_wffilemods</h2>';
                echo '<div class="info">SQL запрос:</div>';
                echo '<pre>' . esc_html( $create_sql ) . '</pre>';
                
                // Выполняем создание таблицы
                $result = $wpdb->query( $create_sql );
                
                if ( $result !== false ) {
                    echo '<div class="success">✓ Таблица <code>stg_wffilemods</code> успешно создана!</div>';
                    
                    // Проверяем, что таблица создана
                    $check = $wpdb->get_var( "SHOW TABLES LIKE 'stg_wffilemods'" );
                    if ( $check === 'stg_wffilemods' ) {
                        echo '<div class="success">✓ Проверка: таблица существует</div>';
                        
                        // Показываем структуру
                        $columns = $wpdb->get_results( "DESCRIBE stg_wffilemods", ARRAY_A );
                        echo '<h3>Структура созданной таблицы:</h3>';
                        echo '<pre>';
                        foreach ( $columns as $column ) {
                            echo esc_html( $column['Field'] . ' | ' . $column['Type'] . ' | ' . $column['Null'] . ' | ' . $column['Key'] . "\n" );
                        }
                        echo '</pre>';
                        
                        echo '<div class="info">Теперь Wordfence сможет использовать таблицу <code>stg_wffilemods</code> для сканирования.</div>';
                        echo '<p><a href="fix-wordfence-scan.php" style="display:inline-block;padding:10px 20px;background:#0073aa;color:white;text-decoration:none;border-radius:3px;">Вернуться к диагностике</a></p>';
                    } else {
                        echo '<div class="error">✗ Ошибка: таблица не была создана</div>';
                    }
                } else {
                    $error = $wpdb->last_error;
                    echo '<div class="error">✗ Ошибка при создании таблицы: ' . esc_html( $error ) . '</div>';
                    echo '<div class="info">Попробуйте выполнить SQL запрос вручную через phpMyAdmin:</div>';
                    echo '<pre>' . esc_html( $create_sql ) . '</pre>';
                }
            }
        }
    }
    ?>
</body>
</html>

