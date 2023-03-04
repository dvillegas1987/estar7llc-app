$("document").ready(function() {
    $(document).on("click", ".selectItem--js", function() {
        if ($(this).prop("checked") == false) {
            $(".selectAll-js").prop("checked", false);
        }
        if ($(".selectItem--js").length == $(".selectItem--js:checked").length) {
            $(".selectAll-js").prop("checked", true);
        }
        showFormActionsBtns();
    });
});

(function() {
    var dv = '#listingDiv';
    searchWishList = function() {
        $("#loadMoreBtnDiv").html('');
        $("#tab-wishlist").parents().children().removeClass("is-active");
        $("#tab-wishlist").addClass("is-active");
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Account', 'wishListSearch'), '', function(res) {
            $(dv).html(res);
        });
    };

    setupWishList2 = function(frm, event) {
        if (!$(frm).validate()) return false;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Account', 'setupWishList'), data, function(ans) {
            if (ans.status) {
                searchWishList();
            }
        });
    };

    deleteWishList = function(uwlist_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) { return false; };
        fcom.updateWithAjax(fcom.makeUrl('Account', 'deleteWishList'), 'uwlist_id=' + uwlist_id, function(ans) {
            if (ans.status) {
                searchWishList();
            }
        });
    };

    viewWishListItems = function(uwlist_id, append) {
        if (typeof append == undefined || append == null) {
            append = 0;
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Account', 'viewWishListItems'), 'uwlist_id=' + uwlist_id, function(ans) {
            if (append == 1) {
                $(dv).find('.loader-yk').remove();
                $(dv).append(ans);
            } else {
                $(dv).find('.loader-yk').remove();
                $(dv).html(ans);
            }
        });
    };

    viewFavouriteItems = function(append) {
        if (typeof append == undefined || append == null) {
            append = 0;
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Account', 'viewFavouriteItems'), '', function(ans) {
            if (append == 1) {
                $(dv).find('.loader-yk').remove();
                $(dv).append(ans);
            } else {
                $(dv).find('.loader-yk').remove();
                $(dv).html(ans);
            }
        });
    };

    searchFavouriteListItems = function(frm, append) {
        var dv2 = "#favListItems";
        if (typeof append == undefined || append == null) {
            append = 0;
        }
        if (typeof frm == undefined || frm == null) {
            frm = document.frmProductSearchPaging;
        }

        data = fcom.frmData(frm);

        if (append == 1) {
            $(dv2).prepend(fcom.getLoader());
        } else {
            $(dv2).html(fcom.getLoader());
        }

        fcom.updateWithAjax(fcom.makeUrl('Account', 'searchFavouriteListItems'), data, function(ans) {
            $.mbsmessage.close();
            if (append == 1) {
                $(dv2).find('.loader-yk').remove();
                $(dv2).append(ans.html);
            } else {
                $(dv2).html(ans.html);
            }
            $("#loadMoreBtnDiv").html(ans.loadMoreBtnHtml);
        });
    }

    searchWishListItems = function(uwlist_id, append, page) {
        var dv2 = "#favListItems";
        append = (append == "undefined") ? 0 : append;
        page = (page == "undefined") ? 0 : page;
        if (append == 1) {
            $(dv2).append(fcom.getLoader());
        } else {
            $(dv2).html(fcom.getLoader());
        }

        fcom.updateWithAjax(fcom.makeUrl('Account', 'searchWishListItems'), 'uwlist_id=' + uwlist_id + '&page=' + page, function(ans) {
            $.mbsmessage.close();
            $(dv).find('.loader-yk').remove();
            if (append == 1) {
                $(dv2).find('.loader-Js').remove();
                $(dv2).append(ans.html);
            } else {
                $(dv2).html(ans.html);
            }

            /* for LoadMore[ */
            $("#loadMoreBtnDiv").html(ans.loadMoreBtnHtml);
            /* ] */
        });
    }

    goToProductListingSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        /* var frm = document.frmProductSearchPaging;
        $(frm.page).val(page);
        $("form[name='frmProductSearchPaging']").remove(); */
        var uwlist_id = $("input[name='uwlist_id']").val();
        searchWishListItems(uwlist_id, 0, page);
        $(".selectAll-js").prop("checked", false);
        $(".formActionBtn-js").addClass('formActions-css');
    }

    goToFavouriteListingSearchPage = function(page) {
        if (typeof page == 'undefined' || page == null) {
            page = 1;
        }
        var frm = document.favtlistForm;
        $(frm.page).val(page);

        searchFavouriteListItems(frm, 0, page);
    }

    searchFavoriteShop = function(frm) {
        if (typeof frm == undefined || frm == null) {
            frm = document.frmFavShopSearchPaging;
        }
        data = fcom.frmData(frm);
        $("#tab-fav-shop").parents().children().removeClass("is-active");
        $("#tab-fav-shop").addClass("is-active");
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Account', 'favoriteShopSearch'), data, function(res) {
            $(dv).html(res);
        });
    };

    goToFavoriteShopSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmFavShopSearchPaging;
        $(frm.page).val(page);
        searchFavoriteShop(frm);
    };

    toggleShopFavorite2 = function(shop_id) {
        toggleShopFavorite(shop_id);
        searchFavoriteShop();
    };

    selectAll = function(obj) {
        $(".selectItem--js").each(function() {
            if (obj.prop("checked") == false) {
                $(this).prop("checked", false);
            } else {
                $(this).prop("checked", true);
            }
        });
        showFormActionsBtns();
    };


    removeFromWishlist = function(selprod_id, wish_list_id, event) {
        if (!confirm(langLbl.confirmDelete)) { return false; };
        addRemoveWishListProduct(selprod_id, wish_list_id, event);
        viewWishListItems(wish_list_id);
    };

    removeSelectedFromWishlist = function(wish_list_id, event) {
        event.stopPropagation();
        if (!confirm(langLbl.confirmDelete)) { return false; };
        updateWishlist();
        viewWishListItems(wish_list_id);
    };

    removeSelectedFromFavtlist = function(event, moveToCart = false) {
        event.stopPropagation();
        if (false === moveToCart) {
            if (!confirm(langLbl.confirmDelete)) { return false; };
        }
        if (0 < $('#wishlistForm').length) {
            var data = $('#wishlistForm').serialize();
        } else {
            var data = $('#favtlistForm').serialize();
        }
        fcom.updateWithAjax(fcom.makeUrl('Account', 'removeFromFavoriteArr'), data, function(ans) {
            if (false === moveToCart) {
                viewFavouriteItems();
                if (ans.status) {
                    $.mbsmessage.close();
                    $.systemMessage(ans.msg, 'alert--success');
                }
            }
        });

    };

    updateWishlist = function() {
        if (0 < $('#wishlistForm').length) {
            var data = $('#wishlistForm').serialize();
        } else {
            var data = $('#favtlistForm').serialize();
        }
        fcom.updateWithAjax(fcom.makeUrl('Account', 'addRemoveWishListProductArr'), data, function(ans) {
            if (ans.status) {
                $.mbsmessage.close();
                $.systemMessage(ans.msg, 'alert--success');
            }
        });
    };

    addToCart = function(obj, event, isWishlist = 0) {
        event.stopPropagation();

        $("#favListItems .selectItem--js").each(function() {
            $(this).prop("checked", false);
        });

        obj.parent().siblings('li').find('.selectItem--js').prop("checked", true);

        addSelectedToCart(event, isWishlist);
    };

    addSelectedToCart = function(event, isWishlist) {
        event.stopPropagation();
        $.mbsmessage(langLbl.processing, false, 'alert--process alert');
        if (0 < $('#wishlistForm').length) {
            var data = $('#wishlistForm').serialize();
        } else {
            var data = $('#favtlistForm').serialize();
        }
        fcom.updateWithAjax(fcom.makeUrl('cart', 'addSelectedToCart'), data, function(ans) {
            if (0 < isWishlist) {
                updateWishlist();
            } else {
                removeSelectedFromFavtlist(event, true);
            }
            setTimeout(function() { location.href = fcom.makeUrl('cart'); }, 1000);
        });
    };

})();