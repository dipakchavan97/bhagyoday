<?php
/**
 * Premium D2C Ayurvedic Single Product Template
 * Editorial Architecture — Bhagyoday Ayurveda
 */

defined('ABSPATH') || exit;

get_header();

while (have_posts()) :
    the_post();
    global $product;

    $product_id    = $product->get_id();
    $price         = (float) $product->get_price();
    $mrp           = get_post_meta($product_id, '_ayur_mrp', true);
    $benefits      = get_post_meta($product_id, '_ayur_benefits', true);
    $ingredients   = get_post_meta($product_id, '_ayur_ingredients', true);
    $dosage        = get_post_meta($product_id, '_ayur_dosage', true);
    $precautions   = get_post_meta($product_id, '_ayur_precautions', true);
    $net_qty       = get_post_meta($product_id, '_ayur_net_quantity', true);
    $cert          = get_post_meta($product_id, '_ayur_certifications', true);
    $batch         = get_post_meta($product_id, '_ayur_batch', true);
    $expiry        = get_post_meta($product_id, '_ayur_expiry', true);

    // Get Primary Category
    $categories = get_the_terms($product_id, 'product_cat');
    $cat_name = '';
    $cat_link = '';
    if (!empty($categories) && !is_wp_error($categories)) {
        foreach ($categories as $term) {
            if ($term->slug !== 'uncategorized') {
                $cat_name = $term->name;
                $cat_link = get_term_link($term);
                break;
            }
        }
    }

    $gallery_ids = $product->get_gallery_image_ids();
    if (has_post_thumbnail()) {
        array_unshift($gallery_ids, get_post_thumbnail_id($product_id));
    }
    $gallery_ids = array_unique(array_filter($gallery_ids));
    ?>

    <article id="product-<?php the_ID(); ?>" <?php wc_product_class('ayur-single-product-wrap', $product); ?>>
        <div class="ayur-container">
            <!-- Subtle Editorial Breadcrumbs -->
            <nav class="ayur-breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'ayur-wellness'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'ayur-wellness'); ?></a>
                <span class="ayur-breadcrumb-sep"><?php echo ayur_svg_icon('chevron-right', '', 11); ?></span>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php _e('Shop', 'ayur-wellness'); ?></a>
                <?php if ($cat_name && !is_wp_error($cat_link)) : ?>
                    <span class="ayur-breadcrumb-sep"><?php echo ayur_svg_icon('chevron-right', '', 11); ?></span>
                    <a href="<?php echo esc_url($cat_link); ?>"><?php echo esc_html($cat_name); ?></a>
                <?php endif; ?>
                <span class="ayur-breadcrumb-sep"><?php echo ayur_svg_icon('chevron-right', '', 11); ?></span>
                <span class="ayur-breadcrumb-current"><?php the_title(); ?></span>
            </nav>

            <div class="ayur-product-layout">
                <!-- Left: Studio Product Gallery -->
                <div class="ayur-gallery-col">
                    <div class="ayur-gallery-stage" id="ayurGalleryStage">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array(
                                'class'         => 'ayur-main-img',
                                'id'            => 'ayurMainProductImg',
                                'fetchpriority' => 'high',
                                'alt'           => esc_attr(get_the_title())
                            )); ?>
                        <?php else : ?>
                            <div class="ayur-gallery-placeholder">
                                <?php echo ayur_svg_icon('leaf', '', 48); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($gallery_ids) > 1) : ?>
                        <div class="ayur-gallery-thumbs" role="region" aria-label="<?php esc_attr_e('Product gallery thumbnails', 'ayur-wellness'); ?>">
                            <?php foreach ($gallery_ids as $index => $att_id) :
                                $thumb_url = wp_get_attachment_image_url($att_id, 'woocommerce_gallery_thumbnail');
                                $large_url = wp_get_attachment_image_url($att_id, 'large');
                                if (!$thumb_url) continue;
                            ?>
                                <button type="button"
                                        class="ayur-gallery-thumb-btn <?php echo $index === 0 ? 'active' : ''; ?>"
                                        data-full="<?php echo esc_url($large_url); ?>"
                                        aria-label="<?php echo esc_attr(sprintf(__('View image %d', 'ayur-wellness'), $index + 1)); ?>">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?> thumbnail <?php echo $index + 1; ?>" loading="lazy" />
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Purchase Information Column -->
                <div class="ayur-product-summary">
                    <?php if ($cat_name) : ?>
                        <span class="ayur-product-eyebrow"><?php echo esc_html(strtoupper($cat_name)); ?></span>
                    <?php endif; ?>

                    <h1 class="ayur-single-title"><?php the_title(); ?></h1>

                    <div class="ayur-product-meta-row">
                        <?php if ($net_qty) : ?>
                            <span class="ayur-net-qty-chip">
                                <?php echo ayur_svg_icon('package', '', 14); ?>
                                <span><?php echo esc_html($net_qty); ?></span>
                            </span>
                        <?php endif; ?>

                        <?php if ($cert) : ?>
                            <span class="ayur-cert-chip">
                                <?php echo ayur_svg_icon('shield-check', '', 14); ?>
                                <span><?php echo esc_html($cert); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Short Editorial Lead -->
                    <div class="ayur-single-excerpt">
                        <?php
                        if (has_excerpt()) {
                            the_excerpt();
                        } else {
                            echo '<p>' . esc_html(wp_trim_words(get_the_content(), 28)) . '</p>';
                        }
                        ?>
                    </div>

                    <!-- Clean Price & Savings -->
                    <div class="ayur-single-pricing-box">
                        <div class="ayur-price-row">
                            <span class="ayur-single-price">₹<?php echo number_format($price); ?></span>
                            <?php if ($mrp && (float)$mrp > $price) : ?>
                                <span class="ayur-single-mrp">MRP ₹<?php echo number_format($mrp); ?></span>
                                <?php
                                $diff = (float)$mrp - $price;
                                ?>
                                <span class="ayur-single-savings">Save ₹<?php echo number_format($diff); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="ayur-price-tax-note"><?php _e('Inclusive of all taxes &bull; Free Pan-India Delivery on orders above ₹999', 'ayur-wellness'); ?></span>
                    </div>

                    <!-- Purchase Actions Form -->
                    <form class="cart ayur-cart-form" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
                        <div class="ayur-purchase-row">
                            <?php if (!$product->is_type('variable')) : ?>
                                <div class="ayur-qty-wrapper">
                                    <label for="ayurQtyInput" class="screen-reader-text"><?php esc_html_e('Quantity', 'woocommerce'); ?></label>
                                    <div class="ayur-qty-stepper">
                                        <button type="button" class="ayur-qty-dec" aria-label="<?php esc_attr_e('Decrease quantity', 'ayur-wellness'); ?>"><?php echo ayur_svg_icon('minus', '', 13); ?></button>
                                        <?php
                                        woocommerce_quantity_input(array(
                                            'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                                            'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                                            'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(),
                                            'classes'     => array('input-text', 'qty', 'text', 'ayur-qty-input'),
                                        ));
                                        ?>
                                        <button type="button" class="ayur-qty-inc" aria-label="<?php esc_attr_e('Increase quantity', 'ayur-wellness'); ?>"><?php echo ayur_svg_icon('plus', '', 13); ?></button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>" class="ayur-btn-add-cart single_add_to_cart_button button alt">
                                <?php echo ayur_svg_icon('shopping-bag', '', 16); ?>
                                <span><?php _e('ADD TO CART', 'ayur-wellness'); ?></span>
                            </button>

                            <a href="<?php echo esc_url(add_query_arg(array('ayur_quick_buy' => $product_id), home_url('/'))); ?>" class="ayur-btn-buy-now">
                                <span><?php _e('BUY NOW', 'ayur-wellness'); ?></span>
                                <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                            </a>
                        </div>
                    </form>

                    <!-- Refined 2x2 Trust Rail (Section 18) -->
                    <div class="ayur-trust-2x2">
                        <div class="ayur-trust-cell">
                            <span class="ayur-cell-icon"><?php echo ayur_svg_icon('shield-check', '', 18); ?></span>
                            <span class="ayur-cell-text"><?php _e('Secure Payments', 'ayur-wellness'); ?></span>
                        </div>
                        <div class="ayur-trust-cell">
                            <span class="ayur-cell-icon"><?php echo ayur_svg_icon('credit-card', '', 18); ?></span>
                            <span class="ayur-cell-text"><?php _e('COD Available', 'ayur-wellness'); ?></span>
                        </div>
                        <div class="ayur-trust-cell">
                            <span class="ayur-cell-icon"><?php echo ayur_svg_icon('truck', '', 18); ?></span>
                            <span class="ayur-cell-text"><?php _e('Pan-India Delivery', 'ayur-wellness'); ?></span>
                        </div>
                        <div class="ayur-trust-cell">
                            <span class="ayur-cell-icon"><?php echo ayur_svg_icon('headphones', '', 18); ?></span>
                            <span class="ayur-cell-text"><?php _e('Customer Support', 'ayur-wellness'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Content Sections: Editorial Accordions & Details (Section 19) -->
            <div class="ayur-product-details-wrap">
                <div class="ayur-details-grid">
                    <!-- Left: Benefits & Ingredients -->
                    <div class="ayur-details-main">
                        <?php if (!empty($benefits)) : ?>
                            <section class="ayur-detail-block">
                                <h3 class="ayur-detail-heading"><?php _e('Therapeutic Benefits', 'ayur-wellness'); ?></h3>
                                <ul class="ayur-benefits-checklist">
                                    <?php
                                    $benefit_lines = explode("\n", $benefits);
                                    foreach ($benefit_lines as $line) {
                                        $clean = trim(ltrim($line, "•- "));
                                        if (!empty($clean)) {
                                            echo '<li><span class="ayur-check-bullet">' . ayur_svg_icon('check', '', 14) . '</span><span>' . esc_html($clean) . '</span></li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </section>
                        <?php endif; ?>

                        <?php if (!empty($ingredients)) : ?>
                            <section class="ayur-detail-block">
                                <h3 class="ayur-detail-heading"><?php _e('Active Botanical Ingredients', 'ayur-wellness'); ?></h3>
                                <div class="ayur-ingredients-content">
                                    <p><?php echo nl2br(esc_html($ingredients)); ?></p>
                                </div>
                            </section>
                        <?php endif; ?>

                        <section class="ayur-detail-block">
                            <h3 class="ayur-detail-heading"><?php _e('Formulation Story & Description', 'ayur-wellness'); ?></h3>
                            <div class="ayur-description-content">
                                <?php the_content(); ?>
                            </div>
                        </section>
                    </div>

                    <!-- Right: Usage, Precautions & Statutory Specs -->
                    <div class="ayur-details-side">
                        <?php if (!empty($dosage)) : ?>
                            <div class="ayur-spec-card">
                                <div class="ayur-spec-header">
                                    <?php echo ayur_svg_icon('clock', '', 18); ?>
                                    <h4><?php _e('Suggested Dosage & Use', 'ayur-wellness'); ?></h4>
                                </div>
                                <p><?php echo esc_html($dosage); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($precautions)) : ?>
                            <div class="ayur-spec-card">
                                <div class="ayur-spec-header">
                                    <?php echo ayur_svg_icon('shield-check', '', 18); ?>
                                    <h4><?php _e('Storage & Precautions', 'ayur-wellness'); ?></h4>
                                </div>
                                <p><?php echo esc_html($precautions); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="ayur-spec-card ayur-statutory-card">
                            <div class="ayur-spec-header">
                                <?php echo ayur_svg_icon('badge-check', '', 18); ?>
                                <h4><?php _e('Product Specifications', 'ayur-wellness'); ?></h4>
                            </div>
                            <dl class="ayur-spec-list">
                                <?php if ($batch) : ?>
                                    <dt><?php _e('Batch / Lot:', 'ayur-wellness'); ?></dt>
                                    <dd><?php echo esc_html($batch); ?></dd>
                                <?php endif; ?>
                                <?php if ($expiry) : ?>
                                    <dt><?php _e('Shelf Life:', 'ayur-wellness'); ?></dt>
                                    <dd><?php echo esc_html($expiry); ?></dd>
                                <?php endif; ?>
                                <dt><?php _e('Country of Origin:', 'ayur-wellness'); ?></dt>
                                <dd><?php _e('India (Jalna, Maharashtra)', 'ayur-wellness'); ?></dd>
                                <dt><?php _e('Formulation Type:', 'ayur-wellness'); ?></dt>
                                <dd><?php _e('Classical Ayurvedic Botanical Actives', 'ayur-wellness'); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Formulations (Clean) -->
            <div class="ayur-related-wrap">
                <div class="ayur-section-header">
                    <span class="ayur-section-eyebrow"><?php _e('COMPLEMENTARY CARE', 'ayur-wellness'); ?></span>
                    <h2 class="ayur-section-title"><?php _e('Related Formulations', 'ayur-wellness'); ?></h2>
                </div>
                <?php woocommerce_output_related_products(); ?>
            </div>
        </div>
    </article>

    <!-- Native Mobile Sticky Bottom Bar (Section 20 & 33) -->
    <div class="ayur-mobile-sticky-bar" id="ayurMobileStickyBar">
        <div class="ayur-sticky-info">
            <span class="ayur-sticky-price">₹<?php echo number_format($price); ?></span>
            <?php if ($mrp && (float)$mrp > $price) : ?>
                <span class="ayur-sticky-mrp">MRP ₹<?php echo number_format($mrp); ?></span>
            <?php endif; ?>
        </div>
        <div class="ayur-sticky-actions">
            <button type="button" class="ayur-sticky-btn ayur-sticky-add-cart" data-product-id="<?php echo esc_attr($product_id); ?>">
                <?php echo ayur_svg_icon('shopping-bag', '', 15); ?>
                <span><?php _e('Add', 'ayur-wellness'); ?></span>
            </button>
            <a href="<?php echo esc_url(add_query_arg(array('ayur_quick_buy' => $product_id), home_url('/'))); ?>" class="ayur-sticky-btn ayur-sticky-buy-now">
                <span><?php _e('Buy Now', 'ayur-wellness'); ?></span>
            </a>
        </div>
    </div>

    <?php
endwhile;

get_footer();
