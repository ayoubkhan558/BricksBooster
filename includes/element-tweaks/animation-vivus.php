<?php
class BricksBooster_Element_Tweaks_16 {
    private static $instance_count = 0;
    private $vivus_instances = [];
    private $has_vivus = false;

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_vivus_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_vivus_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_vivus_initialization'], 999);
            add_action('wp_footer', [$this, 'add_vivus_refresh_script'], 1000);
        }
    }

    /**
     * Enqueue Vivus.js assets
     */
    public function enqueue_vivus_assets() {
        // Check if WordPress functions are available
        if (!function_exists('wp_enqueue_script')) {
            return;
        }
        
        // Track enqueued assets to prevent duplicates
        static $enqueued = false;
        
        if (!$enqueued) {
            // Enqueue Vivus.js
            wp_enqueue_script(
                'vivus-js',
                'https://cdnjs.cloudflare.com/ajax/libs/vivus/0.4.6/vivus.min.js',
                [],
                '0.4.6',
                true
            );
            
            // Enqueue custom script
            wp_enqueue_script(
                'bricks-booster-vivus',
                BRICKSBOOSTER_PLUGIN_URL . 'assets/js/vivus.js',
                ['vivus-js'],
                BRICKSBOOSTER_VERSION,
                true
            );
            
            $enqueued = true;
        }
    }

    /**
     * Initialize animation controls for SVG elements
     */
    public function init_animation_controls() {
        if (!class_exists('Bricks\\Elements')) {
            return;
        }

        // Only add controls to SVG elements
        add_filter('bricks/elements/svg/control_groups', [$this, 'add_control_group'], 10);
        add_filter('bricks/elements/svg/controls', [$this, 'add_controls'], 10);
    }

    /**
     * Add Vivus control group
     */
    public function add_control_group($control_groups) {
        if (!is_array($control_groups)) {
            $control_groups = [];
        }

        $control_groups['bricksbooster_vivus'] = [
            'tab'   => 'content',
            'title' => esc_html__('SVG Animation', 'bricks-booster'),
            'icon'  => 'ti-layout-media-overlay-alt-2'
        ];

        return $control_groups;
    }

    /**
     * Add Vivus controls
     */
    public function add_controls($controls) {
        if (!is_array($controls)) {
            $controls = [];
        }

        // Enable Vivus
        $controls['_vivus_enable'] = [
            'tab'   => 'content',
            'group' => 'bricksbooster_vivus',
            'label' => esc_html__('Enable SVG Animation', 'bricks-booster'),
            'type'  => 'checkbox',
            'inline' => true,
            'description' => esc_html__('Animate the SVG paths on page load', 'bricks-booster')
        ];

        // Animation Type
        $controls['_vivus_type'] = [
            'tab'         => 'content',
            'group'       => 'bricksbooster_vivus',
            'label'       => esc_html__('Animation Type', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'delayed'   => esc_html__('Delayed', 'bricks-booster'),
                'async'     => esc_html__('Async', 'bricks-booster'),
                'oneByOne'  => esc_html__('One by One', 'bricks-booster'),
                'scenario'  => esc_html__('Scenario', 'bricks-booster'),
                'scenario-sync' => esc_html__('Scenario Sync', 'bricks-booster'),
                'custom'    => esc_html__('Custom', 'bricks-booster')
            ],
            'default'     => 'delayed',
            'required'    => ['_vivus_enable', '!=', ''],
            'inline'      => true,
        ];

        // Duration
        $controls['_vivus_duration'] = [
            'tab'         => 'content',
            'group'       => 'bricksbooster_vivus',
            'label'       => esc_html__('Duration (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 10000,
            'step'        => 50,
            'default'     => 200,
            'required'    => [
                ['_vivus_enable', '!=', ''],
                ['_vivus_type', '!=', 'custom']
            ],
            'inline'      => true,
        ];

        // Delay between path animations
        $controls['_vivus_delay'] = [
            'tab'         => 'content',
            'group'       => 'bricksbooster_vivus',
            'label'       => esc_html__('Delay Between Paths (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1000,
            'step'        => 10,
            'default'     => 10,
            'required'    => [
                ['_vivus_enable', '!=', ''],
                ['_vivus_type', 'in', ['delayed', 'oneByOne']]
            ],
            'inline'      => true,
        ];

        // Start Animation
        $controls['_vivus_start'] = [
            'tab'         => 'content',
            'group'       => 'bricksbooster_vivus',
            'label'       => esc_html__('Start Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'auto'      => esc_html__('Auto (on page load)', 'bricks-booster'),
                'manual'    => esc_html__('Manual (trigger with JavaScript)', 'bricks-booster'),
                'scroll'    => esc_html__('On Scroll Into View', 'bricks-booster'),
                'hover'     => esc_html__('On Hover', 'bricks-booster'),
                'click'     => esc_html__('On Click', 'bricks-booster')
            ],
            'default'     => 'auto',
            'required'    => ['_vivus_enable', '!=', ''],
            'inline'      => true,
        ];

        // Animation Direction
        $controls['_vivus_direction'] = [
            'tab'         => 'content',
            'group'       => 'bricksbooster_vivus',
            'label'       => esc_html__('Direction', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                '0' => esc_html__('Normal', 'bricks-booster'),
                '1' => esc_html__('Reverse', 'bricks-booster'),
                '2' => esc_html__('Sync', 'bricks-booster')
            ],
            'default'     => '0',
            'required'    => ['_vivus_enable', '!=', ''],
            'inline'      => true,
        ];

        // Self Destruction
        $controls['_vivus_self_destroy'] = [
            'tab'         => 'content',
            'group'       => 'bricksbooster_vivus',
            'label'       => esc_html__('Remove SVG Styles', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'false' => esc_html__('No', 'bricks-booster'),
                'true'  => esc_html__('Yes', 'bricks-booster')
            ],
            'default'     => 'false',
            'description' => esc_html__('Remove all styles on the SVG, and leave it as original', 'bricks-booster'),
            'required'    => ['_vivus_enable', '!=', ''],
            'inline'      => true,
        ];

        // Animation Color
        $controls['_vivus_anim_color'] = [
            'tab'         => 'content',
            'group'       => 'bricksbooster_vivus',
            'label'       => esc_html__('Animation Color', 'bricks-booster'),
            'type'        => 'color',
            'inline'      => true,
            'required'    => ['_vivus_enable', '!=', ''],
            'css'         => [
                [
                    'selector' => '',
                    'property' => '--vivus-color',
                ]
            ],
        ];

        // Stroke Width
        $controls['_vivus_stroke_width'] = [
            'tab'         => 'content',
            'group'       => 'bricksbooster_vivus',
            'label'       => esc_html__('Stroke Width', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0.1,
            'max'         => 10,
            'step'        => 0.1,
            'default'     => 1,
            'required'    => ['_vivus_enable', '!=', ''],
            'inline'      => true,
            'css'         => [
                [
                    'selector' => ' svg',
                    'property' => '--vivus-stroke-width',
                ]
            ],
        ];

        // Custom Options
        $controls['_vivus_custom_options'] = [
            'tab'         => 'content',
            'group'       => 'bricksbooster_vivus',
            'label'       => esc_html__('Custom Options', 'bricks-booster'),
            'type'        => 'code',
            'mode'        => 'json',
            'description' => esc_html__('Enter custom Vivus.js options as JSON. Will override other settings.', 'bricks-booster'),
            'required'    => [
                ['_vivus_enable', '!=', ''],
                ['_vivus_type', '=', 'custom']
            ],
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        // Only process SVG elements
        if (!isset($element->name) || $element->name !== 'svg') {
            return $html;
        }

        $settings = $element->settings;
        
        // Check if Vivus is enabled
        if (empty($settings['_vivus_enable'])) {
            return $html;
        }

        // Set flag to include initialization script
        $this->has_vivus = true;

        // Generate unique ID for this instance
        self::$instance_count++;
        $instance_id = 'vivus-' . self::$instance_count . '-' . uniqid();

        // Get animation settings with defaults
        $animation_type = $settings['_vivus_type'] ?? 'delayed';
        $duration = isset($settings['_vivus_duration']) ? intval($settings['_vivus_duration']) : 200;
        $delay = isset($settings['_vivus_delay']) ? intval($settings['_vivus_delay']) : 10;
        $start = $settings['_vivus_start'] ?? 'auto';
        $direction = $settings['_vivus_direction'] ?? '0';
        $self_destroy = $settings['_vivus_self_destroy'] ?? 'false';
        $anim_color = $settings['_vivus_anim_color'] ?? '';
        $stroke_width = $settings['_vivus_stroke_width'] ?? 1;
        $custom_options = $settings['_vivus_custom_options'] ?? '';

        // Prepare config
        $config = [
            'type' => $animation_type,
            'duration' => $duration,
            'delay' => $delay,
            'start' => $start,
            'direction' => $direction,
            'selfDestroy' => $self_destroy === 'true',
            'animColor' => $anim_color,
            'strokeWidth' => $stroke_width,
            'customOptions' => $custom_options,
            'elementId' => $instance_id
        ];

        // Store instance config for initialization
        $this->vivus_instances[$instance_id] = $config;

        // Add data attributes for initialization
        $data_attrs = [
            'id' => esc_attr($instance_id),
            'class' => 'vivus-target',
            'data-vivus' => '',
            'data-vivus-config' => wp_json_encode($config),
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
     * Add Vivus initialization script
     */
    public function add_vivus_initialization() {
        if (empty($this->vivus_instances) || !$this->has_vivus) {
            return;
        }
        
        // Prepare the initialization script
        $script = "<script>";
        $script .= "document.addEventListener('DOMContentLoaded', function() {";
        $script .= "if (typeof Vivus !== 'undefined') {";
        
        foreach ($this->vivus_instances as $id => $config) {
            $script .= "{"; // Start scope for this animation
            $script .= "const element = document.getElementById('" . esc_js($id) . "');";
            $script .= "if (element && !element.hasAttribute('data-vivus-initialized')) {";
            $script .= "element.setAttribute('data-vivus-initialized', 'true');";
            
            // Prepare options
            $script .= "const options = {";
            
            // Set animation type
            if ($config['type'] === 'custom' && !empty($config['customOptions'])) {
                $script .= "..." . $config['customOptions'] . ",";
            } else {
                $script .= "type: '" . esc_js($config['type']) . "',";
                $script .= "duration: " . intval($config['duration']) . ",";
                
                if (in_array($config['type'], ['delayed', 'oneByOne'])) {
                    $script .= "delay: " . intval($config['delay']) . ",";
                }
                
                $script .= "pathTimingFunction: Vivus.EASE_OUT,";
            }
            
            // Add direction
            $script .= "animTimingFunction: Vivus.EASE_OUT,";
            $script .= "start: 'manual',"; // We'll handle start manually
            $script .= "selfDestroy: " . ($config['selfDestroy'] ? 'true' : 'false') . ",";
            
            // Close options
            $script .= "};";
            
            // Initialize Vivus
            $script .= "const vivus = new Vivus('" . esc_js($id) . "', options);";
            
            // Handle start trigger
            switch ($config['start']) {
                case 'manual':
                    // Do nothing, will be started manually
                    break;
                    
                case 'scroll':
                    $script .= "const observer = new IntersectionObserver((entries) => {";
                    $script .= "entries.forEach(entry => {";
                    $script .= "if (entry.isIntersecting) {";
                    $script .= "vivus.play(" . floatval($config['direction']) . ");";
                    $script .= "observer.unobserve(entry.target);";
                    $script .= "}";
                    $script .= "});";
                    $script .= "}, { threshold: 0.1 });";
                    $script .= "observer.observe(element);";
                    break;
                    
                case 'hover':
                    $script .= "let played = false;";
                    $script .= "element.addEventListener('mouseenter', function() {";
                    $script .= "if (!played) {";
                    $script .= "vivus.play(" . floatval($config['direction']) . ");";
                    $script .= "played = true;";
                    $script .= "} else {";
                    $script .= "vivus.reset().play(" . (1 - floatval($config['direction'])) . ");";
                    $script .= "played = false;";
                    $script .= "}";
                    $script .= "});";
                    break;
                    
                case 'click':
                    $script .= "element.style.cursor = 'pointer';";
                    $script .= "let played = false;";
                    $script .= "element.addEventListener('click', function() {";
                    $script .= "if (!played) {";
                    $script .= "vivus.play(" . floatval($config['direction']) . ");";
                    $script .= "played = true;";
                    $script .= "} else {";
                    $script .= "vivus.reset().play(" . (1 - floatval($config['direction'])) . ");";
                    $script .= "played = false;";
                    $script .= "}";
                    $script .= "});";
                    break;
                    
                case 'auto':
                default:
                    $script .= "vivus.play(" . floatval($config['direction']) . ");";
                    break;
            }
            
            // Store Vivus instance for potential manual control
            $script .= "window['" . esc_js($id) . "_vivus'] = vivus;";
            
            $script .= "}"; // End element check
            $script .= "}"; // End scope for this animation
        }
        
        $script .= "}"; // End Vivus check
        $script .= "});"; // End DOMContentLoaded
        $script .= "</script>";
        
        echo $script;
    }

    /**
     * Add script to refresh Vivus after AJAX loads
     */
    public function add_vivus_refresh_script() {
        if (!$this->has_vivus) {
            return;
        }
        ?>
        <script>
        (function($) {
            $(document).on('bricks/ajax/after_load', function() {
                if (typeof Vivus !== 'undefined') {
                    // Re-initialize Vivus animations on new elements
                    $('[data-vivus]:not([data-vivus-initialized])').each(function() {
                        const $this = $(this);
                        const id = $this.attr('id');
                        const config = $this.data('vivus-config');
                        
                        if (config && id) {
                            $this.attr('data-vivus-initialized', 'true');
                            
                            // Re-apply the same animation logic as in initialization
                            const options = {
                                type: config.type,
                                duration: config.duration,
                                animTimingFunction: Vivus.EASE_OUT,
                                start: 'manual',
                                selfDestroy: config.selfDestroy
                            };
                            
                            if (['delayed', 'oneByOne'].includes(config.type)) {
                                options.delay = config.delay;
                            }
                            
                            if (config.type === 'custom' && config.customOptions) {
                                try {
                                    Object.assign(options, JSON.parse(config.customOptions));
                                } catch (e) {
                                    console.error('Error parsing custom Vivus options:', e);
                                }
                            }
                            
                            const vivus = new Vivus(id, options);
                            
                            // Handle start trigger
                            switch (config.start) {
                                case 'manual':
                                    // Do nothing, will be started manually
                                    break;
                                    
                                case 'scroll':
                                    const observer = new IntersectionObserver((entries) => {
                                        entries.forEach(entry => {
                                            if (entry.isIntersecting) {
                                                vivus.play(parseFloat(config.direction));
                                                observer.unobserve(entry.target);
                                            }
                                        });
                                    }, { threshold: 0.1 });
                                    observer.observe(document.getElementById(id));
                                    break;
                                    
                                case 'hover':
                                    let hoverPlayed = false;
                                    $this.on('mouseenter', function() {
                                        if (!hoverPlayed) {
                                            vivus.play(parseFloat(config.direction));
                                            hoverPlayed = true;
                                        } else {
                                            vivus.reset().play(1 - parseFloat(config.direction));
                                            hoverPlayed = false;
                                        }
                                    });
                                    break;
                                    
                                case 'click':
                                    $this.css('cursor', 'pointer');
                                    let clickPlayed = false;
                                    $this.on('click', function() {
                                        if (!clickPlayed) {
                                            vivus.play(parseFloat(config.direction));
                                            clickPlayed = true;
                                        } else {
                                            vivus.reset().play(1 - parseFloat(config.direction));
                                            clickPlayed = false;
                                        }
                                    });
                                    break;
                                    
                                case 'auto':
                                default:
                                    vivus.play(parseFloat(config.direction));
                                    break;
                            }
                            
                            // Store Vivus instance for potential manual control
                            window[id + '_vivus'] = vivus;
                        }
                    });
                }
            });
        })(jQuery);
        </script>
        <?php
    }
}
