<?php
/**
 * My Account Navigation
 * Premium Bhagyoday Ayurveda Architecture
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_navigation');
?>

<nav class="woocommerce-MyAccount-navigation ayur-account-nav" aria-label="<?php esc_attr_e('Account navigation', 'ayur-wellness'); ?>">
    <ul class="ayur-account-nav-list">
        <?php foreach (wc_get_account_menu_items() as $endpoint => $label) :
            $icon = 'grid';
            if ($endpoint === 'dashboard') $icon = 'home';
            elseif ($endpoint === 'orders') $icon = 'package';
            elseif ($endpoint === 'downloads') $icon = 'sparkles';
            elseif ($endpoint === 'edit-address') $icon = 'map-pin';
            elseif ($endpoint === 'edit-account') $icon = 'user';
            elseif ($endpoint === 'customer-logout') $icon = 'lock';
        ?>
            <li class="<?php echo wc_get_account_menu_item_classes($endpoint); ?>">
                <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>">
                    <span class="ayur-nav-icon" aria-hidden="true"><?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon($icon, '', 17) : ''; ?></span>
                    <span class="ayur-nav-label"><?php echo esc_html($label); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<?php do_action('woocommerce_after_account_navigation'); ?>
