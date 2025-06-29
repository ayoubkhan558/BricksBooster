<?php
class BricksBooster_Builder_Tweaks_CodeToBricks {
    public function __construct() {
        // Enqueue assets when scripts are enqueued
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        // Only enqueue in admin or builder
        if (!is_admin() && !bricks_is_builder()) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'bricksbooster-code-to-bricks',
            BRICKSBOOSTER_URL . 'assets/css/builder-tweaks/code-to-bricks.css',
            ['bricksbooster-builder-tweaks'],
            BRICKSBOOSTER_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'bricksbooster-code-to-bricks',
            BRICKSBOOSTER_URL . 'assets/js/builder-tweaks/code-to-bricks/converter.js',
            ['jquery', 'bricksbooster-builder-tweaks'],
            BRICKSBOOSTER_VERSION,
            true
        );

        // Localize script if needed
        wp_localize_script(
            'bricksbooster-code-to-bricks',
            'bricksBoostercode-to-bricks',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bricksbooster-code-to-bricks-nonce')
            ]
        );
        // Debugging: log to PHP error log to confirm this runs
        error_log('BricksBooster: enqueueing code-to-bricks assets');
    }
}
