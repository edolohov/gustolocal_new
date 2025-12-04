## Install: Google Sheets Menu Draft MVP

1) Open your Sheet: https://docs.google.com/spreadsheets/d/1ig-Uo2foSNrJhuAVsgYEjm9DkfOcpboTx9qQJXwr6FA/edit?usp=sharing
2) Extensions → Apps Script → paste contents of `generateMenu.gs` into Code.gs
3) Save. Reload the Sheet. You will see menu: “Menu”.
4) Run → “Menu → Generate Draft (this week)”. Approve permissions on first run.
5) Преобразование существующих меню: откройте вкладку с меню (например, `3 ноября`) → “Menu → Transform Current Tab → CSV Sheet” → получите лист `<tab>_CSV` c колонками `Название, Цена, Единица, Категория, Теги, Состав, Аллергены, Активно`.
6) Категории автоматически приводятся к фиксированному набору и сортируются в строгом порядке: Завтраки и сладкое → Авторские сэндвичи и перекусы → Паста ручной работы → Супы и крем-супы → Основные блюда → Гарниры и салаты → Для запаса / в морозильник.
7) Инструкции по приготовлению: находясь на `*_CSV` листе, нажмите “Menu → Generate Instructions for CSV” и получите лист `Instructions`.
8) PDF/Doc: “Menu → Export Instructions to Doc/PDF” — создаст Google Doc (далее File → Download → PDF).

Notes
- Для LLM используем только актуальную инструкцию из `/Users/eugene/Documents/instructions/Инструкция для LLM .pdf`; описание стиля также в `docs/instructions-style.md`.

LLM (опционально)
- В Apps Script → Project Settings → Script properties добавьте:
  - LLM_PROVIDER = openai
  - LLM_API_KEY = sk-...
  - LLM_MODEL = gpt-4o-mini (или другой)
- Генератор автоматически попробует LLM и валидирует результат правилами.

