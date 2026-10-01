<?php
/**
 * Template Name: My Account Page
 * Premium D2C Customer Dashboard — Bhagyoday Ayurveda
 */

defined('ABSPATH') || exit;

get_header();
?>

<div class="ayur-page-header ayur-account-header">
    <div class="ayur-container">
        <nav class="ayur-breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'ayur-wellness'); ?>">
            <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'ayur-wellness'); ?></a>
            <span class="ayur-breadcrumb-sep"><?php echo ayur_svg_icon('chevron-right', '', 11); ?></span>
            <span class="ayur-breadcrumb-current"><?php _e('My Account', 'ayur-wellness'); ?></span>
        </nav>
        <h1 class="ayur-page-title ayur-account-title"><?php _e('My Account', 'ayur-wellness'); ?></h1>
    </div>
</div>

<div class="ayur-container ayur-account-content-wrap">
    <div class="ayur-account-entry">
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
