const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9222;

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-phase3',
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

    async function captureScreenshot(filepath) {
        const { data } = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(filepath, Buffer.from(data, 'base64'));
        console.log(`Saved screenshot: ${filepath}`);
    }

    const viewports = [
        { name: '360x800', width: 360, height: 800, mobile: true },
        { name: '375x812', width: 375, height: 812, mobile: true },
        { name: '390x844', width: 390, height: 844, mobile: true },
        { name: '393x852', width: 393, height: 852, mobile: true },
        { name: '412x915', width: 412, height: 915, mobile: true },
        { name: 'desktop_1280x800', width: 1280, height: 800, mobile: false }
    ];

    console.log('\n--- 1. AUDITING HOMEPAGE CONVERSION FLOW ACROSS VIEWPORTS ---');

    for (const vp of viewports) {
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

        await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/?nocache=' + Date.now() });
        await new Promise(r => setTimeout(r, 4000));

        const audit = await send('Runtime.evaluate', {
            expression: `(() => {
                const docScrollWidth = document.documentElement.scrollWidth;
                const overflow = docScrollWidth - window.innerWidth;

                const q = (sel) => document.querySelector(sel);
                const qAll = (sel) => Array.from(document.querySelectorAll(sel));

                return {
                    viewport: { w: window.innerWidth, h: window.innerHeight },
                    overflow: overflow,
                    announcement: q('.ayur-announcement-text') ? q('.ayur-announcement-text').innerText.trim() : null,
                    heroTitle: q('.ayur-hero-title') ? q('.ayur-hero-title').innerText.replace(/\\s+/g, ' ').trim() : null,
                    heroPrimaryCTA: q('.ayur-hero-actions .ayur-btn-primary') ? q('.ayur-hero-actions .ayur-btn-primary').innerText.trim() : null,
                    heroSecondaryCTA: q('.ayur-hero-actions .ayur-btn-secondary') ? q('.ayur-hero-actions .ayur-btn-secondary').innerText.trim() : null,
                    salesModuleTitle: q('.ayur-hero-sales-title') ? q('.ayur-hero-sales-title').innerText.trim() : null,
                    salesModulePrice: q('.ayur-hero-price') ? q('.ayur-hero-price').innerText.trim() : null,
                    salesModuleMRP: q('.ayur-hero-mrp') ? q('.ayur-hero-mrp').innerText.trim() : null,
                    salesModuleSave: q('.ayur-hero-save-tag') ? q('.ayur-hero-save-tag').innerText.trim() : null,
                    microTrustItems: qAll('.ayur-micro-trust-item').map(el => el.innerText.trim()),
                    whyPillars: qAll('.ayur-why-title').map(el => el.innerText.trim()),
                    wellnessCategories: qAll('.ayur-cat-block .ayur-cat-title').map(el => el.innerText.trim()),
                    categoryShopLinks: qAll('.ayur-cat-block .ayur-cat-link-text span:first-child').map(el => el.innerText.trim()),
                    bestsellerCount: qAll('#ayurBestsellers .ayur-product-card').length,
                    botanicals: qAll('.ayur-ingredient-card .ayur-ingredient-name').map(el => el.innerText.trim()),
                    bundlesCount: qAll('#ayurBundles .ayur-bundle-card').length,
                    howToChooseRows: qAll('.ayur-choose-row .ayur-choose-name').map(el => el.innerText.trim()),
                    faqCount: qAll('.ayur-faq-details').length,
                    trustBlocks: qAll('.ayur-trust-block-title').map(el => el.innerText.trim()),
                    finalCTATitle: q('.ayur-final-cta-title') ? q('.ayur-final-cta-title').innerText.trim() : null
                };
            })()`,
            returnByValue: true
        });

        console.log(`\nViewport ${vp.name}: Overflow = ${audit.result.value.overflow}px`);
        console.log('Audit Summary:', JSON.stringify(audit.result.value, null, 2));

        const ssPath = path.join(ARTIFACTS_DIR, `phase3_home_${vp.name}.png`);
        await captureScreenshot(ssPath);
    }

    console.log('\n--- 2. AUDITING SINGLE PRODUCT PAGE (CONVERSION-FIRST) ---');
    await send('Emulation.setDeviceMetricsOverride', {
        width: 390,
        height: 844,
        deviceScaleFactor: 2,
        mobile: true
    });
    await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/' });
    await new Promise(r => setTimeout(r, 4000));

    const prodAudit = await send('Runtime.evaluate', {
        expression: `(() => {
            const q = (sel) => document.querySelector(sel);
            const qAll = (sel) => Array.from(document.querySelectorAll(sel));
            return {
                title: q('.ayur-single-title') ? q('.ayur-single-title').innerText.trim() : null,
                price: q('.ayur-single-price') ? q('.ayur-single-price').innerText.trim() : null,
                mrp: q('.ayur-single-mrp') ? q('.ayur-single-mrp').innerText.trim() : null,
                savings: q('.ayur-single-savings') ? q('.ayur-single-savings').innerText.trim() : null,
                stockBadge: q('.ayur-stock-badge') ? q('.ayur-stock-badge').innerText.trim() : null,
                addToCartBtn: q('.ayur-btn-add-cart') ? q('.ayur-btn-add-cart').innerText.trim() : null,
                buyNowBtn: q('.ayur-btn-buy-now') ? q('.ayur-btn-buy-now').innerText.trim() : null,
                trustMicrocopy: q('.ayur-product-trust-microcopy') ? q('.ayur-product-trust-microcopy').innerText.trim() : null,
                benefitsCount: qAll('.ayur-benefits-checklist li').length,
                hasIngredients: !!q('.ayur-ingredients-content'),
                hasDosage: !!q('.ayur-spec-card'),
                stickyBarPrice: q('.ayur-sticky-price') ? q('.ayur-sticky-price').innerText.trim() : null,
                overflow: document.documentElement.scrollWidth - window.innerWidth
            };
        })()`,
        returnByValue: true
    });
    console.log('Product Page Audit:', JSON.stringify(prodAudit.result.value, null, 2));
    await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase3_single_product_390x844.png'));

    console.log('\n--- 3. TESTING CART DRAWER & EMPTY CART RECOVERY ---');
    // Open empty cart
    await send('Runtime.evaluate', { expression: `document.getElementById('ayurHeaderCartBtn').click();` });
    await new Promise(r => setTimeout(r, 800));

    const emptyCartAudit = await send('Runtime.evaluate', {
        expression: `(() => {
            const q = (sel) => document.querySelector(sel);
            const qAll = (sel) => Array.from(document.querySelectorAll(sel));
            return {
                isOpen: q('#ayurCartDrawerWrap').classList.contains('is-open'),
                emptyTitle: q('.ayur-drawer-empty-state h4') ? q('.ayur-drawer-empty-state h4').innerText.trim() : null,
                recoveryTitle: q('.ayur-drawer-recovery-title') ? q('.ayur-drawer-recovery-title').innerText.trim() : null,
                recoveryItems: qAll('.ayur-drawer-rec-name').map(el => el.innerText.trim())
            };
        })()`,
        returnByValue: true
    });
    console.log('Empty Cart Audit:', JSON.stringify(emptyCartAudit.result.value, null, 2));
    await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase3_cart_empty_with_recovery.png'));

    console.log('\n--- 4. TESTING QUICK ADD TO CART & POPULATED CART DRAWER ---');
    // Add recovery product to cart
    await send('Runtime.evaluate', { expression: `document.querySelector('.ayur-drawer-rec-add-btn').click();` });
    await new Promise(r => setTimeout(r, 2500));

    const filledCartAudit = await send('Runtime.evaluate', {
        expression: `(() => {
            const q = (sel) => document.querySelector(sel);
            const qAll = (sel) => Array.from(document.querySelectorAll(sel));
            return {
                isOpen: q('#ayurCartDrawerWrap').classList.contains('is-open'),
                itemCount: qAll('.ayur-drawer-item').length,
                itemName: q('.ayur-drawer-item-name') ? q('.ayur-drawer-item-name').innerText.trim() : null,
                shippingText: q('.ayur-drawer-shipping-text') ? q('.ayur-drawer-shipping-text').innerText.trim() : null,
                subtotal: q('.ayur-drawer-breakdown-row .val') ? q('.ayur-drawer-breakdown-row .val').innerText.trim() : null,
                hasCheckoutBtn: !!q('.ayur-btn-drawer-checkout'),
                hasContinueBtn: !!q('#ayurContinueShopping')
            };
        })()`,
        returnByValue: true
    });
    console.log('Filled Cart Audit:', JSON.stringify(filledCartAudit.result.value, null, 2));
    await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase3_cart_filled.png'));

    console.log('\n--- 5. TESTING CHECKOUT PAGE & PAYMENT METHODS ---');
    await send('Page.navigate', { url: 'https://lightgreen-heron-113083.hostingersite.com/checkout/' });
    await new Promise(r => setTimeout(r, 4500));

    const checkoutAudit = await send('Runtime.evaluate', {
        expression: `(() => {
            const q = (sel) => document.querySelector(sel);
            const qAll = (sel) => Array.from(document.querySelectorAll(sel));
            return {
                title: document.title,
                overflow: document.documentElement.scrollWidth - window.innerWidth,
                hasOrderReview: !!q('#order_review'),
                orderTotal: q('.order-total .amount') ? q('.order-total .amount').innerText.trim() : null,
                paymentMethods: qAll('ul.wc_payment_methods li label').map(el => el.innerText.trim()),
                placeOrderBtn: q('#place_order') ? q('#place_order').value : null,
                billingPhonePlaceholder: q('#billing_phone') ? q('#billing_phone').placeholder : null
            };
        })()`,
        returnByValue: true
    });
    console.log('Checkout Page Audit:', JSON.stringify(checkoutAudit.result.value, null, 2));
    await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase3_checkout_390x844.png'));

    ws.close();
    chrome.kill();
    console.log('\n--- ALL PHASE 3 TESTING COMPLETED SUCCESSFULLY ---');
}

main().catch(err => {
    console.error('Error during execution:', err);
    process.exit(1);
});
