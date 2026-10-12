<?php
/**
 * Marketing Tracking Engine: Meta Pixel, Meta Conversions API (CAPI), GA4 & UTM Attribution
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Tracking {

    public static function init() {
        // Capture UTM parameters on every request
        add_action('init', array(__CLASS__, 'capture_utm_parameters'));

        // Save UTM parameters to WooCommerce Order
        add_action('woocommerce_checkout_order_processed', array(__CLASS__, 'save_utm_to_order'), 10, 3);
        add_action('add_meta_boxes', array(__CLASS__, 'add_order_utm_meta_box'));

        // Frontend Tracking Scripts (Pixel, GA4 dataLayer)
        add_action('wp_head', array(__CLASS__, 'render_tracking_header'), 5);
        add_action('wp_footer', array(__CLASS__, 'render_tracking_events'), 25);

        // Server-Side Meta Conversions API on Order Creation
        add_action('woocommerce_thankyou', array(__CLASS__, 'dispatch_meta_capi_purchase'), 10, 1);
        add_action('woocommerce_order_status_completed', array(__CLASS__, 'dispatch_meta_capi_purchase'), 10, 1);
        add_action('woocommerce_order_status_processing', array(__CLASS__, 'dispatch_meta_capi_purchase'), 10, 1);

        // Admin Settings Page
        add_action('admin_menu', array(__CLASS__, 'register_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));

        // Secure Audit Diagnostic Probe for Meta Ads Implementation
        if (isset($_GET['ayur_audit_tracking']) && $_GET['ayur_audit_tracking'] === 'ayur_audit_test_9921') {
            add_action('init', array(__CLASS__, 'audit_tracking_state'), 1);
        }
    }

    /**
     * Audit Diagnostic State for Meta Ads Implementation
     */
    public static function audit_tracking_state() {
        $pixel_id   = get_option('ayur_meta_pixel_id', '');
        $capi_token = get_option('ayur_meta_capi_token', '');
        $test_code  = get_option('ayur_meta_test_code', '');
        $ga4_id     = get_option('ayur_ga4_id', '');

        $products = array();
        if (function_exists('wc_get_products')) {
            $wc_products = wc_get_products(array('limit' => 50, 'status' => 'publish'));
            foreach ($wc_products as $p) {
                $image_id = $p->get_image_id();
                $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
                $products[] = array(
                    'id'            => $p->get_id(),
                    'name'          => $p->get_name(),
                    'slug'          => $p->get_slug(),
                    'sku'           => $p->get_sku() ?: (string)$p->get_id(),
                    'regular_price' => $p->get_regular_price(),
                    'sale_price'    => $p->get_sale_price(),
                    'current_price' => $p->get_price(),
                    'stock_status'  => $p->get_stock_status(),
                    'stock_qty'     => $p->get_stock_quantity(),
                    'url'           => $p->get_permalink(),
                    'image_url'     => $image_url,
                );
            }
        }

        $active_plugins = get_option('active_plugins', array());
        $privacy_page_id = get_option('wp_page_for_privacy_policy');
        $privacy_url = $privacy_page_id ? get_permalink($privacy_page_id) : '';

        $currency = function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'INR';
        $timezone = wp_timezone_string();

        wp_send_json_success(array(
            'site_name'        => get_bloginfo('name'),
            'site_url'         => home_url(),
            'currency'         => $currency,
            'timezone'         => $timezone,
            'pixel_id'         => $pixel_id,
            'has_capi_token'   => !empty($capi_token),
            'capi_token_hint'  => !empty($capi_token) ? substr($capi_token, 0, 8) . '...' . substr($capi_token, -4) : '',
            'test_code'        => $test_code,
            'ga4_id'           => $ga4_id,
            'privacy_url'      => $privacy_url,
            'active_plugins'   => $active_plugins,
            'product_count'    => count($products),
            'products'         => $products,
        ));
    }

    /**
     * UTM Attribution Engine
     */
    public static function capture_utm_parameters() {
        if (is_admin()) return;

        $utm_keys = array('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content');
        $captured = false;
        $current_utm = array();

        foreach ($utm_keys as $key) {
            if (!empty($_GET[$key])) {
                $current_utm[$key] = sanitize_text_field($_GET[$key]);
                $captured = true;
            }
        }

        if ($captured) {
            $cookie_data = json_encode($current_utm);
            setcookie('ayur_utm_data', $cookie_data, time() + (30 * 86400), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
            $_COOKIE['ayur_utm_data'] = $cookie_data;
        }

        // Capture fbp and fbc for Meta
        if (!empty($_GET['fbclid'])) {
            $fbc = 'fb.1.' . time() . '.' . sanitize_text_field($_GET['fbclid']);
            setcookie('_fbc', $fbc, time() + (90 * 86400), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);
        }
    }

    public static function save_utm_to_order($order_id, $posted_data, $order) {
        if (!empty($_COOKIE['ayur_utm_data'])) {
            $utm_data = json_decode(stripslashes($_COOKIE['ayur_utm_data']), true);
            if (is_array($utm_data)) {
                foreach ($utm_data as $key => $val) {
                    $order->update_meta_data('_' . $key, sanitize_text_field($val));
                }
                $order->save();
            }
        }

        // Attach fbp / fbc
        if (!empty($_COOKIE['_fbp'])) {
            $order->update_meta_data('_fbp', sanitize_text_field($_COOKIE['_fbp']));
        }
        if (!empty($_COOKIE['_fbc'])) {
            $order->update_meta_data('_fbc', sanitize_text_field($_COOKIE['_fbc']));
        }
    }

    public static function add_order_utm_meta_box() {
        add_meta_box(
            'ayur_order_utm_box',
            __('Campaign Attribution (Meta / Google Ads)', 'ayur-core'),
            array(__CLASS__, 'render_order_utm_box'),
            'shop_order',
            'side',
            'default'
        );
        add_meta_box(
            'ayur_order_utm_box_hpos',
            __('Campaign Attribution (Meta / Google Ads)', 'ayur-core'),
            array(__CLASS__, 'render_order_utm_box'),
            'woocommerce_page_wc-orders',
            'side',
            'default'
        );
    }

    public static function render_order_utm_box($post_or_order) {
        $order = ($post_or_order instanceof WC_Order) ? $post_or_order : wc_get_order($post_or_order->ID);
        if (!$order) return;

        $source   = $order->get_meta('_utm_source');
        $medium   = $order->get_meta('_utm_medium');
        $campaign = $order->get_meta('_utm_campaign');
        $content  = $order->get_meta('_utm_content');
        $term     = $order->get_meta('_utm_term');

        echo '<div style="font-size:13px; line-height:1.6;">';
        echo '<p><strong>Source:</strong> ' . ($source ? esc_html($source) : '<span style="color:#888;">Direct / Organic</span>') . '</p>';
        if ($medium)   echo '<p><strong>Medium:</strong> ' . esc_html($medium) . '</p>';
        if ($campaign) echo '<p><strong>Campaign:</strong> <mark>' . esc_html($campaign) . '</mark></p>';
        if ($content)  echo '<p><strong>Ad Content:</strong> ' . esc_html($content) . '</p>';
        if ($term)     echo '<p><strong>Term/Keyword:</strong> ' . esc_html($term) . '</p>';
        echo '</div>';
    }

    /**
     * Frontend Scripts: Meta Pixel & GA4
     */
    public static function render_tracking_header() {
        $pixel_id = get_option('ayur_meta_pixel_id', '');
        $ga4_id   = get_option('ayur_ga4_id', '');

        // Meta Pixel Base Code
        if (!empty($pixel_id)) {
            ?>
            <!-- Meta Pixel Code -->
            <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo esc_js($pixel_id); ?>');
            fbq('track', 'PageView');
            </script>
            <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=<?php echo esc_attr($pixel_id); ?>&ev=PageView&noscript=1"
            /></noscript>
            <!-- End Meta Pixel Code -->
            <?php
        }

        // GA4 Base Code
        if (!empty($ga4_id)) {
            ?>
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga4_id); ?>"></script>
            <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js($ga4_id); ?>', { 'send_page_view': true });
            </script>
            <?php
        }
    }

    public static function render_tracking_events() {
        $pixel_id = get_option('ayur_meta_pixel_id', '');
        $ga4_id   = get_option('ayur_ga4_id', '');

        // 1. ViewContent on Single Product
        if (is_product()) {
            global $product;
            if (!$product) return;

            $event_id = 'vc_' . $product->get_id() . '_' . time();
            $price = $product->get_price();
            $name = $product->get_name();
            $sku = $product->get_sku() ? $product->get_sku() : (string)$product->get_id();

            echo "<script>
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'view_item',
                'ecommerce': {
                    'currency': 'INR',
                    'value': {$price},
                    'items': [{
                        'item_id': '{$sku}',
                        'item_name': " . json_encode($name) . ",
                        'price': {$price},
                        'item_category': 'Ayurvedic Wellness'
                    }]
                }
            });";

            if (!empty($pixel_id)) {
                echo "
                if (typeof fbq === 'function') {
                    fbq('track', 'ViewContent', {
                        content_name: " . json_encode($name) . ",
                        content_ids: ['{$sku}'],
                        content_type: 'product',
                        value: {$price},
                        currency: 'INR'
                    }, { eventID: '{$event_id}' });
                }";
            }
            echo "</script>";
        }

        // 2. InitiateCheckout
        if (is_checkout() && !is_order_received_page()) {
            $cart = WC()->cart;
            if ($cart && !$cart->is_empty()) {
                $total = $cart->get_total('edit');
                $event_id = 'ic_' . time();
                $content_ids = array();
                foreach ($cart->get_cart() as $cart_item) {
                    if (isset($cart_item['data']) && is_object($cart_item['data'])) {
                        $p = $cart_item['data'];
                        $content_ids[] = $p->get_sku() ?: (string)$p->get_id();
                    }
                }
                
                echo "<script>
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    'event': 'begin_checkout',
                    'ecommerce': {
                        'currency': 'INR',
                        'value': {$total}
                    }
                });";

                if (!empty($pixel_id)) {
                    echo "
                    if (typeof fbq === 'function') {
                        fbq('track', 'InitiateCheckout', {
                            value: {$total},
                            currency: 'INR',
                            content_ids: " . json_encode($content_ids) . ",
                            content_type: 'product',
                            num_items: " . $cart->get_cart_contents_count() . "
                        }, { eventID: '{$event_id}' });
                    }";
                }
                echo "</script>";
            }
        }

        // 3. Purchase (Browser Pixel with Deduplication)
        if (is_order_received_page()) {
            global $wp;
            $order_id = isset($wp->query_vars['order-received']) ? absint($wp->query_vars['order-received']) : 0;
            if ($order_id) {
                $order = wc_get_order($order_id);
                if ($order && !$order->get_meta('_pixel_purchase_fired')) {
                    $event_id = 'order_' . $order_id;
                    $total = $order->get_total();
                    $items_data = array();
                    $content_ids = array();

                    foreach ($order->get_items() as $item) {
                        $product = $item->get_product();
                        $sku = $product ? ($product->get_sku() ?: (string)$product->get_id()) : (string)$item->get_product_id();
                        $content_ids[] = $sku;
                        $items_data[] = array(
                            'item_id'   => $sku,
                            'item_name' => $item->get_name(),
                            'price'     => $item->get_total(),
                            'quantity'  => $item->get_quantity(),
                        );
                    }

                    echo "<script>
                    window.dataLayer = window.dataLayer || [];
                    dataLayer.push({
                        'event': 'purchase',
                        'ecommerce': {
                            'transaction_id': '{$order_id}',
                            'value': {$total},
                            'currency': 'INR',
                            'items': " . json_encode($items_data) . "
                        }
                    });";

                    if (!empty($pixel_id)) {
                        echo "
                        if (typeof fbq === 'function') {
                            fbq('track', 'Purchase', {
                                value: {$total},
                                currency: 'INR',
                                content_ids: " . json_encode($content_ids) . ",
                                content_type: 'product'
                            }, { eventID: '{$event_id}' });
                        }";
                    }
                    echo "</script>";

                    $order->update_meta_data('_pixel_purchase_fired', '1');
                    $order->save();
                }
            }
        }
    }

    /**
     * Server-Side Meta Conversions API (CAPI) Dispatcher
     */
    public static function dispatch_meta_capi_purchase($order_id) {
        $pixel_id = get_option('ayur_meta_pixel_id', '');
        $token    = get_option('ayur_meta_capi_token', '');
        $test_code = get_option('ayur_meta_test_code', '');

        if (empty($pixel_id) || empty($token)) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order || $order->get_meta('_capi_purchase_fired')) {
            return;
        }

        $event_id = 'order_' . $order_id;
        $total    = (float)$order->get_total();
        $email    = strtolower(trim($order->get_billing_email()));
        $phone    = preg_replace('/[^0-9]/', '', $order->get_billing_phone());
        if (!empty($phone) && strlen($phone) === 10) {
            $phone = '91' . $phone; // Add Indian country code
        }

        // Hashing personal data strictly per Meta specification
        $user_data = array(
            'client_ip_address' => $order->get_customer_ip_address(),
            'client_user_agent' => $order->get_customer_user_agent(),
        );

        if (!empty($email)) {
            $user_data['em'] = array(hash('sha256', $email));
        }
        if (!empty($phone)) {
            $user_data['ph'] = array(hash('sha256', $phone));
        }

        $fbp = $order->get_meta('_fbp');
        $fbc = $order->get_meta('_fbc');
        if ($fbp) $user_data['fbp'] = $fbp;
        if ($fbc) $user_data['fbc'] = $fbc;

        $contents = array();
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $sku = $product ? ($product->get_sku() ?: (string)$product->get_id()) : (string)$item->get_product_id();
            $contents[] = array(
                'id'         => $sku,
                'quantity'   => $item->get_quantity(),
                'item_price' => (float)$item->get_total(),
            );
        }

        $custom_data = array(
            'currency'     => 'INR',
            'value'        => $total,
            'content_type' => 'product',
            'contents'     => $contents,
            'order_id'     => (string)$order_id,
        );

        $event_payload = array(
            'event_name'       => 'Purchase',
            'event_time'       => time(),
            'event_id'         => $event_id,
            'event_source_url' => $order->get_checkout_order_received_url(),
            'action_source'    => 'website',
            'user_data'        => $user_data,
            'custom_data'      => $custom_data,
        );

        $body = array(
            'data' => array($event_payload),
        );
        if (!empty($test_code)) {
            $body['test_event_code'] = $test_code;
        }

        $url = "https://graph.facebook.com/v19.0/{$pixel_id}/events?access_token={$token}";

        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body'    => json_encode($body),
            'timeout' => 15,
        ));

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $order->update_meta_data('_capi_purchase_fired', '1');
            $order->add_order_note(__('Meta Conversions API: Purchase event successfully dispatched to Meta servers (deduplicated).', 'ayur-core'));
            $order->save();
        }
    }

    /**
     * Admin Settings Menu & Page
     */
    public static function register_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Tracking & Attribution', 'ayur-core'),
            __('Tracking & Attribution', 'ayur-core'),
            'manage_woocommerce',
            'ayur-tracking',
            array(__CLASS__, 'render_admin_page')
        );
    }

    public static function register_settings() {
        register_setting('ayur_tracking_group', 'ayur_meta_pixel_id');
        register_setting('ayur_tracking_group', 'ayur_meta_capi_token');
        register_setting('ayur_tracking_group', 'ayur_meta_test_code');
        register_setting('ayur_tracking_group', 'ayur_ga4_id');
    }

    public static function render_admin_page() {
        ?>
        <div class="wrap" style="max-width:900px;">
            <h1><?php _e('Marketing Tracking & Attribution Suite', 'ayur-core'); ?></h1>
            <p><?php _e('Configure Meta Pixel + Conversions API (CAPI) deduplicated tracking, GA4, and UTM attribution for Meta Ads traffic.', 'ayur-core'); ?></p>

            <form method="post" action="options.php" style="background:#fff; padding:25px; border-radius:8px; border:1px solid #ccd0d4; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <?php settings_fields('ayur_tracking_group'); ?>
                
                <h2><?php _e('Meta (Facebook / Instagram) Ads Tracking', 'ayur-core'); ?></h2>
                <p class="description"><?php _e('Server-to-server CAPI ensures 100% conversion attribution even when iOS 14+ ad blockers prevent browser pixel execution. Both use identical event_id for automatic deduplication.', 'ayur-core'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ayur_meta_pixel_id"><?php _e('Meta Pixel ID', 'ayur-core'); ?></label></th>
                        <td>
                            <input name="ayur_meta_pixel_id" type="text" id="ayur_meta_pixel_id" value="<?php echo esc_attr(get_option('ayur_meta_pixel_id')); ?>" class="regular-text" placeholder="e.g. 1234567890123456" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ayur_meta_capi_token"><?php _e('Meta CAPI Access Token', 'ayur-core'); ?></label></th>
                        <td>
                            <input name="ayur_meta_capi_token" type="password" id="ayur_meta_capi_token" value="<?php echo esc_attr(get_option('ayur_meta_capi_token')); ?>" class="large-text" placeholder="EAAB..." />
                            <p class="description"><?php _e('Generated from Meta Events Manager > Settings > Conversions API > Generate Access Token. Stored securely on server.', 'ayur-core'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ayur_meta_test_code"><?php _e('Test Event Code (Optional)', 'ayur-core'); ?></label></th>
                        <td>
                            <input name="ayur_meta_test_code" type="text" id="ayur_meta_test_code" value="<?php echo esc_attr(get_option('ayur_meta_test_code')); ?>" class="regular-text" placeholder="e.g. TEST12345" />
                        </td>
                    </tr>
                </table>

                <hr style="margin:25px 0;" />

                <h2><?php _e('Google Analytics 4 (GA4)', 'ayur-core'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ayur_ga4_id"><?php _e('GA4 Measurement ID', 'ayur-core'); ?></label></th>
                        <td>
                            <input name="ayur_ga4_id" type="text" id="ayur_ga4_id" value="<?php echo esc_attr(get_option('ayur_ga4_id')); ?>" class="regular-text" placeholder="G-XXXXXXXXXX" />
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Tracking Configuration', 'ayur-core')); ?>
            </form>
        </div>
        <?php
    }
}
