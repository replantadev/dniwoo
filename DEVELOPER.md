# DNIWOO - Developer Guide

## Professional Distribution Strategy

This plugin follows WordPress.org standards for professional distribution and updates.

## How Pros Handle Plugin Distribution

### 1. **GitHub Releases (Not Code Downloads)**
- ✅ Users download from **Releases** tab, not "Code" button
- ✅ Each release contains a clean ZIP file
- ✅ No `-main` suffix problems
- ✅ Professional versioning

### 2. **Build Process**
```bash
# Create production build
./build.sh

# This creates: dniwoo-v1.0.0.zip
# Structure: dniwoo/dniwoo.php (correct!)
```

### 3. **Auto-Updates via Plugin Update Checker**
- Users get notified of updates in WordPress admin
- One-click updates from GitHub releases
- No manual downloads needed after first install

## Distribution Workflow

### For Developers:
```bash
# 1. Update version in dniwoo.php
# 2. Update CHANGELOG.md
# 3. Commit changes
git add .
git commit -m "Release v1.0.1"

# 4. Create tag
git tag v1.0.1

# 5. Push (triggers auto-release)
git push origin main --tags
```

### For Users:
1. **First Install**: Download ZIP from GitHub Releases
2. **Updates**: Automatic via WordPress admin notifications

## Release Automation

The `.github/workflows/release.yml` automatically:
- Detects version tags (`v1.0.1`)
- Creates clean plugin ZIP
- Publishes GitHub Release
- Users get auto-update notifications

## Directory Structure (Production)

```
dniwoo-v1.0.0.zip
└── dniwoo/                    ← Correct folder name
    ├── dniwoo.php            ← Main plugin file
    ├── includes/             ← PHP classes
    ├── assets/               ← CSS/JS
    ├── languages/            ← Translations
    ├── vendor/               ← Update checker only
    ├── README.md             ← User documentation
    └── CHANGELOG.md          ← Version history
```

## Why This Prevents Duplicates

### ❌ **Amateur Approach:**
- User downloads from "Code" button
- Gets `dniwoo-main.zip`
- Extracts to `dniwoo-main/`
- WordPress sees wrong folder name
- Conflicts with existing installations

### ✅ **Professional Approach:**
- User downloads from "Releases"
- Gets `dniwoo-v1.0.0.zip`
- Contains clean `dniwoo/` folder
- WordPress recognizes correctly
- Auto-updates work seamlessly

## Commands for Different Platforms

### Linux/Mac:
```bash
chmod +x build.sh
./build.sh
```

### Windows:
```cmd
build.bat
```

### Manual Build:
```bash
# Create structure
mkdir -p build/dniwoo
cp -r includes assets languages build/dniwoo/
cp dniwoo.php README.md CHANGELOG.md build/dniwoo/

# Create ZIP
cd build && zip -r ../dniwoo-v1.0.0.zip dniwoo/
```

## Professional Standards Checklist

- ✅ Clean folder structure
- ✅ Semantic versioning
- ✅ GitHub Releases
- ✅ Auto-update system
- ✅ Build automation
- ✅ No development files in distribution
- ✅ Proper plugin headers
- ✅ Update URI specification

## User Instructions

Direct users to:
1. **GitHub Releases**: https://github.com/replantadev/dniwoo/releases
2. **Download latest ZIP** (not "Code" button)
3. **Upload via WordPress admin**

## Troubleshooting

### If users still download from "Code":
- Add prominent notice in README
- Hide "Code" button for releases
- Clear installation instructions

### If duplicates persist:
- Check folder naming in ZIP
- Verify plugin headers
- Test clean WordPress install

This approach matches how professional WordPress plugins like Elementor, WooCommerce, and others handle distribution.
