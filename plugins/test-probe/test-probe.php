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

        // 5. Verify COD Order Metadata
        if ($action === 'verify_cod_order_meta' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $order_id = absint($_GET['order_id']);
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error(array('message' => 'Order not found'));
            }

            wp_send_json_success(array(
                'order_id'                  => $order_id,
                'status'                    => $order->get_status(),
                'total'                     => (float) $order->get_total(),
                'subtotal'                  => (float) $order->get_subtotal(),
                'shipping_total'            => (float) $order->get_shipping_total(),
                'payment_method'            => $order->get_payment_method(),
                'cod_confirmation_fee'     => get_post_meta($order_id, '_cod_confirmation_fee', true),
                'cod_confirmation_payment_id' => get_post_meta($order_id, '_cod_confirmation_payment_id', true),
                'cod_confirmation_razorpay_order_id' => get_post_meta($order_id, '_cod_confirmation_razorpay_order_id', true),
                'cod_confirmation_paid'    => get_post_meta($order_id, '_cod_confirmation_paid', true),
                'cod_remaining_amount'      => get_post_meta($order_id, '_cod_remaining_amount', true),
                'cod_payment_status'       => get_post_meta($order_id, '_cod_payment_status', true),
                'cod_confirmation_timestamp' => get_post_meta($order_id, '_cod_confirmation_timestamp', true),
            ));
        }

        // 6. Create Test Confirmed COD Order
        if ($action === 'create_test_cod_confirmed_order' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $order = wc_create_order(array('status' => 'processing'));
            $product_id = 30; // Ashwagandha Shilajit (₹890)
            $product = wc_get_product($product_id);
            if ($product) {
                $order->add_product($product, 1);
            }

            // Standard Shipping ₹70
            $shipping_item = new WC_Order_Item_Shipping();
            $shipping_item->set_method_title('Standard Delivery (₹70)');
            $shipping_item->set_method_id('flat_rate:2');
            $shipping_item->set_total(70);
            $order->add_item($shipping_item);

            $order->set_address(array(
                'first_name' => 'Audit',
                'last_name'  => 'Tester',
                'email'      => 'audit@bhagyodayayurveda.com',
                'phone'      => '9888686858',
                'address_1'  => 'Civil Hospital Road',
                'city'       => 'Mantha',
                'state'      => 'MH',
                'postcode'   => '431504',
                'country'    => 'IN'
            ), 'billing');
            $order->set_address(array(
                'first_name' => 'Audit',
                'last_name'  => 'Tester',
                'phone'      => '9888686858',
                'address_1'  => 'Civil Hospital Road',
                'city'       => 'Mantha',
                'state'      => 'MH',
                'postcode'   => '431504',
                'country'    => 'IN'
            ), 'shipping');

            $order->set_payment_method('cod');
            $order->set_payment_method_title('Cash on Delivery (COD)');
            $order->calculate_totals();

            $order_id = $order->get_id();
            $remaining = (float) $order->get_total(); // ₹960

            update_post_meta($order_id, '_cod_confirmation_fee', 100);
            update_post_meta($order_id, '_cod_confirmation_payment_id', 'pay_test_' . wp_generate_password(14, false));
            update_post_meta($order_id, '_cod_confirmation_razorpay_order_id', 'order_test_' . wp_generate_password(14, false));
            update_post_meta($order_id, '_cod_confirmation_paid', 'yes');
            update_post_meta($order_id, '_cod_remaining_amount', $remaining);
            update_post_meta($order_id, '_cod_payment_status', 'confirmed');
            update_post_meta($order_id, '_cod_confirmation_timestamp', current_time('mysql'));

            $order->add_order_note('Test COD Confirmation Fee of ₹100 paid online. Remaining ₹' . $remaining . ' due on delivery.', false);

            wp_send_json_success(array(
                'order_id'       => $order_id,
                'order_key'      => $order->get_order_key(),
                'total'          => $order->get_total(),
                'thank_you_url'  => $order->get_checkout_order_received_url(),
            ));
        }

        // 7. Get Ayur Commerce State
        if ($action === 'get_ayur_commerce_state' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            wp_send_json_success(array(
                'instant_checkout_enabled' => class_exists('Ayur_Instant_Checkout') ? Ayur_Instant_Checkout::is_enabled() : false,
                'offers_enabled'           => class_exists('Ayur_Quantity_Offers') ? Ayur_Quantity_Offers::is_enabled() : false,
                'offer_tiers'              => class_exists('Ayur_Quantity_Offers') ? Ayur_Quantity_Offers::get_offer_tiers() : array(),
                'cod_fee_enabled'          => class_exists('Ayur_COD_Confirmation') ? Ayur_COD_Confirmation::is_enabled() : false,
                'cod_fee_amount'           => class_exists('Ayur_COD_Confirmation') ? Ayur_COD_Confirmation::get_fee_amount() : 100,
                'address_autofill_enabled' => class_exists('Ayur_Address_Autofill') ? Ayur_Address_Autofill::is_enabled() : false,
            ));
        }

        // 8. Get PHP Debug Log
        if ($action === 'get_debug_log' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            $log_path = WP_CONTENT_DIR . '/debug.log';
            $content = '';
            if (file_exists($log_path)) {
                $lines = file($log_path);
                $last_lines = array_slice($lines, -50);
                $content = implode("", $last_lines);
            }
            wp_send_json_success(array(
                'log_exists' => file_exists($log_path),
                'content'    => $content
            ));
        }

        // 9. Diagnose Instant Checkout execution
        if ($action === 'test_instant_checkout_exec' && isset($_GET['key']) && $_GET['key'] === 'ayur_audit_test_9921') {
            try {
                $_REQUEST['ayur_instant_buy'] = 30;
                $_REQUEST['quantity'] = 1;
                Ayur_Instant_Checkout::handle_instant_checkout();
                wp_send_json_success(array('status' => 'completed_no_throw'));
            } catch (Throwable $t) {
                wp_send_json_error(array(
                    'message' => $t->getMessage(),
                    'file'    => $t->getFile(),
                    'line'    => $t->getLine(),
                    'trace'   => $t->getTraceAsString()
                ));
            }
        }
    }
});



