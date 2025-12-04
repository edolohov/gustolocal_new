#!/bin/bash
# Простой деплой плагина через SFTP (требует sshpass)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/load-env.sh"

cd "$PROJECT_ROOT"

echo "🚀 Деплою плагин weekly-meal-builder..."

# Параметры (из документации)
SFTP_HOST="82.29.185.42"
SFTP_PORT="65002"
SFTP_USER="u850527203"
SFTP_PASS="hiLKov15!"  # Из документации

REMOTE_PATH="${SFTP_REMOTE_PATH}/wp-content/plugins/weekly-meal-builder"

# Проверяем sshpass
if ! command -v sshpass &> /dev/null; then
    echo "❌ sshpass не установлен"
    echo "   Установите: brew install hudochenkov/sshpass/sshpass"
    echo ""
    echo "Или используйте FileZilla:"
    echo "   Хост: $SFTP_HOST"
    echo "   Порт: $SFTP_PORT"
    echo "   Пользователь: $SFTP_USER"
    echo "   Пароль: (из .env.local или документации)"
    echo "   Путь: $REMOTE_PATH"
    exit 1
fi

echo "📦 Загружаю файлы плагина..."

# Создаем архив и загружаем
cd "$PROJECT_ROOT"
tar -czf /tmp/wmb-plugin.tar.gz weekly-meal-builder/

sshpass -p "$SFTP_PASS" ssh -p "$SFTP_PORT" -o StrictHostKeyChecking=no "$SFTP_USER@$SFTP_HOST" <<EOF
cd ${SFTP_REMOTE_PATH}/wp-content/plugins
rm -rf weekly-meal-builder
mkdir -p weekly-meal-builder
EOF

sshpass -p "$SFTP_PASS" scp -P "$SFTP_PORT" -o StrictHostKeyChecking=no -r weekly-meal-builder/* "$SFTP_USER@$SFTP_HOST:$REMOTE_PATH/"

if [ $? -eq 0 ]; then
    echo "✅ Плагин успешно задеплоен!"
    echo ""
    echo "📝 Проверьте в админке WordPress:"
    echo "   Meal Builder > Блюда > Откройте любое блюдо"
    echo "   В правой колонке должны быть новые поля для фото и КБЖУ"
else
    echo "❌ Ошибка при деплое"
    exit 1
fi

rm -f /tmp/wmb-plugin.tar.gz

