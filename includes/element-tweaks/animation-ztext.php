<?php
class BricksBooster_Element_Tweaks_11 {
    private static $instance_count = 0;
    private $ztext_instances = [];
    private $has_ztext = false;

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_ztext_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_ztext_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_ztext_initialization'], 999);
            add_action('wp_footer', [$this, 'add_ztext_refresh_script'], 1000);
        }
    }

    /**
     * Enqueue Ztext.js assets
     */
    public function enqueue_ztext_assets() {
        // Check if WordPress functions are available
        if (!function_exists('wp_enqueue_script')) {
            return;
        }
        
        // Track enqueued assets to prevent duplicates
        static $enqueued = false;
        
        if (!$enqueued) {
            // Enqueue Ztext JS
            wp_enqueue_script(
                'ztext-js',
                'https://cdn.jsdelivr.net/npm/ztext@2.0.0-beta.7/ztext.min.js',
                [],
                '2.0.0-beta.7',
                true
            );
            
            $enqueued = true;
        }
    }

    /**
     * Add Ztext initialization script
     */
    public function add_ztext_initialization() {
        if (empty($this->ztext_instances) || !$this->has_ztext) {
            return;
        }
        
        // Prepare the initialization script
        $script = "<script>";
        $script .= "document.addEventListener('DOMContentLoaded', function() {";
        $script .= "if (typeof Ztextify !== 'undefined') {";
        
        foreach ($this->ztext_instances as $id => $config) {
            $script .= "var element = document.getElementById('" . esc_js($id) . "');";
            $script .= "if (element && !element.hasAttribute('data-ztext-processed')) {";
            $script .= "element.setAttribute('data-ztext-processed', 'true');";
            $script .= "new Ztextify('#' . '" . esc_js($id) . "', " . wp_json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT) . ");";
            $script .= "}";
        }
        
        $script .= "}"; // End Ztextify check
        
        // Add refresh handler for AJAX content
        $script .= "if (typeof jQuery !== 'undefined') {";
        $script .= "jQuery(document).on('bricks/ajax/after_load', function() {";
        foreach ($this->ztext_instances as $id => $config) {
            $script .= "var element = document.getElementById('" . esc_js($id) . "');";
            $script .= "if (element && !element.hasAttribute('data-ztext-processed')) {";
            $script .= "element.setAttribute('data-ztext-processed', 'true');";
            $script .= "new Ztextify('#' . '" . esc_js($id) . "', " . wp_json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT) . ");";
            $script .= "}";
        }
        $script .= "});"; // End AJAX handler
        $script .= "}"; // End jQuery check
        
        $script .= "});"; // End DOMContentLoaded
        $script .= "</script>";
        
        echo $script;
    }

    /**
     * Add script to refresh Ztext after AJAX loads
     */
    public function add_ztext_refresh_script() {
        if (!$this->has_ztext) {
            return;
        }
        ?>
        <script>
        (function($) {
            $(document).on('bricks/ajax/after_load', function() {
                if (typeof Ztextify !== 'undefined' && window.ztextInstances) {
                    Object.values(window.ztextInstances).forEach(function(instance) {
                        if (instance && typeof instance.refresh === 'function') {
                            try {
                                instance.refresh();
                            } catch (error) {
                                console.warn('Failed to refresh Ztext instance after AJAX:', error);
                            }
                        }
                    });
                }
            });
        })(window.jQuery);
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
     * Add Ztext control group
     */
    public function add_control_group($control_groups) {
        if (!is_array($control_groups)) {
            $control_groups = [];
        }

        $control_groups['bricksbooster_ztext'] = [
            'tab'   => 'style',
            'title' => esc_html__('Ztext.js', 'bricks-booster'),
            'icon'  => 'ti-text'
        ];

        return $control_groups;
    }

    /**
     * Add Ztext controls
     */
    public function add_controls($controls) {
        if (!is_array($controls)) {
            $controls = [];
        }

        // Enable Ztext
        $controls['_ztext_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_ztext',
            'label' => esc_html__('Enable Ztext', 'bricks-booster'),
            'type'  => 'checkbox',
            'icon'  => 'ti-text'
        ];

        // Effect Type
        $controls['_ztext_effect'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Effect Type', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'chars' => esc_html__('Characters', 'bricks-booster'),
                'words' => esc_html__('Words', 'bricks-booster'),
                'lines' => esc_html__('Lines', 'bricks-booster'),
                'block' => esc_html__('Block', 'bricks-booster'),
            ],
            'default'     => 'chars',
            'required'    => ['_ztext_enable', '!=', ''],
            'inline'      => true,
        ];

        // Depth
        $controls['_ztext_depth'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Depth', 'bricks-booster'),
            'type'        => 'slider',
            'min'         => 0,
            'max'         => 100,
            'step'        => 1,
            'default'     => 20,
            'description' => esc_html__('3D depth of the effect', 'bricks-booster'),
            'required'    => ['_ztext_enable', '!=', ''],
            'css'         => [
                [
                    'property' => '--ztext-depth',
                    'selector' => '',
                ],
            ],
        ];

        // Direction
        $controls['_ztext_direction'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Direction', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'forwards'  => esc_html__('Forwards', 'bricks-booster'),
                'backwards' => esc_html__('Backwards', 'bricks-booster'),
                'center'    => esc_html__('Center', 'bricks-booster'),
                'up'        => esc_html__('Up', 'bricks-booster'),
                'down'      => esc_html__('Down', 'bricks-booster'),
            ],
            'default'     => 'forwards',
            'required'    => ['_ztext_enable', '!=', ''],
            'inline'      => true,
        ];

        // Layers
        $controls['_ztext_layers'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Layers', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 1,
            'max'         => 10,
            'default'     => 5,
            'description' => esc_html__('Number of 3D layers', 'bricks-booster'),
            'required'    => ['_ztext_enable', '!=', ''],
            'inline'      => true,
        ];

        // Fade
        $controls['_ztext_fade'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Fade', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'description' => esc_html__('Fade out layers into the background', 'bricks-booster'),
            'required'    => ['_ztext_enable', '!=', ''],
            'inline'      => true,
        ];

        // Event
        $controls['_ztext_event'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Event', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'none'    => esc_html__('None (Always On)', 'bricks-booster'),
                'hover'   => esc_html__('Hover', 'bricks-booster'),
                'scroll'  => esc_html__('Scroll', 'bricks-booster'),
                'click'   => esc_html__('Click', 'bricks-booster'),
            ],
            'default'     => 'none',
            'required'    => ['_ztext_enable', '!=', ''],
            'inline'      => true,
        ];

        // Event Direction
        $controls['_ztext_event_direction'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Event Direction', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'default' => esc_html__('Default', 'bricks-booster'),
                'reverse' => esc_html__('Reverse', 'bricks-booster'),
            ],
            'default'     => 'default',
            'required'    => [
                ['_ztext_enable', '!=', ''],
                ['_ztext_event', '!=', 'none']
            ],
            'inline'      => true,
        ];

        // Event Rotation
        $controls['_ztext_event_rotation'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Event Rotation', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 90,
            'default'     => 20,
            'description' => esc_html__('Max rotation in degrees', 'bricks-booster'),
            'required'    => [
                ['_ztext_enable', '!=', ''],
                ['_ztext_event', '!=', 'none']
            ],
            'inline'      => true,
        ];

        // Duration
        $controls['_ztext_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Duration', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 5000,
            'step'        => 100,
            'default'     => 1000,
            'description' => esc_html__('Animation duration in milliseconds', 'bricks-booster'),
            'required'    => ['_ztext_enable', '!=', ''],
            'inline'      => true,
        ];

        // Easing
        $controls['_ztext_easing'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Easing', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'ease' => 'ease',
                'ease-in' => 'ease-in',
                'ease-out' => 'ease-out',
                'ease-in-out' => 'ease-in-out',
                'linear' => 'linear',
                'cubic-bezier(0.25, 0.1, 0.25, 1)' => 'ease (default)',
                'cubic-bezier(0.42, 0, 1, 1)' => 'ease-in (quad)',
                'cubic-bezier(0, 0, 0.58, 1)' => 'ease-out (quad)',
                'cubic-bezier(0.46, 0.03, 0.52, 0.96)' => 'ease-in-out (sine)',
            ],
            'default'     => 'ease',
            'required'    => ['_ztext_enable', '!=', ''],
            'inline'      => true,
        ];

        // Perspective
        $controls['_ztext_perspective'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Perspective', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 5000,
            'step'        => 100,
            'default'     => 1000,
            'description' => esc_html__('CSS perspective value (px)', 'bricks-booster'),
            'required'    => ['_ztext_enable', '!=', ''],
            'inline'      => true,
        ];

        // Layer Gap
        $controls['_ztext_layer_gap'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Layer Gap', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 100,
            'step'        => 1,
            'default'     => 20,
            'description' => esc_html__('Space between layers (px)', 'bricks-booster'),
            'required'    => ['_ztext_enable', '!=', ''],
            'inline'      => true,
        ];

        // Disable on Mobile
        $controls['_ztext_disable_mobile'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ztext',
            'label'       => esc_html__('Disable on Mobile', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Disable effect on mobile devices', 'bricks-booster'),
            'required'    => ['_ztext_enable', '!=', ''],
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
        
        // Check if Ztext is enabled
        if (empty($settings['_ztext_enable'])) {
            return $html;
        }

        // Set flag to include initialization script
        $this->has_ztext = true;

        // Generate unique ID for this instance
        self::$instance_count++;
        $instance_id = 'ztext-' . self::$instance_count . '-' . uniqid();

        // Get animation settings with proper sanitization
        $config = [
            'event' => sanitize_text_field($settings['_ztext_event'] ?? 'none'),
            'eventDirection' => sanitize_text_field($settings['_ztext_event_direction'] ?? 'default'),
            'eventRotation' => intval($settings['_ztext_event_rotation'] ?? 20),
            'depth' => intval($settings['_ztext_depth'] ?? 20),
            'layers' => intval($settings['_ztext_layers'] ?? 5),
            'fade' => sanitize_text_field($settings['_ztext_fade'] ?? 'true') === 'true',
            'direction' => sanitize_text_field($settings['_ztext_direction'] ?? 'forwards'),
            'effect' => sanitize_text_field($settings['_ztext_effect'] ?? 'chars'),
            'duration' => intval($settings['_ztext_duration'] ?? 1000),
            'easing' => sanitize_text_field($settings['_ztext_easing'] ?? 'ease'),
            'perspective' => intval($settings['_ztext_perspective'] ?? 1000),
            'layerGap' => intval($settings['_ztext_layer_gap'] ?? 20),
            'disabled' => sanitize_text_field($settings['_ztext_disable_mobile'] ?? 'false') === 'true' ? 
                '(max-width: 767px)' : false,
        ];

        // Store instance config for initialization
        $this->ztext_instances[$instance_id] = $config;

        // Add data attributes for initialization
        $data_attrs = [
            'id' => esc_attr($instance_id),
            'class' => 'ztext',
            'data-ztext' => '',
            'data-ztext-effect' => $config['effect'],
            'data-ztext-depth' => $config['depth'],
            'data-ztext-direction' => $config['direction'],
            'data-ztext-layers' => $config['layers'],
            'data-ztext-fade' => $config['fade'] ? 'true' : 'false',
            'data-ztext-event' => $config['event'],
            'data-ztext-event-rotation' => $config['eventRotation'],
            'data-ztext-duration' => $config['duration'],
            'data-ztext-easing' => $config['easing'],
            'data-ztext-perspective' => $config['perspective'],
            'data-ztext-layer-gap' => $config['layerGap'],
        ];

        // Add conditional attributes
        if ($config['event'] !== 'none') {
            $data_attrs['data-ztext-event-direction'] = $config['eventDirection'];
        }

        if ($config['disabled'] !== false) {
            $data_attrs['data-ztext-disabled'] = $config['disabled'];
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
