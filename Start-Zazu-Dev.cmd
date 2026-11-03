@echo off
setlocal
set "PROJECT_ROOT=%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%PROJECT_ROOT%ops\windows\Start-ZazuDev.ps1"
if errorlevel 1 pause
