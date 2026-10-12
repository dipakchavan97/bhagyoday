# Meta Ads Manager Manual Draft Setup Guide

**Campaign Name:** `BA | Sales | India | ₹300 Daily | Test 01`  
**Ad Set Name:** `India | Broad | Purchase | Test 01`  
**Brand:** Bhagyoday Ayurveda  
**Target Product URL:** [Amrit Heart Health Immunity Juice 500ml](https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/)  
**Initial Daily Budget:** **₹300 / day** (Ad Set Level Budget)  
**Safety Gate:** **ALL ADS REMAIN IN DRAFT / PAUSED STATUS (ZERO AD SPEND)**  

---

## 1. Access & Security Disclosure

In accordance with strict security protocols:
* Antigravity **never** requests or stores your Meta account password, two-factor authentication (2FA) tokens, or browser session cookies.
* An automated browser session test to access Meta Ads Manager encountered an execution environment dependency limitation (Playwright driver CDN 404), and Meta strictly mandates interactive two-factor authentication (2FA) and Business Portfolio admin rights that cannot and should not be automated programmatically.
* Because the automated session cannot bypass your 2FA authentication, this guide provides the exact parameter-by-parameter specification to configure the campaign directly in your authenticated [Meta Ads Manager](https://adsmanager.facebook.com/) dashboard in under 5 minutes.

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
   * Dataset / Pixel: Select Dataset **`1755****5967`** (Bhagyoday Ayurveda Dataset)
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

Configure two active test ads (Ad 01 and Ad 02), with Ad 03 retained as an alternative.

---

### Ad 01: Lifestyle Focus
1. **Ad Name:** Enter exactly:  
   `Ad 01 | Lifestyle | 4x5 + 9x16`
2. **Identity:** Select your official **Bhagyoday Ayurveda Facebook Page** and connected **Instagram Account**.
3. **Ad Setup:** **Single image or video**.
4. **Media Upload:**
   * Click **Add Media** -> **Add Image**.
   * For **Feed (1:1 / 4:5)** placements: Upload `assets/meta-creatives/ad_creative_amrit_lifestyle_feed_4x5.jpg`.
   * For **Stories & Reels (9:16)** placements: Click **Replace** on the 9:16 crop and upload `assets/meta-creatives/ad_creative_amrit_lifestyle_story_9x16.jpg`.
5. **Ad Copy:**
   * **Primary Text:**  
     `Discover Bhagyoday Ayurveda’s Amrit Juice, a traditional Ayurvedic herbal formulation. Explore the product details, ingredients and recommended usage on our official website. Free delivery on orders above ₹999.`
   * **Headline:**  
     `Discover Amrit Juice | 500 ml`
   * **Description:**  
     `Traditional Ayurvedic herbal formulation.`
   * **Call to Action (CTA):** Select **Shop Now**.
6. **Destination:**
   * Website URL:  
     `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/?utm_source=meta&utm_medium=paid_social&utm_campaign=BA_Sales_India_Test01&utm_content=lifestyle`

---

### Ad 02: Product Hero & Formulation Focus
1. In the left navigation tree, click the three dots next to `Ad 01` and select **Duplicate**.
2. **Ad Name:** Enter exactly:  
   `Ad 02 | Product Hero | 4x5 + 9x16`
3. **Media:**
   * Click **Edit** -> **Change Media**.
   * For **Feed placements**: Upload `assets/meta-creatives/ad_creative_amrit_product_hero_feed_4x5.jpg`.
   * For **Stories & Reels placements**: Upload `assets/meta-creatives/ad_creative_amrit_product_hero_story_9x16.jpg`.
4. **Ad Copy:**
   * **Primary Text:**  
     `Explore Bhagyoday Ayurveda Amrit Juice (500 ml). Visit our official store for product details, listed ingredients, pricing and delivery information.`
   * **Headline:**  
     `Explore Amrit Juice 500 ml`
   * **Description:**  
     `Discover the product online.`
   * **Call to Action (CTA):** Select **Shop Now**.
5. **Destination:**
   * Website URL:  
     `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/?utm_source=meta&utm_medium=paid_social&utm_campaign=BA_Sales_India_Test01&utm_content=product_hero`

---

### Ad 03: Hindi-First Alternative Creative (Reserve / Test)
*(Optional: Can be launched alongside Ad 01 & 02 or kept in draft reserve)*
1. **Ad Name:** `Ad 03 | Hindi-First Hero | 4x5 + 9x16`
2. **Media:** `assets/meta-creatives/ad_creative_amrit_hindi_hero_feed_4x5.jpg` and `ad_creative_amrit_hindi_hero_story_9x16.jpg`.
3. **Primary Text (Hindi):**  
   `भाग्योदय आयुर्वेद अमृत जूस — अर्जुन छाल और पारंपरिक आयुर्वेदिक जड़ी-बूटियों से निर्मित प्राकृतिक फॉर्मूलेशन। सामग्री, सेवन विधि और प्रामाणिक जानकारी के लिए हमारी वेबसाइट पर जाएं। ₹999 से अधिक के ऑर्डर पर मुफ्त डिलीवरी।`
4. **Headline:** `अमृत जूस 500 ml | भाग्योदय आयुर्वेद`
5. **Description:** `पारंपरिक आयुर्वेदिक जड़ी-बूटियों का संगम।`
6. **CTA:** `Shop Now`
7. **Destination URL:**  
   `https://bhagyodayayurveda.in/product/arjuna-amrit-heart-vitality-juice/?utm_source=meta&utm_medium=paid_social&utm_campaign=BA_Sales_India_Test01&utm_content=hindi_hero`

---

## 5. Step 4: Save as Draft & Keep Paused (CRITICAL SAFETY STEP)

1. Once Ad 01 and Ad 02 are configured, look at the top-right corner of Meta Ads Manager.
2. **DO NOT CLICK "PUBLISH"**.
3. Click the small **Close (X)** icon in the top right.
4. Meta will display a notice: *"Your changes have been saved as a draft."*
5. In the Ads Manager table, confirm that the toggle switch next to `BA | Sales | India | ₹300 Daily | Test 01` is set to **OFF (Grey / Paused)**.
6. Also confirm that the Ad Set and both Ads are set to **OFF / Paused**.
7. The campaign is now safely stored as a draft with **zero ad spend**.

---

## 6. Pre-Launch Owner Approval Checklist

Before switching the campaign toggle to **ON**:

- [ ] **1. Shipping Disclosure Check:** Verified that all creatives state *"FREE DELIVERY Above ₹999"* and no false claims of "Free Shipping on All Orders" remain.
- [ ] **2. Test Event Code Cleared:** Logged into WordPress Admin -> **WooCommerce** -> **Tracking & Attribution**, confirmed the **Test Event Code** field is blank/empty, and saved.
- [ ] **3. Budget Verification:** Confirmed Ad Set daily budget is exactly **₹300.00** (not ₹3,000 or lifetime).
- [ ] **4. Payment Gateway Check:** Completed a quick sanity check of Razorpay UPI and COD confirmation flow on live checkout.
- [ ] **5. Explicit Owner Launch Approval:** Confirmed owner readiness to initiate live ad spend.
