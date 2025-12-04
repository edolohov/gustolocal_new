<?php
/**
 * Класс для работы с базой данных
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Найти или создать клиента
     */
    public function findOrCreateClient($identifier, $channel, $name = null, $phone = null) {
        $conn = $this->connection;
        
        // Определяем поле для поиска в зависимости от канала
        $searchField = $this->getSearchField($channel);
        
        // Ищем существующего клиента
        $stmt = $conn->prepare("
            SELECT id FROM clients 
            WHERE channel = :channel 
            AND ($searchField = :identifier OR phone = :phone)
            LIMIT 1
        ");
        
        $stmt->execute([
            ':channel' => $channel,
            ':identifier' => $identifier,
            ':phone' => $phone ?: $identifier
        ]);
        
        $client = $stmt->fetch();
        
        if ($client) {
            return $client['id'];
        }
        
        // Создаем нового клиента
        $stmt = $conn->prepare("
            INSERT INTO clients (name, username, phone, channel_first, created_at) 
            VALUES (:name, :username, :phone, :channel, NOW())
        ");
        
        $username = ($channel === 'telegram' || $channel === 'instagram') ? $identifier : null;
        $phoneValue = ($channel === 'whatsapp') ? $identifier : $phone;
        
        $stmt->execute([
            ':name' => $name,
            ':username' => $username,
            ':phone' => $phoneValue,
            ':channel' => $channel
        ]);
        
        return $conn->lastInsertId();
    }
    
    /**
     * Сохранить входящее сообщение
     */
    public function saveMessage($clientId, $channel, $messageText, $externalMessageId, $metadata = []) {
        $conn = $this->connection;
        
        $stmt = $conn->prepare("
            INSERT INTO messages 
            (client_id, channel, direction, message_text, external_message_id, timestamp, metadata, created_at) 
            VALUES (:client_id, :channel, 'incoming', :message_text, :external_message_id, NOW(), :metadata, NOW())
        ");
        
        $stmt->execute([
            ':client_id' => $clientId,
            ':channel' => $channel,
            ':message_text' => $messageText,
            ':external_message_id' => $externalMessageId,
            ':metadata' => json_encode($metadata)
        ]);
        
        return $conn->lastInsertId();
    }
    
    /**
     * Получить поле для поиска клиента по каналу
     */
    private function getSearchField($channel) {
        switch ($channel) {
            case 'telegram':
            case 'instagram':
                return 'username';
            case 'whatsapp':
                return 'phone';
            default:
                return 'username';
        }
    }
    
    /**
     * Получить все непрочитанные сообщения
     */
    public function getUnreadMessages($limit = 100) {
        $conn = $this->connection;
        
        $stmt = $conn->prepare("
            SELECT 
                m.id,
                m.message_text,
                m.channel,
                m.timestamp,
                m.created_at,
                c.name,
                c.username,
                c.phone
            FROM messages m
            LEFT JOIN clients c ON m.client_id = c.id
            WHERE m.direction = 'incoming'
            AND m.is_read = 0
            ORDER BY m.created_at DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}

