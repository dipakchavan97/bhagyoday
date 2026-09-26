<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Subtle Announcement Bar / Auto-Scrolling Ticker -->
<div class="ayur-announcement-bar notification-bar" role="region" aria-label="<?php esc_attr_e('Announcement', 'ayur-wellness'); ?>">
    <div class="ayur-announcement-viewport notification-viewport">
        <div class="ayur-announcement-track notification-track">
            <div class="ayur-announcement-item notification-item">
                <span class="ayur-announcement-segment"><?php _e('Free Pan-India Delivery on orders above ₹999', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Cash on Delivery Available', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Free Pan-India Delivery on orders above ₹999', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Cash on Delivery Available', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Free Pan-India Delivery on orders above ₹999', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Cash on Delivery Available', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
            </div>
            <div class="ayur-announcement-item notification-item" aria-hidden="true">
                <span class="ayur-announcement-segment"><?php _e('Free Pan-India Delivery on orders above ₹999', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Cash on Delivery Available', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Free Pan-India Delivery on orders above ₹999', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Cash on Delivery Available', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Free Pan-India Delivery on orders above ₹999', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
                <span class="ayur-announcement-segment"><?php _e('Cash on Delivery Available', 'ayur-wellness'); ?></span>
                <span class="ayur-ticker-sep" aria-hidden="true">&bull;</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Sticky Header (Phase 2) -->
<header class="ayur-header" id="ayurHeader">
    <div class="ayur-container">
        <div class="ayur-header-inner">
            <!-- Mobile Menu Toggle Button (Left) -->
            <button type="button" class="ayur-mobile-menu-btn" id="ayurMobileMenuBtn" aria-label="<?php esc_attr_e('Open Navigation Menu', 'ayur-wellness'); ?>" aria-controls="ayurMobileDrawerWrap" aria-expanded="false">
                <?php echo ayur_svg_icon('menu', '', 20, 1.75); ?>
            </button>

            <!-- Brand Wordmark Logo (Aligned Center on Mobile / Left on Desktop) -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-brand" aria-label="<?php esc_attr_e('Bhagyoday Ayurveda Home', 'ayur-wellness'); ?>">
                <span class="ayur-brand-name">Bhagyoday</span>
                <span class="ayur-brand-tagline">Ayurveda</span>
            </a>

            <!-- Desktop Navigation Links (Hidden on Mobile) -->
            <nav class="ayur-nav-wrap" aria-label="<?php esc_attr_e('Primary Navigation', 'ayur-wellness'); ?>">
                <ul class="ayur-nav-desktop">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>" class="<?php echo is_front_page() ? 'active' : ''; ?>"><?php _e('Home', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="<?php echo (function_exists('is_shop') && is_shop()) ? 'active' : ''; ?>"><?php _e('Shop All', 'ayur-wellness'); ?></a></li>
                    <li class="ayur-has-dropdown">
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                            <span><?php _e('Wellness', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('chevron-down', 'ayur-dropdown-arrow', 11, 1.6); ?>
                        </a>
                        <ul class="ayur-nav-sub">
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>"><?php _e("Men's Vitality", 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>"><?php _e('Metabolism & Balance', 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>"><?php _e('Digestive & Gut Comfort', 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>"><?php _e('Heart & Vitality', 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>"><?php _e('Joint Mobility', 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>"><?php _e("Women's Wellness", 'ayur-wellness'); ?></a></li>
                        </ul>
                    </li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>"><?php _e('Bundles', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>"><?php _e('Our Story', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact-us/')); ?>"><?php _e('Contact', 'ayur-wellness'); ?></a></li>
                </ul>
            </nav>

            <!-- Utility Actions (Search & Cart Right) -->
            <div class="ayur-header-actions">
                <!-- Search Button -->
                <button type="button" class="ayur-icon-btn" id="ayurSearchToggle" aria-label="<?php esc_attr_e('Search Catalog', 'ayur-wellness'); ?>" aria-controls="ayurSearchModal" aria-expanded="false">
                    <?php echo ayur_svg_icon('search', '', 19, 1.75); ?>
                </button>

                <!-- My Account Icon (Desktop Only) -->
                <a href="<?php echo esc_url(function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/')); ?>" class="ayur-icon-btn ayur-desktop-account" title="<?php esc_attr_e('Account', 'ayur-wellness'); ?>" aria-label="<?php esc_attr_e('Customer Account', 'ayur-wellness'); ?>">
                    <?php echo ayur_svg_icon('user', '', 19, 1.75); ?>
                </a>

                <!-- Cart Button with Count Badge -->
                <button type="button" class="ayur-icon-btn ayur-cart-btn ayur-cart-trigger" id="ayurHeaderCartBtn" title="<?php esc_attr_e('Shopping Cart', 'ayur-wellness'); ?>" aria-label="<?php esc_attr_e('Open Cart Drawer', 'ayur-wellness'); ?>" aria-controls="ayurCartDrawer" aria-expanded="false">
                    <?php echo ayur_svg_icon('shopping-bag', '', 19, 1.75); ?>
                    <span class="ayur-cart-badge"><?php echo (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0; ?></span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Slide-Out Navigation Drawer (Phase 3) -->
<div class="ayur-mobile-drawer-wrap" id="ayurMobileDrawerWrap" aria-hidden="true">
    <div class="ayur-drawer-backdrop" id="ayurMobileDrawerBackdrop"></div>
    <div class="ayur-mobile-drawer" id="ayurMobileDrawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Navigation Menu', 'ayur-wellness'); ?>">
        <!-- Mobile Drawer Header -->
        <div class="ayur-drawer-header">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-brand ayur-drawer-brand">
                <span class="ayur-brand-name">Bhagyoday</span>
                <span class="ayur-brand-tagline">Ayurveda</span>
            </a>
            <button type="button" class="ayur-drawer-close-btn" id="ayurCloseMobileDrawer" aria-label="<?php esc_attr_e('Close Navigation', 'ayur-wellness'); ?>">
                <?php echo ayur_svg_icon('x', '', 18, 1.75); ?>
            </button>
        </div>

        <!-- Mobile Drawer Content (44px+ tap targets, grouped sections) -->
        <div class="ayur-mobile-drawer-body">
            <!-- Search Input in Drawer -->
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="ayur-mobile-search-form">
                <div class="ayur-mobile-search-input-wrap">
                    <?php echo ayur_svg_icon('search', '', 16, 1.75); ?>
                    <input type="search" name="s" placeholder="<?php esc_attr_e('Search formulations...', 'ayur-wellness'); ?>" required />
                    <input type="hidden" name="post_type" value="product" />
                </div>
            </form>

            <!-- SHOP GROUP -->
            <div class="ayur-drawer-section-label"><?php _e('SHOP', 'ayur-wellness'); ?></div>
            <ul class="ayur-mobile-nav-list">
                <li>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                        <?php echo ayur_svg_icon('grid', '', 16, 1.75); ?>
                        <span><?php _e('All Formulations', 'ayur-wellness'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>">
                        <?php echo ayur_svg_icon('package', '', 16, 1.75); ?>
                        <span><?php _e('Value Bundles', 'ayur-wellness'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>">
                        <?php echo ayur_svg_icon('zap', '', 16, 1.75); ?>
                        <span><?php _e("Men's Vitality", 'ayur-wellness'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>">
                        <?php echo ayur_svg_icon('activity', '', 16, 1.75); ?>
                        <span><?php _e('Metabolism & Balance', 'ayur-wellness'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>">
                        <?php echo ayur_svg_icon('flame', '', 16, 1.75); ?>
                        <span><?php _e('Digestive & Gut Comfort', 'ayur-wellness'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>">
                        <?php echo ayur_svg_icon('heart', '', 16, 1.75); ?>
                        <span><?php _e('Heart & Vitality', 'ayur-wellness'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>">
                        <?php echo ayur_svg_icon('shield-check', '', 16, 1.75); ?>
                        <span><?php _e('Joint Mobility', 'ayur-wellness'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>">
                        <?php echo ayur_svg_icon('sparkles', '', 16, 1.75); ?>
                        <span><?php _e("Women's Wellness", 'ayur-wellness'); ?></span>
                    </a>
                </li>
            </ul>

            <!-- DISCOVER GROUP -->
            <div class="ayur-drawer-section-label"><?php _e('DISCOVER', 'ayur-wellness'); ?></div>
            <ul class="ayur-mobile-nav-list">
                <li>
                    <a href="<?php echo esc_url(home_url('/about-us/')); ?>">
                        <?php echo ayur_svg_icon('leaf', '', 16, 1.75); ?>
                        <span><?php _e('Our Story', 'ayur-wellness'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(home_url('/contact-us/')); ?>">
                        <?php echo ayur_svg_icon('headphones', '', 16, 1.75); ?>
                        <span><?php _e('Customer Care', 'ayur-wellness'); ?></span>
                    </a>
                </li>
            </ul>

            <!-- ACCOUNT GROUP -->
            <div class="ayur-drawer-section-label"><?php _e('ACCOUNT', 'ayur-wellness'); ?></div>
            <ul class="ayur-mobile-nav-list">
                <li>
                    <a href="<?php echo esc_url(function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/')); ?>">
                        <?php echo ayur_svg_icon('user', '', 16, 1.75); ?>
                        <span><?php _e('My Account', 'ayur-wellness'); ?></span>
                    </a>
                </li>
            </ul>

            <!-- SUPPORT GROUP -->
            <div class="ayur-drawer-section-label"><?php _e('SUPPORT', 'ayur-wellness'); ?></div>
            <div class="ayur-mobile-support-box">
                <a href="https://wa.me/918888686858" target="_blank" rel="noopener noreferrer" class="ayur-drawer-support-link">
                    <?php echo ayur_svg_icon('whatsapp', '', 18); ?>
                    <span><?php _e('WhatsApp / Contact', 'ayur-wellness'); ?></span>
                </a>
                <a href="tel:+918888686858" class="ayur-drawer-support-link">
                    <?php echo ayur_svg_icon('phone', '', 16, 1.75); ?>
                    <span>+91 8888686858</span>
                </a>
            </div>

            <!-- Compact Legal Row -->
            <div class="ayur-drawer-legal-row">
                <a href="<?php echo esc_url(home_url('/shipping-policy/')); ?>"><?php _e('Shipping', 'ayur-wellness'); ?></a>
                <span>&bull;</span>
                <a href="<?php echo esc_url(home_url('/refund-policy/')); ?>"><?php _e('Refunds', 'ayur-wellness'); ?></a>
                <span>&bull;</span>
                <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php _e('Privacy', 'ayur-wellness'); ?></a>
                <span>&bull;</span>
                <a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>"><?php _e('Terms', 'ayur-wellness'); ?></a>
            </div>
        </div>
    </div>
</div>

<!-- Full-Width Slide-Down Search Overlay (Phase 12) -->
<div class="ayur-search-modal-wrap" id="ayurSearchModal" aria-hidden="true">
    <div class="ayur-search-backdrop" id="ayurSearchBackdrop"></div>
    <div class="ayur-search-box" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Search Catalog', 'ayur-wellness'); ?>">
        <div class="ayur-container">
            <div class="ayur-search-bar-inner">
                <span class="ayur-search-bar-icon"><?php echo ayur_svg_icon('search', '', 18, 1.75); ?></span>
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="ayur-search-field-form">
                    <input type="search" name="s" id="ayurSearchInput" placeholder="<?php esc_attr_e('Search formulations (Shilajit, Arjuna, Shallaki)...', 'ayur-wellness'); ?>" autocomplete="off" />
                    <input type="hidden" name="post_type" value="product" />
                </form>
                <button type="button" class="ayur-search-close-btn" id="ayurCloseSearch" aria-label="<?php esc_attr_e('Close Search', 'ayur-wellness'); ?>">
                    <?php echo ayur_svg_icon('x', '', 18, 1.75); ?>
                </button>
            </div>
            <!-- Live Search Suggestions Container (Phase 7 Section 32) -->
            <div class="ayur-search-live-results" id="ayurSearchLiveResults" style="display:none;" aria-live="polite"></div>

            <div class="ayur-search-quick-links" id="ayurSearchQuickLinks">
                <span class="ayur-search-quick-title"><?php _e('Popular Formulations:', 'ayur-wellness'); ?></span>
                <div class="ayur-search-pills">
                    <a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>"><?php _e("Men's Vitality", 'ayur-wellness'); ?></a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>"><?php _e('Metabolism', 'ayur-wellness'); ?></a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>"><?php _e('Digestive Comfort', 'ayur-wellness'); ?></a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>"><?php _e('Heart & Vitality', 'ayur-wellness'); ?></a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>"><?php _e('Joint Mobility', 'ayur-wellness'); ?></a>
                    <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>"><?php _e('Value Bundles', 'ayur-wellness'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
