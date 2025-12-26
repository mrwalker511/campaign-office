/**
 * Design Studio JavaScript
 */
jQuery(document).ready(function($) {
    // Tab switching
    $('.cp-tab').click(function() {
        var tab = $(this).data('tab');
        $('.cp-tab').removeClass('active');
        $(this).addClass('active');
        $('.cp-tab-content').removeClass('active');
        $('[data-tab-content="' + tab + '"]').addClass('active');
    });

    // Device switcher
    $('#cp-device-desktop').click(function() {
        $('.cp-canvas-viewport').attr('data-device', 'desktop');
        $('#cp-device-desktop, #cp-device-tablet, #cp-device-mobile').removeClass('button-primary');
        $(this).addClass('button-primary');
    });
    $('#cp-device-tablet').click(function() {
        $('.cp-canvas-viewport').attr('data-device', 'tablet');
        $('#cp-device-desktop, #cp-device-tablet, #cp-device-mobile').removeClass('button-primary');
        $(this).addClass('button-primary');
    });
    $('#cp-device-mobile').click(function() {
        $('.cp-canvas-viewport').attr('data-device', 'mobile');
        $('#cp-device-desktop, #cp-device-tablet, #cp-device-mobile').removeClass('button-primary');
        $(this).addClass('button-primary');
    });

    // Drag and drop
    var draggedComponent = null;

    $('.cp-component-card').on('dragstart', function(e) {
        draggedComponent = $(this).data('component');
        $(this).css('opacity', '0.5');
    });

    $('.cp-component-card').on('dragend', function(e) {
        $(this).css('opacity', '1');
    });

    $('#cp-canvas').on('dragover', function(e) {
        e.preventDefault();
        $(this).css('background', '#f0f8ff');
    });

    $('#cp-canvas').on('dragleave', function(e) {
        $(this).css('background', '#fff');
    });

    $('#cp-canvas').on('drop', function(e) {
        e.preventDefault();
        $(this).css('background', '#fff');

        if (draggedComponent) {
            $('.cp-canvas-empty-state').remove();

            var componentHTML = '<div class="cp-dropped-component" data-component="' + draggedComponent + '">' +
                '<div class="cp-component-controls">' +
                '<button class="cp-control-btn cp-edit-component" title="Edit"><span class="dashicons dashicons-edit"></span></button>' +
                '<button class="cp-control-btn cp-delete-component" title="Delete"><span class="dashicons dashicons-trash"></span></button>' +
                '</div>' +
                '<div class="cp-component-content">' +
                '<h3>🎯 ' + draggedComponent.replace('-', ' ').toUpperCase() + '</h3>' +
                '<p>Component preview will appear here</p>' +
                '</div>' +
                '</div>';

            $(this).append(componentHTML);
            draggedComponent = null;
        }
    });

    // Component controls
    $(document).on('click', '.cp-delete-component', function() {
        if (confirm(cpDesignStudio.i18n.delete_confirm)) {
            $(this).closest('.cp-dropped-component').fadeOut(300, function() {
                $(this).remove();
                if ($('#cp-canvas .cp-dropped-component').length === 0) {
                    $('#cp-canvas').html('<div class="cp-canvas-empty-state"><div class="cp-empty-icon"><span class="dashicons dashicons-layout"></span></div><h3>' + cpDesignStudio.i18n.start_building + '</h3><p>' + cpDesignStudio.i18n.drag_desc + '</p></div>');
                }
            });
        }
    });

    // Clear canvas
    $('#cp-clear-canvas').click(function() {
        if (confirm(cpDesignStudio.i18n.clear_confirm)) {
            $('#cp-canvas').html('<div class="cp-canvas-empty-state"><div class="cp-empty-icon"><span class="dashicons dashicons-layout"></span></div><h3>' + cpDesignStudio.i18n.start_building + '</h3><p>' + cpDesignStudio.i18n.drag_desc + '</p></div>');
        }
    });

    // Save design
    $('#cp-save-design-btn').click(function() {
        var components = [];
        $('.cp-dropped-component').each(function() {
            components.push({
                type: $(this).data('component'),
                html: $(this).html()
            });
        });

        var $btn = $(this);
        var originalHTML = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> ' + cpDesignStudio.i18n.saving);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cp_save_design',
                post_id: $('#cp-page-selector').val(),
                design_data: JSON.stringify(components),
                custom_css: $('#cp-custom-css').val(),
                _wpnonce: cpDesignStudio.nonces.save_design
            },
            success: function(response) {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> ' + cpDesignStudio.i18n.saved);
                setTimeout(function() {
                    $btn.html(originalHTML);
                }, 2000);
            },
            error: function() {
                $btn.prop('disabled', false).html(originalHTML);
                alert(cpDesignStudio.i18n.error_saving);
            }
        });
    });

    // Page selector
    $('#cp-page-selector').change(function() {
        var postId = $(this).val();
        if (postId) {
            window.location.href = '?page=cp-design-studio&post_id=' + postId;
        }
    });

    // Load existing design if post_id is set
    var currentPostId = $('#cp-page-selector').val();
    if (currentPostId) {
        loadExistingDesign(currentPostId);
    }

    // Function to load existing design
    function loadExistingDesign(postId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cp_load_design',
                post_id: postId,
                _wpnonce: cpDesignStudio.nonces.load_design
            },
            success: function(response) {
                if (response.success && response.data.design_data && response.data.design_data.length > 0) {
                    // Clear canvas
                    $('.cp-canvas-empty-state').remove();

                    // Add each component to canvas
                    response.data.design_data.forEach(function(component) {
                        var componentHTML = '<div class="cp-dropped-component" data-component="' + component.type + '">' +
                            component.html +
                            '</div>';
                        $('#cp-canvas').append(componentHTML);
                    });

                    // Load custom CSS if exists
                    if (response.data.custom_css) {
                        $('#cp-custom-css').val(response.data.custom_css);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading design:', error);
            }
        });
    }

    // Template usage logic
    $('.cp-use-template').click(function() {
        var templateKey = $(this).data('template');
        var postId = $('#cp-template-page-selector').val();
        var $btn = $(this);
        var originalText = $btn.text();

        if (!postId) {
            alert(cpDesignStudio.i18n.select_page_first);
            return;
        }

        $btn.prop('disabled', true).text(cpDesignStudio.i18n.applying);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cp_apply_template',
                template: templateKey,
                post_id: postId,
                _wpnonce: cpDesignStudio.nonces.apply_template
            },
            success: function(response) {
                $btn.prop('disabled', false).text(originalText);
                if (response.success) {
                    alert(cpDesignStudio.i18n.template_applied);
                    window.location.href = '?page=cp-design-studio&post_id=' + postId;
                } else {
                    alert(response.data.message || cpDesignStudio.i18n.error_applying);
                }
            },
            error: function() {
                $btn.prop('disabled', false).text(originalText);
                alert(cpDesignStudio.i18n.error_applying);
            }
        });
    });
});
