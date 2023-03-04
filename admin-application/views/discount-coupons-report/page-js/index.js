$(document).ready(function(){
	searchDiscountCouponsReport( document.frmDiscountCouponsReportSearch );
	
	$('input[name=\'keyword\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {	
			$.ajax({
				url: fcom.makeUrl('DiscountCouponsReport', 'autoCompleteJson'),
				data: {keyword: request['term'], fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['code'], value: item['code'], id: item['id']	};
					}));
				},
			});
		},
		'select': function(event, ui) {
			$("input[name='coupon_id']").val( ui.item.id );
		}
	});
	
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmDiscountCouponsReportSearch;		
		$(frm.page).val(page);
		searchDiscountCouponsReport(frm);
	};
	
	searchDiscountCouponsReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('DiscountCouponsReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmDiscountCouponsReportSearch.action = fcom.makeUrl('DiscountCouponsReport','export');
		document.frmDiscountCouponsReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmDiscountCouponsReportSearch.reset();
		searchDiscountCouponsReport(document.frmDiscountCouponsReportSearch);
	};
})();	