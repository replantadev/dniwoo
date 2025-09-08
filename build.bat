@echo off
REM DNIWOO Professional Build Script for Windows
REM Creates a clean, production-ready plugin ZIP

setlocal enabledelayedexpansion

echo DNIWOO Professional Build Script
echo ==================================

REM Get version from plugin file
for /f "tokens=3" %%a in ('findstr "Version:" dniwoo.php') do set VERSION=%%a
echo Building version: %VERSION%

REM Create build directory
set BUILD_DIR=build
set PLUGIN_DIR=%BUILD_DIR%\dniwoo

echo Creating build directory...
if exist %BUILD_DIR% rmdir /s /q %BUILD_DIR%
mkdir %PLUGIN_DIR%

echo Copying production files...
copy dniwoo.php %PLUGIN_DIR%\
copy readme.txt %PLUGIN_DIR%\
copy CHANGELOG.md %PLUGIN_DIR%\

REM Copy directories
xcopy includes %PLUGIN_DIR%\includes\ /E /I /Q
xcopy assets %PLUGIN_DIR%\assets\ /E /I /Q
xcopy languages %PLUGIN_DIR%\languages\ /E /I /Q

REM Create vendor directory
mkdir %PLUGIN_DIR%\vendor
if exist vendor\plugin-update-checker (
    xcopy vendor\plugin-update-checker %PLUGIN_DIR%\vendor\plugin-update-checker\ /E /I /Q
)

REM Create ZIP using PowerShell
set ZIP_NAME=dniwoo-v%VERSION%.zip
echo Creating ZIP: %ZIP_NAME%

powershell -Command "Compress-Archive -Path '%BUILD_DIR%\dniwoo' -DestinationPath '%ZIP_NAME%' -CompressionLevel Optimal -Force"

REM Cleanup
rmdir /s /q %BUILD_DIR%

echo.
echo ✓ Build complete!
echo ✓ File created: %ZIP_NAME%
echo.
echo Installation instructions:
echo 1. Download: %ZIP_NAME%
echo 2. WordPress Admin ^> Plugins ^> Add New ^> Upload Plugin
echo 3. Upload the ZIP file and activate
echo.

REM Show file size
for %%A in (%ZIP_NAME%) do echo File size: %%~zA bytes

pause
