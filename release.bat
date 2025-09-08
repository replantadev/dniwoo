@echo off
REM DNIWOO Release Creator
REM Creates GitHub releases with proper assets

setlocal enabledelayedexpansion

echo DNIWOO Release Creator
echo =====================

if "%1"=="" (
    echo Usage: release.bat [version]
    echo Example: release.bat 1.0.1
    exit /b 1
)

set VERSION=%1
set ZIP_NAME=dniwoo-v%VERSION%.zip

echo Creating release for version %VERSION%...

REM 1. Build the plugin
echo Step 1: Building plugin...
call build.bat

REM 2. Check if ZIP exists
if not exist "%ZIP_NAME%" (
    echo Error: %ZIP_NAME% not found
    exit /b 1
)

REM 3. Create git tag
echo Step 2: Creating git tag...
git tag v%VERSION%
git push origin v%VERSION%

echo.
echo ✓ Release v%VERSION% prepared!
echo.
echo Next steps:
echo 1. Go to: https://github.com/replantadev/dniwoo/releases
echo 2. Click "Create a new release"
echo 3. Select tag: v%VERSION%
echo 4. Title: DNIWOO v%VERSION%
echo 5. Upload: %ZIP_NAME%
echo 6. Copy description from: release-notes-v%VERSION%.md
echo.

pause
