const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const path = require('path');

const ARTIFACTS_DIR = 'C:\\Users\\chava\\.gemini\\antigravity-ide\\brain\\39982474-5099-4401-ae83-3a88d85932a3';

async function main() {
    console.log('=== PHASE 4: FULL CONVERSION AUDIT & AUTHENTICITY VERIFICATION ===\n');
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const port = 9225;

    const chrome = spawn(chromePath, [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        `--remote-debugging-port=${port}`,
        '--user-data-dir=d:\\woocom\\scratch\\chrome-user-data-phase4-final',
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
    await send('Network.enable');

    async function setViewport(w, h, isMobile = true) {
        await send('Emulation.setDeviceMetricsOverride', {
            width: w,
            height: h,
            deviceScaleFactor: 2,
            mobile: isMobile
        });
    }

    async function navigate(url, waitMs = 3000) {
        await send('Page.navigate', { url });
        await new Promise(r => setTimeout(r, waitMs));
    }

    async function captureScreenshot(filename) {
        const { data } = await send('Page.captureScreenshot', { format: 'png' });
        const fullPath = path.join(ARTIFACTS_DIR, filename);
        fs.writeFileSync(fullPath, Buffer.from(data, 'base64'));
        console.log(`Saved screenshot: ${filename}`);
    }

    async function evaluate(expression) {
        const res = await send('Runtime.evaluate', {
            expression,
            returnByValue: true,
            awaitPromise: true
        });
        return res ? res.result.value : null;
    }

    const audit = {};

    try {
        // ==============================================================
        // 1. AUDIT HOMEPAGE CLAIMS & MERCHANDISING (390x844)
        // ==============================================================
        console.log('1. Auditing Homepage (390x844)...');
        await setViewport(390, 844, true);
        await navigate('https://lightgreen-heron-113083.hostingersite.com/');

        audit.homepage = await evaluate(`(() => {
            const body = document.body.innerText;
            const primaryCta = document.querySelector('.ayur-hero-actions .ayur-btn-primary');
            const secCta = document.querySelector('.ayur-hero-actions .ayur-btn-secondary');
            const heroModule = document.querySelector('.ayur-hero-sales-module');
            const heroTitle = heroModule?.querySelector('.ayur-hero-sales-title')?.textContent.trim();
            const heroPrice = heroModule?.querySelector('.ayur-hero-price')?.textContent.trim();
            const heroMrp = heroModule?.querySelector('.ayur-hero-mrp')?.textContent.trim();
            const heroSave = heroModule?.querySelector('.ayur-hero-save-tag')?.textContent.trim();
            const trustPills = Array.from(document.querySelectorAll('.ayur-trust-pill')).map(p => p.textContent.trim());
            const whyPillars = Array.from(document.querySelectorAll('.ayur-pillar-title')).map(p => p.textContent.trim());
            const botanicalIngredients = Array.from(document.querySelectorAll('.ayur-ingredient-name')).map(i => i.textContent.trim());
            const bundles = Array.from(document.querySelectorAll('.ayur-bundle-card')).map(b => ({
                title: b.querySelector('.ayur-bundle-title')?.textContent.trim(),
                price: b.querySelector('.ayur-bundle-price')?.textContent.trim(),
                mrp: b.querySelector('.ayur-bundle-mrp')?.textContent.trim(),
                save: b.querySelector('.ayur-bundle-save')?.textContent.trim()
            }));

            return {
                primaryCta: { text: primaryCta?.textContent.trim(), href: primaryCta?.getAttribute('href') },
                secCta: { text: secCta?.textContent.trim(), href: secCta?.getAttribute('href') },
                heroSalesModule: { heroTitle, heroPrice, heroMrp, heroSave },
                trustPills,
                whyPillars,
                botanicalIngredients,
                bundles,
                hasKsm66: body.includes('KSM-66'),
                hasCleanroom: body.includes('Cleanroom'),
                hasFakeReviews: !!document.querySelector('.star-rating, .ayur-stars')
            };
        })()`);
        console.log('Homepage Audit Results:\n', JSON.stringify(audit.homepage, null, 2));
        await captureScreenshot('phase4_home_verified_390x844.png');

        // ==============================================================
        // 2. AUDIT PRODUCT PAGE (Product 30)
        // ==============================================================
        console.log('\n2. Auditing Single Product Page (Product 30)...');
        await navigate('https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/');

        audit.product = await evaluate(`(() => {
            const title = document.querySelector('.ayur-product-title')?.textContent.trim();
            const price = document.querySelector('.ayur-price-current')?.textContent.trim();
            const mrp = document.querySelector('.ayur-mrp-strike')?.textContent.trim();
            const saveBadge = document.querySelector('.ayur-save-badge')?.textContent.trim();
            const stockBadge = document.querySelector('.ayur-stock-badge')?.textContent.trim();
            const atcBtn = document.querySelector('.ayur-btn-add-cart')?.textContent.trim();
            const buyNowBtn = document.querySelector('.ayur-btn-buy-now')?.textContent.trim();
            const stickyBar = document.querySelector('.ayur-mobile-sticky-bar');
            const stickyPrice = stickyBar?.querySelector('.ayur-sticky-price')?.textContent.trim();
            const specs = Array.from(document.querySelectorAll('.ayur-spec-list dt, .ayur-spec-list dd')).map(el => el.textContent.trim());
            const dataLayer = window.dataLayer || [];
            const viewItem = dataLayer.find(e => e.event === 'view_item');

            return {
                title,
                price,
                mrp,
                saveBadge,
                stockBadge,
                atcBtn,
                buyNowBtn,
                hasStickyBar: !!stickyBar,
                stickyPrice,
                specs: specs.slice(0, 10),
                viewItemData: viewItem
            };
        })()`);
        console.log('Product Page Audit Results:\n', JSON.stringify(audit.product, null, 2));
        await captureScreenshot('phase4_product_verified_390x844.png');

        // ==============================================================
        // 3. TEST JOURNEY A: Add to Cart & Cart Drawer Merchandising
        // ==============================================================
        console.log('\n3. Testing Journey A: Add to Cart from Product Page...');
        await evaluate(`document.querySelector('.ayur-btn-add-cart').click()`);
        await new Promise(r => setTimeout(r, 2500));

        audit.cartState1 = await evaluate(`(() => {
            const drawer = document.querySelector('#ayurCartDrawer');
            const isOpen = drawer ? drawer.classList.contains('active') : false;
            const count = drawer?.querySelector('.ayur-drawer-count')?.textContent.trim();
            const progress = drawer?.querySelector('.ayur-progress-text')?.textContent.trim();
            const subtotal = drawer?.querySelector('.ayur-breakdown-subtotal')?.textContent.trim();
            const shipping = drawer?.querySelector('.ayur-breakdown-shipping')?.textContent.trim();
            const total = drawer?.querySelector('.ayur-breakdown-total')?.textContent.trim();
            const checkoutBtn = drawer?.querySelector('.ayur-btn-checkout')?.textContent.trim();
            const dataLayer = window.dataLayer || [];
            const atcEvent = dataLayer.find(e => e.event === 'add_to_cart');

            return {
                isOpen,
                count,
                progress,
                subtotal,
                shipping,
                total,
                checkoutBtn,
                atcEvent
            };
        })()`);
        console.log('Cart State 1 (1 Item ₹890):\n', JSON.stringify(audit.cartState1, null, 2));
        await captureScreenshot('phase4_cart_drawer_1item.png');

        // Test Free Shipping Threshold: Add second product to surpass ₹999
        console.log('\nTesting Free Shipping Unlock with 2 items...');
        await evaluate(`(() => {
            const plusBtn = document.querySelector('.ayur-drawer-qty-btn[data-action="inc"]');
            if (plusBtn) plusBtn.click();
        })()`);
        await new Promise(r => setTimeout(r, 2500));

        audit.cartState2 = await evaluate(`(() => {
            const drawer = document.querySelector('#ayurCartDrawer');
            const progress = drawer?.querySelector('.ayur-progress-text')?.textContent.trim();
            const count = drawer?.querySelector('.ayur-drawer-count')?.textContent.trim();
            const subtotal = drawer?.querySelector('.ayur-breakdown-subtotal')?.textContent.trim();
            const shipping = drawer?.querySelector('.ayur-breakdown-shipping')?.textContent.trim();
            const total = drawer?.querySelector('.ayur-breakdown-total')?.textContent.trim();

            return {
                count,
                progress,
                subtotal,
                shipping,
                total
            };
        })()`);
        console.log('Cart State 2 (2 Items ₹1,780 > ₹999):\n', JSON.stringify(audit.cartState2, null, 2));
        await captureScreenshot('phase4_cart_drawer_2items_freeshipping.png');

        // ==============================================================
        // 4. TEST CHECKOUT FUNNEL
        // ==============================================================
        console.log('\n4. Testing Checkout Funnel with Items in Cart...');
        await navigate('https://lightgreen-heron-113083.hostingersite.com/checkout/');

        audit.checkout = await evaluate(`(() => {
            const hasForm = !!document.querySelector('form.woocommerce-checkout');
            const placeOrderBtn = document.querySelector('#place_order')?.textContent.trim();
            const dataLayer = window.dataLayer || [];
            const beginCheckout = dataLayer.find(e => e.event === 'begin_checkout');
            const orderTotal = document.querySelector('.order-total')?.textContent.trim().replace(/\\s+/g, ' ');
            const body = document.body.innerText;
            const hasRazorpay = body.includes('Razorpay') || body.includes('Cards, NetBanking, Wallet & UPI');
            const hasCOD = body.includes('Cash on delivery') || body.includes('Cash on Delivery') || body.includes('COD');

            return {
                hasForm,
                placeOrderBtn,
                beginCheckout,
                orderTotal,
                hasRazorpay,
                hasCOD
            };
        })()`);
        console.log('Checkout Funnel Audit:\n', JSON.stringify(audit.checkout, null, 2));
        await captureScreenshot('phase4_checkout_active.png');

        // ==============================================================
        // 5. TEST JOURNEY B: Buy Now Direct Route
        // ==============================================================
        console.log('\n5. Testing Journey B: Direct Buy Now Flow...');
        await navigate('https://lightgreen-heron-113083.hostingersite.com/?ayur_quick_buy=33');
        const buyNowFinalUrl = await evaluate('window.location.href');
        console.log('Buy Now Navigated to:', buyNowFinalUrl);
        audit.buyNowRoute = { targetProduct: 33, finalUrl: buyNowFinalUrl };

        // ==============================================================
        // 6. TEST SEO, CANONICAL & SCHEMA AUDIT
        // ==============================================================
        console.log('\n6. Auditing Product SEO, Canonical & Schema...');
        await navigate('https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules/');

        audit.seo = await evaluate(`(() => {
            const title = document.title;
            const metaDesc = document.querySelector('meta[name="description"]')?.getAttribute('content');
            const canonical = document.querySelector('link[rel="canonical"]')?.getAttribute('href');
            const ogTitle = document.querySelector('meta[property="og:title"]')?.getAttribute('content');
            const schemas = Array.from(document.querySelectorAll('script[type="application/ld+json"]')).map(s => {
                try { return JSON.parse(s.innerText); } catch (e) { return null; }
            }).filter(Boolean);

            const hasProductSchema = schemas.some(s => {
                if (s['@type'] === 'Product') return true;
                if (Array.isArray(s['@graph']) && s['@graph'].some(g => g['@type'] === 'Product')) return true;
                return false;
            });

            const hasFakeRating = schemas.some(s => {
                if (s.aggregateRating) return true;
                if (Array.isArray(s['@graph']) && s['@graph'].some(g => g.aggregateRating)) return true;
                return false;
            });

            return {
                title,
                metaDesc,
                canonical,
                ogTitle,
                hasProductSchema,
                hasFakeRating
            };
        })()`);
        console.log('SEO Audit Results:\n', JSON.stringify(audit.seo, null, 2));

        // ==============================================================
        // 7. DESKTOP 1280x800 FIRST VIEW CONVERSION
        // ==============================================================
        console.log('\n7. Auditing Desktop 1280x800 First View Conversion...');
        await setViewport(1280, 800, false);
        await navigate('https://lightgreen-heron-113083.hostingersite.com/');

        audit.desktop = await evaluate(`(() => {
            const heroHeight = document.querySelector('.ayur-hero-section')?.offsetHeight;
            const moduleRect = document.querySelector('.ayur-hero-sales-module')?.getBoundingClientRect();
            const moduleInView = moduleRect ? moduleRect.top < 800 && moduleRect.bottom > 0 : false;
            const primaryCtaRect = document.querySelector('.ayur-hero-actions .ayur-btn-primary')?.getBoundingClientRect();
            const ctaInView = primaryCtaRect ? primaryCtaRect.top < 800 : false;

            return {
                heroHeight,
                moduleInView,
                ctaInView
            };
        })()`);
        console.log('Desktop 1280x800 Audit Results:\n', JSON.stringify(audit.desktop, null, 2));
        await captureScreenshot('phase4_desktop_1280x800.png');

    } catch (err) {
        console.error('Audit execution error:', err);
    } finally {
        ws.close();
        chrome.kill();
        fs.writeFileSync(path.join(__dirname, 'phase4_final_audit_results.json'), JSON.stringify(audit, null, 2));
        console.log('\n=== AUDIT COMPLETE ===');
    }
}

main().catch(console.error);
