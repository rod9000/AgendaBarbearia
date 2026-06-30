@echo off
chcp 65001 >nul
title Agenda Barbearia - Iniciar Servidores

echo ============================================
echo   AGENDA BARBEARIA - Iniciando Servidores
echo ============================================
echo.

:: 1. MySQL via XAMPP
echo [1/4] Iniciando MySQL (XAMPP)...
start "" "C:\xampp\mysql_start.bat"
timeout /t 3 /nobreak >nul
echo [OK] MySQL iniciado na porta 3306
echo.

:: 2. Docker Evolution API
echo [2/4] Iniciando Evolution API (Docker)...
cd /d "C:\Users\Precode TI\Documents\GitHub\AgendaBarbearia\docker\evolution"
docker-compose up -d
timeout /t 10 /nobreak >nul
echo [OK] Evolution API iniciada na porta 8080
echo.

:: 3. Laravel
echo [3/4] Iniciando Laravel (porta 8000)...
cd /d "C:\Users\Precode TI\Documents\GitHub\AgendaBarbearia"
start "Laravel Server" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 3 /nobreak >nul
echo [OK] Laravel iniciado na porta 8000
echo.

:: 4. Verificação
echo [4/4] Verificando servidores...
echo.
echo ============================================
echo   SERVIDORES INICIADOS COM SUCESSO!
echo ============================================
echo.
echo   MySQL (XAMPP) ............. localhost:3306
echo   Laravel (Agenda) .......... localhost:8000
echo   Evolution API ............. localhost:8080
echo   Manager Evolution ......... localhost:8080/manager
echo   Admin do Laravel .......... localhost:8000/admin
echo.
echo   Webhook URL: http://localhost:8000/api/webhook/evolution
echo.
echo ============================================
echo.

pause
