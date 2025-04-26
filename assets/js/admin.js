/**
 * ClickUp Elementor Addon Admin JavaScript
 * Handles AJAX interactions for the ClickUp Elementor integration
 */
(function($) {
    'use strict';

    /**
     * Initialize once Elementor editor is loaded
     */
    $(window).on('elementor/init', function() {
        const elementorForms = elementor.modules.controls.Select2;
        if (!elementorForms) return;

        // Add loading indicators
        const addLoader = function($el) {
            $el.find('option:first').text(clickupElementor.i18n.loading);
        };

        $(document).on('click mousedown', '.elementor-control-section_clickup:not(.elementor-control-section_clickup_loaded)', function(event) {
            var tthis = $(this);
            tthis.addClass('elementor-control-section_clickup_loaded');
            var $formControl = tthis.closest('#elementor-controls');
            setTimeout(function() {
                const $workspaceSelect = $formControl.find('.clickup-elementor-workspace-select select');
                $workspaceSelect.trigger('change');
            }, 100);
        });

        // Handle workspace selection change
        $(document).on('change', '.clickup-elementor-workspace-select select', function() {
            const $this = $(this);
            const workspaceId = $this.val();
            const $formControl = $this.closest('#elementor-controls');
            const $spaceSelect = $formControl.find('.clickup-elementor-space-select select');
            const $listSelect = $formControl.find('.clickup-elementor-list-select select');
            const $statusSelect = $formControl.find('.clickup-elementor-status-select select');

            // Reset dependent selects
            $spaceSelect.html('<option value="">' + clickupElementor.i18n.selectSpace + '</option>');
            $listSelect.html('<option value="">' + clickupElementor.i18n.selectList + '</option>');
            $statusSelect.html('');
            
            if (!workspaceId) return;
            
            // Show loading indicator
            addLoader($spaceSelect.parent());
            
            // Fetch spaces for selected workspace
            $.ajax({
                url: clickupElementor.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'clickup_ele_get_spaces',
                    workspace_id: workspaceId,
                    nonce: clickupElementor.nonce
                },
                success: function(response) {
                    let options = '<option value="">' + clickupElementor.i18n.selectSpace + '</option>';
                    
                    if (response.success && response.data && response.data.length > 0) {
                        $.each(response.data, function(index, space) {
                            options += '<option value="' + space.id + '">' + space.name + '</option>';
                        });
                    }
                    
                    $spaceSelect.html(options);
                    
                    const currentModel = elementor.getPanelView().getCurrentPageView().getOption('editedElementView').getEditModel();
                    const settings_data = currentModel.get('settings').toJSON();
                    if(settings_data.clickup_space){
                        $spaceSelect.val(settings_data.clickup_space);
                        $spaceSelect.trigger('change');
                    }
                }
            });
        });

        // Handle space selection change
        $(document).on('change', '.clickup-elementor-space-select select', function() {
            const $this = $(this);
            const spaceId = $this.val();
            const $formControl = $this.closest('#elementor-controls');
            const $listSelect = $formControl.find('.clickup-elementor-list-select select');
            const $statusSelect = $formControl.find('.clickup-elementor-status-select select');

            // Reset dependent selects
            $listSelect.html('<option value="">' + clickupElementor.i18n.selectList + '</option>');
            $statusSelect.html('');

            // Show loading indicator
            addLoader($statusSelect.parent());
            
            if (!spaceId) return;
            
            // Show loading indicator
            addLoader($listSelect.parent());
            
            // Fetch lists for selected space
            $.ajax({
                url: clickupElementor.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'clickup_ele_get_lists',
                    space_id: spaceId,
                    nonce: clickupElementor.nonce
                },
                success: function(response) {
                    let options = '<option value="">' + clickupElementor.i18n.selectList + '</option>';
                    
                    if (response.success && response.data && response.data.length > 0) {
                        $.each(response.data, function(index, list) {
                            options += '<option value="' + list.value + '">' + list.label + '</option>';
                        });
                    }
                    
                    $listSelect.html(options);

                    let optionsStatus = '<option value="">' + clickupElementor.i18n.selectStatus + '</option>';
                    
                    if (response.success && response.data && response.data.length > 0) {
                        $.each(response.data, function(index, status) {
                            optionsStatus += '<option value="' + status.id + '">' + status.name + '</option>';
                        });
                    }
                    
                    $statusSelect.html(optionsStatus);

                    const currentModel = elementor.getPanelView().getCurrentPageView().getOption('editedElementView').getEditModel();
                    const settings_data = currentModel.get('settings').toJSON();
                    if(settings_data.clickup_list){
                        $listSelect.val(settings_data.clickup_list);
                        $listSelect.trigger('change');
                    }
                }
            });
        });
    });

    // Handle space selection change
    $(document).on('change', '.clickup-elementor-list-select select', function() {
        const $this = $(this);
        const listId = $this.val();
        const $formControl = $this.closest('#elementor-controls');
        const $statusSelect = $formControl.find('.clickup-elementor-status-select select');

        // Reset dependent selects
        $statusSelect.html('');

        if (!listId) return;
        
        // Fetch lists for selected space
        $.ajax({
            url: clickupElementor.ajaxUrl,
            type: 'POST',
            data: {
                action: 'clickup_ele_get_statuses',
                list_id: listId,
                nonce: clickupElementor.nonce
            },
            success: function(response) {
                let optionsStatus = '<option value="">' + clickupElementor.i18n.selectStatus + '</option>';
                
                if (response.success && response.data && response.data.length > 0) {
                    $.each(response.data, function(index, status) {
                        optionsStatus += '<option value="' + status.id + '">' + status.name + '</option>';
                    });
                }
                
                $statusSelect.html(optionsStatus);

                const currentModel = elementor.getPanelView().getCurrentPageView().getOption('editedElementView').getEditModel();
                const settings_data = currentModel.get('settings').toJSON();
                if(settings_data.clickup_status){
                    $statusSelect.val(settings_data.clickup_status);
                }
            }
        });
    });

})(jQuery); 
