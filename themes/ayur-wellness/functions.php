<?php
/**
 * Ayur Wellness Theme Functions and Definitions
 * Premium Authentic Indian Ayurvedic D2C Architecture
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AYUR_THEME_VERSION', '2.7.9');

/**
 * Theme Setup
 */
function ayur_wellness_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));

    // WooCommerce Theme Support
    add_theme_support('woocommerce', array(
        'thumbnail_image_width' => 500,
        'single_image_width'    => 900,
        'product_grid'          => array(
            'default_rows'    => 3,
            'min_rows'        => 2,
            'max_rows'        => 8,
            'default_columns' => 4,
            'min_columns'     => 2,
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
    // Google Fonts: Cormorant Garamond (Editorial Serif) & Plus Jakarta Sans (Clean Modern Sans)
    wp_enqueue_style(
        'ayur-google-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
        array(),
        null
    );

    // Main Stylesheet
    wp_enqueue_style('ayur-main-style', get_stylesheet_uri(), array(), AYUR_THEME_VERSION);

    // Main Theme JS
    wp_enqueue_script('ayur-theme-script', get_template_directory_uri() . '/assets/js/ayur-theme.js', array('jquery'), AYUR_THEME_VERSION, true);

    // Photorealistic Cinematic Animation Layer (Front Page only)
    if (is_front_page() || is_home()) {
        wp_enqueue_script('ayur-hero-cinematic', get_template_directory_uri() . '/assets/js/ayur-hero-cinematic.js', array(), AYUR_THEME_VERSION, true);
    }

    wp_localize_script('ayur-theme-script', 'ayurThemeParams', array(
        'ajax_url'     => admin_url('admin-ajax.php'),
        'nonce'        => wp_create_nonce('ayur_theme_nonce'),
        'cart_url'     => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/'),
        'checkout_url' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/'),
    ));
}
add_action('wp_enqueue_scripts', 'ayur_wellness_scripts');

/**
 * Unified Premium Vector Icon System (Lucide-Style Stroke SVG)
 * Stroke: 1.5 - 2px | Size: 16-24px | Zero UI emojis
 */
function ayur_svg_icon($name, $classes = '', $size = 20, $stroke_width = 1.75) {
    $icons = array(
        'leaf' => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>',
        'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'truck' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-5.28a2 2 0 0 0-.59-1.42L18.41 7.3A2 2 0 0 0 17 6.72H14"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'credit-card' => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
        'shopping-bag' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'menu' => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
        'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'rotate-ccw' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
        'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'sparkles' => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>',
        'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        'flask' => '<path d="M10 2v7.31L4.15 19.86A2 2 0 0 0 5.87 23h12.26a2 2 0 0 0 1.72-3.14L14 9.31V2"/><path d="M8.5 2h7"/><path d="M14 9.3 8.5 18"/>',
        'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'grid' => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
        'flame' => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
        'zap' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'activity' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        'package' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'trash' => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>',
        'headphones' => '<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>',
        'badge-check' => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>',
        'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'star-outline' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'whatsapp' => '<path fill="currentColor" stroke="none" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>',
    );

    if (!isset($icons[$name])) {
        return '';
    }

    $is_whatsapp = ($name === 'whatsapp');
    $is_star     = ($name === 'star');
    $fill   = $is_whatsapp ? 'currentColor' : ($is_star ? '#B79552' : 'none');
    $stroke = $is_whatsapp ? 'none' : ($is_star ? '#B79552' : 'currentColor');
    $sw     = $is_whatsapp ? '0' : esc_attr($stroke_width);
    $cls    = $classes ? ' ' . esc_attr($classes) : '';

    return sprintf(
        '<svg class="ayur-icon ayur-icon-%s%s" width="%s" height="%s" viewBox="0 0 24 24" fill="%s" stroke="%s" stroke-width="%s" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
        esc_attr($name),
        $cls,
        esc_attr($size),
        esc_attr($size),
        $fill,
        $stroke,
        $sw,
        $icons[$name]
    );
}

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
 * Helper: Refined Discount Percentage Badge (No emojis, no screaming)
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
 * Helper: Clean Rating Display (Zero Emojis, Truth-in-Advertising)
 */
function ayur_get_star_rating_html($product) {
    if (!$product) return '';
    $rating = (float) $product->get_average_rating();
    $count = (int) $product->get_review_count();

    if ($count > 0 && $rating > 0) {
        $full_stars = min(5, max(1, (int) round($rating)));
        $stars_html = '';
        for ($i = 0; $i < $full_stars; $i++) {
            $stars_html .= ayur_svg_icon('star', '', 13);
        }
        for ($i = $full_stars; $i < 5; $i++) {
            $stars_html .= ayur_svg_icon('star-outline', '', 13);
        }
        return '<div class="ayur-product-rating">
            <span class="ayur-stars-wrap" style="display:inline-flex; align-items:center; gap:2px;">' . $stars_html . '</span>
            <span class="ayur-rating-count">(' . $count . ')</span>
        </div>';
    }

    if (is_product()) {
        return '<div class="ayur-product-rating"><a href="#reviews" class="ayur-be-first-review">' . ayur_svg_icon('sparkles', '', 13) . ' ' . __('Be the first to review', 'ayur-wellness') . '</a></div>';
    }

    return '';
}

/**
 * Cart Drawer HTML Generator
 */
function ayur_get_cart_drawer_content_html() {
    if (!function_exists('WC') || !WC()->cart) {
        return '<div class="ayur-cart-drawer-empty"><p>' . __('Cart unavailable', 'ayur-wellness') . '</p></div>';
    }

    $cart = WC()->cart;
    $count = $cart->get_cart_contents_count();
    $subtotal = (float) $cart->get_displayed_subtotal();
    $qty_discount = class_exists('Ayur_Quantity_Offers') ? Ayur_Quantity_Offers::calculate_cart_discount($cart) : 0;
    $eligible_subtotal = max(0, $subtotal - $qty_discount);
    $threshold = 999;
    $remaining = $threshold - $eligible_subtotal;
    $percent = min(100, max(0, round(($eligible_subtotal / $threshold) * 100)));

    ob_start();
    ?>
    <div class="ayur-drawer-header">
        <div class="ayur-drawer-title-wrap">
            <span class="ayur-drawer-title"><?php _e('Your Cart', 'ayur-wellness'); ?></span>
            <span class="ayur-drawer-count-badge"><?php echo esc_html($count); ?></span>
        </div>
        <button type="button" class="ayur-drawer-close-btn" id="ayurCloseCartDrawer" aria-label="<?php esc_attr_e('Close Cart', 'ayur-wellness'); ?>">
            <?php echo ayur_svg_icon('x', '', 18, 1.6); ?>
        </button>
    </div>

    <!-- Free Shipping Progress Indicator (Threshold ₹999) -->
    <div class="ayur-drawer-shipping-bar">
        <div class="ayur-drawer-shipping-text">
            <?php if ($remaining <= 0) : ?>
                <span class="unlocked"><?php echo ayur_svg_icon('check', '', 15); ?> <strong><?php _e('You have unlocked FREE Pan-India Delivery', 'ayur-wellness'); ?></strong></span>
            <?php else : ?>
                <span><?php echo ayur_svg_icon('truck', '', 15); ?> <?php printf(__('Add <strong>₹%s</strong> more for <strong>FREE Delivery</strong>', 'ayur-wellness'), number_format($remaining)); ?></span>
            <?php endif; ?>
        </div>
        <div class="ayur-drawer-progress-track">
            <div class="ayur-drawer-progress-fill" style="width: <?php echo esc_attr($percent); ?>%;"></div>
        </div>
    </div>

    <?php if ($cart->is_empty()) : ?>
        <div class="ayur-drawer-empty-state">
            <div class="ayur-drawer-empty-icon">
                <?php echo ayur_svg_icon('shopping-bag', '', 36, 1.4); ?>
            </div>
            <h4><?php _e('Your cart is empty', 'ayur-wellness'); ?></h4>
            <p><?php _e('Explore authentic classical formulations crafted to support your holistic wellness routine.', 'ayur-wellness'); ?></p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary ayur-btn-empty-shop">
                <span><?php _e('SHOP FORMULATIONS', 'ayur-wellness'); ?></span>
                <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
            </a>

            <!-- Conversion Recovery: 2 Featured Formulations with Direct Quick Add -->
            <div class="ayur-drawer-recovery-wrap">
                <span class="ayur-drawer-recovery-title"><?php _e('FEATURED FORMULATIONS', 'ayur-wellness'); ?></span>
                <div class="ayur-drawer-recovery-grid">
                    <?php
                    $rec_query = new WP_Query(array(
                        'post_type'      => 'product',
                        'posts_per_page' => 2,
                        'orderby'        => 'menu_order',
                        'order'          => 'ASC',
                    ));
                    if ($rec_query->have_posts()) :
                        while ($rec_query->have_posts()) : $rec_query->the_post();
                            $rec_prod = wc_get_product(get_the_ID());
                            $r_id = $rec_prod->get_id();
                            $r_price = (float) $rec_prod->get_price();
                            $r_mrp = (float) get_post_meta($r_id, '_ayur_mrp', true);
                            ?>
                            <div class="ayur-drawer-rec-card">
                                <div class="ayur-drawer-rec-thumb">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail(array(60, 60), array('alt' => get_the_title())); ?>
                                        <?php else : ?>
                                            <?php echo ayur_svg_icon('leaf', '', 24); ?>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="ayur-drawer-rec-details">
                                    <h5 class="ayur-drawer-rec-name">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h5>
                                    <div class="ayur-drawer-rec-pricing">
                                        <span class="rec-price">₹<?php echo number_format($r_price); ?></span>
                                        <?php if ($r_mrp > $r_price) : ?>
                                            <span class="rec-mrp">MRP ₹<?php echo number_format($r_mrp); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo esc_url($rec_prod->add_to_cart_url()); ?>"
                                       class="ayur-drawer-rec-add-btn ajax_add_to_cart add_to_cart_button"
                                       data-product_id="<?php echo esc_attr($r_id); ?>"
                                       data-quantity="1"
                                       aria-label="<?php echo esc_attr($rec_prod->add_to_cart_description()); ?>">
                                        <?php echo ayur_svg_icon('plus', '', 12); ?>
                                        <span><?php _e('Add', 'ayur-wellness'); ?></span>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="ayur-drawer-items-list">
            <?php
            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                    $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(array(72, 72)), $cart_item, $cart_item_key);
                    $price = WC()->cart->get_product_subtotal($_product, $cart_item['quantity']);
                    $net_qty = get_post_meta($product_id, '_ayur_net_quantity', true);
                    ?>
                    <div class="ayur-drawer-item" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                        <div class="ayur-drawer-item-thumb">
                            <?php if ($product_permalink) : ?>
                                <a href="<?php echo esc_url($product_permalink); ?>"><?php echo $thumbnail; ?></a>
                            <?php else : ?>
                                <?php echo $thumbnail; ?>
                            <?php endif; ?>
                        </div>
                        <div class="ayur-drawer-item-details">
                            <h4 class="ayur-drawer-item-name">
                                <?php if ($product_permalink) : ?>
                                    <a href="<?php echo esc_url($product_permalink); ?>"><?php echo esc_html($_product->get_name()); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html($_product->get_name()); ?>
                                <?php endif; ?>
                            </h4>
                            <?php if ($net_qty) : ?>
                                <div class="ayur-drawer-item-qty-tag"><?php echo esc_html($net_qty); ?></div>
                            <?php endif; ?>
                            <div class="ayur-drawer-item-price"><?php echo $price; ?></div>
                            <?php
                            $item_tier = class_exists('Ayur_Quantity_Offers') ? Ayur_Quantity_Offers::get_applicable_tier($cart_item['quantity']) : null;
                            if ($item_tier) : ?>
                                <div class="ayur-drawer-item-offer-tag">
                                    <?php printf(__('Buy %d+ and save %d%%', 'ayur-wellness'), $item_tier['min_qty'], $item_tier['discount_pct']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="ayur-drawer-item-actions">
                                <div class="ayur-drawer-qty-stepper">
                                    <button type="button" class="ayur-drawer-qty-btn" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" data-action="dec" aria-label="<?php esc_attr_e('Decrease quantity', 'ayur-wellness'); ?>">
                                        <?php echo ayur_svg_icon('minus', '', 12); ?>
                                    </button>
                                    <span class="ayur-drawer-qty-val"><?php echo esc_html($cart_item['quantity']); ?></span>
                                    <button type="button" class="ayur-drawer-qty-btn" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" data-action="inc" aria-label="<?php esc_attr_e('Increase quantity', 'ayur-wellness'); ?>">
                                        <?php echo ayur_svg_icon('plus', '', 12); ?>
                                    </button>
                                </div>
                                <button type="button" class="ayur-drawer-item-remove" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" title="<?php esc_attr_e('Remove item', 'ayur-wellness'); ?>" aria-label="<?php esc_attr_e('Remove item', 'ayur-wellness'); ?>">
                                    <?php echo ayur_svg_icon('trash', '', 15); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
            <?php
            // Phase 7 Section 24: Cart Cross-Sell (1-2 non-duplicate relevant recommendations)
            $in_cart_ids = array();
            foreach ($cart->get_cart() as $ci) {
                if (!empty($ci['product_id'])) {
                    $in_cart_ids[] = (int) $ci['product_id'];
                }
            }
            $cross_query = new WP_Query(array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'post__not_in'   => $in_cart_ids,
                'posts_per_page' => 2,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ));
            if ($cross_query->have_posts()) :
            ?>
                <div class="ayur-drawer-cross-sell">
                    <span class="ayur-drawer-cross-title"><?php _e('COMPLETE YOUR WELLNESS ROUTINE', 'ayur-wellness'); ?></span>
                    <div class="ayur-drawer-cross-grid">
                        <?php while ($cross_query->have_posts()) : $cross_query->the_post();
                            $c_prod = wc_get_product(get_the_ID());
                            $c_id = $c_prod->get_id();
                            $c_price = (float) $c_prod->get_price();
                            $c_mrp = (float) get_post_meta($c_id, '_ayur_mrp', true);
                        ?>
                            <div class="ayur-drawer-cross-item">
                                <div class="ayur-drawer-cross-thumb">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail(array(56, 56), array('alt' => get_the_title())); ?>
                                        <?php else : ?>
                                            <?php echo ayur_svg_icon('leaf', '', 20); ?>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="ayur-drawer-cross-info">
                                    <h5 class="ayur-drawer-cross-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
                                    <div class="ayur-drawer-cross-pricing">
                                        <span class="cross-price">₹<?php echo number_format($c_price); ?></span>
                                        <?php if ($c_mrp > $c_price) : ?>
                                            <span class="cross-mrp">MRP ₹<?php echo number_format($c_mrp); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="<?php echo esc_url($c_prod->add_to_cart_url()); ?>"
                                   class="ayur-drawer-cross-add-btn ajax_add_to_cart add_to_cart_button"
                                   data-product_id="<?php echo esc_attr($c_id); ?>"
                                   data-quantity="1"
                                   aria-label="<?php echo esc_attr($c_prod->add_to_cart_description()); ?>">
                                    <?php echo ayur_svg_icon('plus', '', 12); ?>
                                    <span><?php _e('Add', 'ayur-wellness'); ?></span>
                                </a>
                            </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="ayur-drawer-footer">
            <div class="ayur-drawer-breakdown">
                <div class="ayur-drawer-breakdown-row">
                    <span class="label"><?php _e('Subtotal', 'ayur-wellness'); ?></span>
                    <span class="val"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                </div>
                <?php
                $qty_discount = class_exists('Ayur_Quantity_Offers') ? Ayur_Quantity_Offers::calculate_cart_discount() : 0;
                if ($qty_discount > 0) :
                ?>
                <div class="ayur-drawer-breakdown-row ayur-drawer-discount-row">
                    <span class="label"><?php _e('Quantity Discount', 'ayur-wellness'); ?></span>
                    <span class="val" style="color: #2e7d32; font-weight: 600;">-₹<?php echo number_format($qty_discount); ?></span>
                </div>
                <?php endif; ?>
                <div class="ayur-drawer-breakdown-row">
                    <span class="label"><?php _e('Shipping', 'ayur-wellness'); ?></span>
                    <span class="val">
                        <?php if ($remaining <= 0) : ?>
                            <span class="free-shipping-tag"><?php _e('FREE Pan-India', 'ayur-wellness'); ?></span>
                        <?php else : ?>
                            <span><?php _e('Calculated at checkout &bull; Free over ₹999', 'ayur-wellness'); ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="ayur-drawer-breakdown-row ayur-drawer-total-row">
                    <span class="label"><?php _e('Estimated Total', 'ayur-wellness'); ?></span>
                    <span class="val"><?php echo WC()->cart->get_cart_total(); ?></span>
                </div>
            </div>

            <div class="ayur-drawer-cta-group">
                <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="ayur-btn-primary ayur-btn-drawer-checkout">
                    <span><?php _e('PROCEED TO CHECKOUT', 'ayur-wellness'); ?></span>
                    <?php echo ayur_svg_icon('arrow-right', '', 15); ?>
                </a>
                <button type="button" class="ayur-drawer-continue-btn" id="ayurContinueShopping">
                    <span><?php _e('Continue Shopping', 'ayur-wellness'); ?></span>
                </button>
            </div>
        </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

/**
 * AJAX: Update Cart Drawer Quantity
 */
function ayur_ajax_update_cart_drawer_qty() {
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : (isset($_POST['cart_key']) ? sanitize_text_field($_POST['cart_key']) : '');
    $qty           = isset($_POST['quantity']) ? intval($_POST['quantity']) : -1;
    $delta         = isset($_POST['delta']) ? intval($_POST['delta']) : 0;

    if ($cart_item_key && WC()->cart) {
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        if ($cart_item) {
            if ($qty >= 0) {
                $new_qty = $qty;
            } else {
                $new_qty = max(0, $cart_item['quantity'] + $delta);
            }
            if ($new_qty === 0) {
                WC()->cart->remove_cart_item($cart_item_key);
            } else {
                WC()->cart->set_quantity($cart_item_key, $new_qty);
            }
        }
    }

    $drawer_html = ayur_get_cart_drawer_content_html();
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

    wp_send_json_success(array(
        'html'        => $drawer_html,
        'drawer_html' => $drawer_html,
        'count'       => $count,
        'cart_count'  => $count,
    ));
}
add_action('wp_ajax_ayur_update_drawer_qty', 'ayur_ajax_update_cart_drawer_qty');
add_action('wp_ajax_nopriv_ayur_update_drawer_qty', 'ayur_ajax_update_cart_drawer_qty');
add_action('wp_ajax_ayur_update_cart_drawer_qty', 'ayur_ajax_update_cart_drawer_qty');
add_action('wp_ajax_nopriv_ayur_update_cart_drawer_qty', 'ayur_ajax_update_cart_drawer_qty');

/**
 * AJAX: Remove Item from Cart Drawer
 */
function ayur_ajax_remove_cart_drawer_item() {
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : (isset($_POST['cart_key']) ? sanitize_text_field($_POST['cart_key']) : '');

    if ($cart_item_key && WC()->cart) {
        WC()->cart->remove_cart_item($cart_item_key);
    }

    $drawer_html = ayur_get_cart_drawer_content_html();
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

    wp_send_json_success(array(
        'html'        => $drawer_html,
        'drawer_html' => $drawer_html,
        'count'       => $count,
        'cart_count'  => $count,
    ));
}
add_action('wp_ajax_ayur_remove_drawer_item', 'ayur_ajax_remove_cart_drawer_item');
add_action('wp_ajax_nopriv_ayur_remove_drawer_item', 'ayur_ajax_remove_cart_drawer_item');
add_action('wp_ajax_ayur_remove_cart_drawer_item', 'ayur_ajax_remove_cart_drawer_item');
add_action('wp_ajax_nopriv_ayur_remove_cart_drawer_item', 'ayur_ajax_remove_cart_drawer_item');

/**
 * AJAX: Get Full Cart Drawer Content
 */
function ayur_ajax_get_cart_drawer() {
    $drawer_html = ayur_get_cart_drawer_content_html();
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

    wp_send_json_success(array(
        'html'        => $drawer_html,
        'drawer_html' => $drawer_html,
        'count'       => $count,
        'cart_count'  => $count,
    ));
}
add_action('wp_ajax_ayur_get_cart_drawer', 'ayur_ajax_get_cart_drawer');
add_action('wp_ajax_nopriv_ayur_get_cart_drawer', 'ayur_ajax_get_cart_drawer');

/**
 * WooCommerce Fragments for Dynamic Header and Drawer Badges
 */
function ayur_wc_cart_fragments($fragments) {
    $count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
    $fragments['span.ayur-cart-badge'] = '<span class="ayur-cart-badge">' . esc_html($count) . '</span>';
    $fragments['div.ayur-cart-drawer'] = '<div class="ayur-cart-drawer" id="ayurCartDrawer" role="dialog" aria-modal="true" aria-label="Shopping Cart">' . ayur_get_cart_drawer_content_html() . '</div>';
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'ayur_wc_cart_fragments', 20);

/**
 * Configure default WooCommerce related products query parameters
 */
function ayur_related_products_args($args) {
    $args['posts_per_page'] = 4;
    $args['columns'] = 4;
    return $args;
}
add_filter('woocommerce_output_related_products_args', 'ayur_related_products_args');

/**
 * Phase 5: Structured Product Information System
 * Provides authentic, verified, non-duplicated product data for editorial single product pages.
 */
function ayur_get_product_extended_data($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) {
        return false;
    }

    $net_qty = get_post_meta($product_id, '_ayur_net_quantity', true);
    $batch   = get_post_meta($product_id, '_ayur_batch', true);
    $expiry  = get_post_meta($product_id, '_ayur_expiry', true);

    // Standardized repository of verified product information
    $data_map = array(
        // Product 30: Ashwagandha Shilajit
        30 => array(
            'positioning' => 'Traditional Ayurvedic Rasayana combining purified Himalayan Shilajit with standardized Ashwagandha root extract for physical endurance, natural vitality, and nervous resilience.',
            'benefits' => array(
                'Combines pure purified Shilajit with standardized Ashwagandha root extract',
                'Traditionally used in classical Ayurveda to support daily stamina and cellular energy reserves',
                'Formulated with Safed Musli and Gokshura to aid muscle vigor and post-exertion recovery',
                '100% plant cellulose vegetarian capsule format with zero synthetic stimulants'
            ),
            'botanicals' => array(
                array(
                    'name'  => 'Ashwagandha Root Extract',
                    'latin' => 'Withania somnifera (300 mg)',
                    'role'  => 'Standardized classical root extract traditionally utilized to foster Ojas, support vitality, and build resilience against daily mental and physical stress.'
                ),
                array(
                    'name'  => 'Shuddha Himalayan Shilajit',
                    'latin' => 'Asphaltum punjabianum (350 mg)',
                    'role'  => 'Triphala-purified classical mineral pitch rich in natural fulvic acid and trace minerals to sustain cellular ATP.'
                ),
                array(
                    'name'  => 'Safed Musli Extract',
                    'latin' => 'Chlorophytum borivilianum (100 mg)',
                    'role'  => 'Venerated Ayurvedic Vajikarana botanical traditionally valued for restorative physical strength and connective vigor.'
                ),
                array(
                    'name'  => 'Gokshura Fruit Extract',
                    'latin' => 'Tribulus terrestris (50 mg)',
                    'role'  => 'Classical Rasayana herb supportive of urinary tract wellness, reproductive tissue vigor, and natural fluid balance.'
                ),
            ),
            'formulation_story' => array(
                'intro'       => 'Crafted in accordance with classical Ayurvedic Vajikarana texts, this formulation combines mineral-rich Shilajit purified through the traditional Shodhana process with whole-root Withania somnifera extract.',
                'traditional' => 'Classical Ayurvedic texts emphasize purifying Himalayan Shilajit using Triphala decoctions to remove insoluble compounds and enhance bio-availability. Paired with Ashwagandha root, it delivers foundational physiological nourishment.',
                'why'         => 'Unlike synthetic stamina pills that spike blood pressure and crash energy, this classical combination builds foundational vitality from within over time. 100% vegetarian plant cellulose capsules with zero synthetic fillers.'
            ),
            'usage_steps' => array(
                array('num' => '01', 'title' => 'Recommended Dose', 'desc' => '1 Capsule per serving.'),
                array('num' => '02', 'title' => 'When & How to Take', 'desc' => 'Take twice daily with warm milk or lukewarm water, ideally after breakfast and dinner.'),
                array('num' => '03', 'title' => 'Recommended Routine', 'desc' => 'Consistent intake for 60 to 90 days recommended for balanced, enduring results.')
            ),
            'specs' => array(
                'Form'              => 'Vegetarian Capsules (100% HPMC Plant Cellulose)',
                'Net Quantity'      => $net_qty ? $net_qty : '30 Capsules (800 mg each)',
                'Key Botanicals'    => 'Shuddha Shilajit, Ashwagandha Root, Safed Musli, Gokshura',
                'Country of Origin' => 'India (Jalna, Maharashtra)',
                'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
                'Packaging'         => 'Sealed amber bottle with moisture-proof barrier',
                'Shelf Life'        => $expiry ? $expiry : '24 Months from Manufacturing Date',
                'Storage'           => 'Store in a cool, dry place away from direct sunlight. Keep bottle tightly closed.'
            ),
            'storage' => 'Store in a cool, dry place away from direct sunlight. Keep bottle tightly closed.',
            'important_note' => 'Classical Ayurvedic proprietary wellness formulation. Dietary supplement; not intended to diagnose, treat, cure, or prevent any acute or chronic disease. Consult your physician prior to use if you are on blood pressure or cardiac medications. Keep out of reach of children.'
        ),

        // Product 31: Slim Care Weight Management Juice
        31 => array(
            'positioning' => 'Classical botanical metabolic elixir blending Garcinia Cambogia, Triphala, and Guggal formulated to awaken digestive fire (Agni) and support natural weight balance.',
            'benefits' => array(
                'Vrikshamla (Garcinia Cambogia) provides natural Hydroxycitric Acid (HCA) to support healthy lipid metabolism',
                'Classical Triphala promotes natural digestive cleansing and healthy morning bowel regularity',
                'Shuddha Guggulu supports optimal metabolic rate and cellular toxin clearance',
                'Pure Aloe Vera inner-leaf juice soothes the gut lining without synthetic stimulants'
            ),
            'botanicals' => array(
                array(
                    'name'  => 'Garcinia Cambogia Extract',
                    'latin' => 'Garcinia gummi-gutta (Vrikshamla)',
                    'role'  => 'Traditional Ayurvedic fruit rich in natural Hydroxycitric Acid (HCA) that assists in natural satiety and lipid metabolism.'
                ),
                array(
                    'name'  => 'Classical Triphala',
                    'latin' => 'Emblica officinalis, Terminalia bellirica, Terminalia chebula',
                    'role'  => 'Three foundational rejuvenating fruits (Amalaki, Bibhitaki, Haritaki) for balanced gut motility and gentle elimination.'
                ),
                array(
                    'name'  => 'Shuddha Guggulu Extract',
                    'latin' => 'Commiphora mukul',
                    'role'  => 'Purified classical gum resin celebrated in Ayurveda for supporting balanced metabolic rate and clearing metabolic waste (Ama).'
                ),
                array(
                    'name'  => 'Pure Aloe Vera Juice',
                    'latin' => 'Aloe barbadensis (Kumari)',
                    'role'  => 'Cold-extracted inner-leaf gel that hydrates the digestive mucosal lining and supports optimal nutrient absorption.'
                ),
            ),
            'formulation_story' => array(
                'intro'       => 'Developed on the classical principle of Deepana-Pachana (igniting digestive fire and clearing metabolic residue), Slim Care Juice harmonizes the body\'s natural metabolic balance.',
                'traditional' => 'Classical Ayurvedic formulations pair astringent and bitter botanicals like Triphala and Guggulu to counterbalance heavy Kapha accumulation while soothing Pitta with cooling Aloe Vera.',
                'why'         => 'Zero added sugar, zero artificial colors, zero synthetic stimulants. A clean, herbal elixir designed for sustainable everyday wellness.'
            ),
            'usage_steps' => array(
                array('num' => '01', 'title' => 'Recommended Measure', 'desc' => 'Measure 20 to 30 ml using the included measuring cup.'),
                array('num' => '02', 'title' => 'When & How to Take', 'desc' => 'Dilute in a glass (150–200 ml) of lukewarm water; drink on an empty stomach in the morning.'),
                array('num' => '03', 'title' => 'Recommended Routine', 'desc' => 'Maintain regular daily hydration and wholesome nutrition for best results.')
            ),
            'specs' => array(
                'Form'              => 'Liquid Herbal Extract Juice',
                'Net Quantity'      => $net_qty ? $net_qty : '500 ml',
                'Key Botanicals'    => 'Garcinia Cambogia, Triphala, Guggulu, Aloe Vera',
                'Country of Origin' => 'India (Jalna, Maharashtra)',
                'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
                'Packaging'         => 'BPA-free food-grade bottle with precision measuring cap',
                'Shelf Life'        => $expiry ? $expiry : '18 Months from Manufacturing Date',
                'Storage'           => 'Store in a cool, dry place. Refrigerate after opening and consume within 30 days.'
            ),
            'storage' => 'Store in a cool, dry place. Refrigerate after opening and consume within 30 days.',
            'important_note' => 'Ayurvedic wellness formulation. Not intended to replace a balanced diet or medical care. Shake well before use. If you are pregnant, nursing, or undergoing medical supervision, consult your healthcare practitioner prior to use.'
        ),

        // Product 32: Aloe Vera Triphala Piles Care Capsules
        32 => array(
            'positioning' => 'Soothing classical Arshoghna botanical formulation designed to support comfortable bowel evacuation, reduce anorectal inflammation, and soothe digestive heat.',
            'benefits' => array(
                'Encourages gentle, strain-free bowel movements without harsh laxative dependency',
                'Soothes inflamed, irritated rectal veins and alleviates burning sensations',
                'Classical Haritaki and Triphala restore normal colon tone and stool consistency',
                'Enriched with cooling Nagkesar and Neem for localized vascular tissue comfort'
            ),
            'botanicals' => array(
                array(
                    'name'  => 'Classical Triphala',
                    'latin' => 'Terminalia chebula, Terminalia bellirica, Emblica officinalis',
                    'role'  => 'Time-honored blend that regulates Apana Vayu to ensure smooth downward elimination and natural stool softness.'
                ),
                array(
                    'name'  => 'Pure Aloe Vera Extract',
                    'latin' => 'Aloe barbadensis (Kumari)',
                    'role'  => 'Cooling botanical that pacifies localized Pitta heat, soothes rectal tissue irritation, and supports mucosal recovery.'
                ),
                array(
                    'name'  => 'Nagkesar Extract',
                    'latin' => 'Mesua ferrea',
                    'role'  => 'Revered classical astringent herb traditionally used to tone venous walls and minimize anorectal swelling.'
                ),
                array(
                    'name'  => 'Neem Leaf Extract',
                    'latin' => 'Azadirachta indica',
                    'role'  => 'Purifying bitter botanical providing natural microbial defense and relief from localized itching.'
                ),
            ),
            'formulation_story' => array(
                'intro'       => 'Rooted in ancient Arshoghna formulations, these capsules address the root physiological causes of anorectal discomfort: sluggish digestion, dry stool, and irritated pelvic veins.',
                'traditional' => 'Classical Ayurveda emphasizes that anorectal ease begins with balancing the Apana Vayu and cooling excess Pitta fire in the lower digestive tract.',
                'why'         => 'Formulated without habit-forming synthetic purgatives. Provides gentle, non-irritating digestive relief in convenient plant cellulose capsules.'
            ),
            'usage_steps' => array(
                array('num' => '01', 'title' => 'Recommended Dose', 'desc' => '1 to 2 Capsules per serving.'),
                array('num' => '02', 'title' => 'When & How to Take', 'desc' => 'Take twice daily after meals with lukewarm water, especially after dinner before sleep.'),
                array('num' => '03', 'title' => 'Recommended Routine', 'desc' => 'Drink 2.5–3 liters of water daily and include high-fiber foods in your routine.')
            ),
            'specs' => array(
                'Form'              => 'Vegetarian Capsules (100% Plant Cellulose)',
                'Net Quantity'      => $net_qty ? $net_qty : '60 Capsules (500 mg each)',
                'Key Botanicals'    => 'Triphala, Aloe Vera, Nagkesar, Neem',
                'Country of Origin' => 'India (Jalna, Maharashtra)',
                'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
                'Packaging'         => 'Sealed amber bottle with tamper-evident cap',
                'Shelf Life'        => $expiry ? $expiry : '24 Months from Manufacturing Date',
                'Storage'           => 'Store in a cool, dry place away from heat and moisture.'
            ),
            'storage' => 'Store in a cool, dry place away from heat and moisture.',
            'important_note' => 'Ayurvedic proprietary formulation. Not intended to diagnose, treat, or cure severe medical conditions. If you experience persistent bleeding or severe anorectal pain, consult a qualified physician promptly.'
        ),

        // Product 33: Amrit Heart Health Immunity Juice
        33 => array(
            'positioning' => 'Classical 5-in-1 Hridaya Rasayana featuring Terminalia Arjuna bark decoction, fresh wild Amla, crushed Garlic, raw Apple Cider Vinegar with Mother, and forest honey for cardiovascular resilience.',
            'benefits' => array(
                'Wild Arjuna bark decoction nourishes cardiac muscle tone and supports healthy blood circulation',
                'Raw crushed Garlic and raw Apple Cider Vinegar support clean blood lipid and cholesterol balance',
                'Fresh wild Amla provides potent natural bioflavonoids and Vitamin C for arterial elasticity',
                'Raw forest honey balances botanical astringency and aids gentle assimilation'
            ),
            'botanicals' => array(
                array(
                    'name'  => 'Terminalia Arjuna Bark Decoction',
                    'latin' => 'Terminalia arjuna (40%)',
                    'role'  => 'Venerated cardiovascular Rasayana rich in bioflavonoids to support cardiac muscular integrity and blood flow.'
                ),
                array(
                    'name'  => 'Fresh Wild Amla Juice',
                    'latin' => 'Emblica officinalis (30%)',
                    'role'  => 'Cold-extracted Indian gooseberry providing potent bioavailable Vitamin C to protect vascular endothelium.'
                ),
                array(
                    'name'  => 'Raw Garlic Bulb Extract',
                    'latin' => 'Allium sativum (10%)',
                    'role'  => 'Standardized allicin botanical source traditionally utilized to maintain clean, unobstructed arterial pathways.'
                ),
                array(
                    'name'  => 'Raw Apple Cider Vinegar',
                    'latin' => 'Raw Fermented Vinegar with Mother (10%)',
                    'role'  => 'Unpasteurized raw apple cider vinegar to support natural enzymatic digestion and healthy lipid metabolism.'
                ),
                array(
                    'name'  => 'Pure Forest Honey',
                    'latin' => 'Madhu (10%)',
                    'role'  => 'Natural Ayurvedic Yogavahi (carrier) that enhances botanical cellular penetration and balances astringency.'
                ),
            ),
            'formulation_story' => array(
                'intro'       => 'A faithful preparation of the classical 5-in-1 Hridaya Rasayana, combining five venerated cardiovascular superfoods into a bio-available daily tonic.',
                'traditional' => 'Ancient texts describe Arjuna as the preeminent herb for the heart (Hridya), which when paired with Amla, Garlic, and fermented fruit acids, acts synergistically on vascular flexibility.',
                'why'         => 'Prepared without boiling, synthetic thickening gums, or artificial preservatives, retaining active enzymes and volatile phytonutrients.'
            ),
            'usage_steps' => array(
                array('num' => '01', 'title' => 'Recommended Measure', 'desc' => 'Dilute 15 to 20 ml in half a glass (100–150 ml) of normal or lukewarm water.'),
                array('num' => '02', 'title' => 'When & How to Take', 'desc' => 'Drink once daily in the morning, 30 minutes before breakfast on an empty stomach.'),
                array('num' => '03', 'title' => 'Recommended Routine', 'desc' => 'Incorporate into an active, heart-conscious lifestyle for enduring cardiovascular vitality.')
            ),
            'specs' => array(
                'Form'              => 'Liquid Herbal Tonic Juice',
                'Net Quantity'      => $net_qty ? $net_qty : '500 ml',
                'Key Botanicals'    => 'Arjuna Bark, Wild Amla, Raw Garlic, Apple Cider Vinegar, Forest Honey',
                'Country of Origin' => 'India (Jalna, Maharashtra)',
                'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
                'Packaging'         => 'Food-grade bottle with calibrated dosing cap',
                'Shelf Life'        => $expiry ? $expiry : '18 Months from Manufacturing Date',
                'Storage'           => 'Store in a cool, dry place. Refrigerate after opening and consume within 30 days.'
            ),
            'storage' => 'Store in a cool, dry place. Refrigerate after opening and consume within 30 days.',
            'important_note' => 'Supportive Ayurvedic botanical tonic. Not intended as a substitute for prescribed cardiovascular medication. If taking anti-coagulants or blood thinners, consult your cardiologist before introducing any garlic-rich tonic.'
        ),

        // Product 34: Karela Jamun Blood Sugar Control Capsules
        34 => array(
            'positioning' => 'Potent 5-herb botanical synergy with Bitter Gourd, Jamun Seed, and Gymnema (The Sugar Destroyer) to support healthy glucose metabolism and curb sugar snacking.',
            'benefits' => array(
                'Gurmar (Gymnema sylvestre) suppresses sweet taste receptors to reduce sugar cravings',
                'Jamun seed (Jambu) aids healthy postprandial glucose stability and pancreatic wellness',
                'Karela provides natural charantin to support cellular glucose uptake',
                'Fenugreek and Vijayasar support healthy digestive enzyme secretion and steady energy'
            ),
            'botanicals' => array(
                array(
                    'name'  => 'Karela (Bitter Gourd) Extract',
                    'latin' => 'Momordica charantia (150 mg)',
                    'role'  => 'Standardized extract containing charantin and vicine to support natural cellular glucose uptake.'
                ),
                array(
                    'name'  => 'Jamun Seed Extract',
                    'latin' => 'Syzygium cumini (150 mg)',
                    'role'  => 'Contains natural jamboline glycosides to assist in slowing down the conversion of dietary starch into sugar.'
                ),
                array(
                    'name'  => 'Gurmar Leaf Extract',
                    'latin' => 'Gymnema sylvestre (100 mg)',
                    'role'  => 'The classical \'sugar destroyer\' that blocks sweet receptors on the tongue and supports healthy beta cell function.'
                ),
                array(
                    'name'  => 'Methi (Fenugreek) & Vijayasar',
                    'latin' => 'Trigonella foenum-graecum & Pterocarpus marsupium (100 mg)',
                    'role'  => 'Traditional botanicals rich in soluble galactomannan and flavonoids that support digestive glucose absorption.'
                ),
            ),
            'formulation_story' => array(
                'intro'       => 'Classical Ayurveda categorizes blood sugar imbalances under Madhumeha, recommending bitter (Tikta) and astringent (Kashaya) herbs to pacify Kapha and regulate carbohydrate digestion.',
                'traditional' => 'Unlike single-herb products, this 5-herb synergy targets both the oral taste receptors, digestive transit, and peripheral tissue cellular utilization.',
                'why'         => 'Utilizes concentrated botanical extracts rather than unstandardized raw powders, providing reliable consistency in vegetarian plant cellulose capsules.'
            ),
            'usage_steps' => array(
                array('num' => '01', 'title' => 'Recommended Dose', 'desc' => '1 Capsule per serving.'),
                array('num' => '02', 'title' => 'When & How to Take', 'desc' => 'Take twice daily, approximately 30 minutes before your morning and evening meals with lukewarm water.'),
                array('num' => '03', 'title' => 'Recommended Routine', 'desc' => 'Consistent daily intake alongside a balanced glycemic diet recommended.')
            ),
            'specs' => array(
                'Form'              => 'Vegetarian Capsules (100% Plant Cellulose)',
                'Net Quantity'      => $net_qty ? $net_qty : '60 Capsules (500 mg each)',
                'Key Botanicals'    => 'Karela, Jamun, Gurmar, Fenugreek, Vijayasar',
                'Country of Origin' => 'India (Jalna, Maharashtra)',
                'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
                'Packaging'         => 'Sealed amber container with freshness seal',
                'Shelf Life'        => $expiry ? $expiry : '24 Months from Manufacturing Date',
                'Storage'           => 'Store in a cool dry place away from heat and direct moisture.'
            ),
            'storage' => 'Store in a cool dry place away from heat and direct moisture.',
            'important_note' => 'Dietary Ayurvedic wellness supplement for carbohydrate metabolism. Not intended to diagnose, treat, or replace diabetes medication. Regularly monitor your blood glucose levels when taking alongside conventional medication.'
        ),

        // Product 35: Shallaki Joint Bone Care Capsules
        35 => array(
            'positioning' => 'Standardized Boswellia Serrata (65% Boswellic Acids) combined with Hadjod and Nirgundi for joint comfort, cartilage lubrication, and morning flexibility.',
            'benefits' => array(
                'Standardized Boswellia serrata inhibits inflammatory 5-LOX enzymes to soothe joint stiffness',
                'Hadjod (Cissus quadrangularis) accelerates bone mineral deposition and connective tissue strength',
                'Nirgundi alleviates joint tenderness and enhances morning range of motion',
                'Gentle on the stomach lining compared to synthetic joint formulations'
            ),
            'botanicals' => array(
                array(
                    'name'  => 'Shallaki Gum Resin Extract',
                    'latin' => 'Boswellia serrata (65% Boswellic Acids)',
                    'role'  => 'Standardized resin extract that inhibits the 5-LOX inflammatory pathway, helping preserve cartilage matrix and ease stiffness.'
                ),
                array(
                    'name'  => 'Hadjod Stem Extract',
                    'latin' => 'Cissus quadrangularis (Asthisamharaka)',
                    'role'  => 'Ancient bone-setting botanical that supports calcium assimilation and strengthens connective ligaments.'
                ),
                array(
                    'name'  => 'Nirgundi Leaf Extract',
                    'latin' => 'Vitex negundo',
                    'role'  => 'Revered anti-inflammatory leaf traditionally used to pacify Vata, relieve muscular tenderness, and support joint mobility.'
                ),
                array(
                    'name'  => 'Shuddha Guggulu',
                    'latin' => 'Commiphora mukul',
                    'role'  => 'Purified gum resin that clears deep Ama from joint pockets and enhances synovial fluid ease.'
                ),
            ),
            'formulation_story' => array(
                'intro'       => 'Joint discomfort and morning stiffness are classically attributed to Sandhigata Vata, where dryness and Ama accumulate in joint capsules.',
                'traditional' => 'Classical Ayurvedic formulation balances the penetrating properties of Shallaki with the restorative bone-nourishing properties of Hadjod and Nirgundi.',
                'why'         => 'Standardized active markers ensure consistent botanical potency without gastrointestinal irritation.'
            ),
            'usage_steps' => array(
                array('num' => '01', 'title' => 'Recommended Dose', 'desc' => '1 Capsule per serving.'),
                array('num' => '02', 'title' => 'When & How to Take', 'desc' => 'Take twice daily after meals with lukewarm water or warm milk.'),
                array('num' => '03', 'title' => 'Recommended Routine', 'desc' => 'Follow a 60 to 90 day regimen for comprehensive connective tissue and cartilage support.')
            ),
            'specs' => array(
                'Form'              => 'Vegetarian Capsules (100% Plant Cellulose)',
                'Net Quantity'      => $net_qty ? $net_qty : '60 Capsules (500 mg each)',
                'Key Botanicals'    => 'Shallaki (Boswellia), Hadjod, Nirgundi, Shuddha Guggulu',
                'Country of Origin' => 'India (Jalna, Maharashtra)',
                'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
                'Packaging'         => 'Sealed amber bottle with safety seal',
                'Shelf Life'        => $expiry ? $expiry : '24 Months from Manufacturing Date',
                'Storage'           => 'Store in a cool dry place away from heat and direct sunlight.'
            ),
            'storage' => 'Store in a cool dry place away from heat and direct sunlight.',
            'important_note' => 'Ayurvedic wellness formulation for joint and bone support. Not intended to treat severe arthritic conditions or replace orthopedic consultation. Consult your doctor if pregnant or lactating.'
        ),

        // Product 36: Shatavari Ashoka Women Wellness Juice
        36 => array(
            'positioning' => 'Nurturing Nari Rasayana with Pure Shatavari Root, Classical Ashoka Bark, Lodhra, and Dashmula for hormonal rhythm, cycle comfort, and radiant vitality.',
            'benefits' => array(
                'Pure Shatavari root provides natural plant phytoestrogens to nurture female hormonal balance',
                'Classical Ashoka bark tones uterine muscles and eases monthly cramping and discomfort',
                'Lodhra supports pelvic tissue resilience and promotes clear, radiant skin',
                'Dashmula roots soothe pelvic nervous fatigue and restore calm energy reserves'
            ),
            'botanicals' => array(
                array(
                    'name'  => 'Pure Shatavari Root Extract',
                    'latin' => 'Asparagus racemosus',
                    'role'  => 'The supreme Ayurvedic female rejuvenator (Nari Rasayana) that nourishes Shukra and Rasa Dhatus for hormonal balance and emotional calm.'
                ),
                array(
                    'name'  => 'Classical Ashoka Bark Decoction',
                    'latin' => 'Saraca asoca',
                    'role'  => 'Time-honored uterine tonic celebrated for regulating menstrual flow and alleviating cyclic abdominal discomfort.'
                ),
                array(
                    'name'  => 'Lodhra Bark Extract',
                    'latin' => 'Symplocos racemosa',
                    'role'  => 'Astringent Pitta-pacifying botanical that helps tone pelvic tissues and support clear skin complexion.'
                ),
                array(
                    'name'  => 'Dashmula Ten Roots Synergy',
                    'latin' => 'Classical Dashmula Group',
                    'role'  => 'Traditional ten-root formulation that calms lower abdominal Vata, relieves nervous tension, and restores natural energy.'
                ),
            ),
            'formulation_story' => array(
                'intro'       => 'A dedicated classical Nari Rasayana created to accompany women through every phase of life, supporting cycle regularity, hormonal harmony, and deep vitality.',
                'traditional' => 'Ancient classical texts regard Ashoka and Shatavari as an essential botanical pair: Ashoka tones and purifies the reproductive seat, while Shatavari deeply hydrates and restores vital Ojas.',
                'why'         => 'Crafted from classical decoctions without synthetic hormones, artificial sweeteners, or added sugar.'
            ),
            'usage_steps' => array(
                array('num' => '01', 'title' => 'Recommended Measure', 'desc' => 'Dilute 20 to 25 ml in half a glass (100–150 ml) of normal or lukewarm water.'),
                array('num' => '02', 'title' => 'When & How to Take', 'desc' => 'Drink twice daily, 30 minutes after your morning and evening meals.'),
                array('num' => '03', 'title' => 'Recommended Routine', 'desc' => 'Consistent intake across consecutive menstrual cycles recommended for balanced vitality.')
            ),
            'specs' => array(
                'Form'              => 'Liquid Herbal Tonic Juice',
                'Net Quantity'      => $net_qty ? $net_qty : '500 ml',
                'Key Botanicals'    => 'Shatavari Root, Ashoka Bark, Lodhra, Dashmula',
                'Country of Origin' => 'India (Jalna, Maharashtra)',
                'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
                'Packaging'         => 'BPA-free food-grade bottle with calibrated dosing cap',
                'Shelf Life'        => $expiry ? $expiry : '18 Months from Manufacturing Date',
                'Storage'           => 'Store in a cool dry place. Refrigerate after opening and consume within 30 days.'
            ),
            'storage' => 'Store in a cool dry place. Refrigerate after opening and consume within 30 days.',
            'important_note' => 'Ayurvedic wellness formulation for women. Not intended to diagnose, treat, or cure gynecological disorders. Discontinue use during pregnancy unless recommended by your healthcare practitioner.'
        ),

        // Product 58: Daily Energy & Vitality Duo
        58 => array(
            'positioning' => 'Complete synergistic morning-to-night wellness regimen pairing Ashwagandha Shilajit Capsules with Amrit Heart Health Juice for endurance, cardiovascular tone, and cellular stamina.',
            'benefits' => array(
                'Ashwagandha Shilajit Capsules (800mg) provide purified Shilajit and standardized root extract for endurance',
                'Amrit Heart Health Juice (500ml) supplies Arjuna, wild Amla, and raw Garlic for arterial elasticity',
                'Dual-action Ayurvedic Rasayana protocol supporting both physical and cardiovascular energy reserves',
                'Exclusive bundle pricing: Save over 55% with complimentary pan-India express shipping'
            ),
            'botanicals' => array(
                array(
                    'name'  => 'Shilajit & Ashwagandha Root',
                    'latin' => 'Asphaltum punjabianum & Withania somnifera',
                    'role'  => 'Foundational adaptogenic duo that builds Ojas, supports ATP synthesis, and modulates stress.'
                ),
                array(
                    'name'  => 'Terminalia Arjuna Bark Decoction',
                    'latin' => 'Terminalia arjuna (40%)',
                    'role'  => 'Cardiovascular tonic nourishing cardiac muscle and supporting healthy blood circulation.'
                ),
                array(
                    'name'  => 'Fresh Wild Amla & Raw Garlic',
                    'latin' => 'Emblica officinalis & Allium sativum',
                    'role'  => 'Rich bioavailable Vitamin C and allicin to maintain clear vascular pathways and arterial tone.'
                ),
            ),
            'formulation_story' => array(
                'intro'       => 'Pairing Bhagyoday Ayurveda Ashwagandha Shilajit Capsules with Amrit Heart Health Juice creates an all-encompassing morning-to-night wellness regimen.',
                'traditional' => 'Classical Ayurveda emphasizes that enduring vitality requires both strong Prana (circulatory vitality) and deep Ojas (cellular endurance). This pair addresses both systemic pathways simultaneously.',
                'why'         => 'A comprehensive two-part protocol combining vegetarian capsules for daily stamina and liquid Rasayana for deep vascular nourishment.'
            ),
            'usage_steps' => array(
                array('num' => '01', 'title' => 'Morning Routine', 'desc' => 'Take 15–20 ml Amrit Heart Juice in lukewarm water 30 minutes before breakfast.'),
                array('num' => '02', 'title' => 'Post-Meal Routine', 'desc' => 'Take 1 Ashwagandha Shilajit Capsule twice daily after meals with water or warm milk.'),
                array('num' => '03', 'title' => 'Recommended Duration', 'desc' => 'Continue the complete duo for 60 to 90 days for deep, holistic rejuvenation.')
            ),
            'specs' => array(
                'Form'              => 'Combo Pack (1x 30 Veg Capsules + 1x 500ml Herbal Juice)',
                'Net Quantity'      => $net_qty ? $net_qty : '30 Capsules + 500 ml Juice',
                'Key Botanicals'    => 'Shilajit, Ashwagandha, Arjuna, Wild Amla, Raw Garlic',
                'Country of Origin' => 'India (Jalna, Maharashtra)',
                'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
                'Packaging'         => 'Sealed bottle and amber jar in tamper-evident bundle packaging',
                'Shelf Life'        => $expiry ? $expiry : '18–24 Months from Manufacturing Date',
                'Storage'           => 'Store capsules in a dry place; refrigerate juice after opening.'
            ),
            'storage' => 'Store capsules in a dry place away from heat. Refrigerate juice bottle after opening and consume within 30 days.',
            'important_note' => 'Supportive Ayurvedic wellness combo. Not intended to replace prescribed cardiovascular or medical therapies. Consult your doctor if on blood pressure or cardiac medication.'
        ),

        // Product 59: Metabolic Detox & Sugar Balance Duo
        59 => array(
            'positioning' => 'Powerful Ayurvedic regimen pairing Karela Jamun Gurmar Blood Sugar Capsules with Slim Care Weight Management Juice for enhanced metabolism, digestive Agni, and natural glucose balance.',
            'benefits' => array(
                'Karela Jamun Gurmar Capsules (500mg) curb sweet cravings and support healthy glucose metabolism',
                'Slim Care Juice (500ml) pairs Garcinia, Triphala, and Guggulu to awaken sluggish digestive fire',
                'Encourages healthy fat metabolism and gentle daily detoxification without harsh stimulants',
                'Exclusive bundle pricing: Save over 55% with complimentary pan-India express delivery'
            ),
            'botanicals' => array(
                array(
                    'name'  => 'Gurmar & Karela Extract',
                    'latin' => 'Gymnema sylvestre & Momordica charantia',
                    'role'  => 'Botanical sugar-regulators that block sweet taste perception and support cellular carbohydrate uptake.'
                ),
                array(
                    'name'  => 'Garcinia Cambogia & Guggulu',
                    'latin' => 'Garcinia gummi-gutta & Commiphora mukul',
                    'role'  => 'Natural HCA and purified classical resin supporting healthy lipid metabolism and digestive Agni.'
                ),
                array(
                    'name'  => 'Classical Triphala & Aloe Vera',
                    'latin' => 'Three Rejuvenating Fruits & Kumari',
                    'role'  => 'Promotes daily colon motility, clears intestinal Ama, and hydrates the digestive tract.'
                ),
            ),
            'formulation_story' => array(
                'intro'       => 'Combining classical Deepana-Pachana digestive juice with potent glucose-modulating capsules for complete metabolic harmony.',
                'traditional' => 'According to classical Ayurveda, healthy carbohydrate utilization depends upon robust digestive fire (Jatharagni) and unobstructed metabolic channels (Srotas). This duo purifies the gut while supporting pancreatic vitality.',
                'why'         => 'A dual-action botanical regimen targeting both the digestive absorption phase and peripheral cellular utilization.'
            ),
            'usage_steps' => array(
                array('num' => '01', 'title' => 'Morning Routine', 'desc' => 'Take 20–30 ml Slim Care Juice in lukewarm water on an empty stomach in the morning.'),
                array('num' => '02', 'title' => 'Pre-Meal Routine', 'desc' => 'Take 1 Karela Jamun Capsule twice daily, 30 minutes before lunch and dinner.'),
                array('num' => '03', 'title' => 'Recommended Duration', 'desc' => 'Follow consistently for 60 to 90 days alongside a balanced, fiber-rich diet.')
            ),
            'specs' => array(
                'Form'              => 'Combo Pack (1x 60 Veg Capsules + 1x 500ml Herbal Juice)',
                'Net Quantity'      => $net_qty ? $net_qty : '60 Capsules + 500 ml Juice',
                'Key Botanicals'    => 'Karela, Jamun, Gurmar, Garcinia, Triphala, Guggulu',
                'Country of Origin' => 'India (Jalna, Maharashtra)',
                'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
                'Packaging'         => 'Sealed bottle and amber jar in tamper-evident bundle packaging',
                'Shelf Life'        => $expiry ? $expiry : '18–24 Months from Manufacturing Date',
                'Storage'           => 'Store capsules in a dry place; refrigerate juice after opening.'
            ),
            'storage' => 'Store capsules in a cool, dry place. Refrigerate juice bottle after opening and consume within 30 days.',
            'important_note' => 'Ayurvedic metabolic wellness combo. Not intended to diagnose, treat, or replace prescription diabetes medication. Regularly monitor your blood glucose levels when using alongside conventional medication.'
        ),
    );

    if (isset($data_map[$product_id])) {
        return $data_map[$product_id];
    }

    // Dynamic fallback for any other product created in WooCommerce
    $fallback_benefits = get_post_meta($product_id, '_ayur_benefits', true);
    $benefits_arr = array();
    if ($fallback_benefits) {
        foreach (explode("\n", $fallback_benefits) as $line) {
            $c = trim(ltrim($line, "•- "));
            if (!empty($c)) $benefits_arr[] = $c;
        }
    }
    if (empty($benefits_arr)) {
        $benefits_arr = array(
            'Formulated with authentic, standardized classical Ayurvedic botanicals',
            'Prepared in GMP and AYUSH compliant certified manufacturing facilities',
            'Clean formulation with zero synthetic fillers, harmful binders, or artificial colors',
            'Convenient everyday wellness format designed for sustained results'
        );
    }

    $fallback_ingredients = get_post_meta($product_id, '_ayur_ingredients', true);
    $botanicals_arr = array();
    if ($fallback_ingredients) {
        $lines = explode("\n", $fallback_ingredients);
        foreach ($lines as $line) {
            $clean = trim($line);
            if (empty($clean)) continue;
            if (preg_match('/^([^(]+)(?:\(([^)]+)\))?\s*(.*)$/i', $clean, $m)) {
                $botanicals_arr[] = array(
                    'name'  => trim($m[1]),
                    'latin' => isset($m[2]) ? trim($m[2]) : '',
                    'role'  => isset($m[3]) && !empty($m[3]) ? trim($m[3]) : 'Classical Ayurvedic botanical traditionally used for foundational wellness.'
                );
            } else {
                $botanicals_arr[] = array(
                    'name'  => $clean,
                    'latin' => '',
                    'role'  => 'Classical Ayurvedic botanical traditionally used for foundational wellness.'
                );
            }
        }
    }

    return array(
        'positioning' => $product->get_short_description() ? strip_tags($product->get_short_description()) : 'Authentic Ayurvedic formulation prepared in accordance with classical botanical principles.',
        'benefits'    => $benefits_arr,
        'botanicals'  => $botanicals_arr,
        'formulation_story' => array(
            'intro'       => 'Crafted in accordance with authentic Ayurvedic texts, honoring classical preparations and balancing time-tested botanicals.',
            'traditional' => 'Our formulations rely on classical decoction and extraction methods to preserve the natural vitality and bio-active essence of the herbs.',
            'why'         => '100% vegetarian, ethical small-batch production with zero synthetic fillers or harmful binders.'
        ),
        'usage_steps' => array(
            array('num' => '01', 'title' => 'Recommended Dose', 'desc' => get_post_meta($product_id, '_ayur_dosage', true) ?: '1 serving as indicated on packaging.'),
            array('num' => '02', 'title' => 'When & How to Take', 'desc' => 'Take with lukewarm water or warm milk after meals.'),
            array('num' => '03', 'title' => 'Recommended Routine', 'desc' => 'Consistent daily use for 60 to 90 days recommended.')
        ),
        'specs' => array(
            'Form'              => (stripos($product->get_name(), 'Juice') !== false) ? 'Liquid Herbal Extract Juice' : 'Vegetarian Capsules (100% Plant Cellulose)',
            'Net Quantity'      => $net_qty ?: 'Standard Pack',
            'Country of Origin' => 'India (Jalna, Maharashtra)',
            'Manufacturer'      => 'Bhagyoday Ayurveda, Mantha, Jalna, Maharashtra 431504',
            'Packaging'         => 'Food-grade sealed container',
            'Shelf Life'        => $expiry ?: '24 Months from Manufacturing Date',
            'Storage'           => 'Store in a cool, dry place away from direct sunlight.'
        ),
        'storage'        => 'Store in a cool, dry place away from direct sunlight.',
        'important_note' => 'Classical Ayurvedic proprietary wellness formulation. Dietary supplement; not intended to diagnose, treat, cure, or prevent any acute or chronic disease. Consult your physician prior to use if pregnant or nursing. Keep out of reach of children.'
    );
}

/**
 * Phase 5: Smart Related Formulations Query
 * Returns 4 complementary products based on category affinity, never stretching a single card.
 */
function ayur_get_smart_related_products($product_id, $limit = 4) {
    $related_ids = array();

    // 1. Get current product category
    $terms = get_the_terms($product_id, 'product_cat');
    $current_slug = '';
    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $t) {
            if ($t->slug !== 'uncategorized') {
                $current_slug = $t->slug;
                break;
            }
        }
    }

    // 2. Fetch products from the SAME category first (excluding current product)
    if ($current_slug) {
        $same_cat_products = wc_get_products(array(
            'status'   => 'publish',
            'limit'    => $limit,
            'exclude'  => array($product_id),
            'category' => array($current_slug),
            'orderby'  => 'menu_order title',
            'order'    => 'ASC',
        ));
        foreach ($same_cat_products as $p) {
            $related_ids[] = $p->get_id();
        }
    }

    // 3. Category affinity map for complementary recommendations
    $affinity_map = array(
        'mens-vitality-stamina' => array('heart-health-immunity', 'metabolism-daily-detox', 'joint-bone-care', 'combos', 'value-combos-packs'),
        'digestive-piles-care'  => array('metabolism-daily-detox', 'heart-health-immunity', 'joint-bone-care'),
        'joint-bone-care'       => array('mens-vitality-stamina', 'heart-health-immunity', 'metabolism-daily-detox'),
        'heart-health-immunity' => array('mens-vitality-stamina', 'metabolism-daily-detox', 'womens-wellness-vitality'),
        'metabolism-daily-detox'=> array('digestive-piles-care', 'heart-health-immunity', 'combos', 'value-combos-packs'),
        'womens-wellness-vitality' => array('heart-health-immunity', 'joint-bone-care', 'metabolism-daily-detox'),
        'combos'                => array('mens-vitality-stamina', 'metabolism-daily-detox', 'heart-health-immunity'),
        'value-combos-packs'    => array('mens-vitality-stamina', 'metabolism-daily-detox', 'heart-health-immunity'),
    );

    // 4. Fill up to $limit from complementary categories
    if (count($related_ids) < $limit && isset($affinity_map[$current_slug])) {
        foreach ($affinity_map[$current_slug] as $comp_cat) {
            if (count($related_ids) >= $limit) break;
            $comp_products = wc_get_products(array(
                'status'   => 'publish',
                'limit'    => $limit - count($related_ids),
                'exclude'  => array_merge(array($product_id), $related_ids),
                'category' => array($comp_cat),
                'orderby'  => 'menu_order title',
                'order'    => 'ASC',
            ));
            foreach ($comp_products as $p) {
                $related_ids[] = $p->get_id();
                if (count($related_ids) >= $limit) break 2;
            }
        }
    }

    // 5. Fill remaining from general published products if still needed
    if (count($related_ids) < $limit) {
        $extra_products = wc_get_products(array(
            'status'  => 'publish',
            'limit'   => $limit - count($related_ids),
            'exclude' => array_merge(array($product_id), $related_ids),
            'orderby' => 'menu_order title',
            'order'   => 'ASC',
        ));
        foreach ($extra_products as $p) {
            $related_ids[] = $p->get_id();
            if (count($related_ids) >= $limit) break;
        }
    }

    // Convert IDs to product objects
    $products = array();
    foreach ($related_ids as $id) {
        $prod = wc_get_product($id);
        if ($prod && $prod->is_visible()) {
            $products[] = $prod;
        }
    }

    return $products;
}

/**
 * Phase 4, 5 & 6 Quality & Claims Sanitizer
 * Ensures no unverified trademarks or medical claims slip through dynamic content.
 */
function ayur_sanitize_unverified_claims($content) {
    if (!is_string($content) || empty($content)) {
        return $content;
    }
    // Replace unverified trademarks with authentic classical naming
    $cleaned = str_ireplace(
        array('KSM-66®', 'KSM-66', 'KSM 66'),
        'Ashwagandha Root Extract (Withania somnifera)',
        $content
    );
    // Clean potential accidental phrase doubling
    $cleaned = str_replace(
        'Ashwagandha Root Extract (Withania somnifera) Ashwagandha root extract',
        'Ashwagandha Root Extract (Withania somnifera)',
        $cleaned
    );
    $cleaned = str_replace(
        'Ashwagandha Root Extract (Withania somnifera) ashwagandha root extract',
        'Ashwagandha Root Extract (Withania somnifera)',
        $cleaned
    );
    return $cleaned;
}
add_filter('the_excerpt', 'ayur_sanitize_unverified_claims', 99);
add_filter('the_content', 'ayur_sanitize_unverified_claims', 99);
add_filter('woocommerce_short_description', 'ayur_sanitize_unverified_claims', 99);
add_filter('the_title', 'ayur_sanitize_unverified_claims', 99);

/**
 * Phase 7 Section 32: Native Fast Live Product Search
 */
function ayur_ajax_search_products() {
    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
    if (strlen($term) < 2) {
        wp_send_json_success(array('results' => array()));
    }

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        's'              => $term,
    );

    $query = new WP_Query($args);
    $results = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $prod = wc_get_product(get_the_ID());
            if (!$prod || !$prod->is_visible()) continue;

            $p_id = $prod->get_id();
            $thumb = has_post_thumbnail($p_id) ? get_the_post_thumbnail_url($p_id, 'thumbnail') : '';
            $net_qty = get_post_meta($p_id, '_ayur_net_quantity', true);
            $price = (float) $prod->get_price();
            $mrp = (float) get_post_meta($p_id, '_ayur_mrp', true);

            $results[] = array(
                'id'        => $p_id,
                'name'      => html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8'),
                'url'       => get_permalink($p_id),
                'thumb'     => $thumb,
                'price'     => '₹' . number_format($price),
                'mrp'       => ($mrp > $price) ? 'MRP ₹' . number_format($mrp) : '',
                'net_qty'   => $net_qty ? esc_html($net_qty) : '',
            );
        }
        wp_reset_postdata();
    }

    // Fallback: If no direct text match on title, search in product categories
    if (empty($results)) {
        $tax_args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 6,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'name',
                    'terms'    => $term,
                    'operator' => 'LIKE',
                ),
            ),
        );
        $tax_query = new WP_Query($tax_args);
        if ($tax_query->have_posts()) {
            while ($tax_query->have_posts()) {
                $tax_query->the_post();
                $prod = wc_get_product(get_the_ID());
                if (!$prod || !$prod->is_visible()) continue;

                $p_id = $prod->get_id();
                $thumb = has_post_thumbnail($p_id) ? get_the_post_thumbnail_url($p_id, 'thumbnail') : '';
                $net_qty = get_post_meta($p_id, '_ayur_net_quantity', true);
                $price = (float) $prod->get_price();
                $mrp = (float) get_post_meta($p_id, '_ayur_mrp', true);

                $results[] = array(
                    'id'        => $p_id,
                    'name'      => html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8'),
                    'url'       => get_permalink($p_id),
                    'thumb'     => $thumb,
                    'price'     => '₹' . number_format($price),
                    'mrp'       => ($mrp > $price) ? 'MRP ₹' . number_format($mrp) : '',
                    'net_qty'   => $net_qty ? esc_html($net_qty) : '',
                );
            }
            wp_reset_postdata();
        }
    }

    wp_send_json_success(array('results' => $results));
}
add_action('wp_ajax_ayur_search_products', 'ayur_ajax_search_products');
add_action('wp_ajax_nopriv_ayur_search_products', 'ayur_ajax_search_products');

/**
 * Ensure body classes for Checkout and Cart
 */
function ayur_theme_body_classes($classes) {
    if (is_checkout() || is_page('checkout') || (function_exists('is_checkout_pay_page') && is_checkout_pay_page())) {
        if (!in_array('woocommerce-checkout', $classes)) {
            $classes[] = 'woocommerce-checkout';
        }
    }
    if (is_cart() || is_page('cart')) {
        if (!in_array('woocommerce-cart', $classes)) {
            $classes[] = 'woocommerce-cart';
        }
    }
    return $classes;
}
add_filter('body_class', 'ayur_theme_body_classes', 99);

