const https = require('https');

https.get('https://lightgreen-heron-113083.hostingersite.com/checkout/', (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
        console.log('Status code:', res.statusCode);
        const idx = data.indexOf('<main');
        if (idx !== -1) {
            console.log('Main snippet:\n', data.substring(idx, idx + 1200));
        } else {
            console.log('Body snippet:\n', data.substring(data.indexOf('<body'), data.indexOf('<body') + 800));
        }
    });
});
