$(function () {
    $(".navbar-burger").click(function() {
        $(".navbar-burger").toggleClass("is-active");
        $(".navbar-menu").toggleClass("is-active");
    });
    $(".button.is-once").click(function (e) {
        $(this).addClass('is-loading');
    });
    $('.projects').on('click', '.project', function () {
        var $link = $(this).find('.js-project-link');
        if (!!$link && !!$link.attr('href')) {
            window.location = $link.attr('href');
        }
    });
});
