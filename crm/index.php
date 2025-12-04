<?php
/**
 * Простой интерфейс для просмотра входящих сообщений
 * Пока без авторизации - добавим позже
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';

$db = Database::getInstance();
$messages = $db->getUnreadMessages(50);

// Группируем по клиентам
$groupedMessages = [];
foreach ($messages as $msg) {
    $clientKey = $msg['name'] ?? $msg['username'] ?? $msg['phone'] ?? 'Unknown';
    if (!isset($groupedMessages[$clientKey])) {
        $groupedMessages[$clientKey] = [
            'client' => [
                'name' => $msg['name'],
                'username' => $msg['username'],
                'phone' => $msg['phone'],
                'channel' => $msg['channel']
            ],
            'messages' => []
        ];
    }
    $groupedMessages[$clientKey]['messages'][] = $msg;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Входящие сообщения</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        
        .messages-list {
            display: grid;
            gap: 15px;
        }
        
        .message-group {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .message-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .client-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .channel-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .channel-telegram {
            background: #0088cc;
            color: white;
        }
        
        .channel-instagram {
            background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
            color: white;
        }
        
        .channel-whatsapp {
            background: #25d366;
            color: white;
        }
        
        .message-count {
            color: #666;
            font-size: 14px;
        }
        
        .messages-container {
            padding: 20px;
        }
        
        .message-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .message-item:last-child {
            border-bottom: none;
        }
        
        .message-text {
            color: #333;
            line-height: 1.5;
            margin-bottom: 5px;
        }
        
        .message-time {
            color: #999;
            font-size: 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .message-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📨 Входящие сообщения</h1>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Всего непрочитанных</h3>
                <div class="number"><?php echo count($messages); ?></div>
            </div>
            <div class="stat-card">
                <h3>Уникальных клиентов</h3>
                <div class="number"><?php echo count($groupedMessages); ?></div>
            </div>
        </div>
        
        <?php if (empty($groupedMessages)): ?>
            <div class="empty-state">
                <p>Нет новых сообщений</p>
            </div>
        <?php else: ?>
            <div class="messages-list">
                <?php foreach ($groupedMessages as $group): ?>
                    <div class="message-group">
                        <div class="message-header">
                            <div class="client-info">
                                <span class="channel-badge channel-<?php echo $group['client']['channel']; ?>">
                                    <?php 
                                    $channels = [
                                        'telegram' => 'TG',
                                        'instagram' => 'IG',
                                        'whatsapp' => 'WA'
                                    ];
                                    echo $channels[$group['client']['channel']] ?? $group['client']['channel'];
                                    ?>
                                </span>
                                <strong><?php echo htmlspecialchars($group['client']['name'] ?? $group['client']['username'] ?? $group['client']['phone'] ?? 'Unknown'); ?></strong>
                            </div>
                            <div class="message-count">
                                <?php echo count($group['messages']); ?> сообщ.
                            </div>
                        </div>
                        <div class="messages-container">
                            <?php foreach ($group['messages'] as $msg): ?>
                                <div class="message-item">
                                    <div class="message-text">
                                        <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                    </div>
                                    <div class="message-time">
                                        <?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

