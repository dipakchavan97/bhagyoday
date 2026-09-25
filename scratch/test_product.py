import urllib.request
import re
import sys

# Ensure UTF-8 output
sys.stdout.reconfigure(encoding='utf-8')

url = 'https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=30) as response:
        html = response.read().decode('utf-8')
        
        print("=== VERIFYING SINGLE PRODUCT PAGE ===")
        print("Target URL:", url)
        
        # Title
        title = re.search(r'<h1>([^<]+)</h1>', html)
        print("Title:", title.group(1).strip() if title else "NOT FOUND")
        
        # Price, MRP, Savings
        price = re.search(r'class="ayur-single-price">([^<]+)<', html)
        mrp = re.search(r'class="ayur-single-mrp">([^<]+)<', html)
        save = re.search(r'class="ayur-single-save-badge">([^<]+)<', html)
        print("Price:", price.group(1).strip() if price else "N/A")
        print("MRP:", mrp.group(1).strip() if mrp else "N/A")
        print("Savings Badge:", save.group(1).strip() if save else "N/A")
        
        # Gallery
        main_img = re.search(r'id="ayurMainProductImg"[^>]*src="([^"]+)"', html)
        print("Main Image:", main_img.group(1) if main_img else "NOT FOUND")
        thumbs = re.findall(r'class="ayur-gallery-thumb-btn[^"]*"[^>]*data-full="([^"]+)"', html)
        print(f"Gallery Thumbnails count: {len(thumbs)}")
        for i, t in enumerate(thumbs):
            print(f"  Thumb {i+1}: {t}")
            
        # CTAs
        add_cart = re.search(r'class="ayur-btn-add-cart[^"]*"', html)
        buy_now = re.search(r'class="ayur-btn-buy-now"', html)
        sticky_bar = re.search(r'class="ayur-mobile-sticky-bar"', html)
        print("Add to Cart CTA:", "YES" if add_cart else "NO")
        print("Buy Now CTA:", "YES" if buy_now else "NO")
        print("Mobile Sticky Bar:", "YES" if sticky_bar else "NO")
        
        # WhatsApp Widget
        wa = re.search(r'https://wa\.me/918888686858', html)
        print("WhatsApp Widget (918888686858):", "VERIFIED" if wa else "NOT FOUND")
        
        # Benefits & Trust Badges
        benefits = re.findall(r'<ul class="ayur-benefits-list">.*?</ul>', html, re.DOTALL)
        print("Key Benefits List:", "PRESENT" if benefits else "NOT FOUND")
        print("Cash on Delivery Badge:", "YES" if "Cash on Delivery" in html else "NO")
        print("24-Hour Dispatch Badge:", "YES" if "Within 24 Hours" in html else "NO")
        
        # Product Tabs Content
        print("Ingredients Section:", "YES" if "Ingredients" in html or "Botanical Actives" in html else "NO")
        print("How to Use / Dosage:", "YES" if "How to Use" in html or "Dosage" in html else "NO")
        print("Precautions Section:", "YES" if "Precautions" in html or "Safety" in html else "NO")
        print("FAQ Section:", "YES" if "FAQ" in html or "Frequently Asked Questions" in html else "NO")
        
        # Legacy Old Domain check
        old_domain = "bhagyodayayurveda.in" in html
        print("Legacy domain leak on page:", "LEAK DETECTED!" if old_domain else "CLEAN (None)")
except Exception as e:
    print("Error:", e)
