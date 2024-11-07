<?php

namespace ProspectM;

defined('ABSPATH') || exit;

/**
 * Class to handle the custom post type "Prospect" and its custom fields.
 */
class ProspectCPT {

    /**
     * Constructor to initialize hooks.
     */
    public function __construct() {
        add_action('init', [$this, 'register_prospect_cpt']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('add_meta_boxes', [$this, 'register_custom_fields']);
        add_action('add_meta_boxes', [$this, 'customize_prospect_status_metabox']);
        add_action('save_post', [$this, 'save_custom_fields']);
        add_action('save_post', [$this, 'save_custom_taxonomy_dropdown']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    }

    /**
     * Registers the custom post type "Prospects".
     */
    public function register_prospect_cpt() {
        $labels = [
            'name'               => __('Prospects', 'prospect-manager-plugin'),
            'singular_name'      => __('Prospect', 'prospect-manager-plugin'),
            'menu_name'          => __('Prospects', 'prospect-manager-plugin'),
            'add_new'            => __('Add New', 'prospect-manager-plugin'),
            'add_new_item'       => __('Add New Prospect', 'prospect-manager-plugin'),
            'edit_item'          => __('Edit Prospect', 'prospect-manager-plugin'),
            'new_item'           => __('New Prospect', 'prospect-manager-plugin'),
            'all_items'          => __('All Prospects', 'prospect-manager-plugin'),
            'view_item'          => __('View Prospect', 'prospect-manager-plugin'),
            'search_items'       => __('Search Prospects', 'prospect-manager-plugin'),
            'not_found'          => __('No prospects found', 'prospect-manager-plugin'),
            'not_found_in_trash' => __('No prospects found in Trash', 'prospect-manager-plugin'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'supports'           => ['title'],
            'show_in_rest'       => true,
            'capability_type'    => 'post',
            'menu_icon'          => 'dashicons-businessperson',
        ];

        register_post_type('prospect', $args);
    }

    /**
     * Registers custom taxonomies for the "Prospects" post type.
     */
    public function register_taxonomies() {
        // Register "Prospect Status" taxonomy.
        $status_labels = [
            'name'              => __('Prospect Status', 'prospect-manager-plugin'),
            'singular_name'     => __('Status', 'prospect-manager-plugin'),
            'search_items'      => __('Search Statuses', 'prospect-manager-plugin'),
            'all_items'         => __('All Statuses', 'prospect-manager-plugin'),
            'edit_item'         => __('Edit Status', 'prospect-manager-plugin'),
            'update_item'       => __('Update Status', 'prospect-manager-plugin'),
            'add_new_item'      => __('Add New Status', 'prospect-manager-plugin'),
            'new_item_name'     => __('New Status Name', 'prospect-manager-plugin'),
            'menu_name'         => __('Prospect Status', 'prospect-manager-plugin'),
        ];

        $status_args = [
            'hierarchical'      => true,
            'labels'            => $status_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'prospect-status'],
        ];

        register_taxonomy('prospect_status', 'prospect', $status_args);

        // Register "Service Type" taxonomy.
        $service_labels = [
            'name'              => __('Service Type', 'prospect-manager-plugin'),
            'singular_name'     => __('Service', 'prospect-manager-plugin'),
            'search_items'      => __('Search Services', 'prospect-manager-plugin'),
            'all_items'         => __('All Services', 'prospect-manager-plugin'),
            'edit_item'         => __('Edit Service', 'prospect-manager-plugin'),
            'update_item'       => __('Update Service', 'prospect-manager-plugin'),
            'add_new_item'      => __('Add New Service', 'prospect-manager-plugin'),
            'new_item_name'     => __('New Service Name', 'prospect-manager-plugin'),
            'menu_name'         => __('Service Type', 'prospect-manager-plugin'),
        ];

        $service_args = [
            'hierarchical'      => true,
            'labels'            => $service_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'service-type'],
        ];

        register_taxonomy('service_type', 'prospect', $service_args);
    }

    /**
     * Customizes the metabox for "Prospect Status" to display a dropdown.
     */
    public function customize_prospect_status_metabox() {
        remove_meta_box('prospect_statusdiv', 'prospect', 'side');
        add_meta_box(
            'prospect_status_dropdown',
            __('Prospect Status', 'prospect-manager-plugin'),
            [$this, 'render_prospect_status_dropdown'],
            'prospect',
            'side',
            'default'
        );
    }

    /**
     * Renders the custom dropdown for "Prospect Status".
     */
    public function render_prospect_status_dropdown($post) {
        $taxonomy = 'prospect_status';
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        $current_term = wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'ids']);
        $current_term_id = !empty($current_term) ? $current_term[0] : '';

        echo '<select name="prospect_status" id="prospect_status" style="width: 100%;">';
        echo '<option value="">' . __('Select Status', 'prospect-manager-plugin') . '</option>';
        foreach ($terms as $term) {
            echo '<option value="' . esc_attr($term->term_id) . '"' . selected($current_term_id, $term->term_id, false) . '>' . esc_html($term->name) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Saves the selected term for "Prospect Status" when the post is saved.
     */
    public function save_custom_taxonomy_dropdown($post_id) {
        if (isset($_POST['prospect_status'])) {
            $term_id = intval($_POST['prospect_status']);
            if ($term_id) {
                wp_set_post_terms($post_id, [$term_id], 'prospect_status');
            }
        }
    }

    /**
     * Enqueues custom styles for the admin interface.
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style('prospect-admin-styles', plugins_url('../assets/css/prospect-admin.css', __FILE__));
    }

    /**
     * Registers custom fields for the "Prospects" post type.
     */
    public function register_custom_fields() {
        add_meta_box('prospect_details', __('Prospect Details', 'prospect-manager-plugin'), [$this, 'display_custom_fields'], 'prospect', 'normal', 'high');
        add_meta_box('prospect_tracking', __('Prospect Tracking', 'prospect-manager-plugin'), [new ProspectTracking(), 'display_tracking_fields'], 'prospect', 'normal', 'default');
    }

    /**
     * Displays custom fields in the WordPress post editor.
     *
     * @param \WP_Post $post The current post object.
     */
    public function display_custom_fields($post) {
        $fields = [
            'full_name' => __('Full Name', 'prospect-manager-plugin'),
            'email' => __('Email', 'prospect-manager-plugin'),
            'whatsapp_number' => __('WhatsApp Number', 'prospect-manager-plugin'),
            'contact_preference' => __('Contact Preference', 'prospect-manager-plugin'),
            'sales_agent' => __('Sales Agent', 'prospect-manager-plugin'),
            'developer' => __('Developer', 'prospect-manager-plugin'),
            'contact_datetime' => __('Contact Date and Time', 'prospect-manager-plugin'),
            'lead_source' => __('Lead Source', 'prospect-manager-plugin'),
            'priority_level' => __('Priority Level', 'prospect-manager-plugin'),
            'inquiry' => __('Inquiry', 'prospect-manager-plugin'),
            'observations' => __('Observations', 'prospect-manager-plugin'),
        ];

        wp_nonce_field('save_prospect_fields', 'prospect_nonce');

        echo '<div class="prospect-fields-grid">';

        // Contact Date and Time, Lead Source, and Priority Level on the same line.
        echo '<div class="prospect-row">';
        $this->render_field($post, 'contact_datetime', $fields['contact_datetime']);
        $this->render_field($post, 'lead_source', $fields['lead_source']);
        $this->render_field($post, 'priority_level', $fields['priority_level']);
        echo '</div>';

        // Full Name and Email on the same line.
        echo '<div class="prospect-row">';
        $this->render_field($post, 'full_name', $fields['full_name']);
        $this->render_field($post, 'email', $fields['email']);
        echo '</div>';

        // WhatsApp Number and Contact Preference on the same line.
        echo '<div class="prospect-row">';
        $this->render_field($post, 'whatsapp_number', $fields['whatsapp_number']);
        $this->render_field($post, 'contact_preference', $fields['contact_preference']);
        echo '</div>';

        // Sales Agent and Developer on the same line.
        echo '<div class="prospect-row">';
        $this->render_field($post, 'sales_agent', $fields['sales_agent']);
        $this->render_field($post, 'developer', $fields['developer']);
        echo '</div>';

        // Inquiry and Observations (full width).
        $this->render_field($post, 'inquiry', $fields['inquiry']);
        $this->render_field($post, 'observations', $fields['observations']);
       
        echo '</div>';


    }

    /**
     * Renders individual fields with consistent formatting.
     */
    private function render_field($post, $key, $label) {
        $value = get_post_meta($post->ID, $key, true);
        echo '<div class="prospect-field">';
        echo '<label for="' . esc_attr($key) . '">' . esc_html($label) . '</label>';
        
        if ($key === 'contact_preference') {
            echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">
                    <option value="email"' . selected($value, 'email', false) . '>Email</option>
                    <option value="whatsapp"' . selected($value, 'whatsapp', false) . '>WhatsApp</option>
                    <option value="virtual_meeting"' . selected($value, 'virtual_meeting', false) . '>Virtual Meeting</option>
                  </select>';
        } elseif ($key === 'sales_agent' || $key === 'developer') {
            wp_dropdown_users([
                'name' => $key,
                'selected' => $value,
                'show_option_none' => __('Select User', 'prospect-manager-plugin')
            ]);
        } elseif ($key === 'observations' || $key === 'inquiry') {
            echo '<textarea id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" rows="5" style="width: 100%; margin-bottom: 15px;">' . esc_textarea($value) . '</textarea>';
        } elseif ($key === 'contact_datetime') {
            $value = $value ? $value : current_time('Y-m-d H:i:s');
            echo '<input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" readonly style="width: 100%; margin-bottom: 15px;">';
        } elseif ($key === 'lead_source') {
            echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">
                    <option value="web"' . selected($value, 'web', false) . '>Web</option>
                    <option value="call"' . selected($value, 'call', false) . '>Call</option>
                    <option value="referral"' . selected($value, 'referral', false) . '>Referral</option>
                    <option value="event"' . selected($value, 'event', false) . '>Event</option>
                  </select>';
        } elseif ($key === 'priority_level') {
            echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">
                    <option value="high"' . selected($value, 'high', false) . '>High</option>
                    <option value="medium"' . selected($value, 'medium', false) . '>Medium</option>
                    <option value="low"' . selected($value, 'low', false) . '>Low</option>
                  </select>';
        } else {
            echo '<input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" style="width: 100%; margin-bottom: 15px;">';
        }

        echo '</div>';
    }

    /**
     * Saves custom fields when the post is saved.
     *
     * @param int $post_id The ID of the current post.
     */
    public function save_custom_fields($post_id) {
        if (!isset($_POST['prospect_nonce']) || !wp_verify_nonce($_POST['prospect_nonce'], 'save_prospect_fields')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if ('prospect' !== $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
            return;
        }

        do_action('prospect_manager_before_save_custom_fields', $post_id, $_POST);

        $fields = ['full_name', 'email', 'whatsapp_number', 'contact_preference', 'sales_agent', 'developer', 'contact_datetime', 'lead_source', 'priority_level', 'inquiry', 'observations'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }

        do_action('prospect_manager_after_save_custom_fields', $post_id);
    }
}
