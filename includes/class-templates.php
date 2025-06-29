<?php
/**
 * BricksBooster Templates
 *
 * Handles template-related functionality for BricksBooster.
 */
class BricksBooster_Templates {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'register_templates']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Register custom templates
     */
    public function register_templates() {
        // Template registration logic will go here
        // Example: require_once BRICKSBOOSTER_DIR . 'includes/templates/your-template.php';
    }

    /**
     * Enqueue required assets
     */
    public function enqueue_assets() {
        // Only enqueue in admin or builder
        if (!is_admin() && !bricks_is_builder()) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'bricksbooster-templates',
            BRICKSBOOSTER_URL . 'assets/css/templates/core.css',
            ['bricksbooster-core'],
            BRICKSBOOSTER_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'bricksbooster-templates',
            BRICKSBOOSTER_URL . 'assets/js/templates/core.js',
            ['bricks-scripts', 'bricksbooster-core'],
            BRICKSBOOSTER_VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'bricksbooster-templates',
            'bricksBoosterTemplates',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bricksbooster-templates-nonce'),
                'i18n' => [
                    // Add any translatable strings here
                ]
            ]
        );
    }
}
