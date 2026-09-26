const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';
const BASE_URL = 'https://lightgreen-heron-113083.hostingersite.com';

const VIEWPORTS = [
    { name: '360x800', width: 360, height: 800, isMobile: true },
    { name: '375x812', width: 375, height: 812, isMobile: true },
    { name: '390x844', width: 390, height: 844, isMobile: true },
    { name: '412x915', width: 412, height: 915, isMobile: true },
    { name: '768x1024', width: 768, height: 1024, isMobile: true },
    { name: '1024x768', width: 1024, height: 768, isMobile: false },
    { name: '1280x800', width: 1280, height: 800, isMobile: false }
];

async function main() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9231;

    console.log('--- STARTING PHASE 7 CDP COMPREHENSIVE AUTOMATED QA ---');

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-phase7-qa',
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

    const testResults = {
        homepageSections: null,
        heroStructure: null,
        featuredFormulation: null,
        shopArchive: null,
        productCards: null,
        quickAddFlow: null,
        cartDrawerThreshold: null,
        cartDrawerCrossSell: null,
        checkoutUX: null,
        searchSuggestions: {},
        overflowChecks: []
    };

    try {
        // ==========================================
        // TEST 1: HOMEPAGE STRUCTURE & SECTIONS
        // ==========================================
        console.log('\n[1/6] Testing Homepage Structure on Desktop & Mobile...');
        await setViewport(1280, 800, false);
        await navigate(BASE_URL + '/?cache_bust=' + Date.now());

        testResults.homepageSections = await evaluate(`(() => {
            const sections = Array.from(document.querySelectorAll('main.ayur-homepage > section')).map(s => {
                return {
                    tag: s.tagName,
                    classes: s.className,
                    id: s.id || '',
                    eyebrow: s.querySelector('.ayur-section-eyebrow, .ayur-hero-eyebrow, .ayur-featured-eyebrow')?.textContent.trim() || '',
                    title: s.querySelector('.ayur-section-title, .ayur-hero-title, .ayur-featured-title')?.textContent.trim() || ''
                };
            });
            return sections;
        })()`);
        console.log('Homepage Sections Found:', testResults.homepageSections);

        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_home_desktop.png'));

        // Test on Mobile 390x844
        await setViewport(390, 844, true);
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_home_mobile_hero.png'));

        // ==========================================
        // TEST 2: SHOP PAGE & COMPACT ARCHIVE
        // ==========================================
        console.log('\n[2/6] Testing Shop Page...');
        await setViewport(390, 844, true);
        await navigate(BASE_URL + '/shop/?cache_bust=' + Date.now());

        testResults.shopArchive = await evaluate(`(() => {
            const banner = document.querySelector('.ayur-archive-banner');
            const chips = Array.from(document.querySelectorAll('.ayur-cat-pill')).map(p => ({
                text: p.textContent.trim(),
                isActive: p.classList.contains('active'),
                href: p.getAttribute('href')
            }));
            const count = document.querySelector('.woocommerce-result-count')?.textContent.trim();
            const firstProduct = document.querySelector('.ayur-products-grid .ayur-product-card');
            const firstProductRect = firstProduct ? firstProduct.getBoundingClientRect() : null;

            return {
                bannerPresent: !!banner,
                chipsCount: chips.length,
                firstChipActive: chips[0]?.isActive,
                firstChipText: chips[0]?.text,
                resultCountText: count,
                firstProductTop: firstProductRect ? Math.round(firstProductRect.top) : null,
                isFirstProductVisibleEarly: firstProductRect ? (firstProductRect.top < 700) : false
            };
        })()`);
        console.log('Shop Archive Metrics (Mobile 390x844):', testResults.shopArchive);
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_shop_mobile.png'));

        await setViewport(1280, 800, false);
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_shop_desktop.png'));

        // ==========================================
        // TEST 3: QUICK ADD & CART DRAWER
        // ==========================================
        console.log('\n[3/6] Testing Quick Add & Cart Drawer...');
        await setViewport(390, 844, true);
        // Clear cart first
        await evaluate(`fetch('${BASE_URL}/cart/?empty-cart=yes').catch(() => {});`);

        // Click Add to Cart on the first product card
        const quickAddResult = await evaluate(`(async () => {
            const btn = document.querySelector('.ayur-products-grid .ayur-product-card .ajax_add_to_cart');
            if (!btn) return { error: 'No add to cart button found' };
            const initialText = btn.textContent.trim();
            btn.click();
            await new Promise(r => setTimeout(r, 600));
            const addingState = btn.classList.contains('is-adding') || btn.classList.contains('is-added');
            const stateText = btn.textContent.trim();
            await new Promise(r => setTimeout(r, 1200));
            const afterAddedText = btn.textContent.trim();
            const drawerOpen = document.querySelector('#ayurCartDrawerWrap')?.classList.contains('is-open');
            return {
                initialText,
                stateText,
                afterAddedText,
                addingState,
                drawerOpen
            };
        })()`);
        console.log('Quick Add Interaction Result:', quickAddResult);
        testResults.quickAddFlow = quickAddResult;

        await new Promise(r => setTimeout(r, 1500));
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_cart_drawer_mobile.png'));

        // Check Cart Drawer Elements
        testResults.cartDrawerThreshold = await evaluate(`(() => {
            const drawer = document.querySelector('#ayurCartDrawer');
            const shippingBar = drawer.querySelector('.ayur-drawer-shipping-text')?.textContent.trim();
            const items = Array.from(drawer.querySelectorAll('.ayur-drawer-item')).map(item => ({
                name: item.querySelector('.ayur-drawer-item-name')?.textContent.trim(),
                price: item.querySelector('.ayur-drawer-item-price')?.textContent.trim(),
                qty: item.querySelector('.ayur-drawer-qty-val')?.textContent.trim(),
                packSize: item.querySelector('.ayur-drawer-item-qty-tag')?.textContent.trim()
            }));
            const crossSell = drawer.querySelector('.ayur-drawer-cross-title')?.textContent.trim();
            const crossSellItems = Array.from(drawer.querySelectorAll('.ayur-drawer-cross-item')).map(c => ({
                name: c.querySelector('.ayur-drawer-cross-name')?.textContent.trim(),
                price: c.querySelector('.cross-price')?.textContent.trim()
            }));
            const checkoutBtn = drawer.querySelector('.ayur-btn-drawer-checkout')?.textContent.trim();

            return {
                shippingBar,
                itemCount: items.length,
                items,
                crossSellTitle: crossSell,
                crossSellItemsCount: crossSellItems.length,
                crossSellItems,
                checkoutBtn
            };
        })()`);
        console.log('Cart Drawer Content:', testResults.cartDrawerThreshold);

        // ==========================================
        // TEST 4: CHECKOUT UX
        // ==========================================
        console.log('\n[4/6] Testing Checkout UX...');
        await setViewport(390, 844, true);
        await navigate(BASE_URL + '/checkout/?cache_bust=' + Date.now());

        testResults.checkoutUX = await evaluate(`(() => {
            const form = document.querySelector('form.woocommerce-checkout');
            const placeOrderBtn = document.querySelector('#place_order');
            const placeOrderRect = placeOrderBtn?.getBoundingClientRect();
            const billingPhone = document.querySelector('#billing_phone');
            const billingPostcode = document.querySelector('#billing_postcode');
            const waWidget = document.querySelector('.ayur-wa-float-container');
            const waVisible = waWidget ? (window.getComputedStyle(waWidget).display !== 'none') : false;
            const bottomNav = document.querySelector('.ayur-mobile-bottom-nav');
            const bottomNavVisible = bottomNav ? (window.getComputedStyle(bottomNav).display !== 'none') : false;
            const paymentMethods = Array.from(document.querySelectorAll('.wc_payment_method')).map(m => {
                return m.querySelector('label')?.textContent.trim();
            });

            return {
                formPresent: !!form,
                placeOrderText: placeOrderBtn?.value || placeOrderBtn?.textContent.trim(),
                placeOrderHeight: placeOrderRect ? placeOrderRect.height : 0,
                billingPhoneLabel: billingPhone?.closest('.form-row')?.querySelector('label')?.textContent.trim(),
                billingPhonePlaceholder: billingPhone?.getAttribute('placeholder'),
                billingPostcodeLabel: billingPostcode?.closest('.form-row')?.querySelector('label')?.textContent.trim(),
                waHiddenOnCheckout: !waVisible,
                bottomNavHiddenOnCheckout: !bottomNavVisible,
                paymentMethods
            };
        })()`);
        console.log('Checkout UX Metrics:', testResults.checkoutUX);
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_checkout_mobile.png'));

        await setViewport(1280, 800, false);
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_checkout_desktop.png'));

        // ==========================================
        // TEST 5: SEARCH SUGGESTIONS
        // ==========================================
        console.log('\n[5/6] Testing Native Live Search Modal...');
        await setViewport(390, 844, true);
        await navigate(BASE_URL + '/?cache_bust=' + Date.now());

        const searchQueries = ['Ashwagandha', 'Shilajit', 'Aloe', 'Joint', 'Heart', 'Metabolism'];
        for (const query of searchQueries) {
            const searchRes = await evaluate(`(async () => {
                // Open search modal
                const toggle = document.querySelector('#ayurSearchToggle') || document.querySelector('#ayurBottomNavSearch');
                toggle.click();
                await new Promise(r => setTimeout(r, 200));

                const input = document.querySelector('#ayurSearchInput');
                input.value = '${query}';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                await new Promise(r => setTimeout(r, 600));

                const results = Array.from(document.querySelectorAll('#ayurSearchLiveResults .ayur-search-result-item')).map(r => ({
                    title: r.querySelector('.ayur-search-result-title')?.textContent.trim(),
                    price: r.querySelector('.ayur-search-result-price')?.textContent.trim(),
                    qty: r.querySelector('.ayur-search-result-qty')?.textContent.trim()
                }));
                return results;
            })()`);
            testResults.searchSuggestions[query] = searchRes;
            console.log(`Search query "${query}": found ${searchRes.length} items`);
        }
        await captureScreenshot(path.join(ARTIFACTS_DIR, 'phase7_search_modal_mobile.png'));

        // Close search
        await evaluate(`document.querySelector('#ayurCloseSearch')?.click();`);

        // ==========================================
        // TEST 6: RESPONSIVE OVERFLOW CHECKS
        // ==========================================
        console.log('\n[6/6] Testing Responsive Viewports for Horizontal Overflow...');
        for (const vp of VIEWPORTS) {
            await setViewport(vp.width, vp.height, vp.isMobile);
            await new Promise(r => setTimeout(r, 500));
            const overflow = await evaluate(`(() => {
                const scrollWidth = document.documentElement.scrollWidth;
                const clientWidth = document.documentElement.clientWidth;
                return {
                    scrollWidth,
                    clientWidth,
                    hasOverflow: scrollWidth > clientWidth
                };
            })()`);
            testResults.overflowChecks.push({
                viewport: vp.name,
                ...overflow
            });
            console.log(`Viewport ${vp.name}: hasOverflow = ${overflow.hasOverflow} (${overflow.scrollWidth}px vs ${overflow.clientWidth}px)`);
        }

        fs.writeFileSync(path.join(ARTIFACTS_DIR, 'phase7_test_results.json'), JSON.stringify(testResults, null, 2));
        console.log('\n--- PHASE 7 AUTOMATED QA FINISHED SUCCESSFULLY ---');

    } catch (err) {
        console.error('QA Error:', err);
    } finally {
        ws.close();
        chrome.kill();
    }
}

main();
