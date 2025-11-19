<?php
/**
 * Принудительное обновление страницы "Хлеб и сэндвичи" контентом из паттерна.
 *
 * Использование:
 * 1. Авторизуйтесь в админке WordPress.
 * 2. Откройте https://gustolocal.es/update-pan-sandwiches-page.php
 * 3. После выполнения удалите файл или оставьте для повторных запусков.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$wp_load_path = __DIR__ . '/../wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
    $wp_load_path = __DIR__ . '/wp-load.php';
}

if ( ! file_exists( $wp_load_path ) ) {
    header('Content-Type: text/plain; charset=utf-8');
    die('wp-load.php не найден. Поместите скрипт в директорию WordPress.');
}

require_once $wp_load_path;

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
    auth_redirect();
}

header('Content-Type: text/html; charset=utf-8');

$page_slug = 'pan-sandwiches-valencia';
$pattern_slug = 'gustolocal/pan-sandwiches';
$option_name = 'gustolocal_seed_pan_sandwiches';

$page = get_page_by_path( $page_slug );
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обновление страницы «Хлеб и сэндвичи»</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; line-height: 1.6; }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin-bottom: 20px; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 20px; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-bottom: 20px; }
        code { background: #f5f5f5; padding: 2px 4px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Обновление страницы «Хлеб и сэндвичи»</h1>
    <?php if ( ! $page ) : ?>
        <div class="error">Страница со слагом <code><?php echo esc_html( $page_slug ); ?></code> не найдена.</div>
    <?php else : ?>
        <?php
        if ( ! function_exists( 'gustolocal_get_pattern_content' ) ) {
            echo '<div class="error">Не найдена функция gustolocal_get_pattern_content. Убедитесь, что активна тема gustolocal.</div>';
        } else {
$content = gustolocal_get_pattern_content( $pattern_slug );
            if ( empty( $content ) ) {
                $pattern_path = get_template_directory() . '/patterns/pan-sandwiches.php';
                if ( file_exists( $pattern_path ) ) {
                    ob_start();
                    include $pattern_path;
                    $content = ob_get_clean();
                }
            }

            if ( empty( $content ) ) {
                echo '<div class="error">Не удалось получить контент паттерна <code>' . esc_html( $pattern_slug ) . '</code>.</div>';
            } else {
                $result = wp_update_post( array(
                    'ID'           => $page->ID,
                    'post_content' => $content,
                ), true );

                if ( is_wp_error( $result ) ) {
                    echo '<div class="error">Ошибка при обновлении страницы: ' . esc_html( $result->get_error_message() ) . '</div>';
                } else {
                    update_option( $option_name, 'done' );
                    echo '<div class="success">Готово! Страница обновлена контентом из паттерна.</div>';
                }
            }
        }
        ?>
        <p><strong>Страница:</strong> <a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html( $page->post_title ); ?></a></p>
        <p><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $page->ID . '&action=edit' ) ); ?>" target="_blank" rel="noreferrer noopener">Открыть редактор</a></p>
    <?php endif; ?>
</body>
</html>

