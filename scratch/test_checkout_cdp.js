const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';
const BASE_URL = 'https://lightgreen-heron-113083.hostingersite.com';

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9232;

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-checkout-qa',
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
        console.log('Navigating via Quick Buy to populate cart and load Checkout...');
        await setViewport(390, 844, true);
        await send('Page.navigate', { url: BASE_URL + '/?ayur_quick_buy=30' });
        await new Promise(r => setTimeout(r, 4500));

        const checkoutData = await evaluate(`(() => {
            const form = document.querySelector('form.woocommerce-checkout');
            const placeOrderBtn = document.querySelector('#place_order');
            const placeOrderRect = placeOrderBtn?.getBoundingClientRect();
            const billingPhone = document.querySelector('#billing_phone');
            const billingPostcode = document.querySelector('#billing_postcode');
            const waWidget = document.querySelector('.ayur-wa-float-container');
            const waVisible = waWidget ? (window.getComputedStyle(waWidget).display !== 'none') : false;
            const bottomNav = document.querySelector('.ayur-mobile-bottom-nav');
            const bottomNavVisible = bottomNav ? (window.getComputedStyle(bottomNav).display !== 'none') : false;
            const paymentMethods = Array.from(document.querySelectorAll('.wc_payment_method')).map(m => {
                return m.querySelector('label')?.textContent.trim();
            });
            const orderRows = Array.from(document.querySelectorAll('.woocommerce-checkout-review-order-table tbody tr.cart_item')).map(r => ({
                name: r.querySelector('.product-name')?.textContent.trim(),
                total: r.querySelector('.product-total')?.textContent.trim()
            }));
            const orderTotal = document.querySelector('.order-total .woocommerce-Price-amount')?.textContent.trim();

            return {
                currentUrl: window.location.href,
                formPresent: !!form,
                placeOrderText: placeOrderBtn?.value || placeOrderBtn?.textContent.trim(),
                placeOrderHeight: placeOrderRect ? placeOrderRect.height : 0,
                billingPhoneLabel: billingPhone?.closest('.form-row')?.querySelector('label')?.textContent.trim(),
                billingPhonePlaceholder: billingPhone?.getAttribute('placeholder'),
                billingPostcodeLabel: billingPostcode?.closest('.form-row')?.querySelector('label')?.textContent.trim(),
                waHiddenOnCheckout: !waVisible,
                bottomNavHiddenOnCheckout: !bottomNavVisible,
                paymentMethods,
                orderRows,
                orderTotal
            };
        })()`);

        console.log('Mobile Checkout Data (390x844):', checkoutData);
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_checkout_mobile_populated.png'));

        // Scroll to order summary and payment methods
        await evaluate(`document.querySelector('#order_review')?.scrollIntoView();`);
        await new Promise(r => setTimeout(r, 500));
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_checkout_mobile_payment.png'));

        // Desktop Viewport
        await setViewport(1280, 800, false);
        await evaluate(`window.scrollTo(0, 0);`);
        await new Promise(r => setTimeout(r, 500));
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_checkout_desktop_populated.png'));

        fs.writeFileSync(path.join(ARTIFACTS_DIR, 'phase7_checkout_metrics.json'), JSON.stringify(checkoutData, null, 2));

    } catch (e) {
        console.error('Error during checkout test:', e);
    } finally {
        ws.close();
        chrome.kill();
    }
}

main();
