$(document).ready(function(){
	searchAbandonedCart(document.frmAbandonedCartSearch);
	
	$('input[name=\'user_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request['term'], fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
                        return { label: item['credential_email']+' ('+item['username']+')', value: item['credential_email']+' ('+item['username']+')', id: item['id'] };
					}));
				},
			});
		},
        select: function (event, ui) {
            $("input[name='abandonedcart_user_id']").val( ui.item.id );
        }
	});
	
	$('input[name=\'user_name\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='abandonedcart_user_id']").val( "" );
		}
	});
    
    $('input[name=\'seller_product\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('sellerProducts', 'autoComplete'),
				data: {keyword: request['term'], fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'], value: item['name'], id: item['id'] };
					}));
				},
			});
		},
        select: function (event, ui) {
            $("input[name='abandonedcart_selprod_id']").val( ui.item.id );
        }
	});
	
	$('input[name=\'seller_product\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='abandonedcart_selprod_id']").val( "" );
		}
	});
    
    $(document).on('click','ul.linksvertical li a.redirect--js',function(event){
		event.stopPropagation();
	});
    
});


(function() {
	var currentPage = 1;
    var abandonedcartId = 0; 
	var userId = 0;
    var productId = 0;
    
	searchAbandonedCart = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		var dv = $('#abandonedCartListing');		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		dv.html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('AbandonedCart','search'),data,function(res){
			dv.html(res);            
		});
	};
    
    submitForm = function(action){
        $("input[name='abandonedcart_action']").val( action );
        searchAbandonedCart(document.frmAbandonedCartSearch);
    }
        
    goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmAbandonedCartSearch;		
		$(frm.page).val(page);
		searchAbandonedCart(frm);
	}    
    
	clearAbandonedCartSearch = function(){
        document.frmAbandonedCartSearch.abandonedcart_user_id.value = '';
		document.frmAbandonedCartSearch.abandonedcart_selprod_id.value = '';
        document.frmAbandonedCartSearch.abandonedcart_action.value = ''; 
        $(".tabs_nav-js > li > a").removeClass('active');
        document.frmAbandonedCartSearch.reset();
		searchAbandonedCart(document.frmAbandonedCartSearch);
	};
    
    
    discountNotification = function(abandonedcart_id, user_id, product_id){
        fcom.updateWithAjax(fcom.makeUrl('AbandonedCart', 'validateProductForNotification', [product_id]), '', function(t) {  
            addCouponForm(0);
            abandonedcartId = abandonedcart_id;
            userId = user_id;
            productId = product_id;
		});
    }
    
    
    addCouponForm = function(id) {			
		$.facebox(function() {
			fcom.displayProcessing();		
			fcom.ajax(fcom.makeUrl('DiscountCoupons', 'form', [id]), '', function(t) { 
				fcom.updateFaceboxContent(t);
			});
		});
	};
    
    callCouponTypePopulate = function(val){
		if( val == 1 ){
			$("#coupon_minorder_div").show();
			$("#coupon_validfor_div").hide();
			
		}if( val == 3 ){
			$("#coupon_minorder_div").hide();
			$("#coupon_validfor_div").show();
		}
	};

    callCouponDiscountIn = function(val){
        if( val == DISCOUNT_IN_PERCENTAGE ){
            $("#coupon_max_discount_value_div").show();
        }
        if( val == DISCOUNT_IN_FLAT ){
            $("#coupon_max_discount_value_div").hide();
        }
    }
    
    setupCoupon = function(frm) { 
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('DiscountCoupons', 'setup'), data, function(t) {  
            updateCouponUser(t.couponId, userId);
            //updateCouponProduct(t.couponId, productId);
			sendDiscountNotification(abandonedcartId, t.couponId);
            $(document).trigger('close.facebox');
		});
	};

    updateCouponUser = function(couponId,userId){
		var data = 'coupon_id='+couponId+'&user_id='+userId;
		fcom.updateWithAjax(fcom.makeUrl('DiscountCoupons', 'updateCouponUser'), data, function(t) {		
		});
	};
    
    /*updateCouponProduct = function(couponId,productId){
		var data = 'coupon_id='+couponId+'&product_id='+productId;
		fcom.updateWithAjax(fcom.makeUrl('DiscountCoupons', 'updateCouponProduct'), data, function(t) {		
		});
	};*/

    sendDiscountNotification = function(abandonedcartId, couponId){
        var data = 'abandonedcartId='+abandonedcartId+'&couponId='+couponId;
        fcom.updateWithAjax(fcom.makeUrl('AbandonedCart', 'discountNotification'), data, function(t) {            
            searchAbandonedCart(document.frmAbandonedCartSearch);
        });
    } 

})();

