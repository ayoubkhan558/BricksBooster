<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BricksBooster_Elements_NestableList extends \Bricks\Element {
    public $category = 'bricksbooster';
    public $name = 'bb-nestable-list';
    public $icon = 'fa-list';
    public $css_selector = '.bb-nestable-list';
    public $scripts = [];
    public $nestable = true;

    public function get_label() {
        return esc_html__('Nestable List', 'bricksbooster');
    }

    public function set_control_groups() {
        $this->control_groups['list'] = [
            'title' => esc_html__('List', 'bricksbooster'),
        ];

        $this->control_groups['item'] = [
            'title' => esc_html__('List Item', 'bricksbooster'),
        ];
    }

    public function set_controls() {
        // Array of nestable element children
        $this->controls['_children'] = [
            'type' => 'repeater',
            'titleProperty' => 'label',
            'items' => 'children',
        ];

        // LIST CONTROLS
        $this->controls['listTag'] = [
            'group' => 'list',
            'label' => esc_html__('List type', 'bricksbooster'),
            'type' => 'select',
            'options' => [
                'ul' => esc_html__('Unordered list (ul)', 'bricksbooster'),
                'ol' => esc_html__('Ordered list (ol)', 'bricksbooster'),
            ],
            'default' => 'ul',
        ];

        $this->controls['listMargin'] = [
            'group' => 'list',
            'label' => esc_html__('Margin', 'bricksbooster'),
            'type' => 'spacing',
            'css' => [
                [
                    'property' => 'margin',
                    'selector' => '',
                ],
            ],
        ];

        $this->controls['listPadding'] = [
            'group' => 'list',
            'label' => esc_html__('Padding', 'bricksbooster'),
            'type' => 'spacing',
            'css' => [
                [
                    'property' => 'padding',
                    'selector' => '',
                ],
            ],
        ];

        $this->controls['listBackgroundColor'] = [
            'group' => 'list',
            'label' => esc_html__('Background color', 'bricksbooster'),
            'type' => 'color',
            'css' => [
                [
                    'property' => 'background-color',
                    'selector' => '',
                ],
            ],
        ];

        $this->controls['listBorder'] = [
            'group' => 'list',
            'label' => esc_html__('Border', 'bricksbooster'),
            'type' => 'border',
            'css' => [
                [
                    'property' => 'border',
                    'selector' => '',
                ],
            ],
        ];

        $this->controls['listTypography'] = [
            'group' => 'list',
            'label' => esc_html__('Typography', 'bricksbooster'),
            'type' => 'typography',
            'css' => [
                [
                    'property' => 'font',
                    'selector' => '',
                ],
            ],
        ];

        // LIST ITEM CONTROLS
        $this->controls['itemMargin'] = [
            'group' => 'item',
            'label' => esc_html__('Margin', 'bricksbooster'),
            'type' => 'spacing',
            'css' => [
                [
                    'property' => 'margin',
                    'selector' => 'li',
                ],
            ],
        ];

        $this->controls['itemPadding'] = [
            'group' => 'item',
            'label' => esc_html__('Padding', 'bricksbooster'),
            'type' => 'spacing',
            'css' => [
                [
                    'property' => 'padding',
                    'selector' => 'li',
                ],
            ],
            'default' => [
                'top' => 5,
                'right' => 0,
                'bottom' => 5,
                'left' => 0,
            ],
        ];

        $this->controls['itemBackgroundColor'] = [
            'group' => 'item',
            'label' => esc_html__('Background color', 'bricksbooster'),
            'type' => 'color',
            'css' => [
                [
                    'property' => 'background-color',
                    'selector' => 'li',
                ],
            ],
        ];

        $this->controls['itemBorder'] = [
            'group' => 'item',
            'label' => esc_html__('Border', 'bricksbooster'),
            'type' => 'border',
            'css' => [
                [
                    'property' => 'border',
                    'selector' => 'li',
                ],
            ],
        ];

        $this->controls['itemTypography'] = [
            'group' => 'item',
            'label' => esc_html__('Typography', 'bricksbooster'),
            'type' => 'typography',
            'css' => [
                [
                    'property' => 'font',
                    'selector' => 'li',
                ],
            ],
        ];
    }

    // Override to ensure only text-basic elements are allowed
    public function get_nestable_allowed_elements() {
        return ['text-basic'];
    }

    public function get_nestable_item() {
        return [
            'name' => 'text-basic',
            'label' => esc_html__('List Item', 'bricksbooster'),
            'settings' => [
                'tag' => 'custom',
                'customTag' => 'li',
                'text' => esc_html__('List item', 'bricksbooster') . ' {item_index}',
            ],
        ];
    }

    public function get_nestable_children() {
        $children = [];

        for ($i = 0; $i < 3; $i++) {
            $item = $this->get_nestable_item();

            // Replace {item_index} with actual index
            $item = wp_json_encode($item);
            $item = str_replace('{item_index}', $i + 1, $item);
            $item = json_decode($item, true);
            $children[] = $item;
        }

        return $children;
    }

    public function render() {
        $settings = $this->settings;
        $list_tag = isset($settings['listTag']) ? $settings['listTag'] : 'ul';

        $this->set_attribute('_root', 'class', 'bb-nestable-list');

        $output = "<{$list_tag} {$this->render_attributes('_root')}>";

        // Render children elements using Frontend::render_children with 'li' wrapper
        $list_content = \Bricks\Frontend::render_children($this, 'li');
        $output .= $list_content;

        $output .= "</{$list_tag}>";

        echo $output;
    }
}