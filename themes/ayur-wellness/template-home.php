<?php
/**
 * Template Name: Ayurvedic High-Converting Homepage
 * Premium D2C Editorial Architecture — Bhagyoday Ayurveda
 */

defined('ABSPATH') || exit;

get_header();

// Fetch hero spotlight product (Ashwagandha Shilajit or first published product)
$hero_product = null;
$hero_query = new WP_Query(array(
    'post_type'      => 'product',
    'name'           => 'ashwagandha-shilajit-men-wellness-capsules',
    'posts_per_page' => 1,
));
if ($hero_query->have_posts()) {
    $hero_query->the_post();
    $hero_product = wc_get_product(get_the_ID());
    wp_reset_postdata();
}
if (!$hero_product) {
    $fallback_query = new WP_Query(array('post_type' => 'product', 'posts_per_page' => 1));
    if ($fallback_query->have_posts()) {
        $fallback_query->the_post();
        $hero_product = wc_get_product(get_the_ID());
        wp_reset_postdata();
    }
}
?>

<main id="primary" class="site-main ayur-homepage">

    <!-- 1. Editorial Hero Section -->
    <section class="ayur-hero">
        <div class="ayur-container">
            <div class="ayur-hero-grid">
                <!-- Hero Text Column -->
                <div class="ayur-hero-text">
                    <span class="ayur-hero-eyebrow"><?php _e('BHAGYODAY AYURVEDA', 'ayur-wellness'); ?></span>
                    <h1 class="ayur-hero-title">
                        Ancient Wisdom.<br>
                        <span>Made for Modern Wellness.</span>
                    </h1>
                    <p class="ayur-hero-desc">
                        <?php _e('Thoughtfully crafted Ayurvedic wellness products inspired by traditional Indian knowledge.', 'ayur-wellness'); ?>
                    </p>
                    <div class="ayur-hero-actions">
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary">
                            <span><?php _e('EXPLORE WELLNESS', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 15); ?>
                        </a>
                        <a href="<?php echo esc_url(home_url('/about-us/')); ?>" class="ayur-btn-secondary">
                            <span><?php _e('OUR STORY', 'ayur-wellness'); ?></span>
                        </a>
                    </div>
                </div>

                <!-- Hero Product Visual Column -->
                <div class="ayur-hero-visual">
                    <div class="ayur-hero-stage">
                        <?php if ($hero_product && has_post_thumbnail($hero_product->get_id())) : ?>
                            <a href="<?php echo esc_url($hero_product->get_permalink()); ?>" class="ayur-hero-img-link" aria-label="<?php echo esc_attr($hero_product->get_name()); ?>">
                                <?php echo get_the_post_thumbnail($hero_product->get_id(), 'large', array(
                                    'class' => 'ayur-hero-img',
                                    'fetchpriority' => 'high',
                                    'alt' => esc_attr($hero_product->get_name())
                                )); ?>
                            </a>
                            <div class="ayur-hero-card-meta">
                                <span class="ayur-hero-card-cat"><?php _e('FEATURED FORMULATION', 'ayur-wellness'); ?></span>
                                <a href="<?php echo esc_url($hero_product->get_permalink()); ?>" class="ayur-hero-card-title">
                                    <?php echo esc_html($hero_product->get_name()); ?>
                                </a>
                                <div class="ayur-hero-card-pricing">
                                    <span class="ayur-hero-price">₹<?php echo number_format($hero_product->get_price()); ?></span>
                                    <?php
                                    $hero_mrp = get_post_meta($hero_product->get_id(), '_ayur_mrp', true);
                                    if ($hero_mrp && (float)$hero_mrp > (float)$hero_product->get_price()) :
                                    ?>
                                        <span class="ayur-hero-mrp">MRP ₹<?php echo number_format($hero_mrp); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="ayur-hero-placeholder">
                                <?php echo ayur_svg_icon('leaf', '', 48); ?>
                                <span><?php _e('Classical Ayurvedic Botanical Formulations', 'ayur-wellness'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Subtle Horizontal Trust Rail -->
    <section class="ayur-trust-rail" aria-label="<?php esc_attr_e('Brand Promises', 'ayur-wellness'); ?>">
        <div class="ayur-container">
            <div class="ayur-trust-rail-inner">
                <div class="ayur-trust-rail-item">
                    <span class="ayur-trust-rail-icon"><?php echo ayur_svg_icon('shield-check', '', 19); ?></span>
                    <span class="ayur-trust-rail-text"><?php _e('Secure Payments', 'ayur-wellness'); ?></span>
                </div>
                <div class="ayur-trust-rail-sep" aria-hidden="true"></div>
                <div class="ayur-trust-rail-item">
                    <span class="ayur-trust-rail-icon"><?php echo ayur_svg_icon('truck', '', 19); ?></span>
                    <span class="ayur-trust-rail-text"><?php _e('Pan-India Delivery', 'ayur-wellness'); ?></span>
                </div>
                <div class="ayur-trust-rail-sep" aria-hidden="true"></div>
                <div class="ayur-trust-rail-item">
                    <span class="ayur-trust-rail-icon"><?php echo ayur_svg_icon('package', '', 19); ?></span>
                    <span class="ayur-trust-rail-text"><?php _e('Carefully Packed', 'ayur-wellness'); ?></span>
                </div>
                <div class="ayur-trust-rail-sep" aria-hidden="true"></div>
                <div class="ayur-trust-rail-item">
                    <span class="ayur-trust-rail-icon"><?php echo ayur_svg_icon('headphones', '', 19); ?></span>
                    <span class="ayur-trust-rail-text"><?php _e('Customer Support', 'ayur-wellness'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Shop by Wellness Categories -->
    <section class="ayur-section ayur-categories-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('CURATED DISCIPLINES', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('Shop by Wellness', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Time-tested botanical therapies formulated for specific bodily balances.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-categories-grid">
                <a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-content">
                        <span class="ayur-cat-tag"><?php _e('STAMINA & ENDURANCE', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e("Men's Wellness", 'ayur-wellness'); ?></h3>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('View Remedies', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                        </span>
                    </div>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-content">
                        <span class="ayur-cat-tag"><?php _e('HORMONAL HARMONY', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e("Women's Wellness", 'ayur-wellness'); ?></h3>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('View Remedies', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                        </span>
                    </div>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-content">
                        <span class="ayur-cat-tag"><?php _e('HEART & IMMUNITY', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e('Daily Vitality', 'ayur-wellness'); ?></h3>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('View Remedies', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                        </span>
                    </div>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-content">
                        <span class="ayur-cat-tag"><?php _e('GUT & METABOLISM', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e('Digestive Wellness', 'ayur-wellness'); ?></h3>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('View Remedies', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                        </span>
                    </div>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-content">
                        <span class="ayur-cat-tag"><?php _e('FLEXIBILITY & COMFORT', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e('Joint & Mobility', 'ayur-wellness'); ?></h3>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('View Remedies', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                        </span>
                    </div>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>" class="ayur-cat-block ayur-cat-block-highlight">
                    <div class="ayur-cat-content">
                        <span class="ayur-cat-tag"><?php _e('SYNERGISTIC REGIMENS', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e('Therapy Bundles', 'ayur-wellness'); ?></h3>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('Explore Bundles', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- 4. Product Section: Explore Our Wellness Essentials -->
    <section class="ayur-section ayur-products-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('CLASSICAL FORMULATIONS', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('Explore Our Wellness Essentials', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Pure botanical actives prepared in small batches with zero synthetic fillers.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-products-grid">
                <?php
                $products_query = new WP_Query(array(
                    'post_type'      => 'product',
                    'posts_per_page' => 8,
                    'orderby'        => 'menu_order',
                    'order'          => 'ASC',
                ));

                if ($products_query->have_posts()) :
                    while ($products_query->have_posts()) : $products_query->the_post();
                        wc_get_template_part('content', 'product');
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p class="ayur-no-products">' . __('Remedies arriving shortly...', 'ayur-wellness') . '</p>';
                endif;
                ?>
            </div>

            <div class="ayur-catalog-action">
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary">
                    <span><?php _e('VIEW ALL FORMULATIONS', 'ayur-wellness'); ?></span>
                    <?php echo ayur_svg_icon('arrow-right', '', 15); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- 5. Editorial Brand Story (Clean, open layout — not a card) -->
    <section class="ayur-section ayur-story-section">
        <div class="ayur-container">
            <div class="ayur-story-grid">
                <div class="ayur-story-heading-col">
                    <span class="ayur-section-eyebrow"><?php _e('OUR HERITAGE', 'ayur-wellness'); ?></span>
                    <h2 class="ayur-story-title">
                        Rooted in Authentic Indian Knowledge.
                    </h2>
                </div>
                <div class="ayur-story-body-col">
                    <p class="ayur-story-lead">
                        <?php _e('Bhagyoday Ayurveda was founded on a simple principle: to bring the uncompromised potency of classical Indian herbs into contemporary daily life without synthetic shortcuts or industrial fillers.', 'ayur-wellness'); ?>
                    </p>
                    <p class="ayur-story-sub">
                        <?php _e('Our botanical actives are responsibly harvested, authenticated for pharmacological purity, and prepared in small controlled batches at our central facility in Mantha, Jalna, Maharashtra. Every formulation preserves the whole herb matrix for optimal bioavailability.', 'ayur-wellness'); ?>
                    </p>
                    <div class="ayur-story-pillars">
                        <div class="ayur-story-pillar">
                            <span class="ayur-pillar-num">01</span>
                            <h4><?php _e('Standardized Actives', 'ayur-wellness'); ?></h4>
                            <p><?php _e('Tested botanical extracts with verified active marker concentrations.', 'ayur-wellness'); ?></p>
                        </div>
                        <div class="ayur-story-pillar">
                            <span class="ayur-pillar-num">02</span>
                            <h4><?php _e('Hygienic Small Batches', 'ayur-wellness'); ?></h4>
                            <p><?php _e('Cleanroom manufacturing adhering to traditional Ayurvedic decoctions.', 'ayur-wellness'); ?></p>
                        </div>
                        <div class="ayur-story-pillar">
                            <span class="ayur-pillar-num">03</span>
                            <h4><?php _e('Direct Hub Dispatch', 'ayur-wellness'); ?></h4>
                            <p><?php _e('Shipped in protective packaging directly from our warehouse to your home.', 'ayur-wellness'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. FAQ Accordion (Clean & Minimal) -->
    <section class="ayur-section ayur-faq-section">
        <div class="ayur-container ayur-faq-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('FREQUENTLY ASKED QUESTIONS', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('Clear Answers & Guidance', 'ayur-wellness'); ?></h2>
            </div>

            <div class="ayur-faq-list">
                <details class="ayur-faq-details" open>
                    <summary class="ayur-faq-summary">
                        <span><?php _e('Are your formulations 100% vegetarian and chemical-free?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p>
                            <?php _e('Yes. All our capsules use 100% plant-derived cellulose (HPMC). We use zero animal gelatin, zero synthetic colors, zero artificial binders, and zero parabens.', 'ayur-wellness'); ?>
                        </p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('How long does delivery take across India?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p>
                            <?php _e('Orders are dispatched within 24 hours from our Maharashtra warehouse via integrated couriers (Delhivery, BlueDart, DTDC). Metro deliveries take 2–3 business days, while non-metro locations take 3–5 business days. Tracking is provided via SMS and WhatsApp.', 'ayur-wellness'); ?>
                        </p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('Can I pay using Cash on Delivery (COD)?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p>
                            <?php _e('Yes! Cash on Delivery is available across serviceable Indian pincodes. Prepaid orders via UPI, Cards, and Net Banking enjoy prioritized express dispatch.', 'ayur-wellness'); ?>
                        </p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('How can I contact customer support for dosage questions?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p>
                            <?php _e('You can reach our customer support team directly at +91 8888686858, via WhatsApp, or by emailing vijaykulal8833@gmail.com. We are available Monday through Saturday from 9:00 AM to 7:00 PM IST.', 'ayur-wellness'); ?>
                        </p>
                    </div>
                </details>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
