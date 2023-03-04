$(document).ready(function() {
    searchShipPackages(document.frmPackageSearch);
});

(function() {	
    var dv = '#listing';
    goToSearchPage = function(page) {
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
	
	addPackageForm = function(id) {
        $.facebox(function() {
            packageForm(id);
        });
    };

    packageForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ShippingPackages', 'form', [id]), '', function(t) {
            $.facebox(t, 'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
    };

    setupPackage = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ShippingPackages', 'setup'), data, function(t) {
			searchShipPackages();
			$(document).trigger('close.facebox');
        });
    };
	
	clearSearch = function() {
        document.frmSearch.reset();
        searchShipPackages(document.frmSearch);
    };
	
})(); 