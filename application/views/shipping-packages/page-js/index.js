$(document).ready(function() {
    searchShipPackages(document.frmPackageSearch);
});

(function() {	
    var dv = '#listing';
    goToPackagesSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmPackageSearchPaging;
        $(frm.page).val(page);
        searchShipPackages(frm);
    };

    reloadList = function() {
        var frm = document.frmPackageSearchPaging;
        searchShipPackages(frm);
    };
	
	searchShipPackages = function(form) {		
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('ShippingPackages', 'search'), data, function(res) {
            $(dv).html(res);
        });
	}
	
	clearSearch = function() {
        document.frmSearch.reset();
        searchShipPackages(document.frmSearch);
    };
	
})(); 