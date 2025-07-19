<?php
class BricksBooster_Element_Tweaks_2 {
    public function __construct() {
        // Only initialize if enabled in settings
        if (get_option('bricksbooster_element_tweaker_animation_2_enabled', true)) {
            add_action('init', [$this, 'init_animation_controls'], 30);
            add_filter('bricks/frontend/render_element', [$this, 'render_animation'], 10, 2);
        }
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
     * Add Animation control group
     */
    public function add_control_group($control_groups) {
        $control_groups['bricksbooster_animation'] = [
            'tab'   => 'style',
            'title' => esc_html__('Animation', 'bricks-booster'),
        ];

        return $control_groups;
    }

    /**
     * Add animation controls
     */
    public function add_controls($controls) {
        // Animation type control
        $controls['_animation_type'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Animation', 'bricks-booster'),
            'type'        => 'select',
            'searchable'  => true,
            'options'     => [
                '' => esc_html__('None', 'bricks-booster'),
                'fadeIn' => 'Fade In',
                'fadeInDown' => 'Fade In Down',
                'fadeInUp' => 'Fade In Up',
                'fadeInLeft' => 'Fade In Left',
                'fadeInRight' => 'Fade In Right',
                'zoomIn' => 'Zoom In',
                'bounceIn' => 'Bounce In',
                'slideInUp' => 'Slide In Up',
                'slideInDown' => 'Slide In Down',
                'slideInLeft' => 'Slide In Left',
                'slideInRight' => 'Slide In Right',
            ],
            'inline'      => true,
            'placeholder' => esc_html__('Select animation', 'bricks-booster'),
        ];

        // Animation duration control
        $controls['_animation_duration'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Duration (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 100,
            'max'         => 5000,
            'step'        => 100,
            'default'     => 1000,
            'required'    => ['_animation_type', '!=', ''],
            'inline'      => true,
        ];

        // Animation delay control
        $controls['_animation_delay'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Delay (ms)', 'bricks-booster'),
            'type'        => 'number',
            'min'         => 0,
            'max'         => 5000,
            'step'        => 100,
            'default'     => 0,
            'required'    => ['_animation_type', '!=', ''],
            'inline'      => true,
        ];

        // Animation repeat control
        $controls['_animation_repeat'] = [
            'tab'         => 'style',
            'group'       => 'bricksbooster_animation',
            'label'       => esc_html__('Repeat', 'bricks-booster'),
            'type'        => 'select',
            'options'     => [
                '' => esc_html__('Once', 'bricks-booster'),
                'infinite' => esc_html__('Infinite', 'bricks-booster'),
            ],
            'required'    => ['_animation_type', '!=', ''],
            'inline'      => true,
        ];

        return $controls;
    }

    /**
     * Render the animation in the frontend
     */
    public function render_animation($html, $element) {
        $animation_type = $element->settings['_animation_type'] ?? '';
        
        if (empty($animation_type)) {
            return $html;
        }

        // Get animation settings
        $duration = !empty($element->settings['_animation_duration']) ? intval($element->settings['_animation_duration']) : 1000;
        $delay = !empty($element->settings['_animation_delay']) ? intval($element->settings['_animation_delay']) : 0;
        $repeat = !empty($element->settings['_animation_repeat']) && $element->settings['_animation_repeat'] === 'infinite' ? 'infinite' : '';

        // Add animation classes
        $classes = ['brx-animation', 'animate__animated', 'animate__' . $animation_type];
        if ($repeat === 'infinite') {
            $classes[] = 'animate__infinite';
        }

        // Add animation styles
        $styles = [
            '--animate-duration' => ($duration / 1000) . 's',
        ];
        
        if ($delay > 0) {
            $styles['--animate-delay'] = ($delay / 1000) . 's';
        }

        // Convert styles array to string
        $style_attr = '';
        foreach ($styles as $prop => $value) {
            $style_attr .= "{$prop}: {$value}; ";
        }

        // Wrap the element with animation wrapper
        $html = sprintf(
            '<div class="%s" style="%s">%s</div>',
            esc_attr(implode(' ', $classes)),
            esc_attr(trim($style_attr)),
            $html
        );

        return $html;
    }
}