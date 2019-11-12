/** global: App **/
var App = App || {};

App.Favourites = {

    defaults: {
        container: '.projects',
        selector: '.is-favourite',
        on: 'is-favourite-on',
        off: 'is-favourite-off',
    },
    options: {},

    init: function (options) {
        this.options = $.extend({}, this.defaults, options);
        this.bindUI();
    },
    bindUI: function () {
        var that = this;
        $(this.options.selector).on('click', function (e) {
            e.preventDefault();
            that._send(this);
        });
    },
    unBindUI: function () {
    },

    _send: function (node) {
        var url = $(node).attr('href');
        $.post(url)
            .done(function (data) {
                var aClass = 'is-favourite-off';
                var iClass = 'far';
                if (data.data.selected) {
                    aClass = 'is-favourite-on';
                    iClass = 'fas';
                }
                $(node)
                    .removeClass('is-favourite-on, is-favourite-off')
                    .addClass(aClass);
                $(node)
                    .find('svg')
                    .removeClass('far, fas')
                    .addClass(iClass);
            })
            .fail(function () {
                alert('Unable to mark item as a favourite');
            });
    }
}
