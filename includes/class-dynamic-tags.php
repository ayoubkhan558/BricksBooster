<?php
class BricksBooster_Dynamic_Tags {
    public function __construct() {
        add_action('init', [$this, 'register_dynamic_tags']);
    }

    public function register_dynamic_tags() {
        // Get options from settings
        $post_tags_enabled = get_option('bbooster_post_tags_enabled', 1);
        $media_tags_enabled = get_option('bbooster_media_tags_enabled', 1);
        $math_tags_enabled = get_option('bbooster_math_tags_enabled', 1);

        // List of dynamic tags to register
        $tags = [
            'reading_time' => [
                'file'  => 'posts-tags.php',
                'class' => 'BricksBooster_Dynamic_Posts_Tags',
                'enabled' => $post_tags_enabled
            ],
            'media_tags' => [
                'file'  => 'media-tags.php',
                'class' => 'BricksBooster_Dynamic_Media_Tags',
                'enabled' => $media_tags_enabled
            ],
            'math_tags' => [
                'file'  => 'tag-math.php',
                'class' => 'BricksBooster_Math_Calculator_Tag',
                'enabled' => $math_tags_enabled
            ],
            // Add more dynamic tags here as needed
        ];

        foreach ( $tags as $tag ) {
            // Only register if tag is enabled
            if (!$tag['enabled']) {
                continue;
            }

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
