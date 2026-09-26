<?php
/**
 * Premium D2C Ayurvedic Single Product Template
 * Conversion-First Architecture — Bhagyoday Ayurveda
 */

defined('ABSPATH') || exit;

get_header();

while (have_posts()) :
    the_post();
    global $product;

    $product_id    = $product->get_id();
    $price         = (float) $product->get_price();
    $mrp           = (float) get_post_meta($product_id, '_ayur_mrp', true);
    $benefits      = get_post_meta($product_id, '_ayur_benefits', true);
    $ingredients   = get_post_meta($product_id, '_ayur_ingredients', true);
    $dosage        = get_post_meta($product_id, '_ayur_dosage', true);
    $precautions   = get_post_meta($product_id, '_ayur_precautions', true);
    $net_qty       = get_post_meta($product_id, '_ayur_net_quantity', true);
    $cert          = get_post_meta($product_id, '_ayur_certifications', true);
    $batch         = get_post_meta($product_id, '_ayur_batch', true);
    $expiry        = get_post_meta($product_id, '_ayur_expiry', true);

    $diff          = ($mrp > $price) ? ($mrp - $price) : 0;
    $discount_pct  = ($mrp > 0 && $diff > 0) ? round(($diff / $mrp) * 100) : 0;

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
    $ext_data = ayur_get_product_extended_data($product_id);
    ?>

    <article id="product-<?php the_ID(); ?>" <?php wc_product_class('ayur-single-product-wrap', $product); ?>>
        <div class="ayur-container">
            <!-- 1. Breadcrumbs -->
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
                <!-- 2. Product Gallery with Label Inspection Zoom -->
                <div class="ayur-gallery-col">
                    <div class="ayur-gallery-stage" id="ayurGalleryStage" role="button" tabindex="0" aria-label="<?php esc_attr_e('Click to enlarge and inspect packaging label', 'ayur-wellness'); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array(
                                'class'         => 'ayur-main-img',
                                'id'            => 'ayurMainProductImg',
                                'fetchpriority' => 'high',
                                'alt'           => esc_attr(get_the_title())
                            )); ?>
                            <span class="ayur-gallery-zoom-badge">
                                <?php echo ayur_svg_icon('search', '', 13); ?>
                                <span><?php _e('Inspect Label', 'ayur-wellness'); ?></span>
                            </span>
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

                <!-- 3. Purchase Information Column (Phase 6 High-Conversion Architecture) -->
                <div class="ayur-product-summary">
                    <?php if ($cat_name) : ?>
                        <span class="ayur-product-eyebrow"><?php echo esc_html(strtoupper(html_entity_decode($cat_name, ENT_QUOTES, 'UTF-8'))); ?></span>
                    <?php endif; ?>

                    <h1 class="ayur-single-title"><?php the_title(); ?></h1>

                    <!-- Short Concise Product Positioning (Factual & Compliant) -->
                    <div class="ayur-single-positioning">
                        <p><?php echo esc_html(!empty($ext_data['positioning']) ? $ext_data['positioning'] : wp_trim_words(get_the_content(), 25)); ?></p>
                    </div>

                    <!-- Clean Price, MRP & Savings Box -->
                    <div class="ayur-single-pricing-box">
                        <div class="ayur-price-row">
                            <span class="ayur-single-price">₹<?php echo number_format($price); ?></span>
                            <?php if ($mrp > $price) : ?>
                                <span class="ayur-single-mrp">MRP ₹<?php echo number_format($mrp); ?></span>
                                <span class="ayur-single-savings"><?php printf(__('SAVE ₹%s (%d%% OFF)', 'ayur-wellness'), number_format($diff), $discount_pct); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="ayur-price-perks">
                            <span class="ayur-perk-item">
                                <?php echo ayur_svg_icon('check', '', 12); ?>
                                <span><?php _e('Inclusive of all taxes', 'ayur-wellness'); ?></span>
                            </span>
                            <span class="ayur-perk-sep">&bull;</span>
                            <span class="ayur-perk-item">
                                <?php echo ayur_svg_icon('truck', '', 12); ?>
                                <span><?php _e('Free delivery on orders above ₹999', 'ayur-wellness'); ?></span>
                            </span>
                        </div>
                    </div>

                    <!-- Clear Pack Size & Stock Status Specification Row -->
                    <div class="ayur-pack-spec-row">
                        <div class="ayur-pack-chip-wrap">
                            <span class="ayur-pack-label"><?php _e('Pack Size:', 'ayur-wellness'); ?></span>
                            <div class="ayur-pack-chip">
                                <?php echo ayur_svg_icon('package', '', 14); ?>
                                <span class="ayur-pack-val"><?php echo esc_html($net_qty ? $net_qty : 'Standard Formulation Pack'); ?></span>
                            </div>
                        </div>
                        <span class="ayur-stock-badge in-stock">
                            <?php echo ayur_svg_icon('check', '', 12); ?>
                            <span><?php _e('In Stock &bull; Dispatched within 24h', 'ayur-wellness'); ?></span>
                        </span>
                    </div>

                    <!-- Purchase Actions Form (Quantity Stepper + ADD TO CART + BUY NOW) -->
                    <form class="cart ayur-cart-form" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
                        <div class="ayur-purchase-actions-grid">
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

                    <!-- CTA Micro-Trust (Compact Factual Signals, No Emojis) -->
                    <div class="ayur-cta-micro-trust">
                        <span class="ayur-micro-trust-pill">
                            <?php echo ayur_svg_icon('shield-check', '', 13); ?>
                            <span><?php _e('Secure Payments', 'ayur-wellness'); ?></span>
                        </span>
                        <span class="ayur-micro-trust-sep">&bull;</span>
                        <span class="ayur-micro-trust-pill">
                            <?php echo ayur_svg_icon('credit-card', '', 13); ?>
                            <span><?php _e('Cash on Delivery', 'ayur-wellness'); ?></span>
                        </span>
                        <span class="ayur-micro-trust-sep">&bull;</span>
                        <span class="ayur-micro-trust-pill">
                            <?php echo ayur_svg_icon('truck', '', 13); ?>
                            <span><?php _e('Pan-India Delivery', 'ayur-wellness'); ?></span>
                        </span>
                    </div>

                    <!-- Compact Delivery Information Panel -->
                    <div class="ayur-delivery-info-panel">
                        <div class="ayur-delivery-panel-title">
                            <?php echo ayur_svg_icon('truck', '', 15); ?>
                            <span><?php _e('DELIVERY & SERVICE DETAILS', 'ayur-wellness'); ?></span>
                        </div>
                        <div class="ayur-delivery-grid">
                            <div class="ayur-delivery-cell">
                                <span class="cell-k"><?php _e('DISPATCH', 'ayur-wellness'); ?></span>
                                <span class="cell-v"><?php _e('Within 24–48 hours from Maharashtra Hub', 'ayur-wellness'); ?></span>
                            </div>
                            <div class="ayur-delivery-cell">
                                <span class="cell-k"><?php _e('METRO DELIVERY', 'ayur-wellness'); ?></span>
                                <span class="cell-v"><?php _e('2–4 business days via Delhivery, BlueDart, DTDC', 'ayur-wellness'); ?></span>
                            </div>
                            <div class="ayur-delivery-cell">
                                <span class="cell-k"><?php _e('FREE DELIVERY', 'ayur-wellness'); ?></span>
                                <span class="cell-v"><?php _e('Orders above ₹999 (₹70 below ₹999)', 'ayur-wellness'); ?></span>
                            </div>
                            <div class="ayur-delivery-cell">
                                <span class="cell-k"><?php _e('COD AVAILABLE', 'ayur-wellness'); ?></span>
                                <span class="cell-v"><?php _e('Pay on arrival across serviceable pincodes', 'ayur-wellness'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Compact Above-The-Fold Formulation Highlights (Max 4 Points) -->
                    <?php if (!empty($ext_data['benefits'])) : ?>
                        <div class="ayur-panel-benefits">
                            <h4 class="ayur-panel-benefits-heading"><?php _e('Why You May Choose This Formulation', 'ayur-wellness'); ?></h4>
                            <ul class="ayur-panel-benefits-list">
                                <?php foreach (array_slice($ext_data['benefits'], 0, 4) as $benefit) : ?>
                                    <li>
                                        <span class="ayur-benefit-check"><?php echo ayur_svg_icon('check', '', 13); ?></span>
                                        <span><?php echo esc_html($benefit); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Information Hierarchy (Phase 5 Structured Presentation) -->
            <div class="ayur-product-details-wrap">
                <div class="ayur-details-grid">
                    <!-- Main Content Group (Left Column on Desktop, Ordered on Mobile) -->
                    <div class="ayur-details-main">
                        <!-- 1. Why You May Choose This Formulation -->
                        <details class="ayur-product-accordion ayur-section-why is-default-open" open>
                            <summary class="ayur-accordion-summary">
                                <h3 class="ayur-detail-heading"><?php _e('Why You May Choose This Formulation', 'ayur-wellness'); ?></h3>
                                <?php echo ayur_svg_icon('chevron-down', 'ayur-accordion-icon', 16); ?>
                            </summary>
                            <div class="ayur-accordion-content">
                                <p class="ayur-section-lead-text"><?php echo esc_html($ext_data['positioning']); ?></p>
                                <ul class="ayur-benefits-checklist">
                                    <?php foreach ($ext_data['benefits'] as $benefit) : ?>
                                        <li>
                                            <span class="ayur-check-bullet"><?php echo ayur_svg_icon('check', '', 14); ?></span>
                                            <span><?php echo esc_html($benefit); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </details>

                        <!-- 2. Key Botanicals -->
                        <?php if (!empty($ext_data['botanicals'])) : ?>
                            <details class="ayur-product-accordion ayur-section-botanicals is-default-open" open>
                                <summary class="ayur-accordion-summary">
                                    <h3 class="ayur-detail-heading"><?php _e('Key Botanicals & Classical Ingredients', 'ayur-wellness'); ?></h3>
                                    <?php echo ayur_svg_icon('chevron-down', 'ayur-accordion-icon', 16); ?>
                                </summary>
                                <div class="ayur-accordion-content">
                                    <div class="ayur-botanicals-list">
                                        <?php foreach ($ext_data['botanicals'] as $botanical) : ?>
                                            <div class="ayur-botanical-card">
                                                <div class="ayur-botanical-header">
                                                    <span class="ayur-botanical-name"><?php echo esc_html($botanical['name']); ?></span>
                                                    <?php if (!empty($botanical['latin'])) : ?>
                                                        <span class="ayur-botanical-latin"><?php echo esc_html($botanical['latin']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="ayur-botanical-role"><?php echo esc_html($botanical['role']); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </details>
                        <?php endif; ?>

                        <!-- 3. Formulation Story -->
                        <?php if (!empty($ext_data['formulation_story'])) : ?>
                            <details class="ayur-product-accordion ayur-section-story is-default-open" open>
                                <summary class="ayur-accordion-summary">
                                    <h3 class="ayur-detail-heading"><?php _e('Formulation Story & Ayurvedic Context', 'ayur-wellness'); ?></h3>
                                    <?php echo ayur_svg_icon('chevron-down', 'ayur-accordion-icon', 16); ?>
                                </summary>
                                <div class="ayur-accordion-content">
                                    <div class="ayur-story-block">
                                        <p class="ayur-story-intro"><?php echo esc_html($ext_data['formulation_story']['intro']); ?></p>
                                        <div class="ayur-story-sub">
                                            <h5 class="ayur-story-subtitle"><?php _e('Traditional Formulation', 'ayur-wellness'); ?></h5>
                                            <p><?php echo esc_html($ext_data['formulation_story']['traditional']); ?></p>
                                        </div>
                                        <div class="ayur-story-sub">
                                            <h5 class="ayur-story-subtitle"><?php _e('Why This Formulation', 'ayur-wellness'); ?></h5>
                                            <p><?php echo esc_html($ext_data['formulation_story']['why']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        <?php endif; ?>

                        <!-- 4. How to Use & Routine -->
                        <?php if (!empty($ext_data['usage_steps'])) : ?>
                            <details class="ayur-product-accordion ayur-section-usage is-default-open" open>
                                <summary class="ayur-accordion-summary">
                                    <h3 class="ayur-detail-heading"><?php _e('How to Use & Suggested Routine', 'ayur-wellness'); ?></h3>
                                    <?php echo ayur_svg_icon('chevron-down', 'ayur-accordion-icon', 16); ?>
                                </summary>
                                <div class="ayur-accordion-content">
                                    <div class="ayur-usage-steps">
                                        <?php foreach ($ext_data['usage_steps'] as $step) : ?>
                                            <div class="ayur-usage-step">
                                                <span class="ayur-usage-num"><?php echo esc_html($step['num']); ?></span>
                                                <div class="ayur-usage-body">
                                                    <h5 class="ayur-usage-step-title"><?php echo esc_html($step['title']); ?></h5>
                                                    <p class="ayur-usage-step-desc"><?php echo esc_html($step['desc']); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </details>
                        <?php endif; ?>
                    </div>

                    <!-- Supporting Side Column (Right on Desktop, Ordered on Mobile) -->
                    <div class="ayur-details-side">
                        <!-- 5. Product Details & Specifications -->
                        <details class="ayur-product-accordion ayur-section-specs">
                            <summary class="ayur-accordion-summary">
                                <h4 class="ayur-side-heading">
                                    <?php echo ayur_svg_icon('badge-check', '', 18); ?>
                                    <span><?php _e('Product Information & Specifications', 'ayur-wellness'); ?></span>
                                </h4>
                                <?php echo ayur_svg_icon('chevron-down', 'ayur-accordion-icon', 16); ?>
                            </summary>
                            <div class="ayur-accordion-content">
                                <div class="ayur-spec-card">
                                    <table class="ayur-spec-table">
                                        <tbody>
                                            <?php foreach ($ext_data['specs'] as $label => $val) : ?>
                                                <tr>
                                                    <th scope="row"><?php echo esc_html($label); ?></th>
                                                    <td><?php echo esc_html($val); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </details>

                        <!-- 6. Shipping & Delivery Policy -->
                        <details class="ayur-product-accordion ayur-section-shipping">
                            <summary class="ayur-accordion-summary">
                                <h4 class="ayur-side-heading">
                                    <?php echo ayur_svg_icon('truck', '', 18); ?>
                                    <span><?php _e('Shipping & Delivery Policy', 'ayur-wellness'); ?></span>
                                </h4>
                                <?php echo ayur_svg_icon('chevron-down', 'ayur-accordion-icon', 16); ?>
                            </summary>
                            <div class="ayur-accordion-content">
                                <div class="ayur-spec-card ayur-shipping-card">
                                    <div class="ayur-shipping-rows">
                                        <div class="ayur-shipping-row">
                                            <span class="ayur-ship-label"><?php _e('DISPATCH', 'ayur-wellness'); ?></span>
                                            <span class="ayur-ship-val"><?php _e('24–48 hours from Maharashtra Hub', 'ayur-wellness'); ?></span>
                                        </div>
                                        <div class="ayur-shipping-row">
                                            <span class="ayur-ship-label"><?php _e('METRO DELIVERY', 'ayur-wellness'); ?></span>
                                            <span class="ayur-ship-val"><?php _e('2–4 business days via Delhivery, BlueDart, DTDC', 'ayur-wellness'); ?></span>
                                        </div>
                                        <div class="ayur-shipping-row">
                                            <span class="ayur-ship-label"><?php _e('SHIPPING FEE', 'ayur-wellness'); ?></span>
                                            <span class="ayur-ship-val"><?php _e('Free Pan-India on orders above ₹999 (₹70 below ₹999)', 'ayur-wellness'); ?></span>
                                        </div>
                                        <div class="ayur-shipping-row">
                                            <span class="ayur-ship-label"><?php _e('PAYMENTS & COD', 'ayur-wellness'); ?></span>
                                            <span class="ayur-ship-val"><?php _e('Cash on Delivery & Secure UPI / Card Checkout', 'ayur-wellness'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <!-- 7. Important Information -->
                        <details class="ayur-product-accordion ayur-section-caution">
                            <summary class="ayur-accordion-summary">
                                <h4 class="ayur-side-heading">
                                    <?php echo ayur_svg_icon('shield-check', '', 18); ?>
                                    <span><?php _e('Important Product Information', 'ayur-wellness'); ?></span>
                                </h4>
                                <?php echo ayur_svg_icon('chevron-down', 'ayur-accordion-icon', 16); ?>
                            </summary>
                            <div class="ayur-accordion-content">
                                <div class="ayur-spec-card ayur-caution-card">
                                    <p class="ayur-caution-text"><?php echo esc_html($ext_data['important_note']); ?></p>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </div>

            <!-- Smart Complementary Related Formulations -->
            <?php
            $related_products = ayur_get_smart_related_products($product_id, 4);
            if (!empty($related_products)) :
            ?>
                <div class="ayur-related-wrap">
                    <div class="ayur-related-header">
                        <span class="ayur-section-eyebrow"><?php _e('COMPLEMENTARY CARE', 'ayur-wellness'); ?></span>
                        <h2 class="ayur-section-title"><?php _e('Related Formulations', 'ayur-wellness'); ?></h2>
                    </div>
                    <div class="ayur-related-grid products columns-4">
                        <?php
                        foreach ($related_products as $rel_prod) {
                            $post_object = get_post($rel_prod->get_id());
                            setup_postdata($GLOBALS['post'] =& $post_object);
                            wc_get_template_part('content', 'product');
                        }
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- Native Label Inspection Lightbox (Phase 6) -->
        <div id="ayurImageLightbox" class="ayur-image-lightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Product Label Inspection', 'ayur-wellness'); ?>">
            <div class="ayur-lightbox-backdrop" id="ayurLightboxBackdrop"></div>
            <div class="ayur-lightbox-dialog">
                <button type="button" class="ayur-lightbox-close" id="ayurLightboxClose" aria-label="<?php esc_attr_e('Close inspection modal', 'ayur-wellness'); ?>">
                    <?php echo ayur_svg_icon('x', '', 18); ?>
                </button>
                <div class="ayur-lightbox-stage">
                    <img id="ayurLightboxImg" src="<?php echo esc_url(get_the_post_thumbnail_url($product_id, 'full')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                </div>
                <div class="ayur-lightbox-footer">
                    <span class="ayur-lightbox-title"><?php the_title(); ?></span>
                    <span class="ayur-lightbox-hint"><?php _e('Authentic Ayurvedic formulation &bull; Inspected classical packaging', 'ayur-wellness'); ?></span>
                </div>
            </div>
        </div>
    </article>

    <!-- Native Mobile Sticky Bottom Bar -->
    <div class="ayur-mobile-sticky-bar" id="ayurMobileStickyBar" role="region" aria-label="<?php esc_attr_e('Mobile Purchase Bar', 'ayur-wellness'); ?>">
        <div class="ayur-sticky-inner">
            <div class="sticky-purchase-price ayur-sticky-info">
                <span class="ayur-sticky-price">₹<?php echo number_format($price); ?></span>
                <?php if ($mrp > $price) : ?>
                    <span class="ayur-sticky-mrp">MRP ₹<?php echo number_format($mrp); ?></span>
                <?php endif; ?>
                <?php
                if ($mrp > $price) :
                    $save_pct = round((($mrp - $price) / $mrp) * 100);
                    if ($save_pct >= 5) : ?>
                        <span class="ayur-sticky-save"><?php echo sprintf(__('Save %d%%', 'ayur-wellness'), $save_pct); ?></span>
                    <?php endif;
                endif;
                ?>
            </div>
            <div class="ayur-sticky-actions">
                <button type="button" class="ayur-sticky-btn ayur-sticky-add-cart" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="<?php esc_attr_e('Add to cart', 'ayur-wellness'); ?>">
                    <span><?php _e('ADD', 'ayur-wellness'); ?></span>
                </button>
                <a href="<?php echo esc_url(add_query_arg(array('ayur_quick_buy' => $product_id), home_url('/'))); ?>" class="ayur-sticky-btn ayur-sticky-buy-now" aria-label="<?php esc_attr_e('Buy now', 'ayur-wellness'); ?>">
                    <span><?php _e('BUY NOW', 'ayur-wellness'); ?></span>
                </a>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var b = document.getElementById('ayurMobileStickyBar');
        if (b) {
            var h = b.offsetHeight || 76;
            document.documentElement.style.setProperty('--mobile-purchase-bar-height', h + 'px');
        }
    })();
    </script>

    <?php
endwhile;

get_footer();

