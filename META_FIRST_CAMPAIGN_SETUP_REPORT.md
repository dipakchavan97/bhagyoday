# Meta Ads Campaign & Performance Marketing Setup Report

**Brand:** Bhagyoday Ayurveda  
**Campaign Name:** `BA | Sales | India | ₹300 Daily | Test 01`  
**Ad Set Name:** `India | Broad | Purchase | Test 01`  
**Target Product:** Amrit Heart Health Immunity Juice, 500 ml  
**Product Landing Page:** [https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/](https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/)  
**Document Timestamp:** October 12, 2026, 14:25:00 UTC (19:55:00 IST)  
**Status:** **DRAFT / PAUSED (READY FOR OWNER APPROVAL)**  
**Safety Gate:** ₹0 spent; no ads published or activated; daily budget strictly capped at **₹300/day**.

---

## 1. Executive Summary & Strategic Objectives

This document establishes the strategic, technical, and creative framework for Bhagyoday Ayurveda's initial paid customer acquisition campaign on Meta (Facebook & Instagram).

### Core Objectives
1. **Initial Validation Test:** Deploy a controlled, low-risk test at **₹300/day** (₹2,100 total 7-day initial budget) to establish baseline cost-per-click (CPC), cost-per-add-to-cart (CPATC), and cost-per-purchase (CPA).
2. **Measurement Integrity:** Rely 100% on the verified Meta Pixel and Conversions API (Dataset `1755****5967`) with server-side deduplication.
3. **Aspirational Goal Reality Check:** The owner's target of 20 orders/day represents ₹17,800/day in gross revenue. At a ₹300/day ad spend, this would require a Cost Per Acquisition (CPA) of ₹15, which is outside realistic benchmarks for cold-traffic direct-to-consumer Ayurvedic health formulations (industry benchmark CPA in India is ₹250–₹450). This report outlines the staged scaling path to reach 20 orders/day sustainably based on proven contribution margin.

---

## 2. Business & Live Store Data Audit

Before constructing ads, the live product page and store checkout were independently inspected:

| Audit Parameter | Live Store State | Marketing Impact & Recommendations |
| :--- | :--- | :--- |
| **Product Title** | Amrit Heart Health Immunity Juice 500ml | Verified. Use exact title in product description and ad copy. |
| **Selling Price** | **₹890** | Verified on live page (`.ayur-single-price`). Used in ad copy and value tracking. |
| **MRP & Savings** | MRP **₹1,990** \| Save ₹1,100 (55% OFF) | Verified. Highlights strong value proposition without artificial inflation. |
| **Inventory Status** | In Stock (500 ml bottle) | Verified. |
| **Store Shipping Fee** | **₹70 Flat Rate** for orders under ₹999<br>**Free Shipping** on orders ≥ ₹999 | **CRITICAL AUDIT FINDING:** 1 single bottle (₹890) incurs ₹70 shipping, bringing checkout total to **₹960**.<br>The creative graphic displays *"FREE SHIPPING On All Orders"*. |
| **Payment Options** | Razorpay (UPI, Cards, NetBanking)<br>Cash on Delivery (₹100 advance fee) | Verified. Customer pays ₹100 online advance fee for COD orders; balance ₹790 collected on delivery. |

> [!WARNING]
> ### Critical Creative & Checkout Discrepancy: Shipping Fee
> * **Creative Banner:** The supplied ad images include a bottom banner stating **"FREE SHIPPING On All Orders"**.
> * **Live Checkout Reality:** A single bottle is priced at **₹890**, which is below the store's **₹999** free delivery threshold. The checkout adds a **₹70 Standard Delivery** fee, making the total **₹960**.
> * **Risk:** Advertising "Free Shipping on All Orders" when single-bottle buyers are charged ₹70 shipping causes cart abandonment and violates Meta's *Misleading & Deceptive Business Practices* policy.
> * **Recommended Action (Owner Decision):**
>   1. **Recommended:** Set WooCommerce Free Shipping threshold to **₹800** (or enable free shipping for Amrit Juice) so that single bottles genuinely receive free shipping at ₹890.
>   2. **Alternative:** Update ad copy and primary text to state: *"Free Pan-India Delivery on orders above ₹999 / 2-Bottle Value Packs"*.

---

## 3. Comprehensive Conversion Tracking Audit

The store's tracking was audited using automated browser inspection and direct server API probes:

| Tracking Requirement | Channel | Verified Parameters | Audit Status | Evidence / Notes |
| :--- | :--- | :--- | :--- | :--- |
| **PageView** | Browser & Server | Base pixel initialized with `1755****5967` | **PASS** | Intercepted 3 times across home, product, checkout pages. |
| **ViewContent** | Browser | SKU: `AYUR-ARJ-500`, Value: `890`, Currency: `INR` | **PASS** | Intercepted on `/product/arjuna-amrit-heart-vitality-juice/` with unique `vc_33_*` event ID. |
| **AddToCart** | Browser | SKU: `AYUR-ARJ-500`, Value: `890`, Currency: `INR` | **PASS** | Intercepted on button click with unique `atc_33_*` event ID. |
| **InitiateCheckout** | Browser | SKU: `AYUR-ARJ-500`, Value: `960`, Items: `1` | **PASS** | Intercepted on `/checkout/` with unique `ic_*` event ID. |
| **CAPI Server Transmission** | Server | Meta Graph API `v19.0` with `test_event_code` | **PASS** | Meta Graph API returned `HTTP 200 OK`, `events_received: 1`, `fbtrace_id: ACcs0dH5YeSEtwS7Kg-aXkl`. |
| **Purchase Deduplication Symmetry** | Code Level | Event ID: `order_{order_id}` on both channels | **PASS** | Exact deterministic matching keys confirmed in `class-ayur-tracking.php`. |
| **Duplicate Fire Safeguards** | Code Level | `_pixel_purchase_fired` & `_capi_purchase_fired` | **PASS** | Page reloads and multiple status hooks will not refire Purchase. |
| **Failed/Abandoned Safeguards** | Code Level | Payment status verification before event | **PASS** | Unpaid or unconfirmed orders never reach `order-received`. |
| **End-to-End Live Purchase in Events Manager UI** | Live UI | Real completed purchase appearing in dashboard | **UNVERIFIED (Safety Gate)** | Intentionally blocked: No fake orders or live payments processed without owner authorization. |

### Tracking Readiness Conclusion
The tracking infrastructure is **architecturally complete and verified across browser and server APIs**. As per the safety gate rules, live ad spending remains paused until the owner reviews the campaign draft.

---

## 4. Meta Campaign Settings (Draft Specification)

| Setting Field | Configuration | Strategic Rationale |
| :--- | :--- | :--- |
| **Campaign Name** | `BA \| Sales \| India \| ₹300 Daily \| Test 01` | Clear naming convention adhering to Brand \| Objective \| Geo \| Budget \| Version. |
| **Campaign Objective** | **Sales** (Conversions) | Optimizes Meta's algorithm specifically for website buyers rather than casual link clickers. |
| **Buying Type** | **Auction** | Standard auction bidding. |
| **Special Ad Categories** | **None** | Amrit Juice is an Ayurvedic botanical supplement; it does not fall under Housing, Employment, Credit, or Politics. |
| **Advantage Campaign Budget (CBO)** | **OFF (Ad Set Budget Optimization)** | Budget is controlled strictly at the ad set level (₹300/day) to prevent algorithmic budget swings across test creatives. |
| **Campaign Status** | **DRAFT / PAUSED** | Strictly enforces the zero-spend safety gate. |

---

## 5. Ad Set Configuration (Draft Specification)

| Setting Field | Configuration | Strategic Rationale |
| :--- | :--- | :--- |
| **Ad Set Name** | `India \| Broad \| Purchase \| Test 01` | Geo \| Targeting Strategy \| Optimization \| Test Identifier. |
| **Conversion Location** | **Website** | Routes traffic directly to WooCommerce store. |
| **Performance Goal** | **Maximize number of conversions** | Instructs Meta to find users most likely to complete a purchase. |
| **Conversion Event** | **Purchase** | Selected from Dataset `1755****5967`. |
| **Daily Budget** | **₹300.00** | Strict budget cap. Meta will spend approximately ₹2,100 over 7 days. |
| **Schedule** | Set to Start upon Approval | Paused by default. |
| **Location** | **India (Country-wide)** | Shiprocket pan-India delivery coverage. |
| **Age Range** | **18 – 65+ (Adults)** | Broad adult wellness audience. (Post-test data will identify top performing brackets, e.g. 30–55). |
| **Gender** | **All Genders** | Amrit Juice benefits both men and women. |
| **Detailed Targeting** | **Broad (No Interest Filters)** | Meta's AI algorithms optimize delivery based on pixel signals and creative hooks. Narrow interest targeting increases CPMs and violates Meta's *Personal Health* policy when targeting specific medical conditions. |
| **Languages** | **All Languages** | Broad reach; creative contains English & Hindi cues. |
| **Placements** | **Advantage+ Placements** | Automatic multi-placement across Facebook & Instagram Feeds, Stories, Reels, and Search. 4:5 vertical creatives fit all primary mobile placements seamlessly. |
| **Attribution Setting** | **7-day click or 1-day view** | Meta's recommended default window for eCommerce conversions. |

---

## 6. Creative Assets & Ad Variations

The authentic product bottle, box, and lifestyle imagery provided by the owner have been imported, verified, and saved to the project workspace:

### Creative File Inventory

| Asset Name | Workspace File Path | Aspect Ratio / Dimensions | Visual Approach |
| :--- | :--- | :--- | :--- |
| **Ad A — Lifestyle** | `assets/meta-creatives/ad_creative_amrit_lifestyle.jpg` | **776 x 1024 (4:5 Vertical Feed)** | Active Indian couple in athletic attire with Amrit Juice bottle and box; highlights natural wellness, immunity, and heart vitality. |
| **Ad B — Product Hero** | `assets/meta-creatives/ad_creative_amrit_product_hero.jpg` | **776 x 1024 (4:5 Vertical Feed)** | Premium product packaging on rustic wood with 5 botanical ingredients (Arjuna, Amla, Turmeric, Guduchi, Berries). Focus on purity and formulation. |
| **Ad C — Hindi Vitality (Alternative)** | `assets/meta-creatives/ad_creative_amrit_hindi_hero.jpg` | **776 x 1024 (4:5 Vertical Feed)** | Bottle, box, and ingredients with Hindi headline: *"दिल की सेहत का आयुर्वेदिक साथी"*. Excellent for regional retargeting. |

### Ad Copy Variations (Compliant with Meta Healthcare Policy)

> [!IMPORTANT]
> ### Strict Health Policy Compliance Note
> Meta strictly prohibits ads that claim to **diagnose, cure, treat, or prevent medical diseases**, make **unsubstantiated cholesterol-lowering guarantees**, or use **personal attributes language** (e.g. *"Are you suffering from heart problems?"*).  
> The ad copy below has been crafted to highlight authentic traditional Ayurvedic heritage while fully complying with Meta's advertising standards.

#### Ad A (Lifestyle Creative)
* **Ad Name:** `Ad A — Lifestyle Couple — Amrit Juice 500ml`
* **Creative Asset:** `ad_creative_amrit_lifestyle.jpg`
* **Primary Text:**  
  *Discover Bhagyoday Ayurveda's Amrit Juice — a traditional Ayurvedic herbal formulation crafted with Arjuna, Amla, Guduchi, and pure botanical extracts to support natural vitality and daily wellness. Explore the authentic ingredients, dosage recommendations, and customer reviews on our official store.*
* **Headline:** `Explore Amrit Juice | Traditional Wellness`
* **Description:** `500 ml Herbal Formulation • Cash on Delivery Available`
* **Call to Action (CTA):** `Shop Now`
* **Destination URL:**  
  `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/?utm_source=meta&utm_medium=cpc&utm_campaign=BA_Sales_India_Test01&utm_content=lifestyle_couple`

#### Ad B (Product Hero Creative)
* **Ad Name:** `Ad B — Product Hero Ingredients — Amrit Juice 500ml`
* **Creative Asset:** `ad_creative_amrit_product_hero.jpg`
* **Primary Text:**  
  *Crafted with potent Ayurvedic herbs including Arjuna, Amla, and Turmeric, Bhagyoday Ayurveda's Amrit Juice brings time-tested botanical care to your everyday routine. 100% natural, preservative-free formulation in a 500 ml bottle. Tap below to learn more and order online.*
* **Headline:** `Amrit Juice 500ml • ₹890`
* **Description:** `Traditional Ayurvedic Blend • Pure Herbs`
* **Call to Action (CTA):** `Shop Now`
* **Destination URL:**  
  `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/?utm_source=meta&utm_medium=cpc&utm_campaign=BA_Sales_India_Test01&utm_content=product_hero`

---

## 7. Landing Page Validation & Conversion Readiness

The landing page (`https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/`) was tested across simulated desktop and mobile viewports:

| Validation Check | Finding | Status |
| :--- | :--- | :--- |
| **Visual Hierarchy & Packaging** | Packaging bottle and box match ad creative imagery exactly. | **PASS** |
| **Pricing & Offer Clarity** | ₹890 selling price is clearly rendered; MRP ₹1,990 and savings badge visible. | **PASS** |
| **Mobile Add to Cart & Buy Now** | Fixed sticky action buttons operate smoothly without horizontal overflow. | **PASS** |
| **Cart & Shipping Rates** | Calculates ₹890 item + ₹70 shipping = ₹960 correctly in cart. | **PASS** |
| **Checkout Form Usability** | Single-page streamlined checkout with mandatory 10-digit Indian phone validation. | **PASS** |
| **COD Confirmation Payment** | Razorpay ₹100 online advance fee modal loads securely on checkout. | **PASS** |
| **Statutory Disclaimer** | Footer includes clear disclaimer: *"Dietary herbal supplement. Not intended to diagnose, treat, cure or prevent any disease."* | **PASS** |

---

## 8. Budget & Performance Measurement Framework

### Initial 7-Day Controlled Test Plan
* **Daily Budget:** ₹300/day
* **Total 7-Day Planned Spend:** ₹2,100
* **Campaign Duration:** Day 1 through Day 7 (Evaluated daily; no mid-test budget changes unless critical errors occur).

### Daily Performance Reporting Sheet Template

| Metric | Benchmark / Target | Calculation / Source |
| :--- | :--- | :--- |
| **Amount Spent** | ₹300.00 / day | Meta Ads Manager |
| **Impressions** | ~1,200 – 2,000 / day | Meta Ads Manager |
| **CPM (Cost per 1,000 Impressions)** | ₹150 – ₹250 | Spend / (Impressions / 1000) |
| **Outbound Clicks** | ~20 – 40 / day | Meta Ads Manager Link Clicks |
| **CTR (Link Click-Through Rate)** | ≥ 1.5% | Link Clicks / Impressions |
| **CPC (Cost Per Outbound Click)** | ₹8 – ₹15 | Spend / Outbound Clicks |
| **Landing Page Views (LPV)** | ≥ 80% of Clicks | Meta Ads Manager Pixel Signal |
| **AddToCart (ATC)** | 2 – 5 / day | Meta Pixel / Events Manager |
| **InitiateCheckout (IC)** | 1 – 3 / day | Meta Pixel / Events Manager |
| **Purchases (Orders)** | 0 – 2 / day (Initial test) | WooCommerce + Meta CAPI |
| **CPA (Cost per Purchase)** | Target < ₹350 | Spend / Purchases |
| **AOV (Average Order Value)** | ₹890 – ₹960 | Total Revenue / Purchases |
| **ROAS (Return on Ad Spend)** | Target > 2.0x | Total Revenue / Spend |
| **COD Confirmation Paid Rate** | > 80% of COD Orders | WooCommerce Backend Orders |

### Scaling Roadmap Toward 20 Orders/Day
* **Unit Economics Analysis:**
  * Selling Price: ₹890
  * Product Cost & Packaging: ~₹220
  * Shipping (Shiprocket): ~₹70
  * Contribution Margin (Pre-Ad): ~₹600
  * **Target Maximum Breakeven CPA:** ₹600
  * **Target Profitable CPA:** ₹250 – ₹300 (yielding ~2.5x to 3.0x ROAS).
* **To achieve 20 orders/day sustainably:**
  * At a healthy CPA of ₹300, the required ad budget is **₹6,000/day** (yielding ₹17,800/day gross revenue).
  * **Staged Scaling Rule:** Do not increase budget immediately. Run the ₹300/day test for 7 days. If Day 1–7 proves a CPA under ₹350 and strong COD fulfillment rates, scale budget by 20% every 3 days.

---

## 9. Step-by-Step Meta Ads Manager Setup Walkthrough

Because Meta Ads Manager requires account authentication, follow these step-by-step instructions to create the exact draft campaign:

### Step 1: Open Meta Ads Manager
1. Navigate to [Meta Ads Manager](https://adsmanager.facebook.com/).
2. Select your Bhagyoday Ayurveda Ad Account in the top-left dropdown.

### Step 2: Create Campaign
1. Click the green **+ Create** button.
2. Under "Choose a campaign objective", select **Sales**.
3. Choose **Manual sales campaign** (do not select Advantage+ shopping campaign yet). Click **Continue**.
4. In Campaign Settings:
   * **Campaign Name:** Enter `BA | Sales | India | ₹300 Daily | Test 01`.
   * **Special Ad Categories:** Leave **None / Blank**.
   * **Advantage campaign budget:** Toggle **OFF** (use Ad Set budget).
5. Click **Next**.

### Step 3: Configure Ad Set
1. **Ad Set Name:** Enter `India | Broad | Purchase | Test 01`.
2. **Conversion:**
   * Conversion location: **Website**.
   * Performance goal: **Maximize number of conversions**.
   * Dataset: Select Dataset **`1755****5967`**.
   * Conversion event: Select **Purchase**.
3. **Budget & Schedule:**
   * Daily Budget: Enter **₹300.00**.
   * Start Date: Today's date (leave end date open).
4. **Audience Controls:**
   * Location: **India**.
   * Age: **18 – 65+**.
   * Gender: **All genders**.
   * Detailed Targeting: **Leave empty / Broad**.
5. **Placements:**
   * Select **Advantage+ placements (Recommended)**.
6. Click **Next**.

### Step 4: Configure Ad A (Lifestyle Creative)
1. **Ad Name:** Enter `Ad A — Lifestyle Couple — Amrit Juice 500ml`.
2. **Identity:** Select your Facebook Page and Instagram Account.
3. **Ad Setup:** Single image or video.
4. **Media:** Click **Add Media** -> **Add Image** -> Upload `assets/meta-creatives/ad_creative_amrit_lifestyle.jpg`.
5. **Ad Copy:**
   * **Primary Text:** Paste the Ad A text from Section 6 above.
   * **Headline:** `Explore Amrit Juice | Traditional Wellness`.
   * **Description:** `500 ml Herbal Formulation • Cash on Delivery Available`.
   * **Call to Action:** Select **Shop Now**.
6. **Destination:**
   * Website URL: `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/?utm_source=meta&utm_medium=cpc&utm_campaign=BA_Sales_India_Test01&utm_content=lifestyle_couple`
7. Click **Duplicate** to create Ad B.

### Step 5: Configure Ad B (Product Hero Creative)
1. In the duplicated ad, change **Ad Name** to `Ad B — Product Hero Ingredients — Amrit Juice 500ml`.
2. Change Media to `assets/meta-creatives/ad_creative_amrit_product_hero.jpg`.
3. Update Primary Text, Headline, and Destination URL per Section 6 above.

### Step 6: Save as Draft
1. Click **Close** in the top-right corner (do **NOT** click "Publish").
2. Your campaign, ad set, and ads are now safely saved in **DRAFT / PAUSED** mode.

---

## 10. Final Pre-Launch Approval Checklist

Before clicking "Publish" in Meta Ads Manager, review this final checklist:

- [ ] **Shipping Fee Decision:** Clarified shipping policy (either set Free Shipping threshold to ₹800 or communicate shipping terms clearly).
- [ ] **Pixel in `<head>`:** Confirmed live on website (exactly 1 instance of `fbq('init')` in `<head>`).
- [ ] **Test Event Code Cleanup:** Cleaned/removed test code from WordPress settings if previously testing.
- [ ] **Budget Cap:** Confirmed ad set budget is exactly **₹300.00 / day**.
- [ ] **Targeting Policy:** Confirmed audience is Broad (no discriminatory or sensitive medical interest filters).
- [ ] **Payment Gateways:** Tested Razorpay UPI and COD confirmation flow on checkout.
- [ ] **Owner Approval:** Final sign-off provided by account owner.

---

**Report Status:** **READY FOR OWNER REVIEW**  
**Repository Branch:** `main` | All creative assets and documentation synced.
