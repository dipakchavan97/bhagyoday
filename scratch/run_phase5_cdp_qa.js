const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';

const VIEWPORTS = [
    { name: '360x800', width: 360, height: 800, isMobile: true },
    { name: '390x844', width: 390, height: 844, isMobile: true },
    { name: '412x915', width: 412, height: 915, isMobile: true },
    { name: '1280x800', width: 1280, height: 800, isMobile: false }
];

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9229;

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-phase5-qa',
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
        await new Promise(r => setTimeout(r, 3500));
    }

    async function evaluate(expression) {
        const res = await send('Runtime.evaluate', {
            expression,
            returnByValue: true,
            awaitPromise: true
        });
        return res ? res.result.value : null;
    }

    async function captureScreenshot(filepath) {
        const { data } = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(filepath, Buffer.from(data, 'base64'));
    }

    const qaReport = {
        shop: {},
        product: {},
        conversion: {},
        ksmCheck: false
    };

    console.log('--- STARTING PHASE 5 CDP QA ---');

    for (const vp of VIEWPORTS) {
        console.log(`\n=================== TESTING VIEWPORT ${vp.name} ===================`);
        await setViewport(vp.width, vp.height, vp.isMobile);

        // 1. SHOP PAGE
        await navigate('https://lightgreen-heron-113083.hostingersite.com/shop/');
        const shopData = await evaluate(`(() => {
            const banner = document.querySelector('.ayur-archive-banner');
            const h1 = document.querySelector('.ayur-archive-title');
            const pillsRow = document.querySelector('.ayur-cat-pills-row');
            const pills = Array.from(document.querySelectorAll('.ayur-cat-pill'));
            const controls = document.querySelector('.ayur-archive-controls');
            const grid = document.querySelector('.ayur-products-grid');
            const firstCard = grid ? grid.querySelector('.ayur-product-card') : null;

            return {
                bannerHeight: banner ? banner.offsetHeight : 0,
                bannerPadding: banner ? window.getComputedStyle(banner).padding : '',
                h1FontSize: h1 ? window.getComputedStyle(h1).fontSize : '',
                pillsHeight: pillsRow ? pillsRow.offsetHeight : 0,
                pillsWrap: pillsRow ? window.getComputedStyle(pillsRow).flexWrap : '',
                pillsCount: pills.length,
                controlsTop: controls ? controls.getBoundingClientRect().top : 0,
                firstCardTop: firstCard ? firstCard.getBoundingClientRect().top : 0,
                hasOverflow: document.documentElement.scrollWidth > window.innerWidth,
                scrollWidth: document.documentElement.scrollWidth,
                innerWidth: window.innerWidth
            };
        })()`);

        qaReport.shop[vp.name] = shopData;
        console.log(`[SHOP ${vp.name}] Banner Height: ${shopData.bannerHeight}px (Target: ${vp.isMobile ? '260-320px' : '250-300px'})`);
        console.log(`[SHOP ${vp.name}] Pills Height: ${shopData.pillsHeight}px | Wrap: ${shopData.pillsWrap}`);
        console.log(`[SHOP ${vp.name}] First Card Top: ${shopData.firstCardTop}px | Overflow: ${shopData.hasOverflow}`);

        await captureScreenshot(path.join(ARTIFACTS_DIR, `phase5_shop_${vp.name}.png`));

        // 2. PRODUCT PAGE
        await navigate('https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/');
        const prodData = await evaluate(`(() => {
            const wrap = document.querySelector('.ayur-product-details-wrap');
            const whyChoose = document.querySelector('.ayur-section-why');
            const botanicals = Array.from(document.querySelectorAll('.ayur-botanical-card'));
            const usageSteps = Array.from(document.querySelectorAll('.ayur-usage-step'));
            const specs = document.querySelector('.ayur-spec-table');
            const specRows = specs ? Array.from(specs.querySelectorAll('tr')).map(r => ({
                th: r.querySelector('th') ? r.querySelector('th').innerText.trim() : '',
                td: r.querySelector('td') ? r.querySelector('td').innerText.trim() : ''
            })) : [];
            const shipping = document.querySelector('.ayur-shipping-card');
            const caution = document.querySelector('.ayur-caution-card');
            const relWrap = document.querySelector('.ayur-related-wrap');
            const relHeader = document.querySelector('.ayur-related-header');
            const relGrid = document.querySelector('.ayur-related-grid');
            const relCards = relGrid ? Array.from(relGrid.querySelectorAll('.ayur-product-card')).map(c => {
                const b = c.getBoundingClientRect();
                const title = c.querySelector('.ayur-product-title');
                const price = c.querySelector('.ayur-price');
                const btn = c.querySelector('.ayur-card-btn');
                return {
                    title: title ? title.innerText.trim() : '',
                    price: price ? price.innerText.trim() : '',
                    hasBtn: !!btn,
                    width: b.width,
                    height: b.height
                };
            }) : [];

            const ksmInBody = /KSM-?66/i.test(document.body.innerText);

            return {
                wrapWidth: wrap ? wrap.offsetWidth : 0,
                whyChooseOpen: whyChoose ? whyChoose.hasAttribute('open') : false,
                botanicalsCount: botanicals.length,
                botanicals: botanicals.map(b => ({
                    name: b.querySelector('.ayur-botanical-name') ? b.querySelector('.ayur-botanical-name').innerText.trim() : '',
                    latin: b.querySelector('.ayur-botanical-latin') ? b.querySelector('.ayur-botanical-latin').innerText.trim() : ''
                })),
                usageStepsCount: usageSteps.length,
                specRowsCount: specRows.length,
                specRows: specRows,
                hasShippingCard: !!shipping,
                hasCautionCard: !!caution,
                cautionText: caution ? caution.innerText.trim() : '',
                relCardsCount: relCards.length,
                relGridWidth: relGrid ? relGrid.offsetWidth : 0,
                relCards: relCards,
                ksmFound: ksmInBody,
                hasOverflow: document.documentElement.scrollWidth > window.innerWidth
            };
        })()`);

        qaReport.product[vp.name] = prodData;
        console.log(`[PROD ${vp.name}] Botanicals: ${prodData.botanicalsCount} | Usage Steps: ${prodData.usageStepsCount} | Spec Rows: ${prodData.specRowsCount}`);
        console.log(`[PROD ${vp.name}] Related Cards: ${prodData.relCardsCount} | First Card Width: ${prodData.relCards[0] ? prodData.relCards[0].width + 'px' : 'none'} (Grid Width: ${prodData.relGridWidth}px)`);
        console.log(`[PROD ${vp.name}] KSM-66 Found: ${prodData.ksmFound} | Overflow: ${prodData.hasOverflow}`);

        // Capture details screenshot
        await evaluate(`(() => {
            const wrap = document.querySelector('.ayur-product-details-wrap');
            if (wrap) wrap.scrollIntoView();
        })()`);
        await new Promise(r => setTimeout(r, 600));
        await captureScreenshot(path.join(ARTIFACTS_DIR, `phase5_prod_details_${vp.name}.png`));

        // Capture related formulations screenshot
        await evaluate(`(() => {
            const rel = document.querySelector('.ayur-related-wrap');
            if (rel) rel.scrollIntoView();
        })()`);
        await new Promise(r => setTimeout(r, 600));
        await captureScreenshot(path.join(ARTIFACTS_DIR, `phase5_prod_related_${vp.name}.png`));
    }

    // 3. CONVERSION TEST: ADD TO CART & DRAWER
    console.log('\n=================== TESTING CONVERSION (MOBILE 390x844) ===================');
    await setViewport(390, 844, true);
    await navigate('https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/');

    await evaluate(`(() => {
        const btn = document.querySelector('.single_add_to_cart_button');
        if (btn) btn.click();
    })()`);
    await new Promise(r => setTimeout(r, 3500));

    // Open drawer via header cart button to verify populated drawer state
    await evaluate(`(() => {
        const cartBtn = document.getElementById('ayurHeaderCartBtn');
        if (cartBtn) cartBtn.click();
    })()`);
    await new Promise(r => setTimeout(r, 1200));

    const conversionData = await evaluate(`(() => {
        const drawerWrap = document.querySelector('.ayur-cart-drawer-wrap');
        const isOpen = drawerWrap && drawerWrap.classList.contains('is-open');
        const items = Array.from(document.querySelectorAll('.ayur-drawer-item')).map(item => ({
            title: item.querySelector('.ayur-drawer-item-title') ? item.querySelector('.ayur-drawer-item-title').innerText.trim() : '',
            price: item.querySelector('.ayur-drawer-item-price') ? item.querySelector('.ayur-drawer-item-price').innerText.trim() : ''
        }));
        const total = document.querySelector('.ayur-drawer-total-row .val') ? document.querySelector('.ayur-drawer-total-row .val').innerText.trim() : '';
        const checkoutBtn = document.querySelector('.ayur-btn-drawer-checkout');

        return {
            drawerOpen: isOpen,
            itemsCount: items.length,
            items: items,
            total: total,
            hasCheckoutBtn: !!checkoutBtn
        };
    })()`);

    qaReport.conversion = conversionData;
    console.log(`[CONVERSION] Drawer Open: ${conversionData.drawerOpen} | Items: ${conversionData.itemsCount} | Total: ${conversionData.total} | Has Checkout Btn: ${conversionData.hasCheckoutBtn}`);

    await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase5_cart_drawer_390.png'));

    // Save final report
    fs.writeFileSync('d:\\woocom\\scratch\\phase5_cdp_qa_results.json', JSON.stringify(qaReport, null, 2));

    ws.close();
    chrome.kill();
    console.log('\n--- ALL PHASE 5 TESTS COMPLETED SUCCESSFULLY ---');
}

main().catch(err => {
    console.error('Test error:', err);
    process.exit(1);
});
