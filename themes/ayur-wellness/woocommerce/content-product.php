<?php
/**
 * Custom Ayurvedic Product Card Template
 * Premium D2C Editorial Ecommerce Architecture
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
?>

<div <?php wc_product_class('ayur-product-card', $product); ?>>
    <div class="ayur-card-badges">
        <?php if ($is_combo) : ?>
            <span class="ayur-product-badge ayur-badge-combo"><?php _e('VALUE BUNDLE', 'ayur-wellness'); ?></span>
        <?php elseif ($product->is_featured()) : ?>
            <span class="ayur-product-badge ayur-badge-bestseller"><?php _e('BESTSELLER', 'ayur-wellness'); ?></span>
        <?php endif; ?>

        <?php echo ayur_get_discount_badge($product); ?>
    </div>

    <!-- Product Image Wrap -->
    <div class="ayur-product-thumb-wrap">
        <a href="<?php the_permalink(); ?>" class="ayur-product-thumb-link" aria-label="<?php echo esc_attr($product->get_name()); ?>">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('woocommerce_thumbnail', array('class' => 'ayur-product-thumb', 'loading' => 'lazy', 'alt' => esc_attr($product->get_name()))); ?>
            <?php else : ?>
                <div class="ayur-product-thumb-fallback">
                    <?php echo ayur_svg_icon('leaf', '', 36, 1.5); ?>
                </div>
            <?php endif; ?>
        </a>
    </div>

    <!-- Product Info Section -->
    <div class="ayur-product-info">
        <?php if ($net_qty) : ?>
            <div class="ayur-product-qty-tag">
                <?php echo ayur_svg_icon('package', '', 12, 1.75); ?>
                <span><?php echo esc_html($net_qty); ?></span>
            </div>
        <?php endif; ?>

        <h3 class="ayur-product-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <div class="ayur-product-pricing">
            <span class="ayur-price">₹<?php echo number_format($product->get_price()); ?></span>
            <?php if ($mrp && (float)$mrp > (float)$product->get_price()) : ?>
                <span class="ayur-mrp">₹<?php echo number_format($mrp); ?></span>
            <?php endif; ?>
        </div>

        <div class="ayur-card-action-wrap">
            <?php if ($product->is_type('variable')) : ?>
                <a href="<?php the_permalink(); ?>" class="ayur-add-btn">
                    <span><?php _e('Select Options', 'ayur-wellness'); ?></span>
                    <?php echo ayur_svg_icon('chevron-right', '', 14); ?>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" data-quantity="1" class="ayur-add-btn ajax_add_to_cart add_to_cart_button" data-product_id="<?php echo esc_attr($product_id); ?>" data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" aria-label="<?php echo esc_attr($product->add_to_cart_description()); ?>">
                    <?php echo ayur_svg_icon('shopping-bag', '', 15); ?>
                    <span><?php _e('Add to Cart', 'ayur-wellness'); ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
