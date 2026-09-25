import urllib.request
import re
from html import unescape

url = 'https://lightgreen-heron-113083.hostingersite.com/shop/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=30) as response:
        html = response.read().decode('utf-8')
        
        # Look for product cards
        links = set(re.findall(r'href="(https://lightgreen-heron-113083\.hostingersite\.com/product/[^/"]+/)"', html))
        print(f"Unique product URLs found on /shop/: {len(links)}")
        for l in sorted(links):
            print("  -", l)
            
        # Also check for images inside shop
        images = set(re.findall(r'src="(https://lightgreen-heron-113083\.hostingersite\.com/wp-content/uploads/[^"]+)"', html))
        print(f"\nUnique upload images on /shop/: {len(images)}")
        for img in sorted(images):
            print("  -", img)
except Exception as e:
    print("Error:", e)
