import urllib.request
import json

url = 'https://lightgreen-heron-113083.hostingersite.com/wp-json/wp/v2/comments'
try:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req, timeout=30) as res:
        data = json.loads(res.read().decode('utf-8'))
        print(f"Total comments via REST API: {len(data)}")
        for c in data:
            print(f"  ID: {c.get('id')} | Author: {c.get('author_name')} | Post: {c.get('post')}")
except Exception as e:
    print("Error:", e)
