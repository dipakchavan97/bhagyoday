import urllib.request
import re

url = 'https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=30) as response:
        html = response.read().decode('utf-8')
        
        print("Checking template markers in HTML:")
        print("ayur-single-product-wrap:", "ayur-single-product-wrap" in html)
        print("ayur-gallery-thumbs:", "ayur-gallery-thumbs" in html)
        print("ayur-product-summary:", "ayur-product-summary" in html)
        print("ayur-single-price-box:", "ayur-single-price-box" in html)
        print("woocommerce-product-gallery:", "woocommerce-product-gallery" in html)
        print("ayur-mobile-sticky-bar:", "ayur-mobile-sticky-bar" in html)
        
        # Check where single_add_to_cart_button is
        atc = re.findall(r'<button[^>]*single_add_to_cart_button[^>]*>(.*?)</button>', html, re.DOTALL)
        print("single_add_to_cart_button count:", len(atc))
        if atc:
            print("Button text:", [x.strip() for x in atc])
            
        # Check title
        h1 = re.findall(r'<h1[^>]*>(.*?)</h1>', html, re.DOTALL)
        print("H1 tags:", [x.strip() for x in h1])
except Exception as e:
    print("Error:", e)
