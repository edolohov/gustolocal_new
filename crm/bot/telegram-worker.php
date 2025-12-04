<?php
/**
 * Long polling worker для Telegram бота
 * 
 * Запуск через cron или как фоновый процесс:
 * php telegram-worker.php
 * 
 * Или через screen/tmux для постоянной работы
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/telegram-api.php';

Logger::init(LOG_FILE);

$db = Database::getInstance();
$telegram = new TelegramAPI(TELEGRAM_BOT_TOKEN);

// Файл для хранения последнего update_id
$offsetFile = __DIR__ . '/../logs/telegram_offset.txt';

// Загружаем последний offset
$lastOffset = 0;
if (file_exists($offsetFile)) {
    $lastOffset = (int)file_get_contents($offsetFile);
}

Logger::log("Telegram worker started", ['last_offset' => $lastOffset]);

// Основной цикл long polling
while (true) {
    try {
        // Получаем обновления (long polling, timeout 30 секунд)
        $updates = $telegram->getUpdates($lastOffset, 30);
        
        if (empty($updates)) {
            // Нет новых обновлений, продолжаем
            continue;
        }
        
        foreach ($updates as $update) {
            $updateId = $update['update_id'];
            $lastOffset = $updateId + 1;
            
            // Сохраняем offset
            file_put_contents($offsetFile, $lastOffset);
            
            // Обрабатываем сообщение
            processUpdate($update, $db, $telegram);
        }
        
    } catch (Exception $e) {
        Logger::error("Error in Telegram worker", $e);
        
        // Небольшая пауза перед повтором
        sleep(5);
    }
}

/**
 * Обработка одного update
 */
function processUpdate($update, $db, $telegram) {
    // Обрабатываем обычные сообщения
    if (isset($update['message'])) {
        processMessage($update['message'], $db, $telegram);
    }
    
    // Обрабатываем отредактированные сообщения
    if (isset($update['edited_message'])) {
        processMessage($update['edited_message'], $db, $telegram, true);
    }
    
    // Обрабатываем callback queries (нажатия на inline кнопки)
    if (isset($update['callback_query'])) {
        processCallbackQuery($update['callback_query'], $db, $telegram);
    }
}

/**
 * Обработка сообщения
 */
function processMessage($message, $db, $telegram, $isEdited = false) {
    $chatId = $message['chat']['id'];
    $username = $message['chat']['username'] ?? null;
    $firstName = $message['chat']['first_name'] ?? null;
    $lastName = $message['chat']['last_name'] ?? null;
    $messageId = $message['message_id'];
    
    // Обработка команд
    if (isset($message['text']) && strpos($message['text'], '/') === 0) {
        $command = explode(' ', $message['text'])[0];
        handleCommand($command, $message, $db, $telegram);
        return;
    }
    
    // Обрабатываем только текстовые сообщения
    if (!isset($message['text'])) {
        return;
    }
    
    $messageText = $message['text'];
    $timestamp = isset($message['date']) ? date('Y-m-d H:i:s', $message['date']) : null;
    
    // Формируем имя клиента
    $clientName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
    if (empty($clientName)) {
        $clientName = $username ?? "Telegram User #$chatId";
    }
    
    // Используем username или chat_id как идентификатор
    $identifier = $username ?? "tg_$chatId";
    
    try {
        // Находим или создаем клиента
        $clientId = $db->findOrCreateClient(
            $identifier,
            'telegram',
            $clientName,
            null
        );
        
        // Сохраняем сообщение
        $metadata = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'is_edited' => $isEdited
        ];
        
        $db->saveMessage(
            $clientId,
            'telegram',
            $messageText,
            "tg_{$messageId}",
            $metadata
        );
        
        Logger::log("Telegram message saved", [
            'client_id' => $clientId,
            'message_id' => $messageId,
            'text' => substr($messageText, 0, 100)
        ]);
        
    } catch (Exception $e) {
        Logger::error("Error saving Telegram message", $e);
    }
}

/**
 * Обработка команд
 */
function handleCommand($command, $message, $db, $telegram) {
    $chatId = $message['chat']['id'];
    
    switch ($command) {
        case '/start':
            $telegram->sendMessage($chatId, 
                "👋 Привет! Я бот для связи с вами.\n\n" .
                "Просто напишите мне сообщение, и я передам его в CRM систему."
            );
            break;
            
        case '/help':
            $telegram->sendMessage($chatId,
                "📋 Доступные команды:\n\n" .
                "/start - Начать работу\n" .
                "/help - Показать эту справку\n" .
                "/status - Проверить статус"
            );
            break;
            
        case '/status':
            $telegram->sendMessage($chatId, "✅ Бот работает нормально");
            break;
            
        default:
            // Неизвестная команда - сохраняем как обычное сообщение
            processMessage($message, $db, $telegram);
            break;
    }
}

/**
 * Обработка callback query (нажатия на inline кнопки)
 */
function processCallbackQuery($callbackQuery, $db, $telegram) {
    $queryId = $callbackQuery['id'];
    $data = $callbackQuery['data'] ?? '';
    $message = $callbackQuery['message'] ?? null;
    $from = $callbackQuery['from'] ?? null;
    
    // Отвечаем на callback
    $telegram->answerCallbackQuery($queryId, "Обрабатываю...");
    
    // Здесь можно добавить логику обработки кнопок
    Logger::log("Callback query received", [
        'data' => $data,
        'from' => $from['username'] ?? $from['id'] ?? 'unknown'
    ]);
}

