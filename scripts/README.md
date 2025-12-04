# Скрипты для автономной работы

Все скрипты используют переменные из `.env.local` (не коммитится в git).

## Использование

### 1. Деплой на продакшн
```bash
./scripts/deploy-to-prod.sh
```
Загружает темы и плагины на сервер через SFTP.

### 2. Коммит в GitHub
```bash
./scripts/git-commit.sh "описание изменений"
```
Автоматически коммитит и пушит изменения в GitHub.

### 3. Подключение к базе данных
```bash
./scripts/db-connect.sh prod
```
Открывает MySQL консоль для работы с БД.

### 4. WP-CLI команды
```bash
./scripts/wp-cli.sh "plugin list"
./scripts/wp-cli.sh "user list"
./scripts/wp-cli.sh "option get siteurl"
```
Выполняет WP-CLI команды на сервере через SSH.

## Требования

- `rsync` - для деплоя (установка: `brew install rsync`)
- `mysql` клиент - для работы с БД (установка: `brew install mysql-client`)
- SSH доступ к серверу настроен

## Безопасность

- Все пароли хранятся в `.env.local` (не в git)
- Скрипты не выводят пароли в консоль
- `.env.local` в `.gitignore` - никогда не коммитится

