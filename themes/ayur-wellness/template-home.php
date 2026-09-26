<?php
/**
 * Template Name: Ayurvedic High-Converting Homepage
 * Premium D2C Ecommerce Architecture — Bhagyoday Ayurveda
 * Phase 7: Conversion System & Information Hierarchy
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

// Fetch Bundle products for Value Bundles section
$bundles_query = new WP_Query(array(
    'post_type'      => 'product',
    'tax_query'      => array(
        array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => 'combos',
        ),
    ),
    'posts_per_page' => 2,
));
?>

<main id="primary" class="site-main ayur-homepage">

    <!-- 1. Hero Section (Editorial Typography + Commercial CTAs + Authentic Product Spotlight) -->
    <section class="ayur-hero">
        <div class="ayur-container">
            <div class="ayur-hero-grid">
                <!-- Left: Headline, Value Proposition & Primary CTAs -->
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
                        <a href="#ayurFormulations" class="ayur-btn-primary">
                            <span><?php _e('SHOP BESTSELLERS', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 15); ?>
                        </a>
                        <a href="#ayurWellness" class="ayur-btn-secondary">
                            <span><?php _e('EXPLORE WELLNESS', 'ayur-wellness'); ?></span>
                        </a>
                    </div>
                </div>

                <!-- Right: Featured Authentic Product Image & Direct Link -->
                <div class="ayur-hero-visual">
                    <div class="ayur-hero-stage">
                        <?php if ($hero_product) : 
                            $h_id = $hero_product->get_id();
                            $h_price = (float) $hero_product->get_price();
                            $h_mrp = (float) get_post_meta($h_id, '_ayur_mrp', true);
                            $h_net_qty = get_post_meta($h_id, '_ayur_net_quantity', true);
                            $h_save = ($h_mrp > $h_price) ? ($h_mrp - $h_price) : 0;
                            $h_discount = ($h_mrp > 0 && $h_save > 0) ? round(($h_save / $h_mrp) * 100) : 0;
                        ?>
                            <div class="ayur-hero-spotlight-card">
                                <div class="ayur-hero-spotlight-badge-row">
                                    <span class="ayur-hero-badge"><?php _e('SPOTLIGHT FORMULATION', 'ayur-wellness'); ?></span>
                                    <?php if ($h_discount > 0) : ?>
                                        <span class="ayur-hero-save-badge"><?php printf(__('%d%% OFF', 'ayur-wellness'), $h_discount); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="ayur-hero-spotlight-img-wrap">
                                    <a href="<?php echo esc_url($hero_product->get_permalink()); ?>" aria-label="<?php echo esc_attr($hero_product->get_name()); ?>">
                                        <?php if (has_post_thumbnail($h_id)) : ?>
                                            <?php echo get_the_post_thumbnail($h_id, 'large', array(
                                                'class' => 'ayur-hero-img',
                                                'fetchpriority' => 'high',
                                                'alt' => esc_attr($hero_product->get_name())
                                            )); ?>
                                        <?php else : ?>
                                            <div class="ayur-hero-placeholder">
                                                <?php echo ayur_svg_icon('leaf', '', 48); ?>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                </div>

                                <div class="ayur-hero-spotlight-meta">
                                    <h3 class="ayur-hero-spotlight-title">
                                        <a href="<?php echo esc_url($hero_product->get_permalink()); ?>">
                                            <?php echo esc_html($hero_product->get_name()); ?>
                                        </a>
                                    </h3>

                                    <div class="ayur-hero-spotlight-pricing">
                                        <span class="ayur-hero-price">₹<?php echo number_format($h_price); ?></span>
                                        <?php if ($h_mrp > $h_price) : ?>
                                            <span class="ayur-hero-mrp">MRP ₹<?php echo number_format($h_mrp); ?></span>
                                            <span class="ayur-hero-save-tag"><?php printf(__('Save ₹%s', 'ayur-wellness'), number_format($h_save)); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="ayur-hero-spotlight-cta">
                                        <a href="<?php echo esc_url($hero_product->get_permalink()); ?>" class="ayur-hero-link-btn">
                                            <span><?php _e('VIEW FORMULATION', 'ayur-wellness'); ?></span>
                                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Featured Formulation Merchandising Block (Immediately After Hero) -->
    <?php if ($hero_product) : 
        $feat_cats = get_the_terms($h_id, 'product_cat');
        $feat_cat_name = '';
        if (!empty($feat_cats) && !is_wp_error($feat_cats)) {
            foreach ($feat_cats as $fc) {
                if ($fc->slug !== 'uncategorized') {
                    $feat_cat_name = $fc->name;
                    break;
                }
            }
        }
    ?>
    <section class="ayur-section ayur-featured-formulation-section">
        <div class="ayur-container">
            <div class="ayur-featured-block">
                <div class="ayur-featured-img-col">
                    <a href="<?php echo esc_url($hero_product->get_permalink()); ?>" aria-label="<?php echo esc_attr($hero_product->get_name()); ?>">
                        <?php if (has_post_thumbnail($h_id)) : ?>
                            <?php echo get_the_post_thumbnail($h_id, 'large', array(
                                'class' => 'ayur-featured-img',
                                'loading' => 'lazy',
                                'alt' => esc_attr($hero_product->get_name())
                            )); ?>
                        <?php else : ?>
                            <div class="ayur-featured-placeholder">
                                <?php echo ayur_svg_icon('leaf', '', 48); ?>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="ayur-featured-content-col">
                    <span class="ayur-featured-eyebrow"><?php _e('FEATURED FORMULATION', 'ayur-wellness'); ?></span>
                    
                    <?php if ($feat_cat_name) : ?>
                        <span class="ayur-featured-category"><?php echo esc_html($feat_cat_name); ?></span>
                    <?php endif; ?>

                    <h2 class="ayur-featured-title">
                        <a href="<?php echo esc_url($hero_product->get_permalink()); ?>">
                            <?php echo esc_html($hero_product->get_name()); ?>
                        </a>
                    </h2>

                    <?php if ($h_net_qty) : ?>
                        <div class="ayur-featured-pack-size">
                            <?php echo ayur_svg_icon('package', '', 14); ?>
                            <span><?php echo esc_html($h_net_qty); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="ayur-featured-pricing-row">
                        <span class="ayur-featured-selling-price">₹<?php echo number_format($h_price); ?></span>
                        <?php if ($h_mrp > $h_price) : ?>
                            <span class="ayur-featured-mrp">MRP ₹<?php echo number_format($h_mrp); ?></span>
                            <span class="ayur-featured-save-badge"><?php printf(__('SAVE ₹%s', 'ayur-wellness'), number_format($h_save)); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="ayur-featured-actions">
                        <a href="<?php echo esc_url($hero_product->add_to_cart_url()); ?>"
                           data-quantity="1"
                           class="ayur-btn-primary ayur-btn-quick-add ajax_add_to_cart add_to_cart_button"
                           data-product_id="<?php echo esc_attr($h_id); ?>"
                           data-product_sku="<?php echo esc_attr($hero_product->get_sku()); ?>"
                           aria-label="<?php echo esc_attr($hero_product->add_to_cart_description()); ?>">
                            <?php echo ayur_svg_icon('shopping-bag', '', 15); ?>
                            <span><?php _e('ADD TO CART', 'ayur-wellness'); ?></span>
                        </a>

                        <a href="<?php echo esc_url($hero_product->get_permalink()); ?>" class="ayur-btn-secondary">
                            <span><?php _e('VIEW FORMULATION', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 3. Trust Strip (4 Compact Trust Points Around First Product Opportunity) -->
    <section class="ayur-micro-trust-strip" aria-label="<?php esc_attr_e('Brand Trust Guarantees', 'ayur-wellness'); ?>">
        <div class="ayur-container">
            <div class="ayur-micro-trust-grid">
                <div class="ayur-micro-trust-item">
                    <span class="ayur-trust-check-icon"><?php echo ayur_svg_icon('badge-check', '', 18); ?></span>
                    <span class="ayur-micro-trust-label"><?php _e('AUTHENTIC FORMULATIONS', 'ayur-wellness'); ?></span>
                </div>
                <div class="ayur-micro-trust-item">
                    <span class="ayur-trust-check-icon"><?php echo ayur_svg_icon('shield-check', '', 18); ?></span>
                    <span class="ayur-micro-trust-label"><?php _e('SECURE PAYMENTS', 'ayur-wellness'); ?></span>
                </div>
                <div class="ayur-micro-trust-item">
                    <span class="ayur-trust-check-icon"><?php echo ayur_svg_icon('credit-card', '', 18); ?></span>
                    <span class="ayur-micro-trust-label"><?php _e('COD AVAILABLE', 'ayur-wellness'); ?></span>
                </div>
                <div class="ayur-micro-trust-item">
                    <span class="ayur-trust-check-icon"><?php echo ayur_svg_icon('truck', '', 18); ?></span>
                    <span class="ayur-micro-trust-label"><?php _e('PAN-INDIA DELIVERY', 'ayur-wellness'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Shop by Wellness (3x2 Desktop, 2x3 Mobile Category Discovery) -->
    <section class="ayur-section ayur-categories-section" id="ayurWellness">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('TARGETED DAILY CARE', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('SHOP BY WELLNESS', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Select a category to explore authentic remedies tailored for your personal routine.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-categories-grid">
                <!-- 1. Men's Wellness -->
                <a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-icon-wrap">
                        <?php echo ayur_svg_icon('zap', 'ayur-cat-icon', 22); ?>
                    </div>
                    <div class="ayur-cat-content">
                        <h3 class="ayur-cat-title"><?php _e("Men's Wellness", 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Traditional Shilajit & Ashwagandha formulations for everyday strength.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 13); ?>
                        </span>
                    </div>
                </a>

                <!-- 2. Women's Wellness -->
                <a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-icon-wrap">
                        <?php echo ayur_svg_icon('sparkles', 'ayur-cat-icon', 22); ?>
                    </div>
                    <div class="ayur-cat-content">
                        <h3 class="ayur-cat-title"><?php _e("Women's Wellness", 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Classical Shatavari & Ashoka botanicals for internal harmony.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 13); ?>
                        </span>
                    </div>
                </a>

                <!-- 3. Metabolism & Balance -->
                <a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-icon-wrap">
                        <?php echo ayur_svg_icon('flame', 'ayur-cat-icon', 22); ?>
                    </div>
                    <div class="ayur-cat-content">
                        <h3 class="ayur-cat-title"><?php _e('Metabolism & Balance', 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Karela Jamun & Slim Care extracts supporting metabolic balance.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 13); ?>
                        </span>
                    </div>
                </a>

                <!-- 4. Digestive & Gut Comfort -->
                <a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-icon-wrap">
                        <?php echo ayur_svg_icon('shield-check', 'ayur-cat-icon', 22); ?>
                    </div>
                    <div class="ayur-cat-content">
                        <h3 class="ayur-cat-title"><?php _e('Digestive & Gut Comfort', 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Triphala & Aloe Vera botanicals for easy digestion and soothing relief.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 13); ?>
                        </span>
                    </div>
                </a>

                <!-- 5. Heart & Vitality -->
                <a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-icon-wrap">
                        <?php echo ayur_svg_icon('heart', 'ayur-cat-icon', 22); ?>
                    </div>
                    <div class="ayur-cat-content">
                        <h3 class="ayur-cat-title"><?php _e('Heart & Vitality', 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Pure Arjuna bark decoction for cardiovascular resilience and tone.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 13); ?>
                        </span>
                    </div>
                </a>

                <!-- 6. Joint Mobility -->
                <a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-icon-wrap">
                        <?php echo ayur_svg_icon('package', 'ayur-cat-icon', 22); ?>
                    </div>
                    <div class="ayur-cat-content">
                        <h3 class="ayur-cat-title"><?php _e('Joint Mobility', 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Shallaki Boswellia serrata resin for joint ease and mobility.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 13); ?>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- 5. Explore Formulations (Main Merchandising Product Grid: 4 Desktop / 2 Mobile) -->
    <section class="ayur-section ayur-products-section" id="ayurFormulations">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('STANDARDIZED BOTANICALS', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('EXPLORE OUR FORMULATIONS', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Thoughtfully selected Ayurvedic wellness products for everyday routines.', 'ayur-wellness'); ?></p>
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

    <!-- 6. Why Bhagyoday (4 Concise Value Pillars) -->
    <section class="ayur-section ayur-why-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('OUR COMMITMENT', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('WHY BHAGYODAY AYURVEDA', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Direct from Maharashtra, crafted with authentic botanical integrity.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-why-grid">
                <div class="ayur-why-card">
                    <div class="ayur-why-icon-wrap">
                        <?php echo ayur_svg_icon('leaf', '', 24); ?>
                    </div>
                    <h3 class="ayur-why-title"><?php _e('AUTHENTIC FORMULATIONS', 'ayur-wellness'); ?></h3>
                    <p class="ayur-why-desc"><?php _e('Carefully presented Ayurvedic formulations prepared in small batches without synthetic compromises.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-why-card">
                    <div class="ayur-why-icon-wrap">
                        <?php echo ayur_svg_icon('badge-check', '', 24); ?>
                    </div>
                    <h3 class="ayur-why-title"><?php _e('CLEAR PRODUCT INFORMATION', 'ayur-wellness'); ?></h3>
                    <p class="ayur-why-desc"><?php _e('Ingredients, usage and specifications presented clearly with complete transparency.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-why-card">
                    <div class="ayur-why-icon-wrap">
                        <?php echo ayur_svg_icon('truck', '', 24); ?>
                    </div>
                    <h3 class="ayur-why-title"><?php _e('PAN-INDIA DELIVERY', 'ayur-wellness'); ?></h3>
                    <p class="ayur-why-desc"><?php _e('Delivery across India with reliable dispatch directly from our facility in Maharashtra.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-why-card">
                    <div class="ayur-why-icon-wrap">
                        <?php echo ayur_svg_icon('shield-check', '', 24); ?>
                    </div>
                    <h3 class="ayur-why-title"><?php _e('SECURE CHECKOUT', 'ayur-wellness'); ?></h3>
                    <p class="ayur-why-desc"><?php _e('Razorpay and COD where available for a dependable, transparent shopping experience.', 'ayur-wellness'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Formulation / Botanical Story (Traditional Knowledge, Modern Convenience, Carefully Presented) -->
    <section class="ayur-section ayur-story-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('OUR BOTANICAL PHILOSOPHY', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('Formulation Story', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Bridging classical wisdom with modern routines for pure daily balance.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-story-grid">
                <div class="ayur-story-col">
                    <div class="ayur-story-number">01</div>
                    <h3 class="ayur-story-heading"><?php _e('TRADITIONAL KNOWLEDGE', 'ayur-wellness'); ?></h3>
                    <p class="ayur-story-text">
                        <?php _e('Rooted in classical Ayurvedic literature, honoring the natural synergy of herbs and time-tested extraction methods.', 'ayur-wellness'); ?>
                    </p>
                </div>

                <div class="ayur-story-col">
                    <div class="ayur-story-number">02</div>
                    <h3 class="ayur-story-heading"><?php _e('MODERN CONVENIENCE', 'ayur-wellness'); ?></h3>
                    <p class="ayur-story-text">
                        <?php _e('Standardized plant-cellulose capsules and calibrated juices formulated for simple, consistent daily consumption.', 'ayur-wellness'); ?>
                    </p>
                </div>

                <div class="ayur-story-col">
                    <div class="ayur-story-number">03</div>
                    <h3 class="ayur-story-heading"><?php _e('CAREFULLY PRESENTED FORMULATIONS', 'ayur-wellness'); ?></h3>
                    <p class="ayur-story-text">
                        <?php _e('Ethically sourced raw ingredients preserved without unnecessary fillers or artificial binders.', 'ayur-wellness'); ?>
                    </p>
                </div>
            </div>

            <div class="ayur-story-action">
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-secondary">
                    <span><?php _e('EXPLORE FORMULATIONS', 'ayur-wellness'); ?></span>
                    <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- 8. Value Bundles Section (Real Store Combos with Dynamically Calculated Savings) -->
    <?php if ($bundles_query->have_posts()) : ?>
        <section class="ayur-section ayur-bundles-section" id="ayurBundles">
            <div class="ayur-container">
                <div class="ayur-section-header">
                    <span class="ayur-section-eyebrow"><?php _e('SYNERGISTIC REGIMENS', 'ayur-wellness'); ?></span>
                    <h2 class="ayur-section-title"><?php _e('WELLNESS, BETTER TOGETHER', 'ayur-wellness'); ?></h2>
                    <p class="ayur-section-desc"><?php _e('Carefully paired formulation duos designed to complement each other for comprehensive daily support.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-bundles-grid">
                    <?php while ($bundles_query->have_posts()) : $bundles_query->the_post(); 
                        $bundle_product = wc_get_product(get_the_ID());
                        $b_id = $bundle_product->get_id();
                        $b_price = (float) $bundle_product->get_price();
                        $b_mrp = (float) get_post_meta($b_id, '_ayur_mrp', true);
                        if (!$b_mrp) {
                            $b_mrp = (float) $bundle_product->get_regular_price();
                        }
                        $b_save = ($b_mrp > $b_price) ? ($b_mrp - $b_price) : 0;
                        $b_pct = ($b_mrp > 0 && $b_save > 0) ? round(($b_save / $b_mrp) * 100) : 0;
                    ?>
                        <div class="ayur-bundle-card">
                            <div class="ayur-bundle-img-col">
                                <a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium_large', array('class' => 'ayur-bundle-img', 'loading' => 'lazy')); ?>
                                    <?php else : ?>
                                        <div class="ayur-bundle-fallback">
                                            <?php echo ayur_svg_icon('package', '', 40); ?>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="ayur-bundle-info-col">
                                <span class="ayur-bundle-badge"><?php _e('VALUE BUNDLE', 'ayur-wellness'); ?></span>
                                <h3 class="ayur-bundle-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <div class="ayur-bundle-desc">
                                    <?php echo wp_trim_words(get_the_excerpt(), 18); ?>
                                </div>

                                <div class="ayur-bundle-pricing">
                                    <span class="ayur-bundle-price">₹<?php echo number_format($b_price); ?></span>
                                    <?php if ($b_mrp > $b_price && $b_save > 0) : ?>
                                        <span class="ayur-bundle-mrp">MRP ₹<?php echo number_format($b_mrp); ?></span>
                                        <span class="ayur-bundle-save"><?php printf(__('Save ₹%s (%d%% OFF)', 'ayur-wellness'), number_format($b_save), $b_pct); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="ayur-bundle-actions">
                                    <a href="<?php the_permalink(); ?>" class="ayur-btn-primary">
                                        <span><?php _e('SHOP BUNDLE', 'ayur-wellness'); ?></span>
                                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- 9. "How to Choose" Guided Discovery (6 Clean Paths) -->
    <section class="ayur-section ayur-how-choose-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('GUIDED WELLNESS PATH', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('NOT SURE WHERE TO START?', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Find the right classical botanical support based on your immediate health priorities.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-choose-grid">
                <a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>" class="ayur-choose-item">
                    <span class="ayur-choose-name"><?php _e("MEN'S WELLNESS", 'ayur-wellness'); ?></span>
                    <span class="ayur-choose-arrow">
                        <span><?php _e('Explore', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>" class="ayur-choose-item">
                    <span class="ayur-choose-name"><?php _e('DIGESTIVE SUPPORT', 'ayur-wellness'); ?></span>
                    <span class="ayur-choose-arrow">
                        <span><?php _e('Explore', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>" class="ayur-choose-item">
                    <span class="ayur-choose-name"><?php _e('METABOLISM', 'ayur-wellness'); ?></span>
                    <span class="ayur-choose-arrow">
                        <span><?php _e('Explore', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>" class="ayur-choose-item">
                    <span class="ayur-choose-name"><?php _e('HEART & VITALITY', 'ayur-wellness'); ?></span>
                    <span class="ayur-choose-arrow">
                        <span><?php _e('Explore', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>" class="ayur-choose-item">
                    <span class="ayur-choose-name"><?php _e('JOINT MOBILITY', 'ayur-wellness'); ?></span>
                    <span class="ayur-choose-arrow">
                        <span><?php _e('Explore', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>" class="ayur-choose-item">
                    <span class="ayur-choose-name"><?php _e("WOMEN'S WELLNESS", 'ayur-wellness'); ?></span>
                    <span class="ayur-choose-arrow">
                        <span><?php _e('Explore', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- 10. FAQ Accordions (Real Store Policies, Compact & Trustworthy) -->
    <section class="ayur-section ayur-faq-section">
        <div class="ayur-container ayur-faq-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('HELP & FREQUENT QUESTIONS', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('Frequently Asked Questions', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Clear answers regarding orders, delivery, payment, and formulation usage.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-faq-list">
                <details class="ayur-faq-details" open>
                    <summary class="ayur-faq-summary">
                        <span><?php _e('Do you offer COD?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('Yes! Cash on Delivery is available across most serviceable pin codes throughout India. You can easily choose COD at checkout and pay the courier executive when your package arrives.', 'ayur-wellness'); ?></p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('Where do you deliver?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('We ship Pan-India covering all major states and Union Territories through trusted logistics partners. Free delivery is applied automatically on all orders above ₹999.', 'ayur-wellness'); ?></p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('How long does delivery take?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('Orders placed before 2:00 PM IST are dispatched within 24 business hours from our central warehouse in Maharashtra. Metro cities typically receive deliveries within 2–3 business days, while non-metro locations take 3–5 business days.', 'ayur-wellness'); ?></p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('How can I track my order?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('As soon as your parcel is dispatched from our facility, you will receive an automated tracking link via SMS and WhatsApp. You can follow your package in real-time until delivery.', 'ayur-wellness'); ?></p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('What payment methods are available?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('We support all major payment options via encrypted Razorpay gateway: UPI (Google Pay, PhonePe, Paytm, BHIM), Credit/Debit Cards (Visa, Mastercard, RuPay), Net Banking, and Cash on Delivery (COD).', 'ayur-wellness'); ?></p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('What is the return/replacement policy?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('Because our products are herbal dietary wellness supplements, we accept returns/replacements for items that arrive damaged during transit or with an unsealed tamper sticker. Simply reach out to customer support within 7 days of delivery with parcel photos.', 'ayur-wellness'); ?></p>
                    </div>
                </details>
            </div>
        </div>
    </section>

    <!-- 11. Final Shopping CTA (Explore Authentic Formulations) -->
    <section class="ayur-final-cta-section">
        <div class="ayur-container">
            <div class="ayur-final-cta-card">
                <span class="ayur-section-eyebrow"><?php _e('EMBRACE TIME-TESTED WELLNESS', 'ayur-wellness'); ?></span>
                <h2 class="ayur-final-cta-title"><?php _e('EXPLORE AUTHENTIC FORMULATIONS', 'ayur-wellness'); ?></h2>
                <p class="ayur-final-cta-desc">
                    <?php _e('"Discover Ayurvedic wellness products thoughtfully presented for modern routines."', 'ayur-wellness'); ?>
                </p>
                <div class="ayur-final-cta-actions">
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary">
                        <span><?php _e('SHOP ALL FORMULATIONS', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 15); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
