const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'https://lightgreen-heron-113083.hostingersite.com';
const VIEWPORTS = [
    { name: 'mobile_360', width: 360, height: 800, isMobile: true },
    { name: 'mobile_390', width: 390, height: 844, isMobile: true },
    { name: 'mobile_412', width: 412, height: 915, isMobile: true },
    { name: 'desktop_1280', width: 1280, height: 800, isMobile: false }
];

async function runQA() {
    const browser = await chromium.launch({ headless: true });
    const results = {
        shop: {},
        product: {},
        conversion: {},
        violations: []
    };

    console.log('--- STARTING PHASE 5 AUTOMATED MULTI-VIEWPORT QA ---');

    for (const vp of VIEWPORTS) {
        console.log(`\nTesting Viewport: ${vp.name} (${vp.width}x${vp.height})`);
        const context = await browser.newContext({
            viewport: { width: vp.width, height: vp.height },
            userAgent: vp.isMobile
                ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
                : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
        });
        const page = await context.newPage();

        // 1. SHOP PAGE TEST
        await page.goto(`${BASE_URL}/shop/`, { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);

        const shopMetrics = await page.evaluate(() => {
            const banner = document.querySelector('.ayur-archive-banner');
            const pillsRow = document.querySelector('.ayur-cat-pills-row');
            const firstProduct = document.querySelector('.ayur-products-grid .ayur-product-card');
            const controls = document.querySelector('.ayur-archive-controls');
            const pills = Array.from(document.querySelectorAll('.ayur-cat-pill')).map(p => ({
                text: p.innerText.trim(),
                box: p.getBoundingClientRect()
            }));

            const bannerBox = banner ? banner.getBoundingClientRect() : null;
            const prodBox = firstProduct ? firstProduct.getBoundingClientRect() : null;
            const controlsBox = controls ? controls.getBoundingClientRect() : null;

            return {
                bannerHeight: bannerBox ? bannerBox.height : 0,
                bannerBottom: bannerBox ? bannerBox.bottom : 0,
                pillsRowHeight: pillsRow ? pillsRow.getBoundingClientRect().height : 0,
                pillsCount: pills.length,
                pillsWrap: pillsRow ? window.getComputedStyle(pillsRow).flexWrap : '',
                firstProductTop: prodBox ? prodBox.top : 0,
                controlsTop: controlsBox ? controlsBox.top : 0,
                hasOverflow: document.documentElement.scrollWidth > window.innerWidth,
                scrollWidth: document.documentElement.scrollWidth,
                innerWidth: window.innerWidth
            };
        });

        results.shop[vp.name] = shopMetrics;
        console.log(`[SHOP] ${vp.name}: Banner Height = ${shopMetrics.bannerHeight.toFixed(1)}px | First Prod Top = ${shopMetrics.firstProductTop.toFixed(1)}px | Overflow: ${shopMetrics.hasOverflow}`);

        await page.screenshot({ path: path.join(__dirname, `phase5_shop_${vp.name}.png`), fullPage: false });

        // 2. PRODUCT PAGE TEST
        await page.goto(`${BASE_URL}/product/ashwagandha-shilajit-men-wellness-capsules/`, { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);

        const prodMetrics = await page.evaluate(() => {
            const wrap = document.querySelector('.ayur-product-details-wrap');
            const whyChoose = document.querySelector('.ayur-section-why');
            const botanicals = document.querySelectorAll('.ayur-botanical-card');
            const usageSteps = document.querySelectorAll('.ayur-usage-step');
            const specs = document.querySelector('.ayur-spec-table');
            const shipping = document.querySelector('.ayur-shipping-card');
            const caution = document.querySelector('.ayur-caution-card');
            const relatedGrid = document.querySelector('.ayur-related-grid');
            const relatedCards = document.querySelectorAll('.ayur-related-grid .ayur-product-card');
            const stickyBar = document.querySelector('.ayur-mobile-sticky-bar');

            // Check KSM-66 in text
            const fullText = document.body.innerText;
            const ksmFound = /KSM-?66/i.test(fullText);

            // Related cards geometry
            const cardBoxes = Array.from(relatedCards).map(c => {
                const b = c.getBoundingClientRect();
                return { width: b.width, height: b.height, left: b.left, top: b.top };
            });

            return {
                detailsWrapWidth: wrap ? wrap.getBoundingClientRect().width : 0,
                whyChooseOpen: whyChoose ? whyChoose.hasAttribute('open') : false,
                botanicalsCount: botanicals.length,
                usageStepsCount: usageSteps.length,
                hasSpecsTable: !!specs,
                hasShippingCard: !!shipping,
                hasCautionCard: !!caution,
                relatedCardsCount: relatedCards.length,
                relatedGridWidth: relatedGrid ? relatedGrid.getBoundingClientRect().width : 0,
                cardBoxes: cardBoxes,
                ksmFound: ksmFound,
                stickyBarVisible: stickyBar ? window.getComputedStyle(stickyBar).display !== 'none' : false,
                hasOverflow: document.documentElement.scrollWidth > window.innerWidth,
                scrollWidth: document.documentElement.scrollWidth,
                innerWidth: window.innerWidth
            };
        });

        results.product[vp.name] = prodMetrics;
        console.log(`[PRODUCT] ${vp.name}: Related Cards = ${prodMetrics.relatedCardsCount} | First Card Width = ${prodMetrics.cardBoxes[0] ? prodMetrics.cardBoxes[0].width.toFixed(1) : 0}px | KSM-66: ${prodMetrics.ksmFound} | Overflow: ${prodMetrics.hasOverflow}`);

        // Scroll to related products & take screenshot
        await page.evaluate(() => {
            const rel = document.querySelector('.ayur-related-wrap');
            if (rel) rel.scrollIntoView();
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(__dirname, `phase5_product_related_${vp.name}.png`), fullPage: false });

        // Scroll back to details wrap & take screenshot
        await page.evaluate(() => {
            const det = document.querySelector('.ayur-product-details-wrap');
            if (det) det.scrollIntoView();
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(__dirname, `phase5_product_details_${vp.name}.png`), fullPage: false });

        await context.close();
    }

    // 3. FUNCTIONAL CHECK: ADD TO CART & QUICK ADD
    const testContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const testPage = testContext.newPage();
    const p = await testPage;
    await p.goto(`${BASE_URL}/product/ashwagandha-shilajit-men-wellness-capsules/`, { waitUntil: 'networkidle' });
    await p.waitForTimeout(1000);

    // Click single add to cart
    console.log('\nTesting Single Product Add to Cart...');
    await p.click('.ayur-btn-add-cart');
    await p.waitForTimeout(2000);

    const drawerStatus = await p.evaluate(() => {
        const drawerWrap = document.querySelector('.ayur-cart-drawer-wrap');
        const drawer = document.querySelector('.ayur-cart-drawer');
        const isOpen = drawerWrap && drawerWrap.classList.contains('is-open');
        const itemCount = document.querySelectorAll('.ayur-drawer-item').length;
        const subtotal = document.querySelector('.ayur-drawer-total-row .val') ? document.querySelector('.ayur-drawer-total-row .val').innerText.trim() : '';
        return { isOpen, itemCount, subtotal };
    });

    results.conversion = drawerStatus;
    console.log(`[CONVERSION] Drawer open: ${drawerStatus.isOpen} | Items in drawer: ${drawerStatus.itemCount} | Subtotal: ${drawerStatus.subtotal}`);

    await p.screenshot({ path: path.join(__dirname, 'phase5_cart_drawer_mobile.png') });
    await testContext.close();

    await browser.close();

    fs.writeFileSync(path.join(__dirname, 'phase5_qa_results.json'), JSON.stringify(results, null, 2));
    console.log('\n--- QA COMPLETE: Results saved to scratch/phase5_qa_results.json ---');
}

runQA().catch(err => {
    console.error('QA Failed:', err);
    process.exit(1);
});
