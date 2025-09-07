#!/bin/bash
# DNIWOO Installation Script
# This script installs DNIWOO plugin in the correct directory

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}DNIWOO Plugin Installer${NC}"
echo "================================"

# Check if we're in wp-content/plugins directory
if [[ ! -d "../../wp-config.php" && ! -d "../../../wp-config.php" ]]; then
    echo -e "${RED}Error: This script must be run from wp-content/plugins directory${NC}"
    exit 1
fi

# Download latest release
echo -e "${YELLOW}Downloading latest DNIWOO release...${NC}"
wget -O dniwoo-latest.zip https://github.com/replantadev/dniwoo/archive/refs/heads/main.zip

# Extract to correct directory
echo -e "${YELLOW}Extracting plugin...${NC}"
unzip -q dniwoo-latest.zip
mv dniwoo-main dniwoo

# Clean up
rm dniwoo-latest.zip

echo -e "${GREEN}✓ DNIWOO plugin installed successfully!${NC}"
echo "Please activate the plugin from WordPress admin panel."
echo ""
echo "Plugin location: wp-content/plugins/dniwoo/"
echo "Plugin name: DNIWOO - DNI/NIF for WooCommerce"
