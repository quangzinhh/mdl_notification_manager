// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Main javascript module.
 *
 * @module     local_notification_manager/main
 * @copyright  2024 Developer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/str'
], function($, ajax, notification, str) {

    const USER_INPUT = '#nm-user-label';
    const USER_DATALIST = '#nm-user-suggestions';
    const USER_ID_INPUT = '#form-userid';

    const parseIdFromLabel = function(value) {
        if (!value) {
            return null;
        }
        const match = value.match(/^\s*(\d+)\s*-/);
        return match ? parseInt(match[1], 10) : null;
    };

    var initUserSelect = function() {
        const $input = $(USER_INPUT);
        const $userId = $(USER_ID_INPUT);

        $input.on('input', function() {
            const term = $(this).val().trim();
            const parsedId = parseIdFromLabel(term);
            const $datalist = $(USER_DATALIST);

            if (parsedId) {
                $userId.val(parsedId);
                // When an actual user is selected, submit form to load data
                document.getElementById('notification-manager-args').submit();
            } else {
                $userId.val('');
            }

            if (term.length < 1) {
                $datalist.empty();
                return;
            }

            ajax.call([{
                methodname: 'local_notification_manager_search_users',
                args: {q: term}
            }])[0].done(function(data) {
                if (!data || !Array.isArray(data.items)) {
                    $datalist.empty();
                    return;
                }
                $datalist.empty();
                data.items.forEach(function(user) {
                    const label = user.id + ' - ' + user.fullname + ' (' + user.email + ')';
                    $datalist.append('<option value="' + label + '"></option>');
                });
            }).fail(function() {
                $datalist.empty();
            });
        });

        $input.on('change', function() {
            const parsedId = parseIdFromLabel($(this).val());
            $userId.val(parsedId || '');
            if (parsedId) {
                document.getElementById('notification-manager-args').submit();
            }
        });
    };

    var initTable = function(userid) {
        var $checkAll = $('#nm-check-all');
        var $checkItems = $('.nm-check-item');
        var $btnSoftDelete = $('#nm-btn-soft-delete');
        var $btnHardDelete = $('#nm-btn-hard-delete');

        var updateDeleteButtons = function() {
            var selectedCount = $('.nm-check-item:checked').length;
            $btnSoftDelete.prop('disabled', selectedCount === 0);
            $btnHardDelete.prop('disabled', selectedCount === 0);
        };

        $checkAll.on('change', function() {
            $checkItems.prop('checked', $(this).prop('checked'));
            updateDeleteButtons();
        });

        $checkItems.on('change', function() {
            updateDeleteButtons();
        });

        var handleDeleteAction = function(actionType) {
            str.get_strings([
                {key: 'confirm', component: 'moodle'},
                {
                    key: actionType === 'soft' ? 'confirm_move_trash' : 'confirm_delete_permanently',
                    component: 'local_notification_manager'
                },
                {key: 'yes', component: 'moodle'},
                {key: 'no', component: 'moodle'}
            ]).done(function(s) {
                notification.confirm(
                    s[0], // Confirm
                    s[1], // Are you sure...
                    s[2], // Yes
                    s[3], // No
                    function() {
                        var selectedIds = [];
                        $('.nm-check-item:checked').each(function() {
                            selectedIds.push($(this).val());
                        });

                        ajax.call([{
                            methodname: 'local_notification_manager_delete_notifications',
                            args: {
                                userid: userid,
                                ids: selectedIds,
                                action: actionType
                            }
                        }])[0].done(function(response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                notification.alert('Error', response.message);
                            }
                        }).fail(function() {
                            notification.alert('Error', 'Failed to communicate with the server.');
                        });
                    }
                );
            });
        };

        $btnSoftDelete.on('click', function(e) {
            e.preventDefault();
            handleDeleteAction('soft');
        });
        $btnHardDelete.on('click', function(e) {
            e.preventDefault();
            handleDeleteAction('hard');
        });
    };

    return {
        init: function(config) {
            initUserSelect();
            if (config.userid > 0) {
                initTable(config.userid);
            }
        }
    };
});
