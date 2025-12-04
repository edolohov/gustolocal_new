#!/bin/bash
# Деплой плагина weekly-meal-builder через SFTP с паролем

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/load-env.sh"

cd "$PROJECT_ROOT"

echo "🚀 Деплою плагин weekly-meal-builder на продакшн..."

# Проверяем наличие sshpass
if ! command -v sshpass &> /dev/null; then
    echo "❌ sshpass не установлен. Установите: brew install hudochenkov/sshpass/sshpass"
    echo ""
    echo "Или используйте FileZilla:"
    echo "   Хост: ${SFTP_HOST}"
    echo "   Порт: ${SFTP_PORT}"
    echo "   Пользователь: ${SFTP_USER}"
    echo "   Путь: ${SFTP_REMOTE_PATH}/wp-content/plugins/weekly-meal-builder/"
    exit 1
fi

# Получаем пароль из .env.local
SFTP_PASS=$(grep "^SFTP_PASS=" "$PROJECT_ROOT/.env.local" | cut -d '=' -f2- | tr -d '"' | tr -d "'")

if [ -z "$SFTP_PASS" ]; then
    echo "❌ Пароль SFTP не найден в .env.local"
    exit 1
fi

# Создаем временный файл с командами SFTP
TMP_SFTP=$(mktemp)
cat > "$TMP_SFTP" <<EOF
cd ${SFTP_REMOTE_PATH}/wp-content/plugins
put -r weekly-meal-builder weekly-meal-builder
quit
EOF

echo "📦 Загружаю файлы плагина..."
sshpass -p "$SFTP_PASS" sftp -P "${SFTP_PORT}" -o StrictHostKeyChecking=no "${SFTP_USER}@${SFTP_HOST}" < "$TMP_SFTP"

if [ $? -eq 0 ]; then
    echo "✅ Плагин успешно задеплоен!"
    echo ""
    echo "📝 Следующие шаги:"
    echo "   1. Откройте админку WordPress"
    echo "   2. Перейдите в Meal Builder > Блюда"
    echo "   3. Откройте любое блюдо для редактирования"
    echo "   4. В правой колонке 'Параметры блюда' должны появиться новые поля:"
    echo "      - Фото (URL)"
    echo "      - Alt текст для фото (SEO)"
    echo "      - КБЖУ (100 г)"
else
    echo "❌ Ошибка при деплое"
    exit 1
fi

rm -f "$TMP_SFTP"

