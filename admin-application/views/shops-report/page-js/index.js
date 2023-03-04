$(document).ready(function(){
	searchShopsReport( document.frmShopsReportSearch );

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
		'select': function(event, ui) {
			$("input[name='shop_id']").val( ui.item.id );
		}
	});

	$('input[name=\'user_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
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
		'select': function(event, ui) {
			$("input[name='shop_user_id']").val( ui.item.id );
		}
	});

	$('input[name=\'shop_name\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='shop_id']").val(0);
		}
	});

	$('input[name=\'user_name\']').keyup(function(){
		if( $(this).val() == "" ){
			$("input[name='shop_user_id']").val(0);
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
		var frm = document.frmShopsReportSearchPaging;
		$( frm.page ).val( page );
		searchShopsReport( frm );
	};

	reloadList = function() {
		var frm = document.frmShopsReportSearchPaging;
		searchShopsReport(frm);
	};

	searchShopsReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}

		$(dv).html(fcom.getLoader());

		fcom.ajax(fcom.makeUrl('ShopsReport','search'),data,function(res){
			$(dv).html(res);
		});
	};

	exportReport = function(dateFormat){
		// document.frmShopsReportSearch.action = fcom.makeUrl('ShopsReport','export');
		// document.frmShopsReportSearch.submit();
		location.href = fcom.makeUrl('ShopsReport','export');
	}

	clearSearch = function(){
		document.frmShopsReportSearch.shop_id.value = '0';
		document.frmShopsReportSearch.shop_user_id.value = '0';
		document.frmShopsReportSearch.reset();
		searchShopsReport( document.frmShopsReportSearch );
	};
})();
