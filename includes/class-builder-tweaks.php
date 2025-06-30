<?php
class BricksBooster_Builder_Tweaks {
    private $features = [];

    public function __construct() {
        $this->load_features();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    private function load_features() {
        // List of feature modules to load
        $features = [
            'code_to_bricks' => [
                'file'  => 'code-to-bricks.php',
                'class' => 'BricksBooster_Builder_Tweaks_CodeToBricks',
            ],
            'link_indicator' => [
                'file'  => 'link-indicator.php',
                'class' => 'BricksBooster_Builder_Tweaks_LinkIndicator',
            ],
            'html_validator' => [
                'file'  => 'html-validator.php',
                'class' => 'BricksBooster_Builder_Tweaks_HtmlValidator',
            ],
        ];

        foreach ($features as $key => $feature) {
            $file_path = BRICKSBOOSTER_DIR . 'includes/builder-tweaks/' . $feature['file'];
            if (file_exists($file_path)) {
                require_once $file_path;
                $class_name = $feature['class'];
                if (class_exists($class_name)) {
                    // Store instance for potential later use
                    $this->features[$key] = new $class_name();
                }
            }
        }
    }

    public function enqueue_assets() {
        // Only enqueue in admin or builder
        if (!is_admin() && !bricks_is_builder()) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'bricksbooster-builder-tweaks',
            BRICKSBOOSTER_URL . 'assets/css/builder-tweaks/core.css',
            [],
            BRICKSBOOSTER_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'bricksbooster-builder-tweaks',
            BRICKSBOOSTER_URL . 'assets/js/builder-tweaks/core.js',
            ['bricks-scripts'],
            BRICKSBOOSTER_VERSION,
            true
        );

        // Localize script if needed
        wp_localize_script(
            'bricksbooster-builder-tweaks',
            'bricksBoosterBuilderTweaks',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bricksbooster-builder-tweaks-nonce')
            ]
        );
    }
}
