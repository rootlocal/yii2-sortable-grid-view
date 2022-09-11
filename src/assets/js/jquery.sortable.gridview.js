;(function ($, window, document, undefined) {
    let pluginName = 'sortable_grid_view';
    let $this, csrfToken;
    let action, delay, items, handle, axis, cursor, opacity, placeholder, cancel, tolerance, zIndex;

    /**
     * Default Plugin config
     *
     * @type {{cursor: string, cancel: string, delay: number, action: string, handle: string, placeholder: string, axis: string, opacity: boolean, items: string, tolerance: string, zIndex: number}}
     */
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
        handle: '.sortable-column-btn-sort',
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
        tolerance = this.options.tolerance;
        zIndex = this.options.zIndex;
        this.init();
    }

    /**
     * INIT function
     */
    Plugin.prototype.init = function () {
        /**
         * Обработчик события при клике на кнопку up в гриде
         */
        $('.sortable-column-btn-up', $this).bind('click', function () {
            let owner = $(this).parents('tr');
            let grid = $(this).parents('tbody > tr');
            let json = JSON.stringify({'id': owner.data('key'), action: 'up'});

            // send request server
            _sendPostRequest(json, function (xhr) {
                grid.each(function () {
                    let target = $(this).prev();
                    let copy_owner = owner.clone(true);
                    let copy_target = target.clone(true);
                    target.replaceWith(copy_owner);
                    owner.replaceWith(copy_target);
                });
            });
        });

        /**
         * Обработчик события при клике на кнопку down в гриде
         */
        $('.sortable-column-btn-down', $this).bind('click', function () {
            let owner = $(this).parents('tr');
            let grid = $(this).parents('tbody > tr');
            let json = JSON.stringify({'id': owner.data('key'), action: 'down'});

            // send request server
            _sendPostRequest(json, function (xhr) {
                grid.each(function () {
                    let target = $(this).next();
                    let copy_owner = owner.clone(true);
                    let copy_target = target.clone(true);
                    target.replaceWith(copy_owner);
                    owner.replaceWith(copy_target);
                });
            });
        });

        _sortable();
    };

    /**
     * Callback helper for sortable
     *
     * @param e
     * @param ui
     * @returns {*}
     * @private
     */
    this._sortableHelper = function (e, ui) {
        ui.children().each(function () {
            $(this).width($(this).width());
        });
        return ui;
    };

    /**
     * Отправляет запрос на сервер
     *
     * @param json Object Данные которые нужно отправить в формате JSON
     * @param callback XMLHttpRequest Callback функция в случае успешного выполнения запроса
     * @private
     */
    this._sendPostRequest = function (json, callback = function (xhr) {
    }) {
        let xhr = new XMLHttpRequest();
        let url = encodeURI(action);

        xhr.open('POST', url, true);
        xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xhr.setRequestHeader('X-CSRF-Token', csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(json);

        xhr.ontimeout = function () {
            console.log('Connection timeout');
        }

        xhr.onload = function () {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                // callback function
                callback(xhr);
            } else if (xhr.status === 400) {
                throw Error('Error request server status: ' + xhr.status);
            } else {
                throw Error('Unknown Error');
            }
        }

        xhr.onreadystatechange = function () {
            if (xhr.readyState !== XMLHttpRequest.DONE) return; // DONE
            if (xhr.status !== 200) {
            }
        };

    }

    /**
     * JqueryUI Sortable
     * See documentation [sortable](https://api.jqueryui.com/sortable/)
     *
     * @private
     */
    this._sortable = function () {
        let grid = $('tbody', $this);
        let initialIndex = [];

        $('tr', grid).each(function () {
            initialIndex.push($(this).data('key'));
        });

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
            helper: _sortableHelper,
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

                // Send Items
                _sendPostRequest(JSON.stringify({'items': items}));

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