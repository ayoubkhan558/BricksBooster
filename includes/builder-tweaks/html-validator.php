<?php
/**
 * BricksBooster HTML Validator
 */

class BricksBooster_Builder_Tweaks_HtmlValidator {

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bricksbooster_html_validator_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        }
    }

    public function enqueue_assets() {
        if (!function_exists('bricks_is_builder') || !bricks_is_builder()) {
            return;
        }

        wp_enqueue_script(
            'bricksbooster-html-validator',
            BRICKSBOOSTER_URL . 'assets/js/builder-tweaks/html-validator.js',
            [],
            filemtime(BRICKSBOOSTER_PATH . 'assets/js/builder-tweaks/html-validator.js'),
            true
        );

        wp_enqueue_style(
            'bricksbooster-html-validator',
            BRICKSBOOSTER_URL . 'assets/css/builder-tweaks/html-validator.css',
            [],
            filemtime(BRICKSBOOSTER_PATH . 'assets/css/builder-tweaks/html-validator.css')
        );
    }
}
