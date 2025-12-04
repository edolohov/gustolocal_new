<?php
/**
 * Скрипт для обновления контента страницы "test" из паттерна
 * 
 * Использование:
 * 1. Откройте в браузере: https://gustolocal.es/update-test-page.php
 * 2. Скрипт обновит контент страницы из паттерна gustolocal/test-page
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Пробуем разные пути к wp-load.php
$wp_load_path = '';

// Если скрипт в корне public_html (где должен быть wp-load.php)
if ( file_exists( __DIR__ . '/wp-load.php' ) ) {
    $wp_load_path = __DIR__ . '/wp-load.php';
}
// Если скрипт в поддиректории, ищем на уровень выше
elseif ( file_exists( dirname( __DIR__ ) . '/wp-load.php' ) ) {
    $wp_load_path = dirname( __DIR__ ) . '/wp-load.php';
}
// Пробуем найти в стандартных местах WordPress
elseif ( file_exists( dirname( dirname( __DIR__ ) ) . '/wp-load.php' ) ) {
    $wp_load_path = dirname( dirname( __DIR__ ) ) . '/wp-load.php';
}
// Последняя попытка - ищем относительно текущего файла
else {
    $current_dir = __DIR__;
    // Поднимаемся на несколько уровней вверх
    for ( $i = 0; $i < 5; $i++ ) {
        $try_path = $current_dir . '/wp-load.php';
        if ( file_exists( $try_path ) ) {
            $wp_load_path = $try_path;
            break;
        }
        $current_dir = dirname( $current_dir );
        if ( $current_dir === '/' || $current_dir === '' ) {
            break;
        }
    }
}

if ( empty( $wp_load_path ) || ! file_exists( $wp_load_path ) ) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Ошибка</title></head><body>';
    echo '<h1>Ошибка: не удалось найти wp-load.php</h1>';
    echo '<p>Текущая директория скрипта: <code>' . htmlspecialchars( __DIR__ ) . '</code></p>';
    echo '<p>Проверьте, что файл wp-load.php находится в корне WordPress (обычно это public_html).</p>';
    echo '</body></html>';
    exit;
}

require_once $wp_load_path;

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
    auth_redirect();
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обновление страницы test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 3px; margin-top: 10px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Обновление страницы "test"</h1>
    
    <?php
    $page_slug = 'test';
    $pattern_slug = 'gustolocal/test-page';
    
    // Получаем страницу
    $page = get_page_by_path( $page_slug );
    
    if ( ! $page ) {
        echo '<div class="error">Страница со слагом <code>' . esc_html( $page_slug ) . '</code> не найдена.</div>';
        exit;
    }
    
    echo '<div class="info">Найдена страница: <strong>' . esc_html( $page->post_title ) . '</strong> (ID: ' . $page->ID . ')</div>';
    
    // Получаем контент из паттерна
    $pattern_content = '';
    
    if ( class_exists( 'WP_Block_Patterns_Registry' ) ) {
        $registry = WP_Block_Patterns_Registry::get_instance();
        if ( $registry->is_registered( $pattern_slug ) ) {
            $pattern = $registry->get_registered( $pattern_slug );
            if ( ! empty( $pattern['content'] ) ) {
                $pattern_content = $pattern['content'];
            }
        }
    }
    
    if ( empty( $pattern_content ) ) {
        // Пробуем загрузить напрямую из файла
        $pattern_file = get_theme_file_path( 'patterns/test-page.php' );
        if ( file_exists( $pattern_file ) ) {
            ob_start();
            include $pattern_file;
            $pattern_content = trim( ob_get_clean() );
        }
    }
    
    if ( empty( $pattern_content ) ) {
        echo '<div class="error">Не удалось загрузить контент из паттерна <code>' . esc_html( $pattern_slug ) . '</code></div>';
        exit;
    }
    
    echo '<div class="info">Контент из паттерна успешно загружен (' . strlen( $pattern_content ) . ' символов)</div>';
    
    // Обновляем страницу
    $result = wp_update_post( array(
        'ID'           => $page->ID,
        'post_content' => $pattern_content,
    ) );
    
    if ( is_wp_error( $result ) ) {
        echo '<div class="error">Ошибка при обновлении страницы: ' . esc_html( $result->get_error_message() ) . '</div>';
    } else {
        echo '<div class="success">✓ Страница успешно обновлена!</div>';
        echo '<div class="info">Контент страницы теперь соответствует паттерну.</div>';
        
        // Сбрасываем опцию, чтобы система знала, что страница обновлена
        update_option( 'gustolocal_seed_test', 'done' );
        
        echo '<p><a href="' . get_permalink( $page->ID ) . '" class="btn" target="_blank">Открыть страницу</a></p>';
        echo '<p><a href="' . admin_url( 'post.php?post=' . $page->ID . '&action=edit' ) . '" class="btn" target="_blank">Редактировать в админке</a></p>';
    }
    ?>
</body>
</html>

