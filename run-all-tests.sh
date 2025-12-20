#!/bin/bash
#
# Master Test Runner for Campaign Office Theme
#
# Runs all test suites and generates comprehensive report
#

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
RUN_PHP_TESTS=${RUN_PHP_TESTS:-true}
RUN_JS_TESTS=${RUN_JS_TESTS:-true}
RUN_LINT=${RUN_LINT:-true}
RUN_THEME_CHECK=${RUN_THEME_CHECK:-true}
RUN_A11Y=${RUN_A11Y:-false}  # Requires running WordPress instance
RUN_E2E=${RUN_E2E:-false}    # Requires running WordPress instance
RUN_PERFORMANCE=${RUN_PERFORMANCE:-false}  # Requires running WordPress instance

# Test results
declare -A TEST_RESULTS

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Campaign Office Theme - Test Suite${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Function to run a test and track results
run_test() {
    local test_name=$1
    local test_command=$2

    echo -e "\n${YELLOW}▶ Running: ${test_name}${NC}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

    if eval "$test_command"; then
        echo -e "${GREEN}✓ ${test_name} passed${NC}"
        TEST_RESULTS[$test_name]="PASS"
        return 0
    else
        echo -e "${RED}✗ ${test_name} failed${NC}"
        TEST_RESULTS[$test_name]="FAIL"
        return 1
    fi
}

# Check dependencies
echo -e "${BLUE}Checking dependencies...${NC}\n"

if [ ! -d "vendor" ] && [ "$RUN_PHP_TESTS" = true ]; then
    echo -e "${YELLOW}Installing PHP dependencies...${NC}"
    composer install --no-interaction
fi

if [ ! -d "node_modules" ] && [ "$RUN_JS_TESTS" = true ]; then
    echo -e "${YELLOW}Installing Node dependencies...${NC}"
    npm install
fi

# Track overall status
OVERALL_STATUS=0

# 1. Theme Check
if [ "$RUN_THEME_CHECK" = true ]; then
    run_test "Theme Check" "node tests/theme-check.js" || OVERALL_STATUS=1
fi

# 2. PHP Linting
if [ "$RUN_LINT" = true ]; then
    run_test "PHP Syntax Check" "composer lint" || OVERALL_STATUS=1
fi

# 3. PHP Code Standards
if [ "$RUN_LINT" = true ]; then
    run_test "PHP Code Standards (PHPCS)" "composer phpcs" || OVERALL_STATUS=1
fi

# 4. PHP Unit Tests
if [ "$RUN_PHP_TESTS" = true ]; then
    if [ -d "vendor/phpunit" ] || [ -f "vendor/bin/phpunit" ]; then
        run_test "PHP Unit Tests" "composer test" || OVERALL_STATUS=1
    else
        echo -e "${YELLOW}⊘ Skipping PHP tests - PHPUnit not installed${NC}"
        echo -e "${YELLOW}  Run: composer install${NC}"
    fi
fi

# 5. JavaScript Linting
if [ "$RUN_LINT" = true ]; then
    run_test "JavaScript Linting (ESLint)" "npm run lint:js" || OVERALL_STATUS=1
fi

# 6. CSS Linting
if [ "$RUN_LINT" = true ]; then
    run_test "CSS Linting (Stylelint)" "npm run lint:css" || OVERALL_STATUS=1
fi

# 7. JavaScript Unit Tests
if [ "$RUN_JS_TESTS" = true ]; then
    run_test "JavaScript Unit Tests (Jest)" "npm run test:js" || OVERALL_STATUS=1
fi

# 8. Accessibility Tests (requires running site)
if [ "$RUN_A11Y" = true ]; then
    run_test "Accessibility Tests" "npm run test:a11y" || OVERALL_STATUS=1
fi

# 9. E2E Tests (requires running site)
if [ "$RUN_E2E" = true ]; then
    run_test "E2E Tests (Playwright)" "npm run test:e2e" || OVERALL_STATUS=1
fi

# 10. Performance Tests (requires running site)
if [ "$RUN_PERFORMANCE" = true ]; then
    run_test "Performance Tests" "npm run test:performance" || OVERALL_STATUS=1
fi

# Summary
echo -e "\n${BLUE}========================================${NC}"
echo -e "${BLUE}Test Results Summary${NC}"
echo -e "${BLUE}========================================${NC}\n"

PASS_COUNT=0
FAIL_COUNT=0

for test_name in "${!TEST_RESULTS[@]}"; do
    result="${TEST_RESULTS[$test_name]}"
    if [ "$result" = "PASS" ]; then
        echo -e "${GREEN}✓${NC} $test_name"
        ((PASS_COUNT++))
    else
        echo -e "${RED}✗${NC} $test_name"
        ((FAIL_COUNT++))
    fi
done

echo -e "\n${BLUE}========================================${NC}"
echo -e "Total: $((PASS_COUNT + FAIL_COUNT)) tests"
echo -e "${GREEN}Passed: $PASS_COUNT${NC}"
echo -e "${RED}Failed: $FAIL_COUNT${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Final status
if [ $OVERALL_STATUS -eq 0 ] && [ $FAIL_COUNT -eq 0 ]; then
    echo -e "${GREEN}✓ All tests passed!${NC}\n"
    exit 0
else
    echo -e "${RED}✗ Some tests failed${NC}\n"
    echo "Run individual test suites for more details:"
    echo "  - Theme check:    node tests/theme-check.js"
    echo "  - PHP tests:      composer test"
    echo "  - JS tests:       npm run test:js"
    echo "  - Lint:           npm run test:lint"
    echo "  - A11y:           npm run test:a11y"
    echo "  - E2E:            npm run test:e2e"
    echo "  - Performance:    npm run test:performance"
    echo ""
    exit 1
fi
