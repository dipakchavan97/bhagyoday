const https = require('https');

https.get('https://lightgreen-heron-113083.hostingersite.com/wp-json/wc/v3/reports/reviews/totals', (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => console.log('Reviews check:', data));
}).on('error', err => console.log('Err:', err.message));
