<?php
/**
 * BricksBooster Admin Element Tweaks Tab
 */

class BricksBooster_Element_Tweaks_Tab {
    public function render() {
        $tweaks = [
            'animation_tweak' => 'Animation Tweak',
            'animation_aos_tweak' => 'AOS Animations',
            'lax' => 'Lax.js Animations',
            'scrollreveal' => 'ScrollReveal Animations',
            // Add more tweaks here as needed
        ];
        ?>
        <div class="bb-admin-settings-section">
            <h3>Element Tweaks Settings</h3>
            <p>Enable/Disable element enhancement features.</p>
            
            <div class="bb-admin-toggles-grid">
                <?php foreach ($tweaks as $tweak_key => $tweak_name) : ?>
                    <?php $tweak_enabled = get_option('bbooster_' . $tweak_key . '_enabled', 1); ?>
                    <div class="bb-admin-toggle">
                        <label>
                            <input type="checkbox" name="bbooster_<?php echo $tweak_key; ?>_enabled" value="1" <?php checked($tweak_enabled, 1); ?>>
                            <span class="toggle-switch"></span>
                            <span class="toggle-label"><?php echo $tweak_name; ?></span>
                            <span class="tooltip">
                                <span class="tooltip-icon">?</span>
                                <span class="tooltip-text"><?php echo $tweak_name; ?> - <?php echo $this->get_tweak_description($tweak_key); ?></span>
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private function get_tweak_description($tweak_key) {
        $descriptions = [
            'animation_tweak' => 'Adds custom text controls to Bricks Builder elements (sections, containers, blocks, and divs)'
            // Add more descriptions as needed
        ];

        return $descriptions[$tweak_key] ?? 'No description available.';
    }
}
