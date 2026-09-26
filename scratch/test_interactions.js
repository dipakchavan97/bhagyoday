const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9222;

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-3',
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

    console.log('Navigating to homepage...');
    await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/' });
    await new Promise(r => setTimeout(r, 3500));

    // 1. Click menu button
    console.log('Clicking mobile menu button...');
    await send('Runtime.evaluate', {
        expression: `(() => {
            const btn = document.getElementById('ayurMobileMenuBtn');
            if (btn) btn.click();
            return {
                btnExists: !!btn,
                drawerWrapClass: document.getElementById('ayurMobileDrawerWrap')?.className,
                drawerRect: document.getElementById('ayurMobileDrawer')?.getBoundingClientRect()
            };
        })()`,
        returnByValue: true
    }).then(r => console.log('Menu click result:', r.result.value));

    await new Promise(r => setTimeout(r, 600));
    let snap = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('d:\\woocom\\scratch\\screen_drawer_open.png', Buffer.from(snap.data, 'base64'));
    console.log('Saved screen_drawer_open.png');

    // Close menu
    await send('Runtime.evaluate', {
        expression: `document.getElementById('ayurCloseMobileDrawer')?.click();`
    });
    await new Promise(r => setTimeout(r, 600));

    // 2. Click cart button
    console.log('Clicking cart button...');
    await send('Runtime.evaluate', {
        expression: `(() => {
            const btn = document.getElementById('ayurHeaderCartBtn');
            if (btn) btn.click();
            return {
                btnExists: !!btn,
                cartWrapClass: document.getElementById('ayurCartDrawerWrap')?.className,
                cartRect: document.getElementById('ayurCartDrawer')?.getBoundingClientRect()
            };
        })()`,
        returnByValue: true
    }).then(r => console.log('Cart click result:', r.result.value));

    await new Promise(r => setTimeout(r, 600));
    snap = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('d:\\woocom\\scratch\\screen_cart_open.png', Buffer.from(snap.data, 'base64'));
    console.log('Saved screen_cart_open.png');

    ws.close();
    chrome.kill();
}

main().catch(console.error);
