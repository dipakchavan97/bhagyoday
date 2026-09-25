import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=30) as response:
        html = response.read().decode('utf-8')
        print(f"HTML length: {len(html)}")
        print("Contains 'Ashwagandha Shilajit':", 'Ashwagandha Shilajit' in html)
        print("Contains 'application/ld+json':", 'application/ld+json' in html)
        
        blocks = html.split('<script type="application/ld+json">')
        print(f"JSON-LD Schema blocks found: {len(blocks) - 1}")
        for i, b in enumerate(blocks[1:]):
            block_content = b.split('</script>')[0].strip()
            print(f"\n--- BLOCK {i+1} ---")
            print(block_content[:400] + ("..." if len(block_content) > 400 else ""))
            
        print("\n--- Tabs in HTML ---")
        for tab in ['ayur_ingredients', 'ayur_usage', 'ayur_precautions', 'ayur_faq']:
            print(f"Tab '{tab}' present:", tab in html)
except Exception as e:
    print("Error:", e)
