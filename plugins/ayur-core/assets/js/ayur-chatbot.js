/**
 * Bhagyoday Ayurveda — AI Product Expert Chatbot Client
 *
 * Handles widget interaction, accessible keyboard controls, AJAX messaging,
 * dynamic product cards rendering, duplicate submission prevention, rate-limit
 * countdown timers, and mobile responsive bottom-sheet ergonomics.
 */

(function($) {
    'use strict';

    // Prevent multi-initialization if script is evaluated multiple times
    if (window.ayurChatInitialized) {
        return;
    }
    window.ayurChatInitialized = true;

    if (typeof window.ayurChatParams === 'undefined') {
        return;
    }

    var params = window.ayurChatParams;
    var $root, $launcher, $window, $messages, $form, $input, $sendBtn, $typing, $error, $errorMsg, $retryBtn, $quickActions;
    var isOpen = false;
    var isSending = false;
    var isRateLimitedBlocked = false;
    var rateLimitTimer = null;
    var lastFailedMessage = '';

    $(document).ready(function() {
        initElements();
        bindEvents();
        initSession();
        setupViewportHandling();
    });

    function initElements() {
        $root         = $('#ayur-chatbot-root');
        $launcher     = $('#ayur-chat-launcher');
        $window       = $('#ayur-chat-window');
        $messages     = $('#ayur-chat-messages');
        $form         = $('#ayur-chat-form');
        $input        = $('#ayur-chat-input');
        $sendBtn      = $('#ayur-chat-send');
        $typing       = $('#ayur-chat-typing');
        $error        = $('#ayur-chat-error');
        $errorMsg     = $('#ayur-chat-error-msg');
        $retryBtn     = $('#ayur-chat-retry-btn');
        $quickActions = $('#ayur-chat-quick-actions');
    }

    function bindEvents() {
        // Toggle & close controls
        $launcher.off('click.ayurchat').on('click.ayurchat', toggleChat);
        $('#ayur-chat-close, #ayur-chat-minimize').off('click.ayurchat').on('click.ayurchat', closeChat);

        // Escape key to close
        $(document).off('keydown.ayurchat').on('keydown.ayurchat', function(e) {
            if (e.key === 'Escape' && isOpen) {
                closeChat();
            }
        });

        // Form submit (Send button click or mobile Go)
        $form.off('submit.ayurchat').on('submit.ayurchat', function(e) {
            e.preventDefault();
            e.stopPropagation();
            submitUserMessage();
        });

        // Enter key to submit (Shift+Enter for newline)
        // Directly calls submitUserMessage without triggering form submit to avoid event duplication
        $input.off('keydown.ayurchat').on('keydown.ayurchat', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                e.stopPropagation();
                submitUserMessage();
            }
        });

        // Auto-resize input height smoothly up to 72px
        $input.off('input.ayurchat').on('input.ayurchat', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 72) + 'px';
        });

        // Retry failed message — retries once without duplicating user bubble
        $retryBtn.off('click.ayurchat').on('click.ayurchat', function(e) {
            e.preventDefault();
            handleRetry();
        });

        // Quick action chip clicks
        $root.off('click.ayurchat', '.ayur-chat-chip').on('click.ayurchat', '.ayur-chat-chip', function(e) {
            e.preventDefault();
            if (isSending || isRateLimitedBlocked) return;

            var actionText = $(this).text().trim();
            if (actionText.indexOf('WhatsApp') !== -1 || actionText.indexOf('व्हाट्सएप') !== -1 || actionText.indexOf('व्हॉट्सॲप') !== -1) {
                trackEvent('ayur_chat_whatsapp_clicked', { source: 'quick_chip' });
                window.open(params.whatsapp_url, '_blank');
                return;
            }

            // Populate and submit directly
            $input.val(actionText);
            submitUserMessage();
        });

        // Product card click tracking
        $root.off('click.ayurchat', '.ayur-chat-card').on('click.ayurchat', '.ayur-chat-card', function() {
            var prodName = $(this).find('.ayur-chat-card-title').text();
            trackEvent('ayur_chat_product_clicked', { product_name: prodName });
        });
    }

    function initSession() {
        if ($messages.children().length === 0) {
            appendBotMessage(params.welcome_message, [], false);
            renderQuickChips(params.prompt_chips);
        }
    }

    function toggleChat() {
        if (isOpen) {
            closeChat();
        } else {
            openChat();
        }
    }

    function openChat() {
        isOpen = true;
        $window.removeAttr('hidden').addClass('ayur-chat-open');
        $launcher.attr('aria-expanded', 'true');
        $launcher.find('.ayur-chat-launcher-badge').hide();

        trackEvent('ayur_chat_opened');
        if (typeof window.fbq === 'function') {
            window.fbq('trackCustom', 'AyurChatOpened');
        }

        setTimeout(function() {
            $input.focus();
            scrollToBottom();
        }, 150);
    }

    function closeChat() {
        isOpen = false;
        $window.removeClass('ayur-chat-open').attr('hidden', 'true');
        $launcher.attr('aria-expanded', 'false');
    }

    /**
     * Submit a New User Message
     * Guarded against:
     * 1. Already sending
     * 2. Rate-limit active lockout
     * 3. Empty text
     * 4. Double event triggers
     */
    function submitUserMessage() {
        if (isSending || isRateLimitedBlocked) {
            return;
        }

        var text = $.trim($input.val());
        if (text.length === 0) {
            return;
        }

        // 1. Immediately reset input field to prevent double submission
        $input.val('').css('height', 'auto');

        // 2. Append user bubble ONCE for new submissions
        appendUserMessage(text);
        lastFailedMessage = text;
        scrollToBottom();

        // 3. Track question submitted
        trackEvent('ayur_chat_question_submitted', { query_length: text.length });

        // 4. Dispatch API request
        dispatchMessageRequest(text, false);
    }

    /**
     * Handle Retry
     * Retries the failed question ONCE without duplicating the user-message bubble
     */
    function handleRetry() {
        if (isSending || isRateLimitedBlocked || !lastFailedMessage) {
            return;
        }

        $error.hide();
        // Dispatch API request with isRetry = true (no duplicate bubble created)
        dispatchMessageRequest(lastFailedMessage, true);
    }

    /**
     * Core AJAX Dispatcher
     * @param {string} text - Message content
     * @param {boolean} isRetry - Whether this is a retry attempt
     */
    function dispatchMessageRequest(text, isRetry) {
        if (isSending) return;

        isSending = true;
        setSendingUI(true);
        $error.hide();
        $typing.show();
        scrollToBottom();

        $.ajax({
            url: params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'ayur_chatbot_message',
                nonce: params.nonce,
                message: text
            },
            timeout: 25000,
            success: function(res) {
                isSending = false;
                $typing.hide();
                setSendingUI(false);

                if (res && res.success && res.data) {
                    var data = res.data;
                    lastFailedMessage = ''; // Clear failed message on success

                    appendBotMessage(data.message, data.products || [], true);

                    // Render max 3 compact contextual quick action chips
                    if (data.quick_replies && data.quick_replies.length > 0) {
                        renderQuickChips(data.quick_replies);
                    }

                    if (data.products && data.products.length > 0) {
                        trackEvent('ayur_chat_product_recommended', {
                            count: data.products.length,
                            product_id: data.products[0].id
                        });
                    }

                    scrollToBottom();
                } else {
                    var errMsg = (res && res.data && res.data.message) ? res.data.message : params.strings.error;
                    handleError(errMsg, false, 0);
                }
            },
            error: function(xhr, status, err) {
                isSending = false;
                $typing.hide();
                setSendingUI(false);

                var is429 = (xhr && xhr.status === 429);
                var retryAfter = 15;
                var errText = params.strings.error;

                if (is429) {
                    try {
                        if (xhr.responseJSON && xhr.responseJSON.data) {
                            if (xhr.responseJSON.data.retry_after) {
                                retryAfter = parseInt(xhr.responseJSON.data.retry_after, 10);
                            }
                            if (xhr.responseJSON.data.message) {
                                errText = xhr.responseJSON.data.message;
                            }
                        } else if (xhr.getResponseHeader('Retry-After')) {
                            retryAfter = parseInt(xhr.getResponseHeader('Retry-After'), 10);
                            errText = 'You are asking questions quickly. Please wait a moment.';
                        }
                    } catch(e) {}

                    if (isNaN(retryAfter) || retryAfter <= 0) {
                        retryAfter = 15;
                    }
                    handleError(errText, true, retryAfter);
                } else {
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errText = xhr.responseJSON.data.message;
                    }
                    handleError(errText, false, 0);
                }
            }
        });
    }

    /**
     * Set Sending UI State
     * Disables Send button and toggles loading spinner
     */
    function setSendingUI(busy) {
        if (busy) {
            $sendBtn.prop('disabled', true).addClass('ayur-chat-loading').attr('aria-busy', 'true');
        } else {
            if (!isRateLimitedBlocked) {
                $sendBtn.prop('disabled', false).removeClass('ayur-chat-loading').attr('aria-busy', 'false');
            }
        }
    }

    /**
     * Handle Errors & 429 Rate-Limit Countdown
     */
    function handleError(msg, is429, retryAfterSeconds) {
        $errorMsg.text(msg);
        $error.show();

        if (rateLimitTimer) {
            clearInterval(rateLimitTimer);
            rateLimitTimer = null;
        }

        if (is429 && retryAfterSeconds > 0) {
            isRateLimitedBlocked = true;
            $sendBtn.prop('disabled', true);
            $retryBtn.prop('disabled', true).addClass('ayur-chat-btn-disabled');

            var remaining = retryAfterSeconds;
            $retryBtn.text('Wait ' + remaining + 's');
            $errorMsg.text('Rate limit reached. Please wait ' + remaining + ' seconds before retrying.');

            rateLimitTimer = setInterval(function() {
                remaining--;
                if (remaining > 0) {
                    $retryBtn.text('Wait ' + remaining + 's');
                    $errorMsg.text('Rate limit reached. Please wait ' + remaining + ' seconds before retrying.');
                } else {
                    clearInterval(rateLimitTimer);
                    rateLimitTimer = null;
                    isRateLimitedBlocked = false;
                    $retryBtn.prop('disabled', false).removeClass('ayur-chat-btn-disabled').text(params.strings.retry || 'Retry');
                    $sendBtn.prop('disabled', false);
                    $errorMsg.text('You can now send your question or click Retry.');
                }
            }, 1000);
        } else {
            $retryBtn.prop('disabled', false).removeClass('ayur-chat-btn-disabled').text(params.strings.retry || 'Retry');
        }

        scrollToBottom();
    }

    /**
     * Append User Message Bubble (Used strictly once per new message)
     */
    function appendUserMessage(text) {
        var timeStr = getCurrentTime();
        var html = '<div class="ayur-chat-msg ayur-chat-msg-user">' +
                   '<div class="ayur-chat-msg-content">' + escapeHtml(text) + '</div>' +
                   '<span class="ayur-chat-msg-time">' + timeStr + '</span>' +
                   '</div>';
        $messages.append(html);
    }

    /**
     * Append Bot Message Bubble
     */
    function appendBotMessage(text, products, animate) {
        var timeStr = getCurrentTime();
        var formattedText = formatMarkdown(text);

        var html = '<div class="ayur-chat-msg ayur-chat-msg-bot">' +
                   '<div class="ayur-chat-msg-content">' + formattedText + '</div>';

        // Render Interactive Product Cards
        if (products && products.length > 0) {
            html += '<div class="ayur-chat-product-cards">';
            for (var i = 0; i < products.length; i++) {
                var p = products[i];
                html += '<a href="' + escapeAttr(p.permalink) + '" class="ayur-chat-card" target="_blank" rel="noopener">' +
                        '  <img src="' + escapeAttr(p.image_url) + '" alt="' + escapeAttr(p.name) + '" class="ayur-chat-card-img" loading="lazy" />' +
                        '  <div class="ayur-chat-card-info">' +
                        '    <div class="ayur-chat-card-title">' + escapeHtml(p.name) + '</div>' +
                        '    <div class="ayur-chat-card-pricing">' +
                        '      <span class="ayur-chat-card-price">' + escapeHtml(p.formatted_price) + '</span>' +
                        '      <span class="ayur-chat-card-mrp">' + escapeHtml(p.formatted_mrp) + '</span>' +
                        '      <span class="ayur-chat-card-badge">' + escapeHtml(p.discount) + '</span>' +
                        '    </div>' +
                        '    <div class="ayur-chat-card-stock">● In Stock</div>' +
                        '  </div>' +
                        '  <span class="ayur-chat-card-btn">' + params.strings.view_prod + ' →</span>' +
                        '</a>';
            }
            html += '</div>';
        }

        html += '<span class="ayur-chat-msg-time">' + timeStr + '</span>' +
                '</div>';

        $messages.append(html);
        scrollToBottom();
    }

    /**
     * Render Contextual Quick Chips (Limited to max 3 compact chips)
     */
    function renderQuickChips(chips) {
        if (!chips || chips.length === 0) return;
        var html = '';
        var count = 0;
        for (var i = 0; i < chips.length; i++) {
            var chip = chips[i];
            if (chip && chip.trim().length > 0 && count < 3) {
                html += '<button type="button" class="ayur-chat-chip">' + escapeHtml(chip.trim()) + '</button>';
                count++;
            }
        }
        $quickActions.html(html);
    }

    function formatMarkdown(str) {
        if (!str) return '';

        // Escape HTML
        var s = str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        // Bold
        s = s.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Italic
        s = s.replace(/\*([^\*]+)\*/g, '<em>$1</em>');

        // Markdown Links
        s = s.replace(/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

        // Lists
        var lines = s.split('\n');
        var inList = false;
        var out = [];

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i].trim();
            if (line.indexOf('• ') === 0 || line.indexOf('- ') === 0) {
                if (!inList) {
                    out.push('<ul>');
                    inList = true;
                }
                out.push('<li>' + line.substring(2) + '</li>');
            } else {
                if (inList) {
                    out.push('</ul>');
                    inList = false;
                }
                if (line.length > 0) {
                    out.push('<p>' + line + '</p>');
                }
            }
        }
        if (inList) {
            out.push('</ul>');
        }

        return out.join('');
    }

    function scrollToBottom() {
        if ($messages.length) {
            $messages.stop().animate({
                scrollTop: $messages[0].scrollHeight
            }, 180);
        }
    }

    function getCurrentTime() {
        var d = new Date();
        var h = d.getHours();
        var m = d.getMinutes();
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        h = h ? h : 12;
        m = m < 10 ? '0' + m : m;
        return h + ':' + m + ' ' + ampm;
    }

    function trackEvent(eventName, payload) {
        payload = payload || {};
        if (window.dataLayer && Array.isArray(window.dataLayer)) {
            window.dataLayer.push($.extend({
                event: eventName,
                chatbot: 'ayur_expert'
            }, payload));
        }
    }

    /**
     * Mobile Visual Viewport & Keyboard Handling
     * Prevents virtual keyboard from covering the message input
     */
    function setupViewportHandling() {
        if (window.visualViewport && window.matchMedia('(max-width: 768px)').matches) {
            var onViewportChange = function() {
                if (!isOpen) return;
                var vh = window.visualViewport.height;
                var offsetTop = window.visualViewport.offsetTop;
                // If keyboard is visible, adapt height and keep footer locked to bottom
                if (window.innerHeight - vh > 120) {
                    $window.css({
                        'height': vh + 'px',
                        'max-height': vh + 'px',
                        'bottom': '0px'
                    });
                    scrollToBottom();
                } else {
                    $window.css({
                        'height': '',
                        'max-height': '',
                        'bottom': ''
                    });
                }
            };

            window.visualViewport.addEventListener('resize', onViewportChange);
            window.visualViewport.addEventListener('scroll', onViewportChange);
        }
    }

    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }

    function escapeAttr(text) {
        return ('' + text).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

})(jQuery);
