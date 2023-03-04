$(document).ready(function() {
    getAppThemeForm();
});

(function() {
    var currentPage = 1;
    var runningAjaxReq = false;
    var dv = '#frmBlock';
    getAppThemeForm = function() {
        fcom.resetEditorInstance();
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('MobileAppSettings', 'appThemeForm'), '', function(t) {
            $(dv).html(t);
            jscolor.installByClassName('jscolor');
        });
    };

    setupAppThemeSettings = function(frm) {
        if (!$(frm).validate()) {
            return;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('MobileAppSettings', 'setupAppTheme'), data, function(t) {
            getAppThemeForm(t.frmType);
            $(document).trigger('close.facebox');
        });
    }
})();