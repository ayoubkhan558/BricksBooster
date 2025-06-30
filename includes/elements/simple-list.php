<?php
/**
 * BricksBooster Simple List Element
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BricksBooster_Elements_SimpleList extends \Bricks\Element {
    
    // Element properties
    public $category = 'bricksbooster';
    public $name = 'bb-simple-list';
    public $icon = 'fa-list-ul';
    public $css_selector = '.bb-simple-list';
    public $scripts = ['bricksboosterSimpleList'];
    public $nestable = false;

    // Get element label
    public function get_label() {
        return esc_html__('Simple List', 'bricksbooster');
    }

    public function set_control_groups() {
        $this->control_groups['list-content'] = [
            'title' => esc_html__( 'Lists Content', 'bricksbooster' ),
        ];

        $this->control_groups['list-style'] = [
            'title' => esc_html__( 'Lists Style', 'bricksbooster' ),
        ];
    }

    // Set element controls
    public function set_controls() {
        $this->controls['listType'] = [
			'group' => 'list-content',
            'tab' => 'content',
            'label' => esc_html__('List Type', 'bricksbooster'),
            'type' => 'select',
            'options' => [
                'ul' => esc_html__('Unordered List (ul)', 'bricksbooster'),
                'ol' => esc_html__('Ordered List (ol)', 'bricksbooster'),
            ],
            'default' => 'ul',
        ];

        $this->controls['items'] = [
			'group' => 'list-content',
            'tab' => 'content',
            'label' => esc_html__('List Items', 'bricksbooster'),
            'type' => 'repeater',
            'titleProperty' => 'text',
            'default' => [
                [
                    'text' => esc_html__('List Item 1', 'bricksbooster'),
                    'level' => 1,
                ],
                [
                    'text' => esc_html__('List Item 2', 'bricksbooster'),
                    'level' => 1,
                ],
                [
                    'text' => esc_html__('Nested Item 2.1', 'bricksbooster'),
                    'level' => 2,
                ],
            ],
            'fields' => [
                'text' => [
                    'label' => esc_html__('Text', 'bricksbooster'),
                    'type' => 'text',
                    'default' => esc_html__('List Item', 'bricksbooster'),
                ],
                'link' => [
                    'label' => esc_html__('Link', 'bricksbooster'),
                    'type' => 'link',
                ],
                'level' => [
                    'label' => esc_html__('Nesting Level', 'bricksbooster'),
                    'type' => 'number',
                    'min' => 1,
                    'step' => '1',
                    'inline' => true,
                    'default' => 1,
                ],
            ],
        ];

        // Style controls
        
        // Style controls
        $this->controls['listStyle'] = [
			'group' => 'list-content',
            'tab' => 'content',
            'label' => esc_html__('List Style Type', 'bricksbooster'),
            'type' => 'select',
            'css' => [
                [
                    'property' => 'list-style-type',
                    'selector' => 'ul, ol',
                ],
            ],
            'options' => [
                'disc' => esc_html__('Disc', 'bricksbooster'),
                'circle' => esc_html__('Circle', 'bricksbooster'),
                'square' => esc_html__('Square', 'bricksbooster'),
                'decimal' => esc_html__('Decimal', 'bricksbooster'),
                'decimal-leading-zero' => esc_html__('Decimal Leading Zero', 'bricksbooster'),
                'lower-roman' => esc_html__('Lower Roman', 'bricksbooster'),
                'upper-roman' => esc_html__('Upper Roman', 'bricksbooster'),
                'lower-alpha' => esc_html__('Lower Alpha', 'bricksbooster'),
                'upper-alpha' => esc_html__('Upper Alpha', 'bricksbooster'),
                'none' => esc_html__('None', 'bricksbooster'),
            ],
        ];

        $this->controls['listStylePosition'] = [
			'group' => 'list-content',
            'label' => esc_html__('List Style Position', 'bricksbooster'),
            'type' => 'select',
            'css' => [
                [
                    'property' => 'list-style-position',
                    'selector' => 'ul, ol',
                ],
            ],
            'options' => [
                'outside' => esc_html__('Outside', 'bricksbooster'),
                'inside' => esc_html__('Inside', 'bricksbooster'),
            ],
            'default' => 'outside',
        ];

        $this->controls['typography'] = [
			'group' => 'list-style',
            'tab' => 'content',
            'label' => esc_html__('Typography', 'bricksbooster'),
            'type' => 'typography',
            'css' => [
                [
                    'property' => 'font',
                    'selector' => 'li',
                ],
            ],
        ];

        $this->controls['itemPadding'] = [
			'group' => 'list-style',
            'tab' => 'content',
            'label' => esc_html__('Item Padding', 'bricksbooster'),
            'type' => 'spacing',
            'css' => [
                [
                    'property' => 'padding',
                    'selector' => 'li',
                ],
            ],
        ];

        $this->controls['itemMargin'] = [
			'group' => 'list-style',
            'tab' => 'content',
            'label' => esc_html__('Item Margin', 'bricksbooster'),
            'type' => 'spacing',
            'css' => [
                [
                    'property' => 'margin',
                    'selector' => 'li',
                ],
            ],
        ];

        $this->controls['background'] = [
			'group' => 'list-style',
            'tab' => 'content',
            'label' => esc_html__('Background', 'bricksbooster'),
            'type' => 'background',
            'css' => [
                [
                    'property' => 'background',
                    'selector' => 'li',
                ],
            ],
        ];

        $this->controls['customIcon'] = [
			'group' => 'list-style',
            'tab' => 'content',
            'label' => esc_html__('Custom List Icon/Image', 'bricksbooster'),
            'type' => 'image',
            'css' => [
                [
                    'property' => 'list-style-image',
                    'selector' => 'ul',
                    'value' => 'url({{url}})', // Bricks will replace {{url}} with the image URL
                ],
            ],
            'description' => esc_html__('Overrides list style type when set', 'bricksbooster'),
        ];

        $this->controls['indentation'] = [
			'group' => 'list-style',
            'tab' => 'content',
            'label' => esc_html__('Nested Indentation', 'bricksbooster'),
            'type' => 'number',
            'units' => true,
            'default' => '20px',
            'css' => [
                [
                    'property' => 'margin-left',
                    'selector' => 'ul ul, ol ol, ul ol, ol ul',
                ],
            ],
        ];
    }

    // Render element HTML
    public function render() {
        $settings = $this->settings;
        $items = isset($settings['items']) ? $settings['items'] : [];
        $listType = isset($settings['listType']) ? $settings['listType'] : 'ul';

        $this->set_attribute('_root', 'class', 'bb-simple-list');
        
        echo "<div {$this->render_attributes('_root')}>";
        // Check if there are child elements
        if (!empty($this->children)) {
            echo $this->render_children();
        } else if (!empty($items)) {
            // Render repeater items if no child elements
            echo $this->build_nested_list($items, $listType);
        } else {
            echo $this->render_element_placeholder(['title' => esc_html__('No list items found.', 'bricksbooster')]);
        }
        echo "</div>";
    }

    // Build nested list structure
    private function build_nested_list($items, $listType, $currentLevel = 1) {
        $html = "<{$listType}>";
        $i = 0;
        
        while ($i < count($items)) {
            $item = $items[$i];
            $itemLevel = isset($item['level']) ? (int)$item['level'] : 1;
            
            if ($itemLevel == $currentLevel) {
                $html .= '<li>';
                
                // Add link if specified
                if (!empty($item['link']['url'])) {
                    $link_attributes = '';
                    if (!empty($item['link']['target'])) {
                        $link_attributes .= ' target="' . esc_attr($item['link']['target']) . '"';
                    }
                    if (!empty($item['link']['rel'])) {
                        $link_attributes .= ' rel="' . esc_attr($item['link']['rel']) . '"';
                    }
                    $html .= '<a href="' . esc_url($item['link']['url']) . '"' . $link_attributes . '>' . esc_html($item['text']) . '</a>';
                } else {
                    $html .= esc_html($item['text']);
                }
                
                // Check for nested items
                $nestedItems = [];
                $j = $i + 1;
                while ($j < count($items) && isset($items[$j]['level']) && $items[$j]['level'] > $currentLevel) {
                    $nestedItems[] = $items[$j];
                    $j++;
                }
                
                if (!empty($nestedItems)) {
                    $html .= $this->build_nested_list($nestedItems, $listType, $currentLevel + 1);
                    $i = $j;
                } else {
                    $i++;
                }
                
                $html .= '</li>';
            } else if ($itemLevel < $currentLevel) {
                break;
            } else {
                $i++;
            }
        }
        
        $html .= "</{$listType}>";
        return $html;
    }

    // Enqueue scripts
    public function enqueue_scripts() {
        wp_enqueue_style(
            'bricksbooster-simple-list',
            BRICKSBOOSTER_URL . 'assets/css/elements/simple-list.css',
            [],
            BRICKSBOOSTER_VERSION
        );

        wp_enqueue_script(
            'bricksboosterSimpleList',
            BRICKSBOOSTER_URL . 'assets/js/elements/simple-list.js',
            ['bricks-scripts'],
            BRICKSBOOSTER_VERSION,
            true
        );
    }
}
