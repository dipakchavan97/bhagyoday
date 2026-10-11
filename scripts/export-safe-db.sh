#!/usr/bin/env bash
# ==============================================================================
# Bhagyoday Ayurveda - Safe Database Export Script (WP-CLI)
# ==============================================================================
# This script exports the WordPress database while excluding customer PII,
# orders, payment transaction logs, customer sessions, and user credentials.
#
# Usage:
#   bash scripts/export-safe-db.sh [output_filename.sql]
# ==============================================================================

set -euo pipefail

OUTPUT_FILE="${1:-safe_catalog_export_$(date +%Y%m%d_%H%M%S).sql}"

echo "==> Starting safe WordPress database export..."

# Tables containing customer PII and sensitive transaction data to EXCLUDE:
EXCLUDE_TABLES=(
    "wp_users"
    "wp_usermeta"
    "wp_woocommerce_order_items"
    "wp_woocommerce_order_itemmeta"
    "wp_wc_orders"
    "wp_wc_orders_meta"
    "wp_wc_order_addresses"
    "wp_wc_order_operational_data"
    "wp_wc_order_stats"
    "wp_wc_order_product_lookup"
    "wp_wc_order_tax_lookup"
    "wp_wc_order_coupon_lookup"
    "wp_wc_customer_lookup"
    "wp_woocommerce_sessions"
    "wp_woocommerce_payment_tokenmeta"
    "wp_woocommerce_payment_tokens"
    "wp_woocommerce_log"
)

EXCLUDE_ARGS=""
for table in "${EXCLUDE_TABLES[@]}"; do
    EXCLUDE_ARGS="${EXCLUDE_ARGS} --exclude_tables=${table}"
done

# Run WP-CLI export
wp db export "${OUTPUT_FILE}" ${EXCLUDE_ARGS} --add-drop-table

echo "==> Export completed successfully: ${OUTPUT_FILE}"
echo "==> Note: Verify the exported SQL does not contain secrets before sharing."
