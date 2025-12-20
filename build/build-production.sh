#!/bin/bash

# Campaign Office Theme - Production Build Script
# This script creates a production-ready distribution package

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
THEME_NAME="campaign-office"
VERSION=$(grep "Version:" style.css | head -1 | awk '{print $2}')
BUILD_DIR="build"
DIST_DIR="$BUILD_DIR/$THEME_NAME"
DIST_FILE="$THEME_NAME-$VERSION.zip"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Campaign Office Production Build${NC}"
echo -e "${BLUE}Version: $VERSION${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Clean previous builds
echo -e "${YELLOW}→ Cleaning previous builds...${NC}"
if [ -d "$BUILD_DIR" ]; then
    rm -rf "$BUILD_DIR"
fi
mkdir -p "$DIST_DIR"

# Copy files (excluding .distignore items)
echo -e "${YELLOW}→ Copying theme files...${NC}"
rsync -av \
    --exclude-from='.distignore' \
    --exclude='build/' \
    ./ "$DIST_DIR/"

# Optimize screenshot if imagemagick is available
if command -v convert &> /dev/null; then
    echo -e "${YELLOW}→ Optimizing screenshot...${NC}"
    if [ -f "$DIST_DIR/screenshot.png" ]; then
        convert "$DIST_DIR/screenshot.png" \
            -resize 1200x900 \
            -quality 80 \
            "$DIST_DIR/screenshot-optimized.png"
        mv "$DIST_DIR/screenshot-optimized.png" "$DIST_DIR/screenshot.png"
        echo -e "${GREEN}✓ Screenshot optimized${NC}"
    fi
else
    echo -e "${YELLOW}! ImageMagick not found, skipping screenshot optimization${NC}"
fi

# Remove any remaining unwanted files
echo -e "${YELLOW}→ Cleaning distribution...${NC}"
find "$DIST_DIR" -name ".DS_Store" -delete
find "$DIST_DIR" -name "Thumbs.db" -delete
find "$DIST_DIR" -name "*.log" -delete
find "$DIST_DIR" -name "*.tmp" -delete
find "$DIST_DIR" -name "*.bak" -delete

# Check for sensitive data
echo -e "${YELLOW}→ Checking for sensitive data...${NC}"
SENSITIVE_FILES=".env .env.local secrets.json"
FOUND_SENSITIVE=false
for file in $SENSITIVE_FILES; do
    if [ -f "$DIST_DIR/$file" ]; then
        echo -e "${RED}✗ WARNING: Found sensitive file: $file${NC}"
        rm "$DIST_DIR/$file"
        FOUND_SENSITIVE=true
    fi
done
if [ "$FOUND_SENSITIVE" = false ]; then
    echo -e "${GREEN}✓ No sensitive files found${NC}"
fi

# Create LICENSE.txt if it doesn't exist
if [ ! -f "$DIST_DIR/LICENSE.txt" ]; then
    echo -e "${YELLOW}→ Creating LICENSE.txt...${NC}"
    cat > "$DIST_DIR/LICENSE.txt" << 'EOF'
Campaign Office WordPress Theme
Copyright (C) 2024

This theme is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This theme is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this theme. If not, see <http://www.gnu.org/licenses/>.
EOF
    echo -e "${GREEN}✓ LICENSE.txt created${NC}"
fi

# Calculate size
echo -e "${YELLOW}→ Calculating package size...${NC}"
SIZE=$(du -sh "$DIST_DIR" | cut -f1)
echo -e "${GREEN}✓ Distribution size: $SIZE${NC}"

# Create zip file
echo -e "${YELLOW}→ Creating zip archive...${NC}"
cd "$BUILD_DIR"
zip -r -q "$DIST_FILE" "$THEME_NAME/"
cd ..

FINAL_SIZE=$(du -sh "$BUILD_DIR/$DIST_FILE" | cut -f1)

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}✓ Build Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}File: $BUILD_DIR/$DIST_FILE${NC}"
echo -e "${GREEN}Size: $FINAL_SIZE${NC}"
echo -e "${GREEN}Version: $VERSION${NC}\n"

# Show recommendations
echo -e "${BLUE}Next Steps:${NC}"
echo -e "  1. Test installation: Unzip and install in WordPress"
echo -e "  2. Check theme check plugin: Test with Theme Check"
echo -e "  3. Verify all features work without dev dependencies"
echo -e "  4. Review documentation for customers"
echo -e "  5. Upload to marketplace\n"

# Size warnings
SIZE_BYTES=$(stat -f%z "$BUILD_DIR/$DIST_FILE" 2>/dev/null || stat -c%s "$BUILD_DIR/$DIST_FILE")
SIZE_MB=$((SIZE_BYTES / 1024 / 1024))

if [ $SIZE_MB -gt 10 ]; then
    echo -e "${YELLOW}⚠ WARNING: Theme size is ${SIZE_MB}MB${NC}"
    echo -e "${YELLOW}  Consider optimizing further for better download speeds${NC}\n"
elif [ $SIZE_MB -gt 5 ]; then
    echo -e "${YELLOW}ℹ Theme size is ${SIZE_MB}MB - within acceptable range${NC}\n"
else
    echo -e "${GREEN}✓ Theme size is ${SIZE_MB}MB - excellent!${NC}\n"
fi
