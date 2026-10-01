<?php
/**
 * Login / Register Form Template
 * Premium Bhagyoday Ayurveda Architecture
 * Centered Card Layout (420–480px)
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_customer_login_form');

$can_register = 'yes' === get_option('woocommerce_enable_myaccount_registration');
?>

<div class="ayur-login-page-wrap">
    <div class="ayur-login-card">
        <div class="ayur-login-card-header">
            <div class="ayur-login-badge" aria-hidden="true">
                <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('leaf', '', 28) : ''; ?>
            </div>
            <h2 class="ayur-login-card-title"><?php _e('Bhagyoday Ayurveda', 'ayur-wellness'); ?></h2>
            <p class="ayur-login-card-subtitle"><?php _e('Access your orders, tracked deliveries, and saved remedies.', 'ayur-wellness'); ?></p>

            <?php if ($can_register) : ?>
                <div class="ayur-auth-tabs" role="tablist">
                    <button type="button" class="ayur-auth-tab active" id="ayurTabLogin" role="tab" aria-selected="true" aria-controls="ayurPanelLogin">
                        <?php _e('Sign In', 'ayur-wellness'); ?>
                    </button>
                    <button type="button" class="ayur-auth-tab" id="ayurTabRegister" role="tab" aria-selected="false" aria-controls="ayurPanelRegister">
                        <?php _e('Create Account', 'ayur-wellness'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div class="ayur-login-card-body">
            <!-- LOGIN PANEL -->
            <div class="ayur-auth-panel active" id="ayurPanelLogin" role="tabpanel" aria-labelledby="ayurTabLogin">
                <form class="woocommerce-form woocommerce-form-login login ayur-auth-form" method="post">
                    <?php do_action('woocommerce_login_form_start'); ?>

                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="username"><?php esc_html_e('Username or email address', 'ayur-wellness'); ?>&nbsp;<span class="required">*</span></label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required />
                    </p>

                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="password"><?php esc_html_e('Password', 'ayur-wellness'); ?>&nbsp;<span class="required">*</span></label>
                        <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required />
                    </p>

                    <?php do_action('woocommerce_login_form'); ?>

                    <div class="ayur-login-meta-row">
                        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                            <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                            <span><?php esc_html_e('Remember me', 'ayur-wellness'); ?></span>
                        </label>
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="woocommerce-LostPassword lost_password">
                            <?php esc_html_e('Forgot password?', 'ayur-wellness'); ?>
                        </a>
                    </div>

                    <p class="form-row">
                        <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                        <button type="submit" class="woocommerce-button button woocommerce-form-login__submit ayur-btn-primary ayur-btn-block" name="login" value="<?php esc_attr_e('Sign In', 'ayur-wellness'); ?>">
                            <span><?php esc_html_e('SIGN IN', 'ayur-wellness'); ?></span>
                            <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('arrow-right', '', 14) : ''; ?>
                        </button>
                    </p>

                    <?php if (!$can_register) : ?>
                        <div class="ayur-login-register-note">
                            <span><?php esc_html_e('New to Bhagyoday Ayurveda?', 'ayur-wellness'); ?></span>
                            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php esc_html_e('Explore our Ayurvedic wellness formulations &rarr;', 'ayur-wellness'); ?></a>
                        </div>
                    <?php endif; ?>

                    <?php do_action('woocommerce_login_form_end'); ?>
                </form>
            </div>

            <?php if ($can_register) : ?>
                <!-- REGISTER PANEL -->
                <div class="ayur-auth-panel" id="ayurPanelRegister" role="tabpanel" aria-labelledby="ayurTabRegister" style="display: none;">
                    <form method="post" class="woocommerce-form woocommerce-form-register register ayur-auth-form" <?php do_action('woocommerce_register_form_tag'); ?>>
                        <?php do_action('woocommerce_register_form_start'); ?>

                        <?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                <label for="reg_username"><?php esc_html_e('Username', 'ayur-wellness'); ?>&nbsp;<span class="required">*</span></label>
                                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required />
                            </p>
                        <?php endif; ?>

                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="reg_email"><?php esc_html_e('Email address', 'ayur-wellness'); ?>&nbsp;<span class="required">*</span></label>
                            <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" required />
                        </p>

                        <?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                <label for="reg_password"><?php esc_html_e('Password', 'ayur-wellness'); ?>&nbsp;<span class="required">*</span></label>
                                <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required />
                            </p>
                        <?php else : ?>
                            <p class="ayur-reg-notice"><?php esc_html_e('A secure password link will be dispatched to your email address.', 'ayur-wellness'); ?></p>
                        <?php endif; ?>

                        <?php do_action('woocommerce_register_form'); ?>

                        <p class="woocommerce-form-row form-row">
                            <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                            <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit ayur-btn-primary ayur-btn-block" name="register" value="<?php esc_attr_e('Register', 'ayur-wellness'); ?>">
                                <span><?php esc_html_e('CREATE ACCOUNT', 'ayur-wellness'); ?></span>
                                <?php echo function_exists('ayur_svg_icon') ? ayur_svg_icon('arrow-right', '', 14) : ''; ?>
                            </button>
                        </p>

                        <?php do_action('woocommerce_register_form_end'); ?>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabLogin = document.getElementById('ayurTabLogin');
    const tabRegister = document.getElementById('ayurTabRegister');
    const panelLogin = document.getElementById('ayurPanelLogin');
    const panelRegister = document.getElementById('ayurPanelRegister');

    if (tabLogin && tabRegister && panelLogin && panelRegister) {
        tabLogin.addEventListener('click', function() {
            tabLogin.classList.add('active');
            tabLogin.setAttribute('aria-selected', 'true');
            tabRegister.classList.remove('active');
            tabRegister.setAttribute('aria-selected', 'false');
            panelLogin.style.display = 'block';
            panelLogin.classList.add('active');
            panelRegister.style.display = 'none';
            panelRegister.classList.remove('active');
        });

        tabRegister.addEventListener('click', function() {
            tabRegister.classList.add('active');
            tabRegister.setAttribute('aria-selected', 'true');
            tabLogin.classList.remove('active');
            tabLogin.setAttribute('aria-selected', 'false');
            panelRegister.style.display = 'block';
            panelRegister.classList.add('active');
            panelLogin.style.display = 'none';
            panelLogin.classList.remove('active');
        });
    }
});
</script>

<?php do_action('woocommerce_after_customer_login_form'); ?>
