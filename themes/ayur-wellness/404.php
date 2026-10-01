<?php
/**
 * 404 Error Page Template
 * Premium D2C Editorial Architecture — Bhagyoday Ayurveda
 * Compact, Helpful, and Conversion-Focused
 */

defined('ABSPATH') || exit;

get_header();
?>

<div class="ayur-page-header ayur-404-header">
    <div class="ayur-container">
        <nav class="ayur-breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'ayur-wellness'); ?>">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'ayur-wellness'); ?></a>
            <span class="ayur-breadcrumb-sep"><?php echo ayur_svg_icon('chevron-right', '', 11); ?></span>
            <span class="ayur-breadcrumb-current"><?php _e('404 Page Not Found', 'ayur-wellness'); ?></span>
        </nav>
    </div>
</div>

<div class="ayur-container ayur-page-content-wrap ayur-404-wrap">
    <div class="ayur-404-card">
        <div class="ayur-404-icon-wrap" aria-hidden="true">
            <?php echo ayur_svg_icon('leaf', '', 40); ?>
        </div>
        <span class="ayur-404-eyebrow"><?php _e('ERROR 404', 'ayur-wellness'); ?></span>
        <h1 class="ayur-404-title"><?php _e('Page Not Found', 'ayur-wellness'); ?></h1>
        <p class="ayur-404-desc">
            <?php _e('The page or herbal remedy you are looking for may have moved, been renamed, or is currently unavailable.', 'ayur-wellness'); ?>
        </p>

        <div class="ayur-404-actions">
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary">
                <span><?php _e('CONTINUE SHOPPING', 'ayur-wellness'); ?></span>
                <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-btn-secondary">
                <span><?php _e('RETURN HOME', 'ayur-wellness'); ?></span>
            </a>
        </div>

        <div class="ayur-404-trust-strip">
            <div class="ayur-trust-mini-item">
                <?php echo ayur_svg_icon('shield-check', '', 14); ?>
                <span><?php _e('100% Authentic Botanicals', 'ayur-wellness'); ?></span>
            </div>
            <span class="ayur-trust-mini-dot">&bull;</span>
            <div class="ayur-trust-mini-item">
                <?php echo ayur_svg_icon('truck', '', 14); ?>
                <span><?php _e('Pan-India Fast Dispatch', 'ayur-wellness'); ?></span>
            </div>
            <span class="ayur-trust-mini-dot">&bull;</span>
            <div class="ayur-trust-mini-item">
                <?php echo ayur_svg_icon('credit-card', '', 14); ?>
                <span><?php _e('Cash on Delivery Available', 'ayur-wellness'); ?></span>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
