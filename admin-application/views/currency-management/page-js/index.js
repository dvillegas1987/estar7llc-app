$(document).ready(function() {
    searchCurrency(document.frmCurrencySearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    reloadList = function() {
        var frm = document.frmCurrencySearch;
        searchCurrency(frm);
    };

    searchCurrency = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('CurrencyManagement', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    editCurrencyForm = function(currencyId) {
        $.facebox(function() {
            currencyForm(currencyId);
        });
    };

    currencyForm = function(currencyId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('CurrencyManagement', 'form', [currencyId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupCurrency = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CurrencyManagement', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editCurrencyLangForm(t.currencyId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editCurrencyLangForm = function(currencyId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('CurrencyManagement', 'langForm', [currencyId, langId, autoFillLangData]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupLangCurrency = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CurrencyManagement', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editCurrencyLangForm(t.currencyId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    toggleStatus = function(obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var currencyId = parseInt(obj.id);
        if (currencyId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'currencyId=' + currencyId;
        fcom.ajax(fcom.makeUrl('CurrencyManagement', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
                setTimeout(function() {
                    reloadList();
                }, 1000);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmCurrencyListing input[name='status']").val(status);
        $("#frmCurrencyListing").submit();
    };

	updateCurrencyRates = function(converterClass){
        if(!confirm(langLbl.updateCurrencyRates)){
            return false;
        }
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl(converterClass, 'update'), '', function(res) {
            var ans = $.parseJSON(res);
            if( ans.status == 1 ){
                $.systemMessage(ans.msg,'alert--success', false);
                reloadList();
			} else {
                $.systemMessage(ans.msg,'alert--danger', false);
                return;
			}
        });
    };

})();
