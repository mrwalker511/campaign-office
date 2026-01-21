#!/bin/bash

# Campaign Office Theme - Testing Build Script
# Creates a lightweight package for testing on live hosts

set -e  # Exit on error

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
THEME_NAME="campaign-office"
VERSION=$(grep "Version:" ../style.css | head -1 | awk '{print $2}')
BUILD_DIR="testing"
DIST_DIR="$BUILD_DIR/$THEME_NAME"
DIST_FILE="$THEME_NAME-testing.zip"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Campaign Office Testing Build${NC}"
echo -e "${BLUE}Version: $VERSION${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Clean previous builds
echo -e "${YELLOW}→ Cleaning previous testing builds...${NC}"
if [ -d "$BUILD_DIR" ]; then
    rm -rf "$BUILD_DIR"
fi
mkdir -p "$DIST_DIR"

# Copy theme files (excluding .distignore items)
echo -e "${YELLOW}→ Copying theme files...${NC}"
cd ..
rsync -av \
    --exclude-from='.distignore' \
    --exclude='build/' \
    ./ "build/$DIST_DIR/"
cd build

# Remove any remaining unwanted files
echo -e "${YELLOW}→ Cleaning distribution...${NC}"
find "$DIST_DIR" -name ".DS_Store" -delete 2>/dev/null || true
find "$DIST_DIR" -name "Thumbs.db" -delete 2>/dev/null || true
find "$DIST_DIR" -name "*.log" -delete 2>/dev/null || true
find "$DIST_DIR" -name "*.tmp" -delete 2>/dev/null || true

# Check for sensitive data
echo -e "${YELLOW}→ Checking for sensitive files...${NC}"
SENSITIVE_FILES=".env .env.local secrets.json credentials.json"
FOUND_SENSITIVE=false
for file in $SENSITIVE_FILES; do
    if [ -f "$DIST_DIR/$file" ]; then
        echo -e "${YELLOW}⚠ Removing sensitive file: $file${NC}"
        rm "$DIST_DIR/$file"
        FOUND_SENSITIVE=true
    fi
done
if [ "$FOUND_SENSITIVE" = false ]; then
    echo -e "${GREEN}✓ No sensitive files found${NC}"
fi

# Calculate size
echo -e "${YELLOW}→ Calculating package size...${NC}"
SIZE=$(du -sh "$DIST_DIR" | cut -f1)
echo -e "${GREEN}✓ Distribution size: $SIZE${NC}"

# Create zip file
echo -e "${YELLOW}→ Creating zip archive...${NC}"
zip -r -q "$DIST_FILE" "$THEME_NAME/"

FINAL_SIZE=$(du -sh "$DIST_FILE" | cut -f1)

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}✓ Testing Build Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}File: build/$DIST_FILE${NC}"
echo -e "${GREEN}Size: $FINAL_SIZE${NC}"
echo -e "${GREEN}Version: $VERSION${NC}\n"

echo -e "${BLUE}Upload Instructions:${NC}"
echo -e "  1. Upload $DIST_FILE to your host"
echo -e "  2. Extract to wp-content/themes/"
echo -e "  3. Activate theme in WordPress admin"
echo -e "  4. Test appearance and functionality\n"

echo -e "${YELLOW}Note: This is a testing build${NC}"
echo -e "${YELLOW}Includes: All theme files, premium features${NC}"
echo -e "${YELLOW}Excludes: Dev dependencies, docs, git files${NC}\n"
