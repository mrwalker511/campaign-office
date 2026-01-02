$errors = $null
$tokens = $null
[System.Management.Automation.Language.Parser]::ParseFile('C:\Users\Matt Walker\Desktop\wp\campaign-office\build-production.ps1',[ref]$errors,[ref]$tokens)
[System.Console]::WriteLine("ErrorsCount: $($errors.Count)")
[System.Console]::WriteLine("TokensCount: $($tokens.Count)")
if ($errors -and $errors.Count -gt 0) {
    $errors | Format-List -Property *
    exit 1
} else {
    Write-Host 'ParsedOK'
}
