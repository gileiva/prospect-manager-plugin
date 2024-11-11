<?php
/**
 * Plugin Name: Prospect Manager Plugin
 * Description: This plugin allows saving all new prospects in the WordPress administrator to  follow up until the prospect becomes a new client.
 * Version: 1.0
 * Author: Gi Leiva
 * Author URI: https://gileiva.ar
 * License: GPL v2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://gileiva.ar
 * Text Domain: prospect-manager-plugin
 * Domain Path: /languages
 */

// Evita el acceso directo
defined('ABSPATH') || exit;

// Autocargador de clases
spl_autoload_register(function ($class) {
    $namespace = 'ProspectM\\';
    $base_dir = __DIR__ . '/includes/';

    if (strncmp($namespace, $class, strlen($namespace)) !== 0) {
        return;
    }

    $relative_class = substr($class, strlen($namespace));
    $file = $base_dir . 'class-' . strtolower(str_replace('\\', '-', $relative_class)) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

add_action('plugins_loaded', function() {
    load_plugin_textdomain('prospect-manager-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
});



// Instancia la clase principal directamente con el nombre completo del namespace
function gl_run_prospect_manager() {
    $plugin = new \ProspectM\GiHandler(); // Usamos el nombre completo del namespace para evitar problemas de referencia
    $plugin->register_assets();
}
add_action('plugins_loaded', 'gl_run_prospect_manager');

/**
 * Función de activación del plugin
 */
function prospect_manager_activate() {
    if (is_multisite()) {
        global $wpdb;
        $original_blog_id = get_current_blog_id();
        $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

        // Iterar sobre cada sitio en la red
        foreach ($blogs as $blog_id) {
            switch_to_blog($blog_id);
            (new \ProspectM\ProspectCPT())->register_prospect_cpt(); // Registrar el CPT en cada sitio
            restore_current_blog();
        }
        switch_to_blog($original_blog_id); // Regresar al sitio original
    } else {
        (new \ProspectM\ProspectCPT())->register_prospect_cpt(); // Registrar el CPT en un solo sitio
    }

    flush_rewrite_rules(); // Limpiar las reglas de reescritura para que los enlaces permanentes funcionen
}

// Llama a la función de activación al activar el plugin
register_activation_hook(__FILE__, 'prospect_manager_activate');
