import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'https://lightgreen-heron-113083.hostingersite.com/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=30) as response:
        html = response.read().decode('utf-8')
        print("=== HOMEPAGE VERIFICATION ===")
        print("Hero Title found:", 'NOURISHING LIVES, NATURALLY' in html or 'Ashwagandha Shilajit' in html)
        print("Phone (+91 8888686858) found:", '8888686858' in html)
        print("Address (Mantha, Jalna) found:", 'Mantha' in html and 'Jalna' in html)
        print("Email (vijaykulal8833@gmail.com) found:", 'vijaykulal8833@gmail.com' in html)
        print("WhatsApp widget link found:", 'wa.me/918888686858' in html)

        # Count products on homepage
        product_links = set(re.findall(r'href="(https://lightgreen-heron-113083\.hostingersite\.com/product/[^/"]+/)"', html))
        print(f"\nUnique products featured on Homepage: {len(product_links)}")
        for p in sorted(product_links):
            print("  -", p)
except Exception as e:
    print("Error:", e)
