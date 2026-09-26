const https = require('https');

https.get('https://lightgreen-heron-113083.hostingersite.com/wp-json/wc/store/v1/products/categories', (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
        try {
            const cats = JSON.parse(data);
            console.log(JSON.stringify(cats.map(c => ({ id: c.id, name: c.name, slug: c.slug, count: c.count })), null, 2));
        } catch (e) {
            console.error('Parse error:', e.message);
        }
    });
});
