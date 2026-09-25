<?php
/**
 * Custom Ayurvedic Product Card Template
 * Premium D2C Editorial Ecommerce Architecture — Bhagyoday Ayurveda
 */

defined('ABSPATH') || exit;

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

$product_id = $product->get_id();
$mrp = get_post_meta($product_id, '_ayur_mrp', true);
$net_qty = get_post_meta($product_id, '_ayur_net_quantity', true);
$is_combo = has_term('combos', 'product_cat', $product_id);
$price = (float) $product->get_price();

// Category eyebrow
$categories = get_the_terms($product_id, 'product_cat');
$cat_name = '';
if (!empty($categories) && !is_wp_error($categories)) {
    foreach ($categories as $term) {
        if ($term->slug !== 'uncategorized') {
            $cat_name = $term->name;
            break;
        }
    }
}
?>

<div <?php wc_product_class('ayur-product-card', $product); ?>>
    <!-- Subtle Single Badge (Only when truly relevant) -->
    <?php if ($is_combo) : ?>
        <div class="ayur-card-badge-wrap">
            <span class="ayur-card-badge"><?php _e('VALUE BUNDLE', 'ayur-wellness'); ?></span>
        </div>
    <?php elseif ($product->is_featured()) : ?>
        <div class="ayur-card-badge-wrap">
            <span class="ayur-card-badge"><?php _e('CLASSICAL FORMULATION', 'ayur-wellness'); ?></span>
        </div>
    <?php endif; ?>

    <!-- Product Image Stage with Breathing Room -->
    <div class="ayur-product-thumb-wrap">
        <a href="<?php the_permalink(); ?>" class="ayur-product-thumb-link" aria-label="<?php echo esc_attr($product->get_name()); ?>">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('woocommerce_thumbnail', array(
                    'class'   => 'ayur-product-thumb',
                    'loading' => 'lazy',
                    'alt'     => esc_attr($product->get_name())
                )); ?>
            <?php else : ?>
                <div class="ayur-product-thumb-fallback">
                    <?php echo ayur_svg_icon('leaf', '', 36, 1.5); ?>
                </div>
            <?php endif; ?>
        </a>
    </div>

    <!-- Product Info Section -->
    <div class="ayur-product-info">
        <?php if ($cat_name) : ?>
            <span class="ayur-product-cat"><?php echo esc_html($cat_name); ?></span>
        <?php endif; ?>

        <h3 class="ayur-product-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <?php if ($net_qty) : ?>
            <div class="ayur-product-descriptor">
                <span><?php echo esc_html($net_qty); ?></span>
            </div>
        <?php endif; ?>

        <div class="ayur-product-pricing">
            <span class="ayur-price">₹<?php echo number_format($price); ?></span>
            <?php if ($mrp && (float)$mrp > $price) : ?>
                <span class="ayur-mrp">MRP ₹<?php echo number_format($mrp); ?></span>
                <?php
                $save_amount = (float)$mrp - $price;
                if ($save_amount > 0) :
                ?>
                    <span class="ayur-save-tag">Save ₹<?php echo number_format($save_amount); ?></span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="ayur-card-action-wrap">
            <?php if ($product->is_type('variable')) : ?>
                <a href="<?php the_permalink(); ?>" class="ayur-card-btn">
                    <span><?php _e('View Formulation', 'ayur-wellness'); ?></span>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                   data-quantity="1"
                   class="ayur-card-btn ajax_add_to_cart add_to_cart_button"
                   data-product_id="<?php echo esc_attr($product_id); ?>"
                   data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                   aria-label="<?php echo esc_attr($product->add_to_cart_description()); ?>">
                    <?php echo ayur_svg_icon('shopping-bag', '', 14); ?>
                    <span><?php _e('Add to Cart', 'ayur-wellness'); ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
