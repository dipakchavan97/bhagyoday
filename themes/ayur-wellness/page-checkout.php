<?php
/**
 * Template Name: Checkout Page
 * Premium D2C Editorial Architecture — Bhagyoday Ayurveda
 * Compact, Conversion-Focused Checkout Page
 */

defined('ABSPATH') || exit;

get_header();
?>

<div class="ayur-page-header ayur-checkout-header">
    <div class="ayur-container">
        <nav class="ayur-breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'ayur-wellness'); ?>">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'ayur-wellness'); ?></a>
            <span class="ayur-breadcrumb-sep"><?php echo ayur_svg_icon('chevron-right', '', 11); ?></span>
            <span class="ayur-breadcrumb-current"><?php _e('Checkout', 'ayur-wellness'); ?></span>
        </nav>
        <h1 class="ayur-page-title ayur-checkout-title"><?php _e('Checkout', 'ayur-wellness'); ?></h1>
    </div>
</div>

<div class="ayur-container ayur-checkout-content-wrap">
    <div class="ayur-checkout-entry">
        <?php
        while (have_posts()) :
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
</div>

<?php
get_footer();
