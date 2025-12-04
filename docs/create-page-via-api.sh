#!/bin/bash
# Скрипт для создания страницы через WordPress REST API

WP_URL="https://gustolocal.es"
WP_USER="admin"
WP_PASS="hiLKov15!"

# Получаем nonce для аутентификации
NONCE=$(curl -s -X POST "${WP_URL}/wp-admin/admin-ajax.php" \
  -d "action=rest-nonce" \
  -c /tmp/wp-cookies.txt | grep -oP '(?<=nonce":")[^"]+')

# Создаём страницу через REST API
RESPONSE=$(curl -s -X POST "${WP_URL}/wp-json/wp/v2/pages" \
  -u "${WP_USER}:${WP_PASS}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Горячие обеды для офисов и детских садов",
    "slug": "corporate-meals",
    "status": "draft",
    "content": ""
  }')

echo "$RESPONSE" | jq '.'

PAGE_ID=$(echo "$RESPONSE" | jq -r '.id // empty')

if [ -n "$PAGE_ID" ] && [ "$PAGE_ID" != "null" ]; then
  echo "Страница создана с ID: $PAGE_ID"
  echo "URL для редактирования: ${WP_URL}/wp-admin/post.php?post=${PAGE_ID}&action=edit"
else
  echo "Ошибка создания страницы"
  exit 1
fi

