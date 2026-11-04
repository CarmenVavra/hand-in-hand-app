@echo off
setlocal
cd /d "%~dp0"
title HandInHand - Start

rem HandInHand starten: Backend (PHP-Server, Port 8600) + Frontend (Vite, Port 5186)
rem   start.bat        -> nur auf diesem Rechner (http://localhost:8600)
rem   start.bat lan    -> auch im WLAN erreichbar (Handy-Test; Kamera braucht dort HTTPS, siehe docs\HandInHand.md)

set HOST=127.0.0.1
if /i "%~1"=="lan" set HOST=0.0.0.0

if not exist "vendor\autoload.php" (
    echo [1/3] Composer-Pakete werden installiert ...
    call composer install || goto :fehler
)
if not exist "node_modules" (
    echo [2/3] npm-Pakete werden installiert ...
    call npm install || goto :fehler
)
if not exist ".env" (
    copy ".env.example" ".env" >nul
    call php artisan key:generate || goto :fehler
)
if not exist "database\database.sqlite" (
    type nul > "database\database.sqlite"
)

echo [3/3] Datenbank wird aktualisiert ...
call php artisan migrate --force || goto :fehler

start "HandInHand PHP" /min cmd /c "php artisan serve --host=%HOST% --port=8600"
start "HandInHand Vite" /min cmd /c "npm run dev"

echo.
echo HandInHand laeuft:
echo   Kinder-App:  http://localhost:8600/home
echo   Admin:       http://localhost:8600/admin
echo Beenden mit stop.bat
timeout /t 3 >nul
start "" "http://localhost:8600/home"
exit /b 0

:fehler
echo.
echo Fehler beim Start - bitte die Meldung oben lesen.
pause
exit /b 1
