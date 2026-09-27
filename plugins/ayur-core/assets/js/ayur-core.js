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

        // Robust React-compatible input setter
        function setReactValue(selectorOrEl, val) {
            var el = typeof selectorOrEl === 'string' ? document.querySelector(selectorOrEl) : selectorOrEl;
            if (!el || val === undefined || val === null) return;

            var proto = el instanceof HTMLSelectElement ? window.HTMLSelectElement.prototype : window.HTMLInputElement.prototype;
            var setter = Object.getOwnPropertyDescriptor(proto, 'value') ? Object.getOwnPropertyDescriptor(proto, 'value').set : null;
            if (setter) {
                setter.call(el, val);
            } else {
                el.value = val;
            }
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
            el.dispatchEvent(new Event('blur', { bubbles: true }));
        }

        // Helper to find an input across WooCommerce Blocks and Classic Checkout
        function findField(selectors) {
            for (var i = 0; i < selectors.length; i++) {
                var el = document.querySelector(selectors[i]);
                if (el) return el;
            }
            return null;
        }

        var addr1Selectors = ['#shipping-address_1', 'input[name="shipping-address_1"]', 'input[autocomplete="address-line1"]', '#billing_address_1', 'input[name="billing-address_1"]'];
        var citySelectors  = ['#shipping-city', 'input[name="shipping-city"]', 'input[autocomplete="address-level2"]', '#billing_city', 'input[name="billing-city"]'];
        var stateSelectors = ['#shipping-state', 'select[name="shipping-state"]', 'select[autocomplete="address-level1"]', '#billing_state', 'select[name="billing-state"]'];
        var pinSelectors   = ['#shipping-postcode', 'input[name="shipping-postcode"]', 'input[autocomplete="postal-code"]', '#billing_postcode', 'input[name="billing-postcode"]'];

        // 1. Inject Address Search Bar above Shipping Fields in WooCommerce Blocks / Classic
        function injectAddressSearch() {
            if (document.getElementById('ayurAddressSearchWrap')) return;

            var $target = $('.wc-block-components-address-form, .wc-block-checkout__shipping-fields, .wc-block-components-checkout-step--shipping, .woocommerce-shipping-fields, .woocommerce-billing-fields__field-wrapper, #shipping-fields').first();
            if (!$target.length && $('#shipping-address_1, input[name="shipping-address_1"]').length) {
                $target = $('#shipping-address_1, input[name="shipping-address_1"]').closest('fieldset, form, div');
            }

            if ($target.length) {
                var placeholder = (window.ayurAddressParams && window.ayurAddressParams.i18n) ? window.ayurAddressParams.i18n.search_placeholder : '🔍 Search your building, street, or landmark...';
                var searchHtml = '<div class="ayur-address-search-wrapper" id="ayurAddressSearchWrap">' +
                    '<label for="ayurAddressSearchInput" class="ayur-address-search-label">' +
                        '<span>📍</span> ' + placeholder +
                    '</label>' +
                    '<input type="text" id="ayurAddressSearchInput" class="ayur-address-search-input" placeholder="Type building, street or locality (e.g. Civil Hospital Road)..." autocomplete="off" />' +
                    '<div class="ayur-pin-feedback" id="ayurPinFeedback" style="display:none;"></div>' +
                '</div>';
                $target.prepend(searchHtml);
                initGooglePlacesAutocomplete();
            }
        }

        injectAddressSearch();

        // Persistent observer to re-inject if React re-renders and wipes the custom search bar
        var checkoutContainer = document.querySelector('.wp-block-woocommerce-checkout, .woocommerce-checkout, form.checkout');
        if (checkoutContainer && window.MutationObserver) {
            var observer = new MutationObserver(function() {
                if (!document.getElementById('ayurAddressSearchWrap')) {
                    injectAddressSearch();
                }
            });
            observer.observe(checkoutContainer, { childList: true, subtree: true });
        } else {
            setInterval(injectAddressSearch, 1200);
        }

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

                var elAddr = findField(addr1Selectors);
                if (elAddr && fullAddress1) setReactValue(elAddr, fullAddress1);

                var elCity = findField(citySelectors);
                if (elCity && city) setReactValue(elCity, city);

                var elPin = findField(pinSelectors);
                if (elPin && postalCode) setReactValue(elPin, postalCode);

                var elState = findField(stateSelectors);
                if (elState && state) setReactValue(elState, state);

                // Feedback pill
                var $feedback = $('#ayurPinFeedback');
                if ($feedback.length) {
                    $feedback.html('<span class="pin-success">✓ Address autofilled from Google Places (' + (city ? city + ', ' : '') + state + ').</span>').fadeIn(200);
                    setTimeout(function() { $feedback.fadeOut(300); }, 5000);
                }
            });
        }

        // 3. Indian PIN Code Intelligence (Auto-lookup on 6 digits)
        var pinLookupTimer = null;
        $(document).on('input change blur', '#shipping-postcode, input[name="shipping-postcode"], #billing_postcode, input[name="billing-postcode"], #ayurAddressSearchInput', function() {
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
                        var elCity = findField(citySelectors);
                        var elState = findField(stateSelectors);
                        var elPin = findField(pinSelectors);

                        if (elPin && (!elPin.value || elPin.value !== pin)) {
                            setReactValue(elPin, pin);
                        }

                        if (elCity && data.city && (!elCity.value || elCity.value === 'Mantha')) {
                            setReactValue(elCity, data.city);
                        }

                        if (elState && data.state) {
                            setReactValue(elState, data.state);
                        }

                        if ($feedback.length) {
                            $feedback.html('<span class="pin-success">✓ Location verified: ' + (data.city ? data.city + ', ' : '') + data.state + ' (' + pin + ')</span>').show();
                            setTimeout(function() { $feedback.fadeOut(400); }, 4000);
                        }
                    }
                }
            });
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

        // TRIGGER COD CONFIRMATION FLOW
        function triggerCodConfirmationFlow(btnElement) {
            if (isProcessingCod) return;

            var $btn = $(btnElement);

            // Read customer input values
            var phone = $('#shipping-phone, input[name="shipping-phone"], #billing_phone, input[name="billing_phone"]').first().val() || '';
            var email = $('#email, input[type="email"], #billing_email, input[name="billing_email"]').first().val() || '';
            var address1 = $('#shipping-address_1, input[name="shipping-address_1"], #billing_address_1, input[name="billing_address_1"]').first().val() || '';
            var city = $('#shipping-city, input[name="shipping-city"], #billing_city, input[name="billing_city"]').first().val() || '';
            var state = $('#shipping-state, select[name="shipping-state"], #billing_state, select[name="billing_state"]').first().val() || 'MH';
            var postcode = $('#shipping-postcode, input[name="shipping-postcode"], #billing_postcode, input[name="billing_postcode"]').first().val() || '';
            var firstName = $('#shipping-first_name, input[name="shipping-first_name"], #billing_first_name, input[name="billing_first_name"]').first().val() || 'Customer';
            var lastName = $('#shipping-last_name, input[name="shipping-last_name"], #billing_last_name, input[name="billing_last_name"]').first().val() || '';

            if (!phone || phone.replace(/\D/g, '').length < 10) {
                alert('Please enter a valid 10-digit mobile number for courier tracking.');
                $('#shipping-phone, #billing_phone, input[type="tel"]').first().focus();
                return;
            }

            if (!address1 || !city || !postcode || postcode.replace(/\D/g, '').length < 6) {
                alert('Please complete your delivery address and 6-digit PIN code.');
                return;
            }

            isProcessingCod = true;
            $btn.prop('disabled', true).css('opacity', '0.7').text('Generating Secure COD Payment...');

            var checkoutPayload = {
                shipping_first_name: firstName,
                shipping_last_name:  lastName,
                billing_email:       email,
                shipping_phone:      phone,
                shipping_address_1:  address1,
                shipping_address_2:  $('#shipping-address_2, input[name="shipping-address_2"]').first().val() || '',
                shipping_city:       city,
                shipping_state:      state,
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
        }

        // CRITICAL CAPTURE-PHASE LISTENER: Intercept click BEFORE React synthetic events or WooCommerce Blocks receive it!
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.wc-block-components-checkout-place-order-button, #place_order');
            if (!btn) return;

            if (checkIsCodActive()) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                triggerCodConfirmationFlow(btn);
            }
        }, true); // TRUE = CAPTURE PHASE!

        // Also intercept submit event in capture phase
        document.addEventListener('submit', function(e) {
            if (checkIsCodActive()) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                var btn = document.querySelector('.wc-block-components-checkout-place-order-button, #place_order');
                if (btn) triggerCodConfirmationFlow(btn);
            }
        }, true);

        // Launch Razorpay Checkout Modal (Live or Sandbox Test Modal)
        function launchRazorpayCodModal(sessionData, $btn) {
            if (!sessionData.is_test_mode && typeof Razorpay !== 'undefined') {
                // Real Live Razorpay Gateway
                var options = {
                    key: sessionData.key_id,
                    amount: sessionData.amount,
                    currency: sessionData.currency || 'INR',
                    name: sessionData.name || 'Bhagyoday Ayurveda',
                    description: sessionData.description || 'COD Confirmation Fee',
                    order_id: sessionData.order_id,
                    prefill: sessionData.prefill || {},
                    theme: { color: '#173B2F' },
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
            } else {
                // Interactive Razorpay Test Mode Modal
                renderRazorpayTestModal(sessionData, $btn);
            }
        }

        // Interactive Razorpay Test Modal
        function renderRazorpayTestModal(sessionData, $btn) {
            $('#ayurRzpModalBackdrop').remove();

            var amounts = sessionData.amounts || {};
            var fee = codConfig.fee_amount || 100;
            var payableOnDelivery = amounts.payable_on_delivery || '₹960';

            var modalHtml = '<div class="ayur-rzp-modal-backdrop" id="ayurRzpModalBackdrop">' +
                '<div class="ayur-rzp-modal-box">' +
                    '<div class="ayur-rzp-modal-header">' +
                        '<div class="ayur-rzp-brand-row">' +
                            '<div class="ayur-rzp-brand-title">🌿 Bhagyoday Ayurveda <span class="ayur-rzp-test-badge">Test Mode</span></div>' +
                            '<button type="button" class="ayur-rzp-modal-close" id="ayurRzpModalCloseBtn" aria-label="Close">✕</button>' +
                        '</div>' +
                        '<div class="ayur-rzp-amount-display">' +
                            '<span class="ayur-rzp-amount-main">₹' + fee + '.00</span>' +
                            '<span class="ayur-rzp-amount-label">COD Confirmation Fee</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="ayur-rzp-modal-body">' +
                        '<div class="ayur-rzp-summary-card">' +
                            '<div class="ayur-rzp-summary-row"><span>Merchandise & Shipping:</span> <strong>' + payableOnDelivery + '</strong></div>' +
                            '<div class="ayur-rzp-summary-row"><span>Confirmation Fee (Online):</span> <strong style="color:#2E7D32;">₹' + fee + '</strong></div>' +
                            '<div class="ayur-rzp-summary-row bold"><span>Due on Delivery (Cash):</span> <strong>' + payableOnDelivery + '</strong></div>' +
                        '</div>' +
                        '<div class="ayur-rzp-methods-list">' +
                            '<div class="ayur-rzp-method-item selected">' +
                                '<span class="ayur-rzp-method-icon">📱</span>' +
                                '<div class="ayur-rzp-method-info">' +
                                    '<div class="ayur-rzp-method-title">UPI / QR (Instant)</div>' +
                                    '<div class="ayur-rzp-method-subtitle">Google Pay, PhonePe, Paytm, BHIM</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="ayur-rzp-method-item">' +
                                '<span class="ayur-rzp-method-icon">💳</span>' +
                                '<div class="ayur-rzp-method-info">' +
                                    '<div class="ayur-rzp-method-title">Cards (Visa, Mastercard, RuPay)</div>' +
                                    '<div class="ayur-rzp-method-subtitle">Test Card 4111 •••• •••• 1111</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<button type="button" class="ayur-rzp-btn-pay-success" id="ayurRzpPaySuccessBtn">' +
                            '<span>🔒</span> PAY ₹' + fee + ' (TEST PAYMENT - SUCCESS)' +
                        '</button>' +
                        '<button type="button" class="ayur-rzp-btn-pay-fail" id="ayurRzpPayFailBtn">' +
                            'Simulate Payment Failure' +
                        '</button>' +
                    '</div>' +
                    '<div class="ayur-rzp-modal-footer">' +
                        '<span>🔒 Razorpay Trusted Business</span>' +
                        '<span>256-bit SSL Encrypted</span>' +
                    '</div>' +
                '</div>' +
            '</div>';

            $('body').append(modalHtml);

            // Close button
            $('#ayurRzpModalCloseBtn').on('click', function() {
                $('#ayurRzpModalBackdrop').fadeOut(200, function() { $(this).remove(); });
                isProcessingCod = false;
                $btn.prop('disabled', false).css('opacity', '1').text('PAY ₹' + fee + ' & CONFIRM COD ORDER');
                renderDismissNotice();
            });

            // Simulate Payment Failure button
            $('#ayurRzpPayFailBtn').on('click', function() {
                $('#ayurRzpModalBackdrop').fadeOut(200, function() { $(this).remove(); });
                isProcessingCod = false;
                $btn.prop('disabled', false).css('opacity', '1').text('PAY ₹' + fee + ' & CONFIRM COD ORDER');
                alert('Test Payment Declined: Simulated bank transaction failure. No order was created.');
            });

            // Simulate Successful Payment button
            $('#ayurRzpPaySuccessBtn').on('click', function() {
                var $payBtn = $(this);
                $payBtn.prop('disabled', true).text('Verifying with Bank...');
                $('#ayurRzpModalBackdrop').fadeOut(250, function() { $(this).remove(); });

                $btn.text('Verifying Payment & Creating COD Order...');

                // Generate simulated test payment response using server's HMAC token
                var mockRzpResponse = {
                    razorpay_payment_id: 'pay_test_' + Math.random().toString(36).substring(2, 14),
                    razorpay_order_id:   sessionData.order_id,
                    razorpay_signature:  sessionData.test_sig_token || ''
                };

                verifyCodPaymentOnServer(sessionData.session_id, mockRzpResponse, $btn);
            });
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
