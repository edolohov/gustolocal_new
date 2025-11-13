-- SQL скрипт для создания таблицы stg_wffilemods
-- 
-- Проблема: Wordfence использует префикс stg_, но таблица wp_wffilemods создана с префиксом wp_
-- Решение: Создаём таблицу stg_wffilemods на основе структуры wp_wffilemods
--
-- ИСПОЛЬЗОВАНИЕ:
-- 1. Откройте phpMyAdmin
-- 2. Выберите базу данных u850527203_5vYEq
-- 3. Перейдите на вкладку "SQL"
-- 4. Сначала выполните: SHOW CREATE TABLE wp_wffilemods;
-- 5. Скопируйте результат и замените wp_wffilemods на stg_wffilemods
-- 6. Или используйте этот скрипт (если структура совпадает)

-- ВАЖНО: Сначала проверьте структуру wp_wffilemods:
-- SHOW CREATE TABLE wp_wffilemods;

-- Типичная структура таблицы wffilemods (может отличаться в зависимости от версии Wordfence):
CREATE TABLE IF NOT EXISTS `stg_wffilemods` (
  `filenameMD5` binary(16) NOT NULL,
  `filename` varchar(1000) NOT NULL,
  `knownFile` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `oldMD5` binary(16) DEFAULT NULL,
  `newMD5` binary(16) DEFAULT NULL,
  `SHAC` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `stoppedOnSignature` varchar(255) NOT NULL DEFAULT '',
  `stoppedOnPosition` int(10) unsigned NOT NULL DEFAULT '0',
  `isSafeFile` varchar(1) NOT NULL DEFAULT '?',
  PRIMARY KEY (`filenameMD5`),
  KEY `k` (`knownFile`),
  KEY `shac` (`SHAC`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Проверка создания:
-- SHOW TABLES LIKE 'stg_wffilemods';
-- DESCRIBE stg_wffilemods;

