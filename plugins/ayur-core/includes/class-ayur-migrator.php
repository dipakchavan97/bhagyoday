<?php
/**
 * Bhagyoday Ayurveda Production Catalog & Business Data Migrator
 *
 * Fully authorized migration of authentic products, images, categories,
 * business info, and conversion-focused copy.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Migrator {

    public static function init() {
        add_action('init', array(__CLASS__, 'check_migration_trigger'));
        add_action('template_redirect', array(__CLASS__, 'handle_legacy_redirects'));
    }

    public static function handle_legacy_redirects() {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (empty($uri)) return;

        $map = array(
            '/Bhagyoday-Ayurveda-Ashwagandha-Shilajit-Men-Wellness-Capsule-800-Mg-30/catalogue/ytBo3OSg/SczrSUHS' => '/product/ashwagandha-shilajit-men-wellness-capsules/',
            '/Bhagyoday-Ayurveda-Slim-Care-Weight-Management-Juice-500-Ml/catalogue/rbSYVZnQ/Grw45yxu' => '/product/slim-care-weight-management-juice/',
            '/Bhagyoday-Ayurveda-Aloe-Vera-Triphala-Piles-Care-Capsule-500-Mg/catalogue/WMOgXUDA/IGNfs7np' => '/product/aloe-vera-triphala-piles-care-capsules/',
            '/Bhagyoday-Ayurveda-Amrit-Heart-Health-Immunity-Juice-500-Ml/catalogue/Kp3mAafD/e5VVY_H8' => '/product/arjuna-amrit-heart-vitality-juice/',
            '/Bhagyoday-Ayurveda-Karela-Jamun-Blood-Sugar-Control-Capsule-500-Mg/catalogue/RPT3AyKk/3VAEOpMM' => '/product/karela-jamun-gurmar-sugar-balance-capsules/',
            '/Bhagyoday-Ayurveda-Shallaki-Joint-Bone-Care-Capsule-500-Mg-60/catalogue/Jf_yaOkK/u25m0wJs' => '/product/shallaki-hadjod-joint-mobility-capsules/',
            '/Bhagyoday-Ayurveda-Shatavari-Ashoka-Women-Wellness-Juice-500-Ml/catalogue/wH306cyK/qPqGz4h1' => '/product/shatavari-ashoka-womens-vitality-juice/',
            '/featured/all-products' => '/shop/',
            '/featured/new-arrivals' => '/shop/',
            '/featured/best-sellers' => '/shop/',
            '/AYURVEDA/collection/5olo-9cY' => '/shop/',
            '/AMAZING-DEALS/collection/afYchHUL' => '/product-category/combos/',
            '/TOP-PRODUCTS/collection/nt4bMsyG' => '/shop/',
            '/return-policy' => '/refund-policy/',
            '/search' => '/shop/',
        );

        $path = rtrim(strtok($uri, '?'), '/');
        if (isset($map[$path])) {
            wp_safe_redirect(home_url($map[$path]), 301);
            exit;
        }
    }

    public static function check_migration_trigger() {
        if (isset($_GET['ayur_upgrade_men_power']) && $_GET['ayur_upgrade_men_power'] === '2026_men_power_verified') {
            self::upgrade_men_power_product();
        }

        if (isset($_GET['ayur_run_source_migration'])) {
            wp_die('Migration complete. This endpoint has been permanently deactivated for production security.', 'Access Denied', array('response' => 403));
        }
    }

    public static function upgrade_men_power_product() {
        if (!class_exists('WooCommerce')) {
            wp_die('WooCommerce not loaded', 'Error', array('response' => 500));
        }

        $prod_id = wc_get_product_id_by_sku('AYUR-SHIL-30');
        if (!$prod_id) {
            $prod_id = 30;
        }

        $product = wc_get_product($prod_id);
        if (!$product) {
            wp_die('Product not found', 'Error', array('response' => 404));
        }

        // 1. Capture Before Snapshot
        $before = array(
            'id'             => $product->get_id(),
            'name'           => $product->get_name(),
            'slug'           => $product->get_slug(),
            'sku'            => $product->get_sku(),
            'regular_price'  => $product->get_regular_price(),
            'sale_price'     => $product->get_sale_price(),
            'price'          => $product->get_price(),
            'weight'         => $product->get_weight(),
            'length'         => $product->get_length(),
            'width'          => $product->get_width(),
            'height'         => $product->get_height(),
            'mrp_meta'       => get_post_meta($prod_id, '_ayur_mrp', true),
            'ingredients'    => get_post_meta($prod_id, '_ayur_ingredients', true),
            'benefits'       => get_post_meta($prod_id, '_ayur_benefits', true),
            'dosage'         => get_post_meta($prod_id, '_ayur_dosage', true),
            'net_qty'        => get_post_meta($prod_id, '_ayur_net_quantity', true),
            'certifications' => get_post_meta($prod_id, '_ayur_certifications', true),
        );

        // 2. Verified Content Definition (Strict Price, SKU, Shiprocket, and Compliance Protection)
        $new_title = 'Men Power Capsule 800mg (30 Capsules)';
        $new_short_desc = 'Men Power Capsule — Daily Herbal Wellness Formula crafted with 10 verified Ayurvedic botanicals including Ashwagandha, Safed Musli, and Shudh Shilajeet for natural stamina and physical vitality.';
        $new_desc = "<h3>Men Power Capsule (800mg)</h3>\n<p class=\"ayur-lead\">Daily Herbal Wellness Formula for Men crafted with pure, verified classical Ayurvedic botanicals to support sustained physical stamina, natural vitality, and daily wellness.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BENEFITS</h4>\n<ul class=\"ayur-feature-list\">\n<li>Synergistic blend of 10 verified classical Ayurvedic botanicals</li>\n<li>Supports healthy physical endurance, daily stamina, and active lifestyle</li>\n<li>Aids natural recovery, vitality, and nervous resilience</li>\n<li>100% vegetarian capsules with zero chemical additives or heavy metals</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>WHAT'S INSIDE</h4>\n<p>Ashwagandha, Safed Musli, Gokhru, Kaunch Beej, Shudh Shilajeet, Garlic, Jaiphal, Vidarikand, Maca Root, Kali Mirch, Vegetarian Plant Cellulose Capsule.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE & DOSAGE</h4>\n<p>Take 1 capsule twice daily with warm milk or lukewarm water, ideally after breakfast and dinner. For optimal results, use consistently for 60 to 90 days.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 30 Vegetarian Capsules<br><strong>Form:</strong> Vegetable Capsule (800 mg)<br><strong>Pack:</strong> Sealed Amber Bottle<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool, dry place away from direct sunlight.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Classical Ayurvedic proprietary formulation. Not intended to diagnose, treat, cure, or prevent any medical condition. Consult your physician if you are under medication for any chronic health condition.</em></p>\n</div>";

        // Verified 10 Ingredients (preserving exact quantitative mg in backend data model)
        $new_ingredients = "Ashwagandha (50mg)\nSafed Musli (50mg)\nGokhru (30mg)\nKaunch Beej (50mg)\nShudh Shilajeet (20mg)\nGarlic (20mg)\nJaiphal (20mg)\nVidarikand (20mg)\nMaca Root (10mg)\nKali Mirch (10mg)\nVegetarian Plant Cellulose Capsule";
        $new_benefits = "• Synergistic blend of 10 verified classical Ayurvedic botanicals\n• Supports healthy physical endurance, daily stamina, and active lifestyle\n• Aids natural recovery, vitality, and nervous resilience\n• 100% vegetarian capsules with zero synthetic additives or heavy metals";
        $new_dosage = 'Take 1 capsule twice daily with warm milk or lukewarm water, ideally after breakfast and dinner.';
        $new_net_qty = '30 Vegetarian Capsules (800 mg)';
        $new_cert = 'AYUSH Licensed • Classical Ayurvedic Formulation • 10 Verified Botanicals';

        // 3. Apply updates preserving prices, SKU, dimensions, and weight
        $product->set_name($new_title);
        $product->set_short_description($new_short_desc);
        $product->set_description($new_desc);
        $product->set_regular_price($before['regular_price']);
        $product->set_sale_price($before['sale_price']);
        $product->set_price($before['price']);
        $product->set_sku($before['sku']);
        $product->set_weight($before['weight']);
        $product->set_length($before['length']);
        $product->set_width($before['width']);
        $product->set_height($before['height']);
        $product->save();

        // 4. Update Meta
        update_post_meta($prod_id, '_ayur_ingredients', $new_ingredients);
        update_post_meta($prod_id, '_ayur_benefits', $new_benefits);
        update_post_meta($prod_id, '_ayur_dosage', $new_dosage);
        update_post_meta($prod_id, '_ayur_net_quantity', $new_net_qty);
        update_post_meta($prod_id, '_ayur_certifications', $new_cert);

        // 5. Reload Product & Capture After Snapshot
        $updated_product = wc_get_product($prod_id);
        $after = array(
            'id'             => $updated_product->get_id(),
            'name'           => $updated_product->get_name(),
            'slug'           => $updated_product->get_slug(),
            'sku'            => $updated_product->get_sku(),
            'regular_price'  => $updated_product->get_regular_price(),
            'sale_price'     => $updated_product->get_sale_price(),
            'price'          => $updated_product->get_price(),
            'weight'         => $updated_product->get_weight(),
            'length'         => $updated_product->get_length(),
            'width'          => $updated_product->get_width(),
            'height'         => $updated_product->get_height(),
            'mrp_meta'       => get_post_meta($prod_id, '_ayur_mrp', true),
            'ingredients'    => get_post_meta($prod_id, '_ayur_ingredients', true),
            'benefits'       => get_post_meta($prod_id, '_ayur_benefits', true),
            'dosage'         => get_post_meta($prod_id, '_ayur_dosage', true),
            'net_qty'        => get_post_meta($prod_id, '_ayur_net_quantity', true),
            'certifications' => get_post_meta($prod_id, '_ayur_certifications', true),
        );

        $response = array(
            'success'              => true,
            'price_unchanged'      => ($before['regular_price'] === $after['regular_price'] && $before['sale_price'] === $after['sale_price'] && $before['price'] === $after['price']),
            'shiprocket_unchanged' => ($before['weight'] === $after['weight'] && $before['length'] === $after['length'] && $before['width'] === $after['width'] && $before['height'] === $after['height']),
            'sku_unchanged'        => ($before['sku'] === $after['sku']),
            'id_unchanged'         => ($before['id'] === $after['id']),
            'before'               => $before,
            'after'                => $after,
            'timestamp'            => current_time('mysql'),
        );

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    public static function execute_migration() {
        $log = array();

        // 1. Clean up ALL products that are NOT in the authentic catalog list
        $authentic_skus = array(
            'AYUR-SHIL-30', 'AYUR-SLIM-500', 'AYUR-PILES-60', 'AYUR-ARJ-500',
            'AYUR-SUGAR-60', 'AYUR-SHAL-60', 'AYUR-SHAT-500',
            'AYUR-COMBO-VIT', 'AYUR-COMBO-DETOX'
        );

        $all_products = wc_get_products(array('limit' => -1, 'return' => 'ids'));
        $deleted_demos = array();
        foreach ($all_products as $p_id) {
            $p_obj = wc_get_product($p_id);
            if ($p_obj) {
                $sku = $p_obj->get_sku();
                if (!in_array($sku, $authentic_skus)) {
                    wp_delete_post($p_id, true);
                    $deleted_demos[] = array('id' => $p_id, 'sku' => $sku, 'title' => $p_obj->get_name());
                }
            }
        }
        $log['cleaned_demo_products'] = $deleted_demos;

        // 2. Ensure Categories
        $categories = array(
            'mens-wellness'          => array('name' => "Men's Vitality & Stamina", 'desc' => 'Classical Rasayana herbs for male vigor, endurance, and physical stamina.'),
            'metabolism-weight-care' => array('name' => 'Metabolism & Daily Detox', 'desc' => 'Ayurvedic botanicals for metabolic balance, digestive fire, and detox.'),
            'digestive'              => array('name' => 'Digestive & Piles Care', 'desc' => 'Gentle Arshoghna herbs for anorectal comfort and smooth bowel movements.'),
            'heart-immunity'         => array('name' => 'Heart Health & Immunity', 'desc' => 'Arjuna bark and antioxidant rasayanas for cardiac resilience and circulation.'),
            'joint-care-mobility'    => array('name' => 'Joint & Bone Care', 'desc' => 'Standardized Boswellia and Hadjod for flexible, comfortable joints.'),
            'womens-wellness'        => array('name' => "Women's Wellness & Vitality", 'desc' => 'Nurturing Shatavari and Ashoka Rasayanas for female hormonal rhythm and energy.'),
            'combos'                 => array('name' => 'Value Combos & Packs', 'desc' => 'Synergistic Ayurvedic regimens offering maximum wellness and value.'),
        );

        $cat_ids = array();
        foreach ($categories as $slug => $cat) {
            $term = get_term_by('slug', $slug, 'product_cat');
            if (!$term) {
                $new_term = wp_insert_term($cat['name'], 'product_cat', array('slug' => $slug, 'description' => $cat['desc']));
                $cat_ids[$slug] = !is_wp_error($new_term) ? $new_term['term_id'] : 0;
            } else {
                wp_update_term($term->term_id, 'product_cat', array('name' => $cat['name'], 'description' => $cat['desc']));
                $cat_ids[$slug] = $term->term_id;
            }
        }
        $log['categories'] = $cat_ids;

        // 3. Products Catalog Data
        $catalog = self::get_authentic_catalog_data();
        $imported_products = array();

        foreach ($catalog as $item) {
            $prod_id = wc_get_product_id_by_sku($item['sku']);
            if ($prod_id) {
                $product = wc_get_product($prod_id);
            } else {
                $product = new WC_Product_Simple();
            }

            $product->set_name($item['title']);
            $product->set_sku($item['sku']);
            if (!empty($item['slug'])) {
                $product->set_slug($item['slug']);
            }
            $product->set_regular_price($item['mrp']);
            $product->set_sale_price($item['sale_price']);
            $product->set_price($item['sale_price']);
            $product->set_short_description($item['short_desc']);
            $product->set_description($item['desc']);
            $product->set_status('publish');
            $product->set_manage_stock(true);
            $product->set_stock_quantity(75);
            $product->set_stock_status('instock');
            $product->set_featured($item['featured']);
            $product->set_reviews_allowed(true);

            if (isset($item['weight'])) {
                $product->set_weight($item['weight']);
            }
            if (isset($item['dimensions'])) {
                $product->set_length($item['dimensions']['length']);
                $product->set_width($item['dimensions']['width']);
                $product->set_height($item['dimensions']['height']);
            }

            if (isset($cat_ids[$item['category']])) {
                $product->set_category_ids(array($cat_ids[$item['category']]));
            }

            $saved_id = $product->save();

            // Custom Meta
            update_post_meta($saved_id, '_ayur_mrp', $item['mrp']);
            update_post_meta($saved_id, '_ayur_cost_price', $item['cogs']);
            update_post_meta($saved_id, '_ayur_source_price', $item['source_price']);
            update_post_meta($saved_id, '_ayur_ingredients', $item['ingredients']);
            update_post_meta($saved_id, '_ayur_benefits', $item['benefits']);
            update_post_meta($saved_id, '_ayur_dosage', $item['dosage']);
            update_post_meta($saved_id, '_ayur_net_quantity', $item['net_qty']);
            update_post_meta($saved_id, '_ayur_certifications', $item['cert']);
            update_post_meta($saved_id, '_ayur_precautions', $item['precautions']);
            update_post_meta($saved_id, '_ayur_batch', 'BA-2026-09');
            update_post_meta($saved_id, '_ayur_expiry', '24 Months from Mfd.');

            // 4. Attach Images
            $attached_images = self::attach_product_images($saved_id, $item);

            // 5. Reviews: Not seeded to comply with Phase 3 Truth-in-Advertising
            // self::seed_product_reviews($saved_id, $item['title']);

            $imported_products[] = array(
                'id'       => $saved_id,
                'sku'      => $item['sku'],
                'title'    => $item['title'],
                'images'   => $attached_images,
            );
        }
        $log['imported_products'] = $imported_products;

        // 6. Update Business & Static Pages
        self::update_business_pages();
        $log['business_pages'] = 'Updated with authentic Bhagyoday Ayurveda information';

        // 7. Update Store Branding Options
        update_option('blogname', 'Bhagyoday Ayurveda');
        update_option('blogdescription', 'Nourishing Lives, Naturally | Authentic Ayurvedic Wellness');
        update_option('ayur_whatsapp_phone', '918888686858');
        update_option('ayur_whatsapp_message', 'Namaste Bhagyoday Ayurveda! I would like some guidance on choosing the right Ayurvedic wellness formulation.');
        $log['options_updated'] = array(
            'blogname' => 'Bhagyoday Ayurveda',
            'phone'    => '918888686858',
            'email'    => 'vijaykulal8833@gmail.com',
            'address'  => 'Mantha, Jalna, Maharashtra 431504'
        );

        flush_rewrite_rules();
        if (has_action('litespeed_purge_all')) {
            do_action('litespeed_purge_all');
        }

        return $log;
    }

    public static function attach_product_images($product_id, $item) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $result = array('featured' => 0, 'gallery' => array());
        $assets_dir = AYUR_CORE_PATH . 'assets/images/';

        // Check if featured image already exists
        $curr_thumb = get_post_thumbnail_id($product_id);
        if (!$curr_thumb && !empty($item['image_files']['featured'])) {
            $feat_file = $assets_dir . $item['image_files']['featured'];
            if (file_exists($feat_file)) {
                $thumb_id = self::insert_attachment_from_local_file($feat_file, $product_id, $item['title'] . ' Bottle', $item['title'] . ' bottle packaging and product presentation');
                if ($thumb_id) {
                    set_post_thumbnail($product_id, $thumb_id);
                    $result['featured'] = $thumb_id;
                }
            }
        } else {
            $result['featured'] = $curr_thumb;
        }

        // Gallery Images
        $gallery_ids = array();
        if (!empty($item['image_files']['gallery'])) {
            foreach ($item['image_files']['gallery'] as $g_file) {
                $full_path = $assets_dir . $g_file;
                if (file_exists($full_path)) {
                    $g_id = self::insert_attachment_from_local_file($full_path, $product_id, $item['title'] . ' Details', $item['title'] . ' ingredients label and product details');
                    if ($g_id) {
                        $gallery_ids[] = $g_id;
                    }
                }
            }
            if (!empty($gallery_ids)) {
                update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
                $result['gallery'] = $gallery_ids;
            }
        }

        return $result;
    }

    public static function insert_attachment_from_local_file($file_path, $parent_post_id, $title, $alt_text) {
        $filename = basename($file_path);
        $upload_dir = wp_upload_dir();
        $target_path = $upload_dir['path'] . '/' . $filename;

        // If file doesn't already exist in uploads, copy it
        if (!file_exists($target_path)) {
            copy($file_path, $target_path);
        }

        // Check if attachment already exists in DB
        global $wpdb;
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid LIKE %s LIMIT 1",
            '%' . $filename
        ));

        if ($existing) {
            return (int) $existing;
        }

        $filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'guid'           => $upload_dir['url'] . '/' . $filename,
            'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/webp',
            'post_title'     => sanitize_text_field($title),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attach_id = wp_insert_attachment($attachment, $target_path, $parent_post_id);
        if (!is_wp_error($attach_id)) {
            $attach_data = wp_generate_attachment_metadata($attach_id, $target_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            update_post_meta($attach_id, '_wp_attachment_image_alt', sanitize_text_field($alt_text));
            return $attach_id;
        }

        return 0;
    }

    public static function update_business_pages() {
        $pages = array(
            'about-us' => array(
                'title'   => 'About Bhagyoday Ayurveda',
                'content' => '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<h2>Nourishing Lives, Naturally</h2>
<p class="lead" style="font-size:18px; color:#173B2F; line-height:1.7;">Bhagyoday Ayurveda brings you a thoughtfully curated range of premium Ayurvedic formulations and wellness supplements, crafted to nurture your body from within. Each product is deeply rooted in 5,000-year-old classical Vedic wisdom and prepared with pure, authentic natural botanicals to support your journey toward holistic wellness.</p>

<h3>The Bhagyoday Philosophy</h3>
<p>We blend time-tested Ayurvedic principles with modern hygienic standards to deliver wellness remedies that truly work. Our products are formulated for health-conscious individuals who seek natural, effective solutions to enhance their vitality and well-being. Whether you are a busy professional managing everyday stress, a homemaker nurturing your family, or someone looking to strengthen immunity and energy levels, Bhagyoday Ayurveda is your trusted partner in health.</p>

<h3>Our Commitment to Purity & Quality</h3>
<ul>
<li><strong>Pure Classical Extraction:</strong> All herbal actives are extracted without harsh chemical solvents, preserving their natural bio-availability and vital Prana.</li>
<li><strong>Ethically Sourced Botanicals:</strong> Herbs like Ashwagandha, Shallaki, Shatavari, and Arjuna are sourced ethically from verified agricultural cooperatives across Maharashtra and central India.</li>
<li><strong>AYUSH & GMP Certified Facilities:</strong> Manufactured in state-of-the-art facilities adhering to stringent Good Manufacturing Practices.</li>
<li><strong>Zero Harmful Additives:</strong> Free from heavy metals, synthetic binders, paraben preservatives, and artificial coloring agents.</li>
</ul>

<h3>Our Mission</h3>
<p>At Bhagyoday Ayurveda, we believe that vibrant health is the foundation of a blessed, prosperous life. We are committed to transparency, quality, and authenticity in every product we create, ensuring that you receive only the finest Ayurvedic nutrition. Our mission is to empower you to take charge of your health naturally, so you can live each day with renewed energy, harmony, and confidence.</p>

<p style="font-style:italic; font-weight:600; color:#173B2F; margin-top:24px;">— The Bhagyoday Ayurveda Family, Mantha, Maharashtra</p>
</div>
<!-- /wp:group -->'
            ),
            'contact-us' => array(
                'title'   => 'Contact Bhagyoday Ayurveda',
                'content' => '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<h2>We Are Here To Assist Your Wellness Journey</h2>
<p>Have questions about selecting the right Ayurvedic remedy, understanding herbal ingredients, or tracking your order? Our dedicated wellness team in Maharashtra is here to support you.</p>

<div style="background:#FAF7F0; border:1px solid #E9DDC8; border-radius:12px; padding:28px; margin:24px 0;">
<h3 style="margin-top:0; color:#173B2F;">Bhagyoday Ayurveda Customer Care</h3>
<p><strong>Corporate & Dispatch Address:</strong><br>Bhagyoday Ayurveda<br>Infront of Nagar Panchayat Civil Hospital,<br>Mantha, District Jalna, Maharashtra – 431504, India</p>

<p><strong>Direct Phone Helpline:</strong> +91 8888686858<br>
<strong>WhatsApp Support:</strong> <a href="https://wa.me/918888686858" target="_blank" rel="noopener noreferrer">+91 8888686858 (Chat with an Expert)</a><br>
<strong>Official Support Email:</strong> <a href="mailto:vijaykulal8833@gmail.com">vijaykulal8833@gmail.com</a><br>
<strong>Support Availability:</strong> Monday – Saturday, 9:00 AM – 7:00 PM IST (24/7 WhatsApp assistance)</p>
</div>

<h3>Direct Order Assistance</h3>
<p>If you prefer placing your order directly via WhatsApp or phone call, our customer representatives can take your delivery address and dispatch via Cash on Delivery (COD) across all serviceable Indian pincodes.</p>
</div>
<!-- /wp:group -->'
            ),
            'shipping-policy' => array(
                'title'   => 'Shipping & Delivery Policy',
                'content' => '<h2>Fast & Reliable Express Shipping Across India</h2><p>At Bhagyoday Ayurveda, all orders are packed with tamper-evident, protective packaging and dispatched directly from our central fulfillment hub in Maharashtra. We partner with India’s leading logistics networks (Delhivery, BlueDart, DTDC, XpressBees) to ensure swift delivery to your doorstep.</p><h3>Delivery Timelines</h3><ul><li><strong>Order Processing:</strong> All confirmed orders are dispatched within 24 to 48 business hours.</li><li><strong>Metro Cities:</strong> 2 to 4 business days.</li><li><strong>Rest of India:</strong> 3 to 6 business days.</li></ul><h3>Shipping Charges</h3><ul><li><strong>Free Express Shipping:</strong> Applies automatically to all orders above ₹999 across India.</li><li><strong>Standard Shipping:</strong> A flat ₹70 shipping charge applies to orders under ₹999.</li><li><strong>Cash on Delivery (COD):</strong> Available across all serviceable Indian pincodes.</li></ul>'
            ),
            'refund-policy' => array(
                'title'   => '7-Day Return & Refund Policy',
                'content' => '<h2>7-Day Customer Satisfaction Guarantee</h2><p>Your satisfaction and wellness are our top priorities. If you receive an item that is damaged, leaked in transit, expired, or incorrect, please notify our customer care team within 7 days of delivery.</p><h3>How to Request a Replacement or Refund</h3><ol><li>Message our WhatsApp helpline at <strong>+91 8888686858</strong> or email <strong>vijaykulal8833@gmail.com</strong> with your Order ID and clear photos/unboxing video of the damaged item.</li><li>Our support team will verify your request within 24 hours.</li><li>We will initiate an immediate free replacement or process a 100% refund to your original payment method (or via UPI for COD orders).</li></ol>'
            ),
            'privacy-policy' => array(
                'title'   => 'Privacy & Data Protection Policy',
                'content' => '<h2>Privacy Policy</h2><p>Bhagyoday Ayurveda is committed to safeguarding your personal data in full compliance with the Information Technology Act and the Digital Personal Data Protection Act of India. We collect only necessary details (such as your delivery name, address, phone number, and email) strictly to fulfill your orders, provide dispatch tracking notifications, and offer customer support. We never sell, rent, or trade your personal information to third-party marketing companies.</p>'
            ),
            'terms-and-conditions' => array(
                'title'   => 'Terms and Conditions',
                'content' => '<h2>Terms of Service</h2><p>Welcome to Bhagyoday Ayurveda. By browsing or purchasing from our online store, you agree to these terms. All products featured are classical Ayurvedic proprietary preparations and dietary supplements designed to maintain natural health and balance. They are not intended as substitutes for professional medical diagnosis or clinical prescription medications. All prices are in Indian Rupees (₹) and include applicable taxes.</p>'
            )
        );

        foreach ($pages as $slug => $pdata) {
            $existing = get_page_by_path($slug);
            if ($existing) {
                wp_update_post(array(
                    'ID'           => $existing->ID,
                    'post_title'   => $pdata['title'],
                    'post_content' => $pdata['content'],
                    'post_status'  => 'publish',
                ));
            } else {
                wp_insert_post(array(
                    'post_name'    => $slug,
                    'post_title'   => $pdata['title'],
                    'post_content' => $pdata['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ));
            }
        }
    }

    public static function seed_product_reviews($product_id, $product_name) {
        return; // Compliance: Never publish unverified or fabricated reviews
    }

    public static function get_authentic_catalog_data() {
        return array(
            array(
                'title'        => 'Men Power Capsule 800mg (30 Capsules)',
                'slug'         => 'ashwagandha-shilajit-men-wellness-capsules',
                'sku'          => 'AYUR-SHIL-30',
                'mrp'          => '1990',
                'sale_price'   => '890',
                'source_price' => '1100',
                'cogs'         => '240',
                'category'     => 'mens-wellness',
                'featured'     => true,
                'weight'       => 0.14,
                'dimensions'   => array('length' => 6, 'width' => 6, 'height' => 11),
                'net_qty'      => '30 Vegetarian Capsules (800 mg)',
                'cert'         => 'AYUSH Licensed • Classical Ayurvedic Formulation • 10 Verified Botanicals',
                'short_desc'   => 'Men Power Capsule — Daily Herbal Wellness Formula crafted with 10 verified Ayurvedic botanicals including Ashwagandha, Safed Musli, and Shudh Shilajeet for natural stamina and physical vitality.',
                'image_files'  => array(
                    'featured' => 'bhagyoday-ashwagandha-shilajit-capsules-featured.webp',
                    'gallery'  => array(
                        'bhagyoday-ashwagandha-shilajit-capsules-gallery-1.webp',
                        'bhagyoday-ashwagandha-shilajit-capsules-packaging.webp'
                    )
                ),
                'desc'         => "<h3>Men Power Capsule (800mg)</h3>\n<p class=\"ayur-lead\">Daily Herbal Wellness Formula for Men crafted with pure, verified classical Ayurvedic botanicals to support sustained physical stamina, natural vitality, and daily wellness.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BENEFITS</h4>\n<ul class=\"ayur-feature-list\">\n<li>Synergistic blend of 10 verified classical Ayurvedic botanicals</li>\n<li>Supports healthy physical endurance, daily stamina, and active lifestyle</li>\n<li>Aids natural recovery, vitality, and nervous resilience</li>\n<li>100% vegetarian capsules with zero chemical additives or heavy metals</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>WHAT'S INSIDE</h4>\n<p>Ashwagandha, Safed Musli, Gokhru, Kaunch Beej, Shudh Shilajeet, Garlic, Jaiphal, Vidarikand, Maca Root, Kali Mirch, Vegetarian Plant Cellulose Capsule.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE & DOSAGE</h4>\n<p>Take 1 capsule twice daily with warm milk or lukewarm water, ideally after breakfast and dinner. For optimal results, use consistently for 60 to 90 days.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 30 Vegetarian Capsules<br><strong>Form:</strong> Vegetable Capsule (800 mg)<br><strong>Pack:</strong> Sealed Amber Bottle<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool, dry place away from direct sunlight.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Classical Ayurvedic proprietary formulation. Not intended to diagnose, treat, cure, or prevent any medical condition. Consult your physician if you are under medication for any chronic health condition.</em></p>\n</div>",
                'ingredients'  => "Ashwagandha (50mg)\nSafed Musli (50mg)\nGokhru (30mg)\nKaunch Beej (50mg)\nShudh Shilajeet (20mg)\nGarlic (20mg)\nJaiphal (20mg)\nVidarikand (20mg)\nMaca Root (10mg)\nKali Mirch (10mg)\nVegetarian Plant Cellulose Capsule",
                'benefits'     => "• Synergistic blend of 10 verified classical Ayurvedic botanicals\n• Supports healthy physical endurance, daily stamina, and active lifestyle\n• Aids natural recovery, vitality, and nervous resilience\n• 100% vegetarian capsules with zero synthetic additives or heavy metals",
                'dosage'       => 'Take 1 capsule twice daily with warm milk or lukewarm water, ideally after breakfast and dinner.',
                'precautions'  => 'Store in a cool dry place away from direct sunlight. Not recommended for children under 18 years. Consult physician if you suffer from high uric acid.',
            ),

            array(
                'title'        => 'Slim Care Weight Management Juice 500ml',
                'slug'         => 'slim-care-weight-management-juice',
                'sku'          => 'AYUR-SLIM-500',
                'mrp'          => '1990',
                'sale_price'   => '890',
                'source_price' => '1100',
                'cogs'         => '260',
                'category'     => 'metabolism-weight-care',
                'featured'     => true,
                'weight'       => 0.62,
                'dimensions'   => array('length' => 7, 'width' => 7, 'height' => 22),
                'net_qty'      => '500 ml Bottle',
                'cert'         => 'AYUSH Certified • Cold-Pressed Botanic Actives • Zero Added Sugar',
                'short_desc'   => 'Classical Botanical Metabolic Elixir with Garcinia Cambogia, Triphala, and Guggulu to Support Digestive Fire (Agni) and Healthy Weight Balance.',
                'image_files'  => array(
                    'featured' => 'bhagyoday-slim-care-juice-featured.webp',
                    'gallery'  => array(
                        'bhagyoday-slim-care-juice-gallery-1.webp',
                        'bhagyoday-slim-care-juice-packaging.webp'
                    )
                ),
                'desc'         => "<h3>Slim Care Weight Management Juice (500ml)</h3>\n<p class=\"ayur-lead\">A synergistic blend of Vrikshamla (Garcinia), Triphala, and Guggal designed to awaken the digestive fire, flush cellular toxins, and support healthy weight balance.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BENEFITS</h4>\n<ul class=\"ayur-feature-list\">\n<li>Stimulates optimal digestive fire (Deepana-Pachana) to burn toxic Ama</li>\n<li>Vrikshamla (Garcinia) supports natural satiety and curbing sweet cravings</li>\n<li>Triphala aids daily intestinal cleansing and regular bowel evacuation</li>\n<li>Pure herbal cold-pressed liquid with zero added refined sugars or artificial colors</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY CHOOSE THIS PRODUCT</h4>\n<p>Bhagyoday Ayurveda Slim Care Juice uses whole-herb cold extraction to preserve bioactive hydroxycitric acid (HCA) and polyphenols. It approaches weight wellness through the root Ayurvedic principle of balancing Meda Dhatu (fat tissue) and purifying the metabolic channels (Srotas).</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>INGREDIENTS</h4>\n<p>Vrikshamla / Garcinia Cambogia Extract 30%, Pure Aloe Vera Inner Leaf Juice (Aloe barbadensis) 25%, Triphala Classical Decoction (Amla, Haritaki, Bibhitaki) 20%, Shuddha Guggulu (Commiphora mukul) 10%, Green Tea (Camellia sinensis) Extract 10%, Nagarmotha (Cyperus rotundus) 5%.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE & DOSAGE</h4>\n<p>Dilute 20 to 30 ml of juice in a glass of lukewarm water. Drink once daily on an empty stomach in the morning, 30 minutes before breakfast.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 500 ml<br><strong>Form:</strong> Cold-pressed herbal tonic<br><strong>Pack:</strong> BPA-Free Bottle with Measuring Cup<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool dry place. Refrigerate after opening and consume within 30 days.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Herbal dietary supplement for healthy metabolism. Best paired with clean hydration and regular walking. Not recommended during pregnancy.</em></p>\n</div>",
                'ingredients'  => "Vrikshamla / Garcinia Cambogia Extract 30%\nPure Aloe Vera Inner Leaf Juice (Aloe barbadensis) 25%\nTriphala Classical Decoction (Amla, Haritaki, Bibhitaki) 20%\nShuddha Guggulu (Commiphora mukul) 10%\nGreen Tea Extract (Camellia sinensis) 10%\nNagarmotha (Cyperus rotundus) 5%",
                'benefits'     => "• Stimulates optimal digestive fire (Deepana-Pachana) to burn toxic Ama\n• Vrikshamla (Garcinia) supports natural satiety and curbing sweet cravings\n• Triphala aids daily intestinal cleansing and regular bowel evacuation\n• Pure herbal cold-pressed liquid with zero added refined sugars",
                'dosage'       => 'Dilute 20-30 ml in a glass of lukewarm water. Drink once daily in the morning on an empty stomach.',
                'precautions'  => 'Refrigerate after opening and consume within 30 days. Not recommended for pregnant or nursing mothers.',
            ),

            array(
                'title'        => 'Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)',
                'slug'         => 'aloe-vera-triphala-piles-care-capsules',
                'sku'          => 'AYUR-PILES-60',
                'mrp'          => '1990',
                'sale_price'   => '890',
                'source_price' => '1100',
                'cogs'         => '230',
                'category'     => 'digestive',
                'featured'     => false,
                'weight'       => 0.14,
                'dimensions'   => array('length' => 6, 'width' => 6, 'height' => 11),
                'net_qty'      => '60 Vegetarian Capsules (500 mg)',
                'cert'         => 'AYUSH Licensed • Classical Arshoghna Formulation • 100% Non-Habit Forming',
                'short_desc'   => 'Soothing Classical Arshoghna Herbal Formulation for Easy Bowel Movements, Anorectal Comfort, and Reduced Burning Sensations.',
                'image_files'  => array(
                    'featured' => 'bhagyoday-piles-care-capsules-featured.webp',
                    'gallery'  => array(
                        'bhagyoday-piles-care-capsules-gallery-1.webp',
                        'bhagyoday-piles-care-capsules-packaging.webp'
                    )
                ),
                'desc'         => "<h3>Aloe Vera Triphala Piles Care Capsules (500mg)</h3>\n<p class=\"ayur-lead\">Classical Arshoghna botanicals formulated to relieve internal digestive heat, promote gentle stool evacuation, and soothe inflamed pelvic tissues.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BENEFITS</h4>\n<ul class=\"ayur-feature-list\">\n<li>Promotes soft, effortless stool passage without straining or urgency</li>\n<li>Calms local burning sensations, itching, and swelling in the pelvic area</li>\n<li>Nagkesar and Neem offer astringent properties that support vascular tone</li>\n<li>Gentle, non-habit-forming bowel regulator suitable for regular use</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY CHOOSE THIS PRODUCT</h4>\n<p>Anorectal discomfort often stems from chronic constipation, excess internal heat (Pitta aggravation), and sluggish digestion. Bhagyoday Ayurveda Piles Care combines the cooling, tissue-healing properties of Ghritkumari (Aloe Vera) with the digestive harmonization of Triphala and the vascular toning of Nagkesar.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>INGREDIENTS</h4>\n<p>Aloe Vera (Ghritkumari) Extract 150 mg, Triphala Classical Extract (Haritaki, Bibhitaki, Amalaki) 150 mg, Nagkesar (Mesua ferrea) Flower Stamen 100 mg, Neem Leaf Extract (Azadirachta indica) 50 mg, Shuddha Guggulu (Commiphora mukul) 50 mg, Plant Cellulose Capsule.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE & DOSAGE</h4>\n<p>Take 1 to 2 capsules twice daily with a full glass of lukewarm water after meals (morning and night before bed). Ensure adequate dietary water intake (2.5-3 liters daily).</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 60 Vegetarian Capsules<br><strong>Strength:</strong> 500 mg<br><strong>Form:</strong> Capsule<br><strong>Pack:</strong> Amber Pharmaceutical Bottle<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool dry place away from heat.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Classical Ayurvedic gut comfort formula. For severe or prolonged bleeding, consult a proctologist immediately.</em></p>\n</div>",
                'ingredients'  => "Aloe Vera (Ghritkumari) Extract 150 mg\nTriphala Extract (Haritaki, Bibhitaki, Amalaki) 150 mg\nNagkesar (Mesua ferrea) Flower Stamen 100 mg\nNeem Leaf Extract (Azadirachta indica) 50 mg\nShuddha Guggulu (Commiphora mukul) 50 mg\nPlant Cellulose Capsule",
                'benefits'     => "• Promotes soft, effortless stool passage without straining\n• Calms local burning sensations and pelvic tissue swelling\n• Nagkesar and Neem support healthy vascular tone\n• 100% non-habit-forming digestive harmony",
                'dosage'       => 'Take 1 to 2 capsules twice daily with lukewarm water after meals.',
                'precautions'  => 'Store in a cool dry place. Avoid excess spicy and constipating deep-fried foods. Drink plenty of water.',
            ),

            array(
                'title'        => 'Amrit Heart Health Immunity Juice 500ml',
                'slug'         => 'arjuna-amrit-heart-vitality-juice',
                'sku'          => 'AYUR-ARJ-500',
                'mrp'          => '1990',
                'sale_price'   => '890',
                'source_price' => '1100',
                'cogs'         => '270',
                'category'     => 'heart-immunity',
                'featured'     => false,
                'weight'       => 0.62,
                'dimensions'   => array('length' => 7, 'width' => 7, 'height' => 22),
                'net_qty'      => '500 ml Bottle',
                'cert'         => 'AYUSH Licensed • Ayurvedic Proprietary Medicine • 100% Natural Botanicals',
                'short_desc'   => 'An Ayurvedic proprietary formulation featuring a curated blend of 60 herbs, fruits, and botanical extracts.',
                'image_files'  => array(
                    'featured' => 'bhagyoday-amrit-heart-juice-featured.webp',
                    'gallery'  => array(
                        'bhagyoday-amrit-heart-juice-gallery-1.webp',
                        'bhagyoday-amrit-heart-juice-composition.webp',
                        'bhagyoday-amrit-heart-juice-packaging.webp'
                    )
                ),
                'desc'         => "<h3>Amrit Heart Health Immunity Juice (500ml)</h3>\n<p class=\"ayur-lead\">A multi-ingredient Ayurvedic proprietary medicine crafted with 60 herbs, fruits, and extracts.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BENEFITS</h4>\n<ul class=\"ayur-feature-list\">\n<li>Multi-botanical synergy featuring 60 carefully specified herbs, fruits, and extracts</li>\n<li>Features classical Rasayana botanicals including Moringa, Noni, Amla, Ashwagandha, and Shilajit</li>\n<li>Supports daily vitality, natural immunity, and overall physiological wellness</li>\n<li>100% natural ingredients with zero added sugar, artificial colours, or synthetic flavours</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BOTANICALS</h4>\n<p>Each 10ml contains specified extracts including Moringa Oleifera 500mg, Noni 400mg, Amla 400mg, Ashwagandha 300mg, Shilajit 300mg, Giloy 300mg, Shatavari 300mg, Arjuna 200mg, and 52 complementary botanical extracts.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE & DOSAGE</h4>\n<p>Shake well before use. Mix 15 to 25 ml in a glass of normal or lukewarm water. Drink once daily on an empty stomach in the morning.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 500 ml<br><strong>Form:</strong> Liquid Herbal Tonic (Ayurvedic Proprietary Medicine)<br><strong>Pack:</strong> Food-grade bottle with measuring cap<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool dry place. Refrigerate after opening and consume within 30 days.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Ayurvedic proprietary formulation. Not intended to diagnose, treat, cure, or prevent any disease. Consult a physician before use if pregnant, lactating, or undergoing medical treatment.</em></p>\n</div>",
                'ingredients'  => "60 Herbs, Fruits & Extracts: Noni 400mg, Aloevera 300mg, Acai Berry 400mg, Sea Buckthorn 300mg, Beetroot 300mg, Harad 300mg, Baheda 300mg, Bhumi Amla 300mg, Shilajit 300mg, Mulethi 300mg, Giloy 300mg, Shatavari 300mg, Ashwagandha 300mg, Amla 400mg, Moringa Oleifera 500mg, Arjuna 200mg, and 44 additional extracts specified per 10ml.",
                'benefits'     => "• Broad-spectrum support for natural vitality and daily immune defense\n• 60 carefully specified botanical extracts per 10ml serving\n• Formulated with zero added sugar, artificial colours, or synthetic flavours\n• Standardized Ayurvedic proprietary medicine",
                'dosage'       => 'Shake well before use. Mix 15 to 25 ml in a glass of lukewarm water. Drink once daily on an empty stomach in the morning.',
                'precautions'  => 'Store in a cool dry place. Refrigerate after opening. Consult physician before use if pregnant or taking medication.',
            ),

            array(
                'title'        => 'Karela Jamun Blood Sugar Control Capsules 500mg (60 Capsules)',
                'slug'         => 'karela-jamun-gurmar-sugar-balance-capsules',
                'sku'          => 'AYUR-SUGAR-60',
                'mrp'          => '1990',
                'sale_price'   => '890',
                'source_price' => '1100',
                'cogs'         => '240',
                'category'     => 'metabolism-weight-care',
                'featured'     => false,
                'weight'       => 0.14,
                'dimensions'   => array('length' => 6, 'width' => 6, 'height' => 11),
                'net_qty'      => '60 Vegetarian Capsules (500 mg)',
                'cert'         => 'AYUSH Licensed • Standardized Botanical Actives • Zero Synthetic Binders',
                'short_desc'   => 'Potent 5-Herb Synergy with Bitter Gourd, Jamun Seed, and Gymnema (The Sugar Destroyer) to Support Healthy Glucose Metabolism and Pancreatic Vitality.',
                'image_files'  => array(
                    'featured' => 'bhagyoday-karela-jamun-capsules-featured.webp',
                    'gallery'  => array(
                        'bhagyoday-karela-jamun-capsules-gallery-1.webp',
                        'bhagyoday-karela-jamun-capsules-packaging.webp'
                    )
                ),
                'desc'         => "<h3>Karela Jamun Blood Sugar Control Capsules (500mg)</h3>\n<p class=\"ayur-lead\">Classical 5-herb botanical formula with Karela, Jamun seed, Gurmar (the sugar destroyer), and Fenugreek to support natural carbohydrate metabolism and curb sugar cravings.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BENEFITS</h4>\n<ul class=\"ayur-feature-list\">\n<li>Gurmar (Gymnema sylvestre) suppresses sweet taste sensation and curbs sugar snacking</li>\n<li>Jamun seed (Jambu) aids healthy postprandial glucose stability</li>\n<li>Karela (Momordica charantia) provides charantin for cellular glucose uptake</li>\n<li>Fenugreek and Vijayasar support healthy pancreatic digestive secretions</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY CHOOSE THIS PRODUCT</h4>\n<p>Bhagyoday Ayurveda Blood Sugar Balance Capsules utilize standardized botanical extracts rather than raw unpurified powders. The 5-herb classical combination works synergistically on both the digestive tract and peripheral tissues to maintain steady energy without sharp afternoon dips.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>INGREDIENTS</h4>\n<p>Pure Karela (Bitter Gourd) Extract 150 mg, Jamun Seed (Syzygium cumini) Extract 150 mg, Gurmar (Gymnema sylvestre) Extract 100 mg, Methi (Fenugreek Seed) 50 mg, Vijayasar (Pterocarpus marsupium) 50 mg, Plant Cellulose Capsule.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE & DOSAGE</h4>\n<p>Take 1 capsule twice daily, approximately 30 minutes before your morning and evening meals with lukewarm water.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 60 Vegetarian Capsules<br><strong>Strength:</strong> 500 mg<br><strong>Form:</strong> Capsule<br><strong>Pack:</strong> Sealed Amber Bottle<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool dry place away from moisture.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Dietary wellness supplement for carbohydrate metabolism. Monitor blood sugar regularly when taking alongside antidiabetic medication.</em></p>\n</div>",
                'ingredients'  => "Karela Extract (Momordica charantia) 150 mg\nJamun Seed Extract (Syzygium cumini) 150 mg\nGurmar Extract (Gymnema sylvestre) 100 mg\nMethi Seed Powder 50 mg\nVijayasar Extract 50 mg\nPlant Cellulose Capsule",
                'benefits'     => "• Helps maintain healthy fasting and postprandial glucose levels\n• Blocks sweet receptors temporarily to suppress sugar cravings\n• Promotes optimal pancreatic digestive secretions\n• Non-habit-forming botanical formulation",
                'dosage'       => 'Take 1 capsule twice daily, 30 minutes before major meals with lukewarm water.',
                'precautions'  => 'Monitor blood sugar levels regularly. Do not alter prescribed antidiabetic medications without physician advice.',
            ),

            array(
                'title'        => 'Shallaki Joint Bone Care Capsules 500mg (60 Capsules)',
                'slug'         => 'shallaki-hadjod-joint-mobility-capsules',
                'sku'          => 'AYUR-SHAL-60',
                'mrp'          => '1990',
                'sale_price'   => '890',
                'source_price' => '1100',
                'cogs'         => '250',
                'category'     => 'joint-care-mobility',
                'featured'     => false,
                'weight'       => 0.14,
                'dimensions'   => array('length' => 6, 'width' => 6, 'height' => 11),
                'net_qty'      => '60 Vegetarian Capsules (500 mg)',
                'cert'         => 'AYUSH Certified • Standardized 65% Boswellic Acids • Stomach Gentle',
                'short_desc'   => 'Standardized Boswellia Serrata (65% Boswellic Acids) with Hadjod and Nirgundi for Joint Comfort, Cartilage Lubrication, and Morning Flexibility.',
                'image_files'  => array(
                    'featured' => 'bhagyoday-shallaki-joint-care-capsules-featured.webp',
                    'gallery'  => array(
                        'bhagyoday-shallaki-joint-care-capsules-gallery-1.webp',
                        'bhagyoday-shallaki-joint-care-capsules-packaging.webp'
                    )
                ),
                'desc'         => "<h3>Shallaki Joint Bone Care Capsules (500mg)</h3>\n<p class=\"ayur-lead\">Standardized Boswellia serrata gum resin combined with Hadjod and Nirgundi to soothe morning stiffness, nourish synovial fluids, and support cartilage resilience.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BENEFITS</h4>\n<ul class=\"ayur-feature-list\">\n<li>High-potency Shallaki (Boswellia) helps soothe knee, hip, and spinal stiffness</li>\n<li>Hadjod (Asthisamharaka) strengthens bone mineral density and connective tendons</li>\n<li>Nirgundi and Shuddha Guggulu support natural joint lubrication and smooth flexion</li>\n<li>Gentle on gastric lining; 100% free of synthetic NSAID side effects</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY CHOOSE THIS PRODUCT</h4>\n<p>Joint discomfort and morning stiffness are classically linked to aggravated Vata in the joints (Sandhigata Vata). Bhagyoday Ayurveda Shallaki Joint Care combines concentrated Boswellic acids—which inhibit pro-inflammatory enzymes—with bone-knitting Hadjod to promote lasting joint health and active mobility.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>INGREDIENTS</h4>\n<p>Standardized Shallaki (Boswellia serrata) Gum Resin Extract (min 65% Boswellic Acids) 250 mg, Hadjod (Cissus quadrangularis) Stem Extract 100 mg, Nirgundi (Vitex negundo) Leaf Extract 75 mg, Shuddha Guggulu (Commiphora mukul) 75 mg, Plant Cellulose Capsule.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE & DOSAGE</h4>\n<p>Take 1 capsule twice daily with meals or warm water. For lasting structural joint comfort, continuous usage for 8 to 12 weeks is recommended.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 60 Vegetarian Capsules<br><strong>Strength:</strong> 500 mg<br><strong>Form:</strong> Capsule<br><strong>Pack:</strong> Sealed Amber Bottle<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool dry place away from heat.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Classical Ayurvedic Sandhivata formulation. Safe for long-term daily use. Consult your physician if pregnant.</em></p>\n</div>",
                'ingredients'  => "Shallaki Gum Resin Extract (min 65% Boswellic Acids) 250 mg\nHadjod Stem Extract (Cissus quadrangularis) 100 mg\nNirgundi Leaf Extract (Vitex negundo) 75 mg\nShuddha Guggulu (Commiphora mukul) 75 mg\nPlant Cellulose Capsule",
                'benefits'     => "• Helps restore natural synovial fluid lubrication in joints\n• Calms inflammatory cytokines to ease morning stiffness\n• Strengthens collagen integrity in ligaments and tendons\n• Safe for long-term daily use without gastric irritation",
                'dosage'       => 'Take 1 capsule twice daily after breakfast and dinner with water.',
                'precautions'  => 'Store in a cool dry place. Consult your doctor if suffering from acute rheumatoid conditions.',
            ),

            array(
                'title'        => 'Shatavari Ashoka Women Wellness Juice 500ml',
                'slug'         => 'shatavari-ashoka-womens-vitality-juice',
                'sku'          => 'AYUR-SHAT-500',
                'mrp'          => '1990',
                'sale_price'   => '890',
                'source_price' => '1100',
                'cogs'         => '260',
                'category'     => 'womens-wellness',
                'featured'     => true,
                'weight'       => 0.62,
                'dimensions'   => array('length' => 7, 'width' => 7, 'height' => 22),
                'net_qty'      => '500 ml Bottle',
                'cert'         => 'AYUSH Licensed • Classical Stree Roga Rasayana • 100% Botanical Actives',
                'short_desc'   => 'Nurturing Nari Rasayana with Pure Shatavari Root, Classical Ashoka Bark, Lodhra, and Dashmula for Hormonal Rhythm, Cycle Comfort, and Radiant Vitality.',
                'image_files'  => array(
                    'featured' => 'bhagyoday-shatavari-ashoka-juice-featured.webp',
                    'gallery'  => array(
                        'bhagyoday-shatavari-ashoka-juice-gallery-1.webp',
                        'bhagyoday-shatavari-ashoka-juice-packaging.webp'
                    )
                ),
                'desc'         => "<h3>Shatavari Ashoka Women Wellness Juice (500ml)</h3>\n<p class=\"ayur-lead\">Rejuvenating female Rasayana combining pure Shatavari root, classical Ashoka bark, Lodhra, and Dashmula to support hormonal rhythm, ease monthly discomfort, and nourish feminine vitality.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BENEFITS</h4>\n<ul class=\"ayur-feature-list\">\n<li>Shatavari (Queen of Herbs) supports female hormonal balance and natural stamina</li>\n<li>Classical Ashoka bark and Lodhra promote uterine wellness and monthly cycle comfort</li>\n<li>Calms periodic abdominal heaviness, mood swings, and monthly fatigue</li>\n<li>Pure botanical elixir with no added white sugar, artificial colors, or harsh chemicals</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY CHOOSE THIS PRODUCT</h4>\n<p>Women's wellness requires gentle, nourishing herbs that adapt to natural bodily cycles. Bhagyoday Ayurveda Shatavari Ashoka Juice harmonizes classical Stree Roga herbs in a bio-available liquid form, supporting physical energy, reproductive health, and emotional equilibrium through all stages of womanhood.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>INGREDIENTS</h4>\n<p>Pure Shatavari (Asparagus racemosus) Root Extract 35%, Classical Ashoka Bark (Saraca asoca) Decoction 30%, Lodhra (Symplocos racemosa) 15%, Dashmula Herbal Blend 10%, Fresh Amla Juice 10%.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE & DOSAGE</h4>\n<p>Dilute 20 ml in half a glass of warm water and take twice daily after meals.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 500 ml Bottle<br><strong>Form:</strong> Cold-pressed liquid tonic<br><strong>Pack:</strong> Food-grade bottle with measuring cap<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool dry place. Refrigerate after opening and consume within 30 days.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Classical Nari Rasayana wellness tonic. Consult gynecologist if pregnant or nursing.</em></p>\n</div>",
                'ingredients'  => "Shatavari Root Extract (Asparagus racemosus) 35%\nAshoka Bark Decoction (Saraca asoca) 30%\nLodhra Bark Extract (Symplocos racemosa) 15%\nDashmula Classical Decoction 10%\nAmla Juice 10%",
                'benefits'     => "• Supports monthly hormonal rhythm and uterine wellness\n• Eases physical fatigue and stabilizes mood fluctuations\n• Calms periodic abdominal cramping and pelvic discomfort\n• Enhances natural skin radiance and holistic female stamina",
                'dosage'       => 'Take 20 ml diluted with half a glass of warm water twice daily after meals.',
                'precautions'  => 'Store in a cool dry place. Refrigerate after opening. Consult doctor if pregnant or lactating.',
            ),

            // Authentic Curated Bundles
            array(
                'title'        => 'Daily Energy & Vitality Duo (Shilajit Capsules 30s + Amrit Heart Juice 500ml)',
                'slug'         => 'daily-energy-vitality-duo-shilajit-amrit-heart',
                'sku'          => 'AYUR-COMBO-VIT',
                'mrp'          => '3980',
                'sale_price'   => '1690',
                'source_price' => '2200',
                'cogs'         => '490',
                'category'     => 'combos',
                'featured'     => true,
                'weight'       => 0.76,
                'dimensions'   => array('length' => 12, 'width' => 8, 'height' => 22),
                'net_qty'      => 'Combo Pack: 30 Capsules + 500 ml Juice',
                'cert'         => 'AYUSH Licensed • Complete Vitality Regimen • Extra 57% Value Savings',
                'short_desc'   => 'Synergistic combination of Ashwagandha Shilajit Capsules and Amrit Heart Immunity Juice for sustained physical energy, cardiovascular stamina, and vitality.',
                'image_files'  => array(
                    'featured' => 'bhagyoday-ashwagandha-shilajit-capsules-featured.webp',
                    'gallery'  => array(
                        'bhagyoday-amrit-heart-juice-featured.webp',
                        'bhagyoday-ashwagandha-shilajit-capsules-packaging.webp'
                    )
                ),
                'desc'         => "<h3>Daily Energy & Vitality Duo</h3>\n<p class=\"ayur-lead\">Pairing Bhagyoday Ayurveda Ashwagandha Shilajit Capsules with Amrit Heart Health Juice creates an all-encompassing morning-to-night wellness regimen.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>WHAT IS INCLUDED</h4>\n<ul>\n<li>1x Ashwagandha Shilajit Men Wellness Capsules (30 Veg Capsules 800mg)</li>\n<li>1x Amrit Heart Health Immunity Juice (500 ml Bottle with measuring cap)</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE THE REGIMEN</h4>\n<p><strong>Morning:</strong> Take 15-20 ml of Amrit Heart Juice diluted in warm water 30 minutes before breakfast.<br><strong>Post-Breakfast & Dinner:</strong> Take 1 Shilajit Capsule with warm milk or water.</p>\n</div>",
                'ingredients'  => "Ashwagandha (300mg), Shuddha Shilajit (350mg), Safed Musli (100mg), Amrit 60-Botanical Complex (Moringa, Noni, Amla, Giloy, Shatavari & 55 more herbs, fruits and extracts).",
                'benefits'     => "• Comprehensive vitality for heart, muscle stamina, and stress resilience\n• All-natural bioavailable liquid + capsule synergy\n• Outstanding savings compared to individual prices\n• Free Express Delivery across India",
                'dosage'       => 'Juice in morning before breakfast; 1 Capsule after breakfast and dinner.',
                'precautions'  => 'Store in cool dry place. Refrigerate juice after opening.',
            ),

            array(
                'title'        => 'Metabolic Detox & Sugar Balance Duo (Karela Jamun 60s + Slim Care Juice 500ml)',
                'slug'         => 'metabolic-detox-sugar-balance-duo',
                'sku'          => 'AYUR-COMBO-DETOX',
                'mrp'          => '3980',
                'sale_price'   => '1690',
                'source_price' => '2200',
                'cogs'         => '490',
                'category'     => 'combos',
                'featured'     => true,
                'weight'       => 0.76,
                'dimensions'   => array('length' => 12, 'width' => 8, 'height' => 22),
                'net_qty'      => 'Combo Pack: 60 Capsules + 500 ml Juice',
                'cert'         => 'AYUSH Licensed • Deep Metabolic Cleanse • Extra 57% Value Savings',
                'short_desc'   => 'Powerful Ayurvedic regimen pairing Karela Jamun Gurmar Blood Sugar Capsules with Slim Care Weight Management Juice for enhanced metabolism and digestive cleansing.',
                'image_files'  => array(
                    'featured' => 'bhagyoday-slim-care-juice-featured.webp',
                    'gallery'  => array(
                        'bhagyoday-karela-jamun-capsules-featured.webp',
                        'bhagyoday-slim-care-juice-packaging.webp'
                    )
                ),
                'desc'         => "<h3>Metabolic Detox & Sugar Balance Duo</h3>\n<p class=\"ayur-lead\">Combining classical Deepana-Pachana digestive juice with potent glucose-modulating capsules for complete metabolic harmony.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>WHAT IS INCLUDED</h4>\n<ul>\n<li>1x Karela Jamun Blood Sugar Control Capsules (60 Veg Capsules 500mg)</li>\n<li>1x Slim Care Weight Management Juice (500 ml Bottle with measuring cap)</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE THE REGIMEN</h4>\n<p><strong>Morning:</strong> Drink 20-30 ml Slim Care Juice diluted in lukewarm water on an empty stomach.<br><strong>Before Meals:</strong> Take 1 Karela Jamun Capsule 30 minutes before lunch and dinner.</p>\n</div>",
                'ingredients'  => "Garcinia Cambogia (30%), Aloe Vera (25%), Triphala (20%), Guggulu (10%), Green Tea (10%), Karela (150mg), Jamun (150mg), Gurmar (100mg), Methi (50mg), Vijayasar (50mg).",
                'benefits'     => "• Supports healthy carbohydrate metabolism and glucose levels\n• Flushes digestive toxins and reduces sweet snack cravings\n• Boosts daily energy without afternoon sluggishness\n• Complete 30-day metabolic reset",
                'dosage'       => 'Juice in morning on empty stomach; 1 Capsule before lunch and dinner.',
                'precautions'  => 'Store in cool dry place. Refrigerate juice after opening. Monitor blood sugar regularly.',
            ),
        );
    }
}
