<?php
class BricksBooster_Query_Loops {
    public function __construct() {
        add_action('init', [$this, 'register_query_loops']);
    }

    public function register_query_loops() {
        // List of dynamic tags to register
        $tags = [
            'comments_loop' => [
                'file'  => 'comments-loop.php',
                'class' => 'BricksBooster_Query_Loops_Comments_Loop',
            ],
            // Add more dynamic tags here as needed
        ];

        foreach ( $tags as $tag ) {
            $file_path = BRICKSBOOSTER_PATH . 'includes/query-loops/' . $tag['file'];
            if ( file_exists( $file_path ) ) {
                require_once $file_path;

                if ( class_exists( $tag['class'] ) ) {
                    new $tag['class']();
                }
            }
        }
    }
}

// Instantiate the class
// new BricksBooster_Query_Loops();
