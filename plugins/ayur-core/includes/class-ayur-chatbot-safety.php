<?php
/**
 * Bhagyoday Ayurveda AI Chatbot Safety & Compliance Guardrails
 *
 * Implements strict Ayurvedic medical safety guidelines, language detection,
 * non-prescriptive disclaimers, and grounded system prompt synthesis.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ayur_Chatbot_Safety {

    /**
     * Detect Customer Language (English, Hindi, or Marathi)
     */
    public static function detect_language($text) {
        $text = trim($text);

        // Check for Devanagari Unicode Block (\x{0900}-\x{097F})
        if (preg_match('/[\x{0900}-\x{097F}]/u', $text)) {
            // Marathi linguistic markers
            $marathi_markers = array('आहे', 'नाही', 'कसे', 'घ्यावे', 'माहिती', 'किंमत', 'डिलिव्हरी', 'काय', 'कधी', 'करावे', 'माझे', 'सांगा', 'वापर', 'मिळेल', 'औषध');
            foreach ($marathi_markers as $m) {
                if (mb_strpos($text, $m) !== false) {
                    return 'mr'; // Marathi
                }
            }
            return 'hi'; // Default Devanagari to Hindi
        }

        // Romanized Hindi / Hinglish detection
        $hinglish_markers = array('kaise', 'kya', 'kitne', 'kaise lena', 'fayde', 'kimat', 'chahiye', 'batao', 'dawa', 'khana', 'peena', 'kripya', 'namaste');
        $lower = strtolower($text);
        foreach ($hinglish_markers as $marker) {
            if (strpos($lower, $marker) !== false) {
                return 'hi';
            }
        }

        // Romanized Marathi / Marathlish detection
        $marathlish_markers = array('kase', 'ahe', 'mahit', 'kadhi', 'dya', 'sanga', 'gheycha', 'kiti');
        foreach ($marathlish_markers as $marker) {
            if (strpos($lower, $marker) !== false) {
                return 'mr';
            }
        }

        return 'en';
    }

    /**
     * Evaluate Safety Guardrails & High-Risk Medical Inquiries
     * Returns a safety warning array if a violation/risk is triggered, or null if safe to proceed.
     */
    public static function evaluate_safety($query, $lang = 'en') {
        $lower = strtolower($query);

        // 1. Critical Medical Emergencies (Chest pain, heart attack, stroke, acute severe symptoms)
        $emergency_terms = array(
            'heart attack', 'chest pain', 'stroke', 'emergency', 'paralysis', 'choking', 'poison', 'suicide', 'unconscious', 'breathing stop',
            'छाती में दर्द', 'हार्ट अटैक', 'छातीत दुखत', 'स्ट्रोक', 'हृदयविकार'
        );
        foreach ($emergency_terms as $term) {
            if (mb_strpos($lower, $term) !== false) {
                return array(
                    'triggered' => true,
                    'type'      => 'emergency',
                    'message'   => self::get_safety_message('emergency', $lang),
                    'refusal'   => true
                );
            }
        }

        // 2. Prescription Medication Discontinuation (Stopping insulin, BP meds, etc.)
        if (preg_match('/\b(stop|leave|discontinue|replace|quit)\b.{0,40}\b(medicine|medication|medicines|medications|insulin|prescription|allopathy|tablet|tablets|pill|pills|doctor)\b/i', $query)) {
            return array(
                'triggered' => true,
                'type'      => 'stop_medication',
                'message'   => self::get_safety_message('stop_medication', $lang),
                'refusal'   => true
            );
        }

        $stop_med_terms = array(
            'stop insulin', 'stop my insulin', 'stop taking insulin', 'leave insulin', 'stop injection',
            'stop my medicine', 'stop medicine', 'leave my medicine', 'leave medicine', 'stop allopathy',
            'stop taking my tablets', 'stop tablets', 'replace my insulin', 'replace insulin',
            'stop prescription', 'stop my prescription', "stop doctor's prescription", 'stop doctor prescription',
            'stop medication', 'stop my medication', 'stop medications', 'stop taking prescription', 'stop taking medication',
            'replace prescription', 'replace medication', 'replace my doctor', 'leave prescription',
            'band kar du', 'dawai band', 'goli band', 'aushadh thambva', 'thambvu ka',
            'दवाई बंद', 'दवा बंद', 'गोली बंद', 'दवाईयाँ बंद', 'औषध बंद', 'औषधे थांबवू', 'थांबवू का'
        );
        foreach ($stop_med_terms as $term) {
            if (mb_strpos($lower, $term) !== false) {
                return array(
                    'triggered' => true,
                    'type'      => 'stop_medication',
                    'message'   => self::get_safety_message('stop_medication', $lang),
                    'refusal'   => true
                );
            }
        }

        // 3. Absolute Disease Cure Claims (Claiming guaranteed cure for diabetes, cancer, arthritis)
        $cure_terms = array(
            'cure diabetes permanently', 'cure cancer', '100% cure', 'guaranteed cure', 'jad se khatam', 'pura thik ho jayega', 'permanently cure',
            'जड़ से खत्म', 'जड से खत्म', '१००% ठीक', '100% ठीक', 'पूर्ण बरा', 'नक्की बरा'
        );
        foreach ($cure_terms as $term) {
            if (mb_strpos($lower, $term) !== false) {
                return array(
                    'triggered' => true,
                    'type'      => 'cure_claim',
                    'message'   => self::get_safety_message('cure_claim', $lang),
                    'refusal'   => false
                );
            }
        }

        return null;
    }

    /**
     * Standard Safety Messages in Supported Languages
     */
    public static function get_safety_message($type, $lang = 'en') {
        $messages = array(
            'emergency' => array(
                'en' => "⚠️ **Medical Alert:** Your query mentions symptoms that may require urgent medical attention. Bhagyoday Ayurveda formulations are dietary herbal wellness supplements, not emergency treatments. Please seek immediate emergency medical care or consult a doctor right away.",
                'hi' => "⚠️ **चिकित्सीय सूचना:** आपके लक्षण तुरंत आपातकालीन चिकित्सीय सहायता की मांग करते हैं। भाग्योदय आयुर्वेद के उत्पाद प्राकृतिक पूरक हैं, आपातकालीन उपचार नहीं। कृपया तुरंत नजदीकी अस्पताल या चिकित्सक से परामर्श लें।",
                'mr' => "⚠️ **वैद्यकीय सूचना:** तुमची लक्षणे तातडीने डॉक्टरांच्या मदतीची आवश्यकता दर्शवतात. भाग्योदय आयुर्वेदाची उत्पादने नैसर्गिक आहार पूरक आहेत, आपत्कालीन उपचार नाहीत. कृपया ताबडतोब वैद्यकीय सल्ला घ्या."
            ),
            'stop_medication' => array(
                'en' => "⚠️ **Safety Guidance:** Never discontinue or alter doctor-prescribed medications (such as insulin or cardiovascular drugs) without direct supervision from your treating physician. Our classical formulations are designed for supportive lifestyle wellness and work best in harmony with qualified medical guidance.",
                'hi' => "⚠️ **सुरक्षा मार्गदर्शन:** अपने डॉक्टर द्वारा निर्धारित किसी भी दवा (जैसे इंसुलिन या बीपी की गोलियां) को अपने आप बंद न करें। हमारे आयुर्वेदिक उत्पाद स्वास्थ्य संतुलन के लिए प्राकृतिक पूरक हैं। किसी भी बदलाव से पहले अपने चिकित्सक से सलाह लें।",
                'mr' => "⚠️ **सुरक्षा मार्गदर्शन:** डॉक्टरांनी लिहून दिलेली औषधे (उदा. इन्सुलिन किंवा बीपीची औषधे) परस्पर कधीही थांबवू नका. आमची उत्पादने जीवनशैलीतील निरोगीपणासाठी आहेत. कोणताही बदल करण्यापूर्वी तुमच्या डॉक्टरांचा सल्ला घ्या."
            ),
            'cure_claim' => array(
                'en' => "🌿 **Truth in Ayurveda:** Authentic Ayurveda focuses on holistic metabolic balance, daily Agni, and long-term vitality rather than synthetic quick fixes. While our products contain verified classical herbs to support natural wellness, they are dietary supplements and not intended to diagnose, cure, or guarantee the prevention of any chronic disease.",
                'hi' => "🌿 **सच्ची आयुर्वेदिक भूमिका:** प्रामाणिक आयुर्वेद किसी भी बीमारी के 'जादुई इलाज' का दावा नहीं करता, बल्कि शरीर के दोषों और चयापचय (Agni) को संतुलित करने में मदद करता है। हमारे उत्पाद शुद्ध शास्त्रीय जड़ी-बूटियों से बने प्राकृतिक पूरक हैं।",
                'mr' => "🌿 **खरे आयुर्वेदिक तत्त्व:** खरा आयुर्वेद कोणताही 'चमत्कारी बरा होण्याचा' दावा करत नाही, तर शरीराचा समतोल राखण्यास मदत करतो. आमची उत्पादने शास्त्रीय औषधी वनस्पतींपासून बनवलेली नैसर्गिक पूरक आहेत."
            )
        );

        if (isset($messages[$type][$lang])) {
            return $messages[$type][$lang];
        }
        return isset($messages[$type]['en']) ? $messages[$type]['en'] : '';
    }

    /**
     * Build Grounded System Prompt for AI Engine
     */
    public static function build_system_prompt($language = 'en', $matched_products = array(), $store_policies = array()) {
        $lang_instruction = "Respond naturally in English.";
        if ($language === 'hi') {
            $lang_instruction = "IMPORTANT: The user is communicating in Hindi (हिंदी). Respond in polite, clear Hindi using Devanagari script (or clean conversational Hindi).";
        } elseif ($language === 'mr') {
            $lang_instruction = "IMPORTANT: The user is communicating in Marathi (मराठी). Respond in polite, authentic Marathi using Devanagari script.";
        }

        // Format Product Catalog Snippet
        $catalog_text = "";
        foreach ($matched_products as $p) {
            $clean_ings = implode(', ', $p['clean_ingredients']);
            $benefits   = implode('; ', $p['benefits']);
            $catalog_text .= "PRODUCT:\n";
            $catalog_text .= "- Title: {$p['name']}\n";
            $catalog_text .= "- SKU: {$p['sku']} | ID: {$p['id']}\n";
            $catalog_text .= "- Price: {$p['formatted_price']} (MRP: {$p['formatted_mrp']} - {$p['discount']})\n";
            $catalog_text .= "- Pack Size: {$p['pack_size']}\n";
            $catalog_text .= "- Verified Ingredients (Botanicals): {$clean_ings}\n";
            $catalog_text .= "- Benefits: {$benefits}\n";
            $catalog_text .= "- Dosage: {$p['dosage']}\n";
            $catalog_text .= "- Precautions: {$p['precautions']}\n";
            $catalog_text .= "- Quantity Offers: {$p['quantity_offers']}\n";
            $catalog_text .= "- URL: {$p['permalink']}\n\n";
        }

        $shipping_info = "Free Express Delivery on orders above ₹999 across India. ₹70 flat shipping on orders below ₹999. Dispatched within 24-48 hours via Shiprocket express couriers (Delhivery, BlueDart, DTDC). Metro delivery 2-3 days, rest of India 3-5 days.";
        $payment_info  = "Payment Options: 100% Prepaid via Razorpay (UPI, Google Pay, PhonePe, Cards, Netbanking) OR Cash on Delivery (COD). For COD, a ₹100 advance confirmation fee is collected online to verify delivery, and the remaining balance is paid in cash upon delivery.";
        $return_info   = "Returns & Refunds: 7-day hassle-free replacement or refund for damaged, leaked, or wrong items with photo proof within 7 days of delivery. Opened consumable tonics cannot be returned for hygiene reasons.";
        $support_info  = "Customer Care: WhatsApp +91 8888686858 | Email: vijaykulal8833@gmail.com | Hours: Mon-Sat 9AM-7PM IST. Corporate: Mantha, Jalna, Maharashtra - 431504.";

        $prompt = <<<EOT
You are the official Bhagyoday Ayurveda AI Product Expert Chatbot for https://bhagyodayayurveda.in.
Your mission is to assist customers with authentic product information, ingredients, usage directions, and store policies using strictly verified data.

{$lang_instruction}

STRICT AYURVEDA SAFETY INVARIANTS:
1. You are a product-information and shopping assistant, NOT a doctor or medical practitioner.
2. NEVER diagnose illnesses, prescribe medications, or guarantee disease cures/reversals/prevention.
3. NEVER instruct a customer to stop or alter doctor-prescribed pharmaceuticals (such as insulin, blood pressure drugs, or heart medicines).
4. For questions on diabetes, heart conditions, pregnancy, or prescription drug interactions, emphasize lifestyle wellness and advise consulting their treating physician.
5. COMPOSITION POLICY: Display ingredient names cleanly WITHOUT milligram (mg) numbers by default. Only provide milligram quantities if the customer explicitly requests quantitative label specifications.
6. NEVER fabricate unverified herbs, dosages, prices, discounts, fake testimonials, or stock urgency.
7. If an answer or product is not in the verified catalog below, clearly state that the information is not verified and invite them to connect with our Ayurvedic experts on WhatsApp (+91 8888686858).
8. ZERO force-selling: Help the customer understand which formulation aligns with their lifestyle routine.

STORE POLICIES KNOWLEDGE:
- Shipping: {$shipping_info}
- Payment & COD: {$payment_info}
- Returns: {$return_info}
- Customer Support: {$support_info}

VERIFIED STORE CATALOG DATA:
{$catalog_text}

OUTPUT FORMAT:
- Be warm, respectful (Namaste / प्रणाम / नमस्कार), concise, and informative.
- When recommending a product, refer to it clearly by name and price so the system can present its interactive card.
- At the end of your response, if a product matches, provide a reference tag: `[PRODUCT: SKU_OR_ID]` (e.g. `[PRODUCT: AYUR-SHIL-30]`).
EOT;

        return $prompt;
    }
}
