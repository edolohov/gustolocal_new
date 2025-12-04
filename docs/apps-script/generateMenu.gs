var CATEGORY_ORDER = [
  'Завтраки и сладкое',
  'Авторские сэндвичи и перекусы',
  'Паста ручной работы',
  'Супы и крем-супы',
  'Основные блюда',
  'Гарниры и салаты',
  'Для запаса / в морозильник'
];
// Порядок категорий для инструкций (отличается от общего порядка)
var INSTRUCTION_CATEGORY_ORDER = [
  'Супы и крем-супы',
  'Паста ручной работы',
  'Основные блюда',
  'Гарниры и салаты',
  'Завтраки и сладкое',
  'Авторские сэндвичи и перекусы',
  'Для запаса / в морозильник'
];
var CATEGORY_DEFAULT = 'Основные блюда';
var MENU_STYLE_NOTE = 'Вес оставляем как указан в поле "Единица" (например, 1200 мл или 10 шт). ' +
  'Требуется аккуратное название, лаконичный состав (текст со строчной буквы, ингредиенты через запятую) и перечисление аллергенов. ' +
  'Исправляй грамматические опечатки (например "йцо" → "яйцо"). ' +
  'Категории и теги проверяй на орфографию, но сохраняй их исходные значения. ' +
  'Никаких смайликов или вольных интерпретаций.';
var COOKING_STYLE_NOTE = 'Блюда приходят как готовые, полуфабрикаты или заморозка. Объясняем, что вкуснее разогревать на плите/в аэрогриле, ' +
  'но при необходимости допустима микроволновка. Супы: бульон → зажарка → довести до лёгкого кипения. Паста: прогреть пасту и соус отдельно, затем объединить. ' +
  'Заморозка: доводим до готовности в духовке 15–30 минут. Тон деловой, без смайликов, в конце упоминаем хранение остатков.';

function onOpen() {
  var ui = SpreadsheetApp.getUi();
  ui.createMenu('Menu')
    .addItem('Transform Current Tab → CSV Sheet', 'transformCurrentTabToCsv')
    .addItem('Generate Instructions for CSV', 'generateInstructionsForCsvSheet')
    .addItem('Export Instructions to Doc/PDF', 'exportInstructionsToDoc')
    .addSeparator()
    .addItem('LLM: Test Connectivity', 'testLlmConnectivity')
    .addToUi();
}

function transformCurrentTabToCsv() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var src = ss.getActiveSheet();
  var sheetName = src.getName();
  if (sheetName.indexOf('_CSV') !== -1) {
    SpreadsheetApp.getActive().toast('Откройте исходный лист меню (без _CSV)', 'CSV Transform', 5);
    return;
  }
  var values = src.getDataRange().getValues();
  if (!values || !values.length) {
    SpreadsheetApp.getActive().toast('Лист пуст', 'CSV Transform', 5);
    return;
  }
  var headerIdx = findHeaderRowIndex(values);
  if (headerIdx === -1) headerIdx = 0;
  var headers = values[headerIdx];
  var rows = [];
  for (var i = headerIdx + 1; i < values.length; i++) {
    var parsed = parseMenuRow(headers, values[i]);
    if (parsed) rows.push(parsed);
  }
  if (!rows.length) {
    SpreadsheetApp.getActive().toast('Не найдено блюд после заголовка', 'CSV Transform', 5);
    return;
  }
  var normalized = enhanceMenuRows(rows, headers);
  // Сортируем по названию (без привязки к фиксированному порядку категорий)
  normalized.sort(function(a, b){
    return a.name.localeCompare(b.name);
  });
  var target = sheetName + '_CSV';
  var out = ss.getSheetByName(target) || ss.insertSheet(target);
  out.clear();
  
  // Используем все заголовки из исходника, добавляем "Активно" если его нет
  var outHeaders = [];
  for (var h = 0; h < headers.length; h++) {
    var headerName = (headers[h] || '').toString().trim();
    if (headerName) {
      outHeaders.push(headerName);
    }
  }
  // Добавляем "Активно" если его нет
  if (outHeaders.indexOf('Активно') === -1) {
    outHeaders.push('Активно');
  }
  
  out.getRange(1,1,1,outHeaders.length).setValues([outHeaders]);
  
  // Создаем маппинг заголовков для быстрого доступа
  var headerMap = {};
  headers.forEach(function(h, idx) {
    headerMap[h.toString().trim().toLowerCase()] = idx;
  });
  
  var data = normalized.map(function(item){
    var row = [];
    for (var h = 0; h < outHeaders.length; h++) {
      var headerName = outHeaders[h];
      var headerLower = headerName.toLowerCase();
      
      if (headerLower === 'название' || headerLower === 'наименование' || headerLower === 'блюдо') {
        row.push(item.name || '');
      } else if (headerLower === 'цена' || headerLower === 'price') {
        row.push(item.price || '');
      } else if (headerLower === 'единица' || headerLower === 'вес/кол-во' || headerLower === 'кол-во/вес' || headerLower === 'кол-во' || headerLower === 'вес') {
        row.push(item.unit || '');
      } else if (headerLower === 'категория' || headerLower === 'category') {
        row.push(item.category || '');
      } else if (headerLower === 'теги') {
        row.push(item.tags || '');
      } else if (headerLower === 'состав') {
        row.push(item.composition || '');
      } else if (headerLower === 'аллергены') {
        row.push(item.allergens || '');
      } else if (headerLower === 'активно') {
        // Если колонка "Активно" уже есть в исходнике, используем её значение (или 1 если пусто)
        var origIdx = headerMap[headerLower];
        if (origIdx !== undefined && item.originalRow && item.originalRow[origIdx] !== undefined && item.originalRow[origIdx] !== '') {
          row.push(item.originalRow[origIdx]);
        } else {
          row.push(1);
        }
      } else {
        // Для всех остальных колонок берем значение из исходника
        var origIdx = headerMap[headerLower];
        if (origIdx !== undefined && item.originalRow && item.originalRow[origIdx] !== undefined) {
          row.push(item.originalRow[origIdx]);
        } else {
          row.push('');
        }
      }
    }
    return row;
  });
  
  out.getRange(2,1,data.length,outHeaders.length).setValues(data);
  out.setFrozenRows(1);
  SpreadsheetApp.getActive().toast('CSV sheet created: ' + target + ' (' + data.length + ' rows)', 'CSV Transform', 5);
}

function enhanceMenuRows(rows, headers) {
  var apiKey = PropertiesService.getScriptProperties().getProperty('LLM_API_KEY');
  var model = PropertiesService.getScriptProperties().getProperty('LLM_MODEL') || 'gpt-4o-mini';
  if (!apiKey) {
    return rows.map(function(row) { return basicNormalizeRow(row, headers); });
  }
  var batchSize = 4;
  var result = [];
  for (var i = 0; i < rows.length; i += batchSize) {
    var batch = rows.slice(i, i + batchSize);
    var enriched = requestMenuBatch(batch, apiKey, model);
    if (!enriched.length) {
      Array.prototype.push.apply(result, batch.map(function(row) { return basicNormalizeRow(row, headers); }));
      continue;
    }
    for (var j = 0; j < batch.length; j++) {
      var source = batch[j];
      var entry = enriched[j] || {};
      // Принудительно используем name от LLM, если он есть (он должен быть исправлен)
      var finalName = entry.name && entry.name.trim() ? entry.name.trim() : prettifyName(source.name);
      // Аллергены: если LLM вернул - используем, иначе пытаемся взять из исходника
      var finalAllergens = (entry.allergens && entry.allergens.toString().trim()) || (source.allergens && source.allergens.toString().trim()) || '';
      // Категория: проверяем орфографию через LLM, но сохраняем исходное значение (или исправленное от LLM)
      var finalCategory = (entry.category && entry.category.trim()) || source.category || '';
      // Теги: проверяем орфографию через LLM, но сохраняем исходное значение (или исправленное от LLM)
      var finalTags = (entry.tags && entry.tags.toString().trim()) || source.tags || '';
      Logger.log('Row ' + j + ': name=' + finalName + ', allergens=' + finalAllergens);
      result.push({
        name: finalName,
        price: entry.price || source.price || '',
        unit: formatUnit(source.unit || entry.unit || ''),
        category: finalCategory,
        tags: finalTags,
        composition: formatComposition(entry.composition || source.composition || ''),
        allergens: finalAllergens,
        originalRow: source.originalRow // Сохраняем исходную строку для переноса всех колонок
      });
    }
  }
  return result;
}

function requestMenuBatch(batch, apiKey, model) {
  var prompt = buildMenuPrompt(batch);
  try {
    var res = UrlFetchApp.fetch('https://api.openai.com/v1/chat/completions', {
      method: 'post',
      contentType: 'application/json',
      headers: { Authorization: 'Bearer ' + apiKey },
      payload: JSON.stringify({
        model: model,
        messages: [
          { role: 'system', content: 'Ты кулинарный редактор. Возвращай только JSON.' },
          { role: 'user', content: prompt }
        ],
        temperature: 0.3
      }),
      muteHttpExceptions: true
    });
    var code = res.getResponseCode();
    var body = res.getContentText();
    Logger.log('Menu LLM code: ' + code);
    Logger.log('Menu LLM body: ' + body);
    if (code >= 300) {
      Logger.log('Menu LLM error ' + code + ': ' + body);
      SpreadsheetApp.getActive().toast('LLM menu error ' + code, 'LLM', 5);
      return [];
    }
    var payload = JSON.parse(body);
    var text = payload.choices && payload.choices[0] && payload.choices[0].message && payload.choices[0].message.content;
    return safeJsonArray(text || '[]');
  } catch (e) {
    Logger.log('Menu batch exception: ' + e);
    return [];
  }
}

function buildMenuPrompt(batch) {
  return 'Ты технолог общественного питания и шеф-редактор меню. Твоя задача — привести блюда к профессиональному формату для сайта.\n\n' +
    'КРИТИЧЕСКИ ВАЖНО — будь внимателен как технолог:\n\n' +
    '1. name (название блюда):\n' +
    '   - Внимательно проверь орфографию. Примеры исправлений:\n' +
    '     * "Фикассе" → "Фрикассе" (правильное написание)\n' +
    '     * "жаркое" → "Жаркое" (капитализация)\n' +
    '     * "винегрет" → "Винегрет"\n' +
    '     * "лапшс" → "лапшой" (полное слово)\n' +
    '   - Первая буква заглавная, остальные строчные (кроме собственных имен типа "Цезарь").\n' +
    '   - Если название обрезано — восстанови его логически (например, "Фикассе из курицы с грибами и сливками" → "Фрикассе из курицы с грибами и сливками").\n\n' +
    '2. category (категория):\n' +
    '   - ПРОВЕРЬ орфографию категории, но НЕ меняй её значение на другое.\n' +
    '   - Исправь только опечатки: "салаты" → "Салаты", "завтрак" → "Завтрак", "десерт" → "Десерт".\n' +
    '   - Сохрани исходное значение категории, только исправь орфографию и капитализацию.\n' +
    '   - Если категория написана правильно — верни её без изменений.\n\n' +
    '3. tags (теги):\n' +
    '   - ПРОВЕРЬ орфографию тегов, но НЕ меняй их содержание.\n' +
    '   - Исправь только опечатки и грамматические ошибки.\n' +
    '   - Сохрани все теги из исходника, только исправь орфографию.\n' +
    '   - Если теги написаны правильно — верни их без изменений.\n\n' +
    '4. allergens (аллергены) — ОБЯЗАТЕЛЬНО анализируй состав как технолог:\n' +
    '   - ГЛЮТЕН: если в составе есть мука, хлеб, лепешка, сухари, панировочные сухари, паста, лапша, тесто — ОБЯЗАТЕЛЬНО укажи "глютен".\n' +
    '   - МОЛОКО: если есть молоко, сливки, сыр, сметана, масло сливочное, творог — укажи "молоко".\n' +
    '   - ЯЙЦА: если есть яйца, яичный порошок — укажи "яйца".\n' +
    '   - ОРЕХИ/СЕМЕЧКИ: если есть орехи, семечки, кунжут, тахини — укажи конкретно (например, "кунжут", "орехи").\n' +
    '   - РЫБА/МОРЕПРОДУКТЫ: если есть рыба, морепродукты — укажи "рыба" или "морепродукты".\n' +
    '   - Если аллергенов нет — оставь пустую строку "".\n' +
    '   - НЕ пропускай это поле! Анализируй КАЖДЫЙ ингредиент в составе.\n\n' +
    '5. composition (состав):\n' +
    '   - Исправь все грамматические ошибки: "йцо" → "яйцо", "картфоель" → "картофель", "моруовь" → "морковь", "пеерц" → "перец".\n' +
    '   - Все строчными буквами, ингредиенты через запятую.\n' +
    '   - Убери дубликаты, приведи к единому формату.\n\n' +
    '6. price (цена):\n' +
    '   - Если цена указана в исходнике — верни её без изменений.\n' +
    '   - Если цена не указана — верни пустую строку "".\n\n' +
    '7. unit (единица):\n' +
    '   - Верни единицу измерения как есть из исходника.\n' +
    '   - Не добавляй "г" если его нет, не меняй формат.\n\n' +
    'Верни строго JSON-массив объектов {"name","price","unit","category","tags","composition","allergens"}.\n' +
    'ВАЖНО: категории и теги проверяй на орфографию, но сохраняй их исходные значения!\n\n' +
    'Входные данные:\n' + JSON.stringify(batch, null, 2);
}

function basicNormalizeRow(row, headers) {
  return {
    name: prettifyName(row.name),
    price: row.price || '',
    unit: formatUnit(row.unit),
    category: row.category || '', // Не маппим категорию, оставляем как есть
    tags: row.tags || '', // Оставляем теги как есть
    composition: formatComposition(row.composition || ''),
    allergens: row.allergens || '',
    originalRow: row.originalRow // Сохраняем исходную строку
  };
}

function generateInstructionsForCsvSheet() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var src = ss.getActiveSheet();
  if (src.getName().indexOf('_CSV') === -1) {
    SpreadsheetApp.getActive().toast('Откройте лист *_CSV', 'Instructions', 5);
    return;
  }
  var values = src.getDataRange().getValues();
  if (!values || values.length <= 1) return;
  var headers = values[0];
  var idxName = headers.indexOf('Название');
  var idxUnit = headers.indexOf('Единица');
  var idxCategory = headers.indexOf('Категория');
  var idxComposition = headers.indexOf('Состав');
  var idxAllergens = headers.indexOf('Аллергены');
  if (idxName === -1 || idxUnit === -1 || idxCategory === -1) {
    SpreadsheetApp.getActive().toast('Нет колонок Название/Единица/Категория', 'Instructions', 5);
    return;
  }
  var rows = [];
  for (var i = 1; i < values.length; i++) {
    var name = (values[i][idxName] || '').toString().trim();
    if (!name) continue;
    rows.push({
      name: name,
      unit: (values[i][idxUnit] || '').toString().trim(),
      category: (values[i][idxCategory] || '').toString().trim(),
      composition: idxComposition !== -1 ? (values[i][idxComposition] || '').toString().trim() : '',
      allergens: idxAllergens !== -1 ? (values[i][idxAllergens] || '').toString().trim() : ''
    });
  }
  if (!rows.length) return;
  // Теперь ВСЕ категории получают инструкции, но с разным форматом
  var generated = generateInstructionsViaLLM(rows);
  // Сортируем по порядку категорий для инструкций
  var dataWithInstructions = rows.map(function(row, idx){
    var entry = generated[idx] || {};
    return {
      name: row.name,
      category: row.category,
      unit: row.unit,
      kbju: entry.kbju_per_100g || '',
      instruction: entry.instruction || fallbackInstruction(row),
      composition: row.composition,
      allergens: entry.allergens || row.allergens
    };
  });
  dataWithInstructions.sort(function(a, b){
    var ai = instructionCategoryIndex(a.category);
    var bi = instructionCategoryIndex(b.category);
    if (ai === bi) return a.name.localeCompare(b.name);
    return ai - bi;
  });
  var sheet = ss.getSheetByName('Instructions') || ss.insertSheet('Instructions');
  sheet.clear();
  var outHeaders = ['Название','Категория','Единица','КБЖУ (100 г)','Инструкция','Состав','Аллергены'];
  sheet.getRange(1,1,1,outHeaders.length).setValues([outHeaders]);
  var data = dataWithInstructions.map(function(item){
    return [
      item.name,
      item.category,
      item.unit,
      item.kbju,
      item.instruction,
      item.composition,
      item.allergens
    ];
  });
  sheet.getRange(2,1,data.length,outHeaders.length).setValues(data);
  sheet.setFrozenRows(1);
  SpreadsheetApp.getActive().toast('Инструкции готовы: ' + data.length, 'Instructions', 5);
}

function generateInstructionsViaLLM(rows) {
  var apiKey = PropertiesService.getScriptProperties().getProperty('LLM_API_KEY');
  var model = PropertiesService.getScriptProperties().getProperty('LLM_MODEL') || 'gpt-4o-mini';
  if (!apiKey) return rows.map(function(r){ return { instruction: fallbackInstruction(r), allergens: r.allergens, kbju_per_100g: '' }; });
  var batchSize = 4;
  var result = [];
  for (var i = 0; i < rows.length; i += batchSize) {
    var batch = rows.slice(i, i + batchSize);
    var prompt = buildInstructionPrompt(batch);
    try {
    var res = UrlFetchApp.fetch('https://api.openai.com/v1/chat/completions', {
        method: 'post',
        contentType: 'application/json',
        headers: { Authorization: 'Bearer ' + apiKey },
        payload: JSON.stringify({
          model: model,
          messages: [
            { role: 'system', content: 'Ты шеф-редактор. Пишешь инструкции по формату заказов.' },
            { role: 'user', content: prompt }
          ],
          temperature: 0.4
        }),
        muteHttpExceptions: true
      });
    var code = res.getResponseCode();
    var body = res.getContentText();
    Logger.log('Instruction LLM code: ' + code);
    Logger.log('Instruction LLM body: ' + body);
    if (code >= 300) {
        Logger.log('Instruction LLM error ' + code + ': ' + body);
        Array.prototype.push.apply(result, batch.map(function(r){ return { instruction: fallbackInstruction(r), allergens: r.allergens, kbju_per_100g: '' }; }));
        continue;
      }
      var text = JSON.parse(body).choices[0].message.content;
      var payload = safeJsonArray(text || '[]');
      for (var j = 0; j < batch.length; j++) {
        var src = batch[j];
        var entry = payload[j] || {};
        result.push({
          instruction: formatInstructionText(entry, src),
          allergens: entry.allergens || src.allergens,
          kbju_per_100g: entry.kbju_per_100g || ''
        });
      }
    } catch (e) {
      Logger.log('Instruction batch exception: ' + e);
      Array.prototype.push.apply(result, batch.map(function(r){ return { instruction: fallbackInstruction(r), allergens: r.allergens, kbju_per_100g: '' }; }));
    }
  }
  return result;
}

function buildInstructionPrompt(batch) {
  return 'Ты опытный технолог общественного питания и шеф-редактор. Составь дружелюбные, понятные инструкции по доготовке блюд.\n\n' +
    'СТИЛЬ:\n' +
    '- Используй повелительное наклонение: "вылейте", "добавьте", "варите" (не "вылить", "добавить").\n' +
    '- Дружелюбный, но профессиональный тон.\n' +
    '- НЕ используй слова "Подготовка:", "Разогрев:", "Подача:" — просто нумеруй шаги: "1) ...", "2) ...".\n' +
    '- Будь смелее! Предлагай дополнения, улучшения, альтернативные способы готовки на основе best practices.\n' +
    '- Для полностью готовых блюд — короткая инструкция типа "Разогрейте на плите или в микроволновке."\n\n' +
    'ПРАВИЛА ПО КАТЕГОРИЯМ:\n\n' +
    '1. "Супы и крем-супы" — ВАЖНО! Формат поставки:\n' +
    '   - Блюда приходят в вакуумных пакетах: отдельно бульон, отдельно зажарка (овощи, мясо, всё остальное в одном пакете).\n' +
    '   - НЕ перечисляй конкретные ингредиенты типа "добавьте нарезанную морковь и лук" или "добавьте нарезанные овощи: свеклу, картофель и капусту".\n' +
    '   - Используй общее название: "зажарка" или "овощная зажарка" или "зажарка с мясом" (в зависимости от состава).\n' +
    '   - Анализируй состав: если есть бульон + зажарка + овощи отдельно — детальная инструкция.\n' +
    '   - Если крем-суп готовый (грибной, тыквенный) — короткая: "Разогрейте на плите или в микроволновке. По желанию можно добавить сливки."\n' +
    '   Примеры:\n' +
    '   * Борщ/куриный суп: "1) Вылейте бульон в кастрюлю и доведите до кипения.\\n2) Добавьте зажарку (всё из вакуумного пакета).\\n3) Варите 10–15 минут на слабом огне.\\n4) Перед подачей добавьте зелень и сметану по желанию."\n' +
    '   * Крем-суп: "1) Разогрейте на плите или в микроволновке.\\n2) По желанию можно добавить сливки."\n\n' +
    '2. "Паста ручной работы":\n' +
    '   - Если паста и соус отдельно (формат 250/400/60): "1) Отварите пасту 4 минуты в подсоленной воде.\\n2) Добавьте тёплую индейку и соус песто, перемешайте.\\n3) Посыпьте пармезаном перед подачей."\n' +
    '   - Если паста готовая: "Разогрейте на плите, перемешайте с соусом."\n\n' +
    '3. "Основные блюда":\n' +
    '   - Для готовых блюд: "Разогрейте на плите или в микроволновке."\n' +
    '   - Для запеканок/запечённых: "Запекайте в духовке 150°C 8–10 минут или в микроволновке 2–3 минуты."\n' +
    '   - Будь конкретным: температура, время, альтернативы.\n\n' +
    '4. "Гарниры и салаты", "Завтраки и сладкое", "Авторские сэндвичи и перекусы":\n' +
    '   Напиши: "Готовое блюдо"\n\n' +
    '5. "Для запаса / в морозильник":\n' +
    '   - Вареники/пельмени: "Готовьте в кипящей воде 8 минут (не размораживать). По желанию можно обжарить на сливочном масле до румяной корочки."\n' +
    '   - Котлеты/биточки: "Не размораживать. Жарите 3–4 минуты с каждой стороны и потом запекайте 12 минут при 180°C."\n' +
    '   - Сырники/оладьи: "Не размораживать. Обжаривайте на сковороде с маслом 3–4 минуты с каждой стороны до золотистой корочки."\n' +
    '   - Анализируй название и состав, определяй оптимальный способ готовки.\n\n' +
    'Для ВСЕХ блюд рассчитай КБЖУ на 100 г в формате: "~135 ккал, Б ~9 г, Ж ~7 г, У ~8 г" (примерно, со знаком ~).\n\n' +
    'Верни строго JSON-массив объектов {"name","instruction","allergens","kbju_per_100g"}.\n\n' +
    'Входные данные:\n' + JSON.stringify(batch, null, 2);
}

function formatInstructionText(entry, row) {
  if (!entry) return fallbackInstruction(row);

  if (Array.isArray(entry.instruction)) {
    return entry.instruction.join('\n');
  }
  if (typeof entry.instruction === 'string') {
    return entry.instruction;
  }
  if (Array.isArray(entry.steps)) {
    return entry.steps.join('\n');
  }
  if (entry.text) {
    return entry.text.toString();
  }
  return fallbackInstruction(row);
}

function fallbackInstruction(row) {
  var cat = (row.category || '').toString().toLowerCase();
  // Для категорий, которые должны иметь "Готовое блюдо"
  if (cat.indexOf('гарнир') !== -1 || cat.indexOf('салат') !== -1 || 
      cat.indexOf('завтрак') !== -1 || cat.indexOf('сладк') !== -1 || 
      cat.indexOf('сэндв') !== -1 || cat.indexOf('перекус') !== -1) {
    return 'Готовое блюдо';
  }
  // Для заморозки - общая инструкция
  if (cat.indexOf('запас') !== -1 || cat.indexOf('мороз') !== -1) {
    return 'Не размораживать. Готовьте согласно типу продукта: вареники/пельмени — варите 5–8 минут в кипящей воде; котлеты — жарьте 3–4 мин с каждой стороны, затем запекайте 12–15 мин при 180°C.';
  }
  // Для остальных - короткие инструкции без шаблонных слов
  if (cat.indexOf('суп') !== -1) {
    return 'Разогрейте на плите или в микроволновке.';
  } else if (cat.indexOf('паста') !== -1) {
    return 'Отварите пасту 3–4 минуты, добавьте соус и перемешайте.';
  } else {
    return 'Разогрейте на плите или в микроволновке.';
  }
}

function exportInstructionsToDoc() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName('Instructions');
  if (!sheet) {
    SpreadsheetApp.getActive().toast('Сначала создайте лист Instructions', 'Export', 5);
    return;
  }
  var values = sheet.getDataRange().getValues();
  if (!values || values.length <= 1) return;
  var headers = values[0];
  var idxName = headers.indexOf('Название');
  var idxCategory = headers.indexOf('Категория');
  var idxUnit = headers.indexOf('Единица');
  var idxKbju = headers.indexOf('КБЖУ (100 г)');
  var idxInstruction = headers.indexOf('Инструкция');
  var idxComposition = headers.indexOf('Состав');
  var idxAllergens = headers.indexOf('Аллергены');
  var docTitle = 'Инструкции ' + Utilities.formatDate(new Date(), Session.getScriptTimeZone(), 'yyyy-MM-dd HH:mm');
  var doc = DocumentApp.create(docTitle);
  var body = doc.getBody();
  body.clear();
  body.setMarginTop(36);
  body.setMarginBottom(36);
  body.setMarginLeft(36);
  body.setMarginRight(36);
  body.appendParagraph(docTitle).setHeading(DocumentApp.ParagraphHeading.TITLE).setBold(true);
  for (var i = 1; i < values.length; i++) {
    var row = values[i];
    var name = (row[idxName] || '').toString().trim();
    if (!name) continue;
    var instruction = idxInstruction !== -1 ? (row[idxInstruction] || '').toString().trim() : '';
    var category = idxCategory !== -1 ? (row[idxCategory] || '').toString().trim() : '';
    var unit = idxUnit !== -1 ? (row[idxUnit] || '').toString().trim() : '';
    var kbju = idxKbju !== -1 ? (row[idxKbju] || '').toString().trim() : '';
    var composition = idxComposition !== -1 ? (row[idxComposition] || '').toString().trim() : '';
    var allergens = idxAllergens !== -1 ? (row[idxAllergens] || '').toString().trim() : '';
    // Теперь все блюда должны иметь инструкции (хотя бы "Готовое блюдо" для некоторых категорий)
    if (!instruction) continue;
    body.appendParagraph(name).setHeading(DocumentApp.ParagraphHeading.HEADING2).setBold(true);
    var meta = [];
    if (category) meta.push(category);
    if (unit) meta.push(unit);
    if (kbju) meta.push('КБЖУ (100 г): ' + kbju);
    if (meta.length) {
      body.appendParagraph(meta.join(' • ')).setHeading(DocumentApp.ParagraphHeading.NORMAL).setFontSize(10).setForegroundColor('#555555');
    }
    instruction.split(/\n+/).forEach(function(line){
      var clean = line.trim();
      if (clean) body.appendParagraph(clean).setHeading(DocumentApp.ParagraphHeading.NORMAL).setFontSize(11).setSpacingAfter(2);
    });
    if (composition) body.appendParagraph('Состав: ' + composition).setHeading(DocumentApp.ParagraphHeading.NORMAL).setFontSize(10).setItalic(true);
    if (allergens) body.appendParagraph('Аллергены: ' + allergens).setHeading(DocumentApp.ParagraphHeading.NORMAL).setFontSize(10).setItalic(true);
    body.appendParagraph('').setSpacingAfter(4);
  }
  doc.saveAndClose();
  SpreadsheetApp.getUi().alert('Документ создан: ' + docTitle + '\n' + doc.getUrl());
}

function testLlmConnectivity() {
  var apiKey = PropertiesService.getScriptProperties().getProperty('LLM_API_KEY');
  var model = PropertiesService.getScriptProperties().getProperty('LLM_MODEL') || 'gpt-4o-mini';
  if (!apiKey) {
    SpreadsheetApp.getActive().toast('Нет LLM_API_KEY', 'LLM Test', 5);
    return;
  }
  var res = UrlFetchApp.fetch('https://api.openai.com/v1/chat/completions', {
    method: 'post',
    contentType: 'application/json',
    headers: { Authorization: 'Bearer ' + apiKey },
    payload: JSON.stringify({
      model: model,
      messages: [ { role: 'user', content: 'Return exactly ["ok"]' } ],
      temperature: 0
    }),
    muteHttpExceptions: true
  });
  SpreadsheetApp.getActive().toast('LLM response code: ' + res.getResponseCode(), 'LLM Test', 5);
}

// --- Helpers ---
function findHeaderRowIndex(values) {
  var maxScan = Math.min(values.length, 10);
  for (var i = 0; i < maxScan; i++) {
    var row = values[i].join(' ').toLowerCase();
    if (row.indexOf('название') !== -1 && row.indexOf('категор') !== -1) return i;
  }
  return -1;
}

function parseMenuRow(headers, row) {
  var map = {};
  headers.forEach(function(header, idx){ map[header.toString().trim().toLowerCase()] = idx; });
  function pick(keys) {
    for (var i = 0; i < keys.length; i++) {
      var idx = map[keys[i]];
      if (idx !== undefined) return (row[idx] || '').toString();
    }
    return '';
  }
  var name = pick(['название','наименование','блюдо']).trim();
  if (!name) return null;
  var price = pick(['цена','price']).trim();
  var unit = pick(['единица','вес/кол-во','кол-во/вес','кол-во','вес']).trim();
  var category = pick(['категория','category']).trim();
  var tags = pick(['теги']).trim();
  var composition = pick(['состав']).trim();
  var allergens = pick(['аллергены']).trim();
  return {
    name: name,
    price: price,
    unit: unit,
    category: category,
    tags: tags,
    composition: composition, // Не форматируем здесь, форматирование будет в LLM
    allergens: allergens,
    originalRow: row // Сохраняем всю исходную строку для переноса всех колонок
  };
}

function mapCategory(text) {
  var t = (text || '').toString().toLowerCase();
  if (t.indexOf('завтр') !== -1 || t.indexOf('десерт') !== -1 || t.indexOf('слад') !== -1) return CATEGORY_ORDER[0];
  if (t.indexOf('сэндв') !== -1 || t.indexOf('перекус') !== -1 || t.indexOf('бутер') !== -1) return CATEGORY_ORDER[1];
  if (t.indexOf('паста') !== -1) return CATEGORY_ORDER[2];
  if (t.indexOf('суп') !== -1) return CATEGORY_ORDER[3];
  if (t.indexOf('гарнир') !== -1 || t.indexOf('салат') !== -1) return CATEGORY_ORDER[5];
  if (t.indexOf('замороз') !== -1 || t.indexOf('мороз') !== -1 || t.indexOf('запас') !== -1) return CATEGORY_ORDER[6];
  return CATEGORY_ORDER[4];
}

function categoryIndex(cat) {
  var idx = CATEGORY_ORDER.indexOf(cat);
  return idx === -1 ? CATEGORY_ORDER.indexOf(CATEGORY_DEFAULT) : idx;
}

function instructionCategoryIndex(cat) {
  var idx = INSTRUCTION_CATEGORY_ORDER.indexOf(cat);
  return idx === -1 ? 999 : idx; // Неизвестные категории в конец
}

function prettifyName(name) {
  var n = (name || '').toString().replace(/\s+/g, ' ').trim();
  return n.charAt(0).toUpperCase() + n.slice(1);
}

function safeJsonArray(text) {
  if (Array.isArray(text)) return text;
  try {
    var start = text.indexOf('[');
    var end = text.lastIndexOf(']');
    if (start === -1 || end === -1) return [];
    return JSON.parse(text.slice(start, end + 1));
  } catch (e) {
    Logger.log('safeJsonArray error: ' + e);
    return [];
  }
}

function normalizeList(val) {
  if (!val && val !== 0) return [];
  if (Array.isArray(val)) return val;
  return val.toString().split(',').map(function(x){ return x.trim(); }).filter(Boolean);
}

function formatUnit(value) {
  var v = (value || '').toString().trim();
  if (!v) return '';
  if (/^\d+([.,]\d+)?$/.test(v)) {
    return v.replace(',', '.') + ' г';
  }
  return v;
}

function formatComposition(text) {
  if (!text) return '';
  var cleaned = text.toString().toLowerCase();
  cleaned = cleaned.replace(/\s*,\s*/g, ', ');
  cleaned = cleaned.replace(/\s+/g, ' ').trim();
  return cleaned;
}

function chooseFilled(primary, fallback) {
  var p = (primary || '').toString().trim();
  if (p) return p;
  return (fallback || '').toString().trim();
}
