<?php
/**
 * Webhook endpoint для Instagram Messaging API (Meta)
 * 
 * ВАЖНО: Meta требует верификацию webhook при первой настройке
 * GET запрос должен вернуть hub.challenge если hub.verify_token совпадает
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/logger.php';

Logger::init(LOG_FILE);

// Обработка верификации webhook (GET запрос от Meta)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    
    Logger::log("Instagram webhook verification request", $_GET);
    
    if ($mode === 'subscribe' && $token === INSTAGRAM_WEBHOOK_VERIFY_TOKEN) {
        echo $challenge;
        exit;
    } else {
        http_response_code(403);
        echo 'Verification failed';
        exit;
    }
}

// Обработка входящих сообщений (POST запрос)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

// Получаем raw body для проверки подписи
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

Logger::log("Instagram webhook received", $data);

// Проверка подписи (X-Hub-Signature-256)
if (isset($_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'];
    $expectedSignature = 'sha256=' . hash_hmac('sha256', $rawInput, INSTAGRAM_APP_SECRET);
    
    if (!hash_equals($expectedSignature, $signature)) {
        Logger::error("Instagram webhook signature mismatch");
        http_response_code(403);
        echo 'Invalid signature';
        exit;
    }
}

try {
    // Обрабатываем входящие сообщения из Instagram
    if (!isset($data['entry'])) {
        http_response_code(200);
        echo json_encode(['status' => 'ok', 'message' => 'No entries']);
        exit;
    }
    
    $db = Database::getInstance();
    
    foreach ($data['entry'] as $entry) {
        if (!isset($entry['messaging'])) {
            continue;
        }
        
        foreach ($entry['messaging'] as $messaging) {
            // Обрабатываем только входящие сообщения
            if (!isset($messaging['message']) || isset($messaging['message']['is_echo'])) {
                continue;
            }
            
            $senderId = $messaging['sender']['id'] ?? null;
            $recipientId = $messaging['recipient']['id'] ?? null;
            $message = $messaging['message'];
            $messageText = $message['text'] ?? '';
            $messageId = $message['mid'] ?? null;
            $timestamp = isset($messaging['timestamp']) 
                ? date('Y-m-d H:i:s', $messaging['timestamp'] / 1000) 
                : null;
            
            // Пропускаем, если нет текста
            if (empty($messageText)) {
                continue;
            }
            
            // Используем sender_id как идентификатор
            $identifier = "ig_{$senderId}";
            
            // Находим или создаем клиента
            $clientId = $db->findOrCreateClient(
                $identifier,
                'instagram',
                "Instagram User #$senderId",
                null
            );
            
            // Сохраняем сообщение
            $metadata = [
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'message_id' => $messageId,
                'timestamp' => $timestamp
            ];
            
            $db->saveMessage(
                $clientId,
                'instagram',
                $messageText,
                "ig_{$messageId}",
                $metadata
            );
            
            Logger::log("Instagram message saved", [
                'client_id' => $clientId,
                'sender_id' => $senderId,
                'text' => substr($messageText, 0, 100)
            ]);
        }
    }
    
    // Всегда возвращаем 200 OK
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'message' => 'Messages processed']);
    
} catch (Exception $e) {
    Logger::error("Error processing Instagram webhook", $e);
    
    // Все равно возвращаем 200
    http_response_code(200);
    echo json_encode(['status' => 'error', 'message' => 'Internal error']);
}

