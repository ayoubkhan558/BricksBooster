<?php
class BricksBooster_Element_Tweaks_15 {
    private static $instance_count = 0;
    private $gsap_instances = [];
    private $has_gsap = false;
    private $registered_animations = [];

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_gsap_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_gsap_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_gsap_initialization'], 999);
            
            // Register default animations
            add_action('init', [$this, 'register_default_animations']);
        }
    }

    /**
     * Register default GSAP animations
     */
    public function register_default_animations() {
        // Fade animations
        $this->registered_animations['fade'] = [
            'label' => 'Fade',
            'tween' => 'opacity: 0',
            'from' => 'opacity: 0',
            'to' => 'opacity: 1',
            'defaults' => [
                'duration' => 1,
                'ease' => 'power2.out'
            ]
        ];

        // Slide animations
        $this->registered_animations['slideUp'] = [
            'label' => 'Slide Up',
            'tween' => 'y: 50, opacity: 0',
            'from' => 'y: 50, opacity: 0',
            'to' => 'y: 0, opacity: 1',
            'defaults' => [
                'duration' => 0.8,
                'ease' => 'power2.out'
            ]
        ];
    }

    /**
     * Enqueue GSAP assets
     */
    public function enqueue_gsap_assets() {
        if (!function_exists('wp_enqueue_script')) {
            return;
        }
        
        static $enqueued = false;
        
        if (!$enqueued) {
            // Enqueue GSAP Core
            wp_enqueue_script(
                'gsap-js',
                'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js',
                [],
                '3.12.2',
                true
            );
            
            // Enqueue GSAP ScrollTrigger
            wp_enqueue_script(
                'gsap-scrolltrigger',
                'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js',
                ['gsap-js'],
                '3.12.2',
                true
            );
            
            $enqueued = true;
        }
    }

    /**
     * Initialize animation controls for all elements
     */
    public function init_animation_controls() {
        if (!class_exists('Bricks\\Elements')) {
            return;
        }

        $elements = \Bricks\Elements::$elements ?? [];
        
        if (empty($elements)) {
            return;
        }

        $names = array_keys($elements);

        // Add control groups and controls to all elements
        foreach ($names as $name) {
            if (!empty($name)) {
                add_filter("bricks/elements/{$name}/control_groups", [$this, 'add_control_group'], 10);
                add_filter("bricks/elements/{$name}/controls", [$this, 'add_controls'], 10);
            }
        }
    }

    /**
     * Add GSAP control group
     */
    public function add_control_group($control_groups) {
        if (!is_array($control_groups)) {
            $control_groups = [];
        }

        $control_groups['bricksbooster_gsap'] = [
            'tab'   => 'style',
            'title' => esc_html__('GSAP Animations', 'bricks-booster'),
            'icon'  => 'ti-layout-media-overlay-alt-2'
        ];

        return $control_groups;
    }

    /**
     * Add GSAP controls
     */
    public function add_controls($controls) {
        if (!is_array($controls)) {
            $controls = [];
        }

        // Enable GSAP
        $controls['_gsap_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_gsap',
            'label' => esc_html__('Enable GSAP Animation', 'bricks-booster'),
            'type'  => 'checkbox',
            'icon'  => 'ti-layout-media-overlay-alt-2'
        ];

        // Animation Type
        $controls['_gsap_animation_type'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_gsap',
            'label'       => esc_html__('Animation Type', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'preset' => esc_html__('Preset Animation', 'bricks-booster'),
                'custom' => esc_html__('Custom Animation', 'bricks-booster'),
            ],
            'default'     => 'preset',
            'required'    => ['_gsap_enable', '!=', ''],
            'inline'      => true,
        ];

        // Preset Animations
        $preset_options = [];
        foreach ($this->registered_animations as $key => $anim) {
            $preset_options[$key] = $anim['label'];
        }
        
        $controls['_gsap_preset_animation'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_gsap',
            'label'       => esc_html__('Preset Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => $preset_options,
            'default'     => 'fade',
            'required'    => [
                ['_gsap_enable', '!=', ''],
                ['_gsap_animation_type', '=', 'preset']
            ],
            'inline'      => true,
        ];

        // Duration
        $controls['_gsap_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_gsap',
            'label'       => esc_html__('Duration (s)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0.1,
            'max'         => 10,
            'step'        => 0.1,
            'default'     => 1,
            'required'    => ['_gsap_enable', '!=', ''],
            'inline'      => true,
        ];

        // Delay
        $controls['_gsap_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_gsap',
            'label'       => esc_html__('Delay (s)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 10,
            'step'        => 0.1,
            'default'     => 0,
            'required'    => ['_gsap_enable', '!=', ''],
            'inline'      => true,
        ];

        // Easing
        $controls['_gsap_ease'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_gsap',
            'label'       => esc_html__('Easing', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'none' => 'Linear',
                'power1.in' => 'Power1 In',
                'power1.out' => 'Power1 Out',
                'power1.inOut' => 'Power1 InOut',
                'power2.in' => 'Power2 In',
                'power2.out' => 'Power2 Out',
                'power2.inOut' => 'Power2 InOut',
                'power3.in' => 'Power3 In',
                'power3.out' => 'Power3 Out',
                'power3.inOut' => 'Power3 InOut',
                'power4.in' => 'Power4 In',
                'power4.out' => 'Power4 Out',
                'power4.inOut' => 'Power4 InOut',
                'back.in' => 'Back In',
                'back.out' => 'Back Out',
                'back.inOut' => 'Back InOut',
                'elastic' => 'Elastic',
                'bounce' => 'Bounce',
            ],
            'default'     => 'power2.out',
            'required'    => ['_gsap_enable', '!=', ''],
            'inline'      => true,
        ];

        // Trigger
        $controls['_gsap_trigger'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_gsap',
            'label'       => esc_html__('Trigger', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'auto'    => esc_html__('On Page Load', 'bricks-booster'),
                'hover'   => esc_html__('On Hover', 'bricks-booster'),
                'click'   => esc_html__('On Click', 'bricks-booster'),
                'scroll'  => esc_html__('On Scroll Into View', 'bricks-booster'),
            ],
            'default'     => 'auto',
            'required'    => ['_gsap_enable', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        if (empty($html) || !is_object($element) || !property_exists($element, 'settings')) {
            return $html;
        }

        $settings = $element->settings;
        
        // Check if GSAP is enabled
        if (empty($settings['_gsap_enable'])) {
            return $html;
        }

        // Set flag to include initialization script
        $this->has_gsap = true;

        // Generate unique ID for this instance
        self::$instance_count++;
        $instance_id = 'gsap-' . self::$instance_count . '-' . uniqid();

        // Get animation settings
        $animation_type = $settings['_gsap_animation_type'] ?? 'preset';
        $preset = $settings['_gsap_preset_animation'] ?? 'fade';
        $duration = floatval($settings['_gsap_duration'] ?? 1);
        $delay = floatval($settings['_gsap_delay'] ?? 0);
        $ease = $settings['_gsap_ease'] ?? 'power2.out';
        $trigger = $settings['_gsap_trigger'] ?? 'auto';

        // Prepare config
        $config = [
            'type' => $animation_type,
            'preset' => $preset,
            'duration' => $duration,
            'delay' => $delay,
            'ease' => $ease,
            'trigger' => $trigger,
            'element_id' => $instance_id
        ];

        // Store instance config for initialization
        $this->gsap_instances[$instance_id] = $config;

        // Add data attributes for initialization
        $data_attrs = [
            'id' => esc_attr($instance_id),
            'class' => 'gsap-target',
            'data-gsap' => '',
            'data-gsap-config' => wp_json_encode($config),
        ];

        // Add the attributes to the element
        $html = preg_replace('/^(<[^>]+)/', '$1 ' . implode(' ', array_map(
            function ($v, $k) { return $k . '="' . $v . '"'; },
            $data_attrs,
            array_keys($data_attrs)
        )), $html, 1);

        return $html;
    }

    /**
     * Add GSAP initialization script
     */
    public function add_gsap_initialization() {
        if (empty($this->gsap_instances) || !$this->has_gsap) {
            return;
        }
        
        // Prepare the initialization script
        $script = "<script>";
        $script .= "document.addEventListener('DOMContentLoaded', function() {";
        $script .= "if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {";
        $script .= "gsap.registerPlugin(ScrollTrigger);";
        
        foreach ($this->gsap_instances as $id => $config) {
            $script .= "{";
            $script .= "const element = document.getElementById('" . esc_js($id) . "');";
            $script .= "if (element) {";
            
            // Create the animation based on config
            $script .= "const tl = gsap.timeline({paused: true});";
            
            // Add the animation
            $script .= "tl.from(element, {";
            $script .= "opacity: 0,";
            $script .= "y: 20,";
            $script .= "duration: " . floatval($config['duration']) . ",";
            $script .= "delay: " . floatval($config['delay']) . ",";
            $script .= "ease: '" . esc_js($config['ease']) . "',";
            $script .= "onComplete: function() {";
            $script .= "element.style.opacity = 1;";
            $script .= "}";
            $script .= "});";
            
            // Handle trigger
            if ($config['trigger'] === 'scroll') {
                $script .= "ScrollTrigger.create({";
                $script .= "trigger: element,";
                $script .= "start: 'top 80%',";
                $script .= "onEnter: function() { tl.play(); }";
                $script .= "});";
            } else if ($config['trigger'] === 'hover') {
                $script .= "element.addEventListener('mouseenter', function() { tl.restart(); });";
            } else if ($config['trigger'] === 'click') {
                $script .= "element.addEventListener('click', function() { tl.restart(); });";
            } else {
                // Auto trigger
                $script .= "tl.play();";
            }
            
            $script .= "}"; // End element check
            $script .= "}"; // End scope
        }
        
        $script .= "}"; // End GSAP check
        $script .= "});"; // End DOMContentLoaded
        $script .= "</script>";
        
        echo $script;
    }
}
