<?php
/**
 * Template Name: Ayurvedic High-Converting Homepage
 * Premium D2C Ecommerce Architecture — Bhagyoday Ayurveda
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

    <!-- 1. High-Converting Hero Section (Editorial Typography + Commercial CTAs) -->
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
                        <a href="#ayurBestsellers" class="ayur-btn-primary">
                            <span><?php _e('SHOP BESTSELLERS', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 15); ?>
                        </a>
                        <a href="#ayurCategories" class="ayur-btn-secondary">
                            <span><?php _e('EXPLORE WELLNESS', 'ayur-wellness'); ?></span>
                        </a>
                    </div>
                </div>

                <!-- Right: Featured Formulation Sales Merchandising Module -->
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
                            <div class="ayur-hero-sales-module">
                                <div class="ayur-hero-sales-badge-row">
                                    <span class="ayur-hero-badge"><?php _e('FEATURED FORMULATION', 'ayur-wellness'); ?></span>
                                    <?php if ($h_discount > 0) : ?>
                                        <span class="ayur-hero-save-badge"><?php printf(__('%d%% OFF', 'ayur-wellness'), $h_discount); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="ayur-hero-product-img-wrap">
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

                                <div class="ayur-hero-sales-content">
                                    <h3 class="ayur-hero-sales-title">
                                        <a href="<?php echo esc_url($hero_product->get_permalink()); ?>">
                                            <?php echo esc_html($hero_product->get_name()); ?>
                                        </a>
                                    </h3>

                                    <?php if ($h_net_qty) : ?>
                                        <div class="ayur-hero-sales-spec">
                                            <?php echo ayur_svg_icon('package', '', 13); ?>
                                            <span><?php echo esc_html($h_net_qty); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="ayur-hero-sales-pricing">
                                        <span class="ayur-hero-price">₹<?php echo number_format($h_price); ?></span>
                                        <?php if ($h_mrp > $h_price) : ?>
                                            <span class="ayur-hero-mrp">MRP ₹<?php echo number_format($h_mrp); ?></span>
                                            <span class="ayur-hero-save-tag"><?php printf(__('Save ₹%s', 'ayur-wellness'), number_format($h_save)); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="ayur-hero-sales-actions">
                                        <a href="<?php echo esc_url($hero_product->add_to_cart_url()); ?>"
                                           data-quantity="1"
                                           class="ayur-btn-primary ayur-btn-quick-add ajax_add_to_cart add_to_cart_button"
                                           data-product_id="<?php echo esc_attr($h_id); ?>"
                                           data-product_sku="<?php echo esc_attr($hero_product->get_sku()); ?>"
                                           aria-label="<?php echo esc_attr($hero_product->add_to_cart_description()); ?>">
                                            <?php echo ayur_svg_icon('shopping-bag', '', 15); ?>
                                            <span><?php _e('ADD TO CART', 'ayur-wellness'); ?></span>
                                        </a>

                                        <a href="<?php echo esc_url(add_query_arg(array('ayur_quick_buy' => $h_id), home_url('/'))); ?>" class="ayur-btn-secondary ayur-btn-buy-now">
                                            <span><?php _e('BUY NOW', 'ayur-wellness'); ?></span>
                                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                                        </a>
                                    </div>

                                    <p class="ayur-hero-sales-trust">
                                        <?php echo ayur_svg_icon('shield-check', '', 13); ?>
                                        <span><?php _e('Secure checkout &bull; COD available &bull; Pan-India delivery', 'ayur-wellness'); ?></span>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Compact Micro Trust Strip (Reassures User Before Browsing) -->
    <section class="ayur-micro-trust-strip" aria-label="<?php esc_attr_e('Brand Trust Promises', 'ayur-wellness'); ?>">
        <div class="ayur-container">
            <div class="ayur-micro-trust-grid">
                <div class="ayur-micro-trust-item">
                    <span class="ayur-trust-check-icon"><?php echo ayur_svg_icon('badge-check', '', 18); ?></span>
                    <span class="ayur-micro-trust-label"><?php _e('Authentic Ayurvedic Formulations', 'ayur-wellness'); ?></span>
                </div>
                <div class="ayur-micro-trust-item">
                    <span class="ayur-trust-check-icon"><?php echo ayur_svg_icon('truck', '', 18); ?></span>
                    <span class="ayur-micro-trust-label"><?php _e('Pan-India Delivery', 'ayur-wellness'); ?></span>
                </div>
                <div class="ayur-micro-trust-item">
                    <span class="ayur-trust-check-icon"><?php echo ayur_svg_icon('credit-card', '', 18); ?></span>
                    <span class="ayur-micro-trust-label"><?php _e('COD Available', 'ayur-wellness'); ?></span>
                </div>
                <div class="ayur-micro-trust-item">
                    <span class="ayur-trust-check-icon"><?php echo ayur_svg_icon('shield-check', '', 18); ?></span>
                    <span class="ayur-micro-trust-label"><?php _e('Secure Payments', 'ayur-wellness'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Why Bhagyoday (4 Concise Value Pillars) -->
    <section class="ayur-section ayur-why-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('OUR CORE COMMITMENT', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('Why Bhagyoday Ayurveda', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Direct from Maharashtra, crafted with authentic botanical integrity.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-why-grid">
                <div class="ayur-why-card">
                    <div class="ayur-why-icon-wrap">
                        <?php echo ayur_svg_icon('leaf', '', 24); ?>
                    </div>
                    <h3 class="ayur-why-title"><?php _e('Authentic Formulations', 'ayur-wellness'); ?></h3>
                    <p class="ayur-why-desc"><?php _e('Carefully presented Ayurvedic formulations prepared in small batches without synthetic compromises.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-why-card">
                    <div class="ayur-why-icon-wrap">
                        <?php echo ayur_svg_icon('badge-check', '', 24); ?>
                    </div>
                    <h3 class="ayur-why-title"><?php _e('Quality Focus', 'ayur-wellness'); ?></h3>
                    <p class="ayur-why-desc"><?php _e('Clear product information, active botanical details, and 100% vegetarian plant cellulose capsules.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-why-card">
                    <div class="ayur-why-icon-wrap">
                        <?php echo ayur_svg_icon('truck', '', 24); ?>
                    </div>
                    <h3 class="ayur-why-title"><?php _e('Pan-India Delivery', 'ayur-wellness'); ?></h3>
                    <p class="ayur-why-desc"><?php _e('Reliable express dispatch across 26,000+ Indian pincodes with real-time SMS & WhatsApp tracking.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-why-card">
                    <div class="ayur-why-icon-wrap">
                        <?php echo ayur_svg_icon('shield-check', '', 24); ?>
                    </div>
                    <h3 class="ayur-why-title"><?php _e('Secure Checkout', 'ayur-wellness'); ?></h3>
                    <p class="ayur-why-desc"><?php _e('Multiple payment options including UPI, Cards, Net Banking, and convenient Cash on Delivery.', 'ayur-wellness'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Shop by Wellness (Commercially Actionable Category Navigation) -->
    <section class="ayur-section ayur-categories-section" id="ayurCategories">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('TARGETED DAILY CARE', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('Shop by Wellness', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Select a category to explore authentic remedies tailored for your personal routine.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-categories-grid">
                <!-- 1. Men's Wellness -->
                <a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>" class="ayur-cat-block">
                    <div class="ayur-cat-icon-wrap">
                        <?php echo ayur_svg_icon('zap', 'ayur-cat-icon', 22); ?>
                    </div>
                    <div class="ayur-cat-content">
                        <span class="ayur-cat-tag"><?php _e('STAMINA & VITALITY', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e("Men's Wellness", 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Traditional Shilajit & Ashwagandha formulations for everyday strength.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e("SHOP MEN'S WELLNESS", 'ayur-wellness'); ?></span>
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
                        <span class="ayur-cat-tag"><?php _e('HORMONAL RHYTHM', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e("Women's Wellness", 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Classical Shatavari & Ashoka botanicals for internal harmony.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e("SHOP WOMEN'S WELLNESS", 'ayur-wellness'); ?></span>
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
                        <span class="ayur-cat-tag"><?php _e('METABOLIC DETOX', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e('Metabolism & Balance', 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Karela Jamun & Slim Care extracts supporting metabolic balance.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP METABOLISM', 'ayur-wellness'); ?></span>
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
                        <span class="ayur-cat-tag"><?php _e('GUT & DIGESTIVE COMFORT', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e('Digestive & Gut Comfort', 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Triphala & Aloe Vera botanicals for easy digestion and soothing relief.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP DIGESTIVE CARE', 'ayur-wellness'); ?></span>
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
                        <span class="ayur-cat-tag"><?php _e('CARDIO & IMMUNITY', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e('Heart & Vitality', 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Pure Arjuna bark decoction for cardiovascular resilience and tone.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP HEART HEALTH', 'ayur-wellness'); ?></span>
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
                        <span class="ayur-cat-tag"><?php _e('FLEXIBILITY & COMFORT', 'ayur-wellness'); ?></span>
                        <h3 class="ayur-cat-title"><?php _e('Joint Mobility', 'ayur-wellness'); ?></h3>
                        <p class="ayur-cat-summary"><?php _e('Shallaki Boswellia serrata resin for joint ease and mobility.', 'ayur-wellness'); ?></p>
                        <span class="ayur-cat-link-text">
                            <span><?php _e('SHOP JOINT CARE', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 13); ?>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- 5. Bestselling / Featured Formulations Grid (2-Column Mobile, High-Converting) -->
    <section class="ayur-section ayur-products-section" id="ayurBestsellers">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('STANDARDIZED BOTANICALS', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('Explore Our Formulations', 'ayur-wellness'); ?></h2>
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

    <!-- 6. Formulation Benefits & Botanical Ingredient Story -->
    <section class="ayur-section ayur-ingredient-story-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('AUTHENTIC INGREDIENT SOURCING', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('The Science of Key Botanicals', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Every herb is authenticated for purity, preserving the full phytochemical matrix for natural bioavailability.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-ingredient-grid">
                <div class="ayur-ingredient-card">
                    <span class="ayur-ingredient-origin"><?php _e('Himalayan Altitudes', 'ayur-wellness'); ?></span>
                    <h3 class="ayur-ingredient-name"><?php _e('Purified Shilajit', 'ayur-wellness'); ?></h3>
                    <span class="ayur-ingredient-type"><?php _e('Mineral-Rich Traditional Ingredient', 'ayur-wellness'); ?></span>
                    <p class="ayur-ingredient-desc"><?php _e('Rich in natural fulvic acid and trace minerals. Traditionally used in Ayurveda to support physical stamina, vitality, and cellular energy.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-ingredient-card">
                    <span class="ayur-ingredient-origin"><?php _e('Western Ghats', 'ayur-wellness'); ?></span>
                    <h3 class="ayur-ingredient-name"><?php _e('KSM-66 Ashwagandha', 'ayur-wellness'); ?></h3>
                    <span class="ayur-ingredient-type"><?php _e('Traditional Adaptogenic Botanical', 'ayur-wellness'); ?></span>
                    <p class="ayur-ingredient-desc"><?php _e('Standardized withanolides extracted from mature root stock. Reassures bodily equilibrium during stressful daily routines.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-ingredient-card">
                    <span class="ayur-ingredient-origin"><?php _e('Maharashtra Forests', 'ayur-wellness'); ?></span>
                    <h3 class="ayur-ingredient-name"><?php _e('Arjuna Bark Extract', 'ayur-wellness'); ?></h3>
                    <span class="ayur-ingredient-type"><?php _e('Classical Cardio-Protective Botanical', 'ayur-wellness'); ?></span>
                    <p class="ayur-ingredient-desc"><?php _e('Extracted from wild Terminalia arjuna bark, naturally abundant in bioflavonoids to support everyday heart vitality and healthy circulation.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-ingredient-card">
                    <span class="ayur-ingredient-origin"><?php _e('Classical Blend', 'ayur-wellness'); ?></span>
                    <h3 class="ayur-ingredient-name"><?php _e('Triphala & Aloe Vera', 'ayur-wellness'); ?></h3>
                    <span class="ayur-ingredient-type"><?php _e('Gentle Digestive Botanicals', 'ayur-wellness'); ?></span>
                    <p class="ayur-ingredient-desc"><?php _e('A time-honored synergy of Amla, Haritaki, and Bibhitaki providing gentle gut cleansing, regular bowel habits, and soothing digestive relief.', 'ayur-wellness'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Value Bundles Section (Real Store Combos with Mathematically Verified Savings) -->
    <?php if ($bundles_query->have_posts()) : ?>
        <section class="ayur-section ayur-bundles-section" id="ayurBundles">
            <div class="ayur-container">
                <div class="ayur-section-header">
                    <span class="ayur-section-eyebrow"><?php _e('SYNERGISTIC REGIMENS', 'ayur-wellness'); ?></span>
                    <h2 class="ayur-section-title"><?php _e('Wellness, Better Together', 'ayur-wellness'); ?></h2>
                    <p class="ayur-section-desc"><?php _e('Carefully paired formulation duos designed to complement each other for comprehensive daily support.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-bundles-grid">
                    <?php while ($bundles_query->have_posts()) : $bundles_query->the_post(); 
                        $bundle_product = wc_get_product(get_the_ID());
                        $b_id = $bundle_product->get_id();
                        $b_price = (float) $bundle_product->get_price();
                        $b_mrp = (float) get_post_meta($b_id, '_ayur_mrp', true);
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
                                <span class="ayur-bundle-badge"><?php _e('COMPLETE CARE DUO', 'ayur-wellness'); ?></span>
                                <h3 class="ayur-bundle-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <div class="ayur-bundle-desc">
                                    <?php echo wp_trim_words(get_the_excerpt(), 18); ?>
                                </div>

                                <div class="ayur-bundle-pricing">
                                    <span class="ayur-bundle-price">₹<?php echo number_format($b_price); ?></span>
                                    <?php if ($b_mrp > $b_price) : ?>
                                        <span class="ayur-bundle-mrp">MRP ₹<?php echo number_format($b_mrp); ?></span>
                                        <span class="ayur-bundle-save"><?php printf(__('Save ₹%s (%d%% OFF)', 'ayur-wellness'), number_format($b_save), $b_pct); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="ayur-bundle-actions">
                                    <a href="<?php echo esc_url($bundle_product->add_to_cart_url()); ?>"
                                       data-quantity="1"
                                       class="ayur-btn-primary ayur-btn-quick-add ajax_add_to_cart add_to_cart_button"
                                       data-product_id="<?php echo esc_attr($b_id); ?>"
                                       data-product_sku="<?php echo esc_attr($bundle_product->get_sku()); ?>"
                                       aria-label="<?php echo esc_attr($bundle_product->add_to_cart_description()); ?>">
                                        <?php echo ayur_svg_icon('shopping-bag', '', 15); ?>
                                        <span><?php _e('ADD TO CART', 'ayur-wellness'); ?></span>
                                    </a>

                                    <a href="<?php echo esc_url(add_query_arg(array('ayur_quick_buy' => $b_id), home_url('/'))); ?>" class="ayur-btn-secondary ayur-btn-buy-now">
                                        <span><?php _e('BUY NOW', 'ayur-wellness'); ?></span>
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

    <!-- 8. "How to Choose" Guided Shopping Module -->
    <section class="ayur-section ayur-how-choose-section">
        <div class="ayur-container">
            <div class="ayur-section-header">
                <span class="ayur-section-eyebrow"><?php _e('GUIDED WELLNESS PATH', 'ayur-wellness'); ?></span>
                <h2 class="ayur-section-title"><?php _e('What Are You Looking For?', 'ayur-wellness'); ?></h2>
                <p class="ayur-section-desc"><?php _e('Find the right classical botanical support based on your immediate health priorities.', 'ayur-wellness'); ?></p>
            </div>

            <div class="ayur-choose-list">
                <a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>" class="ayur-choose-row">
                    <div class="ayur-choose-indicator">
                        <span class="ayur-choose-tag"><?php _e("MEN'S WELLNESS", 'ayur-wellness'); ?></span>
                        <h4 class="ayur-choose-name"><?php _e('Physical Stamina, Vigor & Everyday Energy', 'ayur-wellness'); ?></h4>
                    </div>
                    <span class="ayur-choose-cta">
                        <span><?php _e('Explore formulations', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>" class="ayur-choose-row">
                    <div class="ayur-choose-indicator">
                        <span class="ayur-choose-tag"><?php _e('DIGESTIVE COMFORT', 'ayur-wellness'); ?></span>
                        <h4 class="ayur-choose-name"><?php _e('Easy Gut Transit, Soothing Regularity & Relief', 'ayur-wellness'); ?></h4>
                    </div>
                    <span class="ayur-choose-cta">
                        <span><?php _e('Explore formulations', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>" class="ayur-choose-row">
                    <div class="ayur-choose-indicator">
                        <span class="ayur-choose-tag"><?php _e('METABOLISM & BALANCE', 'ayur-wellness'); ?></span>
                        <h4 class="ayur-choose-name"><?php _e('Healthy Sugar Balance & Metabolic Cleansing', 'ayur-wellness'); ?></h4>
                    </div>
                    <span class="ayur-choose-cta">
                        <span><?php _e('Explore formulations', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>" class="ayur-choose-row">
                    <div class="ayur-choose-indicator">
                        <span class="ayur-choose-tag"><?php _e('JOINT & MOBILITY', 'ayur-wellness'); ?></span>
                        <h4 class="ayur-choose-name"><?php _e('Cartilage Comfort, Bone Strength & Flexibility', 'ayur-wellness'); ?></h4>
                    </div>
                    <span class="ayur-choose-cta">
                        <span><?php _e('Explore formulations', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>" class="ayur-choose-row">
                    <div class="ayur-choose-indicator">
                        <span class="ayur-choose-tag"><?php _e("WOMEN'S WELLNESS", 'ayur-wellness'); ?></span>
                        <h4 class="ayur-choose-name"><?php _e('Hormonal Rhythm, Rejuvenation & Vital Tone', 'ayur-wellness'); ?></h4>
                    </div>
                    <span class="ayur-choose-cta">
                        <span><?php _e('Explore formulations', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>

                <a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>" class="ayur-choose-row">
                    <div class="ayur-choose-indicator">
                        <span class="ayur-choose-tag"><?php _e('HEART & VITALITY', 'ayur-wellness'); ?></span>
                        <h4 class="ayur-choose-name"><?php _e('Cardiovascular Resilience & Natural Immunity', 'ayur-wellness'); ?></h4>
                    </div>
                    <span class="ayur-choose-cta">
                        <span><?php _e('Explore formulations', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- 9. Quality Assurance & Authentic Trust (Truth-in-Advertising: Zero Fake Reviews) -->
    <section class="ayur-section ayur-quality-section">
        <div class="ayur-container">
            <div class="ayur-quality-box">
                <div class="ayur-quality-content">
                    <span class="ayur-section-eyebrow"><?php _e('PHARMACEUTICAL PURITY', 'ayur-wellness'); ?></span>
                    <h2 class="ayur-quality-title"><?php _e('Standardized Herbal Integrity & Quality Assurance', 'ayur-wellness'); ?></h2>
                    <p class="ayur-quality-lead">
                        <?php _e('We believe that genuine wellness requires total transparency. Every Bhagyoday Ayurveda remedy is prepared without synthetic fillers, artificial binders, or deceptive shortcuts.', 'ayur-wellness'); ?>
                    </p>

                    <div class="ayur-quality-features">
                        <div class="ayur-quality-feature">
                            <span class="ayur-qf-icon"><?php echo ayur_svg_icon('check', '', 16); ?></span>
                            <div>
                                <strong><?php _e('100% Plant Cellulose Capsules (HPMC):', 'ayur-wellness'); ?></strong>
                                <span><?php _e('Zero animal gelatin, 100% vegetarian and vegan-friendly.', 'ayur-wellness'); ?></span>
                            </div>
                        </div>

                        <div class="ayur-quality-feature">
                            <span class="ayur-qf-icon"><?php echo ayur_svg_icon('check', '', 16); ?></span>
                            <div>
                                <strong><?php _e('Standardized Botanical Actives:', 'ayur-wellness'); ?></strong>
                                <span><?php _e('Validated marker concentrations ensure consistent potency in every batch.', 'ayur-wellness'); ?></span>
                            </div>
                        </div>

                        <div class="ayur-quality-feature">
                            <span class="ayur-qf-icon"><?php echo ayur_svg_icon('check', '', 16); ?></span>
                            <div>
                                <strong><?php _e('Small-Batch Cleanroom Decocting:', 'ayur-wellness'); ?></strong>
                                <span><?php _e('Prepared with care at our facility in Mantha, Jalna, Maharashtra.', 'ayur-wellness'); ?></span>
                            </div>
                        </div>

                        <div class="ayur-quality-feature">
                            <span class="ayur-qf-icon"><?php echo ayur_svg_icon('check', '', 16); ?></span>
                            <div>
                                <strong><?php _e('Direct Hub Dispatch:', 'ayur-wellness'); ?></strong>
                                <span><?php _e('Shipped in protective tamper-evident packaging straight to your door.', 'ayur-wellness'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 10. FAQ (Conversion-Oriented Accordion with Real Store Policies) -->
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
                        <span><?php _e('Do you offer Cash on Delivery (COD)?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('Yes! Cash on Delivery is available across most serviceable pin codes throughout India. You can easily choose COD at checkout and pay the courier executive when your package arrives.', 'ayur-wellness'); ?></p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('Where do you deliver across India?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('We ship Pan-India covering all major states and Union Territories through trusted logistics partners including Delhivery, BlueDart, and DTDC. Free delivery is applied automatically on all orders above ₹999.', 'ayur-wellness'); ?></p>
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
                        <span><?php _e('How can I track my order once placed?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('As soon as your parcel is dispatched from our facility, you will receive an automated tracking link via SMS and WhatsApp. You can follow your package in real-time until delivery.', 'ayur-wellness'); ?></p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('What payment methods are supported?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('We support all major payment options via encrypted Razorpay gateway: UPI (Google Pay, PhonePe, Paytm, BHIM), Credit/Debit Cards (Visa, Mastercard, RuPay), Net Banking, and Cash on Delivery (COD).', 'ayur-wellness'); ?></p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('What is your return and refund policy?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('Because our products are herbal dietary wellness supplements, we accept returns/replacements for items that arrive damaged during transit or with an unsealed tamper sticker. Simply reach out to customer support within 7 days of delivery with parcel photos.', 'ayur-wellness'); ?></p>
                    </div>
                </details>

                <details class="ayur-faq-details">
                    <summary class="ayur-faq-summary">
                        <span><?php _e('How should products be stored?', 'ayur-wellness'); ?></span>
                        <span class="ayur-faq-arrow"><?php echo ayur_svg_icon('chevron-down', '', 16); ?></span>
                    </summary>
                    <div class="ayur-faq-body">
                        <p><?php _e('Store bottles and juice containers in a cool, dry place away from direct sunlight. Reseal cap tightly after each use. Juices should be refrigerated after opening and consumed within 30 days for maximum freshness.', 'ayur-wellness'); ?></p>
                    </div>
                </details>
            </div>
        </div>
    </section>

    <!-- 11. Final Shipping & COD Trust Strip -->
    <section class="ayur-shipping-trust-section" aria-label="<?php esc_attr_e('Delivery & Payment Guarantees', 'ayur-wellness'); ?>">
        <div class="ayur-container">
            <div class="ayur-trust-blocks-grid">
                <div class="ayur-trust-block">
                    <span class="ayur-trust-block-icon"><?php echo ayur_svg_icon('truck', '', 24); ?></span>
                    <h4 class="ayur-trust-block-title"><?php _e('Pan-India Delivery', 'ayur-wellness'); ?></h4>
                    <p class="ayur-trust-block-sub"><?php _e('Free shipping on orders above ₹999 across India.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-trust-block">
                    <span class="ayur-trust-block-icon"><?php echo ayur_svg_icon('credit-card', '', 24); ?></span>
                    <h4 class="ayur-trust-block-title"><?php _e('Cash on Delivery', 'ayur-wellness'); ?></h4>
                    <p class="ayur-trust-block-sub"><?php _e('Pay easily in cash or UPI when your parcel arrives.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-trust-block">
                    <span class="ayur-trust-block-icon"><?php echo ayur_svg_icon('shield-check', '', 24); ?></span>
                    <h4 class="ayur-trust-block-title"><?php _e('100% Secure Checkout', 'ayur-wellness'); ?></h4>
                    <p class="ayur-trust-block-sub"><?php _e('Bank-grade 256-bit encrypted transactions.', 'ayur-wellness'); ?></p>
                </div>

                <div class="ayur-trust-block">
                    <span class="ayur-trust-block-icon"><?php echo ayur_svg_icon('headphones', '', 24); ?></span>
                    <h4 class="ayur-trust-block-title"><?php _e('Customer Care Support', 'ayur-wellness'); ?></h4>
                    <p class="ayur-trust-block-sub"><?php _e('Helpline assistance available Mon–Sat 9AM–7PM IST.', 'ayur-wellness'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- 12. Final High-Converting Call to Action -->
    <section class="ayur-final-cta-section">
        <div class="ayur-container">
            <div class="ayur-final-cta-card">
                <span class="ayur-section-eyebrow"><?php _e('EMBRACE TIME-TESTED WELLNESS', 'ayur-wellness'); ?></span>
                <h2 class="ayur-final-cta-title"><?php _e('Begin Your Ayurvedic Wellness Routine', 'ayur-wellness'); ?></h2>
                <p class="ayur-final-cta-desc">
                    <?php _e('Experience pure, unadulterated classical Indian botanicals crafted for modern daily balance. Dispatched straight from Maharashtra to your door.', 'ayur-wellness'); ?>
                </p>
                <div class="ayur-final-cta-actions">
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary">
                        <span><?php _e('EXPLORE ALL FORMULATIONS', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('arrow-right', '', 15); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();

