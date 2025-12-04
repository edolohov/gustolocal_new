# CRM Панель - Сбор входящих сообщений

Минимальная система для сбора входящих сообщений из Telegram, Instagram и WhatsApp.

## ⚠️ Важно: Что это такое?

Это **не сам бот**, а система для **приема и сохранения сообщений** от ботов и других каналов.

**Архитектура:**
- Telegram бот создается через @BotFather (см. `SETUP.md`)
- Instagram/WhatsApp настраиваются через Meta API
- Наш код **принимает** сообщения от этих каналов и **сохраняет** в БД
- Интерфейс `index.php` показывает все входящие сообщения

## Быстрый старт

**См. подробную инструкцию:** [`SETUP.md`](SETUP.md)

### 1. Создайте Telegram бота

1. Найдите @BotFather в Telegram
2. Отправьте `/newbot` и следуйте инструкциям
3. **Сохраните токен** (нужен для шага 2)

### 2. Настройка базы данных

Выполните SQL скрипт из `database/schema.sql` для создания таблиц.

### 3. Настройка конфигурации

Откройте `config.php` и заполните:

- **База данных**: DB_HOST, DB_NAME, DB_USER, DB_PASS
- **Telegram**: TELEGRAM_BOT_TOKEN (токен из шага 1)
- **Instagram/WhatsApp**: настройте позже, когда будут токены

### 4. Настройка Telegram бота

#### Вариант A: Webhook (рекомендуется для production)

```bash
curl -X POST "https://api.telegram.org/bot<YOUR_TOKEN>/setWebhook?url=https://yourdomain.com/crm/webhooks/telegram.php"
```

Если используете secret token:
```bash
curl -X POST "https://api.telegram.org/bot<YOUR_TOKEN>/setWebhook?url=https://yourdomain.com/crm/webhooks/telegram.php&secret_token=YOUR_SECRET"
```

#### Вариант B: Long Polling (для тестирования или если webhook недоступен)

Запустите worker скрипт:

```bash
php bot/telegram-worker.php
```

Или через screen/tmux для постоянной работы:
```bash
screen -S telegram-bot
php bot/telegram-worker.php
# Нажмите Ctrl+A, затем D для отсоединения
```

Для автоматического перезапуска можно использовать supervisor или systemd.

**Важно**: При использовании long polling, удалите webhook:
```bash
curl -X POST "https://api.telegram.org/bot<YOUR_TOKEN>/deleteWebhook"
```

### 4. Настройка webhook'ов для Instagram и WhatsApp

Настройте webhook'и через Meta App Dashboard:
- Instagram: https://developers.facebook.com/apps/
- WhatsApp: https://business.facebook.com/

URL для webhook'ов:
- Instagram: `https://yourdomain.com/crm/webhooks/instagram.php`
- WhatsApp: `https://yourdomain.com/crm/webhooks/whatsapp.php`

При настройке укажите `hub.verify_token` из конфига.

### 5. Права доступа

Убедитесь, что папка `logs/` существует и доступна для записи:

```bash
mkdir -p logs
chmod 755 logs
```

## Структура проекта

```
crm/
├── config.php              # Конфигурация
├── index.php               # Интерфейс просмотра сообщений
├── .htaccess              # Защита файлов
├── database/
│   └── schema.sql         # SQL схема БД
├── includes/
│   ├── database.php       # Класс для работы с БД
│   ├── logger.php         # Логгер
│   └── telegram-api.php   # Класс для работы с Telegram API
├── webhooks/
│   ├── telegram.php       # Webhook для Telegram
│   ├── instagram.php      # Webhook для Instagram
│   └── whatsapp.php       # Webhook для WhatsApp
└── bot/
    └── telegram-worker.php # Long polling worker для Telegram
```

## Проверка работы

1. Откройте `https://yourdomain.com/crm/index.php` - должны увидеть интерфейс
2. Отправьте тестовое сообщение в Telegram бот
3. Проверьте логи в `logs/webhook.log`
4. Обновите страницу - сообщение должно появиться

## Логирование

Все webhook'и логируются в `logs/webhook.log`. Проверяйте этот файл при проблемах.

## Безопасность

⚠️ **ВАЖНО**: 
- Не коммитьте `config.php` с реальными токенами в git
- Добавьте `config.php` в `.gitignore`
- Настройте `.htaccess` для защиты конфигурационных файлов
- Используйте secret token для Telegram webhook'ов

## Telegram бот - команды

Бот поддерживает следующие команды:
- `/start` - Начать работу с ботом
- `/help` - Показать справку
- `/status` - Проверить статус бота

## Следующие шаги

- [x] Базовая структура и webhook'и
- [x] Long polling для Telegram
- [x] Обработка команд бота
- [ ] Добавить авторизацию
- [ ] Добавить возможность отвечать на сообщения
- [ ] Добавить фильтры и поиск
- [ ] Добавить обработку медиа-файлов
