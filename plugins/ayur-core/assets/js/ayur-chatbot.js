/**
 * Bhagyoday Ayurveda — AI Product Expert Chatbot Client
 *
 * Handles widget interaction, accessible keyboard controls, AJAX messaging,
 * dynamic product cards rendering, and privacy-conscious analytics tracking.
 */

(function($) {
    'use strict';

    if (typeof window.ayurChatParams === 'undefined') {
        return;
    }

    var params = window.ayurChatParams;
    var $root, $launcher, $window, $messages, $form, $input, $sendBtn, $typing, $error, $errorMsg, $retryBtn, $quickActions;
    var isOpen = false;
    var isSending = false;
    var lastFailedMessage = '';

    $(document).ready(function() {
        initElements();
        bindEvents();
        initSession();
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
        // Toggle chat window
        $launcher.on('click', toggleChat);
        $('#ayur-chat-close, #ayur-chat-minimize').on('click', closeChat);

        // Escape key to close
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && isOpen) {
                closeChat();
            }
        });

        // Submit message form
        $form.on('submit', function(e) {
            e.preventDefault();
            var text = $.trim($input.val());
            if (text.length > 0 && !isSending) {
                sendMessage(text);
            }
        });

        // Enter key to submit (Shift+Enter for newline)
        $input.on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                $form.trigger('submit');
            }
        });

        // Auto-resize input
        $input.on('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 80) + 'px';
        });

        // Retry failed message
        $retryBtn.on('click', function() {
            if (lastFailedMessage) {
                $error.hide();
                sendMessage(lastFailedMessage);
            }
        });

        // Quick action chip click
        $root.on('click', '.ayur-chat-chip', function() {
            var actionText = $(this).text().trim();
            if (actionText.indexOf('WhatsApp') !== false || actionText.indexOf('व्हाट्सएप') !== false || actionText.indexOf('व्हॉट्सॲप') !== false) {
                trackEvent('ayur_chat_whatsapp_clicked', { source: 'quick_chip' });
                window.open(params.whatsapp_url, '_blank');
                return;
            }
            sendMessage(actionText);
        });

        // Product card click tracking
        $root.on('click', '.ayur-chat-card', function() {
            var prodName = $(this).find('.ayur-chat-card-title').text();
            trackEvent('ayur_chat_product_clicked', { product_name: prodName });
        });
    }

    function initSession() {
        // Load initial welcome message if messages container is empty
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
        
        // Track chat opened
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

    function sendMessage(text) {
        if (isSending) return;

        isSending = true;
        lastFailedMessage = text;
        $input.val('').css('height', 'auto');
        $sendBtn.prop('disabled', true);
        $error.hide();

        // 1. Append User Message
        appendUserMessage(text);
        scrollToBottom();

        // 2. Track question submitted
        trackEvent('ayur_chat_question_submitted', { query_length: text.length });

        // 3. Show Typing Indicator
        $typing.show();
        scrollToBottom();

        // 4. Send AJAX Request
        $.ajax({
            url: params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'ayur_chatbot_message',
                nonce: params.nonce,
                message: text
            },
            timeout: 20000,
            success: function(res) {
                $typing.hide();
                isSending = false;
                $sendBtn.prop('disabled', false);

                if (res && res.success && res.data) {
                    var data = res.data;
                    appendBotMessage(data.message, data.products || [], true);
                    
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
                    handleError(res && res.data && res.data.message ? res.data.message : params.strings.error);
                }
            },
            error: function(xhr, status, err) {
                $typing.hide();
                isSending = false;
                $sendBtn.prop('disabled', false);

                var errText = params.strings.error;
                if (xhr.status === 429) {
                    errText = 'You are sending questions too fast. Please wait a moment.';
                }
                handleError(errText);
            }
        });
    }

    function handleError(msg) {
        $errorMsg.text(msg);
        $error.show();
        scrollToBottom();
    }

    function appendUserMessage(text) {
        var timeStr = getCurrentTime();
        var html = '<div class="ayur-chat-msg ayur-chat-msg-user">' +
                   escapeHtml(text) +
                   '<span class="ayur-chat-msg-time">' + timeStr + '</span>' +
                   '</div>';
        $messages.append(html);
    }

    function appendBotMessage(text, products, animate) {
        var timeStr = getCurrentTime();
        var formattedText = formatMarkdown(text);

        var html = '<div class="ayur-chat-msg ayur-chat-msg-bot">' +
                   formattedText;

        // Render Interactive Product Cards
        if (products && products.length > 0) {
            html += '<div class="ayur-chat-product-cards">';
            for (var i = 0; i < products.length; i++) {
                var p = products[i];
                html += '<a href="' + escapeAttr(p.permalink) + '" class="ayur-chat-card" target="_blank" rel="noopener">' +
                        '  <img src="' + escapeAttr(p.image_url) + '" alt="' + escapeAttr(p.name) + '" class="ayur-chat-card-img" />' +
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

    function renderQuickChips(chips) {
        if (!chips || chips.length === 0) return;
        var html = '';
        for (var i = 0; i < chips.length; i++) {
            var chip = chips[i];
            if (chip && chip.trim().length > 0) {
                html += '<button type="button" class="ayur-chat-chip">' + escapeHtml(chip.trim()) + '</button>';
            }
        }
        $quickActions.html(html);
    }

    function formatMarkdown(str) {
        if (!str) return '';
        
        // Escape basic HTML
        var s = str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        // Bold: **text**
        s = s.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Italic: *text*
        s = s.replace(/\*([^\*]+)\*/g, '<em>$1</em>');

        // Markdown Links: [label](url)
        s = s.replace(/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

        // Bullet lists
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
            }, 200);
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

    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }

    function escapeAttr(text) {
        return ('' + text).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

})(jQuery);
