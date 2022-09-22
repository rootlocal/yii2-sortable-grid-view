;(function ($) {
    let pluginName = 'sortable_grid_view';
    let $this;
    let action, items, handle,
        axis, cursor, opacity,
        placeholder, cancel, tolerance,
        zIndex, sortValueSelector;

    let defaults = {
        action: 'sort',
        sortValueSelector: '.sort_order',
        axis: 'y',
        cursor: 'move',
        opacity: false,
        items: 'tr',
        handle: '.sortable-column-btn-sort',
        placeholder: 'sortable-column-empty',
        cancel: 'disabled',
        tolerance: 'intersect',
        zIndex: 1000
    };

    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        action = this.options.action;
        items = this.options.items;
        handle = this.options.handle;
        placeholder = this.options.placeholder;
        cancel = this.options.cancel;
        tolerance = this.options.tolerance;
        zIndex = this.options.zIndex;
        sortValueSelector = this.options.sortValueSelector;
        this.init();
    }

    Plugin.prototype.init = function () {
        $('.sortable-column-btn-up', $this).bind('click', function () {
            let clicked = $(this);
            let json = JSON.stringify({
                action: 'up',
                id: clicked.parents('tr').data('key'),
            });

            _sendServerRequest(json, function () {
                _replaceTableCol(clicked, 'up');
            });
        });

        $('.sortable-column-btn-down', $this).bind('click', function () {
            let clicked = $(this);
            let json = JSON.stringify({
                action: 'down',
                id: clicked.parents('tr').data('key'),
            });

            _sendServerRequest(json, function () {
                _replaceTableCol(clicked, 'down');
            });
        });

        _sortable();
    };

    this._replaceTableCol = function (sort_element, sort_action = 'up') {
        let owner = sort_element.parents('tr');
        sort_element.parents('tbody > tr').each(function () {
            let target;
            if (sort_action === 'down') {
                target = $(this).next();
                owner.detach().insertAfter(target);
            } else {
                target = $(this).prev();
                owner.detach().insertBefore(target);
            }
        });
    };

    this._sortableHelper = function (e, ui) {

        ui.children().each(function () {
            $(this).width($(this).width());
        });

        return ui;
    };

    this._sendServerRequest = function (json, sendPostRequestCallback = function () {
        return false;
    }) {

        $.ajax({
            url: encodeURI(action),
            method: 'POST',
            dataType: 'json',
            async: true,
            cache: false,
            data: json,

            beforeSend: function (request) {
                request.setRequestHeader('X-CSRF-Token', yii.getCsrfToken());
                request.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
                request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            },

            success: function (response) {
                if (response.status === 'success' && !$.isEmptyObject(response.result)) {
                    let json = JSON.parse(response.result);
                    let result = [];

                    for (let key in json) {
                        result[json[key].id] = json[key].sort_id;
                    }

                    $('tr', $('tbody', $this)).each(function () {
                        let key = $(this).data('key');

                        if (result.indexOf(key)) {
                            let newValue = result[key];
                            $(this).find(sortValueSelector).text(newValue);
                        }

                    });

                    return true;
                }

                location.reload();
            },

            complete: function (jqXHR) {
                if (jqXHR.responseJSON.status === 'success') {
                    sendPostRequestCallback(jqXHR);
                }
            },

            statusCode: {
                400: function (e) {
                    console.log(e.responseJSON.name);
                }
            }
        });
    };

    this._sortable = function () {
        let grid = $('tbody', $this);
        let initialIndex = [];

        $('tr', grid).each(function () {
            initialIndex.push($(this).data('key'));
        });

        grid.sortable({
            items: items,
            handle: handle,
            axis: axis,
            cursor: cursor,
            opacity: opacity,
            placeholder: placeholder,
            cancel: cancel,
            tolerance: tolerance,
            zIndex: zIndex,
            helper: _sortableHelper,
            update: function () {
                let items = {};

                $(this).children().each(function (index) {
                    let currentKey = $(this).data('key');

                    if (initialIndex[index] !== currentKey) {
                        items[currentKey] = initialIndex[index];
                        initialIndex[index] = currentKey;
                    }

                });

                _sendServerRequest(JSON.stringify({'action': 'sortable', 'items': items}));
            }
        }).disableSelection();
    };

    $.fn[pluginName] = function (options) {
        $this = $(this);
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery);