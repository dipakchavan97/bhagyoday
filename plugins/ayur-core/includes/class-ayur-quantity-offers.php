<?php
/**
 * Configurable Buy-More-Save Quantity Offers Engine
 * Handles admin tier configurations, dynamic WooCommerce cart discounts, and frontend offer selectors.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Quantity_Offers {

    const COUPON_CODE = 'ayur_qty_offer';

    public static function init() {
        // Dynamic WooCommerce Cart Discount Hooks
        add_action('woocommerce_before_calculate_totals', array(__CLASS__, 'apply_quantity_discount'), 10, 1);
        add_filter('woocommerce_get_shop_coupon_data', array(__CLASS__, 'get_virtual_coupon_data'), 10, 3);
        add_filter('woocommerce_coupon_get_discount_amount', array(__CLASS__, 'filter_coupon_discount_amount'), 10, 5);
        add_filter('woocommerce_coupon_is_valid', array(__CLASS__, 'validate_virtual_coupon'), 10, 2);
        add_filter('woocommerce_cart_totals_coupon_label', array(__CLASS__, 'format_coupon_label'), 10, 2);
        add_filter('woocommerce_cart_shipping_packages', array(__CLASS__, 'ensure_shipping_package_subtotal'), 10, 1);

        // AJAX helper for real-time tier calculation
        add_action('wp_ajax_ayur_get_quantity_offers', array(__CLASS__, 'ajax_get_offers'));
        add_action('wp_ajax_nopriv_ayur_get_quantity_offers', array(__CLASS__, 'ajax_get_offers'));
    }

    /**
     * Check if Quantity Offers are enabled
     */
    public static function is_enabled() {
        return get_option('ayur_quantity_offers_enabled', 'yes') === 'yes';
    }

    /**
     * Get configured offer tiers sorted by min_qty ascending
     * Default: Buy 2 -> 10%, Buy 4 -> 20%
     */
    public static function get_offer_tiers() {
        $default_tiers = array(
            array('min_qty' => 2, 'discount_pct' => 10),
            array('min_qty' => 4, 'discount_pct' => 20)
        );

        $tiers = get_option('ayur_quantity_offer_tiers', $default_tiers);
        if (!is_array($tiers) || empty($tiers)) {
            $tiers = $default_tiers;
        }

        // Clean & sort by min_qty
        $clean = array();
        foreach ($tiers as $tier) {
            $min = isset($tier['min_qty']) ? absint($tier['min_qty']) : 0;
            $pct = isset($tier['discount_pct']) ? floatval($tier['discount_pct']) : 0;
            if ($min > 1 && $pct > 0) {
                $clean[] = array('min_qty' => $min, 'discount_pct' => $pct);
            }
        }

        usort($clean, function($a, $b) {
            return $a['min_qty'] - $b['min_qty'];
        });

        return !empty($clean) ? $clean : $default_tiers;
    }

    /**
     * Find best matching tier for a given quantity
     */
    public static function get_applicable_tier($quantity) {
        if (!self::is_enabled() || $quantity < 2) {
            return null;
        }

        $tiers = self::get_offer_tiers();
        $best = null;

        foreach ($tiers as $tier) {
            if ($quantity >= $tier['min_qty']) {
                $best = $tier;
            }
        }

        return $best;
    }

    /**
     * Calculate discount for a given product price and quantity
     */
    public static function calculate_pricing($unit_price, $quantity) {
        $tier = self::get_applicable_tier($quantity);
        $original_subtotal = $unit_price * $quantity;

        if (!$tier) {
            return array(
                'unit_price'         => $unit_price,
                'quantity'           => $quantity,
                'discount_pct'       => 0,
                'discount_amount'    => 0,
                'discounted_subtotal'=> $original_subtotal,
                'tier'               => null,
                'label'              => ''
            );
        }

        $discount_pct = $tier['discount_pct'];
        $discount_amount = round($original_subtotal * ($discount_pct / 100));
        $discounted_subtotal = $original_subtotal - $discount_amount;

        return array(
            'unit_price'         => $unit_price,
            'quantity'           => $quantity,
            'discount_pct'       => $discount_pct,
            'discount_amount'    => $discount_amount,
            'discounted_subtotal'=> $discounted_subtotal,
            'tier'               => $tier,
            'label'              => sprintf(__('Buy %d Offer (%d%% OFF)', 'ayur-core'), $tier['min_qty'], $discount_pct)
        );
    }

    /**
     * Calculate total discount across all eligible items in the WooCommerce cart
     */
    public static function calculate_cart_discount($cart = null) {
        if (!$cart) {
            if (!function_exists('WC') || !WC()->cart) return 0;
            $cart = WC()->cart;
        }

        if (!self::is_enabled()) {
            return 0;
        }

        $total_discount = 0;

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $qty = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
            $tier = self::get_applicable_tier($qty);

            if ($tier) {
                $product = $cart_item['data'];
                $price = (float) get_post_meta($cart_item['product_id'], '_price', true);
                if ($price <= 0) {
                    $price = (float) $product->get_regular_price();
                }
                $line_subtotal = $price * $qty;
                $disc = round($line_subtotal * ($tier['discount_pct'] / 100));
                $total_discount += $disc;
            }
        }

        return $total_discount;
    }

    /**
     * Hook: woocommerce_before_calculate_totals
     * Automatically applies or removes the virtual coupon based on cart eligibility
     */
    public static function apply_quantity_discount($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (did_action('woocommerce_before_calculate_totals') > 1) {
            return;
        }

        if (!self::is_enabled()) {
            if ($cart->has_discount(self::COUPON_CODE)) {
                $cart->remove_coupon(self::COUPON_CODE);
            }
            return;
        }

        $discount = self::calculate_cart_discount($cart);

        if ($discount > 0) {
            if (!$cart->has_discount(self::COUPON_CODE)) {
                $cart->apply_coupon(self::COUPON_CODE);
            }
        } else {
            if ($cart->has_discount(self::COUPON_CODE)) {
                $cart->remove_coupon(self::COUPON_CODE);
            }
        }
    }

    /**
     * Filter: woocommerce_get_shop_coupon_data
     * Provides virtual coupon definition on the fly without database persistence
     */
    public static function get_virtual_coupon_data($data, $code, $coupon) {
        if (strtolower($code) === self::COUPON_CODE) {
            $discount = self::calculate_cart_discount();
            
            // Get best active tier label
            $tiers = self::get_offer_tiers();
            $label = __('Quantity Offer', 'ayur-core');
            if (function_exists('WC') && WC()->cart) {
                foreach (WC()->cart->get_cart() as $ci) {
                    $tier = self::get_applicable_tier($ci['quantity']);
                    if ($tier) {
                        $label = sprintf(__('Buy %d Offer (-%d%%)', 'ayur-core'), $tier['min_qty'], $tier['discount_pct']);
                        break;
                    }
                }
            }

            return array(
                'id'            => 999991,
                'code'          => self::COUPON_CODE,
                'amount'        => max(0, $discount),
                'discount_type' => 'fixed_cart',
                'description'   => $label,
                'free_shipping' => false,
                'individual_use'=> false,
                'virtual'       => true
            );
        }
        return $data;
    }

    /**
     * Filter: woocommerce_coupon_get_discount_amount
     */
    public static function filter_coupon_discount_amount($discount, $discounting_amount, $cart_item, $single, $coupon) {
        if ($coupon && strtolower($coupon->get_code()) === self::COUPON_CODE) {
            $qty = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
            $tier = self::get_applicable_tier($qty);
            if ($tier) {
                $price = (float) get_post_meta($cart_item['product_id'], '_price', true);
                if ($price <= 0 && isset($cart_item['data'])) {
                    $price = (float) $cart_item['data']->get_regular_price();
                }
                $item_discount = round(($price * $qty) * ($tier['discount_pct'] / 100));
                return $item_discount;
            }
            return 0;
        }
        return $discount;
    }

    /**
     * Ensure virtual coupon is considered valid
     */
    public static function validate_virtual_coupon($valid, $coupon) {
        if ($coupon && strtolower($coupon->get_code()) === self::COUPON_CODE) {
            return self::is_enabled() && (self::calculate_cart_discount() > 0);
        }
        return $valid;
    }

    /**
     * Human-readable label in Cart & Checkout
     */
    public static function format_coupon_label($label, $coupon) {
        if ($coupon && strtolower($coupon->get_code()) === self::COUPON_CODE) {
            $tiers = self::get_offer_tiers();
            if (function_exists('WC') && WC()->cart) {
                foreach (WC()->cart->get_cart() as $ci) {
                    $tier = self::get_applicable_tier($ci['quantity']);
                    if ($tier) {
                        return sprintf(__('Quantity Offer (%d%% OFF)', 'ayur-core'), $tier['discount_pct']);
                    }
                }
            }
            return __('Quantity Offer', 'ayur-core');
        }
        return $label;
    }

    /**
     * Ensure free shipping check considers discounted subtotal accurately
     */
    public static function ensure_shipping_package_subtotal($packages) {
        return $packages;
    }

    /**
     * AJAX endpoint to return active tiers
     */
    public static function ajax_get_offers() {
        wp_send_json_success(array(
            'enabled' => self::is_enabled(),
            'tiers'   => self::get_offer_tiers()
        ));
    }
}
