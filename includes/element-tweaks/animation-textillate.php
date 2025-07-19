<?php
class BricksBooster_Element_Tweaks_13 {
    private static $instance_count = 0;
    private $textillate_instances = [];
    private $has_textillate = false;

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_textillate_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_textillate_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_textillate_initialization'], 999);
            add_action('wp_footer', [$this, 'add_textillate_refresh_script'], 1000);
        }
    }

    /**
     * Enqueue Textillate.js and dependencies
     */
    public function enqueue_textillate_assets() {
        // Check if WordPress functions are available
        if (!function_exists('wp_enqueue_script')) {
            return;
        }
        
        // Track enqueued assets to prevent duplicates
        static $enqueued = false;
        
        if (!$enqueued) {
            // Enqueue jQuery (required by Textillate)
            wp_enqueue_script('jquery');
            
            // Enqueue Animate.css
            wp_enqueue_style(
                'animate-css',
                'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css',
                [],
                '4.1.1'
            );
            
            // Enqueue Lettering.js (required by Textillate)
            wp_enqueue_script(
                'lettering-js',
                'https://cdnjs.cloudflare.com/ajax/libs/lettering.js/0.7.0/jquery.lettering.min.js',
                ['jquery'],
                '0.7.0',
                true
            );
            
            // Enqueue Textillate JS
            wp_enqueue_script(
                'textillate-js',
                'https://cdnjs.cloudflare.com/ajax/libs/textillate/0.4.0/jquery.textillate.min.js',
                ['jquery', 'lettering-js'],
                '0.4.0',
                true
            );
            
            $enqueued = true;
        }
    }

    /**
     * Add Textillate initialization script
     */
    public function add_textillate_initialization() {
        if (empty($this->textillate_instances) || !$this->has_textillate) {
            return;
        }
        
        // Prepare the initialization script
        $script = "<script>";
        $script .= "jQuery(document).ready(function($) {";
        
        foreach ($this->textillate_instances as $id => $config) {
            $script .= "var element = $('#" . esc_js($id) . "');";
            $script .= "if (element.length && !element.hasClass('textillate-initialized')) {";
            $script .= "element.addClass('textillate-initialized');";
            $script .= "element.textillate(" . wp_json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT) . ");";
            $script .= "}";
        }
        
        $script .= "});"; // End document ready
        $script .= "</script>";
        
        echo $script;
    }

    /**
     * Add script to refresh Textillate after AJAX loads
     */
    public function add_textillate_refresh_script() {
        if (!$this->has_textillate) {
            return;
        }
        ?>
        <script>
        (function($) {
            $(document).on('bricks/ajax/after_load', function() {
                $('.textillate-target:not(.textillate-initialized)').each(function() {
                    var $this = $(this);
                    var config = $this.data('textillate-config');
                    if (config) {
                        $this.addClass('textillate-initialized');
                        $this.textillate(JSON.parse(config));
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Initialize animation controls for all elements
     */
    public function init_animation_controls() {
        if (!class_exists('Bricks\\Elements')) {
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
     * Add Textillate control group
     */
    public function add_control_group($control_groups) {
        if (!is_array($control_groups)) {
            $control_groups = [];
        }

        $control_groups['bricksbooster_textillate'] = [
            'tab'   => 'style',
            'title' => esc_html__('Textillate.js', 'bricks-booster'),
            'icon'  => 'ti-text'
        ];

        return $control_groups;
    }

    /**
     * Add Textillate controls
     */
    public function add_controls($controls) {
        if (!is_array($controls)) {
            $controls = [];
        }

        // Enable Textillate
        $controls['_textillate_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_textillate',
            'label' => esc_html__('Enable Textillate', 'bricks-booster'),
            'type'  => 'checkbox',
            'icon'  => 'ti-text'
        ];

        // Animation Type Group
        $controls['_textillate_animation_group'] = [
            'tab'      => 'style',
            'group'    => 'bricksbooster_textillate',
            'label'    => esc_html__('Animation Type', 'bricks-booster'),
            'type'     => 'group',
            'required' => ['_textillate_enable', '!=', ''],
        ];

        // In Animation
        $controls['_textillate_in_effect'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('In Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => $this->get_animation_options('in'),
            'default'     => 'fadeIn',
            'required'    => ['_textillate_enable', '!=', ''],
            'inline'      => true,
            'placeholder' => esc_html__('Select in animation', 'bricks-booster'),
        ];

        // Out Animation
        $controls['_textillate_out_effect'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Out Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => array_merge(
                ['' => esc_html__('None', 'bricks-booster')],
                $this->get_animation_options('out')
            ),
            'default'     => '',
            'required'    => ['_textillate_enable', '!=', ''],
            'inline'      => true,
            'placeholder' => esc_html__('Select out animation', 'bricks-booster'),
        ];

        // Animation Settings Group
        $controls['_textillate_settings_group'] = [
            'tab'      => 'style',
            'group'    => 'bricksbooster_textillate',
            'label'    => esc_html__('Animation Settings', 'bricks-booster'),
            'type'     => 'group',
            'required' => ['_textillate_enable', '!=', ''],
        ];

        // Loop
        $controls['_textillate_loop'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Loop', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'required'    => ['_textillate_enable', '!=', ''],
            'inline'      => true,
        ];

        // Initial Delay
        $controls['_textillate_initial_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Initial Delay (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 10000,
            'step'        => 100,
            'default'     => 0,
            'required'    => ['_textillate_enable', '!=', ''],
            'inline'      => true,
        ];

        // Auto Start
        $controls['_textillate_auto_start'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Auto Start', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'description' => esc_html__('Start animation on page load', 'bricks-booster'),
            'required'    => ['_textillate_enable', '!=', ''],
            'inline'      => true,
        ];

        // Start Paused
        $controls['_textillate_start_paused'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Start Paused', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Start with animation paused', 'bricks-booster'),
            'required'    => [
                ['_textillate_enable', '!=', ''],
                ['_textillate_auto_start', '=', 'true']
            ],
            'inline'      => true,
        ];

        // In Effect Settings Group
        $controls['_textillate_in_settings_group'] = [
            'tab'      => 'style',
            'group'    => 'bricksbooster_textillate',
            'label'    => esc_html__('In Effect Settings', 'bricks-booster'),
            'type'     => 'group',
            'required' => [
                ['_textillate_enable', '!=', ''],
                ['_textillate_in_effect', '!=', '']
            ],
        ];

        // In Delay
        $controls['_textillate_in_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('In Delay (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 10000,
            'step'        => 50,
            'default'     => 50,
            'required'    => [
                ['_textillate_enable', '!=', ''],
                ['_textillate_in_effect', '!=', '']
            ],
            'inline'      => true,
        ];

        // In Delay Scale
        $controls['_textillate_in_delay_scale'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('In Delay Scale', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0.1,
            'max'         => 10,
            'step'        => 0.1,
            'default'     => 1.0,
            'description' => esc_html__('Multiplier for delay between elements', 'bricks-booster'),
            'required'    => [
                ['_textillate_enable', '!=', ''],
                ['_textillate_in_effect', '!=', '']
            ],
            'inline'      => true,
        ];

        // In Sync
        $controls['_textillate_in_sync'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('In Sync', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Animate all elements at the same time', 'bricks-booster'),
            'required'    => [
                ['_textillate_enable', '!=', ''],
                ['_textillate_in_effect', '!=', '']
            ],
            'inline'      => true,
        ];

        // Out Effect Settings Group (only show if out effect is selected)
        $controls['_textillate_out_settings_group'] = [
            'tab'      => 'style',
            'group'    => 'bricksbooster_textillate',
            'label'    => esc_html__('Out Effect Settings', 'bricks-booster'),
            'type'     => 'group',
            'required' => [
                ['_textillate_enable', '!=', ''],
                ['_textillate_out_effect', '!=', '']
            ],
        ];

        // Out Delay
        $controls['_textillate_out_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Out Delay (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 10000,
            'step'        => 50,
            'default'     => 50,
            'required'    => [
                ['_textillate_enable', '!=', ''],
                ['_textillate_out_effect', '!=', '']
            ],
            'inline'      => true,
        ];

        // Out Delay Scale
        $controls['_textillate_out_delay_scale'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Out Delay Scale', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0.1,
            'max'         => 10,
            'step'        => 0.1,
            'default'     => 1.0,
            'description' => esc_html__('Multiplier for delay between elements', 'bricks-booster'),
            'required'    => [
                ['_textillate_enable', '!=', ''],
                ['_textillate_out_effect', '!=', '']
            ],
            'inline'      => true,
        ];

        // Out Sync
        $controls['_textillate_out_sync'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Out Sync', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Animate all elements at the same time', 'bricks-booster'),
            'required'    => [
                ['_textillate_enable', '!=', ''],
                ['_textillate_out_effect', '!=', '']
            ],
            'inline'      => true,
        ];

        // Animation Trigger Group
        $controls['_textillate_trigger_group'] = [
            'tab'      => 'style',
            'group'    => 'bricksbooster_textillate',
            'label'    => esc_html__('Animation Trigger', 'bricks-booster'),
            'type'     => 'group',
            'required' => ['_textillate_enable', '!=', ''],
        ];

        // Trigger
        $controls['_textillate_trigger'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Trigger', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'auto'    => esc_html__('Auto (on page load)', 'bricks-booster'),
                'hover'   => esc_html__('On Hover', 'bricks-booster'),
                'click'   => esc_html__('On Click', 'bricks-booster'),
                'scroll'  => esc_html__('On Scroll Into View', 'bricks-booster'),
            ],
            'default'     => 'auto',
            'required'    => ['_textillate_enable', '!=', ''],
            'inline'      => true,
        ];

        // Disable on Mobile
        $controls['_textillate_disable_mobile'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_textillate',
            'label'       => esc_html__('Disable on Mobile', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Disable effect on mobile devices', 'bricks-booster'),
            'required'    => ['_textillate_enable', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Get animation options for select field
     */
    private function get_animation_options($type = 'in') {
        $options = [
            'fadeIn' => 'Fade In',
            'fadeInUp' => 'Fade In Up',
            'fadeInDown' => 'Fade In Down',
            'fadeInLeft' => 'Fade In Left',
            'fadeInRight' => 'Fade In Right',
            'fadeInUpBig' => 'Fade In Up Big',
            'fadeInDownBig' => 'Fade In Down Big',
            'fadeInLeftBig' => 'Fade In Left Big',
            'fadeInRightBig' => 'Fade In Right Big',
            'bounceIn' => 'Bounce In',
            'bounceInUp' => 'Bounce In Up',
            'bounceInDown' => 'Bounce In Down',
            'bounceInLeft' => 'Bounce In Left',
            'bounceInRight' => 'Bounce In Right',
            'flipInX' => 'Flip In X',
            'flipInY' => 'Flip In Y',
            'lightSpeedIn' => 'Light Speed In',
            'rotateIn' => 'Rotate In',
            'rotateInDownLeft' => 'Rotate In Down Left',
            'rotateInDownRight' => 'Rotate In Down Right',
            'rotateInUpLeft' => 'Rotate In Up Left',
            'rotateInUpRight' => 'Rotate In Up Right',
            'rollIn' => 'Roll In',
            'zoomIn' => 'Zoom In',
            'zoomInDown' => 'Zoom In Down',
            'zoomInLeft' => 'Zoom In Left',
            'zoomInRight' => 'Zoom In Right',
            'zoomInUp' => 'Zoom In Up',
            'slideInDown' => 'Slide In Down',
            'slideInLeft' => 'Slide In Left',
            'slideInRight' => 'Slide In Right',
            'slideInUp' => 'Slide In Up',
        ];

        if ($type === 'out') {
            $out_options = [];
            foreach ($options as $key => $label) {
                $out_key = str_replace('In', 'Out', $key);
                $out_label = str_replace('In', 'Out', $label);
                $out_options[$out_key] = $out_label;
            }
            return $out_options;
        }

        return $options;
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
        
        // Check if Textillate is enabled
        if (empty($settings['_textillate_enable'])) {
            return $html;
        }

        // Set flag to include initialization script
        $this->has_textillate = true;

        // Generate unique ID for this instance
        self::$instance_count++;
        $instance_id = 'textillate-' . self::$instance_count . '-' . uniqid();

        // Get animation settings with proper sanitization
        $in_effect = sanitize_text_field($settings['_textillate_in_effect'] ?? 'fadeIn');
        $out_effect = sanitize_text_field($settings['_textillate_out_effect'] ?? '');
        
        // Prepare config
        $config = [
            'loop' => sanitize_text_field($settings['_textillate_loop'] ?? 'false') === 'true',
            'autoStart' => sanitize_text_field($settings['_textillate_auto_start'] ?? 'true') === 'true',
            'initialDelay' => intval($settings['_textillate_initial_delay'] ?? 0),
            'in' => [
                'effect' => $in_effect,
                'delayScale' => floatval($settings['_textillate_in_delay_scale'] ?? 1.0),
                'delay' => intval($settings['_textillate_in_delay'] ?? 50),
                'sync' => sanitize_text_field($settings['_textillate_in_sync'] ?? 'false') === 'true',
            ]
        ];

        // Add out effect if specified
        if (!empty($out_effect)) {
            $config['out'] = [
                'effect' => $out_effect,
                'delayScale' => floatval($settings['_textillate_out_delay_scale'] ?? 1.0),
                'delay' => intval($settings['_textillate_out_delay'] ?? 50),
                'sync' => sanitize_text_field($settings['_textillate_out_sync'] ?? 'false') === 'true',
            ];
        }

        // Handle start paused
        if (isset($settings['_textillate_start_paused']) && $settings['_textillate_start_paused'] === 'true') {
            $config['autoStart'] = false;
        }

        // Handle mobile disable
        if (sanitize_text_field($settings['_textillate_disable_mobile'] ?? 'false') === 'true') {
            $config['mobileDisabled'] = true;
        }

        // Store instance config for initialization
        $this->textillate_instances[$instance_id] = $config;

        // Add data attributes for initialization
        $data_attrs = [
            'id' => esc_attr($instance_id),
            'class' => 'textillate-target',
            'data-textillate-config' => wp_json_encode($config),
        ];

        // Add trigger class if needed
        $trigger = sanitize_text_field($settings['_textillate_trigger'] ?? 'auto');
        if ($trigger !== 'auto') {
            $data_attrs['data-textillate-trigger'] = $trigger;
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
