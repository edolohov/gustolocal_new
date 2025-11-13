<?php
/**
 * Скрипт заполняет страницу «Как мы готовим» контентом из блок-паттерна.
 *
 * Запуск:
 *   php docs/seed-como-preparamos.php
 */

require_once dirname(__FILE__, 2) . '/wp-load.php';

if (!function_exists('wp_update_post')) {
	exit("Не удалось загрузить WordPress.\n");
}

$page = get_page_by_path('como-preparamos');
if (!$page) {
	exit("Страница como-preparamos не найдена.\n");
}

if (!class_exists('WP_Block_Patterns_Registry')) {
	exit("Реестр блок-паттернов недоступен.\n");
}

$registry = WP_Block_Patterns_Registry::get_instance();
$pattern = $registry->get_registered('gustolocal/como-preparamos');

if (!$pattern || empty($pattern['content'])) {
	exit("Паттерн gustolocal/como-preparamos не зарегистрирован или пуст.\n");
}

wp_update_post([
	'ID'           => $page->ID,
	'post_content' => $pattern['content'],
]);

delete_transient('gustolocal_como_preparamos_seeded');
update_option('gustolocal_como_preparamos_seeded', current_time('mysql'));

echo "Страница «Как мы готовим» обновлена контентом из паттерна.\n";

