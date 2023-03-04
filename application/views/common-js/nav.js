$(document).ready(function () {

    /* for navigation drop down */
    var isOnDiv = false;
    $('.navchild').mouseenter(function () { isOnDiv = true; });
    $('.navchild').mouseleave(function () { isOnDiv = false; });

    $('.navchild').hover(function () {
        var el = $("body");
        if (isOnDiv === true) {
            $('.navchild').removeClass('active');
            el.removeClass('nav_show');
        }
        if ($(window).width() > 1025) {
            $(this).toggleClass("active");
            el.toggleClass("nav_show");
        }
        return false;
    });


    /* for mobile navigations */
    $('.link__mobilenav').click(function () {

        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $(this).siblings('.navigations > li .subnav').slideUp();
            return false;
        }
        $('.link__mobilenav').removeClass('active');
        $(this).addClass("active");
        if ($(window).width() < 1200) {
            $('.navigations > li .subnav').slideUp();
            $(this).siblings('.navigations > li .subnav').slideDown();
        }
        return;
    });


    /* for mobile toggle navigation */
    $('.navs_toggle').click(function () {

        /* if($('html').hasClass('cart-is-active')){
            $('.cart').removeClass('cart-is-active');
            $('html').removeClass("cart-is-active");
        } */
        if ($('html').hasClass('toggled-user')) {
            $('.dropdown__trigger-js').parent('.dropdown').removeClass("is-active");
            $("html").removeClass("toggled-user");
        }

        $(this).toggleClass("active");
        var el = $("body");
        if (el.hasClass('toggled_left')) el.removeClass("toggled_left");
        else el.addClass('toggled_left');
        return false;
    });

    $('body').click(function () {
        if ($('body').hasClass('toggled_left')) {
            $('.navs_toggle').removeClass("active");
            $('body').removeClass('toggled_left');
        }
    });

    $('.mobile__overlay').click(function () {
        if ($('body').hasClass('toggled_left')) {
            $('.navs_toggle').removeClass("active");
            $('body').removeClass('toggled_left');
        }
    });

    $('.navigation-wrapper,.section_primary').click(function (e) {
        e.stopPropagation();
    });
});