(function($) {
    'use strict';

    // Track if ripplet is already initialized
    window.bricksBoosterRippletInitialized = false;

    /**
     * Initialize Ripplet effects
     */
    function initRipplet() {
        if (window.bricksBoosterRippletInitialized) {
            return;
        }

        // Check if Ripplet.js is loaded
        if (typeof ripplet === 'undefined') {
            console.warn('Ripplet.js is not loaded');
            return;
        }

        // Initialize all elements with data-ripplet attribute
        $('[data-ripplet]').each(function() {
            const $element = $(this);
            
            // Skip if already initialized
            if ($element.data('ripplet-initialized')) {
                return;
            }

            // Get settings from data attribute or use defaults
            const settings = $element.data('ripplet-settings') || {};
            
            // Mark as initialized
            $element.data('ripplet-initialized', true);
            
            // Set up ripple effect
            const eventType = settings.onHover ? 'mouseenter' : 'mousedown';
            
            $element.on(eventType, function(e) {
                // Skip if disabled on mobile
                if (settings.disableOnMobile && window.innerWidth <= 767) {
                    return;
                }
                
                // Get the target element (in case of nested elements)
                let target = e.target;
                while (target !== this && !$(target).is('[data-ripplet]')) {
                    target = target.parentNode;
                    if (!target) return;
                }
                
                // Get position relative to the target
                const rect = target.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // Create ripple
                ripplet({
                    x: x,
                    y: y,
                    color: settings.color || 'rgba(255, 255, 255, 0.5)',
                    opacity: settings.opacity || 0.5,
                    duration: settings.duration || 500,
                    type: settings.type || 'bounded',
                    center: settings.center || false,
                    radius: settings.radius || 0,
                    element: target
                });
                
                // Prevent text selection on rapid clicks
                if (eventType === 'mousedown') {
                    e.preventDefault();
                }
            });
            
            // Prevent text selection on rapid clicks
            if (eventType === 'mousedown') {
                $element.on('selectstart', function(e) {
                    if ($(e.target).is('input, textarea, select, [contenteditable]')) {
                        return true;
                    }
                    return false;
                });
            }
        });
        
        window.bricksBoosterRippletInitialized = true;
    }

    // Initialize on document ready
    $(document).ready(function() {
        initRipplet();
    });

    // Handle AJAX content loaded
    $(document).on('bricks/ajax/after_load', function() {
        initRipplet();
    });

    // Handle dynamic content added to the page
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            let shouldInit = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    shouldInit = true;
                }
            });
            
            if (shouldInit) {
                initRipplet();
            }
        });
        
        // Start observing the document with the configured parameters
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

})(jQuery);
