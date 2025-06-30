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

$section_id = 'bricksbooster_settings_group';

// Register settings - Use WordPress Settings API properly
add_action('admin_init', function() {
    $option_group = 'bricksbooster_settings_group';
    // Register TEMPLATES settings
    register_setting($option_group, 'bbooster_template_library_enabled');
    // Register DYNAMIC TAGS settings
    register_setting($option_group, 'bbooster_custom_tags_enabled');
    // Register ELEMENTS settings
    register_setting($option_group, 'bricksbooster_custom_elements_enabled');
    
    // Register BUILDER TWEAKS settings from features array
    $features = [
        'code_to_bricks' => 'Code to Bricks Converter',
        'html_validator' => 'HTML Visual Validator',
        'link_indicator' => 'Link Indicator'
    ];
    
    foreach ($features as $feature_key => $feature_name) {
        register_setting($option_group, 'bbooster_' . $feature_key . '_enabled');
    }
});

class BricksBooster_Options_Page {

    public function __construct() {
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
                        <button type="button" data-tab="builder-tweaks" class="bb-admin-nav-tab bb-admin-nav-tab-active">Builder Tweaks</button>
                        <button type="button" data-tab="tags" class="bb-admin-nav-tab">Tags</button>
                        <button type="button" data-tab="elements" class="bb-admin-nav-tab">Elements</button>
                        <button type="button" data-tab="templates" class="bb-admin-nav-tab">Templates</button>
                    </nav>
                    
                    <div class="bb-admin-tab-content">
                        <!-- Form using WordPress Settings API -->
                        <form action="options.php" method="post" id="bricksbooster-settings-form">
                            <?php
                            settings_fields('bricksbooster_settings_group');
                            do_settings_sections('bricksbooster-options');
                            ?>
                            <?php wp_nonce_field('ajax_file_nonce', 'security'); ?>
                            
                            <div id="builder-tweaks" class="bb-admin-tab-pane active">
                                <?php $this->render_builder_tweaks_tab(); ?>
                            </div>
                            <div id="templates" class="bb-admin-tab-pane">
                                <?php $this->render_templates_tab(); ?>
                            </div>
                            <div id="tags" class="bb-admin-tab-pane">
                                <?php $this->render_tags_tab(); ?>
                            </div>
                            <div id="elements" class="bb-admin-tab-pane">
                                <?php $this->render_elements_tab(); ?>
                            </div>
                            
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

    private function render_templates_tab() {
        $template_library_enabled = get_option('bbooster_template_library_enabled', 1);
        ?>
        <div class="bb-admin-settings-section">
            <h3>Template Library Settings</h3>
            <p>Enable/Disable the template library functionality.</p>

            <div class="bb-admin-toggles-grid">
                <div class="bb-admin-toggle">
                    <label>
                        <input type="checkbox" name="bbooster_template_library_enabled" value="1" <?php checked($template_library_enabled, 1); ?>>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">Template Library</span>
                        <span class="tooltip">
                            <span class="tooltip-icon">?</span>
                            <span class="tooltip-text">Enable the BricksBooster template library with pre-designed templates</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_tags_tab() {
        $custom_tags_enabled = get_option('bbooster_custom_tags_enabled', 1);
        ?>
        <div class="bb-admin-settings-section">
            <h3>Custom Tags Settings</h3>
            <p>Enable/Disable custom HTML/CSS tags functionality.</p>

            <div class="bb-admin-toggles-grid">
                <div class="bb-admin-toggle">
                    <label>
                        <input type="checkbox" name="bbooster_custom_tags_enabled" value="1" <?php checked($custom_tags_enabled, 1); ?>>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">Custom Tags</span>
                        <span class="tooltip">
                            <span class="tooltip-icon">?</span>
                            <span class="tooltip-text">Enable additional HTML/CSS tags in the Bricks builder</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_elements_tab() {
        $custom_elements_enabled = get_option('bricksbooster_custom_elements_enabled', 1);
        ?>
        <div class="bb-admin-settings-section">
            <h3>Custom Elements Settings</h3>
            <p>Enable/Disable custom elements functionality.</p>
            
            <div class="bb-admin-toggles-grid">
                <div class="bb-admin-toggle">
                    <label>
                        <input type="checkbox" name="bricksbooster_custom_elements_enabled" value="1" <?php checked($custom_elements_enabled, 1); ?>>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">Custom Elements</span>
                        <span class="tooltip">
                            <span class="tooltip-icon">?</span>
                            <span class="tooltip-text">Enable additional elements in the Bricks builder</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_builder_tweaks_tab() {
        $features = [
            'code_to_bricks' => 'Code to Bricks Converter',
            'html_validator' => 'HTML Visual Validator',
            'link_indicator' => 'Link Indicator'
        ];
        
        ?>
        <div class="bb-admin-settings-section">
            <h3>Builder Tweaks Settings</h3>
            <p>Enable/Disable builder enhancement tools.</p>
            
            <div class="bb-admin-toggles-grid">
                <?php foreach ($features as $feature_key => $feature_name) : ?>
                    <?php $feature_enabled = get_option('bbooster_' . $feature_key . '_enabled', 1); ?>
                    <div class="bb-admin-toggle">
                        <label>
                            <input type="checkbox" name="bbooster_<?php echo $feature_key; ?>_enabled" value="1" <?php checked($feature_enabled, 1); ?>>
                            <span class="toggle-switch"></span>
                            <span class="toggle-label"><?php echo $feature_name; ?></span>
                            <span class="tooltip">
                                <span class="tooltip-icon">?</span>
                                <span class="tooltip-text"><?php echo $feature_name; ?></span>
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

new BricksBooster_Options_Page();