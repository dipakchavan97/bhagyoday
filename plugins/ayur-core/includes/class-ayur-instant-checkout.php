<?php
/**
 * Instant Checkout Session Engine
 * Allows direct single-product checkout ("Buy Now") without losing pre-existing cart items.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Instant_Checkout {

    public static function init() {
        add_action('wp_loaded', array(__CLASS__, 'handle_instant_checkout'), 20);
        add_action('template_redirect', array(__CLASS__, 'handle_cart_page_return'));
        add_action('woocommerce_thankyou', array(__CLASS__, 'handle_order_completed'), 10, 1);
        add_filter('woocommerce_add_to_cart_redirect', array(__CLASS__, 'filter_add_to_cart_redirect'));
    }

    /**
     * Check if Instant Checkout is globally enabled in settings
     */
    public static function is_enabled() {
        return get_option('ayur_instant_checkout_enabled', 'yes') === 'yes';
    }

    /**
     * Handle Buy Now action from Single Product or Sticky Bar
     * URL pattern: ?ayur_instant_buy={id}&quantity={qty} or ?ayur_quick_buy={id}&quantity={qty}
     */
    public static function handle_instant_checkout() {
        if (!self::is_enabled()) {
            return;
        }

        $product_id = 0;
        if (isset($_REQUEST['ayur_instant_buy']) && !empty($_REQUEST['ayur_instant_buy'])) {
            $product_id = absint($_REQUEST['ayur_instant_buy']);
        } elseif (isset($_REQUEST['ayur_quick_buy']) && !empty($_REQUEST['ayur_quick_buy'])) {
            $product_id = absint($_REQUEST['ayur_quick_buy']);
        }

        if (!$product_id || !function_exists('WC')) {
            return;
        }

        // Initialize session if not yet initialized
        if (null === WC()->session) {
            $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
            if (class_exists($session_class)) {
                WC()->session = new $session_class();
                WC()->session->init();
            }
        }
        if (WC()->session && !WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }

        // Initialize cart if not yet initialized
        if (null === WC()->cart) {
            WC()->cart = new WC_Cart();
        }

        $quantity = isset($_REQUEST['quantity']) ? max(1, absint($_REQUEST['quantity'])) : 1;
        $variation_id = isset($_REQUEST['variation_id']) ? absint($_REQUEST['variation_id']) : 0;
        $variation = isset($_REQUEST['variation']) && is_array($_REQUEST['variation']) ? $_REQUEST['variation'] : array();

        // 1. Safe Cart Preservation:
        // If current cart has items and is not already an instant session for this exact product/qty, save it
        if (WC()->cart) {
            $current_cart = WC()->cart->get_cart();
            if (!empty($current_cart)) {
                $is_already_same = false;
                if (count($current_cart) === 1) {
                    $only_item = reset($current_cart);
                    if ((int) $only_item['product_id'] === $product_id && (int) $only_item['quantity'] === $quantity) {
                        $is_already_same = true;
                    }
                }

                if (!$is_already_same && WC()->session) {
                    if (!WC()->session->get('ayur_is_instant_session')) {
                        $cart_to_save = array();
                        foreach ($current_cart as $key => $item) {
                            $cart_to_save[] = array(
                                'product_id'   => absint($item['product_id']),
                                'quantity'     => absint($item['quantity']),
                                'variation_id' => isset($item['variation_id']) ? absint($item['variation_id']) : 0,
                                'variation'    => isset($item['variation']) && is_array($item['variation']) ? $item['variation'] : array(),
                            );
                        }
                        WC()->session->set('ayur_saved_pre_instant_cart', $cart_to_save);
                    }
                }
            }

            // 2. Mark this session as a dedicated instant checkout session
            if (WC()->session) {
                WC()->session->set('ayur_is_instant_session', true);
                WC()->session->set('ayur_instant_product_id', $product_id);
                WC()->session->set('ayur_instant_quantity', $quantity);
            }

            // 3. Clear current cart and add only the instant checkout item
            WC()->cart->empty_cart();
            WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
        }

        if (WC()->session) {
            WC()->session->save_data();
        }

        // 4. Redirect directly to /checkout/
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }

    /**
     * If customer abandons instant checkout and visits the /cart/ page, restore their previous cart
     */
    public static function handle_cart_page_return() {
        $is_cart = (function_exists('is_cart') && is_cart()) || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/cart') !== false && strpos($_SERVER['REQUEST_URI'], '/checkout') === false);
        if (!$is_cart || !function_exists('WC') || !WC()->session) {
            return;
        }

        if (WC()->session->get('ayur_is_instant_session')) {
            $saved_cart = WC()->session->get('ayur_saved_pre_instant_cart');
            if (!empty($saved_cart) && is_array($saved_cart)) {
                // Customer decided to view their full cart rather than instant checkout
                WC()->cart->empty_cart();
                foreach ($saved_cart as $item) {
                    WC()->cart->add_to_cart(
                        $item['product_id'],
                        $item['quantity'],
                        isset($item['variation_id']) ? $item['variation_id'] : 0,
                        isset($item['variation']) ? $item['variation'] : array()
                    );
                }
                WC()->session->__unset('ayur_saved_pre_instant_cart');
            }
            WC()->session->__unset('ayur_is_instant_session');
            WC()->session->__unset('ayur_instant_product_id');
            WC()->session->__unset('ayur_instant_quantity');
            WC()->session->save_data();
        }
    }

    /**
     * After instant order is completed, restore pre-existing cart items if any were preserved
     */
    public static function handle_order_completed($order_id) {
        if (!function_exists('WC') || !WC()->session) {
            return;
        }

        if (WC()->session->get('ayur_is_instant_session')) {
            $saved_cart = WC()->session->get('ayur_saved_pre_instant_cart');
            if (!empty($saved_cart) && is_array($saved_cart)) {
                // Restore their original items so they find them upon return
                WC()->cart->empty_cart();
                foreach ($saved_cart as $item) {
                    WC()->cart->add_to_cart(
                        $item['product_id'],
                        $item['quantity'],
                        isset($item['variation_id']) ? $item['variation_id'] : 0,
                        isset($item['variation']) ? $item['variation'] : array()
                    );
                }
                WC()->session->__unset('ayur_saved_pre_instant_cart');
            }
            WC()->session->__unset('ayur_is_instant_session');
            WC()->session->__unset('ayur_instant_product_id');
            WC()->session->__unset('ayur_instant_quantity');
            WC()->session->save_data();
        }
    }

    public static function filter_add_to_cart_redirect($url) {
        return $url;
    }
}
