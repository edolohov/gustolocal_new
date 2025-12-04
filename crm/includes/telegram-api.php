<?php
/**
 * Класс для работы с Telegram Bot API
 */

class TelegramAPI {
    private $token;
    private $apiUrl;
    
    public function __construct($token) {
        $this->token = $token;
        $this->apiUrl = "https://api.telegram.org/bot{$token}/";
    }
    
    /**
     * Отправка запроса к API
     */
    private function request($method, $params = []) {
        $url = $this->apiUrl . $method;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("API returned HTTP $httpCode: $response");
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['ok'])) {
            throw new Exception("Invalid API response: $response");
        }
        
        if (!$data['ok']) {
            throw new Exception("API error: " . ($data['description'] ?? 'Unknown error'));
        }
        
        return $data['result'];
    }
    
    /**
     * Получить обновления (для long polling)
     */
    public function getUpdates($offset = 0, $timeout = 30) {
        return $this->request('getUpdates', [
            'offset' => $offset,
            'timeout' => $timeout,
            'allowed_updates' => ['message', 'edited_message', 'callback_query']
        ]);
    }
    
    /**
     * Отправить сообщение
     */
    public function sendMessage($chatId, $text, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ], $options);
        
        return $this->request('sendMessage', $params);
    }
    
    /**
     * Отправить сообщение с клавиатурой
     */
    public function sendMessageWithKeyboard($chatId, $text, $keyboard, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false
            ]
        ], $options);
        
        return $this->request('sendMessage', $params);
    }
    
    /**
     * Отправить inline клавиатуру
     */
    public function sendMessageWithInlineKeyboard($chatId, $text, $inlineKeyboard, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => $inlineKeyboard
            ]
        ], $options);
        
        return $this->request('sendMessage', $params);
    }
    
    /**
     * Ответить на callback query
     */
    public function answerCallbackQuery($callbackQueryId, $text = '', $showAlert = false) {
        return $this->request('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert
        ]);
    }
    
    /**
     * Установить webhook
     */
    public function setWebhook($url, $secretToken = null) {
        $params = ['url' => $url];
        if ($secretToken) {
            $params['secret_token'] = $secretToken;
        }
        return $this->request('setWebhook', $params);
    }
    
    /**
     * Удалить webhook
     */
    public function deleteWebhook() {
        return $this->request('deleteWebhook');
    }
    
    /**
     * Получить информацию о боте
     */
    public function getMe() {
        return $this->request('getMe');
    }
}

