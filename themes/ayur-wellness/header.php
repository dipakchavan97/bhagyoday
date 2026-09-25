<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Top Announcement Bar -->
<div class="ayur-announcement-bar">
    🌿 Free Express Delivery on Orders Above ₹999 <span>| Code: WELCOME10 for 10% Off</span>
</div>

<!-- Main Sticky Header -->
<header class="ayur-header" id="ayurHeader">
    <div class="ayur-container">
        <div class="ayur-header-inner">
            <!-- Mobile Menu Toggle Button -->
            <button type="button" class="ayur-mobile-menu-btn" id="ayurMobileMenuBtn" aria-label="<?php esc_attr_e('Toggle Menu', 'ayur-wellness'); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <!-- Brand Logo -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-brand">
                <span class="ayur-brand-leaf">🌿</span>
                <div>
                    <div>VEDA WELLNESS</div>
                    <span class="ayur-brand-sub">AUTHENTIC AYURVEDA</span>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="ayur-nav-wrap" aria-label="<?php esc_attr_e('Main Navigation', 'ayur-wellness'); ?>">
                <ul class="ayur-nav-desktop">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>"><?php _e('All Products', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('immunity')); ?>"><?php _e('Immunity', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('hair-care')); ?>"><?php _e('Hair Care', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('skin-care')); ?>"><?php _e('Skin Care', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>"><?php _e('Combos & Kits', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>"><?php _e('Our Story', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact-us/')); ?>"><?php _e('Contact', 'ayur-wellness'); ?></a></li>
                </ul>
            </nav>

            <!-- Header Utilities / Actions -->
            <div class="ayur-header-actions">
                <!-- Search Button -->
                <button type="button" class="ayur-icon-btn" id="ayurSearchToggle" aria-label="<?php esc_attr_e('Search Store', 'ayur-wellness'); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>

                <!-- My Account Icon -->
                <a href="<?php echo esc_url(function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/')); ?>" class="ayur-icon-btn" title="<?php esc_attr_e('Account', 'ayur-wellness'); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>

                <!-- Cart Icon with Badge -->
                <a href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/')); ?>" class="ayur-icon-btn ayur-cart-btn" title="<?php esc_attr_e('Cart', 'ayur-wellness'); ?>">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span class="ayur-cart-badge"><?php echo (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0; ?></span>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="ayur-mobile-drawer" id="ayurMobileDrawer" style="display:none;">
    <div class="ayur-drawer-overlay" id="ayurDrawerOverlay"></div>
    <div class="ayur-drawer-content" style="position:fixed; top:0; left:0; bottom:0; width:300px; background:#fff; z-index:99999; padding:24px; box-shadow:0 0 20px rgba(0,0,0,0.2); overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <span style="font-family:'Cinzel', serif; font-weight:700; color:#1E3A2F;">🌿 MENU</span>
            <button type="button" id="ayurCloseDrawer" style="background:none; border:none; font-size:22px; cursor:pointer;">&times;</button>
        </div>
        <ul style="list-style:none; padding:0; line-height:2.2; font-size:15px; font-weight:600;">
            <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'ayur-wellness'); ?></a></li>
            <li><a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>"><?php _e('All Products', 'ayur-wellness'); ?></a></li>
            <li><a href="<?php echo esc_url(ayur_get_cat_url('immunity')); ?>"><?php _e('Immunity & Vitality', 'ayur-wellness'); ?></a></li>
            <li><a href="<?php echo esc_url(ayur_get_cat_url('hair-care')); ?>"><?php _e('Hair & Scalp Care', 'ayur-wellness'); ?></a></li>
            <li><a href="<?php echo esc_url(ayur_get_cat_url('skin-care')); ?>"><?php _e('Pure Skin Care', 'ayur-wellness'); ?></a></li>
            <li><a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>"><?php _e('Combos & Bundles (Save 35%+)', 'ayur-wellness'); ?></a></li>
            <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>"><?php _e('Our Story', 'ayur-wellness'); ?></a></li>
            <li><a href="<?php echo esc_url(home_url('/contact-us/')); ?>"><?php _e('Customer Care', 'ayur-wellness'); ?></a></li>
        </ul>
        <div style="margin-top:30px; padding-top:20px; border-top:1px solid #eee;">
            <a href="<?php echo esc_url(function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/')); ?>" style="display:block; padding:10px 0; font-weight:600; color:#1E3A2F;">👤 <?php _e('My Account', 'ayur-wellness'); ?></a>
            <a href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/')); ?>" style="display:block; padding:10px 0; font-weight:600; color:#1E3A2F;">🛍️ <?php _e('View Cart', 'ayur-wellness'); ?></a>
        </div>
    </div>
</div>

<!-- Search Overlay Modal -->
<div class="ayur-search-modal" id="ayurSearchModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(22,44,35,0.85); backdrop-filter:blur(6px); z-index:99999; padding:40px 20px;">
    <div style="max-width:600px; margin:60px auto 0 auto; background:#fff; border-radius:12px; padding:30px; position:relative; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
        <button type="button" id="ayurCloseSearch" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
        <h3 style="margin-top:0; font-family:'Cinzel', serif; color:#1E3A2F;"><?php _e('Search Ayurvedic Remedies', 'ayur-wellness'); ?></h3>
        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" style="display:flex; gap:10px; margin-top:15px;">
            <input type="search" name="s" placeholder="<?php esc_attr_e('Search Ashwagandha, Bhringraj, Kumkumadi...', 'ayur-wellness'); ?>" style="flex:1; padding:12px 16px; border:1px solid #ccc; border-radius:6px; font-size:15px;" required autofocus />
            <input type="hidden" name="post_type" value="product" />
            <button type="submit" class="ayur-btn-primary" style="padding:12px 24px; border:none; cursor:pointer;"><?php _e('Search', 'ayur-wellness'); ?></button>
        </form>
    </div>
</div>
