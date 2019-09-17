$(function () {
    $(".navbar-burger").click(function() {
        $(".navbar-burger").toggleClass("is-active");
        $(".navbar-menu").toggleClass("is-active");
    });
    $(".button.is-once").click(function (e) {
        $(this).addClass('is-loading');
    });
});
