/**
 * Ayur Wellness Theme Interactions & Native Mobile UX
 * Premium Ayurvedic D2C Storefront Architecture
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initStickyHeader();
        initMobileNavDrawer();
        initCartDrawer();
        initSearchOverlay();
        initProductGallery();
        initQtySteppers();
        initMobileStickyBar();
    });

    /**
     * Sticky Header with Scroll Compaction
     */
    function initStickyHeader() {
        var $header = $('#ayurHeader');
        if (!$header.length) return;

        $(window).on('scroll', function() {
            var st = $(this).scrollTop();
            if (st > 40) {
                $header.addClass('scrolled');
            } else {
                $header.removeClass('scrolled');
            }
        });
    }

    /**
     * Mobile Navigation Drawer (Left to Right, Transform Animation)
     */
    function initMobileNavDrawer() {
        var $drawerWrap = $('#ayurMobileDrawerWrap');
        if (!$drawerWrap.length) return;

        function openDrawer() {
            $drawerWrap.addClass('is-open').attr('aria-hidden', 'false');
            $('body').addClass('ayur-scroll-locked');
            $('#ayurCloseMobileDrawer').focus();
        }

        function closeDrawer() {
            $drawerWrap.removeClass('is-open').attr('aria-hidden', 'true');
            $('body').removeClass('ayur-scroll-locked');
            $('#ayurMobileMenuBtn').focus();
        }

        $('#ayurMobileMenuBtn').on('click', function(e) {
            e.preventDefault();
            openDrawer();
        });

        $('#ayurCloseMobileDrawer, #ayurMobileDrawerBackdrop').on('click', function(e) {
            e.preventDefault();
            closeDrawer();
        });

        $(document).on('keyup', function(e) {
            if (e.key === 'Escape' && $drawerWrap.hasClass('is-open')) {
                closeDrawer();
            }
        });
    }

    /**
     * Premium Cart Drawer (Right to Left, AJAX Qty & Auto-Open)
     */
    function initCartDrawer() {
        var $cartWrap = $('#ayurCartDrawerWrap');
        if (!$cartWrap.length) return;

        function openCart() {
            $cartWrap.addClass('is-open').attr('aria-hidden', 'false');
            $('body').addClass('ayur-scroll-locked');
            $('#ayurCloseCartDrawer').focus();
        }

        function closeCart() {
            $cartWrap.removeClass('is-open').attr('aria-hidden', 'true');
            $('body').removeClass('ayur-scroll-locked');
        }

        // Open Cart triggers (Header icons, product add-to-cart & Mobile bottom nav)
        $(document).on('click', '#ayurHeaderCartBtn, .ayur-cart-trigger, [data-action="open-cart"]', function(e) {
            e.preventDefault();
            openCart();
        });

        // Close triggers
        $(document).on('click', '#ayurCloseCartDrawer, #ayurCartDrawerBackdrop', function(e) {
            e.preventDefault();
            closeCart();
        });

        $(document).on('keyup', function(e) {
            if (e.key === 'Escape' && $cartWrap.hasClass('is-open')) {
                closeCart();
            }
        });

        // Auto open cart drawer when a product is added via AJAX
        $(document.body).on('added_to_cart', function(event, fragments, cart_hash) {
            openCart();
            refreshCartDrawer();
        });

        // AJAX Quantity Increment / Decrement inside Cart Drawer
        $(document).on('click', '.ayur-drawer-qty-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var key = $btn.data('cart-key');
            var action = $btn.data('action');
            var $input = $btn.siblings('.ayur-drawer-qty-val');
            var currentVal = parseInt($input.text(), 10) || 1;
            var newVal = action === 'inc' ? currentVal + 1 : currentVal - 1;

            if (newVal < 1) {
                removeDrawerItem(key);
                return;
            }

            updateDrawerQty(key, newVal);
        });

        // AJAX Item Removal inside Cart Drawer
        $(document).on('click', '.ayur-drawer-item-remove', function(e) {
            e.preventDefault();
            var key = $(this).data('cart-key');
            removeDrawerItem(key);
        });

        function updateDrawerQty(key, qty) {
            $('#ayurCartDrawer').addClass('is-loading');
            $.ajax({
                url: ayurThemeParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'ayur_update_cart_drawer_qty',
                    cart_item_key: key,
                    quantity: qty
                },
                success: function(res) {
                    if (res.success && res.data) {
                        $('#ayurCartDrawer').html(res.data.html);
                        updateBadges(res.data.count);
                    }
                },
                complete: function() {
                    $('#ayurCartDrawer').removeClass('is-loading');
                }
            });
        }

        function removeDrawerItem(key) {
            $('#ayurCartDrawer').addClass('is-loading');
            $.ajax({
                url: ayurThemeParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'ayur_remove_cart_drawer_item',
                    cart_item_key: key
                },
                success: function(res) {
                    if (res.success && res.data) {
                        $('#ayurCartDrawer').html(res.data.html);
                        updateBadges(res.data.count);
                    }
                },
                complete: function() {
                    $('#ayurCartDrawer').removeClass('is-loading');
                }
            });
        }

        function refreshCartDrawer() {
            $.ajax({
                url: ayurThemeParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'ayur_update_cart_drawer_qty',
                    cart_item_key: '',
                    quantity: 0
                },
                success: function(res) {
                    if (res.success && res.data) {
                        $('#ayurCartDrawer').html(res.data.html);
                        updateBadges(res.data.count);
                    }
                }
            });
        }

        function updateBadges(count) {
            var $badges = $('.ayur-cart-badge');
            $badges.text(count);
        }
    }

    /**
     * Search Overlay (Slide-Down / Fade)
     */
    function initSearchOverlay() {
        var $searchModal = $('#ayurSearchModal');
        if (!$searchModal.length) return;

        function openSearch() {
            $searchModal.addClass('is-open').attr('aria-hidden', 'false');
            $('body').addClass('ayur-scroll-locked');
            setTimeout(function() {
                $('#ayurSearchInput').focus();
            }, 100);
        }

        function closeSearch() {
            $searchModal.removeClass('is-open').attr('aria-hidden', 'true');
            $('body').removeClass('ayur-scroll-locked');
            $('#ayurSearchToggle').focus();
        }

        $(document).on('click', '#ayurSearchToggle, #ayurBottomNavSearch, [data-action="open-search"]', function(e) {
            e.preventDefault();
            openSearch();
        });

        $('#ayurCloseSearch, #ayurSearchBackdrop').on('click', function(e) {
            e.preventDefault();
            closeSearch();
        });

        $(document).on('keyup', function(e) {
            if (e.key === 'Escape' && $searchModal.hasClass('is-open')) {
                closeSearch();
            }
        });
    }

    /**
     * Product Gallery Switcher
     */
    function initProductGallery() {
        $(document).on('click', '.ayur-gallery-thumb-btn', function(e) {
            e.preventDefault();
            var fullUrl = $(this).data('full');
            if (fullUrl) {
                var $mainImg = $('#ayurMainProductImg');
                $mainImg.css('opacity', '0.4');
                $mainImg.attr('src', fullUrl).attr('srcset', '');
                setTimeout(function() {
                    $mainImg.css('opacity', '1');
                }, 120);

                $('.ayur-gallery-thumb-btn').removeClass('active');
                $(this).addClass('active');
            }
        });
    }

    /**
     * Quantity Steppers on Product Page
     */
    function initQtySteppers() {
        $(document).on('click', '.ayur-qty-inc', function(e) {
            e.preventDefault();
            var $input = $(this).siblings('.qty');
            var val = parseInt($input.val(), 10) || 1;
            var max = parseInt($input.attr('max'), 10) || 999;
            if (val < max) {
                $input.val(val + 1).trigger('change');
            }
        });

        $(document).on('click', '.ayur-qty-dec', function(e) {
            e.preventDefault();
            var $input = $(this).siblings('.qty');
            var val = parseInt($input.val(), 10) || 1;
            var min = parseInt($input.attr('min'), 10) || 1;
            if (val > min) {
                $input.val(val - 1).trigger('change');
            }
        });
    }

    /**
     * Mobile Sticky Purchase Bar Interaction
     */
    function initMobileStickyBar() {
        var $bar = $('#ayurMobileStickyBar');
        if (!$bar.length) return;

        $bar.find('.ayur-sticky-add-cart').on('click', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            if (!productId) return;

            var $btn = $(this);
            $btn.css('opacity', '0.6');

            $.ajax({
                url: ayurThemeParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'woocommerce_add_to_cart',
                    product_id: productId,
                    quantity: 1
                },
                success: function(response) {
                    $(document.body).trigger('added_to_cart');
                },
                complete: function() {
                    $btn.css('opacity', '1');
                }
            });
        });
    }

})(jQuery);
