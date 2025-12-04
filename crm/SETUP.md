# Пошаговая инструкция по настройке

## Архитектура системы

**Важно понимать:**
- Я создал только **приемники сообщений** (webhook endpoints и worker)
- Сам **бот создается в Telegram** через @BotFather
- Бот получает сообщения от пользователей → отправляет их на ваш сервер → мы сохраняем в БД

## Шаг 1: Создание Telegram бота

1. Откройте Telegram и найдите **@BotFather**
2. Отправьте команду `/newbot`
3. Следуйте инструкциям:
   - Введите имя бота (например: "Моя CRM Панель")
   - Введите username бота (должен заканчиваться на `bot`, например: `my_crm_bot`)
4. **Сохраните токен**, который выдаст BotFather (выглядит как: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)

## Шаг 2: Настройка базы данных

1. Создайте базу данных MySQL (или используйте существующую)
2. Выполните SQL скрипт из `database/schema.sql`

```sql
-- Выполните содержимое файла database/schema.sql
```

## Шаг 3: Настройка config.php

Откройте `crm/config.php` и заполните:

```php
// База данных
define('DB_HOST', 'localhost');  // или ваш хост
define('DB_NAME', 'crm_database');  // имя вашей БД
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');

// Telegram - вставьте токен из шага 1
define('TELEGRAM_BOT_TOKEN', '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');
define('TELEGRAM_WEBHOOK_SECRET', '');  // опционально, для безопасности
```

## Шаг 4: Выбор способа получения сообщений

У вас есть **два варианта**:

### Вариант A: Webhook (если есть домен с HTTPS)

Telegram будет отправлять сообщения на ваш сервер автоматически.

1. Убедитесь, что ваш домен доступен по HTTPS
2. Установите webhook:

```bash
curl -X POST "https://api.telegram.org/bot<ВАШ_ТОКЕН>/setWebhook?url=https://yourdomain.com/crm/webhooks/telegram.php"
```

Замените:
- `<ВАШ_ТОКЕН>` на токен из шага 1
- `yourdomain.com` на ваш домен

3. Проверьте, что webhook установлен:

```bash
curl "https://api.telegram.org/bot<ВАШ_ТОКЕН>/getWebhookInfo"
```

### Вариант B: Long Polling (если нет HTTPS или хотите запустить на отдельном сервере)

Ваш сервер сам будет запрашивать новые сообщения у Telegram.

1. Загрузите файлы на сервер, где может работать PHP скрипт постоянно
2. Запустите worker:

```bash
cd /path/to/crm
php bot/telegram-worker.php
```

3. Для постоянной работы используйте screen:

```bash
screen -S telegram-bot
cd /path/to/crm
php bot/telegram-worker.php
# Нажмите Ctrl+A, затем D для отсоединения
```

4. Или настройте supervisor/systemd для автозапуска

**Важно:** При использовании long polling, убедитесь что webhook удален:

```bash
curl -X POST "https://api.telegram.org/bot<ВАШ_ТОКЕН>/deleteWebhook"
```

## Шаг 5: Проверка работы

1. Найдите вашего бота в Telegram (по username, который вы указали)
2. Отправьте боту команду `/start`
3. Отправьте боту любое текстовое сообщение
4. Откройте `https://yourdomain.com/crm/index.php` - сообщение должно появиться
5. Проверьте логи: `crm/logs/webhook.log`

## Что происходит:

1. Пользователь пишет боту в Telegram
2. Telegram отправляет update на ваш webhook (или worker получает через long polling)
3. Скрипт сохраняет сообщение в БД
4. Вы видите сообщение в интерфейсе `index.php`

## Настройка Instagram и WhatsApp

Эти настройки делаются позже, когда будут готовы токены от Meta.

1. Создайте приложение в Meta Developers: https://developers.facebook.com/
2. Настройте Instagram Messaging API или WhatsApp Business API
3. Получите токены и добавьте в `config.php`
4. Настройте webhook'и в панели Meta

## Troubleshooting

**Бот не отвечает:**
- Проверьте токен в `config.php`
- Проверьте логи `logs/webhook.log`
- Убедитесь, что webhook установлен (для варианта A) или worker запущен (для варианта B)

**Сообщения не сохраняются:**
- Проверьте подключение к БД в `config.php`
- Убедитесь, что таблицы созданы (выполнили `schema.sql`)
- Проверьте права доступа к БД

**Webhook не работает:**
- Убедитесь, что домен доступен по HTTPS
- Проверьте, что файл `webhooks/telegram.php` доступен
- Проверьте логи сервера

