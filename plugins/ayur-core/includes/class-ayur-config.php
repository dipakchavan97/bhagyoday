<?php
/**
 * WooCommerce Configuration & Indian Localization Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Config {

    public static function init() {
        add_action('init', array(__CLASS__, 'ensure_wc_settings'));
        add_filter('woocommerce_currency_symbols', array(__CLASS__, 'add_inr_symbol'));
        add_filter('woocommerce_currencies', array(__CLASS__, 'add_inr_currency'));
        add_filter('woocommerce_package_rates', array(__CLASS__, 'hide_shipping_when_free_is_available'), 100, 2);

        // Production Security Hardening
        add_filter('xmlrpc_enabled', '__return_false');
        remove_action('wp_head', 'wp_generator');
        add_filter('rest_endpoints', array(__CLASS__, 'protect_rest_user_endpoints'));
    }

    public static function setup_defaults() {
        // Disable WooCommerce Coming Soon mode so store is live
        update_option('woocommerce_coming_soon', 'no');
        update_option('woocommerce_store_pages_only', 'no');

        // Core WooCommerce Settings for India
        update_option('woocommerce_default_country', 'IN:MH'); // Default Maharashtra, India
        update_option('woocommerce_currency', 'INR');
        update_option('woocommerce_currency_pos', 'left');
        update_option('woocommerce_price_thousand_sep', ',');
        update_option('woocommerce_price_decimal_sep', '.');
        update_option('woocommerce_price_num_decimals', 0); // Clean Indian D2C look (₹499)
        update_option('woocommerce_weight_unit', 'g');
        update_option('woocommerce_dimension_unit', 'cm');
        
        // Allowed Countries: Sell and Ship to India only
        update_option('woocommerce_allowed_countries', 'specific');
        update_option('woocommerce_specific_allowed_countries', array('IN'));
        update_option('woocommerce_ship_to_countries', 'specific');
        update_option('woocommerce_specific_ship_to_countries', array('IN'));
        
        // Checkout & Accounts
        update_option('woocommerce_enable_guest_checkout', 'yes');
        update_option('woocommerce_enable_signup_and_login_from_checkout', 'yes');
        update_option('woocommerce_enable_myaccount_registration', 'yes');
        
        // Inventory
        update_option('woocommerce_manage_stock', 'yes');
        update_option('woocommerce_notify_low_stock', 'yes');
        update_option('woocommerce_notify_low_stock_amount', 5);
        update_option('woocommerce_notify_no_stock_amount', 0);
        update_option('woocommerce_stock_format', 'low_amount');
        
        // Reviews
        update_option('woocommerce_enable_reviews', 'yes');
        update_option('woocommerce_review_rating_required', 'yes');
        update_option('woocommerce_review_rating_verification_required', 'no');
        
        // Set standard Indian Shipping Zone if none exists
        self::setup_shipping_zones();
        
        // Enable Cash on Delivery
        self::ensure_payment_gateways();

        // Transactional Email Branding
        update_option('woocommerce_email_from_name', 'Bhagyoday Ayurveda');
        update_option('woocommerce_email_from_address', 'vijaykulal8833@gmail.com');
        update_option('woocommerce_email_footer_text', 'Bhagyoday Ayurveda &bull; Infront of Nagar Panchayat Civil Hospital, Mantha, Maharashtra 431504');
    }

    public static function ensure_wc_settings() {
        if (class_exists('WC_Shipping_Zones')) {
            $zones = WC_Shipping_Zones::get_zones();
            if (empty($zones)) {
                self::setup_shipping_zones();
            }
        }
        self::ensure_payment_gateways();

        if (get_option('woocommerce_coming_soon') === 'yes' || get_option('woocommerce_currency') !== 'INR' || get_option('woocommerce_email_from_name') !== 'Bhagyoday Ayurveda') {
            self::setup_defaults();
        }
    }

    public static function setup_shipping_zones() {
        if (!class_exists('WC_Shipping_Zones')) {
            return;
        }

        $zones = WC_Shipping_Zones::get_zones();
        $india_zone_exists = false;

        foreach ($zones as $zone) {
            if ($zone['zone_name'] === 'India') {
                $india_zone_exists = true;
                break;
            }
        }

        if (!$india_zone_exists) {
            $zone = new WC_Shipping_Zone();
            $zone->set_zone_name('India');
            $zone->set_zone_order(1);
            $zone->add_location('IN', 'country');
            $zone->save();

            // Add Free Shipping for Orders >= ₹999
            $free_shipping_instance_id = $zone->add_shipping_method('free_shipping');
            $free_shipping = new WC_Shipping_Free_Shipping($free_shipping_instance_id);
            $free_shipping->init_instance_settings();
            $free_shipping->instance_settings['title'] = 'Free Express Delivery (Orders Above ₹999)';
            $free_shipping->instance_settings['requires'] = 'min_amount';
            $free_shipping->instance_settings['min_amount'] = '999';
            update_option($free_shipping->get_instance_option_key(), $free_shipping->instance_settings);

            // Add Flat Rate ₹70 for Standard Shipping
            $flat_rate_instance_id = $zone->add_shipping_method('flat_rate');
            $flat_rate = new WC_Shipping_Flat_Rate($flat_rate_instance_id);
            $flat_rate->init_instance_settings();
            $flat_rate->instance_settings['title'] = 'Standard Delivery (₹70)';
            $flat_rate->instance_settings['cost'] = '70';
            update_option($flat_rate->get_instance_option_key(), $flat_rate->instance_settings);
        }
    }

    public static function ensure_payment_gateways() {
        // Enable COD by default
        $cod_settings = get_option('woocommerce_cod_settings', array());
        $cod_settings['enabled'] = 'yes';
        $cod_settings['title'] = 'Cash on Delivery (COD)';
        $cod_settings['description'] = 'Pay with cash upon delivery. Verified by automated phone confirmation.';
        $cod_settings['instructions'] = 'Please keep exact cash ready upon delivery. Our delivery partner will verify your order.';
        $cod_settings['enable_for_virtual'] = 'yes';
        $cod_settings['enable_for_methods'] = array();
        update_option('woocommerce_cod_settings', $cod_settings);
    }

    /**
     * Hide Standard Delivery when Free Express Delivery is unlocked (> ₹999)
     */
    public static function hide_shipping_when_free_is_available($rates, $package) {
        $free = array();
        foreach ($rates as $rate_id => $rate) {
            if ('free_shipping' === $rate->method_id) {
                $free[$rate_id] = $rate;
                break;
            }
        }
        return !empty($free) ? $free : $rates;
    }

    public static function add_inr_symbol($symbols) {
        $symbols['INR'] = '₹';
        return $symbols;
    }

    public static function add_inr_currency($currencies) {
        $currencies['INR'] = __('Indian Rupee (₹)', 'ayur-core');
        return $currencies;
    }

    /**
     * Prevent unauthenticated REST API user enumeration
     */
    public static function protect_rest_user_endpoints($endpoints) {
        if (isset($endpoints['/wp/v2/users'])) {
            $endpoints['/wp/v2/users'][0]['permission_callback'] = function() {
                return current_user_can('list_users');
            };
        }
        if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
            $endpoints['/wp/v2/users/(?P<id>[\d]+)'][0]['permission_callback'] = function() {
                return current_user_can('list_users');
            };
        }
        return $endpoints;
    }
}
