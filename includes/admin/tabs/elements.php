<?php
/**
 * BricksBooster Admin Elements Tab
 */

class BricksBooster_Elements_Tab {
    public function render() {
        ?>
        <div class="bb-admin-settings-section">
            <h3>Custom Elements Settings</h3>
            <p>Enable/Disable custom elements functionality.</p>
            <div class="bb-admin-toggles-grid">
                <?php
                $elements = [
                    'nestable_list' => [
                        'label' => 'Nestable List',
                        'tooltip' => 'Enable nestable list element'
                    ],
                    'nestable_link' => [
                        'label' => 'Nestable Link',
                        'tooltip' => 'Enable nestable link element'
                    ],
                    'simple_list' => [
                        'label' => 'Simple List',
                        'tooltip' => 'Enable simple list element'
                    ],
                ];

                foreach ($elements as $key => $element) {
                    $enabled = get_option('bricksbooster_' . $key . '_enabled', 1);
                    ?>
                    <div class="bb-admin-toggle">
                        <label>
                            <input type="checkbox" name="bricksbooster_<?php echo esc_attr($key); ?>_enabled" value="1" <?php checked($enabled, 1); ?>>
                            <span class="toggle-switch"></span>
                            <span class="toggle-label"><?php echo esc_html($element['label']); ?></span>
                            <span class="tooltip">
                                <span class="tooltip-icon">?</span>
                                <span class="tooltip-text"><?php echo esc_html($element['tooltip']); ?></span>
                            </span>
                        </label>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
}
