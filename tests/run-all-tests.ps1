# Master Test Runner for Campaign Office Theme (Windows PowerShell)
#
# Runs all test suites and generates comprehensive report
#

param(
    [switch]$PHP = $true,
    [switch]$JS = $true,
    [switch]$Lint = $true,
    [switch]$ThemeCheck = $true,
    [switch]$A11y = $false,
    [switch]$E2E = $false,
    [switch]$Performance = $false,
    [switch]$All = $false
)

# Enable all tests if -All is passed
if ($All) {
    $PHP = $true
    $JS = $true
    $Lint = $true
    $ThemeCheck = $true
    $A11y = $true
    $E2E = $true
    $Performance = $true
}

# Colors
$Red = "Red"
$Green = "Green"
$Yellow = "Yellow"
$Blue = "Cyan"

# Track results
$TestResults = @{}
$OverallStatus = 0

function Write-Header {
    param([string]$Message)
    Write-Host "`n========================================" -ForegroundColor $Blue
    Write-Host $Message -ForegroundColor $Blue
    Write-Host "========================================`n" -ForegroundColor $Blue
}

function Run-Test {
    param(
        [string]$TestName,
        [string]$Command
    )
    
    Write-Host "`n▶ Running: $TestName" -ForegroundColor $Yellow
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor $Yellow
    
    try {
        $output = Invoke-Expression $Command 2>&1
        $exitCode = $LASTEXITCODE
        
        Write-Host $output
        
        if ($exitCode -eq 0) {
            Write-Host "✓ $TestName passed" -ForegroundColor $Green
            $script:TestResults[$TestName] = "PASS"
            return $true
        } else {
            Write-Host "✗ $TestName failed" -ForegroundColor $Red
            $script:TestResults[$TestName] = "FAIL"
            $script:OverallStatus = 1
            return $false
        }
    }
    catch {
        Write-Host "✗ $TestName failed with error: $_" -ForegroundColor $Red
        $script:TestResults[$TestName] = "FAIL"
        $script:OverallStatus = 1
        return $false
    }
}

# Header
Write-Header "Campaign Office Theme - Test Suite"

# Check dependencies
Write-Host "Checking dependencies...`n" -ForegroundColor $Blue

if (!(Test-Path "vendor") -and $PHP) {
    Write-Host "Installing PHP dependencies..." -ForegroundColor $Yellow
    composer install --no-interaction
}

if (!(Test-Path "node_modules") -and $JS) {
    Write-Host "Installing Node dependencies..." -ForegroundColor $Yellow
    npm install
}

# 1. Theme Check
if ($ThemeCheck) {
    Run-Test "Theme Check" "node tests/theme-check.js" | Out-Null
}

# 2. PHP Syntax Check
if ($Lint) {
    Run-Test "PHP Syntax Check" "composer lint" | Out-Null
}

# 3. PHP Code Standards
if ($Lint) {
    Run-Test "PHP Code Standards (PHPCS)" "composer phpcs" | Out-Null
}

# 4. PHP Unit Tests
if ($PHP) {
    if (Test-Path "vendor/bin/phpunit") {
        Run-Test "PHP Unit Tests" "composer test" | Out-Null
    } else {
        Write-Host "⊘ Skipping PHP tests - PHPUnit not installed" -ForegroundColor $Yellow
        Write-Host "  Run: composer install" -ForegroundColor $Yellow
    }
}

# 5. JavaScript Linting
if ($Lint) {
    Run-Test "JavaScript Linting (ESLint)" "npm run lint:js" | Out-Null
}

# 6. CSS Linting
if ($Lint) {
    Run-Test "CSS Linting (Stylelint)" "npm run lint:css" | Out-Null
}

# 7. JavaScript Unit Tests
if ($JS) {
    Run-Test "JavaScript Unit Tests (Jest)" "npm run test:js" | Out-Null
}

# 8. Accessibility Tests (requires running site)
if ($A11y) {
    Run-Test "Accessibility Tests" "npm run test:a11y" | Out-Null
}

# 9. E2E Tests (requires running site)
if ($E2E) {
    Run-Test "E2E Tests (Playwright)" "npm run test:e2e" | Out-Null
}

# 10. Performance Tests (requires running site)
if ($Performance) {
    Run-Test "Performance Tests" "npm run test:performance" | Out-Null
}

# Summary
Write-Header "Test Results Summary"

$PassCount = 0
$FailCount = 0

foreach ($test in $TestResults.GetEnumerator()) {
    if ($test.Value -eq "PASS") {
        Write-Host "✓ $($test.Key)" -ForegroundColor $Green
        $PassCount++
    } else {
        Write-Host "✗ $($test.Key)" -ForegroundColor $Red
        $FailCount++
    }
}

Write-Host "`n========================================" -ForegroundColor $Blue
Write-Host "Total: $($PassCount + $FailCount) tests"
Write-Host "Passed: $PassCount" -ForegroundColor $Green
Write-Host "Failed: $FailCount" -ForegroundColor $Red
Write-Host "========================================`n" -ForegroundColor $Blue

# Final status
if ($OverallStatus -eq 0 -and $FailCount -eq 0) {
    Write-Host "✓ All tests passed!`n" -ForegroundColor $Green
    exit 0
} else {
    Write-Host "✗ Some tests failed`n" -ForegroundColor $Red
    Write-Host "Run individual test suites for more details:"
    Write-Host "  - Theme check:    node tests/theme-check.js"
    Write-Host "  - PHP tests:      composer test"
    Write-Host "  - JS tests:       npm run test:js"
    Write-Host "  - Lint:           npm run test:lint"
    Write-Host "  - A11y:           npm run test:a11y"
    Write-Host "  - E2E:            npm run test:e2e"
    Write-Host "  - Performance:    npm run test:performance"
    Write-Host ""
    exit 1
}
