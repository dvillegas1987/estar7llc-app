$(document).ready(function () {
    searchStpls(document.frmStplsSearch);
    recordInfoSection();

    $(document).on('keyup', "textarea[name='stpl_body']", function(e){
        if (160 < $(this).val().length) {
            e.preventDefault();
            return false;
        }
    });
});

(function () {
    var currentPage = 1;
    var dv = '#listing';
    var tempDv = '#templateDetail';

    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmStplsSrchPaging;
        $(frm.page).val(page);
        searchStpls(frm);
    };

    reloadList = function () {
        var frm = document.frmStplsSrchPaging;
        searchStpls(frm);
    };

    recordInfoSection = function () {
        $(tempDv + " .sectionbody").html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('SmsTemplates', 'recordInfoSection'), '', function (t) {
            $(tempDv).html(t);
        });
    };

    searchStpls = function (form) {
        /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('SmsTemplates', 'search'), data, function (res) {
            $(dv).html(res);
        });
    };

    detailSection = function (stplCode, langId, autoFillLangData = 0) {
        $(tempDv + " .sectionbody").html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('SmsTemplates', 'editTemplate', [stplCode, langId, autoFillLangData]), '', function (t) {
            $(tempDv).html(t);
        });
    };

    setup = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SmsTemplates', 'setup'), data, function (t) {
            reloadList();
        });
    };
    
    makeInActive = function (obj) {
        toggleStatus(obj, 'makeInActive');
    }

    makeActive = function (obj) {
        toggleStatus(obj, 'makeActive');
    }

    toggleStatus = function (obj, action) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var stplCode = obj.id;
        if (stplCode == '') {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'stplCode=' + stplCode;
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SmsTemplates', action), data, function (res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                action = 'makeActive' == action ? 'makeInActive' : 'makeActive';
                $(obj).toggleClass("active").removeAttr('onclick').attr('onclick', action + '(this)');
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
        $.systemMessage.close();
    };

    clearSearch = function () {
        document.frmStplsSearch.reset();
        searchStpls(document.frmStplsSearch);
    };
})();
