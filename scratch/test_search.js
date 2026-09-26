const https = require('https');

const terms = ['Ashwagandha', 'Shilajit', 'Aloe', 'Joint', 'Heart'];

async function search(term) {
    const url = 'https://lightgreen-heron-113083.hostingersite.com/?s=' + encodeURIComponent(term) + '&post_type=product';
    const res = await fetch(url, { redirect: 'follow' });
    const finalUrl = res.url;
    const html = await res.text();
    
    let products = [];
    if (finalUrl.includes('/product/')) {
        const titleMatch = html.match(/<h1[^>]*class="[^"]*ayur-product-title[^"]*"[^>]*>([^<]+)<\/h1>/i) || html.match(/<h1[^>]*>([^<]+)<\/h1>/i);
        if (titleMatch) {
            products.push(titleMatch[1].trim());
        }
    } else {
        const re = /<h3[^>]*class="[^"]*ayur-product-title[^"]*"[^>]*>\s*<a[^>]*>([^<]+)<\/a>\s*<\/h3>/gi;
        let m;
        while ((m = re.exec(html)) !== null) {
            products.push(m[1].trim());
        }
    }

    return {
        term,
        finalUrl,
        count: products.length,
        results: products
    };
}

(async () => {
    console.log('Testing WooCommerce Search for 5 Terms...');
    for (const t of terms) {
        const r = await search(t);
        console.log(`Term: "${r.term}" -> ${r.count} products found:`, r.results);
    }
})();
