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

        // Format Product Catalog Snippet with Complete Ingredients
        $catalog_text = "";
        foreach ($matched_products as $p) {
            $canonical  = Ayur_Chatbot_Knowledge::get_canonical_ingredient_record($p['sku']);
            $ings_list  = "";
            if ($canonical && !empty($canonical['items'])) {
                foreach ($canonical['items'] as $item) {
                    $ings_list .= "  • " . $item['name'] . (!empty($item['quantity']) ? " ({$item['quantity']})" : "") . "\n";
                }
            } else {
                $ings_list = "  • " . implode("\n  • ", $p['clean_ingredients']) . "\n";
            }

            $benefits   = implode('; ', $p['benefits']);
            $catalog_text .= "PRODUCT:\n";
            $catalog_text .= "- Title: {$p['name']}\n";
            $catalog_text .= "- SKU: {$p['sku']} | ID: {$p['id']}\n";
            $catalog_text .= "- Price: {$p['formatted_price']} (MRP: {$p['formatted_mrp']} - {$p['discount']})\n";
            $catalog_text .= "- Pack Size: {$p['pack_size']}\n";
            $catalog_text .= "- Complete Verified Ingredients (from packaging label):\n{$ings_list}";
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

CONVERSATION & GREETING RULES (MANDATORY):
1. The welcome greeting is already presented separately by the frontend interface.
2. DO NOT begin ANY answer with "Namaste", "Welcome", "Welcome to Bhagyoday Ayurveda", "नमस्ते", "नमस्कार", "प्रणाम", or company introductions.
3. Start answering the customer's question directly from the very first word.
4. Keep answers concise, helpful, and focused. Do not generate unnecessarily long walls of text.
5. If the customer asks a general question without specifying a product:
   - About ingredients (e.g. "Ask about ingredients"): Answer concisely that our formulations contain different herbal ingredients depending on the product, and ask which product they would like to know about. Do NOT append a product card or list all products.
   - About usage (e.g. "How to use" or "How do I use it?"): Advise them to follow the directions on the product label and ask for the exact product name so you can provide the listed instructions. Do NOT guess a product or append an unrelated card.
6. Only output a product reference tag `[PRODUCT: SKU_OR_ID]` when the customer's question is specifically about that product or condition (such as piles). Never append unrelated product cards.

STRICT AYURVEDA SAFETY INVARIANTS:
1. You are a product-information and shopping assistant, NOT a doctor or medical practitioner.
2. NEVER diagnose illnesses, prescribe medications, or guarantee disease cures/reversals/prevention.
3. NEVER instruct a customer to stop or alter doctor-prescribed pharmaceuticals (such as insulin, blood pressure drugs, or heart medicines).
4. For questions on diabetes, heart conditions, pregnancy, or prescription drug interactions, emphasize lifestyle wellness and advise consulting their treating physician.
5. INGREDIENTS POLICY (AUTHORITATIVE): When the customer asks about ingredients of a product ("What are the ingredients?", "List all ingredients", "What does this product contain?", "Tell me all ingredients in Amrit Heart Juice"), return the COMPLETE, VERIFIED list of all ingredients documented for that exact product under a clear product heading, with verified quantities as documented on the packaging label.
   - For Amrit Heart Health Immunity Juice, it contains the 60 herbs, fruits and extracts specified in the catalog — NEVER abbreviate to 6 herbs or invent ingredients.
   - Never mix ingredients across products.
   - Separate ingredients from dosage or precautions; only provide dosage if specifically requested.
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

PRODUCT RECOMMENDATION GUIDELINES:
- When a customer asks which product is for piles, hemorrhoids, bawasir, or anorectal/bowel care, explicitly identify and recommend: **Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)** ([PRODUCT: AYUR-PILES-60]).
  Your response MUST include:
  1. Actual Product Name: Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules).
  2. Current Price & Storefront Link: ₹890 (https://bhagyodayayurveda.in/product/aloe-vera-triphala-piles-care-capsules/).
  3. Verified Ingredients: Kumari (Aloe Vera), Triphala (Amalaki, Haritaki, Bibhitaki), Nagkesar, Neem, Jimikand (Suran).
  4. Concise Explanation: Classical Ayurvedic botanicals that facilitate smooth, effortless bowel evacuation, soothe inflamed tissues, and support anorectal vascular tone.
  5. Clear Non-Cure Disclaimer: This is an Ayurvedic dietary wellness supplement, not intended to cure piles or replace medical/surgical care. Advise consulting a doctor if bleeding or severe pain persists.
- For Hindi queries on piles/बवासीर, provide the exact details in Hindi: उत्पाद का नाम **Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)**, कीमत ₹890, लिंक, घटक (कुमारी, त्रिफला, नागकेशर, नीम, जिमीकंद), और स्पष्ट गैर-चिकित्सीय सलाह।
- For Marathi queries on मूळव्याध/पाइल्स, provide the exact details in Marathi: उत्पादनाचे नाव **Aloe Vera Triphala Piles Care Capsules 500mg (60 Capsules)**, किंमत ₹८९०, लिंक, शास्त्रीय घटक (कोरफड, त्रिफळा, नागकेशर, कडुलिंब, सुरण), आणि वैद्यकीय सल्ल्याची सूचना।
- NEVER deflect or reply with an empty greeting when asked a product question. Always answer the question with the verified product details from the catalog below.

OUTPUT FORMAT:
- Be direct, polite, concise, and informative.
- NEVER start with greetings or welcoming phrases like 'Namaste', 'Welcome', 'प्रणाम', or 'नमस्कार'.
- When recommending a product, refer to it clearly by name, price, and storefront link so the customer has complete verified information.
- At the end of your response, provide the reference tag: `[PRODUCT: SKU_OR_ID]` (e.g. `[PRODUCT: AYUR-PILES-60]`).
EOT;

        return $prompt;
    }
}
