$(document).ready(function(){
	searchCatalogReport( document.frmCatalogReportSearch );
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {
		if( typeof page == undefined || page == null ){
			page = 1;
		}
		var frm = document.frmCatalogReportSearchPaging;		
		$( frm.page ).val( page );
		searchCatalogReport( frm );
	};

	reloadList = function() {
		var frm = document.frmCatalogReportSearchPaging;
		searchCatalogReport(frm);
	};
	
	searchCatalogReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('CatalogReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmCatalogReportSearch.action = fcom.makeUrl('CatalogReport','export');
		document.frmCatalogReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmCatalogReportSearch.reset();
		searchCatalogReport(document.frmCatalogReportSearch);
	};
})();	