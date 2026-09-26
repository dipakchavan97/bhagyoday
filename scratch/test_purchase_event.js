const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';

async function main() {
    console.log('=== VERIFYING PURCHASE EVENT & META/GA4 DEDUPLICATION ===\n');

    // 1. Create a test order via our secure probe
    const createRes = await fetch('https://lightgreen-heron-113083.hostingersite.com/?ayur_test_probe_action=create_test_order&key=ayur_audit_test_9921');
    const createJson = await createRes.json();
    console.log('Test Order Created:', createJson);

    if (!createJson.success || !createJson.data) {
        throw new Error('Failed to create test order: ' + JSON.stringify(createJson));
    }

    const { order_id, thank_you_url } = createJson.data;
    console.log(`Order ID: ${order_id}`);
    console.log(`Thank You URL: ${thank_you_url}`);

    // Launch Chrome CDP to inspect actual browser runtime execution
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9226;

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-purchase',
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

    async function evaluate(expression) {
        const res = await send('Runtime.evaluate', {
            expression,
            returnByValue: true,
            awaitPromise: true
        });
        return res ? res.result.value : null;
    }

    try {
        // 2. Navigate to Thank You Page for the First Time
        console.log('\n2. Navigating to Thank You page for FIRST visit...');
        await send('Page.navigate', { url: thank_you_url });
        await new Promise(r => setTimeout(r, 3500));

        const firstVisit = await evaluate(`(() => {
            const body = document.body.innerText;
            const dataLayer = window.dataLayer || [];
            const purchaseEvent = dataLayer.find(e => e.event === 'purchase');
            const pageHtml = document.documentElement.outerHTML;

            // Check for Meta Pixel Purchase script call
            const hasMetaPurchase = pageHtml.includes("fbq('track', 'Purchase'");
            const eventIdMatch = pageHtml.match(/eventID:\\s*'([^']+)'/);

            return {
                title: document.title,
                hasPurchaseEvent: !!purchaseEvent,
                purchaseEvent,
                hasMetaPurchase,
                metaEventId: eventIdMatch ? eventIdMatch[1] : null,
                isOrderReceived: body.includes('Thank you. Your order has been received') || body.includes('Order received') || body.includes('Order details')
            };
        })()`);

        console.log('First Visit Purchase Tracking Results:\n', JSON.stringify(firstVisit, null, 2));

        // Screenshot Thank You page
        const { data: shotData } = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(path.join(ARTIFACTS_DIR, 'phase4_thank_you_page.png'), Buffer.from(shotData, 'base64'));
        console.log('Saved screenshot: phase4_thank_you_page.png');

        // 3. Reload the Page to Verify Deduplication (Must NOT fire a second time)
        console.log('\n3. Reloading Thank You page to test Deduplication...');
        await send('Page.reload');
        await new Promise(r => setTimeout(r, 3500));

        const secondVisit = await evaluate(`(() => {
            const dataLayer = window.dataLayer || [];
            const purchaseEvents = dataLayer.filter(e => e.event === 'purchase');
            const pageHtml = document.documentElement.outerHTML;
            const hasMetaPurchase = pageHtml.includes("fbq('track', 'Purchase'");

            return {
                purchaseEventCount: purchaseEvents.length,
                hasMetaPurchaseOnReload: hasMetaPurchase
            };
        })()`);

        console.log('Second Visit (Reload) Deduplication Results:\n', JSON.stringify(secondVisit, null, 2));

        const deduplicated = secondVisit.purchaseEventCount === 0 && !secondVisit.hasMetaPurchaseOnReload;
        console.log(`\n=> DEDUPLICATION VERIFIED: ${deduplicated ? 'PASSED (0 duplicate events on reload)' : 'FAILED'}`);

    } finally {
        ws.close();
        chrome.kill();

        // 4. Clean up the test order
        console.log(`\n4. Cleaning up test order ID ${order_id}...`);
        const cleanupRes = await fetch(`https://lightgreen-heron-113083.hostingersite.com/?ayur_test_probe_action=cleanup_test_order&key=ayur_audit_test_9921&order_id=${order_id}`);
        const cleanupJson = await cleanupRes.json();
        console.log('Cleanup result:', cleanupJson);
    }
}

main().catch(console.error);
