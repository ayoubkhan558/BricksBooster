(function($) {
    'use strict';

    // Initialize particles when document is ready
    $(document).ready(function() {
        initParticles();
    });

    // Initialize particles when AJAX content is loaded
    $(document).on('bricks/ajax/after_load', function() {
        initParticles();
    });

    // Initialize particles effect
    function initParticles() {
        // Check if particles.js is loaded
        if (typeof particlesJS === 'undefined') {
            console.warn('particles.js is not loaded');
            return;
        }

        // Find all particle containers
        $('.bricks-particles-container').each(function() {
            const $container = $(this);
            const $particles = $container.find('.bricks-particles');
            const config = $container.data('particles-config');
            const id = $particles.attr('id');

            // Skip if already initialized
            if ($particles.hasClass('particles-js')) {
                return;
            }

            // Initialize particles
            if (id && config) {
                try {
                    // Parse config if it's a string
                    const parsedConfig = typeof config === 'string' ? JSON.parse(config) : config;
                    
                    // Initialize particles.js
                    particlesJS(id, parsedConfig);
                    
                    // Mark as initialized
                    $particles.addClass('particles-js');
                    
                    // Handle window resize
                    let resizeTimer;
                    $(window).on('resize', function() {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(function() {
                            if (window.pJSDom && window.pJSDom.length > 0) {
                                window.pJSDom.forEach(function(p) {
                                    p.pJS.fn.vendors.densityAutoParticles();
                                });
                            }
                        }, 250);
                    });
                } catch (error) {
                    console.error('Error initializing particles:', error);
                }
            }
        });
    }

    // Handle dynamic content
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            let shouldInit = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    shouldInit = true;
                }
            });
            
            if (shouldInit) {
                initParticles();
            }
        });
        
        // Start observing the document
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

})(jQuery);
