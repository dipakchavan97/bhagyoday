<?php
/**
 * Bhagyoday Ayurveda AI Chatbot Configuration & Secrets Manager
 *
 * Handles API provider configuration, credentials security, rate-limiting parameters,
 * and connection diagnostics. Keeps all keys securely on the server.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Chatbot_Config {

    const OPTION_ENABLED           = 'ayur_chatbot_enabled';
    const OPTION_PROVIDER          = 'ayur_chatbot_provider';
    const OPTION_API_KEY           = 'ayur_chatbot_api_key';
    const OPTION_MODEL             = 'ayur_chatbot_model';
    const OPTION_CUSTOM_ENDPOINT   = 'ayur_chatbot_custom_endpoint';
    const OPTION_TEMPERATURE       = 'ayur_chatbot_temperature';
    const OPTION_MAX_TOKENS        = 'ayur_chatbot_max_tokens';
    const OPTION_POSITION          = 'ayur_chatbot_position';
    const OPTION_RATE_LIMIT        = 'ayur_chatbot_rate_limit';
    const OPTION_MAX_INPUT_LENGTH  = 'ayur_chatbot_max_input_length';
    const OPTION_WELCOME_MSG       = 'ayur_chatbot_welcome_message';
    const OPTION_PROMPT_CHIPS      = 'ayur_chatbot_prompt_chips';
    const OPTION_SUPPORT_PHONE     = 'ayur_chatbot_support_phone';
    const OPTION_RETENTION_DAYS    = 'ayur_chatbot_retention_days';
    const OPTION_STATS_QUERIES     = 'ayur_chatbot_stats_queries';
    const OPTION_STATS_TOKENS      = 'ayur_chatbot_stats_tokens';
    const OPTION_STATS_ERRORS      = 'ayur_chatbot_stats_errors';

    public static function is_enabled() {
        return get_option(self::OPTION_ENABLED, 'yes') === 'yes';
    }

    public static function get_provider() {
        return get_option(self::OPTION_PROVIDER, 'gemini');
    }

    public static function get_api_key() {
        // 1. Check environment constants first (wp-config or server env)
        if (defined('AYUR_CHATBOT_API_KEY') && !empty(constant('AYUR_CHATBOT_API_KEY'))) {
            return constant('AYUR_CHATBOT_API_KEY');
        }

        $provider = self::get_provider();
        if ($provider === 'gemini') {
            if (defined('GEMINI_API_KEY') && !empty(constant('GEMINI_API_KEY'))) {
                return constant('GEMINI_API_KEY');
            }
            $env_key = getenv('GEMINI_API_KEY');
            if (!empty($env_key)) return $env_key;
        } elseif ($provider === 'openai') {
            if (defined('OPENAI_API_KEY') && !empty(constant('OPENAI_API_KEY'))) {
                return constant('OPENAI_API_KEY');
            }
            $env_key = getenv('OPENAI_API_KEY');
            if (!empty($env_key)) return $env_key;
        }

        // 2. Check stored WordPress option
        $stored = get_option(self::OPTION_API_KEY, '');
        return !empty($stored) ? trim($stored) : '';
    }

    public static function get_model() {
        $provider = self::get_provider();
        $default = ($provider === 'gemini') ? 'gemini-1.5-flash' : 'gpt-4o-mini';
        return get_option(self::OPTION_MODEL, $default);
    }

    public static function get_custom_endpoint() {
        return get_option(self::OPTION_CUSTOM_ENDPOINT, 'https://api.openai.com/v1');
    }

    public static function get_temperature() {
        return floatval(get_option(self::OPTION_TEMPERATURE, 0.3));
    }

    public static function get_max_tokens() {
        return absint(get_option(self::OPTION_MAX_TOKENS, 600));
    }

    public static function get_position() {
        // Default to bottom-left to avoid colliding with WhatsApp float at bottom-right
        return get_option(self::OPTION_POSITION, 'bottom-left');
    }

    public static function get_rate_limit_per_minute() {
        return absint(get_option(self::OPTION_RATE_LIMIT, 15));
    }

    public static function get_max_input_length() {
        return absint(get_option(self::OPTION_MAX_INPUT_LENGTH, 500));
    }

    public static function get_welcome_message() {
        $default = "Namaste! 🙏 I am your Bhagyoday Ayurveda Product Expert. Ask me about authentic herbal ingredients, how to use our formulations, order delivery, or get personalized recommendations.";
        return get_option(self::OPTION_WELCOME_MSG, $default);
    }

    public static function get_prompt_chips() {
        $stored = get_option(self::OPTION_PROMPT_CHIPS, null);
        if (is_array($stored) && !empty($stored)) {
            return $stored;
        }
        return array(
            'Ask about ingredients',
            'How to use products',
            'Compare products',
            'Delivery & COD policy',
            'Contact WhatsApp support'
        );
    }

    public static function get_support_phone() {
        $phone = get_option(self::OPTION_SUPPORT_PHONE, '');
        if (empty($phone)) {
            $phone = get_option('ayur_whatsapp_phone', '918888686858');
        }
        return preg_replace('/[^0-9]/', '', $phone);
    }

    public static function get_whatsapp_url($message = '') {
        $phone = self::get_support_phone();
        if (empty($message)) {
            $message = 'Namaste Bhagyoday Ayurveda! I need help with product recommendations.';
        }
        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    public static function record_query_stat($tokens_used = 0, $is_error = false) {
        $queries = absint(get_option(self::OPTION_STATS_QUERIES, 0)) + 1;
        update_option(self::OPTION_STATS_QUERIES, $queries, false);

        if ($tokens_used > 0) {
            $tokens = absint(get_option(self::OPTION_STATS_TOKENS, 0)) + $tokens_used;
            update_option(self::OPTION_STATS_TOKENS, $tokens, false);
        }

        if ($is_error) {
            $errors = absint(get_option(self::OPTION_STATS_ERRORS, 0)) + 1;
            update_option(self::OPTION_STATS_ERRORS, $errors, false);
        }
    }

    public static function get_stats() {
        return array(
            'queries' => absint(get_option(self::OPTION_STATS_QUERIES, 0)),
            'tokens'  => absint(get_option(self::OPTION_STATS_TOKENS, 0)),
            'errors'  => absint(get_option(self::OPTION_STATS_ERRORS, 0)),
        );
    }

    /**
     * Diagnostic Connection Test
     */
    public static function test_connection($provider, $api_key, $model, $custom_endpoint = '') {
        if (empty($api_key)) {
            return array('success' => false, 'message' => 'API Key is empty.');
        }

        $start = microtime(true);

        if ($provider === 'gemini') {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($api_key);
            $body = array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => 'Respond with the single word "OK" only.')
                        )
                    )
                ),
                'generationConfig' => array('maxOutputTokens' => 10)
            );

            $response = wp_remote_post($url, array(
                'headers' => array('Content-Type' => 'application/json'),
                'body'    => json_encode($body),
                'timeout' => 12,
            ));
        } else {
            // OpenAI or Custom OpenAI-Compatible
            $endpoint = !empty($custom_endpoint) ? rtrim($custom_endpoint, '/') : 'https://api.openai.com/v1';
            $url = $endpoint . '/chat/completions';
            $body = array(
                'model'       => $model,
                'messages'    => array(
                    array('role' => 'user', 'content' => 'Respond with the single word "OK" only.')
                ),
                'max_tokens'  => 10,
                'temperature' => 0.1,
            );

            $response = wp_remote_post($url, array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'body'    => json_encode($body),
                'timeout' => 12,
            ));
        }

        $duration = round((microtime(true) - $start) * 1000);

        if (is_wp_error($response)) {
            return array(
                'success'    => false,
                'message'    => 'Network/HTTP Error: ' . $response->get_error_message(),
                'latency_ms' => $duration
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $res_body = wp_remote_retrieve_body($response);
        $json = json_decode($res_body, true);

        if ($code >= 200 && $code < 300) {
            return array(
                'success'    => true,
                'message'    => "Connection successful! Provider responded in {$duration} ms.",
                'latency_ms' => $duration
            );
        }

        $error_msg = isset($json['error']['message']) ? $json['error']['message'] : ("HTTP " . $code . ": " . substr($res_body, 0, 150));
        return array(
            'success'    => false,
            'message'    => "API Error ({$code}): " . $error_msg,
            'latency_ms' => $duration
        );
    }
}
