# Bhagyoday Ayurveda Chatbot: Price Layout & Ingredient Grounding Report

**Document:** `CHATBOT_PRICE_AND_INGREDIENT_ACCURACY_REPORT.md`  
**Live Website:** [https://bhagyodayayurveda.in/](https://bhagyodayayurveda.in/)  
**Repository:** [https://github.com/dipakchavan97/bhagyoday.git](https://github.com/dipakchavan97/bhagyoday.git)  
**Plugin:** `plugins/ayur-core` (Version `1.2.0`)  
**Deployment Date:** October 11, 2026  
**Primary Commits:** `13b2c3b`, `b9e02b2`, `ec89b93`

---

## 1. Executive Summary & Acceptance Criteria Validation

| Requirement | Acceptance Criteria | Verified Result | Status |
| :--- | :--- | :--- | :---: |
| **Selling Price Display** | Must render horizontally as unbreakable `₹890` | `₹890` single horizontal line across 320px–1200px (14–16px height) | **PASSED** |
| **MRP Display** | Must render horizontally as unbreakable `MRP ₹1,990` | `MRP ₹1,990` with strikethrough on a single horizontal line | **PASSED** |
| **Discount Badge** | Single unbreakable badge `55% OFF` | `55% OFF` badge aligned on the right edge of pricing row | **PASSED** |
| **Mobile Responsiveness** | No horizontal overflow across 320, 360, 375, 390, 430px | Tested across all viewports; `horizontalOverflow: false` | **PASSED** |
| **Amrit Heart Juice Ingredients** | Complete verified composition (not generic 6 herbs) | Exact **60 Herbs, Fruits & Extracts** from official label | **PASSED** |
| **Piles Care Ingredients** | Canonical composition matching packaging label | Exact verified formula (Aloe Vera, Triphala, Nagkesar, Neem, Suran) | **PASSED** |
| **Active Product Context** | Resolve ingredients when asked "List all ingredients of this product" | Context transmitted via AJAX; resolves exact SKU | **PASSED** |
| **Dosage Separation** | Separate directions for use from ingredient lists | Isolated dosage & precautions returned without dumping ingredients | **PASSED** |
| **Multilingual Grounding** | English, Hindi, and Marathi queries resolved | All 3 languages resolve canonical records and return verified items | **PASSED** |

---

## 2. Priority 1 — Price Rendering Diagnosis & Component Refactoring

### A. Root Cause Analysis
1. **Narrow Flex Column Constraint:**
   In previous iterations, pricing markup (`.ayur-chat-card-price`, `.ayur-chat-card-mrp`) was nested inside `.ayur-chat-card-info` right next to the 72px thumbnail in `.ayur-chat-card-row-top`.
   On a mobile viewport (320px–375px), after subtracting chat container padding, message bubble margins, card borders, and the 72px image thumbnail + gap, `.ayur-chat-card-info` had only ~130px–150px of available width.
   Inside that narrow column, the multiline product title and pricing elements competed for horizontal room. When `flex-wrap` kicked in, or when font sizing caused soft wrapping, the currency symbol `₹` or numbers split across lines (`₹8` / `90` and `MRP ₹1,` / `990`).

2. **Asset Cache Invalidation:**
   `AYUR_CORE_VERSION` was previously held at `1.1.0`. Mobile browsers and intermediate CDN caches continued serving cached stylesheets (`ayur-chatbot.css?ver=1.1.0`) and cached scripts. Bumping the plugin version to `1.2.0` in `ayur-core.php` instantly invalidated browser and server caches.

### B. Structural Component Refactoring (Semantic BEM Layout)
The product card component was refactored from a compressed 2-column layout to a clean **3-Row Semantic Architecture**:

```
.product-card (.ayur-chat-card)
│
├── Row 1: .product-card__main (.ayur-chat-card-row-top)
│   ├── .product-card__image (.ayur-chat-card-thumb) [60px-64px thumbnail]
│   └── .product-card__details (.ayur-chat-card-info)
│       ├── .product-card__title [natural multiline wrap, no price competition]
│       └── .product-card__stock [● In Stock / Out of Stock indicator]
│
├── Row 2: .product-card__pricing (.ayur-chat-card-pricing) [DEDICATED FULL-WIDTH ROW]
│   ├── .product-card__sale-price: ₹890 [white-space: nowrap !important; word-break: keep-all !important;]
│   ├── .product-card__mrp: MRP ₹1,990 [white-space: nowrap !important; strikethrough]
│   └── .product-card__discount: 55% OFF [white-space: nowrap !important; margin-left: auto;]
│
└── Row 3: .product-card__actions
    └── .product-card__btn (.ayur-chat-card-btn): View Product → [36px tap target]
```

By dedicating Row 2 completely to pricing, the elements have **260px–350px of width** available on mobile, eliminating all horizontal compression. The combined width of `₹890` (~39px) + `MRP ₹1,990` (~67px) + `55% OFF` (~60px) is ~166px plus margins, which effortlessly fits inside the 262px–352px row on any smartphone.

### C. Responsive Computed Metrics (Live Browser Automated Audit)

| Viewport | Device Class | Card Width | Row Width | Selling Price | MRP | Discount Badge | Overflow |
| :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **320 × 600** | Small Mobile (iPhone SE 1st gen) | 280px | 262px | `₹890` (14px h, 1-line) | `MRP ₹1,990` (1-line) | `55% OFF` (1-line) | **None** (false) |
| **360 × 640** | Standard Mobile (Galaxy S8) | 320px | 302px | `₹890` (14px h, 1-line) | `MRP ₹1,990` (1-line) | `55% OFF` (1-line) | **None** (false) |
| **375 × 812** | Mobile (iPhone 13 mini) | 320px | 298px | `₹890` (15px h, 1-line) | `MRP ₹1,990` (1-line) | `55% OFF` (1-line) | **None** (false) |
| **390 × 844** | Mobile (iPhone 14 / 15) | 335px | 313px | `₹890` (15px h, 1-line) | `MRP ₹1,990` (1-line) | `55% OFF` (1-line) | **None** (false) |
| **430 × 932** | Large Mobile (iPhone 15 Pro Max) | 374px | 352px | `₹890` (15px h, 1-line) | `MRP ₹1,990` (1-line) | `55% OFF` (1-line) | **None** (false) |
| **768 × 1024** | Tablet (iPad portrait) | 705px | 683px | `₹890` (15px h, 1-line) | `MRP ₹1,990` (1-line) | `55% OFF` (1-line) | **None** (false) |
| **1200 × 800** | Desktop Laptop | 299px | 273px | `₹890` (16px h, 1-line) | `MRP ₹1,990` (1-line) | `55% OFF` (1-line) | **None** (false) |

---

## 3. Priority 2 — Canonical Product Ingredient Grounding

### A. Authoritative Data Source Audit
- **Previous Flaw:** The chatbot previously returned an informal 6-ingredient placeholder (*"Arjuna Bark, Fresh Wild Amla, Ginger, Garlic, Raw Forest Honey, Apple Cider Vinegar"*) inferred from generic herbal associations.
- **Authoritative Ground Truth:** In `plugins/ayur-core/assets/images/`, the official high-resolution packaging composition asset `bhagyoday-amrit-heart-juice-composition.webp` explicitly specifies:
  > **"EACH 10ML CONTAINS:**  
  > 1. Noni 400mg, 2. Aloevera 300mg, 3. Acai Bery 400mg, 4. Sea Backthrone 300mg, 5. Beetroot 300mg, 6. Harad 300mg, 7. Baheda 300mg, 8. Bhumi Amla 300mg, 9. Shilajit 300mg, 10. Mulethi 300mg, 11. Giloy 300mg, 12. Shatavari 300mg, 13. Ashwagandha 300mg, 14. Amla 400mg, 15. Moringa Oleifera 500mg, 16. Anjeer 200mg, 17. Punarnava 200mg, 18. Ginkgo Biloba 200mg, 19. Garcinia Cambogia 200mg, 20. Mangosteen 300mg, 21. Spirulina 300mg, 22. Haridra 300mg, 23. Arjuna 200mg, 24. Brahmi 200mg, 25. Draksha Extract 200mg, 26. Manjistha 200mg, 27. Siberian Ginseng 200mg, 28. Ganoderma Extract 200mg, 29. Blue Berry 200mg, 30. Cordyceps Extract 100mg, 31. Chiku 100mg, 32. Neem 100mg, 33. Kesar 100mg, 34. Elaichi 50mg, 35. Shankapushpi 100mg, 36. Sarpagandha 100mg, 37. Faneel Seed (Fennel) 100mg, 38. Lemon Gras 100mg, 39. Apple 100mg, 40. Curcumin 100mg, 41. Goji Berry Extract 100mg, 42. Black Current Extract 100mg, 43. Straw Berry Extract 100mg, 44. Elder Berry Extract 100mg, 45. Wild Berry Extract 100mg, 46. Rasp Berry Extract 100mg, 47. Sour Cherry Extract 100mg, 48. Maca Root Extract 100mg, 49. Cran Berry Extract 100mg, 50. Pomegranate Extract 100mg, 51. Basil Extract 100mg, 52. Green Tea Extract 100mg, 53. Carrot Extract 100mg, 54. Vasa Extract 100mg, 55. Vitamin B12 100mg, 56. Vitamin D3 100mg, 57. Amla Extract 100mg, 58. Ashwagandha Extract 100mg, 59. Tulsi Extract 100mg, 60. Rest of all Berry's & Extracts 100mg."

### B. Canonical Ingredient Registry Implementation
Implemented `get_canonical_ingredient_records()`, `get_canonical_ingredient_record($sku)`, and `format_canonical_ingredients_response($record, $lang)` in `class-ayur-chatbot-knowledge.php`.

Every catalog product has an immutable structured record:
- **`AYUR-ARJ-500` (Amrit Heart Health Immunity Juice):** 60 verified botanicals, fruits, and extracts with exact mg quantities per 10ml, verified from `bhagyoday-amrit-heart-juice-composition.webp`.
- **`AYUR-PILES-60` (Aloe Vera Triphala Piles Care Capsules):** Kumari (Aloe Vera) 150mg, Triphala 150mg, Nagkesar 100mg, Neem 50mg, Suran 50mg per 500mg vegetarian capsule, verified from `bhagyoday-piles-care-capsules-packaging.webp`.
- **`AYUR-SHIL-30` (Men Power Capsule):** 20 verified Rasayana botanicals & Shudh Shilajit, verified from `bhagyoday-ashwagandha-shilajit-capsules-packaging.webp`.
- **`AYUR-SLIM-500` (Slim Care Juice):** 16 verified metabolic botanicals, verified from `bhagyoday-slim-care-juice-packaging.webp`.
- **`AYUR-SUGAR-60` (Karela Jamun Capsules):** 10 verified classical bitter botanicals, verified from `bhagyoday-karela-jamun-capsules-packaging.webp`.
- **`AYUR-SHAL-60` (Shallaki Joint Care Capsules):** 13 verified ortho herbs, verified from `bhagyoday-shallaki-joint-care-capsules-packaging.webp`.
- **`AYUR-SHAT-500` (Shatavari Ashoka Juice):** 14 verified female wellness botanicals, verified from `bhagyoday-shatavari-ashoka-juice-packaging.webp`.
- **`AYUR-COMBO-VIT` & `AYUR-COMBO-DETOX`:** Verified component formulations with cross-reference to single product canonical records.

### C. Active Product Context Passing
When a user views a single product page (`is_product()`), `class-ayur-chatbot.php` passes `current_product_sku` via `wp_localize_script`. When a recommendation card is rendered or clicked, `ayur-chatbot.js` sets `activeProductSku` and sends `active_product` with every message request. Queries like `"List all ingredients of this product"` or `"What is the dosage?"` resolve directly to that product.

### D. Separation of Dosage and Usage
When users query dosage or usage directions (`"What is the dosage?"`, `"how to use"`), the direct handler responds with the exact dosage and precautions (*"Mix 15 to 25 ml in a glass of lukewarm water..."*) without dumping or confusing it with the 60-ingredient composition list.

---

## 4. Priority 3 — Test Execution & Evidence

### A. Automated Backend Test Suite
Executed against live production endpoint `https://bhagyodayayurveda.in/wp-admin/admin-ajax.php`:

```
======================================================================
TEST SUMMARY:
[PASS] Test 1: Amrit Heart Juice Ingredients (English) => All criteria satisfied
[PASS] Test 2: List All Ingredients with Active Product context => All criteria satisfied
[PASS] Test 3: Dosage Query (Separated from Ingredients) => All criteria satisfied
[PASS] Test 4: Aloe Vera Triphala Piles Care Capsules Ingredients => All criteria satisfied
[PASS] Test 5: Hindi Ingredient Query => All criteria satisfied
[PASS] Test 6: Marathi Ingredient Query => All criteria satisfied
======================================================================
```

### B. Live Edge Automated Browser Screenshots
- `verified_card_mobile_320.png`: Proves single-line `₹890`, `MRP ₹1,990`, and `55% OFF` at 320px viewport width.
- `verified_ingredients_mobile_320.png`: Proves complete canonical ingredients returned with exact milligram dosages.
- `verified_card_mobile_375.png`: Proves standard mobile viewport layout with zero horizontal overflow.
- `verified_card_desktop_1200.png`: Proves desktop floating chat card with full readability.

---

## 5. Git & Deployment Log

- **Branch:** `main`
- **Commits:**
  - `13b2c3b` — `fix(chatbot): correct price layout and ground complete ingredients`
  - `b9e02b2` — `fix(chatbot): ground ingredient queries directly in canonical records`
  - `ec89b93` — `fix(chatbot): add multilingual product aliases and dedicated dosage handling`
- **Hostinger Deployment:**
  - 52 files uploaded successfully via `hosting_deploy-wordpress-plugin`.
  - Hostinger cache purged via `hosting_cache_clear-website`.
  - Plugin version active: `1.2.0`.
