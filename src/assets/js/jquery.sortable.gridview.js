;(function ($, window, document, undefined) {
    let pluginName = 'sortable_grid_view', id, $this, csrfToken, action,
        defaults = {
            action: 'sort',
        };

    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        csrfToken = yii.getCsrfToken();
        action = this.options.action;
        this.init();
    }

    Plugin.prototype.init = function () {
        _sortableGrid(action);
    };

    const fixHelper = function (e, ui) {

        ui.children().each(function () {
            $(this).width($(this).width());
        });

        return ui;
    };

    this._sortableGrid = function (action) {
        const grid = $('tbody', $this);
        const initialIndex = [];

        $('tr', grid).each(function () {
            initialIndex.push($(this).data('key'));
        });

        // https://api.jqueryui.com/sortable/
        grid.sortable({
            items: 'tr',
            axis: 'y',
            cursor: "move",
            opacity: 0.9,
            // This event is triggered when using connected lists, every connected list on drag start receives it.
            activate: function (event, ui) {
            },
            // This event is triggered when the user stopped sorting and the DOM position has changed.
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
                        throw Error('Error: ' + this.status);
                    }

                };

            },
            // The jQuery object representing the helper being sorted.
            helper: fixHelper,
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