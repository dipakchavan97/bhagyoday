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
}
