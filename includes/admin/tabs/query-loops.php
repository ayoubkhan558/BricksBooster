<?php
/**
 * BricksBooster Admin Query Loops Tab
 */

class BricksBooster_Query_Loops_Tab {
    public function render() {
        $comments_query_enabled = get_option('bricksbooster_comments_query_enabled', 1);
        $woocommerce_orders_query_enabled = get_option('bricksbooster_woocommerce_orders_query_enabled', 1);
        ?>
        <div class="bb-admin-settings-section">
            <h3>Query Loops Settings</h3>
            <p>Enable/Disable query loop functionality in Bricks.</p>
            
            <div class="bb-admin-toggles-grid">
                <div class="bb-admin-toggle">
                    <label>
                        <input type="checkbox" name="bricksbooster_comments_query_enabled" value="1" <?php checked($comments_query_enabled, 1); ?>>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">Comments Query</span>
                        <span class="tooltip">
                            <span class="tooltip-icon">?</span>
                            <span class="tooltip-text">Enable comments query functionality in Bricks builder</span>
                        </span>
                    </label>
                </div>
                <div class="bb-admin-toggle">
                    <label>
                        <input type="checkbox" name="bricksbooster_woocommerce_orders_query_enabled" value="1" <?php checked($woocommerce_orders_query_enabled, 1); ?>>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">WooCommerce Orders Query</span>
                        <span class="tooltip">
                            <span class="tooltip-icon">?</span>
                            <span class="tooltip-text">Enable WooCommerce orders query functionality in Bricks builder</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }
}
