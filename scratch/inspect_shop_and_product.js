const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9227;

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-phase5-inspect',
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

    async function evaluate(expression) {
        const res = await send('Runtime.evaluate', {
            expression,
            returnByValue: true,
            awaitPromise: true
        });
        return res ? res.result.value : null;
    }

    try {
        // Inspect Shop Desktop 1280x800
        await setViewport(1280, 800, false);
        await navigate('https://lightgreen-heron-113083.hostingersite.com/shop/');
        const shopDesktop = await evaluate(`(() => {
            const banner = document.querySelector('.ayur-archive-banner');
            const h1 = document.querySelector('.ayur-archive-title');
            const pillsRow = document.querySelector('.ayur-cat-pills-row');
            const controls = document.querySelector('.ayur-archive-controls');
            const grid = document.querySelector('.ayur-products-grid');

            return {
                bannerHeight: banner ? banner.offsetHeight : 0,
                bannerPadding: banner ? window.getComputedStyle(banner).padding : '',
                h1FontSize: h1 ? window.getComputedStyle(h1).fontSize : '',
                pillsHeight: pillsRow ? pillsRow.offsetHeight : 0,
                firstCardTop: grid ? grid.getBoundingClientRect().top : 0
            };
        })()`);
        console.log('Shop Desktop 1280x800:', shopDesktop);

        const { data: dShot } = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(path.join(ARTIFACTS_DIR, 'phase5_current_shop_desktop.png'), Buffer.from(dShot, 'base64'));

        // Inspect Shop Mobile 390x844
        await setViewport(390, 844, true);
        await navigate('https://lightgreen-heron-113083.hostingersite.com/shop/');
        const shopMobile = await evaluate(`(() => {
            const banner = document.querySelector('.ayur-archive-banner');
            const h1 = document.querySelector('.ayur-archive-title');
            const pillsRow = document.querySelector('.ayur-cat-pills-row');
            const grid = document.querySelector('.ayur-products-grid');

            return {
                bannerHeight: banner ? banner.offsetHeight : 0,
                bannerPadding: banner ? window.getComputedStyle(banner).padding : '',
                h1FontSize: h1 ? window.getComputedStyle(h1).fontSize : '',
                pillsHeight: pillsRow ? pillsRow.offsetHeight : 0,
                firstCardTop: grid ? grid.getBoundingClientRect().top : 0
            };
        })()`);
        console.log('Shop Mobile 390x844:', shopMobile);

        const { data: mShot } = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(path.join(ARTIFACTS_DIR, 'phase5_current_shop_mobile.png'), Buffer.from(mShot, 'base64'));

        // Inspect Product Page Desktop 1280x800 (especially Related Products)
        await setViewport(1280, 800, false);
        await navigate('https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/');
        const productDesktop = await evaluate(`(() => {
            const relatedWrap = document.querySelector('.ayur-related-wrap');
            const relatedProds = Array.from(document.querySelectorAll('.ayur-related-wrap .product, .ayur-related-wrap .ayur-product-card'));
            const firstRelCard = relatedProds[0];

            return {
                relatedWrapWidth: relatedWrap ? relatedWrap.offsetWidth : 0,
                relatedCount: relatedProds.length,
                firstCardWidth: firstRelCard ? firstRelCard.offsetWidth : 0,
                firstCardParentWidth: firstRelCard ? firstRelCard.parentElement.offsetWidth : 0,
                firstCardClasses: firstRelCard ? firstRelCard.className : ''
            };
        })()`);
        console.log('Product Desktop 1280x800 Related Prods:', productDesktop);

        const { data: pShot } = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(path.join(ARTIFACTS_DIR, 'phase5_current_product_desktop.png'), Buffer.from(pShot, 'base64'));

    } finally {
        ws.close();
        chrome.kill();
    }
}

main().catch(console.error);
