$(document).ready(function(){
	searchSellerOrders(document.frmVendorOrderSearch);
	$('input[name=\'buyer\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request['term'], user_is_buyer: 1, fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['credential_email']+' ('+item['username']+')', value: item['credential_email']+' ('+item['username']+')', id: item['id']	};
					}));
				},
			});
		},
		select: function(event, ui) {
			$("input[name='user_id']").val( ui.item.id );
		}
	});
	
	$('input[name=\'buyer\']').keyup(function(){
		$('input[name=\'user_id\']').val('');
	});
	
	$(document).on('click','ul.linksvertical li a.redirect--js',function(event){
		window.location = event.target();
	});	
	
});
(function() {
	var currentPage = 1;
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmVendorOrderSearchPaging;		
		$(frm.page).val(page);
		searchSellerOrders(frm);
	}
	
	searchSellerOrders = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$('#ordersListing').html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('SellerOrders','search'),data,function(res){
			$('#ordersListing').html(res);
		});
	};
		
	reloadSellerOrderList = function() {
		searchSellerOrders(document.frmVendorOrderSearchPaging, currentPage);
	}
	
/* 	cancelOrder = function (id){
		if(!confirm(langLbl.confirmCancelOrder)){return;}		
		fcom.updateWithAjax(fcom.makeUrl('SellerOrders','CancelOrder',[id]),'',function(res){		
			reloadSellerOrderList();
		});
	}; */
	
	clearSellerOrderSearch = function(){
		document.frmVendorOrderSearch.user_id.value = '';
		document.frmVendorOrderSearch.reset();
		searchSellerOrders(document.frmVendorOrderSearch);
    };
    
    /* ShipStation */
    generateLabel = function (opId) {
        fcom.updateWithAjax(fcom.makeUrl('ShippingServices', 'generateLabel', [opId]), '', function (t) {
            window.location.reload();
        });
    }
    /* ShipStation */
})();