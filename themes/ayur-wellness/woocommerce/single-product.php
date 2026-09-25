<?php
/**
 * Conversion-Focused Ayurvedic Single Product Template
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
            <div style="font-size:13px; color:#6B7280; margin-bottom:24px;">
                <a href="<?php echo esc_url(home_url('/')); ?>">Home</a> / 
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Shop</a> / 
                <span><?php the_title(); ?></span>
            </div>

            <div class="ayur-product-layout">
                <!-- Left: Product Gallery -->
                <div class="ayur-gallery-main">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('large', array('class' => 'ayur-main-img', 'fetchpriority' => 'high')); ?>
                    <?php else : ?>
                        <div style="height:420px; display:flex; align-items:center; justify-content:center; font-size:72px; background:#F4F2EC;">
                            🌿
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right: High-Converting Purchase Column -->
                <div class="ayur-product-summary">
                    <?php if ($cert) : ?>
                        <div style="font-size:12px; font-weight:700; color:var(--ayur-gold-dark); text-transform:uppercase; letter-spacing:1.5px; margin-bottom:6px;">
                            🛡️ <?php echo esc_html($cert); ?>
                        </div>
                    <?php endif; ?>

                    <h1><?php the_title(); ?></h1>

                    <div class="ayur-product-meta-strip">
                        <?php echo ayur_get_star_rating_html($product); ?>
                        <?php if ($net_qty) : ?>
                            <span style="color:#6B7280;">•</span>
                            <span style="font-weight:600; color:#374151;">📦 <?php echo esc_html($net_qty); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Price & Savings Box -->
                    <div class="ayur-single-price-box">
                        <span class="ayur-single-price">₹<?php echo number_format($price); ?></span>
                        <?php if ($mrp) : ?>
                            <span class="ayur-single-mrp">₹<?php echo number_format($mrp); ?></span>
                            <?php
                            $diff = $mrp - $price;
                            $pct = round(($diff / $mrp) * 100);
                            ?>
                            <span class="ayur-single-save-badge">SAVE <?php echo $pct; ?>% (₹<?php echo number_format($diff); ?> OFF)</span>
                        <?php endif; ?>
                    </div>

                    <!-- Short Benefits Checklist -->
                    <?php if (!empty($benefits)) : ?>
                        <ul class="ayur-benefits-list">
                            <?php
                            $lines = explode("\n", $benefits);
                            foreach ($lines as $line) {
                                $clean_line = trim(ltrim($line, "•- "));
                                if (!empty($clean_line)) {
                                    echo '<li>' . esc_html($clean_line) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                    <?php else : ?>
                        <div style="font-size:14px; color:#4B5563; margin-bottom:20px; line-height:1.6;">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Live Stock Urgency Badge -->
                    <div class="ayur-stock-alert">
                        🔥 In Stock — Limited small-batch botanical formulation available
                    </div>

                    <!-- Add to Cart / Buy Now CTAs -->
                    <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
                        <div class="ayur-cta-row">
                            <?php if (!$product->is_type('variable')) : ?>
                                <?php
                                woocommerce_quantity_input(array(
                                    'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                                    'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                                    'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(),
                                ));
                                ?>
                            <?php endif; ?>

                            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="ayur-btn-add-cart single_add_to_cart_button button alt">
                                ADD TO CART
                            </button>

                            <a href="<?php echo esc_url(add_query_arg(array('ayur_quick_buy' => $product->get_id()), home_url('/'))); ?>" class="ayur-btn-buy-now">
                                BUY NOW (⚡ EXPRESS)
                            </a>
                        </div>
                    </form>

                    <!-- Trust Bar for Indian Customers -->
                    <div style="border-top:1px solid #E5E0D8; padding-top:18px; margin-top:20px; display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; font-size:13px; color:#374151;">
                        <div>💵 <strong>Cash on Delivery:</strong> Available</div>
                        <div>⚡ <strong>Dispatch:</strong> Within 24 Hours</div>
                        <div>🌿 <strong>Formulation:</strong> 100% Ayurvedic</div>
                        <div>🔄 <strong>Returns:</strong> 7-Day Hassle-Free</div>
                    </div>
                </div>
            </div>

            <!-- Ayurvedic Tabs (Ingredients, Dosage, Storage, Reviews) -->
            <div style="margin-top:60px; background:#FFFFFF; border:1px solid #E5E0D8; border-radius:16px; padding:32px; box-shadow:var(--ayur-shadow-soft);">
                <?php woocommerce_output_product_data_tabs(); ?>
            </div>

            <!-- Related Products Section -->
            <div style="margin-top:60px;">
                <?php woocommerce_output_related_products(); ?>
            </div>
        </div>
    </div>

    <!-- Mobile Sticky Purchase Bar -->
    <div class="ayur-mobile-sticky-bar">
        <div class="ayur-sticky-price">
            <span>₹<?php echo number_format($price); ?></span>
            <?php if ($mrp) : ?>
                <span class="ayur-sticky-mrp">₹<?php echo number_format($mrp); ?></span>
            <?php endif; ?>
        </div>
        <div class="ayur-sticky-actions">
            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="ayur-sticky-btn ayur-sticky-add-cart">
                Add to Cart
            </a>
            <a href="<?php echo esc_url(add_query_arg(array('ayur_quick_buy' => $product->get_id()), home_url('/'))); ?>" class="ayur-sticky-btn ayur-sticky-buy-now">
                Buy Now ⚡
            </a>
        </div>
    </div>

    <?php
endwhile;

get_footer();
