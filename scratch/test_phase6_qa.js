const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'https://lightgreen-heron-113083.hostingersite.com';
const TEST_SLUG = '/product/ashwagandha-shilajit-men-wellness-capsules/';

const VIEWPORTS = [
    { name: 'mobile_360', width: 360, height: 800, isMobile: true },
    { name: 'mobile_390', width: 390, height: 844, isMobile: true },
    { name: 'mobile_412', width: 412, height: 915, isMobile: true },
    { name: 'desktop_1280', width: 1280, height: 800, isMobile: false }
];

async function runPhase6QA() {
    console.log('====================================================');
    console.log('  PHASE 6: HIGH-CONVERSION PRODUCT PAGE AUDIT');
    console.log('====================================================');

    const browser = await chromium.launch({ headless: true });
    const report = {
        viewports: {},
        compliance: {},
        salesFlow: {},
        violations: []
    };

    try {
        for (const vp of VIEWPORTS) {
            console.log(`\n>>> Testing Viewport: ${vp.name} (${vp.width}x${vp.height})`);
            const context = await browser.newContext({
                viewport: { width: vp.width, height: vp.height },
                userAgent: vp.isMobile
                    ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
                    : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
            });

            const page = await context.newPage();
            const consoleErrors = [];
            page.on('console', msg => {
                if (msg.type() === 'error') consoleErrors.push(msg.text());
            });

            await page.goto(`${BASE_URL}${TEST_SLUG}`, { waitUntil: 'networkidle', timeout: 30000 });
            await page.waitForTimeout(1000);

            // 1. Gather Elements & Computed Styles
            const metrics = await page.evaluate((vpHeight) => {
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

                // Gallery & Purchase Layout Split
                const layout = document.querySelector('.ayur-product-layout');
                const galleryCol = document.querySelector('.ayur-gallery-col');
                const summaryCol = document.querySelector('.ayur-product-summary');

                // Related Products Grid
                const relatedGrid = document.querySelector('.ayur-related-grid');
                const relatedCards = document.querySelectorAll('.ayur-related-grid .ayur-product-card');

                // Geometry
                const getBox = el => el ? el.getBoundingClientRect() : null;

                const stageBox = getBox(galleryStage);
                const titleBox = getBox(title);
                const priceBox = getBox(pricingBox);
                const packBox = getBox(packRow);
                const addBox = getBox(addBtn);
                const buyBox = getBox(buyBtn);
                const microBox = getBox(microTrust);
                const delivBox = getBox(deliveryPanel);
                const layoutBox = getBox(layout);
                const galColBox = getBox(galleryCol);
                const sumColBox = getBox(summaryCol);

                // Computed Styles
                const titleStyle = title ? window.getComputedStyle(title) : null;
                const eyebrowStyle = eyebrow ? window.getComputedStyle(eyebrow) : null;
                const addStyle = addBtn ? window.getComputedStyle(addBtn) : null;
                const buyStyle = buyBtn ? window.getComputedStyle(buyBtn) : null;

                // Check text wrapping on CTAs
                const addTextOverflow = addBtn ? (addBtn.scrollWidth > addBtn.clientWidth + 2) : false;
                const buyTextOverflow = buyBtn ? (buyBtn.scrollWidth > buyBtn.clientWidth + 2) : false;

                // Related product column calculation
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

                // Check for fake elements
                const bodyText = document.body.innerText;
                const hasFakeReviews = /10,?000\+\s*customers|4\.9\s*\/\s*5|verified\s*buyers|customer\s*reviews/i.test(bodyText);
                const hasFakeUrgency = /only\s*\d+\s*left|sale\s*ends\s*tonight|people\s*are\s*buying/i.test(bodyText);
                const hasKSM66 = /KSM-?66/i.test(bodyText);

                return {
                    hasOverflow,
                    docWidth,
                    winWidth,
                    titleText: title ? title.innerText.trim() : '',
                    titleFontSize: titleStyle ? parseFloat(titleStyle.fontSize) : 0,
                    titleFontFamily: titleStyle ? titleStyle.fontFamily : '',
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
                    addTextOverflow,
                    buyTextOverflow,
                    // Positions relative to viewport top
                    stageTop: stageBox ? stageBox.top : 0,
                    titleTop: titleBox ? titleBox.top : 0,
                    priceTop: priceBox ? priceBox.top : 0,
                    packTop: packBox ? packBox.top : 0,
                    addBtnTop: addBox ? addBox.top : 0,
                    addBtnBottom: addBox ? addBox.bottom : 0,
                    buyBtnBottom: buyBox ? buyBox.bottom : 0,
                    // First viewport check
                    isAddBtnInFirstViewport: addBox ? (addBox.bottom <= vpHeight + 20) : false,
                    // Desktop split check
                    layoutWidth: layoutBox ? layoutBox.width : 0,
                    galleryWidth: galColBox ? galColBox.width : 0,
                    summaryWidth: sumColBox ? sumColBox.width : 0,
                    // Micro-trust and delivery
                    microTrustText: microTrust ? microTrust.innerText.replace(/\s+/g, ' ').trim() : '',
                    deliveryPanelExists: !!deliveryPanel,
                    benefitsCount: benefitsPanel ? benefitsPanel.querySelectorAll('li').length : 0,
                    stickyBarVisible: stickyBar ? window.getComputedStyle(stickyBar).display !== 'none' : false,
                    relatedCols,
                    relatedCount: relatedCards.length,
                    hasFakeReviews,
                    hasFakeUrgency,
                    hasKSM66
                };
            }, vp.height);

            report.viewports[vp.name] = metrics;

            console.log(`  - Title: "${metrics.titleText}" (${metrics.titleFontSize}px, Font: ${metrics.titleFontFamily.slice(0, 20)})`);
            console.log(`  - Eyebrow: "${metrics.eyebrowText}" | Color: ${metrics.eyebrowColor}`);
            console.log(`  - Price: ${metrics.priceText} | MRP: ${metrics.mrpText} | Savings: ${metrics.savingsText}`);
            console.log(`  - Pack: ${metrics.packText} | Stock: ${metrics.stockText}`);
            console.log(`  - CTAs: Add Height = ${metrics.addBtnHeight.toFixed(1)}px | Buy Height = ${metrics.buyBtnHeight.toFixed(1)}px`);
            console.log(`  - CTA Text Overflow: Add=${metrics.addTextOverflow} | Buy=${metrics.buyTextOverflow}`);
            console.log(`  - Add To Cart in First Viewport (top/bottom vs ${vp.height}px): bottom=${metrics.addBtnBottom.toFixed(1)}px -> ${metrics.isAddBtnInFirstViewport ? 'YES' : 'NEAR/REACHABLE'}`);
            if (!vp.isMobile) {
                const galRatio = ((metrics.galleryWidth / metrics.layoutWidth) * 100).toFixed(1);
                const sumRatio = ((metrics.summaryWidth / metrics.layoutWidth) * 100).toFixed(1);
                console.log(`  - Desktop Layout Split: Gallery ${galRatio}% (${metrics.galleryWidth}px) vs Summary ${sumRatio}% (${metrics.summaryWidth}px)`);
            }
            console.log(`  - Micro-trust: "${metrics.microTrustText}"`);
            console.log(`  - Related Products Grid Columns: ${metrics.relatedCols} cols (Count: ${metrics.relatedCount})`);
            console.log(`  - Horizontal Overflow: ${metrics.hasOverflow} (${metrics.docWidth}px vs ${metrics.winWidth}px)`);
            console.log(`  - Compliance Checks: KSM-66=${metrics.hasKSM66} | Fake Reviews=${metrics.hasFakeReviews} | Fake Urgency=${metrics.hasFakeUrgency}`);

            // Take Above-The-Fold Screenshot
            const screenshotPath = path.join(__dirname, `phase6_atf_${vp.name}.png`);
            await page.screenshot({ path: screenshotPath, fullPage: false });
            console.log(`  - Screenshot saved: ${screenshotPath}`);

            // 2. Lightbox Test (Desktop & Mobile)
            console.log(`  - Testing packaging label inspection lightbox...`);
            await page.click('#ayurGalleryStage');
            await page.waitForTimeout(400);

            const lightboxOpen = await page.evaluate(() => {
                const lb = document.querySelector('#ayurImageLightbox');
                const img = document.querySelector('#ayurLightboxImg');
                const isVisible = lb && lb.classList.contains('is-open');
                const hasImgSrc = img && img.getAttribute('src') && img.getAttribute('src').length > 5;
                return isVisible && hasImgSrc;
            });
            console.log(`    Lightbox opened and image loaded: ${lightboxOpen ? 'SUCCESS' : 'FAILED'}`);

            // Close lightbox via close button
            await page.click('#ayurLightboxClose');
            await page.waitForTimeout(300);
            const lightboxClosed = await page.evaluate(() => {
                const lb = document.querySelector('#ayurImageLightbox');
                return lb && !lb.classList.contains('is-open');
            });
            console.log(`    Lightbox closed cleanly: ${lightboxClosed ? 'SUCCESS' : 'FAILED'}`);

            // 3. Quantity Stepper Interaction Test
            console.log(`  - Testing Quantity Stepper (+ and -)...`);
            const initialQty = await page.inputValue('.ayur-qty-input');
            await page.click('.ayur-qty-inc');
            await page.waitForTimeout(100);
            const incQty = await page.inputValue('.ayur-qty-input');
            await page.click('.ayur-qty-dec');
            await page.waitForTimeout(100);
            const decQty = await page.inputValue('.ayur-qty-input');
            console.log(`    Qty Stepper: ${initialQty} -> ${incQty} -> ${decQty} (${incQty == 2 && decQty == 1 ? 'PASS' : 'FAIL'})`);

            // 4. Sales-Flow Add To Cart Test on Desktop Viewport
            if (!vp.isMobile) {
                console.log(`  - Testing Add To Cart interaction & Cart Drawer...`);
                await page.click('.single_add_to_cart_button');
                await page.waitForTimeout(1500);

                const cartDrawerState = await page.evaluate(() => {
                    const drawerWrap = document.querySelector('#ayurCartDrawerWrap');
                    const isOpen = drawerWrap && drawerWrap.classList.contains('is-open');
                    const items = document.querySelectorAll('.ayur-drawer-item');
                    const countBadge = document.querySelector('.ayur-cart-badge');
                    return {
                        drawerOpen: isOpen,
                        itemsCount: items.length,
                        badgeCount: countBadge ? countBadge.innerText.trim() : '0'
                    };
                });
                console.log(`    Cart Drawer: Open=${cartDrawerState.drawerOpen} | Items=${cartDrawerState.itemsCount} | Badge=${cartDrawerState.badgeCount}`);

                // Close cart drawer
                const closeBtn = await page.$('#ayurCloseCartDrawer');
                if (closeBtn) {
                    await closeBtn.click();
                    await page.waitForTimeout(400);
                }
            }

            await context.close();
        }

        // 5. Sales-Flow Buy Now Test (Quick Buy Redirect to Checkout)
        console.log(`\n>>> Testing BUY NOW Flow (Quick Buy redirect to /checkout/)...`);
        const buyNowContext = await browser.newContext({ viewport: { width: 1280, height: 800 } });
        const buyNowPage = await buyNowContext.newPage();
        await buyNowPage.goto(`${BASE_URL}${TEST_SLUG}`, { waitUntil: 'networkidle' });
        await buyNowPage.waitForTimeout(500);

        // Click BUY NOW
        await Promise.all([
            buyNowPage.waitForNavigation({ waitUntil: 'networkidle', timeout: 30000 }),
            buyNowPage.click('.ayur-btn-buy-now')
        ]);

        const currentUrl = buyNowPage.url();
        const onCheckout = currentUrl.includes('/checkout');
        const checkoutTitle = await buyNowPage.title();
        console.log(`    Buy Now Destination: ${currentUrl} (Checkout verified: ${onCheckout}) | Title: ${checkoutTitle}`);

        await buyNowContext.close();

        console.log('\n====================================================');
        console.log('  PHASE 6 QA EXECUTION COMPLETED');
        console.log('====================================================');

    } catch (err) {
        console.error('QA Error:', err);
    } finally {
        await browser.close();
    }
}

runPhase6QA();
