<?php
class BricksBooster_Element_Tweaks_14 {
    private static $instance_count = 0;
    private $baffle_instances = [];
    private $has_baffle = false;

    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bbooster_baffle_enabled', true)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_baffle_assets']);
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
            add_action('wp_footer', [$this, 'add_baffle_initialization'], 999);
            add_action('wp_footer', [$this, 'add_baffle_refresh_script'], 1000);
        }
    }

    /**
     * Enqueue Baffle.js assets
     */
    public function enqueue_baffle_assets() {
        // Check if WordPress functions are available
        if (!function_exists('wp_enqueue_script')) {
            return;
        }
        
        // Track enqueued assets to prevent duplicates
        static $enqueued = false;
        
        if (!$enqueued) {
            // Enqueue Baffle JS
            wp_enqueue_script(
                'baffle-js',
                'https://unpkg.com/baffle@0.3.6/dist/baffle.min.js',
                [],
                '0.3.6',
                true
            );
            
            $enqueued = true;
        }
    }

    /**
     * Add Baffle initialization script
     */
    public function add_baffle_initialization() {
        if (empty($this->baffle_instances) || !$this->has_baffle) {
            return;
        }
        
        // Prepare the initialization script
        $script = "<script>";
        $script .= "document.addEventListener('DOMContentLoaded', function() {";
        $script .= "if (typeof baffle !== 'undefined') {";
        
        foreach ($this->baffle_instances as $id => $config) {
            $script .= "var element = document.getElementById('" . esc_js($id) . "');";
            $script .= "if (element && !element.hasAttribute('data-baffle-initialized')) {";
            $script .= "element.setAttribute('data-baffle-initialized', 'true');";
            $script .= "var b = baffle('#' + '" . esc_js($id) . "');";
            
            // Set options
            if (!empty($config['characters'])) {
                $script .= "b.set({";
                $script .= "characters: '" . esc_js($config['characters']) . "',";
                $script .= "speed: " . intval($config['speed']) . ",";
                $script .= "});";
            }
            
            // Set effect
            $effect = $config['effect'];
            if ($effect === 'reveal') {
                $script .= "b.reveal(" . intval($config['duration']) . ");";
            } elseif ($effect === 'once') {
                $script .= "b.once();";
            } elseif ($effect === 'start') {
                $script .= "b.start();";
                if (!empty($config['interval'])) {
                    $script .= "setInterval(function() { b.start(); }, " . intval($config['interval']) . ");";
                }
            } elseif ($effect === 'revealOnce') {
                $script .= "b.reveal(" . intval($config['duration']) . ", { once: true });";
            } elseif ($effect === 'custom') {
                $script .= $config['custom_effect'] . "";
            }
            
            $script .= "}"; // End element check
        }
        
        $script .= "}"; // End Baffle check
        $script .= "});"; // End DOMContentLoaded
        $script .= "</script>";
        
        echo $script;
    }

    /**
     * Add script to refresh Baffle after AJAX loads
     */
    public function add_baffle_refresh_script() {
        if (!$this->has_baffle) {
            return;
        }
        ?>
        <script>
        (function($) {
            $(document).on('bricks/ajax/after_load', function() {
                if (typeof baffle !== 'undefined') {
                    $('[data-baffle]:not([data-baffle-initialized])').each(function() {
                        var $this = $(this);
                        var id = $this.attr('id');
                        var config = $this.data('baffle-config');
                        
                        if (config && id) {
                            $this.attr('data-baffle-initialized', 'true');
                            var b = baffle('#' + id);
                            
                            // Re-apply the same animation logic as in initialization
                            if (config.characters) {
                                b.set({
                                    characters: config.characters,
                                    speed: config.speed
                                });
                            }
                            
                            // Re-apply effect
                            if (config.effect === 'reveal') {
                                b.reveal(config.duration);
                            } else if (config.effect === 'once') {
                                b.once();
                            } else if (config.effect === 'start') {
                                b.start();
                                if (config.interval) {
                                    setInterval(function() { b.start(); }, config.interval);
                                }
                            } else if (config.effect === 'revealOnce') {
                                b.reveal(config.duration, { once: true });
                            } else if (config.effect === 'custom' && config.custom_effect) {
                                try {
                                    // Safely evaluate the custom effect
                                    (new Function('b', config.custom_effect))(b);
                                } catch (e) {
                                    console.error('Baffle custom effect error:', e);
                                }
                            }
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
     * Add Baffle control group
     */
    public function add_control_group($control_groups) {
        if (!is_array($control_groups)) {
            $control_groups = [];
        }

        $control_groups['bricksbooster_baffle'] = [
            'tab'   => 'style',
            'title' => esc_html__('Baffle.js', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add Baffle controls
     */
    public function add_controls($controls) {
        if (!is_array($controls)) {
            $controls = [];
        }

        // Enable Baffle
        $controls['_baffle_enable'] = [
            'tab'   => 'style',
            'group' => 'bricksbooster_baffle',
            'label' => esc_html__('Enable Baffle', 'bricks-booster'),
            'type'  => 'checkbox',
            'icon'  => 'ti-layout-media-overlay-alt-2'
        ];

        // Effect Type
        $controls['_baffle_effect'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_baffle',
            'label'       => esc_html__('Effect Type', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'reveal'    => esc_html__('Reveal', 'bricks-booster'),
                'revealOnce' => esc_html__('Reveal Once', 'bricks-booster'),
                'once'      => esc_html__('Animate Once', 'bricks-booster'),
                'start'     => esc_html__('Continuous Animation', 'bricks-booster'),
                'custom'    => esc_html__('Custom Effect', 'bricks-booster'),
            ],
            'default'     => 'reveal',
            'required'    => ['_baffle_enable', '!=', ''],
            'inline'      => true,
        ];

        // Characters
        $controls['_baffle_characters'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_baffle',
            'label'       => esc_html__('Characters', 'bricks-booster'),
            'type'        => 'text',
            'default'     => '!@#$%^&*()_+-=[]{}|;:,./<>?',
            'description' => esc_html__('Characters to use for the obfuscation effect', 'bricks-booster'),
            'required'    => ['_baffle_enable', '!=', ''],
            'inline'      => true,
        ];

        // Speed
        $controls['_baffle_speed'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_baffle',
            'label'       => esc_html__('Animation Speed', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 1,
            'max'         => 1000,
            'step'        => 10,
            'default'     => 50,
            'description' => esc_html__('Lower is faster', 'bricks-booster'),
            'required'    => ['_baffle_enable', '!=', ''],
            'inline'      => true,
        ];

        // Duration
        $controls['_baffle_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_baffle',
            'label'       => esc_html__('Reveal Duration (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 10000,
            'step'        => 100,
            'default'     => 1500,
            'required'    => [
                ['_baffle_enable', '!=', ''],
                ['_baffle_effect', 'in', ['reveal', 'revealOnce']]
            ],
            'inline'      => true,
        ];

        // Interval (for continuous animation)
        $controls['_baffle_interval'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_baffle',
            'label'       => esc_html__('Animation Interval (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 10000,
            'step'        => 100,
            'default'     => 2000,
            'description' => esc_html__('How often to re-animate', 'bricks-booster'),
            'required'    => [
                ['_baffle_enable', '!=', ''],
                ['_baffle_effect', '=', 'start']
            ],
            'inline'      => true,
        ];

        // Custom Effect
        $controls['_baffle_custom_effect'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_baffle',
            'label'       => esc_html__('Custom Effect', 'bricks-booster'),
            'type'        => 'code',
            'mode'        => 'javascript',
            'description' => esc_html__('Write custom Baffle.js effect. Use "b" as the Baffle instance.', 'bricks-booster') . 
                            '<br>Example: <code>b.start().reveal(1000);</code>',
            'required'    => [
                ['_baffle_enable', '!=', ''],
                ['_baffle_effect', '=', 'custom']
            ],
            'inline'      => false,
        ];

        // Trigger
        $controls['_baffle_trigger'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_baffle',
            'label'       => esc_html__('Trigger', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'auto'    => esc_html__('On Page Load', 'bricks-booster'),
                'hover'   => esc_html__('On Hover', 'bricks-booster'),
                'click'   => esc_html__('On Click', 'bricks-booster'),
                'scroll'  => esc_html__('On Scroll Into View', 'bricks-booster'),
            ],
            'default'     => 'auto',
            'required'    => ['_baffle_enable', '!=', ''],
            'inline'      => true,
        ];

        // Disable on Mobile
        $controls['_baffle_disable_mobile'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_baffle',
            'label'       => esc_html__('Disable on Mobile', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                'true'  => esc_html__('Yes', 'bricks-booster'),
                'false' => esc_html__('No', 'bricks-booster'),
            ],
            'default'     => 'false',
            'description' => esc_html__('Disable effect on mobile devices', 'bricks-booster'),
            'required'    => ['_baffle_enable', '!=', ''],
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
        
        // Check if Baffle is enabled
        if (empty($settings['_baffle_enable'])) {
            return $html;
        }

        // Set flag to include initialization script
        $this->has_baffle = true;

        // Generate unique ID for this instance
        self::$instance_count++;
        $instance_id = 'baffle-' . self::$instance_count . '-' . uniqid();

        // Get animation settings with proper sanitization
        $effect = sanitize_text_field($settings['_baffle_effect'] ?? 'reveal');
        $characters = sanitize_text_field($settings['_baffle_characters'] ?? '!@#$%^&*()_+-=[]{}|;:,./<>?');
        $speed = intval($settings['_baffle_speed'] ?? 50);
        $duration = intval($settings['_baffle_duration'] ?? 1500);
        $interval = intval($settings['_baffle_interval'] ?? 2000);
        $custom_effect = isset($settings['_baffle_custom_effect']) ? wp_kses_post($settings['_baffle_custom_effect']) : '';
        $trigger = sanitize_text_field($settings['_baffle_trigger'] ?? 'auto');
        $disable_mobile = sanitize_text_field($settings['_baffle_disable_mobile'] ?? 'false') === 'true';

        // Prepare config
        $config = [
            'effect' => $effect,
            'characters' => $characters,
            'speed' => $speed,
            'duration' => $duration,
            'interval' => $interval,
            'custom_effect' => $custom_effect,
            'trigger' => $trigger,
            'disable_mobile' => $disable_mobile,
        ];

        // Store instance config for initialization
        $this->baffle_instances[$instance_id] = $config;

        // Add data attributes for initialization
        $data_attrs = [
            'id' => esc_attr($instance_id),
            'class' => 'baffle-target',
            'data-baffle' => '',
            'data-baffle-config' => wp_json_encode($config),
        ];

        // Add mobile class if needed
        if ($disable_mobile) {
            $data_attrs['class'] .= ' baffle-disable-mobile';
        }

        // Add trigger class
        if ($trigger !== 'auto') {
            $data_attrs['data-baffle-trigger'] = $trigger;
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
