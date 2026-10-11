# GitHub Backup & Project Audit Report

**Project:** Bhagyoday Ayurveda (`https://bhagyodayayurveda.in`)  
**Target Repository:** `https://github.com/dipakchavan97/bhagyoday.git`  
**Target Branch:** `main`  
**Backup Commit:** `chore: back up complete Bhagyoday Ayurveda project`  
**Backup Date:** 2026-10-11  
**Status:** Prepared & Ready for GitHub Push  

---

## 1. Project Root & Repository Audit

- **Project Root:** `D:\woocom`
- **Active WordPress Theme:** `themes/ayur-wellness` (Bespoke Ayurvedic responsive theme, 3D Three.js interactive hero banner, custom product layout, sticky Add-To-Cart, responsive mini-cart, and redesigned customer My Account portal)
- **Active Custom Plugin:** `plugins/ayur-core` (Modular suite providing instant checkout, Razorpay COD ₹100 confirmation fee, Google Places address autofill, quantity discount tiers, photo reviews engine, Shiprocket integration, and Meta Conversions API tracking)
- **Diagnostic Plugin:** `plugins/test-probe` (Automated diagnostic probe for headless integration testing)
- **Core Invariants:** ₹890 core product selling price, SKU preservation, Shiprocket dimension/weight preservation, authentic packaging-verified botanical compositions (names only customer-facing)
- **Initial Branch:** `master` (renamed to `main` to align with GitHub standards and the target branch requirement)
- **Commit History:** 43 historical development commits preserved without any squashing, rebase, or force-push.
- **Configured Remotes:**
  - `origin`: `https://github.com/dipakchavan97/bhagyoday.git`
  - `github-backup`: `https://github.com/dipakchavan97/bhagyoday.git`

---

## 2. Security Audit Findings

A rigorous multi-pass security inspection was conducted across all tracked files, untracked files, ignored files, and git history:

1. **WordPress Database Credentials:**
   - **Finding:** No `wp-config.php`, `wp-config-local.php`, or database passwords exist in tracked files or Git history.
   - **Mitigation:** `.env.example` provides safe placeholders. Added strict exclusion in `.gitignore` for `wp-config*.php` and `.env*`.

2. **Razorpay Credentials & Signatures:**
   - **Finding:** Zero live Razorpay keys (`rzp_live`) or live secrets found in Git history or tracked files.
   - **Details:** Sandbox fallback string `'rzp_test_placeholder'` and mock IDs are used in test fixtures. Production credentials are dynamically loaded from secure WordPress options in the database.

3. **Google API Keys:**
   - **Finding:** No active Google API keys found.
   - **Details:** Settings field contains only the placeholder pattern `AIzaSy...` in HTML form markup.

4. **Meta Conversions API (CAPI) & Pixel:**
   - **Finding:** No active access tokens found.
   - **Details:** UI input field contains only the placeholder pattern `EAAB...` in HTML form markup.

5. **Shiprocket & WhatsApp Credentials:**
   - **Finding:** No production Shiprocket passwords or WhatsApp auth tokens tracked in source files. Values are managed via WordPress admin options.

6. **Customer Personal Identifiable Information (PII):**
   - **Finding:** No customer order records, customer emails, customer phone numbers, or customer shipping addresses are tracked in the repository.
   - **Public Store Info:** Store customer support email (`vijaykulal8833@gmail.com`) and helpline (`+91 8888686858`) appearing in headers and footers are verified official public contact channels.

7. **Database Dumps & Backups:**
   - **Finding:** No `.sql`, `.dump`, or `.tar.gz` files exist in the repository.
   - **Mitigation:** `.gitignore` explicitly prevents accidental commits of database dumps. Safe export/import scripts created in `scripts/` to ensure future database dumps exclude customer PII.

---

## 3. Files Included in Source Control

The repository contains all source code and documentation required to continue development:

1. **Theme Source Code (`themes/ayur-wellness/`):**
   - Templates: `front-page.php`, `template-home.php`, `header.php`, `footer.php`, `page.php`, `page-checkout.php`, `page-my-account.php`, `404.php`, `index.php`
   - Styles: `style.css` (complete responsive design system)
   - Scripts: `assets/js/ayur-theme.js`, `assets/js/ayur-hero-3d.js`, `assets/js/three.min.js`
   - Assets: `assets/images/hero/`
   - WooCommerce overrides: `single-product.php`, `archive-product.php`, `content-product.php`, and `myaccount/` dashboard templates

2. **Core Plugin Source Code (`plugins/ayur-core/`):**
   - Main loader: `ayur-core.php`
   - Includes: All 15 functional classes (`class-ayur-*.php`)
   - Assets: Admin & frontend CSS, JavaScript, and WebP product imagery

3. **Diagnostic Test Plugin (`plugins/test-probe/`):**
   - `test-probe.php`

4. **Product Imagery (`imported_images/`):**
   - Authentic packaging photos, composition labels, and product photography

5. **Safe Migration & Export Scripts (`scripts/`):**
   - `export-safe-db.sh` & `export-safe-db.ps1` (WP-CLI export excluding customer data)
   - `import-safe-db.sh` & `import-safe-db.ps1` (WP-CLI import and search-replace)

6. **Documentation & Architecture Reports:**
   - `README.md`
   - `FIVE_PRODUCT_COMPOSITION_UPDATE_REPORT.md`
   - `AUDIT_REPORT.md`
   - `CHECKOUT_DASHBOARD_UI_REDESIGN_REPORT.md`
   - `COMPLIANCE_REVIEW.md`
   - `FASTCHECKOUT_COMPATIBILITY_AUDIT.md`
   - `FASTTRR_AUDIT.md`
   - `FINAL_IMPLEMENTATION_REPORT.md`
   - `GLOBAL_UI_RESPONSIVE_OPTIMIZATION_REPORT.md`
   - `GLOBAL_UI_SPACING_AUDIT.md`
   - `IMPORT_LOG.md`
   - `IMPORT_REVIEW_REQUIRED.md`
   - `MEN_POWER_PRODUCT_UPGRADE_REPORT.md`
   - `PHASE_2_PRODUCTION_AUDIT.md`
   - `REVIEW_AUDIT.md`
   - `SOURCE_CATALOG.md`
   - `SOURCE_DATA_ACCURACY_REPORT.md`
   - `SOURCE_MIGRATION_FINAL_REPORT.md`
   - `SOURCE_PAGES.md`
   - `URL_REDIRECT_MAP.csv`

7. **Automated Testing Suite (`scratch/`):**
   - Tracked test and verification scripts (`test_checkout_cdp.js`, `test_full_qa.py`, `test_shipping.py`, etc.)

---

## 4. Files Excluded from Source Control

| Path / Pattern | Reason for Exclusion |
| :--- | :--- |
| `wp-config*.php` | Contains production database passwords, table prefixes, and auth salts |
| `.env`, `.env.*` | Local environment variables and secrets |
| `*.sql`, `*.dump` | Database dumps containing customer orders, transactions, and user hashes |
| `*.log`, `debug.log` | Server error logs, debug traces, and session diagnostics |
| `scratch/chrome-user-*/` | Local Chrome browser testing profiles and session states |
| `scratch/*.png`, `scratch/*.jpg` | Local test screenshot artifacts generated during visual QA |
| `scratch/node_modules/` | Temporary Node.js test dependencies (restorable via npm) |
| `.DS_Store`, `Thumbs.db` | Operating system metadata files |

---

## 5. Instructions for Setup on Another Computer

1. **Clone the Project:**
   ```bash
   git clone https://github.com/dipakchavan97/bhagyoday.git bhagyoday-ayurveda
   cd bhagyoday-ayurveda
   ```
2. **Setup WordPress & WooCommerce:**
   Install WordPress 6.x and WooCommerce 8.x locally.
3. **Deploy Theme & Plugin:**
   Copy or symlink `themes/ayur-wellness` into `wp-content/themes/` and `plugins/ayur-core` into `wp-content/plugins/`.
4. **Activate in WP Admin:**
   Activate **Ayur Wellness** theme and **Ayur Core** plugin.
5. **Seed Catalog & Legal Pages:**
   Trigger the built-in seeder at:
   `http://localhost:8080/wp-admin/admin.php?page=wc-admin&ayur_seed_data=1`
   This will immediately populate the complete 9-product catalog with accurate Ayurvedic compositions, prices, categories, and legal pages.
6. **Configure Local Credentials:**
   Copy `.env.example` to `.env` and fill in local development credentials under WP Admin -> Settings -> Ayur Commerce.
