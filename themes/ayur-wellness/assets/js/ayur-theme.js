/**
 * Ayur Wellness Theme Interactions
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initStickyHeader();
        initMobileDrawer();
        initSearchModal();
    });

    function initStickyHeader() {
        var $header = $('#ayurHeader');
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 30) {
                $header.css('box-shadow', '0 4px 16px rgba(0,0,0,0.06)');
            } else {
                $header.css('box-shadow', 'none');
            }
        });
    }

    function initMobileDrawer() {
        $('#ayurMobileMenuBtn').on('click', function(e) {
            e.preventDefault();
            $('#ayurMobileDrawer').fadeIn(200);
            $('body').css('overflow', 'hidden');
        });

        $('#ayurCloseDrawer, #ayurDrawerOverlay').on('click', function() {
            $('#ayurMobileDrawer').fadeOut(200);
            $('body').css('overflow', 'auto');
        });
    }

    function initSearchModal() {
        $('#ayurSearchToggle').on('click', function(e) {
            e.preventDefault();
            $('#ayurSearchModal').fadeIn(200);
            $('#ayurSearchModal input[type="search"]').focus();
        });

        $('#ayurCloseSearch').on('click', function() {
            $('#ayurSearchModal').fadeOut(200);
        });

        $(document).on('keyup', function(e) {
            if (e.key === 'Escape') {
                $('#ayurSearchModal').fadeOut(200);
                $('#ayurMobileDrawer').fadeOut(200);
                $('body').css('overflow', 'auto');
            }
        });
    }

})(jQuery);
