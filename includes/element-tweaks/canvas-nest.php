<?php
/**
 * Canvas Nest Effect for Bricks Builder
 * 
 * @package BricksBooster
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class BricksBooster_Element_Tweaks_18 {
    private static $instance = null;
    
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'bricks/element/after_render', [ $this, 'add_canvas_nest_effect' ], 10, 1 );
        add_filter( 'bricks/element/render_attributes', [ $this, 'add_render_attributes' ], 10, 3 );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function enqueue_assets() {
        if ( ! get_option( 'bbooster_canvas_nest_enabled', 1 ) ) {
            return;
        }

        // Register canvas-nest.js from CDN
        wp_register_script(
            'canvas-nest-js',
            'https://cdn.jsdelivr.net/npm/canvas-nest.js@2.0.4/dist/canvas-nest.js',
            [],
            '2.0.4',
            true
        );

        // Our custom initialization script
        wp_register_script(
            'bricksbooster-canvas-nest',
            BRICKSBOOSTER_URL . 'assets/js/canvas-nest.js',
            [ 'jquery', 'canvas-nest-js' ],
            BRICKSBOOSTER_VERSION,
            true
        );

        // Only enqueue on frontend when needed
        if ( ! bricks_is_builder() ) {
            wp_enqueue_script( 'bricksbooster-canvas-nest' );
        }
    }

    public function add_render_attributes( $attributes, $element_name, $element_instance ) {
        $settings = $element_instance->settings;
        
        // Check if canvas nest is enabled for this element
        if ( empty( $settings['bboosterCanvasNestEnable'] ) ) {
            return $attributes;
        }

        // Add data attributes for configuration
        $config = [
            'color'       => ! empty( $settings['bboosterCanvasNestColor'] ) ? $settings['bboosterCanvasNestColor'] : '128,128,128',
            'opacity'     => isset( $settings['bboosterCanvasNestOpacity'] ) ? floatval( $settings['bboosterCanvasNestOpacity'] ) : 0.7,
            'count'       => ! empty( $settings['bboosterCanvasNestCount'] ) ? intval( $settings['bboosterCanvasNestCount'] ) : 150,
            'zIndex'      => ! empty( $settings['bboosterCanvasNestZIndex'] ) ? intval( $settings['bboosterCanvasNestZIndex'] ) : -1,
            'pointColor'  => ! empty( $settings['bboosterCanvasNestPointColor'] ) ? $settings['bboosterCanvasNestPointColor'] : '128,128,128',
            'pointOpacity' => isset( $settings['bboosterCanvasNestPointOpacity'] ) ? floatval( $settings['bboosterCanvasNestPointOpacity'] ) : 0.7,
            'pointRadius' => ! empty( $settings['bboosterCanvasNestPointRadius'] ) ? floatval( $settings['bboosterCanvasNestPointRadius'] ) : 1,
            'lineWidth'   => ! empty( $settings['bboosterCanvasNestLineWidth'] ) ? floatval( $settings['bboosterCanvasNestLineWidth'] ) : 0.5,
            'lineDistance' => ! empty( $settings['bboosterCanvasNestLineDistance'] ) ? intval( $settings['bboosterCanvasNestLineDistance'] ) : 150,
            'follow'      => ! empty( $settings['bboosterCanvasNestFollow'] ) ? 'true' : 'false',
            'mobile'      => ! empty( $settings['bboosterCanvasNestMobile'] ) ? 'true' : 'false',
        ];

        $attributes['data-canvas-nest-config'] = wp_json_encode( $config );
        $attributes['class'][] = 'bricksbooster-canvas-nest-container';

        return $attributes;
    }

    public function add_canvas_nest_effect( $element_instance ) {
        $settings = $element_instance->settings;
        
        // Check if canvas nest is enabled for this element
        if ( empty( $settings['bboosterCanvasNestEnable'] ) ) {
            return;
        }

        // Enqueue the script if not already enqueued
        if ( ! wp_script_is( 'bricksbooster-canvas-nest', 'enqueued' ) ) {
            wp_enqueue_script( 'bricksbooster-canvas-nest' );
        }
    }

    public static function get_controls() {
        return [
            'bboosterCanvasNest' => [
                'tab' => 'content',
                'label' => esc_html__( 'Canvas Nest Effect', 'bricksbooster' ),
                'type'  => 'separator',
            ],
            'bboosterCanvasNestEnable' => [
                'label' => esc_html__( 'Enable Canvas Nest', 'bricksbooster' ),
                'type'  => 'checkbox',
                'inline' => true,
                'default' => false,
                'description' => esc_html__( 'Add an interactive particle network animation to this container.', 'bricksbooster' ),
            ],
            'bboosterCanvasNestColor' => [
                'label' => esc_html__( 'Line Color', 'bricksbooster' ),
                'type'  => 'color',
                'default' => 'rgba(128, 128, 128, 0.7)',
                'css'   => [
                    [
                        'selector' => '',
                        'property' => '--canvas-nest-color',
                    ],
                ],
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestOpacity' => [
                'label' => esc_html__( 'Line Opacity', 'bricksbooster' ),
                'type'  => 'slider',
                'min'   => 0,
                'max'   => 1,
                'step'  => 0.1,
                'default' => 0.7,
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestCount' => [
                'label' => esc_html__( 'Particle Count', 'bricksbooster' ),
                'type'  => 'number',
                'min'   => 1,
                'max'   => 500,
                'default' => 150,
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestZIndex' => [
                'label' => esc_html__( 'Z-Index', 'bricksbooster' ),
                'type'  => 'number',
                'default' => -1,
                'description' => esc_html__( 'Lower values appear behind content.', 'bricksbooster' ),
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestPointColor' => [
                'label' => esc_html__( 'Particle Color', 'bricksbooster' ),
                'type'  => 'color',
                'default' => 'rgba(128, 128, 128, 0.7)',
                'css'   => [
                    [
                        'selector' => '',
                        'property' => '--canvas-nest-point-color',
                    ],
                ],
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestPointOpacity' => [
                'label' => esc_html__( 'Particle Opacity', 'bricksbooster' ),
                'type'  => 'slider',
                'min'   => 0,
                'max'   => 1,
                'step'  => 0.1,
                'default' => 0.7,
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestPointRadius' => [
                'label' => esc_html__( 'Particle Size', 'bricksbooster' ),
                'type'  => 'slider',
                'min'   => 0.1,
                'max'   => 5,
                'step'  => 0.1,
                'default' => 1,
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestLineWidth' => [
                'label' => esc_html__( 'Line Width', 'bricksbooster' ),
                'type'  => 'slider',
                'min'   => 0.1,
                'max'   => 5,
                'step'  => 0.1,
                'default' => 0.5,
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestLineDistance' => [
                'label' => esc_html__( 'Line Distance', 'bricksbooster' ),
                'type'  => 'number',
                'min'   => 10,
                'max'   => 500,
                'default' => 150,
                'description' => esc_html__( 'Maximum distance between connected points.', 'bricksbooster' ),
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestFollow' => [
                'label' => esc_html__( 'Follow Mouse', 'bricksbooster' ),
                'type'  => 'checkbox',
                'inline' => true,
                'default' => true,
                'description' => esc_html__( 'Particles follow mouse movement.', 'bricksbooster' ),
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
            'bboosterCanvasNestMobile' => [
                'label' => esc_html__( 'Enable on Mobile', 'bricksbooster' ),
                'type'  => 'checkbox',
                'inline' => true,
                'default' => false,
                'description' => esc_html__( 'Disable on mobile for better performance.', 'bricksbooster' ),
                'required' => [ 'bboosterCanvasNestEnable', '!=', '' ],
            ],
        ];
    }
}

// Initialize
add_action( 'init', function() {
    if ( get_option( 'bbooster_canvas_nest_enabled', 1 ) ) {
        BricksBooster_Element_Tweaks_18::get_instance();
    }
} );

// Add controls to sections and containers
add_filter( 'bricks/elements/container/controls', function( $controls ) {
    return array_merge( $controls, BricksBooster_Element_Tweaks_18::get_controls() );
} );

add_filter( 'bricks/elements/section/controls', function( $controls ) {
    return array_merge( $controls, BricksBooster_Element_Tweaks_18::get_controls() );
} );
