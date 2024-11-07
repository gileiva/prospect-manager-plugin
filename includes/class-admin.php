<?php

namespace ProspectM;

defined('ABSPATH') || exit;

/**
 * Clase para gestionar la administración del plugin.
 */
class Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }
    
    /**
     * Agrega un menú al administrador de WordPress.
     */
    public function add_admin_menu() {

        add_options_page(
            __('Prospect Manager Settings', 'prospect-manager-plugin'), 
            __('Prospect Manager', 'prospect-manager-plugin'), 
            'manage_options',
            'prospect-manager-settings', 
            [$this, 'display_admin_page'] 
        );


        add_submenu_page(
            'prospect-manager-admin',
            __('reCAPTCHA', 'prospect-manager-plugin'),
            __('reCAPTCHA', 'prospect-manager-plugin'),
            'manage_options',
            'prospect-manager-recaptcha',
            [$this, 'display_recaptcha']
        );
    }

    /**
     * Muestra el contenido de la página de administración.
     */
    public function display_admin_page() {
        if (isset($_POST['submit_settings'])) {
            check_admin_referer('save_settings');
            
            $enable_button = isset($_POST['enable_floating_button']) ? 'yes' : 'no';
            update_option('prospect_manager_enable_floating_button', $enable_button);
            echo '<div class="updated"><p>' . __('Settings saved.', 'prospect-manager-plugin') . '</p></div>';
        }
    
        $is_enabled = get_option('prospect_manager_enable_floating_button', 'no');
    
        echo '<h1>' . __('Settings', 'prospect-manager-plugin') . '</h1>';
        echo '<form method="post" action="">';
        wp_nonce_field('save_settings');
        echo '
            <label for="enable_floating_button">
                <input type="checkbox" name="enable_floating_button" id="enable_floating_button" value="yes" ' . checked($is_enabled, 'yes', false) . ' />
                ' . __('Enable Floating Button', 'prospect-manager-plugin') . '
            </label>
            <br><br>
            <input type="submit" name="submit_settings" value="' . __('Save Settings', 'prospect-manager-plugin') . '" class="button button-primary" />
        </form>';

        // Guardar la configuración si se envía el formulario
        if (isset($_POST['save_recaptcha_settings'])) {
            check_admin_referer('save_recaptcha_settings'); // Verificación de seguridad

            update_option('prospect_manager_enable_recaptcha', isset($_POST['enable_recaptcha']) ? 'yes' : 'no');
            update_option('prospect_manager_recaptcha_site_key', sanitize_text_field($_POST['recaptcha_site_key']));
            update_option('prospect_manager_recaptcha_secret_key', sanitize_text_field($_POST['recaptcha_secret_key']));

            echo '<div class="updated"><p>' . __('Settings saved.', 'prospect-manager-plugin') . '</p></div>';
        }

        // Obtener las opciones actuales
        $enable_recaptcha = get_option('prospect_manager_enable_recaptcha', 'no');
        $recaptcha_site_key = get_option('prospect_manager_recaptcha_site_key', '');
        $recaptcha_secret_key = get_option('prospect_manager_recaptcha_secret_key', '');

        ?>
        <div class="wrap">
            <h1><?php _e('Prospect Manager Settings', 'prospect-manager-plugin'); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('save_recaptcha_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable reCAPTCHA', 'prospect-manager-plugin'); ?></th>
                        <td>
                            <input type="checkbox" name="enable_recaptcha" id="enable_recaptcha" value="yes" <?php checked($enable_recaptcha, 'yes'); ?> />
                            <label for="enable_recaptcha"><?php _e('Enable reCAPTCHA v2 Invisible', 'prospect-manager-plugin'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('reCAPTCHA Site Key', 'prospect-manager-plugin'); ?></th>
                        <td>
                            <input type="text" name="recaptcha_site_key" value="<?php echo esc_attr($recaptcha_site_key); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('reCAPTCHA Secret Key', 'prospect-manager-plugin'); ?></th>
                        <td>
                            <input type="text" name="recaptcha_secret_key" value="<?php echo esc_attr($recaptcha_secret_key); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="save_recaptcha_settings" class="button-primary" value="<?php _e('Save Settings', 'prospect-manager-plugin'); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    
}