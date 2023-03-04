

if (getCookie("screenWidth") != screen.width) {
    $.ajax({ url: fcom.makeUrl('Custom', 'updateScreenResolution', [screen.width, screen.height]) });
}

var Dashboard = function () {
    var menuChangeActive = function (el) {
        var hasSubmenu = $(el).hasClass("has-submenu");
        $(global.menuClass + " .is-active").removeClass("is-active");
        $(el).addClass("is-active");
    };
    var sidebarChangeWidth = function () {

        var $menuItemsTitle = $("li .menu-item__title");
        if ($("body").hasClass('sidebar-is-reduced')) {
            $("body").removeClass('sidebar-is-reduced').addClass('sidebar-is-expanded');
            $("<div class='sidebar-overlay--js'></div>").appendTo("body");
            var visibility = 1;
        } else {
            $("body").removeClass('sidebar-is-expanded').addClass('sidebar-is-reduced');
            $("div.sidebar-overlay--js").remove();
            var visibility = 0;
        }
        $.ajax({ url: fcom.makeUrl('Custom', 'setupSidebarVisibility', [visibility]) });
        /* $("body").toggleClass("sidebar-is-reduced sidebar-is-expanded"); */
        $(".hamburger-toggle").toggleClass("is-opened");
        setTimeout(function () {
            unlinkSlick();
            slickWidgetScroll();
        }, 500);
    };
    return {
        init: function init() {
            $(document).on("click", ".js-hamburger, .sidebar-overlay--js", sidebarChangeWidth);
            $(document).on("click", ".js-menu li", function (e) {
                menuChangeActive(e.currentTarget);
            });
        }
    };
}();
Dashboard.init();

$(document).on('click', '.menu-toggle ', function () {
    if (!$(this).parent().hasClass("is--active") && $(".collections-ui").hasClass("is--active")) {
        $(".collections-ui").removeClass("is--active")
        $(".menu-toggle").removeClass("cross");
        $('html').removeClass("nav-active");
    }
    $(this).parent().toggleClass("is--active");
    $('html').toggleClass("nav-active");
    $(this).toggleClass("cross");
});

/* Expand/collapse/accordion */
$(document).on('click', '.js-acc-triger', function (e) {
    e.preventDefault();
    if ($(this).hasClass('active')) {
        $(this).removeClass('active');
        $(this).next().stop().slideUp(300);
    } else {
        $(this).addClass('active');
        $(this).next().stop().slideDown(300);
    }
});

/*Ripple*/
$('[ripple]').on('click', function (e) {
    var rippleDiv = $('<div class="ripple" />'),
        rippleOffset = $(this).offset(),
        rippleY = e.pageY - rippleOffset.top,
        rippleX = e.pageX - rippleOffset.left,
        ripple = $('.ripple');

    rippleDiv.css({
        top: rippleY - (ripple.height() / 2),
        left: rippleX - (ripple.width() / 2),
        background: $(this).attr("ripple-color")
    }).appendTo($(this));

    window.setTimeout(function () {
        rippleDiv.remove();
    }, 1500);
});

/*Tabs*/
$(document).ready(function () {
    $(".tabs-content-js").hide();
    $(".tabs--flat-js li:first").addClass("is-active").show();
    $(".section").find(".tabs-content-js:first").show();
    $(".tabs--flat-js li").click(function () {
        $(this).parent().find("li").removeClass("is-active");
        $(this).addClass("is-active");
        $(".tabs-content-js").hide();
        var activeTab = $(this).find("a").attr("href");
        $(activeTab).fadeIn();
        return false;
        setSlider();
    });

    if (CONF_ENABLE_GEO_LOCATION && isUserDashboard == 0 && CONF_MAINTENANCE == 0 && (getCookie('_ykGeoDisabled') != 1 || className == 'CheckoutController' || className == 'CartController')) {
        accessLocation();
    }
});

$(document).on('afterClose.facebox', $('.location-permission').closest("#facebox"), function () {
    setCookie('_ykGeoDisabled', 1, 0.5);
});

/* for search form */
$(document).on('click', '.toggle--search-js', function () {
    $(this).toggleClass("is--active");
    $('html').toggleClass("is--form-visible");
});

$(document).on('click', '.toggle--search', function () {
    setTimeout(function () { $(".search--keyword--js").focus(); }, 500);
});

$("document").ready(function () {

    $('.parents--link').click(function () {


        $(this).parent().toggleClass("is--active");
        $(this).parent().find('.childs').toggleClass("opened");
    });
    /* for Dashbaord Links form */
});

// Wait for window load
$(window).on('load', function () {
    /* Animate loader off screen */
    $(".pageloader").remove();
    setSelectedCatValue();
});

$(document).ready(function () {
    /*common drop down function  */
    $('.dropdown__trigger-js').each(function () {
        $(this).click(function () {
            /*if($('html').hasClass('cart-is-active')){
             $('.cart').removeClass('cart-is-active');
             $('html').removeClass("cart-is-active");
            }*/
            if ($('body').hasClass('toggled_left')) {
                $('.navs_toggle').removeClass("active");
                $('body').removeClass('toggled_left');
            }
            if ($('html').hasClass('toggled-user')) {
                $('.dropdown__trigger-js').parent('.dropdown').removeClass("is-active");
                $("html").removeClass("toggled-user");
            } else {
                $(this).parent('.dropdown').toggleClass("is-active");
                $("html").toggleClass("toggled-user");
            }


            return false;
        });
    });
    $('html, .common_overlay').click(function () {
        if ($('.dropdown').hasClass('is-active')) {
            $('.dropdown').removeClass('is-active');
            $('html').removeClass('toggled-user');
        }
    });
    $('.dropdown__target-js').click(function (e) {
        e.stopPropagation();
    });

    $('.collections-ui').on('click', '.collection__container', function (e) {
        e.stopPropagation();
    });

    $('#cartSummary').on('click', '.cart-detail', function (e) {
        e.stopPropagation();
    });

    /* $('.main-search').on('click','.form--search-popup',function(e){

        if(!$(e.target).hasClass('close-layer')){
            e.stopPropagation();
        }else{
            if($('html').hasClass('is--form-visible')){
                $('html').removeClass('is--form-visible');
                $('.toggle--search-js').toggleClass("is--active");
            }
        }
    }); */

    /* for fixed header */
    /*$(window).scroll(function(){
        body_height = $("#body").position();
        scroll_position = $(window).scrollTop();
        if( typeof body_height !== typeof undefined && body_height.top < scroll_position)
            $("body").addClass("fixed");
        else
            $("body").removeClass("fixed");
    });*/

    /* for footer */
    if ($(window).width() < 576) {
        /* FOR FOOTER TOGGLES */
        $('.toggle__trigger-js').click(function () {
            if ($(this).hasClass('is-active')) {
                $(this).removeClass('is-active');
                $(this).siblings('.toggle__target-js').slideUp(); return false;
            }
            $('.toggle__trigger-js').removeClass('is-active');
            $(this).addClass("is-active");
            $('.toggle__target-js').slideUp();
            $(this).siblings('.toggle__target-js').slideDown();
        });
    }

    /* for footer accordion */
    $(function () {
        $('.accordion_triger').on('click', function (e) {
            e.preventDefault();
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                $(this).next()
                    .stop()
                    .slideUp(300);
            } else {
                $(this).addClass('active');
                $(this).next()
                    .stop()
                    .slideDown(300);
            }
        });
        /* $(document).delegate('.cart > a','click',function(){
        $('html').toggleClass("cart-is-active");
        $(this).toggleClass("cart-is-active");
        }); */
    });


    /* for cart area */
    $('.cart').on('click', function () {
        if ($('html').hasClass('toggled-user')) {
            $('.dropdown__trigger-js').parent('.dropdown').removeClass("is-active");
            $("html").removeClass("toggled-user");
        }
        /* $('html').toggleClass("cart-is-active");
        $(this).toggleClass("cart-is-active"); */
        /* return false;  */
    });
    $('html').click(function () {
        /* if($('html').hasClass('cart-is-active')){
            $('html').removeClass('cart-is-active');
            $('.cart').toggleClass("cart-is-active");
        } */
        if ($('.collection__container').hasClass('open-menu')) {
            $('.open-menu').parent().toggleClass('is-active');
            $('.open-menu').toggleClass('open-menu');
        }
    });

    $('.cart').click(function (e) {
        e.stopPropagation();
    });


});

/*ripple effect*/
$(function () {
    var ink, d, x, y;
    $(".ripplelink, .slick-arrow").click(function (e) {
        if ($(this).find(".ink").length === 0) {
            $(this).prepend("<span class='ink'></span>");
        }
        ink = $(this).find(".ink");
        ink.removeClass("animate");

        if (!ink.height() && !ink.width()) {
            d = Math.max($(this).outerWidth(), $(this).outerHeight());
            ink.css({ height: d, width: d });
        }
        x = e.pageX - $(this).offset().left - ink.width() / 2;
        y = e.pageY - $(this).offset().top - ink.height() / 2;
        ink.css({ top: y + 'px', left: x + 'px' }).addClass("animate");
    });
});



/*back-top*/
$(document).ready(function () {
    /* hide #back-top first */
    $(".back-to-top").hide();

    /* fade in #back-top */
    $(function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('.back-to-top').fadeIn();
            } else {
                $('.back-to-top').fadeOut();
            }
        });
        /* scroll body to 0px on click */
        $('.back-to-top a').click(function () {
            $('body,html').animate({
                scrollTop: 0
            }, 800);
            return false;
        });
    });

    $('.switch-button').click(function () {
        $(this).toggleClass("is--active");
        if ($(this).hasClass("buyer") && !$(this).hasClass("is--active")) {
            window.location.href = fcom.makeUrl('seller');
        } if ($(this).hasClass("seller") && $(this).hasClass("is--active")) {
            window.location.href = fcom.makeUrl('buyer');
        }
    });

    var t;
    $('a.loadmore').on('click', function (e) {
        e.preventDefault();
        clearTimeout(t);
        $(this).toggleClass('loading');
        t = setTimeout(function () {
            $('a.loadmore').removeClass("loading")
        }, 2500);
    });

});



/*  like animation  */
$(document).ready(function () {
    var debug = /*true ||*/ false;
    var h = document.querySelector('.heart-wrapper-Js');

    /*   function toggleActivate(){
        h.classList.toggle('is-active');
      }   */

    if (debug) {
        var elts = Array.prototype.slice.call(h.querySelectorAll(':scope > *'), 0);
        var activated = false;
        var animating = false;
        var count = 0;
        var step = 1000;

        function setAnim(state) {
            elts.forEach(function (elt) {
                elt.style.animationPlayState = state;
            });
        }

        h.addEventListener('click', function () {
            if (animating) return;
            if (count > 27) {
                h.classList.remove('is-active');
                count = 0;
                return;
            }
            if (!activated) h.classList.add('is-active') && (activated = true);

            animating = true;

            setAnim('running');
            setTimeout(function () {
                setAnim('paused');
                animating = false;
            }, step);
        }, false);

        setAnim('paused');
        elts.forEach(function (elt) {
            elt.style.animationDuration = step / 1000 * 27 + 's';
        });
    }
});

$(function () {
    var elem = "";
    var settings = {
        mode: "toggle",
        limit: 2,
    };
    var text = "";
    $.fn.viewMore = function (options) {
        $.extend(settings, options)
        text = $(this).html();
        elem = this;
        initialize();
    };

    function initialize() {
        total_li = $(elem).children('ul').children('li').length;
        limit = settings.limit;
        extra_li = total_li - limit;
        if (total_li > limit) {
            $(elem).children('ul').children('li:gt(' + (limit - 1) + ')').hide();
            $(elem).append('<a class="read_more_toggle closed"  onClick="bindChangeToggle(this);"><span class="ink animate"></span> <span class="read_more">View More</span></a>');
        }
    }
});

function bindChangeToggle(obj) {
    if ($(obj).hasClass('closed')) {
        $(obj).find('.read_more').text('.. View Less');
        $(obj).removeClass('closed');
        $('#accordian').children('ul').children('li').show();
    } else {
        $(obj).addClass('closed');
        $(obj).find('.read_more').text('.. View More');
        $('#accordian').children('ul').children('li:gt(0)').hide();
    }
}

function setSelectedCatValue(id) {
    var currentId = 'category--js-' + id;
    var e = document.getElementById(currentId);
    if (e != undefined) {
        var catName = e.text;
        $(e).parent().siblings().removeClass('is-active');
        $(e).parent().addClass('is-active');
        $('#selected__value-js').html(catName);
        $('#selected__value-js').closest('form').find('input[name="category"]').val(id);
        $('.dropdown__trigger-js').parent('.dropdown').removeClass("is-active");
    }
}

function setQueryParamSeperator(urlstr) {
    if (urlstr.indexOf("?") > -1) {
        return '&';
    }
    return '?';
}

function animation(obj) {
    if ($(obj).val().length > 0) {
        if (!$('.submit--js').hasClass('is--active'))
            $('.submit--js').addClass('is--active');
    } else {
        $('.submit--js').removeClass('is--active');
    }
}

(function () {
    Slugify = function (str, str_val_id, is_slugify, caption) {
        var str = str.toString().toLowerCase()
            .replace(/\s+/g, '-')           /* Replace spaces with - */
            .replace(/[^\w\-]+/g, '')       /* Remove all non-word chars */
            .replace(/\-\-+/g, '-')         /* Replace multiple - with single - */
            .replace(/^-+/, '')             /* Trim - from start of text */
            .replace(/-+$/, '');
        if ($("#" + is_slugify).val() == 0) {
            $("#" + str_val_id).val(str).keyup();
            /* $("#" + str_val_id).val(str); */
            $("#" + caption).html(siteConstants.webroot + str);
        }
    };

    getSlugUrl = function (obj, str, extra, pos) {
        if (typeof pos == undefined || pos == null) {
            pos = 'pre';
        }
        var str = str.toString().toLowerCase()
            .replace(/\s+/g, '-')           /* Replace spaces with - */
            .replace(/[^\w\-]+/g, '')       /* Remove all non-word chars */
            .replace(/\-\-+/g, '-')         /* Replace multiple - with single - */
            .replace(/^-+/, '')             /* Trim - from start of text */
            .replace(/-+$/, '');
        if (extra && pos == 'pre') {
            str = extra + '-' + str;
        } if (extra && pos == 'post') {
            str = str + '-' + extra;
        }
        $(obj).next().html(siteConstants.webroot + str);
    };
})();

/* scroll tab active function */
moveToTargetDiv('.tabs--scroll ul li.is-active', '.tabs--scroll ul', langLbl.layoutDirection);

$(document).on('click', '.tabs--scroll ul li', function () {
    if ($(this).hasClass('fat-inactive')) { return; }
    $(this).closest('.tabs--scroll ul li').removeClass('is-active');
    $(this).addClass('is-active');
    moveToTargetDiv('.tabs--scroll ul li.is-active', '.tabs--scroll ul', langLbl.layoutDirection);
});

function moveToTargetDiv(target, outer, layout) {
    var out = $(outer);
    var tar = $(target);
    var z = tar.index();
    var q = 0;
    var m = out.find('li');

    for (var i = 0; i < z; i++) {
        q += $(m[i]).outerWidth(true) + 4;
    }

    $('.tabs--scroll ul').animate({
        scrollLeft: Math.max(0, q)
    }, 800);
    return false;
}

function moveToTargetDivssss(target, outer, layout) {
    var out = $(outer);
    var tar = $(target);
    var z = tar.index();
    var m = out.find('li');

    if (layout == 'ltr') {
        var q = 0;
        for (var i = 0; i < z; i++) {
            q += $(m[i]).outerWidth(true) + 4;
        }
    } else {
        var ulWidth = 0;
        $(outer + " li").each(function () {
            ulWidth = ulWidth + $(this).outerWidth(true);
        });

        var q = 0;
        for (var i = 0; i <= z; i++) {
            q += $(m[i]).outerWidth(true);
        }
        q = ulWidth - q;

        /* var q = out.last().outerWidth(true);
        var q = ulWidth;
        for(var i = z; i > 0; i--){
            q-= $(m[i]).outerWidth(true);
        }   */
    }
    out.animate({
        scrollLeft: Math.max(0, q)
    }, 800);
    return false;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

/*Google reCaptcha V3  */
function googleCaptcha() {
    $("body").addClass("captcha");
    var inputObj = $("form input[name='g-recaptcha-response']");
    if ('' != inputObj.val()) { return; }

    var submitBtn = inputObj.parent("form").find('input[type="submit"]');
    $.mbsmessage(langLbl.loadingCaptcha, false, 'alert--process');
    submitBtn.attr({ "disabled": "disabled", "type": "button" });

    var counter = 0;
    var checkToken = setInterval(function () {
        counter++
        /* Check if not loaded until 30 Sec = counter 150. Because it run 5 times in 1 sec. */
        if (150 == counter) {
            $.mbsmessage(langLbl.invalidGRecaptchaKeys, true, 'alert--danger');
            clearInterval(checkToken);
            return;
        }

        if (0 < inputObj.length && 'undefined' !== typeof grecaptcha) {
            grecaptcha.ready(function () {
                try {
                    grecaptcha.execute(langLbl.captchaSiteKey, { action: inputObj.data('action') }).then(function (token) {
                        inputObj.val(token);
                        submitBtn.removeAttr("disabled").attr('type', 'submit');
                        clearInterval(checkToken);
                        $.mbsmessage.close();
                    });
                }
                catch (error) {
                    $.mbsmessage(error, true, 'alert--danger');
                    return;
                }
            });
        }
    }, 200); /* 1000 MS = 1 Sec. */
    return;
}

function getLocation() {
    return {
        'lat': getCookie('_ykGeoLat'),
        'lng': getCookie('_ykGeoLng'),
        'countryCode': getCookie('_ykGeoCountryCode'),
        'stateCode': getCookie('_ykGeoStateCode'),
        'zip': getCookie('_ykGeoZip')
    };
}

function accessLocation(force = false) {
    var location = getLocation();
    if ("" == location.lat || "" == location.lng || "" == location.countryCode || force) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('Home', 'accessLocation'), '', function (t) {
                try {
                    var json = $.parseJSON(t);
                    if (1 > json.status) {
                        $.mbsmessage(json.msg, false, 'alert--danger');
                    }
                    $(document).trigger('close.facebox');
                    return false;
                } catch (exc) {
                    $.facebox(t, 'location-popup-width');
                    googleAddressAutocomplete();
                }
            });
        });
    }
}

function loadGeoLocation() {
    if (!CONF_ENABLE_GEO_LOCATION) {
        return;
    }

    if (typeof navigator.geolocation == 'undefined') {
        $.mbsmessage(langLbl.geoLocationNotSupported, true, 'alert--danger');
        return false;
    }

    navigator.geolocation.getCurrentPosition(function (position) {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        codeLatLng(lat, lng, getGeoAddress);
    }, function (error) {
        if (1 == error.code) {
            $.mbsmessage(error.message, true, 'alert--danger');
        }
    });
}

function setGeoAddress(data) {
    var address = '';
    setCookie('_ykGeoLat', data.lat);
    setCookie('_ykGeoLng', data.lng);

    if ('undefined' != typeof data.postal_code) {
        setCookie('_ykGeoZip', data.postal_code);
        address += data.postal_code + ', ';
    }

    if ('undefined' != typeof data.city) {
        address += data.city + ', ';
    }

    if ('undefined' != typeof data.state) {
        setCookie('_ykGeoStateCode', data.state_code);
        address += data.state + ', ';
    }

    if ('undefined' != typeof data.country) {
        setCookie('_ykGeoCountryCode', data.country_code);
        address += data.country + ', ';
    }
    address = address.replace(/,\s*$/, "");

    var formatedAddr = ('undefined' == typeof data.formatted_address) ? '' : data.formatted_address;
    address = ('' == address) ? formatedAddr : address;

    setCookie('_ykGeoAddress', address);

    return address;
}

function getGeoAddress(data) {
    address = setGeoAddress(data);
    $(document).trigger('close.facebox');
    displayGeoAddress(address);
}

var canSetCookie = false;
function setCookie(cname, cvalue, exdays = 365) {
    if (false == canSetCookie) {
        return false;
    }
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function displayGeoAddress(address) {
    if (0 < $("#js-curent-zip-code").length) {
        $("#js-curent-zip-code").text(address);
    }
}

function googleAddressAutocomplete(elementId = 'ga-autoComplete', field = 'formatted_address', saveCookie = true, callback = 'googleSelectedAddress') {
    canSetCookie = saveCookie;
    if (1 > $("#" + elementId).length) {
        var msg = (langLbl.fieldNotFound).replace('{field}', elementId + ' Field');
        $.systemMessage(msg, 'alert--danger');
        return false;
    }
    var fieldElement = document.getElementById(elementId);
    setTimeout(function () { $("#" + elementId).attr('autocomplete', 'no'); }, 500);
    var options = { types: ['(regions)'] }
    var autocomplete = new google.maps.places.Autocomplete(fieldElement, options);
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace();
        var lat = place['geometry']['location'].lat();
        var lng = place['geometry']['location'].lng();
        var address = '';
        var data = {};
        data['lat'] = lat;
        data['lng'] = lng;
        data['formatted_address'] = place['formatted_address'];
        if (0 < place.address_components.length) {
            var addressComponents = place.address_components;
            for (var i = 0; i < addressComponents.length; i++) {
                var key = place.address_components[i].types[0];
                var value = place.address_components[i].long_name;
                data[key] = value;
                if ('country' == key) {
                    data['country_code'] = place.address_components[i].short_name;
                    data['country'] = value;
                } else if ('administrative_area_level_1' == key) {
                    data['state_code'] = place.address_components[i].short_name;
                    data['state'] = value;
                } else if ('administrative_area_level_2' == key) {
                    data['city'] = value;
                }
            }
            address = setGeoAddress(data);
            if ('' == address) {
                var msg = (langLbl.fieldNotFound).replace('{field}', field);
                $.systemMessage(msg, 'alert--danger');
            }

            $("#" + elementId).val(address);
            displayGeoAddress(address);
        }

        if (0 < $("#facebox #" + elementId).length) {
            $(document).trigger('close.facebox');
        }
        if (eval("typeof " + callback) == 'function') {
            window[callback](data);
        }
        return data;
    });
}

function getSelectedCountry() {
    var country = document.getElementById('shop_country_code');
    console.log(country[0].selectedOptions[0].innerText);
    return country[0].selectedOptions[0].innerText;
}

var map;
var marker;
var geocoder;
var infowindow;
/* Initialize the map. */
function initMap(lat = 40.72, lng = -73.96, elementId = 'map') {
    var lat = parseFloat(lat);
    var lng = parseFloat(lng);
    var latlng = { lat: lat, lng: lng };
    var address = '';
    if (1 > $("#" + elementId).length) {
        return;
    }
    map = new google.maps.Map(document.getElementById(elementId), {
        zoom: 12,
        center: latlng
    });
    geocoder = new google.maps.Geocoder;
    infowindow = new google.maps.InfoWindow;

    geocodeAddress(geocoder, map, infowindow, { 'location': latlng });

    /* var sel = document.getElementById('shop_country_code');
    var country = sel.options[sel.selectedIndex].text;
    
    address = document.getElementById('postal_code').value;
    address = country + ' ' + address;
	
    geocodeAddress(geocoder, map, infowindow, { 'address': address }); */

    document.getElementById('postal_code').addEventListener('blur', function () {
        var sel = document.getElementById('shop_country_code');
        var country = sel.options[sel.selectedIndex].text;

        var sel = document.getElementById('shop_state');
        var state = sel.options[sel.selectedIndex].text;

        address = document.getElementById('postal_code').value;
        address = country + ' ' + state + ' ' + address;
        geocodeAddress(geocoder, map, infowindow, { 'address': address });
    });

    document.getElementById('shop_state').addEventListener('change', function () {
        var sel = document.getElementById('shop_country_code');
        var country = sel.options[sel.selectedIndex].text;

        var sel = document.getElementById('shop_state');
        var state = sel.options[sel.selectedIndex].text;

        address = country + ' ' + state;

        geocodeAddress(geocoder, map, infowindow, { 'address': address });
    });

    document.getElementById('shop_country_code').addEventListener('change', function () {
        var sel = document.getElementById('shop_country_code');
        var country = sel.options[sel.selectedIndex].text;
        geocodeAddress(geocoder, map, infowindow, { 'address': country });
    });

    /* for (i = 0; i < document.getElementsByClassName('addressSelection-js').length; i++) {
        document.getElementsByClassName('addressSelection-js')[i].addEventListener("change", function(e) {
            address = e.target.options[e.target.selectedIndex].text;
            geocodeAddress(geocoder, map, infowindow, {'address': address});
            });
    } */
}

function geocodeAddress(geocoder, resultsMap, infowindow, address) {
    geocoder.geocode(address, function (results, status) {
        if (status === google.maps.GeocoderStatus.OK) {
            resultsMap.setCenter(results[0].geometry.location);
            if (marker && marker.setMap) {
                marker.setMap(null);
            }
            marker = new google.maps.Marker({
                map: resultsMap,
                position: results[0].geometry.location,
                draggable: true
            });
            geocodeSetData(results);
            google.maps.event.addListener(marker, 'dragend', function () {
                geocoder.geocode({ 'latLng': marker.getPosition() }, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        geocodeSetData(results);
                    }
                });
            });
        } else {
            console.log('Geocode was not successful for the following reason: ' + status);
        }
    });
}

function geocodeSetData(results) {
    document.getElementById('lat').value = marker.getPosition().lat();
    document.getElementById('lng').value = marker.getPosition().lng();
    if (results[0]) {
        infowindow.setContent(results[0].formatted_address);
        infowindow.open(map, marker);
        var address_components = results[0].address_components;
        var data = {};
        /* data['lat'] = pos.lat();
        data['lng'] = pos.lng(); */
        data['formatted_address'] = results[0].formatted_address;
        if (0 < address_components.length) {
            var addressComponents = address_components;
            for (var i = 0; i < addressComponents.length; i++) {
                var key = address_components[i].types[0];
                var value = address_components[i].long_name;
                data[key] = value;
                if ('country' == key) {
                    data['country_code'] = address_components[i].short_name;
                    data['country'] = value;
                } else if ('administrative_area_level_1' == key) {
                    data['state_code'] = address_components[i].short_name;
                    data['state'] = value;
                } else if ('administrative_area_level_2' == key) {
                    data['city'] = value;
                }
            }
        }
        $('#postal_code').val(data.postal_code);

        $('#shop_country_code option').each(function () {
            if (this.text == data.country) {
                $('#shop_country_code').val(this.value);
                var state = 0;
                $('#shop_state option').each(function () {
                    if (this.value == data.state_code || this.text == data.state || this.text == data.locality) {
                        return state = this.value;
                    }
                });
                getStatesByCountryCode(this.value, state, '#shop_state', 'state_code');
                return false;
            }
        });
    }
}

function loadScript(src, callback = '', params = []) {
    if ($('script[src="' + src + '"]').length) {
        callback.apply(this, params);
        return;
    }

    let script = document.createElement('script');
    script.src = src;
    if ('' != callback) {
        script.onload = function () {
            callback.apply(this, params);
        };
    }

    document.head.append(script);
}