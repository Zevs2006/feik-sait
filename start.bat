@echo off
setlocal
cd /d "%~dp0public"

echo Starting VOLT store at http://localhost:8000
echo Press Ctrl+C to stop the server.
echo.

if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" -S localhost:8000
) else (
    php -S localhost:8000
)

echo.
echo Server stopped.
pause
