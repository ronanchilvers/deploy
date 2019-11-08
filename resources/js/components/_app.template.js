var App = App || {};

App.Template = {
    render: function (html, data) {
        $.each(data, function (k,v) {
            var token = '{{' + k + '}}';
            html = html.replace(token, v);
        });

        return html;
    },
}
