<?php
/**
 * Webhook endpoint для Telegram Bot API
 * 
 * Настройка webhook в Telegram:
 * https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://yourdomain.com/crm/webhooks/telegram.php
 * 
 * Альтернатива: используйте telegram-worker.php для long polling
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/telegram-api.php';

Logger::init(LOG_FILE);

// Получаем raw body для проверки подписи (если настроен secret_token)
$rawInput = file_get_contents('php://input');
$update = json_decode($rawInput, true);

// Проверка secret token (если настроен)
if (!empty(TELEGRAM_WEBHOOK_SECRET) && isset($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'])) {
    if ($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] !== TELEGRAM_WEBHOOK_SECRET) {
        Logger::error("Telegram webhook: Invalid secret token");
        http_response_code(403);
        exit;
    }
}

// Логируем входящий запрос
Logger::log("Telegram webhook received", $update);

// Проверяем, что это валидный update от Telegram
if (!isset($update['message']) && !isset($update['edited_message']) && !isset($update['callback_query'])) {
    // Это может быть проверка webhook или другой тип update
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'message' => 'No message in update']);
    exit;
}

// Обрабатываем callback query (нажатия на inline кнопки)
if (isset($update['callback_query'])) {
    $db = Database::getInstance();
    $telegram = new TelegramAPI(TELEGRAM_BOT_TOKEN);
    $callbackQuery = $update['callback_query'];
    
    // Отвечаем на callback
    $telegram->answerCallbackQuery($callbackQuery['id'], "Обрабатываю...");
    
    Logger::log("Telegram callback query", [
        'data' => $callbackQuery['data'] ?? '',
        'from' => $callbackQuery['from']['username'] ?? $callbackQuery['from']['id'] ?? 'unknown'
    ]);
    
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

// Обрабатываем сообщение (приоритет edited_message, потом message)
$message = $update['edited_message'] ?? $update['message'];

// Обрабатываем команды
if (isset($message['text']) && strpos($message['text'], '/') === 0) {
    $db = Database::getInstance();
    $telegram = new TelegramAPI(TELEGRAM_BOT_TOKEN);
    $command = explode(' ', $message['text'])[0];
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
    }
    
    // Команды тоже сохраняем в БД
    // (продолжаем обработку ниже)
}

// Пропускаем, если это не текстовое сообщение
if (!isset($message['text'])) {
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'message' => 'Not a text message']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Извлекаем данные из сообщения
    $chatId = $message['chat']['id'];
    $username = $message['chat']['username'] ?? null;
    $firstName = $message['chat']['first_name'] ?? null;
    $lastName = $message['chat']['last_name'] ?? null;
    $messageText = $message['text'];
    $messageId = $message['message_id'];
    $timestamp = isset($message['date']) ? date('Y-m-d H:i:s', $message['date']) : null;
    
    // Формируем имя клиента
    $clientName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));
    if (empty($clientName)) {
        $clientName = $username ?? "Telegram User #$chatId";
    }
    
    // Используем username или chat_id как идентификатор
    $identifier = $username ?? "tg_$chatId";
    
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
        'username' => $username
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
    
    // Всегда возвращаем 200 OK
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'message' => 'Message saved']);
    
} catch (Exception $e) {
    Logger::error("Error processing Telegram webhook", $e);
    
    // Все равно возвращаем 200, чтобы Telegram не повторял запрос
    http_response_code(200);
    echo json_encode(['status' => 'error', 'message' => 'Internal error']);
}

