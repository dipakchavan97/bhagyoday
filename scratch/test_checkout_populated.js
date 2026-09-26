const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9223;

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-checkout',
        'about:blank'
    ]);

    await new Promise(r => setTimeout(r, 1500));

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

    async function navigate(url) {
        await send('Page.navigate', { url });
        await new Promise(r => setTimeout(r, 3000));
    }

    async function captureScreenshot(filepath) {
        const { data } = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(filepath, Buffer.from(data, 'base64'));
    }

    async function evaluate(expression) {
        const res = await send('Runtime.evaluate', {
            expression,
            returnByValue: true,
            awaitPromise: true
        });
        return res.result.value;
    }

    try {
        await setViewport(390, 844, true);

        console.log('1. Quick buy item 30 to add to cart and seed session...');
        await navigate('https://lightgreen-heron-113083.hostingersite.com/?ayur_quick_buy=30');

        console.log('2. Current URL after quick buy:');
        const url1 = await evaluate('window.location.href');
        console.log('URL:', url1);

        console.log('3. Navigating explicitly to checkout...');
        await navigate('https://lightgreen-heron-113083.hostingersite.com/checkout/');

        // Scroll down to reveal payment methods and order total
        await evaluate(`window.scrollBy(0, 600)`);
        await new Promise(r => setTimeout(r, 1000));

        const checkoutData = await evaluate(`(() => {
            const bodyText = document.body.innerText;
            const hasRazorpay = bodyText.includes('Razorpay') || bodyText.includes('Cards, NetBanking, Wallet & UPI') || bodyText.includes('UPI');
            const hasCOD = bodyText.includes('Cash on delivery') || bodyText.includes('COD') || bodyText.includes('Cash on Delivery');
            const buttons = Array.from(document.querySelectorAll('button')).map(b => b.textContent.trim());
            const inputs = Array.from(document.querySelectorAll('input[type="radio"]')).map(r => ({ name: r.name, value: r.value, id: r.id }));

            return {
                url: window.location.href,
                hasRazorpay,
                hasCOD,
                buttons,
                radioInputs: inputs
            };
        })()`);

        console.log('Populated Checkout Payment Data:', JSON.stringify(checkoutData, null, 2));

        const screenshotPath = path.join(ARTIFACTS_DIR, 'phase3_checkout_populated_scrolled.png');
        await captureScreenshot(screenshotPath);
        console.log(`Saved screenshot to ${screenshotPath}`);

    } catch (err) {
        console.error('Test error:', err);
    } finally {
        ws.close();
        chrome.kill();
    }
}

main().catch(console.error);
