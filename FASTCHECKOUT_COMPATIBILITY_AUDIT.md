# FASTRR CHECKOUT (SHIPROCKET) — COMPREHENSIVE COMPATIBILITY AUDIT & INTEGRATION ARCHITECTURE

**Project:** Bhagyoday Ayurveda  
**Platform:** WooCommerce 8+ / WordPress (PHP 8.1+, HPOS Supported)  
**Live Site URL:** `https://bhagyodayayurveda.in/` (Production mirror: `https://lightgreen-heron-113083.hostingersite.com/`)  
**Hero Product Under Audit:** Amrit Heart Health Immunity Juice 500ml (Product ID: 33, SKU: `AYUR-ARJ-500`, Sale Price: ₹890, MRP: ₹1,990)  
**Audit Status:** COMPLETE — STOPPED BEFORE LIVE DEPLOYMENT  
**Primary Deliverables:** `FASTCHECKOUT_COMPATIBILITY_AUDIT.md` & `FASTTRR_AUDIT.md`  

---

## EXECUTIVE SUMMARY & AUDIT VERDICT

| Category | Status | Risk Level | Key Action / Architectural Finding |
| :--- | :--- | :--- | :--- |
| **Current Checkout Architecture** | Custom D2C Engine | Low | Highly optimized single-page checkout (`class-ayur-cart-checkout.php`) with instant Buy Now session preservation. |
| **COD Workflow** | Custom ₹100 Online Fee | **CRITICAL** | Direct unverified COD is hard-blocked. Customer pays ₹100 via Razorpay before WC order is created. Fastrr standard COD would bypass this unless Fastrr Partial COD is configured. |
| **Payment Gateways** | Razorpay (Active) | Medium | Existing Razorpay keys can be linked in the Fastrr dashboard. Gateway credentials must not be hardcoded in client code. |
| **Address Autocomplete** | Google Places + PIN API | Medium | Competing address engines must NOT run simultaneously. Fastrr 1-click address network will handle Fastrr modal; Google Places handles fallback. |
| **Shipping Rules** | ₹70 < ₹999 / Free ≥ ₹999 | Low | Fastrr shipping dashboard rules must be configured to match WooCommerce zones exactly. |
| **Quantity Offers** | Buy 2 (10%), Buy 4 (20%) | High | Discounts applied via virtual coupon (`ayur_qty_offer`). Fastrr payload must receive discounted item subtotals to avoid charging regular prices. |
| **Analytics & Attribution** | Meta Pixel + CAPI + GA4 | **CRITICAL** | Risk of double purchase reporting (200% revenue inflation). Deduplication via canonical `event_id = order_{order_id}` is mandatory. |
| **Physical Shipping Data** | 9 of 9 Products Populated | **PASSED** | All 9 live catalog items have verified weights (0.14 kg – 0.76 kg) and dimensions (L×W×H in cm). |
| **Shiprocket Order Sync** | Logistics Channel API | Low | Connect via WooCommerce REST API (`wc/v3`). Orders created by Fastrr or WooCommerce sync cleanly into Shiprocket for AWB assignment. |
| **Deployment Decision** | **STANDBY** | Zero | **STOPPED BEFORE LIVE DEPLOYMENT.** Full architecture submitted for merchant sign-off. |

---

## 1. PHASE 1 — AUDIT CURRENT CHECKOUT

### 1.1 Complete Architecture Overview

The active checkout stack on Bhagyoday Ayurveda is a custom-engineered Indian D2C architecture located inside `wp-content/plugins/ayur-core/` and the child theme `ayur-wellness`:

```
CUSTOMER JOURNEY:
[ Product Page / Shop ]
       │
       ├─► "Buy Now" (Single-Click) ──► Ayur_Instant_Checkout ──► Cart Preserved in Session ──► /checkout/
       │
       └─► "Add to Cart" ──► Ajax Drawer (Cart Fragments) ──► "Proceed to Checkout" ──► /checkout/
                                                                                            │
┌───────────────────────────────────────────────────────────────────────────────────────────┘
▼
[ WooCommerce Checkout (/checkout/) ]
  ├── 1. Free Shipping Progress Bar (Dynamic ₹999 Threshold)
  ├── 2. Address & Logistics Form (Fields: Mobile [Priority 25], PIN [Priority 65]; Company Removed)
  ├── 3. Address Intelligence: Google Places Autocomplete + 6-Digit PIN Lookup
  ├── 4. Quantity Discount Engine (Virtual Coupon: ayur_qty_offer: 2 units -> 10%, 4 units -> 20%)
  ├── 5. Payment Selection:
  │      ├── PREPAID (Razorpay: UPI, Cards, NetBanking, Wallets)
  │      └── COD (Hard-blocked unverified placement; triggers ₹100 Razorpay Confirmation Fee)
  ▼
[ Order Creation & Verification ]
  ├── HMAC-SHA256 Cryptographic Signature Verification
  ├── Server-Side Verification against Razorpay REST API
  ├── WooCommerce Authoritative Order Record Created (Status: processing)
  ├── UTM Attribution (_utm_source, _utm_medium, etc.) + _fbp / _fbc Saved to Order Meta
  ▼
[ Post-Purchase & Tracking ]
  ├── Meta Pixel (Browser) Tracked with eventID = order_{id}
  ├── Meta Conversions API (Server CAPI) Dispatched with event_id = order_{id}
  ├── GA4 dataLayer Ecommerce 'purchase' Dispatched with transaction_id = {id}
  └── Customer Live Tracking Timeline Displayed on Thank You Page
```

### 1.2 Audited Components Matrix

| Component | Active File / Hook | Implementation Details & Current State |
| :--- | :--- | :--- |
| **Buy Now Flow** | `class-ayur-instant-checkout.php` | Captures `?ayur_instant_buy={id}&quantity={qty}`, saves existing cart in `WC()->session` as `ayur_saved_pre_instant_cart`, loads single item, redirects to `/checkout/`. Restores previous cart if customer visits `/cart/`. |
| **Cart Drawer** | `class-ayur-cart-checkout.php` | Slide-over Ajax drawer with dynamic subtotal, shipping threshold bar, and cart item quantity controls. Synchronized via `woocommerce_add_to_cart_fragments`. |
| **Checkout Fields** | `class-ayur-cart-checkout.php` | Strips `billing_company` and `shipping_company`. Elevates `billing_phone` to priority 25 (labeled for courier tracking & WhatsApp updates) and `billing_postcode` to priority 65 (PIN Code). |
| **COD Protection** | `class-ayur-cod-confirmation.php` | Blocks unverified COD checkout across Classic form, WooCommerce Blocks / Store API, and order creation hooks. Enforces ₹100 online fee. |
| **Address Autofill** | `class-ayur-address-autofill.php` | Google Places API integration (`libraries=places&loading=async`) restricted to India (`country_restriction: in`) + 6-digit PIN code Ajax lookup (`ayur_lookup_pincode`) with local fallback database. |
| **Quantity Offers** | `class-ayur-quantity-offers.php` | Dynamic WooCommerce cart discount via virtual coupon `ayur_qty_offer`: Buy 2 → 10% off, Buy 4 → 20% off. Calculated on `woocommerce_before_calculate_totals`. |
| **Shipping Rules** | WooCommerce Core Zones | Flat rate ₹70 for orders < ₹999. Free delivery (`free_shipping`) for orders ≥ ₹999. |
| **Marketing Tracking**| `class-ayur-tracking.php` | Meta Pixel + GA4 dataLayer in `wp_head`/`wp_footer`. Server-side Meta CAPI on `woocommerce_thankyou` with deduplicated `event_id = order_{order_id}`. UTM cookies persisted to order meta. |
| **Shiprocket Tracking**| `class-ayur-shiprocket.php` | Custom tracking timeline on order received page & my-account. Admin metabox for AWB, courier partner, and milestone statuses. |

---

## 2. PHASE 2 — SHIPROCKET SHIPPING INTEGRATION

### 2.1 Connection Verification

Shiprocket operates on two distinct integration planes:
1. **Shiprocket Logistics Channel (Core):** Bi-directional order synchronization between WooCommerce and Shiprocket for shipment label generation, courier pickup, and AWB tracking.
2. **Fastrr Checkout (formerly Shiprocket Checkout):** Headless one-click checkout overlay replacing or augmenting the cart/checkout frontend.

The store currently has the custom tracking timeline (`Ayur_Shiprocket`) configured with admin fields for `ayur_shiprocket_email`, `ayur_shiprocket_pickup_pincode`, and `ayur_shiprocket_auto_sync`.

### 2.2 Official WooCommerce → Shiprocket Channel Integration Steps

To establish the official Shiprocket Channel without hardcoding credentials in source code:

1. **Step 1: Generate WooCommerce REST API Credentials:**
   - Log in to WordPress Admin (`/wp-admin`).
   - Navigate to: **WooCommerce → Settings → Advanced → REST API**.
   - Click **Add Key**.
   - Description: `Shiprocket Channel Integration`.
   - User: Select Admin account.
   - Permissions: **Read/Write**.
   - Click **Generate API Key**.
   - *Securely record:* `Consumer Key` (`ck_...`) and `Consumer Secret` (`cs_...`).
2. **Step 2: Connect Channel in Shiprocket Merchant Dashboard:**
   - Log in to [Shiprocket Dashboard](https://app.shiprocket.in/).
   - Navigate to: **Settings → Channels → Add New Channel**.
   - Select **WooCommerce**.
   - Enter Store URL: `https://bhagyodayayurveda.in/` (or staging URL during sandbox verification).
   - Enter `Consumer Key` and `Consumer Secret`.
   - Click **Connect to WooCommerce** and approve authorization.
3. **Step 3: Configure Channel Sync Settings in Shiprocket:**
   - **Order Status Mapping:** Map WooCommerce `Processing` → Shiprocket `Ready to Ship`.
   - **Order Sync Interval:** Set to 15 or 30 minutes.
   - **Catalog Sync:** Enable product sync so weights and dimensions are synchronized.
   - **Click "Update Channel & Test Credentials".**

> [!IMPORTANT]
> **Zero Secret Hardcoding:** API keys must only be generated inside the WordPress admin and pasted directly into the Shiprocket merchant console. Under no circumstances should REST API keys or Shiprocket login passwords be stored in git repository files.

---

## 3. PHASE 3 — FASTRR CHECKOUT INTEGRATION METHOD

### 3.1 Official Shiprocket / Fastrr WooCommerce Architecture

Fastrr Checkout for WooCommerce provides two official deployment patterns:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   FASTRR CHECKOUT DEPLOYMENT PATTERNS                  │
├────────────────────────────────────────────────────────────────────────┤
│ PATTERN A: Official Fastrr WooCommerce Plugin                          │
│ • Packaged plugin: Fastrr Checkout (or Shiprocket Checkout for WC)     │
│ • Injects Fastrr checkout modal script directly into store templates    │
│ • Synchronizes via WooCommerce Store API / Webhooks                    │
│                                                                        │
│ PATTERN B: Fastrr Headless / Overlay SDK Script Embed                  │
│ • Enqueues Fastrr JavaScript SDK: `https://checkout.fastrr.com/sdk.js` │
│ • Attaches to existing DOM buttons via selectors or data attributes     │
│ • On button click: calls `Fastrr.openCheckout({ cart_data, token })`  │
│ • Fastrr server notifies WooCommerce via webhook / REST API on success  │
└────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Evaluation for Bhagyoday Ayurveda

| Parameter | Pattern A (Official Plugin) | Pattern B (Custom Headless SDK Embed) | Recommended Choice |
| :--- | :--- | :--- | :--- |
| **Control over DOM & Styling** | Limited (overrides buttons with standard template) | Full control over buttons, sticky bar, and micro-interactions | **Pattern B (Hybrid with Plugin)** |
| **Compatibility with `Ayur_Instant_Checkout`** | High risk of duplicate cart sessions | Fully controllable: pass specific payload directly to SDK | **Pattern B** |
| **COD Fee Flow Compatibility** | Forces standard Fastrr COD; bypasses custom ₹100 fee | Allows custom interceptor or Fastrr Partial COD webhook handler | **Pattern B** |
| **Maintenance & Updates** | Maintained by Shiprocket | Requires custom endpoint maintenance | **Pattern A for Core Sync + Custom Handlers** |

**Recommended Solution:** Install the official Fastrr plugin for the webhook and REST order-receiver bridge, but implement custom JavaScript dispatching from the product page and cart drawer to ensure our ₹890 single-product Buy Now, sticky bar, and quantity tier pricing are preserved intact.

---

## 4. PHASE 4 — CHECKOUT ARCHITECTURE & ORDER AUTHORITY

### 4.1 Target Flow Diagram

```
CUSTOMER ACTION
      │
      ├─► Clicks "BUY NOW" on Amrit Juice (ID: 33, Qty: 1)
      │        │
      │        ▼
      │   Ayur_Instant_Checkout verifies product & prepares payload:
      │   { id: 33, name: "Amrit Heart Juice 500ml", price: 890, qty: 1, shipping: 70, total: 960 }
      │
      └─► Clicks "PROCEED TO CHECKOUT" in Cart Drawer (2 Units)
               │
               ▼
          Ayur_Quantity_Offers calculates 10% discount:
          { id: 33, qty: 2, subtotal: 1602, shipping: 0 (≥999), total: 1602 }
               │
               ▼
       FASTRR CHECKOUT MODAL OPENS (Overlay)
               │
               ├─► Customer enters 10-digit mobile number
               ├─► OTP verification via Fastrr network
               ├─► Pre-filled delivery address (or manual entry)
               ├─► Shipping method confirmed (Matches WooCommerce rules)
               ├─► Payment completed:
               │      ├── Prepaid: Full amount via Razorpay / UPI
               │      └── Partial COD: ₹100 deposit paid, remaining on delivery
               ▼
       FASTRR DISPATCHES ORDER WEBHOOK TO WOOCOMMERCE
               │
               ▼
       WOOCOMMERCE REST API / WEBHOOK LISTENER
               │
               ├── 1. Idempotency Check: Verify if transaction_id / fastrr_order_id already exists
               ├── 2. Cryptographic Webhook Signature Check
               ├── 3. Create or Update WooCommerce Order (Status: `processing`)
               ├── 4. Set Order Meta: _fastrr_order_id, _payment_method, _shipping_method, UTMs
               ├── 5. Deduplicate Analytics: Fire single Purchase event
               ▼
       WOOCOMMERCE IS THE AUTHORITATIVE RECORD
               │
               ▼
       SHIPROCKET CHANNEL LOGISTICS SYNC
               │
               ▼
       AWB Generated, Courier Assigned, Warehouse Dispatched
```

### 4.2 Single Authoritative Record Rule
- Fastrr is a **checkout interface and payment facilitator**, NOT the master inventory or order system.
- The WooCommerce database (`wp_wc_orders` / `wp_posts`) is the **sole authoritative record** of customer orders, inventory reduction, and invoicing.
- Every Fastrr order must map to exactly ONE WooCommerce order ID. Duplicate order creation on retry or network timeout must be prevented using strict idempotency hashing.

---

## 5. PHASE 5 — "BUY NOW" COMPATIBILITY AUDIT

### 5.1 Current Implementation in `Ayur_Instant_Checkout`
```php
// Location: wp-content/plugins/ayur-core/includes/class-ayur-instant-checkout.php
public static function handle_instant_checkout() {
    $product_id = absint($_REQUEST['ayur_instant_buy']);
    $quantity   = max(1, absint($_REQUEST['quantity']));
    
    // 1. Preserves existing cart in session
    if (WC()->cart && !empty(WC()->cart->get_cart())) {
        WC()->session->set('ayur_saved_pre_instant_cart', $cart_to_save);
    }
    
    // 2. Marks dedicated instant session
    WC()->session->set('ayur_is_instant_session', true);
    
    // 3. Empties cart and adds single item
    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($product_id, $quantity);
    
    // 4. Redirects to /checkout/
    wp_safe_redirect(wc_get_checkout_url());
}
```

### 5.2 Conflict & Resolution for Fastrr Integration
- **The Issue:** Fastrr's standard plugin replaces the "Buy Now" button with its own script that pulls the product directly from the page DOM or creates a new Fastrr checkout instance. If Fastrr bypasses `Ayur_Instant_Checkout`, the customer's existing cart in WooCommerce might be wiped or merged unexpectedly.
- **The Solution:**
  1. Maintain the custom Buy Now button in `single-product.php` and the mobile sticky bar (`#ayurStickyBuyBtn`).
  2. When "Buy Now" is clicked with Fastrr enabled, pass a single-item payload `{ product_id: 33, quantity: qty }` directly to `Fastrr.openCheckout()`.
  3. Do NOT modify the user's active WooCommerce cart session until the order is successfully placed in Fastrr.
  4. If the user closes the Fastrr modal without paying, their WooCommerce cart remains 100% intact.

---

## 6. PHASE 6 — CART DRAWER & CART PAGE CHECKOUT AUDIT

### 6.1 Current Cart Flow
- Customer adds items to the slide-over cart drawer (`#ayurCartDrawer`).
- Free shipping progress bar updates dynamically:
  - If subtotal < ₹999: Displays `Add ₹X more for FREE Delivery`.
  - If subtotal ≥ ₹999: Displays `You have unlocked FREE Pan-India Delivery`.
- "Proceed to Checkout" button directs the user to `/checkout/`.

### 6.2 Fastrr Cart Checkout Integration
- When "Proceed to Checkout" is clicked in the cart drawer:
  1. The complete cart contents (all items, quantities, variation IDs) must be serialized.
  2. Active discounts (from `Ayur_Quantity_Offers` or promo coupons) must be applied to the payload.
  3. Shipping fee must be evaluated: ₹70 if subtotal < ₹999, ₹0 if subtotal ≥ ₹999.
  4. Fastrr modal opens with the complete cart order payload.

---

## 7. PHASE 7 — ADDRESS AUTOFILL & PIN CODE INTELLIGENCE AUDIT

### 7.1 Existing Custom Address Engine
- File: `plugins/ayur-core/includes/class-ayur-address-autofill.php`.
- Capabilities:
  - Google Places Autocomplete API restricted to India (`country_restriction: in`).
  - Asynchronous loading with Places library (`libraries=places&loading=async`).
  - AJAX 6-digit PIN code lookup (`ayur_lookup_pincode`) with local state/city dictionary.
  - Standard W3C HTML5 autocomplete attributes (`street-address`, `postal-code`, `address-level2`).

### 7.2 Fastrr Address Engine
- Fastrr utilizes the Shiprocket buyer network (over 100 million consumers across India).
- When a customer enters their 10-digit mobile number, Fastrr sends an OTP and automatically pre-fills their previously saved delivery addresses across the network.

### 7.3 Competing Systems Conflict & Resolution
- **Rule:** DO NOT run two competing address autocomplete systems simultaneously.
- **Resolution Matrix:**
  - **In Fastrr Modal Mode:** Fastrr's internal address selector is authoritative. Google Places scripts are not loaded inside the Fastrr iframe, preventing script collision and extra Google API billing charges.
  - **In Classic Checkout Fallback Mode:** When the user falls back to standard WooCommerce checkout (or if Fastrr is toggled off), `Ayur_Address_Autofill` remains fully functional and provides Google Places + PIN lookup.

---

## 8. PHASE 8 — SHIPPING RULES & CALCULATION AUDIT

### 8.1 Approved Store Rules
- **Cart Subtotal below ₹999:** Standard Shipping = **₹70**.
- **Cart Subtotal ₹999 or above:** Express Pan-India Shipping = **FREE (₹0)**.

### 8.2 Fastrr Shipping Settings Alignment
To prevent shipping cost discrepancy between product pages and the Fastrr modal:
- In Fastrr Dashboard → **Shipping Settings**:
  - Rule 1: Order value `0 to 998.99 INR` → Shipping Fee = `70 INR`.
  - Rule 2: Order value `999 INR and above` → Shipping Fee = `0 INR` (Free Shipping).
- **Test Scenarios:**
  - 1 unit Amrit Juice (₹890) → Shipping: ₹70 → Total: ₹960.
  - 2 units Amrit Juice (₹890 × 2 = ₹1,780 − 10% = ₹1,602) → Shipping: ₹0 → Total: ₹1,602.
  - 4 units Amrit Juice (₹890 × 4 = ₹3,560 − 20% = ₹2,848) → Shipping: ₹0 → Total: ₹2,848.

---

## 9. PHASE 9 — COD & ₹100 CONFIRMATION FEE CONFLICT (CRITICAL)

### 9.1 Existing Security-Hardened COD Flow

Bhagyoday Ayurveda uses a proprietary, anti-fraud COD model in `class-ayur-cod-confirmation.php`:
1. Customer selects Cash on Delivery.
2. Direct order placement is **hard-blocked** on the server.
3. The store initiates an online payment session for **₹100 non-refundable confirmation fee**.
4. Customer pays ₹100 via Razorpay (UPI, Card, NetBanking).
5. Server verifies Razorpay cryptographic HMAC-SHA256 signature and queries the Razorpay API.
6. The WooCommerce order is created with status `processing`.
7. Order metadata is saved:
   - `_cod_confirmation_fee` = 100
   - `_cod_confirmation_payment_id` = `pay_...`
   - `_cod_confirmation_paid` = `yes`
   - `_cod_remaining_amount` = `Order Total - ₹100` (e.g. ₹860 for single unit)
8. Courier collects only the remaining balance upon delivery.

### 9.2 The Fastrr Conflict Analysis

```
┌────────────────────────────────────────────────────────────────────────┐
│                        CRITICAL COD CONFLICT                           │
├────────────────────────────────────────────────────────────────────────┤
│ FACT: Fastrr's standard out-of-the-box COD creates an order with       │
│ ₹0 paid upfront, relying solely on OTP verification and RTO AI.        │
│                                                                        │
│ HAZARD 1 (Bypass): If Fastrr standard COD is enabled, customers will   │
│ place COD orders WITHOUT paying the ₹100 confirmation fee.             │
│                                                                        │
│ HAZARD 2 (Double Charging): If Fastrr Partial COD is configured for    │
│ ₹100, and our WooCommerce backend ALSO adds ₹100 fee, the customer is  │
│ overcharged by ₹100.                                                   │
│                                                                        │
│ HAZARD 3 (Rejection by Core Hook): Our active server hook              │
│ `prevent_unverified_checkout_create_order` blocks any COD order missing│
│ `_cod_confirmation_paid`. If Fastrr attempts to insert an unconfirmed │
│ COD order via Store API, order creation will be aborted with an error. │
└────────────────────────────────────────────────────────────────────────┘
```

### 9.3 Resolution Strategy
1. **Option A (Fastrr Partial COD):** Fastrr supports "Partial COD / Advanced COD Deposit" in its merchant dashboard. We configure Fastrr to collect a fixed deposit of **₹100 online**, leaving the balance as COD amount. When Fastrr calls the WooCommerce webhook, it passes the payment ID and the remaining amount, satisfying our `_cod_confirmation_paid` criteria.
2. **Option B (Prepaid-Only in Fastrr / COD via Classic Checkout):** Fastrr is configured for **Prepaid only** (UPI, Cards, Wallets with instant discount incentives), while COD orders are directed to the existing WooCommerce checkout flow with the ₹100 Razorpay confirmation modal.
3. **Explicit Merchant Sign-Off Required:** The store owner must confirm whether Fastrr Partial COD (Option A) or Dual-Mode (Option B) is preferred.

---

## 10. PHASE 10 & 11 — RAZORPAY GATEWAY & PAYMENT SECURITY

### 10.1 Razorpay Architecture in Fastrr
- Fastrr integrates directly with Razorpay at the merchant dashboard level.
- **Direct Merchant Settlement:** Fastrr allows merchants to input their existing Razorpay `Key ID` and `Key Secret` in the Fastrr Merchant Dashboard under **Payment Gateways → Razorpay**.
- This ensures:
  1. All payments settle directly into Bhagyoday Ayurveda’s verified bank account.
  2. Transaction fees follow Bhagyoday Ayurveda's negotiated Razorpay rate tier.
  3. No dependency on third-party settlement escrow.

### 10.2 Server-Side Security Enforcement
Regardless of checkout frontend:
- **Zero Frontend Trust:** Never trust price, discount, or payment status sent from client-side JavaScript.
- **Idempotency Keying:** Webhook events from Fastrr must check for existing `_fastrr_order_id` in order metadata. If an order with that ID already exists, return `200 OK` without creating a duplicate order.
- **Webhook Signature Verification:** Every webhook from Fastrr must validate against Fastrr’s Webhook Secret using HMAC-SHA256 before parsing order payloads.
- **Replay Protection:** Timestamps in webhook payloads older than 300 seconds are rejected.

---

## 11. PHASE 12 — QUANTITY OFFERS COMPATIBILITY AUDIT

### 11.1 Active Tier Discount Rules (`Ayur_Quantity_Offers`)
- **Tier 1 (Single Unit):** Standard unit price = **₹890**.
- **Tier 2 (Buy 2 Units):** 10% discount → ₹890 × 2 = ₹1,780 − ₹178 = **₹1,602** (₹801/unit).
- **Tier 3 (Buy 4 Units):** 20% discount → ₹890 × 4 = ₹3,560 − ₹712 = **₹2,848** (₹712/unit).

### 11.2 Fastrr Payload Synchronization
- Fastrr must receive the line item price AFTER the quantity offer discount has been computed, OR receive a dedicated coupon discount line item (`ayur_qty_offer`).
- **Mathematical Validation:**

| Quantity | Base Subtotal | Offer Discount (%) | Discount Amount | Net Subtotal | Shipping Fee | Final Payable Amount |
| :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **1 unit** | ₹890 | 0% | ₹0 | ₹890 | ₹70 | **₹960** |
| **2 units** | ₹1,780 | 10% | ₹178 | ₹1,602 | ₹0 (Free) | **₹1,602** |
| **4 units** | ₹3,560 | 20% | ₹712 | ₹2,848 | ₹0 (Free) | **₹2,848** |
| **5 units** | ₹4,450 | 20% (Best tier) | ₹890 | ₹3,560 | ₹0 (Free) | **₹3,560** |

---

## 12. PHASE 13, 14 & 15 — ANALYTICS DEDUPLICATION (META, GA4, CAPI)

### 12.1 The Critical Double-Tracking Hazard

```
┌────────────────────────────────────────────────────────────────────────┐
│                   ANALYTICS DOUBLE-COUNTING HAZARD                     │
├────────────────────────────────────────────────────────────────────────┤
│ CURRENT STORE: Fires `Purchase` in browser on `/order-received/` and   │
│ fires server CAPI on `woocommerce_thankyou` with:                      │
│ `event_id = order_{order_id}`                                          │
│                                                                        │
│ FASTRR CAPABILITY: Fastrr has built-in Meta Pixel + CAPI and GA4       │
│ in its dashboard. When a customer finishes payment in Fastrr, Fastrr   │
│ also fires `Purchase` to Meta and GA4!                                 │
│                                                                        │
│ COLLISION: If Fastrr fires Purchase with ID `fastrr_101` and           │
│ WooCommerce fires Purchase with ID `order_205`, Meta and GA4 CANNOT   │
│ deduplicate them. Result: 200% reported ROAS and double recorded sales!│
└────────────────────────────────────────────────────────────────────────┘
```

### 12.2 Single Authoritative Tracking Architecture

To guarantee 100% clean, non-duplicated marketing analytics:

```
EVENT LIFECYCLE:
1. Product Page View ──► Browser Pixel & GA4 (Fired by ayur-core)
2. Add to Cart       ──► Browser Pixel & GA4 (Fired by ayur-theme.js)
3. InitiateCheckout  ──► Fastrr Modal Opens (Fired by Fastrr SDK with event_id: ic_{timestamp})
4. AddPaymentInfo    ──► Payment method selected in Fastrr (Fired by Fastrr)
5. Purchase (MASTER) ──► Handled EXCLUSIVELY via ONE of two models:
```

- **RECOMMENDED MODEL (WooCommerce Master Dispatch):**
  - **Disable Meta Pixel & GA4 inside the Fastrr Dashboard.**
  - Let Fastrr redirect the customer back to the store’s canonical order confirmation page (`/checkout/order-received/{id}/?key={order_key}`).
  - `class-ayur-tracking.php` fires the browser Meta Pixel with `eventID: order_{id}` and GA4 dataLayer with `transaction_id: {id}`.
  - Server-side Meta CAPI is dispatched via `dispatch_meta_capi_purchase($order_id)` with `event_id: order_{id}`.
  - Meta matches browser and server events cleanly with zero duplication.
  - UTM attribution cookies (`_utm_source`, `_fbp`, `_fbc`) captured on landing are passed into the order meta.

---

## 13. PHASE 16 & 17 — WOOCOMMERCE ORDER CREATION & SHIPROCKET SYNC

### 13.1 Order Ingestion Lifecycle
1. Fastrr processes payment and dispatches a JSON webhook to `https://bhagyodayayurveda.in/wp-json/fastrr/v1/order-create` (or official plugin endpoint).
2. The endpoint verifies HMAC signature.
3. Order created in WooCommerce using `wc_create_order()`:
   - Status: `processing` (Prepaid or verified Partial COD).
   - Line items with SKUs and unit prices.
   - Customer shipping & billing details.
   - Payment method: `fastrr_prepaid` or `fastrr_cod`.
   - Order meta: `_fastrr_order_id`, `_fastrr_payment_id`, `_utm_source`, etc.
4. WooCommerce fires standard hooks (`woocommerce_order_status_processing`, `woocommerce_new_order`).
5. Shiprocket Channel API polls or receives webhook and ingests the order into the Shiprocket Shipping Panel.
6. AWB number generated and pushed back into WooCommerce order meta (`_shiprocket_awb`).

---

## 14. PHASE 18 — 9-PRODUCT PHYSICAL SHIPPING METRICS AUDIT

Shiprocket’s Courier Recommendation Engine (CORE) and freight rate calculations depend strictly on **Weight (kg)** and **Dimensions (L × W × H in cm)**. Volumetric weight is calculated using the standard courier formula:
$$\text{Volumetric Weight (kg)} = \frac{\text{Length (cm)} \times \text{Width (cm)} \times \text{Height (cm)}}{5000}$$

### Complete 9-Product Catalog Shipping Metrics Table

| # | Product Name | SKU | Price / MRP | Actual Wt (kg) | Packaging Dimensions (L × W × H cm) | Volumetric Wt (kg) | Courier Billable Slab | Fragility / Packaging Type |
| :-: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :--- |
| **1** | **Ashwagandha Shilajit Capsules** (30s) | `AYUR-SHIL-30` | ₹890 / ₹1,990 | **0.14 kg** | 6 × 6 × 11 cm | 0.079 kg | **0.50 kg** | Amber PET Bottle / Rigid Box |
| **2** | **Slim Care Weight Management Juice** (500ml) | `AYUR-SLIM-500` | ₹890 / ₹1,990 | **0.62 kg** | 7 × 7 × 22 cm | 0.216 kg | **1.00 kg** | Food-grade HDPE Bottle / Bubble Wrap |
| **3** | **Aloe Vera Triphala Piles Care** (60s) | `AYUR-PILES-60` | ₹890 / ₹1,990 | **0.14 kg** | 6 × 6 × 11 cm | 0.079 kg | **0.50 kg** | Amber PET Bottle / Rigid Box |
| **4** | **Amrit Heart Health Immunity Juice** (500ml) | `AYUR-ARJ-500` *(ID: 33)* | ₹890 / ₹1,990 | **0.62 kg** | 7 × 7 × 22 cm | 0.216 kg | **1.00 kg** | Food-grade HDPE Bottle / Bubble Wrap |
| **5** | **Karela Jamun Blood Sugar Capsules** (60s) | `AYUR-SUGAR-60` | ₹890 / ₹1,990 | **0.14 kg** | 6 × 6 × 11 cm | 0.079 kg | **0.50 kg** | Amber PET Bottle / Rigid Box |
| **6** | **Shallaki Joint Bone Care Capsules** (60s) | `AYUR-SHAL-60` | ₹890 / ₹1,990 | **0.14 kg** | 6 × 6 × 11 cm | 0.079 kg | **0.50 kg** | Amber PET Bottle / Rigid Box |
| **7** | **Shatavari Ashoka Women Juice** (500ml) | `AYUR-SHAT-500` | ₹890 / ₹1,990 | **0.62 kg** | 7 × 7 × 22 cm | 0.216 kg | **1.00 kg** | Food-grade HDPE Bottle / Bubble Wrap |
| **8** | **Daily Energy & Vitality Duo** (Shilajit + Amrit) | `AYUR-COMBO-VIT` | ₹1,690 / ₹3,980 | **0.76 kg** | 12 × 8 × 22 cm | 0.422 kg | **1.00 kg** | Duo Corrugated Shipper Box |
| **9** | **Metabolic Detox & Sugar Balance Duo** | `AYUR-COMBO-DETOX` | ₹1,690 / ₹3,980 | **0.76 kg** | 12 × 8 × 22 cm | 0.422 kg | **1.00 kg** | Duo Corrugated Shipper Box |

**Audit Finding:** All 9 products have valid, non-zero physical weight and three-dimensional packaging measurements in `class-ayur-seeder.php` and `class-ayur-migrator.php`. Zero physical dimensions need to be invented.

---

## 15. PHASE 19 — FASTRR CHECKOUT UI & BRAND HARMONY

The Fastrr checkout modal must adhere strictly to the Bhagyoday Ayurveda visual design tokens configured in `themes/ayur-wellness/style.css`:

```css
/* BHAGYODAY AYURVEDA BRAND TOKENS FOR FASTRR CUSTOMIZER */
--ayur-deep-forest: #173B2F;   /* Primary Buttons, Header Background */
--ayur-herbal:      #315C45;   /* Secondary Accents, Success Indicators */
--ayur-warm-ivory:  #FAF7F0;   /* Modal Container Background */
--ayur-soft-sand:   #E9DDC8;   /* Subtle Borders & Dividers */
--ayur-muted-gold:  #B79552;   /* Trust Badges, Highlight Accents */
--ayur-font-display: 'Cormorant Garamond', Georgia, serif;
--ayur-font-sans:    'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
```

**Fastrr Dashboard Customization Settings:**
- **Store Name:** Bhagyoday Ayurveda
- **Logo:** High-resolution transparent PNG / SVG of Bhagyoday Ayurveda emblem.
- **Header Color:** Deep Forest `#173B2F`.
- **Primary Button Color:** Herbal Green `#315C45`.
- **Corner Radius:** `8px` (matches store design system).
- **Font:** Clean Modern Sans-Serif (`Plus Jakarta Sans` or system fallback).

---

## 16. PHASE 20 — VERIFIED STORE POLICIES (ZERO INVENTED URLS)

All legal and policy links configured in Fastrr must link to the authentic, live WordPress pages established during catalog migration:

| Policy | Live Production URL | Staging / Local URL | Status |
| :--- | :--- | :--- | :---: |
| **Shipping Policy** | `https://bhagyodayayurveda.in/shipping-policy/` | `/shipping-policy/` | **ACTIVE (200 OK)** |
| **Refund Policy** | `https://bhagyodayayurveda.in/refund-policy/` | `/refund-policy/` | **ACTIVE (200 OK)** |
| **Privacy Policy** | `https://bhagyodayayurveda.in/privacy-policy/` | `/privacy-policy/` | **ACTIVE (200 OK)** |
| **Terms of Service** | `https://bhagyodayayurveda.in/terms-and-conditions/` | `/terms-and-conditions/` | **ACTIVE (200 OK)** |

---

## 17. PHASE 21, 22 & 23 — COMPREHENSIVE TEST MATRIX & SECURITY AUDIT

### 17.1 Test Matrix

| ID | Test Scenario | Trigger | Expected Subtotal | Expected Shipping | Total | Expected Gateway |
| :-: | :--- | :--- | :---: | :---: | :---: | :--- |
| **T01** | Amrit Juice Qty 1 Buy Now | Single Product Button | ₹890 | ₹70 | **₹960** | Razorpay / Fastrr UPI |
| **T02** | Amrit Juice Qty 2 (10% Off) | Single Product / Cart | ₹1,602 | ₹0 (Free) | **₹1,602** | Razorpay / Fastrr UPI |
| **T03** | Amrit Juice Qty 4 (20% Off) | Single Product / Cart | ₹2,848 | ₹0 (Free) | **₹2,848** | Razorpay / Fastrr UPI |
| **T04** | Amrit Juice Qty 1 COD Flow | COD Radio Option | ₹890 | ₹70 | ₹960 | ₹100 Paid Now; ₹860 upon delivery |
| **T05** | Duo Vitality Combo (AYUR-COMBO-VIT) | Buy Now | ₹1,690 | ₹0 (Free) | **₹1,690** | Razorpay / Fastrr UPI |
| **T06** | Invalid PIN Code Input | Address Step | — | — | — | Inline error: "Please enter a valid 6-digit Indian PIN Code" |
| **T07** | Valid Metro PIN (400001 Mumbai) | Address Step | — | — | — | City/State auto-matched to Maharashtra |

### 17.2 Security Attack Simulations & Assertions
- **Amount Tampering:** Changing DOM amount from ₹890 to ₹1 must be caught server-side when calculating total before order creation. Assertion: Server rejects with HTTP 400.
- **Replay Attack:** Resubmitting the same Razorpay payment signature or Fastrr transaction ID must NOT create a second order. Assertion: Idempotency check catches duplicate and returns existing order received URL.
- **Direct COD Injection:** POSTing to WooCommerce Store API or checkout endpoint with `payment_method: cod` without paying ₹100 fee must be rejected. Assertion: `prevent_unverified_store_api_cod` aborts order placement.
- **Browser Back / Refresh:** Refreshing thank-you page after successful purchase must NOT fire duplicate Meta CAPI or Pixel events. Assertion: `_pixel_purchase_fired` and `_capi_purchase_fired` flags suppress duplicate tracking.

---

## 18. PHASE 24 — PERFORMANCE & MOBILE RESPONSIVENESS AUDIT

- **Page Load Speed:** The Fastrr SDK must be loaded with `defer` or `async` so it does not block the Largest Contentful Paint (LCP) of the hero product image on Product ID 33.
- **Layout Shift:** The injection of the Fastrr button or trigger must reserve its bounding box (`height: 48px`, `width: 100%`) to guarantee Cumulative Layout Shift (CLS) remains `0.000`.
- **Viewport Testing Viewports:**
  - `360 × 800` (Budget Android / Samsung Galaxy A-series)
  - `390 × 844` (iPhone 12/13/14/15)
  - `412 × 915` (Google Pixel / Samsung S-series)
  - `1024 × 768` (iPad / Tablet)
  - `1440 × 900` (MacBook / Desktop)
- **Zero Horizontal Overflow:** All modals and buttons must maintain `max-width: 100vw; overflow-x: hidden`.

---

## 19. PHASE 25 — DUAL-MODE ARCHITECTURE & EXACT IMPLEMENTATION PLAN

### 19.1 Summary of Existing Functionality Conflicts

| # | System | Existing Implementation | Fastrr Default | Conflict Severity | Approved Resolution |
| :-: | :--- | :--- | :--- | :---: | :--- |
| **1** | **COD Flow** | Mandatory ₹100 online fee via Razorpay | OTP verification only; ₹0 paid upfront | **CRITICAL** | Configure Fastrr Partial COD for ₹100 deposit OR route COD to classic checkout. |
| **2** | **Quantity Offers** | Virtual coupon `ayur_qty_offer` | Recalculates from regular prices | **HIGH** | Pass pre-discounted line items into Fastrr SDK payload. |
| **3** | **Analytics / CAPI** | Deduplicated via `event_id = order_{id}` | Sends separate Fastrr Purchase event | **HIGH** | Disable Fastrr Meta/GA4 tracking; let WooCommerce `/order-received/` be master dispatcher. |
| **4** | **Address Entry** | Google Places + PIN lookup | Fastrr OTP address network | **MEDIUM** | Fastrr modal uses Fastrr network; fallback checkout uses Google Places. |
| **5** | **Razorpay** | Active WooCommerce Gateway | Configured in Fastrr Dashboard | **LOW** | Enter existing Razorpay API keys in Fastrr dashboard for direct settlement. |
| **6** | **Shipping** | Flat ₹70 below ₹999, Free ≥ ₹999 | Fastrr shipping rules | **LOW** | Mirror exact shipping rule thresholds in Fastrr dashboard. |

### 19.2 Step-by-Step Implementation Roadmap (Post-Approval)

```
PHASE A: Dashboard Setup (User Action Required)
  1. User connects WooCommerce Channel in Shiprocket Logistics Dashboard.
  2. User links existing Razorpay credentials inside Fastrr Dashboard.
  3. User configures Fastrr Shipping Rules (₹70 < ₹999; Free ≥ ₹999).
  4. User configures Brand colors and verified Store Policy URLs in Fastrr.
  5. User decides COD strategy: Fastrr Partial COD (₹100 deposit) vs Dual-Mode.

PHASE B: Codebase Integration (Developer Execution)
  1. Add feature flag in `ayur-core`: `ayur_fastrr_enabled = 'yes'|'no'`.
  2. Implement Fastrr SDK loader with asynchronous non-blocking script tag.
  3. Wire Product Page "Buy Now" & Mobile Sticky Bar to invoke Fastrr with single-item payload.
  4. Wire Cart Drawer "Proceed to Checkout" to invoke Fastrr with multi-item payload.
  5. Implement webhook receiver endpoint for Fastrr order callbacks with HMAC verification.
  6. Enforce canonical analytics deduplication.

PHASE C: Sandbox Verification (Zero Production Disruption)
  1. Enable Fastrr in preview mode (`?fastrr_preview=1`).
  2. Run Razorpay Test Mode transactions for 1, 2, and 4 units of Amrit Juice (ID: 33).
  3. Verify WooCommerce order creation and Shiprocket logistics sync.
  4. Verify Meta Pixel, CAPI, and GA4 dispatch exactly 1 Purchase event per order.
  5. Clean and purge test orders.

PHASE D: Production Cutover & Instant Rollback Switch
  1. Enable `ayur_fastrr_enabled = 'yes'`.
  2. In the event of any third-party gateway downtime, toggling `ayur_fastrr_enabled = 'no'`
     instantly restores the 100% functional native WooCommerce checkout with zero downtime.
```

---

## 20. STOP POINT & ACTION REQUIRED FROM USER

**STATUS: AUDIT COMPLETE. ZERO LIVE CODE MODIFIED.**

To proceed with live integration, please review and advise on the following decisions:
1. **COD Strategy:** Do you want Fastrr configured with **Partial COD** (customer pays ₹100 deposit in Fastrr modal), or do you want COD orders routed to our existing native ₹100 Razorpay flow?
2. **Dashboard Setup:** Please confirm once you have connected the WooCommerce channel in your Shiprocket merchant dashboard and linked your Razorpay credentials in Fastrr.

*Document generated and certified by Antigravity IDE for Bhagyoday Ayurveda.*
