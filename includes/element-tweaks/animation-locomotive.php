<?php
class BricksBooster_Element_Tweaks_8 {
    private $scroll;

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_locomotive_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_locomotive_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_locomotive_initialization'], 999);
            add_action('wp_footer', [$this, 'add_locomotive_refresh_script'], 1000);
        }
    }

    /**
     * Enqueue Locomotive Scroll assets
     */
    public function enqueue_locomotive_assets() {
        // Locomotive Scroll from CDN
        wp_enqueue_style(
            'locomotive-scroll-css',
            'https://cdn.jsdelivr.net/npm/locomotive-scroll@4.1.4/dist/locomotive-scroll.min.css',
            [],
            '4.1.4'
        );
        
        wp_enqueue_script(
            'locomotive-scroll-js',
            'https://cdn.jsdelivr.net/npm/locomotive-scroll@4.1.4/dist/locomotive-scroll.min.js',
            [],
            '4.1.4',
            true
        );
    }

    /**
     * Add Locomotive Scroll initialization script
     */
    public function add_locomotive_initialization() {
        if (did_action('bricks_after_site_wrapper')) {
            return;
        }
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof LocomotiveScroll !== 'undefined') {
                // Initialize Locomotive Scroll
                window.locomotiveScroll = new LocomotiveScroll({
                    el: document.querySelector('[data-scroll-container]'),
                    smooth: true,
                    lerp: 0.1,
                    smartphone: {
                        smooth: true
                    },
                    tablet: {
                        smooth: true,
                        breakpoint: 1024
                    }
                });

                // Update Locomotive Scroll on page load
                window.addEventListener('load', function() {
                    if (window.locomotiveScroll) {
                        window.locomotiveScroll.update();
                    }
                });

                // Update on Bricks Builder refresh
                if (typeof bricksData !== 'undefined' && bricksData.isBuilder) {
                    window.addEventListener('bricks.builder.refresh', function() {
                        if (window.locomotiveScroll) {
                            window.locomotiveScroll.update();
                        }
                    });
                }
            }
        });
        </script>
        <?php
    }

    /**
     * Add script to refresh Locomotive Scroll after AJAX loads
     */
    public function add_locomotive_refresh_script() {
        ?>
        <script>
        (function($) {
            $(document).on('bricks/ajax/after_load', function() {
                if (typeof window.locomotiveScroll !== 'undefined') {
                    window.locomotiveScroll.update();
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
     * Add Locomotive control group
     */
    public function add_control_group($control_groups) {
        $control_groups['bricksbooster_locomotive'] = [
            'tab'   => 'style',
            'title' => esc_html__('Locomotive Scroll', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add Locomotive controls
     */
    public function add_controls($controls) {
        // Enable Locomotive
        $controls['_locomotive_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_locomotive',
            'label' => esc_html__('Enable Locomotive', 'bricks-booster'),
            'type'  => 'checkbox',
        ];

        // Animation type
        $controls['_locomotive_animation'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_locomotive',
            'label'       => esc_html__('Animation', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'fade' => 'Fade',
                'fade-up' => 'Fade Up',
                'fade-down' => 'Fade Down',
                'fade-left' => 'Fade Left',
                'fade-right' => 'Fade Right',
                'fade-up-right' => 'Fade Up Right',
                'fade-up-left' => 'Fade Up Left',
                'fade-down-right' => 'Fade Down Right',
                'fade-down-left' => 'Fade Down Left',
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
            'required'    => ['_locomotive_enable', '!=', ''],
            'inline'      => true,
        ];

        // Duration
        $controls['_locomotive_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_locomotive',
            'label'       => esc_html__('Duration', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0.1,
            'max'         => 5,
            'step'        => 0.1,
            'default'     => 0.8,
            'description' => esc_html__('Animation duration in seconds', 'bricks-booster'),
            'required'    => ['_locomotive_enable', '!=', ''],
            'inline'      => true,
        ];

        // Delay
        $controls['_locomotive_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_locomotive',
            'label'       => esc_html__('Delay', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 5,
            'step'        => 0.1,
            'default'     => 0,
            'description' => esc_html__('Animation delay in seconds', 'bricks-booster'),
            'required'    => ['_locomotive_enable', '!=', ''],
            'inline'      => true,
        ];

        // Easing
        $controls['_locomotive_easing'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_locomotive',
            'label'       => esc_html__('Easing', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'ease' => 'ease',
                'ease-in' => 'ease-in',
                'ease-out' => 'ease-out',
                'ease-in-out' => 'ease-in-out',
                'linear' => 'linear',
                'cubic-bezier(0.4, 0, 0.2, 1)' => 'easeInOutCubic',
                'cubic-bezier(0.4, 0, 0.6, 1)' => 'easeInOutQuad',
                'cubic-bezier(0.4, 0, 0.2, 1)' => 'easeInOutQuart',
                'cubic-bezier(0.4, 0, 0.2, 1)' => 'easeInOutQuint',
                'cubic-bezier(0.4, 0, 1, 1)' => 'easeInOutSine',
            ],
            'default'     => 'ease',
            'required'    => ['_locomotive_enable', '!=', ''],
            'inline'      => true,
        ];

        // Start
        $controls['_locomotive_start'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_locomotive',
            'label'       => esc_html__('Start', 'bricks-booster'),
            'type'        => 'text',
            'default'     => 'top bottom',
            'description' => esc_html__('Animation start position (e.g., "top bottom", "top center")', 'bricks-booster'),
            'required'    => ['_locomotive_enable', '!=', ''],
            'inline'      => true,
        ];

        // End
        $controls['_locomotive_end'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_locomotive',
            'label'       => esc_html__('End', 'bricks-booster'),
            'type'        => 'text',
            'default'     => 'bottom top',
            'description' => esc_html__('Animation end position (e.g., "bottom top", "center center")', 'bricks-booster'),
            'required'    => ['_locomotive_enable', '!=', ''],
            'inline'      => true,
        ];

        // Stagger
        $controls['_locomotive_stagger'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_locomotive',
            'label'       => esc_html__('Stagger', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 2,
            'step'        => 0.1,
            'default'     => 0,
            'description' => esc_html__('Stagger delay between elements', 'bricks-booster'),
            'required'    => ['_locomotive_enable', '!=', ''],
            'inline'      => true,
        ];

        // Disable on mobile
        $controls['_locomotive_disable_mobile'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_locomotive',
            'label'       => esc_html__('Disable on Mobile', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'required'    => ['_locomotive_enable', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        $settings = $element->settings;
        
        // Check if Locomotive is enabled
        if (empty($settings['_locomotive_enable'])) {
            return $html;
        }

        // Get animation settings
        $animation = $settings['_locomotive_animation'] ?? 'fade-up';
        $duration = $settings['_locomotive_duration'] ?? 0.8;
        $delay = $settings['_locomotive_delay'] ?? 0;
        $easing = $settings['_locomotive_easing'] ?? 'ease';
        $start = $settings['_locomotive_start'] ?? 'top bottom';
        $end = $settings['_locomotive_end'] ?? 'bottom top';
        $stagger = $settings['_locomotive_stagger'] ?? 0;
        $disableMobile = $settings['_locomotive_disable_mobile'] ?? 'false';

        // Prepare data attributes
        $data_attrs = [
            'data-scroll' => '',
            'data-scroll-class' => 'is-inview',
            'data-scroll-position' => 'top',
            'data-scroll-speed' => '1',
            'data-scroll-direction' => 'vertical',
            'data-scroll-animate' => $animation,
            'data-scroll-duration' => $duration,
            'data-scroll-delay' => $delay,
            'data-scroll-easing' => $easing,
            'data-scroll-offset' => '0',
            'data-scroll-repeat' => 'true',
            'data-scroll-call' => '',
            'data-scroll-sticky' => '',
            'data-scroll-target' => '',
            'data-scroll-start' => $start,
            'data-scroll-end' => $end,
            'data-scroll-stagger' => $stagger,
            'data-scroll-mobile' => $disableMobile === 'true' ? 'false' : 'true',
        ];

        // Convert data attributes to string
        $attrs_string = '';
        foreach ($data_attrs as $key => $value) {
            if ($value !== '') {
                $attrs_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }

        // Add the attributes to the element
        $html = preg_replace('/^(<[^>]+)/', '$1' . $attrs_string, $html);

        return $html;
    }
}
