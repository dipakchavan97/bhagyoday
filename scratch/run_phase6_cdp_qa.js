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
    const port = 9230;

    console.log('--- STARTING PHASE 6 CDP AUTOMATED QA ---');

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-phase6-qa',
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
    await send('Network.clearBrowserCache');
    await send('Network.setCacheDisabled', { cacheDisabled: true });

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
        if (res && res.exceptionDetails) {
            console.error('Eval Exception Details:', res.exceptionDetails);
        }
        return res && res.result ? res.result.value : null;
    }

    async function captureScreenshot(filepath) {
        const { data } = await send('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(filepath, Buffer.from(data, 'base64'));
    }

    const qaReport = {
        viewports: {},
        interactions: {},
        salesFlow: {},
        compliance: {}
    };

    const targetUrl = 'https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/';

    for (const vp of VIEWPORTS) {
        console.log(`\n=================== TESTING VIEWPORT ${vp.name} ===================`);
        await setViewport(vp.width, vp.height, vp.isMobile);
        await navigate(targetUrl);

        // 1. Gather all Phase 6 Metrics
        const metrics = await evaluate(`(() => {
            const docWidth = document.documentElement.scrollWidth;
            const winWidth = window.innerWidth;
            const hasOverflow = docWidth > winWidth;

            // Elements
            const galleryStage = document.querySelector('.ayur-gallery-stage');
            const mainImg = document.querySelector('#ayurMainProductImg');
            const zoomBadge = document.querySelector('.ayur-gallery-zoom-badge');
            const eyebrow = document.querySelector('.ayur-product-eyebrow');
            const title = document.querySelector('.ayur-single-title');
            const positioning = document.querySelector('.ayur-single-positioning');
            const pricingBox = document.querySelector('.ayur-single-pricing-box');
            const singlePrice = document.querySelector('.ayur-single-price');
            const singleMrp = document.querySelector('.ayur-single-mrp');
            const singleSavings = document.querySelector('.ayur-single-savings');
            const packRow = document.querySelector('.ayur-pack-spec-row');
            const packChip = document.querySelector('.ayur-pack-chip');
            const stockBadge = document.querySelector('.ayur-stock-badge');
            const stepper = document.querySelector('.ayur-qty-stepper');
            const addBtn = document.querySelector('.ayur-btn-add-cart');
            const buyBtn = document.querySelector('.ayur-btn-buy-now');
            const microTrust = document.querySelector('.ayur-cta-micro-trust');
            const deliveryPanel = document.querySelector('.ayur-delivery-info-panel');
            const benefitsPanel = document.querySelector('.ayur-panel-benefits');
            const stickyBar = document.querySelector('.ayur-mobile-sticky-bar');

            // Columns layout
            const layout = document.querySelector('.ayur-product-layout');
            const galleryCol = document.querySelector('.ayur-gallery-col');
            const summaryCol = document.querySelector('.ayur-product-summary');

            // Related Products
            const relatedCards = document.querySelectorAll('.ayur-related-grid .ayur-product-card');

            // Rectangles
            const getBox = el => el ? el.getBoundingClientRect() : null;
            const stageBox = getBox(galleryStage);
            const titleBox = getBox(title);
            const priceBox = getBox(pricingBox);
            const packBox = getBox(packRow);
            const addBox = getBox(addBtn);
            const buyBox = getBox(buyBtn);
            const layoutBox = getBox(layout);
            const galColBox = getBox(galleryCol);
            const sumColBox = getBox(summaryCol);

            // Computed styles
            const titleStyle = title ? window.getComputedStyle(title) : null;
            const eyebrowStyle = eyebrow ? window.getComputedStyle(eyebrow) : null;
            const addStyle = addBtn ? window.getComputedStyle(addBtn) : null;
            const buyStyle = buyBtn ? window.getComputedStyle(buyBtn) : null;

            // Related column count
            let relatedCols = 0;
            if (relatedCards.length >= 2) {
                const c1 = relatedCards[0].getBoundingClientRect();
                const c2 = relatedCards[1].getBoundingClientRect();
                if (Math.abs(c1.top - c2.top) < 15) {
                    relatedCols = (relatedCards.length >= 4 && Math.abs(c1.top - relatedCards[3].getBoundingClientRect().top) < 15) ? 4 : 2;
                } else {
                    relatedCols = 1;
                }
            }

            // Compliance & Trust
            const bodyText = document.body.innerText;
            const hasFakeReviews = bodyText.includes('10,000+ customers') || bodyText.includes('4.9/5');
            const hasFakeUrgency = bodyText.includes('Only 3 left') || bodyText.includes('Sale ends tonight');
            const hasKSM66 = bodyText.includes('KSM-66') || bodyText.includes('KSM 66');

            return {
                hasOverflow,
                docWidth,
                winWidth,
                titleText: title ? title.innerText.trim() : '',
                titleFontSize: titleStyle ? parseFloat(titleStyle.fontSize) : 0,
                titleFontFamily: titleStyle ? titleStyle.fontFamily : '',
                titleLineHeight: titleStyle ? titleStyle.lineHeight : '',
                eyebrowText: eyebrow ? eyebrow.innerText.trim() : '',
                eyebrowColor: eyebrowStyle ? eyebrowStyle.color : '',
                positioningText: positioning ? positioning.innerText.trim() : '',
                priceText: singlePrice ? singlePrice.innerText.trim() : '',
                mrpText: singleMrp ? singleMrp.innerText.trim() : '',
                savingsText: singleSavings ? singleSavings.innerText.trim() : '',
                packText: packChip ? packChip.innerText.trim() : '',
                stockText: stockBadge ? stockBadge.innerText.trim() : '',
                addBtnHeight: addBox ? addBox.height : 0,
                buyBtnHeight: buyBox ? buyBox.height : 0,
                addBtnBg: addStyle ? addStyle.backgroundColor : '',
                buyBtnBg: buyStyle ? buyStyle.backgroundColor : '',
                addBtnBottom: addBox ? addBox.bottom : 0,
                buyBtnBottom: buyBox ? buyBox.bottom : 0,
                stageHeight: stageBox ? stageBox.height : 0,
                layoutWidth: layoutBox ? layoutBox.width : 0,
                galleryWidth: galColBox ? galColBox.width : 0,
                summaryWidth: sumColBox ? sumColBox.width : 0,
                microTrustText: microTrust ? microTrust.innerText.replace(/\\s+/g, ' ').trim() : '',
                hasDeliveryPanel: !!deliveryPanel,
                benefitsCount: benefitsPanel ? benefitsPanel.querySelectorAll('li').length : 0,
                stickyBarVisible: stickyBar ? window.getComputedStyle(stickyBar).display !== 'none' : false,
                relatedCols,
                relatedCount: relatedCards.length,
                hasFakeReviews,
                hasFakeUrgency,
                hasKSM66
            };
        })()`);

        qaReport.viewports[vp.name] = metrics;

        console.log(`[TITLE] "${metrics.titleText}" | Font: ${metrics.titleFontFamily.slice(0, 20)} | Size: ${metrics.titleFontSize}px`);
        console.log(`[CATEGORY] "${metrics.eyebrowText}" | Color: ${metrics.eyebrowColor}`);
        console.log(`[PRICING] ${metrics.priceText} | MRP: ${metrics.mrpText} | Savings: ${metrics.savingsText}`);
        console.log(`[PACK & STOCK] Pack: "${metrics.packText}" | Stock: "${metrics.stockText}"`);
        console.log(`[CTAs] ADD Height: ${metrics.addBtnHeight.toFixed(1)}px (Bg: ${metrics.addBtnBg}) | BUY Height: ${metrics.buyBtnHeight.toFixed(1)}px (Bg: ${metrics.buyBtnBg})`);
        console.log(`[CTA BOTTOM POS] Add To Cart Bottom: ${metrics.addBtnBottom.toFixed(1)}px vs Viewport ${vp.height}px`);
        if (!vp.isMobile) {
            const galPct = ((metrics.galleryWidth / metrics.layoutWidth) * 100).toFixed(1);
            const sumPct = ((metrics.summaryWidth / metrics.layoutWidth) * 100).toFixed(1);
            console.log(`[DESKTOP SPLIT] Gallery: ${galPct}% (${metrics.galleryWidth}px) | Summary: ${sumPct}% (${metrics.summaryWidth}px)`);
        }
        console.log(`[MICRO-TRUST] "${metrics.microTrustText}"`);
        console.log(`[DELIVERY & HIGHLIGHTS] Delivery Panel: ${metrics.hasDeliveryPanel} | Highlights Count: ${metrics.benefitsCount}`);
        console.log(`[RELATED PRODUCTS] Columns: ${metrics.relatedCols} | Cards: ${metrics.relatedCount}`);
        console.log(`[OVERFLOW] ${metrics.hasOverflow} (${metrics.docWidth}px vs ${metrics.winWidth}px)`);
        console.log(`[COMPLIANCE] KSM-66: ${metrics.hasKSM66} | Fake Reviews: ${metrics.hasFakeReviews} | Fake Urgency: ${metrics.hasFakeUrgency}`);

        // Capture above-the-fold screenshot
        const atfPath = path.join(ARTIFACTS_DIR, `phase6_atf_${vp.name}.png`);
        await captureScreenshot(atfPath);
        console.log(`[SCREENSHOT] Saved: ${atfPath}`);

        // 2. Test Lightbox Inspection on mobile_390 and desktop_1280
        if (vp.name === '390x844' || vp.name === '1280x800') {
            console.log(`[TEST LIGHTBOX] Triggering label inspection on ${vp.name}...`);
            await evaluate(`document.querySelector('#ayurGalleryStage').click();`);
            await new Promise(r => setTimeout(r, 600));

            const lbState = await evaluate(`(() => {
                const lb = document.querySelector('#ayurImageLightbox');
                const img = document.querySelector('#ayurLightboxImg');
                return {
                    isOpen: lb && lb.classList.contains('is-open'),
                    hasImg: img && img.getAttribute('src') && img.getAttribute('src').length > 5
                };
            })()`);
            console.log(`  Lightbox State: Open=${lbState.isOpen} | HasImg=${lbState.hasImg}`);

            const lbPath = path.join(ARTIFACTS_DIR, `phase6_lightbox_${vp.name}.png`);
            await captureScreenshot(lbPath);

            // Close lightbox
            await evaluate(`document.querySelector('#ayurLightboxClose').click();`);
            await new Promise(r => setTimeout(r, 400));
            const lbClosed = await evaluate(`!document.querySelector('#ayurImageLightbox').classList.contains('is-open')`);
            console.log(`  Lightbox Closed: ${lbClosed}`);
        }

        // 3. Test Quantity Stepper on desktop_1280
        if (vp.name === '1280x800') {
            console.log('[TEST STEPPER] Testing quantity increment & decrement...');
            const initVal = await evaluate(`document.querySelector('.ayur-qty-input').value`);
            await evaluate(`document.querySelector('.ayur-qty-inc').click();`);
            const incVal = await evaluate(`document.querySelector('.ayur-qty-input').value`);
            await evaluate(`document.querySelector('.ayur-qty-dec').click();`);
            const decVal = await evaluate(`document.querySelector('.ayur-qty-input').value`);
            console.log(`  Stepper result: Initial=${initVal} -> Inc=${incVal} -> Dec=${decVal}`);
            qaReport.interactions.stepper = { initVal, incVal, decVal };

            // 4. Test Add To Cart
            console.log('[TEST ADD TO CART] Adding product to cart...');
            await evaluate(`document.querySelector('.single_add_to_cart_button').click();`);
            await new Promise(r => setTimeout(r, 2000));

            const drawerState = await evaluate(`(() => {
                const drawerWrap = document.querySelector('#ayurCartDrawerWrap');
                const items = document.querySelectorAll('.ayur-drawer-item');
                const badge = document.querySelector('.ayur-cart-badge');
                return {
                    isOpen: drawerWrap && drawerWrap.classList.contains('is-open'),
                    itemsCount: items.length,
                    badgeCount: badge ? badge.innerText.trim() : '0'
                };
            })()`);
            console.log(`  Cart Drawer State: Open=${drawerState.isOpen} | Items=${drawerState.itemsCount} | Badge=${drawerState.badgeCount}`);
            qaReport.salesFlow.addToCart = drawerState;

            const cartPath = path.join(ARTIFACTS_DIR, `phase6_cart_drawer_desktop.png`);
            await captureScreenshot(cartPath);

            // Close cart drawer
            await evaluate(`document.querySelector('#ayurCloseCartDrawer').click();`);
            await new Promise(r => setTimeout(r, 500));
        }
    }

    // 5. Test Quick Buy / Buy Now navigation
    console.log('\n=================== TESTING BUY NOW QUICK FLOW ===================');
    await setViewport(1280, 800, false);
    await navigate(targetUrl);
    const buyNowHref = await evaluate(`document.querySelector('.ayur-btn-buy-now').getAttribute('href')`);
    console.log(`Buy Now Link: ${buyNowHref}`);
    await navigate(buyNowHref);

    const checkoutState = await evaluate(`(() => {
        const isCheckout = window.location.pathname.includes('/checkout');
        const checkoutForm = document.querySelector('form.woocommerce-checkout');
        const orderSummary = document.querySelector('#order_review');
        return {
            url: window.location.href,
            isCheckout,
            hasCheckoutForm: !!checkoutForm,
            hasOrderSummary: !!orderSummary,
            pageTitle: document.title
        };
    })()`);
    console.log(`Checkout Verification: isCheckout=${checkoutState.isCheckout} | Form=${checkoutState.hasCheckoutForm} | Title="${checkoutState.pageTitle}"`);
    qaReport.salesFlow.buyNow = checkoutState;

    const checkoutPath = path.join(ARTIFACTS_DIR, `phase6_checkout_verified.png`);
    await captureScreenshot(checkoutPath);

    // Save final report
    fs.writeFileSync('d:\\woocom\\scratch\\phase6_cdp_qa_results.json', JSON.stringify(qaReport, null, 2));
    console.log('\n--- PHASE 6 CDP QA COMPLETED SUCCESSFULLY ---');

    ws.close();
    chrome.kill();
}

main().catch(err => {
    console.error('QA Execution Error:', err);
    process.exit(1);
});
