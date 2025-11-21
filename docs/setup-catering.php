<?php
/**
 * Скрипт для создания страницы "Кейтеринг Llévatelo"
 * 
 * Инструкция:
 * 1. Загрузите этот файл в корень WordPress (рядом с wp-config.php)
 * 2. Откройте в браузере: https://gustolocal.es/setup-catering.php
 * 3. После выполнения удалите файл из соображений безопасности
 */

// Подключаем WordPress
require_once(__DIR__ . '/wp-load.php');

// Проверка прав доступа
if (!is_user_logged_in() || !current_user_can('edit_pages')) {
    wp_die('Недостаточно прав для выполнения этого действия. Войдите как администратор.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Настройка страницы кейтеринга</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; border-radius: 4px; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0; border-radius: 4px; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; border-radius: 4px; }
        .btn { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 3px; margin-top: 10px; }
        .btn:hover { background: #005a87; }
        h1 { color: #333; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Настройка страницы "Кейтеринг Llévatelo"</h1>
        
        <?php
        $page_slug = 'catering';
        $page_title = 'Кейтеринг Llévatelo';
        $pattern_slug = 'gustolocal/catering';
        
        // Проверяем, существует ли паттерн
        $pattern_file = get_theme_file_path('patterns/catering.php');
        $pattern_exists = file_exists($pattern_file);
        
        if (!$pattern_exists) {
            echo '<div class="error">❌ Файл паттерна не найден: <code>' . esc_html($pattern_file) . '</code></div>';
            echo '<div class="info">Убедитесь, что файл <code>catering.php</code> загружен в папку <code>wp-content/themes/gustolocal/patterns/</code></div>';
        } else {
            echo '<div class="success">✓ Файл паттерна найден</div>';
        }
        
        // Проверяем, существует ли страница
        $existing_page = get_page_by_path($page_slug);
        
        if ($existing_page) {
            echo '<div class="info">Страница со слагом <code>' . esc_html($page_slug) . '</code> уже существует.</div>';
            echo '<div class="info">ID страницы: ' . $existing_page->ID . '</div>';
            echo '<div class="info">Заголовок: ' . esc_html($existing_page->post_title) . '</div>';
            echo '<div class="info">Статус: ' . esc_html($existing_page->post_status) . '</div>';
            
            $page_id = $existing_page->ID;
            $needs_update = false;
            
            // Проверяем контент
            if (empty(trim($existing_page->post_content))) {
                echo '<div class="info">Контент страницы пуст. Заполняем паттерном...</div>';
                $needs_update = true;
            } else {
                $has_blocks = strpos($existing_page->post_content, '<!-- wp:') !== false;
                if ($has_blocks) {
                    echo '<div class="success">✓ Страница уже содержит блоки Gutenberg.</div>';
                } else {
                    echo '<div class="info">Страница содержит контент, но не в формате блоков. Обновляем...</div>';
                    $needs_update = true;
                }
            }
            
            // Обновляем контент, если нужно
            if ($needs_update && $pattern_exists) {
                if (function_exists('gustolocal_get_pattern_content')) {
                    $pattern_content = gustolocal_get_pattern_content($pattern_slug);
                } else {
                    // Fallback
                    if (function_exists('gustolocal_load_pattern_file')) {
                        $pattern_content = gustolocal_load_pattern_file($pattern_file);
                    } else {
                        ob_start();
                        include $pattern_file;
                        $pattern_content = trim(ob_get_clean());
                        // Убираем PHP теги
                        $pattern_content = preg_replace('/^<\?php\s*/', '', $pattern_content);
                        $pattern_content = preg_replace('/\?>\s*$/', '', $pattern_content);
                        $pattern_content = preg_replace('/^\/\*\*.*?\*\//s', '', $pattern_content);
                        $pattern_content = trim($pattern_content);
                    }
                }
                
                if (!empty($pattern_content)) {
                    wp_update_post(array(
                        'ID'           => $page_id,
                        'post_content' => $pattern_content,
                        'post_status'  => 'draft', // Оставляем как черновик
                    ));
                    echo '<div class="success">✓ Контент страницы обновлён паттерном</div>';
                } else {
                    echo '<div class="error">❌ Не удалось загрузить контент паттерна</div>';
                }
            }
            
        } else {
            // Создаём страницу
            echo '<div class="info">Создаём новую страницу...</div>';
            
            $page_data = array(
                'post_title'    => $page_title,
                'post_name'     => $page_slug,
                'post_content'  => '', // Будет заполнено паттерном
                'post_status'   => 'draft', // Черновик для проверки
                'post_type'     => 'page',
                'post_author'   => get_current_user_id(),
            );
            
            $page_id = wp_insert_post($page_data);
            
            if (is_wp_error($page_id)) {
                echo '<div class="error">❌ Ошибка при создании страницы: ' . esc_html($page_id->get_error_message()) . '</div>';
            } else {
                echo '<div class="success">✓ Страница успешно создана!</div>';
                echo '<div class="info">ID страницы: ' . $page_id . '</div>';
                
                // Заполняем контентом из паттерна
                if ($pattern_exists) {
                    if (function_exists('gustolocal_get_pattern_content')) {
                        $pattern_content = gustolocal_get_pattern_content($pattern_slug);
                    } else {
                        if (function_exists('gustolocal_load_pattern_file')) {
                            $pattern_content = gustolocal_load_pattern_file($pattern_file);
                        } else {
                            ob_start();
                            include $pattern_file;
                            $pattern_content = trim(ob_get_clean());
                            $pattern_content = preg_replace('/^<\?php\s*/', '', $pattern_content);
                            $pattern_content = preg_replace('/\?>\s*$/', '', $pattern_content);
                            $pattern_content = preg_replace('/^\/\*\*.*?\*\//s', '', $pattern_content);
                            $pattern_content = trim($pattern_content);
                        }
                    }
                    
                    if (!empty($pattern_content)) {
                        wp_update_post(array(
                            'ID'           => $page_id,
                            'post_content' => $pattern_content,
                        ));
                        echo '<div class="success">✓ Контент страницы заполнен паттерном</div>';
                    } else {
                        echo '<div class="error">❌ Не удалось загрузить контент паттерна</div>';
                    }
                }
            }
        }
        
        if (isset($page_id) && $page_id) {
            echo '<hr>';
            echo '<h2>Ссылки:</h2>';
            echo '<p><a href="' . admin_url("post.php?post={$page_id}&action=edit") . '" class="btn" target="_blank">✏️ Редактировать страницу в админке</a></p>';
            echo '<p><a href="' . get_preview_post_link($page_id) . '" class="btn" target="_blank">👁️ Предпросмотр страницы</a></p>';
        }
        ?>
        
        <hr>
        <div class="info">
            <strong>Важно:</strong> После проверки страницы удалите этот файл (<code>setup-catering.php</code>) из соображений безопасности.
        </div>
        <p><a href="<?php echo admin_url(); ?>">← Вернуться в админку</a></p>
    </div>
</body>
</html>

