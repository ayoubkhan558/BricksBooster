jQuery(document).ready(function($) {
    // Tab switching functionality - delegate to document in case buttons are added dynamically
    $(document).on('click', '.bb-admin-nav-tab-wrapper .bb-admin-nav-tab', function(e) {
        e.preventDefault();
        
        // Update active tab
        $('.bb-admin-nav-tab-wrapper .bb-admin-nav-tab').removeClass('bb-admin-nav-tab-active');
        $(this).addClass('bb-admin-nav-tab-active');
        
        // Show corresponding tab content
        const tabId = $(this).data('tab');
        $('.bb-admin-tab-pane').removeClass('active');
        $('#' + tabId).addClass('active');
    });
    
    // Form submission handling
    $('.bb-admin-options form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitButton = $form.find('.button-primary');
        const originalText = $submitButton.val();
        
        // Disable button during submission
        $submitButton.prop('disabled', true).val('Saving...');
        
        // Get form data including nonce
        const formData = $form.serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=bricksbooster_save_options',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $form.prepend('<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>');
                    
                    // Remove notice after 3 seconds
                    setTimeout(function() {
                        $('.notice', $form).fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    // Show error message
                    $form.prepend('<div class="notice notice-error is-dismissible"><p>' + (response.data || 'Error saving settings') + '</p></div>');
                }
            },
            error: function(xhr) {
                $form.prepend('<div class="notice notice-error is-dismissible"><p>Error saving settings. Please try again.</p></div>');
            },
            complete: function() {
                $submitButton.prop('disabled', false).val(originalText);
            }
        });
    });
});
