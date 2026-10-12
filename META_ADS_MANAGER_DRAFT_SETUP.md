# Meta Ads Manager Manual Draft Setup Guide

**Campaign Name:** `BA | Sales | India | ₹300 Daily | Test 01`  
**Ad Set Name:** `India | Broad | Purchase | Test 01`  
**Brand:** Bhagyoday Ayurveda  
**Target Product URL:** [Amrit Heart Health Immunity Juice 500ml](https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/)  
**Initial Daily Budget:** **₹300 / day** (₹2,100 planned for initial 7-day test)  
**Safety Gate:** **ALL ADS REMAIN IN DRAFT / PAUSED STATUS (ZERO AD SPEND)**  

---

## 1. Access & Security Disclosure

In accordance with strict security protocols:
* Antigravity **never** requests or stores your Meta account password, two-factor authentication (2FA) tokens, or browser session cookies.
* Because direct API write tokens for Meta Ads Manager creation are restricted to authorized account owners, this guide provides the exact parameter-by-parameter specification to configure the campaign directly in your authenticated [Meta Ads Manager](https://adsmanager.facebook.com/) dashboard in under 5 minutes.

---

## 2. Step 1: Campaign Configuration (Level 1)

1. Open [Meta Ads Manager](https://adsmanager.facebook.com/) in your browser.
2. Select your active **Bhagyoday Ayurveda Ad Account** in the top-left dropdown.
3. Click the green **+ Create** button.
4. **Campaign Objective:** Select **Sales**.
5. Click **Continue** -> Choose **Manual sales campaign** (do not select Advantage+ shopping campaign yet) -> Click **Continue**.
6. Set the Campaign parameters:
   * **Campaign Name:** Enter exactly:  
     `BA | Sales | India | ₹300 Daily | Test 01`
   * **Special Ad Categories:** Leave **None / Blank** (Ayurvedic dietary supplement; does not fall under Housing, Employment, Credit, or Social Issues/Politics).
   * **Campaign Details:**
     * Buying Type: **Auction**
     * Campaign Objective: **Sales**
   * **Advantage Campaign Budget (CBO):** Toggle **OFF** (Keep budget at the Ad Set level for strict ₹300/day control).
7. Click the blue **Next** button.

---

## 3. Step 2: Ad Set Configuration (Level 2)

Configure the ad set with broad, compliant targeting:

1. **Ad Set Name:** Enter exactly:  
   `India | Broad | Purchase | Test 01`
2. **Conversion:**
   * Conversion location: **Website**
   * Performance goal: **Maximize number of conversions**
   * Dataset / Pixel: Select Dataset **`1755****5967`**
   * Conversion event: Select **Purchase**
3. **Budget & Schedule:**
   * Budget: **Daily Budget**
   * Amount: Enter **₹300.00**
   * Schedule: Set start date to today; leave end date unchecked.
4. **Audience Controls & Demographics:**
   * Location: **India (Country)** — Include all India (Shiprocket pan-India delivery).
   * Minimum Age: **18**
   * Age Range: **18 – 65+**
   * Gender: **All genders**
   * Detailed Targeting: **Leave completely empty / Broad**  
     *(Why: Meta's AI algorithm optimizes based on conversion pixel signals. Narrow targeting inflates CPMs and risks violating Meta's Personal Health policy regarding medical condition targeting).*
   * Languages: **All languages**
5. **Placements:**
   * Select **Advantage+ placements (Recommended)**.  
     *(Our 4:5 feed and 9:16 story creative assets are optimized for seamless multi-placement across Facebook & Instagram Feeds, Stories, Reels, and Search).*
6. **Optimization & Delivery:**
   * Attribution Setting: **7-day click or 1-day view**
7. Click the blue **Next** button.

---

## 4. Step 3: Creative & Ad Configuration (Level 3)

Create two active test ads (Ad A and Ad B), with Ad C retained as an alternative.

---

### Ad A: Lifestyle Creative (Indian Couple)
1. **Ad Name:** Enter:  
   `Ad A — Lifestyle Couple — Amrit Juice 500ml`
2. **Identity:** Select your official **Bhagyoday Ayurveda Facebook Page** and **Instagram Account**.
3. **Ad Setup:** **Single image or video**.
4. **Media Upload:**
   * Click **Add Media** -> **Add Image**.
   * For **Feed (1:1 / 4:5)** placements: Upload `assets/meta-creatives/ad_creative_amrit_lifestyle_feed_4x5.jpg`.
   * For **Stories & Reels (9:16)** placements: Click **Replace** on the 9:16 crop and upload `assets/meta-creatives/ad_creative_amrit_lifestyle_story_9x16.jpg`.
5. **Ad Copy:**
   * **Primary Text:**  
     `Discover Bhagyoday Ayurveda’s Amrit Juice, a traditional Ayurvedic herbal formulation. Explore the product details, ingredients and usage information on our official website.`
   * **Headline:**  
     `Discover Amrit Juice`
   * **Description:**  
     `Explore the 500 ml formulation online.`
   * **Call to Action (CTA):** Select **Shop Now**.
6. **Destination:**
   * Website URL:  
     `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/?utm_source=meta&utm_medium=paid_social&utm_campaign=BA_Sales_India_Test01&utm_content=lifestyle`

---

### Ad B: Premium Product Hero Creative (Ingredients Focus)
1. In the left navigation tree, click the three dots next to `Ad A` and select **Duplicate**.
2. **Ad Name:** Enter:  
   `Ad B — Product Hero Ingredients — Amrit Juice 500ml`
3. **Media:**
   * Click **Edit** -> **Change Media**.
   * For **Feed placements**: Upload `assets/meta-creatives/ad_creative_amrit_product_hero_feed_4x5.jpg`.
   * For **Stories & Reels placements**: Upload `assets/meta-creatives/ad_creative_amrit_product_hero_story_9x16.jpg`.
4. **Ad Copy:**
   * **Primary Text:**  
     `Explore Bhagyoday Ayurveda Amrit Juice and learn more about its listed ingredients, product details and recommended usage. Visit our official store for current pricing and delivery information.`
   * **Headline:**  
     `Explore Amrit Juice 500 ml`
   * **Description:**  
     `Product details and ordering online.`
   * **Call to Action (CTA):** Select **Shop Now**.
5. **Destination:**
   * Website URL:  
     `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/?utm_source=meta&utm_medium=paid_social&utm_campaign=BA_Sales_India_Test01&utm_content=product_hero`

---

### Ad C: Hindi-First Alternative Creative (Optional / Regional Test)
*(Can be launched alongside Ad A & B or kept as reserve)*
1. **Ad Name:** `Ad C — Hindi Vitality — Amrit Juice 500ml`
2. **Media:** `ad_creative_amrit_hindi_hero_feed_4x5.jpg` and `ad_creative_amrit_hindi_hero_story_9x16.jpg`.
3. **Primary Text (Hindi):**  
   `भाग्योदय आयुर्वेद के अमृत जूस के बारे में जानें — अर्जुन, आंवला और पारंपरिक जड़ी-बूटियों से तैयार एक आयुर्वेदिक फॉर्मूलेशन। उत्पाद सामग्री, उपयोग विधि और प्रामाणिक विवरण जानने के लिए हमारी आधिकारिक वेबसाइट देखें।`
4. **Headline:** `अमृत जूस 500ml | प्रामाणिक आयुर्वेद`
5. **Description:** `वेबसाइट पर पूरी जानकारी देखें • कैश ऑन डिलीवरी उपलब्ध`
6. **CTA:** `Shop Now`
7. **Destination URL:**  
   `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/?utm_source=meta&utm_medium=paid_social&utm_campaign=BA_Sales_India_Test01&utm_content=hindi_companion`

---

## 5. Step 4: Save as Draft & Keep Paused (CRITICAL SAFETY STEP)

1. Once Ad A and Ad B are configured, look at the top-right corner of Meta Ads Manager.
2. **DO NOT CLICK "PUBLISH"**.
3. Click the small **Close (X)** icon in the top right.
4. Meta will display a notice: *"Your changes have been saved as a draft."*
5. In the Ads Manager table, confirm that the toggle switch next to `BA | Sales | India | ₹300 Daily | Test 01` is set to **OFF (Grey / Paused)**.
6. The campaign is now safely stored as a draft without spending any money.

---

## 6. Pre-Launch Owner Approval Checklist

Before switching the campaign toggle to **ON**:

- [ ] **1. Shipping Disclosure Check:** Verified that all creatives state *"FREE DELIVERY Above ₹999"* and no false claims of "Free Shipping on All Orders" remain.
- [ ] **2. Test Event Code Cleared:** Logged into WordPress Admin -> **WooCommerce** -> **Tracking & Attribution**, confirmed the **Test Event Code** field is blank/empty, and saved.
- [ ] **3. Budget Verification:** Confirmed Ad Set daily budget is exactly **₹300.00** (not ₹3,000 or lifetime).
- [ ] **4. Payment Gateway Check:** Completed a quick sanity check of Razorpay UPI and COD confirmation flow on live checkout.
- [ ] **5. Explicit Owner Launch Approval:** Confirmed owner readiness to initiate live ad spend.
