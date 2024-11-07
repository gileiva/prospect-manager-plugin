<?php

namespace ProspectM;

defined('ABSPATH') || exit;

/**
 * Clase principal del plugin GiBasePlugin.
 */
class GiHandler {

    /**
     * Constructor de la clase.
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'initialize_admin']);
        $this->initialize_cpt();
        $this->initialize_form_handler();
        $this->initialize_shortcode_renderer();
        $this->initialize_floating_button();
        $this->initialize_notification_manager();
        $this->initialize_tracking();
    }

    /**
     * Registra los scripts y estilos del plugin.
     */
    public function register_assets() {
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style('gl-admin-css', plugins_url('../assets/css/admin.css', __FILE__));
            wp_enqueue_script('gl-admin-js', plugins_url('../assets/js/admin.js', __FILE__), ['jquery'], null, true);
        });
    }

    /**
     * Inicializa la clase Admin.
     */
    public function initialize_admin() {
        $admin = new Admin();
        $admin->add_admin_menu();
    }

    public function initialize_cpt() {
        $prospectCPT = new ProspectCPT();
    }

    /**
     * Initializes the FormHandler class.
     */
    public function initialize_form_handler() {
        $form_handler = new FormHandler();
        add_action('init', [$form_handler, 'handle_form_submission']);
    }

    /**
     * Initializes the ShortcodeRenderer class.
     */
    public function initialize_shortcode_renderer() {
        $shortcode_renderer = new ShortcodeRenderer();
    }

    /**
     * Initializes the FloatingButton class.
     */
    public function initialize_floating_button() {
        $floating_button = new FloatingButton();
    }

    /**
     * Initializes the NotificationManager class.
     */
    public function initialize_notification_manager() {
        $notification_manager = new NotificationManager();
    }
    public function initialize_tracking() {
        $tracking = new ProspectTracking();
    }
    
}