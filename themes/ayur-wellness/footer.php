<?php
/**
 * Footer template for Ayur Wellness
 * Premium Authentic Indian Ayurvedic D2C Brand Footer & Mobile Navigation
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<footer class="ayur-footer" id="ayurFooter">
    <div class="ayur-footer-main">
        <div class="ayur-container">
            <div class="ayur-footer-grid">
                <!-- 1. Brand Philosophy & Sourcing -->
                <div class="ayur-footer-col ayur-footer-col-brand">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-brand ayur-footer-brand" aria-label="<?php esc_attr_e('Bhagyoday Ayurveda Home', 'ayur-wellness'); ?>">
                        <span class="ayur-brand-name">Bhagyoday</span>
                        <span class="ayur-brand-tagline">Ayurveda</span>
                    </a>
                    <p class="ayur-footer-desc">
                        <?php _e('Rooted in classical Indian herbal wisdom. Thoughtfully formulated botanical extracts and wellness supplements, handcrafted in Maharashtra to support balanced vitality.', 'ayur-wellness'); ?>
                    </p>
                    <div class="ayur-footer-hub-badge">
                        <?php echo ayur_svg_icon('map-pin', '', 14); ?>
                        <span><?php _e('Dispatched directly from Mantha, Jalna, Maharashtra', 'ayur-wellness'); ?></span>
                    </div>
                </div>

                <!-- 2. Formulations / Shop -->
                <div class="ayur-footer-col">
                    <h4 class="ayur-footer-heading"><?php _e('Shop Formulations', 'ayur-wellness'); ?></h4>
                    <ul class="ayur-footer-links">
                        <li><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php _e('All Formulations', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>"><?php _e("Men's Vitality", 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>"><?php _e("Women's Wellness", 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>"><?php _e('Heart & Vitality', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>"><?php _e('Digestive Health', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>"><?php _e('Joint Mobility', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>"><?php _e('Value Bundles', 'ayur-wellness'); ?></a></li>
                    </ul>
                </div>

                <!-- 3. Company & Customer Policies -->
                <div class="ayur-footer-col">
                    <h4 class="ayur-footer-heading"><?php _e('Trust & Policies', 'ayur-wellness'); ?></h4>
                    <ul class="ayur-footer-links">
                        <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>"><?php _e('Our Story & Sourcing', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(home_url('/shipping-policy/')); ?>"><?php _e('Shipping & Delivery', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(home_url('/refund-policy/')); ?>"><?php _e('Returns & Refunds', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php _e('Privacy Policy', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>"><?php _e('Terms & Conditions', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/')); ?>"><?php _e('Customer Account', 'ayur-wellness'); ?></a></li>
                    </ul>
                </div>

                <!-- 4. Customer Support & Contact -->
                <div class="ayur-footer-col ayur-footer-col-contact">
                    <h4 class="ayur-footer-heading"><?php _e('Customer Care', 'ayur-wellness'); ?></h4>
                    <div class="ayur-footer-contact-list">
                        <div class="ayur-contact-line">
                            <span class="ayur-contact-icon"><?php echo ayur_svg_icon('phone', '', 15); ?></span>
                            <a href="tel:+918888686858">+91 8888686858</a>
                        </div>
                        <div class="ayur-contact-line">
                            <span class="ayur-contact-icon"><?php echo ayur_svg_icon('whatsapp', '', 15); ?></span>
                            <a href="https://wa.me/918888686858" target="_blank" rel="noopener noreferrer"><?php _e('WhatsApp Helpline', 'ayur-wellness'); ?></a>
                        </div>
                        <div class="ayur-contact-line">
                            <span class="ayur-contact-icon"><?php echo ayur_svg_icon('mail', '', 15); ?></span>
                            <a href="mailto:vijaykulal8833@gmail.com">vijaykulal8833@gmail.com</a>
                        </div>
                        <div class="ayur-contact-line ayur-contact-address">
                            <span class="ayur-contact-icon"><?php echo ayur_svg_icon('map-pin', '', 15); ?></span>
                            <span><?php _e('Infront of Nagar Panchayat Civil Hospital, Mantha, Jalna, Maharashtra 431504', 'ayur-wellness'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom / Disclaimers -->
            <div class="ayur-footer-bottom">
                <p class="ayur-copyright">&copy; <?php echo date('Y'); ?> Bhagyoday Ayurveda. <?php _e('All rights reserved.', 'ayur-wellness'); ?></p>
                <p class="ayur-statutory-note">
                    <?php _e('*Statutory Disclaimer: Traditional Ayurvedic formulations are dietary herbal supplements designed to support holistic health and balance. They are not intended to diagnose, treat, cure, or prevent any medical disease. Consult a physician before starting any herbal regimen if pregnant or managing medical conditions.', 'ayur-wellness'); ?>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Interactive Slide-In Cart Drawer (Desktop & Mobile - Phase 11) -->
<div class="ayur-cart-drawer-wrap" id="ayurCartDrawerWrap" aria-hidden="true">
    <div class="ayur-drawer-backdrop" id="ayurCartDrawerBackdrop"></div>
    <div class="ayur-cart-drawer" id="ayurCartDrawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Shopping Cart Drawer', 'ayur-wellness'); ?>">
        <?php echo ayur_get_cart_drawer_content_html(); ?>
    </div>
</div>

<?php
// On single product pages, sticky purchase bar is active. On checkout, user is completing order.
// Exclude bottom nav on single-product and checkout pages to ensure clean, distraction-free UX.
$is_single_prod = function_exists('is_product') && is_product();
$is_checkout = (function_exists('is_checkout') && is_checkout()) || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/checkout') !== false);
if (!$is_single_prod && !$is_checkout) :
?>
<!-- Mobile Native App Bottom Navigation Bar (Phase 10: Home, Shop, Search, Account, Cart) -->
<nav class="ayur-mobile-bottom-nav" aria-label="<?php esc_attr_e('Mobile Navigation', 'ayur-wellness'); ?>">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-bottom-nav-item <?php echo is_front_page() ? 'active' : ''; ?>">
        <?php echo ayur_svg_icon('home', '', 20, 1.75); ?>
        <span><?php _e('Home', 'ayur-wellness'); ?></span>
    </a>
    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-bottom-nav-item <?php echo (function_exists('is_shop') && (is_shop() || is_product_taxonomy())) ? 'active' : ''; ?>">
        <?php echo ayur_svg_icon('grid', '', 20, 1.75); ?>
        <span><?php _e('Shop', 'ayur-wellness'); ?></span>
    </a>
    <button type="button" class="ayur-bottom-nav-item" id="ayurBottomNavSearch" aria-label="<?php esc_attr_e('Search Catalog', 'ayur-wellness'); ?>">
        <?php echo ayur_svg_icon('search', '', 20, 1.75); ?>
        <span><?php _e('Search', 'ayur-wellness'); ?></span>
    </button>
    <a href="<?php echo esc_url(function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/')); ?>" class="ayur-bottom-nav-item <?php echo (function_exists('is_account_page') && is_account_page()) ? 'active' : ''; ?>">
        <?php echo ayur_svg_icon('user', '', 20, 1.75); ?>
        <span><?php _e('Account', 'ayur-wellness'); ?></span>
    </a>
    <button type="button" class="ayur-bottom-nav-item ayur-cart-trigger" aria-label="<?php esc_attr_e('Open Shopping Cart', 'ayur-wellness'); ?>">
        <div class="ayur-bottom-cart-badge-wrap">
            <?php echo ayur_svg_icon('shopping-bag', '', 20, 1.75); ?>
            <span class="ayur-cart-badge"><?php echo (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0; ?></span>
        </div>
        <span><?php _e('Cart', 'ayur-wellness'); ?></span>
    </button>
</nav>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
