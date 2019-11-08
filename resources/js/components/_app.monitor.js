/** global: App **/
var App = App || {};

App.Monitor = {

    defaults: {
    },
    options: {},

    init: function (options) {
        this.options = $.extend({}, this.defaults, options);
        this.bindUI();
    },
    bindUI: function () {
    },
}
