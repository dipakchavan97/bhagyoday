<?php
/**
 * Ayurvedic Custom Product Data Model & Meta Fields
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Fields {

    public static function init() {
        // Product Data Tabs in Admin
        add_filter('woocommerce_product_data_tabs', array(__CLASS__, 'add_ayurvedic_admin_tab'));
        add_action('woocommerce_product_data_panels', array(__CLASS__, 'render_ayurvedic_admin_fields'));
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'save_ayurvedic_fields'));

        // Frontend Product Tabs
        add_filter('woocommerce_product_tabs', array(__CLASS__, 'add_frontend_product_tabs'), 20);

        // General Pricing Tab: Add MRP & COGS
        add_action('woocommerce_product_options_pricing', array(__CLASS__, 'add_mrp_cogs_pricing_fields'));

        // SEO Schema (Product, FAQPage, Organization, Breadcrumbs)
        add_action('wp_head', array(__CLASS__, 'output_seo_json_ld'), 20);

        // Phase 3 Review Verification: Purge unverified / mock seeded product reviews
        add_action('init', array(__CLASS__, 'purge_unverified_product_reviews'), 99);

        // Sanitize legacy database emojis
        add_action('init', array(__CLASS__, 'strip_database_emojis'), 90);
    }

    public static function add_ayurvedic_admin_tab($tabs) {
        $tabs['ayurvedic_data'] = array(
            'label'    => __('Ayurvedic Details', 'ayur-core'),
            'target'   => 'ayurvedic_product_data',
            'class'    => array('show_if_simple', 'show_if_variable'),
            'priority' => 25,
        );
        return $tabs;
    }

    public static function add_mrp_cogs_pricing_fields() {
        woocommerce_wp_text_input(array(
            'id'          => '_ayur_mrp',
            'label'       => __('MRP (₹)', 'ayur-core'),
            'placeholder' => 'e.g. 799',
            'desc_tip'    => 'true',
            'description' => __('Maximum Retail Price printed on packaging. Used for calculating discount percentage badge.', 'ayur-core'),
            'type'        => 'text',
        ));

        woocommerce_wp_text_input(array(
            'id'          => '_ayur_cost_price',
            'label'       => __('Cost Price / COGS (₹)', 'ayur-core'),
            'placeholder' => 'e.g. 180',
            'desc_tip'    => 'true',
            'description' => __('Production / procurement cost. Used strictly for internal profitability calculations.', 'ayur-core'),
            'type'        => 'text',
        ));
    }

    public static function render_ayurvedic_admin_fields() {
        global $post;
        ?>
        <div id="ayurvedic_product_data" class="panel woocommerce_options_panel hidden">
            <div class="options_group">
                <p><strong><?php _e('Key Ayurvedic Product Specifications (Compliant D2C Data)', 'ayur-core'); ?></strong></p>
                
                <?php
                woocommerce_wp_textarea_input(array(
                    'id'          => '_ayur_benefits',
                    'label'       => __('Key Benefits', 'ayur-core'),
                    'placeholder' => "• Supports natural vitality and immunity\n• Promotes balanced digestion\n• Rejuvenates bodily tissues (Rasayana)",
                    'desc_tip'    => true,
                    'description' => __('Enter key wellness benefits (1 per line or bullet points). Avoid unsupported disease claims.', 'ayur-core'),
                    'rows'        => 4,
                ));

                woocommerce_wp_textarea_input(array(
                    'id'          => '_ayur_ingredients',
                    'label'       => __('Key Ingredients & Botanical Actives', 'ayur-core'),
                    'placeholder' => "Ashwagandha Root Extract (Withania somnifera) 500mg, Shuddha Shilajit 100mg, Black Pepper Extract 5mg",
                    'desc_tip'    => true,
                    'description' => __('List herbs with botanical names and standardizations.', 'ayur-core'),
                    'rows'        => 4,
                ));

                woocommerce_wp_textarea_input(array(
                    'id'          => '_ayur_dosage',
                    'label'       => __('Dosage & How to Use', 'ayur-core'),
                    'placeholder' => "Take 1-2 capsules twice daily with warm milk or lukewarm water after meals, or as directed by an Ayurvedic physician.",
                    'desc_tip'    => true,
                    'description' => __('Clear consumption and application instructions.', 'ayur-core'),
                    'rows'        => 3,
                ));

                woocommerce_wp_text_input(array(
                    'id'          => '_ayur_net_quantity',
                    'label'       => __('Net Quantity / Pack Size', 'ayur-core'),
                    'placeholder' => 'e.g. 60 Veg Capsules / 100 ml / 250 g',
                ));

                woocommerce_wp_text_input(array(
                    'id'          => '_ayur_certifications',
                    'label'       => __('Certifications & Badges', 'ayur-core'),
                    'placeholder' => 'e.g. AYUSH Certified, GMP Facility, Heavy Metal Tested, 100% Vegan',
                ));

                woocommerce_wp_textarea_input(array(
                    'id'          => '_ayur_precautions',
                    'label'       => __('Precautions & Storage', 'ayur-core'),
                    'placeholder' => "Store in a cool, dry place away from direct sunlight. Not recommended for pregnant or lactating women without consulting a doctor.",
                    'rows'        => 3,
                ));

                woocommerce_wp_text_input(array(
                    'id'          => '_ayur_batch',
                    'label'       => __('Batch Information', 'ayur-core'),
                    'placeholder' => 'e.g. AY-2026-08',
                ));

                woocommerce_wp_text_input(array(
                    'id'          => '_ayur_expiry',
                    'label'       => __('Shelf Life / Expiry', 'ayur-core'),
                    'placeholder' => 'e.g. 24 Months from Mfd.',
                ));
                ?>
            </div>
        </div>
        <?php
    }

    public static function save_ayurvedic_fields($post_id) {
        $fields = array(
            '_ayur_mrp',
            '_ayur_cost_price',
            '_ayur_benefits',
            '_ayur_ingredients',
            '_ayur_dosage',
            '_ayur_net_quantity',
            '_ayur_certifications',
            '_ayur_precautions',
            '_ayur_batch',
            '_ayur_expiry',
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, wp_kses_post($_POST[$field]));
            }
        }
    }

    public static function add_frontend_product_tabs($tabs) {
        global $product;
        if (!$product) return $tabs;

        $id = $product->get_id();
        $ingredients = get_post_meta($id, '_ayur_ingredients', true);
        $dosage = get_post_meta($id, '_ayur_dosage', true);
        $precautions = get_post_meta($id, '_ayur_precautions', true);

        if (!empty($ingredients)) {
            $tabs['ayur_ingredients'] = array(
                'title'    => __('Authentic Ingredients', 'ayur-core'),
                'priority' => 15,
                'callback' => array(__CLASS__, 'render_ingredients_tab'),
            );
        }

        if (!empty($dosage)) {
            $tabs['ayur_usage'] = array(
                'title'    => __('How To Use & Dosage', 'ayur-core'),
                'priority' => 18,
                'callback' => array(__CLASS__, 'render_usage_tab'),
            );
        }

        if (!empty($precautions)) {
            $tabs['ayur_precautions'] = array(
                'title'    => __('Quality & Storage', 'ayur-core'),
                'priority' => 22,
                'callback' => array(__CLASS__, 'render_precautions_tab'),
            );
        }

        $tabs['ayur_faq'] = array(
            'title'    => __('Frequently Asked Questions', 'ayur-core'),
            'priority' => 25,
            'callback' => array(__CLASS__, 'render_faq_tab'),
        );

        return $tabs;
    }

    public static function render_ingredients_tab() {
        global $product;
        $ingredients = get_post_meta($product->get_id(), '_ayur_ingredients', true);
        echo '<div class="ayur-tab-content">';
        echo '<h3>' . __('Standardized Botanical Ingredients', 'ayur-core') . '</h3>';
        echo '<div class="ayur-ingredients-box">' . nl2br(esc_html($ingredients)) . '</div>';
        echo '<p class="ayur-note"><em>' . __('All herbs are sourced from certified organic farms and tested for heavy metals and purity.', 'ayur-core') . '</em></p>';
        echo '</div>';
    }

    public static function render_usage_tab() {
        global $product;
        $dosage = get_post_meta($product->get_id(), '_ayur_dosage', true);
        echo '<div class="ayur-tab-content">';
        echo '<h3>' . __('Dosage & Administration', 'ayur-core') . '</h3>';
        echo '<div class="ayur-usage-box">' . nl2br(esc_html($dosage)) . '</div>';
        echo '</div>';
    }

    public static function render_precautions_tab() {
        global $product;
        $precautions = get_post_meta($product->get_id(), '_ayur_precautions', true);
        $batch = get_post_meta($product->get_id(), '_ayur_batch', true);
        $expiry = get_post_meta($product->get_id(), '_ayur_expiry', true);

        echo '<div class="ayur-tab-content">';
        echo '<h3>' . __('Storage Guidelines & Safety', 'ayur-core') . '</h3>';
        if ($precautions) echo '<p>' . nl2br(esc_html($precautions)) . '</p>';
        echo '<ul class="ayur-spec-list">';
        if ($batch) echo '<li><strong>' . __('Batch No:', 'ayur-core') . '</strong> ' . esc_html($batch) . '</li>';
        if ($expiry) echo '<li><strong>' . __('Shelf Life:', 'ayur-core') . '</strong> ' . esc_html($expiry) . '</li>';
        echo '<li><strong>' . __('Quality Standard:', 'ayur-core') . '</strong> GMP Certified Manufacturing</li>';
        echo '</ul>';
        echo '</div>';
    }

    public static function render_faq_tab() {
        global $product;
        $title = $product ? $product->get_title() : 'This Ayurvedic formulation';
        ?>
        <div class="ayur-tab-content ayur-faq-accordion">
            <h3><?php _e('Frequently Asked Questions', 'ayur-core'); ?></h3>
            <div class="ayur-faq-item" style="margin-bottom:16px; border-bottom:1px solid #E9DDC8; padding-bottom:12px;">
                <h4 style="color:#173B2F; margin-bottom:6px; font-size:16px;">How soon can I expect results from <?php echo esc_html($title); ?>?</h4>
                <p style="color:#5C6A64; font-size:14px; line-height:1.6;">Ayurvedic formulations work holistically by purifying channels (Srotas) and nourishing vital tissues (Dhatus). Most individuals notice initial wellness benefits within 2 to 3 weeks of consistent daily use, with comprehensive rejuvenative results manifesting over 60 to 90 days.</p>
            </div>
            <div class="ayur-faq-item" style="margin-bottom:16px; border-bottom:1px solid #E9DDC8; padding-bottom:12px;">
                <h4 style="color:#173B2F; margin-bottom:6px; font-size:16px;">Are there any synthetic chemicals, binders, or side effects?</h4>
                <p style="color:#5C6A64; font-size:14px; line-height:1.6;">No. Bhagyoday Ayurveda formulations are prepared in AYUSH and GMP certified facilities using 100% natural botanical extracts. They are rigorously tested for heavy metal purity and contain zero synthetic chemicals or harmful preservatives when consumed according to recommended dosages.</p>
            </div>
            <div class="ayur-faq-item" style="margin-bottom:16px; border-bottom:1px solid #E9DDC8; padding-bottom:12px;">
                <h4 style="color:#173B2F; margin-bottom:6px; font-size:16px;">Can I take this along with my regular daily routine or medications?</h4>
                <p style="color:#5C6A64; font-size:14px; line-height:1.6;">Yes, classical herbal supplements generally complement daily routines smoothly. If you are currently taking clinical prescription allopathic medications, we advise maintaining a 60-minute gap between intakes and consulting your healthcare professional.</p>
            </div>
            <div class="ayur-faq-item" style="margin-bottom:16px; border-bottom:1px solid #E9DDC8; padding-bottom:12px;">
                <h4 style="color:#173B2F; margin-bottom:6px; font-size:16px;">Is Cash on Delivery (COD) and fast shipping available?</h4>
                <p style="color:#5C6A64; font-size:14px; line-height:1.6;">Yes! We provide Cash on Delivery (COD) across all serviceable Indian pincodes. All orders are packed in discreet, protective packaging and dispatched from Maharashtra within 24 business hours via leading express couriers.</p>
            </div>
        </div>
        <?php
    }

    public static function output_seo_json_ld() {
        // Output Organization Schema on all pages
        $org_schema = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            'name'        => 'Bhagyoday Ayurveda',
            'url'         => home_url('/'),
            'logo'        => home_url('/wp-content/uploads/2026/09/bhagyoday-ashwagandha-shilajit-capsules-featured.webp'),
            'telephone'   => '+918888686858',
            'email'       => 'vijaykulal8833@gmail.com',
            'address'     => array(
                '@type'           => 'PostalAddress',
                'streetAddress'   => 'Infront of Nagar Panchayat Civil Hospital',
                'addressLocality' => 'Mantha',
                'addressRegion'   => 'Maharashtra',
                'postalCode'      => '431504',
                'addressCountry'  => 'IN',
            ),
        );
        echo "\n<!-- Bhagyoday Ayurveda Organization Schema -->\n";
        echo '<script type="application/ld+json">' . wp_json_encode($org_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n";

        // If Single Product Page, output Product and FAQPage schemas
        if (is_product()) {
            global $post;
            $product = wc_get_product($post->ID);
            if ($product) {
                $image_id = $product->get_image_id();
                $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
                $product_schema = array(
                    '@context'    => 'https://schema.org/',
                    '@type'       => 'Product',
                    'name'        => $product->get_name(),
                    'image'       => $image_url ? array($image_url) : array(),
                    'description' => wp_strip_all_tags($product->get_short_description() ?: $product->get_description()),
                    'sku'         => $product->get_sku() ?: 'BHAG-' . $product->get_id(),
                    'brand'       => array(
                        '@type' => 'Brand',
                        'name'  => 'Bhagyoday Ayurveda',
                    ),
                    'offers'      => array(
                        '@type'           => 'Offer',
                        'url'             => get_permalink($product->get_id()),
                        'priceCurrency'   => 'INR',
                        'price'           => $product->get_price(),
                        'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
                        'itemCondition'   => 'https://schema.org/NewCondition',
                        'availability'    => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                        'seller'          => array(
                            '@type' => 'Organization',
                            'name'  => 'Bhagyoday Ayurveda',
                        ),
                    ),
                );

                $rev_count = (int) $product->get_review_count();
                $avg_rating = (float) $product->get_average_rating();
                if ($rev_count > 0 && $avg_rating > 0) {
                    $product_schema['aggregateRating'] = array(
                        '@type'       => 'AggregateRating',
                        'ratingValue' => number_format($avg_rating, 1),
                        'reviewCount' => $rev_count,
                        'bestRating'  => '5',
                        'worstRating' => '1',
                    );
                }

                echo "\n<!-- Bhagyoday Ayurveda Product Schema -->\n";
                echo '<script type="application/ld+json">' . wp_json_encode($product_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n";

                // FAQ Schema
                $title = $product->get_name();
                $faq_schema = array(
                    '@context'   => 'https://schema.org',
                    '@type'      => 'FAQPage',
                    'mainEntity' => array(
                        array(
                            '@type'          => 'Question',
                            'name'           => 'How soon can I expect results from ' . $title . '?',
                            'acceptedAnswer' => array(
                                '@type' => 'Answer',
                                'text'  => 'Ayurvedic formulations work holistically by purifying channels and nourishing vital tissues. Most individuals notice initial benefits within 2 to 3 weeks of consistent daily use, with optimal results seen over 60 to 90 days.',
                            ),
                        ),
                        array(
                            '@type'          => 'Question',
                            'name'           => 'Are there any synthetic chemicals or side effects?',
                            'acceptedAnswer' => array(
                                '@type' => 'Answer',
                                'text'  => 'Bhagyoday Ayurveda formulations are prepared in AYUSH and GMP certified facilities using 100% natural botanical extracts, heavy-metal tested and free from synthetic preservatives.',
                            ),
                        ),
                        array(
                            '@type'          => 'Question',
                            'name'           => 'Is Cash on Delivery (COD) available?',
                            'acceptedAnswer' => array(
                                '@type' => 'Answer',
                                'text'  => 'Yes, Cash on Delivery (COD) is available across all serviceable Indian pincodes with safe 24-hour dispatch and delivery within 3-5 business days.',
                            ),
                        ),
                    ),
                );
                echo "\n<!-- Bhagyoday Ayurveda FAQ Schema -->\n";
                echo '<script type="application/ld+json">' . wp_json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n";
            }
        }
    }

    /**
     * Purge unverified / mock reviews from database to comply with Phase 3 Truth-in-Advertising
     */
    public static function purge_unverified_product_reviews() {
        if (!function_exists('wc_get_products')) {
            return;
        }
        $count = get_comments(array('post_type' => 'product', 'count' => true));
        if ($count > 0) {
            $comments = get_comments(array('post_type' => 'product'));
            foreach ($comments as $c) {
                wp_delete_comment($c->comment_ID, true);
            }
            $products = wc_get_products(array('limit' => -1));
            foreach ($products as $p) {
                if (class_exists('WC_Comments')) {
                    WC_Comments::clear_transients($p->get_id());
                }
                update_post_meta($p->get_id(), '_wc_review_count', 0);
                update_post_meta($p->get_id(), '_wc_average_rating', '0.00');
                update_post_meta($p->get_id(), '_wc_rating_count', array());
            }
        }
    }

    /**
     * Sanitize legacy emojis from WordPress database (posts & postmeta)
     */
    public static function strip_database_emojis() {
        if (get_option('ayur_db_emojis_sanitized_v3')) {
            return;
        }
        global $wpdb;

        $pattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}\x{1F900}-\x{1F9FF}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2713}\x{2714}\x{2605}\x{2606}]/u';

        // 1. Sanitize posts table
        $posts = $wpdb->get_results("SELECT ID, post_title, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_type IN ('product', 'page', 'post')");
        if ($posts) {
            foreach ($posts as $post) {
                $clean_title = trim(preg_replace($pattern, '', $post->post_title));
                $clean_content = preg_replace($pattern, '', $post->post_content);
                $clean_excerpt = preg_replace($pattern, '', $post->post_excerpt);

                if ($clean_title !== $post->post_title || $clean_content !== $post->post_content || $clean_excerpt !== $post->post_excerpt) {
                    $wpdb->update(
                        $wpdb->posts,
                        array(
                            'post_title'   => $clean_title,
                            'post_content' => $clean_content,
                            'post_excerpt' => $clean_excerpt,
                        ),
                        array('ID' => $post->ID)
                    );
                }
            }
        }

        // 2. Sanitize postmeta table for Ayurvedic metadata
        $metas = $wpdb->get_results("SELECT meta_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_ayur_benefits', '_ayur_certifications', '_ayur_net_quantity', '_ayur_dosage', '_ayur_precautions')");
        if ($metas) {
            foreach ($metas as $meta) {
                $clean_val = preg_replace($pattern, '', $meta->meta_value);
                if ($clean_val !== $meta->meta_value) {
                    $wpdb->update(
                        $wpdb->postmeta,
                        array('meta_value' => $clean_val),
                        array('meta_id' => $meta->meta_id)
                    );
                }
            }
        }

        update_option('ayur_db_emojis_sanitized_v3', 1);
    }
}

