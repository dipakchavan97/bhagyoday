# ==============================================================================
# Bhagyoday Ayurveda - Safe Database Export Script (PowerShell / WP-CLI)
# ==============================================================================
# Usage:
#   .\scripts\export-safe-db.ps1 [-OutputFile "safe_catalog_export.sql"]
# ==============================================================================

param(
    [string]$OutputFile = "safe_catalog_export_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
)

$ErrorActionPreference = "Stop"

Write-Host "==> Starting safe WordPress database export..." -ForegroundColor Cyan

# Tables containing customer PII and sensitive transaction data to EXCLUDE:
$ExcludeTables = @(
    "wp_users",
    "wp_usermeta",
    "wp_woocommerce_order_items",
    "wp_woocommerce_order_itemmeta",
    "wp_wc_orders",
    "wp_wc_orders_meta",
    "wp_wc_order_addresses",
    "wp_wc_order_operational_data",
    "wp_wc_order_stats",
    "wp_wc_order_product_lookup",
    "wp_wc_order_tax_lookup",
    "wp_wc_order_coupon_lookup",
    "wp_wc_customer_lookup",
    "wp_woocommerce_sessions",
    "wp_woocommerce_payment_tokenmeta",
    "wp_woocommerce_payment_tokens",
    "wp_woocommerce_log"
)

$ExcludeArgs = ($ExcludeTables | ForEach-Object { "--exclude_tables=$_" }) -join " "

wp db export $OutputFile $ExcludeArgs.Split(" ") --add-drop-table

Write-Host "==> Safe database export complete: $OutputFile" -ForegroundColor Green
Write-Host "==> WARNING: Never commit real database dumps containing secrets or customer data." -ForegroundColor Yellow
