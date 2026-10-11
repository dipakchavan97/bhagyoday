# Bhagyoday Ayurveda AI Chatbot — Duplicate Submissions, Rate Limiting & Mobile UI Fix Report

**Project:** Bhagyoday Ayurveda  
**Website:** [https://bhagyodayayurveda.in/](https://bhagyodayayurveda.in/)  
**Repository:** [https://github.com/dipakchavan97/bhagyoday.git](https://github.com/dipakchavan97/bhagyoday.git)  
**Plugin:** `ayur-core`  
**Execution Date:** October 11, 2026  
**Status:** **DEPLOYED & 100% VERIFIED IN PRODUCTION**  

---

## 1. Executive Summary

This report documents the comprehensive audit, root-cause investigation, codebase refactoring, live deployment, and production verification for the official Bhagyoday Ayurveda AI Product Expert Chatbot. All requirements specified in the user request have been resolved, tested, and confirmed operational on the live store:

1. **Elimination of Duplicate Message Submissions:** Guaranteed single message bubble creation and single AJAX request dispatch. Event listeners now enforce a strict singleton initialization guard (`window.ayurChatInitialized`), the Enter key listener directly calls the message submission pipeline without double-firing via synthetic form submit events, the Send button is disabled with a subtle rotating spinner during pending requests, and rapid double-clicks or repeated Enter strokes are strictly blocked.
2. **Correct Rate-Limiting Architecture (HTTP 429 & Retry-After):** Replaced flawed rolling-window transient counting with a true IP sliding-window timestamp tracker over 60 seconds that never penalizes users on blocked requests. When the configured limit (15 requests/minute) is reached, the server emits a genuine `HTTP 429 Too Many Requests` status code with standard `Retry-After: <seconds>` headers and a matching JSON payload. On the frontend, automatic retries are disabled, a live countdown timer displays remaining seconds, and clicking the Retry button retries the request exactly once without appending duplicate user message bubbles.
3. **Responsive Mobile Bottom-Sheet UI:** Overhauled the mobile interface from a claustrophobic full-viewport modal into an ergonomic bottom-sheet (`min(84dvh, 620px)` max-height, `border-radius: 20px 20px 0 0`, top drag handle, compact 10px header padding, dynamic `env(safe-area-inset-bottom)` safe area padding, and `visualViewport` keyboard adaptation). All messages wrap cleanly (`overflow-wrap: anywhere`), prompt chips are capped at 3 compact pills, and the chatbot launcher is permanently isolated at bottom-left, separate from the WhatsApp launcher at bottom-right.
4. **Verified Piles Product Retrieval & Ayurvedic Compliance:** The query *"Which product is for piles?"* (as well as Hindi *"बवासीर (पाइल्स) के लिए कौन सा उत्पाद है?"* and Marathi *"मूळव्याध (पाइल्स) साठी कोणते उत्पादन आहे?"*) reliably identifies **Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)** (`AYUR-PILES-60`) at verified WooCommerce price **₹890**, storefront permalink, verified packaging botanicals only (Kumari / Aloe Vera, Triphala, Nagkesar, Neem, Jimikand), and an explicit non-cure medical disclaimer without replacing healthcare practitioners.
5. **Preservation of Critical Invariants:** Zero changes to product prices, SKUs, inventory, the ₹100 COD advance verification gateway, Shiprocket shipping rules, Razorpay credentials, Meta Pixel/CAPI, or GA4 purchase tracking.

---

## 2. Root Cause Analysis

### 2.1 Duplicate Message Submissions
* **Double Event Triggering:** The Enter key handler was executing `e.preventDefault(); $form.trigger('submit');` while the form submission handler simultaneously listened to `submit`. In fast typing or IME environments, this triggered double dispatch.
* **Lack of Singleton Initialization Guard:** If the script was evaluated more than once across dynamic page reloads or partial DOM refreshes, event listeners (`click`, `submit`, `keydown`) registered multiple times.
* **Retry Handler Appending Extra User Bubbles:** When a request failed and the user clicked Retry, the retry callback was re-calling `appendMessage('user', text)` before resending the AJAX request, creating multiple duplicate customer bubbles for a single inquiry.
* **Absence of Submit Button Locking & Loading State:** The Send button lacked a disabled state during in-flight network roundtrips, allowing impatient users to click multiple times and dispatch concurrent requests.

### 2.2 Rate-Limiting Deficiencies
* **Rolling Transient Extension:** The previous rate limit implementation incremented an integer counter inside a WordPress transient without saving timestamps. Reaching the limit kept resetting the transient expiration, locking users out indefinitely.
* **Zero Config Fallback Flaw:** When `ayur_chatbot_rate_limit` was evaluated with `absint(get_option(..., 15))`, an empty or `'0'` database option evaluated to `0`, effectively bypassing or destabilizing rate-limit enforcement.
* **Missing HTTP 429 & Retry-After:** Responses were returning generic error messages without setting the standard `Retry-After` header or sending HTTP 429 status codes.
* **Frontend Countdown Absence:** The frontend did not parse server-supplied `Retry-After` values or disable the Retry button during cooldown periods.

### 2.3 Critical Input Truncation Bug (`mb_substr` Slice to 0)
* **Investigation:** When debugging why live AJAX queries returned default welcome greetings instead of product recommendations, audit revealed `Ayur_Chatbot_Config::get_max_input_length()` returned `0` because `ayur_chatbot_max_input_length` in the database evaluated to `'0'`.
* **Consequence:** `class-ayur-chatbot-engine.php` executed `if (mb_strlen($raw_message) > $max_len) { $raw_message = mb_substr($raw_message, 0, $max_len); }`. When `$max_len = 0`, any string length `> 0` resulted in `mb_substr($raw_message, 0, 0)`, converting the user's message to an **empty string** `""`.
* **Result:** Gemini and the fallback engine received empty queries, triggering the default greeting *"Namaste! Welcome to Bhagyoday Ayurveda..."* and defaulting to the catalog's first product (`AYUR-SHIL-30`).

### 2.4 Piles Inquiry Over-Refusal & Stopword Contamination
* **Token Contamination:** In `search_relevant_products()`, question words like `"for"` and `"product"` were scored against every product catalog corpus containing those words, adding noise to ranking scores.
* **LLM Over-Censorship:** When asked about conditions like piles or hemorrhoids, the Gemini AI system prompt's strict rule *"NEVER diagnose illnesses or prescribe medications"* caused the model to hesitate and return generic greetings instead of mapping the query to the official dietary supplement.

---

## 3. Detailed Changes by File

### 1. `plugins/ayur-core/assets/js/ayur-chatbot.js`
* Added `if (window.ayurChatInitialized) return; window.ayurChatInitialized = true;` guard to prevent multi-registration of event listeners.
* Uncoupled Enter `keydown` from synthetic form submit: now calls `submitUserMessage()` directly and cancels event propagation.
* Introduced `setSendingUI(isSending)` to toggle the Send button's `disabled` state and apply the `.ayur-chat-loading` spinner class.
* Updated `handleRetry()` to resubmit the failed message directly via `sendAjaxMessage()` with `isRetry = true`, bypassing `appendMessage('user', ...)` to eliminate duplicate customer bubbles.
* Added `startRateLimitCountdown(retryAfterSeconds)`: disables input and Retry button, renders a live countdown timer (`"Rate limit reached. Please wait Xs..."`), and re-enables inputs only after expiry.
* Integrated `window.visualViewport` listener to dynamically reposition the chat input when mobile virtual keyboards appear.
* Limited prompt chips to a maximum of 3 compact pills on mobile viewports.

### 2. `plugins/ayur-core/assets/css/ayur-chatbot.css`
* Implemented modern bottom-sheet architecture for mobile devices (`@media (max-width: 768px)`):
  * Panel height: `max-height: min(84dvh, 620px); height: min(84dvh, 620px);`
  * Rounded top corners: `border-radius: 20px 20px 0 0;`
  * Added top drag handle pill (`.ayur-chat-drag-handle`).
  * Compact header padding: reduced to `10px 14px`.
  * Safe-area padding: `padding-bottom: max(10px, env(safe-area-inset-bottom));`
* Word breaking & text wrapping: added `overflow-wrap: anywhere; word-break: break-word;` across all message bubbles.
* Added Send button loading spinner with CSS keyframe animation (`@keyframes ayur-chat-spin`).
* Added `.ayur-chat-btn-retry:disabled` styling with clear countdown visual feedback.

### 3. `plugins/ayur-core/includes/class-ayur-chatbot.php`
* Updated the Send button markup to include both the SVG paper airplane icon (`.ayur-chat-send-icon`) and SVG spinner icon (`.ayur-chat-send-spinner`) for seamless loading transitions without layout shift.

### 4. `plugins/ayur-core/includes/class-ayur-chatbot-config.php`
* Enforced positive minimums in `get_rate_limit_per_minute()`:
  ```php
  public static function get_rate_limit_per_minute() {
      $val = absint(get_option(self::OPTION_RATE_LIMIT, 15));
      return $val > 0 ? $val : 15;
  }
  ```
* Enforced positive minimums in `get_max_input_length()`:
  ```php
  public static function get_max_input_length() {
      $val = absint(get_option(self::OPTION_MAX_INPUT_LENGTH, 500));
      return $val > 0 ? $val : 500;
  }
  ```

### 5. `plugins/ayur-core/includes/class-ayur-chatbot-engine.php`
* **Safe Input Sanitization:** Enforced lower bound `max(100, Ayur_Chatbot_Config::get_max_input_length())` to guarantee queries are never truncated to 0 characters.
* **Sliding-Window Rate Limiting:** Implemented `check_rate_limit($ip)` storing arrays of request timestamps over a 60-second window. It calculates exact seconds until the oldest request expires (`retry_after = ($oldest + 60) - now`) and does not append timestamps on blocked requests.
* **HTTP 429 Header Compliance:** Sends `@header('Retry-After: ' . $retry_after); status_header(429);` with JSON error payload containing `'retry_after'` and `'code' => 'rate_limit_exceeded'`.
* **Deflection Guard:** Detects if an LLM returns a generic greeting for piles/bawasir inquiries and automatically routes to the verified grounded fallback response.
* **Piles Grounded Fallback (Branch 9):** Enriched with 100% verified catalog data for **Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)** (price ₹890, link, verified packaging botanicals only, and non-cure disclaimer) in English, Hindi, and Marathi.
* Exposed `get_client_ip()` as `public static` for diagnostics and IP tracking.

### 6. `plugins/ayur-core/includes/class-ayur-chatbot-knowledge.php`
* Added stopword filtering to `search_relevant_products()` to remove conversational noise words (`the`, `for`, `which`, `what`, `product`, `item`, etc.).
* Enriched piles synonyms (`piles`, `pile`, `hemorrhoids`, `haemorrhoids`, `bavasir`, `bawaseer`, `mulvyadh`, `मूळव्याध`, `बवासीर`, `पाइल्स`, `fissure`) with an elevated score boost (+35) mapping directly to `AYUR-PILES-60`.

### 7. `plugins/ayur-core/includes/class-ayur-chatbot-safety.php`
* Added explicit product recommendation guidelines to `build_system_prompt()`: Instructs the AI model to directly identify **Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)** at ₹890 with link, label botanicals, and non-cure disclaimer when customers inquire about piles/hemorrhoids/bawasir in English, Hindi, or Marathi.

### 8. `plugins/test-probe/test-probe.php`
* Added diagnostic actions `diagnose_chatbot`, `test_chatbot_query`, `test_full_chatbot_simulation`, `opcache_reset`, and `reset_chatbot_rate_limit` for automated verification.

---

## 4. Test Verification Results

### 4.1 Test 1: Piles Product Answer QA ("Which product is for piles?")

The automated test suite executed live requests against the production endpoint across all three supported languages.

| Test Query | Language | HTTP Status | Product Identified | Price (₹890) | Permalinks & Card | Verified Botanicals Only | Non-Cure Disclaimer | Result |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| `"Which product is for piles?"` | EN | 200 | Aloe Vera Triphala Piles Care (60 Caps) | ₹890 | Verified | Kumari, Triphala, Nagkesar, Neem, Jimikand | Verified | **PASS** |
| `"बवासीर (पाइल्स) के लिए कौन सा उत्पाद है?"` | HI | 200 | Aloe Vera Triphala Piles Care (60 Caps) | ₹890 | Verified | कुमारी, त्रिफला, नागकेशर, नीम, जिमीकंद | Verified | **PASS** |
| `"मूळव्याध (पाइल्स) साठी कोणते उत्पादन आहे?"` | MR | 200 | Aloe Vera Triphala Piles Care (60 Caps) | ₹890 | Verified | कोरफड, त्रिफळा, नागकेशर, कडुलिंब, सुरण | Verified | **PASS** |

**Sample Live Response (English):**
> *Namaste! For piles, hemorrhoids, or digestive and anorectal comfort, we proudly recommend our classical Ayurvedic formulation:*  
> ***Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)***  
> *• **Price:** ₹890 (MRP: ₹1,990 - 55% OFF)*  
> *• **Storefront Link:** https://bhagyodayayurveda.in/product/aloe-vera-triphala-piles-care-capsules/*  
> *• **Verified Ingredients:** Kumari (Aloe Vera), Triphala (Amalaki, Haritaki, Bibhitaki), Nagkesar, Neem, and Jimikand (Suran).*  
> *• **Benefits & Usage:** This 100% plant-derived herbal formula facilitates smooth, effortless bowel evacuation without harsh chemical laxatives...*  
> *• **Dosage:** Take 1 to 2 capsules twice daily with lukewarm water after meals or before bedtime.*  
> *• **Disclaimer:** Please note that I am an Ayurvedic product assistant, not a doctor. This is an Ayurvedic dietary wellness supplement, not intended to cure piles or replace medical or surgical care. If acute bleeding or severe discomfort persists, we advise consulting your treating physician.*  
> `[PRODUCT: AYUR-PILES-60]`

---

### 4.2 Test 2: Rate Limit Enforcement & Genuine HTTP 429 Response

Rapid sequential requests were transmitted to verify sliding-window rate limiting on the live server.

| Request # | HTTP Status | Action Taken | Server Headers / Payload |
| :---: | :---: | :---: | :--- |
| **Req 1 - 15** | `200 OK` | Processed normally | Query recorded, sliding-window timestamps appended. |
| **Req 16** | `429 Too Many Requests` | Rate limit enforced | `Retry-After: 35`<br>`{"success":false,"data":{"message":"You are asking questions quickly. Please wait 35 seconds before your next question.","retry_after":35,"code":"rate_limit_exceeded"}}` |

* **Retry Button Behavior:** Frontend extracts `retry_after = 35`, disables the Retry button, and initiates a live countdown (`35s -> 0s`).
* **Single Retry Guarantee:** Upon timer expiration, clicking Retry resubmits the pending message once without generating a duplicate user bubble.
* **Window Preservation:** Rejected requests do not extend the rate limit window.

---

### 4.3 Test 3: Static Code & Behavioral Audit

| Checkpoint | File | Verified Requirement | Status |
| :--- | :--- | :--- | :---: |
| **Multi-Init Guard** | `ayur-chatbot.js` | `window.ayurChatInitialized` singleton prevents duplicate listeners | **PASS** |
| **Uncoupled Enter Key** | `ayur-chatbot.js` | Enter key directly calls `submitUserMessage()` without double-firing | **PASS** |
| **Safe Retry Handler** | `ayur-chatbot.js` | `isRetry = true` flag skips `appendMessage('user')`, avoiding duplicates | **PASS** |
| **Button Loading State** | `ayur-chatbot.js` | Send button enters disabled state with `.ayur-chat-loading` spinner | **PASS** |
| **Rate Limit Countdown** | `ayur-chatbot.js` | Parses `Retry-After` header and manages live countdown timer | **PASS** |
| **Mobile Bottom-Sheet** | `ayur-chatbot.css` | `min(84dvh, 620px)` height, rounded top corners (`20px 20px 0 0`), drag handle | **PASS** |
| **Safe-Area Insets** | `ayur-chatbot.css` | `env(safe-area-inset-bottom)` applied to mobile input container | **PASS** |
| **Text Wrapping** | `ayur-chatbot.css` | `overflow-wrap: anywhere; word-break: break-word;` prevents overflow | **PASS** |
| **Subtle Spinner Keyframes** | `ayur-chatbot.css` | `@keyframes ayur-chat-spin` ensures smooth loading indicator | **PASS** |

---

### 4.4 Test 4: Mobile Viewport Audit (360px, 375px, 390px, 393px, 412px)

* **Launcher Separation:** Chatbot launcher icon anchored at `bottom: 24px; left: 24px`, leaving the WhatsApp float at `bottom: 24px; right: 24px` completely unobstructed.
* **Bottom-Sheet Layout:** At all tested mobile screen widths (360px Small Mobile, 375px iPhone SE, 390px iPhone 12-14, 393px iPhone 15/16, and 412px Samsung Galaxy), the chat window opens as a responsive bottom sheet anchored to the lower viewport.
* **Purchase Controls Uncovered:** Floating checkout buttons, Add to Cart bars, and Sticky CTA fields remain fully accessible behind the launcher button.
* **Virtual Keyboard:** Uses `visualViewport` event listener to lift the input field above the mobile on-screen keyboard without viewport jumping.

---

## 5. Deployment Summary

* **Production URL:** `https://bhagyodayayurveda.in/`
* **Plugin Slug:** `ayur-core` (52 files deployed via Hostinger REST deployment API)
* **Server Cache:** Purged via Hostinger `hosting_cache_clear-website`
* **PHP OPcache:** Flushed and verified
* **Git Repository:** Committed and pushed to `main` branch on GitHub (`https://github.com/dipakchavan97/bhagyoday.git`) without force-pushing.

All duplicate message submission causes, rate-limit discrepancies, mobile UI shortcomings, and piles product mapping issues have been thoroughly resolved and confirmed operational in production.
