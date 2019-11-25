/** global: App **/
var App = App || {};

App.Monitor = {

    defaults: {
        project: null,
        number: null,
        interval: 1000,

        tpl_details: '<div class="details" id="details-{{id}}">{{content}}</div>',
        tpl_summary: '<div class="summary has-events is-type-{{type}}"><p class="duration">{{duration}}s</p><p data-events="events-{{id}}">{{label}}</p></div>',
        tpl_events: '<div class="events" id="events-{{id}}">{{content}}</div>',
        tpl_event: '<div class="event">{{content}}</div>'
    },
    options: {},

    timer: null,
    configuration_updated: false,

    init: function (options) {
        this.options = $.extend({}, this.defaults, options);
        this.start();
    },
    start: function () {
        App.Debug.log('Starting project monitor');
        var that = this;
        this.timer = setInterval(function () {
            that._update();
        }, this.options.interval);
        $('#output-loader').show();
    },
    stop: function (deployment) {
        App.Debug.log('Stopping project monitor');
        clearInterval(this.timer);
        $('#output-loader').hide();
        App.Debug.log('Updating project last status: ', deployment.status);
        $('.project .status')
            .removeClass('status--deployed status--failed')
            .addClass('status--' + deployment.status);
        $('.project .status .status__label').html('Just now');
    },
    _update: function () {
        var url = '/api/project/' +
            this.options.project +
            '/events/' +
            this.options.number;
        var that = this;
        App.Debug.log('Querying API for data: ', url);
        $.get(url, function(data) {
            if ('ok' != data.status || 'deployed' == data.data.deployment.status || 'failed' == data.data.deployment.status) {
                that.stop(data.data.deployment);
            }
            that._updateStatus(data.data.deployment)
            that._updateConfiguration(data.data.deployment)
            that._displayActions(data.data.events);
        });
    },
    _updateStatus: function (deployment) {
        App.Debug.log('Updating project and deployment status: ', deployment.status);
        $('.tab-is-pending,.tab-is-deploying')
            .removeClass('tab-is-pending tab-is-deploying')
            .addClass('tab-is-' + deployment.status);
        $('.deployment')
            .removeClass('is-pending is-deploying is-deployed is-failed')
            .addClass('is-' + deployment.status);
        if ('deployed' == deployment.status || 'failed' == deployment.status) {
            App.Debug.log('Enabling deployment UI actions');
            $('.button[disabled="disabled"]')
                .removeAttr('disabled')
                .removeClass('tooltip');
            var statusClass = 'far fa-' + (('deployed' == deployment.status) ? 'thumbs-up' : 'thumbs-down');
            $('.fa-spinner')
                .addClass(statusClass)
                .removeClass('fas fa-spinner');
        }
    },
    _updateConfiguration: function (deployment) {
        if ('' !== deployment.configuration && !this.configuration_updated) {
            App.Debug.log('Updating deployment configuration: ', deployment.configuration);
            $('#js-configuration pre').html(deployment.configuration);
            this.configuration_updated = true;
        }
    },
    _displayActions: function (events) {
        var that = this;
        $.each(events, function (label, action) {
            var action_id = '#details-' + action.id;
            var events_selector = action_id + ' .events .event';
            if (0 === $(action_id).length) {
                that._createAction(label, action);
            }
            if ($(events_selector).length < action.events.length) {
                that._updateAction(label, action);
            }
        });
    },
    _createAction: function (label, action) {
        App.Debug.log('Creating action HTML: ', label, action)
        var that = this;
        var html = '';
        var events = '';
        var summary = App.Template.render(
            this.options.tpl_summary,
            {
                type: action.type,
                duration: (0 === action.times.duration) ? '&lt;1' : action.times.duration,
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
    },
    _updateAction: function (label, action) {
        App.Debug.log('Updating action HTML: ', label, action)
        var that = this;
        var action_id = '#details-' + action.id;
        var events = '';
        $.each(action.events, function (idx, event) {
            events = events + App.Template.render(
                that.options.tpl_event,
                {
                    content: event
                }
            );
        });
        $(action_id + " .duration").html(((0 === action.times.duration) ? '&lt;1' : action.times.duration) + 's');
        $(action_id + " .events").html(events);
    }
}
