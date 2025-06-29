<?php
class BricksBooster_Builder_Tweaks_LinkIndicator {
    public function __construct() {
        add_action('bricks:builder:ready', [$this, 'init']);
    }

    public function init() {
        // Enqueue CSS
        wp_enqueue_style(
            'bricksbooster-link-indicator',
            BRICKSBOOSTER_URL . 'assets/css/builder-tweaks/link-indicator.css',
            ['bricksbooster-builder-tweaks'],
            BRICKSBOOSTER_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'bricksbooster-link-indicator',
            BRICKSBOOSTER_URL . 'assets/js/builder-tweaks/link-indicator.js',
            ['jquery', 'bricksbooster-builder-tweaks'],
            BRICKSBOOSTER_VERSION,
            true
        );

        // Localize script if needed
        wp_localize_script(
            'bricksbooster-link-indicator',
            'bricksBoosterLinkIndicator',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bricksbooster-link-indicator-nonce')
            ]
        );
    }
}
