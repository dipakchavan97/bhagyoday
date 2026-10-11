# ==============================================================================
# Bhagyoday Ayurveda - Safe Database Import Script (PowerShell / WP-CLI)
# ==============================================================================
# Usage:
#   .\scripts\import-safe-db.ps1 -SqlFile "safe_catalog.sql" -NewUrl "http://localhost:8080" [-OldUrl "https://bhagyodayayurveda.in"]
# ==============================================================================

param(
    [Parameter(Mandatory=$true)]
    [string]$SqlFile,
    
    [Parameter(Mandatory=$true)]
    [string]$NewUrl,

    [string]$OldUrl = "https://bhagyodayayurveda.in"
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path $SqlFile)) {
    Write-Error "SQL file not found: $SqlFile"
}

Write-Host "==> Importing database dump: $SqlFile..." -ForegroundColor Cyan
wp db import $SqlFile

Write-Host "==> Replacing site URLs from $OldUrl to $NewUrl..." -ForegroundColor Cyan
wp search-replace $OldUrl $NewUrl --all-tables

Write-Host "==> Flushing rewrite rules and cache..." -ForegroundColor Cyan
wp rewrite flush --hard
wp cache flush

Write-Host "==> Database imported and prepared for local development successfully!" -ForegroundColor Green
