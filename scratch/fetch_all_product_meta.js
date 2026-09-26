const https = require('https');

https.get('https://lightgreen-heron-113083.hostingersite.com/wp-json/wc/store/v1/products?per_page=20', (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
        try {
            const products = JSON.parse(data);
            const summary = products.map(p => ({
                id: p.id,
                name: p.name,
                permalink: p.permalink,
                categories: p.categories.map(c => c.name),
                description_length: p.description ? p.description.length : 0,
                short_description: p.short_description ? p.short_description.replace(/<[^>]+>/g, '').trim() : ''
            }));
            console.log(JSON.stringify(summary, null, 2));
        } catch (e) {
            console.error('Error:', e.message);
        }
    });
});
