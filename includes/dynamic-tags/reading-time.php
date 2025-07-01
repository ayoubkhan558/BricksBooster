<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BricksBooster_Dynamic_Tags_ReadingTime extends \Bricks\Integrations\Dynamic_Data\Tag_Base {
    
    public function __construct() {
        // Set tag properties
        $this->tag = 'reading_time';
        $this->name = esc_html__('Reading Time', 'bricksbooster');
        $this->group = 'bricksbooster'; // Custom group for your tags
        
        // Register the tag
        add_action('init', [$this, 'register_tag']);
    }

    public function register_tag() {
        // Register this tag with Bricks
        add_filter('bricks/dynamic_tags_list', [$this, 'add_tag_to_list']);
        
        // Enqueue tag-specific assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_tag_to_list($tags) {
        $tags[$this->tag] = [
            'name' => $this->name,
            'group' => $this->group,
        ];
        return $tags;
    }

    public function enqueue_assets() {
        // Only enqueue if we're in Bricks builder or frontend with Bricks elements
        if (bricks_is_builder() || bricks_is_frontend()) {
            wp_enqueue_style(
                'bricksbooster-reading-time',
                BRICKSBOOSTER_URL . 'assets/css/dynamic-tags/reading-time.css',
                [],
                BRICKSBOOSTER_VERSION
            );

            wp_enqueue_script(
                'bricksbooster-reading-time',
                BRICKSBOOSTER_URL . 'assets/js/dynamic-tags/reading-time.js',
                ['jquery'],
                BRICKSBOOSTER_VERSION,
                true
            );
        }
    }

    public function set_controls() {
        // Content source control
        $this->controls['source'] = [
            'label' => esc_html__('Content Source', 'bricksbooster'),
            'type' => 'select',
            'options' => [
                'current_post' => esc_html__('Current Post Content', 'bricksbooster'),
                'custom_field' => esc_html__('Custom Field', 'bricksbooster'),
                'custom_text' => esc_html__('Custom Text', 'bricksbooster'),
            ],
            'default' => 'current_post',
        ];

        // Custom field name (shown when source is custom_field)
        $this->controls['custom_field'] = [
            'label' => esc_html__('Custom Field Name', 'bricksbooster'),
            'type' => 'text',
            'required' => ['source', '=', 'custom_field'],
        ];

        // Custom text (shown when source is custom_text)
        $this->controls['custom_text'] = [
            'label' => esc_html__('Custom Text', 'bricksbooster'),
            'type' => 'textarea',
            'required' => ['source', '=', 'custom_text'],
        ];

        // Words per minute
        $this->controls['wpm'] = [
            'label' => esc_html__('Words Per Minute', 'bricksbooster'),
            'type' => 'number',
            'min' => 100,
            'max' => 500,
            'default' => 200,
            'description' => esc_html__('Average reading speed (default: 200 WPM)', 'bricksbooster'),
        ];

        // Output format
        $this->controls['format'] = [
            'label' => esc_html__('Output Format', 'bricksbooster'),
            'type' => 'select',
            'options' => [
                'minutes_only' => esc_html__('Minutes only (e.g., "5")', 'bricksbooster'),
                'minutes_text' => esc_html__('Minutes with text (e.g., "5 min read")', 'bricksbooster'),
                'full_text' => esc_html__('Full text (e.g., "5 minutes read")', 'bricksbooster'),
                'detailed' => esc_html__('Detailed (e.g., "5 min 30 sec read")', 'bricksbooster'),
            ],
            'default' => 'minutes_text',
        ];

        // Minimum reading time
        $this->controls['min_time'] = [
            'label' => esc_html__('Minimum Reading Time', 'bricksbooster'),
            'type' => 'number',
            'min' => 1,
            'default' => 1,
            'description' => esc_html__('Minimum time to display (in minutes)', 'bricksbooster'),
        ];

        // Include images in calculation
        $this->controls['include_images'] = [
            'label' => esc_html__('Include Images', 'bricksbooster'),
            'type' => 'checkbox',
            'description' => esc_html__('Add extra time for images (12 seconds per image)', 'bricksbooster'),
        ];
    }

    public function get_value($settings) {
        // Get content based on source
        $content = $this->get_content($settings);
        
        if (empty($content)) {
            return '';
        }

        // Get settings with defaults
        $wpm = isset($settings['wpm']) ? (int) $settings['wpm'] : 200;
        $format = isset($settings['format']) ? $settings['format'] : 'minutes_text';
        $min_time = isset($settings['min_time']) ? (int) $settings['min_time'] : 1;
        $include_images = isset($settings['include_images']) && $settings['include_images'];

        // Calculate reading time
        $reading_time = $this->calculate_reading_time(
            $content, 
            $wpm, 
            $include_images
        );

        // Apply minimum time
        $reading_time = max($reading_time, $min_time * 60); // Convert min_time to seconds

        // Format output
        return $this->format_reading_time($reading_time, $format);
    }

    private function get_content($settings) {
        $source = isset($settings['source']) ? $settings['source'] : 'current_post';
        
        switch ($source) {
            case 'current_post':
                global $post;
                if (!$post) {
                    return '';
                }
                return get_the_content();
                
            case 'custom_field':
                $field_name = isset($settings['custom_field']) ? $settings['custom_field'] : '';
                if (empty($field_name)) {
                    return '';
                }
                global $post;
                if (!$post) {
                    return '';
                }
                return get_post_meta($post->ID, $field_name, true);
                
            case 'custom_text':
                return isset($settings['custom_text']) ? $settings['custom_text'] : '';
                
            default:
                return '';
        }
    }

    private function calculate_reading_time($content, $wpm, $include_images = false) {
        // Strip HTML tags and get plain text
        $text = wp_strip_all_tags($content);
        
        // Count words
        $word_count = str_word_count($text);
        
        // Calculate base reading time in seconds
        $reading_time_seconds = ($word_count / $wpm) * 60;
        
        // Add time for images if enabled
        if ($include_images) {
            $image_count = substr_count($content, '<img');
            $reading_time_seconds += $image_count * 12; // 12 seconds per image
        }
        
        return $reading_time_seconds;
    }

    private function format_reading_time($seconds, $format) {
        $minutes = ceil($seconds / 60);
        $remaining_seconds = $seconds % 60;
        
        switch ($format) {
            case 'minutes_only':
                return (string) $minutes;
                
            case 'minutes_text':
                return sprintf(_n('%d min read', '%d min read', $minutes, 'bricksbooster'), $minutes);
                
            case 'full_text':
                return sprintf(_n('%d minute read', '%d minutes read', $minutes, 'bricksbooster'), $minutes);
                
            case 'detailed':
                if ($remaining_seconds > 30) {
                    $seconds_display = ceil($remaining_seconds / 10) * 10; // Round to nearest 10
                    return sprintf(
                        esc_html__('%d min %d sec read', 'bricksbooster'), 
                        $minutes, 
                        $seconds_display
                    );
                } else {
                    return sprintf(_n('%d min read', '%d min read', $minutes, 'bricksbooster'), $minutes);
                }
                
            default:
                return sprintf(_n('%d min read', '%d min read', $minutes, 'bricksbooster'), $minutes);
        }
    }

    // Add method to handle tag rendering in builder
    public function get_tag_value_for_builder($post_id, $settings = []) {
        // Set global post for builder context
        global $post;
        $original_post = $post;
        $post = get_post($post_id);
        
        $value = $this->get_value($settings);
        
        // Restore original post
        $post = $original_post;
        
        return $value;
    }
}

// Initialize the dynamic tag
new BricksBooster_Dynamic_Tags_ReadingTime();