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
        // Atropos from CDN
        wp_enqueue_style(
            'atropos-css',
            'https://cdn.jsdelivr.net/npm/atropos@1.0.2/atropos.min.css',
            [],
            '1.0.2'
        );
        
        wp_enqueue_script(
            'atropos-js',
            'https://cdn.jsdelivr.net/npm/atropos@1.0.2/atropos.min.js',
            [],
            '1.0.2',
            true
        );
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
                <?php foreach ($this->atropos_instances as $id => $config): ?>
                (function() {
                    var el = document.getElementById('atropos-<?php echo esc_js($id); ?>');
                    if (el) {
                        var config = <?php echo wp_json_encode($config); ?>;
                        
                        // Convert string values to numbers where appropriate
                        var numericProps = ['rotateXMax', 'rotateYMax', 'rotateXMin', 'rotateYMin', 'rotateXInvert', 'rotateYInvert', 
                                          'shadowScale', 'shadowOffsetX', 'shadowOffsetY', 'duration', 'rotateTouch'];
                        
                        numericProps.forEach(function(prop) {
                            if (config[prop] !== undefined) {
                                config[prop] = parseFloat(config[prop]);
                            }
                        });
                        
                        // Convert boolean strings to booleans
                        var booleanProps = ['activeOffset', 'shadow', 'highlight', 'rotateTouch', 'rotateLock'];
                        booleanProps.forEach(function(prop) {
                            if (config[prop] !== undefined) {
                                config[prop] = config[prop] === 'true' || config[prop] === true;
                            }
                        });
                        
                        // Initialize Atropos instance
                        window.atroposInstances = window.atroposInstances || {};
                        window.atroposInstances['<?php echo esc_js($id); ?>'] = new Atropos(el, config);
                    }
                })();
                <?php endforeach; ?>
                
                // Handle window resize
                var resizeTimer;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        if (window.atroposInstances) {
                            Object.values(window.atroposInstances).forEach(function(instance) {
                                if (instance && typeof instance.update === 'function') {
                                    instance.update();
                                }
                            });
                        }
                    }, 250);
                });
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
            $(document).on('bricks/ajax/after_load', function() {
                if (typeof Atropos !== 'undefined' && window.atroposInstances) {
                    Object.values(window.atroposInstances).forEach(function(instance) {
                        if (instance && typeof instance.update === 'function') {
                            instance.update();
                        }
                    });
                }
            });
        })(jQuery);
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
        $elements = \Bricks\Elements::$elements;
        $names = array_keys($elements);

        // Add control groups and controls to all elements
        foreach ($names as $name) {
            add_filter("bricks/elements/{$name}/control_groups", [$this, 'add_control_group'], 10);
            add_filter("bricks/elements/{$name}/controls", [$this, 'add_controls'], 10);
        }
    }

    /**
     * Add Atropos control group
     */
    public function add_control_group($control_groups) {
        $control_groups['bricksbooster_atropos'] = [
            'tab'   => 'style',
            'title' => esc_html__('Atropos.js', 'bricks-booster'),
            'icon'  => 'ti-layout-accordion-merged'
        ];

        return $control_groups;
    }

    /**
     * Add Atropos controls
     */
    public function add_controls($controls) {
        // Enable Atropos
        $controls['_atropos_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_atropos',
            'label' => esc_html__('Enable Atropos', 'bricks-booster'),
            'type'  => 'checkbox',
            'icon'  => 'ti-layout-accordion-merged'
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
        $settings = $element->settings;
        
        // Check if Atropos is enabled
        if (empty($settings['_atropos_enable'])) {
            return $html;
        }

        // Generate unique ID for this instance
        self::$instance_count++;
        $instance_id = 'atropos-' . self::$instance_count . '-' . uniqid();

        // Get animation settings
        $config = [
            'activeOffset' => $settings['_atropos_active_offset'] ?? 'true',
            'shadow' => $settings['_atropos_shadow'] ?? 'true',
            'highlight' => $settings['_atropos_highlight'] ?? 'true',
            'rotateXMax' => $settings['_atropos_rotate_x_max'] ?? 10,
            'rotateYMax' => $settings['_atropos_rotate_y_max'] ?? 10,
            'shadowScale' => $settings['_atropos_shadow_scale'] ?? 1.2,
            'duration' => $settings['_atropos_duration'] ?? 300,
            'rotateTouch' => $settings['_atropos_rotate_touch'] ?? 'true',
            'rotateLock' => $settings['_atropos_rotate_lock'] ?? 'false',
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
