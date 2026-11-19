@echo off
setlocal
set "PROJECT_ROOT=%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%PROJECT_ROOT%ops\windows\Start-ZazuLanHttps.ps1"
set "EXIT_CODE=%ERRORLEVEL%"
echo.
if not "%EXIT_CODE%"=="0" echo Zazu LAN HTTPS startup failed. Read the error above.
echo.
echo Copy the HTTPS URL and certificate path above before closing this window.
pause
exit /b %EXIT_CODE%
