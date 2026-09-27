/**
 * Ayur Core - Frontend Interactive Engine
 * Indian D2C Checkout Suite: Address Autofill, PIN Intelligence,
 * Prepaid COD Confirmation Fee (₹100) & Real-Time Tracking.
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initCheckoutLeadCapture();
        initWishlist();
        initIndianFormValidation();
        initAddressAutofill();
        initCodConfirmationFee();
    });

    /**
     * Capture Abandoned Cart Leads (Email / Phone) on blur
     */
    function initCheckoutLeadCapture() {
        if (!$('body').hasClass('woocommerce-checkout')) return;

        var leadTimer = null;
        $(document).on('blur change', '#email, #shipping-phone, #billing_email, #billing_phone', function() {
            clearTimeout(leadTimer);
            leadTimer = setTimeout(function() {
                var email = $('#email').val() || $('#billing_email').val() || '';
                var phone = $('#shipping-phone').val() || $('#billing_phone').val() || '';

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
        $(document).on('input', '#shipping-phone, #billing_phone', function() {
            var val = $(this).val().replace(/[^0-9]/g, '');
            if (val.length > 10) val = val.substring(0, 10);
            $(this).val(val);
        });

        $(document).on('input', '#shipping-postcode, #billing_postcode', function() {
            var val = $(this).val().replace(/[^0-9]/g, '');
            if (val.length > 6) val = val.substring(0, 6);
            $(this).val(val);
        });
    }

    /**
     * Address Autofill & Indian Pincode Intelligence
     */
    function initAddressAutofill() {
        var isCheckout = $('body').hasClass('woocommerce-checkout') || window.location.pathname.indexOf('/checkout') !== -1;
        if (!isCheckout) return;

        // 1. Inject Address Search Bar above Shipping Fields in WooCommerce Blocks
        function injectAddressSearch() {
            if ($('#ayurAddressSearchWrap').length) return;

            var $target = $('.wc-block-components-address-form, .wc-block-checkout__shipping-fields, .wc-block-components-checkout-step--shipping, .woocommerce-shipping-fields, .woocommerce-billing-fields__field-wrapper, #shipping-fields').first();
            if (!$target.length && $('#shipping-address_1').length) {
                $target = $('#shipping-address_1').closest('fieldset, form, div');
            }

            if ($target.length) {
                var searchHtml = '<div class="ayur-address-search-wrapper" id="ayurAddressSearchWrap">' +
                    '<label for="ayurAddressSearchInput" class="ayur-address-search-label">' +
                        '<span>🔍</span> ' + (window.ayurAddressParams ? window.ayurAddressParams.i18n.search_placeholder : 'Search your address / area / landmark...') +
                    '</label>' +
                    '<input type="text" id="ayurAddressSearchInput" class="ayur-address-search-input" placeholder="Type building, street or locality (e.g. Civil Hospital Road)..." autocomplete="off" />' +
                    '<div class="ayur-pin-feedback" id="ayurPinFeedback" style="display:none;"></div>' +
                '</div>';
                $target.prepend(searchHtml);
                initGooglePlacesAutocomplete();
            }
        }

        // Run check on load and upon block mutation
        injectAddressSearch();
        var checkTimer = setInterval(function() {
            if ($('#ayurAddressSearchWrap').length) {
                clearInterval(checkTimer);
            } else {
                injectAddressSearch();
            }
        }, 600);

        // 2. Google Places Autocomplete initialization
        function initGooglePlacesAutocomplete() {
            var input = document.getElementById('ayurAddressSearchInput');
            if (!input || !window.google || !google.maps || !google.maps.places) {
                return;
            }

            var country = (window.ayurAddressParams && window.ayurAddressParams.country_restriction) ? window.ayurAddressParams.country_restriction : 'in';
            var autocomplete = new google.maps.places.Autocomplete(input, {
                componentRestrictions: { country: country },
                fields: ['address_components', 'geometry', 'formatted_address']
            });

            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();
                if (!place || !place.address_components) return;

                var streetNum = '', route = '', sublocality = '', city = '', state = '', postalCode = '';

                place.address_components.forEach(function(comp) {
                    var types = comp.types;
                    if (types.indexOf('street_number') !== -1) streetNum = comp.long_name;
                    if (types.indexOf('route') !== -1) route = comp.long_name;
                    if (types.indexOf('sublocality') !== -1 || types.indexOf('sublocality_level_1') !== -1) sublocality = comp.long_name;
                    if (types.indexOf('locality') !== -1 || types.indexOf('administrative_area_level_2') !== -1) city = comp.long_name;
                    if (types.indexOf('administrative_area_level_1') !== -1) state = comp.short_name;
                    if (types.indexOf('postal_code') !== -1) postalCode = comp.long_name;
                });

                var fullAddress1 = [streetNum, route, sublocality].filter(Boolean).join(', ') || place.formatted_address.split(',')[0];

                setInputValue('#shipping-address_1', fullAddress1);
                if (city) setInputValue('#shipping-city', city);
                if (postalCode) setInputValue('#shipping-postcode', postalCode);
                if (state) setSelectValue('#shipping-state', state);

                // Feedback pill
                $('#ayurPinFeedback').html('<span class="pin-success">✓ Address autofilled from Google Places. You can review or edit below.</span>').fadeIn(200);
                setTimeout(function() { $('#ayurPinFeedback').fadeOut(300); }, 5000);
            });
        }

        // 3. Indian PIN Code Intelligence (Auto-lookup on 6 digits)
        var pinLookupTimer = null;
        $(document).on('input change blur', '#shipping-postcode, #billing_postcode', function() {
            var $pinInput = $(this);
            var pin = $pinInput.val().replace(/[^0-9]/g, '');

            if (pin.length === 6) {
                clearTimeout(pinLookupTimer);
                pinLookupTimer = setTimeout(function() {
                    lookupIndianPincode(pin);
                }, 300);
            }
        });

        function lookupIndianPincode(pin) {
            var $feedback = $('#ayurPinFeedback');
            if ($feedback.length) {
                $feedback.html('<span class="pin-loading">⏳ Checking Indian PIN Code ' + pin + '...</span>').show();
            }

            $.ajax({
                url: ayurCoreParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'ayur_lookup_pincode',
                    nonce: (window.ayurAddressParams ? window.ayurAddressParams.nonce : ayurCoreParams.nonce),
                    pincode: pin
                },
                success: function(res) {
                    if (res.success && res.data) {
                        var data = res.data;
                        var $city = $('#shipping-city, #billing_city');
                        var $state = $('#shipping-state, #billing_state');

                        if (data.city && (!$city.val() || $city.val() === 'Mantha')) {
                            setInputValue('#shipping-city', data.city);
                            setInputValue('#billing_city', data.city);
                        }

                        if (data.state) {
                            setSelectValue('#shipping-state', data.state);
                            setSelectValue('#billing_state', data.state);
                        }

                        if ($feedback.length) {
                            $feedback.html('<span class="pin-success">✓ Location verified: ' + (data.city ? data.city + ', ' : '') + data.state + '</span>').show();
                            setTimeout(function() { $feedback.fadeOut(400); }, 4000);
                        }
                    }
                }
            });
        }

        function setInputValue(selector, val) {
            var $el = $(selector);
            if ($el.length && val) {
                $el.val(val);
                var el = $el[0];
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                el.dispatchEvent(new Event('blur', { bubbles: true }));
            }
        }

        function setSelectValue(selector, val) {
            var $el = $(selector);
            if ($el.length && val) {
                $el.val(val);
                var el = $el[0];
                el.dispatchEvent(new Event('change', { bubbles: true }));
                el.dispatchEvent(new Event('blur', { bubbles: true }));
            }
        }
    }

    /**
     * Prepaid ₹100 COD Confirmation Fee Flow
     */
    function initCodConfirmationFee() {
        var isCheckout = $('body').hasClass('woocommerce-checkout') || window.location.pathname.indexOf('/checkout') !== -1;
        if (!isCheckout) return;

        var codConfig = ayurCoreParams.cod_confirmation || { enabled: true, fee_amount: 100, fee_label: 'COD Confirmation Fee' };
        if (!codConfig.enabled) return;

        var isProcessingCod = false;

        function checkIsCodActive() {
            var $codRadio = $('input[type="radio"][value="cod"], input[type="radio"][id*="cod"], #radio-control-wc-payment-method-options-cod');
            if ($codRadio.length && $codRadio.is(':checked')) {
                return true;
            }
            var $codAccordion = $('.wc-block-components-radio-control-accordion-option:contains("Cash on Delivery"), .wc-block-components-radio-control-accordion-option:contains("COD")');
            if ($codAccordion.length && ($codAccordion.hasClass('is-active') || $codAccordion.find('input:checked').length)) {
                return true;
            }
            return false;
        }

        // Monitor active payment method
        function updateCodState() {
            var isCodSelected = checkIsCodActive();
            var $placeOrderBtn = $('.wc-block-components-checkout-place-order-button, #place_order');

            if (isCodSelected) {
                renderCodBreakdownBox();
                if ($placeOrderBtn.length) {
                    var fee = codConfig.fee_amount || 100;
                    $placeOrderBtn.attr('data-orig-text', $placeOrderBtn.attr('data-orig-text') || $placeOrderBtn.text().trim());
                    $placeOrderBtn.text('PAY ₹' + fee + ' & CONFIRM COD ORDER');
                    $placeOrderBtn.addClass('ayur-cod-fee-active');
                }
            } else {
                removeCodBreakdownBox();
                if ($placeOrderBtn.length && $placeOrderBtn.hasClass('ayur-cod-fee-active')) {
                    $placeOrderBtn.removeClass('ayur-cod-fee-active');
                    var orig = $placeOrderBtn.attr('data-orig-text') || 'Place Order';
                    $placeOrderBtn.text(orig);
                }
            }
        }

        function renderCodBreakdownBox() {
            if ($('#ayurCodBreakdownBox').length) return;

            var $codContainer = $('#radio-control-wc-payment-method-options-cod').closest('.wc-block-components-radio-control-accordion-option, .wc-block-components-radio-control__option, .payment_method_cod');
            if (!$codContainer.length) {
                $codContainer = $('input[type="radio"][value="cod"], input[type="radio"][id*="cod"]').closest('div, li, label');
            }
            if (!$codContainer.length) {
                $codContainer = $('.wc-block-components-payment-methods, #payment').first();
            }

            var fee = codConfig.fee_amount || 100;

            // Read live order summary amounts
            var subtotalText = $('.wc-block-components-totals-item:contains("Subtotal") .wc-block-formatted-money-amount, .cart-subtotal .amount').first().text().replace(/[^0-9]/g, '') || '890';
            var shippingText = $('.wc-block-components-totals-shipping, .woocommerce-shipping-totals').first().text() || '';
            var isFreeShipping = shippingText.toLowerCase().indexOf('free') !== -1;
            var shippingVal = isFreeShipping ? 0 : 70;
            var orderVal = parseInt(subtotalText, 10) + shippingVal;

            var boxHtml = '<div class="ayur-cod-fee-breakdown-box" id="ayurCodBreakdownBox">' +
                '<div class="ayur-cod-fee-header">' +
                    '<span class="badge-icon">🛡️</span>' +
                    '<div>' +
                        '<strong>Cash on Delivery (COD) Confirmation</strong>' +
                        '<div class="subtext">To confirm dispatch, a non-refundable ₹' + fee + ' confirmation fee is payable online now. The remaining balance is payable on delivery.</div>' +
                    '</div>' +
                '</div>' +
                '<div class="ayur-cod-fee-table">' +
                    '<div class="split-row"><span>Order Value:</span> <strong>₹' + orderVal.toLocaleString('en-IN') + '</strong></div>' +
                    '<div class="split-row"><span>Delivery:</span> <strong>' + (isFreeShipping ? 'FREE' : '₹70') + '</strong></div>' +
                    '<div class="split-row highlight"><span>' + (codConfig.fee_label || 'COD Confirmation Fee') + ':</span> <strong>₹' + fee + '</strong></div>' +
                    '<div class="split-row pay-now-row"><span>Total Payable Now (Online):</span> <strong class="pay-now-val">₹' + fee + '</strong></div>' +
                    '<div class="split-row pay-cod-row"><span>Remaining Due on Delivery (Cash):</span> <strong class="pay-cod-val">₹' + orderVal.toLocaleString('en-IN') + '</strong></div>' +
                '</div>' +
                '<div class="ayur-cod-fee-footer-note">' +
                    'Pay ₹' + fee + ' now via UPI, Google Pay, PhonePe, Cards or NetBanking. Zero extra hidden charges upon delivery.' +
                '</div>' +
            '</div>';

            $codContainer.append(boxHtml);
        }

        function removeCodBreakdownBox() {
            $('#ayurCodBreakdownBox').remove();
        }

        // Listen for radio selection changes
        $(document).on('change click', 'input[type="radio"]', function() {
            setTimeout(updateCodState, 100);
        });

        // Periodic observer for Blocks dynamic re-renders
        setInterval(updateCodState, 800);

        // Intercept Place Order Button when COD is selected
        $(document).on('click', '.wc-block-components-checkout-place-order-button, #place_order', function(e) {
            var isCodSelected = checkIsCodActive();


            if (!isCodSelected) {
                return; // Let standard Razorpay / other gateway handle normally
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            if (isProcessingCod) return;

            // Validate required inputs
            var phone = $('#shipping-phone').val() || $('#billing_phone').val() || '';
            var email = $('#email').val() || $('#billing_email').val() || '';
            var address1 = $('#shipping-address_1').val() || $('#billing_address_1').val() || '';
            var city = $('#shipping-city').val() || $('#billing_city').val() || '';
            var postcode = $('#shipping-postcode').val() || $('#billing_postcode').val() || '';

            if (!phone || phone.length < 10) {
                alert('Please enter a valid 10-digit mobile number for delivery verification.');
                $('#shipping-phone, #billing_phone').first().focus();
                return;
            }

            if (!address1 || !city || !postcode) {
                alert('Please complete your delivery address and 6-digit PIN code.');
                return;
            }

            var $btn = $(this);
            isProcessingCod = true;
            $btn.prop('disabled', true).css('opacity', '0.7').text('Generating Secure COD Payment...');

            // Harvest checkout inputs
            var checkoutPayload = {
                shipping_first_name: $('#shipping-first_name').val() || $('#billing_first_name').val() || 'Customer',
                shipping_last_name:  $('#shipping-last_name').val() || $('#billing_last_name').val() || '',
                billing_email:       email,
                shipping_phone:      phone,
                shipping_address_1:  address1,
                shipping_address_2:  $('#shipping-address_2').val() || '',
                shipping_city:       city,
                shipping_state:      $('#shipping-state').val() || 'MH',
                shipping_postcode:   postcode
            };

            // Call Server to Initiate Secure COD Confirmation Session
            $.ajax({
                url: ayurCoreParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'ayur_init_cod_confirmation',
                    nonce: ayurCoreParams.nonce,
                    checkout_data: checkoutPayload
                },
                success: function(res) {
                    if (res.success && res.data) {
                        launchRazorpayCodModal(res.data, $btn);
                    } else {
                        isProcessingCod = false;
                        $btn.prop('disabled', false).css('opacity', '1').text('PAY ₹' + (codConfig.fee_amount || 100) + ' & CONFIRM COD ORDER');
                        alert((res.data && res.data.message) ? res.data.message : 'Unable to initiate COD payment. Please try again.');
                    }
                },
                error: function() {
                    isProcessingCod = false;
                    $btn.prop('disabled', false).css('opacity', '1').text('PAY ₹' + (codConfig.fee_amount || 100) + ' & CONFIRM COD ORDER');
                    alert('Network error connecting to payment server. Please verify your connection.');
                }
            });
        });

        // Launch Razorpay Checkout Modal for ₹100
        function launchRazorpayCodModal(sessionData, $btn) {
            if (typeof Razorpay === 'undefined') {
                isProcessingCod = false;
                $btn.prop('disabled', false).css('opacity', '1').text('PAY ₹' + (codConfig.fee_amount || 100) + ' & CONFIRM COD ORDER');
                alert('Razorpay payment gateway is loading. Please try again in a moment.');
                return;
            }

            var options = {
                key: sessionData.key_id,
                amount: sessionData.amount,
                currency: sessionData.currency || 'INR',
                name: sessionData.name || 'Bhagyoday Ayurveda',
                description: sessionData.description || 'COD Confirmation Fee',
                order_id: sessionData.order_id,
                prefill: sessionData.prefill || {},
                theme: {
                    color: '#173B2F'
                },
                modal: {
                    ondismiss: function() {
                        isProcessingCod = false;
                        $btn.prop('disabled', false).css('opacity', '1').text('PAY ₹' + (codConfig.fee_amount || 100) + ' & CONFIRM COD ORDER');
                        renderDismissNotice();
                    }
                },
                handler: function(response) {
                    $btn.text('Verifying Payment & Creating COD Order...');
                    verifyCodPaymentOnServer(sessionData.session_id, response, $btn);
                }
            };

            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function(resp) {
                isProcessingCod = false;
                $btn.prop('disabled', false).css('opacity', '1').text('PAY ₹' + (codConfig.fee_amount || 100) + ' & CONFIRM COD ORDER');
                alert('Payment was not completed. ' + (resp.error ? resp.error.description : 'Please try again.'));
            });

            rzp.open();
        }

        function verifyCodPaymentOnServer(sessionId, rzpResponse, $btn) {
            $.ajax({
                url: ayurCoreParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'ayur_verify_cod_confirmation',
                    nonce: ayurCoreParams.nonce,
                    session_id: sessionId,
                    razorpay_payment_id: rzpResponse.razorpay_payment_id,
                    razorpay_order_id: rzpResponse.razorpay_order_id,
                    razorpay_signature: rzpResponse.razorpay_signature
                },
                success: function(res) {
                    if (res.success && res.data && res.data.redirect_url) {
                        window.location.href = res.data.redirect_url;
                    } else {
                        isProcessingCod = false;
                        $btn.prop('disabled', false).css('opacity', '1').text('PAY ₹' + (codConfig.fee_amount || 100) + ' & CONFIRM COD ORDER');
                        alert((res.data && res.data.message) ? res.data.message : 'Payment verification failed. Please contact support.');
                    }
                },
                error: function() {
                    isProcessingCod = false;
                    $btn.prop('disabled', false).css('opacity', '1').text('PAY ₹' + (codConfig.fee_amount || 100) + ' & CONFIRM COD ORDER');
                    alert('Network error during verification. Your payment has been received; please check your email.');
                }
            });
        }

        function renderDismissNotice() {
            var noticeHtml = '<div class="ayur-notice-banner warning" style="margin:15px 0; padding:12px 16px; background:#FFFBEB; border:1px solid #FDE68A; border-radius:6px; color:#92400E; font-size:13px;">' +
                'COD confirmation payment was not completed. No COD order has been placed. You can click the button below to retry anytime.' +
            '</div>';
            $('#ayurCodBreakdownBox').before(noticeHtml);
            setTimeout(function() {
                $('.ayur-notice-banner.warning').fadeOut(400, function() { $(this).remove(); });
            }, 6000);
        }
    }

})(jQuery);
