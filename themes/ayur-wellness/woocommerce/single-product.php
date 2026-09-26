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
                <!-- 2. Product Gallery -->
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

                <!-- 3. Purchase Information Column (Conversion-First) -->
                <div class="ayur-product-summary">
                    <?php if ($cat_name) : ?>
                        <span class="ayur-product-eyebrow"><?php echo esc_html(strtoupper(html_entity_decode($cat_name, ENT_QUOTES, 'UTF-8'))); ?></span>
                    <?php endif; ?>

                    <h1 class="ayur-single-title"><?php the_title(); ?></h1>

                    <!-- Short Product Positioning Statement -->
                    <div class="ayur-single-excerpt">
                        <?php
                        if (has_excerpt()) {
                            $raw_excerpt = get_the_excerpt();
                            $clean_excerpt = str_ireplace(
                                ['KSM-66®', 'KSM-66', 'KSM 66'],
                                'standardized Ashwagandha Root Extract (Withania somnifera)',
                                $raw_excerpt
                            );
                            echo wp_kses_post(wpautop($clean_excerpt));
                        } else {
                            echo '<p>' . esc_html(wp_trim_words(get_the_content(), 28)) . '</p>';
                        }
                        ?>
                    </div>

                    <!-- Clean Price, MRP & Savings -->
                    <div class="ayur-single-pricing-box">
                        <div class="ayur-price-row">
                            <span class="ayur-single-price">₹<?php echo number_format($price); ?></span>
                            <?php if ($mrp > $price) : ?>
                                <span class="ayur-single-mrp">MRP ₹<?php echo number_format($mrp); ?></span>
                                <span class="ayur-single-savings"><?php printf(__('Save ₹%s (%d%% OFF)', 'ayur-wellness'), number_format($diff), $discount_pct); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="ayur-price-tax-note"><?php _e('Inclusive of all taxes &bull; Free Pan-India Delivery on orders above ₹999', 'ayur-wellness'); ?></span>
                    </div>

                    <!-- Availability & Dispatch Badge -->
                    <div class="ayur-stock-badge-row">
                        <span class="ayur-stock-badge in-stock">
                            <?php echo ayur_svg_icon('check', '', 14); ?>
                            <span><?php _e('In Stock &bull; Dispatched within 24 Hours from Maharashtra Hub', 'ayur-wellness'); ?></span>
                        </span>
                        <?php if ($net_qty) : ?>
                            <span class="ayur-net-qty-chip">
                                <?php echo ayur_svg_icon('package', '', 14); ?>
                                <span><?php echo esc_html($net_qty); ?></span>
                            </span>
                        <?php endif; ?>
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

                    <!-- Trust Microcopy (Directly Below Actions) -->
                    <p class="ayur-product-trust-microcopy">
                        <?php echo ayur_svg_icon('shield-check', '', 14); ?>
                        <span><?php _e('Secure checkout &bull; Cash on Delivery available &bull; Pan-India express delivery', 'ayur-wellness'); ?></span>
                    </p>

                    <!-- Refined 2x2 Trust Rail -->
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

            <?php
            $ext_data = ayur_get_product_extended_data($product_id);
            ?>

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
    </article>

    <!-- Native Mobile Sticky Bottom Bar -->
    <div class="ayur-mobile-sticky-bar" id="ayurMobileStickyBar">
        <div class="ayur-sticky-info">
            <span class="ayur-sticky-price">₹<?php echo number_format($price); ?></span>
            <?php if ($mrp > $price) : ?>
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

