<?php
/**
 * Bhagyoday Ayurveda AI Chatbot Backend Engine
 *
 * Handles AJAX requests, rate-limiting, external AI API orchestration (Gemini/OpenAI),
 * intelligent grounded offline fallback, and privacy protection.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Chatbot_Engine {

    public static function init() {
        // Customer Ajax Endpoints
        add_action('wp_ajax_ayur_chatbot_message', array(__CLASS__, 'handle_chat_message'));
        add_action('wp_ajax_nopriv_ayur_chatbot_message', array(__CLASS__, 'handle_chat_message'));

        // Admin Ajax Diagnostic Endpoints
        add_action('wp_ajax_ayur_chatbot_test_connection', array(__CLASS__, 'handle_admin_test_connection'));
        add_action('wp_ajax_ayur_chatbot_reindex', array(__CLASS__, 'handle_admin_reindex'));
    }

    /**
     * Handle Customer Chat Message
     */
    public static function handle_chat_message() {
        // 1. Verify CSRF Nonce
        if (!check_ajax_referer('ayur_chatbot_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security token expired. Please refresh the page and try again.'), 403);
        }

        // 2. Validate Chatbot Enabled Status
        if (!Ayur_Chatbot_Config::is_enabled()) {
            wp_send_json_error(array('message' => 'Chatbot is currently paused by store administrator.'), 503);
        }

        // 3. Extract and Sanitize User Input
        $raw_message = isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : '';
        $raw_message = trim($raw_message);

        if (empty($raw_message)) {
            wp_send_json_error(array('message' => 'Please enter a question.'), 400);
        }

        $max_len = max(100, Ayur_Chatbot_Config::get_max_input_length());
        if (mb_strlen($raw_message) > $max_len) {
            $raw_message = mb_substr($raw_message, 0, $max_len);
        }

        // 4. Rate Limiting (IP-based sliding window with Retry-After header)
        $client_ip = self::get_client_ip();
        $rate_check = self::check_rate_limit($client_ip);
        if ($rate_check['limited']) {
            $retry_after = (int)$rate_check['retry_after'];
            @header('Retry-After: ' . $retry_after);
            status_header(429);
            wp_send_json_error(array(
                'message'     => sprintf(
                    __('You are asking questions quickly. Please wait %d seconds before your next question.', 'ayur-core'),
                    $retry_after
                ),
                'retry_after' => $retry_after,
                'code'        => 'rate_limit_exceeded'
            ), 429);
        }

        // 5. Language Detection
        $lang = Ayur_Chatbot_Safety::detect_language($raw_message);

        // 6. Medical Safety Evaluation
        $safety = Ayur_Chatbot_Safety::evaluate_safety($raw_message, $lang);
        if ($safety && !empty($safety['refusal'])) {
            // Immediate emergency refusal
            wp_send_json_success(array(
                'status'        => 'success',
                'message'       => $safety['message'],
                'products'      => array(),
                'quick_replies' => array('Contact WhatsApp Support', 'Store policies', 'Ask about a product'),
                'language'      => $lang,
            ));
            return;
        }

        // 7. Search Grounded Product Catalog
        $matched_products = Ayur_Chatbot_Knowledge::search_relevant_products($raw_message, 3);
        $all_products     = Ayur_Chatbot_Knowledge::get_all_products();
        $policies         = Ayur_Chatbot_Knowledge::get_store_policies();

        // 8. Attempt External AI API Generation
        $api_key = Ayur_Chatbot_Config::get_api_key();
        $ai_response = null;

        if (!empty($api_key)) {
            $ai_response = self::call_ai_provider($raw_message, $lang, $matched_products, $policies);
        }

        // 9. If AI responded successfully, format and return
        if ($ai_response && !empty($ai_response['text'])) {
            $reply_text = self::strip_leading_greeting($ai_response['text']);

            // Deflection check: If user asked about piles/hemorrhoids/bawasir/mulvyadh,
            // ensure the response actually addresses piles and mentions the verified formulation.
            // If the model produced a generic greeting without mentioning the piles product, fallback to verified branch.
            $is_piles_inquiry = self::contains_any(strtolower($raw_message), array('piles', 'pile', 'bawasir', 'bawaseer', 'hemorrhoid', 'haemorrhoids', 'mulvyadh', 'moolvyadh', 'बवासीर', 'मूळव्याध', 'पाइल्स', 'मुलव्याध', 'fissure'));
            if ($is_piles_inquiry && !self::contains_any(strtolower($reply_text), array('piles care', 'triphala', 'ayur-piles', 'aloe vera triphala', 'बवासीर', 'मूळव्याध', 'पाइल्स', 'kumari', 'nagkesar'))) {
                $ai_response = null;
            }
        }

        if ($ai_response && !empty($ai_response['text'])) {
            $reply_text = self::strip_leading_greeting($ai_response['text']);

            // Prepend safety note if non-refusal warning was detected
            if ($safety && !empty($safety['message'])) {
                $reply_text = $safety['message'] . "\n\n" . $reply_text;
            }

            // Extract recommended products
            $recommended_products = self::extract_product_cards($reply_text, $matched_products, $all_products);
            $clean_text = self::clean_product_tags($reply_text);

            Ayur_Chatbot_Config::record_query_stat($ai_response['tokens'], false);

            wp_send_json_success(array(
                'status'        => 'success',
                'message'       => $clean_text,
                'products'      => $recommended_products,
                'quick_replies' => self::generate_quick_replies($matched_products, $lang),
                'language'      => $lang,
            ));
            return;
        }

        // 10. Intelligent Grounded Fallback (when AI key is empty or API call fails)
        $fallback = self::execute_grounded_fallback($raw_message, $lang, $matched_products, $all_products, $policies, $safety);

        // Record query and error stats if external key failed
        if (!empty($api_key)) {
            Ayur_Chatbot_Config::record_query_stat(0, true);
        } else {
            Ayur_Chatbot_Config::record_query_stat(0, false);
        }

        wp_send_json_success(array(
            'status'        => 'success',
            'message'       => $fallback['message'],
            'products'      => $fallback['products'],
            'quick_replies' => $fallback['quick_replies'],
            'language'      => $lang,
        ));
    }

    /**
     * Call Configured AI Provider (Gemini or OpenAI-compatible)
     */
    private static function call_ai_provider($user_message, $lang, $matched_products, $policies) {
        $provider    = Ayur_Chatbot_Config::get_provider();
        $api_key     = Ayur_Chatbot_Config::get_api_key();
        $model       = Ayur_Chatbot_Config::get_model();
        $temperature = Ayur_Chatbot_Config::get_temperature();
        $max_tokens  = Ayur_Chatbot_Config::get_max_tokens();

        $system_prompt = Ayur_Chatbot_Safety::build_system_prompt($lang, $matched_products, $policies);

        try {
            if ($provider === 'gemini') {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($api_key);
                $payload = array(
                    'system_instruction' => array(
                        'parts' => array(array('text' => $system_prompt))
                    ),
                    'contents' => array(
                        array(
                            'role'  => 'user',
                            'parts' => array(array('text' => $user_message))
                        )
                    ),
                    'generationConfig' => array(
                        'temperature'     => $temperature,
                        'maxOutputTokens' => $max_tokens,
                    )
                );

                $response = wp_remote_post($url, array(
                    'headers' => array('Content-Type' => 'application/json'),
                    'body'    => json_encode($payload),
                    'timeout' => 15,
                ));
            } else {
                // OpenAI or Custom Endpoint
                $endpoint = rtrim(Ayur_Chatbot_Config::get_custom_endpoint(), '/');
                $url = $endpoint . '/chat/completions';
                $payload = array(
                    'model'       => $model,
                    'messages'    => array(
                        array('role' => 'system', 'content' => $system_prompt),
                        array('role' => 'user', 'content' => $user_message)
                    ),
                    'temperature' => $temperature,
                    'max_tokens'  => $max_tokens,
                );

                $response = wp_remote_post($url, array(
                    'headers' => array(
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $api_key,
                    ),
                    'body'    => json_encode($payload),
                    'timeout' => 15,
                ));
            }

            if (is_wp_error($response)) {
                return null;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code < 200 || $code >= 300) {
                return null;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($provider === 'gemini') {
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $tokens = isset($data['usageMetadata']['totalTokenCount']) ? $data['usageMetadata']['totalTokenCount'] : 0;
                    return array(
                        'text'   => trim($data['candidates'][0]['content']['parts'][0]['text']),
                        'tokens' => $tokens
                    );
                }
            } else {
                if (isset($data['choices'][0]['message']['content'])) {
                    $tokens = isset($data['usage']['total_tokens']) ? $data['usage']['total_tokens'] : 0;
                    return array(
                        'text'   => trim($data['choices'][0]['message']['content']),
                        'tokens' => $tokens
                    );
                }
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Intelligent Grounded Fallback Engine
     * Generates 100% verified answers using local catalog knowledge when AI is unreachable.
     */
    public static function execute_grounded_fallback($query, $lang, $matched_products, $all_products, $policies, $safety = null) {
        $lower = strtolower($query);
        $message = "";
        $recommended_cards = array();
        $target_product = !empty($matched_products) ? reset($matched_products) : null;

        // Check if query is about piles / bawasir / mulvyadh
        $is_piles_inquiry = self::contains_any($lower, array('piles', 'pile', 'bavasir', 'bawasir', 'bawaseer', 'hemorrhoid', 'haemorrhoids', 'mulvyadh', 'moolvyadh', 'बवासीर', 'मूळव्याध', 'पाइल्स', 'मुलव्याध', 'fissure'));

        // 1. Ingredients / Composition Inquiry
        if (self::contains_any($lower, array('ingredient', 'ingredients', 'whats inside', 'what is inside', 'content', 'herb', 'herbs', 'composition', ' घटक', 'साहित्य', 'सामग्री', 'घटक काय'))) {
            $has_product = self::has_product_context($lower, $target_product);
            if ($has_product && $target_product) {
                $recommended_cards[] = self::format_card_data($target_product);
                $names = implode(', ', $target_product['clean_ingredients']);

                // If user explicitly asks for milligrams / quantities
                if (self::contains_any($lower, array('mg', 'milligram', 'quantity', 'label', 'exact dosage', 'dose', 'प्रमाण', 'मात्रा'))) {
                    $detailed = implode("\n• ", $target_product['label_composition']);
                    if ($lang === 'hi') {
                        $message = "🌿 **{$target_product['name']} की प्रामाणिक संरचना (लेबल विवरण):**\n• {$detailed}\n\nयह 100% शाकाहारी वनस्पति कैप्सूल में निर्मित है।";
                    } elseif ($lang === 'mr') {
                        $message = "🌿 **{$target_product['name']} चे लेबल तपशील (प्रमाण):**\n• {$detailed}\n\nहे १००% शाकाहारी वनस्पती कॅप्सूलमध्ये उपलब्ध आहे.";
                    } else {
                        $message = "🌿 **Verified Packaging Label Composition for {$target_product['name']}:**\n• {$detailed}\n\nFormulated with 100% vegetarian plant-cellulose capsules with zero artificial fillers.";
                    }
                } else {
                    if ($lang === 'hi') {
                        $message = "🌿 **{$target_product['name']} के प्रामाणिक घटक:**\nइसमें निम्नलिखित शुद्ध आयुर्वेदिक जड़ी-बूटियां शामिल हैं: **{$names}**।\n\nसभी घटक उच्च गुणवत्ता और आयुष मानकों के अनुसार तैयार किए गए हैं।";
                    } elseif ($lang === 'mr') {
                        $message = "🌿 **{$target_product['name']} मधील घटक:**\nयामध्ये पुढील शुद्ध आयुर्वेदिक वनस्पती समाविष्ट आहेत: **{$names}**।\n\nसर्व घटक उच्च दर्जाचे व आयुष प्रमाणित आहेत.";
                    } else {
                        $message = "🌿 **Verified Ingredients in {$target_product['name']}:**\nIt contains authentic classical botanicals: **{$names}**.\n\nAll ingredients adhere strictly to our verified packaging specifications.";
                    }
                }
            } else {
                $message = self::get_generic_text('specify_product_for_ingredients', $lang);
            }
        }

        // 2. How to Use / Dosage Inquiry
        elseif (self::contains_any($lower, array('how to use', 'how should i use', 'dosage', 'how to take', 'directions', 'usage', 'sevan', 'kaise lena', 'kase ghyayche', 'वापर कसा', 'कसे घ्यावे', 'कधी घ्यावे', 'उपयोग', 'कैसे इस्तेमाल', 'इस्तेमाल कैसे', 'इस्तेमाल', 'कसा वापरावा', 'how do i use it'))) {
            $has_product = self::has_product_context($lower, $target_product);
            if ($has_product && $target_product) {
                $recommended_cards[] = self::format_card_data($target_product);
                if ($lang === 'hi') {
                    $message = "📋 **{$target_product['name']} लेने की विधि (निर्देश):**\n{$target_product['dosage']}\n\n💡 **सावधानी:** {$target_product['precautions']}";
                } elseif ($lang === 'mr') {
                    $message = "📋 **{$target_product['name']} वापरण्याची पद्धत (निर्देश):**\n{$target_product['dosage']}\n\n💡 **काळजी:** {$target_product['precautions']}";
                } else {
                    $message = "📋 **Usage Instructions for {$target_product['name']}:**\n{$target_product['dosage']}\n\n💡 **Precautions:** {$target_product['precautions']}";
                }
            } else {
                $message = self::get_generic_text('specify_product_for_dosage', $lang);
            }
        }

        // 3. Piles / Mulvyadh / Anorectal Care Inquiry (Direct high-priority intent)
        elseif ($is_piles_inquiry) {
            $piles_sku = 'AYUR-PILES-60';
            $piles_prod = isset($all_products[$piles_sku]) ? $all_products[$piles_sku] : ($target_product ? $target_product : null);
            if ($piles_prod) {
                $recommended_cards[] = self::format_card_data($piles_prod);
                if ($lang === 'hi') {
                    $message = "बवासीर (पाइल्स) व पाचन संबंधी देखभाल के लिए हमारे कैटलॉग में **{$piles_prod['name']}** सूचीबद्ध है। आप यहाँ उत्पाद विवरण देख सकते हैं।\n\n⚠️ **चिकित्सीय सूचना:** यह उत्पाद पाचन स्वास्थ्य और नियमितता के लिए एक सहायक हर्बल पूरक है। यह बवासीर को जड़ से ठीक करने का दावा नहीं करता और न ही चिकित्सीय या शल्य चिकित्सा (सर्जरी) का विकल्प है। यदि अत्यधिक दर्द, सूजन या रक्तस्राव हो तो तुरंत योग्य चिकित्सक से परामर्श लें।";
                } elseif ($lang === 'mr') {
                    $message = "मूळव्याध (पाइल्स) व पचन तक्रारींसाठी आमच्या कॅटलॉगमध्ये **{$piles_prod['name']}** सूचीबद्ध आहे. आपण येथे उत्पादनाची माहिती पाहू शकता.\n\n⚠️ **वैद्यकीय सूचना:** हे पचनक्रिया व आरोग्यासाठी एक सहायक आयुर्वेदिक उत्पादन आहे; हे आजार पूर्ण बरा करण्याचा दावा करत नाही आणि डॉक्टरी उपचारांचा पर्याय नाही. तीव्र रक्तस्त्राव किंवा वेदना असल्यास त्वरित डॉक्टरांचा सल्ला घ्या.";
                } else {
                    $message = "The Aloe Vera Triphala Piles Care Capsules are listed in our catalog for piles-related wellness. You can review the product details here.\n\n⚠️ **Medical Advisory:** This herbal formulation is a supportive wellness supplement for bowel regularity and digestive comfort. It does not claim to cure piles or replace medical/surgical clinical care. Please consult a qualified physician if experiencing acute bleeding, severe pain, or persistent symptoms.";
                }
            } else {
                $message = "The Aloe Vera Triphala Piles Care Capsules are listed in our catalog for piles-related wellness. You can review the product details here.";
            }
        }

        // 4. Product Comparison Inquiry
        elseif (self::contains_any($lower, array('compare', 'difference', 'vs', 'versus', 'तुलना', 'फरक', 'अंतर'))) {
            if (count($matched_products) >= 2) {
                $p1 = $matched_products[0];
                $p2 = $matched_products[1];
                $recommended_cards[] = self::format_card_data($p1);
                $recommended_cards[] = self::format_card_data($p2);

                if ($lang === 'hi') {
                    $message = "⚖️ **उत्पाद तुलना:**\n\n1. **{$p1['name']}** ({$p1['formatted_price']}):\n• वर्ग: {$p1['category']}\n• मुख्य लाभ: " . implode('; ', array_slice($p1['benefits'], 0, 2)) . "\n• पैक आकार: {$p1['pack_size']}\n\n2. **{$p2['name']}** ({$p2['formatted_price']}):\n• वर्ग: {$p2['category']}\n• मुख्य लाभ: " . implode('; ', array_slice($p2['benefits'], 0, 2)) . "\n• पैक आकार: {$p2['pack_size']}";
                } elseif ($lang === 'mr') {
                    $message = "⚖️ **उत्पादनांमधील तुलना:**\n\n1. **{$p1['name']}** ({$p1['formatted_price']}):\n• विभाग: {$p1['category']}\n• मुख्य फायदे: " . implode('; ', array_slice($p1['benefits'], 0, 2)) . "\n• पॅक: {$p1['pack_size']}\n\n2. **{$p2['name']}** ({$p2['formatted_price']}):\n• विभाग: {$p2['category']}\n• मुख्य फायदे: " . implode('; ', array_slice($p2['benefits'], 0, 2)) . "\n• पॅक: {$p2['pack_size']}";
                } else {
                    $message = "⚖️ **Product Comparison:**\n\n1. **{$p1['name']}** ({$p1['formatted_price']}):\n• Focus: {$p1['category']}\n• Highlights: " . implode('; ', array_slice($p1['benefits'], 0, 2)) . "\n• Form: {$p1['pack_size']}\n\n2. **{$p2['name']}** ({$p2['formatted_price']}):\n• Focus: {$p2['category']}\n• Highlights: " . implode('; ', array_slice($p2['benefits'], 0, 2)) . "\n• Form: {$p2['pack_size']}";
                }
            } else {
                $message = self::get_generic_text('comparison_hint', $lang);
            }
        }

        // 5. Price, Discount & Quantity Offers Inquiry
        elseif (self::contains_any($lower, array('price', 'cost', 'mrp', 'rate', 'discount', 'offer', 'offer price', 'किती रुपये', 'किंमत', 'दाम', 'सूट', 'ऑफर'))) {
            $has_product = self::has_product_context($lower, $target_product);
            if ($has_product && $target_product) {
                $recommended_cards[] = self::format_card_data($target_product);
                if ($lang === 'hi') {
                    $message = "💰 **{$target_product['name']} की कीमत व ऑफर्स:**\n• वर्तमान ऑफर मूल्य: **{$target_product['formatted_price']}** (MRP: {$target_product['formatted_mrp']} - {$target_product['discount']})\n• पैक का आकार: {$target_product['pack_size']}\n• कॉम्बो बचत: {$target_product['quantity_offers']}\n• स्टॉक स्थिति: " . ($target_product['in_stock'] ? 'उपलब्ध (In Stock)' : 'आउट ऑफ स्टॉक');
                } elseif ($lang === 'mr') {
                    $message = "💰 **{$target_product['name']} ची किंमत आणि ऑफर्स:**\n• ऑफर किंमत: **{$target_product['formatted_price']}** (MRP: {$target_product['formatted_mrp']} - {$target_product['discount']})\n• पॅक साईज: {$target_product['pack_size']}\n• प्रमाण ऑफर: {$target_product['quantity_offers']}\n• उपलब्धता: " . ($target_product['in_stock'] ? 'उपलब्ध (In Stock)' : 'संपले आहे');
                } else {
                    $message = "💰 **Pricing & Quantity Offers for {$target_product['name']}:**\n• Current Selling Price: **{$target_product['formatted_price']}** (MRP: {$target_product['formatted_mrp']} - {$target_product['discount']})\n• Pack Size: {$target_product['pack_size']}\n• Multi-pack Savings: {$target_product['quantity_offers']}\n• Availability: " . ($target_product['in_stock'] ? 'In Stock' : 'Out of Stock');
                }
            } else {
                $message = self::get_generic_text('all_pricing_overview', $lang);
            }
        }

        // 6. Shipping & Delivery Policy Inquiry
        elseif (self::contains_any($lower, array('delivery', 'shipping', 'courier', 'charge', 'charges', 'how many days', 'kiti divas', 'डिलिव्हरी', 'शिपिंग', 'पहुंचने में समय'))) {
            $ship = $policies['shipping'];
            if ($lang === 'hi') {
                $message = "🚚 **डिलिव्हरी व शिपिंग नीति:**\n• **मुफ्त डिलीवरी:** ₹999 से अधिक के सभी ऑर्डरों पर पूरे भारत में निःशुल्क एक्सप्रेस डिलीवरी।\n• **मानक शुल्क:** ₹999 से कम के ऑर्डर पर ₹70।\n• **डिलिव्हरी समय:** मेट्रो शहरों में 2-3 कार्यदिवस; अन्य क्षेत्रों में 3-5 कार्यदिवस।\n• **कूरियर साझेदार:** दिल्लीवरी, ब्लू डार्ट, डीटीडीसी (शिपरोकेट एक्सप्रेस)।";
            } elseif ($lang === 'mr') {
                $message = "🚚 **डिलिव्हरी आणि शिपिंग माहिती:**\n• **मोफत डिलिव्हरी:** ₹९९९ वरील सर्व ऑर्डर्सवर संपूर्ण भारतात मोफत एक्सप्रेस डिलिव्हरी।\n• **स्टँडर्ड चार्ज:** ₹९९९ खालील ऑर्डरसाठी ₹७०।\n• **डिलिव्हरी कालावधी:** मेट्रो शहरांमध्ये २-३ दिवस; इतर भागात ३-५ दिवस।\n• **कुरिअर:** दिल्लीव्हरी, ब्लू डार्ट, डीटीडीसी (शिपरोकेटद्वारे)।";
            } else {
                $message = "🚚 **Shipping & Delivery Policy:**\n• **Free Delivery:** Automatically applied on all orders above ₹999 across India.\n• **Standard Rate:** ₹70 on orders below ₹999.\n• **Timelines:** 2-3 business days in Metro cities; 3-5 business days across Rest of India.\n• **Couriers:** Dispatched via Shiprocket partner express couriers (Delhivery, BlueDart, DTDC).";
            }
        }

        // 7. Cash on Delivery (COD) & Payment Inquiry
        elseif (self::contains_any($lower, array('cod', 'cash on delivery', 'payment', 'upi', 'gpay', 'paytm', 'कैश ऑन डिलीवरी', 'सीओडी', 'पैसे कसे द्यावे'))) {
            $pay = $policies['payments'];
            if ($lang === 'hi') {
                $message = "💳 **भुगतान और कैश ऑन डिलीवरी (COD):**\n• **ऑनलाइन प्रीपेड:** रेज़रपे (UPI, GPay, PhonePe, Paytm, कार्ड, नेट बैंकिंग) द्वारा 100% सुरक्षित भुगतान।\n• **कैश ऑन डिलीवरी (COD):** भारत भर में उपलब्ध है। फर्जी ऑर्डर्स को रोकने के लिए चेकआउट के समय ₹100 की अग्रिम पुष्टि राशि (Advance Confirmation Fee) ऑनलाइन ली जाती है। बाकी शेष राशि पार्सल मिलने पर नकद दी जाती है।";
            } elseif ($lang === 'mr') {
                $message = "💳 **पेमेंट आणि कॅश ऑन डिलिव्हरी (COD):**\n• **ऑनलाइन प्रीपेड:** Razorpay (UPI, GPay, PhonePe, कार्ड्स) द्वारे १००% सुरक्षित पेमेंट।\n• **कॅश ऑन डिलिव्हरी:** सर्व पिनकोडवर उपलब्ध। बोगस ऑर्डर्स टाळण्यासाठी ₹१०० ॲडव्हान्स कन्फर्मेशन फी ऑनलाइन घेतली जाते, आणि उर्वरित रक्कम पार्सल हातात मिळाल्यावर रोख दिली जाते।";
            } else {
                $message = "💳 **Payment & Cash on Delivery (COD):**\n• **Prepaid Options:** 100% secure payment via Razorpay (UPI, Google Pay, PhonePe, Cards, Net Banking).\n• **Cash on Delivery (COD):** Available across India. To verify genuine orders and prevent fake bookings, a ₹100 COD advance confirmation fee is paid online during checkout, and the remaining order balance is paid in cash upon physical delivery.";
            }
        }

        // 8. Returns & Refunds Policy Inquiry
        elseif (self::contains_any($lower, array('return', 'refund', 'exchange', 'damaged', 'leak', 'वापसी', 'परत', 'पैसे परत'))) {
            $ret = $policies['returns_and_refunds'];
            if ($lang === 'hi') {
                $message = "🔄 **7-दिवसीय वापसी व रिफंड नीति:**\nयदि आपको कोई क्षतिग्रस्त (damaged), लीक हुआ या गलत उत्पाद प्राप्त होता है, तो डिलीवरी के 7 दिनों के भीतर अनबॉक्सिंग फोटो के साथ हमारे कस्टमर केयर से संपर्क करें। हम तुरंत 100% मुफ्त रिप्लेसमेंट या रिफंड प्रदान करेंगे।\n\n(स्वास्थ्य स्वच्छता मानकों के कारण, खोली और उपभोग की गई बोतलें वापस नहीं ली जा सकतीं।)";
            } elseif ($lang === 'mr') {
                $message = "🔄 **७-दिवसीय रिटर्न व रिफंड पॉलिसी:**\nआपल्याला खराब, फुटलेली किंवा चुकीची बाटली मिळाल्यास ७ दिवसांच्या आत अनबॉक्सिंग फोटोसह संपर्क साधा. आम्ही लगेच मोफत रिप्लेसमेंट किंवा १००% रिफंड देऊ.\n\n(स्वच्छता नियमांनुसार उघडलेली उत्पादने परत घेतली जात नाहीत.)";
            } else {
                $message = "🔄 **7-Day Hassle-Free Return & Refund Policy:**\nIf you receive a damaged, leaked, expired, or incorrect bottle, contact our team within 7 days of delivery with an unboxing photo. We will initiate a prompt free replacement or 100% refund.\n\n(For health and hygiene safety, opened and consumed wellness bottles are not eligible for return.)";
            }
        }

        // 9. Contact Support / WhatsApp Helpline Inquiry
        elseif (self::contains_any($lower, array('support', 'contact', 'whatsapp', 'phone', 'call', 'talk to human', 'customer care', 'कस्टमर केयर', 'व्हाट्सएप', 'फोन', 'संपर्क'))) {
            $care = $policies['customer_care'];
            $wa_url = Ayur_Chatbot_Config::get_whatsapp_url();
            if ($lang === 'hi') {
                $message = "📞 **भाग्योदय आयुर्वेद ग्राहक सेवा:**\n• **व्हाट्सएप हेल्पलाइन:** {$care['whatsapp']}\n• **ईमेल:** {$care['email']}\n• **समय:** सोमवार से शनिवार, सुबह 9:00 से शाम 7:00 बजे तक।\n• **पता:** नगर पंचायत सिविल अस्पताल के सामने, मंठा, जालना, महाराष्ट्र - 431504।\n\n[यहाँ क्लिक करके व्हाट्सएप पर सीधे बात करें]({$wa_url})";
            } elseif ($lang === 'mr') {
                $message = "📞 **भाग्योदय आयुर्वेद ग्राहक सहाय्य:**\n• **व्हॉट्सॲप हेल्पलाइन:** {$care['whatsapp']}\n• **ईमेल:** {$care['email']}\n• **वेळ:** सोमवार ते शनिवार, सकाळी ९ ते संध्याकाळी ७।\n• **पत्ता:** नगर पंचायत सिव्हिल हॉस्पिटल समोर, मंठा, जालना, महाराष्ट्र - ४३१५०४।\n\n[येथे क्लिक करून थेट व्हॉट्सॲपवर बोला]({$wa_url})";
            } else {
                $message = "📞 **Bhagyoday Ayurveda Customer Care:**\n• **WhatsApp Support:** {$care['whatsapp']}\n• **Email:** {$care['email']}\n• **Operating Hours:** Monday to Saturday, 9:00 AM – 7:00 PM IST\n• **Address:** Infront of Nagar Panchayat Civil Hospital, Mantha, Jalna, Maharashtra - 431504\n\n[Click here to chat directly with an expert on WhatsApp]({$wa_url})";
            }
        }

        // 10. Specific Product Recommendation by Symptom / Category
        elseif ($target_product && self::has_product_context($lower, $target_product)) {
            $recommended_cards[] = self::format_card_data($target_product);
            $clean_ings = implode(', ', $target_product['clean_ingredients']);
            $benefits   = implode('; ', array_slice($target_product['benefits'], 0, 2));

            if ($lang === 'hi') {
                $message = "🌿 आपकी आवश्यकता के लिए हमारे कैटलॉग में सत्यापित उत्पाद:\n\n**{$target_product['name']}**\n• **ऑफर मूल्य:** **{$target_product['formatted_price']}** (MRP: {$target_product['formatted_mrp']} - {$target_product['discount']})\n• **उत्पाद लिंक:** [यहाँ क्लिक करके विवरण देखें]({$target_product['permalink']})\n• **प्रमुख जड़ी-बूटियां:** {$clean_ings}\n• **मुख्य लाभ:** {$benefits}\n• **सेवन विधि:** {$target_product['dosage']}\n\n💡 **सावधानी:** {$target_product['precautions']}";
            } elseif ($lang === 'mr') {
                $message = "🌿 आपल्या गरजेनुसार आमच्या कैटलॉगमधील सत्यापित उत्पादन:\n\n**{$target_product['name']}**\n• **ऑफर किंमत:** **{$target_product['formatted_price']}** (MRP: {$target_product['formatted_mrp']} - {$target_product['discount']})\n• **उत्पादनाची लिंक:** [येथे क्लिक करून माहिती पहा]({$target_product['permalink']})\n• **प्रमुख वनस्पती:** {$clean_ings}\n• **मुख्य फायदे:** {$benefits}\n• **वापर:** {$target_product['dosage']}\n\n💡 **काळजी:** {$target_product['precautions']}";
            } else {
                $message = "🌿 Here is the verified formulation matching your wellness interest:\n\n**{$target_product['name']}**\n• **Current Price:** **{$target_product['formatted_price']}** (MRP: {$target_product['formatted_mrp']} - {$target_product['discount']})\n• **Product Link:** [View Product Details on Storefront]({$target_product['permalink']})\n• **Verified Botanicals:** {$clean_ings}\n• **Key Benefits:** {$benefits}\n• **Directions for Use:** {$target_product['dosage']}\n\n💡 **Precautions:** {$target_product['precautions']}";
            }
        }

        // 11. Fallback: Information not verified (Concise, no repeated greeting)
        else {
            Ayur_Chatbot_Knowledge::log_unresolved_question($query, 'Fallback: no keyword match');
            $wa_url = Ayur_Chatbot_Config::get_whatsapp_url();

            if ($lang === 'hi') {
                $message = "आपके इस विशेष प्रश्न के लिए मुझे हमारे कैटलॉग में सत्यापित जानकारी नहीं मिली।\n\nआप हमारे आयुर्वेदिक विशेषज्ञों से सीधे [व्हाट्सएप (+91 8888686858)]({$wa_url}) पर मार्गदर्शन प्राप्त कर सकते हैं, या हमारे किसी उत्पाद (जैसे Men Power, Slim Care Juice, Amrit Heart Juice, Piles Care), डिलीवरी या भुगतान के बारे में पूछ सकते हैं।";
            } elseif ($lang === 'mr') {
                $message = "या प्रश्नासाठी आमच्या कॅटलॉगमध्ये थेट सत्यापित माहिती उपलब्ध नाही.\n\nतुम्ही आमच्या आयुर्वेदिक तज्ज्ञांशी थेट [व्हॉट्सॲपवर (+91 8888686858)]({$wa_url}) बोलू शकता, किंवा आमच्या उत्पादनांबद्दल (उदा. Men Power, Slim Care, Piles Care), डिलिव्हरी किंवा पेमेंटबद्दल विचारू शकता.";
            } else {
                $message = "That exact question is not currently verified in our store catalog documentation.\n\nFor personalized advice or special health queries, please [chat with our Ayurvedic consultant on WhatsApp (+91 8888686858)]({$wa_url}). You can also ask me about any product's ingredients, usage instructions, pricing, or delivery options!";
            }
        }

        // Prepend safety note if non-refusal safety was detected
        if ($safety && !empty($safety['message'])) {
            $message = $safety['message'] . "\n\n" . $message;
        }

        return array(
            'message'       => $message,
            'products'      => $recommended_cards,
            'quick_replies' => self::generate_quick_replies($matched_products, $lang),
        );
    }

    /**
     * Card Data Formatter
     */
    private static function format_card_data($prod) {
        return array(
            'id'              => $prod['id'],
            'sku'             => $prod['sku'],
            'name'            => $prod['name'],
            'price'           => $prod['selling_price'],
            'formatted_price' => $prod['formatted_price'],
            'formatted_mrp'   => $prod['formatted_mrp'],
            'discount'        => $prod['discount'],
            'pack_size'       => $prod['pack_size'],
            'in_stock'        => $prod['in_stock'],
            'permalink'       => $prod['permalink'],
            'image_url'       => $prod['image_url'],
            'category'        => $prod['category'],
        );
    }

    /**
     * Extract product cards from AI text with [PRODUCT: SKU_OR_ID] markers
     */
    private static function extract_product_cards($text, $matched_products, $all_products) {
        $cards = array();
        $seen = array();

        if (preg_match_all('/\[PRODUCT:\s*([a-zA-Z0-9\-_]+)\]/i', $text, $matches)) {
            foreach ($matches[1] as $target) {
                $target = strtoupper(trim($target));
                foreach ($all_products as $sku => $prod) {
                    if ((strtoupper($sku) === $target || (string)$prod['id'] === $target) && !isset($seen[$sku])) {
                        $cards[] = self::format_card_data($prod);
                        $seen[$sku] = true;
                    }
                }
            }
        }

        // If no explicit tags were extracted, supply top matched product
        if (empty($cards) && !empty($matched_products)) {
            $cards[] = self::format_card_data(reset($matched_products));
        }

        return $cards;
    }

    private static function clean_product_tags($text) {
        return trim(preg_replace('/\[PRODUCT:\s*[a-zA-Z0-9\-_]+\]/i', '', $text));
    }

    private static function generate_quick_replies($matched_products, $lang = 'en') {
        if ($lang === 'hi') {
            return array('घटक बताएं', 'इस्तेमाल कैसे करें', 'डिलीवरी और COD', 'व्हाट्सएप सपोर्ट');
        } elseif ($lang === 'mr') {
            return array('घटक सांगा', 'कसे वापरावे', 'डिलिव्हरी आणि COD', 'व्हॉट्सॲप सपोर्ट');
        }
        return array('Ask about ingredients', 'How to use', 'Delivery & COD', 'Chat on WhatsApp');
    }

    private static function get_generic_text($key, $lang) {
        $texts = array(
            'specify_product_for_ingredients' => array(
                'en' => "Our formulations contain different herbal ingredients depending on the product. Which product would you like to know about?",
                'hi' => "हमारे प्रत्येक उत्पाद में अलग-अलग आयुर्वेदिक घटक होते हैं। आप किस उत्पाद के घटकों के बारे में जानना चाहते हैं?",
                'mr' => "आमच्या प्रत्येक उत्पादनात वेगवेगळे आयुर्वेदिक घटक आहेत. आपल्याला कोणत्या उत्पादनाचे घटक जाणून घ्यायचे आहेत?"
            ),
            'specify_product_for_dosage' => array(
                'en' => "Please follow the usage instructions on the product label. If you share the exact product name, I can help you find the listed directions.",
                'hi' => "कृपया उत्पाद के लेबल पर दिए गए निर्देशों का पालन करें। यदि आप सटीक उत्पाद का नाम बताएंगे, तो मैं उसके सही सेवन के निर्देश बता सकता हूँ।",
                'mr' => "कृपया उत्पादनाच्या लेबलवरील निर्देशांचे पालन करा. आपण उत्पादनाचे नाव सांगितल्यास मी त्याचे योग्य निर्देश सांगू शकेन."
            ),
            'comparison_hint' => array(
                'en' => "Please name any two products you wish to compare (for example: Men Power vs Amrit Heart Juice, or Slim Care vs Karela Jamun).",
                'hi' => "कृपया तुलना करने के लिए दो उत्पादों का नाम बताएं (जैसे Men Power और Amrit Juice)।",
                'mr' => "तुलना करण्यासाठी कृपया दोन उत्पादने सांगा (उदा. Slim Care आणि Karela Jamun)."
            ),
            'all_pricing_overview' => array(
                'en' => "Our core Bhagyoday Ayurveda formulations are available at a special offer price of **₹890** (MRP: ₹1,990 / Save 55%). Value 2-product combos are priced at **₹1,690** (Save 58%). Which product's details would you like?",
                'hi' => "हमारे मुख्य आयुर्वेदिक उत्पाद विशेष ऑफर मूल्य **₹890** (MRP: ₹1,990 / 55% छूट) पर उपलब्ध हैं। कॉम्बो पैक **₹1,690** पर उपलब्ध हैं। आप किस उत्पाद की जानकारी चाहते हैं?",
                'mr' => "मुख्य आयुर्वेदिक उत्पादने विशेष ऑफर किमतीत **₹८९०** (MRP: ₹१,९९०) मध्ये उपलब्ध आहेत. कॉम्बो पॅक **₹१,६९०** ला आहेत. आपल्याला कोणत्या उत्पादनाची माहिती हवी आहे?"
            )
        );

        return isset($texts[$key][$lang]) ? $texts[$key][$lang] : $texts[$key]['en'];
    }

    /**
     * Determines whether user query has specific product/condition context
     */
    private static function has_product_context($query, $product) {
        if (!$product) return false;
        $q = strtolower($query);
        $checks = array_merge(
            array(strtolower($product['name']), strtolower($product['sku'])),
            array_map('strtolower', $product['aliases'])
        );
        foreach ($checks as $item) {
            if (strlen($item) >= 3 && strpos($q, $item) !== false) {
                return true;
            }
        }
        $conditions = array('piles', 'pile', 'bavasir', 'bawasir', 'mulvyadh', 'sugar', 'diabetes', 'joint', 'knee', 'ortho', 'shallaki', 'slim', 'weight', 'heart', 'cholesterol', 'arjuna', 'shilajit', 'men power', 'stamina', 'shatavari', 'karela');
        foreach ($conditions as $cond) {
            if (strpos($q, $cond) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove greeting prefixes (Namaste, Welcome, etc.) from ongoing conversation turns
     */
    public static function strip_leading_greeting($text) {
        $patterns = array(
            '/^(?:Namaste|Namaskar|Pranam|Hello|Hi)[\s,!–—\-]+(?:🙏\s*)?(?:Welcome to Bhagyoday Ayurveda[!\.,\s]*)?(?:I(?:’|\')?m your Ayur(?:vedic)? Product Expert[!\.,\s]*)?/iu',
            '/^(?:नमस्ते|नमस्कार|प्रणाम)[\s,!–—\-]+(?:🙏\s*)?(?:भाग्योदय आयुर्वेद में आपका स्वागत है[!\.,\s]*)?/iu',
            '/^(?:Welcome to Bhagyoday Ayurveda[!\.,\s]*)/iu',
        );
        $cleaned = $text;
        foreach ($patterns as $pattern) {
            $cleaned = preg_replace($pattern, '', $cleaned);
        }
        return trim($cleaned);
    }

    private static function contains_any($haystack, array $needles) {
        foreach ($needles as $needle) {
            if (mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Sliding Window IP Rate Limiter (60-second window storing timestamps)
     */
    public static function check_rate_limit($ip) {
        $limit = Ayur_Chatbot_Config::get_rate_limit_per_minute();
        if ($limit <= 0) {
            return array('limited' => false, 'retry_after' => 0);
        }

        $window = 60; // 60 seconds window
        $now    = time();
        $key    = 'ayur_chat_rl_' . md5($ip);

        $stored = get_transient($key);
        $timestamps = is_array($stored) ? $stored : array();

        // Prune timestamps outside the 60-second window
        $valid_timestamps = array();
        foreach ($timestamps as $ts) {
            if (($now - (int)$ts) < $window) {
                $valid_timestamps[] = (int)$ts;
            }
        }

        if (count($valid_timestamps) >= $limit) {
            // Find how many seconds until the earliest timestamp leaves the window
            $oldest = min($valid_timestamps);
            $retry_after = max(1, ($oldest + $window) - $now);
            return array(
                'limited'     => true,
                'retry_after' => $retry_after,
            );
        }

        // Add current timestamp and save
        $valid_timestamps[] = $now;
        set_transient($key, $valid_timestamps, $window);

        return array(
            'limited'     => false,
            'retry_after' => 0,
        );
    }

    public static function reset_ip_rate_limit($ip) {
        $key = 'ayur_chat_rl_' . md5($ip);
        delete_transient($key);
    }

    public static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']);
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return sanitize_text_field(trim($parts[0]));
        }
        return !empty($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '127.0.0.1';
    }

    /**
     * Admin Test Connection Ajax Handler
     */
    public static function handle_admin_test_connection() {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        check_ajax_referer('ayur_chatbot_admin_nonce', 'nonce');

        $provider = isset($_POST['provider']) ? sanitize_text_field($_POST['provider']) : 'gemini';
        $api_key  = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        $model    = isset($_POST['model']) ? sanitize_text_field($_POST['model']) : 'gemini-1.5-flash';
        $endpoint = isset($_POST['endpoint']) ? esc_url_raw($_POST['endpoint']) : '';

        // Fallback to configured key if input is masked placeholder
        if (strpos($api_key, '••••') !== false || empty($api_key)) {
            $api_key = Ayur_Chatbot_Config::get_api_key();
        }

        $result = Ayur_Chatbot_Config::test_connection($provider, $api_key, $model, $endpoint);
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Admin Reindex Knowledge Ajax Handler
     */
    public static function handle_admin_reindex() {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => 'Unauthorized'), 403);
        }

        check_ajax_referer('ayur_chatbot_admin_nonce', 'nonce');

        Ayur_Chatbot_Knowledge::invalidate_cache();
        $summary = Ayur_Chatbot_Knowledge::get_knowledge_summary();

        wp_send_json_success(array(
            'message'        => 'Knowledge base reindexed successfully!',
            'products_count' => count($summary['products']),
            'timestamp'      => $summary['generated_at'],
        ));
    }
}
