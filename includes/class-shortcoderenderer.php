<?php

namespace ProspectM;

defined('ABSPATH') || exit;

/**
 * Class responsible for registering and rendering the shortcode for the form.
 */
class ShortcodeRenderer {

    public function __construct() {
        add_shortcode('prospect_form', [$this, 'render_shortcode']);
    }

    public function render_shortcode() {
        $form_handler = new FormHandler();
        return $form_handler->render_form();
    }
}
