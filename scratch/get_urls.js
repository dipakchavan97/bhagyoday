const https = require('https');
https.get('https://lightgreen-heron-113083.hostingersite.com/shop/', res => {
  let data = '';
  res.on('data', chunk => data += chunk);
  res.on('end', () => {
    const matches = [...data.matchAll(/href="(https:\/\/lightgreen-heron-113083\.hostingersite\.com\/product\/[^"]+)"/g)].map(m => m[1]);
    const unique = Array.from(new Set(matches));
    console.log('Unique product URLs:', unique);
  });
});
