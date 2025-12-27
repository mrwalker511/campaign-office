jQuery(document).ready(function ($) {
    var draggedComponent = null;
    var activeComponent = null;

    // Tab switching
    $('.cp-tab').click(function () {
        var tab = $(this).data('tab');
        $('.cp-tab').removeClass('active');
        $(this).addClass('active');
        $('.cp-tab-content').removeClass('active');
        $('[data-tab-content="' + tab + '"]').addClass('active');
    });

    // Device switcher
    $('.cp-canvas-header button[id^="cp-device"]').click(function () {
        var device = $(this).attr('id').replace('cp-device-', '');
        $('.cp-canvas-viewport').attr('data-device', device);
        $('.cp-canvas-header button[id^="cp-device"]').removeClass('button-primary');
        $(this).addClass('button-primary');
    });

    // Drag and drop initialization
    $('.cp-component-card').on('dragstart', function (e) {
        draggedComponent = $(this).data('component');
        $(this).css('opacity', '0.5');
    });

    $('.cp-component-card').on('dragend', function (e) {
        $(this).css('opacity', '1');
    });

    $('#cp-canvas').on('dragover', function (e) {
        e.preventDefault();
        $(this).css('background', '#f0f8ff');
    }).on('dragleave', function (e) {
        $(this).css('background', '#fff');
    }).on('drop', function (e) {
        e.preventDefault();
        $(this).css('background', '#fff');

        if (draggedComponent) {
            addComponentToCanvas(draggedComponent);
            draggedComponent = null;
        }
    });

    // Add component to canvas
    function addComponentToCanvas(type, variant, settings) {
        variant = variant || 'default';
        settings = settings || {};

        $('.cp-canvas-empty-state').remove();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cp_get_component_html',
                component_type: type,
                variant: variant,
                settings: settings,
                _wpnonce: cpDesignStudio.nonces.get_component
            },
            success: function (response) {
                if (response.success) {
                    var $el = $('<div class="cp-dropped-component" data-component="' + type + '" data-variant="' + variant + '">' +
                        '<div class="cp-component-controls">' +
                        '<button class="cp-control-btn cp-edit-component" title="Edit"><span class="dashicons dashicons-edit"></span></button>' +
                        '<button class="cp-control-btn cp-delete-component" title="Delete"><span class="dashicons dashicons-trash"></span></button>' +
                        '</div>' +
                        '<div class="cp-component-content-wrap">' + response.data.html + '</div>' +
                        '</div>');

                    $el.data('settings', settings);
                    $('#cp-canvas').append($el);

                    // Auto-select the newly added component
                    selectComponent($el);
                }
            }
        });
    }

    // Select component on canvas
    $(document).on('click', '.cp-dropped-component', function (e) {
        e.stopPropagation();
        selectComponent($(this));
    });

    function selectComponent($el) {
        activeComponent = $el;
        $('.cp-dropped-component').removeClass('active');
        $el.addClass('active');

        var type = $el.data('component');
        var variant = $el.data('variant');
        var settings = $el.data('settings') || {};

        $('#cp-selected-component-name').text(type.replace('-', ' ').toUpperCase());
        loadComponentProperties(type, variant, settings);
    }

    // Load and render property controls
    function loadComponentProperties(type, currentVariant, settings) {
        var $container = $('#cp-properties-content');
        $container.html('<span class="dashicons dashicons-update spin"></span> Loading properties...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cp_get_component_properties',
                component_type: type,
                _wpnonce: cpDesignStudio.nonces.get_component
            },
            success: function (response) {
                if (response.success) {
                    renderPropertiesPanel(response.data.variants, response.data.settings, currentVariant, settings);
                }
            }
        });
    }

    function renderPropertiesPanel(variants, schema, currentVariant, currentSettings) {
        var $container = $('#cp-properties-content');
        $container.empty();

        // Variant Selector
        if (variants && variants.length > 0) {
            $container.append('<h4 class="cp-property-group-title">Variants</h4>');
            var $vSelector = $('<div class="cp-variant-selector"></div>');
            variants.forEach(function (v) {
                var active = v === currentVariant ? 'active' : '';
                $vSelector.append('<button class="cp-variant-btn ' + active + '" data-variant="' + v + '">' + v.charAt(0).toUpperCase() + v.slice(1) + '</button>');
            });
            $container.append($vSelector);
        }

        // Settings Schema
        if (schema && Object.keys(schema).length > 0) {
            $container.append('<h4 class="cp-property-group-title">Settings</h4>');

            for (var key in schema) {
                var field = schema[key];
                var val = currentSettings[key] !== undefined ? currentSettings[key] : field.default;

                var $control = $('<div class="cp-property-control" data-key="' + key + '"></div>');
                $control.append('<label>' + field.label + '</label>');

                if (field.type === 'text') {
                    $control.append('<input type="text" class="cp-input cp-prop-input" value="' + val + '">');
                } else if (field.type === 'textarea') {
                    $control.append('<textarea class="cp-input cp-prop-input" rows="3">' + val + '</textarea>');
                } else if (field.type === 'color') {
                    $control.append('<input type="text" class="cp-prop-input color-picker" value="' + val + '">');
                } else if (field.type === 'select') {
                    var $select = $('<select class="cp-input cp-prop-input"></select>');
                    for (var optVal in field.options) {
                        var selected = optVal == val ? 'selected' : '';
                        $select.append('<option value="' + optVal + '" ' + selected + '>' + field.options[optVal] + '</option>');
                    }
                    $control.append($select);
                } else if (field.type === 'number') {
                    $control.append('<input type="number" class="cp-input cp-prop-input" value="' + val + '">');
                } else if (field.type === 'checkbox') {
                    var checked = val ? 'checked' : '';
                    $control.append('<input type="checkbox" class="cp-prop-input" ' + checked + '>');
                }

                $container.append($control);
            }
        }

        // Initialize color pickers
        $container.find('.color-picker').wpColorPicker({
            change: function (event, ui) {
                updateActiveComponent();
            }
        });
    }

    // Handle property changes
    $(document).on('input change', '.cp-prop-input', function () {
        if (!$(this).hasClass('wp-color-picker')) { // Color picker handled by its own 'change' callback
            updateActiveComponent();
        }
    });

    $(document).on('click', '.cp-variant-btn', function () {
        $('.cp-variant-btn').removeClass('active');
        $(this).addClass('active');
        updateActiveComponent();
    });

    function updateActiveComponent() {
        if (!activeComponent) return;

        var type = activeComponent.data('component');
        var variant = $('.cp-variant-btn.active').data('variant') || 'default';
        var settings = {};

        $('.cp-property-control').each(function () {
            var key = $(this).data('key');
            var $input = $(this).find('.cp-prop-input');
            var val;

            if ($input.attr('type') === 'checkbox') {
                val = $input.is(':checked');
            } else {
                val = $input.val();
            }
            settings[key] = val;
        });

        activeComponent.data('variant', variant);
        activeComponent.data('settings', settings);

        // Refresh HTML
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cp_get_component_html',
                component_type: type,
                variant: variant,
                settings: settings,
                _wpnonce: cpDesignStudio.nonces.get_component
            },
            success: function (response) {
                if (response.success) {
                    activeComponent.find('.cp-component-content-wrap').html(response.data.html);
                }
            }
        });
    }

    // Component controls (Delete)
    $(document).on('click', '.cp-delete-component', function (e) {
        e.stopPropagation();
        if (confirm(cpDesignStudio.i18n.delete_confirm)) {
            $(this).closest('.cp-dropped-component').fadeOut(300, function () {
                $(this).remove();
                if ($('#cp-canvas .cp-dropped-component').length === 0) {
                    $('#cp-canvas').html('<div class="cp-canvas-empty-state"><div class="cp-empty-icon"><span class="dashicons dashicons-layout"></span></div><h3>' + cpDesignStudio.i18n.start_building + '</h3><p>' + cpDesignStudio.i18n.drag_desc + '</p></div>');
                    $('#cp-properties-content').html('<p class="description">' + cpDesignStudio.i18n.drag_desc + '</p>');
                }
            });
        }
    });

    // Clear canvas
    $('#cp-clear-canvas').click(function () {
        if (confirm(cpDesignStudio.i18n.clear_confirm)) {
            $('#cp-canvas').html('<div class="cp-canvas-empty-state"><div class="cp-empty-icon"><span class="dashicons dashicons-layout"></span></div><h3>' + cpDesignStudio.i18n.start_building + '</h3><p>' + cpDesignStudio.i18n.drag_desc + '</p></div>');
            $('#cp-properties-content').html('<p class="description">' + cpDesignStudio.i18n.drag_desc + '</p>');
        }
    });

    // Save design
    $('#cp-save-design-btn').click(function () {
        var components = [];
        $('.cp-dropped-component').each(function () {
            components.push({
                type: $(this).data('component'),
                variant: $(this).data('variant'),
                settings: $(this).data('settings')
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
            success: function (response) {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> ' + cpDesignStudio.i18n.saved);
                setTimeout(function () {
                    $btn.html(originalHTML);
                }, 2000);
            },
            error: function () {
                $btn.prop('disabled', false).html(originalHTML);
                alert(cpDesignStudio.i18n.error_saving);
            }
        });
    });

    // Page selector
    $('#cp-page-selector').change(function () {
        var postId = $(this).val();
        if (postId) {
            window.location.href = '?page=cp-design-studio&post_id=' + postId;
        }
    });

    // Load existing design
    var currentPostId = $('#cp-page-selector').val();
    if (currentPostId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cp_load_design',
                post_id: currentPostId,
                _wpnonce: cpDesignStudio.nonces.load_design
            },
            success: function (response) {
                if (response.success && response.data.design_data) {
                    var items = JSON.parse(response.data.design_data);
                    items.forEach(function (item) {
                        addComponentToCanvas(item.type, item.variant, item.settings);
                    });
                    if (response.data.custom_css) {
                        $('#cp-custom-css').val(response.data.custom_css);
                    }
                }
            }
        });
    }

    // Template usage logic
    $('.cp-use-template').click(function () {
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
            success: function (response) {
                $btn.prop('disabled', false).text(originalText);
                if (response.success) {
                    alert(cpDesignStudio.i18n.template_applied);
                    window.location.href = '?page=cp-design-studio&post_id=' + postId;
                } else {
                    alert(response.data.message || cpDesignStudio.i18n.error_applying);
                }
            },
            error: function () {
                $btn.prop('disabled', false).text(originalText);
                alert(cpDesignStudio.i18n.error_applying);
            }
        });
    });
});
