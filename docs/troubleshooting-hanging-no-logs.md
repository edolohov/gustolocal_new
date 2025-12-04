# Диагностика зависания сайта без записей в debug.log

## Проблема
Сайт зависает, но в `debug.log` нет новых записей (последняя дата 7/15/25). Это означает, что проблема происходит **до** того, как WordPress успевает залогировать ошибку.

## Возможные причины

1. **Бесконечный цикл в коде** (плагин или тема)
2. **Проблема на уровне PHP** (до загрузки WordPress)
3. **Проблема с веб-сервером** (Apache/Nginx)
4. **Таймаут выполнения** (скрипт выполняется слишком долго)
5. **Проблема с базой данных** (медленные запросы или блокировки)

---

## Шаг 1: Проверьте логи PHP (error_log)

Логи PHP находятся **вне** WordPress и могут показать ошибки, которые не попали в debug.log.

### Где найти error_log:

**Через SFTP/FTP:**
- `/home/s1149026/gustolocal.es/error_log` (в корне сайта)
- `/home/s1149026/gustolocal.es/logs/error_log`
- `/home/s1149026/gustolocal.es/logs/php_error.log`

**Через панель хостинга:**
- Обычно в разделе "Логи" или "Error Logs"
- Или в "File Manager" → найдите файл `error_log`

**Через SSH:**
```bash
# Найти все error_log файлы
find /home/s1149026 -name "error_log" -type f -mtime -1

# Посмотреть последние ошибки
tail -100 /home/s1149026/gustolocal.es/error_log

# Следить за логами в реальном времени
tail -f /home/s1149026/gustolocal.es/error_log
```

---

## Шаг 2: Проверьте логи веб-сервера (Apache)

Логи Apache показывают проблемы на уровне веб-сервера.

**Через SSH:**
```bash
# Логи Apache (обычные пути)
tail -100 /var/log/apache2/error.log
tail -100 /var/log/httpd/error_log

# Или через панель хостинга
# Обычно в разделе "Логи" → "Apache Error Log"
```

**Что искать:**
- `timeout` - таймауты
- `mod_php` - проблемы с PHP модулем
- `Premature end of script headers` - скрипт не завершился
- `Connection reset` - соединение разорвано

---

## Шаг 3: Включите логирование на уровне PHP

Добавьте в начало файла `wp-config.php` (самые первые строки, перед всем остальным):

```php
<?php
// ВРЕМЕННО: Принудительное логирование PHP ошибок
ini_set('log_errors', 1);
ini_set('error_log', '/home/s1149026/gustolocal.es/error_log');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('max_execution_time', 60); // Увеличить время выполнения для диагностики

// Остальной код wp-config.php...
```

Это заставит PHP логировать ошибки **до** загрузки WordPress.

---

## Шаг 4: Отключите плагины через базу данных

Если сайт зависает при загрузке, WordPress может не успеть выполнить код. Отключите плагины через базу данных:

### Через phpMyAdmin:

1. Откройте phpMyAdmin
2. Выберите базу данных `s1149026_gusto`
3. Найдите таблицу `dp___bk_250951_wp_options` (с вашим префиксом)
4. Найдите опцию `active_plugins`
5. Измените значение на: `a:0:{}` (пустой массив)
6. Сохраните

**SQL запрос:**
```sql
UPDATE `dp___bk_250951_wp_options` 
SET `option_value` = 'a:0:{}' 
WHERE `option_name` = 'active_plugins';
```

После этого сайт должен загрузиться без плагинов.

---

## Шаг 5: Проверьте проблемные плагины через переименование

Если сайт все еще зависает после отключения плагинов через БД:

1. **Через SFTP** перейдите в `/home/s1149026/gustolocal.es/wp-content/plugins/`
2. **Переименуйте папку** `plugins` в `plugins-disabled`
3. **Создайте пустую папку** `plugins`
4. Проверьте, загружается ли сайт

Если загружается - проблема точно в плагинах.

---

## Шаг 6: Создайте тестовый файл для диагностики

Создайте файл `test-load.php` в корне WordPress:

```php
<?php
// test-load.php - диагностика загрузки WordPress

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log');
ini_set('max_execution_time', 30);

echo "Начало загрузки...<br>\n";
flush();

echo "Попытка загрузить wp-load.php...<br>\n";
flush();

$start_time = microtime(true);

try {
    define('WP_USE_THEMES', false);
    require_once('./wp-load.php');
    
    $load_time = microtime(true) - $start_time;
    echo "✅ WordPress загружен за " . round($load_time, 2) . " секунд<br>\n";
    echo "Версия WordPress: " . get_bloginfo('version') . "<br>\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>\n";
    echo "Файл: " . $e->getFile() . "<br>\n";
    echo "Строка: " . $e->getLine() . "<br>\n";
} catch (Error $e) {
    echo "❌ Фатальная ошибка: " . $e->getMessage() . "<br>\n";
    echo "Файл: " . $e->getFile() . "<br>\n";
    echo "Строка: " . $e->getLine() . "<br>\n";
}

$total_time = microtime(true) - $start_time;
echo "<br>Общее время выполнения: " . round($total_time, 2) . " секунд<br>\n";

if ($total_time > 25) {
    echo "<br>⚠️ <strong>ВНИМАНИЕ:</strong> Загрузка заняла больше 25 секунд!<br>\n";
}
?>
```

**Использование:**
1. Загрузите файл в корень WordPress через SFTP
2. Откройте в браузере: `https://gustolocal.es/test-load.php`
3. Если скрипт зависает - проблема в `wp-load.php` или плагинах, которые загружаются автоматически

---

## Шаг 7: Проверьте плагины must-use (mu-plugins)

Плагины в папке `mu-plugins` загружаются **автоматически** и не отключаются через админку.

**Проверьте:**
```bash
# Через SFTP
/home/s1149026/gustolocal.es/wp-content/mu-plugins/

# Если есть файлы - временно переименуйте папку
mu-plugins → mu-plugins-disabled
```

---

## Шаг 8: Проверьте тему

Если проблема не в плагинах, проверьте тему:

1. **Через SFTP** перейдите в `/home/s1149026/gustolocal.es/wp-content/themes/`
2. **Переименуйте активную тему** (например, `twentytwentyfour` → `twentytwentyfour-disabled`)
3. WordPress автоматически переключится на стандартную тему
4. Проверьте, загружается ли сайт

---

## Шаг 9: Проверьте functions.php

Проблема может быть в `functions.php` темы или дочерней темы.

**Временное решение:**
1. Переименуйте `functions.php` в `functions.php.backup`
2. Создайте пустой файл `functions.php`
3. Проверьте, загружается ли сайт

Если загружается - проблема в `functions.php`.

---

## Шаг 10: Проверьте .htaccess

Проблема может быть в правилах `.htaccess`, которые вызывают редиректы или блокировки.

**Временное решение:**
1. Переименуйте `.htaccess` в `.htaccess.backup`
2. Создайте минимальный `.htaccess`:

```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

3. Проверьте, загружается ли сайт

---

## Шаг 11: Проверьте базу данных

Медленные запросы к БД могут вызывать зависания.

**Через phpMyAdmin:**
```sql
-- Проверьте активные процессы
SHOW PROCESSLIST;

-- Если видите долгие запросы (State = "Locked" или время > 10 сек) - это проблема
```

**Что делать:**
- Убейте долгие запросы: `KILL <process_id>;`
- Проверьте, нет ли блокировок таблиц

---

## Шаг 12: Проверьте использование ресурсов

**Через SSH:**
```bash
# Проверьте использование памяти PHP
ps aux | grep php

# Проверьте использование CPU
top

# Проверьте использование диска
df -h
```

Если PHP процессы "съедают" всю память или CPU - это проблема.

---

## Быстрая диагностика (чек-лист)

Выполните по порядку:

- [ ] Проверен `error_log` PHP (не `debug.log`)
- [ ] Проверены логи Apache
- [ ] Включено принудительное логирование PHP в `wp-config.php`
- [ ] Отключены плагины через базу данных
- [ ] Переименована папка `plugins`
- [ ] Проверена папка `mu-plugins`
- [ ] Переименована активная тема
- [ ] Переименован `functions.php`
- [ ] Переименован `.htaccess`
- [ ] Проверена база данных на долгие запросы
- [ ] Проверено использование ресурсов сервера

---

## Типичные проблемы и решения

### Проблема: Бесконечный цикл в плагине

**Симптомы:**
- Сайт зависает навсегда
- В логах ничего нет
- CPU на 100%

**Решение:**
- Отключите плагины через БД
- Включайте по одному, чтобы найти проблемный

### Проблема: Таймаут выполнения

**Симптомы:**
- Сайт загружается очень долго, потом таймаут
- В логах: `Maximum execution time exceeded`

**Решение:**
```php
// В wp-config.php
set_time_limit(300); // 5 минут
ini_set('max_execution_time', 300);
```

### Проблема: Нехватка памяти

**Симптомы:**
- Сайт зависает
- В логах: `Allowed memory size exhausted`

**Решение:**
```php
// В wp-config.php
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '1024M');
```

---

## Что делать после нахождения проблемы

1. **Сохраните логи** - они понадобятся для исправления
2. **Отключите проблемный плагин/код**
3. **Обратитесь к разработчику** с логами
4. **Обновите или замените** проблемный компонент
5. **Верните настройки** (отключите отладку, верните .htaccess и т.д.)

---

## Полезные команды для SSH

```bash
# Найти все логи за последний час
find /home/s1149026 -name "*.log" -type f -mmin -60

# Посмотреть последние ошибки во всех логах
tail -50 /home/s1149026/gustolocal.es/error_log
tail -50 /home/s1149026/gustolocal.es/wp-content/debug.log
tail -50 /var/log/apache2/error.log

# Проверить, какие PHP процессы запущены
ps aux | grep php-fpm
ps aux | grep apache

# Проверить использование памяти
free -h

# Проверить нагрузку на сервер
uptime
```




