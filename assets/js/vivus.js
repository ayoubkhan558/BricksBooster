(function($) {
    'use strict';

    // Initialize Vivus animations
    function initVivusAnimations() {
        $('.vivus-target').each(function() {
            const $this = $(this);
            if ($this.data('vivus-initialized')) {
                return; // Already initialized
            }

            const config = $this.data('vivus-config');
            if (!config) return;

            // Mark as initialized
            $this.attr('data-vivus-initialized', 'true');

            // Initialize Vivus
            const options = {
                type: config.type || 'delayed',
                duration: config.duration || 200,
                animTimingFunction: Vivus.EASE_OUT,
                start: 'manual', // We'll handle start manually
                selfDestroy: config.selfDestroy || false
            };

            // Add delay for specific animation types
            if (['delayed', 'oneByOne'].includes(options.type)) {
                options.delay = config.delay || 10;
            }

            // Handle custom options
            if (options.type === 'custom' && config.customOptions) {
                try {
                    Object.assign(options, JSON.parse(config.customOptions));
                } catch (e) {
                    console.error('Error parsing custom Vivus options:', e);
                }
            }

            // Initialize Vivus
            const vivus = new Vivus($this.attr('id'), options);

            // Handle start trigger
            switch (config.start || 'auto') {
                case 'manual':
                    // Do nothing, will be started manually
                    break;

                case 'scroll':
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                vivus.play(parseFloat(config.direction || 0));
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.1 });
                    observer.observe(this);
                    break;

                case 'hover':
                    let hoverPlayed = false;
                    $this.on('mouseenter', function() {
                        if (!hoverPlayed) {
                            vivus.play(parseFloat(config.direction || 0));
                            hoverPlayed = true;
                        } else {
                            vivus.reset().play(1 - parseFloat(config.direction || 0));
                            hoverPlayed = false;
                        }
                    });
                    break;

                case 'click':
                    $this.css('cursor', 'pointer');
                    let clickPlayed = false;
                    $this.on('click', function() {
                        if (!clickPlayed) {
                            vivus.play(parseFloat(config.direction || 0));
                            clickPlayed = true;
                        } else {
                            vivus.reset().play(1 - parseFloat(config.direction || 0));
                            clickPlayed = false;
                        }
                    });
                    break;

                case 'auto':
                default:
                    vivus.play(parseFloat(config.direction || 0));
                    break;
            }

            // Store Vivus instance for potential manual control
            window[`${$this.attr('id')}_vivus`] = vivus;
        });
    }

    // Run on document ready
    $(document).ready(function() {
        if (typeof Vivus !== 'undefined') {
            initVivusAnimations();
        }
    });

    // Handle AJAX content loaded
    $(document).on('bricks/ajax/after_load', function() {
        if (typeof Vivus !== 'undefined') {
            initVivusAnimations();
        }
    });

})(jQuery);
