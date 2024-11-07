<?php

namespace ProspectM;

defined('ABSPATH') || exit;

/**
 * Class responsible for managing notifications for new prospects.
 */
class NotificationManager {

    /**
     * Constructor that hooks into the save_post action to send notifications.
     */
    public function __construct() {
        add_action('save_post_prospect', [$this, 'send_notifications'], 10, 3);
    }

    /**
     * Sends notifications when a prospect post is saved or updated.
     *
     * @param int $post_id The ID of the post being saved.
     * @param \WP_Post $post The post object.
     * @param bool $update Whether this is an existing post being updated or not.
     */
    public function send_notifications($post_id, $post, $update) {
        // Ensure this only runs on the 'prospect' post type.
        if ($post->post_type !== 'prospect') {
            return;
        }

        // Get meta fields.
        $developer_id = get_post_meta($post_id, 'developer', true);
        $sales_agent_id = get_post_meta($post_id, 'sales_agent', true);

        // Notify the admin of a new prospect.
        if (!$update) { // Only notify for new posts.
            $admin_email = get_option('admin_email');
            $subject = __('New Prospect Created', 'prospect-manager-plugin');
            $message = sprintf(
                __('A new prospect titled "%s" has been created. You can view it here: %s', 'prospect-manager-plugin'),
                $post->post_title,
                get_edit_post_link($post_id)
            );
            wp_mail($admin_email, $subject, $message);
        }

        // Notify the Developer when assigned.
        if ($developer_id) {
            $developer_user = get_userdata($developer_id);
            if ($developer_user && $developer_user->user_email) {
                $subject = __('You have been assigned as Developer', 'prospect-manager-plugin');
                $message = sprintf(
                    __('You have been assigned as the Developer for the prospect titled "%s". View it here: %s', 'prospect-manager-plugin'),
                    $post->post_title,
                    get_edit_post_link($post_id)
                );
                wp_mail($developer_user->user_email, $subject, $message);
            }
        }

        // Notify the Sales Agent when assigned.
        if ($sales_agent_id) {
            $sales_agent_user = get_userdata($sales_agent_id);
            if ($sales_agent_user && $sales_agent_user->user_email) {
                $subject = __('You have been assigned as Sales Agent', 'prospect-manager-plugin');
                $message = sprintf(
                    __('You have been assigned as the Sales Agent for the prospect titled "%s". View it here: %s', 'prospect-manager-plugin'),
                    $post->post_title,
                    get_edit_post_link($post_id)
                );
                wp_mail($sales_agent_user->user_email, $subject, $message);
            }
        }
    }
}
