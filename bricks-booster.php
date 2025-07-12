<?php
/**
 * Plugin Name: BricksBooster
 * Description: Supercharge your Bricks Builder with templates, builder tweaks, custom elements and dynamic tags
 * Version: 1.0.0
 * Author: Ayoub Khan
 * Text Domain: bricksbooster
 */

define('BRICKSBOOSTER_VERSION', '1.0.0');
define('BRICKSBOOSTER_DIR', plugin_dir_path(__FILE__));
define('BRICKSBOOSTER_URL', plugin_dir_url(__FILE__));

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Initialize plugin components
require_once BRICKSBOOSTER_DIR . 'includes/admin/options-page.php';

class BricksBooster {
    private static $instance = null;
    private $modules = [];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        $this->load_modules();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    private function load_modules() {
        // Load and instantiate modules
        $modules = [
            'builder_tweaks' => [
                'file' => 'class-builder-tweaks.php',
                'class' => 'BricksBooster_Builder_Tweaks'
            ],
            'templates' => [
                'file' => 'class-templates.php',
                'class' => 'BricksBooster_Templates'
            ],
            'elements' => [
                'file' => 'class-elements.php',
                'class' => 'BricksBooster_Elements'
            ],
            'dynamic_tags' => [
                'file' => 'class-dynamic-tags.php',
                'class' => 'BricksBooster_Dynamic_Tags'
            ],
            'query_loops' => [
                'file' => 'query-loops.php',
                'class' => 'BricksBooster_Query_Loops'
            ]
        ];

        foreach ($modules as $key => $module) {
            $file = BRICKSBOOSTER_DIR . 'includes/' . $module['file'];
            if (file_exists($file)) {
                require_once $file;
                $class_name = $module['class'];
                if (class_exists($class_name)) {
                    $this->modules[$key] = new $class_name();
                }
            }
        }
    }

    public function enqueue_assets() {
        if (bricks_is_builder()) {
            // Core assets
            wp_enqueue_style(
                'bricksbooster-core',
                BRICKSBOOSTER_URL . 'assets/css/core.css',
                [],
                BRICKSBOOSTER_VERSION
            );

            wp_enqueue_script(
                'bricksbooster-core',
                BRICKSBOOSTER_URL . 'assets/js/core.js',
                [],
                BRICKSBOOSTER_VERSION,
                true
            );
        } 
    }
}

BricksBooster::get_instance();
