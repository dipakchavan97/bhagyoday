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
    }
});
