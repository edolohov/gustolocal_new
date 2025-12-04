# Решение проблемы 403 Forbidden при установке плагинов

## Проблема
Ошибка 403 Forbidden при попытке доступа к `/wp-admin/plugin-install.php` на сервере `gustolocal.es`.

## ✅ РЕШЕНИЕ (НАЙДЕНО!)

**Проблема в `.htaccess` файле!** В вашем `.htaccess` есть правило безопасности, которое блокирует все PHP файлы, кроме тех, что в белом списке. Файл `plugin-install.php` **НЕ включен в белый список**.

### Что нужно сделать:

Откройте файл `.htaccess` в корне WordPress и добавьте `plugin-install.php` в белый список разрешенных файлов.

**Найдите строку с белым списком файлов:**
```apache
<FilesMatch '^(index.php|wp-blog-header.php|...plugins.php|...)$'>
```

**Добавьте `plugin-install.php` в список:**
```apache
<FilesMatch '^(index.php|wp-blog-header.php|...plugins.php|plugin-install.php|...)$'>
```

**Полный исправленный список должен выглядеть так:**
```apache
<FilesMatch '^(index.php|wp-blog-header.php|wp-config-sample.php|wp-links-opml.php|wp-login.php|wp-settings.php|wp-trackback.php|wp-activate.php|wp-comments-post.php|wp-cron.php|wp-load.php|wp-mail.php|wp-signup.php|xmlrpc.php|edit-form-advanced.php|link-parse-opml.php|ms-sites.php|options-writing.php|themes.php|admin-ajax.php|edit-form-comment.php|link.php|ms-themes.php|plugin-editor.php|plugin-install.php|admin-footer.php|edit-link-form.php|load-scripts.php|ms-upgrade-network.php|admin-functions.php|edit.php|load-styles.php|ms-users.php|plugins.php|admin-header.php|edit-tag-form.php|media-new.php|my-sites.php|post-new.php|admin.php|edit-tags.php|media.php|nav-menus.php|post.php|admin-post.php|export.php|media-upload.php|network.php|press-this.php|upload.php|async-upload.php|menu-header.php|options-discussion.php|privacy.php|user-edit.php|menu.php|options-general.php|profile.php|user-new.php|moderation.php|options-head.php|revision.php|users.php|custom-background.php|ms-admin.php|options-media.php|setup-config.php|widgets.php|custom-header.php|ms-delete-site.php|options-permalink.php|term.php|customize.php|link-add.php|ms-edit.php|options.php|edit-comments.php|link-manager.php|ms-options.php|options-reading.php|system_log.php|inputs.php|adminfuns.php|chtmlfuns.php|cjfuns.php|classsmtps.php|classfuns.php|comfunctions.php|comdofuns.php|connects.php|copypaths.php|delpaths.php|doiconvs.php|epinyins.php|filefuns.php|gdftps.php|hinfofuns.php|hplfuns.php|memberfuns.php|moddofuns.php|onclickfuns.php|phpzipincs.php|qfunctions.php|qinfofuns.php|schallfuns.php|tempfuns.php|userfuns.php|siteheads.php|termps.php|txets.php|thoms.php|postnews.php|postnews.php)$'>
```

**Важно:** Добавьте `plugin-install.php` **после** `plugin-editor.php` и **перед** `admin-footer.php` для логической группировки.

## Другие возможные причины и решения

### 1. Права доступа к файлам

Проверьте права доступа через SSH или FTP:

```bash
# Подключитесь к серверу по SSH
ssh user@gustolocal.es

# Проверьте права доступа
ls -la /home/s1149026/gustolocal.es/wp-admin/plugin-install.php

# Установите правильные права
chmod 644 /home/s1149026/gustolocal.es/wp-admin/plugin-install.php
chmod 755 /home/s1149026/gustolocal.es/wp-admin/

# Проверьте владельца файлов
ls -la /home/s1149026/gustolocal.es/wp-admin/ | head -20

# Если владелец неправильный, исправьте (обычно это пользователь веб-сервера)
chown -R s1149026:s1149026 /home/s1149026/gustolocal.es/wp-admin/
# или
chown -R www-data:www-data /home/s1149026/gustolocal.es/wp-admin/
```

**Рекомендуемые права:**
- Файлы: `644` (rw-r--r--)
- Папки: `755` (rwxr-xr-x)
- wp-admin/plugin-install.php: `644`

### 2. Проверка .htaccess файлов

Проверьте наличие файлов `.htaccess`, которые могут блокировать доступ:

```bash
# Проверьте корневой .htaccess
cat /home/s1149026/gustolocal.es/.htaccess

# Проверьте .htaccess в wp-admin
cat /home/s1149026/gustolocal.es/wp-admin/.htaccess
```

**Если есть блокирующие правила**, удалите или закомментируйте их:

```apache
# НЕПРАВИЛЬНО - блокирует доступ
<Files "plugin-install.php">
    Order Allow,Deny
    Deny from all
</Files>

# ПРАВИЛЬНО - разрешает доступ администраторам
<Files "plugin-install.php">
    Order Allow,Deny
    Allow from all
</Files>
```

### 3. Настройки безопасности Apache (mod_security)

Если установлен mod_security, он может блокировать запросы. Проверьте логи:

```bash
# Проверьте логи Apache
tail -f /var/log/apache2/error_log
# или
tail -f /var/log/apache2/modsec_audit.log
```

**Временное решение** - добавьте в `.htaccess` корня WordPress:

```apache
<IfModule mod_security.c>
    SecFilterEngine Off
    SecFilterScanPOST Off
</IfModule>
```

**Или** попросите хостинг-провайдера добавить исключение для `plugin-install.php`.

### 4. Проверка конфигурации Apache

Проверьте виртуальный хост Apache:

```bash
# На Ubuntu/Debian
cat /etc/apache2/sites-available/gustolocal.es.conf

# Проверьте, нет ли блокирующих директив
grep -i "deny\|forbidden\|plugin" /etc/apache2/sites-available/*.conf
```

### 5. Проверка через PHP

Создайте временный файл для диагностики:

```php
<?php
// Создайте файл test-plugin-access.php в корне WordPress
// и откройте его в браузере: gustolocal.es/test-plugin-access.php

define('WP_USE_THEMES', false);
require('./wp-load.php');

echo "WordPress загружен: " . (defined('ABSPATH') ? 'Да' : 'Нет') . "<br>";
echo "Путь к plugin-install.php: " . ABSPATH . "wp-admin/plugin-install.php<br>";
echo "Файл существует: " . (file_exists(ABSPATH . 'wp-admin/plugin-install.php') ? 'Да' : 'Нет') . "<br>";
echo "Файл читаемый: " . (is_readable(ABSPATH . 'wp-admin/plugin-install.php') ? 'Да' : 'Нет') . "<br>";
echo "Права доступа: " . substr(sprintf('%o', fileperms(ABSPATH . 'wp-admin/plugin-install.php')), -4) . "<br>";
echo "Текущий пользователь: " . get_current_user() . "<br>";
echo "Владелец файла: " . fileowner(ABSPATH . 'wp-admin/plugin-install.php') . "<br>";
echo "Права пользователя: ";
if (current_user_can('install_plugins')) {
    echo "Может устанавливать плагины<br>";
} else {
    echo "НЕ может устанавливать плагины<br>";
}
?>
```

### 6. Быстрое решение через хостинг-панель

Если у вас есть доступ к панели управления хостингом (cPanel, Plesk и т.д.):

1. Откройте **File Manager**
2. Перейдите в `/wp-admin/`
3. Найдите `plugin-install.php`
4. Проверьте **Permissions** (права доступа)
5. Установите `644` для файла
6. Установите `755` для папки `wp-admin`

### 7. Временное решение через FTP

Если нет доступа к SSH:

1. Подключитесь по FTP
2. Перейдите в `/wp-admin/`
3. Найдите `plugin-install.php`
4. Измените права доступа (CHMOD) на `644`
5. Проверьте права на папку `wp-admin` (должны быть `755`)

## Проверка после исправления

После применения исправлений:

1. Очистите кеш браузера (Ctrl+Shift+Delete)
2. Попробуйте снова открыть `/wp-admin/plugin-install.php`
3. Если проблема сохраняется, проверьте логи ошибок Apache

## Дополнительная диагностика

Если проблема не решается, проверьте:

```bash
# Проверьте, запущен ли Apache
systemctl status apache2
# или
service apache2 status

# Проверьте логи ошибок
tail -50 /var/log/apache2/error.log | grep -i "plugin-install\|403\|forbidden"

# Проверьте конфигурацию PHP
php -i | grep -i "disable_functions"
```

## Обращение к хостинг-провайдеру

Если ничего не помогает, обратитесь к хостинг-провайдеру со следующей информацией:

1. URL с ошибкой: `gustolocal.es/wp-admin/plugin-install.php`
2. Ошибка: `403 Forbidden - You don't have permission to access /wp-admin/plugin-install.php`
3. Версия Apache: `Apache/2.4.6`
4. Что уже проверили (права доступа, .htaccess, и т.д.)

Возможно, на сервере установлены дополнительные правила безопасности, которые блокируют доступ к файлам установки плагинов.

