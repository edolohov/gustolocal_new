<?php
/**
 * Twenty Twenty-Four functions and definitions - OPTIMIZED VERSION
 * Основные функции темы + многоязычность + WooCommerce
 */

// Подключаем основные функции многоязычности
require_once get_template_directory() . '/multilang-core.php';

// Подключаем многоязычную поддержку для Meal Builder
require_once get_template_directory() . '/meal-builder-multilang.php';

/**
 * Register block styles.
 */
if ( ! function_exists( 'twentytwentyfour_block_styles' ) ) :
	function twentytwentyfour_block_styles() {
		register_block_style(
			'core/details',
			array(
				'name'         => 'arrow-icon-details',
				'label'        => __( 'Arrow icon', 'twentytwentyfour' ),
				'inline_style' => '
				.is-style-arrow-icon-details {
					padding-top: var(--wp--preset--spacing--10);
					padding-bottom: var(--wp--preset--spacing--10);
				}
				.is-style-arrow-icon-details summary {
					list-style-type: "\2193\00a0\00a0\00a0";
				}
				.is-style-arrow-icon-details[open]>summary {
					list-style-type: "\2192\00a0\00a0\00a0";
				}',
			)
		);
		register_block_style(
			'core/post-terms',
			array(
				'name'         => 'pill',
				'label'        => __( 'Pill', 'twentytwentyfour' ),
				'inline_style' => '
				.is-style-pill a,
				.is-style-pill span:not([class*="color"]),
				.is-style-pill span:not([class*="background-color"]) {
					display: inline-block;
					background-color: var(--wp--preset--color--contrast-2, currentColor);
					color: var(--wp--preset--color--base, #fff);
					padding: 0.375rem 0.875rem;
					border-radius: 9999px;
					font-size: 0.875rem;
					font-weight: 600;
				}
				.is-style-pill a:hover {
					background-color: var(--wp--preset--color--contrast-3, currentColor);
					color: var(--wp--preset--color--base, #fff);
				}',
			)
		);
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark list', 'twentytwentyfour' ),
				'inline_style' => '
				.is-style-checkmark-list {
					list-style-type: "\2713";
				}
				.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
		register_block_style(
			'core/navigation-link',
			array(
				'name'         => 'arrow-link',
				'label'        => __( 'Arrow', 'twentytwentyfour' ),
				'inline_style' => '
				.is-style-arrow-link .wp-block-navigation-item__content:after {
					content: "\2197";
					padding-inline-start: 0.25rem;
					vertical-align: middle;
					text-decoration: none;
					display: inline-block;
				}',
			)
		);
		register_block_style(
			'core/heading',
			array(
				'name'         => 'asterisk',
				'label'        => __( 'With asterisk', 'twentytwentyfour' ),
				'inline_style' => "
				.is-style-asterisk:before {
					content: '';
					width: 1.5rem;
					height: 3rem;
					background: var(--wp--preset--color--contrast-2, currentColor);
					clip-path: path('M11.93.684v8.039l5.633-5.633 1.216 1.23-5.66 5.66h8.04v1.737H13.2l5.701 5.701-1.23 1.23-5.742-5.742V21h-1.737v-8.094l-5.77 5.77-1.23-1.217 5.743-5.742H.842V9.98h8.162l-5.701-5.7 1.23-1.231 5.66 5.66V.684h1.737Z');
					display: block;
				}
				.is-style-asterisk:empty:before {
					content: none;
				}
				.is-style-asterisk:-moz-only-whitespace:before {
					content: none;
				}
				.is-style-asterisk.has-text-align-center:before {
					margin: 0 auto;
				}
				.is-style-asterisk.has-text-align-right:before {
					margin-left: auto;
				}
				.rtl .is-style-asterisk.has-text-align-left:before {
					margin-right: auto;
				}",
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfour_block_styles' );

/**
 * Enqueue block stylesheets.
 */
if ( ! function_exists( 'twentytwentyfour_block_stylesheets' ) ) :
	function twentytwentyfour_block_stylesheets() {
		wp_enqueue_block_style(
			'core/button',
			array(
				'handle' => 'twentytwentyfour-button-style-outline',
				'src'    => get_parent_theme_file_uri( 'assets/css/button-outline.css' ),
				'ver'    => wp_get_theme( get_template() )->get( 'Version' ),
				'path'   => get_parent_theme_file_path( 'assets/css/button-outline.css' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfour_block_stylesheets' );

// Диагностика пользователя и прав доступа
add_action('wp_footer', 'debug_user_permissions');
function debug_user_permissions() {
    if (!is_user_logged_in()) {
        echo "<!-- DEBUG: Пользователь НЕ авторизован -->";
        return;
    }
    
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $user_roles = $user->roles;
    $can_manage_options = current_user_can('manage_options');
    
    echo "<!-- DEBUG: Пользователь авторизован -->";
    echo "<!-- DEBUG: User ID: $user_id -->";
    echo "<!-- DEBUG: User roles: " . implode(', ', $user_roles) . " -->";
    echo "<!-- DEBUG: Can manage options: " . ($can_manage_options ? 'YES' : 'NO') . " -->";
}

// Экстренное восстановление прав администратора
add_action('init', 'emergency_admin_restore');
function emergency_admin_restore() {
    if (!is_user_logged_in()) {
        return;
    }
    
    $user = wp_get_current_user();
    if ($user->ID > 0 && !in_array('administrator', $user->roles)) {
        // Проверяем, должен ли пользователь быть администратором
        $user_login = $user->user_login;
        if (in_array($user_login, ['admin', 'administrator', 'edolohovgm'])) {
            $user->set_role('administrator');
        }
    }
}

// Принудительные права администратора
add_filter('user_has_cap', 'force_admin_capabilities', 10, 4);
function force_admin_capabilities($allcaps, $caps, $args, $user) {
    if (!is_user_logged_in()) {
        return $allcaps;
    }
    
    $current_user = wp_get_current_user();
    if ($current_user->ID > 0) {
        $user_login = $current_user->user_login;
        if (in_array($user_login, ['admin', 'administrator', 'edolohovgm'])) {
            // Даем все права администратора
            $allcaps['manage_options'] = true;
            $allcaps['edit_posts'] = true;
            $allcaps['edit_pages'] = true;
            $allcaps['edit_others_posts'] = true;
            $allcaps['edit_others_pages'] = true;
            $allcaps['delete_posts'] = true;
            $allcaps['delete_pages'] = true;
            $allcaps['delete_others_posts'] = true;
            $allcaps['delete_others_pages'] = true;
            $allcaps['publish_posts'] = true;
            $allcaps['publish_pages'] = true;
        }
    }
    
    return $allcaps;
}

// REST API доступ
add_filter('rest_authentication_errors', 'allow_rest_api_access');
function allow_rest_api_access($result) {
    return null; // Разрешаем доступ
}

// Убираем ограничения REST API
add_filter('rest_pre_serve_request', 'remove_rest_api_restrictions');
function remove_rest_api_restrictions($served, $result, $request, $server) {
    // Убираем заголовки, которые могут блокировать доступ
    return $served;
}

// Простой переключатель языков
add_action('wp_head', 'add_simple_language_switcher');
function add_simple_language_switcher() {
    $current_lang = get_current_language();
    $current_url = home_url($_SERVER['REQUEST_URI']);
    
    // Убираем текущий языковой префикс
    $base_url = $current_url;
    $base_url = str_replace('/es/', '/', $base_url);
    $base_url = str_replace('/en/', '/', $base_url);
    $base_url = str_replace('/es', '', $base_url);
    $base_url = str_replace('/en', '', $base_url);
    
    // Создаем URL для каждого языка
    $ru_url = $base_url;
    $es_url = str_replace(home_url(), home_url() . '/es', $base_url);
    $en_url = str_replace(home_url(), home_url() . '/en', $base_url);
    
    // Если это главная страница
    if (rtrim($base_url, '/') === rtrim(home_url(), '/')) {
        $es_url = home_url('/es/');
        $en_url = home_url('/en/');
    }
    ?>
    <div id="language-switcher" style="position: fixed; top: 20px; right: 20px; background: white; border: 2px solid #007cba; border-radius: 8px; padding: 12px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-family: Arial, sans-serif; font-size: 14px;">
        <div style="margin-bottom: 8px; font-weight: bold; color: #333;">Язык:</div>
        <div style="display: flex; gap: 8px;">
            <a href="<?php echo esc_url($ru_url); ?>" style="display: inline-block; padding: 6px 12px; background: <?php echo $current_lang === 'ru' ? '#007cba' : '#f0f0f0'; ?>; color: <?php echo $current_lang === 'ru' ? 'white' : '#333'; ?>; text-decoration: none; border-radius: 4px; font-weight: bold; transition: all 0.2s;">RU</a>
            <a href="<?php echo esc_url($es_url); ?>" style="display: inline-block; padding: 6px 12px; background: <?php echo $current_lang === 'es' ? '#007cba' : '#f0f0f0'; ?>; color: <?php echo $current_lang === 'es' ? 'white' : '#333'; ?>; text-decoration: none; border-radius: 4px; font-weight: bold; transition: all 0.2s;">ES</a>
            <a href="<?php echo esc_url($en_url); ?>" style="display: inline-block; padding: 6px 12px; background: <?php echo $current_lang === 'en' ? '#007cba' : '#f0f0f0'; ?>; color: <?php echo $current_lang === 'en' ? 'white' : '#333'; ?>; text-decoration: none; border-radius: 4px; font-weight: bold; transition: all 0.2s;">EN</a>
        </div>
    </div>
    <?php
}

// Скрипт для сохранения языка
add_action('wp_footer', 'add_language_persistence_script');
function add_language_persistence_script() {
    ?>
    <script>
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
    
    function showLanguageLoader() {
        var loader = document.createElement('div');
        loader.id = 'language-loader';
        loader.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        `;
        loader.innerHTML = `
            <div style="text-align: center;">
                <div style="
                    width: 40px;
                    height: 40px;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #007cba;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 20px;
                "></div>
                <div style="color: #333; font-size: 16px;">Переключение языка...</div>
            </div>
        `;
        
        var style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(loader);
    }
    
    (function() {
        var savedLang = localStorage.getItem('user_language');
        var currentUrl = window.location.href;
        var currentLang = getCurrentLangFromUrl(currentUrl);
        
        console.log('Language Debug:', {
            savedLang: savedLang,
            currentUrl: currentUrl,
            currentLang: currentLang
        });
        
        if (!savedLang) {
            var browserLang = getBrowserLanguage();
            console.log('No saved language, browser lang:', browserLang);
            if (browserLang && browserLang !== 'ru') {
                localStorage.setItem('user_language', browserLang);
                setCookie('user_language', browserLang, 365);
                var newUrl = switchLanguageInUrl(currentUrl, browserLang);
                if (newUrl !== currentUrl) {
                    console.log('Redirecting to browser language:', newUrl);
                    showLanguageLoader();
                    window.location.href = newUrl;
                    return;
                }
            } else {
                localStorage.setItem('user_language', 'ru');
                setCookie('user_language', 'ru', 365);
                console.log('Set default language to ru');
            }
        }
        
        if (savedLang && savedLang !== currentLang) {
            var newUrl = switchLanguageInUrl(currentUrl, savedLang);
            if (newUrl !== currentUrl) {
                console.log('Redirecting to saved language:', newUrl);
                showLanguageLoader();
                window.location.href = newUrl;
                return;
            }
        }
        
        console.log('No redirect needed');
    })();
    
    document.addEventListener('DOMContentLoaded', function() {
        var switcher = document.getElementById('language-switcher');
        if (switcher) {
            var links = switcher.querySelectorAll('a');
            links.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var targetLang = getLangFromLink(this);
                    if (targetLang) {
                        localStorage.setItem('user_language', targetLang);
                        setCookie('user_language', targetLang, 365);
                        var newUrl = switchLanguageInUrl(window.location.href, targetLang);
                        if (newUrl !== window.location.href) {
                            showLanguageLoader();
                            window.location.href = newUrl;
                        }
                    }
                });
            });
        }
    });
    
    function getBrowserLanguage() {
        var lang = navigator.language || navigator.userLanguage;
        if (lang.startsWith('es')) return 'es';
        if (lang.startsWith('en')) return 'en';
        return 'ru';
    }
    
    function getCurrentLangFromUrl(url) {
        if (url.includes('/es/')) return 'es';
        if (url.includes('/en/')) return 'en';
        return 'ru';
    }
    
    function getLangFromLink(link) {
        var text = link.textContent.trim();
        if (text === 'RU') return 'ru';
        if (text === 'ES') return 'es';
        if (text === 'EN') return 'en';
        return null;
    }
    
    function switchLanguageInUrl(url, targetLang) {
        var baseUrl = url;
        
        baseUrl = baseUrl.replace(/\/es\//g, '/');
        baseUrl = baseUrl.replace(/\/en\//g, '/');
        baseUrl = baseUrl.replace(/\/es$/, '');
        baseUrl = baseUrl.replace(/\/en$/, '');
        
        if (targetLang === 'ru') {
            return baseUrl;
        } else {
            var domain = baseUrl.split('/')[0] + '//' + baseUrl.split('/')[2];
            var path = baseUrl.replace(domain, '');
            return domain + '/' + targetLang + path;
        }
    }
    </script>
    <?php
}

// PHP редирект на основе cookie
add_action('template_redirect', 'simple_language_redirect');
function simple_language_redirect() {
    if (isset($_COOKIE['user_language'])) {
        $saved_lang = sanitize_text_field($_COOKIE['user_language']);
        $current_lang = get_current_language();
        
        if ($saved_lang !== $current_lang && in_array($saved_lang, ['ru', 'es', 'en'])) {
            $current_url = home_url($_SERVER['REQUEST_URI']);
            
            $base_url = $current_url;
            $base_url = str_replace('/es/', '/', $base_url);
            $base_url = str_replace('/en/', '/', $base_url);
            $base_url = str_replace('/es', '', $base_url);
            $base_url = str_replace('/en', '', $base_url);
            
            $new_url = $base_url;
            if ($saved_lang !== 'ru') {
                $new_url = str_replace(home_url(), home_url() . '/' . $saved_lang, $base_url);
                if (rtrim($base_url, '/') === rtrim(home_url(), '/')) {
                    $new_url = home_url('/' . $saved_lang . '/');
                }
            }
            
            if ($new_url !== $current_url) {
                wp_redirect($new_url, 302);
                exit;
            }
        }
    }
}

// Система переводов WooCommerce
add_filter('gettext', 'translate_woocommerce_strings', 20, 3);
function translate_woocommerce_strings($translated_text, $text, $domain) {
    if (is_admin() || $domain !== 'woocommerce') {
        return $translated_text;
    }
    
    $current_lang = get_current_language();
    return get_translation($text, $current_lang);
}

// Переводы полей формы чекаута
add_filter('woocommerce_checkout_fields', 'translate_checkout_fields');
function translate_checkout_fields($fields) {
    $current_lang = get_current_language();
    
    $field_keys = array(
        'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 
        'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 
        'billing_country', 'billing_phone', 'billing_email',
        'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 
        'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 
        'shipping_country'
    );
    
    foreach ($field_keys as $field_key) {
        $section = strpos($field_key, 'billing_') === 0 ? 'billing' : 'shipping';
        $original_label = $fields[$section][$field_key]['label'];
        $translated_label = get_translation($original_label, $current_lang);
        
        if ($translated_label !== $original_label) {
            $fields[$section][$field_key]['label'] = $translated_label;
        }
    }
    
    return $fields;
}

// Переводы сообщений WooCommerce
add_filter('woocommerce_add_to_cart_message', 'translate_add_to_cart_message');
function translate_add_to_cart_message($message) {
    $current_lang = get_current_language();
    
    $message = str_replace('has been added to your cart.', get_translation('has been added to your cart.', $current_lang), $message);
    $message = str_replace('have been added to your cart.', get_translation('have been added to your cart.', $current_lang), $message);
    $message = str_replace('View cart', get_translation('View cart', $current_lang), $message);
    
    return $message;
}

// Переводы кнопок товаров
add_filter('woocommerce_product_add_to_cart_text', 'translate_add_to_cart_button');
function translate_add_to_cart_button($text) {
    $current_lang = get_current_language();
    return get_translation('Add to cart', $current_lang);
}

// Переводы заголовков страниц WooCommerce
add_filter('woocommerce_page_title', 'translate_woocommerce_page_title');
function translate_woocommerce_page_title($title) {
    $current_lang = get_current_language();
    return get_translation($title, $current_lang);
}

// Переводы кастомных полей и инструкций
add_filter('woocommerce_form_field', 'translate_custom_form_fields', 10, 4);
function translate_custom_form_fields($field, $key, $args, $value) {
    $current_lang = get_current_language();
    $translations = get_translations($current_lang);
    
    foreach ($translations as $original => $translated) {
        $field = str_replace($original, $translated, $field);
    }
    
    return $field;
}

// Переводы способов доставки
add_filter('woocommerce_shipping_method_title', 'translate_shipping_methods');
function translate_shipping_methods($title) {
    $current_lang = get_current_language();
    return get_translation($title, $current_lang);
}

// Переводы заголовков секций и других элементов
add_filter('woocommerce_cart_totals_order_total_html', 'translate_cart_totals');
function translate_cart_totals($html) {
    $current_lang = get_current_language();
    $translations = get_translations($current_lang);
    
    foreach ($translations as $original => $translated) {
        $html = str_replace($original, $translated, $html);
    }
    
    return $html;
}

// Переводы для всех остальных строк
add_filter('gettext', 'translate_remaining_strings', 25, 3);
function translate_remaining_strings($translated_text, $text, $domain) {
    if (is_admin()) {
        return $translated_text;
    }
    
    $current_lang = get_current_language();
    return get_translation($text, $current_lang);
}

// Специальные переводы для страниц WooCommerce
add_filter('the_content', 'translate_woocommerce_page_content');
function translate_woocommerce_page_content($content) {
    if (!is_wc_endpoint_url() && !is_checkout() && !is_cart() && !is_account_page()) {
        return $content;
    }
    
    $current_lang = get_current_language();
    $translations = get_translations($current_lang);
    
    foreach ($translations as $original => $translated) {
        $content = str_replace($original, $translated, $content);
    }
    
    return $content;
}

// Переводы для страницы 404
add_filter('the_content', 'translate_404_page_content');
function translate_404_page_content($content) {
    if (!is_404()) {
        return $content;
    }
    
    $current_lang = get_current_language();
    $translations = get_translations($current_lang);
    
    foreach ($translations as $original => $translated) {
        $content = str_replace($original, $translated, $content);
    }
    
    return $content;
}

// Переводы для заголовков страниц
add_filter('wp_title', 'translate_page_title');
function translate_page_title($title) {
    if (is_404()) {
        $current_lang = get_current_language();
        return get_translation('Страница не найдена', $current_lang);
    }
    return $title;
}

// JavaScript для перевода страницы 404
add_action('wp_footer', 'translate_404_with_javascript');
function translate_404_with_javascript() {
    if (!is_404()) {
        return;
    }
    
    $current_lang = get_current_language();
    if ($current_lang === 'ru') {
        return;
    }
    
    $translations = get_translations($current_lang);
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var translations = <?php echo json_encode($translations); ?>;
        
        var title = document.querySelector('h1');
        if (title) {
            var titleText = title.textContent.trim();
            if (translations[titleText]) {
                title.textContent = translations[titleText];
            }
        }
        
        var description = document.querySelector('p');
        if (description) {
            var descText = description.textContent.trim();
            if (translations[descText]) {
                description.textContent = translations[descText];
            }
        }
        
        var searchButton = document.querySelector('input[type="submit"]');
        if (searchButton) {
            var buttonText = searchButton.value;
            if (translations[buttonText]) {
                searchButton.value = translations[buttonText];
            }
        }
    });
    </script>
    <?php
}

// Перевод текста на странице "Спасибо за заказ"
add_filter('woocommerce_thankyou_order_received_text', 'translate_thankyou_order_received_text', 10, 2);
function translate_thankyou_order_received_text($text, $order) {
    if (is_admin()) {
        return $text;
    }
    $current_lang = get_current_language();
    return get_translation($text, $current_lang);
}

// WooCommerce стили и функции (основные)
add_action('wp_head', 'simple_cart_styling');
function simple_cart_styling() {
    if (is_cart() || is_checkout()) {
        ?>
        <style>
        .woocommerce-cart-form, .woocommerce-checkout-form {
            max-width: 100%;
        }
        .woocommerce table.shop_table {
            border-collapse: collapse;
            width: 100%;
        }
        .woocommerce table.shop_table th,
        .woocommerce table.shop_table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        @media (max-width: 768px) {
            .woocommerce table.shop_table {
                font-size: 14px;
            }
            .woocommerce table.shop_table th,
            .woocommerce table.shop_table td {
                padding: 8px;
            }
        }
        </style>
        <?php
    }
}

// Убираем ссылки на Weekly Meal Plan
add_action('wp_footer', 'remove_weekly_meal_plan_links');
function remove_weekly_meal_plan_links() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var links = document.querySelectorAll('a[href*="weekly-meal-plan"], a[href*="product/weekly-meal-plan"]');
        
        links.forEach(function(link) {
            link.removeAttribute('href');
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            link.style.cursor = 'default';
            link.style.textDecoration = 'none';
        });
    });
    </script>
    <?php
}

// WooCommerce инициализация
add_action('wp_footer', function() {
    ?>
    <script>
    (function () {
        var c = document.body.className;
        c = c.replace(/woocommerce-no-js/, 'woocommerce-js');
        document.body.className = c;
    })();
    </script>
    <?php
});
