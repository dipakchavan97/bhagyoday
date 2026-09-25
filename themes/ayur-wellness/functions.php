<?php
/**
 * Ayur Wellness Theme Functions and Definitions
 * Premium Authentic Indian Ayurvedic D2C Architecture
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AYUR_THEME_VERSION', '2.0.0');

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
    $threshold = 999;
    $remaining = $threshold - $subtotal;
    $percent = min(100, max(0, round(($subtotal / $threshold) * 100)));

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

    <!-- Free Shipping Indicator -->
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
                <?php echo ayur_svg_icon('shopping-bag', '', 40, 1.4); ?>
            </div>
            <h4><?php _e('Your cart is empty', 'ayur-wellness'); ?></h4>
            <p><?php _e('Explore authentic classical formulations crafted to support your holistic wellness routine.', 'ayur-wellness'); ?></p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary ayur-btn-empty-shop">
                <span><?php _e('Explore Formulations', 'ayur-wellness'); ?></span>
                <?php echo ayur_svg_icon('arrow-right', '', 15); ?>
            </a>
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
        </div>

        <div class="ayur-drawer-footer">
            <div class="ayur-drawer-subtotal-row">
                <span class="ayur-drawer-subtotal-label"><?php _e('Subtotal', 'ayur-wellness'); ?></span>
                <span class="ayur-drawer-subtotal-val"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
            </div>

            <div class="ayur-drawer-cta-group">
                <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="ayur-btn-primary ayur-btn-drawer-checkout">
                    <span><?php _e('PROCEED TO CHECKOUT', 'ayur-wellness'); ?></span>
                    <?php echo ayur_svg_icon('arrow-right', '', 15); ?>
                </a>
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
    check_ajax_referer('ayur_theme_nonce', 'nonce');

    $cart_item_key = isset($_POST['cart_key']) ? sanitize_text_field($_POST['cart_key']) : '';
    $delta         = isset($_POST['delta']) ? intval($_POST['delta']) : 0;

    if ($cart_item_key && WC()->cart) {
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        if ($cart_item) {
            $new_qty = max(0, $cart_item['quantity'] + $delta);
            if ($new_qty === 0) {
                WC()->cart->remove_cart_item($cart_item_key);
            } else {
                WC()->cart->set_quantity($cart_item_key, $new_qty);
            }
        }
    }

    wp_send_json_success(array(
        'drawer_html' => ayur_get_cart_drawer_content_html(),
        'cart_count'  => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
    ));
}
add_action('wp_ajax_ayur_update_drawer_qty', 'ayur_ajax_update_cart_drawer_qty');
add_action('wp_ajax_nopriv_ayur_update_drawer_qty', 'ayur_ajax_update_cart_drawer_qty');

/**
 * AJAX: Remove Item from Cart Drawer
 */
function ayur_ajax_remove_cart_drawer_item() {
    check_ajax_referer('ayur_theme_nonce', 'nonce');

    $cart_item_key = isset($_POST['cart_key']) ? sanitize_text_field($_POST['cart_key']) : '';

    if ($cart_item_key && WC()->cart) {
        WC()->cart->remove_cart_item($cart_item_key);
    }

    wp_send_json_success(array(
        'drawer_html' => ayur_get_cart_drawer_content_html(),
        'cart_count'  => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
    ));
}
add_action('wp_ajax_ayur_remove_drawer_item', 'ayur_ajax_remove_cart_drawer_item');
add_action('wp_ajax_nopriv_ayur_remove_drawer_item', 'ayur_ajax_remove_cart_drawer_item');

/**
 * AJAX: Get Full Cart Drawer Content
 */
function ayur_ajax_get_cart_drawer() {
    wp_send_json_success(array(
        'drawer_html' => ayur_get_cart_drawer_content_html(),
        'cart_count'  => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
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
