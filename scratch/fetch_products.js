const https = require('https');

https.get('https://lightgreen-heron-113083.hostingersite.com/wp-json/wc/store/v1/products?per_page=20', (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
        try {
            const products = JSON.parse(data);
            console.log(JSON.stringify(products.map(p => ({
                id: p.id,
                name: p.name,
                price: p.prices.price,
                regular_price: p.prices.regular_price,
                categories: p.categories.map(c => c.name),
                short_description: p.short_description ? p.short_description.replace(/<[^>]+>/g, '').trim() : ''
            })), null, 2));
        } catch (e) {
            console.error('Parse error:', e.message, data.substring(0, 300));
        }
    });
}).on('error', err => console.error('Fetch error:', err.message));
