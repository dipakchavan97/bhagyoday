const https = require('https');

https.get('https://lightgreen-heron-113083.hostingersite.com/product/karela-jamun-gurmar-sugar-balance-capsules/', (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
        const start = data.indexOf('ayur-product-details-wrap');
        const end = data.indexOf('ayur-mobile-sticky-bar');
        console.log('--- PRODUCT DETAILS WRAP SLICE ---');
        console.log(data.substring(start, start + 3000));
        
        const relStart = data.indexOf('ayur-related-wrap');
        console.log('--- RELATED PRODUCTS SLICE ---');
        console.log(data.substring(relStart, relStart + 2000));
    });
});
