$(document).on("change", "select[name='is_custom_or_catalog']", function(){
    if ( 0 == $(this).val() ) {
        $("input[name='product_seller_id']").val('');
        $("input[name='product_seller']").val('').attr('disabled','disabled');
    }else{
        $("input[name='product_seller']").removeAttr('disabled');
    }
});

$(document).ready(function(){
    searchProducts(document.frmSearch);

    $("input[name='product_seller']").autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            if( '' != request ){
                $.ajax({
                    url: fcom.makeUrl('Products', 'autoCompleteSellerJson'),
                    data: {keyword: request['term']},
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            var email = '';
                            if( null !== item['credential_email'] ){
                                email = ' ('+item['credential_email']+')';
                            }
                            return { label: item['credential_username'] + email, value: item['credential_username'] + email, id: item['credential_user_id'] };
                        }));
                    },
                });
            }else{
                $("input[name='product_seller_id']").val('');
            }
        },
        select: function(event, ui) {
            $("input[name='product_seller_id']").val( ui.item.id );
        }
    });

});

(function() {
    var runningAjaxReq = false;
    searchProducts = function(frm){
        if( runningAjaxReq == true ){
            return;
        }
        runningAjaxReq = true;
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (frm) {
            data = fcom.frmData(frm);
        }
        /*]*/
        var dv = $('#listing');
        $(dv).html( fcom.getLoader() );
        window.history.replaceState({}, document.title, fcom.makeUrl('Products'));
        fcom.ajax(fcom.makeUrl('Products','search'),data,function(res){
            runningAjaxReq = false;
            $("#listing").html(res);
        });
    };

    goToSearchPage = function(page) {
        if(typeof page==undefined || page == null){
            page =1;
        }
        var frm = document.frmProductSearchPaging;
        $(frm.page).val(page);
        searchProducts(frm);
    };

    reloadList = function() {
        var frm = document.frmProductSearchPaging;
        searchProducts(frm);
    };
    
    clearSearch = function(){
        document.frmSearch.reset();
        document.frmSearch.product_seller_id.value = '';
        document.frmSearch.product_id.value = '';
        document.frmSearch.prodcat_id.value = -1;
        $("input[name='product_seller']").removeAttr('disabled');
        searchProducts(document.frmSearch);
    };
    
    toggleStatus = function(e,obj,canEdit){
        if(canEdit == 0){
            e.preventDefault();
            return;
        }
        if(!confirm(langLbl.confirmUpdateStatus)){
            e.preventDefault();
            return;
        }
        var productId = parseInt(obj.value);
        if(productId < 1){
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data='productId='+productId;
        fcom.ajax(fcom.makeUrl('Products','changeStatus'),data,function(res){
        var ans = $.parseJSON(res);
            if( ans.status == 1 ){
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
                /* setTimeout(function(){
                    reloadList();
                }, 1000); */
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    deleteProduct = function(productId){
        if(!confirm(langLbl.confirmDelete)){
            e.preventDefault();
            return;
        }
        if(productId < 1){
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data='productId='+productId;
        fcom.ajax(fcom.makeUrl('Products','deleteProduct'),data,function(res){
        var ans = $.parseJSON(res);
            if( ans.status == 1 ){
                fcom.displaySuccessMessage(ans.msg);
                setTimeout(function(){
                    reloadList();
                }, 1000);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmProdListing").attr("action",fcom.makeUrl('Products','deleteSelected')).submit();
    };
})();
