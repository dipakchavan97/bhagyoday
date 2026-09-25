import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

domain = 'https://lightgreen-heron-113083.hostingersite.com'

products = [
    'ashwagandha-shilajit-men-wellness-capsules',
    'slim-care-weight-management-juice',
    'aloe-vera-triphala-piles-care-capsules',
    'arjuna-amrit-heart-vitality-juice',
    'karela-jamun-gurmar-sugar-balance-capsules',
    'shallaki-hadjod-joint-mobility-capsules',
    'shatavari-ashoka-womens-vitality-juice',
    'daily-energy-vitality-duo-shilajit-amrit-heart',
    'metabolic-detox-sugar-balance-duo'
]

print("=== CHECKING ALL 9 PRODUCTS ===")
for p in products:
    url = f"{domain}/product/{p}/"
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            code = resp.getcode()
            html = resp.read().decode('utf-8')
            h1 = re.search(r'<h1>([^<]+)</h1>', html)
            price = re.search(r'class="ayur-single-price">([^<]+)<', html)
            mrp = re.search(r'class="ayur-single-mrp">([^<]+)<', html)
            img = re.search(r'wp-content/uploads/2026/09/bhagyoday-[^"\']+\.webp', html)
            title = h1.group(1).strip() if h1 else "NO TITLE"
            p_val = price.group(1).strip() if price else "NO PRICE"
            m_val = mrp.group(1).strip() if mrp else "NO MRP"
            img_val = "WEBP IMG OK" if img else "NO IMG"
            print(f"[{code}] {p[:35]:<35} | {p_val} (MRP {m_val}) | {img_val} | {title[:40]}")
    except Exception as e:
        print(f"[ERR] {p}: {e}")

print("\n=== CHECKING STATIC BUSINESS PAGES ===")
pages = ['about-us', 'contact-us', 'shipping-policy', 'refund-policy', 'privacy-policy', 'terms-and-conditions']
for page in pages:
    url = f"{domain}/{page}/"
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            code = resp.getcode()
            html = resp.read().decode('utf-8')
            has_phone = "8888686858" in html
            has_mantha = "Mantha" in html or "Jalna" in html
            print(f"[{code}] /{page}/ | Phone Verified: {has_phone} | Mantha/Jalna Verified: {has_mantha}")
    except Exception as e:
        print(f"[ERR] /{page}/: {e}")

print("\n=== CHECKING CHECKOUT & CART ===")
for ep in ['cart', 'checkout']:
    url = f"{domain}/{ep}/"
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            print(f"[{resp.getcode()}] /{ep}/ OK")
    except Exception as e:
        print(f"[ERR] /{ep}/: {e}")
