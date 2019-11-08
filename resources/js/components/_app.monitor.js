/** global: App **/
var App = App || {};

App.Monitor = {

    defaults: {
        project: null,
        number: null,
        interval: 2000,

        tpl_details: '<div class="details" id="details-{{id}}">{{content}}</div>',
        tpl_summary: '<div class="summary has-events is-type-{{type}}"><p class="duration">{{duration}}s</p><p data-events="events-{{id}}">{{label}}</p></div>',
        tpl_events: '<div class="events" id="events-{{id}}">{{content}}</div>',
        tpl_event: '<div class="event">{{content}}</div>'
    },
    options: {},

    timer: null,

    init: function (options) {
        this.options = $.extend({}, this.defaults, options);
        this._bindUI();
        this.start();
    },
    start: function () {
        var that = this;
        this.timer = setInterval(function () {
            that._update();
        }, this.options.interval);
    },
    stop: function () {
        clearInterval(this.timer);
    },
    _bindUI: function () {
    },
    _update: function () {
        var url = '/api/project/' +
            this.options.project +
            '/events/' +
            this.options.number;
        var that = this;
        $.get(url, function(data) {
        if ('ok' != data.status) {
                that.stop();
                return;
            }
            that._displayActions(data.data.events);
        });
    },
    _displayActions: function (events) {
        var that = this;
        $.each(events, function (label, action) {
            var id = '#details-' + action.id;
            if (0 < $(id).length) {
                return;
            }
            console.log('creating action for id ' + id);
            that._createAction(label, action);
        });
    },
    _createAction: function (label, action) {
        var that = this;
        var html = '';
        var events = '';
        var summary = App.Template.render(
            this.options.tpl_summary,
            {
                type: action.type,
                duration: (0 == action.times.duration) ? '&lt;1' : action.times.duration,
                id: action.id,
                label: label
            }
        );
        $.each(action.events, function (idx, event) {
            events = events + App.Template.render(
                that.options.tpl_event,
                {
                    content: event
                }
            );
        });
        events = App.Template.render(
            this.options.tpl_events,
            {
                id: action.id,
                content: events
            }
        );
        html = App.Template.render(
            this.options.tpl_details,
            {
                id: action.id,
                content: summary + events
            }
        );
        $('#log-output').append(html);
    }
}
