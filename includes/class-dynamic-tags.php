<?php
class BricksBooster_Dynamic_Tags {
    public function __construct() {
        add_action('init', [$this, 'register_dynamic_tags']);
    }

    public function register_dynamic_tags() {

        // List of dynamic tags to register
        $tags = [
            'reading_time' => [
                'file'  => 'posts-tags.php',
                'class' => 'BricksBooster_Dynamic_Posts_Tags',
            ],
            'media_tags' => [
                'file'  => 'media-tags.php',
                'class' => 'BricksBooster_Dynamic_Media_Tags',
            ],
            // Add more dynamic tags here as needed
        ];

        foreach ( $tags as $tag ) {
            $file_path = BRICKSBOOSTER_PATH . 'includes/dynamic-tags/' . $tag['file'];
            if ( file_exists( $file_path ) ) {
                require_once $file_path;

                if ( class_exists( $tag['class'] ) ) {
                    // Initialize the dynamic tag
                    new $tag['class']();
                }
            }
        }
    }
}
