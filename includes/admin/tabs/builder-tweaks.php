<?php
/**
 * BricksBooster Admin Builder Tweaks Tab
 */

class BricksBooster_Builder_Tweaks_Tab {
    public function render() {
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
