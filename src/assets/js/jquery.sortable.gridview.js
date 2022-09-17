;(function ($) {
    let pluginName = 'sortable_grid_view';
    let $this;
    let action, items, handle, axis, cursor, opacity, placeholder, cancel, tolerance, zIndex, sortValueSelector;

    let defaults = {
        // Адрес url экшена котроллера на который отправляется запрос
        action: 'sort',
        sortValueSelector: '.sort_order',

        // Позволяет задать ось, по которой можно перетаскивать элемент. Возможные значения:
        // 'x' (элемент можно будет перетаскивать только по горизонтали) и
        // 'y' (элемент можно будет перетаскивать только по вертикали).
        axis: 'y',

        // Позволяет задать вид курсора мыши во время перетаскивания.
        cursor: 'move',

        // Устанавливает прозрачность элемента помощника (элемент, который отображается во время перетаскивания).
        opacity: false,

        // Указывает какие элементы в группе могут быть отсортированы.
        // Значение  '> *' - все элементы в выбранной группе
        items: 'tr',

        // Указывает элемент, при щелчке на который начнется перетаскивание.
        handle: '.sortable-column-btn-sort',

        // класс, который будет назначен элементу, созданному для заполнения позиции,
        // занимаемой сортируемым элементом до его перемещения в новое расположение
        placeholder: 'sortable-column-empty',

        // заблокировать элемент, нужно добавить к нему класс disabled
        cancel: 'disabled',

        // intersect | pointer
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

            _sendPostRequest(json, function () {
                _replace(clicked, 'up');
            });
        });

        $('.sortable-column-btn-down', $this).bind('click', function () {
            let clicked = $(this);
            let json = JSON.stringify({
                action: 'down',
                id: clicked.parents('tr').data('key'),
            });

            _sendPostRequest(json, function () {
                _replace(clicked, 'down');
            });
        });

        _sortable();
    };

    this._replace = function (sort_element, sort_action = 'up') {
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

    this._sendPostRequest = function (json, sendPostRequestCallback = function () {
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
                }
            },

            complete: function (jqXHR, exception) {
                if (exception === 'success') {
                    sendPostRequestCallback(jqXHR);
                }
            },

            statusCode: {
                400: function (e) {
                    console.log(e.responseJSON.name);
                }
            },

            error: function (jqXHR, exception) {
                if (jqXHR.status === 0) {
                    console.log('Not connect. Verify Network.');
                } else if (jqXHR.status === 404) {
                    console.log('Requested page not found (404).');
                } else if (jqXHR.status === 500) {
                    console.log('Internal Server Error (500).');
                } else if (exception === 'parsererror') {
                    console.log('Requested JSON parse failed.');
                } else if (exception === 'timeout') {
                    console.log('Time out error.');
                } else if (exception === 'abort') {
                    console.log('Ajax request aborted.');
                } else {
                    //console.log('Uncaught Error. ' + jqXHR.responseText);
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

                _sendPostRequest(JSON.stringify({'action': 'sortable', 'items': items}));
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