#!/bin/bash
# Скрипт для развёртывания страницы корпоративных обедов
# Использование: ./deploy-corporate-meals.sh

echo "=== Развёртывание страницы корпоративных обедов ==="

# Параметры SFTP
SFTP_HOST="82.29.185.42"
SFTP_PORT="65002"
SFTP_USER="u850527203"
SFTP_PASS="hiLKov15!"

# Пути
LOCAL_PATTERN="wp-content/themes/gustolocal/patterns/corporate-meals.php"
REMOTE_PATTERN="wp-content/themes/gustolocal/patterns/corporate-meals.php"
LOCAL_FUNCTIONS="wp-content/themes/gustolocal/functions.php"
REMOTE_FUNCTIONS="wp-content/themes/gustolocal/functions.php"
LOCAL_SETUP="docs/setup-corporate-meals.php"
REMOTE_SETUP="setup-corporate-meals.php"

echo "1. Загрузка паттерна на сервер..."
sshpass -p "$SFTP_PASS" sftp -P "$SFTP_PORT" -o StrictHostKeyChecking=no "$SFTP_USER@$SFTP_HOST" <<EOF
put $LOCAL_PATTERN $REMOTE_PATTERN
put $LOCAL_FUNCTIONS $REMOTE_FUNCTIONS
put $LOCAL_SETUP $REMOTE_SETUP
quit
EOF

if [ $? -eq 0 ]; then
    echo "✓ Файлы успешно загружены"
else
    echo "✗ Ошибка загрузки файлов"
    exit 1
fi

echo ""
echo "2. Для завершения настройки:"
echo "   - Откройте в браузере: https://gustolocal.es/setup-corporate-meals.php"
echo "   - Или выполните через WordPress CLI:"
echo "     wp eval-file setup-corporate-meals.php"
echo ""
echo "3. После проверки удалите setup-corporate-meals.php с сервера"

