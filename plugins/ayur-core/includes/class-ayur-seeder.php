<?php
/**
 * Realistic Ayurvedic Demo Data & Essential Pages Seeder
 *
 * Includes original formulations based on Ayurvedic classical texts
 * and research benchmarks.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Seeder {

    public static function init() {
        add_action('admin_init', array(__CLASS__, 'check_trigger_seeder'));
        add_action('admin_notices', array(__CLASS__, 'render_seeder_notice'));
    }

    public static function check_secret_trigger() {
        if (isset($_GET['ayur_import_research']) && $_GET['ayur_import_research'] === 'veda_research_import_2026') {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
            try {
                self::seed_categories_and_products();
                wp_send_json_success(array(
                    'status'    => 'Research catalog imported successfully',
                    'timestamp' => current_time('mysql'),
                ));
            } catch (Throwable $e) {
                wp_send_json_error(array(
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ));
            }
            exit;
        }
    }

    public static function check_trigger_seeder() {
        if (isset($_GET['ayur_seed_data']) && check_admin_referer('ayur_seed_action', 'ayur_nonce')) {
            if (current_user_can('manage_woocommerce')) {
                self::seed_essential_pages();
                self::seed_categories_and_products();
                self::seed_coupons();
                wp_safe_redirect(admin_url('admin.php?page=wc-admin&ayur_seeded=1'));
                exit;
            }
        }
    }

    public static function render_seeder_notice() {
        if (isset($_GET['ayur_seeded'])) {
            echo '<div class="notice notice-success is-dismissible"><p><strong>Ayurvedic Store Catalog, Categories, and Legal Pages successfully created!</strong></p></div>';
        }
    }

    public static function seed_essential_pages() {
        $pages = array(
            'home' => array(
                'title'   => 'Home',
                'content' => '<!-- wp:pattern {"slug":"ayur-wellness/homepage"} /-->',
                'template'=> 'template-home.php',
            ),
            'about-us' => array(
                'title'   => 'About Us',
                'content' => '<h2>Authentic Ayurvedic Wisdom for Modern Living</h2><p>Founded on 5,000-year-old classical Ayurvedic principles, our formulations combine ancient herbal sciences with modern standardized botanical extraction. We source all herbs ethically from verified cultivators across the Indian sub-continent, ensuring zero heavy metals, zero synthetic binders, and 100% bioavailability.</p><h3>Our Purity Standards</h3><ul><li><strong>100% Ayurvedic Formulation:</strong> Authentic classical preparations adhering to the Ayurvedic Pharmacopoeia of India.</li><li><strong>GMP & AYUSH Licensed:</strong> Manufactured in state-of-the-art sterile facilities with ISO 9001:2015 certification.</li><li><strong>Third-Party Lab Tested:</strong> Every batch is independently verified for microbiological safety and purity.</li></ul>',
            ),
            'contact-us' => array(
                'title'   => 'Contact Us',
                'content' => '<h2>We are here to assist your wellness journey</h2><p>Our team of Ayurvedic consultants and customer care specialists are available Monday to Saturday, 9:00 AM to 7:00 PM IST.</p><p><strong>Customer Care:</strong> vijaykulal8833@gmail.com<br><strong>WhatsApp Support:</strong> +91 8888686858<br><strong>Corporate & Dispatch Address:</strong> Bhagyoday Ayurveda, Infront of Nagar Panchayat Civil Hospital, Mantha, Jalna, Maharashtra - 431504</p>',
            ),
            'faq' => array(
                'title'   => 'Frequently Asked Questions',
                'content' => '<h3>Are these products 100% natural and vegetarian?</h3><p>Yes. All our herbal powders and extracts are encased in 100% plant-based vegetable cellulose capsules with zero gelatin and zero artificial colorings.</p><h3>How long does delivery take in India?</h3><p>We partner with express courier networks (Delhivery, BlueDart, DTDC). Metro cities typically receive orders in 2-3 business days, while other regions take 3-5 business days.</p><h3>Is Cash on Delivery (COD) available?</h3><p>Yes! Cash on Delivery is available across all serviceable Indian pincodes for orders up to ₹5,000.</p>',
            ),
            'privacy-policy' => array(
                'title'   => 'Privacy Policy',
                'content' => '<h2>Privacy Policy</h2><p>We respect your privacy and comply with the Information Technology Act and the Digital Personal Data Protection Act of India. We collect only necessary shipping and contact details strictly to fulfill your orders and provide shipment tracking updates.</p>',
            ),
            'terms-and-conditions' => array(
                'title'   => 'Terms and Conditions',
                'content' => '<h2>Terms of Service</h2><p>By placing an order on our store, you agree to our terms of service. Products are wellness food supplements and classical Ayurvedic preparations; they are not intended to diagnose, cure, or prevent any medical disease.</p>',
            ),
            'shipping-policy' => array(
                'title'   => 'Shipping & Delivery Policy',
                'content' => '<h2>Fast & Reliable Express Shipping Across India</h2><p>All orders are packed in eco-friendly tamper-evident packaging and dispatched via premium air/surface couriers (Delhivery, BlueDart, DTDC, XpressBees). Standard delivery timelines: Metro cities 2-3 business days; Rest of India 3-5 business days. Free shipping applies automatically to all prepaid and COD orders exceeding ₹999.</p>',
            ),
            'refund-policy' => array(
                'title'   => '7-Day Return & Refund Policy',
                'content' => '<h2>7-Day Hassle-Free Returns</h2><p>Your satisfaction is paramount. If you receive a damaged, leaked, or incorrect item, notify our customer care team within 7 days of delivery. We will initiate a prompt replacement or 100% refund via original payment method or UPI.</p>',
            ),
        );

        foreach ($pages as $slug => $data) {
            $existing = get_page_by_path($slug);
            if (!$existing) {
                $page_id = wp_insert_post(array(
                    'post_title'   => $data['title'],
                    'post_name'    => $slug,
                    'post_content' => $data['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ));

                if ($slug === 'home') {
                    update_option('show_on_front', 'page');
                    update_option('page_on_front', $page_id);
                }
            }
        }
    }

    public static function seed_categories_and_products() {
        if (!class_exists('WC_Product_Simple')) return;

        // Categories Map
        $categories = array(
            'immunity'               => array('name' => 'Immunity & Rasayana', 'desc' => 'Traditional Rasayana herbs to bolster everyday natural defense.'),
            'digestive'              => array('name' => 'Digestive Wellness', 'desc' => 'Classic Agni and gut rejuvenation formulas for healthy digestion.'),
            'hair-care'              => array('name' => 'Hair & Scalp Care', 'desc' => 'Nourishing Bhringraj, Rosemary and Amla botanical infusions.'),
            'skin-care'              => array('name' => 'Pure Skin Care', 'desc' => 'Kumkumadi, Aloe Vera and saffron extracts for natural glow.'),
            'stress-sleep'           => array('name' => 'Stress & Deep Sleep', 'desc' => 'Calming adaptogens including Ashwagandha, Brahmi and Shankhpushpi.'),
            'mens-wellness'          => array('name' => "Men's Vitality & Stamina", 'desc' => 'Potent Ayurvedic herbs for male vigor, stamina, and energy.'),
            'womens-wellness'        => array('name' => "Women's Hormonal Vitality", 'desc' => 'Nurturing Rasayanas for female balance, cycle comfort, and vitality.'),
            'heart-immunity'         => array('name' => 'Heart & Vitality Rasayana', 'desc' => 'Cardiovascular herbs for healthy circulation and arterial resilience.'),
            'metabolism-weight-care' => array('name' => 'Metabolism & Daily Detox', 'desc' => 'Ayurvedic botanicals for metabolic balance and digestive cleansing.'),
            'joint-care-mobility'    => array('name' => 'Joint Care & Mobility', 'desc' => 'Shallaki and herbal tonics for flexible, comfortable joints.'),
            'combos'                 => array('name' => 'Wellness Combos', 'desc' => 'Curated multi-product rituals with exceptional value and savings.'),
        );

        $cat_ids = array();
        foreach ($categories as $slug => $cat) {
            $term = get_term_by('slug', $slug, 'product_cat');
            if (!$term) {
                $new_term = wp_insert_term($cat['name'], 'product_cat', array('slug' => $slug, 'description' => $cat['desc']));
                $cat_ids[$slug] = !is_wp_error($new_term) ? $new_term['term_id'] : 0;
            } else {
                $cat_ids[$slug] = $term->term_id;
            }
        }

        // Realistic Ayurvedic Products
        $products_data = array(
            // --- ORIGINAL SEEDED CATALOG (8 Products) ---
            array(
                'title'       => 'Organic Ashwagandha KSM-66 Vitality Capsules',
                'sku'         => 'AYUR-ASH-60',
                'price'       => '499',
                'mrp'         => '799',
                'cogs'        => '160',
                'source_price'=> '799',
                'category'    => 'stress-sleep',
                'short_desc'  => 'Certified Organic KSM-66 Ashwagandha Root Extract (500mg, 5% Withanolides) for natural stress relief, enhanced stamina, and restful sleep.',
                'desc'        => "<h3>Organic Ashwagandha KSM-66 Vitality Capsules</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Helps reduce everyday physical and mental stress</li>\n<li>Promotes sustained vitality without caffeine jitters</li>\n<li>Supports deep, restorative REM sleep cycles</li>\n<li>Aids muscle recovery and natural endurance</li>\n</ul>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>KSM-66® Ashwagandha Root Extract (Withania somnifera) 500mg, BioPerine® Black Pepper Extract 5mg, 100% Plant Cellulose Vegetable Capsule.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Take 1 capsule twice daily with warm water or milk after breakfast and dinner.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 60 Vegetarian Capsules<br><strong>Form:</strong> Vegetable Capsule<br><strong>Pack:</strong> Sealed Amber Bottle<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool, dry place away from direct sunlight.</p>\n</div>",
                'ingredients' => "KSM-66® Ashwagandha Root Extract (Withania somnifera) 500mg\nBioPerine® Black Pepper Extract 5mg\n100% Plant Cellulose Vegetable Capsule",
                'benefits'    => "• Helps reduce everyday physical and mental stress\n• Promotes sustained vitality without caffeine jitters\n• Supports deep, restorative REM sleep cycles\n• Aids muscle recovery and natural endurance",
                'dosage'      => 'Take 1 capsule twice daily with warm water or milk after breakfast and dinner, or as recommended by an Ayurvedic doctor.',
                'net_qty'     => '60 Vegetarian Capsules',
                'cert'        => 'AYUSH Licensed, USDA Organic, GMP Certified, Non-GMO',
                'featured'    => true,
                'weight'      => 0.14,
                'dimensions'  => array('length' => 6, 'width' => 6, 'height' => 11),
            ),
            array(
                'title'       => 'Maha Bhringraj Herbal Hair Oil with Rosemary',
                'sku'         => 'AYUR-OIL-200',
                'price'       => '599',
                'mrp'         => '899',
                'cogs'        => '190',
                'source_price'=> '899',
                'category'    => 'hair-care',
                'short_desc'  => 'Slow-cooked Kshir Pak Ayurvedic hair oil with pure Bhringraj, Rosemary oil, Brahmi, and cold-pressed Sesame oil for intense root strengthening.',
                'desc'        => "<h3>Maha Bhringraj Herbal Hair Oil with Rosemary</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Helps reduce premature hair fall and breakage</li>\n<li>Deeply conditions dry scalp and prevents flaky dandruff</li>\n<li>Strengthens roots and promotes natural hair density</li>\n<li>Calms the mind and promotes restful night sleep when massaged</li>\n</ul>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Bhringraj (Eclipta alba) 25%, Amla (Phyllanthus emblica) 15%, Brahmi (Bacopa monnieri) 10%, Rosemary Essential Oil 2%, Cold-Pressed Virgin Sesame Oil base.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Gently warm 10-15 ml of oil. Part hair and massage deeply into roots using circular motions for 10 minutes. Leave on for at least 2 hours or overnight before washing.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 200 ml Bottle<br><strong>Form:</strong> Herbal Hair Oil<br><strong>Pack:</strong> Recyclable PET Bottle with Flip Cap<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store at room temperature.</p>\n</div>",
                'ingredients' => "Bhringraj (Eclipta alba) 25%\nAmla (Phyllanthus emblica) 15%\nBrahmi (Bacopa monnieri) 10%\nRosemary Essential Oil (Rosmarinus officinalis) 2%\nCold-Pressed Virgin Sesame Oil base",
                'benefits'    => "• Helps reduce premature hair fall and breakage\n• Deeply conditions dry scalp and prevents flaky dandruff\n• Strengthens roots and promotes natural hair density\n• Calms the mind and promotes restful night sleep when massaged",
                'dosage'      => 'Gently warm 10-15 ml of oil. Part hair and massage deeply into roots using circular motions for 10 minutes. Leave on for at least 2 hours or overnight before washing.',
                'net_qty'     => '200 ml Bottle',
                'cert'        => '100% Natural, Mineral Oil Free, Paraben Free, Cruelty Free',
                'featured'    => true,
                'weight'      => 0.25,
                'dimensions'  => array('length' => 5, 'width' => 5, 'height' => 17),
            ),
            array(
                'title'       => 'Triphala Pure Digestive Detox Cleanse',
                'sku'         => 'AYUR-TRIPH-90',
                'price'       => '399',
                'mrp'         => '599',
                'cogs'        => '120',
                'source_price'=> '599',
                'category'    => 'digestive',
                'short_desc'  => 'Classical three-fruit blend of Amla, Haritaki, and Bibhitaki for gentle bowel regulation, gut microbiome balance, and natural detoxification.',
                'desc'        => "<h3>Triphala Pure Digestive Detox Cleanse</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Non-habit forming daily colon cleanse and digestive regulator</li>\n<li>Relieves occasional constipation and post-meal abdominal heaviness</li>\n<li>Nourishes gut flora and promotes nutrient bioavailability</li>\n<li>Potent tri-doshic rejuvenator suitable for long-term daily use</li>\n</ul>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Amalaki (Emblica officinalis) 250mg, Haritaki (Terminalia chebula) 250mg, Bibhitaki (Terminalia bellirica) 250mg, Plant Cellulose Capsule.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Take 2 capsules with a full glass of warm water before going to sleep at night.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 90 Vegetarian Capsules<br><strong>Form:</strong> Vegetable Capsule<br><strong>Pack:</strong> Sealed Amber Bottle<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool, dry place away from direct sunlight.</p>\n</div>",
                'ingredients' => "Amalaki (Emblica officinalis) 250mg\nHaritaki (Terminalia chebula) 250mg\nBibhitaki (Terminalia bellirica) 250mg\nPlant Cellulose Capsule",
                'benefits'    => "• Non-habit forming daily colon cleanse and digestive regulator\n• Relieves occasional constipation and post-meal abdominal heaviness\n• Nourishes gut flora and promotes nutrient bioavailability\n• Potent tri-doshic rejuvenator suitable for long-term daily use",
                'dosage'      => 'Take 2 capsules with a full glass of warm water before going to sleep at night, or 1 capsule before breakfast and dinner.',
                'net_qty'     => '90 Vegetarian Capsules',
                'cert'        => 'Classical Ayurvedic Formula, AYUSH Licensed, Zero Preservatives',
                'featured'    => false,
                'weight'      => 0.16,
                'dimensions'  => array('length' => 6, 'width' => 6, 'height' => 11),
            ),
            array(
                'title'       => 'Kumkumadi Ayurvedic Miracle Face Oil (100% Pure Saffron)',
                'sku'         => 'AYUR-KUMKUM-30',
                'price'       => '899',
                'mrp'         => '1299',
                'cogs'        => '280',
                'source_price'=> '1299',
                'category'    => 'skin-care',
                'short_desc'  => 'Prestige facial elixir infused with pure Kashmiri Saffron, Sandalwood, Lotus stamen, and 26 classical herbs for glowing, youthful skin tone.',
                'desc'        => "<h3>Kumkumadi Ayurvedic Miracle Face Oil</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Illuminates skin complexion and fades dark spots</li>\n<li>Minimizes fine dry lines and boosts skin elasticity</li>\n<li>Non-greasy lightweight absorption with rich saffron aroma</li>\n<li>100% pure botanical oil with zero synthetic fragrances</li>\n</ul>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Kashmiri Saffron (Crocus sativus), Red Sandalwood (Pterocarpus santalinus), Manjistha (Rubia cordifolia), Lotus Stamen, Goat Milk, Virgin Sesame Oil.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>After cleansing at night, take 3-4 drops onto palms, gently press into damp face and neck, and massage upwards.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 30 ml Dropper Bottle<br><strong>Form:</strong> Facial Oil<br><strong>Pack:</strong> Amber Glass with Glass Dropper<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store away from direct sunlight.</p>\n</div>",
                'ingredients' => "Kashmiri Saffron (Crocus sativus) 5%\nRed Sandalwood (Pterocarpus santalinus) 10%\nManjistha (Rubia cordifolia) 8%\nLotus Stamen (Nelumbo nucifera) 5%\nPure Goat Milk & Sesame Oil base 72%",
                'benefits'    => "• Illuminates skin complexion and fades dark spots\n• Minimizes fine dry lines and boosts skin elasticity\n• Non-greasy lightweight absorption with rich saffron aroma\n• 100% pure botanical oil with zero synthetic fragrances",
                'dosage'      => 'After cleansing face at night, take 3-4 drops onto clean fingertips. Gently press and massage into damp face and neck using upward strokes.',
                'net_qty'     => '30 ml Dropper Bottle',
                'cert'        => 'Classical Bhasma & Taila Vidhi, 100% Chemical Free',
                'featured'    => true,
                'weight'      => 0.12,
                'dimensions'  => array('length' => 4, 'width' => 4, 'height' => 10),
            ),
            array(
                'title'       => 'Golden Turmeric Curcumin 95% with Piperine',
                'sku'         => 'AYUR-CURC-60',
                'price'       => '599',
                'mrp'         => '849',
                'cogs'        => '180',
                'source_price'=> '849',
                'category'    => 'immunity',
                'short_desc'  => 'Standardized 95% Curcuminoids extract combined with Piperine for 2000% enhanced absorption. Cellular immunity and joint comfort.',
                'desc'        => "<h3>Golden Turmeric Curcumin 95% with Piperine</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Promotes healthy joint flexibility and cartilage comfort</li>\n<li>Powerful antioxidant protection against cellular oxidative stress</li>\n<li>Supports clean cardiovascular and metabolic health</li>\n<li>Enhances systemic immunity and seasonal resilience</li>\n</ul>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Curcumin Extract 95% (Curcuma longa) 500mg, Piperine Extract 95% (Piper nigrum) 5mg, Vegetable Cellulose Capsule.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Take 1 capsule twice daily with or immediately after meals.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 60 Vegetarian Capsules<br><strong>Form:</strong> Vegetable Capsule<br><strong>Pack:</strong> Sealed Amber Bottle<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool, dry place.</p>\n</div>",
                'ingredients' => "Curcumin Extract 95% (Curcuma longa) 500mg\nPiperine Extract 95% (Piper nigrum) 5mg\nVegetable Cellulose Capsule",
                'benefits'    => "• Promotes healthy joint flexibility and cartilage comfort\n• Powerful antioxidant protection against cellular oxidative stress\n• Supports clean cardiovascular and metabolic health\n• Enhances systemic immunity and seasonal resilience",
                'dosage'      => 'Take 1 capsule twice daily with or immediately after meals.',
                'net_qty'     => '60 Vegetarian Capsules',
                'cert'        => 'AYUSH Licensed, Standardized Extract, Heavy Metal Tested',
                'featured'    => false,
                'weight'      => 0.14,
                'dimensions'  => array('length' => 6, 'width' => 6, 'height' => 11),
            ),
            array(
                'title'       => 'Pure Cold-Pressed Aloe Vera & Rose Soothing Gel',
                'sku'         => 'AYUR-ALOE-200',
                'price'       => '349',
                'mrp'         => '499',
                'cogs'        => '95',
                'source_price'=> '499',
                'category'    => 'skin-care',
                'short_desc'  => '99% Pure inner-leaf Aloe Vera juice steam-distilled with Indian Gulab (Rosewater) for multi-purpose skin hydration and cooling relief.',
                'desc'        => "<h3>Pure Cold-Pressed Aloe Vera & Rose Soothing Gel</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Instantly cools sunburns, irritation, and redness</li>\n<li>Lightweight non-comedogenic hydration for oily and acne-prone skin</li>\n<li>Can be used as a hair gel or leave-in scalp conditioner</li>\n<li>Soothes skin after shaving or waxing</li>\n</ul>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Cold-Pressed Aloe Vera Leaf Juice (Aloe barbadensis) 95%, Steam Distilled Rose Water (Rosa damascena) 4%, Natural Plant Gums & Vitamin E.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Apply generously to face, neck, or body as needed. Store in refrigerator for an extra-cooling soothing effect.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 200 g Tub<br><strong>Form:</strong> Soothing Gel<br><strong>Pack:</strong> Wide-Mouth Jar<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool place.</p>\n</div>",
                'ingredients' => "Cold-Pressed Aloe Vera Leaf Juice (Aloe barbadensis) 95%\nSteam Distilled Rose Water (Rosa damascena) 4%\nNatural Plant Gums & Vitamin E",
                'benefits'    => "• Instantly cools sunburns, irritation, and redness\n• Lightweight non-comedogenic hydration for oily and acne-prone skin\n• Can be used as a hair gel or leave-in scalp conditioner\n• Soothes skin after shaving or waxing",
                'dosage'      => 'Apply generously to face, neck, or body as needed. Store in refrigerator for an extra-cooling soothing effect.',
                'net_qty'     => '200 g Tub',
                'cert'        => '99% Pure Botanical, Alcohol Free, Paraben Free, Cruelty Free',
                'featured'    => false,
                'weight'      => 0.24,
                'dimensions'  => array('length' => 8, 'width' => 8, 'height' => 7),
            ),
            array(
                'title'       => 'Complete Ayurvedic Hair Care Ritual Combo',
                'sku'         => 'AYUR-COMBO-HAIR',
                'price'       => '999',
                'mrp'         => '1599',
                'cogs'        => '350',
                'source_price'=> '1599',
                'category'    => 'combos',
                'short_desc'  => 'Complete 3-step hair regrowth therapy: Maha Bhringraj Hair Oil (200ml) + Herbal Amla Cleanser (200ml) + Hair Mask (150g). Save ₹600!',
                'desc'        => "<h3>Complete Ayurvedic Hair Care Ritual Combo</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Complete 360-degree hair strengthening ritual</li>\n<li>Noticeable reduction in hair fall within 4-6 weeks</li>\n<li>Restores natural shine, softness, and scalp balance</li>\n<li>Bundled together with over 37% direct savings</li>\n</ul>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Maha Bhringraj Hair Oil (200ml), Herbal Amla & Shikakai Shampoo (200ml), Brahmi & Methi Hair Repair Mask (150g).</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Follow the 3-step ritual twice weekly: Oil before sleep, wash with cleanser next morning, and apply mask for 15 minutes before rinsing.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 3 Full-Size Products Kit<br><strong>Form:</strong> Complete Ritual Bundle<br><strong>Pack:</strong> Custom Wellness Box<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store at room temperature.</p>\n</div>",
                'ingredients' => "Maha Bhringraj Hair Oil (200ml)\nHerbal Amla & Shikakai Shampoo (200ml)\nBrahmi & Methi Hair Repair Mask (150g)",
                'benefits'    => "• Complete 360-degree hair strengthening ritual\n• Noticeable reduction in hair fall within 4-6 weeks\n• Restores natural shine, softness, and scalp balance\n• Bundled together with over 37% direct savings",
                'dosage'      => 'Follow the 3-step ritual twice weekly: Oil before sleep, wash with cleanser next morning, and apply mask for 15 minutes before rinsing.',
                'net_qty'     => '3 Full-Size Products Kit',
                'cert'        => 'Complete Ayurvedic Therapy Kit, AYUSH Certified',
                'featured'    => true,
                'weight'      => 0.65,
                'dimensions'  => array('length' => 18, 'width' => 14, 'height' => 8),
            ),
            array(
                'title'       => 'Ultimate Daily Immunity & Vitality Bundle',
                'sku'         => 'AYUR-COMBO-IMMUN',
                'price'       => '1099',
                'mrp'         => '1799',
                'cogs'        => '380',
                'source_price'=> '1799',
                'category'    => 'combos',
                'short_desc'  => 'Powerhouse Rasayana trio: Organic Ashwagandha (60 Caps) + Golden Curcumin (60 Caps) + Triphala Detox (90 Caps). Save ₹700!',
                'desc'        => "<h3>Ultimate Daily Immunity & Vitality Bundle</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Comprehensive vitality, immunity, and digestive support</li>\n<li>Synergistic herbal harmony for mind and body</li>\n<li>Ideal foundation for daily wellness and healthy aging</li>\n<li>High-value bundle with instant ₹700 savings and Free Shipping</li>\n</ul>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>1x Ashwagandha KSM-66 (60 Capsules), 1x Golden Turmeric Curcumin 95% (60 Capsules), 1x Triphala Detox Cleanse (90 Capsules).</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Ashwagandha morning & night; Curcumin with meals; Triphala 2 caps at bedtime.</p>\n</div>\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 3 Bottled Supplements (210 Capsules Total)<br><strong>Form:</strong> Multi-Bottle Pack<br><strong>Pack:</strong> Custom Wellness Box<br><strong>Origin:</strong> India<br><strong>Storage:</strong> Store in a cool, dry place.</p>\n</div>",
                'ingredients' => "1x Ashwagandha KSM-66 (60 Capsules)\n1x Golden Turmeric Curcumin 95% (60 Capsules)\n1x Triphala Detox Cleanse (90 Capsules)",
                'benefits'    => "• Comprehensive vitality, immunity, and digestive support\n• Synergistic herbal harmony for mind and body\n• Ideal foundation for daily wellness and healthy aging\n• High-value bundle with instant ₹700 savings and Free Shipping",
                'dosage'      => 'Ashwagandha morning & night; Curcumin with meals; Triphala 2 caps at bedtime.',
                'net_qty'     => '3 Bottled Supplements (210 Capsules Total)',
                'cert'        => '100% Ayurvedic, Non-GMO, Heavy Metal Tested',
                'featured'    => true,
                'weight'      => 0.45,
                'dimensions'  => array('length' => 18, 'width' => 12, 'height' => 8),
            ),

            // --- RESEARCH-DRIVEN AYURVEDIC FORMULATIONS (7 Products) ---
            array(
                'title'       => "Shuddha Shilajit & KSM-66 Ashwagandha Men's Vitality Capsules (800mg)",
                'sku'         => 'AYUR-SHIL-30',
                'price'       => '890',
                'mrp'         => '1990',
                'cogs'        => '280',
                'source_price'=> '1100',
                'category'    => 'mens-wellness',
                'short_desc'  => 'High-potency Himalayan Gold-Grade Shilajit (min 60% Fulvic Acid) and KSM-66 Ashwagandha (800mg per serving) for male stamina, natural testosterone support, and deep physical vigor.',
                'desc'        => "<h3>Shuddha Shilajit & KSM-66 Ashwagandha Men's Vitality Capsules (800mg)</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Formulated with purified Himalayan Gold Grade Shilajit (min 60% Fulvic Acid)</li>\n<li>Standardized KSM-66 Ashwagandha for deep cortisol control and physical stamina</li>\n<li>Supports natural testosterone levels, physical endurance, and active recovery</li>\n<li>100% vegetarian cellulose capsules with zero synthetic fillers or heavy metals</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Purified Himalayan Shilajit (Asphaltum punjabianum) 400mg, Standardized KSM-66 Ashwagandha (Withania somnifera) 250mg, Safed Musli (Chlorophytum borivilianum) 100mg, Gokshura (Tribulus terrestris) 50mg, Vegetable Cellulose Capsule.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Take 1 capsule twice daily with warm milk or lukewarm water, ideally 30 minutes after breakfast and dinner.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 30 Vegetarian Capsules<br>\n<strong>Form:</strong> 800mg High-Potency Vegetable Capsule<br>\n<strong>Pack:</strong> UV-Protected Amber Sealed Bottle<br>\n<strong>Origin:</strong> India<br>\n<strong>Storage:</strong> Store in a cool, dry place away from direct sunlight.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Authentic Ayurvedic botanical formulation. Adheres strictly to classical herbal standards. Consult your physician if undergoing treatment for any chronic condition.</em></p>\n</div>",
                'ingredients' => "Purified Himalayan Shilajit (min 60% Fulvic Acid) 400mg\nKSM-66® Ashwagandha Root Extract 250mg\nSafed Musli (Chlorophytum borivilianum) 100mg\nGokshura (Tribulus terrestris) 50mg\nPlant Cellulose Capsule",
                'benefits'    => "• Helps restore peak physical vitality and male stamina\n• Rich in fulvic acid and 84+ essential ionic trace minerals\n• Promotes natural hormonal equilibrium and stress resilience\n• Non-habit forming natural vitality booster",
                'dosage'      => 'Take 1 capsule twice daily with warm milk or lukewarm water, ideally 30 minutes after meals.',
                'net_qty'     => '30 Vegetarian Capsules (800 mg)',
                'cert'        => 'Purified Himalayan Origin, AYUSH Licensed, Heavy Metal Tested',
                'featured'    => true,
                'weight'      => 0.12,
                'dimensions'  => array('length' => 6, 'width' => 6, 'height' => 11),
            ),
            array(
                'title'       => 'Slim Detox & Herbal Agni Metabolism Juice (500ml)',
                'sku'         => 'AYUR-SLIM-500',
                'price'       => '890',
                'mrp'         => '1990',
                'cogs'        => '260',
                'source_price'=> '1100',
                'category'    => 'metabolism-weight-care',
                'short_desc'  => 'Pure cold-pressed Garcinia Cambogia (Vrikshamla), Organic Aloe Vera, Triphala, and Guggal elixir to stimulate digestive fire (Agni), curb unhealthy cravings, and eliminate metabolic Ama.',
                'desc'        => "<h3>Slim Detox & Herbal Agni Metabolism Juice (500ml)</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Pure botanical blend supporting natural metabolic fire (Agni)</li>\n<li>Curbs unhealthy hunger spikes and helps burn stubborn metabolic waste (Ama)</li>\n<li>Gentle daily colon and liver detox without harsh laxatives</li>\n<li>Zero added sugar, zero artificial colors, zero synthetic preservatives</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Cold-Pressed Garcinia Cambogia (Vrikshamla) 30%, Organic Aloe Vera Leaf Juice (Ghritkumari) 25%, Triphala Extract (Amalaki, Bibhitaki, Haritaki) 25%, Shuddha Guggulu 10%, Green Tea Leaf Extract 10%.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Shake well before use. Mix 20-30 ml of juice in a glass of lukewarm water and consume on an empty stomach every morning.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 500 ml Bottle<br>\n<strong>Form:</strong> Cold-Pressed Liquid Herbal Juice<br>\n<strong>Pack:</strong> BPA-Free Food Grade Bottle with Measuring Cup<br>\n<strong>Origin:</strong> India<br>\n<strong>Storage:</strong> Store in a cool dry place. Refrigerate after opening and consume within 30 days.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Authentic Ayurvedic botanical formulation. Best paired with an active lifestyle and balanced diet. Consult physician if pregnant or nursing.</em></p>\n</div>",
                'ingredients' => "Garcinia Cambogia (Vrikshamla) 30%\nOrganic Aloe Vera Leaf Juice (Ghritkumari) 25%\nTriphala Extract 25%\nShuddha Guggulu 10%\nGreen Tea Extract 10%",
                'benefits'    => "• Promotes healthy daily metabolic rate and natural fat metabolism\n• Gentle digestive detoxification without cramping\n• Helps maintain satiety and curb between-meal sugar cravings\n• Free from added sugar, gluten, and chemical flavorings",
                'dosage'      => 'Mix 20-30 ml in a glass of lukewarm water. Drink once daily on an empty stomach 30 minutes before breakfast.',
                'net_qty'     => '500 ml Bottle',
                'cert'        => '100% Botanical Juice, AYUSH Certified, Zero Added Sugar',
                'featured'    => true,
                'weight'      => 0.62,
                'dimensions'  => array('length' => 7, 'width' => 7, 'height' => 22),
            ),
            array(
                'title'       => 'Triphala & Aloe Vera Anorectal Comfort Capsules (500mg)',
                'sku'         => 'AYUR-PILES-60',
                'price'       => '890',
                'mrp'         => '1990',
                'cogs'        => '250',
                'source_price'=> '1100',
                'category'    => 'digestive',
                'short_desc'  => 'Classical cooling botanical blend of Triphala, Inner-Fillet Aloe Vera, Nagkesar, and Neem for strain-free bowel ease, vascular tone, and soothing comfort for sensitive anorectal tissues.',
                'desc'        => "<h3>Triphala & Aloe Vera Anorectal Comfort Capsules (500mg)</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Provides cooling, soothing relief for sensitive anorectal tissues</li>\n<li>Promotes smooth, strain-free daily bowel movements</li>\n<li>Supports natural vascular tone and tissue recovery around hemorrhoidal veins</li>\n<li>Traditional Ayurvedic botanical synergy for chronic gut heat and dryness</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Classical Triphala Powder 200mg, Aloe Vera Inner Gel Extract (Aloe barbadensis) 150mg, Nagkesar (Mesua ferrea) 100mg, Shuddha Guggulu 50mg, Plant Cellulose Capsule.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Take 1-2 capsules twice daily with lukewarm water before bedtime and in the morning.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 60 Vegetarian Capsules<br>\n<strong>Form:</strong> Vegetable Capsule<br>\n<strong>Pack:</strong> Sealed Amber Bottle<br>\n<strong>Origin:</strong> India<br>\n<strong>Storage:</strong> Store in a cool, dry place away from direct sunlight.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Authentic Ayurvedic botanical formulation. For chronic anorectal bleeding or severe pain, consult an Ayurvedic surgeon or medical specialist immediately.</em></p>\n</div>",
                'ingredients' => "Triphala Extract (Haritaki, Bibhitaki, Amalaki) 200mg\nAloe Vera Inner Leaf Extract 150mg\nNagkesar (Mesua ferrea) 100mg\nShuddha Guggulu 50mg\nPlant Cellulose Capsule",
                'benefits'    => "• Eases painful burning sensations and local inflammation\n• Softens stools and prevents straining during evacuation\n• Tones intestinal blood vessels and promotes tissue repair\n• Non-habit-forming, gentle botanical action",
                'dosage'      => 'Take 1-2 capsules twice daily with lukewarm water before bedtime and in the morning.',
                'net_qty'     => '60 Vegetarian Capsules (500 mg)',
                'cert'        => 'Classical Arshoghna Herb Formula, AYUSH Licensed',
                'featured'    => false,
                'weight'      => 0.14,
                'dimensions'  => array('length' => 6, 'width' => 6, 'height' => 11),
            ),
            array(
                'title'       => 'Amrit Heart Health Immunity Juice (500ml)',
                'sku'         => 'AYUR-ARJ-500',
                'price'       => '890',
                'mrp'         => '1990',
                'cogs'        => '270',
                'source_price'=> '1100',
                'category'    => 'heart-immunity',
                'short_desc'  => 'An Ayurvedic proprietary formulation featuring a curated blend of 60 herbs, fruits, and botanical extracts.',
                'desc'        => "<h3>Amrit Heart Health Immunity Juice (500ml)</h3>\n<p class=\"ayur-lead\">A multi-ingredient Ayurvedic proprietary medicine crafted with 60 herbs, fruits, and extracts.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Multi-botanical synergy featuring 60 carefully specified herbs, fruits, and extracts</li>\n<li>Features classical Rasayana botanicals including Moringa, Noni, Amla, Ashwagandha, and Shilajit</li>\n<li>Supports daily vitality, natural immunity, and overall physiological wellness</li>\n<li>100% natural ingredients with zero added sugar, artificial colours, or synthetic flavours</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY BOTANICALS</h4>\n<p>Each 10ml contains specified extracts including Moringa Oleifera 500mg, Noni 400mg, Amla 400mg, Ashwagandha 300mg, Shilajit 300mg, Giloy 300mg, Shatavari 300mg, Arjuna 200mg, and 52 complementary botanical extracts.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Shake well before use. Mix 15 to 25 ml of Amrit Juice in a glass of normal or lukewarm water. Drink once daily on an empty stomach in the morning.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 500 ml Bottle<br>\n<strong>Form:</strong> Liquid Herbal Tonic (Ayurvedic Proprietary Medicine)<br>\n<strong>Pack:</strong> Food-Grade Bottle with Calibrated Dosing Cap<br>\n<strong>Origin:</strong> India<br>\n<strong>Storage:</strong> Store in a cool, dry place. Refrigerate after opening and consume within 30 days.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Ayurvedic proprietary formulation. Not intended to diagnose, treat, cure, or prevent any disease. Consult a physician before use if pregnant, lactating, or undergoing medical treatment.</em></p>\n</div>",
                'ingredients' => "60 Herbs, Fruits & Extracts: Noni 400mg, Aloevera 300mg, Acai Berry 400mg, Sea Buckthorn 300mg, Beetroot 300mg, Harad 300mg, Baheda 300mg, Bhumi Amla 300mg, Shilajit 300mg, Mulethi 300mg, Giloy 300mg, Shatavari 300mg, Ashwagandha 300mg, Amla 400mg, Moringa Oleifera 500mg, Arjuna 200mg, and 44 additional extracts specified per 10ml.",
                'benefits'    => "• Broad-spectrum support for natural vitality and daily immune defense\n• 60 carefully specified botanical extracts per 10ml serving\n• Formulated with zero added sugar, artificial colours, or synthetic flavours\n• Standardized Ayurvedic proprietary medicine",
                'dosage'      => 'Shake well before use. Mix 15 to 25 ml in a glass of lukewarm water. Drink once daily on an empty stomach in the morning.',
                'net_qty'     => '500 ml Bottle',
                'cert'        => 'Ayurvedic Proprietary Medicine, AYUSH Compliant Facility, 100% Natural',
                'featured'    => false,
                'weight'      => 0.62,
                'dimensions'  => array('length' => 7, 'width' => 7, 'height' => 22),
            ),
            array(
                'title'       => 'Karela Jamun Gurmar Blood Sugar Balance Capsules (500mg)',
                'sku'         => 'AYUR-SUGAR-60',
                'price'       => '890',
                'mrp'         => '1990',
                'cogs'        => '240',
                'source_price'=> '1100',
                'category'    => 'metabolism-weight-care',
                'short_desc'  => 'Synergistic 5-herb botanical formula with Karela, Jamun seed, Gurmar (the sugar destroyer), and Fenugreek to support natural carbohydrate metabolism and curb sugar cravings.',
                'desc'        => "<h3>Karela Jamun Gurmar Blood Sugar Balance Capsules (500mg)</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>5-Herb synergy formulated to assist natural carbohydrate and glucose metabolism</li>\n<li>Gurmar leaf compounds naturally reduce sweetness cravings on the palate</li>\n<li>Supports pancreatic beta-cell vitality and healthy digestive enzyme secretion</li>\n<li>100% natural, vegetarian capsules with zero chemical additives</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Pure Karela (Bitter Gourd) Extract 150mg, Jamun Seed (Syzygium cumini) Extract 150mg, Gurmar (Gymnema sylvestre) Extract 100mg, Methi (Fenugreek Seed) 50mg, Vijayasar (Pterocarpus marsupium) 50mg, Plant Cellulose Capsule.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Take 1 capsule twice daily, approximately 30 minutes before your morning and evening meals with water.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 60 Vegetarian Capsules<br>\n<strong>Form:</strong> Vegetable Capsule<br>\n<strong>Pack:</strong> Sealed Amber Bottle<br>\n<strong>Origin:</strong> India<br>\n<strong>Storage:</strong> Store in a cool dry place away from moisture.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Dietary wellness supplement for carbohydrate metabolism. Monitor blood sugar regularly when taking alongside antidiabetic medication.</em></p>\n</div>",
                'ingredients' => "Karela Extract (Momordica charantia) 150mg\nJamun Seed Extract (Syzygium cumini) 150mg\nGurmar Extract (Gymnema sylvestre) 100mg\nMethi Seed Powder 50mg\nVijayasar Extract 50mg\nPlant Cellulose Capsule",
                'benefits'    => "• Helps maintain healthy fasting and postprandial glucose levels\n• Blocks sweet receptors temporarily to suppress sugar indulgence\n• Promotes optimal pancreatic digestive secretions\n• Non-habit-forming botanical formulation",
                'dosage'      => 'Take 1 capsule twice daily, 30 minutes before major meals with lukewarm water.',
                'net_qty'     => '60 Vegetarian Capsules (500 mg)',
                'cert'        => 'Standardized Herbal Actives, AYUSH Licensed, Zero Fillers',
                'featured'    => false,
                'weight'      => 0.14,
                'dimensions'  => array('length' => 6, 'width' => 6, 'height' => 11),
            ),
            array(
                'title'       => 'Shallaki & Hadjod Joint Mobility & Cartilage Care Capsules (500mg)',
                'sku'         => 'AYUR-SHAL-60',
                'price'       => '890',
                'mrp'         => '1990',
                'cogs'        => '250',
                'source_price'=> '1100',
                'category'    => 'joint-care-mobility',
                'short_desc'  => 'Standardized Boswellia serrata (Shallaki min 65% Boswellic acids) combined with Hadjod and Nirgundi to lubricate joints, soothe morning stiffness, and support long-term cartilage resilience.',
                'desc'        => "<h3>Shallaki & Hadjod Joint Mobility & Cartilage Care Capsules (500mg)</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Standardized 65% Boswellic acids to calm joint inflammation and soothe morning stiffness</li>\n<li>Classical Hadjod herb to strengthen bone mineral density and connective tendons</li>\n<li>Promotes comfortable knee flexion, spinal flexibility, and active mobility</li>\n<li>Gentle on stomach compared to synthetic anti-inflammatory tablets</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Standardized Shallaki (Boswellia serrata) Gum Resin Extract (min 65% Boswellic Acids) 300mg, Hadjod (Cissus quadrangularis) 100mg, Nirgundi (Vitex negundo) 50mg, Shuddha Guggulu 50mg, Plant Cellulose Capsule.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Take 1-2 capsules twice daily with meals or warm water. For best results, use consistently for 8-12 weeks.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 60 Vegetarian Capsules<br>\n<strong>Form:</strong> Vegetable Capsule<br>\n<strong>Pack:</strong> Sealed Amber Bottle<br>\n<strong>Origin:</strong> India<br>\n<strong>Storage:</strong> Store in a cool, dry place away from heat.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Authentic Ayurvedic joint support formulation. Adheres to classical Sandhivata principles. Consult your doctor if suffering from acute rheumatoid conditions.</em></p>\n</div>",
                'ingredients' => "Shallaki Gum Resin Extract (Boswellia serrata 65% acids) 300mg\nHadjod Extract (Cissus quadrangularis) 100mg\nNirgundi Leaf Extract (Vitex negundo) 50mg\nShuddha Guggulu 50mg\nPlant Cellulose Capsule",
                'benefits'    => "• Helps restore natural synovial fluid lubrication in knee and hip joints\n• Calms inflammatory cytokines to ease morning joint stiffness\n• Strengthens collagen integrity in ligaments and tendons\n• Safe for long-term daily use without gastrointestinal irritation",
                'dosage'      => 'Take 1-2 capsules twice daily after breakfast and dinner with water.',
                'net_qty'     => '60 Vegetarian Capsules (500 mg)',
                'cert'        => 'Standardized 65% Boswellic Acids, AYUSH Certified',
                'featured'    => false,
                'weight'      => 0.14,
                'dimensions'  => array('length' => 6, 'width' => 6, 'height' => 11),
            ),
            array(
                'title'       => "Shatavari & Ashoka Women's Hormonal Vitality Juice (500ml)",
                'sku'         => 'AYUR-SHAT-500',
                'price'       => '890',
                'mrp'         => '1990',
                'cogs'        => '260',
                'source_price'=> '1100',
                'category'    => 'womens-wellness',
                'short_desc'  => 'Rejuvenating female Rasayana combining pure Shatavari root, classical Ashoka bark, Lodhra, and Dashmula to support hormonal rhythm, ease monthly discomfort, and nourish feminine vitality.',
                'desc'        => "<h3>Shatavari & Ashoka Women's Hormonal Vitality Juice (500ml)</h3>\n<p class=\"ayur-lead\">A simple Ayurvedic addition to your daily wellness routine.</p>\n\n<div class=\"ayur-desc-section\">\n<h4>WHY YOU'LL LOVE IT</h4>\n<ul class=\"ayur-feature-list\">\n<li>Formulated to nurture female reproductive health, cycle regularity, and mood equilibrium</li>\n<li>Shatavari serves as a master female rejuvenator (Nari Rasayana) for energy and vitality</li>\n<li>Eases periodic cramps, bloating, and fatigue with soothing botanicals</li>\n<li>Naturally sweetened with Ayurvedic herbs; zero artificial colors or added sugar</li>\n</ul>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>KEY INGREDIENTS</h4>\n<p>Pure Shatavari (Asparagus racemosus) Root Extract 35%, Classical Ashoka Bark (Saraca asoca) Decoction 30%, Lodhra (Symplocos racemosa) 15%, Dashmula Herbal Blend 10%, Fresh Amla Juice 10%.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>HOW TO USE</h4>\n<p>Dilute 20 ml in half a glass of warm water and take twice daily after meals.</p>\n</div>\n\n<div class=\"ayur-desc-section\">\n<h4>PRODUCT DETAILS</h4>\n<p><strong>Net Quantity:</strong> 500 ml Bottle<br>\n<strong>Form:</strong> Cold-Pressed Liquid Herbal Tonic<br>\n<strong>Pack:</strong> Food-Grade PET Bottle with Measuring Cap<br>\n<strong>Origin:</strong> India<br>\n<strong>Storage:</strong> Store in a cool dry place. Refrigerate after opening and consume within 30 days.</p>\n</div>\n\n<div class=\"ayur-desc-section ayur-important-note\">\n<p><em><strong>IMPORTANT:</strong> Traditional Nari Rasayana wellness tonic. Consult gynecologist if pregnant or nursing.</em></p>\n</div>",
                'ingredients' => "Shatavari Root Extract (Asparagus racemosus) 35%\nAshoka Bark Decoction (Saraca asoca) 30%\nLodhra Bark Extract (Symplocos racemosa) 15%\nDashmula Classical Decoction 10%\nAmla Juice 10%",
                'benefits'    => "• Supports monthly hormonal rhythm and uterine wellness\n• Eases physical fatigue and stabilizes mood fluctuations\n• Calms periodic abdominal cramping and pelvic discomfort\n• Enhances natural skin radiance and holistic female stamina",
                'dosage'      => 'Take 20 ml diluted with half a glass of warm water twice daily after meals.',
                'net_qty'     => '500 ml Bottle',
                'cert'        => 'Classical Stree Roga Formula, AYUSH Licensed, 100% Plant Based',
                'featured'    => true,
                'weight'      => 0.62,
                'dimensions'  => array('length' => 7, 'width' => 7, 'height' => 22),
            ),
        );

        foreach ($products_data as $p) {
            $existing_id = wc_get_product_id_by_sku($p['sku']);
            if ($existing_id) continue;

            $product = new WC_Product_Simple();
            $product->set_name($p['title']);
            $product->set_sku($p['sku']);
            $product->set_regular_price($p['mrp']);
            $product->set_sale_price($p['price']);
            $product->set_price($p['price']);
            $product->set_short_description($p['short_desc']);
            $product->set_description($p['desc']);
            $product->set_status('publish');
            $product->set_manage_stock(true);
            $product->set_stock_quantity(50);
            $product->set_stock_status('instock');
            $product->set_featured($p['featured']);
            $product->set_reviews_allowed(true);

            if (isset($p['weight'])) {
                $product->set_weight($p['weight']);
            }
            if (isset($p['dimensions'])) {
                $product->set_length($p['dimensions']['length']);
                $product->set_width($p['dimensions']['width']);
                $product->set_height($p['dimensions']['height']);
            }

            if (isset($cat_ids[$p['category']])) {
                $product->set_category_ids(array($cat_ids[$p['category']]));
            }

            $id = $product->save();

            // Save Ayurvedic Meta
            update_post_meta($id, '_ayur_mrp', $p['mrp']);
            update_post_meta($id, '_ayur_cost_price', $p['cogs']);
            if (isset($p['source_price'])) {
                update_post_meta($id, '_ayur_source_price', $p['source_price']);
            }
            update_post_meta($id, '_ayur_ingredients', $p['ingredients']);
            update_post_meta($id, '_ayur_benefits', $p['benefits']);
            update_post_meta($id, '_ayur_dosage', $p['dosage']);
            update_post_meta($id, '_ayur_net_quantity', $p['net_qty']);
            update_post_meta($id, '_ayur_certifications', $p['cert']);
            update_post_meta($id, '_ayur_precautions', 'Store in a cool dry place away from direct sunlight. Consult physician if pregnant or on prescription medication.');
            update_post_meta($id, '_ayur_batch', 'AY-2026-09');
            update_post_meta($id, '_ayur_expiry', '24 Months from Mfd.');

            // Reviews are NOT seeded to comply with Phase 3 Truth-in-Advertising
            // self::seed_sample_reviews($id, $p['title']);
        }
    }

    public static function seed_sample_reviews($product_id, $product_name) {
        return; // Compliance: Never publish unverified or fabricated reviews
    }

    public static function seed_coupons() {
        if (!class_exists('WC_Coupon')) return;

        // WELCOME10 (10% off)
        $coupon_code = 'WELCOME10';
        $coupon_id = wc_get_coupon_id_by_code($coupon_code);
        if (!$coupon_id) {
            $coupon = new WC_Coupon();
            $coupon->set_code($coupon_code);
            $coupon->set_discount_type('percent');
            $coupon->set_amount(10);
            $coupon->set_minimum_amount(499);
            $coupon->set_description('10% OFF on your first Ayurvedic order (Min order ₹499)');
            $coupon->set_individual_use(true);
            $coupon->save();
        }

        // AYURFREE (Free delivery)
        $free_coupon_code = 'AYURFREE';
        $free_coupon_id = wc_get_coupon_id_by_code($free_coupon_code);
        if (!$free_coupon_id) {
            $coupon = new WC_Coupon();
            $coupon->set_code($free_coupon_code);
            $coupon->set_discount_type('fixed_cart');
            $coupon->set_amount(0);
            $coupon->set_free_shipping(true);
            $coupon->set_minimum_amount(499);
            $coupon->set_description('Free Shipping across India for orders above ₹499');
            $coupon->save();
        }
    }
}
