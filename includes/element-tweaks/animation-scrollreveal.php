<?php
class BricksBooster_Element_Tweaks_4 {
    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bricksbooster_scrollreveal_animation_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scrollreveal_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_scrollreveal_initialization']);
        }
    }

    /**
     * Enqueue ScrollReveal.js assets
     */
    public function enqueue_scrollreveal_assets() {
        // ScrollReveal from CDN
        wp_enqueue_script(
            'scrollreveal-js',
            'https://unpkg.com/scrollreveal@4.0.9/dist/scrollreveal.min.js',
            [],
            '4.0.9',
            true
        );
    }

    /**
     * Add ScrollReveal initialization script
     */
    public function add_scrollreveal_initialization() {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ScrollReveal !== 'undefined') {
                // Initialize ScrollReveal with default settings
                window.sr = ScrollReveal({
                    reset: false,
                    distance: '60px',
                    duration: 600,
                    delay: 100,
                    easing: 'cubic-bezier(0.5, 0, 0, 1)',
                    viewFactor: 0.1,
                    mobile: true
                });
                
                // Reveal all elements with data-sr-init="true"
                document.querySelectorAll('[data-sr-init="true"]').forEach(function(el) {
                    const config = {};
                    
                    // Get all data attributes
                    el.getAttributeNames().forEach(function(attr) {
                        if (attr.startsWith('data-sr-') && attr !== 'data-sr-init') {
                            const prop = attr.replace('data-sr-', '');
                            let value = el.getAttribute(attr);
                            
                            // Convert string values to appropriate types
                            if (value === 'true') value = true;
                            else if (value === 'false') value = false;
                            else if (!isNaN(value) && value !== '') value = parseFloat(value);
                            else if (value.startsWith('{') || value.startsWith('[')) {
                                try { value = JSON.parse(value); } catch(e) {}
                            }
                            
                            // Convert kebab-case to camelCase
                            const camelCaseProp = prop.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
                            config[camelCaseProp] = value;
                        }
                    });
                    
                    // Apply the reveal
                    window.sr.reveal(el, config);
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
     * Add ScrollReveal control group
     */
    public function add_control_group($control_groups) {
        $control_groups['bricksbooster_scrollreveal'] = [
            'tab'   => 'style',
            'title' => esc_html__('ScrollReveal', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add ScrollReveal controls
     */
    public function add_controls($controls) {
        // Enable ScrollReveal
        $controls['_sr_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_scrollreveal',
            'label' => esc_html__('Enable ScrollReveal', 'bricks-booster'),
            'type'  => 'checkbox',
        ];

        // Animation type
        $controls['_sr_animation'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollreveal',
            'label'       => esc_html__('Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'fade' => 'Fade',
                'fade-up' => 'Fade Up',
                'fade-down' => 'Fade Down',
                'fade-left' => 'Fade Left',
                'fade-right' => 'Fade Right',
                'fade-up-right' => 'Fade Up Right',
                'fade-up-left' => 'Fade Up Left',
                'fade-down-right' => 'Fade Down Right',
                'fade-down-left' => 'Fade Down Left',
                'zoom-in' => 'Zoom In',
                'zoom-in-up' => 'Zoom In Up',
                'zoom-in-down' => 'Zoom In Down',
                'zoom-in-left' => 'Zoom In Left',
                'zoom-in-right' => 'Zoom In Right',
                'zoom-out' => 'Zoom Out',
                'flip-up' => 'Flip Up',
                'flip-down' => 'Flip Down',
                'flip-left' => 'Flip Left',
                'flip-right' => 'Flip Right',
            ],
            'default'     => 'fade-up',
            'required'    => ['_sr_enable', '!=', ''],
            'inline'      => true,
        ];

        // Duration
        $controls['_sr_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollreveal',
            'label'       => esc_html__('Duration (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 3000,
            'step'        => 100,
            'default'     => 1000,
            'required'    => ['_sr_enable', '!=', ''],
            'inline'      => true,
        ];

        // Delay
        $controls['_sr_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollreveal',
            'label'       => esc_html__('Delay (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 5000,
            'step'        => 100,
            'default'     => 200,
            'required'    => ['_sr_enable', '!=', ''],
            'inline'      => true,
        ];

        // Distance
        $controls['_sr_distance'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollreveal',
            'label'       => esc_html__('Distance (px)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 500,
            'step'        => 10,
            'default'     => 60,
            'required'    => ['_sr_enable', '!=', ''],
            'inline'      => true,
        ];

        // Easing
        $controls['_sr_easing'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollreveal',
            'label'       => esc_html__('Easing', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'ease' => 'ease',
                'ease-in' => 'ease-in',
                'ease-out' => 'ease-out',
                'ease-in-out' => 'ease-in-out',
                'cubic-bezier(0.6, 0, 0.4, 1)' => 'easeInOutCubic',
                'cubic-bezier(0.5, 1, 0.5, 1)' => 'easeInOutSine',
                'cubic-bezier(0.5, 0, 0.5, 1)' => 'easeInOutQuad',
                'cubic-bezier(0.5, 0, 0, 1)' => 'easeInOutQuart',
                'cubic-bezier(0.5, 0, 0.25, 1)' => 'easeInOutQuint',
                'cubic-bezier(0.2, 0.6, 0.2, 1)' => 'easeOutExpo',
                'cubic-bezier(0.2, 0.6, 0.8, 1)' => 'easeOutBack',
            ],
            'default'     => 'cubic-bezier(0.5, 0, 0, 1)',
            'required'    => ['_sr_enable', '!=', ''],
            'inline'      => true,
        ];

        // View factor
        $controls['_sr_view_factor'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollreveal',
            'label'       => esc_html__('View Factor', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1,
            'step'        => 0.1,
            'default'     => 0.1,
            'description' => esc_html__('Amount of element visible before triggering', 'bricks-booster'),
            'required'    => ['_sr_enable', '!=', ''],
            'inline'      => true,
        ];

        // Reset
        $controls['_sr_reset'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollreveal',
            'label'       => esc_html__('Reset After Reveal', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'required'    => ['_sr_enable', '!=', ''],
            'inline'      => true,
        ];

        // Mobile
        $controls['_sr_mobile'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollreveal',
            'label'       => esc_html__('Enable on Mobile', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'required'    => ['_sr_enable', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        $settings = $element->settings;
        
        // Check if ScrollReveal is enabled
        if (empty($settings['_sr_enable'])) {
            return $html;
        }

        // Get animation settings
        $animation = $settings['_sr_animation'] ?? 'fade-up';
        $duration = $settings['_sr_duration'] ?? 1000;
        $delay = $settings['_sr_delay'] ?? 200;
        $distance = $settings['_sr_distance'] ?? 60;
        $easing = $settings['_sr_easing'] ?? 'cubic-bezier(0.5, 0, 0, 1)';
        $viewFactor = $settings['_sr_view_factor'] ?? 0.1;
        $reset = $settings['_sr_reset'] ?? 'false';
        $mobile = $settings['_sr_mobile'] ?? 'true';

        // Prepare data attributes
        $data_attrs = [
            'data-sr-init' => 'true',
            'data-sr-animation' => $animation,
            'data-sr-duration' => $duration,
            'data-sr-delay' => $delay,
            'data-sr-distance' => $distance,
            'data-sr-easing' => $easing,
            'data-sr-view-factor' => $viewFactor,
            'data-sr-reset' => $reset,
            'data-sr-mobile' => $mobile,
        ];

        // Convert data attributes to string
        $attrs_string = '';
        foreach ($data_attrs as $key => $value) {
            $attrs_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        // Add the attributes to the element
        $html = preg_replace('/^(<[^>]+)/', '$1' . $attrs_string, $html);

        return $html;
    }
}
