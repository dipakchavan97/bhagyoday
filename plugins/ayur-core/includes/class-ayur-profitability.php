<?php
/**
 * Admin Profitability & Contribution Margin Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Profitability {

    public static function init() {
        add_action('wp_dashboard_setup', array(__CLASS__, 'add_profitability_dashboard_widget'));
    }

    public static function add_profitability_dashboard_widget() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        wp_add_dashboard_widget(
            'ayur_d2c_profitability_widget',
            __('Ayurvedic D2C Unit Economics & Contribution Dashboard', 'ayur-core'),
            array(__CLASS__, 'render_profitability_widget')
        );
    }

    public static function render_profitability_widget() {
        // Query recent 30 days orders
        $orders = wc_get_orders(array(
            'limit'        => 100,
            'status'       => array('completed', 'processing'),
            'date_created' => '>' . (time() - (30 * 86400)),
        ));

        $total_revenue = 0;
        $order_count = count($orders);
        $total_cogs = 0;

        foreach ($orders as $order) {
            $total_revenue += (float)$order->get_total();
            
            // Calculate COGS
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                if ($product) {
                    $cogs = (float)get_post_meta($product->get_id(), '_ayur_cost_price', true);
                    if ($cogs <= 0) {
                        // Standard Ayurvedic formulation default: 35% of product price
                        $cogs = (float)$product->get_price() * 0.35;
                    }
                    $total_cogs += ($cogs * $item->get_quantity());
                }
            }
        }

        $pg_fees = $total_revenue * 0.02; // Standard 2% Razorpay fee
        $shipping_costs = $order_count * 70; // Standard ₹70 Shiprocket avg
        $gross_contribution = $total_revenue - $total_cogs - $pg_fees - $shipping_costs;
        $contribution_margin_pct = ($total_revenue > 0) ? round(($gross_contribution / $total_revenue) * 100, 1) : 0;
        $aov = ($order_count > 0) ? round($total_revenue / $order_count) : 0;

        ?>
        <div style="font-family: inherit;">
            <p style="color: #666; margin-top: 0;">
                <?php _e('Real-time 30-day unit economics based on verified orders and Ayurvedic formulation COGS.', 'ayur-core'); ?>
            </p>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 15px;">
                <div style="background: #F0FDF4; border: 1px solid #BBF7D0; padding: 12px; border-radius: 8px;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #166534; font-weight: 600;">Total Revenue</div>
                    <div style="font-size: 20px; font-weight: 700; color: #14532D;">₹<?php echo number_format($total_revenue); ?></div>
                    <div style="font-size: 11px; color: #15803D;"><?php echo $order_count; ?> Orders (AOV: ₹<?php echo number_format($aov); ?>)</div>
                </div>

                <div style="background: #FEF3C7; border: 1px solid #FDE68A; padding: 12px; border-radius: 8px;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #92400E; font-weight: 600;">Contribution Margin</div>
                    <div style="font-size: 20px; font-weight: 700; color: #78350F;"><?php echo $contribution_margin_pct; ?>%</div>
                    <div style="font-size: 11px; color: #B45309;">₹<?php echo number_format(max(0, $gross_contribution)); ?> Estimated Net Margin</div>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 6px 0; color: #666;">Product COGS / Herbs:</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600;">-₹<?php echo number_format($total_cogs); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 6px 0; color: #666;">Payment Gateway Fees (2% Razorpay):</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600;">-₹<?php echo number_format($pg_fees); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 6px 0; color: #666;">Logistics & Shiprocket Costs:</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 600;">-₹<?php echo number_format($shipping_costs); ?></td>
                </tr>
                <tr style="font-weight: 700; background: #FAF5FF;">
                    <td style="padding: 8px; color: #581C87;">Estimated Contribution Profit:</td>
                    <td style="padding: 8px; text-align: right; color: #581C87;">₹<?php echo number_format(max(0, $gross_contribution)); ?></td>
                </tr>
            </table>
            
            <p style="font-size: 11px; color: #9CA3AF; margin-top: 10px; margin-bottom: 0;">
                * Excludes external ad spend (Meta ROAS). Configure product COGS under product edit screen for exact item-level accuracy.
            </p>
        </div>
        <?php
    }
}
