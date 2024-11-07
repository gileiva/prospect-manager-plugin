<?php

namespace ProspectM;
defined('ABSPATH') || exit;

/**
 * Class responsible for generating the form and handling form submissions.
 */
class FormHandler {

    const MAX_ATTEMPTS = 5;
    const BLOCK_TIME = 60 * 5; // 5 minutes

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_submit_prospect_form', [$this, 'handle_ajax_submission']);
        add_action('wp_ajax_nopriv_submit_prospect_form', [$this, 'handle_ajax_submission']);
        add_action('init', [$this, 'handle_form_submission']); 
    }

    /**
     * Enqueues the JavaScript file for handling the AJAX form submission.
     */
    public function enqueue_scripts() {
        $enable_recaptcha = get_option('prospect_manager_enable_recaptcha', 'no');
        if ($enable_recaptcha === 'yes') {
            wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
        }
        
        wp_enqueue_style('normalize-css', 'https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.1/normalize.css');
        wp_enqueue_style(
            'floating-button-css',
            plugins_url('../assets/css/floating-button.css', __FILE__)
        );
        wp_enqueue_script(
            'prospect-form-js',
            plugins_url('../assets/js/prospect-form.js', __FILE__),
            ['jquery'],
            null,
            true
        );
        wp_localize_script('prospect-form-js', 'prospectFormAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('prospect_form_submit'),
            'enable_recaptcha' => $enable_recaptcha,
        ]);
    }
    
    /**
     * Generates the HTML form.
     *
     * @return string The form HTML.
     */
    public function render_form() {
        $enable_recaptcha = get_option('prospect_manager_enable_recaptcha', 'no');
        $recaptcha_site_key = get_option('prospect_manager_recaptcha_site_key', '');        

        ob_start();
        ?>
        <form id="prospect-form" method="post" action="" class="prospect-form">
            <?php wp_nonce_field('prospect_form_submit', 'prospect_form_nonce'); ?>
            <div class="form-group">
                <label for="full_name"><?php _e('Full Name', 'prospect-manager-plugin'); ?></label>
                <input type="text" id="full_name" name="full_name" required class="form-control" />
            </div>
            <div class="form-group">
                <label for="email"><?php _e('Email', 'prospect-manager-plugin'); ?></label>
                <input type="email" id="email" name="email" required class="form-control" />
            </div>
            <div class="form-group">
                <label for="whatsapp_number"><?php _e('WhatsApp Number', 'prospect-manager-plugin'); ?></label>
                <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control" />
            </div>
            <div class="form-group">
                <label for="contact_preference"><?php _e('How do you prefer to be contacted?', 'prospect-manager-plugin'); ?></label>
                <div class="radio-group">
                    <label><input type="radio" name="contact_preference" value="email" required> <?php _e('By email', 'prospect-manager-plugin'); ?></label>
                    <label><input type="radio" name="contact_preference" value="whatsapp"> <?php _e('By WhatsApp', 'prospect-manager-plugin'); ?></label>
                    <label><input type="radio" name="contact_preference" value="virtual_meeting"> <?php _e('Schedule a virtual meeting', 'prospect-manager-plugin'); ?></label>
                </div>
            </div>
            <div class="form-group">
                <label for="inquiry"><?php _e('Inquiry', 'prospect-manager-plugin'); ?></label>
                <textarea id="inquiry" name="inquiry" rows="5" required class="form-control"></textarea>
            </div>
            <?php if ($enable_recaptcha === 'yes' && !empty($recaptcha_site_key)) : ?>
                <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($recaptcha_site_key); ?>"></div>
                <button type="submit" name="submit_prospect_form" class="btn btn-primary">
                    <?php _e('Submit', 'prospect-manager-plugin'); ?>
                </button>
            <?php else : ?>
                <button type="submit" name="submit_prospect_form" class="btn btn-primary">
                    <?php _e('Submit', 'prospect-manager-plugin'); ?>
                </button>
            <?php endif; ?>
        </form>

        <div id="form-response" style="margin-top: 15px;"></div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handles form submission for non-AJAX submissions (regular form post).
     */
    public function handle_form_submission() {
        if (isset($_POST['submit_prospect_form'])) {
            if (!isset($_POST['prospect_form_nonce']) || !wp_verify_nonce($_POST['prospect_form_nonce'], 'prospect_form_submit')) {
                wp_die(__('Nonce verification failed.', 'prospect-manager-plugin'));
            }

            $post_data = [
                'post_title'   => sanitize_text_field($_POST['full_name']),
                'post_type'    => 'prospect',
                'post_status'  => 'publish',
                'meta_input'   => [
                    'full_name'          => sanitize_text_field($_POST['full_name']),
                    'email'              => sanitize_email($_POST['email']),
                    'whatsapp_number'    => sanitize_text_field($_POST['whatsapp_number']),
                    'contact_preference' => sanitize_text_field($_POST['contact_preference']),
                    'inquiry'            => sanitize_textarea_field($_POST['inquiry']),
                ],
            ];

            $post_id = wp_insert_post($post_data);

            if (!is_wp_error($post_id)) {
                wp_mail(
                    sanitize_email($_POST['email']),
                    __('Thank you for your inquiry', 'prospect-manager-plugin'),
                    __('We have received your inquiry and will contact you shortly.', 'prospect-manager-plugin')
                );

                wp_send_json_success(__('Your inquiry has been successfully submitted.', 'prospect-manager-plugin'));
            } else {
                wp_send_json_error(__('There was an error submitting your inquiry. Please try again.', 'prospect-manager-plugin'));
            }

            wp_die();
        }
    }

    /**
     * Handles the AJAX form submission and saves the data to the CPT.
     */
    public function handle_ajax_submission() {
        check_ajax_referer('prospect_form_submit', 'nonce');

        $user_ip = $_SERVER['REMOTE_ADDR'];
        $attempts = get_transient('form_attempts_' . $user_ip) ?: 0;

        if ($attempts >= self::MAX_ATTEMPTS) {
            wp_send_json_error(__('Too many attempts. Please try again later.', 'prospect-manager-plugin'));
            return;
        }

        if (empty($_POST['full_name']) || empty($_POST['email']) || empty($_POST['inquiry'])) {
            wp_send_json_error(__('Please fill in all required fields.', 'prospect-manager-plugin'));
            return;
        }

        $enable_recaptcha = get_option('prospect_manager_enable_recaptcha', 'no');
        if ($enable_recaptcha === 'yes') {
            if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                wp_send_json_error(__('reCAPTCHA response missing.', 'prospect-manager-plugin'));
                return;
            }

            $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);
            $recaptcha_secret = get_option('prospect_manager_recaptcha_secret_key', '');

            $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret'   => $recaptcha_secret,
                    'response' => $recaptcha_response,
                ]
            ]);

            $response_body = wp_remote_retrieve_body($response);
            $result = json_decode($response_body, true);

            if (empty($result['success']) || $result['success'] !== true) {
                wp_send_json_error(__('reCAPTCHA verification failed. Please try again.', 'prospect-manager-plugin'));
                return;
            }
        }

        $post_data = [
            'post_title'   => sanitize_text_field($_POST['full_name']),
            'post_type'    => 'prospect',
            'post_status'  => 'publish',
            'meta_input'   => [
                'full_name'          => sanitize_text_field($_POST['full_name']),
                'email'              => sanitize_email($_POST['email']),
                'whatsapp_number'    => sanitize_text_field($_POST['whatsapp_number']),
                'contact_preference' => sanitize_text_field($_POST['contact_preference']),
                'inquiry'            => sanitize_textarea_field($_POST['inquiry']),
            ],
        ];

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            wp_mail(
                sanitize_email($_POST['email']),
                __('Thank you for your inquiry', 'prospect-manager-plugin'),
                __('We have received your inquiry and will contact you shortly.', 'prospect-manager-plugin')
            );

            set_transient('form_attempts_' . $user_ip, $attempts + 1, self::BLOCK_TIME);

            wp_send_json_success(__('Your inquiry has been successfully submitted.', 'prospect-manager-plugin'));
        } else {
            wp_send_json_error(__('There was an error submitting your inquiry. Please try again.', 'prospect-manager-plugin'));
        }
        
        wp_die();
    }
}
