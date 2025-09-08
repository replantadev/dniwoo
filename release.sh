#!/bin/bash
# DNIWOO Release Creator
# Creates GitHub releases with proper assets

if [ -z "$1" ]; then
    echo "Usage: ./release.sh [version]"
    echo "Example: ./release.sh 1.0.1"
    exit 1
fi

VERSION="$1"
ZIP_NAME="dniwoo-v${VERSION}.zip"

echo "DNIWOO Release Creator"
echo "====================="
echo "Creating release for version $VERSION..."

# 1. Build the plugin
echo "Step 1: Building plugin..."
./build.sh

# 2. Check if ZIP exists
if [ ! -f "$ZIP_NAME" ]; then
    echo "Error: $ZIP_NAME not found"
    exit 1
fi

# 3. Create git tag
echo "Step 2: Creating git tag..."
git tag "v$VERSION"
git push origin "v$VERSION"

echo ""
echo "✓ Release v$VERSION prepared!"
echo ""
echo "Next steps:"
echo "1. Go to: https://github.com/replantadev/dniwoo/releases"
echo "2. Click 'Create a new release'"
echo "3. Select tag: v$VERSION"
echo "4. Title: DNIWOO v$VERSION"
echo "5. Upload: $ZIP_NAME"
echo "6. Copy description from: release-notes-v$VERSION.md"
echo ""
