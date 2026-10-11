<?php
/**
 * Bhagyoday Ayurveda AI Chatbot Admin Settings Interface
 *
 * Provides a responsive management console for credentials, provider selection,
 * appearance, knowledge re-indexing, custom FAQs, analytics, and unresolved inquiries.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Chatbot_Admin {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_post_ayur_chatbot_add_faq', array(__CLASS__, 'handle_add_faq'));
        add_action('admin_post_ayur_chatbot_delete_faq', array(__CLASS__, 'handle_delete_faq'));
        add_action('admin_post_ayur_chatbot_clear_unresolved', array(__CLASS__, 'handle_clear_unresolved'));
    }

    public static function register_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('AI Product Chatbot', 'ayur-core'),
            __('AI Product Chatbot', 'ayur-core'),
            'manage_woocommerce',
            'ayur-chatbot-settings',
            array(__CLASS__, 'render_admin_page')
        );
    }

    public static function register_settings() {
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_ENABLED);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_PROVIDER);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_API_KEY, array('sanitize_callback' => array(__CLASS__, 'sanitize_api_key')));
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_MODEL);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_CUSTOM_ENDPOINT);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_TEMPERATURE);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_MAX_TOKENS);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_POSITION);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_RATE_LIMIT);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_MAX_INPUT_LENGTH);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_WELCOME_MSG);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_PROMPT_CHIPS, array('sanitize_callback' => array(__CLASS__, 'sanitize_prompt_chips')));
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_SUPPORT_PHONE);
        register_setting('ayur_chatbot_group', Ayur_Chatbot_Config::OPTION_RETENTION_DAYS);
    }

    public static function sanitize_api_key($input) {
        $input = trim($input);
        if (strpos($input, '••••') !== false) {
            return get_option(Ayur_Chatbot_Config::OPTION_API_KEY, '');
        }
        return sanitize_text_field($input);
    }

    public static function sanitize_prompt_chips($input) {
        if (is_string($input)) {
            $lines = explode("\n", str_replace("\r", "", $input));
            $clean = array();
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $clean[] = sanitize_text_field($line);
                }
            }
            return $clean;
        }
        return is_array($input) ? array_map('sanitize_text_field', $input) : array();
    }

    public static function render_admin_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
        $summary    = Ayur_Chatbot_Knowledge::get_knowledge_summary();
        $stats      = Ayur_Chatbot_Config::get_stats();
        $unresolved = Ayur_Chatbot_Knowledge::get_unresolved_questions();
        $faqs       = Ayur_Chatbot_Knowledge::get_approved_faqs();
        $api_key    = Ayur_Chatbot_Config::get_api_key();
        $masked_key = !empty($api_key) ? substr($api_key, 0, 4) . '••••••••••••' . substr($api_key, -4) : '';
        ?>
        <div class="wrap" style="max-width:1100px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:20px; margin-bottom:15px;">
                <h1 style="color:#173B2F; margin:0; display:flex; align-items:center; gap:10px;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; background:#173B2F; border-radius:8px; color:#B79552;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 3.5 2.5 6.5 6 7.5V20H8v2h8v-2h-2v-2.5c3.5-1 6-4 6-7.5a8 8 0 0 0-8-8z"/><path d="M9 10h.01M15 10h.01"/></svg>
                    </span>
                    <?php _e('Bhagyoday Ayurveda — AI Product Expert Chatbot', 'ayur-core'); ?>
                </h1>
                <div>
                    <span style="display:inline-block; padding:4px 12px; border-radius:20px; font-weight:600; font-size:12px; <?php echo Ayur_Chatbot_Config::is_enabled() ? 'background:#e6f4ea; color:#137333;' : 'background:#fce8e6; color:#c5221f;'; ?>">
                        ● <?php echo Ayur_Chatbot_Config::is_enabled() ? 'Chatbot Active' : 'Chatbot Paused'; ?>
                    </span>
                </div>
            </div>

            <nav class="nav-tab-wrapper" style="margin-bottom:20px;">
                <a href="?page=ayur-chatbot-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('AI Engine & Credentials', 'ayur-core'); ?>
                </a>
                <a href="?page=ayur-chatbot-settings&tab=appearance" class="nav-tab <?php echo $active_tab === 'appearance' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Appearance & Launcher', 'ayur-core'); ?>
                </a>
                <a href="?page=ayur-chatbot-settings&tab=knowledge" class="nav-tab <?php echo $active_tab === 'knowledge' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Catalog Knowledge Base', 'ayur-core'); ?>
                </a>
                <a href="?page=ayur-chatbot-settings&tab=faqs" class="nav-tab <?php echo $active_tab === 'faqs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Custom FAQs', 'ayur-core'); ?>
                </a>
                <a href="?page=ayur-chatbot-settings&tab=safety" class="nav-tab <?php echo $active_tab === 'safety' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Safety & Rate Limits', 'ayur-core'); ?>
                </a>
                <a href="?page=ayur-chatbot-settings&tab=analytics" class="nav-tab <?php echo $active_tab === 'analytics' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Analytics & Unresolved', 'ayur-core'); ?>
                    <?php if (!empty($unresolved)): ?>
                        <span style="background:#B79552; color:#fff; border-radius:10px; padding:1px 6px; font-size:11px; margin-left:4px;"><?php echo count($unresolved); ?></span>
                    <?php endif; ?>
                </a>
            </nav>

            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
                <div class="notice notice-success is-dismissible"><p><strong><?php _e('Chatbot settings saved successfully.', 'ayur-core'); ?></strong></p></div>
            <?php endif; ?>

            <div style="background:#fff; padding:25px; border-radius:8px; border:1px solid #ccd0d4; box-shadow:0 1px 3px rgba(0,0,0,0.05);">

                <!-- TAB 1: GENERAL & AI ENGINE -->
                <?php if ($active_tab === 'general'): ?>
                    <form method="post" action="options.php">
                        <?php settings_fields('ayur_chatbot_group'); ?>
                        <h3><?php _e('AI Provider & Server Credentials', 'ayur-core'); ?></h3>
                        <p class="description"><?php _e('All AI API keys are stored securely on the server and never exposed to the frontend browser.', 'ayur-core'); ?></p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Enable AI Chatbot', 'ayur-core'); ?></th>
                                <td>
                                    <label>
                                        <input name="<?php echo Ayur_Chatbot_Config::OPTION_ENABLED; ?>" type="checkbox" value="yes" <?php checked('yes', get_option(Ayur_Chatbot_Config::OPTION_ENABLED, 'yes')); ?> />
                                        <?php _e('Display interactive AI shopping assistant on the storefront', 'ayur-core'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_provider"><?php _e('AI Provider', 'ayur-core'); ?></label></th>
                                <td>
                                    <select name="<?php echo Ayur_Chatbot_Config::OPTION_PROVIDER; ?>" id="ayur_chatbot_provider">
                                        <option value="gemini" <?php selected('gemini', Ayur_Chatbot_Config::get_provider()); ?>>Google Gemini (Recommended — Ultra-fast & Cost Effective)</option>
                                        <option value="openai" <?php selected('openai', Ayur_Chatbot_Config::get_provider()); ?>>OpenAI (GPT-4o Mini / GPT-4o)</option>
                                        <option value="custom" <?php selected('custom', Ayur_Chatbot_Config::get_provider()); ?>>Custom OpenAI-Compatible (OpenRouter / Groq / Local LLM)</option>
                                    </select>
                                    <p class="description"><?php _e('Select your preferred AI inference backend.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_api_key"><?php _e('API Key', 'ayur-core'); ?></label></th>
                                <td>
                                    <input name="<?php echo Ayur_Chatbot_Config::OPTION_API_KEY; ?>" type="password" id="ayur_chatbot_api_key" value="<?php echo esc_attr($masked_key); ?>" class="regular-text" placeholder="Enter API Key" autocomplete="new-password" />
                                    <?php if (defined('AYUR_CHATBOT_API_KEY') || defined('GEMINI_API_KEY') || defined('OPENAI_API_KEY')): ?>
                                        <p style="color:#137333; font-weight:600; margin-top:4px;">🔒 <?php _e('Active API key detected via wp-config / environment variable constant.', 'ayur-core'); ?></p>
                                    <?php endif; ?>
                                    <p class="description"><?php _e('If no API key is provided, the chatbot uses the built-in Grounded Knowledge Matching Engine with 100% uptime.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_model"><?php _e('Model Name', 'ayur-core'); ?></label></th>
                                <td>
                                    <input name="<?php echo Ayur_Chatbot_Config::OPTION_MODEL; ?>" type="text" id="ayur_chatbot_model" value="<?php echo esc_attr(Ayur_Chatbot_Config::get_model()); ?>" class="regular-text" />
                                    <p class="description"><?php _e('e.g. gemini-1.5-flash, gemini-2.5-flash, gpt-4o-mini, or mixtral-8x7b.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr id="row_custom_endpoint" style="<?php echo Ayur_Chatbot_Config::get_provider() === 'custom' ? '' : 'display:none;'; ?>">
                                <th scope="row"><label for="ayur_chatbot_custom_endpoint"><?php _e('Custom Endpoint Base URL', 'ayur-core'); ?></label></th>
                                <td>
                                    <input name="<?php echo Ayur_Chatbot_Config::OPTION_CUSTOM_ENDPOINT; ?>" type="url" id="ayur_chatbot_custom_endpoint" value="<?php echo esc_attr(Ayur_Chatbot_Config::get_custom_endpoint()); ?>" class="large-text" placeholder="https://api.openai.com/v1" />
                                    <p class="description"><?php _e('Base URL for custom OpenAI-compatible API without trailing slash (e.g. https://openrouter.ai/api/v1).', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('API Connectivity Diagnostic', 'ayur-core'); ?></th>
                                <td>
                                    <button type="button" id="btn-test-connection" class="button button-secondary">
                                        ⚡ <?php _e('Test Connection Now', 'ayur-core'); ?>
                                    </button>
                                    <span id="test-connection-status" style="margin-left:12px; font-weight:600;"></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_temperature"><?php _e('Temperature', 'ayur-core'); ?></label></th>
                                <td>
                                    <input name="<?php echo Ayur_Chatbot_Config::OPTION_TEMPERATURE; ?>" type="number" step="0.05" min="0" max="1" id="ayur_chatbot_temperature" value="<?php echo esc_attr(Ayur_Chatbot_Config::get_temperature()); ?>" class="small-text" />
                                    <p class="description"><?php _e('Recommended: 0.2 - 0.4 for strict factual accuracy and zero hallucinations.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_max_tokens"><?php _e('Max Output Tokens', 'ayur-core'); ?></label></th>
                                <td>
                                    <input name="<?php echo Ayur_Chatbot_Config::OPTION_MAX_TOKENS; ?>" type="number" step="50" min="150" max="2000" id="ayur_chatbot_max_tokens" value="<?php echo esc_attr(Ayur_Chatbot_Config::get_max_tokens()); ?>" class="small-text" />
                                    <p class="description"><?php _e('Maximum tokens allowed per response (default 600). Keeps answers concise and controls API costs.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <?php submit_button(__('Save AI Settings', 'ayur-core')); ?>
                    </form>

                <!-- TAB 2: APPEARANCE & LAUNCHER -->
                <?php elseif ($active_tab === 'appearance'): ?>
                    <form method="post" action="options.php">
                        <?php settings_fields('ayur_chatbot_group'); ?>
                        <h3><?php _e('Chatbot Interface & Positioning', 'ayur-core'); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_position"><?php _e('Launcher Position', 'ayur-core'); ?></label></th>
                                <td>
                                    <select name="<?php echo Ayur_Chatbot_Config::OPTION_POSITION; ?>" id="ayur_chatbot_position">
                                        <option value="bottom-left" <?php selected('bottom-left', Ayur_Chatbot_Config::get_position()); ?>>Bottom-Left (Recommended — Keeps WhatsApp Button Free at Bottom-Right)</option>
                                        <option value="bottom-right" <?php selected('bottom-right', Ayur_Chatbot_Config::get_position()); ?>>Bottom-Right (Stacked with WhatsApp Support)</option>
                                    </select>
                                    <p class="description"><?php _e('Automatically positions launcher above mobile safe-areas and avoids sticky Add-to-Cart drawer.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_welcome_message"><?php _e('Welcome Greeting', 'ayur-core'); ?></label></th>
                                <td>
                                    <textarea name="<?php echo Ayur_Chatbot_Config::OPTION_WELCOME_MSG; ?>" id="ayur_chatbot_welcome_message" rows="3" class="large-text"><?php echo esc_textarea(Ayur_Chatbot_Config::get_welcome_message()); ?></textarea>
                                    <p class="description"><?php _e('Displayed when customer first opens the chat window.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_prompt_chips"><?php _e('Suggested Starter Chips', 'ayur-core'); ?></label></th>
                                <td>
                                    <?php
                                    $chips = Ayur_Chatbot_Config::get_prompt_chips();
                                    $chips_str = implode("\n", $chips);
                                    ?>
                                    <textarea name="<?php echo Ayur_Chatbot_Config::OPTION_PROMPT_CHIPS; ?>" id="ayur_chatbot_prompt_chips" rows="5" class="large-text"><?php echo esc_textarea($chips_str); ?></textarea>
                                    <p class="description"><?php _e('Enter one suggested question per line. Displayed as clickable pill buttons.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_support_phone"><?php _e('WhatsApp Helpline Number', 'ayur-core'); ?></label></th>
                                <td>
                                    <input name="<?php echo Ayur_Chatbot_Config::OPTION_SUPPORT_PHONE; ?>" type="text" id="ayur_chatbot_support_phone" value="<?php echo esc_attr(Ayur_Chatbot_Config::get_support_phone()); ?>" class="regular-text" placeholder="918888686858" />
                                    <p class="description"><?php _e('Destination phone for live escalation buttons inside the chat.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <?php submit_button(__('Save Appearance Settings', 'ayur-core')); ?>
                    </form>

                <!-- TAB 3: CATALOG KNOWLEDGE BASE -->
                <?php elseif ($active_tab === 'knowledge'): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <div>
                            <h3 style="margin:0;"><?php _e('Indexed Product Knowledge Base', 'ayur-core'); ?></h3>
                            <p class="description" style="margin:4px 0 0 0;">
                                <?php printf(__('Last indexed: %s | Active products: %d', 'ayur-core'), esc_html($summary['generated_at']), count($summary['products'])); ?>
                            </p>
                        </div>
                        <button type="button" id="btn-reindex-catalog" class="button button-primary">
                            🔄 <?php _e('Refresh & Reindex Catalog Now', 'ayur-core'); ?>
                        </button>
                    </div>
                    <div id="reindex-status" style="margin-bottom:12px; font-weight:600; display:none;"></div>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:70px;"><?php _e('Image', 'ayur-core'); ?></th>
                                <th><?php _e('Product Formulation', 'ayur-core'); ?></th>
                                <th style="width:130px;"><?php _e('Category', 'ayur-core'); ?></th>
                                <th style="width:100px;"><?php _e('Live Price', 'ayur-core'); ?></th>
                                <th style="width:90px;"><?php _e('Stock', 'ayur-core'); ?></th>
                                <th style="width:110px;"><?php _e('Ingredients', 'ayur-core'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summary['products'] as $p): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo esc_url($p['image_url']); ?>" alt="" style="width:48px; height:48px; object-fit:contain; border-radius:4px; border:1px solid #e2e8f0; background:#FAF7F0;" />
                                    </td>
                                    <td>
                                        <strong><a href="<?php echo esc_url($p['permalink']); ?>" target="_blank"><?php echo esc_html($p['name']); ?></a></strong><br>
                                        <small style="color:#64748b;">SKU: <?php echo esc_html($p['sku']); ?> | <?php echo esc_html($p['pack_size']); ?></small>
                                    </td>
                                    <td><?php echo esc_html($p['category']); ?></td>
                                    <td>
                                        <strong style="color:#173B2F;"><?php echo esc_html($p['formatted_price']); ?></strong>
                                        <br><small><del style="color:#94a3b8;"><?php echo esc_html($p['formatted_mrp']); ?></del></small>
                                    </td>
                                    <td>
                                        <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600; <?php echo $p['in_stock'] ? 'background:#e6f4ea; color:#137333;' : 'background:#fce8e6; color:#c5221f;'; ?>">
                                            <?php echo $p['in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span title="<?php echo esc_attr(implode(', ', $p['clean_ingredients'])); ?>" style="cursor:help; border-bottom:1px dotted #999;">
                                            <?php echo count($p['clean_ingredients']); ?> Verified Herbs
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <!-- TAB 4: CUSTOM FAQS -->
                <?php elseif ($active_tab === 'faqs'): ?>
                    <h3><?php _e('Approved Store FAQs', 'ayur-core'); ?></h3>
                    <p class="description"><?php _e('Custom question-and-answer pairs that are directly fed into the AI knowledge layer.', 'ayur-core'); ?></p>

                    <table class="wp-list-table widefat fixed striped" style="margin-bottom:25px;">
                        <thead>
                            <tr>
                                <th style="width:35%;"><?php _e('Question', 'ayur-core'); ?></th>
                                <th><?php _e('Verified Answer', 'ayur-core'); ?></th>
                                <th style="width:90px;"><?php _e('Action', 'ayur-core'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($faqs)): ?>
                                <tr><td colspan="3"><?php _e('No custom FAQs defined.', 'ayur-core'); ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($faqs as $idx => $f): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($f['question']); ?></strong></td>
                                        <td><?php echo esc_html($f['answer']); ?></td>
                                        <td>
                                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirm('Delete this FAQ?');">
                                                <input type="hidden" name="action" value="ayur_chatbot_delete_faq" />
                                                <input type="hidden" name="faq_index" value="<?php echo esc_attr($idx); ?>" />
                                                <?php wp_nonce_field('ayur_chatbot_faq_nonce'); ?>
                                                <button type="submit" class="button button-small button-link-delete"><?php _e('Delete', 'ayur-core'); ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div style="background:#FAF7F0; padding:20px; border-radius:6px; border:1px solid #e5dfd5;">
                        <h4 style="margin-top:0; color:#173B2F;"><?php _e('Add New Verified FAQ', 'ayur-core'); ?></h4>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                            <input type="hidden" name="action" value="ayur_chatbot_add_faq" />
                            <?php wp_nonce_field('ayur_chatbot_faq_nonce'); ?>
                            <p>
                                <label for="faq_question"><strong><?php _e('Question:', 'ayur-core'); ?></strong></label><br>
                                <input type="text" name="faq_question" id="faq_question" class="large-text" required placeholder="e.g. Are your products AYUSH approved?" />
                            </p>
                            <p>
                                <label for="faq_answer"><strong><?php _e('Answer:', 'ayur-core'); ?></strong></label><br>
                                <textarea name="faq_answer" id="faq_answer" rows="3" class="large-text" required placeholder="Verified factual response..."></textarea>
                            </p>
                            <p>
                                <button type="submit" class="button button-primary"><?php _e('Add FAQ to Knowledge Base', 'ayur-core'); ?></button>
                            </p>
                        </form>
                    </div>

                <!-- TAB 5: SAFETY & RATE LIMITS -->
                <?php elseif ($active_tab === 'safety'): ?>
                    <form method="post" action="options.php">
                        <?php settings_fields('ayur_chatbot_group'); ?>
                        <h3><?php _e('Ayurveda Safety Standards & Abuse Protection', 'ayur-core'); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_rate_limit"><?php _e('Rate Limit (Queries/Minute)', 'ayur-core'); ?></label></th>
                                <td>
                                    <input name="<?php echo Ayur_Chatbot_Config::OPTION_RATE_LIMIT; ?>" type="number" min="3" max="60" id="ayur_chatbot_rate_limit" value="<?php echo esc_attr(Ayur_Chatbot_Config::get_rate_limit_per_minute()); ?>" class="small-text" />
                                    <p class="description"><?php _e('Maximum questions allowed per IP per minute (default 15). Protects against automated script abuse.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="ayur_chatbot_max_input_length"><?php _e('Max Question Length', 'ayur-core'); ?></label></th>
                                <td>
                                    <input name="<?php echo Ayur_Chatbot_Config::OPTION_MAX_INPUT_LENGTH; ?>" type="number" min="100" max="2000" id="ayur_chatbot_max_input_length" value="<?php echo esc_attr(Ayur_Chatbot_Config::get_max_input_length()); ?>" class="small-text" />
                                    <p class="description"><?php _e('Maximum characters allowed per question (default 500). Prevents prompt injection and token bloat.', 'ayur-core'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Ayurvedic Compliance Invariants', 'ayur-core'); ?></th>
                                <td>
                                    <div style="background:#FAF7F0; padding:15px; border-radius:6px; border-left:4px solid #173B2F;">
                                        <p style="margin:0 0 8px 0;"><strong>Active Guardrails Enforced in Backend Engine:</strong></p>
                                        <ul style="list-style:disc; margin-left:20px; color:#334155;">
                                            <li><strong>Strict Non-Prescriptive Role:</strong> Always identifies as shopping/product assistant; refuses diagnostic and prescription requests.</li>
                                            <li><strong>Zero Disease Cure Promises:</strong> Rejects claims of disease cure/reversal; explains classical dietary Rasayana concepts.</li>
                                            <li><strong>Medication Preservation:</strong> Refuses requests to stop doctor-prescribed medications (e.g. insulin or BP drugs).</li>
                                            <li><strong>Clean Composition Presentation:</strong> Names-only presented on storefront; milligram dosages restricted to verified label inquiries.</li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <?php submit_button(__('Save Safety Settings', 'ayur-core')); ?>
                    </form>

                <!-- TAB 6: ANALYTICS & UNRESOLVED INQUIRIES -->
                <?php elseif ($active_tab === 'analytics'): ?>
                    <h3><?php _e('Usage Statistics & Performance Metrics', 'ayur-core'); ?></h3>
                    
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin-bottom:25px;">
                        <div style="background:#FAF7F0; border:1px solid #e2e8f0; border-radius:8px; padding:15px;">
                            <div style="color:#64748b; font-size:12px; font-weight:600; text-transform:uppercase;"><?php _e('Total Queries Answered', 'ayur-core'); ?></div>
                            <div style="font-size:28px; font-weight:700; color:#173B2F; margin-top:5px;"><?php echo number_format($stats['queries']); ?></div>
                        </div>
                        <div style="background:#FAF7F0; border:1px solid #e2e8f0; border-radius:8px; padding:15px;">
                            <div style="color:#64748b; font-size:12px; font-weight:600; text-transform:uppercase;"><?php _e('Estimated Tokens Consumed', 'ayur-core'); ?></div>
                            <div style="font-size:28px; font-weight:700; color:#315C45; margin-top:5px;"><?php echo number_format($stats['tokens']); ?></div>
                        </div>
                        <div style="background:#FAF7F0; border:1px solid #e2e8f0; border-radius:8px; padding:15px;">
                            <div style="color:#64748b; font-size:12px; font-weight:600; text-transform:uppercase;"><?php _e('Estimated API Cost', 'ayur-core'); ?></div>
                            <div style="font-size:28px; font-weight:700; color:#B79552; margin-top:5px;">
                                $<?php echo number_format(($stats['tokens'] / 1000000) * 0.15, 4); ?>
                            </div>
                            <small style="color:#94a3b8;"><?php _e('Based on standard Flash model rates ($0.15 / 1M tokens)', 'ayur-core'); ?></small>
                        </div>
                        <div style="background:#FAF7F0; border:1px solid #e2e8f0; border-radius:8px; padding:15px;">
                            <div style="color:#64748b; font-size:12px; font-weight:600; text-transform:uppercase;"><?php _e('Fallback / API Errors', 'ayur-core'); ?></div>
                            <div style="font-size:28px; font-weight:700; color:#64748b; margin-top:5px;"><?php echo number_format($stats['errors']); ?></div>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <h4 style="margin:0;"><?php _e('Unresolved Customer Questions', 'ayur-core'); ?></h4>
                        <?php if (!empty($unresolved)): ?>
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirm('Clear unresolved queries log?');">
                                <input type="hidden" name="action" value="ayur_chatbot_clear_unresolved" />
                                <?php wp_nonce_field('ayur_chatbot_unresolved_nonce'); ?>
                                <button type="submit" class="button button-secondary"><?php _e('Clear Log', 'ayur-core'); ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <p class="description"><?php _e('Questions where customers asked something not currently found in the catalog knowledge base. Review and add answers to the FAQ tab.', 'ayur-core'); ?></p>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Customer Query', 'ayur-core'); ?></th>
                                <th style="width:200px;"><?php _e('Reason', 'ayur-core'); ?></th>
                                <th style="width:160px;"><?php _e('Timestamp', 'ayur-core'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($unresolved)): ?>
                                <tr><td colspan="3" style="text-align:center; color:#64748b;"><?php _e('No unresolved queries logged. Excellent!', 'ayur-core'); ?></td></tr>
                            <?php else: ?>
                                <?php foreach ($unresolved as $item): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($item['query']); ?></strong></td>
                                        <td><code><?php echo esc_html($item['reason']); ?></code></td>
                                        <td><?php echo esc_html($item['timestamp']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div style="margin-top:20px; padding:12px 15px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; color:#64748b; font-size:12px;">
                        🔒 <strong><?php _e('Privacy & Data Governance Notice:', 'ayur-core'); ?></strong>
                        <?php _e('To protect shopper privacy, conversations are not stored in the database. Customer names, delivery addresses, phone numbers, and payment details are never sent to AI providers or persisted in chatbot logs.', 'ayur-core'); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Toggle custom endpoint field
            $('#ayur_chatbot_provider').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#row_custom_endpoint').show();
                } else {
                    $('#row_custom_endpoint').hide();
                }
            });

            // Diagnostic Connection Test Button
            $('#btn-test-connection').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var status = $('#test-connection-status');
                
                btn.prop('disabled', true).text('Testing...');
                status.html('<span style="color:#64748b;">Contacting provider...</span>');

                $.post(ajaxurl, {
                    action: 'ayur_chatbot_test_connection',
                    nonce: '<?php echo wp_create_nonce('ayur_chatbot_admin_nonce'); ?>',
                    provider: $('#ayur_chatbot_provider').val(),
                    api_key: $('#ayur_chatbot_api_key').val(),
                    model: $('#ayur_chatbot_model').val(),
                    endpoint: $('#ayur_chatbot_custom_endpoint').val()
                }, function(res) {
                    btn.prop('disabled', false).text('⚡ Test Connection Now');
                    if (res.success) {
                        status.html('<span style="color:#137333;">✔ ' + res.data.message + '</span>');
                    } else {
                        status.html('<span style="color:#c5221f;">✖ ' + (res.data ? res.data.message : 'Connection failed') + '</span>');
                    }
                }).fail(function() {
                    btn.prop('disabled', false).text('⚡ Test Connection Now');
                    status.html('<span style="color:#c5221f;">✖ Request failed (Network error)</span>');
                });
            });

            // Reindex Catalog Knowledge
            $('#btn-reindex-catalog').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var status = $('#reindex-status');

                btn.prop('disabled', true).text('Reindexing...');
                status.show().html('<span style="color:#64748b;">Invalidating cache and rebuilding knowledge layer...</span>');

                $.post(ajaxurl, {
                    action: 'ayur_chatbot_reindex',
                    nonce: '<?php echo wp_create_nonce('ayur_chatbot_admin_nonce'); ?>'
                }, function(res) {
                    btn.prop('disabled', false).text('🔄 Refresh & Reindex Catalog Now');
                    if (res.success) {
                        status.html('<span style="color:#137333;">✔ ' + res.data.message + ' (' + res.data.products_count + ' products synced)</span>');
                        setTimeout(function() { location.reload(); }, 1200);
                    } else {
                        status.html('<span style="color:#c5221f;">✖ Reindexing failed</span>');
                    }
                }).fail(function() {
                    btn.prop('disabled', false).text('🔄 Refresh & Reindex Catalog Now');
                    status.html('<span style="color:#c5221f;">✖ Request failed</span>');
                });
            });
        });
        </script>
        <?php
    }

    public static function handle_add_faq() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Unauthorized'));
        }
        check_admin_referer('ayur_chatbot_faq_nonce');

        $q = isset($_POST['faq_question']) ? sanitize_text_field(wp_unslash($_POST['faq_question'])) : '';
        $a = isset($_POST['faq_answer']) ? sanitize_textarea_field(wp_unslash($_POST['faq_answer'])) : '';

        if (!empty($q) && !empty($a)) {
            $faqs = Ayur_Chatbot_Knowledge::get_approved_faqs();
            $faqs[] = array('question' => $q, 'answer' => $a);
            update_option('ayur_chatbot_custom_faqs', $faqs);
            Ayur_Chatbot_Knowledge::invalidate_cache();
        }

        wp_safe_redirect(admin_url('admin.php?page=ayur-chatbot-settings&tab=faqs&added=1'));
        exit;
    }

    public static function handle_delete_faq() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Unauthorized'));
        }
        check_admin_referer('ayur_chatbot_faq_nonce');

        $idx = isset($_POST['faq_index']) ? absint($_POST['faq_index']) : -1;
        $faqs = Ayur_Chatbot_Knowledge::get_approved_faqs();

        if (isset($faqs[$idx])) {
            unset($faqs[$idx]);
            $faqs = array_values($faqs);
            update_option('ayur_chatbot_custom_faqs', $faqs);
            Ayur_Chatbot_Knowledge::invalidate_cache();
        }

        wp_safe_redirect(admin_url('admin.php?page=ayur-chatbot-settings&tab=faqs&deleted=1'));
        exit;
    }

    public static function handle_clear_unresolved() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Unauthorized'));
        }
        check_admin_referer('ayur_chatbot_unresolved_nonce');

        Ayur_Chatbot_Knowledge::clear_unresolved_questions();

        wp_safe_redirect(admin_url('admin.php?page=ayur-chatbot-settings&tab=analytics&cleared=1'));
        exit;
    }
}
