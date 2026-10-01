<?php
/**
 * My Account Dashboard
 * Premium Bhagyoday Ayurveda Architecture
 */

defined('ABSPATH') || exit;

$user_id       = get_current_user_id();
$current_user  = wp_get_current_user();
$customer_name = $current_user->first_name ? $current_user->first_name : $current_user->display_name;

// Get actual orders for customer
$orders_all = wc_get_orders(array(
    'customer' => $user_id,
    'limit'    => -1,
    'return'   => 'ids',
));
$total_orders = is_array($orders_all) ? count($orders_all) : 0;

$pending_orders = count(wc_get_orders(array(
    'customer' => $user_id,
    'status'   => array('wc-pending', 'wc-on-hold', 'wc-processing'),
    'limit'    => -1,
    'return'   => 'ids',
)));

$completed_orders = count(wc_get_orders(array(
    'customer' => $user_id,
    'status'   => array('wc-completed'),
    'limit'    => -1,
    'return'   => 'ids',
)));

$recent_orders = wc_get_orders(array(
    'customer' => $user_id,
    'limit'    => 5,
));
?>

<div class="ayur-dashboard-wrapper">
    <!-- Welcome Header Banner -->
    <div class="ayur-dashboard-hero-card">
        <div class="ayur-dashboard-hero-content">
            <span class="ayur-dashboard-eyebrow"><?php _e('CUSTOMER DASHBOARD', 'ayur-wellness'); ?></span>
            <h2 class="ayur-dashboard-name"><?php printf(__('Welcome back, %s', 'ayur-wellness'), esc_html($customer_name)); ?></h2>
            <p class="ayur-dashboard-subtitle"><?php _e('Manage your wellness orders, delivery addresses, and personal account details in one place.', 'ayur-wellness'); ?></p>
        </div>
        <div class="ayur-dashboard-hero-badge" aria-hidden="true">
            <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('leaf', '', 36) : ''; ?>
        </div>
    </div>

    <!-- 4 Compact Stat Cards (Part 18) -->
    <div class="ayur-dashboard-stats-grid">
        <div class="ayur-stat-card">
            <div class="ayur-stat-icon-wrap" aria-hidden="true">
                <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('package', '', 20) : ''; ?>
            </div>
            <div class="ayur-stat-info">
                <span class="ayur-stat-label"><?php _e('TOTAL ORDERS', 'ayur-wellness'); ?></span>
                <strong class="ayur-stat-value"><?php echo esc_html($total_orders); ?></strong>
            </div>
        </div>

        <div class="ayur-stat-card">
            <div class="ayur-stat-icon-wrap" aria-hidden="true">
                <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('clock', '', 20) : ''; ?>
            </div>
            <div class="ayur-stat-info">
                <span class="ayur-stat-label"><?php _e('IN PROGRESS', 'ayur-wellness'); ?></span>
                <strong class="ayur-stat-value"><?php echo esc_html($pending_orders); ?></strong>
            </div>
        </div>

        <div class="ayur-stat-card">
            <div class="ayur-stat-icon-wrap" aria-hidden="true">
                <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('shield-check', '', 20) : ''; ?>
            </div>
            <div class="ayur-stat-info">
                <span class="ayur-stat-label"><?php _e('DELIVERED', 'ayur-wellness'); ?></span>
                <strong class="ayur-stat-value"><?php echo esc_html($completed_orders); ?></strong>
            </div>
        </div>

        <div class="ayur-stat-card">
            <div class="ayur-stat-icon-wrap" aria-hidden="true">
                <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('badge-check', '', 20) : ''; ?>
            </div>
            <div class="ayur-stat-info">
                <span class="ayur-stat-label"><?php _e('ACCOUNT STATUS', 'ayur-wellness'); ?></span>
                <strong class="ayur-stat-value ayur-stat-badge"><?php _e('Active Member', 'ayur-wellness'); ?></strong>
            </div>
        </div>
    </div>

    <!-- Recent Orders Section (Part 19) -->
    <div class="ayur-dashboard-section">
        <div class="ayur-dashboard-section-header">
            <h3 class="ayur-dashboard-section-title"><?php _e('Recent Orders', 'ayur-wellness'); ?></h3>
            <?php if ($total_orders > 0) : ?>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="ayur-view-all-link">
                    <span><?php _e('View All', 'ayur-wellness'); ?></span>
                    <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('chevron-right', '', 12) : ''; ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($recent_orders)) : ?>
            <!-- Desktop Orders Table -->
            <div class="ayur-orders-table-wrap ayur-desktop-only">
                <table class="ayur-orders-table">
                    <thead>
                        <tr>
                            <th><?php _e('Order', 'ayur-wellness'); ?></th>
                            <th><?php _e('Date', 'ayur-wellness'); ?></th>
                            <th><?php _e('Status', 'ayur-wellness'); ?></th>
                            <th><?php _e('Total', 'ayur-wellness'); ?></th>
                            <th class="ayur-text-right"><?php _e('Action', 'ayur-wellness'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order) :
                            $order_id = $order->get_id();
                            $status   = $order->get_status();
                            $date     = wc_format_datetime($order->get_date_created(), 'd M Y');
                            $total    = $order->get_formatted_order_total();
                            $url      = $order->get_view_order_url();
                        ?>
                            <tr>
                                <td class="ayur-order-num"><strong>#<?php echo esc_html($order_id); ?></strong></td>
                                <td><?php echo esc_html($date); ?></td>
                                <td><span class="ayur-status-pill ayur-status-<?php echo esc_attr($status); ?>"><?php echo esc_html(wc_get_order_status_name($status)); ?></span></td>
                                <td><?php echo wp_kses_post($total); ?></td>
                                <td class="ayur-text-right">
                                    <a href="<?php echo esc_url($url); ?>" class="ayur-btn-view-order"><?php _e('View Order', 'ayur-wellness'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Orders Cards -->
            <div class="ayur-orders-mobile-list ayur-mobile-only">
                <?php foreach ($recent_orders as $order) :
                    $order_id = $order->get_id();
                    $status   = $order->get_status();
                    $date     = wc_format_datetime($order->get_date_created(), 'd M Y');
                    $total    = $order->get_formatted_order_total();
                    $url      = $order->get_view_order_url();
                ?>
                    <div class="ayur-order-mobile-card">
                        <div class="ayur-order-mobile-card-top">
                            <div>
                                <span class="ayur-order-mobile-id">#<?php echo esc_html($order_id); ?></span>
                                <span class="ayur-order-mobile-date"><?php echo esc_html($date); ?></span>
                            </div>
                            <span class="ayur-status-pill ayur-status-<?php echo esc_attr($status); ?>"><?php echo esc_html(wc_get_order_status_name($status)); ?></span>
                        </div>
                        <div class="ayur-order-mobile-card-bottom">
                            <div class="ayur-order-mobile-total">
                                <span><?php _e('Total Amount', 'ayur-wellness'); ?></span>
                                <strong><?php echo wp_kses_post($total); ?></strong>
                            </div>
                            <a href="<?php echo esc_url($url); ?>" class="ayur-btn-view-order"><?php _e('View Order', 'ayur-wellness'); ?></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <!-- Clean Empty State (Part 18) -->
            <div class="ayur-dashboard-empty-card">
                <div class="ayur-empty-icon" aria-hidden="true">
                    <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('package', '', 36) : ''; ?>
                </div>
                <h4><?php _e('No orders yet', 'ayur-wellness'); ?></h4>
                <p><?php _e('Explore our authentic Ayurvedic wellness collection and start your holistic health journey today.', 'ayur-wellness'); ?></p>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-btn-primary ayur-btn-shop-now">
                    <span><?php _e('SHOP NOW', 'ayur-wellness'); ?></span>
                    <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('arrow-right', '', 14) : ''; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Shortcuts & Dedicated Customer Support -->
    <div class="ayur-dashboard-grid-two">
        <div class="ayur-dashboard-card">
            <div class="ayur-dashboard-card-header">
                <div class="ayur-card-icon" aria-hidden="true"><?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('map-pin', '', 18) : ''; ?></div>
                <h4><?php _e('Saved Addresses', 'ayur-wellness'); ?></h4>
            </div>
            <p><?php _e('Manage your primary billing and shipping destinations for speedy checkout.', 'ayur-wellness'); ?></p>
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="ayur-card-action-link">
                <span><?php _e('Manage Addresses', 'ayur-wellness'); ?></span>
                <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('arrow-right', '', 12) : ''; ?>
            </a>
        </div>

        <div class="ayur-dashboard-card">
            <div class="ayur-dashboard-card-header">
                <div class="ayur-card-icon" aria-hidden="true"><?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('headphones', '', 18) : ''; ?></div>
                <h4><?php _e('Ayurvedic Support', 'ayur-wellness'); ?></h4>
            </div>
            <p><?php _e('Have questions about dosage or transit? Reach our herbal experts directly.', 'ayur-wellness'); ?></p>
            <div class="ayur-support-contacts">
                <a href="https://wa.me/918888686858" target="_blank" rel="noopener noreferrer" class="ayur-support-pill">
                    <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('whatsapp', '', 14) : ''; ?>
                    <span>+91 8888686858</span>
                </a>
                <a href="mailto:vijaykulal8833@gmail.com" class="ayur-support-pill">
                    <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('mail', '', 14) : ''; ?>
                    <span>Email Support</span>
                </a>
            </div>
        </div>
    </div>
</div>
