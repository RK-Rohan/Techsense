(function ($) {
    'use strict';

    function key(settings) {
        if (!window.APP || !APP.USER_ID || !settings.nTable.id ||
            $(settings.nTable).closest('form, .modal').length ||
            /\/(create|edit)(\/|$)/.test(window.location.pathname)) return null;
        var columns = settings.aoColumns.map(function (column) {
            return [column.sName || '', column.mData, column.bSortable];
        });
        return 'list_sort_v1:' + JSON.stringify([
            APP.BUSINESS_ID, APP.USER_ID, window.location.pathname, settings.nTable.id, columns
        ]);
    }

    function validOrder(order, settings) {
        return Array.isArray(order) && order.every(function (item) {
            return Array.isArray(item) && Number.isInteger(item[0]) && item[0] >= 0 &&
                item[0] < settings.aoColumns.length && settings.aoColumns[item[0]].bSortable &&
                (item[1] === 'asc' || item[1] === 'desc');
        });
    }

    // preInit runs after DataTables loads state and before the first data request.
    $(document).on('preInit.dt', function (event, settings) {
        var storageKey = key(settings);
        if (!storageKey || !settings.oFeatures.bSort) return;
        var order = null;
        try { order = JSON.parse(localStorage.getItem(storageKey)); } catch (e) {}
        if (validOrder(order, settings)) {
            settings.aaSorting = order;
        } else if (settings.oLoadedState) {
            // Older DataTables state was shared by all users of this browser.
            settings.aaSorting = $.extend(true, [], settings.oInit.order ||
                settings.oInit.aaSorting || $.fn.dataTable.defaults.aaSorting);
        }
        settings._userSortKey = storageKey;
    });

    $(document).on('order.dt', function (event, settings) {
        if (!settings._userSortKey || !validOrder(settings.aaSorting, settings)) return;
        try {
            localStorage.setItem(settings._userSortKey, JSON.stringify(settings.aaSorting));
        } catch (e) {
            // Sorting continues normally if browser storage is unavailable.
        }
    });
})(jQuery);
