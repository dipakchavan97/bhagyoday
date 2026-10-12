# Meta Pixel & Conversions API (CAPI) Live Tracking Verification Report

**Website:** [bhagyodayayurveda.in](https://bhagyodayayurveda.in/)  
**Audit & Verification Timestamp:** October 12, 2026, 12:35:00 UTC (18:05:00 IST)  
**Environment:** Production Live Environment (`https://bhagyodayayurveda.in/`)  
**Git Commits Tested & Deployed:** [`db43497`](https://github.com/dipakchavan97/bhagyoday/commit/db43497), [`b36b1ef`](https://github.com/dipakchavan97/bhagyoday/commit/b36b1ef), and Security Hardening Commit  
**Final Audit Recommendation:** **READY FOR OWNER REVIEW**  
**Agreed Initial Ad Budget Cap:** **₹300/day** (strictly preserved, no ads published)  

---

## 1. Executive Summary & Audit Scope

This report documents the end-to-end troubleshooting, independent verification, and security hardening of the **Meta Pixel** and **Conversions API (CAPI)** tracking suite on **Bhagyoday Ayurveda**.

### Key Investigation Deliverables
1. **Root Cause Analysis:** Diagnosed why server-to-server CAPI returned `HTTP 200 OK` and `events_received: 1` but was not visible in the **Test Events** tab of Meta Events Manager.
2. **Dataset Alignment:** Confirmed the live WordPress configuration targets Dataset `1755****5967` (16 digits).
3. **Independent Browser Automation Verification:** Verified network transmissions to `https://www.facebook.com/tr/` using an automated Microsoft Edge browser session. `PageView`, `ViewContent`, `AddToCart`, and `InitiateCheckout` were intercepted with verified SKUs, values, and currencies.
4. **Purchase Deduplication Symmetry:** Confirmed exact deterministic matching keys (`order_{order_id}`) across browser and server payloads, along with anti-replay guards.
5. **Security Hardening:** Completely removed the public diagnostic probe endpoint from `class-ayur-tracking.php` to prevent exposure of configuration or token hints.
6. **Minimum Action Plan:** Provided clear instructions on where to copy the Meta Test Event Code, where to enter it in WordPress, and how to remove it after testing.

### Strict Safety Guarantees
* **Zero Ad Spend:** No campaigns, ad sets, or ads have been published or activated.
* **Secret Protection:** The 16-digit Meta Pixel ID and CAPI Access Token are strictly masked (`1755****5967` and `EAAaa8dj...ZDZD`).
* **Zero Fake Purchases:** No fake orders, charges, or mock purchase events were fired against live payment gateways or customer data.
* **Production Safeguards Intact:** Native WooCommerce checkout validation, COD ₹100 online confirmation fee requirement, and Razorpay gateway remain untouched.

---

## 2. Root Cause Diagnosis: Why CAPI Succeeded but Was Missing in "Test Events"

### The Issue
A direct server-to-server request to Meta Graph API `v19.0` returned:
* **HTTP Status Code:** `200 OK`
* **Events Received:** `1`
* **Meta Trace ID:** `A5LAuH59Ogly-M2Py6RN8Ol`
Yet the account owner observed that no events appeared in the **Test Events** tab in Meta Events Manager.

### Technical Explanation
Meta Events Manager maintains two distinct ingestion pipelines:

1. **The Production Event Pipeline (Default):**
   * When an event is dispatched to `https://graph.facebook.com/v19.0/{pixel_id}/events` **without** a `test_event_code` parameter, Meta accepts the event into its standard production pipeline.
   * Meta responds immediately with `HTTP 200 OK` and `{"events_received": 1}`.
   * The event is routed directly to backend ad attribution and machine learning models.
   * It **does not appear in the real-time "Test Events" tab**. Instead, it appears in the **Overview / Aggregated Event Measurement** tab after standard batch aggregation processing (typically 20–30+ minutes).

2. **The Real-Time Test Events Debugger:**
   * The **Test Events** tab in Meta Events Manager acts as an interactive real-time filter.
   * To stream incoming server events into the live Test Events console, Meta requires the event payload to include the session's temporary alphanumeric test code:
     ```json
     {
       "data": [ ... ],
       "test_event_code": "TEST#####"
     }
     ```
   * Because `ayur_meta_test_code` in WordPress was left empty (`""`), the server event was processed as a genuine production event rather than routed to the live test debugger.

### Resolution
The WordPress tracking engine already supports `test_event_code`. Once the owner copies the active test code from Meta Events Manager and saves it under **WooCommerce > Tracking & Attribution**, server-side CAPI dispatches will immediately stream live into the Test Events console.

---

## 3. Configuration & Dataset Alignment Audit

| Setting / Asset | Configured State | Masked Identifier | Verification Finding |
| :--- | :--- | :--- | :--- |
| **Meta Pixel / Dataset ID** | Active in Database | `1755****5967` (16 digits) | Matches deployed base code on all live pages. |
| **Meta CAPI Access Token** | Active in Database | `EAAaa8dj...ZDZD` | Valid System User Token accepted by Meta Graph API `v19.0`. |
| **Meta Test Event Code** | Empty in Database | `""` (Optional) | Explains omission from real-time Test Events console. |
| **GA4 Measurement ID** | Disabled | `""` | Zero interference with Meta Pixel. |

### Dataset Matching Guidance in Meta Events Manager
To ensure events are viewed in the correct dashboard:
1. Log in to [Meta Events Manager](https://eventsmanager.facebook.com/).
2. In the top-left dropdown, verify that the **Business Portfolio** containing Bhagyoday Ayurveda is selected (not a personal ad account).
3. In the left navigation bar, click **Data Sources**.
4. Locate the Dataset whose ID ends in **`5967`** (full 16-digit ID starts with `1755...`).
5. All browser Pixel and server CAPI events from the store are mapped to this specific Dataset.

---

## 4. Independent Browser Pixel Network Verification

An automated browser session using Microsoft Edge was executed against the live production store (`https://bhagyodayayurveda.in/`) to intercept every outgoing HTTP network request to `https://www.facebook.com/tr/`.

### Summary of Intercepted Network Requests
* **Total Meta Pixel Network Requests Intercepted:** 7
* **Target Pixel ID:** `1755****5967` (100% match on all requests)

### Detailed Event Payloads Intercepted

| Event Name | Page Visited | Intercepted Custom Data (`cd[...]`) | Deduplication Key (`eid`) | Status |
| :--- | :--- | :--- | :--- | :--- |
| **PageView** | Homepage (`/`) | Base pageview payload | `ob3_plugin-set_ff...` | **PASS** |
| **PageView** | Single Product (`/product/...juice/`) | Base pageview payload | `ob3_plugin-set_28...` | **PASS** |
| **ViewContent** | Single Product (`/product/...juice/`) | `content_ids: ["AYUR-ARJ-500"]`<br>`content_type: product`<br>`value: 890`<br>`currency: INR` | `vc_33_1791787102` | **PASS** |
| **AddToCart** | Product Page (Add to Cart click) | `content_ids: ["AYUR-ARJ-500"]`<br>`content_type: product`<br>`value: 890`<br>`currency: INR` | `atc_33_1791788639135` | **PASS** |
| **PageView** | Checkout Page (`/checkout/`) | Base pageview payload | `ob3_plugin-set_6f...` | **PASS** |
| **InitiateCheckout** | Checkout Page (`/checkout/`) | `content_ids: ["AYUR-ARJ-500"]`<br>`content_type: product`<br>`value: 960`<br>`currency: INR`<br>`num_items: 1` | `ic_1791788681` | **PASS** |

### Key Catalog Consistency Confirmations
1. **Identifier Symmetry:** Every commerce event (`ViewContent`, `AddToCart`, `InitiateCheckout`) passes the WooCommerce SKU `AYUR-ARJ-500` rather than internal post IDs.
2. **Cart Value Accuracy:** `AddToCart` passes the single item price (`₹890`); `InitiateCheckout` passes cart subtotal + shipping (`₹960`); currency is uniformly `INR`.
3. **Deduplication Keys:** Unique deterministic event IDs (`vc_*`, `atc_*`, `ic_*`) are generated and transmitted on each user action.

---

## 5. Purchase Deduplication & Server-Browser Symmetry

Meta's deduplication algorithm automatically reconciles browser and server events when the `event_name` and `event_id` match exactly within a 48-hour window.

### Code-Level Symmetry Audit
* **Browser Pixel (`class-ayur-tracking.php`):**
  ```javascript
  fbq('track', 'Purchase', {
      value: 890,
      currency: 'INR',
      content_ids: ["AYUR-ARJ-500"],
      content_type: 'product'
  }, { eventID: 'order_' + orderId });
  ```
* **Server CAPI Payload (`class-ayur-tracking.php`):**
  ```php
  'event_name' => 'Purchase',
  'event_id'   => 'order_' . $order_id,
  'custom_data' => array(
      'currency'     => 'INR',
      'value'        => $total,
      'content_type' => 'product',
      'contents'     => $contents,
      'order_id'     => (string)$order_id,
  )
  ```

### Replay & Deduplication Safeguards
1. **Receipt Page Reloads:** The browser Pixel inspects `$order->get_meta('_pixel_purchase_fired')`. Upon initial render, it flags `_pixel_purchase_fired = '1'`. Any reload or bookmark revisit completely suppresses the tracking snippet.
2. **Webhook & Hook Retries:** The CAPI dispatcher is hooked to `woocommerce_thankyou`, `woocommerce_order_status_processing`, and `woocommerce_order_status_completed`. The check `$order->get_meta('_capi_purchase_fired')` guarantees that only the first triggered hook contacts Meta Graph API.
3. **Unconfirmed COD & Abandoned Payments:** Unpaid or cancelled checkout sessions never reach `is_order_received_page()` or trigger completion hooks, preventing phantom purchases.

---

## 6. Security Hardening & Diagnostic Endpoint Cleanup

Per the safety requirements, any public diagnostic probe that could expose tracking configuration or credentials was removed:

1. **Removed Code:** The temporary diagnostic probe hooks (`ayur_audit_tracking` and `ayur_configure_tracking`) and their associated helper functions were completely purged from `plugins/ayur-core/includes/class-ayur-tracking.php`.
2. **Live Production Verification:** Dispatched requests to `https://bhagyodayayurveda.in/?ayur_audit_tracking=ayur_audit_test_9921`. Confirmed that the endpoint returns standard HTML without returning JSON, configuration data, or token hints.
3. **Administrative Access:** All tracking settings (`Pixel ID`, `CAPI Token`, `Test Event Code`, `GA4 ID`) are managed strictly through the authenticated WordPress Admin menu under **WooCommerce > Tracking & Attribution** (`wp-admin/admin.php?page=ayur-tracking`), requiring `manage_woocommerce` capabilities.

---

## 7. Minimum Manual Steps for the Account Owner

To observe events in real time in Meta Events Manager, follow these minimum manual steps:

### Step 1: Open Meta Events Manager
1. Navigate to [Meta Events Manager](https://eventsmanager.facebook.com/).
2. Select the Dataset ending in **`5967`** under **Data Sources**.

### Step 2: Obtain the Server Test Code
1. Click on the **Test Events** tab.
2. In the section titled **"Confirm your server events are set up correctly"** (or "Test Server Events"), locate the test code starting with `TEST` (e.g. `TEST49210`).
3. Click to copy this code.

### Step 3: Enter the Code in WordPress
1. Log in to your WordPress Admin dashboard (`https://bhagyodayayurveda.in/wp-admin/`).
2. Go to **WooCommerce** -> **Tracking & Attribution**.
3. In the field **Test Event Code (Optional)**, paste your `TEST#####` code.
4. Click **Save Tracking Configuration**.

### Step 4: Test in Real-Time
* **To Test Browser Events:**
  1. In the **Test Events** tab, go to **"Confirm your website's events are set up correctly"**.
  2. Enter `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/` and click **Open Website**.
  3. Browse the product, click **Add to Cart**, and proceed to checkout.
  4. Switch back to Meta Events Manager: `PageView`, `ViewContent`, `AddToCart`, and `InitiateCheckout` will display live in the feed.
* **To Test Server (CAPI) Events:**
  * Any order processed while the test code is saved will include `test_event_code` and immediately appear under Server events in the same tab.

### Step 5: Post-Testing Cleanup (Crucial)
* Once you have verified the events in Test Events, return to **WooCommerce** -> **Tracking & Attribution**.
* **Delete** the test code from the **Test Event Code** field (leave it blank).
* Click **Save Tracking Configuration**.
* *Why:* This ensures live customer purchases are recorded as genuine production conversions for ad optimization rather than test events.

---

## 8. Verification Matrix

| Check / Requirement | Evidence / Result | Status |
| :--- | :--- | :--- |
| **Pixel Base Code** | Active across all pages (`fbq('init', '1755****5967')`) | **PASS** |
| **PageView** | Intercepted 3 times via Edge browser automation | **PASS** |
| **ViewContent** | Intercepted on product page (`AYUR-ARJ-500`, ₹890, INR) | **PASS** |
| **AddToCart** | Intercepted on button click (`AYUR-ARJ-500`, ₹890, INR) | **PASS** |
| **InitiateCheckout** | Intercepted on checkout page (`AYUR-ARJ-500`, ₹960, INR) | **PASS** |
| **Server CAPI Token** | Meta Graph API `v19.0` returned `HTTP 200`, `events_received: 1` | **PASS** |
| **Purchase Deduplication** | Deterministic `order_{order_id}` symmetry across browser & server | **PASS (Architecture)** |
| **Diagnostic Security** | Public diagnostic probe completely removed and verified | **PASS** |
| **Ad Spend Safeguard** | ₹0 spent; no ads or campaigns published; ₹300/day cap preserved | **PASS** |

---

## 9. Final Recommendation

### **STATUS: READY FOR OWNER REVIEW**

The Meta tracking infrastructure is verified on the live site:
1. Browser Pixel events (`PageView`, `ViewContent`, `AddToCart`, `InitiateCheckout`) fire reliably with matching SKUs, prices, and deduplication IDs.
2. Server Conversions API is verified with Meta Graph API `HTTP 200 OK`.
3. The reason events were absent from the **Test Events** tab has been diagnosed (absence of `test_event_code` in the test payload) and clear guidance provided to view real-time events.
4. All diagnostic probes have been removed from the codebase.
5. The store is ready for owner review and subsequent campaign launch.
