<?php

namespace ProspectM;

defined('ABSPATH') || exit;

/**
 * Class responsible for displaying a floating button and a modal with the form.
 */
class FloatingButton {

    public function __construct() {
        add_action('wp_footer', [$this, 'render_floating_button']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_print_styles', [$this, 'enqueue_dashicons']);
        
    }

    /**
     * Enqueues the JavaScript and CSS for the floating button and modal.
     */
    public function enqueue_scripts() {

        wp_enqueue_style(
            'floating-button-css',
            plugins_url('../assets/css/floating-button.css', __FILE__)
        );
        wp_enqueue_script(
            'floating-button-js',
            plugins_url('../assets/js/floating-button.js', __FILE__),
            ['jquery'],
            null,
            true
        );
    }
    /**
     * Ensures Dashicons is enqueued in the frontend.
     */
    public function enqueue_dashicons() {
        if (!is_admin()) {
            wp_enqueue_style('dashicons');
        }
    }

    /**
     * Renders the floating button and the modal with the form.
     */
    public function render_floating_button() {
        $is_enabled = get_option('prospect_manager_enable_floating_button', 'no');

        if ($is_enabled === 'yes') {
            echo '
            <div id="pool">
                <div class="button-wrapper">
                    <div class="layer"></div>
                    <button class="main-button fa fa-envelope">
                        <div class="ripple"></div>
                    </button>
                </div>
                <div class="layered-content">
                    <button class="close-button close-button1 fa fa-times"></button>
                    <div class="content-form">' . do_shortcode('[prospect_form]') . '</div>
                </div>
            </div>';
        }

    }
}
