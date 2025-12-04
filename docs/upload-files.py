#!/usr/bin/env python3
"""
Скрипт для загрузки файлов на сервер через SFTP
"""
import os
import sys
import pysftp

# Параметры подключения
SFTP_HOST = "82.29.185.42"
SFTP_PORT = 65002
SFTP_USER = "u850527203"
SFTP_PASS = "hiLKov15!"

# Файлы для загрузки
FILES_TO_UPLOAD = [
    {
        'local': 'wp-content/themes/gustolocal/patterns/corporate-meals.php',
        'remote': 'wp-content/themes/gustolocal/patterns/corporate-meals.php'
    },
    {
        'local': 'wp-content/themes/gustolocal/functions.php',
        'remote': 'wp-content/themes/gustolocal/functions.php'
    },
    {
        'local': 'docs/setup-corporate-meals.php',
        'remote': 'setup-corporate-meals.php'
    }
]

def upload_files():
    """Загружает файлы на сервер через SFTP"""
    try:
        # Подключаемся к серверу
        cnopts = pysftp.CnOpts()
        cnopts.hostkeys = None  # Отключаем проверку ключей для автоматизации
        
        with pysftp.Connection(
            host=SFTP_HOST,
            port=SFTP_PORT,
            username=SFTP_USER,
            password=SFTP_PASS,
            cnopts=cnopts
        ) as sftp:
            print("✓ Подключение к серверу установлено")
            
            # Загружаем файлы
            for file_info in FILES_TO_UPLOAD:
                local_path = file_info['local']
                remote_path = file_info['remote']
                
                if not os.path.exists(local_path):
                    print(f"✗ Файл не найден: {local_path}")
                    continue
                
                # Создаём директорию, если нужно
                remote_dir = os.path.dirname(remote_path)
                if remote_dir and not sftp.exists(remote_dir):
                    sftp.makedirs(remote_dir)
                
                # Загружаем файл
                print(f"Загрузка: {local_path} -> {remote_path}")
                sftp.put(local_path, remote_path)
                print(f"✓ Файл загружен: {remote_path}")
            
            print("\n✓ Все файлы успешно загружены!")
            print("\nСледующие шаги:")
            print("1. Откройте в браузере: https://gustolocal.es/setup-corporate-meals.php")
            print("2. После проверки удалите setup-corporate-meals.php с сервера")
            
    except Exception as e:
        print(f"✗ Ошибка: {e}")
        sys.exit(1)

if __name__ == '__main__':
    upload_files()

