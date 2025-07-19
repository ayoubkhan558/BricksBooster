<?php
class BricksBooster_Element_Tweaks_5 {
    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_scrollout_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scrollout_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_scrollout_initialization']);
        }
    }

    /**
     * Enqueue ScrollOut.js assets
     */
    public function enqueue_scrollout_assets() {
        // ScrollOut from CDN
        wp_enqueue_script(
            'scrollout-js',
            'https://unpkg.com/scroll-out/dist/scroll-out.min.js',
            [],
            '2.2.12',
            true
        );
    }

    /**
     * Add ScrollOut initialization script
     */
    public function add_scrollout_initialization() {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ScrollOut !== 'undefined') {
                // Initialize ScrollOut with default settings
                window.ScrollOut({
                    threshold: 0.2,
                    onShown: function(el) {
                        el.setAttribute('data-scroll', 'in');
                    },
                    onHidden: function(el) {
                        el.setAttribute('data-scroll', 'out');
                    },
                    onChange: function(el) {
                        // Update data-scroll-* attributes
                        el.setAttribute('data-scroll', el.visible ? 'in' : 'out');
                        el.setAttribute('data-scroll-visible', el.visible);
                        el.setAttribute('data-scroll-into-view', el.isVisible);
                        el.setAttribute('data-scroll-index', el.index);
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
     * Add ScrollOut control group
     */
    public function add_control_group($control_groups) {
        $control_groups['bricksbooster_scrollout'] = [
            'tab'   => 'style',
            'title' => esc_html__('ScrollOut', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add ScrollOut controls
     */
    public function add_controls($controls) {
        // Enable ScrollOut
        $controls['_so_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_scrollout',
            'label' => esc_html__('Enable ScrollOut', 'bricks-booster'),
            'type'  => 'checkbox',
        ];

        // Animation type
        $controls['_so_animation'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollout',
            'label'       => esc_html__('Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'fade' => 'Fade',
                'fade-up' => 'Fade Up',
                'fade-down' => 'Fade Down',
                'fade-left' => 'Fade Left',
                'fade-right' => 'Fade Right',
                'zoom-in' => 'Zoom In',
                'zoom-out' => 'Zoom Out',
                'flip-up' => 'Flip Up',
                'flip-down' => 'Flip Down',
                'flip-left' => 'Flip Left',
                'flip-right' => 'Flip Right',
                'slide-up' => 'Slide Up',
                'slide-down' => 'Slide Down',
                'slide-left' => 'Slide Left',
                'slide-right' => 'Slide Right',
            ],
            'default'     => 'fade-up',
            'required'    => ['_so_enable', '!=', ''],
            'inline'      => true,
        ];

        // Threshold
        $controls['_so_threshold'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollout',
            'label'       => esc_html__('Threshold', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1,
            'step'        => 0.1,
            'default'     => 0.2,
            'description' => esc_html__('Percentage of element visibility needed to trigger', 'bricks-booster'),
            'required'    => ['_so_enable', '!=', ''],
            'inline'      => true,
        ];

        // Offset
        $controls['_so_offset'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollout',
            'label'       => esc_html__('Offset', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 500,
            'step'        => 10,
            'default'     => 0,
            'description' => esc_html__('Offset in pixels from the element', 'bricks-booster'),
            'required'    => ['_so_enable', '!=', ''],
            'inline'      => true,
        ];

        // Once
        $controls['_so_once'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollout',
            'label'       => esc_html__('Animate Once', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Only animate once when scrolling down', 'bricks-booster'),
            'required'    => ['_so_enable', '!=', ''],
            'inline'      => true,
        ];

        // Mobile
        $controls['_so_mobile'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_scrollout',
            'label'       => esc_html__('Enable on Mobile', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'true',
            'required'    => ['_so_enable', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        $settings = $element->settings;
        
        // Check if ScrollOut is enabled
        if (empty($settings['_so_enable'])) {
            return $html;
        }

        // Get animation settings
        $animation = $settings['_so_animation'] ?? 'fade-up';
        $threshold = $settings['_so_threshold'] ?? 0.2;
        $offset = $settings['_so_offset'] ?? 0;
        $once = $settings['_so_once'] ?? 'false';
        $mobile = $settings['_so_mobile'] ?? 'true';

        // Prepare data attributes
        $data_attrs = [
            'data-scroll' => 'out',
            'data-scroll-animation' => $animation,
            'data-scroll-threshold' => $threshold,
            'data-scroll-offset' => $offset,
            'data-scroll-once' => $once,
            'data-scroll-mobile' => $mobile,
        ];

        // Add CSS classes for the animation
        $classes = [
            'scroll-out-element',
            'scroll-out-animation',
            'scroll-out-animation--' . $animation,
        ];

        // Convert data attributes to string
        $attrs_string = '';
        foreach ($data_attrs as $key => $value) {
            $attrs_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        // Add the attributes to the element
        $html = preg_replace('/^(<[^>]+)/', '$1' . $attrs_string . ' class="' . esc_attr(implode(' ', $classes)) . '"', $html);

        return $html;
    }
}
