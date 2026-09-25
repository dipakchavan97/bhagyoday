import urllib.request
import re

urls = [
    'https://lightgreen-heron-113083.hostingersite.com/',
    'https://lightgreen-heron-113083.hostingersite.com/shop/',
    'https://lightgreen-heron-113083.hostingersite.com/cart/',
    'https://lightgreen-heron-113083.hostingersite.com/checkout/',
    'https://lightgreen-heron-113083.hostingersite.com/about-us/',
    'https://lightgreen-heron-113083.hostingersite.com/contact-us/',
    'https://lightgreen-heron-113083.hostingersite.com/shipping-policy/',
    'https://lightgreen-heron-113083.hostingersite.com/refund-policy/',
    'https://lightgreen-heron-113083.hostingersite.com/privacy-policy/',
    'https://lightgreen-heron-113083.hostingersite.com/terms-and-conditions/',
    'https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/',
    'https://lightgreen-heron-113083.hostingersite.com/product/slim-care-weight-management-juice/',
    'https://lightgreen-heron-113083.hostingersite.com/product/aloe-vera-triphala-piles-care-capsules/',
    'https://lightgreen-heron-113083.hostingersite.com/product/arjuna-amrit-heart-vitality-juice/',
    'https://lightgreen-heron-113083.hostingersite.com/product/karela-jamun-gurmar-sugar-balance-capsules/',
    'https://lightgreen-heron-113083.hostingersite.com/product/shallaki-hadjod-joint-mobility-capsules/',
    'https://lightgreen-heron-113083.hostingersite.com/product/shatavari-ashoka-womens-vitality-juice/',
    'https://lightgreen-heron-113083.hostingersite.com/product/daily-energy-vitality-duo-shilajit-amrit-heart/',
    'https://lightgreen-heron-113083.hostingersite.com/product/metabolic-detox-sugar-balance-duo/'
]

print("=== FINAL QA SITE INTEGRITY SCAN ===")
for u in urls:
    req = urllib.request.Request(u, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=15) as res:
            html = res.read().decode('utf-8', errors='ignore')
            status = res.status
            has_fatal = 'Fatal error' in html or 'Parse error' in html
            has_lorem = 'Lorem ipsum' in html or 'lorem ipsum' in html
            has_hello = 'Hello world' in html or 'Hello World' in html
            has_old_domain = 'bhagyodayayurveda.in' in html
            flags = []
            if has_fatal: flags.append('FATAL_ERR')
            if has_lorem: flags.append('LOREM_IPSUM')
            if has_hello: flags.append('HELLO_WORLD')
            if has_old_domain: flags.append('OLD_DOMAIN_LINK')
            path = u.split(".com")[-1]
            print(f"[{status}] {path:<55} -> Flags: {flags if flags else 'CLEAN'}")
    except Exception as e:
        print(f"ERROR {u}: {e}")
