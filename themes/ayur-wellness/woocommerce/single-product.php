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

    // Explicitly isolate the single product context for the sticky purchase bar
    $sticky_product_id = $product_id;
    $sticky_price      = $price;
    $sticky_mrp        = $mrp;
    $sticky_save_pct   = $discount_pct;
    $sticky_quick_buy  = add_query_arg(array('ayur_quick_buy' => $product_id), home_url('/'));

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
                    <div class="ayur-gallery-stage" id="ayurGalleryStage" role="button" tabindex="0" aria-label="<?php esc_attr_e('Click to enlarge and inspect packaging label', 'ayur-wellness'); ?>" data-zoom-url="<?php echo esc_url(get_the_post_thumbnail_url($product_id, 'full')); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array(
                                'class'         => 'ayur-main-img',
                                'id'            => 'ayurMainProductImg',
                                'fetchpriority' => 'high',
                                'alt'           => esc_attr(get_the_title()),
                                'data-full'     => esc_url(get_the_post_thumbnail_url($product_id, 'full')),
                                'data-index'    => '0'
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

                    <?php if (count($gallery_ids) > 1 || $product_id == 33) : ?>
                        <div class="ayur-gallery-thumbs" role="region" aria-label="<?php esc_attr_e('Product gallery thumbnails', 'ayur-wellness'); ?>">
                            <?php foreach ($gallery_ids as $index => $att_id) :
                                $thumb_url = wp_get_attachment_image_url($att_id, 'woocommerce_gallery_thumbnail');
                                $large_url = wp_get_attachment_image_url($att_id, 'large');
                                $full_url  = wp_get_attachment_image_url($att_id, 'full');
                                if (!$thumb_url) continue;
                                if (!$large_url) $large_url = $thumb_url;
                                if (!$full_url)  $full_url  = $large_url;
                            ?>
                                <button type="button"
                                        class="ayur-gallery-thumb-btn <?php echo $index === 0 ? 'active' : ''; ?>"
                                        data-index="<?php echo esc_attr($index); ?>"
                                        data-large="<?php echo esc_url($large_url); ?>"
                                        data-full="<?php echo esc_url($full_url); ?>"
                                        data-alt="<?php echo esc_attr(sprintf(__('%s - View %d', 'ayur-wellness'), get_the_title(), $index + 1)); ?>"
                                        aria-label="<?php echo esc_attr(sprintf(__('View image %d', 'ayur-wellness'), $index + 1)); ?>">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?> thumbnail <?php echo $index + 1; ?>" loading="lazy" />
                                </button>
                            <?php endforeach; ?>
                            <?php if ($product_id == 33) :
                                $comp_img_url = get_template_directory_uri() . '/assets/images/amrit-juice-composition-label.webp';
                                $comp_idx = count($gallery_ids);
                            ?>
                                <button type="button"
                                        class="ayur-gallery-thumb-btn ayur-comp-thumb-btn"
                                        data-index="<?php echo esc_attr($comp_idx); ?>"
                                        data-large="<?php echo esc_url($comp_img_url); ?>"
                                        data-full="<?php echo esc_url($comp_img_url); ?>"
                                        data-alt="<?php esc_attr_e('Amrit Juice 60 Ingredients Composition Label', 'ayur-wellness'); ?>"
                                        aria-label="<?php esc_attr_e('View 60 Ingredients Composition Label', 'ayur-wellness'); ?>">
                                    <img src="<?php echo esc_url($comp_img_url); ?>" alt="<?php esc_attr_e('Composition Label Thumbnail', 'ayur-wellness'); ?>" loading="lazy" />
                                    <span class="ayur-thumb-comp-tag"><?php _e('60 Herbs', 'ayur-wellness'); ?></span>
                                </button>
                            <?php endif; ?>
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

                    <?php if ($product_id == 33 || !empty($ext_data['has_60_composition'])) : ?>
                        <!-- Section 4: Compact Above-the-Fold Formulation Summary -->
                        <div class="ayur-atf-composition-summary" aria-label="<?php esc_attr_e('Formulation Summary', 'ayur-wellness'); ?>">
                            <div class="ayur-atf-badge-pill">
                                <span class="ayur-atf-count-badge">60</span>
                                <div class="ayur-atf-text-col">
                                    <strong class="ayur-atf-heading"><?php _e('Herbs, Fruits &amp; Extracts', 'ayur-wellness'); ?></strong>
                                    <span class="ayur-atf-sub"><?php _e('Each 10ml contains a carefully specified blend of botanical ingredients.', 'ayur-wellness'); ?></span>
                                </div>
                            </div>
                            <a href="#ayurCompositionSection" class="ayur-atf-view-btn" id="ayurAtfViewBtn">
                                <span><?php _e('VIEW COMPLETE COMPOSITION', 'ayur-wellness'); ?></span>
                                <?php echo ayur_svg_icon('chevron-down', '', 13); ?>
                            </a>
                        </div>
                    <?php endif; ?>

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

                    <!-- Phase 5: Buy More, Save More Offer Selector -->
                    <?php
                    $offers_enabled = class_exists('Ayur_Quantity_Offers') && Ayur_Quantity_Offers::is_enabled();
                    $offer_tiers = $offers_enabled ? Ayur_Quantity_Offers::get_offer_tiers() : array();
                    if ($offers_enabled && !empty($offer_tiers)) :
                    ?>
                    <div class="ayur-offer-selector-box" id="ayurOfferSelectorBox" data-unit-price="<?php echo esc_attr($price); ?>">
                        <div class="ayur-offer-selector-header">
                            <span class="ayur-offer-header-title">
                                <?php echo ayur_svg_icon('sparkles', '', 14); ?>
                                <strong><?php _e('BUY MORE, SAVE MORE', 'ayur-wellness'); ?></strong>
                            </span>
                            <span class="ayur-offer-live-badge" id="ayurOfferLiveBadge" style="display:none;"></span>
                        </div>
                        <div class="ayur-offer-chips-grid">
                            <!-- Tier: Buy 1 -->
                            <div class="ayur-offer-chip is-selected" data-qty="1" data-discount="0" role="button" tabindex="0">
                                <div class="ayur-offer-chip-radio">
                                    <span class="radio-circle"></span>
                                    <span class="chip-qty-title"><?php _e('Buy 1', 'ayur-wellness'); ?></span>
                                </div>
                                <div class="ayur-offer-chip-pricing">
                                    <span class="chip-total">₹<?php echo number_format($price); ?></span>
                                </div>
                            </div>

                            <!-- Configured Tiers -->
                            <?php foreach ($offer_tiers as $t) :
                                $t_qty = (int) $t['min_qty'];
                                $t_pct = (float) $t['discount_pct'];
                                $t_calc = Ayur_Quantity_Offers::calculate_pricing($price, $t_qty);
                            ?>
                                <div class="ayur-offer-chip" data-qty="<?php echo esc_attr($t_qty); ?>" data-discount="<?php echo esc_attr($t_pct); ?>" role="button" tabindex="0">
                                    <div class="ayur-offer-chip-radio">
                                        <span class="radio-circle"></span>
                                        <span class="chip-qty-title"><?php printf(__('Buy %d', 'ayur-wellness'), $t_qty); ?></span>
                                        <span class="chip-save-pill"><?php printf(__('%d%% OFF', 'ayur-wellness'), $t_pct); ?></span>
                                    </div>
                                    <div class="ayur-offer-chip-pricing">
                                        <span class="chip-total">₹<?php echo number_format($t_calc['discounted_subtotal']); ?> <?php _e('total', 'ayur-wellness'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

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

                            <a href="<?php echo esc_url(add_query_arg(array('ayur_instant_buy' => $product_id, 'quantity' => 1), home_url('/'))); ?>" class="ayur-btn-buy-now" id="ayurBtnBuyNow" data-product-id="<?php echo esc_attr($product_id); ?>">
                                <span><?php _e('BUY NOW', 'ayur-wellness'); ?></span>
                                <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                            </a>
                        </div>
                    </form>

                    <!-- Premium Trust Badges System (Factual, Focussed, Authentic Ayurveda) -->
                    <div class="ayur-product-trust-system" aria-label="<?php esc_attr_e('Product Trust & Authenticity Badges', 'ayur-wellness'); ?>">
                        <div class="ayur-trust-badges-grid">
                            <!-- Badge 1: Secure Payments -->
                            <div class="ayur-trust-badge-card">
                                <div class="ayur-trust-badge-icon" aria-hidden="true">
                                    <svg class="ayur-trust-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                        <rect x="9" y="11" width="6" height="5" rx="1"/>
                                        <path d="M10 11V9a2 2 0 1 1 4 0v2"/>
                                    </svg>
                                </div>
                                <div class="ayur-trust-badge-content">
                                    <span class="ayur-trust-badge-title"><?php _e('Secure Payments', 'ayur-wellness'); ?></span>
                                    <span class="ayur-trust-badge-sub"><?php _e('Razorpay Protected', 'ayur-wellness'); ?></span>
                                </div>
                            </div>

                            <!-- Badge 2: Pan-India Delivery -->
                            <div class="ayur-trust-badge-card">
                                <div class="ayur-trust-badge-icon" aria-hidden="true">
                                    <svg class="ayur-trust-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/>
                                        <path d="M15 18H9"/>
                                        <path d="M19 18h2a1 1 0 0 0 1-1v-5.28a2 2 0 0 0-.59-1.42L18.41 7.3A2 2 0 0 0 17 6.72H14"/>
                                        <circle cx="7" cy="18" r="2"/>
                                        <circle cx="17" cy="18" r="2"/>
                                    </svg>
                                </div>
                                <div class="ayur-trust-badge-content">
                                    <span class="ayur-trust-badge-title"><?php _e('Pan-India Delivery', 'ayur-wellness'); ?></span>
                                    <span class="ayur-trust-badge-sub"><?php _e('Reliable Shipping', 'ayur-wellness'); ?></span>
                                </div>
                            </div>

                            <!-- Badge 3: Authentic Ayurveda -->
                            <div class="ayur-trust-badge-card">
                                <div class="ayur-trust-badge-icon" aria-hidden="true">
                                    <svg class="ayur-trust-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                                        <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                                    </svg>
                                </div>
                                <div class="ayur-trust-badge-content">
                                    <span class="ayur-trust-badge-title"><?php _e('Authentic Ayurveda', 'ayur-wellness'); ?></span>
                                    <span class="ayur-trust-badge-sub"><?php _e('Genuine Formulations', 'ayur-wellness'); ?></span>
                                </div>
                            </div>

                            <!-- Badge 4: Quality Assured -->
                            <div class="ayur-trust-badge-card">
                                <div class="ayur-trust-badge-icon" aria-hidden="true">
                                    <svg class="ayur-trust-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                </div>
                                <div class="ayur-trust-badge-content">
                                    <span class="ayur-trust-badge-title"><?php _e('Quality Assured', 'ayur-wellness'); ?></span>
                                    <span class="ayur-trust-badge-sub"><?php _e('Carefully Sourced', 'ayur-wellness'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Verified Business Trust Signals Strip -->
                        <div class="ayur-trust-compliance-strip">
                            <div class="ayur-compliance-pill">
                                <svg class="ayur-compliance-check" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span><?php _e('GMP & AYUSH Compliant Facility', 'ayur-wellness'); ?></span>
                            </div>
                            <span class="ayur-compliance-sep" aria-hidden="true">•</span>
                            <div class="ayur-compliance-pill">
                                <svg class="ayur-compliance-check" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span><?php _e('Standardized Classical Extraction', 'ayur-wellness'); ?></span>
                            </div>
                        </div>
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

            <?php if ($product_id == 33 || !empty($ext_data['has_60_composition'])) : ?>
                <!-- Section 16: What's Inside Callout Banner -->
                <div class="ayur-whats-inside-banner" aria-label="<?php esc_attr_e('What\'s Inside Formulation Callout', 'ayur-wellness'); ?>">
                    <div class="ayur-whats-inside-inner">
                        <div class="ayur-wi-header">
                            <span class="ayur-wi-eyebrow"><?php _e('AUTHENTIC AYURVEDIC FORMULATION', 'ayur-wellness'); ?></span>
                            <span class="ayur-wi-subhead"><?php _e('WHAT\'S INSIDE', 'ayur-wellness'); ?></span>
                        </div>
                        <div class="ayur-wi-main">
                            <div class="ayur-wi-count-badge">
                                <span class="ayur-wi-count">60</span>
                            </div>
                            <div class="ayur-wi-content">
                                <h3 class="ayur-wi-title"><?php _e('HERBS &bull; FRUITS &bull; EXTRACTS', 'ayur-wellness'); ?></h3>
                                <p class="ayur-wi-desc"><?php _e('Each 10ml contains a carefully specified blend of botanical ingredients.', 'ayur-wellness'); ?></p>
                            </div>
                        </div>
                        <div class="ayur-wi-badges-row">
                            <div class="ayur-wi-pill">
                                <?php echo ayur_svg_icon('check', '', 13); ?>
                                <span><?php _e('No Added Sugar', 'ayur-wellness'); ?></span>
                            </div>
                            <div class="ayur-wi-pill">
                                <?php echo ayur_svg_icon('check', '', 13); ?>
                                <span><?php _e('No Artificial Colour', 'ayur-wellness'); ?></span>
                            </div>
                            <div class="ayur-wi-pill">
                                <?php echo ayur_svg_icon('check', '', 13); ?>
                                <span><?php _e('No Artificial Flavour', 'ayur-wellness'); ?></span>
                            </div>
                            <div class="ayur-wi-pill">
                                <?php echo ayur_svg_icon('check', '', 13); ?>
                                <span><?php _e('100% Natural Ingredients', 'ayur-wellness'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sections 5-12: Complete Composition Section -->
                <section id="ayurCompositionSection" class="ayur-composition-section" aria-label="<?php esc_attr_e('Complete Product Composition', 'ayur-wellness'); ?>">
                    <div class="ayur-composition-header-card">
                        <span class="ayur-comp-eyebrow"><?php _e('DETAILED FORMULATION PANEL', 'ayur-wellness'); ?></span>
                        <h2 class="ayur-comp-title"><?php _e('COMPOSITION', 'ayur-wellness'); ?></h2>
                        <p class="ayur-comp-count"><?php _e('60 Herbs, Fruits &amp; Extracts', 'ayur-wellness'); ?></p>
                        <div class="ayur-comp-serving-pill">
                            <span class="serving-dot"></span>
                            <span><?php _e('Each 10ml contains:', 'ayur-wellness'); ?></span>
                        </div>
                    </div>

                    <!-- Search and Action Controls -->
                    <div class="ayur-composition-controls">
                        <div class="ayur-comp-search-box">
                            <span class="ayur-comp-search-icon"><?php echo ayur_svg_icon('search', '', 16); ?></span>
                            <input type="text"
                                   id="ayurIngredientSearchInput"
                                   class="ayur-comp-search-input"
                                   placeholder="<?php esc_attr_e('Search ingredients (e.g., Ashwagandha, Noni, Amla, Shilajit)...', 'ayur-wellness'); ?>"
                                   aria-label="<?php esc_attr_e('Search 60 ingredients', 'ayur-wellness'); ?>"
                                   autocomplete="off" />
                            <button type="button" id="ayurIngredientSearchClear" class="ayur-comp-search-clear" style="display:none;" aria-label="<?php esc_attr_e('Clear search', 'ayur-wellness'); ?>">&times;</button>
                        </div>
                        <div class="ayur-comp-actions">
                            <span id="ayurCompFilterCounter" class="ayur-comp-counter"><?php _e('Showing all 60 ingredients', 'ayur-wellness'); ?></span>
                            <button type="button" class="ayur-comp-inspect-btn" id="ayurBtnInspectLabel" data-full="<?php echo esc_url(get_template_directory_uri() . '/assets/images/amrit-juice-composition-label.webp'); ?>" aria-label="<?php esc_attr_e('Inspect official packaging label image', 'ayur-wellness'); ?>">
                                <?php echo ayur_svg_icon('search', '', 13); ?>
                                <span><?php _e('Inspect Packaging Label', 'ayur-wellness'); ?></span>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Accordion Trigger -->
                    <div class="ayur-comp-mobile-toggle-wrap">
                        <button type="button"
                                id="ayurCompMobileToggle"
                                class="ayur-comp-mobile-toggle-btn"
                                aria-expanded="false"
                                aria-controls="ayurCompositionTableWrap">
                            <span class="btn-text"><?php _e('View Complete Composition (60 Ingredients)', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('chevron-down', 'toggle-arrow', 16); ?>
                        </button>
                    </div>

                    <!-- 60 Ingredients Table Grid -->
                    <div id="ayurCompositionTableWrap" class="ayur-composition-table-wrap is-collapsed-mobile">
                        <div class="ayur-comp-columns-grid">
                            <!-- Left Column: Items 01 to 30 -->
                            <div class="ayur-comp-column ayur-comp-col-1">
                                <table class="ayur-comp-table" role="table" aria-label="<?php esc_attr_e('Ingredients 1 to 30', 'ayur-wellness'); ?>">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="th-num">#</th>
                                            <th scope="col" class="th-name"><?php _e('Ingredient', 'ayur-wellness'); ?></th>
                                            <th scope="col" class="th-qty"><?php _e('Per 10ml', 'ayur-wellness'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $all_ingredients = ayur_get_amrit_juice_composition();
                                        $col1 = array_slice($all_ingredients, 0, 30);
                                        foreach ($col1 as $item) :
                                        ?>
                                        <tr class="ayur-comp-row" data-search="<?php echo esc_attr($item['search']); ?>" data-name="<?php echo esc_attr(strtolower($item['name'])); ?>">
                                            <td class="td-num"><?php echo esc_html($item['num']); ?></td>
                                            <td class="td-name">
                                                <span class="ing-name"><?php echo esc_html($item['name']); ?></span>
                                                <?php if (!empty($item['label_name']) && $item['label_name'] !== $item['name']) : ?>
                                                    <span class="ing-label-alt" title="<?php esc_attr_e('Spelling on packaging label', 'ayur-wellness'); ?>">(<?php echo esc_html($item['label_name']); ?>)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="td-qty"><?php echo esc_html($item['qty']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Right Column: Items 31 to 60 -->
                            <div class="ayur-comp-column ayur-comp-col-2">
                                <table class="ayur-comp-table" role="table" aria-label="<?php esc_attr_e('Ingredients 31 to 60', 'ayur-wellness'); ?>">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="th-num">#</th>
                                            <th scope="col" class="th-name"><?php _e('Ingredient', 'ayur-wellness'); ?></th>
                                            <th scope="col" class="th-qty"><?php _e('Per 10ml', 'ayur-wellness'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $col2 = array_slice($all_ingredients, 30, 30);
                                        foreach ($col2 as $item) :
                                        ?>
                                        <tr class="ayur-comp-row" data-search="<?php echo esc_attr($item['search']); ?>" data-name="<?php echo esc_attr(strtolower($item['name'])); ?>">
                                            <td class="td-num"><?php echo esc_html($item['num']); ?></td>
                                            <td class="td-name">
                                                <span class="ing-name"><?php echo esc_html($item['name']); ?></span>
                                                <?php if (!empty($item['label_name']) && $item['label_name'] !== $item['name']) : ?>
                                                    <span class="ing-label-alt" title="<?php esc_attr_e('Spelling on packaging label', 'ayur-wellness'); ?>">(<?php echo esc_html($item['label_name']); ?>)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="td-qty"><?php echo esc_html($item['qty']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="ayurCompNoResults" class="ayur-comp-no-results" style="display:none;">
                            <p><?php _e('No matching ingredients found. Try a different search term or clear the filter.', 'ayur-wellness'); ?></p>
                        </div>
                    </div>

                    <!-- Section Footer / Certification Note -->
                    <div class="ayur-comp-footer-bar">
                        <div class="ayur-comp-footer-left">
                            <span class="ayur-proprietary-badge"><?php _e('An Ayurvedic Proprietary Medicine', 'ayur-wellness'); ?></span>
                        </div>
                        <div class="ayur-comp-footer-right">
                            <p class="ayur-comp-notice"><?php _e('Total 60 herbs, fruits &amp; extracts. Formulated and tested in an AYUSH-compliant facility. No artificial colours or flavours added.', 'ayur-wellness'); ?></p>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

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

            <!-- Amazon-Style Authentic Customer Reviews System -->
            <?php
            if (class_exists('Ayur_Reviews')) {
                Ayur_Reviews::render_product_reviews($product_id);
            }
            ?>

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
                        $GLOBALS['product'] = wc_get_product($sticky_product_id);
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- Native Label Inspection Lightbox (Phase 6) -->
        <div id="ayurImageLightbox" class="ayur-image-lightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Product Label Inspection', 'ayur-wellness'); ?>">
            <div class="ayur-lightbox-backdrop" id="ayurLightboxBackdrop"></div>
            <div class="ayur-lightbox-dialog">
                <div class="ayur-lightbox-header-bar">
                    <div class="ayur-lightbox-zoom-toolbar" id="ayurLightboxZoomToolbar" aria-label="<?php esc_attr_e('Image Zoom Controls', 'ayur-wellness'); ?>">
                        <button type="button" class="ayur-lb-zoom-btn" id="ayurLbZoomOut" aria-label="<?php esc_attr_e('Zoom out', 'ayur-wellness'); ?>" title="<?php esc_attr_e('Zoom out (-)', 'ayur-wellness'); ?>">
                            <?php echo ayur_svg_icon('minus', '', 14); ?>
                        </button>
                        <span class="ayur-lb-zoom-level" id="ayurLbZoomLevel">100%</span>
                        <button type="button" class="ayur-lb-zoom-btn" id="ayurLbZoomIn" aria-label="<?php esc_attr_e('Zoom in', 'ayur-wellness'); ?>" title="<?php esc_attr_e('Zoom in (+)', 'ayur-wellness'); ?>">
                            <?php echo ayur_svg_icon('plus', '', 14); ?>
                        </button>
                        <button type="button" class="ayur-lb-zoom-btn ayur-lb-zoom-reset" id="ayurLbZoomReset" aria-label="<?php esc_attr_e('Reset zoom', 'ayur-wellness'); ?>" title="<?php esc_attr_e('Reset (100%)', 'ayur-wellness'); ?>">
                            <span><?php _e('Reset', 'ayur-wellness'); ?></span>
                        </button>
                    </div>
                    <button type="button" class="ayur-lightbox-close" id="ayurLightboxClose" aria-label="<?php esc_attr_e('Close inspection modal', 'ayur-wellness'); ?>">
                        <?php echo ayur_svg_icon('x', '', 20); ?>
                    </button>
                </div>
                <?php if (count($gallery_ids) > 1) : ?>
                    <button type="button" class="ayur-lightbox-nav ayur-lightbox-prev" id="ayurLightboxPrev" aria-label="<?php esc_attr_e('Previous image', 'ayur-wellness'); ?>">
                        <?php echo ayur_svg_icon('chevron-left', '', 22); ?>
                    </button>
                    <button type="button" class="ayur-lightbox-nav ayur-lightbox-next" id="ayurLightboxNext" aria-label="<?php esc_attr_e('Next image', 'ayur-wellness'); ?>">
                        <?php echo ayur_svg_icon('chevron-right', '', 22); ?>
                    </button>
                <?php endif; ?>
                <div class="ayur-lightbox-stage" id="ayurLightboxStage">
                    <div class="ayur-lightbox-pan-wrap" id="ayurLightboxPanWrap">
                        <img id="ayurLightboxImg" src="<?php echo esc_url(get_the_post_thumbnail_url($product_id, 'full')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" draggable="false" />
                    </div>
                </div>
                <div class="ayur-lightbox-footer">
                    <span class="ayur-lightbox-title"><?php the_title(); ?></span>
                    <?php if (count($gallery_ids) > 1) : ?>
                        <span class="ayur-lightbox-counter" id="ayurLightboxCounter">1 / <?php echo count($gallery_ids); ?></span>
                    <?php endif; ?>
                    <span class="ayur-lightbox-hint"><?php _e('High-resolution label inspection &bull; Use buttons or double-tap to zoom', 'ayur-wellness'); ?></span>
                </div>
            </div>
        </div>
    </article>

    <!-- Native Mobile Sticky Bottom Bar -->
    <div class="ayur-mobile-sticky-bar" id="ayurMobileStickyBar" role="region" aria-label="<?php esc_attr_e('Mobile Purchase Bar', 'ayur-wellness'); ?>">
        <div class="ayur-sticky-inner">
            <div class="sticky-purchase-price ayur-sticky-info">
                <span class="ayur-sticky-price">₹<?php echo number_format($sticky_price); ?></span>
                <?php if ($sticky_mrp > $sticky_price) : ?>
                    <span class="ayur-sticky-mrp">MRP ₹<?php echo number_format($sticky_mrp); ?></span>
                <?php endif; ?>
                <?php if ($sticky_mrp > $sticky_price && $sticky_save_pct >= 5) : ?>
                    <span class="ayur-sticky-save"><?php echo sprintf(__('Save %d%%', 'ayur-wellness'), $sticky_save_pct); ?></span>
                <?php endif; ?>
            </div>
            <div class="ayur-sticky-actions">
                <button type="button" class="ayur-sticky-btn ayur-sticky-add-cart" data-product-id="<?php echo esc_attr($sticky_product_id); ?>" aria-label="<?php esc_attr_e('Add to cart', 'ayur-wellness'); ?>">
                    <span><?php _e('ADD', 'ayur-wellness'); ?></span>
                </button>
                <a href="<?php echo esc_url($sticky_quick_buy); ?>" class="ayur-sticky-btn ayur-sticky-buy-now" id="ayurStickyBuyNow" data-product-id="<?php echo esc_attr($sticky_product_id); ?>" aria-label="<?php esc_attr_e('Buy now', 'ayur-wellness'); ?>">
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

