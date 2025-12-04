#!/bin/bash
# Выполнение WP-CLI команд на сервере через SSH

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/load-env.sh"

if [ -z "$1" ]; then
    echo "Использование: $0 'wp command'"
    echo "Пример: $0 'plugin list'"
    echo "Пример: $0 'user list'"
    exit 1
fi

COMMAND="$@"

echo "🔧 Выполняю WP-CLI команду: wp $COMMAND"
echo ""

ssh -p ${SFTP_PORT} -o StrictHostKeyChecking=no ${SFTP_USER}@${SFTP_HOST} \
  "cd ${SFTP_REMOTE_PATH} && wp $COMMAND --allow-root"

