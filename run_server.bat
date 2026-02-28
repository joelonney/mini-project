@echo off
echo Starting SmartBus Server...

:: Check if PHP is in PATH
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo PHP not found in global PATH. Checking XAMPP...
    if exist "C:\xampp\php\php.exe" (
        set PHP_BIN="C:\xampp\php\php.exe"
    ) else (
        echo Error: PHP not found. Please install XAMPP or add PHP to PATH.
        pause
        exit /b
    )
) else (
    set PHP_BIN=php
)

:: Run Seeder if needed (optional, user can run via web)
:: %PHP_BIN% setup_data.php

:: Start Server
echo Server starting at http://localhost:8000
start http://localhost:8000
%PHP_BIN% -S localhost:8000
pause
