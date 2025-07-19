<?php
class BricksBooster_Element_Tweaks_2 {
    public function __construct() {
        error_log('BricksBooster AOS: Constructor called');
        // Only initialize if enabled in settings
        if (get_option('bbooster_animation_aos_tweak_enabled', true)) {
            error_log('BricksBooster AOS: Feature is enabled, setting up hooks');
            add_action('wp_enqueue_scripts', [$this, 'enqueue_aos_assets']);
            add_action('init', [$this, 'add_custom_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_custom_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_aos_initialization']);
        }
    }

    /**
     * Enqueue AOS assets
     */
    public function enqueue_aos_assets() {
        error_log('BricksBooster AOS: Enqueuing AOS assets');
        
        // Check if we're in admin or frontend
        $is_admin = is_admin();
        $is_bricks_builder = isset($_GET['bricks']) && $_GET['bricks'] === 'run';
        
        // Only enqueue if we're in the frontend or Bricks Builder
        if (!wp_script_is('aos-js', 'enqueued') && (!$is_admin || $is_bricks_builder)) {
            // AOS CSS
            wp_enqueue_style(
                'aos-css',
                'https://unpkg.com/aos@2.3.1/dist/aos.css',
                [],
                '2.3.1'
            );
            error_log('BricksBooster AOS: Enqueued AOS CSS');

            // AOS JavaScript
            wp_enqueue_script(
                'aos-js',
                'https://unpkg.com/aos@2.3.1/dist/aos.js',
                [],
                '2.3.1',
                true
            );
            error_log('BricksBooster AOS: Enqueued AOS JS');
            
            // Add inline script to initialize AOS
            add_action('wp_footer', function() {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { if (typeof AOS !== "undefined") { AOS.init({ duration: 800, once: true }); } });</script>';
            }, 999);
        }
    }

    /**
     * Add AOS initialization script
     */
    public function add_aos_initialization() {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 800,
                    easing: 'ease-in-out',
                    once: true,
                    mirror: false,
                    offset: 120
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Add custom animation controls to all Bricks elements
     */
    public function add_custom_controls() {
        if (!class_exists('Bricks\Elements')) {
            return;
        }

        $elements = \Bricks\Elements::$elements;
        $names = array_keys($elements);
        
        foreach ($names as $name) {
            add_filter("bricks/elements/{$name}/control_groups", [$this, 'add_control_groups'], 10);
            add_filter("bricks/elements/{$name}/controls", [$this, 'add_controls'], 10);
        }
    }

    /**
     * Register the Animation control group
     */
    public function add_control_groups($control_groups) {
        $control_groups['bricksbooster_animation'] = [
            'tab'   => 'style',
            'title' => esc_html__('AOS Animations ', 'bricks-booster'),
            'priority' => 20, // Second position
        ];

        return $control_groups;
    }

    /**
     * Define animation control fields
     */
    public function add_controls($controls) {
        // Enable Animation
        $controls['bb_animation_enabled'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_animation',
            'label' => esc_html__('Enable Animation', 'bricks-booster'),
            'type'  => 'checkbox', // Using Tabler icon
        ];

        // Animation Type
        $controls['bb_animation_type'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Animation Type', 'bricks-booster'),
            'type'        => 'select',
            'options'     => $this->get_aos_animation_options(),
            'placeholder' => esc_html__('Select animation', 'bricks-booster'),
            'required'    => ['bb_animation_enabled', '!=', ''],
            'icon'        => 'ti-layout-accordion-merged'
        ];

        // Animation Duration
        $controls['bb_animation_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Duration (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 3000,
            'step'        => 50,
            'default'     => 800,
            'required'    => ['bb_animation_enabled', '!=', ''],
            'icon'        => 'ti-alarm'
        ];

        // Animation Delay
        $controls['bb_animation_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Delay (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 5000,
            'step'        => 50,
            'default'     => 0,
            'required'    => ['bb_animation_enabled', '!=', ''],
            'icon'        => 'ti-clock'
        ];

        // Animation Easing
        $controls['bb_animation_easing'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Easing', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'ease'              => 'ease',
                'ease-in'           => 'ease-in',
                'ease-out'          => 'ease-out',
                'ease-in-out'       => 'ease-in-out',
                'ease-in-back'      => 'ease-in-back',
                'ease-out-back'     => 'ease-out-back',
                'ease-in-out-back'  => 'ease-in-out-back',
                'ease-in-sine'      => 'ease-in-sine',
                'ease-out-sine'     => 'ease-out-sine',
                'ease-in-out-sine'  => 'ease-in-out-sine',
                'ease-in-quad'      => 'ease-in-quad',
                'ease-out-quad'     => 'ease-out-quad',
                'ease-in-out-quad'  => 'ease-in-out-quad',
            ],
            'default'     => 'ease',
            'required'    => ['bb_animation_enabled', '!=', ''],
            'icon'        => 'ti-wave-saw-tool'
        ];

        // Animation Offset
        $controls['bb_animation_offset'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Offset (px)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1000,
            'step'        => 10,
            'default'     => 120,
            'required'    => ['bb_animation_enabled', '!=', ''],
            'icon'        => 'ti-arrow-bar-to-down'
        ];

        // Animation Anchor Placement
        $controls['bb_animation_anchor'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Anchor Placement', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'top-bottom'        => 'Top Bottom',
                'top-center'        => 'Top Center',
                'top-top'           => 'Top Top',
                'center-bottom'     => 'Center Bottom',
                'center-center'     => 'Center Center',
                'center-top'        => 'Center Top',
                'bottom-bottom'     => 'Bottom Bottom',
                'bottom-center'     => 'Bottom Center',
                'bottom-top'        => 'Bottom Top',
            ],
            'default'     => 'bottom-bottom',
            'required'    => ['bb_animation_enabled', '!=', ''],
            'icon'        => 'ti-layout-align-bottom'
        ];

        // Animation Mirror
        $controls['bb_animation_mirror'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Mirror Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'false' => esc_html__('No', 'bricks-booster'),
                'true'  => esc_html__('Yes', 'bricks-booster'),
            ],
            'default'     => 'false',
            'required'    => ['bb_animation_enabled', '!=', ''],
            'icon'        => 'ti-reflect'
        ];

        // Animation Once
        $controls['bb_animation_once'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Animate Once', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'required'    => ['bb_animation_enabled', '!=', ''],
            'icon'        => 'ti-repeat'
        ];

        return $controls;
    }

    /**
     * Get AOS animation options
     */
    private function get_aos_animation_options() {
        return [
            // Fade animations
            'fade' => 'Fade',
            'fade-up' => 'Fade Up',
            'fade-down' => 'Fade Down',
            'fade-left' => 'Fade Left',
            'fade-right' => 'Fade Right',
            'fade-up-right' => 'Fade Up Right',
            'fade-up-left' => 'Fade Up Left',
            'fade-down-right' => 'Fade Down Right',
            'fade-down-left' => 'Fade Down Left',

            // Flip animations
            'flip-up' => 'Flip Up',
            'flip-down' => 'Flip Down',
            'flip-left' => 'Flip Left',
            'flip-right' => 'Flip Right',

            // Slide animations
            'slide-up' => 'Slide Up',
            'slide-down' => 'Slide Down',
            'slide-left' => 'Slide Left',
            'slide-right' => 'Slide Right',

            // Zoom animations
            'zoom-in' => 'Zoom In',
            'zoom-in-up' => 'Zoom In Up',
            'zoom-in-down' => 'Zoom In Down',
            'zoom-in-left' => 'Zoom In Left',
            'zoom-in-right' => 'Zoom In Right',
            'zoom-out' => 'Zoom Out',
            'zoom-out-up' => 'Zoom Out Up',
            'zoom-out-down' => 'Zoom Out Down',
            'zoom-out-left' => 'Zoom Out Left',
            'zoom-out-right' => 'Zoom Out Right',

            // Special animations
            'flip-x' => 'Flip X',
            'flip-y' => 'Flip Y',
            'flip-x-up' => 'Flip X Up',
            'flip-x-down' => 'Flip X Down',
            'flip-y-left' => 'Flip Y Left',
            'flip-y-right' => 'Flip Y Right',
        ];
    }

    /**
     * Render the animation in the frontend
     */
    public function render_custom_animation($html, $element) {
        $settings = $element->settings;
        
        // Check if animation is enabled
        if (!isset($settings['bb_animation_enabled']) || !$settings['bb_animation_enabled']) {
            return $html;
        }

        // Get animation settings
        $animation_type = $settings['bb_animation_type'] ?? 'fade-up';
        $duration = $settings['bb_animation_duration'] ?? 800;
        $delay = $settings['bb_animation_delay'] ?? 0;
        $easing = $settings['bb_animation_easing'] ?? 'ease';
        $offset = $settings['bb_animation_offset'] ?? 120;
        $anchor = $settings['bb_animation_anchor'] ?? 'bottom-bottom';
        $mirror = $settings['bb_animation_mirror'] ?? 'false';
        $once = $settings['bb_animation_once'] ?? 'true';

        // Generate data attributes
        $animation_data = [
            'data-aos' => $animation_type,
            'data-aos-duration' => $duration,
            'data-aos-delay' => $delay,
            'data-aos-easing' => $easing,
            'data-aos-offset' => $offset,
            'data-aos-anchor-placement' => $anchor,
            'data-aos-mirror' => $mirror,
            'data-aos-once' => $once,
        ];

        // Add data attributes to the element
        $attrs_string = '';
        foreach ($animation_data as $key => $value) {
            $attrs_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        // Add the animation attributes and class to the element
        $html = preg_replace('/^(<[^>]+)/', '$1' . $attrs_string . ' data-aos-once="true"', $html);
        $html = preg_replace('/class="([^"]*)"/', 'class="$1 aos-init"', $html);

        return $html;
    }
}