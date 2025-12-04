<?php
/**
 * Простой логгер для webhook'ов
 */

class Logger {
    private static $logFile;
    
    public static function init($logFile) {
        self::$logFile = $logFile;
        
        // Создаем директорию для логов, если её нет
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public static function log($message, $data = null) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message";
        
        if ($data !== null) {
            $logMessage .= "\n" . print_r($data, true);
        }
        
        $logMessage .= "\n" . str_repeat('-', 80) . "\n";
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
    
    public static function error($message, $exception = null) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] ERROR: $message";
        
        if ($exception instanceof Exception) {
            $logMessage .= "\n" . $exception->getMessage();
            $logMessage .= "\n" . $exception->getTraceAsString();
        }
        
        $logMessage .= "\n" . str_repeat('-', 80) . "\n";
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
}

