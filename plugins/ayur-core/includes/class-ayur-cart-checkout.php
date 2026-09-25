<?php
/**
 * Cart & Checkout Optimization Engine for Indian D2C
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Cart_Checkout {

    public static function init() {
        // Free Shipping Progress Bar
        add_action('woocommerce_before_cart', array(__CLASS__, 'render_free_shipping_progress_bar'), 10);
        add_action('woocommerce_before_checkout_form', array(__CLASS__, 'render_free_shipping_progress_bar'), 10);
        add_filter('woocommerce_add_to_cart_fragments', array(__CLASS__, 'add_cart_fragments'));

        // Checkout Field Optimization for India
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
     * Free Shipping Progress Bar (₹999 Threshold)
     */
    public static function get_free_shipping_bar_html() {
        if (!WC()->cart) return '';

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
        $fragments['span.ayur-cart-badge'] = '<span class="ayur-cart-badge">' . (WC()->cart ? WC()->cart->get_cart_contents_count() : 0) . '</span>';
        return $fragments;
    }

    /**
     * Streamlined Indian D2C Checkout Fields
     */
    public static function optimize_indian_checkout_fields($fields) {
        // Remove unneeded fields for clean mobile checkout
        unset($fields['billing']['billing_company']);
        unset($fields['shipping']['shipping_company']);

        // Optimize Billing Phone (Crucial for Indian Logistics / OTP / WhatsApp)
        if (isset($fields['billing']['billing_phone'])) {
            $fields['billing']['billing_phone']['label'] = __('Mobile Number (For Courier Tracking & WhatsApp Updates)', 'ayur-core');
            $fields['billing']['billing_phone']['placeholder'] = __('10-digit mobile number (e.g. 9876543210)', 'ayur-core');
            $fields['billing']['billing_phone']['priority'] = 25;
            $fields['billing']['billing_phone']['class'] = array('form-row-wide', 'ayur-phone-field');
            $fields['billing']['billing_phone']['required'] = true;
        }

        // Optimize Postcode / State for India
        if (isset($fields['billing']['billing_postcode'])) {
            $fields['billing']['billing_postcode']['label'] = __('PIN Code', 'ayur-core');
            $fields['billing']['billing_postcode']['placeholder'] = __('6-digit PIN Code', 'ayur-core');
            $fields['billing']['billing_postcode']['priority'] = 65;
        }

        return $fields;
    }

    /**
     * Quick Buy Now (Single Click Direct to Checkout)
     */
    public static function handle_quick_buy() {
        if (isset($_REQUEST['ayur_quick_buy']) && !empty($_REQUEST['ayur_quick_buy'])) {
            $product_id = absint($_REQUEST['ayur_quick_buy']);
            $quantity = isset($_REQUEST['quantity']) ? absint($_REQUEST['quantity']) : 1;
            $variation_id = isset($_REQUEST['variation_id']) ? absint($_REQUEST['variation_id']) : 0;

            if (WC()->cart) {
                WC()->cart->empty_cart();
                WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
                wp_safe_redirect(wc_get_checkout_url());
                exit;
            }
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
                'cart_total'=> WC()->cart ? WC()->cart->get_total('edit') : 0,
                'items'     => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
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
