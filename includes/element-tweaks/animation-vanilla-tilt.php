<?php
class BricksBooster_Element_Tweaks_10 {
    private static $instance_count = 0;
    private $tilt_instances = [];

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_vanilla_tilt_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_vanilla_tilt_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_vanilla_tilt_initialization'], 999);
        }
    }

    /**
     * Enqueue Vanilla Tilt.js assets
     */
    public function enqueue_vanilla_tilt_assets() {
        // Check if WordPress functions are available
        if (!function_exists('wp_enqueue_script')) {
            return;
        }
        
        // Track enqueued assets to prevent duplicates
        static $enqueued = false;
        
        if (!$enqueued) {
            // Enqueue Vanilla Tilt JS
            wp_enqueue_script(
                'vanilla-tilt-js',
                'https://cdn.jsdelivr.net/npm/vanilla-tilt@1.8.0/dist/vanilla-tilt.min.js',
                [],
                '1.8.0',
                true
            );
            
            $enqueued = true;
        }
    }

    /**
     * Add Vanilla Tilt initialization script
     */
    public function add_vanilla_tilt_initialization() {
        if (empty($this->tilt_instances)) {
            return;
        }
        
        // Prepare the initialization script
        $script = "<script>";
        $script .= "document.addEventListener('DOMContentLoaded', function() {";
        $script .= "if (typeof VanillaTilt !== 'undefined') {";
        
        foreach ($this->tilt_instances as $id => $config) {
            $script .= "var element = document.getElementById('" . esc_js($id) . "');";
            $script .= "if (element) { new VanillaTilt(element, " . wp_json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT) . "); }";
        }
        
        $script .= "}"; // End VanillaTilt check
        
        // Add refresh handler for AJAX content
        $script .= "if (typeof jQuery !== 'undefined') {";
        $script .= "jQuery(document).on('bricks/ajax/after_load', function() {";
        foreach (array_keys($this->tilt_instances) as $id) {
            $script .= "var element = document.getElementById('" . esc_js($id) . "');";
            $script .= "if (element && !element.hasAttribute('data-tilt')) { new VanillaTilt(element, " . wp_json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT) . "); }";
        }
        $script .= "});"; // End AJAX handler
        $script .= "}"; // End jQuery check
        
        $script .= "});"; // End DOMContentLoaded
        $script .= "</script>";
        
        echo $script;
    }

    /**
     * Initialize animation controls for all elements
     */
    public function init_animation_controls() {
        if (!class_exists('Bricks\Elements')) {
            return;
        }

        // Get all registered elements
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
     * Add Vanilla Tilt control group
     */
    public function add_control_group($control_groups) {
        if (!is_array($control_groups)) {
            $control_groups = [];
        }

        $control_groups['bricksbooster_vanilla_tilt'] = [
            'tab'   => 'style',
            'title' => esc_html__('Vanilla Tilt.js', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add Vanilla Tilt controls
     */
    public function add_controls($controls) {
        if (!is_array($controls)) {
            $controls = [];
        }

        // Enable Vanilla Tilt
        $controls['_vanilla_tilt_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_vanilla_tilt',
            'label' => esc_html__('Enable Tilt Effect', 'bricks-booster'),
            'type'  => 'checkbox',
        ];

        // Max Tilt
        $controls['_vanilla_tilt_max'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Max Tilt', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 45,
            'step'        => 1,
            'default'     => 20,
            'description' => esc_html__('Maximum tilt rotation in degrees', 'bricks-booster'),
            'required'    => ['_vanilla_tilt_enable', '!=', ''],
            'inline'      => true,
        ];

        // Scale
        $controls['_vanilla_tilt_scale'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Scale', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 1,
            'max'         => 2,
            'step'        => 0.05,
            'default'     => 1.05,
            'description' => esc_html__('Scale of the element on hover', 'bricks-booster'),
            'required'    => ['_vanilla_tilt_enable', '!=', ''],
            'inline'      => true,
        ];

        // Speed
        $controls['_vanilla_tilt_speed'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Speed', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 2000,
            'step'        => 100,
            'default'     => 400,
            'description' => esc_html__('Speed of the enter/exit transition in ms', 'bricks-booster'),
            'required'    => ['_vanilla_tilt_enable', '!=', ''],
            'inline'      => true,
        ];

        // Axis
        $controls['_vanilla_tilt_axis'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Axis', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'both' => esc_html__('Both', 'bricks-booster'),
                'x'    => esc_html__('X Only', 'bricks-booster'),
                'y'    => esc_html__('Y Only', 'bricks-booster'),
            ],
            'default'     => 'both',
            'required'    => ['_vanilla_tilt_enable', '!=', ''],
            'inline'      => true,
        ];

        // Reset
        $controls['_vanilla_tilt_reset'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Reset on Leave', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'description' => esc_html__('Reset the tilt effect when mouse leaves the element', 'bricks-booster'),
            'required'    => ['_vanilla_tilt_enable', '!=', ''],
            'inline'      => true,
        ];

        // Glare
        $controls['_vanilla_tilt_glare'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Enable Glare', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Adds a glare effect to the element', 'bricks-booster'),
            'required'    => ['_vanilla_tilt_enable', '!=', ''],
            'inline'      => true,
        ];

        // Max Glare
        $controls['_vanilla_tilt_max_glare'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Max Glare', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1,
            'step'        => 0.05,
            'default'     => 0.5,
            'description' => esc_html__('Maximum glare opacity (0-1)', 'bricks-booster'),
            'required'    => [
                ['_vanilla_tilt_enable', '!=', ''],
                ['_vanilla_tilt_glare', '=', 'true']
            ],
            'inline'      => true,
        ];

        // Perspective
        $controls['_vanilla_tilt_perspective'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Perspective', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 3000,
            'step'        => 100,
            'default'     => 1000,
            'description' => esc_html__('Transform perspective in px', 'bricks-booster'),
            'required'    => ['_vanilla_tilt_enable', '!=', ''],
            'inline'      => true,
        ];

        // Easing
        $controls['_vanilla_tilt_easing'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Easing', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'cubic-bezier(.03,.98,.52,.99)' => 'Ease In Out',
                'cubic-bezier(.17,.67,.83,.67)'  => 'Ease Out',
                'cubic-bezier(.36,0,.66,-0.56)'  => 'Ease In',
                'cubic-bezier(.34,1.56,.64,1)'   => 'Bounce',
            ],
            'default'     => 'cubic-bezier(.03,.98,.52,.99)',
            'required'    => ['_vanilla_tilt_enable', '!=', ''],
            'inline'      => true,
        ];

        // Disable on Mobile
        $controls['_vanilla_tilt_mobile_disable'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_vanilla_tilt',
            'label'       => esc_html__('Disable on Mobile', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Disable tilt effect on mobile devices', 'bricks-booster'),
            'required'    => ['_vanilla_tilt_enable', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        // Validate inputs
        if (empty($html) || !is_object($element) || !property_exists($element, 'settings')) {
            return $html;
        }

        $settings = $element->settings;
        
        // Check if Vanilla Tilt is enabled
        if (empty($settings['_vanilla_tilt_enable'])) {
            return $html;
        }

        // Generate unique ID for this instance
        self::$instance_count++;
        $instance_id = 'vanilla-tilt-' . self::$instance_count . '-' . uniqid();

        // Get animation settings with proper sanitization
        $config = [
            'max' => floatval($settings['_vanilla_tilt_max'] ?? 20),
            'scale' => floatval($settings['_vanilla_tilt_scale'] ?? 1.05),
            'speed' => intval($settings['_vanilla_tilt_speed'] ?? 400),
            'axis' => sanitize_text_field($settings['_vanilla_tilt_axis'] ?? 'both'),
            'reset' => sanitize_text_field($settings['_vanilla_tilt_reset'] ?? 'true') === 'true',
            'glare' => sanitize_text_field($settings['_vanilla_tilt_glare'] ?? 'false') === 'true',
            'maxGlare' => floatval($settings['_vanilla_tilt_max_glare'] ?? 0.5),
            'perspective' => intval($settings['_vanilla_tilt_perspective'] ?? 1000),
            'easing' => sanitize_text_field($settings['_vanilla_tilt_easing'] ?? 'cubic-bezier(.03,.98,.52,.99)'),
            'disableAxis' => sanitize_text_field($settings['_vanilla_tilt_axis'] ?? 'both') === 'both' ? null : 
                            ($settings['_vanilla_tilt_axis'] === 'x' ? 'y' : 'x'),
            'mobile' => sanitize_text_field($settings['_vanilla_tilt_mobile_disable'] ?? 'false') !== 'true',
        ];

        // Store instance config for initialization
        $this->tilt_instances[$instance_id] = $config;

        // Add data attributes for initialization
        $data_attrs = [
            'id' => esc_attr($instance_id),
            'class' => 'js-tilt',
            'data-tilt' => '',
            'data-tilt-max' => $config['max'],
            'data-tilt-scale' => $config['scale'],
            'data-tilt-speed' => $config['speed'],
            'data-tilt-perspective' => $config['perspective'],
            'data-tilt-easing' => $config['easing'],
        ];

        // Add conditional attributes
        if ($config['axis'] !== 'both') {
            $data_attrs['data-tilt-axis'] = $config['axis'];
        }

        if (!$config['reset']) {
            $data_attrs['data-tilt-reset'] = 'false';
        }

        if ($config['glare']) {
            $data_attrs['data-tilt-glare'] = 'true';
            $data_attrs['data-tilt-max-glare'] = $config['maxGlare'];
        }

        if (!$config['mobile']) {
            $data_attrs['data-tilt-mobile'] = 'false';
        }

        // Convert data attributes to string
        $attrs_string = '';
        foreach ($data_attrs as $key => $value) {
            $attrs_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        // Add the attributes to the element
        $html = preg_replace('/^(<[^>]+)/', '$1' . $attrs_string, $html, 1);

        return $html;
    }
}
