<?php
/**
 * Template Name: Ayurvedic High-Converting Homepage
 */

get_header();
?>

<main id="primary" class="site-main">

    <!-- 1. Hero Section -->
    <section class="ayur-hero">
        <div class="ayur-container">
            <div class="ayur-hero-grid">
                <div>
                    <div class="ayur-hero-badge">
                        ✨ Authentic 5,000-Year-Old Formulations
                    </div>
                    <h1>Ancient Wisdom.<br>Modern Wellness.</h1>
                    <p>
                        Discover clinically standardized Ayurvedic remedies for everyday vitality, deep restful sleep, pure skin radiance, and balanced gut health. Crafted ethically with pure organic botanicals.
                    </p>
                    <div class="ayur-hero-ctas">
                        <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="ayur-btn-primary">
                            SHOP BEST SELLERS →
                        </a>
                        <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>" class="ayur-btn-secondary">
                            EXPLORE COMBOS (SAVE 35%)
                        </a>
                    </div>
                    <div style="margin-top:28px; display:flex; align-items:center; gap:16px; font-size:13px; color:#A7B3AE;">
                        <span>⭐ <strong>4.9/5</strong> Rating</span>
                        <span>•</span>
                        <span>🚚 <strong>Free Shipping</strong> Above ₹999</span>
                        <span>•</span>
                        <span>🛡️ <strong>COD</strong> Available</span>
                    </div>
                </div>

                <!-- Hero Graphic Showcase -->
                <div class="ayur-hero-visual">
                    <div class="ayur-hero-card">
                        <div style="background:linear-gradient(180deg, #2D5A46 0%, #162C23 100%); border-radius:16px; padding:24px; position:relative; overflow:hidden;">
                            <div style="font-size:54px; margin-bottom:10px;">🌿</div>
                            <span style="background:var(--ayur-gold); color:#1A1A1A; font-size:11px; font-weight:800; padding:4px 10px; border-radius:999px; text-transform:uppercase;">
                                Special Launch Offer
                            </span>
                            <h3 style="color:#FFFFFF; font-size:22px; margin:14px 0 8px 0; font-family:'Cinzel', serif;">
                                KSM-66® Ashwagandha
                            </h3>
                            <p style="color:#D1D5DB; font-size:13px; margin-bottom:16px;">
                                500mg Standardized Organic Root Extract for Stress Relief & Stamina.
                            </p>
                            <div style="font-size:24px; font-weight:800; color:#FFFFFF; margin-bottom:14px;">
                                ₹499 <span style="font-size:14px; color:#9CA3AF; text-decoration:line-through; font-weight:400;">₹799</span>
                            </div>
                            <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="ayur-btn-primary" style="width:100%; justify-content:center; padding:12px;">
                                ORDER NOW
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Trust Strip -->
    <section class="ayur-trust-bar">
        <div class="ayur-container">
            <div class="ayur-trust-grid">
                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon">🌱</div>
                    <div>
                        <div class="ayur-trust-title">100% Pure Botanicals</div>
                        <div class="ayur-trust-desc">Zero artificial binders or fillers</div>
                    </div>
                </div>
                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon">🔬</div>
                    <div>
                        <div class="ayur-trust-title">Lab Tested & Certified</div>
                        <div class="ayur-trust-desc">Heavy metal tested in GMP lab</div>
                    </div>
                </div>
                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon">⚡</div>
                    <div>
                        <div class="ayur-trust-title">Express Delivery in India</div>
                        <div class="ayur-trust-desc">2-4 days transit via Delhivery</div>
                    </div>
                </div>
                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon">🔒</div>
                    <div>
                        <div class="ayur-trust-title">UPI & COD Available</div>
                        <div class="ayur-trust-desc">Instant online or cash at door</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Shop By Category -->
    <section class="ayur-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Targeted Solutions</span>
                <h2 class="ayur-section-title">Shop by Health Goal</h2>
                <p class="ayur-section-desc">Handcrafted Ayurvedic formulations tailored for your specific daily wellness needs.</p>
            </div>

            <div class="ayur-categories-grid">
                <a href="<?php echo esc_url(ayur_get_cat_url('immunity')); ?>" class="ayur-cat-card">
                    <div class="ayur-cat-icon">🛡️</div>
                    <div class="ayur-cat-name">Immunity</div>
                    <span style="font-size:12px; color:#6B7280;">Curcumin & Rasayana</span>
                </a>
                <a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>" class="ayur-cat-card">
                    <div class="ayur-cat-icon">🍃</div>
                    <div class="ayur-cat-name">Digestion</div>
                    <span style="font-size:12px; color:#6B7280;">Triphala & Gut Detox</span>
                </a>
                <a href="<?php echo esc_url(ayur_get_cat_url('hair-care')); ?>" class="ayur-cat-card">
                    <div class="ayur-cat-icon">✨</div>
                    <div class="ayur-cat-name">Hair Care</div>
                    <span style="font-size:12px; color:#6B7280;">Bhringraj & Rosemary</span>
                </a>
                <a href="<?php echo esc_url(ayur_get_cat_url('skin-care')); ?>" class="ayur-cat-card">
                    <div class="ayur-cat-icon">🌸</div>
                    <div class="ayur-cat-name">Skin Care</div>
                    <span style="font-size:12px; color:#6B7280;">Kumkumadi & Saffron</span>
                </a>
                <a href="<?php echo esc_url(ayur_get_cat_url('stress-sleep')); ?>" class="ayur-cat-card">
                    <div class="ayur-cat-icon">🌙</div>
                    <div class="ayur-cat-name">Sleep & Stress</div>
                    <span style="font-size:12px; color:#6B7280;">KSM-66 Ashwagandha</span>
                </a>
                <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>" class="ayur-cat-card" style="border-color:var(--ayur-gold); background:#FAF6EE;">
                    <div class="ayur-cat-icon" style="background:#F0E4CE;">🎁</div>
                    <div class="ayur-cat-name" style="color:#A6833D;">Combos & Kits</div>
                    <span style="font-size:12px; color:#78350F; font-weight:700;">Save Up to 40%</span>
                </a>
            </div>
        </div>
    </section>

    <!-- 4. Featured Best Sellers Grid -->
    <section class="ayur-section" style="background:var(--ayur-white); border-top:1px solid var(--ayur-border); border-bottom:1px solid var(--ayur-border);">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Most Loved Remedies</span>
                <h2 class="ayur-section-title">Best Sellers Across India</h2>
                <p class="ayur-section-desc">Trusted by over 10,000+ wellness seekers for verified purity and noticeable results.</p>
            </div>

            <div class="ayur-products-grid">
                <?php
                $args = array(
                    'post_type'      => 'product',
                    'posts_per_page' => 4,
                    'orderby'        => 'menu_order',
                    'order'          => 'ASC',
                );
                $loop = new WP_Query($args);

                if ($loop->have_posts()) :
                    while ($loop->have_posts()) : $loop->the_post();
                        global $product;
                        $mrp = get_post_meta($product->get_id(), '_ayur_mrp', true);
                        $net_qty = get_post_meta($product->get_id(), '_ayur_net_quantity', true);
                        ?>
                        <div class="ayur-product-card">
                            <span class="ayur-product-badge">BESTSELLER</span>
                            <?php echo ayur_get_discount_badge($product); ?>

                            <div class="ayur-product-thumb-wrap">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium', array('class' => 'ayur-product-thumb', 'loading' => 'lazy')); ?>
                                    <?php else : ?>
                                        <div class="ayur-product-thumb" style="display:flex; align-items:center; justify-content:center; font-size:48px;">
                                            🌿
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="ayur-product-info">
                                <?php echo ayur_get_star_rating_html($product); ?>
                                <h3 class="ayur-product-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <?php if ($net_qty) : ?>
                                    <div style="font-size:12px; color:#6B7280; margin-bottom:8px;">📦 <?php echo esc_html($net_qty); ?></div>
                                <?php endif; ?>

                                <div class="ayur-product-pricing">
                                    <span class="ayur-price">₹<?php echo number_format($product->get_price()); ?></span>
                                    <?php if ($mrp) : ?>
                                        <span class="ayur-mrp">₹<?php echo number_format($mrp); ?></span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="ayur-add-btn">
                                    ADD TO CART
                                </a>
                            </div>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p style="grid-column:1/-1; text-align:center; color:#888;">Fresh Ayurvedic remedies arriving shortly...</p>';
                endif;
                ?>
            </div>

            <div style="text-align:center; margin-top:40px;">
                <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="ayur-btn-primary" style="display:inline-flex;">
                    VIEW COMPLETE CATALOG →
                </a>
            </div>
        </div>
    </section>

    <!-- 5. Promotional Combo Banner -->
    <section class="ayur-container">
        <div class="ayur-promo-banner">
            <div class="ayur-promo-content">
                <span style="color:var(--ayur-gold); font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">
                    Save More Together
                </span>
                <h3>Natural Ayurvedic Combos</h3>
                <p>
                    Experience enhanced wellness synergy with our classical 3-step rituals for complete hair restoration and full-body immunity. Save up to 40% with complimentary express delivery.
                </p>
                <div style="margin-top:24px;">
                    <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>" class="ayur-btn-primary" style="display:inline-flex;">
                        SHOP COMBOS & SAVE →
                    </a>
                </div>
            </div>
            <div style="text-align:center; padding:20px; background:rgba(255,255,255,0.06); border-radius:16px; border:1px solid rgba(255,255,255,0.15); min-width:240px;">
                <div style="font-size:38px; font-weight:800; color:var(--ayur-gold);">₹700 OFF</div>
                <div style="font-size:13px; color:#D1D5DB; margin-top:4px;">On Complete Therapy Kits</div>
                <div style="font-size:12px; color:#9CA3AF; margin-top:8px;">Use Code: <strong>WELCOME10</strong></div>
            </div>
        </div>
    </section>

    <!-- 6. Classical Ayurvedic Ingredients Spotlight -->
    <section class="ayur-section" style="padding-top:20px;">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Purity in Every Drop</span>
                <h2 class="ayur-section-title">Classical Botanical Actives</h2>
                <p class="ayur-section-desc">Standardized extracts from organically grown medicinal herbs adhering to classical scripture.</p>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:24px;">
                <div style="background:#fff; border-radius:12px; border:1px solid var(--ayur-border); padding:24px; text-align:center;">
                    <div style="font-size:36px; margin-bottom:12px;">🌿</div>
                    <h4 style="margin-bottom:8px; font-family:'Cinzel', serif;">KSM-66® Ashwagandha</h4>
                    <p style="font-size:13px; color:#6B7280; line-height:1.6;">
                        High-concentration, full-spectrum root extract clinically studied to reduce cortisol and replenish natural Ojas (vital energy).
                    </p>
                </div>
                <div style="background:#fff; border-radius:12px; border:1px solid var(--ayur-border); padding:24px; text-align:center;">
                    <div style="font-size:36px; margin-bottom:12px;">🌾</div>
                    <h4 style="margin-bottom:8px; font-family:'Cinzel', serif;">Curcumin 95%</h4>
                    <p style="font-size:13px; color:#6B7280; line-height:1.6;">
                        Standardized 95% curcuminoids paired with Piperine for 2000% higher bioavailability to soothe inflamed joints and tissues.
                    </p>
                </div>
                <div style="background:#fff; border-radius:12px; border:1px solid var(--ayur-border); padding:24px; text-align:center;">
                    <div style="font-size:36px; margin-bottom:12px;">🌱</div>
                    <h4 style="margin-bottom:8px; font-family:'Cinzel', serif;">Maha Bhringraj</h4>
                    <p style="font-size:13px; color:#6B7280; line-height:1.6;">
                        "Keshraj" — The sovereign herb for hair. Slow-cooked in cold-pressed virgin sesame oil to revive thinning follicles.
                    </p>
                </div>
                <div style="background:#fff; border-radius:12px; border:1px solid var(--ayur-border); padding:24px; text-align:center;">
                    <div style="font-size:36px; margin-bottom:12px;">🌸</div>
                    <h4 style="margin-bottom:8px; font-family:'Cinzel', serif;">Kashmiri Kesar</h4>
                    <p style="font-size:13px; color:#6B7280; line-height:1.6;">
                        Authentic grade-A saffron stigmas handpicked for Kumkumadi Tailam to restore luminous, non-greasy golden skin complexion.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Verified Reviews & Testimonials -->
    <section class="ayur-section" style="background:#FAF8F5;">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Real Customer Stories</span>
                <h2 class="ayur-section-title">Trusted by Thousands Across India</h2>
                <p class="ayur-section-desc">Read honest experiences from verified customers using our herbal formulations daily.</p>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px;">
                <div style="background:#fff; border-radius:12px; border:1px solid var(--ayur-border); padding:24px; box-shadow:var(--ayur-shadow-soft);">
                    <div style="color:#F59E0B; margin-bottom:10px;">★★★★★</div>
                    <p style="font-size:14px; line-height:1.7; color:#374151; margin-bottom:16px;">
                        "The Maha Bhringraj oil is magical. Unlike commercial synthetic oils, this has the rich authentic herbal aroma of real Kshir Pak preparation. Within 4 weeks my hair fall reduced by 70%!"
                    </p>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:36px; height:36px; border-radius:50%; background:#1E3A2F; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">P</div>
                        <div>
                            <div style="font-weight:700; font-size:14px; color:#1E3A2F;">Pooja Sharma</div>
                            <div style="font-size:11px; color:#059669;">✓ Verified Buyer (Pune, Maharashtra)</div>
                        </div>
                    </div>
                </div>

                <div style="background:#fff; border-radius:12px; border:1px solid var(--ayur-border); padding:24px; box-shadow:var(--ayur-shadow-soft);">
                    <div style="color:#F59E0B; margin-bottom:10px;">★★★★★</div>
                    <p style="font-size:14px; line-height:1.7; color:#374151; margin-bottom:16px;">
                        "Been taking the KSM-66 Ashwagandha every night. My sleep quality has improved dramatically, and I wake up without morning grogginess. Genuine Ayurvedic purity without chemicals."
                    </p>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:36px; height:36px; border-radius:50%; background:#1E3A2F; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">R</div>
                        <div>
                            <div style="font-weight:700; font-size:14px; color:#1E3A2F;">Rajesh Varma</div>
                            <div style="font-size:11px; color:#059669;">✓ Verified Buyer (Bengaluru, Karnataka)</div>
                        </div>
                    </div>
                </div>

                <div style="background:#fff; border-radius:12px; border:1px solid var(--ayur-border); padding:24px; box-shadow:var(--ayur-shadow-soft);">
                    <div style="color:#F59E0B; margin-bottom:10px;">★★★★★</div>
                    <p style="font-size:14px; line-height:1.7; color:#374151; margin-bottom:16px;">
                        "The Triphala detox capsules are gentle and non-cramping. Fast delivery to Delhi in just 2 days via Delhivery with live WhatsApp tracking updates. Truly a 5-star brand!"
                    </p>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:36px; height:36px; border-radius:50%; background:#1E3A2F; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">A</div>
                        <div>
                            <div style="font-weight:700; font-size:14px; color:#1E3A2F;">Ananya Desai</div>
                            <div style="font-size:11px; color:#059669;">✓ Verified Buyer (New Delhi)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. FAQ Accordion -->
    <section class="ayur-section">
        <div class="ayur-container" style="max-width:800px;">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Clear Answers</span>
                <h2 class="ayur-section-title">Frequently Asked Questions</h2>
            </div>

            <div style="display:flex; flex-direction:column; gap:12px;">
                <details style="background:#fff; border:1px solid var(--ayur-border); border-radius:8px; padding:18px 24px; cursor:pointer;" open>
                    <summary style="font-weight:700; color:#1E3A2F; font-size:15px; outline:none;">
                        Are your products 100% vegetarian and chemical-free?
                    </summary>
                    <p style="margin-top:12px; font-size:14px; color:#5C6A64; line-height:1.6;">
                        Yes! All our capsule supplements are encased in 100% plant-based vegetable cellulose (HPMC). We use zero animal gelatin, zero parabens, zero synthetic fragrance, and zero artificial colors.
                    </p>
                </details>

                <details style="background:#fff; border:1px solid var(--ayur-border); border-radius:8px; padding:18px 24px; cursor:pointer;">
                    <summary style="font-weight:700; color:#1E3A2F; font-size:15px; outline:none;">
                        How long does shipping take across India?
                    </summary>
                    <p style="margin-top:12px; font-size:14px; color:#5C6A64; line-height:1.6;">
                        Orders are dispatched within 24 hours via express couriers (Delhivery, BlueDart, DTDC). Metro cities receive packages in 2-3 business days; tier 2/3 locations take 3-5 days. Tracking updates are sent automatically via SMS and WhatsApp.
                    </p>
                </details>

                <details style="background:#fff; border:1px solid var(--ayur-border); border-radius:8px; padding:18px 24px; cursor:pointer;">
                    <summary style="font-weight:700; color:#1E3A2F; font-size:15px; outline:none;">
                        Can I pay using Cash on Delivery (COD)?
                    </summary>
                    <p style="margin-top:12px; font-size:14px; color:#5C6A64; line-height:1.6;">
                        Yes! Cash on Delivery is available across 25,000+ Indian pincodes. For prepaid orders paid via UPI, Cards, or Netbanking, you also enjoy prioritized express dispatch.
                    </p>
                </details>

                <details style="background:#fff; border:1px solid var(--ayur-border); border-radius:8px; padding:18px 24px; cursor:pointer;">
                    <summary style="font-weight:700; color:#1E3A2F; font-size:15px; outline:none;">
                        Can I consult an Ayurvedic expert for customized dosage?
                    </summary>
                    <p style="margin-top:12px; font-size:14px; color:#5C6A64; line-height:1.6;">
                        Absolutely. Simply tap the green WhatsApp button on the bottom right to connect directly with our wellness consultants.
                    </p>
                </details>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
