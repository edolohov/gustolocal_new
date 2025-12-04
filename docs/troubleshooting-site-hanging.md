# Диагностика зависания сайта WordPress

## Быстрая диагностика проблемы с плагинами

Если сайт не загружается и зависает, скорее всего проблема в одном из плагинов. Вот пошаговая инструкция по диагностике.

---

## Шаг 1: Включить логирование ошибок WordPress

### Через wp-config.php

1. Подключитесь к серверу через **FTP** или **SSH**
2. Найдите файл `wp-config.php` в корне WordPress
3. Добавьте или измените следующие строки (перед строкой `/* That's all, stop editing! */`):

```php
// Включить отладку
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);

// Логировать все ошибки
define('SCRIPT_DEBUG', true);
```

**Важно:** `WP_DEBUG_DISPLAY` должен быть `false`, чтобы ошибки не показывались на сайте, а только логировались.

### Через functions.php (временное решение)

Если нет доступа к `wp-config.php`, добавьте в начало файла `functions.php` вашей темы:

```php
// ВРЕМЕННО: Включить логирование
ini_set('log_errors', 1);
ini_set('error_log', ABSPATH . 'wp-content/debug.log');
error_reporting(E_ALL);
```

---

## Шаг 2: Где найти логи

### 1. Логи WordPress (debug.log)

**Путь:** `/wp-content/debug.log`

```bash
# Через SSH
tail -f /path/to/wordpress/wp-content/debug.log

# Или последние 100 строк
tail -100 /path/to/wordpress/wp-content/debug.log
```

**Через FTP/File Manager:**
- Перейдите в `/wp-content/`
- Найдите файл `debug.log`
- Скачайте и откройте в текстовом редакторе

### 2. Логи PHP (error_log)

**Возможные пути:**
- `/logs/error_log` (в корне сайта)
- `/logs/php_error.log`
- `/error_log` (в корне сайта)
- `/public_html/error_log`

**Через панель хостинга:**
- Обычно в разделе "Логи" или "Error Logs"
- Или в "File Manager" → найдите файл `error_log`

**Через SSH:**
```bash
# Найти все error_log файлы
find /home/username -name "error_log" -type f

# Посмотреть последние ошибки
tail -100 /path/to/error_log
```

### 3. Логи веб-сервера (Apache/Nginx)

**Apache:**
```bash
# Обычные пути
/var/log/apache2/error.log
/var/log/httpd/error_log
/home/username/logs/error_log

# Посмотреть последние ошибки
tail -100 /var/log/apache2/error.log | grep -i "php\|fatal\|error"
```

**Nginx:**
```bash
/var/log/nginx/error.log
tail -100 /var/log/nginx/error.log
```

### 4. Логи через панель хостинга

**cPanel:**
- `Metrics` → `Errors` или `Error Log`
- Или `File Manager` → найдите `error_log` в корне

**Plesk:**
- `Logs` → `Error Log`

---

## Шаг 3: Отключить плагины (если сайт не загружается)

### Способ 1: Через переименование папки (самый быстрый)

**Через FTP/SSH:**

1. Подключитесь к серверу
2. Перейдите в `/wp-content/plugins/`
3. Переименуйте папку `plugins` в `plugins-disabled`

```bash
# Через SSH
cd /path/to/wordpress/wp-content/
mv plugins plugins-disabled
mkdir plugins
```

4. Откройте сайт - он должен загрузиться (без плагинов)
5. Постепенно возвращайте плагины:
   - Переименуйте `plugins-disabled` обратно в `plugins`
   - Переименуйте папки плагинов по одному, чтобы найти проблемный

### Способ 2: Через базу данных (если есть доступ к phpMyAdmin)

1. Откройте phpMyAdmin
2. Выберите базу данных WordPress
3. Найдите таблицу `wp_options` (может быть с префиксом)
4. Найдите опцию `active_plugins`
5. Очистите значение (оставьте пустым: `a:0:{}`)
6. Сохраните

**SQL запрос:**
```sql
UPDATE wp_options 
SET option_value = 'a:0:{}' 
WHERE option_name = 'active_plugins';
```

### Способ 3: Через файл .maintenance (временное отключение)

Создайте файл `.maintenance` в корне WordPress:

```php
<?php
// Файл: .maintenance (в корне WordPress)
$upgrading = time();
```

Это покажет страницу "Техническое обслуживание" и даст время для диагностики.

---

## Шаг 4: Найти проблемный плагин

### Метод "половинного деления"

1. Отключите **половину** плагинов (переименуйте папки)
2. Проверьте, загружается ли сайт
3. Если загружается - проблема в отключенных плагинах
4. Если не загружается - проблема в активных плагинах
5. Повторяйте, пока не найдете проблемный плагин

### Через логи

Ищите в логах:
- Имя плагина в стек-трейсе ошибки
- `Fatal error` или `PHP Fatal error`
- `Maximum execution time exceeded`
- `Memory limit exceeded`

**Примеры ошибок:**
```
Fatal error: Uncaught Error: Call to undefined function...
in /wp-content/plugins/plugin-name/file.php on line 123
```

```
PHP Fatal error: Allowed memory size of 134217728 bytes exhausted
```

---

## Шаг 5: Типичные проблемы и решения

### Проблема: Превышен лимит памяти

**В логах:**
```
Fatal error: Allowed memory size exhausted
```

**Решение:**
Добавьте в `wp-config.php`:
```php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

Или в `.htaccess`:
```apache
php_value memory_limit 256M
```

### Проблема: Превышено время выполнения

**В логах:**
```
Maximum execution time of 30 seconds exceeded
```

**Решение:**
Добавьте в `wp-config.php`:
```php
set_time_limit(300); // 5 минут
```

Или в `.htaccess`:
```apache
php_value max_execution_time 300
```

### Проблема: Конфликт плагинов

**Симптомы:**
- Сайт работает с одним плагином, но не работает с другим
- Ошибки появляются только при активации обоих плагинов

**Решение:**
1. Отключите один из конфликтующих плагинов
2. Обратитесь к разработчикам плагинов
3. Проверьте обновления плагинов

### Проблема: Устаревший плагин

**Симптомы:**
- Плагин не обновлялся давно
- Ошибки совместимости с новой версией WordPress/PHP

**Решение:**
1. Обновите плагин до последней версии
2. Если обновления нет - найдите альтернативу
3. Временно отключите плагин

---

## Шаг 6: Проверка через консоль браузера

1. Откройте сайт в браузере
2. Нажмите `F12` (открыть DevTools)
3. Перейдите на вкладку **Console**
4. Посмотрите на ошибки JavaScript
5. Перейдите на вкладку **Network**
6. Проверьте, какие запросы зависают (статус "pending")

---

## Шаг 7: Проверка через командную строку (SSH)

### Проверка загрузки WordPress

```bash
# Перейдите в корень WordPress
cd /path/to/wordpress

# Попробуйте загрузить WordPress через PHP CLI
php -r "define('WP_USE_THEMES', false); require('wp-load.php'); echo 'WordPress loaded successfully';"
```

Если команда зависает - проблема в коде WordPress или плагинах.

### Проверка конкретного плагина

```bash
# Создайте тестовый файл
cat > test-plugin.php << 'EOF'
<?php
define('WP_USE_THEMES', false);
require('wp-load.php');

$plugin = 'plugin-name/plugin-name.php'; // Замените на имя плагина
if (is_plugin_active($plugin)) {
    echo "Plugin is active\n";
} else {
    echo "Plugin is not active\n";
}
EOF

# Запустите
php test-plugin.php
```

---

## Шаг 8: Временное решение (быстрый фикс)

Если нужно срочно восстановить сайт:

1. **Отключите все плагины** (переименуйте папку `plugins`)
2. **Включите стандартную тему** (если используете кастомную)
3. **Очистите кеш** (если есть плагин кеширования)
4. **Проверьте, загружается ли сайт**

Если загружается - проблема точно в плагинах или теме.

---

## Полезные команды для диагностики

### Найти все PHP ошибки в логах

```bash
# В логах WordPress
grep -i "fatal\|error\|warning" /path/to/wp-content/debug.log | tail -50

# В логах PHP
grep -i "fatal\|error" /path/to/error_log | tail -50

# В логах Apache
grep -i "php\|fatal" /var/log/apache2/error.log | tail -50
```

### Проверить использование памяти

```bash
# Создайте файл phpinfo.php в корне WordPress
echo "<?php phpinfo(); ?>" > phpinfo.php

# Откройте в браузере: ваш-сайт.com/phpinfo.php
# Найдите "memory_limit"
```

### Проверить активные плагины через базу данных

```sql
-- Через phpMyAdmin или MySQL CLI
SELECT option_value 
FROM wp_options 
WHERE option_name = 'active_plugins';
```

---

## Контрольный список диагностики

- [ ] Включено логирование (`WP_DEBUG = true`)
- [ ] Проверены логи WordPress (`/wp-content/debug.log`)
- [ ] Проверены логи PHP (`error_log`)
- [ ] Проверены логи веб-сервера
- [ ] Отключены все плагины (проверка)
- [ ] Проверена консоль браузера (F12)
- [ ] Проверены лимиты памяти и времени выполнения
- [ ] Найден проблемный плагин (если проблема в плагинах)

---

## Что делать после нахождения проблемы

1. **Обновите проблемный плагин** до последней версии
2. **Проверьте совместимость** с версией WordPress и PHP
3. **Обратитесь к разработчику плагина** с логами ошибок
4. **Найдите альтернативу**, если плагин не поддерживается
5. **Отключите отладку** после решения проблемы:

```php
// В wp-config.php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
```

---

## Полезные плагины для диагностики

- **Query Monitor** - показывает все запросы к БД и ошибки
- **Debug Bar** - панель отладки в админке
- **Error Log Monitor** - показывает ошибки прямо в админке
- **Health Check & Troubleshooting** - диагностика проблем WordPress

---

## Контакты для помощи

Если проблема не решается:
1. Сохраните логи ошибок
2. Запишите шаги, которые привели к проблеме
3. Обратитесь к хостинг-провайдеру с логами
4. Обратитесь к разработчику проблемного плагина




