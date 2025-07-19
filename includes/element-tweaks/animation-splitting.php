<?php
class BricksBooster_Element_Tweaks_12 {
    private static $instance_count = 0;
    private $splitting_instances = [];
    private $has_splitting = false;

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_splitting_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_splitting_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_splitting_initialization'], 999);
            add_action('wp_footer', [$this, 'add_splitting_refresh_script'], 1000);
        }
    }

    /**
     * Enqueue Splitting.js assets
     */
    public function enqueue_splitting_assets() {
        // Check if WordPress functions are available
        if (!function_exists('wp_enqueue_script')) {
            return;
        }
        
        // Track enqueued assets to prevent duplicates
        static $enqueued = false;
        
        if (!$enqueued) {
            // Enqueue Splitting JS
            wp_enqueue_script(
                'splitting-js',
                'https://unpkg.com/splitting/dist/splitting.min.js',
                [],
                '1.4.1',
                true
            );
            
            // Enqueue Splitting CSS if needed
            wp_enqueue_style(
                'splitting-css',
                'https://unpkg.com/splitting/dist/splitting-cells.css',
                [],
                '1.4.1'
            );
            
            $enqueued = true;
        }
    }

    /**
     * Add Splitting initialization script
     */
    public function add_splitting_initialization() {
        if (empty($this->splitting_instances) || !$this->has_splitting) {
            return;
        }
        
        // Prepare the initialization script
        $script = "<script>";
        $script .= "document.addEventListener('DOMContentLoaded', function() {";
        $script .= "if (typeof Splitting !== 'undefined') {";
        
        foreach ($this->splitting_instances as $id => $config) {
            $script .= "var element = document.getElementById('" . esc_js($id) . "');";
            $script .= "if (element && !element.hasAttribute('data-splitting-processed')) {";
            $script .= "element.setAttribute('data-splitting-processed', 'true');";
            $script .= "Splitting({";
            $script .= "target: element,";
            $script .= "by: '" . esc_js($config['by']) . "',";
            
            if (!empty($config['by']) && $config['by'] === 'chars') {
                $script .= "charClass: 'char',";
                $script .= "charType: 'letter',";
                $script .= "position: 'relative',";
                $script .= "whitespace: 'normal',";
                $script .= "prepend: false,";
                $script .= "append: false,";
                $script .= "whitespace: 'normal',";
                $script .= "position: 'relative',";
                $script .= "display: 'inline-block',";
            }
            
            if (!empty($config['rows']) || !empty($config['columns'])) {
                $script .= "rows: " . (int)$config['rows'] . ",";
                $script .= "columns: " . (int)$config['columns'] . ",";
                $script .= "image: true,";
                $script .= "position: 'relative',";
                $script .= "display: 'block',";
                $script .= "overflow: 'hidden',";
            }
            
            $script .= "});"; // End Splitting config
            $script .= "}"; // End element check
        }
        
        $script .= "}"; // End Splitting check
        $script .= "});"; // End DOMContentLoaded
        $script .= "</script>";
        
        echo $script;
    }

    /**
     * Add script to refresh Splitting after AJAX loads
     */
    public function add_splitting_refresh_script() {
        if (!$this->has_splitting) {
            return;
        }
        ?>
        <script>
        (function($) {
            $(document).on('bricks/ajax/after_load', function() {
                if (typeof Splitting !== 'undefined') {
                    // Re-initialize Splitting on all elements with data-splitting attribute
                    $('[data-splitting]').each(function() {
                        if (!this.hasAttribute('data-splitting-processed')) {
                            this.setAttribute('data-splitting-processed', 'true');
                            Splitting({
                                target: this,
                                by: this.getAttribute('data-splitting') || 'chars'
                            });
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
     * Add Splitting control group
     */
    public function add_control_group($control_groups) {
        if (!is_array($control_groups)) {
            $control_groups = [];
        }

        $control_groups['bricksbooster_splitting'] = [
            'tab'   => 'style',
            'title' => esc_html__('Splitting.js', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add Splitting controls
     */
    public function add_controls($controls) {
        if (!is_array($controls)) {
            $controls = [];
        }

        // Enable Splitting
        $controls['_splitting_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_splitting',
            'label' => esc_html__('Enable Splitting', 'bricks-booster'),
            'type'  => 'checkbox',
            'icon'  => 'ti-split-h'
        ];

        // Split Type
        $controls['_splitting_type'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
            'label'       => esc_html__('Split Type', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'chars'   => esc_html__('Characters', 'bricks-booster'),
                'words'   => esc_html__('Words', 'bricks-booster'),
                'lines'   => esc_html__('Lines', 'bricks-booster'),
                'grid'    => esc_html__('Grid (for images)', 'bricks-booster'),
            ],
            'default'     => 'chars',
            'required'    => ['_splitting_enable', '!=', ''],
            'inline'      => true,
        ];

        // Grid Rows (only for grid type)
        $controls['_splitting_grid_rows'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
            'label'       => esc_html__('Grid Rows', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 1,
            'max'         => 20,
            'default'     => 4,
            'description' => esc_html__('Number of rows for grid splitting', 'bricks-booster'),
            'required'    => [
                ['_splitting_enable', '!=', ''],
                ['_splitting_type', '=', 'grid']
            ],
            'inline'      => true,
        ];

        // Grid Columns (only for grid type)
        $controls['_splitting_grid_columns'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
            'label'       => esc_html__('Grid Columns', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 1,
            'max'         => 20,
            'default'     => 4,
            'description' => esc_html__('Number of columns for grid splitting', 'bricks-booster'),
            'required'    => [
                ['_splitting_enable', '!=', ''],
                ['_splitting_type', '=', 'grid']
            ],
            'inline'      => true,
        ];

        // Animation Type
        $controls['_splitting_animation'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
            'label'       => esc_html__('Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'none'       => esc_html__('None (just split)', 'bricks-booster'),
                'fade'       => esc_html__('Fade In', 'bricks-booster'),
                'slide-up'   => esc_html__('Slide Up', 'bricks-booster'),
                'slide-down' => esc_html__('Slide Down', 'bricks-booster'),
                'slide-left' => esc_html__('Slide Left', 'bricks-booster'),
                'slide-right'=> esc_html__('Slide Right', 'bricks-booster'),
                'scale'      => esc_html__('Scale', 'bricks-booster'),
                'rotate'     => esc_html__('Rotate', 'bricks-booster'),
            ],
            'default'     => 'none',
            'required'    => ['_splitting_enable', '!=', ''],
            'inline'      => true,
        ];

        // Animation Duration
        $controls['_splitting_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
            'label'       => esc_html__('Duration (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 5000,
            'step'        => 100,
            'default'     => 1000,
            'description' => esc_html__('Animation duration in milliseconds', 'bricks-booster'),
            'required'    => [
                ['_splitting_enable', '!=', ''],
                ['_splitting_animation', '!=', 'none']
            ],
            'inline'      => true,
        ];

        // Animation Delay
        $controls['_splitting_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
            'label'       => esc_html__('Delay (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1000,
            'step'        => 50,
            'default'     => 50,
            'description' => esc_html__('Delay between animations in milliseconds', 'bricks-booster'),
            'required'    => [
                ['_splitting_enable', '!=', ''],
                ['_splitting_animation', '!=', 'none']
            ],
            'inline'      => true,
        ];

        // Stagger Direction
        $controls['_splitting_stagger_direction'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
            'label'       => esc_html__('Stagger Direction', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'forward'  => esc_html__('Forward', 'bricks-booster'),
                'backward' => esc_html__('Backward', 'bricks-booster'),
                'random'   => esc_html__('Random', 'bricks-booster'),
                'center'   => esc_html__('Center Out', 'bricks-booster'),
                'edges'    => esc_html__('Edges In', 'bricks-booster'),
            ],
            'default'     => 'forward',
            'required'    => [
                ['_splitting_enable', '!=', ''],
                ['_splitting_animation', '!=', 'none']
            ],
            'inline'      => true,
        ];

        // Easing
        $controls['_splitting_easing'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
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
                'cubic-bezier(0.68, -0.55, 0.27, 1.55)' => 'bounce',
                'cubic-bezier(0.34, 1.56, 0.64, 1)' => 'elastic',
            ],
            'default'     => 'ease',
            'required'    => [
                ['_splitting_enable', '!=', ''],
                ['_splitting_animation', '!=', 'none']
            ],
            'inline'      => true,
        ];

        // Animation Trigger
        $controls['_splitting_trigger'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
            'label'       => esc_html__('Trigger', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'load'    => esc_html__('On Page Load', 'bricks-booster'),
                'scroll'  => esc_html__('On Scroll Into View', 'bricks-booster'),
                'hover'   => esc_html__('On Hover', 'bricks-booster'),
                'click'   => esc_html__('On Click', 'bricks-booster'),
            ],
            'default'     => 'load',
            'required'    => [
                ['_splitting_enable', '!=', ''],
                ['_splitting_animation', '!=', 'none']
            ],
            'inline'      => true,
        ];

        // Disable on Mobile
        $controls['_splitting_disable_mobile'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_splitting',
            'label'       => esc_html__('Disable on Mobile', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Disable effect on mobile devices', 'bricks-booster'),
            'required'    => ['_splitting_enable', '!=', ''],
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
        
        // Check if Splitting is enabled
        if (empty($settings['_splitting_enable'])) {
            return $html;
        }

        // Set flag to include initialization script
        $this->has_splitting = true;

        // Generate unique ID for this instance
        self::$instance_count++;
        $instance_id = 'splitting-' . self::$instance_count . '-' . uniqid();

        // Get split type
        $split_type = sanitize_text_field($settings['_splitting_type'] ?? 'chars');
        
        // Prepare config
        $config = [
            'by' => $split_type,
            'rows' => 0,
            'columns' => 0,
        ];

        // Handle grid type
        if ($split_type === 'grid') {
            $config['rows'] = intval($settings['_splitting_grid_rows'] ?? 4);
            $config['columns'] = intval($settings['_splitting_grid_columns'] ?? 4);
        }

        // Store instance config for initialization
        $this->splitting_instances[$instance_id] = $config;

        // Add data attributes for initialization
        $data_attrs = [
            'id' => esc_attr($instance_id),
            'data-splitting' => $split_type,
        ];

        // Add animation classes if animation is enabled
        $animation = sanitize_text_field($settings['_splitting_animation'] ?? 'none');
        if ($animation !== 'none') {
            $data_attrs['class'] = 'splitting-animation';
            $data_attrs['data-splitting-animation'] = $animation;
            $data_attrs['data-splitting-duration'] = intval($settings['_splitting_duration'] ?? 1000);
            $data_attrs['data-splitting-delay'] = intval($settings['_splitting_delay'] ?? 50);
            $data_attrs['data-splitting-stagger'] = sanitize_text_field($settings['_splitting_stagger_direction'] ?? 'forward');
            $data_attrs['data-splitting-easing'] = sanitize_text_field($settings['_splitting_easing'] ?? 'ease');
            $data_attrs['data-splitting-trigger'] = sanitize_text_field($settings['_splitting_trigger'] ?? 'load');
            
            if (sanitize_text_field($settings['_splitting_disable_mobile'] ?? 'false') === 'true') {
                $data_attrs['data-splitting-mobile'] = 'false';
            }
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
