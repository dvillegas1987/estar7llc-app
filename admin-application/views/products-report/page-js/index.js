$(document).ready(function(){
	searchProductsReport( document.frmProductsReportSearch );
	
	$('input[name=\'shop_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Shops', 'autoComplete'),
				data: { keyword: request['term'], fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'], value: item['name'], id: item['id'] };
					}));
				},
			});
		},
		select: function(event, ui) {
			$("input[name='shop_id']").val( ui.item.id );
		}
	});
	
	$('input[name=\'brand_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Brands', 'autoComplete'),
				data: { keyword: request['term'], fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'], value: item['name'], id: item['id'] };
					}));
				},
			});
		},
		select: function(event, ui) {
			$("input[name='brand_id']").val( ui.item.id );
		}
	});
	
	$('input[name=\'shop_name\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='shop_id']").val(0);
		}
	});
	
	$('input[name=\'brand_name\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='brand_id']").val(0);
		}
	});
	
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {
		if( typeof page == undefined || page == null ){
			page = 1;
		}
		var frm = document.frmProductsReportSearchPaging;		
		$( frm.page ).val( page );
		searchProductsReport( frm );
	};

	reloadList = function() {
		var frm = document.frmProductsReportSearchPaging;
		searchProductsReport(frm);
	};
	
	searchProductsReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('ProductsReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmProductsReportSearch.action = fcom.makeUrl('ProductsReport','export');
		document.frmProductsReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmProductsReportSearch.shop_id.value = '0';
		document.frmProductsReportSearch.brand_id.value = '0';
		document.frmProductsReportSearch.reset();
		searchProductsReport(document.frmProductsReportSearch);
	};
})();	