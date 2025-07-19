<?php
class BricksBooster_Element_Tweaks_1 {
    public function __construct() {
        // add_action('bricks:builder:ready', [$this, 'init']);
        // Only initialize if enabled in settings
        if (get_option('bbooster_animation_tweak_enabled', 1)) {
            add_action('init', [$this, 'add_custom_controls']);
            add_filter('bricks/frontend/render_element', [$this, 'render_custom_text'], 10, 2);
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
                $controls['custom_text'] = [
                    'tab'     => 'content',
                    'label'   => esc_html__('Custom Text', 'bricks-booster'),
                    'type'    => 'text',
                    'default' => '',
                    'inline'  => true,
                    'description' => esc_html__('Add custom text that will be displayed with this element', 'bricks-booster'),
                ];
                return $controls;
            });
        }
    }

    /**
     * Render the custom text in the frontend
     */
    public function render_custom_text($html, $element) {
        $targets = ['section', 'container', 'block', 'div'];
        $custom = $element->settings['custom_text'] ?? '';

        if ($custom !== '' && in_array($element->name, $targets, true)) {
            $html .= '<div class="brx-custom-text">' . esc_html($custom) . '</div>';
        }

        return $html;
    }
}