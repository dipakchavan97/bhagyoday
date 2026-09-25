<?php
/**
 * Footer template for Ayur Wellness
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<footer class="ayur-footer">
    <!-- Bottom Trust Bar -->
    <div class="ayur-container" style="padding-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 40px;">
        <div class="ayur-trust-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="ayur-trust-item">
                <div class="ayur-trust-icon" style="background:rgba(197, 160, 89, 0.15); color:#C5A059;">🌿</div>
                <div>
                    <div class="ayur-trust-title" style="color:#FFFFFF;"><?php _e('Classical Ayurveda', 'ayur-wellness'); ?></div>
                    <div class="ayur-trust-desc"><?php _e('100% Authentic herbal extracts', 'ayur-wellness'); ?></div>
                </div>
            </div>
            <div class="ayur-trust-item">
                <div class="ayur-trust-icon" style="background:rgba(197, 160, 89, 0.15); color:#C5A059;">🏭</div>
                <div>
                    <div class="ayur-trust-title" style="color:#FFFFFF;"><?php _e('GMP & AYUSH Certified', 'ayur-wellness'); ?></div>
                    <div class="ayur-trust-desc"><?php _e('Zero heavy metals & fillers', 'ayur-wellness'); ?></div>
                </div>
            </div>
            <div class="ayur-trust-item">
                <div class="ayur-trust-icon" style="background:rgba(197, 160, 89, 0.15); color:#C5A059;">🚚</div>
                <div>
                    <div class="ayur-trust-title" style="color:#FFFFFF;"><?php _e('Free Shipping > ₹999', 'ayur-wellness'); ?></div>
                    <div class="ayur-trust-desc"><?php _e('Fast delivery via Delhivery / Bluedart', 'ayur-wellness'); ?></div>
                </div>
            </div>
            <div class="ayur-trust-item">
                <div class="ayur-trust-icon" style="background:rgba(197, 160, 89, 0.15); color:#C5A059;">🛡️</div>
                <div>
                    <div class="ayur-trust-title" style="color:#FFFFFF;"><?php _e('COD & UPI Available', 'ayur-wellness'); ?></div>
                    <div class="ayur-trust-desc"><?php _e('100% Encrypted & Secure Checkout', 'ayur-wellness'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main 4-Column Footer -->
    <div class="ayur-container">
        <div class="ayur-footer-grid">
            <!-- Brand & Story -->
            <div>
                <div class="ayur-brand" style="color:#FFFFFF; margin-bottom:15px;">
                    <span class="ayur-brand-leaf">🌿</span>
                    <div>VEDA WELLNESS</div>
                </div>
                <p style="line-height:1.7; font-size:13px; color:#9CA3AF; margin-bottom:18px;">
                    Dedicated to reviving authentic 5,000-year-old Vedic formulations for contemporary daily wellness. Ethical herb harvesting, lab-standardized extraction, and pure mind-body balance.
                </p>
                <div style="font-size:12px; color:#C5A059; font-weight:600;">
                    AYUSH Licensed Facility • Heavy Metal Tested
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4><?php _e('Explore Remedies', 'ayur-wellness'); ?></h4>
                <ul class="ayur-footer-links">
                    <li><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php _e('All Ayurvedic Products', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('immunity')); ?>"><?php _e('Immunity & Vitality', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('hair-care')); ?>"><?php _e('Hair & Scalp Rituals', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('skin-care')); ?>"><?php _e('Pure Skin Nourishment', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(ayur_get_cat_url('combos')); ?>"><?php _e('Curated Wellness Combos', 'ayur-wellness'); ?></a></li>
                </ul>
            </div>

            <!-- Trust & Policies -->
            <div>
                <h4><?php _e('Customer Trust', 'ayur-wellness'); ?></h4>
                <ul class="ayur-footer-links">
                    <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>"><?php _e('Our Sourcing & Standards', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/shipping-policy/')); ?>"><?php _e('Shipping & Delivery Policy', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/refund-policy/')); ?>"><?php _e('7-Day Refund Policy', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>"><?php _e('Terms & Conditions', 'ayur-wellness'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php _e('Privacy & Data Protection', 'ayur-wellness'); ?></a></li>
                </ul>
            </div>

            <!-- Contact & Help -->
            <div>
                <h4><?php _e('Expert Support', 'ayur-wellness'); ?></h4>
                <p style="font-size:13px; color:#D1D5DB; margin-bottom:12px;">
                    Have questions about dosages, herbal interactions, or order tracking?
                </p>
                <div style="font-size:13px; line-height:1.8;">
                    <div>📱 <strong>WhatsApp:</strong> +91 98765 43210</div>
                    <div>✉️ <strong>Email:</strong> care@ayurvedicwellness.in</div>
                    <div>🕒 <strong>Hours:</strong> Mon - Sat (9am - 7pm IST)</div>
                </div>
            </div>
        </div>

        <!-- Statutory Disclaimer & Copyright -->
        <div class="ayur-footer-bottom">
            <div>
                <p style="margin:0 0 4px 0;">&copy; <?php echo date('Y'); ?> Veda Wellness India. All rights reserved.</p>
                <p style="font-size:11px; color:#6B7280; margin:0;">
                    *Disclaimer: Ayurvedic food supplements and classical preparations are intended to maintain wellness and support vitality; not intended to diagnose, treat, cure, or prevent any clinical disease.
                </p>
            </div>
            <div class="ayur-pay-badges">
                <span class="ayur-pay-badge">UPI / GPay</span>
                <span class="ayur-pay-badge">PhonePe</span>
                <span class="ayur-pay-badge">Paytm</span>
                <span class="ayur-pay-badge">Cards / RuPay</span>
                <span class="ayur-pay-badge">Cash on Delivery</span>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
