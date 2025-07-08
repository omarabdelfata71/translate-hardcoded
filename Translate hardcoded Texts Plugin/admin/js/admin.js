(function($) {
    'use strict';

    // Initialize admin functionality
    $(document).ready(function() {
        const form = $('.wpml-hardcoded-translator-add-form form');

        // Form validation
        form.on('submit', function(e) {
            const originalText = $('#original_text').val().trim();
            const context = $('#context').val().trim();
            const domain = $('#domain').val().trim();

            if (!originalText || !context || !domain) {
                e.preventDefault();
                alert(wpmlHardcodedTranslator.messages.fillAllFields || 'Please fill all required fields.');
                return false;
            }

            // Check if text already exists
            const existingTexts = $('.wpml-hardcoded-translator-list tbody tr td:first-child').map(function() {
                return $(this).text().trim();
            }).get();

            if (existingTexts.includes(originalText)) {
                e.preventDefault();
                alert(wpmlHardcodedTranslator.messages.textExists || 'This text already exists in the list.');
                return false;
            }
        });

        // Clear form fields
        $('.wpml-hardcoded-translator-add-form .clear-form').on('click', function(e) {
            e.preventDefault();
            form[0].reset();
        });

        // Initialize tooltips if any
        if ($.fn.tooltip) {
            $('[data-tooltip]').tooltip();
        }

        // Handle string deletion using event delegation
        $(document).on('click', '.delete-string', function(e) {
            e.preventDefault();
            if (!confirm(wpmlHardcodedTranslator.messages.confirmDelete || 'Are you sure you want to delete this text?')) {
                return;
            }

            const row = $(this).closest('tr');
            const textId = $(this).data('id');

            $.ajax({
                url: wpmlHardcodedTranslator.ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_hardcoded_text',
                    id: textId,
                    nonce: $(this).data('nonce')
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(400, function() {
                            $(this).remove();
                            if ($('.wpml-hardcoded-translator-list tbody tr').length === 0) {
                                $('.wpml-hardcoded-translator-list tbody').append('<tr><td colspan="5">No texts found.</td></tr>');
                            }
                        });
                    } else {
                        alert(response.data.message || 'Error deleting text');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Server error occurred. Please try again.');
                }
            });
        });

        // Handle text status toggle if implemented
        $('.toggle-status').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const textId = button.data('id');
            const currentStatus = button.data('status');

            $.ajax({
                url: wpmlHardcodedTranslator.ajaxurl,
                type: 'POST',
                data: {
                    action: 'toggle_text_status',
                    id: textId,
                    status: currentStatus,
                    nonce: wpmlHardcodedTranslator.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                        button.data('status', newStatus);
                        button.text(newStatus === 'active' ? 'Deactivate' : 'Activate');
                        button.closest('tr').find('.status-column').text(newStatus);
                    } else {
                        alert(response.data.message || 'Error updating status');
                    }
                },
                error: function() {
                    alert('Server error occurred');
                }
            });
        });
    });

})(jQuery);