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
    $(".button.is-once").click(function () {
        $(this).addClass('is-loading');
    });
    $('.confirm').on('click', function (e) {
        if (!confirm('Are you sure?')) {
            $(this).removeClass('is-loading');
            e.preventDefault();
        }
    });
    $('.modal-trigger').on('click', function (e) {
        e.preventDefault();
        var $modal = $($(this).data('modal'));
        $modal.addClass('is-active');
    });
    $('.tabs').on('click', '.tab', function (e) {
        e.preventDefault();
        var $pane = $($(this).data('target'));
        if (0 < $pane.length) {
            $('.js-tabs li').removeClass('is-active');
            $(this).closest('li').addClass('is-active');
            $('.tab-pane').removeClass('is-active');
            $pane.addClass('is-active');
        }
    });
    $('.output').on('click', '.has-events', function () {
        $(this).toggleClass('is-active');
        $(this).next('.events').toggleClass('is-active');
    });

    /** global: App **/
    App.Favourites.init();
    App.Modal.init();
});
