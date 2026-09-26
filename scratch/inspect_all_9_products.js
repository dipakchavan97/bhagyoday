const https = require('https');

https.get('https://lightgreen-heron-113083.hostingersite.com/wp-json/wc/store/v1/products?per_page=20', (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
        const products = JSON.parse(data);
        console.log('Total products found:', products.length);
        products.forEach(p => {
            console.log(`\n========================================`);
            console.log(`ID: ${p.id} | Slug: ${p.slug} | Name: ${p.name}`);
            console.log(`Categories:`, p.categories.map(c => c.name));
            console.log(`Description preview:`, p.description.substring(0, 300).replace(/<[^>]+>/g, ' '));
        });
    });
});
