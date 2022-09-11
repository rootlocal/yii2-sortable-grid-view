;(function ($, window, document, undefined) {
    let pluginName = 'sortable_grid_view';
    let $this, csrfToken;
    let action, delay, items, handle, axis, cursor, opacity, placeholder, cancel,
        tolerance, zIndex;

    let defaults = {
        // Адрес url экшена котроллера на который отправляется запрос
        action: 'sort',
        // Позволяет задать ось, по которой можно перетаскивать элемент. Возможные значения: 'x'
        // (элемент можно будет перетаскивать только по горизонтали) и 'y' (элемент можно будет перетаскивать только по вертикали).
        axis: 'y',
        // Позволяет задать вид курсора мыши во время перетаскивания.
        cursor: 'move',
        // Устанавливает прозрачность элемента помощника (элемент, который отображается во время перетаскивания).
        opacity: false, // 0.5
        // Устанавливает задержку в миллисекундах перед тем, как элемент начнет перетаскиваться
        // (может использоваться для предотвращения перетаскивания при случайном щелчке на элементе).
        delay: 0,
        // Указывает какие элементы в группе могут быть отсортированы.
        // Значение  '> *' - все элементы в выбранной группе
        items: 'tr',
        // Указывает элемент, при щелчке на который начнется перетаскивание.
        handle: '.sortable-column-btn',
        // класс, который будет назначен элементу, созданному для заполнения позиции, занимаемой сортируемым элементом до его перемещения в новое расположение
        placeholder: 'sortable-column-empty',
        // заблокировать элемент, нужно добавить к нему класс disabled
        cancel: 'disabled',
        tolerance: 'pointer', // intersect
        zIndex: 1000
    };

    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        csrfToken = yii.getCsrfToken();

        action = this.options.action;
        delay = this.options.delay;
        items = this.options.items;
        handle = this.options.handle;
        placeholder = this.options.placeholder;
        cancel = this.options.cancel;
        tolerance  = this.options.tolerance;
        zIndex = this.options.zIndex;

        this.init();
    }

    Plugin.prototype.init = function () {
        _sortable();
    };

    this._helper = function (e, ui) {

        ui.children().each(function () {
            $(this).width($(this).width());
        });

        return ui;
    };

    this._sortable = function () {
        const grid = $('tbody', $this);
        const initialIndex = [];

        $('tr', grid).each(function () {
            initialIndex.push($(this).data('key'));
        });

        // https://api.jqueryui.com/sortable/
        grid.sortable({
            items: items,
            delay: delay,
            handle: handle,
            axis: axis,
            cursor: cursor,
            opacity: opacity,
            placeholder: placeholder,
            cancel: cancel,
            tolerance: tolerance,
            zIndex: zIndex,
            helper: _helper,
            // This event is triggered when using connected lists, every connected list on drag start receives it.
            activate: function (event, ui) {
            },
            // Происходит при каждом перемещении мыши в процессе сортировки
            sort: function (event, ui) {
            },
            // Происходит при изменении позиции элемента в результате сортировки, выполненной пользователем
            change: function (event, ui) {
            },
            // Происходит при перемещении элемента в данный сортируемый элемент-контейнер из другого связанного сортируемого элемента-контейнера
            receive: function (event, ui) {
            },
            // Происходит при перемещении элемента из данного сортируемого элемента-контейнера в другой связанный сортируемый элемент-контейнер
            remove: function (event, ui) {
            },
            // This event is triggered when the user stopped sorting and the DOM position has changed.
            // Происходит при завершении перемещения элемента пользователем при условии, что порядок элементов был изменен
            update: function () {
                let items = {};
                let i = 0;

                $('tr', grid).each(function () {
                    let currentKey = $(this).data('key');

                    if (initialIndex[i] !== currentKey) {
                        items[currentKey] = initialIndex[i];
                        initialIndex[i] = currentKey;
                    }

                    ++i;
                });

                let xhr = new XMLHttpRequest();
                let url = encodeURI(action);
                xhr.open('POST', url, true);
                xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                let json = JSON.stringify({'items': items});
                xhr.send(json);

                xhr.onreadystatechange = function () {

                    if (this.readyState !== 4) {
                        return false;
                    }

                    if (this.status !== 200) {
                        throw Error('Error request server status: ' + this.status);
                    }

                    /*
                    if (this.response && JSON.parse(this.response).status !== 'success') {
                    }
                    */

                };

            }
        }).disableSelection();
    };

    $.fn[pluginName] = function (options) {
        $this = $(this);
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                    new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);