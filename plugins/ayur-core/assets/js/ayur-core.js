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
     * Helper: Validate and normalize Indian mobile phone number according to explicit documented rule:
     * 1. Strips non-digits (+, spaces, hyphens).
     * 2. If 12 digits starting with '91' (+91 prefix), strip '91'.
     * 3. If 11 digits starting with '0' (trunk prefix), strip '0'.
     * 4. Must be exactly 10 digits starting with 6, 7, 8, or 9.
     */
    function normalizeIndianMobile(raw) {
        if (!raw) return null;
        var digits = String(raw).replace(/\D/g, '');
        if (digits.length === 12 && digits.indexOf('91') === 0) {
            digits = digits.substring(2);
        } else if (digits.length === 11 && digits.indexOf('0') === 0) {
            digits = digits.substring(1);
        }
        if (/^[6-9]\d{9}$/.test(digits)) {
            return digits;
        }
        return null;
    }

    /**
     * Inline Field Error Helpers
     */
    function showFieldError($field, message) {
        var $row = $field.closest('.form-row');
        $row.addClass('woocommerce-invalid ayur-field-has-error').removeClass('woocommerce-validated');
        $field.addClass('ayur-input-invalid');
        var $err = $row.find('.ayur-field-error-msg');
        if (!$err.length) {
            $err = $('<span class="ayur-field-error-msg" role="alert" style="display:block; color:#B91C1C; font-size:12px; margin-top:5px; font-weight:600; line-height:1.3;"></span>');
            $row.append($err);
        }
        $err.text(message).show();
    }

    function clearFieldError($field) {
        var $row = $field.closest('.form-row');
        $row.removeClass('woocommerce-invalid ayur-field-has-error').addClass('woocommerce-validated');
        $field.removeClass('ayur-input-invalid');
        $row.find('.ayur-field-error-msg').hide().text('');
    }

    /**
     * Indian Phone & Pincode Formatting & Live Validation
     */
    function initIndianFormValidation() {
        var phoneSelector = '#billing_phone, #shipping-phone, input[name="billing_phone"]';

        // Phone Input: Flexible entry without an inflexible mask
        $(document).on('input', phoneSelector, function() {
            var val = $(this).val();
            // Allow digits, +, spaces, and hyphens
            var cleaned = val.replace(/[^\d\s\+\-]/g, '');
            if (cleaned.length > 16) {
                cleaned = cleaned.substring(0, 16);
            }
            if (cleaned !== val) {
                $(this).val(cleaned);
            }

            // Real-time error clearance once valid
            if (normalizeIndianMobile(cleaned)) {
                clearFieldError($(this));
            }
        });

        // Phone Blur: Helpful validation message
        $(document).on('blur', phoneSelector, function() {
            var val = $(this).val().trim();
            if (!val) {
                showFieldError($(this), 'Mobile Number is required for courier tracking.');
            } else if (!normalizeIndianMobile(val)) {
                showFieldError($(this), 'Please enter a valid 10-digit Indian mobile number (e.g. 9876543210 or +91 9876543210).');
            } else {
                clearFieldError($(this));
            }
        });

        // PIN Code Input: Exactly up to 6 numeric digits
        $(document).on('input', '#billing_postcode, #shipping-postcode', function() {
            var val = $(this).val().replace(/[^0-9]/g, '');
            if (val.length > 6) val = val.substring(0, 6);
            $(this).val(val);
        });

        // Checkout Place Order Hook: Validate phone & prevent double submissions
        $('form.checkout').on('checkout_place_order', function() {
            var $phoneField = $(phoneSelector).first();
            if ($phoneField.length) {
                var rawVal = $phoneField.val().trim();
                var norm = normalizeIndianMobile(rawVal);
                if (!norm) {
                    showFieldError($phoneField, 'Please enter a valid 10-digit Indian mobile number before placing your order.');
                    $('html, body').animate({
                        scrollTop: Math.max(0, $phoneField.offset().top - 120)
                    }, 300);
                    $phoneField.focus();
                    return false;
                } else {
                    clearFieldError($phoneField);
                }
            }

            // Add Place Order button loading state & protect against double submissions
            var $placeOrderBtn = $('#place_order, .wc-block-components-checkout-place-order-button');
            if ($placeOrderBtn.length && !$placeOrderBtn.hasClass('ayur-btn-submitting')) {
                $placeOrderBtn.addClass('ayur-btn-submitting').attr('data-orig-html', $placeOrderBtn.html());
                $placeOrderBtn.css({ 'opacity': '0.75', 'pointer-events': 'none' });
            }
        });

        // On WooCommerce checkout error, restore button loading state
        $(document.body).on('checkout_error', function() {
            var $placeOrderBtn = $('#place_order, .wc-block-components-checkout-place-order-button');
            if ($placeOrderBtn.length && $placeOrderBtn.hasClass('ayur-btn-submitting')) {
                $placeOrderBtn.removeClass('ayur-btn-submitting');
                $placeOrderBtn.css({ 'opacity': '', 'pointer-events': '' });
                var orig = $placeOrderBtn.attr('data-orig-html');
                if (orig) $placeOrderBtn.html(orig);
            }
        });
    }

    /**
     * Address Entry Enforcement: Strictly Manual Address Entry
     * Suppress address autofill and browser address suggestion dropdowns.
     * Enforce explicit Mobile Number label.
     */
    function initAddressAutofill() {
        var addressInputs = '#billing_phone, #shipping-phone, #billing_address_1, #billing_address_2, #billing_city, #billing_state, #billing_postcode, #shipping_address_1, #shipping_address_2, #shipping_city, #shipping_state, #shipping_postcode';

        function enforceManualAddressAttributes() {
            $(addressInputs).attr('autocomplete', 'off');
            var $phoneLabel = $('label[for="billing_phone"]');
            if ($phoneLabel.length) {
                $phoneLabel.contents().filter(function() {
                    return this.nodeType === 3 && this.nodeValue.indexOf('Phone') !== -1;
                }).each(function() {
                    this.nodeValue = this.nodeValue.replace('Phone', 'Mobile Number');
                });
            }
        }

        enforceManualAddressAttributes();
        $(document).ajaxComplete(enforceManualAddressAttributes);
        $(document.body).on('updated_checkout country_to_state_changed', enforceManualAddressAttributes);
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
                    $placeOrderBtn.text('Confirm COD Order (Pay ₹' + fee + ')');
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
                '<p class="ayur-cod-explanation">Pay the applicable confirmation amount online (₹' + fee + '). Remaining balance is payable on delivery.</p>' +
                '<div class="ayur-cod-fee-table">' +
                    '<div class="split-row"><span>Order Value</span><strong>₹' + orderVal.toLocaleString('en-IN') + '</strong></div>' +
                    '<div class="split-row"><span>Shipping</span><strong>' + (isFreeShipping ? 'FREE' : '₹70') + '</strong></div>' +
                    '<div class="split-row highlight"><span>Confirmation Amount</span><strong>₹' + fee + '</strong></div>' +
                    '<div class="split-row pay-now-row"><span>Pay Now (Online)</span><strong class="pay-now-val">₹' + fee + '</strong></div>' +
                    '<div class="split-row pay-cod-row"><span>Remaining on Delivery</span><strong class="pay-cod-val">₹' + orderVal.toLocaleString('en-IN') + '</strong></div>' +
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

            var validMobile = normalizeIndianMobile(phone);
            if (!validMobile) {
                alert('Please enter a valid 10-digit Indian mobile number for courier tracking.');
                $('#billing_phone, #shipping-phone, input[type="tel"]').first().focus();
                return;
            }
            phone = validMobile;

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
