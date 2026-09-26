const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');

async function testPage(ws, send, path, name) {
    console.log(`\n================ Testing ${path} (${name}) ================`);
    await send('Page.navigate', { url: `https://lightgreen-heron-113083.hostingersite.com${path}` });
    await new Promise(r => setTimeout(r, 3500));

    const evalResult = await send('Runtime.evaluate', {
        expression: `(() => {
            const vw = window.innerWidth;
            const overflows = [];
            for (const el of document.querySelectorAll('*')) {
                const rect = el.getBoundingClientRect();
                const style = window.getComputedStyle(el);
                if ((rect.right > vw + 2 || rect.width > vw + 2) && style.display !== 'none' && style.visibility !== 'hidden') {
                    overflows.push({
                        tag: el.tagName,
                        id: el.id,
                        className: (el.className ? String(el.className) : '').substring(0, 50),
                        width: Math.round(rect.width),
                        right: Math.round(rect.right),
                        left: Math.round(rect.left),
                        scrollWidth: el.scrollWidth,
                        outerHTML: el.outerHTML.substring(0, 100)
                    });
                }
            }
            return {
                docScrollWidth: document.documentElement.scrollWidth,
                bodyScrollWidth: document.body.scrollWidth,
                windowWidth: vw,
                overflowCount: overflows.length,
                overflows: overflows.slice(0, 10)
            };
        })()`,
        returnByValue: true
    });

    console.log('Result:', JSON.stringify(evalResult.result.value, null, 2));

    const screenshot = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync(`d:\\woocom\\scratch\\screen_${name}.png`, Buffer.from(screenshot.data, 'base64'));
    console.log(`Saved d:\\woocom\\scratch\\screen_${name}.png`);
}

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9222;

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-2',
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

    await send('Emulation.setDeviceMetricsOverride', {
        width: 390,
        height: 844,
        deviceScaleFactor: 2,
        mobile: true
    });
    await send('Emulation.setUserAgentOverride', {
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
    });

    await testPage(ws, send, '/', 'home');
    await testPage(ws, send, '/shop/', 'shop');
    await testPage(ws, send, '/product/ashwagandha-shilajit-men-wellness-capsules/', 'product');
    await testPage(ws, send, '/cart/', 'cart');
    await testPage(ws, send, '/checkout/', 'checkout');

    ws.close();
    chrome.kill();
}

main().catch(console.error);
