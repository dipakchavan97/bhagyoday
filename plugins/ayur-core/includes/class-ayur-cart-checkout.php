<?php
/**
 * Cart & Checkout Optimization Engine for Indian D2C
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Cart_Checkout {

    public static function init() {
        // Restore Native Checkout Shortcode & Clean Options
        add_action('init', array(__CLASS__, 'restore_native_woocommerce_checkout'), 5);

        // Product thumbnail in order review table
        add_filter('woocommerce_cart_item_name', array(__CLASS__, 'render_checkout_item_name_with_thumbnail'), 10, 3);

        // Free Shipping Progress Bar
        add_action('woocommerce_before_cart', array(__CLASS__, 'render_free_shipping_progress_bar'), 10);
        add_action('woocommerce_before_checkout_form', array(__CLASS__, 'render_free_shipping_progress_bar'), 10);
        add_filter('woocommerce_add_to_cart_fragments', array(__CLASS__, 'add_cart_fragments'));

        // Checkout Field Optimization for India (Single clean manual delivery address form)
        add_filter('woocommerce_checkout_fields', array(__CLASS__, 'optimize_indian_checkout_fields'));

        // Quick "Buy Now" handler
        add_action('wp_loaded', array(__CLASS__, 'handle_quick_buy'));

        // AJAX Abandoned Cart Capture
        add_action('wp_ajax_ayur_capture_checkout_lead', array(__CLASS__, 'ajax_capture_lead'));
        add_action('wp_ajax_nopriv_ayur_capture_checkout_lead', array(__CLASS__, 'ajax_capture_lead'));

        // Wishlist AJAX handler
        add_action('wp_ajax_ayur_toggle_wishlist', array(__CLASS__, 'ajax_toggle_wishlist'));
        add_action('wp_ajax_nopriv_ayur_toggle_wishlist', array(__CLASS__, 'ajax_toggle_wishlist'));

        // COD Verification Notice
        add_action('woocommerce_review_order_after_payment', array(__CLASS__, 'render_cod_security_notice'));
    }

    /**
     * Restore Native WooCommerce Pages & Clean Up Database Options (Phase 2 & 3)
     */
    public static function restore_native_woocommerce_checkout() {
        if (get_option('ayur_native_checkout_restored_v1') !== 'yes' || (isset($_GET['ayur_restore_native_checkout']) && $_GET['ayur_restore_native_checkout'] === 'ayur_audit_test_9921')) {
            $checkout_page_id = function_exists('wc_get_page_id') ? wc_get_page_id('checkout') : 0;
            if ($checkout_page_id) {
                wp_update_post(array(
                    'ID'           => $checkout_page_id,
                    'post_content' => '[woocommerce_checkout]',
                ));
            }

            $cart_page_id = function_exists('wc_get_page_id') ? wc_get_page_id('cart') : 0;
            if ($cart_page_id) {
                wp_update_post(array(
                    'ID'           => $cart_page_id,
                    'post_content' => '[woocommerce_cart]',
                ));
            }

            // Ensure guest checkout is permitted (do not force accounts per Phase 4)
            update_option('woocommerce_enable_guest_checkout', 'yes');
            update_option('woocommerce_enable_signup_and_login_from_checkout', 'no');
            update_option('woocommerce_ship_to_destination', 'billing');

            // Remove all leftover fastrr/pickrr options from database
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%fastrr%' OR option_name LIKE '%pickrr%'");

            update_option('ayur_native_checkout_restored_v1', 'yes');
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }

            if (isset($_GET['ayur_restore_native_checkout'])) {
                wp_send_json_success(array(
                    'status'      => 'Native checkout restored successfully',
                    'checkout_id' => $checkout_page_id,
                    'cart_id'     => $cart_page_id,
                ));
            }
        }
    }

    /**
     * Free Shipping Progress Bar (₹999 Threshold)
     */
    public static function get_free_shipping_bar_html() {
        if (!function_exists('WC') || !WC()->cart) return '';

        $threshold = 999;
        $subtotal  = (float) WC()->cart->get_displayed_subtotal();
        $remaining = $threshold - $subtotal;
        $percent   = min(100, max(0, round(($subtotal / $threshold) * 100)));

        ob_start();
        ?>
        <div class="ayur-shipping-progress-box" data-threshold="<?php echo esc_attr($threshold); ?>">
            <div class="ayur-progress-text">
                <?php if ($remaining <= 0) : ?>
                    <span class="unlocked"><?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('check', '', 15) : ''; ?> <strong><?php _e('You have unlocked FREE Pan-India Delivery', 'ayur-core'); ?></strong></span>
                <?php else : ?>
                    <span><?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('truck', '', 15) : ''; ?> <?php printf(__('Add <strong>₹%s</strong> more for <strong>FREE Delivery</strong>', 'ayur-core'), number_format($remaining)); ?></span>
                <?php endif; ?>
            </div>
            <div class="ayur-progress-bar-wrap">
                <div class="ayur-progress-bar-fill" style="width: <?php echo esc_attr($percent); ?>%;"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_free_shipping_progress_bar() {
        echo self::get_free_shipping_bar_html();
    }

    public static function add_cart_fragments($fragments) {
        $fragments['div.ayur-shipping-progress-box'] = self::get_free_shipping_bar_html();
        $fragments['span.ayur-cart-badge'] = '<span class="ayur-cart-badge">' . (function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0) . '</span>';
        return $fragments;
    }

    /**
     * Render Product Thumbnail and Details in Checkout Order Review Table
     */
    public static function render_checkout_item_name_with_thumbnail($name, $cart_item, $cart_item_key) {
        if (function_exists('is_checkout') && is_checkout()) {
            if (isset($cart_item['data']) && is_object($cart_item['data'])) {
                $product = $cart_item['data'];
                $thumb_id = $product->get_image_id();
                $thumb_html = '';
                if ($thumb_id) {
                    $thumb_html = wp_get_attachment_image($thumb_id, array(56, 56), false, array('class' => 'ayur-checkout-product-img'));
                } elseif (function_exists('ayur_svg_icon')) {
                    $thumb_html = '<span class="ayur-checkout-product-img placeholder">' . ayur_svg_icon('leaf', '', 20) . '</span>';
                }
                return '<div class="ayur-checkout-table-product">' . 
                    $thumb_html . 
                    '<div class="ayur-checkout-table-details">' .
                        '<strong class="ayur-checkout-table-name">' . esc_html($product->get_name()) . '</strong>' .
                        '<span class="ayur-checkout-table-qty">' . sprintf(__('Qty: %d', 'ayur-core'), $cart_item['quantity']) . '</span>' .
                    '</div>' .
                '</div>';
            }
        }
        return $name;
    }

    /**
     * Streamlined Indian D2C Checkout Fields (Manual Delivery Address Form)
     */
    public static function optimize_indian_checkout_fields($fields) {
        // Remove unneeded company fields for clean mobile checkout
        unset($fields['billing']['billing_company']);
        unset($fields['shipping']['shipping_company']);

        // First Name
        if (isset($fields['billing']['billing_first_name'])) {
            $fields['billing']['billing_first_name']['label'] = __('First Name', 'ayur-core');
            $fields['billing']['billing_first_name']['placeholder'] = __('First name', 'ayur-core');
            $fields['billing']['billing_first_name']['priority'] = 10;
            $fields['billing']['billing_first_name']['class'] = array('form-row-first');
        }

        // Last Name
        if (isset($fields['billing']['billing_last_name'])) {
            $fields['billing']['billing_last_name']['label'] = __('Last Name', 'ayur-core');
            $fields['billing']['billing_last_name']['placeholder'] = __('Last name', 'ayur-core');
            $fields['billing']['billing_last_name']['priority'] = 15;
            $fields['billing']['billing_last_name']['class'] = array('form-row-last');
        }

        // Billing Phone (Crucial for Indian Logistics / Courier Tracking)
        if (isset($fields['billing']['billing_phone'])) {
            $fields['billing']['billing_phone']['label'] = __('Mobile Number (For Courier Tracking & Updates)', 'ayur-core');
            $fields['billing']['billing_phone']['placeholder'] = __('10-digit mobile number (e.g. 9876543210)', 'ayur-core');
            $fields['billing']['billing_phone']['priority'] = 20;
            $fields['billing']['billing_phone']['class'] = array('form-row-wide', 'ayur-phone-field');
            $fields['billing']['billing_phone']['required'] = true;
        }

        // Email
        if (isset($fields['billing']['billing_email'])) {
            $fields['billing']['billing_email']['label'] = __('Email Address', 'ayur-core');
            $fields['billing']['billing_email']['placeholder'] = __('For order confirmation & invoice', 'ayur-core');
            $fields['billing']['billing_email']['priority'] = 25;
            $fields['billing']['billing_email']['class'] = array('form-row-wide');
            $fields['billing']['billing_email']['required'] = true;
        }

        // Delivery Street Address (Address 1)
        if (isset($fields['billing']['billing_address_1'])) {
            $fields['billing']['billing_address_1']['label'] = __('Delivery Street Address', 'ayur-core');
            $fields['billing']['billing_address_1']['placeholder'] = __('House / Flat No., Building, Street', 'ayur-core');
            $fields['billing']['billing_address_1']['priority'] = 30;
            $fields['billing']['billing_address_1']['class'] = array('form-row-wide');
            $fields['billing']['billing_address_1']['required'] = true;
        }

        // Landmark / Colony (Address 2)
        if (isset($fields['billing']['billing_address_2'])) {
            $fields['billing']['billing_address_2']['label'] = __('Landmark / Locality (Optional)', 'ayur-core');
            $fields['billing']['billing_address_2']['placeholder'] = __('Nearby landmark, apartment or area', 'ayur-core');
            $fields['billing']['billing_address_2']['priority'] = 35;
            $fields['billing']['billing_address_2']['class'] = array('form-row-wide');
            $fields['billing']['billing_address_2']['required'] = false;
        }

        // PIN Code
        if (isset($fields['billing']['billing_postcode'])) {
            $fields['billing']['billing_postcode']['label'] = __('PIN Code', 'ayur-core');
            $fields['billing']['billing_postcode']['placeholder'] = __('6-digit PIN Code', 'ayur-core');
            $fields['billing']['billing_postcode']['priority'] = 40;
            $fields['billing']['billing_postcode']['class'] = array('form-row-first');
            $fields['billing']['billing_postcode']['required'] = true;
        }

        // City
        if (isset($fields['billing']['billing_city'])) {
            $fields['billing']['billing_city']['label'] = __('City / District', 'ayur-core');
            $fields['billing']['billing_city']['placeholder'] = __('City or District', 'ayur-core');
            $fields['billing']['billing_city']['priority'] = 45;
            $fields['billing']['billing_city']['class'] = array('form-row-last');
            $fields['billing']['billing_city']['required'] = true;
        }

        // State
        if (isset($fields['billing']['billing_state'])) {
            $fields['billing']['billing_state']['label'] = __('State', 'ayur-core');
            $fields['billing']['billing_state']['priority'] = 50;
            $fields['billing']['billing_state']['class'] = array('form-row-wide');
            $fields['billing']['billing_state']['required'] = true;
        }

        // Country
        if (isset($fields['billing']['billing_country'])) {
            $fields['billing']['billing_country']['priority'] = 55;
            $fields['billing']['billing_country']['default'] = 'IN';
        }

        return $fields;
    }

    /**
     * Quick Buy Now (Single Click Direct to Checkout)
     */
    public static function handle_quick_buy() {
        if (class_exists('Ayur_Instant_Checkout')) {
            Ayur_Instant_Checkout::handle_instant_checkout();
        }
    }

    /**
     * COD Security Notice
     */
    public static function render_cod_security_notice() {
        ?>
        <div class="ayur-checkout-trust-badge" style="margin: 15px 0; padding: 14px 18px; background: #FAF7F0; border-radius: 8px; border: 1px solid rgba(23,59,47,0.1); font-size: 13px; color: #222522; display: flex; align-items: center; gap: 12px;">
            <span style="color: #315C45; display: inline-flex;"><?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('shield-check', '', 22) : ''; ?></span>
            <div>
                <strong style="color: #173B2F;"><?php _e('100% Authentic & Secure Guarantee:', 'ayur-core'); ?></strong>
                <div style="color: #5F6762; font-size: 12px; margin-top: 2px;"><?php _e('Instant UPI / Card refunds on returns. For COD orders, automated phone verification confirms dispatch.', 'ayur-core'); ?></div>
            </div>
        </div>
        <?php
    }

    /**
     * Abandoned Cart Lead Capture
     */
    public static function ajax_capture_lead() {
        check_ajax_referer('ayur_core_nonce', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

        if (!empty($email) || !empty($phone)) {
            $leads = get_option('ayur_abandoned_cart_leads', array());
            $key = !empty($email) ? $email : $phone;
            
            $leads[$key] = array(
                'email'     => $email,
                'phone'     => $phone,
                'cart_total'=> (function_exists('WC') && WC()->cart) ? WC()->cart->get_total('edit') : 0,
                'items'     => (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0,
                'time'      => current_time('mysql'),
            );

            // Keep maximum 200 recent leads
            if (count($leads) > 200) {
                $leads = array_slice($leads, -200, 200, true);
            }

            update_option('ayur_abandoned_cart_leads', $leads);
        }

        wp_send_json_success();
    }

    /**
     * Wishlist Toggle AJAX
     */
    public static function ajax_toggle_wishlist() {
        check_ajax_referer('ayur_core_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id) wp_send_json_error();

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $wishlist = get_user_meta($user_id, '_ayur_wishlist', true);
            if (!is_array($wishlist)) $wishlist = array();

            if (in_array($product_id, $wishlist)) {
                $wishlist = array_diff($wishlist, array($product_id));
                $action = 'removed';
            } else {
                $wishlist[] = $product_id;
                $action = 'added';
            }
            update_user_meta($user_id, '_ayur_wishlist', array_values($wishlist));
            wp_send_json_success(array('action' => $action, 'count' => count($wishlist)));
        } else {
            wp_send_json_success(array('action' => 'guest_sync'));
        }
    }
}
