const https = require('https');

const urls = [
  'https://lightgreen-heron-113083.hostingersite.com/',
  'https://lightgreen-heron-113083.hostingersite.com/shop/',
  'https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/',
  'https://lightgreen-heron-113083.hostingersite.com/cart/'
];

async function checkUrl(url) {
  return new Promise((resolve) => {
    https.get(url, (res) => {
      resolve({ url, status: res.statusCode });
    }).on('error', (e) => {
      resolve({ url, error: e.message });
    });
  });
}

// Check checkout by first adding an item to get cart session cookie, then checking checkout HTTP status
function checkCheckoutWithItem() {
  return new Promise((resolve) => {
    const postData = 'add-to-cart=51'; // Ashwagandha product id
    const req = https.request('https://lightgreen-heron-113083.hostingersite.com/shop/?add-to-cart=51', {
      method: 'GET'
    }, (res) => {
      const cookies = (res.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
      
      const checkReq = https.request('https://lightgreen-heron-113083.hostingersite.com/checkout/', {
        method: 'GET',
        headers: {
          'Cookie': cookies
        }
      }, (cRes) => {
        resolve({ url: 'https://lightgreen-heron-113083.hostingersite.com/checkout/', status: cRes.statusCode, note: 'Tested with active cart session' });
      });
      checkReq.end();
    });
    req.end();
  });
}

async function run() {
  const results = [];
  for (const url of urls) {
    results.push(await checkUrl(url));
  }
  results.push(await checkCheckoutWithItem());
  console.log(JSON.stringify(results, null, 2));
}

run();
