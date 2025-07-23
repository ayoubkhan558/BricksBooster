<?php
class BricksBooster_Elements {
    public function __construct() {
        add_action('init', [$this, 'register_elements']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_elements() {



        
        /**
         * Register custom elements
         */
        add_action( 'init', function() {
            // List of elements to register
            $elements = [
                'simple_list' => [
                    'file' => 'simple-list.php'
                ],
                'nestable_link' => [
                    'file' => 'nestable-link.php'
                ],
                'nestable_list' => [
                    'file' => 'nestable-list.php'
                ],
            ];
            // Register elements that are enabled in settings
            foreach ($elements as $key => $element) {
                if (get_option('bricksbooster_' . $key . '_enabled', 1)) {
                    $file_path = BRICKSBOOSTER_DIR . 'includes/elements/' . $element['file'];
                    if (file_exists($file_path)) {
                        require_once $file_path;
                        
                        // If element has a custom class, register it directly
                        if (!empty($element['class']) && class_exists($element['class'])) {
                            \Bricks\Elements::register_element(new $element['class']());
                        } else {
                            // Fallback to file-based registration
                            \Bricks\Elements::register_element($file_path);
                        }
                    }
                }
            }
        }, 11 );
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
