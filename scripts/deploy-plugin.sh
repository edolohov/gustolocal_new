#!/bin/bash
# Деплой плагина weekly-meal-builder через SFTP (использует expect)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/load-env.sh"

cd "$PROJECT_ROOT"

echo "🚀 Деплою плагин weekly-meal-builder на продакшн..."

# Используем expect скрипт
"$SCRIPT_DIR/deploy-plugin.exp"

