<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class BricksBooster_Math_Calculator_Tag {

    public function __construct() {
        // Hook into the very final output
        add_filter( 'the_content', [ $this, 'process_math_expressions' ], 999 );
        add_filter( 'bricks/frontend/render_data', [ $this, 'process_math_expressions' ], 999 );
        
        // Also process widget content
        add_filter( 'widget_text', [ $this, 'process_math_expressions' ], 999 );
        
        // Hook into wp_footer to catch any remaining content
        add_action( 'wp_footer', [ $this, 'process_page_content' ] );
    }

    public function process_page_content() {
        ob_start( [ $this, 'process_math_expressions' ] );
    }

    public function process_math_expressions( $content ) {
        if ( ! is_string( $content ) ) {
            return $content;
        }

        // Process math() functions
        $content = preg_replace_callback( '/math\(([^)]+)\)/', function( $matches ) {
            return $this->evaluate_math( $matches[1] );
        }, $content );

        // Process parentheses with math operators
        $content = preg_replace_callback( '/\(([^)]*[\+\-\*\/][^)]*)\)/', function( $matches ) {
            $expression = $matches[1];
            
            // Only process if it contains numbers and math operators
            if ( preg_match( '/[0-9]/', $expression ) && preg_match( '/[\+\-\*\/]/', $expression ) ) {
                return $this->evaluate_math( $expression );
            }
            
            return $matches[0];
        }, $content );

        return $content;
    }

    private function evaluate_math( $expression ) {
        // Remove any non-math characters
        $clean = preg_replace( '/[^0-9+\-*\/\(\).\s]/', '', $expression );
        
        // Validate
        if ( empty( $clean ) || ! preg_match( '/[0-9]/', $clean ) ) {
            return $expression;
        }
        
        // Check for division by zero
        if ( preg_match( '/\/\s*0(?!\d)/', $clean ) ) {
            return $expression;
        }
        
        try {
            $result = eval( "return $clean;" );
            
            if ( is_numeric( $result ) ) {
                // Format the result
                if ( is_float( $result ) && $result != (int) $result ) {
                    return round( $result, 2 );
                } else {
                    return (int) $result;
                }
            }
            
            return $expression;
        } catch ( Exception $e ) {
            return $expression;
        }
    }
}

// new BricksBooster_Math_Post_Processor();