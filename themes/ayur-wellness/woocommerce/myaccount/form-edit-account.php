<?php
/**
 * Edit Account Form Template
 * Premium Bhagyoday Ayurveda Architecture
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_edit_account_form');
?>

<div class="ayur-edit-account-wrap">
    <div class="ayur-orders-page-header">
        <h2 class="ayur-orders-title"><?php _e('Account Details', 'ayur-wellness'); ?></h2>
        <p class="ayur-orders-desc"><?php _e('Update your personal profile information and authentication credentials.', 'ayur-wellness'); ?></p>
    </div>

    <form class="woocommerce-EditAccountForm edit-account ayur-form-card" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>
        <?php do_action('woocommerce_edit_account_form_start'); ?>

        <div class="ayur-form-section-title">
            <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('user', '', 18) : ''; ?>
            <h3><?php _e('Personal Information', 'ayur-wellness'); ?></h3>
        </div>

        <div class="ayur-form-row-grid">
            <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                <label for="account_first_name"><?php esc_html_e('First name', 'ayur-wellness'); ?>&nbsp;<span class="required">*</span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" required />
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                <label for="account_last_name"><?php esc_html_e('Last name', 'ayur-wellness'); ?>&nbsp;<span class="required">*</span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" required />
            </p>
        </div>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="account_display_name"><?php esc_html_e('Display name', 'ayur-wellness'); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" value="<?php echo esc_attr($user->display_name); ?>" required />
            <span class="ayur-field-hint"><?php esc_html_e('This is how your name will appear on verified reviews and in the customer dashboard.', 'ayur-wellness'); ?></span>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="account_email"><?php esc_html_e('Email address', 'ayur-wellness'); ?>&nbsp;<span class="required">*</span></label>
            <input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" required />
        </p>

        <fieldset class="ayur-password-fieldset">
            <div class="ayur-form-section-title">
                <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('lock', '', 18) : ''; ?>
                <h3><?php esc_html_e('Password Change', 'ayur-wellness'); ?></h3>
            </div>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="password_current"><?php esc_html_e('Current password (leave blank to leave unchanged)', 'ayur-wellness'); ?></label>
                <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="current-password" />
            </p>
            <div class="ayur-form-row-grid">
                <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                    <label for="password_1"><?php esc_html_e('New password (leave blank to leave unchanged)', 'ayur-wellness'); ?></label>
                    <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="new-password" />
                </p>
                <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                    <label for="password_2"><?php esc_html_e('Confirm new password', 'ayur-wellness'); ?></label>
                    <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="new-password" />
                </p>
            </div>
        </fieldset>

        <?php do_action('woocommerce_edit_account_form'); ?>

        <p class="ayur-form-actions">
            <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
            <button type="submit" class="woocommerce-Button button ayur-btn-primary" name="save_account_details" value="<?php esc_attr_e('Update Details', 'ayur-wellness'); ?>">
                <span><?php esc_html_e('Update Details', 'ayur-wellness'); ?></span>
                <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('check', '', 15) : ''; ?>
            </button>
            <input type="hidden" name="action" value="save_account_details" />
        </p>

        <?php do_action('woocommerce_edit_account_form_end'); ?>
    </form>
</div>

<?php do_action('woocommerce_after_edit_account_form'); ?>
