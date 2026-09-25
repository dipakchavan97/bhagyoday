<?php
/**
 * Custom Ayurvedic Product Card Template
 */

defined('ABSPATH') || exit;

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

$mrp = get_post_meta($product->get_id(), '_ayur_mrp', true);
$net_qty = get_post_meta($product->get_id(), '_ayur_net_quantity', true);
?>

<div <?php wc_product_class('ayur-product-card', $product); ?>>
    <?php if ($product->is_featured()) : ?>
        <span class="ayur-product-badge">BESTSELLER</span>
    <?php endif; ?>

    <?php echo ayur_get_discount_badge($product); ?>

    <div class="ayur-product-thumb-wrap">
        <a href="<?php the_permalink(); ?>">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium', array('class' => 'ayur-product-thumb', 'loading' => 'lazy')); ?>
            <?php else : ?>
                <div class="ayur-product-thumb" style="display:flex; align-items:center; justify-content:center; font-size:48px;">
                    🌿
                </div>
            <?php endif; ?>
        </a>
    </div>

    <div class="ayur-product-info">
        <?php echo ayur_get_star_rating_html($product); ?>

        <h3 class="ayur-product-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <?php if ($net_qty) : ?>
            <div style="font-size:12px; color:#6B7280; margin-bottom:8px;">📦 <?php echo esc_html($net_qty); ?></div>
        <?php endif; ?>

        <div class="ayur-product-pricing">
            <span class="ayur-price">₹<?php echo number_format($product->get_price()); ?></span>
            <?php if ($mrp) : ?>
                <span class="ayur-mrp">₹<?php echo number_format($mrp); ?></span>
            <?php endif; ?>
        </div>

        <?php if ($product->is_type('variable')) : ?>
            <a href="<?php the_permalink(); ?>" class="ayur-add-btn">
                SELECT OPTIONS
            </a>
        <?php else : ?>
            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="ayur-add-btn">
                ADD TO CART
            </a>
        <?php endif; ?>
    </div>
</div>
