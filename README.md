# Bhagyoday Ayurveda — Production eCommerce Platform

[![WordPress](https://img.shields.io/badge/WordPress-6.x-blue.svg)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-8.x-purple.svg)](https://woocommerce.com)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)]()

Production source code, custom theme (`ayur-wellness`), custom core plugin (`ayur-core`), diagnostics suite, automated regression tests, and architectural audits for **Bhagyoday Ayurveda** (`https://bhagyodayayurveda.in`).

---

## 1. Project Overview

Bhagyoday Ayurveda is a high-converting, mobile-first Ayurvedic direct-to-consumer (D2C) eCommerce store built on WordPress and WooCommerce. The platform features:

- **Custom Theme (`themes/ayur-wellness`)**: Bespoke styling, responsive typography, 3D interactive hero canvas using Three.js, high-converting product pages with sticky Add-to-Cart drawer, custom mini-cart, and redesigned customer My Account portal.
- **Custom Plugin (`plugins/ayur-core`)**: Instant checkout modal, Razorpay ₹100 COD advance confirmation fee flow with remaining balance on delivery, Google Places address autocomplete, tiered quantity discount engine, native verified reviews module with photo uploads, Shiprocket fulfillment hooks, and Meta Conversions API (CAPI) server-side event tracking.
- **Strict Brand Invariants**: Authentic classical botanical compositions verified against physical packaging artwork, price protection (₹890 selling price across core products), and strict Shiprocket parcel dimension/weight preservation.
- **Automated Verification Suite (`scratch/`)**: Chrome DevTools Protocol (CDP) / Puppeteer automated test suite verifying checkout conversions, mobile viewport responsive clearances, sticky bars, and purchase flows.

---

## 2. System Requirements

| Component | Minimum Version | Recommended Version |
| :--- | :--- | :--- |
| **PHP** | 8.0 | 8.1 / 8.2 |
| **WordPress** | 6.2 | 6.5+ |
| **WooCommerce** | 8.0 | 8.8+ |
| **MySQL / MariaDB** | MySQL 5.7 / MariaDB 10.4 | MySQL 8.0+ / MariaDB 10.6+ |
| **Web Server** | Nginx or Apache with `mod_rewrite` | Nginx + PHP-FPM |
| **Node.js** (Optional, for running QA tests) | Node 18+ | Node 20 LTS |

---

## 3. Repository Directory Structure

```text
├── .env.example                                  # Environment & credential configuration template
├── .gitignore                                    # Production gitignore rules
├── README.md                                     # Project setup & development documentation
├── GITHUB_BACKUP_REPORT.md                       # Comprehensive backup audit and verification report
├── URL_REDIRECT_MAP.csv                          # 301 legacy URL redirect mapping
│
├── themes/
│   └── ayur-wellness/                            # Active custom WordPress theme
│       ├── 404.php                               # Custom 404 page
│       ├── footer.php                            # Global footer with WhatsApp & trust badges
│       ├── front-page.php                        # Front-page template router
│       ├── functions.php                         # Theme setup, assets enqueue, WooCommerce hooks
│       ├── header.php                            # Header, announcement ticker, mobile nav drawer
│       ├── index.php                             # Fallback template
│       ├── page.php                              # Standard page template
│       ├── page-checkout.php                     # Clean high-conversion checkout page
│       ├── page-my-account.php                   # Redesigned customer account template
│       ├── style.css                             # Complete design system CSS & responsive styles
│       ├── template-home.php                     # Custom homepage layout
│       ├── assets/
│       │   ├── images/                           # Hero bottle, ingredients, and vector badges
│       │   └── js/
│       │       ├── ayur-hero-3d.js               # Three.js 3D hero animation script
│       │       ├── ayur-theme.js                 # Sticky ATC, drawer, accordion logic
│       │       └── three.min.js                  # Three.js library bundle
│       └── woocommerce/                          # Custom WooCommerce template overrides
│           ├── archive-product.php               # Shop catalog layout
│           ├── content-product.php               # Product card grid item
│           ├── single-product.php                # High-converting single product page
│           └── myaccount/                        # Custom My Account dashboard templates
│
├── plugins/
│   ├── ayur-core/                                # Main custom store functionality plugin
│   │   ├── ayur-core.php                         # Plugin bootstrap & module loader
│   │   ├── assets/                               # Plugin CSS, JS & WebP product imagery
│   │   └── includes/
│   │       ├── class-ayur-address-autofill.php   # Google Places address autocomplete
│   │       ├── class-ayur-cart-checkout.php      # Mini-cart drawer & checkout modifications
│   │       ├── class-ayur-cod-confirmation.php   # Razorpay ₹100 COD advance verification
│   │       ├── class-ayur-commerce-settings.php  # Admin settings UI for all modules
│   │       ├── class-ayur-config.php             # Core store constants & configuration
│   │       ├── class-ayur-fields.php             # Custom product metadata fields
│   │       ├── class-ayur-instant-checkout.php   # Instant Buy Now modal flow
│   │       ├── class-ayur-migrator.php           # Catalog migration & composition data
│   │       ├── class-ayur-profitability.php      # Profitability & margin calculations
│   │       ├── class-ayur-quantity-offers.php    # Buy 2 Get 10% / Buy 4 Get 20% tier logic
│   │       ├── class-ayur-reviews.php            # Photo review submission & rendering engine
│   │       ├── class-ayur-seeder.php             # Demo catalog & legal pages seeder
│   │       ├── class-ayur-shiprocket.php         # Shiprocket automated order sync
│   │       ├── class-ayur-tracking.php           # Meta Pixel & Conversions API (CAPI)
│   │       └── class-ayur-whatsapp.php           # Floating WhatsApp helpline button
│   │
│   └── test-probe/                               # Diagnostic & verification testing plugin
│       └── test-probe.php                        # Diagnostic endpoints for automated audits
│
├── imported_images/                              # Packaging artwork & original photography
├── scripts/                                      # Safe database export/import scripts
│   ├── export-safe-db.sh                         # Linux/macOS DB export (excludes customer PII)
│   ├── export-safe-db.ps1                        # Windows DB export (excludes customer PII)
│   ├── import-safe-db.sh                         # Linux/macOS DB import & URL search-replace
│   └── import-safe-db.ps1                        # Windows DB import & URL search-replace
│
├── scratch/                                      # Automated testing scripts & verification suites
│   ├── test_checkout_cdp.js                      # Headless checkout flow test
│   ├── test_full_qa.py                           # Full store test suite
│   ├── test_shipping.py                          # Shipping calculation test
│   └── ...                                       # Viewport, regression & CDP tests
│
└── Documentation Reports/                        # Comprehensive engineering audit reports
    ├── AUDIT_REPORT.md
    ├── CHECKOUT_DASHBOARD_UI_REDESIGN_REPORT.md
    ├── COMPLIANCE_REVIEW.md
    ├── FASTCHECKOUT_COMPATIBILITY_AUDIT.md
    ├── FASTTRR_AUDIT.md
    ├── FINAL_IMPLEMENTATION_REPORT.md
    ├── FIVE_PRODUCT_COMPOSITION_UPDATE_REPORT.md
    ├── GLOBAL_UI_RESPONSIVE_OPTIMIZATION_REPORT.md
    ├── GLOBAL_UI_SPACING_AUDIT.md
    ├── IMPORT_LOG.md
    ├── IMPORT_REVIEW_REQUIRED.md
    ├── MEN_POWER_PRODUCT_UPGRADE_REPORT.md
    ├── PHASE_2_PRODUCTION_AUDIT.md
    ├── REVIEW_AUDIT.md
    ├── SOURCE_CATALOG.md
    ├── SOURCE_DATA_ACCURACY_REPORT.md
    ├── SOURCE_MIGRATION_FINAL_REPORT.md
    └── SOURCE_PAGES.md
```

---

## 4. Setup on Another Computer (Step-by-Step)

### Step 1: Clone the Repository
```bash
git clone https://github.com/dipakchavan97/bhagyoday.git bhagyoday-ayurveda
cd bhagyoday-ayurveda
```

### Step 2: Set Up Local WordPress Environment
Install WordPress on your local server stack (LocalWP, XAMPP, Laragon, Docker, or Valet):
1. Create a database (e.g. `bhagyoday_local`).
2. Complete the standard WordPress installation.
3. Install and activate the official **WooCommerce** plugin.

### Step 3: Link Theme and Custom Plugin
Link or copy the project folders into your WordPress directory:

**Windows (PowerShell - Symlinks / Directory Junctions):**
```powershell
# In your local WordPress web root (e.g. C:\xampp\htdocs\bhagyoday):
cmd /c mklink /J "wp-content\themes\ayur-wellness" "D:\path\to\bhagyoday-ayurveda\themes\ayur-wellness"
cmd /c mklink /J "wp-content\plugins\ayur-core" "D:\path\to\bhagyoday-ayurveda\plugins\ayur-core"
cmd /c mklink /J "wp-content\plugins\test-probe" "D:\path\to\bhagyoday-ayurveda\plugins\test-probe"
```

**macOS / Linux (Symlinks):**
```bash
ln -s /path/to/bhagyoday-ayurveda/themes/ayur-wellness /var/www/html/wp-content/themes/ayur-wellness
ln -s /path/to/bhagyoday-ayurveda/plugins/ayur-core /var/www/html/wp-content/plugins/ayur-core
ln -s /path/to/bhagyoday-ayurveda/plugins/test-probe /var/www/html/wp-content/plugins/test-probe
```

*Or simply copy the folders directly into `wp-content/themes/` and `wp-content/plugins/`.*

### Step 4: Activate Theme and Plugins
Log in to WordPress Admin (`/wp-admin`):
1. Navigate to **Appearance > Themes** and activate **Ayur Wellness**.
2. Navigate to **Plugins > Installed Plugins** and activate **Ayur Core** and **WooCommerce**.

### Step 5: Configure Credentials (`.env`)
1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
2. Navigate to **Settings > Ayur Commerce** in WP Admin and configure:
   - **Razorpay**: Enter your Razorpay Test Key ID and Key Secret.
   - **Google Places**: Enter your Google Places API key (restricted to browser/Places).
   - **Shiprocket**: Configure Shiprocket credentials for sandbox testing.
   - **Meta CAPI**: Enter Meta Pixel ID and Test Access Token.

---

## 5. Catalog Seeding & Data Migration

Because source control intentionally **excludes live production databases, customer records, and orders**, you have two safe ways to seed the catalog on a new computer:

### Option A: Built-in Catalog Seeder (Recommended for Clean Development)
1. Ensure `Ayur Core` is active.
2. In your browser, while logged into WP Admin as an Administrator, trigger the seeder:
   ```text
   http://localhost:8080/wp-admin/admin.php?page=wc-admin&ayur_seed_data=1
   ```
   This will automatically generate the 9 core products, categories, legal pages, and default coupons with verified packaging compositions and prices.

### Option B: Import Sanitized Database Dump
If transferring a database dump from staging:
```bash
# Using WP-CLI
bash scripts/import-safe-db.sh safe_catalog_export.sql http://localhost:8080
```
Or in Windows PowerShell:
```powershell
.\scripts\import-safe-db.ps1 -SqlFile safe_catalog_export.sql -NewUrl "http://localhost:8080"
```

---

## 6. What Must Be Configured Separately

The following components are not included in the GitHub repository for security and privacy:
1. **Live Production Secrets**: Razorpay live secret keys, Shiprocket passwords, Meta CAPI tokens, and Google API keys.
2. **Customer Orders & PII**: Real customer names, shipping addresses, phone numbers, and payment transaction IDs.
3. **Full WordPress Media Library**: `wp-content/uploads/` directory (the repository includes the essential theme webp assets, icons, and `imported_images/`).

---

## 7. Automated Testing & QA

To run the automated Puppeteer / CDP regression test suite:
```bash
cd scratch
npm install puppeteer
node test_checkout_cdp.js
```

---

## 8. Production Invariants & Safety Guidelines

When developing new features:
1. **Core Price Protection**: The selling price for the core products (Slim Care, Lady Care, Men Power, Diabetic Control, Ortho Care, etc.) is ₹890. Never alter product prices programmatically without explicit client authorization.
2. **Customer-Facing Composition Display**: The composition display on the product page shows botanical names only; milligram dosages are preserved in backend meta fields but excluded from customer frontend badges.
3. **No Force-Pushes**: Never force-push (`git push --force`) to remote branches.
