<?php
/**
 * Unified Ayurveda Commerce Settings Administration
 * Provides a clean, responsive settings interface for Instant Checkout, Address Autocomplete,
 * Prepaid COD Confirmation Fee, and Buy-More-Save Quantity Offers.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Commerce_Settings {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }

    public static function register_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Ayurveda Commerce Settings', 'ayur-core'),
            __('Ayurveda Commerce', 'ayur-core'),
            'manage_woocommerce',
            'ayur-commerce-settings',
            array(__CLASS__, 'render_admin_page')
        );
    }

    public static function register_settings() {
        // Instant Checkout
        register_setting('ayur_commerce_group', 'ayur_instant_checkout_enabled');

        // Address Autocomplete
        register_setting('ayur_commerce_group', 'ayur_address_autocomplete_enabled');
        register_setting('ayur_commerce_group', 'ayur_places_api_key');
        register_setting('ayur_commerce_group', 'ayur_address_country_restriction');

        // COD Confirmation
        register_setting('ayur_commerce_group', 'ayur_cod_confirmation_enabled');
        register_setting('ayur_commerce_group', 'ayur_cod_confirmation_fee');
        register_setting('ayur_commerce_group', 'ayur_cod_fee_label');
        register_setting('ayur_commerce_group', 'ayur_cod_razorpay_key_id');
        register_setting('ayur_commerce_group', 'ayur_cod_razorpay_key_secret');

        // Quantity Offers
        register_setting('ayur_commerce_group', 'ayur_quantity_offers_enabled');
        register_setting('ayur_commerce_group', 'ayur_quantity_offer_tiers', array(
            'sanitize_callback' => array(__CLASS__, 'sanitize_tiers')
        ));
    }

    public static function sanitize_tiers($input) {
        if (!is_array($input)) return array();
        $clean = array();
        foreach ($input as $row) {
            $min = isset($row['min_qty']) ? absint($row['min_qty']) : 0;
            $pct = isset($row['discount_pct']) ? min(100, max(1, floatval($row['discount_pct']))) : 0;
            if ($min >= 2 && $pct > 0) {
                $clean[] = array('min_qty' => $min, 'discount_pct' => $pct);
            }
        }
        usort($clean, function($a, $b) {
            return $a['min_qty'] - $b['min_qty'];
        });
        return $clean;
    }

    public static function render_admin_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'instant_checkout';
        $tiers = Ayur_Quantity_Offers::get_offer_tiers();
        ?>
        <div class="wrap" style="max-width:960px;">
            <h1 style="color:#173B2F; display:flex; align-items:center; gap:8px;">
                <span>🌿</span> <?php _e('Ayurveda Commerce Conversion Suite', 'ayur-core'); ?>
            </h1>
            <p><?php _e('Configure Instant Checkout, Address Autocomplete, Prepaid COD Confirmation, and Buy-More-Save Offers for Bhagyoday Ayurveda.', 'ayur-core'); ?></p>

            <nav class="nav-tab-wrapper" style="margin-bottom:20px;">
                <a href="?page=ayur-commerce-settings&tab=instant_checkout" class="nav-tab <?php echo $active_tab === 'instant_checkout' ? 'nav-tab-active' : ''; ?>"><?php _e('⚡ Instant Checkout', 'ayur-core'); ?></a>
                <a href="?page=ayur-commerce-settings&tab=address_autofill" class="nav-tab <?php echo $active_tab === 'address_autofill' ? 'nav-tab-active' : ''; ?>"><?php _e('📍 Address Autocomplete', 'ayur-core'); ?></a>
                <a href="?page=ayur-commerce-settings&tab=cod_confirmation" class="nav-tab <?php echo $active_tab === 'cod_confirmation' ? 'nav-tab-active' : ''; ?>"><?php _e('💵 COD Confirmation Fee', 'ayur-core'); ?></a>
                <a href="?page=ayur-commerce-settings&tab=quantity_offers" class="nav-tab <?php echo $active_tab === 'quantity_offers' ? 'nav-tab-active' : ''; ?>"><?php _e('🏷️ Quantity Offers', 'ayur-core'); ?></a>
            </nav>

            <form method="post" action="options.php" style="background:#fff; padding:24px 28px; border-radius:8px; border:1px solid #ccd0d4; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <?php settings_fields('ayur_commerce_group'); ?>

                <!-- Tab 1: Instant Checkout -->
                <?php if ($active_tab === 'instant_checkout') : ?>
                    <h2 style="color:#173B2F; margin-top:0;"><?php _e('Instant Checkout Configuration', 'ayur-core'); ?></h2>
                    <p class="description"><?php _e('Allows customers clicking "BUY NOW" on product pages or the sticky bar to proceed directly to checkout without visiting the cart page, while safely preserving any existing cart items in the background.', 'ayur-core'); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Enable Instant Checkout', 'ayur-core'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ayur_instant_checkout_enabled" value="yes" <?php checked(get_option('ayur_instant_checkout_enabled', 'yes'), 'yes'); ?> />
                                    <?php _e('Activate 1-Click Instant Checkout for simple and bundle products', 'ayur-core'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>

                <!-- Tab 2: Address Autocomplete -->
                <?php if ($active_tab === 'address_autofill') : ?>
                    <h2 style="color:#173B2F; margin-top:0;"><?php _e('Address Autocomplete & PIN Code Intelligence', 'ayur-core'); ?></h2>
                    <p class="description"><?php _e('Enables Google Places autocomplete for Indian addresses and PIN code intelligence (auto-populating State and City). Checkout seamlessly falls back to standard manual fields if the API is offline or unconfigured.', 'ayur-core'); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Enable Address Autocomplete', 'ayur-core'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ayur_address_autocomplete_enabled" value="yes" <?php checked(get_option('ayur_address_autocomplete_enabled', 'yes'), 'yes'); ?> />
                                    <?php _e('Show address search bar above shipping fields at checkout', 'ayur-core'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ayur_places_api_key"><?php _e('Google Places API Key', 'ayur-core'); ?></label></th>
                            <td>
                                <input type="password" id="ayur_places_api_key" name="ayur_places_api_key" value="<?php echo esc_attr(get_option('ayur_places_api_key', '')); ?>" class="regular-text" placeholder="AIzaSy..." />
                                <p class="description"><?php _e('Restricted browser API key with Places API enabled. Never expose server secrets.', 'ayur-core'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ayur_address_country_restriction"><?php _e('Country Restriction', 'ayur-core'); ?></label></th>
                            <td>
                                <input type="text" id="ayur_address_country_restriction" name="ayur_address_country_restriction" value="<?php echo esc_attr(get_option('ayur_address_country_restriction', 'in')); ?>" class="small-text" readonly />
                                <span class="description"><?php _e('Restricted to India ("in") for accurate domestic deliveries.', 'ayur-core'); ?></span>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>

                <!-- Tab 3: COD Confirmation Fee -->
                <?php if ($active_tab === 'cod_confirmation') : ?>
                    <h2 style="color:#173B2F; margin-top:0;"><?php _e('Prepaid ₹100 COD Confirmation Fee', 'ayur-core'); ?></h2>
                    <p class="description"><?php _e('Requires customers choosing Cash on Delivery to pay an online confirmation fee (default ₹100) via Razorpay. The order is only created upon server signature verification, with the remaining balance due upon delivery.', 'ayur-core'); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Enable COD Confirmation Fee', 'ayur-core'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ayur_cod_confirmation_enabled" value="yes" <?php checked(get_option('ayur_cod_confirmation_enabled', 'yes'), 'yes'); ?> />
                                    <?php _e('Enforce prepaid confirmation fee for Cash on Delivery orders', 'ayur-core'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ayur_cod_confirmation_fee"><?php _e('Confirmation Fee Amount (₹)', 'ayur-core'); ?></label></th>
                            <td>
                                <input type="number" id="ayur_cod_confirmation_fee" name="ayur_cod_confirmation_fee" value="<?php echo esc_attr(get_option('ayur_cod_confirmation_fee', 100)); ?>" class="small-text" min="1" step="1" />
                                <p class="description"><?php _e('Configurable amount paid online now (Default: ₹100).', 'ayur-core'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ayur_cod_fee_label"><?php _e('Fee Label', 'ayur-core'); ?></label></th>
                            <td>
                                <input type="text" id="ayur_cod_fee_label" name="ayur_cod_fee_label" value="<?php echo esc_attr(get_option('ayur_cod_fee_label', __('COD Confirmation Fee', 'ayur-core'))); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ayur_cod_razorpay_key_id"><?php _e('Razorpay Key ID (Optional Override)', 'ayur-core'); ?></label></th>
                            <td>
                                <input type="text" id="ayur_cod_razorpay_key_id" name="ayur_cod_razorpay_key_id" value="<?php echo esc_attr(get_option('ayur_cod_razorpay_key_id', '')); ?>" class="regular-text" placeholder="Inherited from WooCommerce Razorpay plugin if blank" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="ayur_cod_razorpay_key_secret"><?php _e('Razorpay Key Secret (Optional Override)', 'ayur-core'); ?></label></th>
                            <td>
                                <input type="password" id="ayur_cod_razorpay_key_secret" name="ayur_cod_razorpay_key_secret" value="<?php echo esc_attr(get_option('ayur_cod_razorpay_key_secret', '')); ?>" class="regular-text" placeholder="Inherited from WooCommerce Razorpay plugin if blank" />
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>

                <!-- Tab 4: Quantity Offers -->
                <?php if ($active_tab === 'quantity_offers') : ?>
                    <h2 style="color:#173B2F; margin-top:0;"><?php _e('Buy-More-Save Quantity Offers', 'ayur-core'); ?></h2>
                    <p class="description"><?php _e('Configure tiered quantity discounts (e.g. Buy 2 → 10% OFF, Buy 4 → 20% OFF). Automatically applied to WooCommerce cart totals and shown on product pages and cart drawer.', 'ayur-core'); ?></p>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Enable Quantity Offers', 'ayur-core'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="ayur_quantity_offers_enabled" value="yes" <?php checked(get_option('ayur_quantity_offers_enabled', 'yes'), 'yes'); ?> />
                                    <?php _e('Enable Buy-More-Save tiered discounts storewide', 'ayur-core'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>

                    <h3><?php _e('Offer Tiers', 'ayur-core'); ?></h3>
                    <table class="widefat fixed striped" id="ayurTiersTable" style="max-width:600px; margin-bottom:15px;">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th><?php _e('Minimum Quantity', 'ayur-core'); ?></th>
                                <th><?php _e('Discount Percentage (%)', 'ayur-core'); ?></th>
                                <th style="width:70px;"><?php _e('Action', 'ayur-core'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tiers as $idx => $t) : ?>
                                <tr>
                                    <td><?php echo $idx + 1; ?></td>
                                    <td>
                                        <input type="number" name="ayur_quantity_offer_tiers[<?php echo $idx; ?>][min_qty]" value="<?php echo esc_attr($t['min_qty']); ?>" min="2" class="small-text" required />
                                        <span class="description"><?php printf(__('Buy %d+ units', 'ayur-core'), $t['min_qty']); ?></span>
                                    </td>
                                    <td>
                                        <input type="number" name="ayur_quantity_offer_tiers[<?php echo $idx; ?>][discount_pct]" value="<?php echo esc_attr($t['discount_pct']); ?>" min="1" max="100" class="small-text" required /> %
                                    </td>
                                    <td>
                                        <button type="button" class="button button-link-delete ayur-remove-tier" <?php echo count($tiers) <= 1 ? 'disabled' : ''; ?>><?php _e('Delete', 'ayur-core'); ?></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button type="button" class="button" id="ayurAddTierBtn">+ <?php _e('Add Offer Tier', 'ayur-core'); ?></button>

                    <script>
                    jQuery(document).ready(function($) {
                        $('#ayurAddTierBtn').on('click', function() {
                            var tbody = $('#ayurTiersTable tbody');
                            var rowCount = tbody.find('tr').length;
                            var newRow = '<tr>' +
                                '<td>' + (rowCount + 1) + '</td>' +
                                '<td><input type="number" name="ayur_quantity_offer_tiers[' + rowCount + '][min_qty]" value="' + ((rowCount + 1) * 2) + '" min="2" class="small-text" required /> <span class="description">units</span></td>' +
                                '<td><input type="number" name="ayur_quantity_offer_tiers[' + rowCount + '][discount_pct]" value="15" min="1" max="100" class="small-text" required /> %</td>' +
                                '<td><button type="button" class="button button-link-delete ayur-remove-tier">Delete</button></td>' +
                            '</tr>';
                            tbody.append(newRow);
                        });

                        $(document).on('click', '.ayur-remove-tier', function() {
                            if ($('#ayurTiersTable tbody tr').length > 1) {
                                $(this).closest('tr').remove();
                            }
                        });
                    });
                    </script>
                <?php endif; ?>

                <?php submit_button(__('Save Settings', 'ayur-core'), 'primary', 'submit', true, array('style' => 'background:#173B2F; border-color:#173B2F;')); ?>
            </form>
        </div>
        <?php
    }
}
