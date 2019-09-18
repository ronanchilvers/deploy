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
    $('.tabs').on('click', '.tab', function (e) {
        e.preventDefault();
        $pane = $($(this).data('target'));
        if (0 < $pane.length) {
            $('.js-tabs li').removeClass('is-active');
            $(this).closest('li').addClass('is-active');
            $('.tab-pane').removeClass('is-active');
            $pane.addClass('is-active');
        }
    });
});
