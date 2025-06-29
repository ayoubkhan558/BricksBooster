<?php
class BricksBooster_Dynamic_Tags {
    public function __construct() {
        add_action('init', [$this, 'register_dynamic_tags']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_dynamic_tags() {
        // List of dynamic tags to register
        $tags = [
            'reading_time' => [
                'file'  => 'reading-time.php',
                'class' => 'BricksBooster_Dynamic_Tags_ReadingTime',
            ],
            // Add more dynamic tags here as needed
        ];

        foreach ($tags as $key => $tag) {
            $file_path = BRICKSBOOSTER_DIR . 'includes/dynamic-tags/' . $tag['file'];
            if (file_exists($file_path)) {
                require_once $file_path;
                $class_name = $tag['class'];
                if (class_exists($class_name)) {
                    // Initialize the dynamic tag
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
            'bricksbooster-dynamic-tags',
            BRICKSBOOSTER_URL . 'assets/css/dynamic-tags/core.css',
            [],
            BRICKSBOOSTER_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'bricksbooster-dynamic-tags',
            BRICKSBOOSTER_URL . 'assets/js/dynamic-tags/core.js',
            ['bricks-scripts'],
            BRICKSBOOSTER_VERSION,
            true
        );

        // Localize script if needed
        wp_localize_script(
            'bricksbooster-dynamic-tags',
            'bricksBoosterDynamicTags',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bricksbooster-dynamic-tags-nonce')
            ]
        );
    }
}
