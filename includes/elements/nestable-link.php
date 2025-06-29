<?php
class BricksBooster_Elements_NestableLink {
    public function __construct() {
        add_action('bricks/elements/register', [$this, 'register_element']);
    }

    public function register_element() {
        // Element registration logic
        
        // Enqueue element-specific assets
        wp_enqueue_style(
            'bricksbooster-nestable-link',
            BRICKSBOOSTER_URL . 'assets/css/elements/nestable-link.css',
            [],
            BRICKSBOOSTER_VERSION
        );

        wp_enqueue_script(
            'bricksbooster-nestable-link',
            BRICKSBOOSTER_URL . 'assets/js/elements/nestable-link.js',
            ['bricksbooster-elements'],
            BRICKSBOOSTER_VERSION,
            true
        );
    }
}
