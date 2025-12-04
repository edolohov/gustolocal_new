<?php
/**
 * Webhook endpoint для WhatsApp Business API (Meta)
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
    
    Logger::log("WhatsApp webhook verification request", $_GET);
    
    if ($mode === 'subscribe' && $token === WHATSAPP_VERIFY_TOKEN) {
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

Logger::log("WhatsApp webhook received", $data);

// Проверка подписи (X-Hub-Signature-256)
if (isset($_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'];
    // Для WhatsApp используется тот же APP_SECRET что и для Instagram
    $expectedSignature = 'sha256=' . hash_hmac('sha256', $rawInput, INSTAGRAM_APP_SECRET);
    
    if (!hash_equals($expectedSignature, $signature)) {
        Logger::error("WhatsApp webhook signature mismatch");
        http_response_code(403);
        echo 'Invalid signature';
        exit;
    }
}

try {
    // Обрабатываем входящие сообщения из WhatsApp
    if (!isset($data['entry'])) {
        http_response_code(200);
        echo json_encode(['status' => 'ok', 'message' => 'No entries']);
        exit;
    }
    
    $db = Database::getInstance();
    
    foreach ($data['entry'] as $entry) {
        if (!isset($entry['changes'])) {
            continue;
        }
        
        foreach ($entry['changes'] as $change) {
            if ($change['field'] !== 'messages') {
                continue;
            }
            
            $value = $change['value'] ?? [];
            
            // Обрабатываем только входящие сообщения
            if (!isset($value['messages'])) {
                continue;
            }
            
            foreach ($value['messages'] as $message) {
                // Пропускаем исходящие сообщения (статусы)
                if (isset($message['from']) && $message['from'] === WHATSAPP_PHONE_NUMBER_ID) {
                    continue;
                }
                
                $fromNumber = $message['from'] ?? null;
                $messageId = $message['id'] ?? null;
                $timestamp = isset($message['timestamp']) 
                    ? date('Y-m-d H:i:s', $message['timestamp']) 
                    : null;
                
                // Обрабатываем текстовые сообщения
                if (isset($message['type']) && $message['type'] === 'text') {
                    $messageText = $message['text']['body'] ?? '';
                    
                    if (empty($messageText)) {
                        continue;
                    }
                    
                    // Используем номер телефона как идентификатор
                    $identifier = $fromNumber;
                    
                    // Находим или создаем клиента
                    $clientId = $db->findOrCreateClient(
                        $identifier,
                        'whatsapp',
                        "WhatsApp User $fromNumber",
                        $fromNumber
                    );
                    
                    // Сохраняем сообщение
                    $metadata = [
                        'from' => $fromNumber,
                        'message_id' => $messageId,
                        'timestamp' => $timestamp,
                        'type' => 'text'
                    ];
                    
                    $db->saveMessage(
                        $clientId,
                        'whatsapp',
                        $messageText,
                        "wa_{$messageId}",
                        $metadata
                    );
                    
                    Logger::log("WhatsApp message saved", [
                        'client_id' => $clientId,
                        'from' => $fromNumber,
                        'text' => substr($messageText, 0, 100)
                    ]);
                }
                // Здесь можно добавить обработку медиа-файлов позже
            }
        }
    }
    
    // Всегда возвращаем 200 OK
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'message' => 'Messages processed']);
    
} catch (Exception $e) {
    Logger::error("Error processing WhatsApp webhook", $e);
    
    // Все равно возвращаем 200
    http_response_code(200);
    echo json_encode(['status' => 'error', 'message' => 'Internal error']);
}

