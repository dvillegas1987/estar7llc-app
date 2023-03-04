$(document).ready(function(){
	var type = $('input[name="fulfillment_type"]:checked').val();
	listCartProducts(type);
});
(function() {
	listCartProducts = function(fulfilmentType = 2){
		if(fulfilmentType == 2){
            $( "#shipping" ).prop( "checked", true );
            $( "#pickup" ).prop( "checked", false );
        }
        if(fulfilmentType == 1){
            $( "#pickup" ).prop( "checked", true );
            $( "#shipping" ).prop( "checked", false );
        }
		$('#cartList').html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Cart','listing', [fulfilmentType]),'',function(res){
			var json = $.parseJSON(res);  
            if(json.hasPhysicalProduct == false){
                $("#js-shiporpickup").remove();
            }
            
            if(json.cartProductsCount == 0){
                $("#js-cart-listing").html(json.html);
            }else{
				$("#cartList").html(json.html);				
                getCartFinancialSummary(fulfilmentType);
            }
			
			if(json.shipProductsCount == 0){
                $("#pickup").prop("checked", true);
                $("#shipping").prop("checked", false).prop("disabled", true).next('label').addClass("disabled").parent().attr("onclick", null);
            }else{
                $("#shipping").prop("disabled", false).next('label').removeClass("disabled").parent().attr("onclick", "listCartProducts(2)");
            }
            
            if(json.pickUpProductsCount == 0){
                $("#shipping").prop("checked", true);
                $("#pickup").prop("checked", false).prop("disabled", true).next('label').addClass("disabled").parent().attr("onclick", null);
            }else{
                $("#pickup").prop("disabled", false).next('label').removeClass("disabled").parent().attr("onclick", "listCartProducts(1)");
            }
		}); 
	};

	getPromoCode = function(){
		if( isUserLogged() == 0 ){
			loginPopUpBox(true);
			return false;
		}

		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Checkout','getCouponForm'), '', function(t){
                try{
                    t = $.parseJSON(t);
                    if(typeof t.status != 'undefined' &&  1 > t.status){
                        $.systemMessage(t.msg,'alert--danger', false);
                        $("#facebox .close").trigger('click');
                        if (typeof t.url != 'undefined') {
                            setTimeout(function(){ document.location.href = t.url; }, 1000);
                        }
                        return false;
                    }
                }
                catch(exc){}
                $.facebox(t,'faceboxWidth medium-fb-width');
				$("input[name='coupon_code']").focus();
			});
		});
	};

	triggerApplyCoupon = function(coupon_code){
		document.frmPromoCoupons.coupon_code.value = coupon_code;
		applyPromoCode(document.frmPromoCoupons);
		return false;
	};

	applyPromoCode  = function(frm){
		if( isUserLogged() == 0 ){
			loginPopUpBox(true);
			return false;
		}
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Cart','applyPromoCode'),data,function(res){
			$("#facebox .close").trigger('click');
			$.systemMessage.close();
			listCartProducts();
		});
	};

	goToCheckout = function(){
        var type = $('input[name="fulfillment_type"]:checked').val();
        var data = "type="+type;
        fcom.updateWithAjax(fcom.makeUrl('Cart','setCartCheckoutType'), data ,function(ans){
            if( isUserLogged() == 0 ){
                loginPopUpBox(true);
                return false;
            }
            document.location.href = fcom.makeUrl('Checkout');
        });
	};

	removePromoCode  = function(){
		fcom.updateWithAjax(fcom.makeUrl('Cart','removePromoCode'),'',function(res){
			listCartProducts();
		});
	};

	moveToWishlist = function( selprod_id, event, key ){
		event.stopPropagation();
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}

		fcom.ajax(fcom.makeUrl('Account','moveToWishList', [selprod_id]),'',function( resp ){
			removeFromCart( key );
		});
	};

	addToFavourite = function( key, selProdId ){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		$.mbsmessage.close();
		fcom.updateWithAjax(fcom.makeUrl('Account', 'markAsFavorite', [selProdId]), '', function(ans) {
			if( ans.status ){
				removeFromCart( key );
			}
		});
	};
    
    moveToSaveForLater = function( key, selProdId, fulfilmentType ){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		$.mbsmessage.close();
		fcom.updateWithAjax(fcom.makeUrl('Account', 'moveToSaveForLater', [selProdId]), '', function(ans) {
			if( ans.status ){
				listCartProducts(fulfilmentType);
				$.mbsmessage(langLbl.MovedSuccessfully, true, 'alert--success');
			}
		});
	};
    
    removeFromWishlist = function( selprod_id, wish_list_id, event){
		if( !confirm( langLbl.confirmDelete ) ){ return false; };
		addRemoveWishListProduct(selprod_id, wish_list_id, event);
		listCartProducts();
	};
    
    moveToCart = function(selprod_id, wish_list_id, event, fulfilmentType){
        var data = 'selprod_id[0]='+selprod_id;
        fcom.updateWithAjax(fcom.makeUrl('cart', 'addSelectedToCart' ), data, function(ans) {
            addRemoveWishListProduct(selprod_id, wish_list_id, event);
            listCartProducts(fulfilmentType);
            $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
            setTimeout(function(){
                if (1 > $("#cartList").length) {
                    location.reload();
                }
            }, 500);
		});
	};
    
    removePickupOnlyProducts = function(){
        if(confirm( langLbl.confirmRemove )){
			fcom.updateWithAjax(fcom.makeUrl('Cart','removePickupOnlyProducts'), '' ,function(ans){
				listCartProducts(2);
                $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
			});
		}
    }
    
    removeShippedOnlyProducts = function(){
        if(confirm( langLbl.confirmRemove )){
			fcom.updateWithAjax(fcom.makeUrl('Cart','removeShippedOnlyProducts'), '' ,function(ans){
				listCartProducts(1);
                $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
			});
		}
    }
    
    setCheckoutType = function(type){
        var data = "type="+type;
        fcom.updateWithAjax(fcom.makeUrl('Cart','setCartCheckoutType'), data ,function(ans){
            if( isUserLogged() == 0 ){
                loginPopUpBox(true);
                return false;
            }
            document.location.href = fcom.makeUrl('Checkout');
        });
    }
    
    getCartFinancialSummary = function(type){
        fcom.ajax(fcom.makeUrl('Cart','getCartFinancialSummary', [type]),'',function(res){
			$("#js-cartFinancialSummary").html(res);
		});
    }

})();
