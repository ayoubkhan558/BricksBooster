<?php
/**
 * BricksBooster Admin Templates Tab
 */

class BricksBooster_Templates_Tab {
    public function render() {
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
}
