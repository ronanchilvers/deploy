/** global: App **/
var App = App || {};

App.Modal = {

    defaults: {
        modal: '#js-modal',
        selector: '.js-modal-trigger',
        close_selector: '.js-modal-close'
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
            $el = $(this);
            if (!$el.attr('disabled')) {
                App.Debug.log('Showing modal for trigger element:', $el);
                that._show($el);
            } else {
                App.Debug.log('Not showing modal for disabled trigger element: ', $el);
            }
        });
        $('body').on('click', this.options.close_selector, function (e) {
            e.preventDefault();
            App.Debug.log('Hiding modal');
            that._hide($(this));
        });
    },

    _show: function ($node) {
        var url = $node.attr('href'),
            $modal = $(this.options.modal);
        $modal.find('.modal-content').load(
            url,
            function () {
                $modal.addClass('is-active');
            }
        );
    },

    _hide: function () {
        $(this.options.modal).removeClass('is-active');
    }
}
