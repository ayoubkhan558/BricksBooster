<?php
/**
 * BricksBooster Admin Options Page
 */

// Define constants if not already defined
if (!defined('BRICKSBOOSTER_PATH')) {
    define('BRICKSBOOSTER_PATH', plugin_dir_path(__FILE__) . '../../');
}

if (!defined('BRICKSBOOSTER_URL')) {
    define('BRICKSBOOSTER_URL', plugin_dir_url(__FILE__) . '../../');
}

// Register settings - Use WordPress Settings API properly
add_action('admin_init', function() {
    $option_group = 'bricksbooster_settings_group';
    // Register TEMPLATES settings
    register_setting($option_group, 'bbooster_template_library_enabled');
    // Register DYNAMIC TAGS settings
    register_setting($option_group, 'bbooster_post_tags_enabled');
    register_setting($option_group, 'bbooster_media_tags_enabled');
    register_setting($option_group, 'bbooster_math_tags_enabled');
    // Register ELEMENTS settings
    register_setting($option_group, 'bricksbooster_nestable_list_enabled');
    register_setting($option_group, 'bricksbooster_nestable_link_enabled');
    register_setting($option_group, 'bricksbooster_simple_list_enabled');     
    // Register QUERY LOOPS settings
    register_setting($option_group, 'bricksbooster_query_loops_enabled');
    register_setting($option_group, 'bricksbooster_comments_query_enabled');
    register_setting($option_group, 'bricksbooster_woocommerce_orders_query_enabled');
    
        // Register ELEMENT TWEAKS settings
    register_setting($option_group, 'bbooster_animation_tweak_enabled', 'intval');
    
    // Register BUILDER TWEAKS settings from features array
    $features = [
        'code_to_bricks' => 'Code to Bricks Converter',
        'html_validator' => 'HTML Visual Validator',
        'link_indicator' => 'Link Indicator',
        'animation_tweak' => 'Animation Tweak',
    ];
    
    foreach ($features as $feature_key => $feature_name) {
        register_setting($option_group, 'bbooster_' . $feature_key . '_enabled');
    }
});

// Include tab classes
require_once BRICKSBOOSTER_PATH . 'includes/admin/tabs/templates.php';
require_once BRICKSBOOSTER_PATH . 'includes/admin/tabs/tags.php';
require_once BRICKSBOOSTER_PATH . 'includes/admin/tabs/elements.php';
require_once BRICKSBOOSTER_PATH . 'includes/admin/tabs/builder-tweaks.php';
require_once BRICKSBOOSTER_PATH . 'includes/admin/tabs/query-loops.php';
require_once BRICKSBOOSTER_PATH . 'includes/admin/tabs/element-tweaks.php';

class BricksBooster_Options_Page {
    private $tabs = [];

    public function __construct() {
        // Initialize tab classes
        $this->tabs = [
            'templates' => new BricksBooster_Templates_Tab(),
            'tags' => new BricksBooster_Tags_Tab(),
            'elements' => new BricksBooster_Elements_Tab(),
            'element-tweaks' => new BricksBooster_Element_Tweaks_Tab(),
            'builder-tweaks' => new BricksBooster_Builder_Tweaks_Tab(),
            'query-loops' => new BricksBooster_Query_Loops_Tab()
        ];

        // Use priority 99 to ensure this appears last
        add_action('admin_menu', [$this, 'add_options_page'], 99);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_options_page() {
        add_submenu_page(
            'bricks',
            'BricksBooster Settings',
            'Bricks Booster',
            'manage_options', 
            'bricksbooster-options',
            [$this, 'render_page']
        );
    }

    public function enqueue_assets($hook) {
        // Only load assets on our options page
        if ($hook !== 'bricks_page_bricksbooster-options') {
            return;
        }

        wp_enqueue_style(
            'bricksbooster-admin',
            BRICKSBOOSTER_URL . 'assets/css/admin/options-page.css',
            [],
            filemtime(BRICKSBOOSTER_PATH . 'assets/css/admin/options-page.css')
        );

        wp_enqueue_script(
            'bricksbooster-admin',
            BRICKSBOOSTER_URL . 'assets/js/admin/options-page.js',
            ['jquery'],
            filemtime(BRICKSBOOSTER_PATH . 'assets/js/admin/options-page.js'),
            true
        );
    }

    public function render_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check if settings were updated
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'bricksbooster_messages',
                'bricksbooster_message',
                __('BricksBooster settings saved', 'bricksbooster'),
                'updated'
            );
        }

        // Show error/update messages
        settings_errors('bricksbooster_messages');

        $plugin_data = get_plugin_data(BRICKSBOOSTER_PATH . 'bricks-booster.php');
        ?>
        <div class="wrap">
            <div class="bb-admin-options">
                <div class="bb-admin-main">
                    <div class="bb-admin-header">
                        <h1 class="bb-admin-title"><?php echo esc_html($plugin_data['Name']); ?></h1>
                    </div>
                    
                    <nav class="bb-admin-nav-tab-wrapper">
                        <?php foreach ($this->tabs as $tab_id => $tab) : ?>
                            <button type="button" data-tab="<?php echo esc_attr($tab_id); ?>" class="bb-admin-nav-tab <?php echo $tab_id === 'builder-tweaks' ? 'bb-admin-nav-tab-active' : ''; ?>">
                                <?php echo ucwords(str_replace('-', ' ', $tab_id)); ?>
                            </button>
                        <?php endforeach; ?>
                    </nav>
                    
                    <div class="bb-admin-tab-content">
                        <!-- Form using WordPress Settings API -->
                        <form action="options.php" method="post" id="bricksbooster-settings-form">
                            <?php
                            settings_fields('bricksbooster_settings_group');
                            do_settings_sections('bricksbooster-options');
                            ?>
                            <?php wp_nonce_field('ajax_file_nonce', 'security'); ?>
                            
                            <?php foreach ($this->tabs as $tab_id => $tab) : ?>
                                <div id="<?php echo esc_attr($tab_id); ?>" class="bb-admin-tab-pane <?php echo $tab_id === 'builder-tweaks' ? 'active' : ''; ?>">
                                    <?php $tab->render(); ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php submit_button('Save All Settings'); ?>
                        </form>
                    </div>
                </div>
                
                <div class="bb-admin-sidebar">
                    <div class="bb-admin-sidebar-box">
                        <div class="bb-admin-sidebar-header">
                            <?php _e('About', 'bricksbooster'); ?>
                        </div>
                        <div class="bb-admin-sidebar-content">
                            <p><strong>Version:</strong> <?php echo esc_html($plugin_data['Version']); ?></p>
                            <p><strong>Author:</strong> <a href="https://www.linkedin.com/in/ayoubkhan558" target="_blank" rel="noopener noreferrer"><?php echo esc_html($plugin_data['Author']); ?></a></p>
                            <div><?php echo wp_kses_post($plugin_data['Description']); ?></div>
                            <p><a href="#" target="_blank"><?php _e('View Documentation', 'bricksbooster'); ?></a></p>
                        </div>
                    </div>
                    
                    <div class="bb-admin-sidebar-box">
                        <div class="bb-admin-sidebar-header">
                            <?php _e('Need Help?', 'bricksbooster'); ?>
                        </div>
                        <div class="bb-admin-sidebar-content">
                            <p><?php _e('If you need help with BricksBooster, please check our documentation or contact support.', 'bricksbooster'); ?></p>
                            <br/>
                            <p><a href="#" class="bb-admin-button-primary" target="_blank"><?php _e('Get Support', 'bricksbooster'); ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

new BricksBooster_Options_Page();