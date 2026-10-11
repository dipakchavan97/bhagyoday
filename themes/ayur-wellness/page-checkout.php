<?php
/**
 * Template Name: Checkout Page
 * Premium D2C Editorial Architecture — Bhagyoday Ayurveda
 * Compact, Conversion-Focused Checkout Page
 */

defined('ABSPATH') || exit;

get_header();
?>

<div class="ayur-checkout-main-wrap">
    <div class="ayur-container">
        <?php if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) : ?>
            <div class="ayur-order-received-entry">
                <?php
                while (have_posts()) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </div>
        <?php else : ?>
            <div class="ayur-checkout-entry">
                <?php
                while (have_posts()) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
