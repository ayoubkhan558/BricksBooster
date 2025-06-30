<?php
class BricksBooster_Builder_Tweaks_HtmlValidator {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        if (!bricks_is_builder()) {
            return;
        }

        wp_enqueue_style(
            'bricksbooster-html-validator',
            BRICKSBOOSTER_URL . 'assets/css/builder-tweaks/html-validator.css',
            [],
            BRICKSBOOSTER_VERSION
        );

        wp_enqueue_script(
            'bricksbooster-html-validator',
            BRICKSBOOSTER_URL . 'assets/js/builder-tweaks/html-validator.js',
            ['jquery', 'bricksbooster-builder-tweaks'],
            BRICKSBOOSTER_VERSION,
            true
        );

        wp_localize_script(
            'bricksbooster-html-validator',
            'bricksBoosterHtmlValidator',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bricksbooster-html-validator-nonce')
            ]
        );
    }
}
