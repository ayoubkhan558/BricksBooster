<?php
/**
 * BricksBooster Admin Tags Tab
 */

class BricksBooster_Tags_Tab {
    public function render() {
        $post_tags_enabled = get_option('bbooster_post_tags_enabled', 1);
        $media_tags_enabled = get_option('bbooster_media_tags_enabled', 1);
        $math_tags_enabled = get_option('bbooster_math_tags_enabled', 1);
        ?>
        <div class="bb-admin-settings-section">
            <h3>Custom Tags Settings</h3>
            <p>Enable/Disable custom HTML/CSS tags functionality.</p>

            <div class="bb-admin-toggles-grid">
                <div class="bb-admin-toggle">
                    <label>
                        <input type="checkbox" name="bbooster_post_tags_enabled" value="1" <?php checked($post_tags_enabled, 1); ?>>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">Post Tags</span>
                        <span class="tooltip">
                            <span class="tooltip-icon">?</span>
                            <span class="tooltip-text">Enable additional HTML/CSS tags in the Bricks builder</span>
                        </span>
                    </label>
                    <hr style="margin: 15px 0;"/>
                    <div>
                        <h3>Post Tags List</h3>
                        <ul column="2">
                            <li>✓ Post Reading Time</li>
                            <li>✓ Post Word Count</li>
                            <li>✓ Post Character Count</li>
                            <li>✓ Post Excerpt Word Count</li>
                            <li>✓ Post First Image URL</li>
                        </ul>
                    </div>
                </div>
                <div class="bb-admin-toggle">
                    <label>
                        <input type="checkbox" name="bbooster_media_tags_enabled" value="1" <?php checked($media_tags_enabled, 1); ?>>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">Media Tags</span>
                        <span class="tooltip">
                            <span class="tooltip-icon">?</span>
                            <span class="tooltip-text">Media Library Images tags in the Bricks builder</span>
                        </span>
                    </label>
                    <hr style="margin: 15px 0;"/>
                    <div>
                        <h3>Media Tags List</h3>
                        <ul column="2">
                            <li>✓ Media Library Images</li>
                            <li>✓ Media Library Videos</li>
                            <li>✓ Media Library Audio</li>
                            <li>✓ Media Library Documents</li>
                            <li>✓ Media Library PDF</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="bb-admin-toggles-grid">
                <div class="bb-admin-toggle">
                    <label>
                        <input type="checkbox" name="bbooster_math_tags_enabled" value="1" <?php checked($math_tags_enabled, 1); ?>>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">Math Tags</span>
                        <span class="tooltip">
                            <span class="tooltip-icon">?</span>
                            <span class="tooltip-text">Math tags in the Bricks builder</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }
}
