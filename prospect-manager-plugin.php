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
