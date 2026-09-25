<?php
/**
 * Custom Shop & Category Archive Template
 */

defined('ABSPATH') || exit;

get_header();
?>

<div class="ayur-archive-banner" style="background:#FAF8F5; border-bottom:1px solid #E5E0D8; padding:40px 0;">
    <div class="ayur-container">
        <div style="font-size:13px; color:#6B7280; margin-bottom:10px;">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a> / <span><?php woocommerce_page_title(); ?></span>
        </div>
        <h1 class="ayur-section-title" style="margin-bottom:8px;"><?php woocommerce_page_title(); ?></h1>
        <?php
        $term_desc = term_description();
        if ($term_desc) : ?>
            <div style="font-size:15px; color:#5C6A64; max-width:650px;"><?php echo $term_desc; ?></div>
        <?php endif; ?>

        <!-- Category Filter Pills -->
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:24px;">
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" style="padding:6px 16px; border-radius:999px; background:<?php echo is_shop() ? '#1E3A2F; color:#fff;' : '#fff; color:#1E3A2F; border:1px solid #E5E0D8;'; ?> font-size:13px; font-weight:600;">
                All Remedies
            </a>
            <?php
            $cats = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
            if (!empty($cats) && !is_wp_error($cats)) {
                foreach ($cats as $cat) {
                    if ($cat->slug === 'uncategorized') continue;
                    $link = get_term_link($cat);
                    if (is_wp_error($link)) continue;
                    $is_current = is_product_category($cat->slug);
                    $style = $is_current ? 'background:#1E3A2F; color:#fff;' : 'background:#fff; color:#1E3A2F; border:1px solid #E5E0D8;';
                    echo '<a href="' . esc_url($link) . '" style="padding:6px 16px; border-radius:999px; ' . $style . ' font-size:13px; font-weight:600;">' . esc_html($cat->name) . '</a>';
                }
            }
            ?>
        </div>
    </div>
</div>

<div class="ayur-container" style="padding:50px 20px;">
    <?php if (woocommerce_product_loop()) : ?>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:16px;">
            <?php woocommerce_result_count(); ?>
            <?php woocommerce_catalog_ordering(); ?>
        </div>

        <div class="ayur-products-grid">
            <?php
            if (wc_get_loop_prop('total')) {
                while (have_posts()) {
                    the_post();
                    wc_get_template_part('content', 'product');
                }
            }
            ?>
        </div>

        <div style="margin-top:50px; text-align:center;">
            <?php woocommerce_pagination(); ?>
        </div>
    <?php else : ?>
        <div style="text-align:center; padding:60px 20px;">
            <p style="font-size:18px; color:#5C6A64;"><?php _e('No Ayurvedic remedies found in this category.', 'ayur-wellness'); ?></p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary" style="display:inline-flex; margin-top:16px;">
                View All Products
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
