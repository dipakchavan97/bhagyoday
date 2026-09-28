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
        initProductLightbox();
        initQtySteppers();
        initMobileStickyBar();
        initProductAccordions();
        initOfferSelector();
        initInstantCheckout();
        initAmritComposition();
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
        $(document).on('click', '#ayurCloseCartDrawer, #ayurCartDrawerBackdrop, #ayurContinueShopping', function(e) {
            e.preventDefault();
            closeCart();
        });

        $(document).on('keyup', function(e) {
            if (e.key === 'Escape' && $cartWrap.hasClass('is-open')) {
                closeCart();
            }
        });

        // Quick add feedback & tracking (Phase 7 Section 10: State transition, duplicate click prevention)
        $(document).on('click', '.ajax_add_to_cart', function(e) {
            var $btn = $(this);
            if ($btn.hasClass('is-adding')) {
                e.preventDefault();
                return false;
            }
            if ($btn.hasClass('is-added')) {
                e.preventDefault();
                openCart();
                return false;
            }

            $btn.addClass('is-adding');
            var $textSpan = $btn.find('span:not(.ayur-icon)');
            if ($textSpan.length && !$btn.data('orig-text')) {
                $btn.data('orig-text', $textSpan.text());
            }

            var prodId = $btn.data('product_id') || $btn.attr('data-product_id');
            var prodSku = $btn.data('product_sku') || prodId;
            var prodName = $btn.closest('.ayur-product-card, .ayur-hero-spotlight-card, .ayur-featured-block, .ayur-bundle-card, .ayur-drawer-rec-card, .ayur-drawer-cross-item').find('.ayur-product-title, .ayur-hero-spotlight-title, .ayur-featured-title, .ayur-bundle-title, .ayur-drawer-rec-name, .ayur-drawer-cross-name').first().text().trim() || 'Ayurvedic Formulation';
            var eventId = 'atc_' + prodId + '_' + Date.now();

            if (window.dataLayer) {
                window.dataLayer.push({
                    'event': 'add_to_cart',
                    'ecommerce': {
                        'currency': 'INR',
                        'items': [{
                            'item_id': String(prodSku),
                            'item_name': prodName,
                            'quantity': 1
                        }]
                    }
                });
            }

            if (typeof fbq === 'function') {
                fbq('track', 'AddToCart', {
                    content_name: prodName,
                    content_ids: [String(prodSku)],
                    content_type: 'product',
                    currency: 'INR'
                }, { eventID: eventId });
            }
        });

        // Single Product Page Form Add to Cart Tracking
        $('form.cart.ayur-cart-form').on('submit', function() {
            var $form = $(this);
            var prodId = $form.find('button[name="add-to-cart"]').val();
            var qty = parseInt($form.find('input[name="quantity"]').val(), 10) || 1;
            var prodName = $('.ayur-product-title').first().text().trim() || 'Ayurvedic Formulation';
            var eventId = 'atc_' + prodId + '_' + Date.now();

            if (window.dataLayer) {
                window.dataLayer.push({
                    'event': 'add_to_cart',
                    'ecommerce': {
                        'currency': 'INR',
                        'items': [{
                            'item_id': String(prodId),
                            'item_name': prodName,
                            'quantity': qty
                        }]
                    }
                });
            }

            if (typeof fbq === 'function') {
                fbq('track', 'AddToCart', {
                    content_name: prodName,
                    content_ids: [String(prodId)],
                    content_type: 'product',
                    currency: 'INR'
                }, { eventID: eventId });
            }
        });

        // Auto open cart drawer when a product is added via AJAX + Button State Updates
        $(document.body).on('added_to_cart', function(event, fragments, cart_hash) {
            $('.ajax_add_to_cart.is-adding').each(function() {
                var $btn = $(this);
                $btn.removeClass('is-adding').addClass('is-added');
                var $textSpan = $btn.find('span:not(.ayur-icon)');
                if ($textSpan.length) {
                    $textSpan.text('✓ ADDED');
                }
            });

            setTimeout(function() {
                $('.ajax_add_to_cart.is-added').each(function() {
                    var $btn = $(this);
                    var $textSpan = $btn.find('span:not(.ayur-icon)');
                    if ($textSpan.length) {
                        $textSpan.text('VIEW CART →');
                    }
                });
            }, 1200);

            setTimeout(function() {
                $('.ajax_add_to_cart.is-added').each(function() {
                    var $btn = $(this);
                    var origText = $btn.data('orig-text');
                    var $textSpan = $btn.find('span:not(.ayur-icon)');
                    if ($textSpan.length && origText) {
                        $textSpan.text(origText);
                    }
                    $btn.removeClass('is-added');
                });
            }, 4500);

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
                        var html = res.data.html || res.data.drawer_html;
                        var count = (typeof res.data.count !== 'undefined') ? res.data.count : res.data.cart_count;
                        $('#ayurCartDrawer').html(html);
                        updateBadges(count);
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
                        var html = res.data.html || res.data.drawer_html;
                        var count = (typeof res.data.count !== 'undefined') ? res.data.count : res.data.cart_count;
                        $('#ayurCartDrawer').html(html);
                        updateBadges(count);
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
                    action: 'ayur_get_cart_drawer'
                },
                success: function(res) {
                    if (res.success && res.data) {
                        var html = res.data.html || res.data.drawer_html;
                        var count = (typeof res.data.count !== 'undefined') ? res.data.count : res.data.cart_count;
                        $('#ayurCartDrawer').html(html);
                        updateBadges(count);
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
     * Search Overlay with Fast Live Search (Phase 7 Section 32)
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

        // Live AJAX Instant Search Suggestions
        var searchTimer = null;
        $('#ayurSearchInput').on('input', function() {
            var term = $(this).val().trim();
            clearTimeout(searchTimer);
            var $results = $('#ayurSearchLiveResults');
            var $pills = $('#ayurSearchQuickLinks');

            if (term.length < 2) {
                $results.hide().empty();
                $pills.show();
                return;
            }

            searchTimer = setTimeout(function() {
                $results.html('<div class="ayur-search-loading"><span class="ayur-search-spinner"></span> Searching formulations...</div>').show();
                $pills.hide();

                $.ajax({
                    url: ayurThemeParams.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ayur_search_products',
                        term: term
                    },
                    success: function(res) {
                        if (res.success && res.data && res.data.results) {
                            var items = res.data.results;
                            if (items.length === 0) {
                                $results.html('<div class="ayur-search-no-results">No formulations found for "<strong>' + $('<div>').text(term).html() + '</strong>". Try searching for Shilajit, Arjuna, or Joint.</div>');
                            } else {
                                var html = '<div class="ayur-search-results-grid">';
                                items.forEach(function(item) {
                                    html += '<a href="' + item.url + '" class="ayur-search-result-item">';
                                    if (item.thumb) {
                                        html += '<div class="ayur-search-result-thumb"><img src="' + item.thumb + '" alt="' + item.name + '" /></div>';
                                    } else {
                                        html += '<div class="ayur-search-result-thumb ayur-thumb-fallback"></div>';
                                    }
                                    html += '<div class="ayur-search-result-info">';
                                    html += '<h4 class="ayur-search-result-title">' + item.name + '</h4>';
                                    if (item.net_qty) {
                                        html += '<span class="ayur-search-result-qty">' + item.net_qty + '</span>';
                                    }
                                    html += '<div class="ayur-search-result-pricing"><span class="ayur-search-result-price">' + item.price + '</span>';
                                    if (item.mrp) {
                                        html += '<span class="ayur-search-result-mrp">' + item.mrp + '</span>';
                                    }
                                    html += '</div></div>';
                                    html += '<span class="ayur-search-result-arrow">→</span>';
                                    html += '</a>';
                                });
                                html += '</div>';
                                $results.html(html);
                            }
                        }
                    },
                    error: function() {
                        $results.html('<div class="ayur-search-no-results">Unable to load search results. Please press enter to search catalog.</div>');
                    }
                });
            }, 220);
        });
    }

    /**
    /**
    /**
     * Product Gallery (Compact Thumbnail Switcher, Strictly No Lightbox)
     */
    function initProductGallery() {
        var $stage = $('#ayurGalleryStage');
        var $mainImg = $('#ayurMainProductImg');
        var $thumbBtns = $('.ayur-gallery-thumb-btn');

        if (!$stage.length || !$mainImg.length) return;

        // Collect all gallery items
        var galleryItems = [];

        if ($thumbBtns.length > 0) {
            $thumbBtns.each(function(i) {
                var $btn = $(this);
                var largeUrl = $btn.data('large') || $btn.data('full') || $btn.find('img').attr('src');
                var alt = $btn.data('alt') || $mainImg.attr('alt') || '';
                galleryItems.push({
                    index: i,
                    large: largeUrl,
                    alt: alt
                });
            });
        }

        function setMainImage(idx) {
            if (!galleryItems[idx]) return;
            var item = galleryItems[idx];

            // Smooth image transition on main stage
            $mainImg.css('opacity', '0.4');
            $mainImg.attr('src', item.large).removeAttr('srcset').attr('data-index', idx);
            if (item.alt) $mainImg.attr('alt', item.alt);

            setTimeout(function() {
                $mainImg.css('opacity', '1');
            }, 80);

            // Update active thumbnail border
            $('.ayur-gallery-thumb-btn').removeClass('active');
            $('.ayur-gallery-thumb-btn[data-index="' + idx + '"]').addClass('active');
        }

        // Thumbnail Click: update main image ONLY (No lightbox, no popup)
        $(document).on('click', '.ayur-gallery-thumb-btn', function(e) {
            e.preventDefault();
            var idx = parseInt($(this).data('index'), 10) || 0;
            setMainImage(idx);
        });

        // Stage click: strictly do nothing special (no modal, no popup, no zoom overlay)
        $stage.on('click', function(e) {
            // Passive click - strictly do nothing
        });
    }

    function initProductLightbox() {
        // Disabled per specification - strictly no lightbox
    }

    /**
     * Quantity Steppers on Product Page
     */
    function initQtySteppers() {
        $(document).on('click', '.ayur-qty-inc', function(e) {
            e.preventDefault();
            var $input = $(this).closest('.ayur-qty-stepper').find('input.qty');
            var val = parseInt($input.val(), 10) || 1;
            var max = parseInt($input.attr('max'), 10) || 999;
            if (val < max) {
                $input.val(val + 1).trigger('change');
            }
        });

        $(document).on('click', '.ayur-qty-dec', function(e) {
            e.preventDefault();
            var $input = $(this).closest('.ayur-qty-stepper').find('input.qty');
            var val = parseInt($input.val(), 10) || 1;
            var min = parseInt($input.attr('min'), 10) || 1;
            if (val > min) {
                $input.val(val - 1).trigger('change');
            }
        });
    }

    /**
     * Mobile Sticky Purchase Bar Interaction & Dynamic Height Synchronization
     */
    function initMobileStickyBar() {
        var bar = document.getElementById('ayurMobileStickyBar');
        if (!bar) return;

        function updateStickyBarHeight() {
            var h = bar.offsetHeight;
            if (h > 0) {
                document.documentElement.style.setProperty('--mobile-purchase-bar-height', h + 'px');
            }
        }

        updateStickyBarHeight();

        if (typeof window.ResizeObserver !== 'undefined') {
            var ro = new ResizeObserver(function() {
                updateStickyBarHeight();
            });
            ro.observe(bar);
        } else {
            window.addEventListener('resize', updateStickyBarHeight, { passive: true });
            window.addEventListener('orientationchange', updateStickyBarHeight, { passive: true });
        }

        var $bar = $(bar);
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

    /**
     * Phase 5: Product Page Responsive Accordions
     * Automatically keeps all accordions open on desktop (>768px).
     * On mobile, respects default-open state (Why choose, Botanicals, Usage open; Specs, Shipping, Caution collapsed).
     */
    function initProductAccordions() {
        var $accordions = $('.ayur-product-accordion');
        if (!$accordions.length) return;

        function syncAccordions() {
            var isDesktop = window.innerWidth >= 769;
            $accordions.each(function() {
                var $acc = $(this);
                if (isDesktop) {
                    $acc.attr('open', 'open');
                } else {
                    if (!$acc.hasClass('is-default-open')) {
                        $acc.removeAttr('open');
                    } else {
                        $acc.attr('open', 'open');
                    }
                }
            });
        }

        syncAccordions();
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(syncAccordions, 150);
        });
    }

    /**
     * Buy More Save More Offer Selector (Phase 5)
     */
    function initOfferSelector() {
        var $box = $('#ayurOfferSelectorBox');
        if (!$box.length) return;

        var $qtyInput = $('form.cart input[name="quantity"]');
        var $badge = $('#ayurOfferLiveBadge');

        // Handle Chip Click
        $(document).on('click', '.ayur-offer-chip', function(e) {
            e.preventDefault();
            var $chip = $(this);
            var targetQty = parseInt($chip.data('qty'), 10) || 1;

            $('.ayur-offer-chip').removeClass('is-selected');
            $chip.addClass('is-selected');

            if ($qtyInput.length) {
                $qtyInput.val(targetQty).trigger('change');
            }
            updateOfferBadge(targetQty);
            syncBuyNowButtons(targetQty);
        });

        // Sync when quantity input changes (steppers or typing)
        $qtyInput.on('input change', function() {
            var currentQty = parseInt($(this).val(), 10) || 1;
            syncChipsToQty(currentQty);
            updateOfferBadge(currentQty);
            syncBuyNowButtons(currentQty);
        });

        function syncChipsToQty(qty) {
            var $chips = $('.ayur-offer-chip');
            var matchedChip = null;

            $chips.each(function() {
                var chipQty = parseInt($(this).data('qty'), 10) || 1;
                if (qty >= chipQty) {
                    matchedChip = $(this);
                }
            });

            $chips.removeClass('is-selected');
            if (matchedChip) {
                matchedChip.addClass('is-selected');
            } else {
                $chips.first().addClass('is-selected');
            }
        }

        function updateOfferBadge(qty) {
            if (!$badge.length) return;
            var appliedDiscount = 0;

            $('.ayur-offer-chip').each(function() {
                var chipQty = parseInt($(this).data('qty'), 10) || 1;
                var disc = parseFloat($(this).data('discount')) || 0;
                if (qty >= chipQty && disc > appliedDiscount) {
                    appliedDiscount = disc;
                }
            });

            if (appliedDiscount > 0) {
                $badge.html('✓ ' + appliedDiscount + '% OFF applied').show();
            } else {
                $badge.hide();
            }
        }

        function syncBuyNowButtons(qty) {
            $('#ayurBtnBuyNow, #ayurStickyBuyNow').each(function() {
                var prodId = $(this).data('product-id');
                if (prodId) {
                    var newUrl = (ayurThemeParams.checkout_url || '/checkout/') + '?ayur_instant_buy=' + prodId + '&quantity=' + qty;
                    $(this).attr('href', newUrl);
                }
            });
        }

        // Initialize state
        var initialQty = parseInt($qtyInput.val(), 10) || 1;
        syncChipsToQty(initialQty);
        updateOfferBadge(initialQty);
        syncBuyNowButtons(initialQty);
    }

    /**
     * Instant Checkout Entry Point for BUY NOW buttons (Phase 1)
     */
    function initInstantCheckout() {
        $(document).on('click', '#ayurBtnBuyNow, #ayurStickyBuyNow, .ayur-btn-buy-now, .ayur-sticky-buy-now', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var prodId = $btn.data('product-id') || $('form.cart button[name="add-to-cart"]').val();
            var qty = parseInt($('form.cart input[name="quantity"]').val(), 10) || 1;

            if (!prodId) {
                window.location.href = $btn.attr('href');
                return;
            }

            // Track begin_checkout in GA4 & Meta Pixel
            var prodName = $('.ayur-single-title, .ayur-product-title, h1.product_title').first().text().trim() || 'Ayurvedic Formulation';
            var price = parseFloat($('#ayurOfferSelectorBox').data('unit-price')) || 890;
            var value = price * qty;

            if (window.dataLayer) {
                window.dataLayer.push({
                    'event': 'begin_checkout',
                    'ecommerce': {
                        'currency': 'INR',
                        'value': value,
                        'items': [{
                            'item_id': String(prodId),
                            'item_name': prodName,
                            'quantity': qty,
                            'price': price
                        }]
                    }
                });
            }

            if (typeof fbq === 'function') {
                fbq('track', 'InitiateCheckout', {
                    content_name: prodName,
                    content_ids: [String(prodId)],
                    content_type: 'product',
                    value: value,
                    currency: 'INR',
                    num_items: qty
                });
            }

            // Direct instant checkout navigation
            var instantUrl = (ayurThemeParams.checkout_url || '/checkout/') + '?ayur_instant_buy=' + prodId + '&quantity=' + qty;
            window.location.href = instantUrl;
        });
    }

    /**
     * Amrit Juice 60-Botanical Composition: Mobile Expand/Collapse Accordion
     */
    function initAmritComposition() {
        var $mobileToggle = $('#ayurCompMobileToggle');
        var $tableWrap = $('#ayurCompositionTableWrap');

        if (!$mobileToggle.length || !$tableWrap.length) return;

        // Mobile Expand/Collapse Accordion
        $mobileToggle.on('click', function(e) {
            e.preventDefault();
            var isCollapsed = $tableWrap.hasClass('is-collapsed-mobile');
            if (isCollapsed) {
                $tableWrap.removeClass('is-collapsed-mobile');
                $(this).attr('aria-expanded', 'true');
                $(this).find('.btn-text').text('COLLAPSE COMPOSITION');
            } else {
                $tableWrap.addClass('is-collapsed-mobile');
                $(this).attr('aria-expanded', 'false');
                $(this).find('.btn-text').text('VIEW COMPLETE COMPOSITION');
            }
        });
    }

})(jQuery);

