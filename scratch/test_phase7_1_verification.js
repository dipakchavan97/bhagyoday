const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const port = 9238;
const userDataDir = 'd:\\woocom\\scratch\\chrome_user_p71';
const artifactsDir = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';

async function main() {
  console.log(`Spawning Chrome on port ${port}...`);
  const chrome = spawn(chromePath, [
    `--remote-debugging-port=${port}`,
    '--headless=new',
    '--disable-gpu',
    '--no-sandbox',
    `--user-data-dir=${userDataDir}`,
    'about:blank'
  ]);

  await new Promise(r => setTimeout(r, 2000));

  const targets = await new Promise((resolve, reject) => {
    http.get(`http://127.0.0.1:${port}/json/list`, res => {
      let data = '';
      res.on('data', c => data += c);
      res.on('end', () => resolve(JSON.parse(data)));
    }).on('error', reject);
  });

  const pageTarget = targets.find(t => t.type === 'page') || targets[0];
  const ws = new WebSocket(pageTarget.webSocketDebuggerUrl);
  await new Promise((resolve, reject) => {
    ws.onopen = resolve;
    ws.onerror = reject;
  });

  let msgId = 1;
  function send(method, params = {}) {
    return new Promise((resolve, reject) => {
      const id = msgId++;
      const handler = (evt) => {
        const res = JSON.parse(evt.data);
        if (res.id === id) {
          ws.removeEventListener('message', handler);
          if (res.error) reject(res.error);
          else resolve(res.result);
        }
      };
      ws.addEventListener('message', handler);
      ws.send(JSON.stringify({ id, method, params }));
    });
  }

  await send('Page.enable');
  await send('DOM.enable');
  await send('Runtime.enable');
  await send('Network.enable');
  await send('Network.clearBrowserCache');
  await send('Network.setCacheDisabled', { cacheDisabled: true });

  console.log('\n--- 1. Testing Homepage & Typography ---');
  await send('Emulation.setDeviceMetricsOverride', {
    width: 1280,
    height: 800,
    deviceScaleFactor: 1,
    mobile: false
  });

  await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/' });
  await new Promise(r => setTimeout(r, 4000));

  const typographyCheck = await send('Runtime.evaluate', {
    expression: `(() => {
      const h1 = document.querySelector('h1, .ayur-hero-title');
      const h2 = document.querySelector('h2, .ayur-section-title');
      const body = document.body;
      const cardTitle = document.querySelector('.ayur-product-title');

      return {
        h1Font: h1 ? window.getComputedStyle(h1).fontFamily : 'null',
        h2Font: h2 ? window.getComputedStyle(h2).fontFamily : 'null',
        bodyFont: window.getComputedStyle(body).fontFamily,
        cardTitleFont: cardTitle ? window.getComputedStyle(cardTitle).fontFamily : 'null'
      };
    })()`,
    returnByValue: true
  });
  console.log('Typography Computed Styles:', typographyCheck.result.value);

  // Capture Homepage Desktop
  const homeShot = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artifactsDir, 'phase7_1_home_desktop.png'), Buffer.from(homeShot.data, 'base64'));
  console.log('Saved phase7_1_home_desktop.png');

  console.log('\n--- 2. Testing Quick Add & Cart Drawer ---');
  // Click first Add to cart button on featured formulation or product grid
  const addClick = await send('Runtime.evaluate', {
    expression: `(() => {
      const btn = document.querySelector('.ayur-featured-actions .add_to_cart_button') || document.querySelector('.ayur-card-btn.add_to_cart_button');
      if (btn) {
        btn.click();
        return { clicked: true, text: btn.innerText.trim() };
      }
      return { clicked: false };
    })()`,
    returnByValue: true
  });
  console.log('Add to cart click:', addClick.result.value);
  await new Promise(r => setTimeout(r, 3500));

  // Check Cart Drawer state
  const drawerCheck = await send('Runtime.evaluate', {
    expression: `(() => {
      const drawer = document.getElementById('ayurCartDrawer') || document.querySelector('.ayur-cart-drawer');
      const isOpen = drawer ? drawer.classList.contains('is-open') : false;
      const freeShippingText = document.querySelector('.ayur-free-shipping-bar, .ayur-progress-text, .ayur-shipping-progress-box');
      const crossSell = document.querySelector('.ayur-drawer-cross-sell');
      const checkoutBtn = document.querySelector('.ayur-drawer-checkout-btn, .checkout-button');

      return {
        isOpen,
        freeShippingMsg: freeShippingText ? freeShippingText.innerText.trim() : 'null',
        crossSellVisible: !!crossSell,
        crossSellItems: crossSell ? crossSell.querySelectorAll('.ayur-drawer-cross-item').length : 0,
        hasCheckoutBtn: !!checkoutBtn
      };
    })()`,
    returnByValue: true
  });
  console.log('Cart Drawer State:', drawerCheck.result.value);

  const cartShot = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artifactsDir, 'phase7_1_cart_drawer.png'), Buffer.from(cartShot.data, 'base64'));
  console.log('Saved phase7_1_cart_drawer.png');

  console.log('\n--- 3. Testing Checkout Flow ---');
  // Click Proceed to Checkout in the drawer
  await send('Runtime.evaluate', {
    expression: `(() => {
      const checkoutBtn = document.querySelector('.ayur-drawer-checkout-btn, a.checkout-button, .ayur-cart-summary a.ayur-btn-primary');
      if (checkoutBtn) checkoutBtn.click();
    })()`
  });
  await new Promise(r => setTimeout(r, 5000));

  const checkoutCheck = await send('Runtime.evaluate', {
    expression: `(() => {
      const currentUrl = window.location.href;
      const billingFields = !!document.getElementById('billing_first_name');
      const placeOrder = !!document.getElementById('place_order');
      const waFloat = document.querySelector('.ayur-wa-float-container');
      const waDisplay = waFloat ? window.getComputedStyle(waFloat).display : 'none';
      const bottomNav = document.querySelector('.ayur-mobile-bottom-nav');
      const bottomNavDisplay = bottomNav ? window.getComputedStyle(bottomNav).display : 'none';

      return {
        url: currentUrl,
        hasBillingFields: billingFields,
        hasPlaceOrderBtn: placeOrder,
        waDisplay: waDisplay,
        bottomNavDisplay: bottomNavDisplay
      };
    })()`,
    returnByValue: true
  });
  console.log('Checkout Verification:', checkoutCheck.result.value);

  const checkoutShot = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artifactsDir, 'phase7_1_checkout_desktop.png'), Buffer.from(checkoutShot.data, 'base64'));
  console.log('Saved phase7_1_checkout_desktop.png');

  console.log('\n--- 4. Mobile Viewports QA ---');
  const mobileWidths = [
    { name: '360x800', w: 360, h: 800 },
    { name: '375x812', w: 375, h: 812 },
    { name: '390x844', w: 390, h: 844 },
    { name: '412x915', w: 412, h: 915 }
  ];

  for (const m of mobileWidths) {
    await send('Emulation.setDeviceMetricsOverride', {
      width: m.w,
      height: m.h,
      deviceScaleFactor: 2,
      mobile: true
    });
    await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/shop/' });
    await new Promise(r => setTimeout(r, 3000));

    const overflowCheck = await send('Runtime.evaluate', {
      expression: `(() => {
        return {
          scrollWidth: document.documentElement.scrollWidth,
          clientWidth: document.documentElement.clientWidth,
          hasOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
        };
      })()`,
      returnByValue: true
    });
    console.log(`Viewport ${m.name}:`, overflowCheck.result.value);
  }

  // Capture Mobile Shop
  const shopMobileShot = await send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(path.join(artifactsDir, 'phase7_1_shop_mobile.png'), Buffer.from(shopMobileShot.data, 'base64'));
  console.log('Saved phase7_1_shop_mobile.png');

  console.log('\n--- 5. Search Modal Verification ---');
  // Open search modal and test keyword query
  await send('Runtime.evaluate', {
    expression: `(() => {
      const searchTrigger = document.querySelector('.ayur-nav-search, [data-drawer="ayurSearchDrawer"], .ayur-search-trigger');
      if (searchTrigger) searchTrigger.click();
    })()`
  });
  await new Promise(r => setTimeout(r, 1000));

  const searchCheck = await send('Runtime.evaluate', {
    expression: `(async () => {
      const input = document.querySelector('#ayurSearchLiveInput, #ayurSearchInput, input[name="s"]');
      if (!input) return { error: 'No input' };
      input.value = 'Ashwagandha';
      input.dispatchEvent(new Event('input', { bubbles: true }));
      await new Promise(r => setTimeout(r, 1500));

      const results = document.querySelectorAll('#ayurSearchLiveResults .ayur-search-result-item');
      return {
        query: 'Ashwagandha',
        resultsFound: results.length,
        firstTitle: results[0] ? results[0].querySelector('.ayur-search-result-title').innerText : 'none'
      };
    })()`,
    awaitPromise: true,
    returnByValue: true
  });
  console.log('Search Live Results Check:', searchCheck.result.value);

  // Close chrome
  chrome.kill();
  console.log('\nAll QA checks finished successfully!');
}

main().catch(console.error);
