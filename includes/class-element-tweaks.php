<?php
class BricksBooster_Element_Tweaks {
    private $features = [];

    public function __construct() {
        $this->load_features();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    private function load_features() {
        // List of feature modules to load
        $features = [
            'animation-tweak' => [
                'file'  => 'animation-tweak.php',
                'class' => 'BricksBooster_Element_Tweaks_1',
            ],
            'animation_aos_tweak' => [
                'file'  => 'animation-aos-tweak.php',
                'class' => 'BricksBooster_Element_Tweaks_2',
            ],
            'laxjs_animation' => [
                'file'  => 'animation-laxjs.php',
                'class' => 'BricksBooster_Element_Tweaks_3',
            ],
            // 'animation-tweak-3' => [
            //     'file'  => 'animation-tweak-3.php',
            //     'class' => 'BricksBooster_Element_Tweaks_3',
            // ],
        ];

        foreach ($features as $key => $feature) {
            // Only load if feature is enabled in settings (default to enabled)
            $option_name = 'bbooster_' . $key . '_enabled';
            $is_enabled = get_option($option_name, 1);
            
            if ($is_enabled) {
                $file_path = BRICKSBOOSTER_DIR . 'includes/element-tweaks/' . $feature['file'];
                
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
