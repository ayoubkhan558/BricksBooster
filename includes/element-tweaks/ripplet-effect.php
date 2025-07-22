<?php
class BricksBooster_Element_Tweaks_17 {
    private $has_ripplet = false;

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_ripplet_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_ripplet_assets']);
            add_action('init', [$this, 'init_ripplet_controls'], 30);
            add_filter('bricks/element/after_render', [$this, 'add_ripplet_attributes'], 10, 2);
            add_action('wp_footer', [$this, 'add_ripplet_initialization'], 999);
        }
    }

    /**
     * Enqueue Ripplet.js assets
     */
    public function enqueue_ripplet_assets() {
        // Check if WordPress functions are available
        if (!function_exists('wp_enqueue_script')) {
            return;
        }
        
        // Track enqueued assets to prevent duplicates
        static $enqueued = false;
        
        if (!$enqueued) {
            // Enqueue Ripplet.js
            wp_enqueue_script(
                'ripplet-js',
                'https://cdn.jsdelivr.net/npm/ripplet.js@0.2.0/umd/ripplet.iife.min.js',
                [],
                '0.2.0',
                true
            );
            
            // Enqueue custom script
            wp_enqueue_script(
                'bricks-booster-ripplet',
                BRICKSBOOSTER_PLUGIN_URL . 'assets/js/ripplet.js',
                ['ripplet-js'],
                BRICKSBOOSTER_VERSION,
                true
            );
            
            // Enqueue custom CSS
            wp_enqueue_style(
                'bricks-booster-ripplet',
                BRICKSBOOSTER_PLUGIN_URL . 'assets/css/ripplet.css',
                [],
                BRICKSBOOSTER_VERSION
            );
            
            $enqueued = true;
        }
    }

    /**
     * Initialize Ripplet controls for clickable elements
     */
    public function init_ripplet_controls() {
        if (!class_exists('Bricks\\Elements')) {
            return;
        }

        // Get all elements that can be clickable
        $clickable_elements = [
            'button', 'container', 'div', 'section', 'block', 'heading', 'text',
            'image', 'icon', 'video', 'audio', 'icon-box', 'counter', 'countdown',
            'accordion', 'tabs', 'accordion-nested', 'tabs-nested', 'alert', 'badge',
            'card', 'pricing-tables', 'team-member', 'testimonial', 'post-title',
            'post-meta', 'post-content', 'post-excerpt', 'post-comments', 'search'
        ];

        // Add control groups and controls to all clickable elements
        foreach ($clickable_elements as $element) {
            add_filter("bricks/elements/{$element}/control_groups", [$this, 'add_control_group'], 10);
            add_filter("bricks/elements/{$element}/controls", [$this, 'add_controls'], 10);
        }
    }

    /**
     * Add Ripplet control group
     */
    public function add_control_group($control_groups) {
        if (!is_array($control_groups)) {
            $control_groups = [];
        }

        $control_groups['bricksbooster_ripplet'] = [
            'tab'   => 'style',
            'title' => esc_html__('Ripple Effect', 'bricks-booster'),
            'icon'  => 'ti-layout-media-overlay-alt-2'
        ];

        return $control_groups;
    }

    /**
     * Add Ripplet controls
     */
    public function add_controls($controls) {
        if (!is_array($controls)) {
            $controls = [];
        }

        // Enable Ripple Effect
        $controls['_ripplet_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_ripplet',
            'label' => esc_html__('Enable Ripple Effect', 'bricks-booster'),
            'type'  => 'checkbox',
            'inline' => true,
            'description' => esc_html__('Adds a material design ripple effect on click', 'bricks-booster')
        ];

        // Ripple Color
        $controls['_ripplet_color'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ripplet',
            'label'       => esc_html__('Ripple Color', 'bricks-booster'),
            'type'        => 'color',
            'default'     => 'rgba(255, 255, 255, 0.5)',
            'required'    => ['_ripplet_enable', '!=', ''],
            'inline'      => true,
            'css'         => [
                [
                    'selector' => '',
                    'property' => '--ripplet-color',
                ]
            ],
        ];

        // Ripple Opacity
        $controls['_ripplet_opacity'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ripplet',
            'label'       => esc_html__('Ripple Opacity', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0.1,
            'max'         => 1,
            'step'        => 0.1,
            'default'     => 0.5,
            'required'    => ['_ripplet_enable', '!=', ''],
            'inline'      => true,
            'css'         => [
                [
                    'selector' => '',
                    'property' => '--ripplet-opacity',
                ]
            ],
        ];

        // Ripple Duration
        $controls['_ripplet_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ripplet',
            'label'       => esc_html__('Animation Duration (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 2000,
            'step'        => 50,
            'default'     => 500,
            'required'    => ['_ripplet_enable', '!=', ''],
            'inline'      => true,
        ];

        // Ripple Type
        $controls['_ripplet_type'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ripplet',
            'label'       => esc_html__('Ripple Type', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'unbounded' => esc_html__('Unbounded', 'bricks-booster'),
                'bounded'   => esc_html__('Bounded', 'bricks-booster'),
            ],
            'default'     => 'bounded',
            'required'    => ['_ripplet_enable', '!=', ''],
            'inline'      => true,
        ];

        // Ripple Center
        $controls['_ripplet_center'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ripplet',
            'label'       => esc_html__('Center Ripple', 'bricks-booster'),
            'type'        => 'checkbox',
            'description' => esc_html__('Center the ripple effect on the element', 'bricks-booster'),
            'required'    => ['_ripplet_enable', '!=', ''],
            'inline'      => true,
        ];

        // Ripple Radius
        $controls['_ripplet_radius'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ripplet',
            'label'       => esc_html__('Ripple Radius', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 1000,
            'step'        => 10,
            'default'     => 0,
            'description' => esc_html__('Set 0 for auto-calculate', 'bricks-booster'),
            'required'    => [
                ['_ripplet_enable', '!=', ''],
                ['_ripplet_type', '=', 'bounded']
            ],
            'inline'      => true,
        ];

        // Ripple on Hover
        $controls['_ripplet_on_hover'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ripplet',
            'label'       => esc_html__('Show on Hover', 'bricks-booster'),
            'type'        => 'checkbox',
            'description' => esc_html__('Show ripple effect on hover instead of click', 'bricks-booster'),
            'required'    => ['_ripplet_enable', '!=', ''],
            'inline'      => true,
        ];

        // Disable on Mobile
        $controls['_ripplet_disable_mobile'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_ripplet',
            'label'       => esc_html__('Disable on Mobile', 'bricks-booster'),
            'type'        => 'checkbox',
            'description' => esc_html__('Disable ripple effect on mobile devices', 'bricks-booster'),
            'required'    => ['_ripplet_enable', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Add Ripplet attributes to the element
     */
    public function add_ripplet_attributes($element) {
        if (!is_object($element) || !property_exists($element, 'settings')) {
            return;
        }

        $settings = $element->settings;
        
        // Check if Ripple Effect is enabled
        if (empty($settings['_ripplet_enable'])) {
            return;
        }

        // Set flag to include initialization script
        $this->has_ripplet = true;

        // Get Ripple settings
        $ripplet_settings = [
            'color' => $settings['_ripplet_color'] ?? 'rgba(255, 255, 255, 0.5)',
            'opacity' => isset($settings['_ripplet_opacity']) ? floatval($settings['_ripplet_opacity']) : 0.5,
            'duration' => isset($settings['_ripplet_duration']) ? intval($settings['_ripplet_duration']) : 500,
            'type' => $settings['_ripplet_type'] ?? 'bounded',
            'center' => !empty($settings['_ripplet_center']),
            'radius' => isset($settings['_ripplet_radius']) ? intval($settings['_ripplet_radius']) : 0,
            'onHover' => !empty($settings['_ripplet_on_hover']),
            'disableOnMobile' => !empty($settings['_ripplet_disable_mobile']),
        ];

        // Add data attributes for initialization
        $element->set_attribute('data-ripplet', '');
        $element->set_attribute('data-ripplet-settings', wp_json_encode($ripplet_settings));

        // Add CSS class for styling
        $element->set_attribute('class', 'bricks-booster-ripplet');
    }

    /**
     * Add Ripplet initialization script
     */
    public function add_ripplet_initialization() {
        if (!$this->has_ripplet) {
            return;
        }
        
        // The actual initialization is handled in the external JS file
        // This is just a fallback in case the external file fails to load
        ?>
        <script>
        (function($) {
            // Check if ripplet is already initialized by the external script
            if (typeof window.bricksBoosterRippletInitialized === 'undefined') {
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof ripplet !== 'undefined') {
                        $('[data-ripplet]').each(function() {
                            const $element = $(this);
                            const settings = $element.data('ripplet-settings') || {};
                            
                            // Skip if already initialized
                            if ($element.data('ripplet-initialized')) {
                                return;
                            }
                            
                            // Mark as initialized
                            $element.data('ripplet-initialized', true);
                            
                            // Set up ripple effect
                            const eventType = settings.onHover ? 'mouseenter' : 'mousedown';
                            
                            $element.on(eventType, function(e) {
                                // Skip if disabled on mobile
                                if (settings.disableOnMobile && window.innerWidth <= 767) {
                                    return;
                                }
                                
                                // Create ripple
                                ripplet({
                                    x: e.pageX,
                                    y: e.pageY,
                                    color: settings.color || 'rgba(255, 255, 255, 0.5)',
                                    opacity: settings.opacity || 0.5,
                                    duration: settings.duration || 500,
                                    type: settings.type || 'bounded',
                                    center: settings.center || false,
                                    radius: settings.radius || 0,
                                    element: this
                                });
                            });
                        });
                    }
                });
            }
        })(jQuery);
        </script>
        <style>
        .bricks-booster-ripplet {
            position: relative;
            overflow: hidden;
            transform: translate3d(0, 0, 0);
            --ripplet-color: rgba(255, 255, 255, 0.5);
            --ripplet-opacity: 0.5;
        }
        .bricks-booster-ripplet .ripplet {
            position: absolute;
            border-radius: 50%;
            background-color: var(--ripplet-color, rgba(255, 255, 255, 0.5));
            opacity: var(--ripplet-opacity, 0.5);
            transform: scale(0);
            pointer-events: none;
        }
        .bricks-booster-ripplet .ripplet.animate {
            animation: ripplet-animation 0.5s linear;
        }
        @keyframes ripplet-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        </style>
        <?php
    }
}
