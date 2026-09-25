import urllib.request
import json

url = 'https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=30) as res:
        html = res.read().decode('utf-8')
        for i, s in enumerate(html.split('<script type="application/ld+json">')[1:]):
            block = s.split('</script>')[0].strip()
            data = json.loads(block)
            if data.get('@type') == 'Product':
                print(f"Block {i+1} aggregateRating:", data.get('aggregateRating'))
except Exception as e:
    print("Error:", e)
