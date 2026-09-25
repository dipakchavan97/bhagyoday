<?php
/**
 * Premium D2C Ayurvedic Single Product Template
 * Bhagyoday Ayurveda
 */

defined('ABSPATH') || exit;

get_header();

while (have_posts()) :
    the_post();
    global $product;

    $mrp = get_post_meta($product->get_id(), '_ayur_mrp', true);
    $price = $product->get_price();
    $benefits = get_post_meta($product->get_id(), '_ayur_benefits', true);
    $net_qty = get_post_meta($product->get_id(), '_ayur_net_quantity', true);
    $cert = get_post_meta($product->get_id(), '_ayur_certifications', true);
    ?>

    <div class="ayur-single-product-wrap">
        <div class="ayur-container">
            <!-- Breadcrumbs -->
            <nav class="ayur-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                <span class="ayur-breadcrumb-sep"><?php echo ayur_svg_icon('chevron-right', '', 11); ?></span>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Shop</a>
                <span class="ayur-breadcrumb-sep"><?php echo ayur_svg_icon('chevron-right', '', 11); ?></span>
                <span class="ayur-breadcrumb-current"><?php the_title(); ?></span>
            </nav>

            <div class="ayur-product-layout">
                <!-- Left: Product Gallery -->
                <div class="ayur-gallery-main">
                    <div class="ayur-gallery-stage">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array(
                                'class' => 'ayur-main-img',
                                'id' => 'ayurMainProductImg',
                                'fetchpriority' => 'high',
                                'alt' => esc_attr(get_the_title())
                            )); ?>
                        <?php else : ?>
                            <div class="ayur-img-placeholder">
                                <?php echo ayur_svg_icon('leaf', 'ayur-placeholder-icon', 48); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php
                    $attachment_ids = $product->get_gallery_image_ids();
                    if (has_post_thumbnail()) {
                        array_unshift($attachment_ids, get_post_thumbnail_id($product->get_id()));
                    }
                    if (!empty($attachment_ids) && count($attachment_ids) > 1) :
                    ?>
                        <div class="ayur-gallery-thumbs" role="region" aria-label="Product thumbnails">
                            <?php foreach ($attachment_ids as $index => $att_id) : 
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

                <!-- Right: High-Converting Purchase Column -->
                <div class="ayur-product-summary">
                    <?php if ($cert) : ?>
                        <div class="ayur-product-badge-top">
                            <?php echo ayur_svg_icon('shield-check', '', 14); ?>
                            <span><?php echo esc_html($cert); ?></span>
                        </div>
                    <?php endif; ?>

                    <h1 class="ayur-product-title"><?php the_title(); ?></h1>

                    <div class="ayur-product-meta-strip">
                        <?php echo ayur_get_star_rating_html($product); ?>
                        <?php if ($net_qty) : ?>
                            <span class="ayur-meta-dot">•</span>
                            <span class="ayur-meta-qty">
                                <?php echo ayur_svg_icon('package', '', 14); ?>
                                <span><?php echo esc_html($net_qty); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Price & Savings Box -->
                    <div class="ayur-single-price-box">
                        <span class="ayur-single-price">₹<?php echo number_format($price); ?></span>
                        <?php if ($mrp && $mrp > $price) : ?>
                            <span class="ayur-single-mrp">MRP ₹<?php echo number_format($mrp); ?></span>
                            <?php
                            $diff = $mrp - $price;
                            $pct = round(($diff / $mrp) * 100);
                            ?>
                            <span class="ayur-single-save-badge">Save <?php echo $pct; ?>% (₹<?php echo number_format($diff); ?> off)</span>
                        <?php endif; ?>
                        <span class="ayur-tax-inclusive">Inclusive of all taxes</span>
                    </div>

                    <!-- Short Benefits Checklist -->
                    <?php if (!empty($benefits)) : ?>
                        <div class="ayur-benefits-wrapper">
                            <h3 class="ayur-benefits-heading">Key Ayurvedic Benefits</h3>
                            <ul class="ayur-benefits-list">
                                <?php
                                $lines = explode("\n", $benefits);
                                foreach ($lines as $line) {
                                    $clean_line = trim(ltrim($line, "•- "));
                                    if (!empty($clean_line)) {
                                        echo '<li><span class="ayur-check-icon">' . ayur_svg_icon('check', '', 14) . '</span><span>' . esc_html($clean_line) . '</span></li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    <?php else : ?>
                        <div class="ayur-product-short-desc">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Stock & Small Batch Reassurance -->
                    <div class="ayur-stock-alert">
                        <span class="ayur-stock-icon"><?php echo ayur_svg_icon('leaf', '', 15); ?></span>
                        <span>In Stock — Fresh small-batch botanical formulation ready for dispatch</span>
                    </div>

                    <!-- Add to Cart / Buy Now CTAs -->
                    <form class="cart ayur-cart-form" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
                        <div class="ayur-cta-row">
                            <?php if (!$product->is_type('variable')) : ?>
                                <div class="ayur-qty-wrapper">
                                    <label for="ayurQtyInput" class="screen-reader-text"><?php esc_html_e('Quantity', 'woocommerce'); ?></label>
                                    <div class="ayur-qty-stepper">
                                        <button type="button" class="ayur-qty-dec" aria-label="<?php esc_attr_e('Decrease quantity', 'ayur-wellness'); ?>"><?php echo ayur_svg_icon('minus', '', 14); ?></button>
                                        <?php
                                        woocommerce_quantity_input(array(
                                            'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                                            'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                                            'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(),
                                            'classes'     => array('input-text', 'qty', 'text', 'ayur-qty-input'),
                                        ));
                                        ?>
                                        <button type="button" class="ayur-qty-inc" aria-label="<?php esc_attr_e('Increase quantity', 'ayur-wellness'); ?>"><?php echo ayur_svg_icon('plus', '', 14); ?></button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="ayur-btn-add-cart single_add_to_cart_button button alt">
                                <?php echo ayur_svg_icon('shopping-bag', '', 18); ?>
                                <span>ADD TO CART</span>
                            </button>

                            <a href="<?php echo esc_url(add_query_arg(array('ayur_quick_buy' => $product->get_id()), home_url('/'))); ?>" class="ayur-btn-buy-now">
                                <span>BUY NOW</span>
                                <?php echo ayur_svg_icon('arrow-right', '', 16); ?>
                            </a>
                        </div>
                    </form>

                    <!-- Trust Row for Indian Ayurvedic D2C Customers -->
                    <div class="ayur-product-trust-grid">
                        <div class="ayur-trust-point">
                            <span class="ayur-trust-icon"><?php echo ayur_svg_icon('shield-check', '', 18); ?></span>
                            <div class="ayur-trust-text">
                                <strong>Secure Payments</strong>
                                <span>Encrypted UPI & Cards</span>
                            </div>
                        </div>
                        <div class="ayur-trust-point">
                            <span class="ayur-trust-icon"><?php echo ayur_svg_icon('credit-card', '', 18); ?></span>
                            <div class="ayur-trust-text">
                                <strong>COD Available</strong>
                                <span>Pay on delivery pan-India</span>
                            </div>
                        </div>
                        <div class="ayur-trust-point">
                            <span class="ayur-trust-icon"><?php echo ayur_svg_icon('truck', '', 18); ?></span>
                            <div class="ayur-trust-text">
                                <strong>Pan-India Delivery</strong>
                                <span>Dispatched via Shiprocket</span>
                            </div>
                        </div>
                        <div class="ayur-trust-point">
                            <span class="ayur-trust-icon"><?php echo ayur_svg_icon('headphones', '', 18); ?></span>
                            <div class="ayur-trust-text">
                                <strong>Direct Support</strong>
                                <span>Call / WhatsApp Helpline</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ayurvedic Information Tabs -->
            <div class="ayur-product-tabs-wrapper">
                <?php woocommerce_output_product_data_tabs(); ?>
            </div>

            <!-- Related Products -->
            <div class="ayur-related-products-wrapper">
                <?php woocommerce_output_related_products(); ?>
            </div>
        </div>
    </div>

    <!-- Mobile Sticky Purchase Bar (above bottom nav) -->
    <div class="ayur-mobile-sticky-bar" id="ayurMobileStickyBar">
        <div class="ayur-sticky-info">
            <div class="ayur-sticky-price">₹<?php echo number_format($price); ?></div>
            <?php if ($mrp && $mrp > $price) : ?>
                <div class="ayur-sticky-mrp">MRP ₹<?php echo number_format($mrp); ?></div>
            <?php endif; ?>
        </div>
        <div class="ayur-sticky-actions">
            <button type="button" class="ayur-sticky-btn ayur-sticky-add-cart" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                <?php echo ayur_svg_icon('shopping-bag', '', 16); ?>
                <span>Add</span>
            </button>
            <a href="<?php echo esc_url(add_query_arg(array('ayur_quick_buy' => $product->get_id()), home_url('/'))); ?>" class="ayur-sticky-btn ayur-sticky-buy-now">
                <span>Buy Now</span>
                <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
            </a>
        </div>
    </div>

    <?php
endwhile;

get_footer();
