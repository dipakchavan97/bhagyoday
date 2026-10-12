<?php
/**
 * Address Autofill & Indian Pincode Intelligence Engine
 * Provides Google Places address autocomplete, 6-digit PIN code intelligence,
 * and standard HTML5 browser autofill integration.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Address_Autofill {

    public static function init() {
        // Enforce Manual Address Entry (Phase 4 requirement)
        // Only inject standard HTML5 browser autofill attributes on checkout fields
        add_filter('woocommerce_checkout_fields', array(__CLASS__, 'add_browser_autofill_attributes'), 30);
    }

    public static function is_enabled() {
        return false;
    }

    public static function get_api_key() {
        return trim(get_option('ayur_places_api_key', ''));
    }

    public static function get_country_restriction() {
        return get_option('ayur_address_country_restriction', 'in');
    }

    /**
     * Address scripts are intentionally disabled for clean manual entry.
     */
    public static function enqueue_address_scripts() {
        // Address autofill/autocomplete scripts intentionally removed.
        return;
    }

    /**
     * Set explicit autocomplete attributes on WooCommerce checkout fields:
     * - Name & Email: standard W3C attributes (given-name, family-name, email)
     * - Address & Phone: autocomplete="off" to prevent automated address autofill
     */
    public static function add_browser_autofill_attributes($fields) {
        // Billing fields
        if (isset($fields['billing']['billing_first_name'])) {
            $fields['billing']['billing_first_name']['custom_attributes']['autocomplete'] = 'given-name';
        }
        if (isset($fields['billing']['billing_last_name'])) {
            $fields['billing']['billing_last_name']['custom_attributes']['autocomplete'] = 'family-name';
        }
        if (isset($fields['billing']['billing_email'])) {
            $fields['billing']['billing_email']['custom_attributes']['autocomplete'] = 'email';
        }
        if (isset($fields['billing']['billing_phone'])) {
            $fields['billing']['billing_phone']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['billing']['billing_address_1'])) {
            $fields['billing']['billing_address_1']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['billing']['billing_address_2'])) {
            $fields['billing']['billing_address_2']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['billing']['billing_city'])) {
            $fields['billing']['billing_city']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['billing']['billing_state'])) {
            $fields['billing']['billing_state']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['billing']['billing_postcode'])) {
            $fields['billing']['billing_postcode']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['billing']['billing_country'])) {
            $fields['billing']['billing_country']['custom_attributes']['autocomplete'] = 'off';
        }

        // Shipping fields
        if (isset($fields['shipping']['shipping_first_name'])) {
            $fields['shipping']['shipping_first_name']['custom_attributes']['autocomplete'] = 'given-name';
        }
        if (isset($fields['shipping']['shipping_last_name'])) {
            $fields['shipping']['shipping_last_name']['custom_attributes']['autocomplete'] = 'family-name';
        }
        if (isset($fields['shipping']['shipping_address_1'])) {
            $fields['shipping']['shipping_address_1']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['shipping']['shipping_address_2'])) {
            $fields['shipping']['shipping_address_2']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['shipping']['shipping_city'])) {
            $fields['shipping']['shipping_city']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['shipping']['shipping_state'])) {
            $fields['shipping']['shipping_state']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['shipping']['shipping_postcode'])) {
            $fields['shipping']['shipping_postcode']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['shipping']['shipping_phone'])) {
            $fields['shipping']['shipping_phone']['custom_attributes']['autocomplete'] = 'off';
        }
        if (isset($fields['shipping']['shipping_country'])) {
            $fields['shipping']['shipping_country']['custom_attributes']['autocomplete'] = 'off';
        }

        return $fields;
    }

    /**
     * Fast Indian Pincode Lookup (Offline state dictionary + live postal lookup)
     */
    public static function ajax_lookup_pincode() {
        check_ajax_referer('ayur_address_nonce', 'nonce');

        $pincode = isset($_POST['pincode']) ? sanitize_text_field($_POST['pincode']) : '';
        $pincode = preg_replace('/\D/', '', $pincode);

        if (strlen($pincode) !== 6 || $pincode[0] === '0') {
            wp_send_json_error(array('message' => __('Invalid PIN Code format', 'ayur-core')));
        }

        // 1. Check transient cache
        $cached = get_transient('ayur_pin_' . $pincode);
        if ($cached && is_array($cached)) {
            wp_send_json_success($cached);
        }

        // 2. Offline Zone Mapping (Instant, 0-latency fallback for all 28 states)
        $offline_info = self::resolve_offline_pincode($pincode);

        // 3. Online Postal API query with short timeout (1.5s)
        $city = $offline_info['city'];
        $state = $offline_info['state'];
        $district = $offline_info['district'];

        $api_url = 'https://api.postalpincode.in/pincode/' . $pincode;
        $response = wp_remote_get($api_url, array('timeout' => 2));

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($body) && isset($body[0]['Status']) && $body[0]['Status'] === 'Success' && !empty($body[0]['PostOffice'])) {
                $po = $body[0]['PostOffice'][0];
                if (!empty($po['District'])) {
                    $city = sanitize_text_field($po['District']);
                }
                if (!empty($po['State'])) {
                    $resolved_state = self::map_state_to_code($po['State']);
                    if ($resolved_state) {
                        $state = $resolved_state;
                    }
                }
                if (!empty($po['Block']) && $po['Block'] !== 'NA') {
                    $district = sanitize_text_field($po['Block']);
                }
            }
        }

        $result = array(
            'pincode'  => $pincode,
            'city'     => $city,
            'state'    => $state,
            'district' => $district
        );

        set_transient('ayur_pin_' . $pincode, $result, DAY_IN_SECONDS * 7);
        wp_send_json_success($result);
    }

    /**
     * Offline Indian PIN prefix mapping to ISO-3166-2:IN code
     */
    private static function resolve_offline_pincode($pin) {
        $prefix2 = (int) substr($pin, 0, 2);
        $state = 'MH';
        $city = '';
        $district = '';

        // Mantha, Jalna specific check
        if ($pin === '431504') {
            return array('city' => 'Mantha', 'state' => 'MH', 'district' => 'Jalna');
        }

        if ($prefix2 >= 11 && $prefix2 <= 11) { $state = 'DL'; $city = 'Delhi'; }
        elseif ($prefix2 >= 12 && $prefix2 <= 13) { $state = 'HR'; }
        elseif ($prefix2 >= 14 && $prefix2 <= 16) { $state = 'PB'; }
        elseif ($prefix2 == 17) { $state = 'HP'; }
        elseif ($prefix2 >= 18 && $prefix2 <= 19) { $state = 'JK'; }
        elseif ($prefix2 >= 20 && $prefix2 <= 28) { $state = 'UP'; }
        elseif ($prefix2 >= 30 && $prefix2 <= 34) { $state = 'RJ'; }
        elseif ($prefix2 >= 36 && $prefix2 <= 39) { $state = 'GJ'; }
        elseif ($prefix2 >= 40 && $prefix2 <= 44) { $state = 'MH'; }
        elseif ($prefix2 >= 45 && $prefix2 <= 48) { $state = 'MP'; }
        elseif ($prefix2 == 49) { $state = 'CT'; }
        elseif ($prefix2 >= 50 && $prefix2 <= 53) { $state = 'AP'; }
        elseif ($prefix2 >= 56 && $prefix2 <= 59) { $state = 'KA'; }
        elseif ($prefix2 >= 60 && $prefix2 <= 64) { $state = 'TN'; }
        elseif ($prefix2 >= 67 && $prefix2 <= 69) { $state = 'KL'; }
        elseif ($prefix2 >= 70 && $prefix2 <= 74) { $state = 'WB'; }
        elseif ($prefix2 >= 75 && $prefix2 <= 77) { $state = 'OR'; }
        elseif ($prefix2 == 78) { $state = 'AS'; }
        elseif ($prefix2 == 79) { $state = 'NE'; }
        elseif ($prefix2 >= 80 && $prefix2 <= 85) { $state = 'BR'; }
        elseif ($prefix2 >= 86 && $prefix2 <= 89) { $state = 'JH'; }

        return array('city' => $city, 'state' => $state, 'district' => $district);
    }

    private static function map_state_to_code($state_name) {
        $states = array(
            'Andhra Pradesh' => 'AP',
            'Arunachal Pradesh' => 'AR',
            'Assam' => 'AS',
            'Bihar' => 'BR',
            'Chhattisgarh' => 'CT',
            'Goa' => 'GA',
            'Gujarat' => 'GJ',
            'Haryana' => 'HR',
            'Himachal Pradesh' => 'HP',
            'Jammu and Kashmir' => 'JK',
            'Jharkhand' => 'JH',
            'Karnataka' => 'KA',
            'Kerala' => 'KL',
            'Madhya Pradesh' => 'MP',
            'Maharashtra' => 'MH',
            'Manipur' => 'MN',
            'Meghalaya' => 'ML',
            'Mizoram' => 'MZ',
            'Nagaland' => 'NL',
            'Odisha' => 'OR',
            'Punjab' => 'PB',
            'Rajasthan' => 'RJ',
            'Sikkim' => 'SK',
            'Tamil Nadu' => 'TN',
            'Telangana' => 'TG',
            'Tripura' => 'TR',
            'Uttarakhand' => 'UT',
            'Uttar Pradesh' => 'UP',
            'West Bengal' => 'WB',
            'Delhi' => 'DL'
        );

        return isset($states[$state_name]) ? $states[$state_name] : 'MH';
    }
}
