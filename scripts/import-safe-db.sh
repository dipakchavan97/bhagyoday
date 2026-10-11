#!/usr/bin/env bash
# ==============================================================================
# Bhagyoday Ayurveda - Safe Database Import Script (WP-CLI)
# ==============================================================================
# Usage:
#   bash scripts/import-safe-db.sh <database_dump.sql> <new_site_url>
# Example:
#   bash scripts/import-safe-db.sh safe_catalog.sql http://localhost:8080
# ==============================================================================

set -euo pipefail

if [ $# -lt 2 ]; then
    echo "Usage: $0 <sql_file> <new_site_url> [old_site_url]"
    echo "Example: $0 safe_catalog.sql http://localhost:8080 https://bhagyodayayurveda.in"
    exit 1
fi

SQL_FILE="$1"
NEW_URL="$2"
OLD_URL="${3:-https://bhagyodayayurveda.in}"

echo "==> Importing database dump: ${SQL_FILE}..."
wp db import "${SQL_FILE}"

echo "==> Replacing site URLs from ${OLD_URL} to ${NEW_URL}..."
wp search-replace "${OLD_URL}" "${NEW_URL}" --all-tables

echo "==> Flushing rewrite rules and cache..."
wp rewrite flush --hard
wp cache flush

echo "==> Database imported and prepared for local development successfully!"
