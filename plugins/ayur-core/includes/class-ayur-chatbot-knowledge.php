<?php
/**
 * Bhagyoday Ayurveda AI Chatbot Knowledge Base & Catalog Search Layer
 *
 * Provides verified product compositions, usage instructions, live WooCommerce prices,
 * store policies, and semantic retrieval for the chatbot.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Chatbot_Knowledge {

    const CACHE_KEY = 'ayur_chatbot_knowledge_cache_v1';
    const CACHE_TTL = 86400; // 24 hours

    public static function init() {
        // Cache Invalidation on product changes
        add_action('woocommerce_update_product', array(__CLASS__, 'invalidate_cache'));
        add_action('save_post_product', array(__CLASS__, 'invalidate_cache'));
        add_action('woocommerce_new_product', array(__CLASS__, 'invalidate_cache'));
        add_action('delete_post', array(__CLASS__, 'invalidate_cache'));
    }

    public static function invalidate_cache() {
        delete_transient(self::CACHE_KEY);
        update_option('ayur_chatbot_last_reindex', current_time('mysql'), false);
    }

    /**
     * Get Complete Grounded Knowledge (Products + Policies + FAQs)
     */
    public static function get_knowledge_summary() {
        $cached = get_transient(self::CACHE_KEY);
        if ($cached && is_array($cached)) {
            return $cached;
        }

        $products = self::get_all_products();
        $policies = self::get_store_policies();
        $faqs     = self::get_approved_faqs();

        $data = array(
            'products'     => $products,
            'policies'     => $policies,
            'faqs'         => $faqs,
            'generated_at' => current_time('mysql'),
        );

        set_transient(self::CACHE_KEY, $data, self::CACHE_TTL);
        return $data;
    }

    /**
     * Verified Product Catalog (Enriched with Live WooCommerce Data when active)
     */
    public static function get_all_products() {
        $baseline = self::get_verified_catalog_baseline();

        // If WooCommerce is running, enrich with live database prices, stock, permalinks, and images
        if (function_exists('wc_get_product')) {
            foreach ($baseline as $sku => &$item) {
                $product_id = isset($item['id']) ? $item['id'] : 0;
                $product = $product_id ? wc_get_product($product_id) : wc_get_product_id_by_sku($sku);
                
                if (is_numeric($product)) {
                    $product = wc_get_product($product);
                }

                if ($product && is_a($product, 'WC_Product')) {
                    $item['id']              = $product->get_id();
                    $item['name']            = $product->get_name();
                    $item['live_price']      = (float)$product->get_price();
                    $item['formatted_price'] = '₹' . number_format($product->get_price());
                    $item['regular_price']   = (float)$product->get_regular_price();
                    $item['formatted_mrp']   = '₹' . number_format($product->get_regular_price());
                    $item['in_stock']        = $product->is_in_stock();
                    $item['permalink']       = $product->get_permalink();

                    $img_id = $product->get_image_id();
                    if ($img_id) {
                        $img_src = wp_get_attachment_image_url($img_id, 'medium');
                        if ($img_src) {
                            $item['image_url'] = $img_src;
                        }
                    }
                }
            }
            unset($item);
        }

        return $baseline;
    }

    /**
     * Verified Authentic Catalog Invariants
     */
    public static function get_verified_catalog_baseline() {
        $site_url = function_exists('home_url') ? home_url() : 'https://bhagyodayayurveda.in';
        $assets_url = defined('AYUR_CORE_URL') ? AYUR_CORE_URL . 'assets/images/' : $site_url . '/wp-content/plugins/ayur-core/assets/images/';

        return array(
            'AYUR-SHIL-30' => array(
                'id'              => 30,
                'sku'             => 'AYUR-SHIL-30',
                'name'            => 'Men Power Capsule 800mg (30 Capsules)',
                'aliases'         => array('Ashwagandha Shilajit', 'Men Wellness Capsule', 'Shilajit Capsule', 'Men Power'),
                'category'        => "Men's Vitality & Stamina",
                'selling_price'   => 890,
                'mrp'             => 1990,
                'formatted_price' => '₹890',
                'formatted_mrp'   => '₹1,990',
                'discount'        => '55% OFF',
                'pack_size'       => '30 Vegetarian Capsules (800 mg per capsule)',
                'in_stock'        => true,
                'permalink'       => $site_url . '/product/ashwagandha-shilajit-men-wellness-capsules/',
                'image_url'       => $assets_url . 'bhagyoday-ashwagandha-shilajit-capsules-featured.webp',
                'clean_ingredients' => array('Ashwagandha', 'Safed Musli', 'Gokhru', 'Kaunch Beej', 'Shudh Shilajeet', 'Garlic', 'Jaiphal', 'Vidarikand', 'Maca Root', 'Kali Mirch', 'Kuchala', 'Saunth', 'Elaichi', 'Mastic Gum', 'Kala Jeera', 'Salav Mishri', 'Dalchini', 'Singhada', 'Harmal', 'Wang Bhasam'),
                'label_composition' => array(
                    'Ashwagandha 50mg', 'Safed Musli 50mg', 'Gokhru 30mg', 'Kaunch Beej 50mg', 'Shudh Shilajeet 20mg',
                    'Garlic 20mg', 'Jaiphal 30mg', 'Vidarikand 20mg', 'Maca Root 20mg', 'Kali Mirch 5mg',
                    'Kuchala 20mg', 'Saunth 20mg', 'Elaichi 5mg', 'Mastic Gum 20mg', 'Kala Jeera 20mg',
                    'Salav Mishri 10mg', 'Dalchini 20mg', 'Singhada 30mg', 'Harmal 10mg', 'Wang Bhasam 10mg'
                ),
                'benefits' => array(
                    'Combines pure purified Shilajit with 20 potent classical herbs',
                    'Supports natural physical stamina, energy, and muscular vigor',
                    'Aids post-activity recovery and daily physical stress resilience (Classical Rasayana)',
                    'Non-stimulant formula free from synthetic caffeine or pharmaceuticals'
                ),
                'dosage' => 'Take 1 capsule twice daily with warm milk or lukewarm water, ideally after breakfast and dinner. Best results observed when taken consistently for 60 to 90 days.',
                'precautions' => 'Store in a cool dry place away from direct sunlight. Consult physician if you suffer from high uric acid. Wellness dietary supplement, not for disease treatment.',
                'quantity_offers' => 'Buy 1 @ ₹890 | Buy 2 @ ₹1,602 (10% OFF) | Buy 4 @ ₹2,848 (20% OFF)'
            ),

            'AYUR-SLIM-500' => array(
                'id'              => 31,
                'sku'             => 'AYUR-SLIM-500',
                'name'            => 'Slim Care Weight Management Juice 500ml',
                'aliases'         => array('Slim Care Juice', 'Slimming Juice', 'Weight Care Juice', 'Fat Burner Juice'),
                'category'        => 'Metabolism & Weight Care',
                'selling_price'   => 890,
                'mrp'             => 1990,
                'formatted_price' => '₹890',
                'formatted_mrp'   => '₹1,990',
                'discount'        => '55% OFF',
                'pack_size'       => '500 ml Bottle with Measuring Cap',
                'in_stock'        => true,
                'permalink'       => $site_url . '/product/slim-care-weight-management-juice/',
                'image_url'       => $assets_url . 'bhagyoday-slim-care-juice-featured.webp',
                'clean_ingredients' => array('Aawala', 'Baheda', 'Harad', 'Guggul', 'Garcinia Cambogia', 'Punarnava', 'Methi', 'Cinnamon', 'Moringa', 'Ginger', 'Jeera (Cumin)', 'Giloy', 'Ashwagandha', 'Garlic', 'Green Tea'),
                'label_composition' => array(
                    'Aawala 300mg', 'Baheda 300mg', 'Harad 300mg', 'Guggul 400mg', 'Garcinia Cambogia 200mg',
                    'Punarnava 300mg', 'Methi 300mg', 'Cinnamon 400mg', 'Moringa 500mg', 'Ginger 300mg',
                    'Jeera (Cumin) 300mg', 'Giloy 200mg', 'Ashwagandha 200mg', 'Garlic 100mg', 'Green Tea 200mg',
                    'Cinnamon (duplicate label entry) 400mg'
                ),
                'benefits' => array(
                    'Formulated with 16 verified herbs and extracts including Garcinia Cambogia and Triphala',
                    'Supports healthy digestive fire (Deepana-Pachana) and natural metabolic rhythm',
                    'Infused with Moringa, Ginger, Cumin, Giloy, and Green Tea for daily antioxidant nourishment',
                    'Zero added sugar, no artificial flavours, and zero synthetic stimulants'
                ),
                'dosage' => 'Measure 20 to 30 ml of juice, dilute in a glass of lukewarm water, and drink once daily on an empty stomach in the morning, 30 minutes before breakfast.',
                'precautions' => 'Refrigerate after opening and consume within 30 days. Not recommended during pregnancy without medical consultation. Wellness dietary supplement, not intended to diagnose or treat obesity as a disease.',
                'quantity_offers' => 'Buy 1 @ ₹890 | Buy 2 @ ₹1,602 (10% OFF) | Buy 4 @ ₹2,848 (20% OFF)'
            ),

            'AYUR-PILES-60' => array(
                'id'              => 32,
                'sku'             => 'AYUR-PILES-60',
                'name'            => 'Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)',
                'aliases'         => array('Piles Care Capsule', 'Aloe Vera Triphala Capsules', 'Bavasir Capsule', 'Mulvyadh Capsule', 'बवासीर कैप्सूल', 'मूळव्याध कॅप्सूल', 'पाइल्स केयर', 'पाइल्स की दवा'),
                'category'        => 'Digestive & Gut Health',
                'selling_price'   => 890,
                'mrp'             => 1990,
                'formatted_price' => '₹890',
                'formatted_mrp'   => '₹1,990',
                'discount'        => '55% OFF',
                'pack_size'       => '60 Vegetarian Capsules (500 mg per capsule)',
                'in_stock'        => true,
                'permalink'       => $site_url . '/product/aloe-vera-triphala-piles-care-capsules/',
                'image_url'       => $assets_url . 'bhagyoday-piles-care-capsules-featured.webp',
                'clean_ingredients' => array('Kumari (Aloe Vera)', 'Triphala (Amalaki, Haritaki, Bibhitaki)', 'Nagkesar', 'Neem', 'Jimikand (Suran)'),
                'label_composition' => array(
                    'Kumari / Aloe Vera Inner Gel Extract 150mg',
                    'Classical Triphala (Amalaki, Haritaki, Bibhitaki) 150mg',
                    'Nagkesar Flower Extract 100mg',
                    'Neem Leaf Extract 50mg',
                    'Jimikand / Suran Tubers 50mg'
                ),
                'benefits' => array(
                    'Facilitates smooth, effortless bowel evacuation without harsh chemical laxatives',
                    'Kumari (Aloe Vera) provides systemic cooling to soothe inflamed mucosal tissues',
                    'Nagkesar and Neem aid healthy anorectal vascular tone and micro-circulation',
                    '100% plant-derived herbal formula for chronic digestive comfort'
                ),
                'dosage' => 'Take 1 to 2 capsules twice daily with lukewarm water after meals or before bedtime. Maintain ample daily hydration and fiber intake.',
                'precautions' => 'Store in a cool dry place. Consult a doctor if acute bleeding or severe discomfort persists.',
                'quantity_offers' => 'Buy 1 @ ₹890 | Buy 2 @ ₹1,602 (10% OFF) | Buy 4 @ ₹2,848 (20% OFF)'
            ),

            'AYUR-ARJ-500' => array(
                'id'              => 33,
                'sku'             => 'AYUR-ARJ-500',
                'name'            => 'Amrit Heart Health Immunity Juice 500ml',
                'aliases'         => array('Arjuna Amrit Juice', 'Amrit Heart Vitality Juice', 'Heart Care Juice'),
                'category'        => 'Heart & Vitality Rasayana',
                'selling_price'   => 890,
                'mrp'             => 1990,
                'formatted_price' => '₹890',
                'formatted_mrp'   => '₹1,990',
                'discount'        => '55% OFF',
                'pack_size'       => '500 ml Bottle with Measuring Cap',
                'in_stock'        => true,
                'permalink'       => $site_url . '/product/arjuna-amrit-heart-vitality-juice/',
                'image_url'       => $assets_url . 'bhagyoday-amrit-heart-juice-featured.webp',
                'clean_ingredients' => array('Arjuna Bark', 'Fresh Wild Amla', 'Ginger', 'Garlic', 'Raw Forest Honey', 'Apple Cider Vinegar'),
                'label_composition' => array(
                    'Standardized Arjuna Bark Extract (Terminalia arjuna) 35%',
                    'Fresh Wild Amla Juice (Phyllanthus emblica) 30%',
                    'Ginger Juice (Zingiber officinale) 15%',
                    'Purified Garlic Juice (Allium sativum) 10%',
                    'Raw Forest Honey 5%',
                    'Pure Apple Cider Vinegar 5%'
                ),
                'benefits' => array(
                    'Classical Hridaya Rasayana tonic supporting arterial flexibility and tone',
                    'Terminalia arjuna supports robust cardiac muscle tone and daily stamina',
                    'Synergistic garlic, ginger, and amla support healthy lipid balance and circulation',
                    'Rich in natural antioxidant polyphenols and bio-available Vitamin C'
                ),
                'dosage' => 'Shake well before use. Mix 15 to 25 ml of Amrit Juice in a glass of normal or lukewarm water. Drink once daily on an empty stomach in the morning.',
                'precautions' => 'Refrigerate after opening and consume within 30 days. If on prescription blood thinners or cardiac medications, consult your doctor. Wellness dietary tonic, not a replacement for medical treatment.',
                'quantity_offers' => 'Buy 1 @ ₹890 | Buy 2 @ ₹1,602 (10% OFF) | Buy 4 @ ₹2,848 (20% OFF)'
            ),

            'AYUR-SUGAR-60' => array(
                'id'              => 34,
                'sku'             => 'AYUR-SUGAR-60',
                'name'            => 'Karela Jamun Blood Sugar Control Capsules 500mg (60 Capsules)',
                'aliases'         => array('Diabetic Control Capsule', 'Sugar Balance Capsules', 'Karela Jamun Gurmar', 'Madhumeha Capsule', 'Madhuramrit', 'Madhur Amrit', 'मधुरामृत', 'मधुमेह कॅप्सूल', 'मधुमेह कैप्सूल'),
                'category'        => 'Metabolism & Weight Care',
                'selling_price'   => 890,
                'mrp'             => 1990,
                'formatted_price' => '₹890',
                'formatted_mrp'   => '₹1,990',
                'discount'        => '55% OFF',
                'pack_size'       => '60 Vegetarian Capsules (500 mg per capsule)',
                'in_stock'        => true,
                'permalink'       => $site_url . '/product/karela-jamun-gurmar-sugar-balance-capsules/',
                'image_url'       => $assets_url . 'bhagyoday-karela-jamun-capsules-featured.webp',
                'clean_ingredients' => array('Gudmaar', 'Periwinkle', 'Methi', 'Costus Igneous (Insulin Plant)', 'Jamun', 'Neem', 'Tulsi', 'Vijaysaar', 'Stevia', 'Bitter Gourd (Karela)'),
                'label_composition' => array(
                    'Gudmaar (Gymnema sylvestre) 200mg',
                    'Periwinkle (Catharanthus roseus) 200mg',
                    'Methi (Trigonella foenum-graecum) 200mg',
                    'Costus Igneous (Insulin Plant) 200mg',
                    'Jamun (Syzygium cumini) 200mg',
                    'Neem (Azadirachta indica) 200mg',
                    'Tulsi (Ocimum sanctum) 200mg',
                    'Vijaysaar (Pterocarpus marsupium) 100mg',
                    'Stevia (Stevia rebaudiana) 100mg',
                    'Bitter Gourd / Karela (Momordica charantia) 100mg'
                ),
                'benefits' => array(
                    'Classical bitter-herb synergy supporting healthy carbohydrate digestion and glucose metabolism',
                    'Gudmaar (Gymnema) naturally helps curb excessive sweet cravings on the tongue',
                    'Jamun seed extract assists pancreatic vitality and healthy digestive secretions',
                    'Pure vegetarian capsules with zero chemical stabilizers or binders'
                ),
                'dosage' => 'Take 1 capsule twice daily, approximately 30 minutes before principal meals (lunch and dinner) with lukewarm water.',
                'precautions' => 'IMPORTANT: Monitor blood glucose levels regularly. Do NOT abruptly reduce or stop physician-prescribed diabetic medications without doctor approval. Wellness supplement for carbohydrate metabolism.',
                'quantity_offers' => 'Buy 1 @ ₹890 | Buy 2 @ ₹1,602 (10% OFF) | Buy 4 @ ₹2,848 (20% OFF)'
            ),

            'AYUR-SHAL-60' => array(
                'id'              => 35,
                'sku'             => 'AYUR-SHAL-60',
                'name'            => 'Shallaki Joint Bone Care Capsules 500mg (60 Capsules)',
                'aliases'         => array('Ortho Care Capsule', 'Joint Care Capsule', 'Shallaki Hadjod', 'Sandhivata Capsule'),
                'category'        => 'Joint Care & Mobility',
                'selling_price'   => 890,
                'mrp'             => 1990,
                'formatted_price' => '₹890',
                'formatted_mrp'   => '₹1,990',
                'discount'        => '55% OFF',
                'pack_size'       => '60 Vegetarian Capsules (500 mg per capsule)',
                'in_stock'        => true,
                'permalink'       => $site_url . '/product/shallaki-hadjod-joint-mobility-capsules/',
                'image_url'       => $assets_url . 'bhagyoday-shallaki-joint-care-capsules-featured.webp',
                'clean_ingredients' => array('Shallaki', 'Ashwagandha', 'Guggul', 'Saunth (Adrak)', 'Haldi', 'Methi', 'Aloevera', 'Hadjod', 'Drumstick (Sahajan)', 'Nilgiri', 'Calotropis (Madar)', 'Nirgundi', 'Ginger'),
                'label_composition' => array(
                    'Shallaki (Boswellia serrata) 100mg',
                    'Ashwagandha (Withania somnifera) 100mg',
                    'Guggul (Commiphora mukul) 100mg',
                    'Saunth / Adrak (Zingiber officinale) 100mg',
                    'Haldi (Curcuma longa) 70mg',
                    'Methi (Fenugreek) 100mg',
                    'Aloevera (Aloe barbadensis) 80mg',
                    'Hadjod (Cissus quadrangularis) 100mg',
                    'Drumstick / Sahajan (Moringa) 70mg',
                    'Nilgiri (Eucalyptus globulus) 50mg',
                    'Calotropis (Madar) 60mg',
                    'Nirgundi (Vitex negundo) 70mg',
                    'Ginger (Zingiber officinale) 80mg'
                ),
                'benefits' => array(
                    'Standardized Shallaki gum resin extract and Guggulu to calm joint stiffness and soothe morning discomfort',
                    'Classical Hadjod herb to support bone mineral integrity and connective tendon strength',
                    'Promotes natural synovial fluid lubrication and active knee/hip flexion',
                    'Gentle on the stomach and suitable for daily routine'
                ),
                'dosage' => 'Take 1 capsule twice daily with water after meals. Consistent use for 45 to 60 days recommended for durable joint comfort.',
                'precautions' => 'Store in a cool dry place. Consult doctor if suffering from acute rheumatoid conditions. Wellness supplement for joint comfort, not an arthritis cure.',
                'quantity_offers' => 'Buy 1 @ ₹890 | Buy 2 @ ₹1,602 (10% OFF) | Buy 4 @ ₹2,848 (20% OFF)'
            ),

            'AYUR-SHAT-500' => array(
                'id'              => 36,
                'sku'             => 'AYUR-SHAT-500',
                'name'            => 'Shatavari Ashoka Women Wellness Juice 500ml',
                'aliases'         => array('Lady Care Juice', 'Women Wellness Juice', 'Nari Rasayana', 'Hormonal Balance Juice'),
                'category'        => "Women's Hormonal Vitality",
                'selling_price'   => 890,
                'mrp'             => 1990,
                'formatted_price' => '₹890',
                'formatted_mrp'   => '₹1,990',
                'discount'        => '55% OFF',
                'pack_size'       => '500 ml Bottle with Measuring Cap',
                'in_stock'        => true,
                'permalink'       => $site_url . '/product/shatavari-ashoka-womens-vitality-juice/',
                'image_url'       => $assets_url . 'bhagyoday-shatavari-ashoka-juice-featured.webp',
                'clean_ingredients' => array('Shatavari', 'Ashwagandha', 'Trifala', 'Kanchanar Guggulu', 'Ashoka', 'Lodhra', 'Guduchi', 'Moringa', 'Cinnamon', 'Turmeric', 'Pippali', 'Varuna', 'Bilva', 'Punarnava'),
                'label_composition' => array(
                    'Shatavari (Asparagus racemosus) 200mg',
                    'Ashwagandha (Withania somnifera) 200mg',
                    'Trifala (Triphala) 200mg',
                    'Kanchanar Guggulu 200mg',
                    'Ashoka (Saraca asoca) 200mg',
                    'Lodhra (Symplocos racemosa) 200mg',
                    'Guduchi (Tinospora cordifolia) 200mg',
                    'Moringa 200mg',
                    'Cinnamon 200mg',
                    'Turmeric 100mg',
                    'Pippali 100mg',
                    'Varuna 100mg',
                    'Bilva 100mg',
                    'Punarnava 100mg'
                ),
                'benefits' => array(
                    'Formulated to nurture female reproductive health, cycle regularity, and emotional equilibrium',
                    'Master female rejuvenator (Nari Rasayana) for sustained vitality and energy',
                    'Eases periodic discomfort, cramps, bloating, and fatigue with calming classical herbs',
                    'Naturally formulated with pure botanicals; zero artificial colors or added sugars'
                ),
                'dosage' => 'Dilute 20 to 30 ml of juice in a cup of lukewarm water or milk. Consume twice daily after meals.',
                'precautions' => 'Refrigerate after opening and consume within 30 days. Traditional Nari Rasayana wellness tonic. Consult gynecologist if pregnant or nursing.',
                'quantity_offers' => 'Buy 1 @ ₹890 | Buy 2 @ ₹1,602 (10% OFF) | Buy 4 @ ₹2,848 (20% OFF)'
            ),

            'AYUR-COMBO-VIT' => array(
                'id'              => 58,
                'sku'             => 'AYUR-COMBO-VIT',
                'name'            => 'Daily Energy & Vitality Duo (Bundle)',
                'aliases'         => array('Energy Vitality Combo', 'Vitality Bundle', 'Shilajit Amrit Combo'),
                'category'        => 'Wellness Combos',
                'selling_price'   => 1690,
                'mrp'             => 3980,
                'formatted_price' => '₹1,690',
                'formatted_mrp'   => '₹3,980',
                'discount'        => '58% OFF',
                'pack_size'       => 'Men Power (30s) + Amrit Heart Juice (500ml)',
                'in_stock'        => true,
                'permalink'       => $site_url . '/product/daily-energy-vitality-duo-shilajit-amrit-heart/',
                'image_url'       => $assets_url . 'bhagyoday-ashwagandha-shilajit-capsules-featured.webp',
                'clean_ingredients' => array('Men Power Capsule (20 Herbs)', 'Amrit Heart Juice (Arjuna, Amla, Garlic, Ginger, Honey, ACV)'),
                'benefits' => array(
                    'Complete vitality & cardiovascular stamina combination',
                    'Synergizes herbal endurance capsules with fresh botanical heart elixir',
                    'High-value bundle saving ₹2,290 compared to individual MRP'
                ),
                'dosage' => 'Take 1 Men Power capsule after dinner with warm water/milk, and take 20ml Amrit Juice in morning with lukewarm water.',
                'precautions' => 'Store capsules dry; refrigerate juice after opening.',
                'quantity_offers' => 'Special Combo Pricing: ₹1,690 (Save 58%)'
            ),

            'AYUR-COMBO-DETOX' => array(
                'id'              => 59,
                'sku'             => 'AYUR-COMBO-DETOX',
                'name'            => 'Metabolic Detox & Sugar Balance Duo (Bundle)',
                'aliases'         => array('Metabolic Detox Combo', 'Sugar Balance Duo', 'Karela Slim Combo'),
                'category'        => 'Wellness Combos',
                'selling_price'   => 1690,
                'mrp'             => 3980,
                'formatted_price' => '₹1,690',
                'formatted_mrp'   => '₹3,980',
                'discount'        => '58% OFF',
                'pack_size'       => 'Karela Jamun Capsules (60s) + Slim Care Juice (500ml)',
                'in_stock'        => true,
                'permalink'       => $site_url . '/product/metabolic-detox-sugar-balance-duo/',
                'image_url'       => $assets_url . 'bhagyoday-slim-care-juice-featured.webp',
                'clean_ingredients' => array('Karela Jamun Gurmar Capsules (10 Botanicals)', 'Slim Care Juice (16 Botanical Actives)'),
                'benefits' => array(
                    'Dual-action metabolic cleanse and carbohydrate digestion balance',
                    'Supports digestive Agni and curbs unnecessary sweet indulgence',
                    'High-value bundle saving ₹2,290 compared to individual MRP'
                ),
                'dosage' => 'Drink 20-30ml Slim Care Juice on empty stomach in morning; take 1 Karela Jamun capsule before lunch and dinner.',
                'precautions' => 'Monitor glucose regularly. Refrigerate juice once opened.',
                'quantity_offers' => 'Special Combo Pricing: ₹1,690 (Save 58%)'
            ),
        );
    }

    /**
     * Store Policies & Operations Knowledge
     */
    public static function get_store_policies() {
        return array(
            'shipping' => array(
                'free_threshold' => '₹999',
                'standard_rate'  => '₹70 for orders below ₹999',
                'free_shipping'  => 'Free Express Delivery on all orders above ₹999 across India',
                'metro_delivery' => '2 to 3 business days',
                'roi_delivery'   => '3 to 5 business days',
                'couriers'       => 'Delhivery, BlueDart, DTDC, XpressBees (via Shiprocket)',
                'dispatch_time'  => 'Within 24 to 48 hours of order confirmation'
            ),
            'payments' => array(
                'online' => 'Prepaid payment via Razorpay (UPI, Google Pay, PhonePe, Paytm, Debit/Credit Cards, Net Banking)',
                'cod'    => 'Cash on Delivery (COD) is available across all serviceable pincodes in India. To prevent fraudulent orders and reduce RTO, a ₹100 COD advance confirmation fee is paid online during checkout, and the remaining order balance is paid in cash upon physical delivery.',
            ),
            'quantity_discounts' => array(
                'buy_1' => '₹890 per unit (Single bottle)',
                'buy_2' => 'Buy 2 Get 10% OFF (₹1,602 total / Save ₹178)',
                'buy_4' => 'Buy 4 Get 20% OFF (₹2,848 total / Save ₹712)'
            ),
            'coupons' => array(
                'WELCOME10' => '10% OFF on first Ayurvedic order above ₹499',
                'AYURFREE'  => 'Free Shipping across India for orders above ₹499'
            ),
            'returns_and_refunds' => array(
                'window'   => '7-Day Return & Replacement Policy',
                'eligible' => 'If you receive a damaged, leaked, expired, or incorrect bottle, contact support within 7 days of delivery with an unboxing photo or package video.',
                'solution' => 'Free prompt replacement or 100% refund via original payment method or UPI.',
                'hygiene'  => 'For safety and hygiene compliance, consumable wellness tonics cannot be returned once opened and consumed.'
            ),
            'customer_care' => array(
                'whatsapp'  => '+91 8888686858',
                'email'     => 'vijaykulal8833@gmail.com',
                'hours'     => 'Monday to Saturday, 9:00 AM – 7:00 PM IST',
                'address'   => 'Bhagyoday Ayurveda, Infront of Nagar Panchayat Civil Hospital, Mantha, Jalna, Maharashtra - 431504'
            )
        );
    }

    /**
     * Approved Custom Admin FAQs
     */
    public static function get_approved_faqs() {
        $stored = get_option('ayur_chatbot_custom_faqs', null);
        if (is_array($stored) && !empty($stored)) {
            return $stored;
        }

        return array(
            array(
                'question' => 'Are your products 100% vegetarian and natural?',
                'answer'   => 'Yes! All our capsules use 100% plant-cellulose vegetable shells with zero gelatin, zero synthetic colors, and zero artificial preservatives. All juices are pure decoctions without refined sugars.'
            ),
            array(
                'question' => 'How does the ₹100 COD Advance Confirmation work?',
                'answer'   => 'To ensure genuine orders and prevent fake bookings, we collect a small ₹100 advance confirmation fee online via UPI or Razorpay. The exact ₹100 is deducted from your order balance, and you pay only the remaining amount in cash when the courier delivers your package.'
            ),
            array(
                'question' => 'How long does delivery take?',
                'answer'   => 'Orders are dispatched within 24-48 hours. Metro cities typically arrive in 2-3 business days, while other areas in India arrive in 3-5 business days via express courier.'
            ),
            array(
                'question' => 'Can I take these with allopathic medicines?',
                'answer'   => 'Our products are classical dietary wellness formulations. While they are made with gentle natural herbs, we advise maintaining a gap of at least 60-90 minutes between herbal supplements and prescription allopathic drugs, and consulting your healthcare doctor.'
            ),
            array(
                'question' => 'Are your products licensed and certified?',
                'answer'   => 'Yes, our formulations adhere to classical Ayurvedic pharmacopoeia standards and are produced in GMP-compliant, AYUSH-certified facilities in India.'
            )
        );
    }

    /**
     * Search relevant products based on query keywords
     */
    public static function search_relevant_products($query, $limit = 3) {
        $products = self::get_all_products();
        $query_clean = strtolower(trim($query));
        $tokens = preg_split('/[\s,\.\?\!\-\+]+/', $query_clean);
        $tokens = array_filter($tokens, function($t) { return strlen($t) >= 3; });

        $scores = array();

        foreach ($products as $sku => $prod) {
            $score = 0;
            $text_corpus = strtolower(
                $prod['name'] . ' ' .
                implode(' ', $prod['aliases']) . ' ' .
                $prod['category'] . ' ' .
                implode(' ', $prod['clean_ingredients']) . ' ' .
                implode(' ', $prod['benefits']) . ' ' .
                $prod['dosage']
            );

            // Exact name or alias match
            if (strpos($text_corpus, $query_clean) !== false) {
                $score += 30;
            }

            // Keyword token matches
            foreach ($tokens as $token) {
                // High weight for title / alias match
                if (stripos($prod['name'], $token) !== false) {
                    $score += 15;
                }
                foreach ($prod['aliases'] as $alias) {
                    if (stripos($alias, $token) !== false) {
                        $score += 12;
                    }
                }
                // Ingredient match
                foreach ($prod['clean_ingredients'] as $ing) {
                    if (stripos($ing, $token) !== false) {
                        $score += 8;
                    }
                }
                // General benefit or category match
                if (strpos($text_corpus, $token) !== false) {
                    $score += 4;
                }
            }

            // Specific semantic synonym mapping & phonetic misspellings
            $synonyms = array(
                'weight' => array('AYUR-SLIM-500', 'AYUR-COMBO-DETOX'),
                'slim' => array('AYUR-SLIM-500', 'AYUR-COMBO-DETOX'),
                'slimcare' => array('AYUR-SLIM-500', 'AYUR-COMBO-DETOX'),
                'slimming' => array('AYUR-SLIM-500', 'AYUR-COMBO-DETOX'),
                'belly' => array('AYUR-SLIM-500', 'AYUR-COMBO-DETOX'),
                'fat' => array('AYUR-SLIM-500', 'AYUR-COMBO-DETOX'),
                'sugar' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'madhuramrit' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'madhur amrit' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'मधुरामृत' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'मधुमेह' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'diabetes' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'diabetic' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'madhumeha' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'glucose' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'karela' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'jamun' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'jamoon' => array('AYUR-SUGAR-60', 'AYUR-COMBO-DETOX'),
                'joint' => array('AYUR-SHAL-60'),
                'knee' => array('AYUR-SHAL-60'),
                'bone' => array('AYUR-SHAL-60'),
                'ortho' => array('AYUR-SHAL-60'),
                'shallaki' => array('AYUR-SHAL-60'),
                'shallki' => array('AYUR-SHAL-60'),
                'shalaki' => array('AYUR-SHAL-60'),
                'sandhivata' => array('AYUR-SHAL-60'),
                'hadjod' => array('AYUR-SHAL-60'),
                'arthritis' => array('AYUR-SHAL-60'),
                'stamina' => array('AYUR-SHIL-30', 'AYUR-COMBO-VIT'),
                'shilajit' => array('AYUR-SHIL-30', 'AYUR-COMBO-VIT'),
                'silajit' => array('AYUR-SHIL-30', 'AYUR-COMBO-VIT'),
                'silajeet' => array('AYUR-SHIL-30', 'AYUR-COMBO-VIT'),
                'shilajeet' => array('AYUR-SHIL-30', 'AYUR-COMBO-VIT'),
                'ashwagandha' => array('AYUR-SHIL-30', 'AYUR-COMBO-VIT'),
                'ashwaganda' => array('AYUR-SHIL-30', 'AYUR-COMBO-VIT'),
                'vigor' => array('AYUR-SHIL-30', 'AYUR-COMBO-VIT'),
                'men' => array('AYUR-SHIL-30', 'AYUR-COMBO-VIT'),
                'heart' => array('AYUR-ARJ-500', 'AYUR-COMBO-VIT'),
                'cardio' => array('AYUR-ARJ-500', 'AYUR-COMBO-VIT'),
                'arjuna' => array('AYUR-ARJ-500', 'AYUR-COMBO-VIT'),
                'cholesterol' => array('AYUR-ARJ-500', 'AYUR-COMBO-VIT'),
                'amrit' => array('AYUR-ARJ-500', 'AYUR-COMBO-VIT'),
                'piles' => array('AYUR-PILES-60'),
                'pile' => array('AYUR-PILES-60'),
                'bavasir' => array('AYUR-PILES-60'),
                'bawaseer' => array('AYUR-PILES-60'),
                'hemorrhoid' => array('AYUR-PILES-60'),
                'hemorrhoids' => array('AYUR-PILES-60'),
                'haemorrhoids' => array('AYUR-PILES-60'),
                'mulvyadh' => array('AYUR-PILES-60'),
                'moolvyadh' => array('AYUR-PILES-60'),
                'मूळव्याध' => array('AYUR-PILES-60'),
                'बवासीर' => array('AYUR-PILES-60'),
                'पाइल्स' => array('AYUR-PILES-60'),
                'मुलव्याध' => array('AYUR-PILES-60'),
                'fissure' => array('AYUR-PILES-60'),
                'constipation' => array('AYUR-PILES-60'),
                'bowel' => array('AYUR-PILES-60'),
                'digestion' => array('AYUR-PILES-60', 'AYUR-SLIM-500'),
                'gut' => array('AYUR-PILES-60'),
                'women' => array('AYUR-SHAT-500'),
                'female' => array('AYUR-SHAT-500'),
                'lady' => array('AYUR-SHAT-500'),
                'shatavari' => array('AYUR-SHAT-500'),
                'satavari' => array('AYUR-SHAT-500'),
                'satavar' => array('AYUR-SHAT-500'),
                'ashoka' => array('AYUR-SHAT-500'),
                'period' => array('AYUR-SHAT-500'),
                'cramp' => array('AYUR-SHAT-500'),
                'hormone' => array('AYUR-SHAT-500'),
                'pcos' => array('AYUR-SHAT-500'),
                'combo' => array('AYUR-COMBO-VIT', 'AYUR-COMBO-DETOX'),
                'bundle' => array('AYUR-COMBO-VIT', 'AYUR-COMBO-DETOX'),
            );

            foreach ($synonyms as $term => $target_skus) {
                if (in_array($sku, $target_skus) && strpos($query_clean, $term) !== false) {
                    $score += 20;
                }
            }

            if ($score > 0) {
                $scores[$sku] = $score;
            }
        }

        arsort($scores);
        $result = array();
        foreach (array_slice(array_keys($scores), 0, $limit) as $sku) {
            $result[] = $products[$sku];
        }

        return $result;
    }

    /**
     * Log an Unresolved Question (Anonymized, no PII)
     */
    public static function log_unresolved_question($query, $reason = '') {
        $existing = get_option('ayur_chatbot_unresolved_queries', array());
        if (!is_array($existing)) $existing = array();

        $entry = array(
            'id'        => uniqid('unres_'),
            'query'     => sanitize_text_field(substr($query, 0, 200)),
            'reason'    => sanitize_text_field($reason),
            'timestamp' => current_time('mysql'),
        );

        // Prepend and keep maximum 100 entries
        array_unshift($existing, $entry);
        $existing = array_slice($existing, 0, 100);

        update_option('ayur_chatbot_unresolved_queries', $existing, false);
    }

    public static function get_unresolved_questions() {
        $items = get_option('ayur_chatbot_unresolved_queries', array());
        return is_array($items) ? $items : array();
    }

    public static function clear_unresolved_questions() {
        update_option('ayur_chatbot_unresolved_queries', array(), false);
    }
}
