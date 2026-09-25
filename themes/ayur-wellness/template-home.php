<?php
/**
 * Template Name: Ayurvedic High-Converting Homepage
 * Premium D2C Editorial Architecture — Bhagyoday Ayurveda
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="site-main ayur-homepage">

    <!-- 1. Hero Section -->
    <section class="ayur-hero">
        <div class="ayur-container">
            <div class="ayur-hero-grid">
                <div class="ayur-hero-text">
                    <div class="ayur-hero-badge">
                        <?php echo ayur_svg_icon('sparkles', '', 14); ?>
                        <span>Classical Botanical Formulations</span>
                    </div>
                    <h1 class="ayur-hero-title">Ancient Ayurvedic Wisdom.<br>Modern Daily Wellness.</h1>
                    <p class="ayur-hero-desc">
                        Handcrafted with standardized botanical actives to restore vitality, digestive balance, and holistic equilibrium. Prepared ethically in Maharashtra with zero synthetic fillers.
                    </p>
                    <div class="ayur-hero-ctas">
                        <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="ayur-btn-primary">
                            <span>Shop Best Sellers</span>
                            <?php echo ayur_svg_icon('arrow-right', '', 16); ?>
                        </a>
                        <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>" class="ayur-btn-secondary">
                            <span>Explore Bundles</span>
                        </a>
                    </div>
                    <div class="ayur-hero-trust-pills">
                        <span class="ayur-pill">
                            <?php echo ayur_svg_icon('leaf', '', 14); ?>
                            <span>100% Botanical Actives</span>
                        </span>
                        <span class="ayur-pill-sep">•</span>
                        <span class="ayur-pill">
                            <?php echo ayur_svg_icon('truck', '', 14); ?>
                            <span>Pan-India Delivery</span>
                        </span>
                        <span class="ayur-pill-sep">•</span>
                        <span class="ayur-pill">
                            <?php echo ayur_svg_icon('credit-card', '', 14); ?>
                            <span>COD Available</span>
                        </span>
                    </div>
                </div>

                <!-- Hero Spotlight Product Card -->
                <div class="ayur-hero-visual">
                    <div class="ayur-hero-card">
                        <div class="ayur-spotlight-inner">
                            <div class="ayur-spotlight-top">
                                <span class="ayur-spotlight-tag">Featured Formulation</span>
                                <span class="ayur-spotlight-icon"><?php echo ayur_svg_icon('leaf', '', 24); ?></span>
                            </div>
                            <h3 class="ayur-spotlight-title">Ashwagandha Shilajit Capsules</h3>
                            <p class="ayur-spotlight-desc">
                                800mg Pure Shodhit Shilajit & Ashwagandha root extract for endurance, natural stamina, and stress resilience.
                            </p>
                            <div class="ayur-spotlight-meta">
                                <span class="ayur-spotlight-price">₹890</span>
                                <span class="ayur-spotlight-mrp">MRP ₹1,990</span>
                                <span class="ayur-spotlight-save">Save 55%</span>
                            </div>
                            <div class="ayur-spotlight-actions">
                                <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="ayur-btn-primary" style="width:100%; justify-content:center;">
                                    <?php echo ayur_svg_icon('shopping-bag', '', 16); ?>
                                    <span>Explore Remedies</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Trust Bar -->
    <section class="ayur-trust-bar">
        <div class="ayur-container">
            <div class="ayur-trust-grid">
                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon"><?php echo ayur_svg_icon('leaf', '', 22); ?></div>
                    <div class="ayur-trust-content">
                        <div class="ayur-trust-title">Pure Botanical Extracts</div>
                        <div class="ayur-trust-desc">Zero artificial binders, colors, or fillers</div>
                    </div>
                </div>
                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon"><?php echo ayur_svg_icon('badge-check', '', 22); ?></div>
                    <div class="ayur-trust-content">
                        <div class="ayur-trust-title">Hygienic Manufacturing</div>
                        <div class="ayur-trust-desc">Small-batch quality-controlled production</div>
                    </div>
                </div>
                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon"><?php echo ayur_svg_icon('truck', '', 22); ?></div>
                    <div class="ayur-trust-content">
                        <div class="ayur-trust-title">Pan-India Express Shipping</div>
                        <div class="ayur-trust-desc">Dispatched directly from Jalna warehouse</div>
                    </div>
                </div>
                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon"><?php echo ayur_svg_icon('credit-card', '', 22); ?></div>
                    <div class="ayur-trust-content">
                        <div class="ayur-trust-title">UPI & COD Available</div>
                        <div class="ayur-trust-desc">Pay online or upon doorstep delivery</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Shop By Health Goal (Horizontal Swipe on Mobile) -->
    <section class="ayur-section ayur-categories-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Targeted Remedies</span>
                <h2 class="ayur-section-title">Shop by Health Goal</h2>
                <p class="ayur-section-desc">Classical herbal formulations designed to address specific daily wellness needs.</p>
            </div>

            <div class="ayur-categories-scroll-wrapper">
                <div class="ayur-categories-grid">
                    <a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>" class="ayur-cat-card">
                        <div class="ayur-cat-icon"><?php echo ayur_svg_icon('activity', '', 22); ?></div>
                        <div class="ayur-cat-name">Men's Vitality</div>
                        <span class="ayur-cat-sub">Shilajit & Ashwagandha</span>
                    </a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>" class="ayur-cat-card">
                        <div class="ayur-cat-icon"><?php echo ayur_svg_icon('leaf', '', 22); ?></div>
                        <div class="ayur-cat-name">Metabolism & Detox</div>
                        <span class="ayur-cat-sub">Slim Care & Sugar Balance</span>
                    </a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>" class="ayur-cat-card">
                        <div class="ayur-cat-icon"><?php echo ayur_svg_icon('sparkles', '', 22); ?></div>
                        <div class="ayur-cat-name">Piles & Gut Comfort</div>
                        <span class="ayur-cat-sub">Aloe Vera & Triphala</span>
                    </a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>" class="ayur-cat-card">
                        <div class="ayur-cat-icon"><?php echo ayur_svg_icon('heart', '', 22); ?></div>
                        <div class="ayur-cat-name">Heart & Immunity</div>
                        <span class="ayur-cat-sub">Arjuna Bark & Wild Amla</span>
                    </a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>" class="ayur-cat-card">
                        <div class="ayur-cat-icon"><?php echo ayur_svg_icon('shield-check', '', 22); ?></div>
                        <div class="ayur-cat-name">Joint & Mobility</div>
                        <span class="ayur-cat-sub">Shallaki & Hadjod</span>
                    </a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>" class="ayur-cat-card">
                        <div class="ayur-cat-icon"><?php echo ayur_svg_icon('leaf', '', 22); ?></div>
                        <div class="ayur-cat-name">Women's Wellness</div>
                        <span class="ayur-cat-sub">Shatavari & Ashoka Juice</span>
                    </a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>" class="ayur-cat-card ayur-cat-bundle">
                        <div class="ayur-cat-icon"><?php echo ayur_svg_icon('package', '', 22); ?></div>
                        <div class="ayur-cat-name">Value Bundles</div>
                        <span class="ayur-cat-sub" style="color:var(--color-gold); font-weight:700;">Save Up to 55%</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Featured Best Sellers (Horizontal Swipe on Mobile) -->
    <section class="ayur-section ayur-products-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Most Loved Formulations</span>
                <h2 class="ayur-section-title">Best Sellers Across India</h2>
                <p class="ayur-section-desc">Authentic small-batch herbal formulations prepared with verified botanical purity.</p>
            </div>

            <div class="ayur-products-scroll-wrapper">
                <div class="ayur-products-grid">
                    <?php
                    $args = array(
                        'post_type'      => 'product',
                        'posts_per_page' => 8,
                        'orderby'        => 'menu_order',
                        'order'          => 'ASC',
                    );
                    $loop = new WP_Query($args);

                    if ($loop->have_posts()) :
                        while ($loop->have_posts()) : $loop->the_post();
                            wc_get_template_part('content', 'product');
                        endwhile;
                        wp_reset_postdata();
                    else :
                        echo '<p style="grid-column:1/-1; text-align:center; color:#888;">Fresh Ayurvedic remedies arriving shortly...</p>';
                    endif;
                    ?>
                </div>
            </div>

            <div class="ayur-catalog-cta-wrap">
                <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="ayur-btn-primary">
                    <span>View Complete Catalog</span>
                    <?php echo ayur_svg_icon('arrow-right', '', 16); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- 5. Promotional Bundle Banner -->
    <section class="ayur-container ayur-promo-wrap">
        <div class="ayur-promo-banner">
            <div class="ayur-promo-content">
                <span class="ayur-promo-tag">Synergistic Formulations</span>
                <h3 class="ayur-promo-title">Complete Ayurvedic Therapy Bundles</h3>
                <p class="ayur-promo-desc">
                    Experience dual wellness synergy with our classical pairing rituals. Bundle your favorite daily wellness juices and capsules to save up to 55% with complimentary express delivery.
                </p>
                <div class="ayur-promo-cta-row">
                    <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>" class="ayur-btn-primary">
                        <span>Shop Bundles & Save</span>
                        <?php echo ayur_svg_icon('arrow-right', '', 16); ?>
                    </a>
                </div>
            </div>
            <div class="ayur-promo-badge-box">
                <div class="ayur-promo-badge-large">UP TO 55% OFF</div>
                <div class="ayur-promo-badge-sub">On Complete Daily Therapy Kits</div>
                <div class="ayur-promo-badge-detail">Free Pan-India Delivery Included</div>
            </div>
        </div>
    </section>

    <!-- 6. Classical Ayurvedic Ingredients Spotlight -->
    <section class="ayur-section ayur-ingredients-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Pure Botanicals</span>
                <h2 class="ayur-section-title">Standardized Botanical Actives</h2>
                <p class="ayur-section-desc">Carefully selected herbal extracts honoring traditional Ayurvedic pharmacology.</p>
            </div>

            <div class="ayur-ingredients-grid">
                <div class="ayur-ingredient-card">
                    <div class="ayur-ingredient-icon"><?php echo ayur_svg_icon('sparkles', '', 28); ?></div>
                    <h4 class="ayur-ingredient-title">Shuddha Shilajit</h4>
                    <p class="ayur-ingredient-desc">
                        Purified Himalayan mineral pitch rich in natural fulvic acid to support cellular vitality, physical endurance, and deep rejuvenation.
                    </p>
                </div>
                <div class="ayur-ingredient-card">
                    <div class="ayur-ingredient-icon"><?php echo ayur_svg_icon('leaf', '', 28); ?></div>
                    <h4 class="ayur-ingredient-title">Terminalia Arjuna</h4>
                    <p class="ayur-ingredient-desc">
                        Revered classical bark extract providing natural polyphenols to support arterial tone, cardiovascular wellness, and vital circulation.
                    </p>
                </div>
                <div class="ayur-ingredient-card">
                    <div class="ayur-ingredient-icon"><?php echo ayur_svg_icon('shield-check', '', 28); ?></div>
                    <h4 class="ayur-ingredient-title">Shallaki (Boswellia)</h4>
                    <p class="ayur-ingredient-desc">
                        Standardized Boswellia serrata gum resin to nourish synovial joint fluid, ease morning stiffness, and maintain flexible movement.
                    </p>
                </div>
                <div class="ayur-ingredient-card">
                    <div class="ayur-ingredient-icon"><?php echo ayur_svg_icon('heart', '', 28); ?></div>
                    <h4 class="ayur-ingredient-title">Shatavari & Ashoka</h4>
                    <p class="ayur-ingredient-desc">
                        Venerated female tonic botanicals that nourish vital tissues, encourage endocrine balance, and support monthly cycle comfort.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Why Bhagyoday Ayurveda? (Authenticity Section) -->
    <section class="ayur-section ayur-why-us-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Authentic & Transparent</span>
                <h2 class="ayur-section-title">Why Bhagyoday Ayurveda?</h2>
                <p class="ayur-section-desc">Rooted in authentic tradition, crafted with modern discipline, and delivered with genuine care.</p>
            </div>

            <div class="ayur-why-grid">
                <div class="ayur-why-card">
                    <div class="ayur-why-icon"><?php echo ayur_svg_icon('leaf', '', 24); ?></div>
                    <h4 class="ayur-why-title">Authentic Formulations</h4>
                    <p class="ayur-why-desc">
                        Every formula is prepared according to traditional Ayurvedic concepts, utilizing genuine herbs without synthetic chemicals or adulterants.
                    </p>
                </div>

                <div class="ayur-why-card">
                    <div class="ayur-why-icon"><?php echo ayur_svg_icon('badge-check', '', 24); ?></div>
                    <h4 class="ayur-why-title">Standardized Actives</h4>
                    <p class="ayur-why-desc">
                        We prioritize consistency and potency by utilizing standardized botanical extracts tested for purity and microbiological safety.
                    </p>
                </div>

                <div class="ayur-why-card">
                    <div class="ayur-why-icon"><?php echo ayur_svg_icon('map-pin', '', 24); ?></div>
                    <h4 class="ayur-why-title">Direct Hub Dispatch</h4>
                    <p class="ayur-why-desc">
                        Shipped directly from our central facility in Mantha, Jalna, Maharashtra in tamper-evident protective transit packaging.
                    </p>
                </div>

                <div class="ayur-why-card">
                    <div class="ayur-why-icon"><?php echo ayur_svg_icon('headphones', '', 24); ?></div>
                    <h4 class="ayur-why-title">Dedicated Support</h4>
                    <p class="ayur-why-desc">
                        Have dosage or product questions? Connect directly with our team via phone at +91 8888686858 or instant WhatsApp assistance.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. FAQ Accordion -->
    <section class="ayur-section ayur-faq-section">
        <div class="ayur-container ayur-faq-container">
            <div class="ayur-section-header">
                <span class="ayur-section-tag">Clear Answers</span>
                <h2 class="ayur-section-title">Frequently Asked Questions</h2>
            </div>

            <div class="ayur-faq-list">
                <details class="ayur-faq-details" open>
                    <summary class="ayur-faq-summary">
                        <span>Are your formulations 100% vegetarian and chemical-free?</span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p>
                            Yes. All our capsule supplements utilize 100% plant-based vegetable cellulose (HPMC). We use zero animal gelatin, zero chemical binders, zero artificial colors, and zero parabens.
                        </p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span>How long does delivery take across India?</span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p>
                            Orders are dispatched within 24 hours via reliable couriers integrated through Shiprocket (Delhivery, BlueDart, DTDC). Metro deliveries typically arrive in 2–3 business days, while other locations take 3–5 business days. Tracking updates are provided via SMS and WhatsApp.
                        </p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span>Can I pay using Cash on Delivery (COD)?</span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p>
                            Yes! Cash on Delivery is available across serviceable Indian pincodes. For prepaid orders paid via UPI or Cards, you also enjoy prioritized express dispatch.
                        </p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span>How can I reach customer support for dosage or order inquiries?</span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p>
                            You can reach us directly by calling +91 8888686858, emailing vijaykulal8833@gmail.com, or tapping the WhatsApp button on the bottom right of the screen for real-time assistance.
                        </p>
                    </div>
                </details>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
