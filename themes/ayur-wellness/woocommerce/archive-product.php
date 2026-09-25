<?php
/**
 * Custom Shop & Category Archive Template
 * Premium D2C Editorial Architecture — Bhagyoday Ayurveda
 */

defined('ABSPATH') || exit;

get_header();
?>

<div class="ayur-archive-banner">
    <div class="ayur-container">
        <nav class="ayur-breadcrumbs" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <span class="ayur-breadcrumb-sep"><?php echo ayur_svg_icon('chevron-right', '', 11); ?></span>
            <span class="ayur-breadcrumb-current"><?php woocommerce_page_title(); ?></span>
        </nav>
        <h1 class="ayur-archive-title"><?php woocommerce_page_title(); ?></h1>
        <?php
        $term_desc = term_description();
        if ($term_desc) : ?>
            <div class="ayur-archive-desc"><?php echo $term_desc; ?></div>
        <?php endif; ?>

        <!-- Category Filter Pills -->
        <div class="ayur-cat-pills-row">
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-cat-pill <?php echo is_shop() ? 'active' : ''; ?>">
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
                    echo '<a href="' . esc_url($link) . '" class="ayur-cat-pill ' . ($is_current ? 'active' : '') . '">' . esc_html($cat->name) . '</a>';
                }
            }
            ?>
        </div>
    </div>
</div>

<div class="ayur-container ayur-archive-body">
    <?php if (woocommerce_product_loop()) : ?>
        <div class="ayur-archive-controls">
            <div class="ayur-results-count">
                <?php woocommerce_result_count(); ?>
            </div>
            <div class="ayur-ordering-wrap">
                <?php woocommerce_catalog_ordering(); ?>
            </div>
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

        <div class="ayur-pagination-wrap">
            <?php woocommerce_pagination(); ?>
        </div>
    <?php else : ?>
        <div class="ayur-empty-state">
            <div class="ayur-empty-icon"><?php echo ayur_svg_icon('leaf', '', 40); ?></div>
            <p><?php _e('No Ayurvedic remedies found in this category.', 'ayur-wellness'); ?></p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary">
                <span>View All Products</span>
                <?php echo ayur_svg_icon('arrow-right', '', 16); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
