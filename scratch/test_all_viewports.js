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
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-viewports',
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

    const viewports = [
        { name: '360x800', width: 360, height: 800, mobile: true },
        { name: '375x812', width: 375, height: 812, mobile: true },
        { name: '390x844', width: 390, height: 844, mobile: true },
        { name: '393x852', width: 393, height: 852, mobile: true },
        { name: '412x915', width: 412, height: 915, mobile: true },
        { name: 'desktop_1280x800', width: 1280, height: 800, mobile: false }
    ];

    const results = {};

    for (const vp of viewports) {
        console.log(`\n================ Testing Viewport ${vp.name} (${vp.width}x${vp.height}) ================`);
        await send('Emulation.setDeviceMetricsOverride', {
            width: vp.width,
            height: vp.height,
            deviceScaleFactor: vp.mobile ? 2 : 1,
            mobile: vp.mobile
        });

        if (vp.mobile) {
            await send('Emulation.setUserAgentOverride', {
                userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
            });
        } else {
            await send('Emulation.setUserAgentOverride', {
                userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
            });
        }

        await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/' });
        await new Promise(r => setTimeout(r, 3500));

        const metrics = await send('Runtime.evaluate', {
            expression: `(() => {
                const vw = window.innerWidth;
                const vh = window.innerHeight;
                const docScrollWidth = document.documentElement.scrollWidth;
                const bodyScrollWidth = document.body.scrollWidth;

                const getBox = (sel) => {
                    const el = document.querySelector(sel);
                    if (!el) return null;
                    const r = el.getBoundingClientRect();
                    return {
                        width: Math.round(r.width),
                        left: Math.round(r.left),
                        right: Math.round(r.right),
                        height: Math.round(r.height)
                    };
                };

                const overflows = [];
                for (const el of document.querySelectorAll('*')) {
                    const r = el.getBoundingClientRect();
                    const s = window.getComputedStyle(el);
                    if ((r.right > vw + 2 || r.width > vw + 2) && s.display !== 'none' && s.visibility !== 'hidden') {
                        overflows.push({
                            tag: el.tagName,
                            id: el.id,
                            className: (el.className ? String(el.className) : '').substring(0, 40),
                            width: Math.round(r.width),
                            right: Math.round(r.right)
                        });
                    }
                }

                return {
                    vw,
                    vh,
                    docScrollWidth,
                    bodyScrollWidth,
                    overflowCount: overflows.length,
                    overflows: overflows.slice(0, 5),
                    header: getBox('.ayur-header'),
                    hero: getBox('.ayur-hero'),
                    productGrid: getBox('.ayur-products-grid'),
                    footer: getBox('.ayur-footer'),
                    bottomNav: getBox('.ayur-mobile-bottom-nav')
                };
            })()`,
            returnByValue: true
        });

        results[vp.name] = metrics.result.value;
        console.log(`Viewport ${vp.name} result:`, JSON.stringify(metrics.result.value, null, 2));

        const snap = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(`d:\\woocom\\scratch\\vp_${vp.name}.png`, Buffer.from(snap.data, 'base64'));
        console.log(`Saved screenshot vp_${vp.name}.png`);
    }

    // Now test interactive states at 390x844
    console.log(`\n================ Testing Interactive Overlays (Drawer, Search, Cart) at 390x844 ================`);
    await send('Emulation.setDeviceMetricsOverride', {
        width: 390,
        height: 844,
        deviceScaleFactor: 2,
        mobile: true
    });
    await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/' });
    await new Promise(r => setTimeout(r, 3500));

    // 1. Mobile Drawer
    await send('Runtime.evaluate', {
        expression: `document.getElementById('ayurMobileMenuBtn')?.click();`
    });
    await new Promise(r => setTimeout(r, 600));
    let snap = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('d:\\woocom\\scratch\\vp_drawer_open.png', Buffer.from(snap.data, 'base64'));
    console.log('Saved vp_drawer_open.png');

    const drawerCheck = await send('Runtime.evaluate', {
        expression: `(() => {
            const drawer = document.getElementById('ayurMobileDrawer');
            const r = drawer ? drawer.getBoundingClientRect() : null;
            return {
                open: document.getElementById('ayurMobileDrawerWrap')?.classList.contains('is-open'),
                width: r ? Math.round(r.width) : null,
                left: r ? Math.round(r.left) : null,
                right: r ? Math.round(r.right) : null,
                bodyScrollLocked: document.body.classList.contains('ayur-scroll-locked')
            };
        })()`,
        returnByValue: true
    });
    console.log('Drawer check:', drawerCheck.result.value);

    // Close drawer
    await send('Runtime.evaluate', {
        expression: `document.getElementById('ayurCloseMobileDrawer')?.click();`
    });
    await new Promise(r => setTimeout(r, 500));

    // 2. Search Modal
    await send('Runtime.evaluate', {
        expression: `document.getElementById('ayurSearchToggle')?.click();`
    });
    await new Promise(r => setTimeout(r, 600));
    snap = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('d:\\woocom\\scratch\\vp_search_open.png', Buffer.from(snap.data, 'base64'));
    console.log('Saved vp_search_open.png');

    const searchCheck = await send('Runtime.evaluate', {
        expression: `(() => {
            const modal = document.getElementById('ayurSearchModal');
            const r = modal ? modal.getBoundingClientRect() : null;
            return {
                open: modal?.classList.contains('is-open'),
                width: r ? Math.round(r.width) : null,
                height: r ? Math.round(r.height) : null
            };
        })()`,
        returnByValue: true
    });
    console.log('Search check:', searchCheck.result.value);

    // Close search
    await send('Runtime.evaluate', {
        expression: `document.getElementById('ayurCloseSearch')?.click();`
    });
    await new Promise(r => setTimeout(r, 500));

    // 3. Cart Drawer
    await send('Runtime.evaluate', {
        expression: `document.getElementById('ayurHeaderCartBtn')?.click();`
    });
    await new Promise(r => setTimeout(r, 600));
    snap = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('d:\\woocom\\scratch\\vp_cart_open.png', Buffer.from(snap.data, 'base64'));
    console.log('Saved vp_cart_open.png');

    const cartCheck = await send('Runtime.evaluate', {
        expression: `(() => {
            const drawer = document.getElementById('ayurCartDrawer');
            const r = drawer ? drawer.getBoundingClientRect() : null;
            return {
                open: document.getElementById('ayurCartDrawerWrap')?.classList.contains('is-open'),
                width: r ? Math.round(r.width) : null,
                left: r ? Math.round(r.left) : null,
                right: r ? Math.round(r.right) : null
            };
        })()`,
        returnByValue: true
    });
    console.log('Cart check:', cartCheck.result.value);

    // Close cart drawer
    await send('Runtime.evaluate', {
        expression: `document.getElementById('ayurCloseCartDrawer')?.click();`
    });
    await new Promise(r => setTimeout(r, 500));

    // 4. Shop Page at 390x844
    console.log(`\n================ Testing Shop Page at 390x844 ================`);
    await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/shop/' });
    await new Promise(r => setTimeout(r, 3000));
    snap = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('d:\\woocom\\scratch\\screen_shop.png', Buffer.from(snap.data, 'base64'));
    console.log('Saved screen_shop.png');

    // 5. Single Product Page at 390x844
    console.log(`\n================ Testing Single Product Page at 390x844 ================`);
    await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/' });
    await new Promise(r => setTimeout(r, 3000));
    snap = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('d:\\woocom\\scratch\\screen_product.png', Buffer.from(snap.data, 'base64'));
    console.log('Saved screen_product.png');

    // 6. Cart Page at 390x844
    console.log(`\n================ Testing Cart Page at 390x844 ================`);
    await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/cart/' });
    await new Promise(r => setTimeout(r, 3000));
    snap = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('d:\\woocom\\scratch\\screen_cart.png', Buffer.from(snap.data, 'base64'));
    console.log('Saved screen_cart.png');

    fs.writeFileSync('d:\\woocom\\scratch\\multi_viewport_results.json', JSON.stringify({ results, drawerCheck: drawerCheck.result.value, searchCheck: searchCheck.result.value, cartCheck: cartCheck.result.value }, null, 2));

    ws.close();
    chrome.kill();
    console.log('\nAll viewport tests completed successfully!');
}

main().catch(console.error);
