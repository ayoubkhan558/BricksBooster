<?php
class BricksBooster_Element_Tweaks_7 {
    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_rellax_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_rellax_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_rellax_initialization']);
        }
    }

    /**
     * Enqueue Rellax.js assets
     */
    public function enqueue_rellax_assets() {
        // Rellax from CDN
        wp_enqueue_script(
            'rellax-js',
            'https://cdn.jsdelivr.net/npm/rellax@1.12.1/rellax.min.js',
            [],
            '1.12.1',
            true
        );
    }

    /**
     * Add Rellax initialization script
     */
    public function add_rellax_initialization() {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Rellax !== 'undefined') {
                // Initialize Rellax with default settings
                window.rellax = new Rellax('.rellax', {
                    // Whether to use horizontal parallax (true) or vertical (false)
                    horizontal: false,
                    // Round values to whole numbers for better performance
                    round: true,
                    // Enable vertical scrolling (true) or disable (false)
                    vertical: true,
                    // Enable horizontal scrolling (true) or disable (false)
                    horizontal: false,
                    // Will run on every animation frame
                    callback: function(positions) {
                        // Callback after position is calculated
                    }
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
        if (!class_exists('Bricks\\Elements')) {
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
     * Add Rellax control group
     */
    public function add_control_group($control_groups) {
        $control_groups['bricksbooster_rellax'] = [
            'tab'   => 'style',
            'title' => esc_html__('Rellax.js', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add Rellax controls
     */
    public function add_controls($controls) {
        // Enable Rellax
        $controls['_rellax_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_rellax',
            'label' => esc_html__('Enable Rellax', 'bricks-booster'),
            'type'  => 'checkbox',
        ];

        // Speed
        $controls['_rellax_speed'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_rellax',
            'label'       => esc_html__('Speed', 'bricks-booster'),
            'type'        => 'number',
            'min'         => -10,
            'max'         => 10,
            'step'        => 0.5,
            'default'     => 2,
            'description' => esc_html__('Speed of the parallax effect (negative for opposite direction)', 'bricks-booster'),
            'required'    => ['_rellax_enable', '!=', ''],
            'inline'      => true,
        ];

        // Percentage
        $controls['_rellax_percentage'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_rellax',
            'label'       => esc_html__('Percentage', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1,
            'step'        => 0.1,
            'default'     => 0.5,
            'description' => esc_html__('How much of the element should be visible before triggering', 'bricks-booster'),
            'required'    => ['_rellax_enable', '!=', ''],
            'inline'      => true,
        ];

        // Z-Index
        $controls['_rellax_zindex'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_rellax',
            'label'       => esc_html__('Z-Index', 'bricks-booster'),
            'type'        => 'number',
            'min'         => -100,
            'max'         => 100,
            'default'     => 0,
            'description' => esc_html__('Z-index of the parallax elements', 'bricks-booster'),
            'required'    => ['_rellax_enable', '!=', ''],
            'inline'      => true,
        ];

        // Center
        $controls['_rellax_center'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_rellax',
            'label'       => esc_html__('Center', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Center the parallax element', 'bricks-booster'),
            'required'    => ['_rellax_enable', '!=', ''],
            'inline'      => true,
        ];

        // Round
        $controls['_rellax_round'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_rellax',
            'label'       => esc_html__('Round Values', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'description' => esc_html__('Round values for better performance', 'bricks-booster'),
            'required'    => ['_rellax_enable', '!=', ''],
            'inline'      => true,
        ];

        // Vertical
        $controls['_rellax_vertical'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_rellax',
            'label'       => esc_html__('Vertical Parallax', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Enable', 'bricks-booster'),
                'false' => esc_html__('Disable', 'bricks-booster'),
            ],
            'default'     => 'true',
            'required'    => ['_rellax_enable', '!=', ''],
            'inline'      => true,
        ];

        // Horizontal
        $controls['_rellax_horizontal'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_rellax',
            'label'       => esc_html__('Horizontal Parallax', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Enable', 'bricks-booster'),
                'false' => esc_html__('Disable', 'bricks-booster'),
            ],
            'default'     => 'false',
            'required'    => ['_rellax_enable', '!=', ''],
            'inline'      => true,
        ];

        // Wrapper
        $controls['_rellax_wrapper'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_rellax',
            'label'       => esc_html__('Wrapper Class', 'bricks-booster'),
            'type'        => 'text',
            'default'     => '',
            'description' => esc_html__('Custom wrapper class (optional)', 'bricks-booster'),
            'required'    => ['_rellax_enable', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        $settings = $element->settings;
        
        // Check if Rellax is enabled
        if (empty($settings['_rellax_enable'])) {
            return $html;
        }

        // Get animation settings
        $speed = $settings['_rellax_speed'] ?? 2;
        $percentage = $settings['_rellax_percentage'] ?? 0.5;
        $zindex = $settings['_rellax_zindex'] ?? 0;
        $center = $settings['_rellax_center'] ?? 'false';
        $round = $settings['_rellax_round'] ?? 'true';
        $vertical = $settings['_rellax_vertical'] ?? 'true';
        $horizontal = $settings['_rellax_horizontal'] ?? 'false';
        $wrapper = $settings['_rellax_wrapper'] ?? '';

        // Prepare data attributes
        $data_attrs = [
            'class' => 'rellax',
            'data-rellax-speed' => $speed,
            'data-rellax-percentage' => $percentage,
            'data-rellax-zindex' => $zindex,
            'data-rellax-center' => $center,
            'data-rellax-round' => $round,
            'data-rellax-vertical' => $vertical,
            'data-rellax-horizontal' => $horizontal,
        ];

        // Add wrapper class if specified
        if (!empty($wrapper)) {
            $data_attrs['data-rellax-wrapper'] = $wrapper;
        }

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
