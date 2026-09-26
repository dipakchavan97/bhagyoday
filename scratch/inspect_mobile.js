const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9222;

    // Launch Chrome headless
    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data',
        'about:blank'
    ]);

    await new Promise(r => setTimeout(r, 1500));

    // Get websocket URL
    const targets = await new Promise((resolve, reject) => {
        http.get(`http://127.0.0.1:${port}/json/list`, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => resolve(JSON.parse(data)));
        }).on('error', reject);
    });

    const pageTarget = targets.find(t => t.type === 'page') || targets[0];
    const wsUrl = pageTarget.webSocketDebuggerUrl;
    console.log('Connecting to:', wsUrl);

    const ws = new WebSocket(wsUrl);
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

    // Emulate Mobile iPhone 14 / standard 390x844
    await send('Emulation.setDeviceMetricsOverride', {
        width: 390,
        height: 844,
        deviceScaleFactor: 2,
        mobile: true
    });
    await send('Emulation.setUserAgentOverride', {
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
    });

    console.log('Navigating to live site...');
    await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/' });

    // Wait for network/load
    await new Promise(r => setTimeout(r, 4000));

    // Evaluate layout
    const evalResult = await send('Runtime.evaluate', {
        expression: `(() => {
            const docEl = document.documentElement;
            const body = document.body;
            const vw = window.innerWidth;
            const vh = window.innerHeight;

            const overflows = [];
            const allElements = document.querySelectorAll('*');
            for (const el of allElements) {
                const rect = el.getBoundingClientRect();
                const style = window.getComputedStyle(el);
                if (rect.right > vw + 1 || rect.width > vw + 1) {
                    if (style.display !== 'none' && style.visibility !== 'hidden') {
                        overflows.push({
                            tag: el.tagName,
                            id: el.id,
                            className: el.className ? String(el.className).substring(0, 80) : '',
                            width: Math.round(rect.width),
                            right: Math.round(rect.right),
                            left: Math.round(rect.left),
                            scrollWidth: el.scrollWidth,
                            computedWidth: style.width,
                            computedMaxWidth: style.maxWidth,
                            computedMargin: style.margin,
                            computedPadding: style.padding,
                            boxSizing: style.boxSizing
                        });
                    }
                }
            }

            // Key landmark containers
            const landmarks = {};
            const selectors = [
                'html', 'body', '#primary', '.site-main',
                '.ayur-announcement-bar', '.ayur-header', '.ayur-header-inner',
                '.ayur-hero', '.ayur-container', '.ayur-hero-grid',
                '.ayur-trust-rail', '.ayur-categories-grid', '.ayur-products-grid',
                '.ayur-footer', '.ayur-mobile-bottom-nav', '.ayur-mobile-drawer',
                '#ayurSearchModal'
            ];
            for (const sel of selectors) {
                const el = document.querySelector(sel);
                if (el) {
                    const r = el.getBoundingClientRect();
                    const s = window.getComputedStyle(el);
                    landmarks[sel] = {
                        width: Math.round(r.width),
                        left: Math.round(r.left),
                        right: Math.round(r.right),
                        scrollWidth: el.scrollWidth,
                        computedWidth: s.width,
                        computedMaxWidth: s.maxWidth,
                        boxSizing: s.boxSizing,
                        transform: s.transform,
                        zoom: s.zoom
                    };
                }
            }

            return {
                windowInnerWidth: vw,
                docClientWidth: docEl.clientWidth,
                docScrollWidth: docEl.scrollWidth,
                bodyClientWidth: body.clientWidth,
                bodyScrollWidth: body.scrollWidth,
                landmarks,
                overflowCount: overflows.length,
                topOverflows: overflows.slice(0, 20)
            };
        })()`,
        returnByValue: true
    });

    console.log('--- DIAGNOSTIC RESULT ---');
    console.log(JSON.stringify(evalResult.result.value, null, 2));

    // Full page screenshot
    const metrics = await send('Page.getLayoutMetrics');
    const contentSize = metrics.contentSize;
    await send('Emulation.setDeviceMetricsOverride', {
        width: 390,
        height: Math.ceil(contentSize.height),
        deviceScaleFactor: 2,
        mobile: true
    });
    const fullScreenshot = await send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true
    });
    fs.writeFileSync('d:\\woocom\\scratch\\mobile_full_page.png', Buffer.from(fullScreenshot.data, 'base64'));
    console.log('Full page screenshot saved to d:\\woocom\\scratch\\mobile_full_page.png (height:', contentSize.height, ')');

    ws.close();
    chrome.kill();
}

main().catch(err => {
    console.error('Error:', err);
    process.exit(1);
});
