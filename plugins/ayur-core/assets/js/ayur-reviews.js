/**
 * Bhagyoday Ayurveda - Amazon-Style Customer Reviews Engine (Frontend JS)
 * Handles interactive star rating, drag & drop photo upload previews,
 * AJAX review submission, dynamic filtering/sorting, helpfulness votes,
 * report modal, and touch-enabled photo lightbox.
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initWriteModal();
        initInteractiveRating();
        initPhotoUploader();
        initReviewSubmission();
        initFiltersAndSorting();
        initHelpfulVoting();
        initReportModal();
        initPhotoLightbox();
    });

    const RATING_TEXTS = {
        1: '1 Star — Poor experience',
        2: '2 Stars — Below expectations',
        3: '3 Stars — Average classical results',
        4: '4 Stars — Good formulation, satisfied',
        5: '5 Stars — Excellent classical formulation'
    };

    /**
     * 1. Write a Review Modal Toggle
     */
    function initWriteModal() {
        const modal = $('#ayurWriteReviewModal');
        if (!modal.length) return;

        $(document).on('click', '#ayurOpenWriteModalBtn, #ayurOpenEmptyWriteBtn', function(e) {
            e.preventDefault();
            modal.addClass('is-open').attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        });

        $(document).on('click', '#ayurCloseWriteModalBtn, #ayurWriteModalBackdrop', function() {
            closeWriteModal();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && modal.hasClass('is-open')) {
                closeWriteModal();
            }
        });

        function closeWriteModal() {
            modal.removeClass('is-open').attr('aria-hidden', 'true');
            $('body').css('overflow', '');
        }
    }

    /**
     * 2. Interactive Star Rating Selector
     */
    function initInteractiveRating() {
        const wrap = $('#ayurInteractiveRating');
        if (!wrap.length) return;

        let selectedRating = 5;
        updateStars(selectedRating);

        wrap.find('.ayur-star-choice').on('mouseenter', function() {
            const val = parseInt($(this).data('value'), 10);
            highlightStars(val);
            $('#ayurRatingText').text(RATING_TEXTS[val] || `${val} Stars`);
        });

        wrap.on('mouseleave', function() {
            highlightStars(selectedRating);
            $('#ayurRatingText').text(RATING_TEXTS[selectedRating] || `${selectedRating} Stars`);
        });

        wrap.find('.ayur-star-choice').on('click', function(e) {
            e.preventDefault();
            selectedRating = parseInt($(this).data('value'), 10);
            $('#ayurRatingInput').val(selectedRating);
            updateStars(selectedRating);
        });

        function highlightStars(val) {
            wrap.find('.ayur-star-choice').each(function() {
                const s = parseInt($(this).data('value'), 10);
                if (s <= val) {
                    $(this).addClass('is-hover');
                } else {
                    $(this).removeClass('is-hover');
                }
            });
        }

        function updateStars(val) {
            wrap.find('.ayur-star-choice').each(function() {
                const s = parseInt($(this).data('value'), 10);
                if (s <= val) {
                    $(this).addClass('is-selected');
                } else {
                    $(this).removeClass('is-selected');
                }
            });
            $('#ayurRatingText').text(RATING_TEXTS[val] || `${val} Stars`);
        }
    }

    /**
     * 3. Multi-Photo Uploader & Client Previews
     */
    function initPhotoUploader() {
        const dropZone = $('#ayurDropZone');
        const fileInput = $('#ayurPhotoFiles');
        const previewWrap = $('#ayurPhotoPreviews');
        if (!dropZone.length || !fileInput.length) return;

        dropZone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.addClass('is-dragover');
        });

        dropZone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.removeClass('is-dragover');
        });

        dropZone.on('drop', function(e) {
            const dt = e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files.length) {
                fileInput[0].files = dt.files;
                renderPreviews(dt.files);
            }
        });

        fileInput.on('change', function() {
            if (this.files && this.files.length) {
                renderPreviews(this.files);
            }
        });

        function renderPreviews(files) {
            previewWrap.empty();
            const max = Math.min(5, files.length);

            for (let i = 0; i < max; i++) {
                const file = files[i];
                if (!file.type.match('image.*')) continue;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const item = $(`
                        <div class="ayur-preview-item">
                            <img src="${e.target.result}" alt="Preview">
                        </div>
                    `);
                    previewWrap.append(item);
                };
                reader.readAsDataURL(file);
            }
        }
    }

    /**
     * 4. Review Submission with Client & Server Validation
     */
    function initReviewSubmission() {
        const form = $('#ayurReviewForm');
        if (!form.length) return;

        form.on('submit', function(e) {
            e.preventDefault();

            const btn = $('#ayurSubmitReviewBtn');
            const resp = $('#ayurFormResponse');

            btn.addClass('is-submitting').prop('disabled', true);
            resp.hide().removeClass('is-success is-error');

            const formData = new FormData(this);

            $.ajax({
                url: ayurReviewsParams ? ayurReviewsParams.ajax_url : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    btn.removeClass('is-submitting').prop('disabled', false);
                    if (res && res.success) {
                        resp.addClass('is-success').html(res.data.message || 'Review submitted successfully.').fadeIn();
                        form[0].reset();
                        $('#ayurPhotoPreviews').empty();
                        setTimeout(function() {
                            $('#ayurWriteReviewModal').removeClass('is-open').attr('aria-hidden', 'true');
                            $('body').css('overflow', '');
                        }, 3500);
                    } else {
                        const msg = (res && res.data && res.data.message) ? res.data.message : 'An error occurred. Please try again.';
                        resp.addClass('is-error').html(msg).fadeIn();
                    }
                },
                error: function() {
                    btn.removeClass('is-submitting').prop('disabled', false);
                    resp.addClass('is-error').html('Connection error. Please try again later.').fadeIn();
                }
            });
        });
    }

    /**
     * 5. Filters & Sorting Controls (Real-Time AJAX)
     */
    function initFiltersAndSorting() {
        const feed = $('#ayurReviewsFeed');
        if (!feed.length) return;

        const productId = feed.data('product-id');
        let currentStar = 'all';
        let currentSort = 'recent';
        let photosOnly = false;
        let verifiedOnly = false;
        let currentPage = 1;

        // Filter chip clicks
        $(document).on('click', '.ayur-filter-chip', function(e) {
            e.preventDefault();
            const star = $(this).data('star');
            const photos = $(this).data('photos');
            const verified = $(this).data('verified');

            if (star !== undefined) {
                $('.ayur-filter-chip[data-star]').removeClass('is-active');
                $(this).addClass('is-active');
                currentStar = star;
            } else if (photos !== undefined) {
                $(this).toggleClass('is-active');
                photosOnly = $(this).hasClass('is-active');
            } else if (verified !== undefined) {
                $(this).toggleClass('is-active');
                verifiedOnly = $(this).hasClass('is-active');
            }

            currentPage = 1;
            fetchFilteredReviews();
        });

        // Distribution bar click as direct filter
        $(document).on('click', '.ayur-dist-row', function(e) {
            e.preventDefault();
            const star = $(this).data('filter-star');
            if (star) {
                $('.ayur-filter-chip[data-star]').removeClass('is-active');
                $(`.ayur-filter-chip[data-star="${star}"]`).addClass('is-active');
                currentStar = star;
                currentPage = 1;
                fetchFilteredReviews();
            }
        });

        // Reset filter button
        $(document).on('click', '#ayurResetFiltersBtn', function(e) {
            e.preventDefault();
            currentStar = 'all';
            photosOnly = false;
            verifiedOnly = false;
            $('.ayur-filter-chip').removeClass('is-active');
            $('.ayur-filter-chip[data-star="all"]').addClass('is-active');
            currentPage = 1;
            fetchFilteredReviews();
        });

        // Sort dropdown change
        $(document).on('change', '#ayurReviewsSort', function() {
            currentSort = $(this).val();
            currentPage = 1;
            fetchFilteredReviews();
        });

        // Load more reviews button
        $(document).on('click', '#ayurLoadMoreReviewsBtn', function(e) {
            e.preventDefault();
            const btn = $(this);
            btn.addClass('is-submitting').prop('disabled', true);
            currentPage++;

            $.ajax({
                url: ayurReviewsParams ? ayurReviewsParams.ajax_url : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'ayur_filter_reviews',
                    product_id: productId,
                    rating: currentStar,
                    sort: currentSort,
                    photos_only: photosOnly ? 1 : 0,
                    verified_only: verifiedOnly ? 1 : 0,
                    page: currentPage
                },
                success: function(res) {
                    btn.removeClass('is-submitting').prop('disabled', false);
                    if (res && res.success && res.data) {
                        feed.append(res.data.html);
                        if (!res.data.has_more) {
                            btn.parent().hide();
                        }
                    }
                },
                error: function() {
                    btn.removeClass('is-submitting').prop('disabled', false);
                }
            });
        });

        function fetchFilteredReviews() {
            feed.addClass('is-loading');

            $.ajax({
                url: ayurReviewsParams ? ayurReviewsParams.ajax_url : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'ayur_filter_reviews',
                    product_id: productId,
                    rating: currentStar,
                    sort: currentSort,
                    photos_only: photosOnly ? 1 : 0,
                    verified_only: verifiedOnly ? 1 : 0,
                    page: 1
                },
                success: function(res) {
                    feed.removeClass('is-loading');
                    if (res && res.success && res.data) {
                        feed.html(res.data.html);
                        const loadMoreWrap = $('.ayur-reviews-loadmore-wrap');
                        if (res.data.has_more) {
                            loadMoreWrap.show();
                            $('#ayurLoadMoreReviewsBtn').data('page', 1).show();
                        } else {
                            loadMoreWrap.hide();
                        }
                    }
                },
                error: function() {
                    feed.removeClass('is-loading');
                }
            });
        }
    }

    /**
     * 6. Helpful Vote Handling with Anti-Abuse
     */
    function initHelpfulVoting() {
        $(document).on('click', '.ayur-btn-helpful', function(e) {
            e.preventDefault();
            const btn = $(this);
            const reviewId = btn.data('review-id');
            const localKey = `ayur_voted_rev_${reviewId}`;

            if (localStorage.getItem(localKey)) {
                alert('You have already marked this review as helpful.');
                return;
            }

            btn.prop('disabled', true);

            $.ajax({
                url: ayurReviewsParams ? ayurReviewsParams.ajax_url : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'ayur_vote_helpful',
                    review_id: reviewId,
                    nonce: ayurReviewsParams ? ayurReviewsParams.nonce : ''
                },
                success: function(res) {
                    if (res && res.success) {
                        localStorage.setItem(localKey, '1');
                        btn.addClass('is-voted');
                        btn.find('.ayur-helpful-count').text(`(${res.data.count})`);
                    } else {
                        btn.prop('disabled', false);
                        alert(res.data.message || 'Unable to record vote.');
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                }
            });
        });
    }

    /**
     * 7. Report Review Modal & Submission
     */
    function initReportModal() {
        const modal = $('#ayurReportModal');
        if (!modal.length) return;

        $(document).on('click', '.ayur-btn-report', function(e) {
            e.preventDefault();
            const reviewId = $(this).data('review-id');
            $('#ayurReportReviewId').val(reviewId);
            $('#ayurReportResponse').hide().empty();
            modal.addClass('is-open').attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        });

        $(document).on('click', '#ayurCloseReportModalBtn, #ayurReportModalBackdrop', function() {
            closeReportModal();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && modal.hasClass('is-open')) {
                closeReportModal();
            }
        });

        function closeReportModal() {
            modal.removeClass('is-open').attr('aria-hidden', 'true');
            $('body').css('overflow', '');
        }

        $('#ayurReportForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#ayurSubmitReportBtn');
            const resp = $('#ayurReportResponse');

            btn.prop('disabled', true);
            resp.hide().removeClass('is-success is-error');

            $.ajax({
                url: ayurReviewsParams ? ayurReviewsParams.ajax_url : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false);
                    if (res && res.success) {
                        resp.addClass('is-success').html(res.data.message).fadeIn();
                        setTimeout(closeReportModal, 2000);
                    } else {
                        resp.addClass('is-error').html(res.data.message || 'Submission failed.').fadeIn();
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                    resp.addClass('is-error').html('Network error. Please try again.').fadeIn();
                }
            });
        });
    }

    /**
     * 8. Customer Photo Lightbox Modal
     */
    function initPhotoLightbox() {
        const lightbox = $('#ayurReviewPhotoLightbox');
        const img = $('#ayurLightboxFullImg');
        const caption = $('#ayurLightboxCaption');
        if (!lightbox.length || !img.length) return;

        $(document).on('click', '.ayur-gallery-thumb-btn', function(e) {
            e.preventDefault();
            const fullUrl = $(this).data('full-img');
            const capText = $(this).data('caption') || '';

            img.attr('src', fullUrl);
            caption.text(capText);
            lightbox.addClass('is-open').attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        });

        $(document).on('click', '#ayurPhotoLightboxClose, #ayurPhotoLightboxBackdrop', function() {
            closeLightbox();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.hasClass('is-open')) {
                closeLightbox();
            }
        });

        function closeLightbox() {
            lightbox.removeClass('is-open').attr('aria-hidden', 'true');
            img.attr('src', '');
            $('body').css('overflow', '');
        }
    }

})(jQuery);
