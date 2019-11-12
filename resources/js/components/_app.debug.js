/** global: App **/
var App = App || {};

App.Debug = {

    defaults: {
        enabled: false,
    },
    options: {},

    init: function (options) {
        this.options = $.extend({}, this.defaults, options);
    },
    log: function () {
        if (this.options.enabled && !!console.log) {
            console.log.apply(null, arguments);
        }
    }
}
