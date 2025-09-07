@echo off
REM DNIWOO Plugin Installer for Windows
REM This script installs DNIWOO plugin in the correct directory

echo DNIWOO Plugin Installer
echo ================================

REM Check if PowerShell is available
powershell -Command "Write-Host 'Downloading DNIWOO plugin...' -ForegroundColor Yellow"

REM Download and extract
powershell -Command "Invoke-WebRequest -Uri 'https://github.com/replantadev/dniwoo/archive/refs/heads/main.zip' -OutFile 'dniwoo-latest.zip'"
powershell -Command "Expand-Archive -Path 'dniwoo-latest.zip' -DestinationPath '.' -Force"
powershell -Command "Move-Item 'dniwoo-main' 'dniwoo' -Force"
powershell -Command "Remove-Item 'dniwoo-latest.zip' -Force"

echo.
powershell -Command "Write-Host 'DNIWOO plugin installed successfully!' -ForegroundColor Green"
echo Please activate the plugin from WordPress admin panel.
echo.
echo Plugin location: wp-content/plugins/dniwoo/
echo Plugin name: DNIWOO - DNI/NIF for WooCommerce
pause
