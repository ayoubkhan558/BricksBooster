<?php
class BricksBooster_Dynamic_Tags_ReadingTime {
    public function __construct() {
        add_action('bricks/dynamic_tags/register', [$this, 'register_tag']);
    }

    public function register_tag() {
        // Dynamic tag registration logic
        
        // Enqueue tag-specific assets
        wp_enqueue_style(
            'bricksbooster-reading-time',
            BRICKSBOOSTER_URL . 'assets/css/dynamic-tags/reading-time.css',
            [],
            BRICKSBOOSTER_VERSION
        );

        wp_enqueue_script(
            'bricksbooster-reading-time',
            BRICKSBOOSTER_URL . 'assets/js/dynamic-tags/reading-time.js',
            ['bricksbooster-dynamic-tags'],
            BRICKSBOOSTER_VERSION,
            true
        );
    }
}
