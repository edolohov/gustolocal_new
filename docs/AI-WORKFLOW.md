# AI Workflow для автономной работы

## Структура проекта

- **Локальная папка**: `/Users/eugene/Documents/site-repo/gustolocal-prod/`
- **Git репозиторий**: `https://github.com/edolohov/gustolocal_new`
- **Продакшн сервер**: Hostinger (82.29.185.42:65002)
- **Сайт**: `gustolocal.es`

## Credentials

Все credentials хранятся в `.env.local` (НЕ в git!)
Файл загружается автоматически через `scripts/load-env.sh`

## Workflow для AI

### 1. Внесение изменений в код
- Редактирую файлы локально в `gustolocal-prod/`
- Тестирую изменения (если возможно)
- Коммичу через: `./scripts/git-commit.sh "описание изменений"`

### 2. Деплой на продакшн
- Запускаю: `./scripts/deploy-to-prod.sh`
- Скрипт загрузит все изменения через SFTP
- Проверяю, что изменения применились на сайте

### 3. Работа с базой данных
- Подключаюсь через: `./scripts/db-connect.sh prod`
- Или создаю SQL скрипты в `scripts/db/` и выполняю их

### 4. Работа с WordPress Admin
- Использую WP-CLI: `./scripts/wp-cli.sh "plugin list"`
- Или создаю PHP скрипты в `scripts/wp/` для выполнения через браузер
- Admin доступ: `https://gustolocal.es/wp-admin` (admin / hiLKov15!)

## Важные файлы

- `wp-config.php` - НЕ коммитить, содержит пароли БД
- `.env.local` - НЕ коммитить, все credentials
- `wp-content/themes/gustolocal/` - основная кастомная тема (все функции здесь)
- `weekly-meal-builder/` - кастомный плагин
- `twentytwentyfour/` - родительская тема (если используется)

## Кастомные функции

Все кастомные функции находятся в теме `gustolocal`:
- **Основной файл**: `wp-content/themes/gustolocal/functions.php`
- **WooCommerce кастомизации**: в `functions.php` и `woocommerce/` папке
- **Meal Builder**: интегрирован в тему
- **Кастомные шаблоны**: в `templates/` и `patterns/`

## Команды для быстрого доступа

```bash
# Деплой
./scripts/deploy-to-prod.sh

# Коммит в GitHub
./scripts/git-commit.sh "описание"

# Подключение к БД
./scripts/db-connect.sh prod

# WP-CLI команды
./scripts/wp-cli.sh "plugin list"
./scripts/wp-cli.sh "user list"
./scripts/wp-cli.sh "option get siteurl"
```

## Безопасность

- Все пароли хранятся только в `.env.local`
- `.env.local` в `.gitignore` - никогда не коммитится
- При работе с БД используем только скрипты, не храним пароли в коде

## SFTP доступ

- **Хост**: 82.29.185.42
- **Порт**: 65002
- **Пользователь**: u850527203
- **Путь на сервере**: `/home/u850527203/domains/gustolocal.es/public_html`

## База данных

- **Имя БД**: u850527203_5vYEq
- **Пользователь**: u850527203_ZmKMJ
- **Хост**: localhost

## Точки отката (Releases)

Все важные версии помечаются git тегами для возможности отката.

### Текущие версии

- **llevatelo-1.0** - Точка отката на вечер 3 декабря 2024 (основная точка отката)
- v1.9.0-delivery-improvements
- v1.8.0-prod-sync
- v0.9.0-before-professional-architecture

### Как откатиться к предыдущей версии

```bash
# 1. Посмотреть все доступные теги
git tag -l

# 2. Откатиться к нужной версии локально
git checkout llevatelo-1.0

# 3. Задеплоить откат на продакшн
./scripts/deploy-to-prod.sh

# 4. Вернуться к последней версии (если нужно)
git checkout main
```

Или использовать скрипт: `./scripts/rollback.sh llevatelo-1.0`

### Создание новой точки отката

После успешного деплоя создаем тег с понятным названием:
```bash
git tag -a "название-версии" -m "Описание изменений"
git push origin "название-версии"
```

Примеры названий: `llevatelo-1.0`, `fix-checkout-2024-12-04`, `meal-builder-update-v2`

