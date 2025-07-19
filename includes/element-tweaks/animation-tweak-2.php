<?php
class BricksBooster_Element_Tweaks_2 {
    public function __construct() {
        // add_action('bricks:builder:ready', [$this, 'init']);
        // Only initialize if enabled in settings
        if (get_option('bricksbooster_element_tweaker_animation_2_enabled', true)) {
            add_action('init', [$this, 'add_custom_controls']);
            add_filter('bricks/frontend/render_element', [$this, 'render_custom_animation'], 10, 2);
        }
    }

    public function init() {
        // Initialization code if needed
    }

    /**
     * Add custom text control to Bricks elements
     */
    public function add_custom_controls() {
        $targets = ['section', 'container', 'block', 'div'];

        foreach ($targets as $name) {
            add_filter("bricks/elements/{$name}/controls", function ($controls) {
                $controls['custom_animation'] = [
                    'tab'     => 'content',
                    'label'   => esc_html__('Custom Animation', 'bricks-booster'),
                    'type'    => 'text',
                    'default' => '',
                    'inline'  => true,
                    'description' => esc_html__('Add custom animation that will be displayed with this element', 'bricks-booster'),
                ];
                return $controls;
            });
        }
    }

    /**
     * Render the custom text in the frontend
     */
    public function render_custom_animation($html, $element) {
        $targets = ['section', 'container', 'block', 'div'];
        $custom = $element->settings['custom_animation'] ?? '';

        if ($custom !== '' && in_array($element->name, $targets, true)) {
            $html .= '<div class="brx-custom-animation">' . esc_html($custom) . '</div>';
        }

        return $html;
    }
}