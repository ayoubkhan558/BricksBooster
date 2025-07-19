<?php
class BricksBooster_Element_Tweaks_2 {
    public function __construct() {
        add_action('bricks:builder:ready', [$this, 'init']);
        // Only initialize if enabled in settings
        if (get_option('bricksbooster_element_tweaker_animation_2_enabled', true)) {
            
        }
    }

    public function init() {
        
    }
} 