<?php

namespace ProspectM;

defined('ABSPATH') || exit;

class ProspectTracking {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_tracking_scripts']);
        add_action('wp_ajax_add_tracking_message', [$this, 'handle_add_message']);
    }

    public function enqueue_tracking_scripts($hook) {
        if ('post.php' === $hook && get_post_type() === 'prospect') {
            wp_enqueue_script(
                'prospect-tracking-js',
                plugins_url('../assets/js/prospect-tracking.js', __FILE__),
                ['jquery'],
                null,
                true
            );
            wp_localize_script('prospect-tracking-js', 'trackingAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('prospect_tracking_nonce')
            ]);
        }
    }

    public function display_tracking_fields($post) {
        ?>
        <div class="prospect-tracking-section">
            <label for="new_tracking_message"><?php _e('New Tracking Message', 'prospect-manager-plugin'); ?></label>
            <input type="text" id="new_tracking_message" style="width: 100%; margin-bottom: 10px;" />
            <button id="add-tracking-message" class="button button-primary"><?php _e('Add Message', 'prospect-manager-plugin'); ?></button>
            <div id="tracking-messages" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-top: 15px;">
                <?php
                $messages = get_post_meta($post->ID, 'tracking_messages', true) ?: [];
                if (!empty($messages)) {
                    foreach ($messages as $message) {
                        echo '<p><strong>' . esc_html($message['date']) . ':</strong> ' . esc_html($message['content']) . '</p>';
                    }
                } else {
                    echo '<p>' . __('No tracking messages yet.', 'prospect-manager-plugin') . '</p>';
                }
                ?>
            </div>
        </div>
        <?php
    }

    public function handle_add_message() {
        check_ajax_referer('prospect_tracking_nonce', 'nonce');

        $post_id = intval($_POST['post_id']);
        $new_message = sanitize_text_field($_POST['message']);
        $current_time = current_time('Y-m-d H:i:s');

        if ($post_id && $new_message) {
            $messages = get_post_meta($post_id, 'tracking_messages', true) ?: [];
            $messages[] = [
                'date' => $current_time,
                'content' => $new_message
            ];
            update_post_meta($post_id, 'tracking_messages', $messages);
            wp_send_json_success(['date' => $current_time, 'content' => $new_message]);
        } else {
            wp_send_json_error(__('Invalid data', 'prospect-manager-plugin'));
        }
        wp_die();
    }
}
