#!/bin/bash
# Автоматический деплой на продакшн через SFTP

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/load-env.sh"

cd "$PROJECT_ROOT"

echo "🚀 Начинаю деплой на продакшн (gustolocal.es)..."

# Проверка наличия rsync или использование sftp
if command -v rsync &> /dev/null; then
    echo "📦 Использую rsync для деплоя..."
    
    # Деплой основной темы gustolocal
    echo "📦 Деплою тему gustolocal..."
    rsync -avz --delete -e "ssh -p ${SFTP_PORT} -o StrictHostKeyChecking=no" \
      wp-content/themes/gustolocal/ \
      ${SFTP_USER}@${SFTP_HOST}:${SFTP_REMOTE_PATH}/wp-content/themes/gustolocal/
    
    # Деплой родительской темы twentytwentyfour (если используется)
    if [ -d "twentytwentyfour" ]; then
        echo "📦 Деплою тему twentytwentyfour..."
        rsync -avz --delete -e "ssh -p ${SFTP_PORT} -o StrictHostKeyChecking=no" \
          twentytwentyfour/ \
          ${SFTP_USER}@${SFTP_HOST}:${SFTP_REMOTE_PATH}/wp-content/themes/twentytwentyfour/
    fi
    
    # Деплой плагина
    echo "📦 Деплою плагин weekly-meal-builder..."
    rsync -avz --delete -e "ssh -p ${SFTP_PORT} -o StrictHostKeyChecking=no" \
      weekly-meal-builder/ \
      ${SFTP_USER}@${SFTP_HOST}:${SFTP_REMOTE_PATH}/wp-content/plugins/weekly-meal-builder/
    
    echo "✅ Деплой завершен через rsync!"
else
    echo "⚠️  rsync не найден, используйте FileZilla или установите rsync"
    echo "   brew install rsync  # для macOS"
    exit 1
fi

