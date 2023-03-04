$(document).ready(function () {
    $('.gallery').modaal({
        type: 'image'
    });
    $('.social-toggle').on('click', function (event) {
        $(this).next().toggleClass('open-menu');
    });
    $("body").mouseup(function (e) {
        if (1 > $(event.target).parents('.social-toggle').length && $('.social-toggle').next().hasClass('open-menu')) {
            $('.social-toggle').next().toggleClass('open-menu');
        }
    });

    function DropDown(el) {
        this.dd = el;
        this.placeholder = this.dd.children('span');
        this.opts = this.dd.find('ul.drop li');
        this.val = '';
        this.index = -1;
        this.initEvents();
    }

    DropDown.prototype = {
        initEvents: function () {
            var obj = this;
            obj.dd.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).toggleClass('active');
            });
            obj.opts.on('click', function () {
                var opt = $(this);
                obj.val = opt.text();
                obj.index = opt.index();
                obj.placeholder.text(obj.val);
                opt.siblings().removeClass('is-active');
                opt.filter(':contains("' + obj.val + '")').addClass('is-active');
                var link = opt.filter(':contains("' + obj.val + '")').find('a').attr('href');
                window.location.replace(link);
            }).change();
        },
        getValue: function () {
            return this.val;
        },
        getIndex: function () {
            return this.index;
        }
    };

    $(function () {

        $(".js-wrap-drop").each(function (index, element) {
            var div = '#js-wrap-drop' + index;
            new DropDown($(div));
        });
        /* var dd1 = new DropDown($('.js-wrap-drop'));
        create new variable for each menu */
        $(document).click(function () {
            /* close menu on document click */
            $('.wrap-drop').removeClass('active');
        });
        $('.js-wrap-drop').click(function () {
            $(this).parent().siblings().children('.js-wrap-drop').removeClass('active');
            /* $(this).siblings().children('.js-wrap-drop').addClass('active'); */
        });
    });

    $(".dropdown-menu li a").click(function () {
        var selText = $(this).html();
        $(this).parents('.dropdown').find('.dropdown-toggle').html(selText);
    });

});


(function ($) {

    var tabs = $(".tabs-js li a");

    tabs.click(function () {
        var content = this.hash.replace('/', '');
        tabs.removeClass("is-active");
        $(this).addClass("is-active");
        $(".tabs-content").find('.tab-item').hide();
        $(content).fadeIn(200);
    });
})(jQuery);

