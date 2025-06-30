<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BricksBooster_Elements_NestableLink extends \Bricks\Element {
    // Element properties
    public $category = 'bricksbooster';
    public $name = 'bb-nestable-link';
    public $icon = 'fa-link';
    public $css_selector = '.bb-nestable-link';
    public $scripts = [];
    public $nestable = true;

    // Get element label
    public function get_label() {
        return esc_html__('Link (Nestable)', 'bricksbooster');
    }

    // Set element controls
    public function set_controls() {
        $this->controls['link'] = [
            'tab' => 'content',
            'label' => esc_html__('Link', 'bricksbooster'),
            'type' => 'link',
        ];

        $this->controls['text'] = [
            'tab' => 'content',
            'label' => esc_html__('Text', 'bricksbooster'),
            'type' => 'text',
            'default' => esc_html__('Link Text', 'bricksbooster'),
            'description' => esc_html__('This text will only show if no child elements are added', 'bricksbooster'),
        ];
    }

    // Render element HTML
    public function render() {
        $settings = $this->settings;
        $text = isset($settings['text']) ? $settings['text'] : '';
        $link = isset($settings['link']['url']) ? $settings['link']['url'] : '';

        $this->set_attribute('_root', 'class', 'bb-nestable-link');
        
        echo "<div {$this->render_attributes('_root')}>";
        
        if ($link) {
            $link_attributes = '';
            if (!empty($settings['link']['target'])) {
                $link_attributes .= ' target="' . esc_attr($settings['link']['target']) . '"';
            }
            if (!empty($settings['link']['rel'])) {
                $link_attributes .= ' rel="' . esc_attr($settings['link']['rel']) . '"';
            }
            
            echo '<a href="' . esc_url($link) . '"' . $link_attributes . '>';
            
            // Check if there are child elements
            if (!empty($this->children)) {
                // Render child elements
                echo $this->render_children();
            } else {
                // Fallback to text if no children
                echo esc_html($text);
            }
            
            echo '</a>';
        } else {
            // No link URL - just render children or text
            if (!empty($this->children)) {
                echo $this->render_children();
            } else {
                echo esc_html($text);
            }
        }
        
        echo "</div>";
    }
}