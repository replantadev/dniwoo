#!/bin/bash
# DNIWOO Professional Build Script
# Creates a clean, production-ready plugin ZIP

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}DNIWOO Professional Build Script${NC}"
echo "=================================="

# Get version from plugin file
VERSION=$(grep "Version:" dniwoo.php | head -1 | awk '{print $3}')
echo -e "${YELLOW}Building version: $VERSION${NC}"

# Create build directory
BUILD_DIR="build"
PLUGIN_DIR="$BUILD_DIR/dniwoo"

echo -e "${YELLOW}Creating build directory...${NC}"
rm -rf $BUILD_DIR
mkdir -p $PLUGIN_DIR

# Copy only production files
echo -e "${YELLOW}Copying production files...${NC}"
cp dniwoo.php $PLUGIN_DIR/
cp README.md $PLUGIN_DIR/
cp CHANGELOG.md $PLUGIN_DIR/

# Copy directories
cp -r includes/ $PLUGIN_DIR/
cp -r assets/ $PLUGIN_DIR/
cp -r languages/ $PLUGIN_DIR/

# Create vendor directory and copy only essential files
mkdir -p $PLUGIN_DIR/vendor
if [ -d "vendor/plugin-update-checker" ]; then
    cp -r vendor/plugin-update-checker/ $PLUGIN_DIR/vendor/
fi

# Remove development files from build
find $PLUGIN_DIR -name "*.md" -not -name "README.md" -not -name "CHANGELOG.md" -delete
find $PLUGIN_DIR -name ".git*" -delete
find $PLUGIN_DIR -name "composer.*" -delete
find $PLUGIN_DIR -name "*.log" -delete
find $PLUGIN_DIR -name "*.tmp" -delete

# Create ZIP
ZIP_NAME="dniwoo-v$VERSION.zip"
echo -e "${YELLOW}Creating ZIP: $ZIP_NAME${NC}"

cd $BUILD_DIR
zip -r ../$ZIP_NAME dniwoo/ -x "*.DS_Store" "*.git*"
cd ..

# Cleanup
rm -rf $BUILD_DIR

echo -e "${GREEN}✓ Build complete!${NC}"
echo -e "${GREEN}✓ File created: $ZIP_NAME${NC}"
echo ""
echo -e "${BLUE}Installation instructions:${NC}"
echo "1. Download: $ZIP_NAME"
echo "2. WordPress Admin > Plugins > Add New > Upload Plugin"
echo "3. Upload the ZIP file and activate"
echo ""
echo -e "${BLUE}File size:${NC} $(du -h $ZIP_NAME | cut -f1)"
