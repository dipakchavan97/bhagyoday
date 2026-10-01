<?php
/**
 * My Addresses Template
 * Premium Bhagyoday Ayurveda Architecture
 */

defined('ABSPATH') || exit;

$customer_id = get_current_user_id();

if (!wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
    $get_addresses = apply_filters(
        'woocommerce_my_account_get_addresses',
        array(
            'billing'  => __('Billing address', 'ayur-wellness'),
            'shipping' => __('Shipping address', 'ayur-wellness'),
        ),
        $customer_id
    );
} else {
    $get_addresses = apply_filters(
        'woocommerce_my_account_get_addresses',
        array(
            'billing' => __('Billing address', 'ayur-wellness'),
        ),
        $customer_id
    );
}
?>

<div class="ayur-addresses-page-wrap">
    <div class="ayur-orders-page-header">
        <h2 class="ayur-orders-title"><?php _e('Saved Addresses', 'ayur-wellness'); ?></h2>
        <p class="ayur-orders-desc"><?php _e('The following addresses will be prefilled automatically on your checkout page.', 'ayur-wellness'); ?></p>
    </div>

    <div class="ayur-addresses-grid">
        <?php foreach ($get_addresses as $name => $address_title) :
            $address = wc_get_account_formatted_address($name);
            $edit_url = wc_get_endpoint_url('edit-address', $name);
        ?>
            <div class="ayur-address-card">
                <div class="ayur-address-card-header">
                    <div class="ayur-address-type-tag">
                        <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon($name === 'billing' ? 'credit-card' : 'truck', '', 16) : ''; ?>
                        <h3><?php echo esc_html($address_title); ?></h3>
                    </div>
                    <a href="<?php echo esc_url($edit_url); ?>" class="ayur-address-edit-link">
                        <?php echo $address ? esc_html__('Edit', 'ayur-wellness') : esc_html__('Add Address', 'ayur-wellness'); ?>
                    </a>
                </div>

                <div class="ayur-address-card-body">
                    <?php if ($address) : ?>
                        <address class="ayur-formatted-address">
                            <?php echo wp_kses_post($address); ?>
                        </address>
                    <?php else : ?>
                        <p class="ayur-no-address-text">
                            <?php esc_html_e('You have not set up this type of address yet.', 'ayur-wellness'); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="ayur-address-card-footer">
                    <a href="<?php echo esc_url($edit_url); ?>" class="ayur-btn-secondary ayur-btn-sm">
                        <?php echo $address ? esc_html__('Edit Details', 'ayur-wellness') : esc_html__('+ Add Address', 'ayur-wellness'); ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
