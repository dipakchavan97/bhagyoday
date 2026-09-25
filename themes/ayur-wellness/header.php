<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Top Announcement Bar -->
<div class="ayur-announcement-bar">
    <div class="ayur-container">
        <div class="ayur-announcement-content">
            <span class="ayur-announcement-pill"><?php echo ayur_svg_icon('leaf', '', 12); ?> <?php _e('Authentic Ayurveda', 'ayur-wellness'); ?></span>
            <span class="ayur-announcement-main"><?php _e('Classical Ayurvedic Wellness • Pan-India Delivery • Cash on Delivery Available', 'ayur-wellness'); ?></span>
            <span class="ayur-announcement-offer"><span><?php _e('Code:', 'ayur-wellness'); ?></span> <strong>WELCOME10</strong></span>
        </div>
    </div>
</div>

<!-- Main Sticky Header -->
<header class="ayur-header" id="ayurHeader">
    <div class="ayur-container">
        <div class="ayur-header-inner">
            <!-- Mobile Menu Toggle Button -->
            <button type="button" class="ayur-mobile-menu-btn" id="ayurMobileMenuBtn" aria-label="<?php esc_attr_e('Open Navigation Menu', 'ayur-wellness'); ?>" aria-controls="ayurMobileDrawer" aria-expanded="false">
                <?php echo ayur_svg_icon('menu', '', 22, 1.8); ?>
            </button>

            <!-- Brand Wordmark Logo -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-brand" aria-label="<?php esc_attr_e('Bhagyoday Ayurveda Home', 'ayur-wellness'); ?>">
                <span class="ayur-brand-icon">
                    <?php echo ayur_svg_icon('leaf', '', 20, 1.75); ?>
                </span>
                <span class="ayur-brand-text-wrap">
                    <span class="ayur-brand-name">BHAGYODAY</span>
                    <span class="ayur-brand-tagline">AYURVEDA</span>
                </span>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="ayur-nav-wrap" aria-label="<?php esc_attr_e('Primary Navigation', 'ayur-wellness'); ?>">
                <ul class="ayur-nav-desktop">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>" class="<?php echo is_front_page() ? 'active' : ''; ?>"><?php _e('Home', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="<?php echo (function_exists('is_shop') && is_shop()) ? 'active' : ''; ?>"><?php _e('Shop All', 'ayur-wellness'); ?></a></li>
                    <li class="ayur-has-dropdown">
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                            <span><?php _e('Wellness Categories', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('chevron-down', 'ayur-dropdown-arrow', 12); ?>
                        </a>
                        <ul class="ayur-nav-sub">
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>"><?php echo ayur_svg_icon('zap', '', 14); ?> <?php _e("Men's Vitality & Stamina", 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>"><?php echo ayur_svg_icon('flame', '', 14); ?> <?php _e('Metabolism & Weight Balance', 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>"><?php echo ayur_svg_icon('leaf', '', 14); ?> <?php _e('Digestive & Piles Care', 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>"><?php echo ayur_svg_icon('heart', '', 14); ?> <?php _e('Heart Resilience & Immunity', 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>"><?php echo ayur_svg_icon('activity', '', 14); ?> <?php _e('Joint Mobility & Bone Care', 'ayur-wellness'); ?></a></li>
                            <li><a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>"><?php echo ayur_svg_icon('sparkles', '', 14); ?> <?php _e("Women's Hormonal Rhythm", 'ayur-wellness'); ?></a></li>
                        </ul>
                    </li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>"><?php _e('Value Bundles', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>"><?php _e('Our Story', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact-us/')); ?>"><?php _e('Contact', 'ayur-wellness'); ?></a></li>
                </ul>
            </nav>

            <!-- Utility Actions (Search, Account, Cart Drawer Trigger) -->
            <div class="ayur-header-actions">
                <!-- Search Button -->
                <button type="button" class="ayur-icon-btn" id="ayurSearchToggle" aria-label="<?php esc_attr_e('Search Store', 'ayur-wellness'); ?>" aria-controls="ayurSearchModal" aria-expanded="false">
                    <?php echo ayur_svg_icon('search', '', 19, 1.8); ?>
                </button>

                <!-- My Account Icon -->
                <a href="<?php echo esc_url(function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/')); ?>" class="ayur-icon-btn ayur-desktop-account" title="<?php esc_attr_e('Account', 'ayur-wellness'); ?>" aria-label="<?php esc_attr_e('Customer Account', 'ayur-wellness'); ?>">
                    <?php echo ayur_svg_icon('user', '', 19, 1.8); ?>
                </a>

                <!-- Cart Button: Triggers Right-Side Cart Drawer -->
                <button type="button" class="ayur-icon-btn ayur-cart-btn ayur-cart-trigger" id="ayurHeaderCartBtn" title="<?php esc_attr_e('Shopping Cart', 'ayur-wellness'); ?>" aria-label="<?php esc_attr_e('Open Shopping Cart Drawer', 'ayur-wellness'); ?>" aria-controls="ayurCartDrawer" aria-expanded="false">
                    <?php echo ayur_svg_icon('shopping-bag', '', 19, 1.8); ?>
                    <span class="ayur-cart-badge"><?php echo (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0; ?></span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Slide-Out Navigation Drawer (Left -> Right) -->
<div class="ayur-mobile-drawer-wrap" id="ayurMobileDrawerWrap" aria-hidden="true">
    <div class="ayur-drawer-backdrop" id="ayurMobileDrawerBackdrop"></div>
    <div class="ayur-mobile-drawer" id="ayurMobileDrawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Site Menu', 'ayur-wellness'); ?>">
        <!-- Mobile Drawer Header -->
        <div class="ayur-drawer-header">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="ayur-brand ayur-drawer-brand">
                <span class="ayur-brand-icon"><?php echo ayur_svg_icon('leaf', '', 18); ?></span>
                <span class="ayur-brand-text-wrap">
                    <span class="ayur-brand-name">BHAGYODAY</span>
                    <span class="ayur-brand-tagline">AYURVEDA</span>
                </span>
            </a>
            <button type="button" class="ayur-drawer-close-btn" id="ayurCloseMobileDrawer" aria-label="<?php esc_attr_e('Close Navigation', 'ayur-wellness'); ?>">
                <?php echo ayur_svg_icon('x', '', 20); ?>
            </button>
        </div>

        <!-- Mobile Drawer Content -->
        <div class="ayur-mobile-drawer-body">
            <!-- Search in Drawer -->
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="ayur-mobile-search-form">
                <div class="ayur-mobile-search-input-wrap">
                    <?php echo ayur_svg_icon('search', 'ayur-search-icon-inside', 16); ?>
                    <input type="search" name="s" placeholder="<?php esc_attr_e('Search formulations, herbs...', 'ayur-wellness'); ?>" required />
                    <input type="hidden" name="post_type" value="product" />
                </div>
            </form>

            <div class="ayur-drawer-section-label"><?php _e('Shop Formulations', 'ayur-wellness'); ?></div>
            <ul class="ayur-mobile-nav-list">
                <li>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ayur-mobile-nav-link">
                        <span class="ayur-nav-icon-wrap"><?php echo ayur_svg_icon('grid', '', 18); ?></span>
                        <span><?php _e('All Formulations', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('chevron-right', 'ayur-chevron-end', 14); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>" class="ayur-mobile-nav-link ayur-highlight-bundle">
                        <span class="ayur-nav-icon-wrap"><?php echo ayur_svg_icon('sparkles', '', 18); ?></span>
                        <span><?php _e('Curated Value Bundles', 'ayur-wellness'); ?></span>
                        <span class="ayur-mini-badge"><?php _e('Save 57%', 'ayur-wellness'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('mens-wellness')); ?>" class="ayur-mobile-nav-link">
                        <span class="ayur-nav-icon-wrap"><?php echo ayur_svg_icon('zap', '', 18); ?></span>
                        <span><?php _e("Men's Vitality & Stamina", 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('chevron-right', 'ayur-chevron-end', 14); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('metabolism-weight-care')); ?>" class="ayur-mobile-nav-link">
                        <span class="ayur-nav-icon-wrap"><?php echo ayur_svg_icon('flame', '', 18); ?></span>
                        <span><?php _e('Metabolism & Weight Detox', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('chevron-right', 'ayur-chevron-end', 14); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('digestive')); ?>" class="ayur-mobile-nav-link">
                        <span class="ayur-nav-icon-wrap"><?php echo ayur_svg_icon('leaf', '', 18); ?></span>
                        <span><?php _e('Digestive & Piles Care', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('chevron-right', 'ayur-chevron-end', 14); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('heart-immunity')); ?>" class="ayur-mobile-nav-link">
                        <span class="ayur-nav-icon-wrap"><?php echo ayur_svg_icon('heart', '', 18); ?></span>
                        <span><?php _e('Heart Health & Immunity', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('chevron-right', 'ayur-chevron-end', 14); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('joint-care-mobility')); ?>" class="ayur-mobile-nav-link">
                        <span class="ayur-nav-icon-wrap"><?php echo ayur_svg_icon('activity', '', 18); ?></span>
                        <span><?php _e('Joint Mobility & Bone Care', 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('chevron-right', 'ayur-chevron-end', 14); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(ayur_get_cat_url('womens-wellness')); ?>" class="ayur-mobile-nav-link">
                        <span class="ayur-nav-icon-wrap"><?php echo ayur_svg_icon('sparkles', '', 18); ?></span>
                        <span><?php _e("Women's Hormonal Vitality", 'ayur-wellness'); ?></span>
                        <?php echo ayur_svg_icon('chevron-right', 'ayur-chevron-end', 14); ?>
                    </a>
                </li>
            </ul>

            <div class="ayur-drawer-section-label"><?php _e('Company & Heritage', 'ayur-wellness'); ?></div>
            <ul class="ayur-mobile-nav-list">
                <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>" class="ayur-mobile-nav-link"><?php _e('Our Story & Philosophy', 'ayur-wellness'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/contact-us/')); ?>" class="ayur-mobile-nav-link"><?php _e('Customer Care & Contact', 'ayur-wellness'); ?></a></li>
                <li><a href="<?php echo esc_url(function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('dashboard') : home_url('/my-account/')); ?>" class="ayur-mobile-nav-link"><?php _e('My Account / Orders', 'ayur-wellness'); ?></a></li>
            </ul>

            <div class="ayur-drawer-section-label"><?php _e('Direct Customer Support', 'ayur-wellness'); ?></div>
            <div class="ayur-mobile-support-card">
                <a href="https://wa.me/918888686858" target="_blank" rel="noopener noreferrer" class="ayur-support-btn ayur-support-whatsapp">
                    <?php echo ayur_svg_icon('whatsapp', '', 18); ?>
                    <span><?php _e('Chat on WhatsApp', 'ayur-wellness'); ?></span>
                </a>
                <a href="tel:+918888686858" class="ayur-support-btn ayur-support-call">
                    <?php echo ayur_svg_icon('phone', '', 16); ?>
                    <span>+91 8888686858</span>
                </a>
            </div>

            <!-- Legal / Policies in Drawer Footer -->
            <div class="ayur-drawer-legal">
                <a href="<?php echo esc_url(home_url('/shipping-policy/')); ?>"><?php _e('Shipping', 'ayur-wellness'); ?></a>
                <span>•</span>
                <a href="<?php echo esc_url(home_url('/refund-policy/')); ?>"><?php _e('Refunds', 'ayur-wellness'); ?></a>
                <span>•</span>
                <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php _e('Privacy', 'ayur-wellness'); ?></a>
                <span>•</span>
                <a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>"><?php _e('Terms', 'ayur-wellness'); ?></a>
            </div>
        </div>
    </div>
</div>

<!-- Slide-Down Elegant Search Overlay -->
<div class="ayur-search-overlay" id="ayurSearchModal" aria-hidden="true">
    <div class="ayur-search-backdrop" id="ayurSearchBackdrop"></div>
    <div class="ayur-search-container" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Search Catalog', 'ayur-wellness'); ?>">
        <div class="ayur-container">
            <div class="ayur-search-bar-inner">
                <span class="ayur-search-bar-icon"><?php echo ayur_svg_icon('search', '', 20); ?></span>
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="ayur-search-field-form">
                    <input type="search" name="s" id="ayurSearchInput" placeholder="<?php esc_attr_e('Search Ayurvedic remedies, ingredients (Shilajit, Arjuna, Shallaki)...', 'ayur-wellness'); ?>" autocomplete="off" />
                    <input type="hidden" name="post_type" value="product" />
                </form>
                <button type="button" class="ayur-search-close-btn" id="ayurCloseSearch" aria-label="<?php esc_attr_e('Close Search', 'ayur-wellness'); ?>">
                    <?php echo ayur_svg_icon('x', '', 20); ?>
                </button>
            </div>
            <div class="ayur-quick-search-tags">
                <span class="ayur-quick-tag-label"><?php _e('Popular:', 'ayur-wellness'); ?></span>
                <a href="<?php echo esc_url(home_url('/?s=Shilajit&post_type=product')); ?>">Shilajit 800mg</a>
                <a href="<?php echo esc_url(home_url('/?s=Slim+Care&post_type=product')); ?>">Slim Care Juice</a>
                <a href="<?php echo esc_url(home_url('/?s=Arjuna&post_type=product')); ?>">Arjuna Amrit</a>
                <a href="<?php echo esc_url(home_url('/?s=Karela&post_type=product')); ?>">Karela Jamun</a>
                <a href="<?php echo esc_url(home_url('/?s=Shallaki&post_type=product')); ?>">Shallaki Joint</a>
            </div>
        </div>
    </div>
</div>
