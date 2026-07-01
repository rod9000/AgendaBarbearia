@echo off
title Agenda Barbearia - Iniciando Servidores

echo ============================================
echo   AGENDA BARBEARIA - Iniciando Servidores
echo ============================================
echo.

echo [1/3] Iniciando MySQL (XAMPP)...
start "" "C:\xampp\mysql_start.bat"
timeout /t 3 /nobreak >nul
echo [OK] MySQL - porta 3306
echo.

echo [2/3] Iniciando Evolution API (Docker)...
cd /d "C:\Users\Precode TI\Documents\GitHub\AgendaBarbearia\docker\evolution"
docker-compose up -d
timeout /t 10 /nobreak >nul
echo [OK] Evolution API - porta 8080
echo.

echo [3/3] Iniciando Laravel (porta 8001)...
cd /d "C:\Users\Precode TI\Documents\GitHub\AgendaBarbearia"
start "Laravel Server" cmd /k "php artisan serve --host=0.0.0.0 --port=8001"
timeout /t 3 /nobreak >nul
echo [OK] Laravel - porta 8001
echo.

echo ============================================
echo   TODOS OS SERVIDORES INICIADOS!
echo ============================================
echo.
echo   MySQL .............. localhost:3306
echo   Laravel ............ localhost:8001
echo   Evolution API ...... localhost:8080
echo   Manager ............ localhost:8080/manager
echo   Admin .............. localhost:8001/admin
echo ============================================
echo.

pause
