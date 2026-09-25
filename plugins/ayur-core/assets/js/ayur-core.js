/**
 * Ayur Core - Frontend Interactive Engine
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initCheckoutLeadCapture();
        initWishlist();
        initIndianFormValidation();
        initCartThresholdSync();
    });

    /**
     * Capture Abandoned Cart Leads (Email / Phone) on blur
     */
    function initCheckoutLeadCapture() {
        if (!$('body').hasClass('woocommerce-checkout')) return;

        var leadTimer = null;
        $('#billing_email, #billing_phone').on('blur change', function() {
            clearTimeout(leadTimer);
            leadTimer = setTimeout(function() {
                var email = $('#billing_email').val();
                var phone = $('#billing_phone').val();

                if ((email && email.indexOf('@') !== -1) || (phone && phone.length >= 10)) {
                    $.ajax({
                        url: ayurCoreParams.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'ayur_capture_checkout_lead',
                            nonce: ayurCoreParams.nonce,
                            email: email,
                            phone: phone
                        }
                    });
                }
            }, 800);
        });
    }

    /**
     * Wishlist with LocalStorage fallback for guests
     */
    function initWishlist() {
        var guestWishlist = JSON.parse(localStorage.getItem('ayur_guest_wishlist') || '[]');

        // Highlight existing wishlisted items
        $('.ayur-wishlist-toggle').each(function() {
            var pid = $(this).data('product-id');
            if (guestWishlist.indexOf(pid) !== -1) {
                $(this).addClass('active');
            }
        });

        $(document).on('click', '.ayur-wishlist-toggle', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var pid = $btn.data('product-id');

            $btn.toggleClass('active');

            if ($btn.hasClass('active')) {
                if (guestWishlist.indexOf(pid) === -1) guestWishlist.push(pid);
            } else {
                guestWishlist = guestWishlist.filter(function(id) { return id !== pid; });
            }
            localStorage.setItem('ayur_guest_wishlist', JSON.stringify(guestWishlist));

            // Sync with server if logged in
            $.ajax({
                url: ayurCoreParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'ayur_toggle_wishlist',
                    nonce: ayurCoreParams.nonce,
                    product_id: pid
                }
            });
        });
    }

    /**
     * Indian Phone & Pincode Formatting
     */
    function initIndianFormValidation() {
        $('#billing_phone').on('input', function() {
            var val = $(this).val().replace(/[^0-9]/g, '');
            if (val.length > 10) val = val.substring(0, 10);
            $(this).val(val);
        });

        $('#billing_postcode').on('input', function() {
            var val = $(this).val().replace(/[^0-9]/g, '');
            if (val.length > 6) val = val.substring(0, 6);
            $(this).val(val);
        });
    }

    /**
     * Live Free Shipping Threshold Reactivity
     */
    function initCartThresholdSync() {
        $(document.body).on('updated_wc_div added_to_cart removed_from_cart', function() {
            // Refreshed via WC fragments automatically
        });
    }

})(jQuery);
