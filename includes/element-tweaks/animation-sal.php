<?php
class BricksBooster_Element_Tweaks_6 {
    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_sal_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_sal_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_sal_initialization']);
        }
    }

    /**
     * Enqueue SAL.js assets
     */
    public function enqueue_sal_assets() {
        // SAL.js from CDN
        wp_enqueue_style(
            'sal-css',
            'https://unpkg.com/sal.js@0.8.5/dist/sal.css',
            [],
            '0.8.5'
        );
        
        wp_enqueue_script(
            'sal-js',
            'https://unpkg.com/sal.js@0.8.5/dist/sal.js',
            [],
            '0.8.5',
            true
        );
    }

    /**
     * Add SAL initialization script
     */
    public function add_sal_initialization() {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof sal !== 'undefined') {
                // Initialize SAL with default settings
                sal({
                    threshold: 0.2,
                    once: false,
                    disable: window.innerWidth < 768 ? true : false
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Initialize animation controls for all elements
     */
    public function init_animation_controls() {
        if (!class_exists('Bricks\Elements')) {
            return;
        }

        // Get all registered elements
        $elements = Bricks\Elements::$elements;
        $names = array_keys($elements);

        // Add control groups and controls to all elements
        foreach ($names as $name) {
            add_filter("bricks/elements/{$name}/control_groups", [$this, 'add_control_group'], 10);
            add_filter("bricks/elements/{$name}/controls", [$this, 'add_controls'], 10);
        }
    }

    /**
     * Add SAL control group
     */
    public function add_control_group($control_groups) {
        $control_groups['bricksbooster_sal'] = [
            'tab'   => 'style',
            'title' => esc_html__('SAL Animations', 'bricks-booster'),
            'icon'  => 'ti-layout-accordion-merged'
        ];

        return $control_groups;
    }

    /**
     * Add SAL controls
     */
    public function add_controls($controls) {
        // Enable SAL
        $controls['_sal_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_sal',
            'label' => esc_html__('Enable SAL', 'bricks-booster'),
            'type'  => 'checkbox',
            'icon'  => 'ti-layout-accordion-merged'
        ];

        // Animation type
        $controls['_sal_animation'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_sal',
            'label'       => esc_html__('Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'fade' => 'Fade',
                'slide' => 'Slide',
                'zoom' => 'Zoom',
                'flip-left' => 'Flip Left',
                'flip-right' => 'Flip Right',
                'flip-up' => 'Flip Up',
                'flip-down' => 'Flip Down',
            ],
            'default'     => 'fade',
            'required'    => ['_sal_enable', '!=', ''],
            'inline'      => true,
        ];

        // Animation duration
        $controls['_sal_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_sal',
            'label'       => esc_html__('Duration (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 200,
            'max'         => 2000,
            'step'        => 100,
            'default'     => 800,
            'required'    => ['_sal_enable', '!=', ''],
            'inline'      => true,
        ];

        // Animation delay
        $controls['_sal_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_sal',
            'label'       => esc_html__('Delay (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1000,
            'step'        => 50,
            'default'     => 0,
            'required'    => ['_sal_enable', '!=', ''],
            'inline'      => true,
        ];

        // Threshold
        $controls['_sal_threshold'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_sal',
            'label'       => esc_html__('Threshold', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1,
            'step'        => 0.05,
            'default'     => 0.2,
            'description' => esc_html__('Amount of element visible to trigger', 'bricks-booster'),
            'required'    => ['_sal_enable', '!=', ''],
            'inline'      => true,
        ];

        // Once
        $controls['_sal_once'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_sal',
            'label'       => esc_html__('Animate Once', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Only animate once when scrolling', 'bricks-booster'),
            'required'    => ['_sal_enable', '!=', ''],
            'inline'      => true,
        ];

        // Disable on mobile
        $controls['_sal_disable_mobile'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_sal',
            'label'       => esc_html__('Disable on Mobile', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'required'    => ['_sal_enable', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        $settings = $element->settings;
        
        // Check if SAL is enabled
        if (empty($settings['_sal_enable'])) {
            return $html;
        }

        // Get animation settings
        $animation = $settings['_sal_animation'] ?? 'fade';
        $duration = $settings['_sal_duration'] ?? 800;
        $delay = $settings['_sal_delay'] ?? 0;
        $threshold = $settings['_sal_threshold'] ?? 0.2;
        $once = $settings['_sal_once'] ?? 'false';
        $disableMobile = $settings['_sal_disable_mobile'] ?? 'true';

        // Prepare data attributes
        $data_attrs = [
            'data-sal' => $animation,
            'data-sal-duration' => $duration,
            'data-sal-delay' => $delay,
            'data-sal-threshold' => $threshold,
            'data-sal-easing' => 'ease-out-quad',
            'data-sal-once' => $once,
            'data-sal-mobile' => $disableMobile === 'true' ? 'false' : 'true',
        ];

        // Add CSS class for the animation
        $classes = ['sal-animate'];

        // Convert data attributes to string
        $attrs_string = '';
        foreach ($data_attrs as $key => $value) {
            $attrs_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        // Add the attributes to the element
        $html = preg_replace('/^(<[^>]+)/', '$1' . $attrs_string . ' class="' . esc_attr(implode(' ', $classes)) . '"', $html);

        return $html;
    }
}
