<?php
class BricksBooster_Elements {
    public function __construct() {
        add_action('init', [$this, 'register_elements']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_elements() {
        // List of elements to register
        $elements = [
            'nestable_link' => [
                'file'  => 'nestable-link.php',
                'class' => 'BricksBooster_Elements_NestableLink',
            ],
            // Add more elements here as needed
        ];

        foreach ($elements as $key => $element) {
            $file_path = BRICKSBOOSTER_DIR . 'includes/elements/' . $element['file'];
            if (file_exists($file_path)) {
                require_once $file_path;
                $class_name = $element['class'];
                if (class_exists($class_name)) {
                    // Initialize the element
                    new $class_name();
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
            'bricksbooster-elements',
            BRICKSBOOSTER_URL . 'assets/css/elements/core.css',
            [],
            BRICKSBOOSTER_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'bricksbooster-elements',
            BRICKSBOOSTER_URL . 'assets/js/elements/core.js',
            ['bricks-scripts'],
            BRICKSBOOSTER_VERSION,
            true
        );

        // Localize script if needed
        wp_localize_script(
            'bricksbooster-elements',
            'bricksBoosterElements',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bricksbooster-elements-nonce')
            ]
        );
    }
}
