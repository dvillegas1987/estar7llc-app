$(document).ready(function() {
    searchZone(document.frmZoneSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmZoneSearchPaging;
        $(frm.page).val(page);
        searchZone(frm);
    }

    reloadList = function() {
        var frm = document.frmZoneSearchPaging;
        searchZone(frm);
    };

    searchZone = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Zones', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };
    addZoneForm = function(id) {
        $.facebox(function() {
            zoneForm(id);
        });
    };

    zoneForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Zones', 'form', [id]), '', function(t) {
            $.facebox(t, 'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
    };

    editZoneFormNew = function(id) {
        $.facebox(function() {
            editZoneForm(id);
        });
    };

    editZoneForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Zones', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupZone = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Zones', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editZoneLangForm(t.zoneId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    editZoneLangForm = function(id, langId ,autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Zones', 'langForm', [id, langId ,autoFillLangData]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupLangZone = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Zones', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editZoneLangForm(t.zoneId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    toggleStatus = function(e, obj, canEdit) {
        if (canEdit == 0) {
            e.preventDefault();
            return;
        }
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var zoneId = parseInt(obj.value);
        if (zoneId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'zoneId=' + zoneId;
        fcom.ajax(fcom.makeUrl('Zones', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $.fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
            }
        });
    };

    toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmZoneListing input[name='status']").val(status);
        $("#frmZoneListing").submit();
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchZone(document.frmSearch);
    };
})();