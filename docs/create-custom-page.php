<?php
/**
 * Скрипт для создания страницы "custom" в WordPress
 * 
 * Использование:
 * 1. Откройте в браузере: https://gustolocal.es/create-custom-page.php
 * 2. Скрипт создаст страницу со слагом "custom" (если её ещё нет)
 * 3. Страница будет автоматически заполнена контентом из паттерна
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

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создание страницы custom</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 3px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Создание страницы "custom"</h1>
    
    <?php
    $page_slug = 'custom';
    $page_title = 'Кастомный заказ';
    
    // Проверяем, существует ли страница
    $existing_page = get_page_by_path( $page_slug );
    
    if ( $existing_page ) {
        echo '<div class="info">Страница со слагом <code>' . esc_html( $page_slug ) . '</code> уже существует.</div>';
        echo '<div class="info">ID страницы: ' . $existing_page->ID . '</div>';
        echo '<div class="info">Заголовок: ' . esc_html( $existing_page->post_title ) . '</div>';
        echo '<div class="info">Статус: ' . esc_html( $existing_page->post_status ) . '</div>';
        
        // Проверяем, есть ли контент
        if ( empty( trim( $existing_page->post_content ) ) ) {
            echo '<div class="info">Контент страницы пуст. Функция автоматического заполнения должна заполнить его при следующей загрузке страницы.</div>';
        } else {
            $has_blocks = strpos( $existing_page->post_content, '<!-- wp:' ) !== false;
            if ( $has_blocks ) {
                echo '<div class="success">✓ Страница уже содержит блоки Gutenberg.</div>';
            } else {
                echo '<div class="info">Страница содержит контент, но не в формате блоков. Функция автоматического заполнения может не сработать.</div>';
            }
        }
        
        echo '<p><a href="' . get_permalink( $existing_page->ID ) . '" class="btn" target="_blank">Открыть страницу</a></p>';
        echo '<p><a href="' . admin_url( 'post.php?post=' . $existing_page->ID . '&action=edit' ) . '" class="btn" target="_blank">Редактировать в админке</a></p>';
        
    } else {
        // Создаём страницу
        $page_data = array(
            'post_title'    => $page_title,
            'post_name'     => $page_slug,
            'post_content'  => '', // Будет заполнено автоматически функцией seeding
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => get_current_user_id(),
        );
        
        $page_id = wp_insert_post( $page_data );
        
        if ( is_wp_error( $page_id ) ) {
            echo '<div class="error">Ошибка при создании страницы: ' . esc_html( $page_id->get_error_message() ) . '</div>';
        } else {
            echo '<div class="success">✓ Страница успешно создана!</div>';
            echo '<div class="info">ID страницы: ' . $page_id . '</div>';
            echo '<div class="info">Слаг: ' . esc_html( $page_slug ) . '</div>';
            echo '<div class="info">Контент будет автоматически заполнен при следующей загрузке страницы функцией seeding.</div>';
            
            // Принудительно запускаем функцию seeding
            if ( function_exists( 'gustolocal_seed_static_pages' ) ) {
                gustolocal_seed_static_pages();
                echo '<div class="success">✓ Функция автоматического заполнения выполнена.</div>';
            }
            
            echo '<p><a href="' . get_permalink( $page_id ) . '" class="btn" target="_blank">Открыть страницу</a></p>';
            echo '<p><a href="' . admin_url( 'post.php?post=' . $page_id . '&action=edit' ) . '" class="btn" target="_blank">Редактировать в админке</a></p>';
        }
    }
    ?>
    
    <hr>
    <p><a href="<?php echo admin_url(); ?>">Вернуться в админку</a></p>
</body>
</html>

