<?php
/**
 * WhatsApp D2C Customer Support & Order Notification Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_WhatsApp {

    public static function init() {
        // Floating Widget on Frontend
        add_action('wp_footer', array(__CLASS__, 'render_floating_whatsapp_widget'), 30);

        // Admin Settings
        add_action('admin_menu', array(__CLASS__, 'register_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }

    public static function register_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('WhatsApp Support', 'ayur-core'),
            __('WhatsApp Support', 'ayur-core'),
            'manage_woocommerce',
            'ayur-whatsapp',
            array(__CLASS__, 'render_admin_page')
        );
    }

    public static function register_settings() {
        register_setting('ayur_whatsapp_group', 'ayur_whatsapp_phone');
        register_setting('ayur_whatsapp_group', 'ayur_whatsapp_message');
        register_setting('ayur_whatsapp_group', 'ayur_whatsapp_enabled');
    }

    public static function render_floating_whatsapp_widget() {
        $enabled = get_option('ayur_whatsapp_enabled', 'yes');
        if ($enabled !== 'yes') return;

        $phone = get_option('ayur_whatsapp_phone', '918888686858');
        $raw_phone = preg_replace('/[^0-9]/', '', $phone);
        $message = get_option('ayur_whatsapp_message', 'Namaste Bhagyoday Ayurveda! I would like some guidance on choosing the right Ayurvedic wellness formulation.');
        $wa_url = 'https://wa.me/' . $raw_phone . '?text=' . rawurlencode($message);

        ?>
        <!-- WhatsApp D2C Floating Support Button -->
        <aside class="ayur-wa-float-container" aria-label="<?php esc_attr_e('WhatsApp Support', 'ayur-core'); ?>">
            <a href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer" class="ayur-wa-btn" title="<?php esc_attr_e('Chat with Ayurvedic Expert on WhatsApp', 'ayur-core'); ?>">
                <span class="ayur-wa-pulse"></span>
                <svg class="ayur-wa-icon" viewBox="0 0 32 32" width="28" height="28" fill="currentColor">
                    <path d="M16 2a13.9 13.9 0 0 0-12 21L2 30l7.3-1.9A13.9 13.9 0 1 0 16 2zm0 25.5a11.5 11.5 0 0 1-5.9-1.6l-.4-.3-4.4 1.1 1.2-4.2-.3-.5a11.6 11.6 0 1 1 9.8 5.5zm6.4-8.6c-.3-.2-2-.9-2.3-1.1-.3-.1-.5-.2-.7.2-.2.3-.9 1.1-1.1 1.3-.2.2-.4.2-.7.1a8.9 8.9 0 0 1-5.3-4.6c-.4-.7.4-.7 1.1-2.1.1-.2 0-.4 0-.5s-.7-1.7-1-2.4c-.3-.6-.6-.5-.8-.5h-.7c-.2 0-.7.1-1.1.5-.4.4-1.5 1.5-1.5 3.6s1.5 4.2 1.7 4.5c.2.3 3 4.6 7.4 6.4 1 .4 1.8.7 2.5.9 1.1.3 2 .3 2.8.2.8-.1 2.3-.9 2.6-1.8.3-.9.3-1.6.2-1.8-.1-.2-.3-.3-.6-.5z"/>
                </svg>
                <span class="ayur-wa-tooltip"><?php _e('Need Ayurvedic Advice? Chat with us', 'ayur-core'); ?></span>
            </a>
        </aside>
        <?php
    }

    public static function render_admin_page() {
        ?>
        <div class="wrap" style="max-width:900px;">
            <h1><?php _e('WhatsApp D2C Support & Automation', 'ayur-core'); ?></h1>
            <p><?php _e('Configure live WhatsApp support chat for Indian customers and automated order status notifications.', 'ayur-core'); ?></p>

            <form method="post" action="options.php" style="background:#fff; padding:25px; border-radius:8px; border:1px solid #ccd0d4; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <?php settings_fields('ayur_whatsapp_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Floating Button', 'ayur-core'); ?></th>
                        <td>
                            <label>
                                <input name="ayur_whatsapp_enabled" type="checkbox" value="yes" <?php checked('yes', get_option('ayur_whatsapp_enabled', 'yes')); ?> />
                                <?php _e('Display high-converting WhatsApp bubble on all storefront pages', 'ayur-core'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ayur_whatsapp_phone"><?php _e('WhatsApp Number (with country code)', 'ayur-core'); ?></label></th>
                        <td>
                            <input name="ayur_whatsapp_phone" type="text" id="ayur_whatsapp_phone" value="<?php echo esc_attr(get_option('ayur_whatsapp_phone', '918888686858')); ?>" class="regular-text" placeholder="918888686858" />
                            <p class="description"><?php _e('Include 91 for India without plus sign (e.g. 918888686858).', 'ayur-core'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ayur_whatsapp_message"><?php _e('Pre-filled Greeting Message', 'ayur-core'); ?></label></th>
                        <td>
                            <textarea name="ayur_whatsapp_message" id="ayur_whatsapp_message" rows="3" class="large-text"><?php echo esc_textarea(get_option('ayur_whatsapp_message', 'Namaste Bhagyoday Ayurveda! I would like some guidance on choosing the right Ayurvedic wellness formulation.')); ?></textarea>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save WhatsApp Settings', 'ayur-core')); ?>
            </form>
        </div>
        <?php
    }
}
