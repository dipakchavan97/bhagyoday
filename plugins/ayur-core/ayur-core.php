<?php
/**
 * Plugin Name: Ayur Core Engine
 * Plugin URI: https://lightgreen-heron-113083.hostingersite.com/
 * Description: Core eCommerce, Indian Localization, Meta CAPI, GA4, Shiprocket, Razorpay, and Ayurvedic Product Architecture.
 * Version: 1.0.0
 * Author: Antigravity Lead Architect
 * Text Domain: ayur-core
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AYUR_CORE_VERSION', '1.0.0');
define('AYUR_CORE_PATH', plugin_dir_path(__FILE__));
define('AYUR_CORE_URL', plugin_dir_url(__FILE__));

// Load Core Components
require_once AYUR_CORE_PATH . 'includes/class-ayur-config.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-fields.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-tracking.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-shiprocket.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-whatsapp.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-cart-checkout.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-profitability.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-seeder.php';

/**
 * Main Plugin Initialization
 */
class Ayur_Core {
    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    public function init() {
        Ayur_Config::init();
        Ayur_Fields::init();
        Ayur_Tracking::init();
        Ayur_Shiprocket::init();
        Ayur_WhatsApp::init();
        Ayur_Cart_Checkout::init();
        Ayur_Profitability::init();
        Ayur_Seeder::init();
    }

    public function enqueue_assets() {
        wp_enqueue_style('ayur-core-style', AYUR_CORE_URL . 'assets/css/ayur-core.css', array(), AYUR_CORE_VERSION);
        wp_enqueue_script('ayur-core-script', AYUR_CORE_URL . 'assets/js/ayur-core.js', array('jquery'), AYUR_CORE_VERSION, true);

        wp_localize_script('ayur-core-script', 'ayurCoreParams', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ayur_core_nonce'),
            'currency_symbol' => '₹',
            'free_shipping_threshold' => 999,
        ));
    }

    public function activate() {
        Ayur_Config::setup_defaults();
        Ayur_Seeder::seed_essential_pages();
        flush_rewrite_rules();
    }
}

Ayur_Core::instance();
