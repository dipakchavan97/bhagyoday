<?php
/**
 * Plugin Name: Ayur Test Probe
 * Description: Diagnostic probe for deployment verification.
 * Version: 1.0.1
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function() {
    if (isset($_GET['ayur_test_probe_action'])) {
        $action = sanitize_text_field($_GET['ayur_test_probe_action']);
        
        // 1. Create a mock order for Purchase Event verification
        if ($action === 'create_test_order' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            if (!class_exists('WC_Order')) {
                wp_send_json_error(array('message' => 'WooCommerce not active'));
            }

            $order = wc_create_order();
            $product_id = 30; // Ashwagandha Shilajit
            $product = wc_get_product($product_id);
            if ($product) {
                $order->add_product($product, 1);
            }
            $order->set_address(array(
                'first_name' => 'Audit',
                'last_name'  => 'Tester',
                'company'    => '',
                'email'      => 'audit@bhagyodayayurveda.com',
                'phone'      => '9888686858',
                'address_1'  => 'Civil Hospital Road',
                'address_2'  => '',
                'city'       => 'Mantha',
                'state'      => 'MH',
                'postcode'   => '431504',
                'country'    => 'IN'
            ), 'billing');
            $order->set_address(array(
                'first_name' => 'Audit',
                'last_name'  => 'Tester',
                'address_1'  => 'Civil Hospital Road',
                'city'       => 'Mantha',
                'state'      => 'MH',
                'postcode'   => '431504',
                'country'    => 'IN'
            ), 'shipping');
            $order->set_payment_method('cod');
            $order->set_payment_method_title('Cash on Delivery');
            $order->calculate_totals();
            $order->update_status('processing', 'Test audit order for purchase verification');
            $order_id = $order->get_id();

            wp_send_json_success(array(
                'order_id'       => $order_id,
                'order_key'      => $order->get_order_key(),
                'total'          => $order->get_total(),
                'thank_you_url'  => $order->get_checkout_order_received_url(),
            ));
        }

        // 2. Clean up mock order
        if ($action === 'cleanup_test_order' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $order_id = absint($_GET['order_id']);
            if ($order_id) {
                wp_delete_post($order_id, true);
                wp_send_json_success(array('deleted' => $order_id));
            }
        }

        // 3. Clear WC Cart
        if ($action === 'empty_cart' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            if (function_exists('WC') && WC()->cart) {
                WC()->cart->empty_cart();
                wp_send_json_success(array('cleared' => true));
            } else {
                wp_send_json_error(array('message' => 'WC Cart unavailable'));
            }
        }

        // 4. Diagnose Checkout, Gateways & Shipping
        if ($action === 'diagnose_checkout' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {

            $prod = wc_get_product(30);
            $zones_data = array();
            if (class_exists('WC_Shipping_Zones')) {
                foreach (WC_Shipping_Zones::get_zones() as $z) {
                    $methods = array();
                    foreach ($z['shipping_methods'] as $m) {
                        $methods[] = array(
                            'id' => $m->id,
                            'instance_id' => $m->instance_id,
                            'title' => $m->title,
                            'enabled' => $m->enabled,
                            'settings' => $m->instance_settings
                        );
                    }
                    $zones_data[] = array(
                        'name' => $z['zone_name'],
                        'methods' => $methods
                    );
                }
            }

            $gateways_data = array();
            if (WC()->payment_gateways()) {
                foreach (WC()->payment_gateways()->payment_gateways() as $id => $gateway) {
                    $gateways_data[$id] = array(
                        'id' => $id,
                        'title' => $gateway->title,
                        'enabled' => $gateway->enabled,
                        'is_available' => $gateway->is_available(),
                        'settings' => isset($gateway->settings) ? $gateway->settings : array()
                    );
                }
            }

            $checkout_page = get_post(wc_get_page_id('checkout'));
            $cart_page = get_post(wc_get_page_id('cart'));

            wp_send_json_success(array(
                'setup_debug' => $setup_debug,
                'shipping_enabled' => wc_shipping_enabled(),
                'product_30' => array(
                    'needs_shipping' => $prod ? $prod->needs_shipping() : false,
                    'is_virtual' => $prod ? $prod->is_virtual() : false,
                    'price' => $prod ? $prod->get_price() : 0,
                    'regular_price' => $prod ? $prod->get_regular_price() : 0,
                ),
                'shipping_zones' => $zones_data,
                'gateways' => $gateways_data,
                'checkout_page_content' => $checkout_page ? $checkout_page->post_content : '',
                'cart_page_content' => $cart_page ? $cart_page->post_content : '',
            ));
        }
    }
});
