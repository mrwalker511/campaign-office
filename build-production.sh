#!/bin/bash

#
# Campaign Office Theme - Production Build Script
# Creates a clean production ZIP excluding development files
#

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
OUTPUT_DIR="${1:-.}"
VERSION="${2:-}"

echo -e "\n${BLUE}========================================"
echo "Campaign Office Theme - Production Build"
echo -e "========================================${NC}\n"

# Get theme directory (script location)
THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_NAME="$(basename "$THEME_DIR")"

echo -e "${BLUE}[1/5] Validating theme directory...${NC}"
if [ ! -f "$THEME_DIR/style.css" ]; then
    echo -e "${RED}Error: style.css not found. Are you in the theme directory?${NC}"
    exit 1
fi

# Get version from style.css if not provided
if [ -z "$VERSION" ]; then
    echo -e "${BLUE}[2/5] Reading version from style.css...${NC}"
    VERSION=$(grep -i "Version:" "$THEME_DIR/style.css" | head -n 1 | sed -E 's/.*Version:\s*([0-9.]+).*/\1/')
    if [ -z "$VERSION" ]; then
        VERSION="dev"
        echo -e "${YELLOW}    Warning: Version not found, using 'dev'${NC}"
    else
        echo -e "${GREEN}    Found version: $VERSION${NC}"
    fi
else
    echo -e "${BLUE}[2/5] Using provided version: $VERSION${NC}"
fi

# Create temp directory
TEMP_DIR=$(mktemp -d)
BUILD_DIR="$TEMP_DIR/$THEME_NAME"

echo -e "${BLUE}[3/5] Creating temporary build directory...${NC}"
mkdir -p "$BUILD_DIR"

# Cleanup function
cleanup() {
    if [ -d "$TEMP_DIR" ]; then
        rm -rf "$TEMP_DIR"
    fi
}
trap cleanup EXIT

echo -e "${BLUE}[4/5] Copying production files...${NC}"

# Use rsync if available, otherwise use cp
if command -v rsync &> /dev/null; then
    rsync -a \
        --exclude='.git' \
        --exclude='.gitignore' \
        --exclude='.gitattributes' \
        --exclude='node_modules' \
        --exclude='package.json' \
        --exclude='package-lock.json' \
        --exclude='.npmrc' \
        --exclude='composer.json' \
        --exclude='composer.lock' \
        --exclude='build-production.ps1' \
        --exclude='build-production.sh' \
        --exclude='build-testing.ps1' \
        --exclude='build/' \
        --exclude='scripts/' \
        --exclude='tests/' \
        --exclude='phpunit.xml' \
        --exclude='.phpunit.result.cache' \
        --exclude='playwright.config.js' \
        --exclude='playwright-report' \
        --exclude='test-results' \
        --exclude='docs/' \
        --exclude='.distignore' \
        --exclude='.github/' \
        --exclude='.vscode/' \
        --exclude='.idea/' \
        --exclude='*.code-workspace' \
        --exclude='.env' \
        --exclude='.env.*' \
        --exclude='*.log' \
        --exclude='debug.log' \
        --exclude='error_log' \
        --exclude='.DS_Store' \
        --exclude='Thumbs.db' \
        --exclude='desktop.ini' \
        --exclude='*.tmp' \
        --exclude='*.temp' \
        --exclude='*.cache' \
        --exclude='.sass-cache' \
        --exclude='.editorconfig' \
        --exclude='.eslintrc*' \
        --exclude='.stylelintrc*' \
        --exclude='.prettierrc*' \
        --exclude='.claude/' \
        --exclude='*.zip' \
        --exclude='assets/react/' \
        --exclude='assets/js/' \
        --exclude='blocks/**/index.js' \
        --exclude='blocks/**/view.js' \
        --include='assets/css/critical/' \
        --exclude='assets/css/*' \
        "$THEME_DIR/" "$BUILD_DIR/"

    ITEM_COUNT=$(find "$BUILD_DIR" -type f | wc -l)
    echo -e "${GREEN}    Copied $ITEM_COUNT files${NC}"
else
    # Fallback to cp with manual exclusion
    echo -e "${YELLOW}    rsync not found, using cp (slower)${NC}"
    cp -r "$THEME_DIR" "$BUILD_DIR"

    # Remove excluded items
    cd "$BUILD_DIR"
    rm -rf .git .gitignore .gitattributes 2>/dev/null || true
    rm -rf node_modules package.json package-lock.json .npmrc 2>/dev/null || true
    rm -rf composer.json composer.lock 2>/dev/null || true
    rm -f build-production.ps1 build-production.sh build-testing.ps1 2>/dev/null || true
    rm -rf build/ scripts/ 2>/dev/null || true
    rm -rf tests/ phpunit.xml .phpunit.result.cache 2>/dev/null || true
    rm -rf playwright.config.js playwright-report test-results 2>/dev/null || true
    rm -rf docs/ .distignore .github/ 2>/dev/null || true
    rm -rf .vscode .idea *.code-workspace 2>/dev/null || true
    rm -f .env .env.* 2>/dev/null || true
    rm -f *.log debug.log error_log 2>/dev/null || true
    rm -f .DS_Store Thumbs.db desktop.ini 2>/dev/null || true
    rm -f *.tmp *.temp *.cache 2>/dev/null || true
    rm -rf .sass-cache .claude/ 2>/dev/null || true
    rm -f .editorconfig .eslintrc* .stylelintrc* .prettierrc* 2>/dev/null || true
    rm -f *.zip 2>/dev/null || true
    rm -rf assets/react/ assets/js/ 2>/dev/null || true
    find blocks/ -name "index.js" -delete 2>/dev/null || true
    find blocks/ -name "view.js" -delete 2>/dev/null || true
    # Keep only critical CSS, remove other assets/css contents
    find assets/css/ -type f ! -path "assets/css/critical/*" -delete 2>/dev/null || true

    cd "$THEME_DIR"
    ITEM_COUNT=$(find "$BUILD_DIR" -type f | wc -l)
    echo -e "${GREEN}    Copied $ITEM_COUNT files${NC}"
fi

# Create ZIP file
ZIP_FILENAME="$THEME_NAME-$VERSION.zip"
ZIP_PATH="$OUTPUT_DIR/$ZIP_FILENAME"

echo -e "${BLUE}[5/5] Creating ZIP archive...${NC}"

# Remove existing ZIP if it exists
if [ -f "$ZIP_PATH" ]; then
    rm -f "$ZIP_PATH"
    echo -e "${YELLOW}    Removed existing ZIP file${NC}"
fi

# Create ZIP
cd "$TEMP_DIR"
if command -v zip &> /dev/null; then
    zip -r -q "$ZIP_PATH" "$THEME_NAME"
else
    echo -e "${RED}Error: 'zip' command not found. Please install zip utility.${NC}"
    exit 1
fi

# Get file size
if [ -f "$ZIP_PATH" ]; then
    ZIP_SIZE=$(stat -f%z "$ZIP_PATH" 2>/dev/null || stat -c%s "$ZIP_PATH" 2>/dev/null)
    ZIP_SIZE_KB=$(echo "scale=2; $ZIP_SIZE / 1024" | bc)
    ZIP_SIZE_MB=$(echo "scale=2; $ZIP_SIZE / 1048576" | bc)

    echo -e "\n${GREEN}========================================"
    echo "Build Complete!"
    echo -e "========================================${NC}\n"
    echo -e "${BLUE}Production ZIP created:${NC}"
    echo -e "${GREEN}  File: $ZIP_FILENAME${NC}"
    echo -e "${GREEN}  Location: $ZIP_PATH${NC}"

    # Show size in appropriate unit
    if (( $(echo "$ZIP_SIZE_MB > 1" | bc -l) )); then
        echo -e "${GREEN}  Size: ${ZIP_SIZE_MB} MB${NC}"
    else
        echo -e "${GREEN}  Size: ${ZIP_SIZE_KB} KB${NC}"
    fi

    echo -e "${GREEN}  Files: $ITEM_COUNT${NC}"
    echo -e "\n${GREEN}Ready for distribution!${NC}\n"
else
    echo -e "${RED}Error: ZIP file was not created${NC}"
    exit 1
fi
