<?php
class BricksBooster_Element_Tweaks_3 {
    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bricksbooster_laxjs_animation_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_lax_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_lax_initialization']);
        }
    }

    /**
     * Enqueue Lax.js assets
     */
    public function enqueue_lax_assets() {
        // Lax.js from CDN
        wp_enqueue_script(
            'lax-js',
            'https://cdn.jsdelivr.net/npm/lax.js@2.0.3/lax.min.js',
            [],
            '2.0.3',
            true
        );
    }

    /**
     * Add Lax.js initialization script
     */
    public function add_lax_initialization() {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lax !== 'undefined') {
                // Initialize Lax.js
                window.lax = new Lax();
                window.lax.init();
                
                // Add scroll event listener
                window.addEventListener('scroll', function() {
                    window.lax.update(window.scrollY);
                }, false);
                
                // Update on resize
                window.addEventListener('resize', function() {
                    window.lax.updateElements();
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
     * Add Animation control group
     */
    public function add_control_group($control_groups) {
        $control_groups['bricksbooster_animation_lax'] = [
            'tab'   => 'style',
            'title' => esc_html__('Lax.js Animation', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add animation controls
     */
    public function add_controls($controls) {
        // Animation type control
        $controls['_lax_animation_type'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation_lax',
            'label'       => esc_html__('Animation Type', 'bricks-booster'),
            'type'        => 'select',
            'searchable'  => true,
            'options'     => [
                '' => esc_html__('None', 'bricks-booster'),
                'lax-fadeIn' => 'Fade In',
                'lax-fadeInOut' => 'Fade In Out',
                'lax-zoomIn' => 'Zoom In',
                'lax-zoomOut' => 'Zoom Out',
                'lax-slideX' => 'Slide X',
                'lax-slideY' => 'Slide Y',
                'lax-spin' => 'Spin',
                'lax-swing' => 'Swing',
                'lax-blurIn' => 'Blur In',
                'lax-blurOut' => 'Blur Out',
            ],
            'inline'      => true,
            'placeholder' => esc_html__('Select animation', 'bricks-booster'),
            'description' => esc_html__('Choose a Lax.js animation preset', 'bricks-booster'),
        ];

        // Animation trigger control
        $controls['_lax_trigger'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation_lax',
            'label'       => esc_html__('Trigger', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'scroll' => 'Scroll',
                'scrollY' => 'Scroll Y',
                'scrollX' => 'Scroll X',
                'wheel' => 'Mouse Wheel',
                'mousemove' => 'Mouse Move',
            ],
            'default'     => 'scroll',
            'required'    => ['_lax_animation_type', '!=', ''],
            'inline'      => true,
        ];

        // Animation start position
        $controls['_lax_start'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation_lax',
            'label'       => esc_html__('Start Position', 'bricks-booster'),
            'type'        => 'text',
            'description' => esc_html__('e.g., vh -100, elTop elBottom', 'bricks-booster'),
            'default'     => 'vh 1',
            'required'    => ['_lax_animation_type', '!=', ''],
            'inline'      => true,
        ];

        // Animation end position
        $controls['_lax_end'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation_lax',
            'label'       => esc_html__('End Position', 'bricks-booster'),
            'type'        => 'text',
            'description' => esc_html__('e.g., vh 1, elBottom elTop', 'bricks-booster'),
            'default'     => 'vh -1',
            'required'    => ['_lax_animation_type', '!=', ''],
            'inline'      => true,
        ];

        // Animation easing
        $controls['_lax_easing'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation_lax',
            'label'       => esc_html__('Easing', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'easeInOutSine' => 'Ease In Out',
                'easeInQuad' => 'Ease In',
                'easeOutQuad' => 'Ease Out',
                'easeInOutQuad' => 'Ease In Out',
                'easeInCubic' => 'Ease In Cubic',
                'easeOutCubic' => 'Ease Out Cubic',
                'easeInOutCubic' => 'Ease In Out Cubic',
            ],
            'default'     => 'easeInOutSine',
            'required'    => ['_lax_animation_type', '!=', ''],
            'inline'      => true,
        ];

        // Animation speed
        $controls['_lax_speed'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation_lax',
            'label'       => esc_html__('Speed', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 1,
            'max'         => 20,
            'step'        => 1,
            'default'     => 10,
            'description' => esc_html__('Animation speed multiplier', 'bricks-booster'),
            'required'    => ['_lax_animation_type', '!=', ''],
            'inline'      => true,
        ];

        // Animation delay
        $controls['_lax_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation_lax',
            'label'       => esc_html__('Delay (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 5000,
            'step'        => 100,
            'default'     => 0,
            'required'    => ['_lax_animation_type', '!=', ''],
            'inline'      => true,
        ];

        // Animation inertia
        $controls['_lax_inertia'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation_lax',
            'label'       => esc_html__('Inertia', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 100,
            'step'        => 1,
            'default'     => 0,
            'description' => esc_html__('Adds smooth deceleration (0-100)', 'bricks-booster'),
            'required'    => ['_lax_animation_type', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        $settings = $element->settings;
        $animation_type = $settings['_lax_animation_type'] ?? '';
        
        if (empty($animation_type)) {
            return $html;
        }

        // Get animation settings
        $trigger = $settings['_lax_trigger'] ?? 'scroll';
        $start = $settings['_lax_start'] ?? 'vh 1';
        $end = $settings['_lax_end'] ?? 'vh -1';
        $easing = $settings['_lax_easing'] ?? 'easeInOutSine';
        $speed = isset($settings['_lax_speed']) ? intval($settings['_lax_speed']) : 10;
        $delay = isset($settings['_lax_delay']) ? intval($settings['_lax_delay']) : 0;
        $inertia = isset($settings['_lax_inertia']) ? intval($settings['_lax_inertia']) : 0;

        // Prepare Lax.js attributes
        $lax_attrs = [
            'data-lax-preset' => str_replace('lax-', '', $animation_type),
            'data-lax-anchor' => 'self',
            'data-lax-optimize' => 'true',
        ];

        // Add trigger
        $lax_attrs["data-lax-{$trigger}-start"] = $start;
        $lax_attrs["data-lax-{$trigger}-end"] = $end;

        // Add animation properties
        $lax_attrs['data-lax-opacity'] = "{$start} 1, {$end} 0";
        $lax_attrs['data-lax-speed'] = $speed;
        
        if ($delay > 0) {
            $lax_attrs['data-lax-anchor'] = 'self';
            $lax_attrs['data-lax-anchor-offset'] = $delay;
        }

        if ($inertia > 0) {
            $lax_attrs['data-lax-inertia'] = $inertia;
        }

        // Convert attributes to string
        $attrs_string = '';
        foreach ($lax_attrs as $attr => $value) {
            $attrs_string .= ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
        }

        // Add Lax.js class
        $html = preg_replace('/class="([^"]*)"/', 'class="$1 lax"' . $attrs_string, $html);

        return $html;
    }
}