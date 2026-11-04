@echo off
setlocal
title HandInHand - Stop

rem Beendet PHP-Server (Port 8600) und Vite (Port 5186)
powershell -NoProfile -Command "Get-NetTCPConnection -LocalPort 8600,5186 -State Listen -ErrorAction SilentlyContinue | ForEach-Object { Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue }"

rem Vite-Marker entfernen (sonst sucht die App nach dem beendeten Dev-Server)
if exist "%~dp0public\hot" del /q "%~dp0public\hot"

rem Uebrig gebliebene Start-Fenster schliessen
taskkill /FI "WINDOWTITLE eq HandInHand PHP*" /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq HandInHand Vite*" /F >nul 2>&1

echo HandInHand wurde beendet.
timeout /t 2 >nul
exit /b 0
