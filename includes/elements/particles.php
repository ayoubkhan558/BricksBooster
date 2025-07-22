<?php
class BricksBooster_Particles_Element extends \Bricks\Element {
    // Element properties
    public $category = 'BricksBooster';
    public $name = 'bricksbooster-particles';
    public $icon = 'ti-layout-media-overlay-alt-2';
    public $tag = 'div';
    public $scripts = ['bricks-booster-particles'];

    // Return local element items
    public function get_local_element_items() {
        return [
            'bricksbooster-particles' => [
                'title' => 'Particles',
                'icon'  => 'ti-layout-media-overlay-alt-2',
            ],
        ];
    }

    // Set builder controls
    public function set_controls() {
        // Content Tab
        $this->controls['_title'] = [
            'tab' => 'content',
            'label' => esc_html__('Title', 'bricks-booster'),
            'type' => 'text',
            'inline' => true,
            'placeholder' => esc_html__('Particles Background', 'bricks-booster'),
        ];

        // Style Tab
        $this->controls['_section_particles'] = [
            'tab' => 'style',
            'label' => esc_html__('Particles', 'bricks-booster'),
            'type' => 'separator',
        ];

        // Particle Type
        $this->controls['_particles_type'] = [
            'tab' => 'style',
            'label' => esc_html__('Particle Type', 'bricks-booster'),
            'type' => 'select',
            'options' => [
                'circle' => esc_html__('Circles', 'bricks-booster'),
                'triangle' => esc_html__('Triangles', 'bricks-booster'),
                'polygon' => esc_html__('Polygons', 'bricks-booster'),
                'star' => esc_html__('Stars', 'bricks-booster'),
                'edge' => esc_html__('Edges', 'bricks-booster'),
                'image' => esc_html__('Images', 'bricks-booster'),
            ],
            'default' => 'circle',
        ];

        // Particle Color
        $this->controls['_particles_color'] = [
            'tab' => 'style',
            'label' => esc_html__('Particle Color', 'bricks-booster'),
            'type' => 'color',
            'default' => '#ffffff',
            'css' => [
                [
                    'selector' => '',
                    'property' => '--particles-color',
                ],
            ],
        ];

        // Background Color
        $this->controls['_particles_bg_color'] = [
            'tab' => 'style',
            'label' => esc_html__('Background Color', 'bricks-booster'),
            'type' => 'color',
            'css' => [
                [
                    'selector' => '',
                    'property' => 'background-color',
                ],
            ],
        ];

        // Particle Count
        $this->controls['_particles_count'] = [
            'tab' => 'style',
            'label' => esc_html__('Particle Count', 'bricks-booster'),
            'type' => 'number',
            'min' => 1,
            'max' => 500,
            'default' => 80,
        ];

        // Particle Size
        $this->controls['_particles_size'] = [
            'tab' => 'style',
            'label' => esc_html__('Particle Size', 'bricks-booster'),
            'type' => 'number',
            'min' => 1,
            'max' => 50,
            'default' => 3,
            'css' => [
                [
                    'selector' => '',
                    'property' => '--particle-size',
                ],
            ],
        ];

        // Line Linked
        $this->controls['_particles_line_linked'] = [
            'tab' => 'style',
            'label' => esc_html__('Connect Particles', 'bricks-booster'),
            'type' => 'checkbox',
            'default' => true,
        ];

        // Line Color
        $this->controls['_particles_line_color'] = [
            'tab' => 'style',
            'label' => esc_html__('Connection Line Color', 'bricks-booster'),
            'type' => 'color',
            'default' => '#ffffff',
            'required' => ['_particles_line_linked', '!=', ''],
            'css' => [
                [
                    'selector' => '',
                    'property' => '--line-color',
                ],
            ],
        ];

        // Line Width
        $this->controls['_particles_line_width'] = [
            'tab' => 'style',
            'label' => esc_html__('Connection Line Width', 'bricks-booster'),
            'type' => 'number',
            'min' => 0.1,
            'max' => 5,
            'step' => 0.1,
            'default' => 1,
            'required' => ['_particles_line_linked', '!=', ''],
            'css' => [
                [
                    'selector' => '',
                    'property' => '--line-width',
                ],
            ],
        ];

        // Move Speed
        $this->controls['_particles_move_speed'] = [
            'tab' => 'style',
            'label' => esc_html__('Move Speed', 'bricks-booster'),
            'type' => 'number',
            'min' => 0.1,
            'max' => 10,
            'step' => 0.1,
            'default' => 2,
        ];

        // Direction
        $this->controls['_particles_direction'] = [
            'tab' => 'style',
            'label' => esc_html__('Move Direction', 'bricks-booster'),
            'type' => 'select',
            'options' => [
                'none' => esc_html__('None', 'bricks-booster'),
                'top' => esc_html__('Top', 'bricks-booster'),
                'top-right' => esc_html__('Top Right', 'bricks-booster'),
                'right' => esc_html__('Right', 'bricks-booster'),
                'bottom-right' => esc_html__('Bottom Right', 'bricks-booster'),
                'bottom' => esc_html__('Bottom', 'bricks-booster'),
                'bottom-left' => esc_html__('Bottom Left', 'bricks-booster'),
                'left' => esc_html__('Left', 'bricks-booster'),
                'top-left' => esc_html__('Top Left', 'bricks-booster'),
            ],
            'default' => 'none',
        ];

        // Shape Type
        $this->controls['_particles_shape_type'] = [
            'tab' => 'style',
            'label' => esc_html__('Shape Type', 'bricks-booster'),
            'type' => 'select',
            'options' => [
                'circle' => esc_html__('Circle', 'bricks-booster'),
                'square' => esc_html__('Square', 'bricks-booster'),
                'triangle' => esc_html__('Triangle', 'bricks-booster'),
                'polygon' => esc_html__('Polygon', 'bricks-booster'),
                'star' => esc_html__('Star', 'bricks-booster'),
                'image' => esc_html__('Image', 'bricks-booster'),
            ],
            'default' => 'circle',
        ];

        // Image URL
        $this->controls['_particles_image'] = [
            'tab' => 'style',
            'label' => esc_html__('Particle Image', 'bricks-booster'),
            'type' => 'image',
            'required' => ['_particles_shape_type', '=', 'image'],
        ];

        // Interactivity
        $this->controls['_particles_interactivity'] = [
            'tab' => 'style',
            'label' => esc_html__('Enable Interactivity', 'bricks-booster'),
            'type' => 'checkbox',
            'default' => true,
        ];

        // Hover Effect
        $this->controls['_particles_hover_effect'] = [
            'tab' => 'style',
            'label' => esc_html__('Hover Effect', 'bricks-booster'),
            'type' => 'select',
            'options' => [
                'grab' => esc_html__('Grab', 'bricks-booster'),
                'bubble' => esc_html__('Bubble', 'bricks-booster'),
                'repulse' => esc_html__('Repulse', 'bricks-booster'),
                'none' => esc_html__('None', 'bricks-booster'),
            ],
            'default' => 'grab',
            'required' => ['_particles_interactivity', '!=', ''],
        ];

        // Click Effect
        $this->controls['_particles_click_effect'] = [
            'tab' => 'style',
            'label' => esc_html__('Click Effect', 'bricks-booster'),
            'type' => 'select',
            'options' => [
                'push' => esc_html__('Push', 'bricks-booster'),
                'bubble' => esc_html__('Bubble', 'bricks-booster'),
                'repulse' => esc_html__('Repulse', 'bricks-booster'),
                'none' => esc_html__('None', 'bricks-booster'),
            ],
            'default' => 'push',
            'required' => ['_particles_interactivity', '!=', ''],
        ];
    }

    // Enqueue scripts and styles
    public function enqueue_scripts() {
        wp_enqueue_script(
            'particles-js',
            'https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js',
            [],
            '2.0.0',
            true
        );

        wp_enqueue_script(
            'bricks-booster-particles',
            BRICKSBOOSTER_PLUGIN_URL . 'assets/js/particles.js',
            ['jquery', 'particles-js'],
            BRICKSBOOSTER_VERSION,
            true
        );

        wp_enqueue_style(
            'bricks-booster-particles',
            BRICKSBOOSTER_PLUGIN_URL . 'assets/css/particles.css',
            [],
            BRICKSBOOSTER_VERSION
        );
    }

    // Render the element
    public function render() {
        $settings = $this->settings;
        $id = 'bricks-particles-' . $this->id;

        // Default attributes
        $this->set_attribute('_root', 'id', $id);
        $this->set_attribute('_root', 'class', 'bricks-particles-container');
        $this->set_attribute('_root', 'data-particles-config', wp_json_encode($this->get_particles_config()));

        // Add title if set
        $title = !empty($settings['_title']) ? '<h3 class="bricks-particles-title">' . esc_html($settings['_title']) . '</h3>' : '';

        echo "<div {$this->render_attributes('_root')}>";
        echo '<div id="' . esc_attr($id) . '-particles" class="bricks-particles"></div>';
        echo '<div class="bricks-particles-content">' . $title . $this->render_children($this->children) . '</div>';
        echo '</div>';
    }

    // Generate particles configuration
    private function get_particles_config() {
        $settings = $this->settings;
        
        $config = [
            'particles' => [
                'number' => [
                    'value' => !empty($settings['_particles_count']) ? intval($settings['_particles_count']) : 80,
                    'density' => [
                        'enable' => true,
                        'value_area' => 800,
                    ],
                ],
                'color' => [
                    'value' => !empty($settings['_particles_color']) ? $settings['_particles_color'] : '#ffffff',
                ],
                'shape' => [
                    'type' => $this->get_particle_shape(),
                    'stroke' => [
                        'width' => 0,
                        'color' => '#000000',
                    ],
                    'polygon' => [
                        'nb_sides' => 5,
                    ],
                ],
                'opacity' => [
                    'value' => 0.5,
                    'random' => true,
                    'anim' => [
                        'enable' => true,
                        'speed' => 1,
                        'opacity_min' => 0.1,
                        'sync' => false,
                    ],
                ],
                'size' => [
                    'value' => !empty($settings['_particles_size']) ? intval($settings['_particles_size']) : 3,
                    'random' => true,
                    'anim' => [
                        'enable' => true,
                        'speed' => 2,
                        'size_min' => 0.1,
                        'sync' => false,
                    ],
                ],
                'line_linked' => [
                    'enable' => !empty($settings['_particles_line_linked']),
                    'distance' => 150,
                    'color' => !empty($settings['_particles_line_color']) ? $settings['_particles_line_color'] : '#ffffff',
                    'opacity' => 0.4,
                    'width' => !empty($settings['_particles_line_width']) ? floatval($settings['_particles_line_width']) : 1,
                ],
                'move' => [
                    'enable' => true,
                    'speed' => !empty($settings['_particles_move_speed']) ? floatval($settings['_particles_move_speed']) : 2,
                    'direction' => $this->get_particle_direction(),
                    'random' => $this->get_particle_direction() === 'none',
                    'straight' => false,
                    'out_mode' => 'out',
                    'bounce' => false,
                    'attract' => [
                        'enable' => true,
                        'rotateX' => 600,
                        'rotateY' => 1200,
                    ],
                ],
            ],
            'interactivity' => [
                'detect_on' => 'canvas',
                'events' => [
                    'onhover' => [
                        'enable' => !empty($settings['_particles_interactivity']),
                        'mode' => !empty($settings['_particles_hover_effect']) ? $settings['_particles_hover_effect'] : 'grab',
                    ],
                    'onclick' => [
                        'enable' => !empty($settings['_particles_interactivity']),
                        'mode' => !empty($settings['_particles_click_effect']) ? $settings['_particles_click_effect'] : 'push',
                    ],
                    'resize' => true,
                ],
                'modes' => [
                    'grab' => [
                        'distance' => 140,
                        'line_linked' => [
                            'opacity' => 1,
                        ],
                    ],
                    'bubble' => [
                        'distance' => 400,
                        'size' => 40,
                        'duration' => 2,
                        'opacity' => 8,
                        'speed' => 3,
                    ],
                    'repulse' => [
                        'distance' => 200,
                        'duration' => 0.4,
                    ],
                    'push' => [
                        'particles_nb' => 4,
                    ],
                    'remove' => [
                        'particles_nb' => 2,
                    ],
                ],
            ],
            'retina_detect' => true,
        ];

        // Add image if shape type is image and image is set
        if (!empty($settings['_particles_shape_type']) && $settings['_particles_shape_type'] === 'image' && !empty($settings['_particles_image']['url'])) {
            $config['particles']['shape']['type'] = 'image';
            $config['particles']['shape']['image'] = [
                'src' => $settings['_particles_image']['url'],
                'width' => 100,
                'height' => 100,
            ];
        }

        return $config;
    }

    // Get particle shape based on settings
    private function get_particle_shape() {
        $settings = $this->settings;
        
        if (empty($settings['_particles_shape_type'])) {
            return 'circle';
        }
        
        return $settings['_particles_shape_type'];
    }

    // Get particle direction based on settings
    private function get_particle_direction() {
        $settings = $this->settings;
        
        if (empty($settings['_particles_direction']) || $settings['_particles_direction'] === 'none') {
            return 'none';
        }
        
        $directions = [
            'top' => 'top',
            'top-right' => 'top-right',
            'right' => 'right',
            'bottom-right' => 'bottom-right',
            'bottom' => 'bottom',
            'bottom-left' => 'bottom-left',
            'left' => 'left',
            'top-left' => 'top-left',
        ];
        
        return $directions[$settings['_particles_direction']] ?? 'none';
    }
}
