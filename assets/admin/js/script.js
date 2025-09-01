/**
 * We Catering Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        WeCateringAdmin.init();
    });

    // Main Admin Object
    var WeCateringAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initComponents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Add menu item button - use event delegation
            $(document).on('click', '#add-menu-item', this.showAddMenuItemModal);
            
            // Add organization button - use event delegation
            $(document).on('click', '#add-organization', this.showAddOrganizationModal);
            
            // Form submissions
            $('.we-catering-form').on('submit', this.handleFormSubmission);
            
            // Date change events
            $('#order_date, #menu_date').on('change', this.handleDateChange);
            
            // Report type change
            $('#report_type').on('change', this.handleReportTypeChange);
            
            // Organization selection
            $('#organization-select').on('change', this.handleOrganizationSelect);
            
            // Organization user management form submissions
            $('#add-user-form').on('submit', function(e) {
                e.preventDefault();
                WeCateringAdmin.addUserToOrganization();
            });

            $('#create-user-form').on('submit', function(e) {
                e.preventDefault();
                WeCateringAdmin.createUserForOrganization();
            });

            // Tab switching for organization user management
            $('.nav-tab-wrapper a').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                // Update active tab
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Show target content, hide others
                $('.we-catering-tab-content').hide();
                $(target).show();
            });
        },

        /**
         * Initialize components
         */
        initComponents: function() {
            this.initDatePickers();
            this.initTooltips();
            this.initConfirmations();
            this.initSelect2();
        },

        /**
         * Initialize date pickers
         */
        initDatePickers: function() {
            if ($.fn.datepicker) {
                $('input[type="date"]').each(function() {
                    $(this).datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true
                    });
                });
            }
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            if ($.fn.tooltip) {
                $('[data-tooltip]').tooltip({
                    position: { my: 'left+5 center', at: 'right center' }
                });
            }
        },

        /**
         * Initialize confirmation dialogs
         */
        initConfirmations: function() {
            $('.we-catering-confirm').on('click', function(e) {
                var message = $(this).data('confirm') || 'Are you sure you want to proceed?';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        /**
         * Initialize Select2 for enhanced dropdowns
         */
        initSelect2: function() {
            console.log('Initializing Select2...');
            
            // Check if Select2 is available
            if (typeof $.fn.select2 === 'undefined') {
                console.log('Select2 not available, using fallback');
                // Fallback: convert select to text input if Select2 is not available
                $('.we-catering-select2').each(function() {
                    var $select = $(this);
                    var isMultiple = $select.attr('multiple') !== undefined;
                    var placeholder = isMultiple ? 'Type items separated by commas...' : 'Type description...';
                    var $input = $('<input type="text" class="regular-text" name="' + $select.attr('name') + '" placeholder="' + placeholder + '" />');
                    $select.replaceWith($input);
                });
                return;
            }

            $('.we-catering-select2').each(function() {
                var $select = $(this);
                var isMultiple = $select.attr('multiple') !== undefined;
                var placeholder = isMultiple ? 'Select or add items...' : 'Select or type description...';
                
                console.log('Initializing Select2 for:', $select.attr('id'), 'Multiple:', isMultiple);
                
                $select.select2({
                    placeholder: placeholder,
                    allowClear: true,
                    tags: true,
                    multiple: isMultiple,
                    width: '100%',
                    createTag: function(params) {
                        console.log('Creating new tag:', params.term);
                        return {
                            id: params.term,
                            text: params.term,
                            newOption: true
                        };
                    },
                    templateResult: function(data) {
                        if (data.newOption) {
                            return $('<span class="custom-tag"><i class="dashicons dashicons-plus-alt2"></i> Add "' + data.text + '" as new item</span>');
                        }
                        return data.text;
                    },
                    templateSelection: function(data) {
                        if (data.newOption) {
                            return data.text;
                        }
                        return data.text;
                    }
                });
            });
        },

        /**
         * Show add menu item modal
         */
        showAddMenuItemModal: function(e) {
            e.preventDefault();
            console.log('Add menu item button clicked');
            
            // Check if modal already exists
            if ($('#add-menu-item-modal').length === 0) {
                console.log('Loading modal via AJAX...');
                // Load modal template via AJAX
                $.ajax({
                    url: We_Catering_Admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'we_catering_get_menu_item_modal',
                        nonce: We_Catering_Admin.nonce
                    },
                    success: function(response) {
                        console.log('AJAX Response:', response);
                        if (response.success) {
                            $('body').append(response.data.html);
                            $('#add-menu-item-modal').fadeIn();
                            WeCateringAdmin.bindModalEvents();
                            WeCateringAdmin.initSelect2();
                        } else {
                            alert('Failed to load menu item form: ' + (response.data ? response.data.message : 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.error('Response Text:', xhr.responseText);
                        alert('Network error. Please try again.');
                    }
                });
            } else {
                // Modal already exists, just show it
                $('#add-menu-item-modal').fadeIn();
                WeCateringAdmin.bindModalEvents();
            }
        },

        /**
         * Show add organization modal
         */
        showAddOrganizationModal: function(e) {
            e.preventDefault();
            
            // Create modal HTML
            var modalHtml = `
                <div id="add-organization-modal" class="we-catering-modal">
                    <div class="we-catering-modal-content">
                        <div class="we-catering-modal-header">
                            <h3>Add New Organization</h3>
                            <span class="we-catering-modal-close">&times;</span>
                        </div>
                        <div class="we-catering-modal-body">
                            <form id="add-organization-form">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="org_name">Organization Name</label>
                                        </th>
                                        <td>
                                            <input type="text" id="org_name" name="name" class="regular-text" required />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="org_description">Description</label>
                                        </th>
                                        <td>
                                            <textarea id="org_description" name="description" class="regular-text" rows="3"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="org_contact_person">Contact Person</label>
                                        </th>
                                        <td>
                                            <input type="text" id="org_contact_person" name="contact_person" class="regular-text" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="org_email">Email</label>
                                        </th>
                                        <td>
                                            <input type="email" id="org_email" name="email" class="regular-text" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="org_phone">Phone</label>
                                        </th>
                                        <td>
                                            <input type="tel" id="org_phone" name="phone" class="regular-text" />
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                        <div class="we-catering-modal-footer">
                            <button type="button" class="button button-secondary we-catering-modal-close-button">Cancel</button>
                            <button type="button" class="button button-primary" id="save-organization">Save Organization</button>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to body
            $('body').append(modalHtml);
            
            // Show modal
            $('#add-organization-modal').fadeIn();
            
            // Bind modal events
            WeCateringAdmin.bindModalEvents();
        },

        /**
         * Bind modal events
         */
        bindModalEvents: function() {
            // Close modal
            $('.we-catering-modal-close, .we-catering-modal-close-button').off('click').on('click', function() {
                $('.we-catering-modal').fadeOut(function() {
                    $(this).remove();
                });
            });
            
            // Save menu item
            $('#save-menu-item').off('click').on('click', function() {
                WeCateringAdmin.saveMenuItem();
            });
            
            // Save organization
            $('#save-organization').off('click').on('click', function() {
                WeCateringAdmin.saveOrganization();
            });

            // Delete organization
            $(document).on('click', '.delete-organization', function(e){
                e.preventDefault();
                var id = $(this).data('id');
                if (!id) return;
                $.post(We_Catering_Admin.ajax_url, {
                    action: 'we_catering_delete_organization',
                    nonce: We_Catering_Admin.nonce,
                    organization_id: id
                }).done(function(res){
                    if (res && res.success) {
                        location.reload();
                    } else {
                        alert((res && res.data && res.data.message) || 'Failed to delete.');
                    }
                }).fail(function(){ alert('Network error.'); });
            });

            // Delete menu item
            $(document).on('click', '.delete-menu-item', function(e){
                e.preventDefault();
                var id = $(this).data('id');
                if (!id) return;
                $.post(We_Catering_Admin.ajax_url, {
                    action: 'we_catering_delete_menu_item',
                    nonce: We_Catering_Admin.nonce,
                    menu_item_id: id
                }).done(function(res){
                    if (res && res.success) {
                        location.reload();
                    } else {
                        alert((res && res.data && res.data.message) || 'Failed to delete.');
                    }
                }).fail(function(){ alert('Network error.'); });
            });

            // Edit menu item
            $(document).on('click', '.edit-menu-item', function(e){
                e.preventDefault();
                var id = $(this).data('id');
                if (!id) return;
                WeCateringAdmin.editMenuItem(id);
            });

        // Save daily menu
        $('#save-daily-menu').on('click', function(e){
            e.preventDefault();
            var date = $('#menu_date').val();
            var ids = [];
            $('input[name="daily_menu_items[]"]:checked').each(function(){ ids.push($(this).val()); });
            $.post(We_Catering_Admin.ajax_url, {
                action: 'we_catering_set_daily_menu',
                nonce: We_Catering_Admin.nonce,
                date: date,
                menu_items: JSON.stringify(ids)
            }).done(function(res){
                if (res && res.success) {
                    alert('Daily menu saved.');
                } else {
                    alert((res && res.data && res.data.message) || 'Failed to save.');
                }
            }).fail(function(){ alert('Network error.'); });
        });




        // Remove user from organization
        $(document).on('click', '.remove-user', function(e) {
            e.preventDefault();
            var userId = $(this).data('user-id');
            var organizationId = $('#organization-select').val();
            WeCateringAdmin.removeUserFromOrganization(userId, organizationId);
        });

        // Update user role
        $(document).on('change', '.user-role-select', function() {
            var userId = $(this).data('user-id');
            var organizationId = $('#organization-select').val();
            var role = $(this).val();
            WeCateringAdmin.updateUserRole(userId, organizationId, role);
        });
        },

        /**
         * Edit menu item
         */
        editMenuItem: function(menuItemId) {
            console.log('Editing menu item:', menuItemId);
            
            // First load the modal
            if ($('#add-menu-item-modal').length === 0) {
                $.ajax({
                    url: We_Catering_Admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'we_catering_get_menu_item_modal',
                        nonce: We_Catering_Admin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('body').append(response.data.html);
                            $('#add-menu-item-modal').fadeIn();
                            WeCateringAdmin.bindModalEvents();
                            WeCateringAdmin.initSelect2();
                            // Now load the data
                            WeCateringAdmin.loadMenuItemData(menuItemId);
                        } else {
                            alert('Failed to load menu item form.');
                        }
                    },
                    error: function() {
                        alert('Network error. Please try again.');
                    }
                });
            } else {
                $('#add-menu-item-modal').fadeIn();
                WeCateringAdmin.bindModalEvents();
                WeCateringAdmin.initSelect2();
                // Load the data
                WeCateringAdmin.loadMenuItemData(menuItemId);
            }
        },

        /**
         * Load menu item data for editing
         */
        loadMenuItemData: function(menuItemId) {
            $.ajax({
                url: We_Catering_Admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'we_catering_get_menu_item_data',
                    nonce: We_Catering_Admin.nonce,
                    menu_item_id: menuItemId
                },
                success: function(response) {
                    if (response.success) {
                        var item = response.data.menu_item;
                        
                        // Populate form fields
                        $('#menu_item_id').val(item.id);
                        $('#menu_item_name').val(item.name);
                        $('#menu_item_description').val(item.description);
                        $('#menu_item_price').val(item.price);
                        $('#menu_item_category').val(item.category);
                        
                        // Handle items Select2
                        var $itemsSelect = $('#menu_item_items');
                        if ($itemsSelect.length > 0 && item.items && item.items.length > 0) {
                            // Clear existing options and add new ones
                            $itemsSelect.empty();
                            
                            // Add default option
                            $itemsSelect.append('<option value="">Select or add items...</option>');
                            
                            // Add predefined options with optgroups
                            var predefinedItems = {
                                'Proteins': ['Chicken', 'Beef', 'Fish', 'Lamb', 'Eggs', 'Tofu'],
                                'Grains & Starches': ['Rice', 'Bread', 'Pasta', 'Potatoes', 'Quinoa'],
                                'Vegetables': ['Mixed Vegetables', 'Salad', 'Broccoli', 'Carrots', 'Spinach'],
                                'Legumes': ['Lentils', 'Chickpeas', 'Beans'],
                                'Soups & Starters': ['Soup', 'Appetizer'],
                                'Desserts & Beverages': ['Dessert', 'Beverage', 'Coffee', 'Tea']
                            };
                            
                            Object.keys(predefinedItems).forEach(function(groupName) {
                                var optgroup = $('<optgroup label="' + groupName + '"></optgroup>');
                                predefinedItems[groupName].forEach(function(item) {
                                    optgroup.append('<option value="' + item + '">' + item + '</option>');
                                });
                                $itemsSelect.append(optgroup);
                            });
                            
                            // Set selected values
                            $itemsSelect.val(item.items).trigger('change');
                        }
                        
                        // Update modal title and button
                        $('.we-catering-modal-header h3').text('Edit Menu Item');
                        $('#save-menu-item').text('Update Menu Item');
                        
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Failed to load menu item data.');
                }
            });
        },

        /**
         * Save menu item via AJAX
         */
        saveMenuItem: function() {
            var $form = $('#add-menu-item-form');
            if ($form.length === 0) { return; }
            var formData = $form.serialize();
            
            $.ajax({
                url: We_Catering_Admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'we_catering_save_menu_item',
                    nonce: We_Catering_Admin.nonce,
                    form_data: formData
                },
                beforeSend: function() {
                    $('#save-menu-item').prop('disabled', true).text('Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $('.we-catering-modal').remove();
                        // Reload page to show updated item
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $('#save-menu-item').prop('disabled', false).text('Save Menu Item');
                }
            });
        },

        /**
         * Save organization via AJAX
         */
        saveOrganization: function() {
            var $form = $('#add-organization-form');
            if ($form.length === 0) { return; }
            var formData = $form.serialize();
            
            $.ajax({
                url: We_Catering_Admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'we_catering_save_organization',
                    nonce: We_Catering_Admin.nonce,
                    form_data: formData
                },
                beforeSend: function() {
                    $('#save-organization').prop('disabled', true).text('Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        alert('Organization saved successfully!');
                        $('.we-catering-modal').remove();
                        // Reload page to show new organization
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $('#save-organization').prop('disabled', false).text('Save Organization');
                }
            });
        },

        /**
         * Handle form submission
         */
        handleFormSubmission: function(e) {
            // Add loading state
            $(this).find('input[type="submit"]').prop('disabled', true).val('Processing...');
        },

        /**
         * Handle date change
         */
        handleDateChange: function() {
            // Trigger data refresh based on selected date
            var selectedDate = $(this).val();
            console.log('Date changed to:', selectedDate);
            // TODO: Implement data refresh logic
        },

        /**
         * Handle report type change
         */
        handleReportTypeChange: function() {
            var reportType = $(this).val();
            console.log('Report type changed to:', reportType);
            // TODO: Implement report type specific logic
        },

        /**
         * Handle organization selection change
         */
        handleOrganizationSelect: function() {
            console.log('Organization selection changed');
            
            var organizationId = $(this).val();
            if (organizationId) {
                WeCateringAdmin.loadOrganizationUsers(organizationId);
                WeCateringAdmin.loadAvailableUsers(organizationId);
                $('#add-user-org-id, #create-user-org-id').val(organizationId);
                $('#organization-users-list, #add-user-section').show();
            } else {
                $('#organization-users-list, #add-user-section').hide();
            }
        },

        /**
         * Load organization users
         */
        loadOrganizationUsers: function(organizationId) {
            $.ajax({
                url: We_Catering_Admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'we_catering_get_organization_users',
                    nonce: We_Catering_Admin.nonce,
                    organization_id: organizationId
                },
                beforeSend: function() {
                    $('#users-list-body').html('<tr><td colspan="5" class="we-catering-loading">Loading users...</td></tr>');
                },
                success: function(response) {
                    if (response.success) {
                        WeCateringAdmin.renderUsersList(response.data.users);
                    } else {
                        $('#users-list-body').html('<tr><td colspan="5">Failed to load users.</td></tr>');
                    }
                },
                error: function() {
                    $('#users-list-body').html('<tr><td colspan="5">Network error.</td></tr>');
                }
            });
        },

        /**
         * Load available users for adding to organization
         */
        loadAvailableUsers: function(organizationId) {
            $.ajax({
                url: We_Catering_Admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'we_catering_get_available_users',
                    nonce: We_Catering_Admin.nonce,
                    organization_id: organizationId
                },
                success: function(response) {
                    if (response.success) {
                        WeCateringAdmin.renderAvailableUsers(response.data.users);
                    }
                }
            });
        },

        /**
         * Render users list
         */
        renderUsersList: function(users) {
            var html = '';
            
            if (users.length === 0) {
                html = '<tr><td colspan="5">No users found in this organization.</td></tr>';
            } else {
                users.forEach(function(user) {
                    html += `
                        <tr>
                            <td>${user.display_name}</td>
                            <td>${user.user_email}</td>
                            <td>
                                <select class="user-role-select" data-user-id="${user.ID}">
                                    <option value="user" ${user.role === 'user' ? 'selected' : ''}>User</option>
                                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                </select>
                            </td>
                            <td><span class="we-catering-status ${user.status}">${user.status}</span></td>
                            <td>
                                <button class="button button-small remove-user" data-user-id="${user.ID}">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            
            $('#users-list-body').html(html);
        },

        /**
         * Render available users dropdown
         */
        renderAvailableUsers: function(users) {
            var $select = $('#user-select');
            $select.empty().append('<option value="">-- Select User --</option>');
            
            if (users.length === 0) {
                $select.append('<option value="" disabled>No available users</option>');
            } else {
                users.forEach(function(user) {
                    $select.append(`<option value="${user.ID}">${user.display_name} (${user.user_email})</option>`);
                });
            }
        },

        /**
         * Add user to organization
         */
        addUserToOrganization: function() {
            var $form = $('#add-user-form');
            var formData = $form.serialize();
            
            $.ajax({
                url: We_Catering_Admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'we_catering_add_user_to_organization',
                    nonce: We_Catering_Admin.nonce,
                    user_id: $('#user-select').val(),
                    organization_id: $('#add-user-org-id').val(),
                    role: $('#user-role').val()
                },
                beforeSend: function() {
                    $('#add-user-form button[type="submit"]').prop('disabled', true).text('Adding...');
                },
                success: function(response) {
                    if (response.success) {
                        alert('User added successfully!');
                        $form[0].reset();
                        // Reload users list
                        var organizationId = $('#organization-select').val();
                        WeCateringAdmin.loadOrganizationUsers(organizationId);
                        WeCateringAdmin.loadAvailableUsers(organizationId);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $('#add-user-form button[type="submit"]').prop('disabled', false).text('Add User');
                }
            });
        },

        /**
         * Create new user and add to organization
         */
        createUserForOrganization: function() {
            var $form = $('#create-user-form');
            
            $.ajax({
                url: We_Catering_Admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'we_catering_create_user_for_organization',
                    nonce: We_Catering_Admin.nonce,
                    username: $('#new-user-username').val(),
                    password: $('#new-user-password').val(),
                    first_name: $('#new-user-first-name').val(),
                    last_name: $('#new-user-last-name').val(),
                    email: $('#new-user-email').val(),
                    organization_id: $('#create-user-org-id').val(),
                    role: $('#new-user-role').val()
                },
                beforeSend: function() {
                    $('#create-user-form button[type="submit"]').prop('disabled', true).text('Creating...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $form[0].reset();
                        // Reload users list
                        var organizationId = $('#organization-select').val();
                        WeCateringAdmin.loadOrganizationUsers(organizationId);
                        WeCateringAdmin.loadAvailableUsers(organizationId);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $('#create-user-form button[type="submit"]').prop('disabled', false).text('Create User & Add to Organization');
                }
            });
        },

        /**
         * Remove user from organization
         */
        removeUserFromOrganization: function(userId, organizationId) {
            if (!confirm('Are you sure you want to remove this user from the organization?')) {
                return;
            }

            $.ajax({
                url: We_Catering_Admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'we_catering_remove_user_from_organization',
                    nonce: We_Catering_Admin.nonce,
                    user_id: userId,
                    organization_id: organizationId
                },
                success: function(response) {
                    if (response.success) {
                        alert('User removed successfully!');
                        // Reload users list
                        WeCateringAdmin.loadOrganizationUsers(organizationId);
                        WeCateringAdmin.loadAvailableUsers(organizationId);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        },

        /**
         * Update user role
         */
        updateUserRole: function(userId, organizationId, role) {
            $.ajax({
                url: We_Catering_Admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'we_catering_update_user_role',
                    nonce: We_Catering_Admin.nonce,
                    user_id: userId,
                    organization_id: organizationId,
                    role: role
                },
                success: function(response) {
                    if (!response.success) {
                        alert('Failed to update user role. Please try again.');
                        // Reload to reset the select value
                        WeCateringAdmin.loadOrganizationUsers(organizationId);
                    }
                },
                error: function() {
                    alert('Network error. Please try again.');
                    // Reload to reset the select value
                    WeCateringAdmin.loadOrganizationUsers(organizationId);
                }
            });
        },

        /**
         * Test order creation
         */
        testOrderCreation: function() {
            var $btn = $('#test-order-creation').prop('disabled', true).text('Testing...');
            var $result = $('#test-result');
            
            $.post(We_Catering_Admin.ajax_url, {
                action: 'we_catering_test_order_creation',
                nonce: We_Catering_Admin.nonce
            })
            .done(function(res) {
                if (res && res.success) {
                    $result.html('<div style="color: green; font-weight: bold;">✓ ' + res.data.message + '</div>').show();
                } else {
                    $result.html('<div style="color: red; font-weight: bold;">✗ ' + (res.data ? res.data.message : 'Test failed') + '</div>').show();
                }
            })
            .fail(function() {
                $result.html('<div style="color: red; font-weight: bold;">✗ Network error during test</div>').show();
            })
            .always(function() {
                $btn.prop('disabled', false).text('Test Order Creation');
            });
        }
    };

    // Bind test order creation button
    $(document).on('click', '#test-order-creation', function() {
        WeCateringAdmin.testOrderCreation();
    });

})(jQuery);
