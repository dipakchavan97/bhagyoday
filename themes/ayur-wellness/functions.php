<?php
/**
 * Ayur Wellness Theme Functions and Definitions
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AYUR_THEME_VERSION', '1.0.0');

/**
 * Theme Setup
 */
function ayur_wellness_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));

    // WooCommerce Theme Support
    add_theme_support('woocommerce', array(
        'thumbnail_image_width' => 450,
        'single_image_width'    => 800,
        'product_grid'          => array(
            'default_rows'    => 3,
            'min_rows'        => 2,
            'max_rows'        => 8,
            'default_columns' => 4,
            'min_columns'     => 1,
            'max_columns'     => 4,
        ),
    ));
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Register Navigation Menus
    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'ayur-wellness'),
        'footer'  => __('Footer Navigation', 'ayur-wellness'),
    ));
}
add_action('after_setup_theme', 'ayur_wellness_setup');

/**
 * Enqueue Styles & Scripts
 */
function ayur_wellness_scripts() {
    // Google Fonts: Cinzel & Plus Jakarta Sans
    wp_enqueue_style('ayur-google-fonts', 'https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', array(), null);

    // Main Stylesheet
    wp_enqueue_style('ayur-main-style', get_stylesheet_uri(), array(), AYUR_THEME_VERSION);

    // Main Theme JS
    wp_enqueue_script('ayur-theme-script', get_template_directory_uri() . '/assets/js/ayur-theme.js', array('jquery'), AYUR_THEME_VERSION, true);

    wp_localize_script('ayur-theme-script', 'ayurThemeParams', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'cart_url' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/'),
        'checkout_url' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/'),
    ));
}
add_action('wp_enqueue_scripts', 'ayur_wellness_scripts');

/**
 * Safe Helper: Get Category URL without WP_Error
 */
function ayur_get_cat_url($slug) {
    if (taxonomy_exists('product_cat')) {
        $term = get_term_by('slug', $slug, 'product_cat');
        if ($term && !is_wp_error($term)) {
            $link = get_term_link($term, 'product_cat');
            if (!is_wp_error($link)) {
                return $link;
            }
        }
    }
    return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
}

/**
 * Helper: Discount Percentage Badge
 */
function ayur_get_discount_badge($product) {
    if (!$product) return '';

    $mrp = (float) get_post_meta($product->get_id(), '_ayur_mrp', true);
    $price = (float) $product->get_price();

    if ($mrp > $price && $mrp > 0) {
        $discount_pct = round((($mrp - $price) / $mrp) * 100);
        return '<span class="ayur-product-discount-badge">' . $discount_pct . '% OFF</span>';
    } elseif ($product->is_on_sale()) {
        $reg = (float) $product->get_regular_price();
        if ($reg > $price && $reg > 0) {
            $discount_pct = round((($reg - $price) / $reg) * 100);
            return '<span class="ayur-product-discount-badge">' . $discount_pct . '% OFF</span>';
        }
    }
    return '';
}

/**
 * Helper: Star Rating HTML
 */
function ayur_get_star_rating_html($product) {
    $rating = $product ? (float) $product->get_average_rating() : 5.0;
    if ($rating == 0) $rating = 5.0;
    $count = $product ? $product->get_review_count() : 12;
    if ($count == 0) $count = 14;

    return '<div class="ayur-product-rating">
        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
        <span class="ayur-rating-count">(' . $count . ')</span>
    </div>';
}
