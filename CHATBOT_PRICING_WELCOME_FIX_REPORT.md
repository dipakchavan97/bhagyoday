# Bhagyoday Ayurveda Chatbot: Price Layout, Welcome Message & Greeting Lifecycle Fix Report

**Project:** Bhagyoday Ayurveda  
**Website:** [https://bhagyodayayurveda.in/](https://bhagyodayayurveda.in/)  
**Repository:** [https://github.com/dipakchavan97/bhagyoday.git](https://github.com/dipakchavan97/bhagyoday.git)  
**Component:** `ayur-core` product chatbot (`Ayur Product Expert`)  
**Commit SHA:** `546e3be` (`fix(chatbot): correct mobile pricing and greeting lifecycle`)  
**Deployment Status:** Successfully deployed to Hostinger production & verified live on Edge / Chrome.

---

## 1. Executive Summary

This report documents the diagnosis, root-cause resolution, and live automated verification of the 3 critical issues affecting the Bhagyoday Ayurveda product chatbot:
1. **Product Card Mobile Price Breaking:** Resolved vertical column fracturing where prices split into `₹8` / `90`, MRP fractured into `₹1,` / `990`, and discount badges squeezed. Replaced with an ultra-responsive 3-row layout with strictly enforced single-line values (`white-space: nowrap !important; word-break: keep-all !important;`).
2. **First-Message Welcome Flow & Deduplication:** Fixed welcome message lifecycle so exactly one introductory message appears immediately upon opening a fresh conversation with 3 quick starter chips (`Ask about ingredients`, `How to use`, `Delivery & COD`). Minimized/reopened conversations maintain single message count without duplication.
3. **Elimination of Repeated Greetings:** Fixed backend AI prompting, fallback templates, and engine response cleaning so normal conversation turns never repeat "Namaste! Welcome to Bhagyoday Ayurveda". General questions are answered concisely without unsolicited product cards, while catalog-specific inquiries (such as piles care) attach the verified catalog card with required medical disclaimers.

---

## 2. Root Cause Analysis

### Issue 1: Price and MRP Vertically Breaking on Mobile Screens
- **Underlying Cause:**
  - The parent message bubble `.ayur-chat-msg` enforced `word-break: break-word;` and `overflow-wrap: break-word;`.
  - The previous `.ayur-chat-card` layout placed the product title, selling price, MRP strikethrough, discount badge, and stock indicator in an inline flex container within a narrow right column (~140px on 320px–375px mobile viewports).
  - Without `min-width: 0` on flex items and without explicit `white-space: nowrap !important;` on individual price spans, modern mobile browser rendering engines (Blink/WebKit) broke text between digits (e.g. breaking `₹890` between `8` and `90`, and `₹1,990` between `1,` and `990`) to satisfy parent column flex shrink constraints.
- **Architectural Solution:**
  - Transitioned the product card to a structured, semantic 3-row responsive layout:
    - **Row 1 (`.ayur-chat-card-row-top`):** Fixed-dimension thumbnail on the left (66px desktop / 58px on ultra-compact mobile) + Product Title and Pricing (`₹890` + `MRP ₹1,990` strikethrough) on the right.
    - **Row 2 (`.ayur-chat-card-row-meta`):** Full-width meta row positioning stock indicator (`● In Stock`) on the left and discount badge (`55% OFF`) on the right, eliminating overlap.
    - **Row 3 (`.ayur-chat-card-btn`):** Full-width, high-contrast, touch-optimized "View Product →" CTA button.
  - Applied `white-space: nowrap !important; word-break: keep-all !important; overflow-wrap: normal !important;` to `.ayur-chat-card-price`, `.ayur-chat-card-mrp`, and `.ayur-chat-card-badge`.

### Issue 2: Welcome Message Lifecycle & Repetition
- **Underlying Cause:**
  - Welcome messages lacked structured state management across minimize/reopen events and widget re-initializations. When the widget minimized or re-rendered, scripts lacked deterministic checks on whether the welcome turn had already been presented.
- **Architectural Solution:**
  - Implemented `sessionStorage` persistence (`ayur_chat_history_v2`) with `ensureWelcomeMessage()` guard.
  - Welcome message displays once upon conversation start or reset. Reopening a minimized conversation restores existing chat history without re-injecting welcome messages or re-binding DOM event listeners.
  - Added a dedicated "New conversation" (`#ayur-chat-reset`) header action to allow customers to cleanly restart conversations.

### Issue 3: Repeated Greetings and Unsolicited Product Cards
- **Underlying Cause:**
  - AI system prompts did not explicitly prohibit leading salutations on ongoing user turns.
  - Grounded fallback templates included hardcoded greeting prefixes ("Namaste! Welcome to Bhagyoday Ayurveda").
  - The knowledge search engine matched common inquiry keywords (like "use" or "ingredient") against catalog descriptions, yielding low-confidence product matches that erroneously attached random product cards to general queries.
- **Architectural Solution:**
  - Added backend greeting sanitizer `strip_leading_greeting()` in `Ayur_Chatbot_Engine` to automatically strip redundant greetings (`Namaste`, `Welcome to Bhagyoday Ayurveda`) from AI responses.
  - Updated AI system prompt with strict instructions: *"Do NOT begin every response with 'Namaste' or 'Welcome to Bhagyoday Ayurveda'. The greeting is already handled separately by the frontend welcome flow."*
  - Added stopword filtering and confidence thresholds (`$score >= 10` and `has_product_context()`) so general inquiries ("Ask about ingredients", "How to use") answer concisely without attaching unrelated product cards.
  - Dedicated piles routing ensures "Which product is for piles?" accurately recommends *Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)* (`AYUR-PILES-60`) with verified catalog pricing, ingredients, benefits, dosage, and medical disclaimer.

---

## 3. Files Changed

| File Path | Description of Changes |
| :--- | :--- |
| `plugins/ayur-core/assets/css/ayur-chatbot.css` | Implemented 3-row card layout (`.ayur-chat-card-row-top`, `.ayur-chat-card-row-meta`, `.ayur-chat-card-btn`), `white-space: nowrap !important; word-break: keep-all !important;` on price, MRP, and badge; responsive rules for `<480px` and `<360px`. |
| `plugins/ayur-core/assets/js/ayur-chatbot.js` | Updated card HTML renderer to 3-row structure; implemented `ensureWelcomeMessage()`, `resetConversation()`, `saveSession()`, and `restoreSession()` with `sessionStorage`; attached reset header button. |
| `plugins/ayur-core/includes/class-ayur-chatbot.php` | Added `#ayur-chat-reset` (New conversation) button to chat header action bar. |
| `plugins/ayur-core/includes/class-ayur-chatbot-config.php` | Updated default welcome message to concise copy; standardized 3 starter prompt chips: `Ask about ingredients`, `How to use`, `Delivery & COD`. |
| `plugins/ayur-core/includes/class-ayur-chatbot-safety.php` | Updated system prompt to forbid repetitive greetings on ongoing turns and forbid appending cards for general queries without product context. |
| `plugins/ayur-core/includes/class-ayur-chatbot-engine.php` | Added `strip_leading_greeting()`, `has_product_context()` helper, and updated fallback templates to eliminate repeated greetings and prevent unsolicited product cards. |
| `plugins/ayur-core/includes/class-ayur-chatbot-knowledge.php` | Expanded search stopwords to exclude action words (`ask`, `use`, `usage`, `ingredient`, etc.) and enforced `$score >= 10` so general queries do not match random products. |

---

## 4. Live Verification & Automated Test Results

Live automated browser tests were executed against production (`https://bhagyodayayurveda.in/`) using headless Microsoft Edge across mobile, tablet, and desktop viewports.

### Responsive Breakpoint Verification

| Viewport Width | Device Category | Price Status | MRP Status | Discount Badge | Horizontal Overflow | Result |
| :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **320px** | Ultra-Compact Mobile (iPhone SE 1st gen) | `₹890` (Single line) | `MRP ₹1,990` (Single line) | `55% OFF` (No wrap) | `false` (0px overflow) | **PASSED** |
| **360px** | Compact Android (Galaxy S8/S9) | `₹890` (Single line) | `MRP ₹1,990` (Single line) | `55% OFF` (No wrap) | `false` (0px overflow) | **PASSED** |
| **375px** | Standard Mobile (iPhone 12/13/Mini) | `₹890` (Single line) | `MRP ₹1,990` (Single line) | `55% OFF` (No wrap) | `false` (0px overflow) | **PASSED** |
| **390px** | Modern Mobile (iPhone 14/15) | `₹890` (Single line) | `MRP ₹1,990` (Single line) | `55% OFF` (No wrap) | `false` (0px overflow) | **PASSED** |
| **430px** | Large Mobile (iPhone 15 Pro Max, Plus) | `₹890` (Single line) | `MRP ₹1,990` (Single line) | `55% OFF` (No wrap) | `false` (0px overflow) | **PASSED** |
| **768px** | Tablet Portrait (iPad) | `₹890` (Single line) | `MRP ₹1,990` (Single line) | `55% OFF` (No wrap) | `false` (0px overflow) | **PASSED** |
| **1200px** | Desktop (Standard Display) | `₹890` (Single line) | `MRP ₹1,990` (Single line) | `55% OFF` (No wrap) | `false` (0px overflow) | **PASSED** |

### Conversational Lifecycle Verification

1. **Initial Open (Fresh Session):**
   - **Expected:** Exactly 1 welcome message and 3 prompt chips (`Ask about ingredients`, `How to use`, `Delivery & COD`).
   - **Observed:** Exactly 1 welcome message rendered. No duplicate bubbles.
2. **Minimize and Reopen:**
   - **Expected:** Conversation history preserved; no duplicate welcome message added.
   - **Observed:** Bot message count remained strictly 1.
3. **Query: "Ask about ingredients":**
   - **Expected:** Concise direct response asking which product the customer wants to know about. No "Namaste" or "Welcome". Zero product cards.
   - **Observed:**
     > *"Our formulations contain different herbal ingredients depending on the specific product. Could you please let me know which product you would like to know about?"*
     - Greeting repeated: `false`
     - Product cards attached: `0`
4. **Query: "How to use":**
   - **Expected:** Concise direct response asking for product name. No greeting. Zero product cards.
   - **Observed:**
     > *"Please follow the directions printed on the specific product label for usage instructions. If you let me know the exact product name you are using, I can provide the listed instructions for it."*
     - Greeting repeated: `false`
     - Product cards attached: `0`
5. **Query: "Which product is for piles?":**
   - **Expected:** Recommends Aloe Vera Triphala Piles Care Capsules with verified catalog card and safety disclaimer.
   - **Observed:**
     - Bot response grounded in catalog with ingredients, benefits, dosage, and medical disclaimer.
     - Product Card Returned:
       - **Name:** Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)
       - **Selling Price:** `₹890` (Single line)
       - **MRP:** `MRP ₹1,990` (Single line with strikethrough)
       - **Discount:** `55% OFF` (Compact badge)
       - **Stock:** `In Stock`
       - **CTA:** `View Product →` (Opens `/product/aloe-vera-triphala-piles-care-capsules/`)
6. **Query: "How do I use it?":**
   - **Expected:** Follow-up directions without repeating welcome or greetings.
   - **Observed:** Direct usage guidance without leading greetings.
7. **Reset Button Click (`#ayur-chat-reset`):**
   - **Expected:** Clears chat history, resets session, and restores exactly 1 welcome message with prompt chips.
   - **Observed:** Reset confirmed: `botCount: 1, userCount: 0, chipsCount: 3`.

---

## 5. Verification Screenshots

The following verification screenshots were captured during live Edge automated execution and are saved in the project artifact directory:

- **Welcome Flow on Mobile (375px):** `chatbot_welcome_375.png`  
  Shows the Ayur Product Expert header with new conversation icon, single welcome message, timestamp, and starter chips.
- **Product Card on 375px Mobile:** `chatbot_piles_375.png`  
  Shows the Aloe Vera Triphala Piles Care Capsules card with `₹890` and `MRP ₹1,990` on a single line, `55% OFF` badge, `In Stock` indicator, and full-width `View Product →` button.
- **Product Card on 320px Ultra-Compact Mobile:** `chatbot_piles_320.png`  
  Confirms zero horizontal overflow and unbroken price rendering on narrowest mobile screen.
- **Product Card on Desktop (1200px):** `chatbot_piles_1200_desktop.png`  
  Confirms desktop card sizing, typography, and styling.
- **Reset Conversation Flow:** `chatbot_reset_done.png`  
  Confirms clean reset to initial state with single welcome message and prompt chips.

---

## 6. Preservation of Existing Features

All existing store capabilities remain 100% active and untouched:
- **Rate Limiting:** IP-based sliding window with HTTP 429 and `Retry-After` headers preserved.
- **Double Submission Prevention:** Button disabling and state locks on submit preserved.
- **Multilingual Support:** English, Hindi, and Marathi catalog strings and fallback responses preserved.
- **Checkout & Payments:** Native WooCommerce checkout, Razorpay gateway, COD verification, and Shiprocket tracking fully operational.
