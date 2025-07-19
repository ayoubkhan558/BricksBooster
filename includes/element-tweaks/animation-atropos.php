<?php
class BricksBooster_Element_Tweaks_9 {
    private static $instance_count = 0;
    private $atropos_instances = [];

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_atropos_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_atropos_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_atropos_initialization'], 999);
            add_action('wp_footer', [$this, 'add_atropos_refresh_script'], 1000);
        }
    }

    /**
     * Enqueue Atropos.js assets
     */
    public function enqueue_atropos_assets() {
        // Check if WordPress functions are available
        if (!function_exists('wp_enqueue_style') || !function_exists('wp_enqueue_script')) {
            return;
        }
        
        // Track enqueued assets to prevent duplicates
        static $enqueued = false;
        
        if (!$enqueued) {
            // Enqueue Atropos CSS
            wp_enqueue_style(
                'atropos-css',
                'https://cdn.jsdelivr.net/npm/atropos@2.0.2/atropos.min.css',
                [],
                '2.0.2'
            );
            
            // Enqueue Atropos JS
            wp_enqueue_script(
                'atropos-js',
                'https://cdn.jsdelivr.net/npm/atropos@2.0.2/atropos.min.js',
                [],
                '2.0.2',
                true
            );
            
            $enqueued = true;
        }
    }

    /**
     * Add Atropos initialization script
     */
    public function add_atropos_initialization() {
        if (empty($this->atropos_instances)) {
            return;
        }
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Atropos !== 'undefined') {
                // Initialize global instances object
                window.atroposInstances = window.atroposInstances || {};
                
                <?php foreach ($this->atropos_instances as $id => $config): ?>
                (function() {
                    var el = document.getElementById('<?php echo esc_js($id); ?>');
                    if (el) {
                        var config = <?php echo wp_json_encode($config, JSON_HEX_QUOT | JSON_HEX_APOS); ?>;
                        
                        // Convert string values to numbers where appropriate
                        var numericProps = ['rotateXMax', 'rotateYMax', 'rotateXMin', 'rotateYMin', 
                                          'shadowScale', 'shadowOffsetX', 'shadowOffsetY', 'duration'];
                        
                        numericProps.forEach(function(prop) {
                            if (config[prop] !== undefined && config[prop] !== null) {
                                var numValue = parseFloat(config[prop]);
                                if (!isNaN(numValue)) {
                                    config[prop] = numValue;
                                }
                            }
                        });
                        
                        // Convert boolean strings to booleans
                        var booleanProps = ['activeOffset', 'shadow', 'highlight', 'rotateTouch', 'rotateLock'];
                        booleanProps.forEach(function(prop) {
                            if (config[prop] !== undefined) {
                                if (typeof config[prop] === 'string') {
                                    config[prop] = config[prop] === 'true';
                                }
                            }
                        });
                        
                        // Initialize Atropos instance with error handling
                        try {
                            window.atroposInstances['<?php echo esc_js($id); ?>'] = new Atropos(el, config);
                        } catch (error) {
                            console.warn('Failed to initialize Atropos for element:', '<?php echo esc_js($id); ?>', error);
                        }
                    }
                })();
                <?php endforeach; ?>
                
                // Handle window resize with debouncing
                var resizeTimer;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        if (window.atroposInstances) {
                            Object.values(window.atroposInstances).forEach(function(instance) {
                                if (instance && typeof instance.refresh === 'function') {
                                    try {
                                        instance.refresh();
                                    } catch (error) {
                                        console.warn('Failed to refresh Atropos instance:', error);
                                    }
                                }
                            });
                        }
                    }, 250);
                });
            } else {
                console.warn('Atropos library not loaded');
            }
        });
        </script>
        <?php
    }

    /**
     * Add script to refresh Atropos after AJAX loads
     */
    public function add_atropos_refresh_script() {
        ?>
        <script>
        (function($) {
            if (typeof $ !== 'undefined') {
                $(document).on('bricks/ajax/after_load', function() {
                    if (typeof Atropos !== 'undefined' && window.atroposInstances) {
                        Object.values(window.atroposInstances).forEach(function(instance) {
                            if (instance && typeof instance.refresh === 'function') {
                                try {
                                    instance.refresh();
                                } catch (error) {
                                    console.warn('Failed to refresh Atropos instance after AJAX:', error);
                                }
                            }
                        });
                    }
                });
            }
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
     * Add Atropos control group
     */
    public function add_control_group($control_groups) {
        if (!is_array($control_groups)) {
            $control_groups = [];
        }

        $control_groups['bricksbooster_atropos'] = [
            'tab'   => 'style',
            'title' => esc_html__('Atropos.js', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add Atropos controls
     */
    public function add_controls($controls) {
        if (!is_array($controls)) {
            $controls = [];
        }

        // Enable Atropos
        $controls['_atropos_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_atropos',
            'label' => esc_html__('Enable Atropos', 'bricks-booster'),
            'type'  => 'checkbox',
        ];

        // Active Offset
        $controls['_atropos_active_offset'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_atropos',
            'label'       => esc_html__('Active on Hover', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'description' => esc_html__('Enable hover effect', 'bricks-booster'),
            'required'    => ['_atropos_enable', '!=', ''],
            'inline'      => true,
        ];

        // Shadow
        $controls['_atropos_shadow'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_atropos',
            'label'       => esc_html__('Enable Shadow', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'required'    => ['_atropos_enable', '!=', ''],
            'inline'      => true,
        ];

        // Highlight
        $controls['_atropos_highlight'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_atropos',
            'label'       => esc_html__('Highlight Content', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'required'    => ['_atropos_enable', '!=', ''],
            'inline'      => true,
        ];

        // Rotate X Max
        $controls['_atropos_rotate_x_max'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_atropos',
            'label'       => esc_html__('Max X Rotation', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 90,
            'step'        => 1,
            'default'     => 10,
            'description' => esc_html__('Maximum X rotation in degrees', 'bricks-booster'),
            'required'    => ['_atropos_enable', '!=', ''],
            'inline'      => true,
        ];

        // Rotate Y Max
        $controls['_atropos_rotate_y_max'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_atropos',
            'label'       => esc_html__('Max Y Rotation', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 90,
            'step'        => 1,
            'default'     => 10,
            'description' => esc_html__('Maximum Y rotation in degrees', 'bricks-booster'),
            'required'    => ['_atropos_enable', '!=', ''],
            'inline'      => true,
        ];

        // Shadow Scale
        $controls['_atropos_shadow_scale'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_atropos',
            'label'       => esc_html__('Shadow Scale', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 2,
            'step'        => 0.1,
            'default'     => 1.2,
            'required'    => ['_atropos_enable', '!=', ''],
            'inline'      => true,
        ];

        // Duration
        $controls['_atropos_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_atropos',
            'label'       => esc_html__('Animation Duration', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 1000,
            'step'        => 50,
            'default'     => 300,
            'description' => esc_html__('Animation duration in milliseconds', 'bricks-booster'),
            'required'    => ['_atropos_enable', '!=', ''],
            'inline'      => true,
        ];

        // Rotate Touch
        $controls['_atropos_rotate_touch'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_atropos',
            'label'       => esc_html__('Touch Rotation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Enable', 'bricks-booster'),
                'false' => esc_html__('Disable', 'bricks-booster'),
            ],
            'default'     => 'true',
            'description' => esc_html__('Enable rotation on touch devices', 'bricks-booster'),
            'required'    => ['_atropos_enable', '!=', ''],
            'inline'      => true,
        ];

        // Rotate Lock
        $controls['_atropos_rotate_lock'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_atropos',
            'label'       => esc_html__('Lock Rotation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'false' => esc_html__('No', 'bricks-booster'),
                'true'  => esc_html__('Yes', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Lock rotation to one axis at a time', 'bricks-booster'),
            'required'    => ['_atropos_enable', '!=', ''],
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
        
        // Check if Atropos is enabled
        if (empty($settings['_atropos_enable'])) {
            return $html;
        }

        // Generate unique ID for this instance
        self::$instance_count++;
        $instance_id = 'atropos-' . self::$instance_count . '-' . uniqid();

        // Get animation settings with proper sanitization
        $config = [
            'activeOffset' => sanitize_text_field($settings['_atropos_active_offset'] ?? 'true'),
            'shadow' => sanitize_text_field($settings['_atropos_shadow'] ?? 'true'),
            'highlight' => sanitize_text_field($settings['_atropos_highlight'] ?? 'true'),
            'rotateXMax' => floatval($settings['_atropos_rotate_x_max'] ?? 10),
            'rotateYMax' => floatval($settings['_atropos_rotate_y_max'] ?? 10),
            'shadowScale' => floatval($settings['_atropos_shadow_scale'] ?? 1.2),
            'duration' => intval($settings['_atropos_duration'] ?? 300),
            'rotateTouch' => sanitize_text_field($settings['_atropos_rotate_touch'] ?? 'true'),
            'rotateLock' => sanitize_text_field($settings['_atropos_rotate_lock'] ?? 'false'),
        ];

        // Store instance config for initialization
        $this->atropos_instances[$instance_id] = $config;

        // Wrap the element with Atropos container
        $html = sprintf(
            '<div id="%s" class="atropos">' . 
            '  <div class="atropos-scale">' . 
            '    <div class="atropos-rotate">' . 
            '      <div class="atropos-inner">%s</div>' . 
            '    </div>' . 
            '  </div>' . 
            '</div>',
            esc_attr($instance_id),
            $html
        );

        return $html;
    }
}