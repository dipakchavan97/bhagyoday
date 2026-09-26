const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';
const BASE_URL = 'https://lightgreen-heron-113083.hostingersite.com';

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9235;

    console.log('--- TESTING CHECKOUT IN NATURAL USER FLOW (SHOP -> ADD TO CART -> PROCEED TO CHECKOUT) ---');

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        'about:blank'
    ]);

    await new Promise(r => setTimeout(r, 2000));

    const targets = await new Promise((resolve, reject) => {
        http.get(`http://127.0.0.1:${port}/json/list`, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
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
    await send('Network.enable');

    async function setViewport(w, h, isMobile = true) {
        await send('Emulation.setDeviceMetricsOverride', {
            width: w,
            height: h,
            deviceScaleFactor: 2,
            mobile: isMobile
        });
    }

    async function evaluate(expression) {
        const res = await send('Runtime.evaluate', {
            expression,
            returnByValue: true,
            awaitPromise: true
        });
        return res && res.result ? res.result.value : null;
    }

    async function captureScreenshot(filepath) {
        const { data } = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(filepath, Buffer.from(data, 'base64'));
    }

    try {
        await setViewport(390, 844, true);
        console.log('1. Navigating to Shop page...');
        await send('Page.navigate', { url: BASE_URL + '/shop/' });
        await new Promise(r => setTimeout(r, 3500));

        console.log('2. Clicking Add to Cart on first product...');
        await evaluate(`document.querySelector('.ayur-products-grid .ayur-product-card .ajax_add_to_cart').click();`);
        await new Promise(r => setTimeout(r, 2500));

        console.log('3. Clicking Proceed to Checkout in Cart Drawer...');
        await evaluate(`document.querySelector('.ayur-btn-drawer-checkout').click();`);
        await new Promise(r => setTimeout(r, 4500));

        console.log('4. Inspecting Checkout Page...');
        const checkoutData = await evaluate(`(() => {
            const currentUrl = window.location.href;
            const form = document.querySelector('form.woocommerce-checkout');
            const placeOrderBtn = document.querySelector('#place_order');
            const placeOrderRect = placeOrderBtn?.getBoundingClientRect();
            const billingPhone = document.querySelector('#billing_phone');
            const billingPostcode = document.querySelector('#billing_postcode');
            const waWidget = document.querySelector('.ayur-wa-float-container');
            const waVisible = waWidget ? (window.getComputedStyle(waWidget).display !== 'none') : false;
            const bottomNav = document.querySelector('.ayur-mobile-bottom-nav');
            const bottomNavVisible = bottomNav ? (window.getComputedStyle(bottomNav).display !== 'none') : false;
            const paymentMethods = Array.from(document.querySelectorAll('.wc_payment_method label')).map(l => l.textContent.trim());
            const cartItems = Array.from(document.querySelectorAll('.woocommerce-checkout-review-order-table tr.cart_item')).map(r => ({
                name: r.querySelector('.product-name')?.textContent.trim(),
                total: r.querySelector('.product-total')?.textContent.trim()
            }));
            const total = document.querySelector('.order-total .woocommerce-Price-amount')?.textContent.trim();

            return {
                currentUrl,
                formPresent: !!form,
                placeOrderText: placeOrderBtn?.value || placeOrderBtn?.textContent.trim(),
                placeOrderHeight: placeOrderRect ? placeOrderRect.height : 0,
                billingPhoneLabel: billingPhone?.closest('.form-row')?.querySelector('label')?.textContent.trim(),
                billingPhonePlaceholder: billingPhone?.getAttribute('placeholder'),
                billingPostcodeLabel: billingPostcode?.closest('.form-row')?.querySelector('label')?.textContent.trim(),
                waHiddenOnCheckout: !waVisible,
                bottomNavHiddenOnCheckout: !bottomNavVisible,
                paymentMethods,
                cartItems,
                total
            };
        })()`);

        console.log('Checkout Verification Data:', checkoutData);

        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_checkout_mobile_flow.png'));

        // Scroll to payment & capture
        await evaluate(`document.querySelector('#order_review')?.scrollIntoView();`);
        await new Promise(r => setTimeout(r, 400));
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_checkout_mobile_payment_flow.png'));

        // Desktop screenshot
        await setViewport(1280, 800, false);
        await evaluate(`window.scrollTo(0, 0);`);
        await new Promise(r => setTimeout(r, 400));
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_checkout_desktop_flow.png'));

    } catch (e) {
        console.error('Error during checkout flow:', e);
    } finally {
        ws.close();
        chrome.kill();
    }
}

main();
