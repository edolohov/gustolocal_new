# Обновление хедера через WP-CLI (если админка зависает)

## Способ 1: Обновить через WP-CLI напрямую

```bash
ssh -i ~/.ssh/id_ed25519_hostinger -p 65002 u850527203@82.29.185.42
cd domains/gustolocal.es/public_html

# Обновить Header (ID 118) из файла
HEADER_CONTENT=$(cat wp-content/themes/gustolocal/parts/header.html)
wp post update 118 --post_content="$HEADER_CONTENT"
```

## Способ 2: Создать скрипт для автоматического обновления

Создайте файл `update-header.php` в корне сайта:

```php
<?php
require_once('wp-load.php');

if (current_user_can('manage_options')) {
    $header_file = get_template_directory() . '/parts/header.html';
    $header_content = file_get_contents($header_file);
    
    $header_post = get_posts([
        'post_type' => 'wp_template_part',
        'name' => 'header',
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ]);
    
    if ($header_post) {
        wp_update_post([
            'ID' => $header_post[0]->ID,
            'post_content' => $header_content
        ]);
        echo "✅ Header обновлен!";
    } else {
        echo "❌ Header не найден в базе данных";
    }
} else {
    echo "❌ Нет прав доступа";
}
?>
```

Затем запустите: `https://ваш-сайт.com/update-header.php`

## Способ 3: Синхронизировать файл с базой данных

Если вы редактируете файл напрямую, можно синхронизировать:

```bash
wp post update 118 --post_content="$(cat wp-content/themes/gustolocal/parts/header.html)"
```

