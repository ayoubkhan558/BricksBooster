(function($) {
    'use strict';

    // Initialize canvas nest when document is ready
    $(document).ready(function() {
        initCanvasNest();
    });

    // Re-initialize when AJAX content is loaded
    $(document).on('bricks/ajax/after_load', function() {
        initCanvasNest();
    });

    // Initialize canvas nest effect
    function initCanvasNest() {
        // Check if canvas-nest.js is loaded
        if (typeof CanvasNest === 'undefined') {
            console.warn('CanvasNest.js is not loaded');
            return;
        }

        // Find all canvas nest containers
        $('.bricksbooster-canvas-nest-container').each(function() {
            var $container = $(this);
            var config = $container.data('canvas-nest-config');
            var canvasId = 'canvas-nest-' + Math.random().toString(36).substr(2, 9);
            
            // Skip if already initialized
            if ($container.find('#' + canvasId).length > 0) {
                return;
            }

            // Create canvas element
            var $canvas = $('<canvas>', {
                id: canvasId,
                class: 'bricksbooster-canvas-nest',
                style: 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: ' + (config.zIndex || -1) + ';'
            });

            // Add canvas to container
            $container.prepend($canvas);

            // Parse config
            try {
                var parsedConfig = typeof config === 'string' ? JSON.parse(config) : config;
                
                // Initialize canvas-nest
                new CanvasNest(document.getElementById(canvasId), {
                    color: parsedConfig.color || '128,128,128',
                    opacity: parsedConfig.opacity || 0.7,
                    count: parsedConfig.count || 150,
                    zIndex: parsedConfig.zIndex || -1,
                    pointColor: parsedConfig.pointColor || '128,128,128',
                    pointOpacity: parsedConfig.pointOpacity || 0.7,
                    pointRadius: parsedConfig.pointRadius || 1,
                    lineWidth: parsedConfig.lineWidth || 0.5,
                    lineDistance: parsedConfig.lineDistance || 150,
                    follow: parsedConfig.follow !== 'false',
                    mobile: parsedConfig.mobile === 'true',
                });
            } catch (error) {
                console.error('Error initializing Canvas Nest:', error);
            }
        });
    }

    // Handle dynamic content
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            var shouldInit = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    shouldInit = true;
                }
            });
            
            if (shouldInit) {
                setTimeout(initCanvasNest, 100); // Small delay to ensure DOM is ready
            }
        });
        
        // Start observing the document
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

})(jQuery);
