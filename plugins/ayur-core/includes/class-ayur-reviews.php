<?php
/**
 * Ayur Core - Amazon-Style Customer Reviews Engine
 * Authentic, factual review system with server-side verified purchase validation,
 * customer photo galleries, helpfulness voting, abuse reporting, moderation queue,
 * CSV import, automated review requests, and valid Schema.org aggregateRating.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Reviews {

    const COMMENT_TYPE = 'review';
    const MAX_PHOTOS = 5;
    const MAX_PHOTO_SIZE = 5242880; // 5MB

    public static function init() {
        // Admin menus & hooks
        add_action('admin_menu', array(__CLASS__, 'register_admin_menus'), 20);
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));

        // AJAX handlers for frontend
        add_action('wp_ajax_ayur_submit_review', array(__CLASS__, 'ajax_submit_review'));
        add_action('wp_ajax_nopriv_ayur_submit_review', array(__CLASS__, 'ajax_submit_review'));

        add_action('wp_ajax_ayur_filter_reviews', array(__CLASS__, 'ajax_filter_reviews'));
        add_action('wp_ajax_nopriv_ayur_filter_reviews', array(__CLASS__, 'ajax_filter_reviews'));

        add_action('wp_ajax_ayur_vote_helpful', array(__CLASS__, 'ajax_vote_helpful'));
        add_action('wp_ajax_nopriv_ayur_vote_helpful', array(__CLASS__, 'ajax_vote_helpful'));

        add_action('wp_ajax_ayur_report_review', array(__CLASS__, 'ajax_report_review'));
        add_action('wp_ajax_nopriv_ayur_report_review', array(__CLASS__, 'ajax_report_review'));

        // Admin actions (bulk moderation, CSV import, status changes)
        add_action('admin_post_ayur_review_action', array(__CLASS__, 'handle_admin_actions'));
        add_action('admin_post_ayur_import_reviews', array(__CLASS__, 'handle_csv_import'));
        add_action('admin_post_ayur_save_review', array(__CLASS__, 'handle_save_review'));
        add_action('admin_post_ayur_save_request_settings', array(__CLASS__, 'handle_save_request_settings'));

        // Automated Review Request Trigger on completed order
        add_action('woocommerce_order_status_completed', array(__CLASS__, 'schedule_order_review_request'), 10, 1);
        add_action('ayur_send_scheduled_review_request', array(__CLASS__, 'process_scheduled_review_request'), 10, 1);

        // SEO Schema Output
        add_action('wp_head', array(__CLASS__, 'output_schema_jsonld'), 30);

    }

    /**
     * Register Admin Menus
     * 1. Under top-level 'Ayurveda Core'
     * 2. Under WooCommerce 'Products' submenu
     */
    public static function register_admin_menus() {
        // Top-level Ayurveda Core Hub
        add_menu_page(
            __('Ayurveda Core', 'ayur-core'),
            __('Ayurveda Core', 'ayur-core'),
            'manage_woocommerce',
            'ayur-core-hub',
            array(__CLASS__, 'render_admin_reviews_page'),
            'dashicons-shield-alt',
            56
        );

        // Submenu: Customer Reviews (default under Ayurveda Core)
        add_submenu_page(
            'ayur-core-hub',
            __('Customer Reviews', 'ayur-core'),
            __('Customer Reviews', 'ayur-core'),
            'manage_woocommerce',
            'ayur-reviews',
            array(__CLASS__, 'render_admin_reviews_page')
        );

        // Remove duplicate hub submenu
        remove_submenu_page('ayur-core-hub', 'ayur-core-hub');

        // Also add submenu under WooCommerce Products
        add_submenu_page(
            'edit.php?post_type=product',
            __('Customer Reviews', 'ayur-core'),
            __('Customer Reviews', 'ayur-core'),
            'manage_woocommerce',
            'ayur-reviews-wc',
            array(__CLASS__, 'render_admin_reviews_page')
        );
    }

    /**
     * Enqueue Admin Assets for Reviews Management
     */
    public static function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ayur-reviews') !== false || strpos($hook, 'ayur-core-hub') !== false) {
            wp_enqueue_media();
            wp_enqueue_style('ayur-reviews-admin', AYUR_CORE_URL . 'assets/css/ayur-reviews-admin.css', array(), AYUR_CORE_VERSION);
            wp_enqueue_script('ayur-reviews-admin', AYUR_CORE_URL . 'assets/js/ayur-reviews-admin.js', array('jquery'), AYUR_CORE_VERSION, true);
            wp_localize_script('ayur-reviews-admin', 'ayurReviewsAdmin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('ayur_reviews_admin_nonce'),
            ));
        }
    }

    /* ==========================================================================
       VERIFIED PURCHASE SERVER-SIDE VALIDATION
       ========================================================================== */

    /**
     * Verify whether an email address or customer ID legitimately purchased a product
     * Returns matching order ID on success, or false on failure.
     */
    public static function verify_purchase($email_or_user_id, $product_id) {
        if (empty($email_or_user_id) || empty($product_id) || !function_exists('wc_get_orders')) {
            return false;
        }

        $product_id = absint($product_id);
        $args = array(
            'status' => array('wc-completed', 'wc-processing'),
            'limit'  => 50,
            'return' => 'ids',
        );

        if (is_numeric($email_or_user_id) && $email_or_user_id > 0) {
            $args['customer_id'] = absint($email_or_user_id);
        } elseif (is_email($email_or_user_id)) {
            $args['billing_email'] = sanitize_email($email_or_user_id);
        } else {
            return false;
        }

        $order_ids = wc_get_orders($args);
        if (empty($order_ids)) {
            return false;
        }

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if (!$order) {
                continue;
            }

            foreach ($order->get_items() as $item) {
                $item_prod_id = $item->get_product_id();
                $item_var_id  = $item->get_variation_id();

                if ($item_prod_id === $product_id || $item_var_id === $product_id) {
                    return $order_id;
                }
            }
        }

        return false;
    }

    /* ==========================================================================
       REVIEW SUMMARY & RATING CALCULATIONS (DATABASE-DRIVEN ONLY)
       ========================================================================== */

    /**
     * Get aggregate statistics for a product directly from the approved reviews database.
     * Never returns fake or hardcoded ratings.
     */
    public static function get_product_review_summary($product_id) {
        $product_id = absint($product_id);

        $args = array(
            'post_id' => $product_id,
            'status'  => 'approve',
            'type'    => self::COMMENT_TYPE,
            'count'   => false,
        );

        $comments = get_comments($args);
        if (empty($comments)) {
            $comments = get_comments(array(
                'post_id' => $product_id,
                'status'  => 'approve',
            ));
        }
        if (empty($comments)) {
            global $wpdb;
            $comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type = %s ORDER BY comment_date_gmt DESC", $product_id, self::COMMENT_TYPE));
        }
        $total = count($comments);

        $counts = array(
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        );

        $sum = 0;
        $photo_count = 0;
        $verified_count = 0;

        foreach ($comments as $c) {
            $rating = absint(get_comment_meta($c->comment_ID, 'rating', true));
            if ($rating >= 1 && $rating <= 5) {
                $counts[$rating]++;
                $sum += $rating;
            }

            $photos = get_comment_meta($c->comment_ID, 'review_photos', true);
            if (!empty($photos) && is_array($photos)) {
                $photo_count++;
            }

            $is_verified = get_comment_meta($c->comment_ID, 'verified', true);
            if (!empty($is_verified)) {
                $verified_count++;
            }
        }

        $average = $total > 0 ? round($sum / $total, 1) : 0.0;

        $percentages = array();
        foreach ($counts as $star => $count) {
            $percentages[$star] = $total > 0 ? round(($count / $total) * 100) : 0;
        }

        return array(
            'total'             => $total,
            'average'           => $average,
            'counts'            => $counts,
            'percentages'       => $percentages,
            'photo_count'       => $photo_count,
            'verified_count'    => $verified_count,
        );
    }

    /* ==========================================================================
       FRONTEND AJAX ENDPOINTS
       ========================================================================== */

    /**
     * AJAX Submit Review Handler
     */
    public static function ajax_submit_review() {
        check_ajax_referer('ayur_review_nonce', 'nonce');

        // Spam Honeypot check
        if (!empty($_POST['ayur_hp_check'])) {
            wp_send_json_error(array('message' => __('Invalid submission detected.', 'ayur-core')));
        }

        // Rate limiting: max 3 reviews per hour per IP
        $ip_hash = md5($_SERVER['REMOTE_ADDR'] . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
        $rl_key = 'ayur_rl_rev_' . $ip_hash;
        $submissions = (int) get_transient($rl_key);
        if ($submissions >= 3) {
            wp_send_json_error(array('message' => __('You have submitted multiple reviews recently. Please wait before submitting another.', 'ayur-core')));
        }

        // Sanitize inputs
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $rating     = isset($_POST['rating']) ? min(5, max(1, absint($_POST['rating']))) : 5;
        $title      = isset($_POST['review_title']) ? sanitize_text_field(wp_unslash($_POST['review_title'])) : '';
        $content    = isset($_POST['review_content']) ? sanitize_textarea_field(wp_unslash($_POST['review_content'])) : '';
        $name       = isset($_POST['reviewer_name']) ? sanitize_text_field(wp_unslash($_POST['reviewer_name'])) : '';
        $email      = isset($_POST['reviewer_email']) ? sanitize_email(wp_unslash($_POST['reviewer_email'])) : '';

        if (empty($product_id) || !get_post($product_id)) {
            wp_send_json_error(array('message' => __('Selected product does not exist.', 'ayur-core')));
        }

        if (empty($name) || strlen($name) < 2) {
            wp_send_json_error(array('message' => __('Please provide your name.', 'ayur-core')));
        }

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => __('Please provide a valid email address.', 'ayur-core')));
        }

        if (empty($title) || strlen($title) < 3) {
            wp_send_json_error(array('message' => __('Please provide a review title (at least 3 characters).', 'ayur-core')));
        }

        if (empty($content) || strlen($content) < 10) {
            wp_send_json_error(array('message' => __('Please provide your review content (at least 10 characters).', 'ayur-core')));
        }

        // Check verified purchase against real WooCommerce orders
        $user_id = get_current_user_id();
        $verified_order_id = self::verify_purchase($user_id ?: $email, $product_id);
        $is_verified = $verified_order_id ? 1 : 0;

        // Process photo uploads safely
        $uploaded_photos = array();
        if (!empty($_FILES['review_photos']) && is_array($_FILES['review_photos']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $files = $_FILES['review_photos'];
            $count = min(self::MAX_PHOTOS, count($files['name']));

            for ($i = 0; $i < $count; $i++) {
                if (empty($files['name'][$i]) || $files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                if ($files['size'][$i] > self::MAX_PHOTO_SIZE) {
                    continue;
                }

                $file_info = wp_check_filetype_and_ext($files['tmp_name'][$i], $files['name'][$i]);
                $allowed_mimes = array('image/jpeg', 'image/png', 'image/webp');
                if (!in_array($file_info['type'], $allowed_mimes, true)) {
                    continue;
                }

                $single_file = array(
                    'name'     => sanitize_file_name($files['name'][$i]),
                    'type'     => $file_info['type'],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                );

                $upload_overrides = array('test_form' => false);
                $upload_result = wp_handle_upload($single_file, $upload_overrides);

                if (!empty($upload_result['file']) && empty($upload_result['error'])) {
                    $attachment = array(
                        'post_mime_type' => $upload_result['type'],
                        'post_title'     => sanitize_text_field(pathinfo($single_file['name'], PATHINFO_FILENAME)),
                        'post_content'   => '',
                        'post_status'    => 'inherit',
                    );

                    $attach_id = wp_insert_attachment($attachment, $upload_result['file'], $product_id);
                    if ($attach_id && !is_wp_error($attach_id)) {
                        $attach_data = wp_generate_attachment_metadata($attach_id, $upload_result['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                        $uploaded_photos[] = $attach_id;
                    }
                }
            }
        }

        // Insert Comment as WooCommerce Review (Pending Moderation)
        $comment_data = array(
            'comment_post_ID'      => $product_id,
            'comment_author'       => $name,
            'comment_author_email' => $email,
            'comment_content'      => $content,
            'comment_type'         => self::COMMENT_TYPE,
            'comment_approved'     => 0, // Defaults to Pending
            'user_id'              => $user_id,
        );

        $comment_id = wp_insert_comment($comment_data);

        if (!$comment_id) {
            wp_send_json_error(array('message' => __('Unable to save review. Please try again.', 'ayur-core')));
        }

        // Save Custom Comment Metadata
        update_comment_meta($comment_id, 'rating', $rating);
        update_comment_meta($comment_id, 'review_title', $title);
        update_comment_meta($comment_id, 'verified', $is_verified);
        if ($verified_order_id) {
            update_comment_meta($comment_id, 'order_id', $verified_order_id);
        }
        update_comment_meta($comment_id, 'review_source', 'website');
        update_comment_meta($comment_id, 'helpful_count', 0);
        update_comment_meta($comment_id, 'report_count', 0);
        if (!empty($uploaded_photos)) {
            update_comment_meta($comment_id, 'review_photos', $uploaded_photos);
        }

        // Increment rate limit transient
        set_transient($rl_key, $submissions + 1, HOUR_IN_SECONDS);

        // Clear WooCommerce transients for this product
        if (class_exists('WC_Comments')) {
            WC_Comments::clear_transients($product_id);
        }

        global $wpdb;
        $wpdb->query("COMMIT");

        wp_send_json_success(array(
            'message'    => __('Thank you! Your review has been submitted and will appear once verified by our moderation team.', 'ayur-core'),
            'verified'   => $is_verified === 1,
            'comment_id' => $comment_id,
        ));
    }

    /**
     * AJAX Filter & Sort Reviews Handler
     */
    public static function ajax_filter_reviews() {
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $rating     = isset($_POST['rating']) ? sanitize_text_field($_POST['rating']) : 'all';
        $sort       = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'recent';
        $verified   = !empty($_POST['verified_only']);
        $photos     = !empty($_POST['photos_only']);
        $page       = isset($_POST['page']) ? max(1, absint($_POST['page'])) : 1;
        $per_page   = 6;

        if (empty($product_id)) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'ayur-core')));
        }

        $query_args = array(
            'post_id' => $product_id,
            'status'  => 'approve',
            'type'    => self::COMMENT_TYPE,
            'number'  => $per_page,
            'offset'  => ($page - 1) * $per_page,
        );

        $meta_query = array('relation' => 'AND');

        // Star filter
        if ($rating !== 'all' && in_array((int)$rating, array(1, 2, 3, 4, 5), true)) {
            $meta_query[] = array(
                'key'     => 'rating',
                'value'   => (int)$rating,
                'compare' => '=',
                'type'    => 'NUMERIC',
            );
        }

        // Verified filter
        if ($verified) {
            $meta_query[] = array(
                'key'     => 'verified',
                'value'   => 1,
                'compare' => '=',
                'type'    => 'NUMERIC',
            );
        }

        // Photos filter
        if ($photos) {
            $meta_query[] = array(
                'key'     => 'review_photos',
                'compare' => 'EXISTS',
            );
        }

        if (count($meta_query) > 1) {
            $query_args['meta_query'] = $meta_query;
        }

        // Sorting
        switch ($sort) {
            case 'highest':
                $query_args['meta_key'] = 'rating';
                $query_args['orderby']  = 'meta_value_num';
                $query_args['order']    = 'DESC';
                break;
            case 'lowest':
                $query_args['meta_key'] = 'rating';
                $query_args['orderby']  = 'meta_value_num';
                $query_args['order']    = 'ASC';
                break;
            case 'helpful':
                $query_args['meta_key'] = 'helpful_count';
                $query_args['orderby']  = 'meta_value_num';
                $query_args['order']    = 'DESC';
                break;
            case 'recent':
            default:
                $query_args['orderby'] = 'comment_date';
                $query_args['order']   = 'DESC';
                break;
        }

        $reviews = get_comments($query_args);

        // Count total matching
        $count_args = $query_args;
        unset($count_args['number'], $count_args['offset']);
        $count_args['count'] = true;
        $total_matching = get_comments($count_args);

        ob_start();
        if (!empty($reviews)) {
            foreach ($reviews as $rev) {
                self::render_review_card($rev);
            }
        } else {
            ?>
            <div class="ayur-reviews-empty-filter">
                <p><?php _e('No reviews match your selected filter criteria.', 'ayur-core'); ?></p>
                <button type="button" class="ayur-reset-filters-btn" id="ayurResetFiltersBtn"><?php _e('Clear All Filters', 'ayur-core'); ?></button>
            </div>
            <?php
        }
        $html = ob_get_clean();

        $has_more = ($page * $per_page) < $total_matching;

        wp_send_json_success(array(
            'html'           => $html,
            'total_matching' => $total_matching,
            'has_more'       => $has_more,
            'page'           => $page,
        ));
    }

    /**
     * AJAX Vote Helpful Handler
     */
    public static function ajax_vote_helpful() {
        check_ajax_referer('ayur_review_nonce', 'nonce');

        $review_id = isset($_POST['review_id']) ? absint($_POST['review_id']) : 0;
        if (empty($review_id)) {
            wp_send_json_error(array('message' => __('Invalid review ID.', 'ayur-core')));
        }

        $ip_hash = md5($_SERVER['REMOTE_ADDR'] . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
        $vote_key = 'ayur_hp_vote_' . $review_id . '_' . $ip_hash;

        if (get_transient($vote_key)) {
            wp_send_json_error(array('message' => __('You have already voted on this review.', 'ayur-core')));
        }

        $current_helpful = (int) get_comment_meta($review_id, 'helpful_count', true);
        $new_helpful = $current_helpful + 1;
        update_comment_meta($review_id, 'helpful_count', $new_helpful);

        // Expire vote prevention in 30 days
        set_transient($vote_key, 1, 30 * DAY_IN_SECONDS);

        global $wpdb;
        $wpdb->query("COMMIT");

        wp_send_json_success(array(
            'count'   => $new_helpful,
            'message' => __('Thank you for your feedback.', 'ayur-core'),
        ));
    }

    /**
     * AJAX Report Review Handler
     */
    public static function ajax_report_review() {
        check_ajax_referer('ayur_review_nonce', 'nonce');

        $review_id = isset($_POST['review_id']) ? absint($_POST['review_id']) : 0;
        $reason    = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : 'other';
        $note      = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';

        if (empty($review_id)) {
            wp_send_json_error(array('message' => __('Invalid review ID.', 'ayur-core')));
        }

        $ip_hash = md5($_SERVER['REMOTE_ADDR'] . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
        $rep_key = 'ayur_rep_' . $review_id . '_' . $ip_hash;

        if (get_transient($rep_key)) {
            wp_send_json_error(array('message' => __('You have already submitted a report for this review.', 'ayur-core')));
        }

        $reports = get_comment_meta($review_id, 'reports', true);
        if (!is_array($reports)) {
            $reports = array();
        }

        $reports[] = array(
            'date'   => current_time('mysql'),
            'reason' => $reason,
            'note'   => $note,
            'ip'     => $ip_hash,
        );

        update_comment_meta($review_id, 'reports', $reports);
        update_comment_meta($review_id, 'report_count', count($reports));

        set_transient($rep_key, 1, 7 * DAY_IN_SECONDS);

        global $wpdb;
        $wpdb->query("COMMIT");

        wp_send_json_success(array(
            'message' => __('Thank you. Your report has been submitted to our moderation team.', 'ayur-core'),
        ));
    }

    /* ==========================================================================
       FRONTEND TEMPLATE RENDERING
       ========================================================================== */

    /**
     * Render the Amazon-style Customer Reviews UI on the single product page
     */
    public static function render_product_reviews($product_id) {
        $product_id = absint($product_id);
        $summary = self::get_product_review_summary($product_id);

        $initial_reviews = get_comments(array(
            'post_id' => $product_id,
            'status'  => 'approve',
            'type'    => self::COMMENT_TYPE,
            'number'  => 6,
            'orderby' => 'comment_date',
            'order'   => 'DESC',
        ));
        if (empty($initial_reviews)) {
            $initial_reviews = get_comments(array(
                'post_id' => $product_id,
                'status'  => 'approve',
                'number'  => 6,
                'orderby' => 'comment_date',
                'order'   => 'DESC',
            ));
        }
        if (empty($initial_reviews)) {
            global $wpdb;
            $initial_reviews = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type = %s ORDER BY comment_date_gmt DESC LIMIT 6", $product_id, self::COMMENT_TYPE));
        }

        ?>
        <section class="ayur-customer-reviews-section" id="ayurCustomerReviews" aria-label="<?php esc_attr_e('Customer Reviews', 'ayur-core'); ?>">
            <div class="ayur-reviews-container">
                <div class="ayur-reviews-header">
                    <span class="ayur-reviews-eyebrow"><?php _e('GENUINE PATIENT EXPERIENCES', 'ayur-core'); ?></span>
                    <h2 class="ayur-reviews-title"><?php _e('Customer Reviews', 'ayur-core'); ?></h2>
                </div>

                <!-- Rating Summary & Breakdown Grid (Amazon-Style) -->
                <div class="ayur-reviews-summary-grid">
                    <!-- Left: Large Score & Write Review CTA -->
                    <div class="ayur-summary-score-card">
                        <div class="ayur-score-display">
                            <span class="ayur-score-number"><?php echo $summary['total'] > 0 ? number_format($summary['average'], 1) : '0.0'; ?></span>
                            <div class="ayur-score-stars-col">
                                <div class="ayur-stars-row" aria-label="<?php echo esc_attr(sprintf(__('%s out of 5 stars', 'ayur-core'), $summary['average'])); ?>">
                                    <?php echo self::render_star_rating($summary['average']); ?>
                                </div>
                                <span class="ayur-score-count">
                                    <?php
                                    if ($summary['total'] > 0) {
                                        printf(_n('Based on %d verified review', 'Based on %d verified reviews', $summary['total'], 'ayur-core'), $summary['total']);
                                    } else {
                                        _e('No reviews yet. Be the first to review!', 'ayur-core');
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($summary['total'] > 0) : ?>
                            <div class="ayur-summary-pills">
                                <?php if ($summary['verified_count'] > 0) : ?>
                                    <span class="ayur-summary-pill">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        <?php printf(__('%d Verified Purchases', 'ayur-core'), $summary['verified_count']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($summary['photo_count'] > 0) : ?>
                                    <span class="ayur-summary-pill">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        <?php printf(__('%d Photo Reviews', 'ayur-core'), $summary['photo_count']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <button type="button" class="ayur-btn-write-review" id="ayurOpenWriteModalBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            <span><?php _e('Write a Customer Review', 'ayur-core'); ?></span>
                        </button>
                    </div>

                    <!-- Right: Star Rating Distribution Bars -->
                    <div class="ayur-summary-distribution-card">
                        <h4 class="ayur-dist-heading"><?php _e('Rating Distribution', 'ayur-core'); ?></h4>
                        <div class="ayur-dist-bars-list">
                            <?php for ($star = 5; $star >= 1; $star--) : 
                                $pct = $summary['percentages'][$star];
                                $count = $summary['counts'][$star];
                            ?>
                                <button type="button" class="ayur-dist-row" data-filter-star="<?php echo $star; ?>" aria-label="<?php echo esc_attr(sprintf(__('%d star reviews (%d%%)', 'ayur-core'), $star, $pct)); ?>">
                                    <span class="ayur-dist-label"><?php echo $star; ?> ★</span>
                                    <div class="ayur-dist-bar-track">
                                        <div class="ayur-dist-bar-fill" style="width: <?php echo $pct; ?>%;"></div>
                                    </div>
                                    <span class="ayur-dist-pct"><?php echo $pct; ?>%</span>
                                    <span class="ayur-dist-num">(<?php echo $count; ?>)</span>
                                </button>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Review Filter & Sorting Bar -->
                <div class="ayur-reviews-filter-bar">
                    <div class="ayur-filter-chips-row" role="radiogroup" aria-label="<?php esc_attr_e('Filter Reviews', 'ayur-core'); ?>">
                        <button type="button" class="ayur-filter-chip is-active" data-star="all"><?php _e('All Stars', 'ayur-core'); ?></button>
                        <button type="button" class="ayur-filter-chip" data-star="5">5 ★ (<?php echo $summary['counts'][5]; ?>)</button>
                        <button type="button" class="ayur-filter-chip" data-star="4">4 ★ (<?php echo $summary['counts'][4]; ?>)</button>
                        <button type="button" class="ayur-filter-chip" data-star="3">3 ★ (<?php echo $summary['counts'][3]; ?>)</button>
                        <button type="button" class="ayur-filter-chip" data-star="2">2 ★ (<?php echo $summary['counts'][2]; ?>)</button>
                        <button type="button" class="ayur-filter-chip" data-star="1">1 ★ (<?php echo $summary['counts'][1]; ?>)</button>
                        <?php if ($summary['photo_count'] > 0) : ?>
                            <button type="button" class="ayur-filter-chip" data-photos="1"><?php _e('With Photos', 'ayur-core'); ?></button>
                        <?php endif; ?>
                        <?php if ($summary['verified_count'] > 0) : ?>
                            <button type="button" class="ayur-filter-chip" data-verified="1"><?php _e('Verified Only', 'ayur-core'); ?></button>
                        <?php endif; ?>
                    </div>

                    <div class="ayur-sort-wrap">
                        <label for="ayurReviewsSort" class="ayur-sort-label"><?php _e('Sort by:', 'ayur-core'); ?></label>
                        <select id="ayurReviewsSort" class="ayur-reviews-sort-select">
                            <option value="recent"><?php _e('Most Recent', 'ayur-core'); ?></option>
                            <option value="highest"><?php _e('Highest Rated', 'ayur-core'); ?></option>
                            <option value="lowest"><?php _e('Lowest Rated', 'ayur-core'); ?></option>
                            <option value="helpful"><?php _e('Most Helpful', 'ayur-core'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Reviews Feed Container -->
                <div class="ayur-reviews-feed" id="ayurReviewsFeed" data-product-id="<?php echo esc_attr($product_id); ?>">
                    <?php if (!empty($initial_reviews)) : ?>
                        <?php foreach ($initial_reviews as $rev) : ?>
                            <?php self::render_review_card($rev); ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="ayur-reviews-empty-state">
                            <div class="ayur-empty-icon" aria-hidden="true">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </div>
                            <h3 class="ayur-empty-title"><?php _e('Be the First to Review This Formulation', 'ayur-core'); ?></h3>
                            <p class="ayur-empty-desc"><?php _e('Have you experienced this classical Ayurvedic remedy? Share your authentic journey with fellow seekers to guide their wellness path.', 'ayur-core'); ?></p>
                            <button type="button" class="ayur-btn-write-review ayur-btn-empty-write" id="ayurOpenEmptyWriteBtn">
                                <span><?php _e('Write Your Honest Review', 'ayur-core'); ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($summary['total'] > 6) : ?>
                    <div class="ayur-reviews-loadmore-wrap">
                        <button type="button" class="ayur-btn-load-more" id="ayurLoadMoreReviewsBtn" data-page="1">
                            <span><?php _e('Load More Reviews', 'ayur-core'); ?></span>
                            <span class="ayur-spinner" aria-hidden="true"></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Write a Review Modal Dialog -->
            <div id="ayurWriteReviewModal" class="ayur-review-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="ayurWriteModalTitle">
                <div class="ayur-modal-backdrop" id="ayurWriteModalBackdrop"></div>
                <div class="ayur-modal-dialog">
                    <div class="ayur-modal-header">
                        <h3 class="ayur-modal-title" id="ayurWriteModalTitle"><?php _e('Write a Customer Review', 'ayur-core'); ?></h3>
                        <button type="button" class="ayur-modal-close" id="ayurCloseWriteModalBtn" aria-label="<?php esc_attr_e('Close modal', 'ayur-core'); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <form id="ayurReviewForm" class="ayur-review-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="ayur_submit_review">
                        <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('ayur_review_nonce')); ?>">
                        
                        <!-- Anti-Spam Honeypot Field -->
                        <div style="display:none !important;" aria-hidden="true">
                            <label for="ayur_hp_check">Do not fill this</label>
                            <input type="text" name="ayur_hp_check" id="ayur_hp_check" tabindex="-1" autocomplete="off">
                        </div>

                        <!-- Rating Selector -->
                        <div class="ayur-form-group">
                            <label class="ayur-form-label"><?php _e('Overall Rating', 'ayur-core'); ?> <span class="required">*</span></label>
                            <div class="ayur-interactive-rating" id="ayurInteractiveRating">
                                <div class="ayur-star-buttons" role="radiogroup" aria-label="<?php esc_attr_e('Rating Stars', 'ayur-core'); ?>">
                                    <?php for ($s = 1; $s <= 5; $s++) : ?>
                                        <button type="button" class="ayur-star-choice" data-value="<?php echo $s; ?>" aria-label="<?php echo esc_attr(sprintf(__('%d stars', 'ayur-core'), $s)); ?>">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                                <span class="ayur-rating-text" id="ayurRatingText"><?php _e('Select a star rating', 'ayur-core'); ?></span>
                                <input type="hidden" name="rating" id="ayurRatingInput" value="5">
                            </div>
                        </div>

                        <!-- Review Title -->
                        <div class="ayur-form-group">
                            <label for="ayurReviewTitle" class="ayur-form-label"><?php _e('Review Headline / Title', 'ayur-core'); ?> <span class="required">*</span></label>
                            <input type="text" id="ayurReviewTitle" name="review_title" class="ayur-form-input" placeholder="<?php esc_attr_e('e.g., Authentic classical quality, very pleased', 'ayur-core'); ?>" maxlength="120" required>
                        </div>

                        <!-- Review Content -->
                        <div class="ayur-form-group">
                            <label for="ayurReviewContent" class="ayur-form-label"><?php _e('Your Honest Review', 'ayur-core'); ?> <span class="required">*</span></label>
                            <textarea id="ayurReviewContent" name="review_content" class="ayur-form-textarea" rows="4" placeholder="<?php esc_attr_e('Share how you incorporated this formulation into your daily routine and your experience...', 'ayur-core'); ?>" minlength="10" maxlength="2000" required></textarea>
                            <span class="ayur-char-hint"><?php _e('Minimum 10 characters', 'ayur-core'); ?></span>
                        </div>

                        <!-- Customer Photos Upload -->
                        <div class="ayur-form-group">
                            <label class="ayur-form-label"><?php _e('Add Photos (Optional)', 'ayur-core'); ?></label>
                            <p class="ayur-upload-hint"><?php _e('Share authentic photos of your formulation (Max 5 photos, JPEG/PNG/WebP, up to 5MB each).', 'ayur-core'); ?></p>
                            <div class="ayur-photo-uploader-zone" id="ayurDropZone">
                                <input type="file" id="ayurPhotoFiles" name="review_photos[]" multiple accept="image/jpeg,image/png,image/webp" class="ayur-hidden-file-input">
                                <div class="ayur-uploader-prompt">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    <span><?php _e('Click or drag photos here to upload', 'ayur-core'); ?></span>
                                </div>
                            </div>
                            <div class="ayur-photo-previews" id="ayurPhotoPreviews"></div>
                        </div>

                        <!-- Name & Email (2 columns) -->
                        <div class="ayur-form-row">
                            <div class="ayur-form-group ayur-col-half">
                                <label for="ayurReviewerName" class="ayur-form-label"><?php _e('Your Name', 'ayur-core'); ?> <span class="required">*</span></label>
                                <input type="text" id="ayurReviewerName" name="reviewer_name" class="ayur-form-input" placeholder="<?php esc_attr_e('e.g., Rahul S.', 'ayur-core'); ?>" required>
                            </div>
                            <div class="ayur-form-group ayur-col-half">
                                <label for="ayurReviewerEmail" class="ayur-form-label"><?php _e('Your Email', 'ayur-core'); ?> <span class="required">*</span></label>
                                <input type="email" id="ayurReviewerEmail" name="reviewer_email" class="ayur-form-input" placeholder="<?php esc_attr_e('e.g., yourname@email.com', 'ayur-core'); ?>" required>
                                <span class="ayur-privacy-hint"><?php _e('Kept strictly confidential. Used only to verify your purchase.', 'ayur-core'); ?></span>
                            </div>
                        </div>

                        <div class="ayur-form-response" id="ayurFormResponse" style="display:none;"></div>

                        <div class="ayur-modal-footer">
                            <button type="submit" class="ayur-btn-submit-review" id="ayurSubmitReviewBtn">
                                <span><?php _e('Submit Review for Verification', 'ayur-core'); ?></span>
                                <span class="ayur-spinner" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Customer Photo Lightbox Modal -->
            <div id="ayurReviewPhotoLightbox" class="ayur-photo-lightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Customer Photo Lightbox', 'ayur-core'); ?>">
                <div class="ayur-lightbox-backdrop" id="ayurPhotoLightboxBackdrop"></div>
                <div class="ayur-lightbox-dialog">
                    <button type="button" class="ayur-lightbox-close" id="ayurPhotoLightboxClose" aria-label="<?php esc_attr_e('Close lightbox', 'ayur-core'); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                    <div class="ayur-lightbox-stage">
                        <img id="ayurLightboxFullImg" src="" alt="<?php esc_attr_e('Customer Review Photo', 'ayur-core'); ?>">
                    </div>
                    <div class="ayur-lightbox-caption" id="ayurLightboxCaption"></div>
                </div>
            </div>

            <!-- Report Review Modal -->
            <div id="ayurReportModal" class="ayur-report-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="ayurReportModalTitle">
                <div class="ayur-modal-backdrop" id="ayurReportModalBackdrop"></div>
                <div class="ayur-modal-dialog">
                    <div class="ayur-modal-header">
                        <h4 class="ayur-modal-title" id="ayurReportModalTitle"><?php _e('Report This Review', 'ayur-core'); ?></h4>
                        <button type="button" class="ayur-modal-close" id="ayurCloseReportModalBtn" aria-label="<?php esc_attr_e('Close modal', 'ayur-core'); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <form id="ayurReportForm" class="ayur-report-form">
                        <input type="hidden" name="action" value="ayur_report_review">
                        <input type="hidden" name="review_id" id="ayurReportReviewId" value="0">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('ayur_review_nonce')); ?>">

                        <p class="ayur-report-intro"><?php _e('Why are you reporting this review? Our team will review the submission.', 'ayur-core'); ?></p>
                        
                        <div class="ayur-report-options">
                            <label class="ayur-report-radio">
                                <input type="radio" name="reason" value="spam" checked>
                                <span><?php _e('Spam, advertising, or duplicate content', 'ayur-core'); ?></span>
                            </label>
                            <label class="ayur-report-radio">
                                <input type="radio" name="reason" value="offensive">
                                <span><?php _e('Offensive, inappropriate, or abusive language', 'ayur-core'); ?></span>
                            </label>
                            <label class="ayur-report-radio">
                                <input type="radio" name="reason" value="irrelevant">
                                <span><?php _e('Not relevant to this Ayurvedic product', 'ayur-core'); ?></span>
                            </label>
                            <label class="ayur-report-radio">
                                <input type="radio" name="reason" value="false_info">
                                <span><?php _e('False, fabricated, or misleading claims', 'ayur-core'); ?></span>
                            </label>
                            <label class="ayur-report-radio">
                                <input type="radio" name="reason" value="other">
                                <span><?php _e('Other concern', 'ayur-core'); ?></span>
                            </label>
                        </div>

                        <div class="ayur-form-group" style="margin-top:12px;">
                            <label class="ayur-form-label"><?php _e('Additional Details (Optional)', 'ayur-core'); ?></label>
                            <textarea name="note" class="ayur-form-textarea" rows="2" placeholder="<?php esc_attr_e('Provide any helpful context for the moderation team...', 'ayur-core'); ?>"></textarea>
                        </div>

                        <div class="ayur-report-response" id="ayurReportResponse" style="display:none;"></div>

                        <div class="ayur-modal-footer">
                            <button type="submit" class="ayur-btn-submit-report" id="ayurSubmitReportBtn">
                                <span><?php _e('Submit Report', 'ayur-core'); ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * Render individual review card
     */
    public static function render_review_card($comment) {
        $comment_id   = $comment->comment_ID;
        $rating       = (int) get_comment_meta($comment_id, 'rating', true) ?: 5;
        $title        = get_comment_meta($comment_id, 'review_title', true) ?: '';
        $is_verified  = (int) get_comment_meta($comment_id, 'verified', true) === 1;
        $source       = get_comment_meta($comment_id, 'review_source', true) ?: 'website';
        $helpful      = (int) get_comment_meta($comment_id, 'helpful_count', true) ?: 0;
        $photos       = get_comment_meta($comment_id, 'review_photos', true);
        $author_name  = $comment->comment_author;
        $date_str     = date_i18n(get_option('date_format'), strtotime($comment->comment_date));

        // Format author name respectfully (e.g. Rahul S.)
        $name_parts = explode(' ', trim($author_name));
        if (count($name_parts) > 1) {
            $formatted_name = $name_parts[0] . ' ' . strtoupper(substr(end($name_parts), 0, 1)) . '.';
        } else {
            $formatted_name = $name_parts[0];
        }

        ?>
        <article class="ayur-review-card" id="ayurReview-<?php echo esc_attr($comment_id); ?>" data-review-id="<?php echo esc_attr($comment_id); ?>">
            <div class="ayur-card-top-row">
                <div class="ayur-card-stars" aria-label="<?php echo esc_attr(sprintf(__('%d out of 5 stars', 'ayur-core'), $rating)); ?>">
                    <?php echo self::render_star_rating($rating); ?>
                </div>
                <h4 class="ayur-card-headline"><?php echo esc_html($title); ?></h4>
            </div>

            <div class="ayur-card-author-row">
                <span class="ayur-card-author-name"><?php echo esc_html($formatted_name); ?></span>
                <?php if ($is_verified) : ?>
                    <span class="ayur-badge-verified">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span><?php _e('Verified Purchase', 'ayur-core'); ?></span>
                    </span>
                <?php else : ?>
                    <span class="ayur-badge-customer-review">
                        <span><?php _e('Customer Review', 'ayur-core'); ?></span>
                    </span>
                <?php endif; ?>
                <span class="ayur-card-meta-sep">•</span>
                <time class="ayur-card-date" datetime="<?php echo esc_attr($comment->comment_date); ?>">
                    <?php printf(__('Reviewed in India on %s', 'ayur-core'), esc_html($date_str)); ?>
                </time>
            </div>

            <div class="ayur-card-body">
                <?php echo wpautop(esc_html($comment->comment_content)); ?>
            </div>

            <?php if (!empty($photos) && is_array($photos)) : ?>
                <div class="ayur-card-photos-gallery">
                    <?php foreach ($photos as $photo_id) :
                        $full_url = wp_get_attachment_image_url($photo_id, 'full');
                        $thumb_url = wp_get_attachment_image_url($photo_id, 'thumbnail');
                        if (!$full_url) {
                            if (filter_var($photo_id, FILTER_VALIDATE_URL)) {
                                $full_url = $photo_id;
                                $thumb_url = $photo_id;
                            } else {
                                continue;
                            }
                        }
                    ?>
                        <button type="button" class="ayur-gallery-thumb-btn" data-full-img="<?php echo esc_url($full_url); ?>" data-caption="<?php echo esc_attr($formatted_name . ' - ' . $title); ?>" aria-label="<?php esc_attr_e('View full review photo', 'ayur-core'); ?>">
                            <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php esc_attr_e('Customer review photo', 'ayur-core'); ?>" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="ayur-card-actions-row">
                <button type="button" class="ayur-btn-helpful" data-review-id="<?php echo esc_attr($comment_id); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                    <span><?php _e('Helpful', 'ayur-core'); ?></span>
                    <span class="ayur-helpful-count" data-count="<?php echo esc_attr($helpful); ?>"><?php echo $helpful > 0 ? '(' . esc_html($helpful) . ')' : ''; ?></span>
                </button>
                <span class="ayur-action-sep">|</span>
                <button type="button" class="ayur-btn-report" data-review-id="<?php echo esc_attr($comment_id); ?>">
                    <span><?php _e('Report', 'ayur-core'); ?></span>
                </button>
            </div>
        </article>
        <?php
    }

    /**
     * Helper to render Star rating SVGs
     */
    public static function render_star_rating($rating) {
        $html = '';
        $full = floor($rating);
        $fraction = $rating - $full;

        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $full) {
                // Filled star
                $html .= '<svg class="ayur-star-svg is-filled" width="16" height="16" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
            } elseif ($i == ($full + 1) && $fraction >= 0.3) {
                // Half star
                $html .= '<svg class="ayur-star-svg is-half" width="16" height="16" viewBox="0 0 24 24"><defs><linearGradient id="halfGrad' . $i . '"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#E5E7EB"/></linearGradient></defs><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="url(#halfGrad' . $i . ')"/></svg>';
            } else {
                // Empty star
                $html .= '<svg class="ayur-star-svg is-empty" width="16" height="16" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
            }
        }
        return $html;
    }

    /* ==========================================================================
       AUTOMATED REVIEW REQUEST TRIGGER (AFTER COMPLETED ORDER)
       ========================================================================== */

    /**
     * Schedule review request action when order completes
     */
    public static function schedule_order_review_request($order_id) {
        $enabled = get_option('ayur_review_requests_enabled', 'no');
        if ($enabled !== 'yes') {
            return;
        }

        $delay_days = (int) get_option('ayur_review_request_delay_days', 7);
        $delay_seconds = max(1, $delay_days) * DAY_IN_SECONDS;
        $timestamp = time() + $delay_seconds;

        // Use Action Scheduler if available, else wp_schedule_single_event
        if (function_exists('as_schedule_single_action')) {
            as_schedule_single_action($timestamp, 'ayur_send_scheduled_review_request', array('order_id' => $order_id));
        } else {
            wp_schedule_single_event($timestamp, 'ayur_send_scheduled_review_request', array($order_id));
        }
    }

    /**
     * Process review request execution
     */
    public static function process_scheduled_review_request($order_id) {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_status() !== 'completed') {
            return;
        }

        // Check if already sent
        if ($order->get_meta('_ayur_review_request_sent')) {
            return;
        }

        $customer_email = $order->get_billing_email();
        if (empty($customer_email) || !is_email($customer_email)) {
            return;
        }

        $items = $order->get_items();
        if (empty($items)) {
            return;
        }

        $first_item = reset($items);
        $product = $first_item->get_product();
        if (!$product) {
            return;
        }

        $review_link = add_query_arg(array('review_invite' => '1', 'order_id' => $order_id), $product->get_permalink());

        $subject = get_option('ayur_review_req_subject', __('How was your experience with Bhagyoday Ayurveda?', 'ayur-core'));
        $body_template = get_option('ayur_review_req_body', "Namaste {customer_name},\n\nWe hope your wellness journey with Bhagyoday Ayurveda is progressing well.\n\nHow was your experience with {product_name}? We would truly appreciate your honest feedback to help us refine our classical formulations.\n\nShare your review here:\n{review_link}\n\nWarm regards,\nBhagyoday Ayurveda");

        $customer_name = $order->get_billing_first_name() ?: __('Wellness Seeker', 'ayur-core');
        $product_name  = $product->get_name();

        $message = str_replace(
            array('{customer_name}', '{product_name}', '{review_link}'),
            array($customer_name, $product_name, $review_link),
            $body_template
        );

        $headers = array('Content-Type: text/plain; charset=UTF-8');
        wp_mail($customer_email, $subject, $message, $headers);

        $order->update_meta_data('_ayur_review_request_sent', current_time('mysql'));
        $order->save();
    }

    /* ==========================================================================
       SCHEMA.ORG STRUCTURED DATA (ONLY WHEN LEGITIMATE REVIEWS EXIST)
       ========================================================================== */

    /**
     * Output Schema.org JSON-LD structured data on single product pages.
     * Strictly avoids outputting aggregateRating when 0 reviews exist.
     */
    public static function output_schema_jsonld() {
        if (!function_exists('is_product') || !is_product()) {
            return;
        }

        $product_id = get_the_ID();
        if (!$product_id) {
            return;
        }

        $product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;
        if (!$product || !is_a($product, 'WC_Product')) {
            return;
        }

        $summary = self::get_product_review_summary($product_id);

        // DO NOT output aggregateRating if zero legitimate reviews exist!
        if ($summary['total'] <= 0) {
            return;
        }

        $approved_reviews = get_comments(array(
            'post_id' => $product_id,
            'status'  => 'approve',
            'type'    => self::COMMENT_TYPE,
            'number'  => 5,
            'orderby' => 'comment_date',
            'order'   => 'DESC',
        ));
        if (empty($approved_reviews)) {
            global $wpdb;
            $approved_reviews = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type = %s ORDER BY comment_date_gmt DESC LIMIT 5", $product_id, self::COMMENT_TYPE));
        }

        $schema_reviews = array();
        foreach ($approved_reviews as $r) {
            $r_rating = (int) get_comment_meta($r->comment_ID, 'rating', true) ?: 5;
            $schema_reviews[] = array(
                '@type'         => 'Review',
                'reviewRating'  => array(
                    '@type'       => 'Rating',
                    'ratingValue' => $r_rating,
                    'bestRating'  => 5,
                    'worstRating' => 1,
                ),
                'author'        => array(
                    '@type' => 'Person',
                    'name'  => $r->comment_author,
                ),
                'datePublished' => date('Y-m-d', strtotime($r->comment_date)),
                'reviewBody'    => wp_strip_all_tags($r->comment_content),
            );
        }

        $schema = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'Product',
            '@id'             => esc_url($product->get_permalink()) . '#product',
            'name'            => $product->get_name(),
            'description'     => wp_strip_all_tags($product->get_short_description() ?: $product->get_description()),
            'image'           => wp_get_attachment_url($product->get_image_id()),
            'aggregateRating' => array(
                '@type'       => 'AggregateRating',
                'ratingValue' => number_format($summary['average'], 1),
                'reviewCount' => $summary['total'],
                'bestRating'  => 5,
                'worstRating' => 1,
            ),
            'review'          => $schema_reviews,
        );

        echo "\n<!-- Bhagyoday Ayurveda Verified Review Schema -->\n";
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n";
    }

    /* ==========================================================================
       WORDPRESS ADMIN REVIEW MANAGEMENT INTERFACE
       ========================================================================== */

    /**
     * Render Complete Admin Interface with Tabs:
     * - All Reviews (List Table with Filters & Bulk Actions)
     * - Add New Review
     * - Edit Review
     * - CSV Import
     * - Review Requests Settings
     * - Analytics Dashboard
     */
    public static function render_admin_reviews_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this page.', 'ayur-core'));
        }

        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'list';
        $message = isset($_GET['ayur_msg']) ? sanitize_text_field($_GET['ayur_msg']) : '';

        ?>
        <div class="wrap ayur-admin-reviews-wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-shield-alt" style="font-size:28px; width:28px; height:28px; color:#173B2F; vertical-align:middle; margin-right:6px;"></span>
                <?php _e('Customer Reviews Management', 'ayur-core'); ?>
            </h1>
            <a href="?page=ayur-reviews&tab=new" class="page-title-action"><?php _e('Add New Review', 'ayur-core'); ?></a>
            <hr class="wp-header-end">

            <?php if (!empty($message)) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div>
            <?php endif; ?>

            <nav class="nav-tab-wrapper" style="margin: 15px 0 20px 0;">
                <a href="?page=ayur-reviews&tab=list" class="nav-tab <?php echo $active_tab === 'list' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('All Reviews', 'ayur-core'); ?>
                </button>
                <a href="?page=ayur-reviews&tab=new" class="nav-tab <?php echo $active_tab === 'new' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Add Review', 'ayur-core'); ?>
                </a>
                <a href="?page=ayur-reviews&tab=import" class="nav-tab <?php echo $active_tab === 'import' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Import CSV', 'ayur-core'); ?>
                </a>
                <a href="?page=ayur-reviews&tab=requests" class="nav-tab <?php echo $active_tab === 'requests' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Automated Requests', 'ayur-core'); ?>
                </a>
                <a href="?page=ayur-reviews&tab=analytics" class="nav-tab <?php echo $active_tab === 'analytics' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Analytics Dashboard', 'ayur-core'); ?>
                </a>
            </nav>

            <div class="ayur-admin-tab-content">
                <?php
                switch ($active_tab) {
                    case 'new':
                        self::render_admin_add_edit_form(0);
                        break;
                    case 'edit':
                        $review_id = isset($_GET['review_id']) ? absint($_GET['review_id']) : 0;
                        self::render_admin_add_edit_form($review_id);
                        break;
                    case 'import':
                        self::render_admin_import_tab();
                        break;
                    case 'requests':
                        self::render_admin_requests_tab();
                        break;
                    case 'analytics':
                        self::render_admin_analytics_tab();
                        break;
                    case 'list':
                    default:
                        self::render_admin_list_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Tab 1: Review List Table with Filters & Moderation
     */
    public static function render_admin_list_tab() {
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        $product_filter = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
        $rating_filter = isset($_GET['rating']) ? absint($_GET['rating']) : 0;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        // Query args
        $args = array(
            'type'   => self::COMMENT_TYPE,
            'number' => 20,
            'paged'  => isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1,
        );

        if ($status_filter === 'pending') {
            $args['status'] = 'hold';
        } elseif ($status_filter === 'approved') {
            $args['status'] = 'approve';
        } elseif ($status_filter === 'spam') {
            $args['status'] = 'spam';
        } elseif ($status_filter === 'trash') {
            $args['status'] = 'trash';
        } else {
            $args['status'] = 'all';
        }

        if ($product_filter > 0) {
            $args['post_id'] = $product_filter;
        }

        if (!empty($search)) {
            $args['search'] = $search;
        }

        if ($rating_filter > 0) {
            $args['meta_query'] = array(
                array(
                    'key'     => 'rating',
                    'value'   => $rating_filter,
                    'compare' => '=',
                    'type'    => 'NUMERIC',
                ),
            );
        }

        $comments = get_comments($args);
        if (empty($comments)) {
            global $wpdb;
            $status_clause = "comment_approved != 'trash' AND comment_approved != 'spam'";
            if ($status_filter === 'pending') {
                $status_clause = "comment_approved = '0'";
            } elseif ($status_filter === 'approved') {
                $status_clause = "comment_approved = '1'";
            } elseif ($status_filter === 'spam') {
                $status_clause = "comment_approved = 'spam'";
            } elseif ($status_filter === 'trash') {
                $status_clause = "comment_approved = 'trash'";
            }
            $p_clause = $product_filter > 0 ? $wpdb->prepare(" AND comment_post_ID = %d", $product_filter) : "";
            $comments = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_type = 'review' AND {$status_clause} {$p_clause} ORDER BY comment_date_gmt DESC LIMIT 20");
        }

        // Direct database counts for admin sub-nav
        global $wpdb;
        $count_pending  = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type = 'review' AND comment_approved = '0'");
        $count_approved = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type = 'review' AND comment_approved = '1'");
        $count_spam     = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type = 'review' AND comment_approved = 'spam'");
        $count_trash    = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_type = 'review' AND comment_approved = 'trash'");
        $count_all      = $count_pending + $count_approved + $count_spam + $count_trash;

        // All live products for filter dropdown
        $all_products = wc_get_products(array('limit' => -1, 'status' => 'publish'));

        ?>
        <ul class="subsubsub">
            <li><a href="?page=ayur-reviews&tab=list&status=all" class="<?php echo $status_filter === 'all' ? 'current' : ''; ?>"><?php _e('All', 'ayur-core'); ?> <span class="count">(<?php echo $count_all; ?>)</span></a> |</li>
            <li><a href="?page=ayur-reviews&tab=list&status=pending" class="<?php echo $status_filter === 'pending' ? 'current' : ''; ?>"><?php _e('Pending', 'ayur-core'); ?> <span class="count">(<?php echo $count_pending; ?>)</span></a> |</li>
            <li><a href="?page=ayur-reviews&tab=list&status=approved" class="<?php echo $status_filter === 'approved' ? 'current' : ''; ?>"><?php _e('Approved', 'ayur-core'); ?> <span class="count">(<?php echo $count_approved; ?>)</span></a> |</li>
            <li><a href="?page=ayur-reviews&tab=list&status=spam" class="<?php echo $status_filter === 'spam' ? 'current' : ''; ?>"><?php _e('Spam', 'ayur-core'); ?> <span class="count">(<?php echo $count_spam; ?>)</span></a> |</li>
            <li><a href="?page=ayur-reviews&tab=list&status=trash" class="<?php echo $status_filter === 'trash' ? 'current' : ''; ?>"><?php _e('Trash', 'ayur-core'); ?> <span class="count">(<?php echo $count_trash; ?>)</span></a></li>
        </ul>

        <form method="get" class="ayur-admin-filter-bar" style="margin: 12px 0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <input type="hidden" name="page" value="ayur-reviews">
            <input type="hidden" name="tab" value="list">
            <input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>">

            <select name="product_id">
                <option value="0"><?php _e('Filter by Product', 'ayur-core'); ?></option>
                <?php foreach ($all_products as $p) : ?>
                    <option value="<?php echo $p->get_id(); ?>" <?php selected($product_filter, $p->get_id()); ?>>
                        <?php echo esc_html($p->get_name()); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="rating">
                <option value="0"><?php _e('Filter by Rating', 'ayur-core'); ?></option>
                <option value="5" <?php selected($rating_filter, 5); ?>>5 Stars</option>
                <option value="4" <?php selected($rating_filter, 4); ?>>4 Stars</option>
                <option value="3" <?php selected($rating_filter, 3); ?>>3 Stars</option>
                <option value="2" <?php selected($rating_filter, 2); ?>>2 Stars</option>
                <option value="1" <?php selected($rating_filter, 1); ?>>1 Star</option>
            </select>

            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search reviews...', 'ayur-core'); ?>">
            <button type="submit" class="button"><?php _e('Filter', 'ayur-core'); ?></button>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="ayur_review_action">
            <?php wp_nonce_field('ayur_admin_action_nonce', 'ayur_nonce'); ?>

            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action">
                        <option value=""><?php _e('Bulk actions', 'ayur-core'); ?></option>
                        <option value="approve"><?php _e('Approve', 'ayur-core'); ?></option>
                        <option value="reject"><?php _e('Reject / Trash', 'ayur-core'); ?></option>
                        <option value="spam"><?php _e('Mark as Spam', 'ayur-core'); ?></option>
                        <option value="delete"><?php _e('Delete Permanently', 'ayur-core'); ?></option>
                    </select>
                    <input type="submit" class="button action" value="<?php esc_attr_e('Apply', 'ayur-core'); ?>">
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped comments">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column"><input type="checkbox"></td>
                        <th style="width:110px;"><?php _e('Rating', 'ayur-core'); ?></th>
                        <th><?php _e('Review', 'ayur-core'); ?></th>
                        <th style="width:180px;"><?php _e('Reviewer', 'ayur-core'); ?></th>
                        <th style="width:200px;"><?php _e('Product', 'ayur-core'); ?></th>
                        <th style="width:120px;"><?php _e('Photos', 'ayur-core'); ?></th>
                        <th style="width:110px;"><?php _e('Verification', 'ayur-core'); ?></th>
                        <th style="width:75px;"><?php _e('Helpful', 'ayur-core'); ?></th>
                        <th style="width:85px;"><?php _e('Status', 'ayur-core'); ?></th>
                        <th style="width:110px;"><?php _e('Date', 'ayur-core'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($comments)) : ?>
                        <?php foreach ($comments as $c) :
                            $cid = $c->comment_ID;
                            $rating = (int) get_comment_meta($cid, 'rating', true) ?: 5;
                            $title = get_comment_meta($cid, 'review_title', true) ?: 'Review';
                            $is_verified = (int) get_comment_meta($cid, 'verified', true) === 1;
                            $order_id = get_comment_meta($cid, 'order_id', true);
                            $photos = get_comment_meta($cid, 'review_photos', true);
                            $helpful = (int) get_comment_meta($cid, 'helpful_count', true);
                            $report_count = (int) get_comment_meta($cid, 'report_count', true);
                            $prod = wc_get_product($c->comment_post_ID);
                        ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="review_ids[]" value="<?php echo $cid; ?>">
                                </th>
                                <td>
                                    <span style="color:#B79552; font-weight:700;"><?php echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); ?></span>
                                    <span style="font-size:11px; display:block; color:#666;"><?php echo $rating; ?> / 5</span>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($title); ?></strong>
                                    <?php if ($report_count > 0) : ?>
                                        <span class="badge" style="background:#dc2626; color:#fff; padding:2px 6px; border-radius:4px; font-size:10px; margin-left:6px;">⚠️ Reported (<?php echo $report_count; ?>)</span>
                                    <?php endif; ?>
                                    <p style="margin:4px 0 6px 0; color:#333; font-size:12.5px;"><?php echo esc_html(wp_trim_words($c->comment_content, 25)); ?></p>
                                    
                                    <div class="row-actions" style="visibility:visible;">
                                        <?php if ($c->comment_approved === '0') : ?>
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ayur_review_action&single_action=approve&review_id=' . $cid), 'ayur_admin_action')); ?>" style="color:#16a34a; font-weight:600;"><?php _e('Approve', 'ayur-core'); ?></a> |
                                        <?php else : ?>
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ayur_review_action&single_action=unapprove&review_id=' . $cid), 'ayur_admin_action')); ?>"><?php _e('Unapprove', 'ayur-core'); ?></a> |
                                        <?php endif; ?>
                                        <a href="?page=ayur-reviews&tab=edit&review_id=<?php echo $cid; ?>"><?php _e('Edit', 'ayur-core'); ?></a> |
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ayur_review_action&single_action=spam&review_id=' . $cid), 'ayur_admin_action')); ?>" style="color:#d97706;"><?php _e('Spam', 'ayur-core'); ?></a> |
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=ayur_review_action&single_action=trash&review_id=' . $cid), 'ayur_admin_action')); ?>" style="color:#dc2626;"><?php _e('Trash', 'ayur-core'); ?></a>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($c->comment_author); ?></strong><br>
                                    <span style="font-size:11px; color:#666;"><?php echo esc_html($c->comment_author_email); ?></span>
                                </td>
                                <td>
                                    <?php if ($prod) : ?>
                                        <a href="<?php echo esc_url($prod->get_permalink()); ?>" target="_blank"><strong><?php echo esc_html($prod->get_name()); ?></strong></a>
                                    <?php else : ?>
                                        <em><?php _e('Product removed', 'ayur-core'); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($photos) && is_array($photos)) : ?>
                                        <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                            <?php foreach (array_slice($photos, 0, 3) as $pid) :
                                                $img_url = wp_get_attachment_image_url($pid, 'thumbnail') ?: $pid;
                                            ?>
                                                <img src="<?php echo esc_url($img_url); ?>" style="width:28px; height:28px; object-fit:cover; border-radius:3px; border:1px solid #ccc;">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <span style="color:#888; font-size:11px;"><?php _e('No photos', 'ayur-core'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($is_verified) : ?>
                                        <span style="color:#16a34a; font-weight:600; font-size:11.5px;">✓ <?php _e('Verified', 'ayur-core'); ?></span>
                                        <?php if ($order_id) : ?>
                                            <span style="font-size:10px; display:block; color:#666;"><a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>" target="_blank">#<?php echo esc_html($order_id); ?></a></span>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <span style="color:#666; font-size:11px;"><?php _e('Customer Review', 'ayur-core'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($helpful); ?>
                                </td>
                                <td>
                                    <?php
                                    if ($c->comment_approved === '1') {
                                        echo '<span class="status-badge" style="background:#dcfce7; color:#166534; padding:3px 7px; border-radius:4px; font-size:11px;">' . __('Approved', 'ayur-core') . '</span>';
                                    } elseif ($c->comment_approved === '0') {
                                        echo '<span class="status-badge" style="background:#fef3c7; color:#92400e; padding:3px 7px; border-radius:4px; font-size:11px;">' . __('Pending', 'ayur-core') . '</span>';
                                    } elseif ($c->comment_approved === 'spam') {
                                        echo '<span class="status-badge" style="background:#fee2e2; color:#991b1b; padding:3px 7px; border-radius:4px; font-size:11px;">' . __('Spam', 'ayur-core') . '</span>';
                                    } else {
                                        echo '<span class="status-badge" style="background:#f3f4f6; color:#374151; padding:3px 7px; border-radius:4px; font-size:11px;">' . esc_html($c->comment_approved) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span style="font-size:11px; color:#555;"><?php echo esc_html(date_i18n('d M Y', strtotime($c->comment_date))); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="10" style="text-align:center; padding:30px;"><?php _e('No customer reviews found matching criteria.', 'ayur-core'); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php
    }

    /**
     * Tab 2 / 3: Add & Edit Review Screen
     */
    public static function render_admin_add_edit_form($review_id = 0) {
        $comment = $review_id > 0 ? get_comment($review_id) : null;
        $is_edit = !empty($comment);

        $product_id = $is_edit ? $comment->comment_post_ID : 0;
        $rating = $is_edit ? (int) get_comment_meta($review_id, 'rating', true) : 5;
        $title = $is_edit ? get_comment_meta($review_id, 'review_title', true) : '';
        $author_name = $is_edit ? $comment->comment_author : '';
        $author_email = $is_edit ? $comment->comment_author_email : '';
        $content = $is_edit ? $comment->comment_content : '';
        $is_verified = $is_edit ? (int) get_comment_meta($review_id, 'verified', true) : 0;
        $order_id = $is_edit ? get_comment_meta($review_id, 'order_id', true) : '';
        $photos = $is_edit ? (array) get_comment_meta($review_id, 'review_photos', true) : array();
        $source = $is_edit ? get_comment_meta($review_id, 'review_source', true) : 'admin';
        $status = $is_edit ? ($comment->comment_approved === '1' ? 'approved' : ($comment->comment_approved === '0' ? 'pending' : $comment->comment_approved)) : 'approved';
        $helpful = $is_edit ? (int) get_comment_meta($review_id, 'helpful_count', true) : 0;

        $products = wc_get_products(array('limit' => -1, 'status' => 'publish'));

        ?>
        <div style="background:#fff; border:1px solid #ccd0d4; padding:24px 28px; border-radius:8px; max-width:850px;">
            <h2><?php echo $is_edit ? __('Edit Customer Review', 'ayur-core') : __('Add New Customer Review', 'ayur-core'); ?></h2>
            <p><?php _e('Review authenticity rules: do not falsely mark reviews as Verified Purchase without a legitimate WooCommerce order.', 'ayur-core'); ?></p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="ayur_save_review">
                <input type="hidden" name="review_id" value="<?php echo esc_attr($review_id); ?>">
                <?php wp_nonce_field('ayur_save_review_nonce', 'ayur_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="review_product_id"><?php _e('Product', 'ayur-core'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select name="product_id" id="review_product_id" required style="width:360px;">
                                <option value=""><?php _e('Select Product...', 'ayur-core'); ?></option>
                                <?php foreach ($products as $p) : ?>
                                    <option value="<?php echo $p->get_id(); ?>" <?php selected($product_id, $p->get_id()); ?>>
                                        <?php echo esc_html($p->get_name()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_rating"><?php _e('Star Rating', 'ayur-core'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select name="rating" id="review_rating" required>
                                <option value="5" <?php selected($rating, 5); ?>>★★★★★ (5 Stars - Excellent)</option>
                                <option value="4" <?php selected($rating, 4); ?>>★★★★☆ (4 Stars - Good)</option>
                                <option value="3" <?php selected($rating, 3); ?>>★★★☆☆ (3 Stars - Average)</option>
                                <option value="2" <?php selected($rating, 2); ?>>★★☆☆☆ (2 Stars - Poor)</option>
                                <option value="1" <?php selected($rating, 1); ?>>★☆☆☆☆ (1 Star - Terrible)</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_title"><?php _e('Review Title', 'ayur-core'); ?> <span class="required">*</span></label></th>
                        <td><input type="text" name="review_title" id="review_title" value="<?php echo esc_attr($title); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_author"><?php _e('Reviewer Name', 'ayur-core'); ?> <span class="required">*</span></label></th>
                        <td><input type="text" name="reviewer_name" id="review_author" value="<?php echo esc_attr($author_name); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_email"><?php _e('Reviewer Email', 'ayur-core'); ?> <span class="required">*</span></label></th>
                        <td><input type="email" name="reviewer_email" id="review_email" value="<?php echo esc_attr($author_email); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_content"><?php _e('Review Content', 'ayur-core'); ?> <span class="required">*</span></label></th>
                        <td>
                            <textarea name="review_content" id="review_content" rows="6" class="large-text" required><?php echo esc_textarea($content); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_verified"><?php _e('Verified Purchase', 'ayur-core'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="verified" id="review_verified" value="1" <?php checked($is_verified, 1); ?>>
                                <?php _e('Mark as Verified Purchase', 'ayur-core'); ?>
                            </label>
                            <p class="description"><?php _e('Check only if there is legitimate WooCommerce purchase proof. Enter the matching Order ID below.', 'ayur-core'); ?></p>
                            <input type="number" name="order_id" id="review_order_id" value="<?php echo esc_attr($order_id); ?>" placeholder="Order ID (e.g. 104)" style="width:160px; margin-top:6px;">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php _e('Review Photos', 'ayur-core'); ?></label></th>
                        <td>
                            <div id="ayurAdminPhotoList" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px;">
                                <?php if (!empty($photos)) : ?>
                                    <?php foreach ($photos as $pid) :
                                        $p_url = wp_get_attachment_image_url($pid, 'thumbnail') ?: $pid;
                                    ?>
                                        <div class="ayur-admin-photo-tile" style="position:relative; width:80px; height:80px; border:1px solid #ccc; border-radius:4px; overflow:hidden;">
                                            <img src="<?php echo esc_url($p_url); ?>" style="width:100%; height:100%; object-fit:cover;">
                                            <input type="hidden" name="review_photos[]" value="<?php echo esc_attr($pid); ?>">
                                            <button type="button" class="ayur-remove-admin-photo" style="position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.6); color:#fff; border:none; border-radius:50%; width:20px; height:20px; cursor:pointer;" onclick="this.parentElement.remove();">&times;</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button" id="ayurAddAdminPhotoBtn"><?php _e('Upload / Select Photo from Media Library', 'ayur-core'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_status"><?php _e('Status', 'ayur-core'); ?></label></th>
                        <td>
                            <select name="status" id="review_status">
                                <option value="approved" <?php selected($status, 'approved'); ?>><?php _e('Approved / Published', 'ayur-core'); ?></option>
                                <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending Moderation', 'ayur-core'); ?></option>
                                <option value="trash" <?php selected($status, 'trash'); ?>><?php _e('Trash', 'ayur-core'); ?></option>
                                <option value="spam" <?php selected($status, 'spam'); ?>><?php _e('Spam', 'ayur-core'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_helpful"><?php _e('Helpful Count', 'ayur-core'); ?></label></th>
                        <td><input type="number" name="helpful_count" id="review_helpful" value="<?php echo esc_attr($helpful); ?>" min="0" style="width:100px;"></td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php echo $is_edit ? esc_attr__('Update Review', 'ayur-core') : esc_attr__('Publish Review', 'ayur-core'); ?>">
                    <a href="?page=ayur-reviews&tab=list" class="button"><?php _e('Cancel', 'ayur-core'); ?></a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Tab 4: CSV Import Tab
     */
    public static function render_admin_import_tab() {
        ?>
        <div style="background:#fff; border:1px solid #ccd0d4; padding:24px 28px; border-radius:8px; max-width:850px;">
            <h2><?php _e('Import Authentic Reviews via CSV', 'ayur-core'); ?></h2>
            <p><?php _e('Import genuine historical customer feedback. Duplicate reviews and invalid SKUs will automatically be skipped.', 'ayur-core'); ?></p>

            <div class="notice notice-info inline" style="margin-bottom:20px;">
                <p><strong><?php _e('Supported CSV Columns:', 'ayur-core'); ?></strong></p>
                <code>product_id, product_sku, reviewer_name, review_title, review_content, rating, review_date, source, verified_purchase, photo_urls</code>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="ayur_import_reviews">
                <?php wp_nonce_field('ayur_import_reviews_nonce', 'ayur_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="csv_file"><?php _e('Choose CSV File', 'ayur-core'); ?></label></th>
                        <td><input type="file" name="csv_file" id="csv_file" accept=".csv" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Import Status', 'ayur-core'); ?></th>
                        <td>
                            <label><input type="radio" name="import_status" value="approved" checked> <?php _e('Publish Approved', 'ayur-core'); ?></label><br>
                            <label><input type="radio" name="import_status" value="pending"> <?php _e('Import to Pending Moderation Queue', 'ayur-core'); ?></label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php esc_attr_e('Upload and Import Reviews', 'ayur-core'); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Tab 5: Automated Review Request Settings
     */
    public static function render_admin_requests_tab() {
        $enabled = get_option('ayur_review_requests_enabled', 'no');
        $delay_days = get_option('ayur_review_request_delay_days', 7);
        $subject = get_option('ayur_review_req_subject', __('How was your experience with Bhagyoday Ayurveda?', 'ayur-core'));
        $body = get_option('ayur_review_req_body', "Namaste {customer_name},\n\nWe hope your wellness journey with Bhagyoday Ayurveda is progressing well.\n\nHow was your experience with {product_name}? We would truly appreciate your honest feedback to help us refine our classical formulations.\n\nShare your review here:\n{review_link}\n\nWarm regards,\nBhagyoday Ayurveda");

        ?>
        <div style="background:#fff; border:1px solid #ccd0d4; padding:24px 28px; border-radius:8px; max-width:850px;">
            <h2><?php _e('Automated Post-Purchase Review Requests', 'ayur-core'); ?></h2>
            <p><?php _e('Automatically send respectful review invitations to verified customers after their order is delivered.', 'ayur-core'); ?></p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="ayur_save_request_settings">
                <?php wp_nonce_field('ayur_save_request_settings_nonce', 'ayur_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Automated Review Requests', 'ayur-core'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="ayur_review_requests_enabled" value="yes" <?php checked($enabled, 'yes'); ?>>
                                <?php _e('Enable automated review requests upon order completion', 'ayur-core'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="delay_days"><?php _e('Send Delay', 'ayur-core'); ?></label></th>
                        <td>
                            <input type="number" name="ayur_review_request_delay_days" id="delay_days" value="<?php echo esc_attr($delay_days); ?>" min="1" max="60" style="width:80px;">
                            <span><?php _e('days after order status is marked Completed', 'ayur-core'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="req_subject"><?php _e('Email Subject', 'ayur-core'); ?></label></th>
                        <td><input type="text" name="ayur_review_req_subject" id="req_subject" value="<?php echo esc_attr($subject); ?>" class="large-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="req_body"><?php _e('Message Body', 'ayur-core'); ?></label></th>
                        <td>
                            <textarea name="ayur_review_req_body" id="req_body" rows="8" class="large-text"><?php echo esc_textarea($body); ?></textarea>
                            <p class="description"><?php _e('Placeholders: {customer_name}, {product_name}, {review_link}', 'ayur-core'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'ayur-core'); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Tab 6: Analytics Dashboard
     */
    public static function render_admin_analytics_tab() {
        $products = wc_get_products(array('limit' => -1, 'status' => 'publish'));

        $total_reviews = 0;
        $total_verified = 0;
        $total_photos = 0;
        $all_ratings = array();

        $product_stats = array();

        foreach ($products as $p) {
            $s = self::get_product_review_summary($p->get_id());
            $product_stats[] = array(
                'id'         => $p->get_id(),
                'name'       => $p->get_name(),
                'sku'        => $p->get_sku() ?: 'N/A',
                'summary'    => $s,
            );

            $total_reviews  += $s['total'];
            $total_verified += $s['verified_count'];
            $total_photos   += $s['photo_count'];
            if ($s['total'] > 0) {
                $all_ratings[] = $s['average'];
            }
        }

        $overall_avg = !empty($all_ratings) ? round(array_sum($all_ratings) / count($all_ratings), 1) : 0.0;
        $pending_count = count(get_comments(array('type' => self::COMMENT_TYPE, 'status' => 'hold')));

        ?>
        <div style="max-width:1000px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#FAF7F0; border:1px solid #E9DDC8; border-radius:8px; padding:18px;">
                    <span style="font-size:12px; font-weight:600; color:#315C45; text-transform:uppercase;"><?php _e('Total Reviews', 'ayur-core'); ?></span>
                    <h3 style="font-size:28px; margin:6px 0; color:#173B2F;"><?php echo $total_reviews; ?></h3>
                </div>
                <div style="background:#FAF7F0; border:1px solid #E9DDC8; border-radius:8px; padding:18px;">
                    <span style="font-size:12px; font-weight:600; color:#315C45; text-transform:uppercase;"><?php _e('Pending Moderation', 'ayur-core'); ?></span>
                    <h3 style="font-size:28px; margin:6px 0; color:#B79552;"><?php echo $pending_count; ?></h3>
                </div>
                <div style="background:#FAF7F0; border:1px solid #E9DDC8; border-radius:8px; padding:18px;">
                    <span style="font-size:12px; font-weight:600; color:#315C45; text-transform:uppercase;"><?php _e('Verified Purchases', 'ayur-core'); ?></span>
                    <h3 style="font-size:28px; margin:6px 0; color:#173B2F;"><?php echo $total_verified; ?></h3>
                </div>
                <div style="background:#FAF7F0; border:1px solid #E9DDC8; border-radius:8px; padding:18px;">
                    <span style="font-size:12px; font-weight:600; color:#315C45; text-transform:uppercase;"><?php _e('Customer Photos', 'ayur-core'); ?></span>
                    <h3 style="font-size:28px; margin:6px 0; color:#173B2F;"><?php echo $total_photos; ?></h3>
                </div>
                <div style="background:#FAF7F0; border:1px solid #E9DDC8; border-radius:8px; padding:18px;">
                    <span style="font-size:12px; font-weight:600; color:#315C45; text-transform:uppercase;"><?php _e('Store Rating', 'ayur-core'); ?></span>
                    <h3 style="font-size:28px; margin:6px 0; color:#173B2F;"><?php echo $overall_avg > 0 ? $overall_avg . ' ★' : '—'; ?></h3>
                </div>
            </div>

            <div style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; overflow:hidden;">
                <h3 style="padding:16px 20px; margin:0; border-bottom:1px solid #eee; background:#fafafa;"><?php _e('Product-Level Performance', 'ayur-core'); ?></h3>
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Product Name', 'ayur-core'); ?></th>
                            <th><?php _e('SKU', 'ayur-core'); ?></th>
                            <th><?php _e('Reviews', 'ayur-core'); ?></th>
                            <th><?php _e('Avg Rating', 'ayur-core'); ?></th>
                            <th><?php _e('Verified %', 'ayur-core'); ?></th>
                            <th><?php _e('Photos %', 'ayur-core'); ?></th>
                            <th><?php _e('Action', 'ayur-core'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($product_stats as $stat) : 
                            $sm = $stat['summary'];
                            $ver_pct = $sm['total'] > 0 ? round(($sm['verified_count'] / $sm['total']) * 100) : 0;
                            $photo_pct = $sm['total'] > 0 ? round(($sm['photo_count'] / $sm['total']) * 100) : 0;
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($stat['name']); ?></strong></td>
                                <td><?php echo esc_html($stat['sku']); ?></td>
                                <td><?php echo $sm['total']; ?></td>
                                <td><span style="color:#B79552; font-weight:700;"><?php echo $sm['total'] > 0 ? $sm['average'] . ' ★' : '—'; ?></span></td>
                                <td><?php echo $ver_pct; ?>%</td>
                                <td><?php echo $photo_pct; ?>%</td>
                                <td>
                                    <a href="?page=ayur-reviews&tab=list&product_id=<?php echo $stat['id']; ?>" class="button button-small"><?php _e('View Reviews', 'ayur-core'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /* ==========================================================================
       ADMIN POST HANDLERS
       ========================================================================== */

    /**
     * Handle Admin Single & Bulk Review Actions
     */
    public static function handle_admin_actions() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Unauthorized access.', 'ayur-core'));
        }

        // Single Action via GET
        if (isset($_GET['single_action'], $_GET['review_id'])) {
            check_admin_referer('ayur_admin_action');
            $action = sanitize_key($_GET['single_action']);
            $rid = absint($_GET['review_id']);

            switch ($action) {
                case 'approve':
                    wp_set_comment_status($rid, 'approve');
                    break;
                case 'unapprove':
                    wp_set_comment_status($rid, 'hold');
                    break;
                case 'spam':
                    wp_spam_comment($rid);
                    break;
                case 'trash':
                    wp_trash_comment($rid);
                    break;
            }

            $comment = get_comment($rid);
            if ($comment && class_exists('WC_Comments')) {
                WC_Comments::clear_transients($comment->comment_post_ID);
            }

            global $wpdb;
            $wpdb->query("COMMIT");

            wp_safe_redirect(add_query_arg(array('page' => 'ayur-reviews', 'tab' => 'list', 'ayur_msg' => 'Review updated.'), admin_url('admin.php')));
            exit;
        }

        // Bulk Action via POST
        if (isset($_POST['bulk_action'], $_POST['review_ids']) && is_array($_POST['review_ids'])) {
            check_admin_referer('ayur_admin_action_nonce', 'ayur_nonce');
            $action = sanitize_key($_POST['bulk_action']);
            $ids = array_map('absint', $_POST['review_ids']);

            foreach ($ids as $rid) {
                switch ($action) {
                    case 'approve':
                        wp_set_comment_status($rid, 'approve');
                        break;
                    case 'reject':
                        wp_trash_comment($rid);
                        break;
                    case 'spam':
                        wp_spam_comment($rid);
                        break;
                    case 'delete':
                        wp_delete_comment($rid, true);
                        break;
                }
            }

            global $wpdb;
            $wpdb->query("COMMIT");

            wp_safe_redirect(add_query_arg(array('page' => 'ayur-reviews', 'tab' => 'list', 'ayur_msg' => sprintf(__('Bulk action completed on %d reviews.', 'ayur-core'), count($ids))), admin_url('admin.php')));
            exit;
        }

        wp_safe_redirect(admin_url('admin.php?page=ayur-reviews'));
        exit;
    }

    /**
     * Handle Admin Save / Edit Review
     */
    public static function handle_save_review() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Unauthorized access.', 'ayur-core'));
        }

        check_admin_referer('ayur_save_review_nonce', 'ayur_nonce');

        $review_id    = isset($_POST['review_id']) ? absint($_POST['review_id']) : 0;
        $product_id   = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $rating       = isset($_POST['rating']) ? min(5, max(1, absint($_POST['rating']))) : 5;
        $title        = isset($_POST['review_title']) ? sanitize_text_field(wp_unslash($_POST['review_title'])) : '';
        $author_name  = isset($_POST['reviewer_name']) ? sanitize_text_field(wp_unslash($_POST['reviewer_name'])) : '';
        $author_email = isset($_POST['reviewer_email']) ? sanitize_email(wp_unslash($_POST['reviewer_email'])) : '';
        $content      = isset($_POST['review_content']) ? sanitize_textarea_field(wp_unslash($_POST['review_content'])) : '';
        $is_verified  = !empty($_POST['verified']) ? 1 : 0;
        $order_id     = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $status       = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'approved';
        $helpful      = isset($_POST['helpful_count']) ? absint($_POST['helpful_count']) : 0;
        $photos       = isset($_POST['review_photos']) && is_array($_POST['review_photos']) ? array_map('absint', $_POST['review_photos']) : array();

        if (empty($product_id)) {
            wp_die(__('Product is required.', 'ayur-core'));
        }

        // Validate verified purchase claims: must have an order ID or matching order
        if ($is_verified && empty($order_id)) {
            $verified_order_id = self::verify_purchase($author_email, $product_id);
            if ($verified_order_id) {
                $order_id = $verified_order_id;
            } else {
                $is_verified = 0; // Prevent false verified badge!
            }
        }

        $comment_approved = ($status === 'approved') ? 1 : (($status === 'pending') ? 0 : $status);

        if ($review_id > 0) {
            // Update existing
            $comment_data = array(
                'comment_ID'           => $review_id,
                'comment_post_ID'      => $product_id,
                'comment_author'       => $author_name,
                'comment_author_email' => $author_email,
                'comment_content'      => $content,
                'comment_approved'     => $comment_approved,
            );
            wp_update_comment($comment_data);
        } else {
            // Insert new
            $comment_data = array(
                'comment_post_ID'      => $product_id,
                'comment_author'       => $author_name,
                'comment_author_email' => $author_email,
                'comment_content'      => $content,
                'comment_type'         => self::COMMENT_TYPE,
                'comment_approved'     => $comment_approved,
            );
            $review_id = wp_insert_comment($comment_data);
        }

        if ($review_id) {
            update_comment_meta($review_id, 'rating', $rating);
            update_comment_meta($review_id, 'review_title', $title);
            update_comment_meta($review_id, 'verified', $is_verified);
            update_comment_meta($review_id, 'order_id', $order_id);
            update_comment_meta($review_id, 'helpful_count', $helpful);
            update_comment_meta($review_id, 'review_photos', $photos);
            update_comment_meta($review_id, 'review_source', 'admin');

            if (class_exists('WC_Comments')) {
                WC_Comments::clear_transients($product_id);
            }
        }

        global $wpdb;
        $wpdb->query("COMMIT");

        wp_safe_redirect(add_query_arg(array('page' => 'ayur-reviews', 'tab' => 'list', 'ayur_msg' => __('Review saved successfully.', 'ayur-core')), admin_url('admin.php')));
        exit;
    }

    /**
     * Handle CSV Import
     */
    public static function handle_csv_import() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Unauthorized access.', 'ayur-core'));
        }

        check_admin_referer('ayur_import_reviews_nonce', 'ayur_nonce');

        if (empty($_FILES['csv_file']['tmp_name'])) {
            wp_die(__('Please upload a valid CSV file.', 'ayur-core'));
        }

        $import_status = isset($_POST['import_status']) && $_POST['import_status'] === 'approved' ? 1 : 0;
        $file = $_FILES['csv_file']['tmp_name'];

        $handle = fopen($file, 'r');
        if (!$handle) {
            wp_die(__('Unable to read uploaded file.', 'ayur-core'));
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row) || count($row) < 5) {
                $skipped++;
                continue;
            }

            // Map standard columns
            $data = array_combine(array_slice($header, 0, count($row)), $row);

            $product_id = !empty($data['product_id']) ? absint($data['product_id']) : 0;
            if (!$product_id && !empty($data['product_sku'])) {
                $product_id = wc_get_product_id_by_sku(trim($data['product_sku']));
            }

            if (!$product_id || !get_post($product_id)) {
                $skipped++;
                continue;
            }

            $name    = !empty($data['reviewer_name']) ? sanitize_text_field($data['reviewer_name']) : __('Ayurvedic Seeker', 'ayur-core');
            $title   = !empty($data['review_title']) ? sanitize_text_field($data['review_title']) : __('Customer Review', 'ayur-core');
            $content = !empty($data['review_content']) ? sanitize_textarea_field($data['review_content']) : '';
            $rating  = !empty($data['rating']) ? min(5, max(1, absint($data['rating']))) : 5;
            $date    = !empty($data['review_date']) ? sanitize_text_field($data['review_date']) : current_time('mysql');
            $source  = !empty($data['source']) ? sanitize_text_field($data['source']) : 'imported';

            if (empty($content)) {
                $skipped++;
                continue;
            }

            // Check for duplicate review by author, product and content snippet
            $existing = get_comments(array(
                'post_id' => $product_id,
                'author'  => $name,
                'type'    => self::COMMENT_TYPE,
                'count'   => true,
            ));

            if ($existing > 0) {
                $skipped++;
                continue;
            }

            $comment_id = wp_insert_comment(array(
                'comment_post_ID'      => $product_id,
                'comment_author'       => $name,
                'comment_author_email' => 'customer@imported.ayur',
                'comment_content'      => $content,
                'comment_type'         => self::COMMENT_TYPE,
                'comment_approved'     => $import_status,
                'comment_date'         => date('Y-m-d H:i:s', strtotime($date)),
            ));

            if ($comment_id) {
                update_comment_meta($comment_id, 'rating', $rating);
                update_comment_meta($comment_id, 'review_title', $title);
                update_comment_meta($comment_id, 'verified', 0); // Imported reviews default to unverified unless order evidence
                update_comment_meta($comment_id, 'review_source', $source);
                update_comment_meta($comment_id, 'helpful_count', 0);

                if (!empty($data['photo_urls'])) {
                    $urls = array_map('trim', explode(',', $data['photo_urls']));
                    update_comment_meta($comment_id, 'review_photos', $urls);
                }

                $imported++;
            }
        }

        fclose($handle);

        global $wpdb;
        $wpdb->query("COMMIT");

        wp_safe_redirect(add_query_arg(array('page' => 'ayur-reviews', 'tab' => 'list', 'ayur_msg' => sprintf(__('Import finished: %d imported, %d skipped.', 'ayur-core'), $imported, $skipped)), admin_url('admin.php')));
        exit;
    }

    /**
     * Handle Save Review Request Automation Settings
     */
    public static function handle_save_request_settings() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Unauthorized access.', 'ayur-core'));
        }

        check_admin_referer('ayur_save_request_settings_nonce', 'ayur_nonce');

        $enabled    = !empty($_POST['ayur_review_requests_enabled']) ? 'yes' : 'no';
        $delay_days = isset($_POST['ayur_review_request_delay_days']) ? max(1, min(60, absint($_POST['ayur_review_request_delay_days']))) : 7;
        $subject    = isset($_POST['ayur_review_req_subject']) ? sanitize_text_field(wp_unslash($_POST['ayur_review_req_subject'])) : '';
        $body       = isset($_POST['ayur_review_req_body']) ? sanitize_textarea_field(wp_unslash($_POST['ayur_review_req_body'])) : '';

        update_option('ayur_review_requests_enabled', $enabled);
        update_option('ayur_review_request_delay_days', $delay_days);
        update_option('ayur_review_req_subject', $subject);
        update_option('ayur_review_req_body', $body);

        wp_safe_redirect(add_query_arg(array('page' => 'ayur-reviews', 'tab' => 'requests', 'ayur_msg' => __('Settings saved successfully.', 'ayur-core')), admin_url('admin.php')));
        exit;
    }
}
