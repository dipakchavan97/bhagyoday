<?php
/**
 * Footer template for Ayur Wellness
 * Premium Authentic Indian Ayurvedic D2C Brand Footer & Mobile App Navigation
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<footer class="ayur-footer">
    <!-- Bottom Trust Bar -->
    <div class="ayur-trust-bar-section">
        <div class="ayur-container">
            <div class="ayur-trust-grid">
                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon-box">
                        <?php echo ayur_svg_icon('leaf', '', 22, 1.75); ?>
                    </div>
                    <div class="ayur-trust-text-box">
                        <div class="ayur-trust-title"><?php _e('Classical Ayurveda', 'ayur-wellness'); ?></div>
                        <div class="ayur-trust-desc"><?php _e('Traditional Ayurvedic botanicals & extracts', 'ayur-wellness'); ?></div>
                    </div>
                </div>

                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon-box">
                        <?php echo ayur_svg_icon('shield-check', '', 22, 1.75); ?>
                    </div>
                    <div class="ayur-trust-text-box">
                        <div class="ayur-trust-title"><?php _e('Quality Focused', 'ayur-wellness'); ?></div>
                        <div class="ayur-trust-desc"><?php _e('Standardized herbs & zero harsh fillers', 'ayur-wellness'); ?></div>
                    </div>
                </div>

                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon-box">
                        <?php echo ayur_svg_icon('truck', '', 22, 1.75); ?>
                    </div>
                    <div class="ayur-trust-text-box">
                        <div class="ayur-trust-title"><?php _e('Pan-India Delivery', 'ayur-wellness'); ?></div>
                        <div class="ayur-trust-desc"><?php _e('Free shipping on orders above ₹999', 'ayur-wellness'); ?></div>
                    </div>
                </div>

                <div class="ayur-trust-item">
                    <div class="ayur-trust-icon-box">
                        <?php echo ayur_svg_icon('credit-card', '', 22, 1.75); ?>
                    </div>
                    <div class="ayur-trust-text-box">
                        <div class="ayur-trust-title"><?php _e('COD & Secure UPI', 'ayur-wellness'); ?></div>
                        <div class="ayur-trust-desc"><?php _e('Encrypted & verified Indian payment methods', 'ayur-wellness'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Editorial Footer -->
    <div class="ayur-footer-main">
        <div class="ayur-container">
            <div class="ayur-footer-grid">
                <!-- Brand & Sourcing Philosophy -->
                <div class="ayur-footer-col ayur-footer-col-brand">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-brand ayur-footer-brand">
                        <span class="ayur-brand-icon"><?php echo ayur_svg_icon('leaf', '', 20); ?></span>
                        <span class="ayur-brand-text-wrap">
                            <span class="ayur-brand-name">BHAGYODAY</span>
                            <span class="ayur-brand-tagline">AYURVEDA</span>
                        </span>
                    </a>
                    <p class="ayur-footer-tagline-text">
                        <?php _e('Nourishing Lives, Naturally. Thoughtfully curated range of premium Ayurvedic formulations and wellness supplements, crafted with pure botanicals to support your journey toward holistic vitality.', 'ayur-wellness'); ?>
                    </p>
                    <div class="ayur-footer-badge-strip">
                        <span class="ayur-footer-chip"><?php echo ayur_svg_icon('badge-check', '', 14); ?> <?php _e('Classical Ayurvedic Heritage', 'ayur-wellness'); ?></span>
                        <span class="ayur-footer-chip"><?php echo ayur_svg_icon('map-pin', '', 14); ?> <?php _e('Mantha, Maharashtra Hub', 'ayur-wellness'); ?></span>
                    </div>
                </div>

                <!-- Explore Formulations -->
                <div class="ayur-footer-col">
                    <h4 class="ayur-footer-heading"><?php _e('Formulations', 'ayur-wellness'); ?></h4>
                    <ul class="ayur-footer-links">
                        <li><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php _e('All Formulations', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>"><?php _e("Men's Vitality (Shilajit 800mg)", 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>"><?php _e('Metabolism Care (Slim Care Juice)', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>"><?php _e('Digestive Comfort (Piles Care)', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>"><?php _e('Cardiovascular Support (Arjuna Amrit)', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>"><?php _e('Joint Mobility (Shallaki 500mg)', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>"><?php _e("Women's Rhythm (Shatavari Ashoka)", 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>"><?php _e('Curated Value Bundles', 'ayur-wellness'); ?></a></li>
                    </ul>
                </div>

                <!-- Customer Trust & Governance -->
                <div class="ayur-footer-col">
                    <h4 class="ayur-footer-heading"><?php _e('Customer Trust', 'ayur-wellness'); ?></h4>
                    <ul class="ayur-footer-links">
                        <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>"><?php _e('Our Sourcing & Philosophy', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(home_url('/shipping-policy/')); ?>"><?php _e('Shipping & Delivery Policy', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(home_url('/refund-policy/')); ?>"><?php _e('7-Day Refund & Return Policy', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>"><?php _e('Terms & Conditions', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php _e('Privacy & Data Protection', 'ayur-wellness'); ?></a></li>
                        <li><a href="<?php echo esc_url(function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/')); ?>"><?php _e('My Account / Order History', 'ayur-wellness'); ?></a></li>
                    </ul>
                </div>

                <!-- Direct Care & Location -->
                <div class="ayur-footer-col">
                    <h4 class="ayur-footer-heading"><?php _e('Customer Support', 'ayur-wellness'); ?></h4>
                    <p class="ayur-footer-help-intro">
                        <?php _e('Questions regarding formulation selection, daily dosage, or live order dispatch?', 'ayur-wellness'); ?>
                    </p>
                    <div class="ayur-footer-contact-list">
                        <div class="ayur-contact-item">
                            <span class="ayur-contact-icon"><?php echo ayur_svg_icon('whatsapp', '', 16); ?></span>
                            <a href="https://wa.me/918888686858" target="_blank" rel="noopener noreferrer">+91 8888686858</a>
                        </div>
                        <div class="ayur-contact-item">
                            <span class="ayur-contact-icon"><?php echo ayur_svg_icon('phone', '', 16); ?></span>
                            <a href="tel:+918888686858">+91 8888686858</a>
                        </div>
                        <div class="ayur-contact-item">
                            <span class="ayur-contact-icon"><?php echo ayur_svg_icon('mail', '', 16); ?></span>
                            <a href="mailto:vijaykulal8833@gmail.com">vijaykulal8833@gmail.com</a>
                        </div>
                        <div class="ayur-contact-item">
                            <span class="ayur-contact-icon"><?php echo ayur_svg_icon('map-pin', '', 16); ?></span>
                            <span><?php _e('Infront of Nagar Panchayat Civil Hospital, Mantha, Jalna, Maharashtra 431504', 'ayur-wellness'); ?></span>
                        </div>
                        <div class="ayur-contact-item">
                            <span class="ayur-contact-icon"><?php echo ayur_svg_icon('clock', '', 16); ?></span>
                            <span><?php _e('Monday – Saturday (9:00 AM – 7:00 PM IST)', 'ayur-wellness'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom / Disclaimers -->
            <div class="ayur-footer-bottom-bar">
                <div class="ayur-footer-legal-row">
                    <p class="ayur-copy-text">&copy; <?php echo date('Y'); ?> Bhagyoday Ayurveda. <?php _e('All rights reserved.', 'ayur-wellness'); ?></p>
                    <p class="ayur-statutory-disclaimer">
                        <?php _e('*Statutory Advisory: Classical Ayurvedic botanical preparations and dietary supplements are formulated to maintain balance and support holistic bodily wellness. They are not intended to replace professional medical diagnosis or serve as prescription medication for acute diseases. If you have medical concerns or are pregnant, consult a qualified physician.', 'ayur-wellness'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Interactive Slide-In Cart Drawer (Desktop & Mobile) -->
<div class="ayur-cart-drawer-wrap" id="ayurCartDrawerWrap" aria-hidden="true">
    <div class="ayur-drawer-backdrop" id="ayurCartDrawerBackdrop"></div>
    <div class="ayur-cart-drawer" id="ayurCartDrawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Shopping Cart Drawer', 'ayur-wellness'); ?>">
        <?php echo ayur_get_cart_drawer_content_html(); ?>
    </div>
</div>

<!-- Mobile Native App Bottom Navigation Bar (Fixed 64px) -->
<nav class="ayur-mobile-bottom-nav" aria-label="<?php esc_attr_e('Mobile Navigation', 'ayur-wellness'); ?>">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-bottom-nav-item <?php echo is_front_page() ? 'active' : ''; ?>">
        <?php echo ayur_svg_icon('home', '', 20); ?>
        <span><?php _e('Home', 'ayur-wellness'); ?></span>
    </a>
    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-bottom-nav-item <?php echo (function_exists('is_shop') && (is_shop() || is_product_taxonomy())) ? 'active' : ''; ?>">
        <?php echo ayur_svg_icon('grid', '', 20); ?>
        <span><?php _e('Shop', 'ayur-wellness'); ?></span>
    </a>
    <button type="button" class="ayur-bottom-nav-item" id="ayurBottomNavSearch" aria-label="<?php esc_attr_e('Search Catalog', 'ayur-wellness'); ?>">
        <?php echo ayur_svg_icon('search', '', 20); ?>
        <span><?php _e('Search', 'ayur-wellness'); ?></span>
    </button>
    <button type="button" class="ayur-bottom-nav-item ayur-cart-trigger" aria-label="<?php esc_attr_e('Open Shopping Cart', 'ayur-wellness'); ?>">
        <div class="ayur-bottom-nav-cart-icon-wrap">
            <?php echo ayur_svg_icon('shopping-bag', '', 20); ?>
            <span class="ayur-cart-badge"><?php echo (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0; ?></span>
        </div>
        <span><?php _e('Cart', 'ayur-wellness'); ?></span>
    </button>
    <a href="https://wa.me/918888686858" target="_blank" rel="noopener noreferrer" class="ayur-bottom-nav-item ayur-bottom-consult" aria-label="<?php esc_attr_e('Consult on WhatsApp', 'ayur-wellness'); ?>">
        <?php echo ayur_svg_icon('whatsapp', '', 20); ?>
        <span><?php _e('Consult', 'ayur-wellness'); ?></span>
    </a>
</nav>

<?php wp_footer(); ?>
</body>
</html>
