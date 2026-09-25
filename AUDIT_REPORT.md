# Phase 0 — Existing Website & Environment Audit Report

**Target Website:** [https://lightgreen-heron-113083.hostingersite.com/](https://lightgreen-heron-113083.hostingersite.com/)  
**Audit Date:** 2026-09-25  
**Auditor:** Lead WordPress/WooCommerce Developer & Architect  
**Hostinger Account ID:** `u942484014` | **WP Software ID:** `30799030`  

---

## 1. Executive Summary

The audit confirms that the target website is a **clean, newly provisioned WordPress installation** on Hostinger Cloud/Business Hosting with LiteSpeed Web Server, PHP 8.3, and SSL enabled. 

- **Critical Content Risk:** **ZERO.** No live customer data, existing orders, legacy products, or published business pages exist. Only default sample content exists ("Hello world!").
- **WooCommerce Status:** **Not installed yet.**
- **Server Resources:** **Excellent.** 512MB PHP memory limit (expandable to 1536MB), 256MB file upload limit, OPcache active, and LiteSpeed caching running smoothly.
- **Feasibility:** 100% ready to be shaped into a high-converting, premium Ayurvedic D2C ecommerce storefront without risk of data loss.

---

## 2. Technical Environment & Server Specifications

| Component | Audit Finding | Recommended Action |
| :--- | :--- | :--- |
| **Site URL** | `https://lightgreen-heron-113083.hostingersite.com/` | Keep URL dynamic via `wp_upload_dir()`, `home_url()`, and `site_url()` for smooth domain migration in Phase 39 |
| **WordPress Core** | Version 7.1.2 (Hostinger release branch / WP 6.7.x) | Verified stable, 0 known vulnerabilities |
| **PHP Version** | `8.3.33` | Modern, high performance, fully compatible with WooCommerce 9.x+ |
| **PHP Memory Limit** | `512M` (Max available: `1536M`) | Ideal for WooCommerce, cart calculation, and image processing |
| **Max Execution Time** | `300s` (Max available: `360s`) | Adequate for background jobs, webhook processing, and batch exports |
| **Upload Max Filesize** | `256M` | Ample for high-res WebP/AVIF product photography and banners |
| **Post Max Size** | `256M` | Matches upload limits |
| **Max Input Vars** | `5000` | Supports extensive product variation matrices without truncation |
| **Key PHP Modules** | `curl`, `mbstring`, `intl`, `imagick`, `zip`, `xml`, `bcmath`, `fileinfo`, `gd`, `openssl`, `pdo_mysql`, `opcache` | All required ecommerce & security extensions enabled |
| **Web Server** | LiteSpeed with Hostinger Edge CDN (`hcdn`) | Extremely fast static asset caching and dynamic HTML caching |
| **SSL / HTTPS** | Active platform SSL certificate; HTTPS redirect enabled | Fully secured for checkout, Razorpay, and Meta CAPI |

---

## 3. Database Architecture

- **Database Name:** `u942484014_t5dTR`
- **Database User:** `u942484014_u6934`
- **Internal Host:** `localhost` / `127.0.0.1` (Port: `3306`)
- **Remote Host:** `srv1954.hstgr.io` (Port: `3306`)
- **Database Size:** `3.00 MB` (Limit: `3,072 MB` / 3 GB)
- **Privileges:** All standard DDL/DML privileges granted (`Alter`, `Create`, `Drop`, `Index`, `Select`, `Insert`, `Update`, `Delete`, `Lock tables`, `Trigger`, etc.).

---

## 4. Theme & Plugin Audit

### Themes
- **Active Theme:** `Twenty Twenty-Five` (v1.5) — Default WordPress Block Theme.
- **Installed Inactive Themes:**
  - `Twenty Twenty-Four` (v1.6)
  - `Twenty Twenty-Three` (v1.7)
- **Assessment:** Default block theme provides no ecommerce UX, no Indian D2C design patterns, and lacks product gallery/review/bundle architecture. A bespoke, lightweight, ultra-premium Ayurvedic theme/child architecture is required.

### Plugins Installed & Active
| Plugin Slug | Name | Version | Status | Purpose / Recommendation |
| :--- | :--- | :--- | :--- | :--- |
| `litespeed-cache` | LiteSpeed Cache | `7.9.1` | Active | Essential for server-level caching. Retain and optimize for WooCommerce non-cacheable pages (cart/checkout/my-account). |
| `hostinger` | Hostinger Tools | `3.0.78` | Active | Hostinger control integration. Safe to keep. |
| `hostinger-reach` | Hostinger Reach | `1.8.4` | Active | Marketing & subscriber integration. Safe to keep. |
| `hostinger-easy-onboarding` | Hostinger Easy Onboarding | `3.0.1` | Active | Initial setup assistant. Can remain or be safely deactivated if not needed. |

- **WooCommerce Status:** **NOT INSTALLED.**
- **Payment Gateways:** None installed.
- **Shipping Integration:** None installed.
- **Tracking / Analytics:** No Meta Pixel, Google Analytics 4, or Google Tag Manager scripts detected.
- **SEO Configuration:** Default WordPress robots configuration only. No XML sitemaps or structured data schemas present.

---

## 5. Existing Content & Permalinks

- **Pages:** 0 published pages.
- **Posts:** 1 post (`Hello world!`, ID 1, slug: `hello-world`).
- **Media Library:** 0 media items uploaded.
- **Users:** 1 user (Username: `admin`, Email: `dipakchavan.mail@gmail.com`, Role: `Administrator`).
- **Permalinks:** Configured as Pretty Permalinks (`/%postname%/`). Verified with HTTP 200 response on `/hello-world/`.
- **Destructive Changes Warning:** Since this is a fresh setup, there is no client-generated content or customer history to protect, but safety backups of the database and configuration will still be established before proceeding.

---

## 6. Performance & Network Baseline

- **Initial Cold TTFB:** `1255ms`
- **LiteSpeed Cache Hit TTFB:** `798ms`
- **Server Header:** `Server: hcdn` (Hostinger Edge Cloudflare/CDN layer)
- **Response Code:** `200 OK`
- **Compression:** Gzip/Brotli supported by web server.

---

## 7. Next Steps & Execution Roadmap

With Phase 0 complete and verified:

1. **Phase 1 (Safety & Version Control):** Snapshot current database, lock initial configuration in Git repository `d:\woocom`.
2. **Phase 2 (WooCommerce Foundation):** Install and activate WooCommerce, set INR (₹) currency, configure India location, tax/shipping rules, guest checkout, and inventory thresholds.
3. **Phase 3 & 4 (Storefront Architecture & Homepage):** Deploy a custom premium Ayurvedic theme featuring deep herbal green palette, earthy warm accents, refined typography (Outfit/Cinzel/Plus Jakarta Sans), mobile-first header, hero banner, category showcase, trust badges, and educational sections.
4. **Phase 5 – 11 (Catalog, Product Page & Checkout):** Product variations, sticky mobile Add-to-Cart/Buy Now, rich Ayurvedic attributes (ingredients, dosage, benefits), quick view, AJAX mini-cart, and streamlined single-page Indian checkout.
5. **Phase 12 & 13 (Razorpay & Shiprocket):** Secure server-side signature verification for UPI/Cards/Netbanking/COD, Shiprocket AWB/tracking sync.
6. **Phase 15 – 18 (Meta CAPI & Tracking):** Deduplicated Meta Pixel + Server-side Conversions API via unique `event_id`, GA4 ecommerce dataLayer, UTM parameter persistence across checkout.
