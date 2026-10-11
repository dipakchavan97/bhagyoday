<?php
/**
 * Bhagyoday Ayurveda AI Product Expert Chatbot — Central Orchestrator
 *
 * Coordinates configuration, knowledge base, safety guardrails, AJAX engine,
 * admin settings, and frontend widget rendering.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once AYUR_CORE_PATH . 'includes/class-ayur-chatbot-config.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-chatbot-knowledge.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-chatbot-safety.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-chatbot-engine.php';
require_once AYUR_CORE_PATH . 'includes/class-ayur-chatbot-admin.php';

class Ayur_Chatbot {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function init() {
        self::instance()->setup();
    }

    public function setup() {
        Ayur_Chatbot_Knowledge::init();
        Ayur_Chatbot_Engine::init();
        Ayur_Chatbot_Admin::init();

        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_frontend_widget'), 40);
    }

    public function enqueue_assets() {
        if (!Ayur_Chatbot_Config::is_enabled()) {
            return;
        }

        // Suppress on checkout page to maintain 100% focus on payment & conversion
        if (self::is_checkout_context()) {
            return;
        }

        wp_enqueue_style(
            'ayur-chatbot-style',
            AYUR_CORE_URL . 'assets/css/ayur-chatbot.css',
            array(),
            AYUR_CORE_VERSION
        );

        wp_enqueue_script(
            'ayur-chatbot-script',
            AYUR_CORE_URL . 'assets/js/ayur-chatbot.js',
            array('jquery'),
            AYUR_CORE_VERSION,
            true
        );

        wp_localize_script('ayur-chatbot-script', 'ayurChatParams', array(
            'ajax_url'        => admin_url('admin-ajax.php'),
            'nonce'           => wp_create_nonce('ayur_chatbot_nonce'),
            'welcome_message' => Ayur_Chatbot_Config::get_welcome_message(),
            'prompt_chips'    => Ayur_Chatbot_Config::get_prompt_chips(),
            'position'        => Ayur_Chatbot_Config::get_position(),
            'whatsapp_url'    => Ayur_Chatbot_Config::get_whatsapp_url(),
            'max_length'      => Ayur_Chatbot_Config::get_max_input_length(),
            'site_name'       => 'Bhagyoday Ayurveda',
            'strings'         => array(
                'send'        => __('Send', 'ayur-core'),
                'typing'      => __('Ayurvedic Expert is typing...', 'ayur-core'),
                'error'       => __('Unable to reach assistant. Please retry or contact support.', 'ayur-core'),
                'retry'       => __('Retry', 'ayur-core'),
                'view_prod'   => __('View Product', 'ayur-core'),
                'in_stock'    => __('In Stock', 'ayur-core'),
                'out_of_stock'=> __('Out of Stock', 'ayur-core'),
                'chat_wa'     => __('Chat on WhatsApp', 'ayur-core'),
                'disclaimer'  => __('Product & shopping guide. Not a medical doctor.', 'ayur-core'),
            )
        ));
    }

    public function render_frontend_widget() {
        if (!Ayur_Chatbot_Config::is_enabled()) {
            return;
        }

        if (self::is_checkout_context()) {
            return;
        }

        $position = Ayur_Chatbot_Config::get_position();
        $pos_class = ($position === 'bottom-right') ? 'ayur-chat-pos-right' : 'ayur-chat-pos-left';
        ?>
        <!-- Bhagyoday Ayurveda AI Product Expert Chatbot Widget -->
        <div id="ayur-chatbot-root" class="ayur-chat-root <?php echo esc_attr($pos_class); ?>" aria-live="polite">
            
            <!-- Floating Launcher Button -->
            <button id="ayur-chat-launcher" class="ayur-chat-launcher" type="button" aria-label="<?php esc_attr_e('Chat with Ayurvedic Product Expert', 'ayur-core'); ?>" aria-expanded="false" aria-controls="ayur-chat-window">
                <span class="ayur-chat-launcher-pulse" aria-hidden="true"></span>
                <span class="ayur-chat-launcher-icon" aria-hidden="true">
                    <!-- Professional Herbal Leaf + Chat Hybrid SVG -->
                    <svg viewBox="0 0 32 32" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" fill="#173B2F" stroke="#B79552" />
                        <path d="M12 25h9l4 4V13a2 2 0 0 0-2-2h-2" stroke="#FAF7F0" />
                        <path d="M8 8h6M8 12h4" stroke="#B79552" stroke-width="2" />
                    </svg>
                </span>
                <span class="ayur-chat-launcher-badge">1</span>
                <span class="ayur-chat-launcher-tooltip"><?php _e('Ask Ayurvedic Product Expert', 'ayur-core'); ?></span>
            </button>

            <!-- Conversation Panel Window -->
            <section id="ayur-chat-window" class="ayur-chat-window" role="dialog" aria-modal="false" aria-label="<?php esc_attr_e('Bhagyoday Ayurveda Product Expert Chat', 'ayur-core'); ?>" hidden>
                
                <!-- Chat Header -->
                <header class="ayur-chat-header">
                    <div class="ayur-chat-header-info">
                        <div class="ayur-chat-avatar" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2a8 8 0 0 0-8 8c0 3.5 2.5 6.5 6 7.5V20H8v2h8v-2h-2v-2.5c3.5-1 6-4 6-7.5a8 8 0 0 0-8-8z"/>
                                <circle cx="9" cy="10" r="1" fill="currentColor"/>
                                <circle cx="15" cy="10" r="1" fill="currentColor"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="ayur-chat-title"><?php _e('Ayur Product Expert', 'ayur-core'); ?></h2>
                            <div class="ayur-chat-status">
                                <span class="ayur-chat-status-dot" aria-hidden="true"></span>
                                <span><?php _e('Verified Catalog AI • Online', 'ayur-core'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="ayur-chat-header-actions">
                        <button type="button" id="ayur-chat-minimize" class="ayur-chat-btn-icon" aria-label="<?php esc_attr_e('Minimize chat', 'ayur-core'); ?>" title="<?php esc_attr_e('Minimize', 'ayur-core'); ?>">
                            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                        <button type="button" id="ayur-chat-close" class="ayur-chat-btn-icon" aria-label="<?php esc_attr_e('Close chat', 'ayur-core'); ?>" title="<?php esc_attr_e('Close', 'ayur-core'); ?>">
                            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </header>

                <!-- Scrollable Messages Container -->
                <div id="ayur-chat-messages" class="ayur-chat-messages" tabindex="0">
                    <!-- Dynamic Messages injected by JS -->
                </div>

                <!-- Suggested Quick Action Chips -->
                <div id="ayur-chat-quick-actions" class="ayur-chat-quick-actions">
                    <!-- Dynamic Action Chips -->
                </div>

                <!-- Typing Indicator Bubble -->
                <div id="ayur-chat-typing" class="ayur-chat-typing" aria-hidden="true" style="display:none;">
                    <div class="ayur-chat-typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                    <span class="ayur-chat-typing-label"><?php _e('Finding verified answer...', 'ayur-core'); ?></span>
                </div>

                <!-- Error & Retry Banner -->
                <div id="ayur-chat-error" class="ayur-chat-error" style="display:none;">
                    <span id="ayur-chat-error-msg"><?php _e('Failed to load response.', 'ayur-core'); ?></span>
                    <button type="button" id="ayur-chat-retry-btn" class="ayur-chat-btn-retry"><?php _e('Retry', 'ayur-core'); ?></button>
                </div>

                <!-- Input Footer Area -->
                <footer class="ayur-chat-footer">
                    <form id="ayur-chat-form" class="ayur-chat-form" autocomplete="off">
                        <textarea id="ayur-chat-input" class="ayur-chat-input" rows="1" placeholder="<?php esc_attr_e('Ask about ingredients, usage, delivery...', 'ayur-core'); ?>" maxlength="500" aria-label="<?php esc_attr_e('Type your question', 'ayur-core'); ?>"></textarea>
                        <button type="submit" id="ayur-chat-send" class="ayur-chat-send" aria-label="<?php esc_attr_e('Send Message', 'ayur-core'); ?>">
                            <svg class="ayur-chat-send-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            <svg class="ayur-chat-send-spinner" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" style="display:none;">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.25"></circle>
                                <path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-linecap="round"></path>
                            </svg>
                        </button>
                    </form>
                    <div class="ayur-chat-disclaimer">
                        <span>🌿 <?php _e('Authentic Ayurvedic product assistant • Not a medical doctor.', 'ayur-core'); ?></span>
                    </div>
                </footer>

            </section>
        </div>
        <?php
    }

    private static function is_checkout_context() {
        if (function_exists('is_checkout') && is_checkout()) return true;
        if (function_exists('is_cart') && is_cart()) return true;
        if (function_exists('is_page') && (is_page('checkout') || is_page('cart'))) return true;
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
            if (strpos($uri, 'checkout') !== false || strpos($uri, 'cart') !== false) return true;
        }
        return false;
    }
}
