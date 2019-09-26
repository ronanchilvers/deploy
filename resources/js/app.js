$(function () {
    $(".navbar-burger").click(function() {
        $(".navbar-burger").toggleClass("is-active");
        $(".navbar-menu").toggleClass("is-active");
    });
    $('.has-dropdown').on('click', '.navbar-link', function () {
        $(this).closest('.has-dropdown').toggleClass('is-active');
    });
    if (0<$('.notification').length) {
        setTimeout(function () {
            $('.notification').slideUp(function () {
                $(this).remove();
            });
        }, 3000);
    }
    $(".button.is-once").click(function (e) {
        $(this).addClass('is-loading');
    });
    $('.modal-trigger').on('click', function (e) {
        e.preventDefault();
        $modal = $($(this).data('modal'));
        $modal.addClass('is-active');
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
    $('.output').on('click', '.has-detail', function () {
        $(this).toggleClass('is-active');
        $(this).next('.detail').toggleClass('is-active');
    });

    App.Favourites.init();
    App.Modal.init();
});
