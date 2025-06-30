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

// Register settings
add_action('admin_init', function() {
    // Template settings
    register_setting('bricksbooster_templates', 'bricksbooster_template_library_enabled');
    
    // Tag settings
    register_setting('bricksbooster_tags', 'bricksbooster_custom_tags_enabled');
    
    // Element settings
    register_setting('bricksbooster_elements', 'bricksbooster_custom_elements_enabled');
    
    // Builder tweaks settings
    register_setting('bricksbooster_builder_tweaks', 'bricksbooster_html_validator_enabled');
    register_setting('bricksbooster_builder_tweaks', 'bricksbooster_code_to_bricks_enabled');
});

// AJAX handler for saving options
add_action('wp_ajax_bricksbooster_save_options', function() {
    // Verify nonce for security
    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'bricksbooster_options_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    // Process and save each setting
    $settings = [
        'bricksbooster_template_library_enabled',
        'bricksbooster_custom_tags_enabled',
        'bricksbooster_custom_elements_enabled',
        'bricksbooster_html_validator_enabled',
        'bricksbooster_code_to_bricks_enabled'
    ];
    
    foreach ($settings as $setting) {
        if (isset($_REQUEST[$setting])) {
            update_option($setting, (bool)$_REQUEST[$setting]);
        } else {
            update_option($setting, false);
        }
    }
    
    wp_send_json_success('Settings saved successfully');
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
        $plugin_data = get_plugin_data(BRICKSBOOSTER_PATH . 'bricks-booster.php');
        ?>
        <div class="wrap">
            <div class="bb-admin-options">
                <div class="bb-admin-main">
                    <div class="bb-admin-header">
                        <h1 class="bb-admin-title"><?php echo esc_html($plugin_data['Name']); ?></h1>
                    </div>
                    
                    <nav class="bb-admin-nav-tab-wrapper">
                        <button type="button" data-tab="templates" class="bb-admin-nav-tab bb-admin-nav-tab-active">Templates</button>
                        <button type="button" data-tab="tags" class="bb-admin-nav-tab">Tags</button>
                        <button type="button" data-tab="elements" class="bb-admin-nav-tab">Elements</button>
                        <button type="button" data-tab="builder-tweaks" class="bb-admin-nav-tab">Builder Tweaks</button>
                    </nav>
                    
                    <div class="bb-admin-tab-content">
                        <div id="templates" class="bb-admin-tab-pane active">
                            <?php $this->render_templates_tab(); ?>
                        </div>
                        <div id="tags" class="bb-admin-tab-pane">
                            <?php $this->render_tags_tab(); ?>
                        </div>
                        <div id="elements" class="bb-admin-tab-pane">
                            <?php $this->render_elements_tab(); ?>
                        </div>
                        <div id="builder-tweaks" class="bb-admin-tab-pane">
                            <?php $this->render_builder_tweaks_tab(); ?>
                        </div>
                    </div>
                </div>
                
                <div class="bb-admin-sidebar">
                    <div class="bb-admin-sidebar-box">
                        <div class="bb-admin-sidebar-header">
                            <?php _e('About', 'bricksbooster'); ?>
                        </div>
                        <div class="bb-admin-sidebar-content">
                            <p><strong>Version:</strong> <?php echo esc_html($plugin_data['Version']); ?></p>
                            <p><strong>Author:</strong> <?php echo esc_html($plugin_data['Author']); ?></p>
                            <p><?php echo esc_html($plugin_data['Description']); ?></p>
                            <p><a href="#" target="_blank"><?php _e('View Documentation', 'bricksbooster'); ?></a></p>
                        </div>
                    </div>
                    
                    <div class="bb-admin-sidebar-box">
                        <div class="bb-admin-sidebar-header">
                            <?php _e('Need Help?', 'bricksbooster'); ?>
                        </div>
                        <div class="bb-admin-sidebar-content">
                            <p><?php _e('If you need help with BricksBooster, please check our documentation or contact support.', 'bricksbooster'); ?></p>
                            <p><a href="#" class="bb-admin-button-primary" target="_blank"><?php _e('Get Support', 'bricksbooster'); ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_templates_tab() {
        echo '<div class="bb-admin-settings-section">';
        echo '<h3>Customize the functions included in BricksBooster</h3>';
        echo '<p>Enable/Disable any of the following settings. Once disabled, the corresponding function will be completely disabled on both the backend and the frontend.</p>';
        
        echo '<form method="post" action="options.php">';
        wp_nonce_field('bricksbooster_options_nonce');
        settings_fields('bricksbooster_templates');
        do_settings_sections('bricksbooster_templates');
        
        $template_library_enabled = get_option('bricksbooster_template_library_enabled', true);
        echo '<div class="bb-admin-toggle">';
        echo '<label><input type="checkbox" name="bricksbooster_template_library_enabled" value="1" ' . checked($template_library_enabled, true, false) . '> Template Library</label>';
        echo '<p class="description">Enable the BricksBooster template library with pre-designed templates</p>';
        echo '</div>';
        
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    private function render_tags_tab() {
        echo '<div class="bb-admin-settings-section">';
        echo '<h3>Customize the functions included in BricksBooster</h3>';
        echo '<p>Enable/Disable any of the following settings. Once disabled, the corresponding function will be completely disabled on both the backend and the frontend.</p>';
        
        echo '<form method="post" action="options.php">';
        wp_nonce_field('bricksbooster_options_nonce');
        settings_fields('bricksbooster_tags');
        do_settings_sections('bricksbooster_tags');
        
        $custom_tags_enabled = get_option('bricksbooster_custom_tags_enabled', true);
        echo '<div class="bb-admin-toggle">';
        echo '<label><input type="checkbox" name="bricksbooster_custom_tags_enabled" value="1" ' . checked($custom_tags_enabled, true, false) . '> Custom Tags</label>';
        echo '<p class="description">Enable additional HTML/CSS tags in the Bricks builder</p>';
        echo '</div>';
        
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    private function render_elements_tab() {
        echo '<div class="bb-admin-settings-section">';
        echo '<h3>Customize the functions included in BricksBooster</h3>';
        echo '<p>Enable/Disable any of the following settings. Once disabled, the corresponding function will be completely disabled on both the backend and the frontend.</p>';
        
        echo '<form method="post" action="options.php">';
        wp_nonce_field('bricksbooster_options_nonce');
        settings_fields('bricksbooster_elements');
        do_settings_sections('bricksbooster_elements');
        
        $custom_elements_enabled = get_option('bricksbooster_custom_elements_enabled', true);
        echo '<div class="bb-admin-toggle">';
        echo '<label><input type="checkbox" name="bricksbooster_custom_elements_enabled" value="1" ' . checked($custom_elements_enabled, true, false) . '> Custom Elements</label>';
        echo '<p class="description">Enable additional elements in the Bricks builder</p>';
        echo '</div>';
        
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    private function render_builder_tweaks_tab() {
        echo '<div class="bb-admin-settings-section">';
        echo '<h3>Customize the functions included in BricksBooster</h3>';
        echo '<p>Enable/Disable any of the following settings. Once disabled, the corresponding function will be completely disabled on both the backend and the frontend.</p>';
        
        echo '<form method="post" action="options.php">';
        wp_nonce_field('bricksbooster_options_nonce');
        settings_fields('bricksbooster_builder_tweaks');
        do_settings_sections('bricksbooster_builder_tweaks');
        
        // Builder tweaks options
        $html_validator_enabled = get_option('bricksbooster_html_validator_enabled', true);
        $code_to_bricks_enabled = get_option('bricksbooster_code_to_bricks_enabled', true);
        
        echo '<div class="bb-admin-toggle">';
        echo '<label><input type="checkbox" name="bricksbooster_html_validator_enabled" value="1" ' . checked($html_validator_enabled, true, false) . '> HTML Visual Validator</label>';
        echo '<p class="description">Enable the HTML validator that checks your structure in the builder</p>';
        echo '</div>';
        
        echo '<div class="bb-admin-toggle">';
        echo '<label><input type="checkbox" name="bricksbooster_code_to_bricks_enabled" value="1" ' . checked($code_to_bricks_enabled, true, false) . '> Code to Bricks Converter</label>';
        echo '<p class="description">Enable the tool that converts HTML/CSS code to Bricks elements</p>';
        echo '</div>';
        
        submit_button();
        echo '</form>';
        echo '</div>';
    }
}

new BricksBooster_Options_Page();
