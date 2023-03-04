$(document).ready(function () {
    searchPlugin(1);
});

(function () {
    var dv = '#pluginsListing';

    reloadList = function () {
        $activeFormType = $('.tabs_nav_container ul.tabs_nav li a.active').data('formtype')
        searchPlugin($activeFormType);
    };

    searchPlugin = function (type) {
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Plugins', 'search', [type]), '', function (res) {
            $(dv).html(res);
        });
    };

    editPluginForm = function (pluginType, pluginId) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('Plugins', 'form', [pluginType, pluginId]), '', function (t) {
                fcom.updateFaceboxContent(t);
            });
        });
    };


    setupPlugin = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Plugins', 'setup'), data, function (t) {
            reloadList();
            if (t.langId > 0) {
                editPluginLangForm(t.pluginId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editPluginLangForm = function (pluginId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('Plugins', 'langForm', [pluginId, langId, autoFillLangData]), '', function (t) {
            fcom.updateFaceboxContent(t);
            fcom.setEditorLayout(langId);
        });
    };

    setupLangPlugin = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Plugins', 'langSetup'), data, function (t) {
            reloadList();
            if (t.langId > 0) {
                editPluginLangForm(t.pluginId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    editSettingForm = function (keyName) {
        fcom.displayProcessing();
        var data = 'keyName=' + keyName;
        fcom.ajax(fcom.makeUrl(keyName + 'Settings'), data, function (t) {
            var res = isJson(t);
            if (res && res.status == 0) {
                fcom.displayErrorMessage(res.msg);
            } else {
                $.facebox(function () {
                    fcom.updateFaceboxContent(t);
                });
            }
        });
    };

    setupPluginsSettings = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        var keyName = frm.keyName.value;
        fcom.updateWithAjax(fcom.makeUrl(keyName + 'Settings', 'setup'), data, function (t) {
            $(document).trigger('close.facebox');
        });
    };

    toggleStatus = function (obj, status) {
        if (!confirm(langLbl.confirmUpdateStatus)) { return; }
        var pluginId = parseInt(obj.id);
        if (pluginId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'pluginId=' + pluginId + "&status=" + status;
        fcom.ajax(fcom.makeUrl('Plugins', 'changeStatus'), data, function (res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                setTimeout(function(){ reloadList(); }, 200);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    changeStatusEitherPluginTypes = function (obj, status, msg) {
        msg = (0 < status) ? msg : langLbl.confirmUpdateStatus;
        if (!confirm(msg)) { return; }
        var pluginId = parseInt(obj.id);
        if (pluginId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'pluginId=' + pluginId + "&status=" + status;
        fcom.ajax(fcom.makeUrl('Plugins', 'changeStatusByType'), data, function (res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
            reloadList();
        });
    };
    syncCategories = function(){        
        fcom.updateWithAjax(fcom.makeUrl('PatchUpdate', 'updateTaxCategories'), '', function (t) {
        },{},false);
    }
})();

$(document).on('click', '.uploadFile-Js', function () {
    var node = this;
    $('#form-upload').remove();
    var pluginId = $(node).attr('data-plugin_id');
    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="pluginId" value="' + pluginId + '"/>');
    $('body').prepend(frm);
    $('#form-upload input[name=\'file\']').trigger('click');
    if (typeof timer != 'undefined') {
        clearInterval(timer);
    }
    timer = setInterval(function () {
        if ($('#form-upload input[name=\'file\']').val() != '') {
            clearInterval(timer);
            $val = $(node).val();
            $.ajax({
                url: fcom.makeUrl('Plugins', 'uploadIcon', [$('#form-upload input[name=\'pluginId\']').val()]),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $(node).val('Loading');
                },
                complete: function () {
                    $(node).val($val);
                },
                success: function (ans) {
                    $('.text-danger').remove();
                    $('#Plugin_icon').html(ans.msg);
                    if (ans.status == true) {
                        $(".tabs_nav a.active").click();
                    } else {
                        $('#Plugin_icon').removeClass('text-success');
                        $('#Plugin_icon').addClass('text-danger');
                        fcom.displayErrorMessage(ans.msg);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});

$(document).ajaxComplete(function () {
    /* StripeConnect */
        $('.StripeConnectPayoutInterval--js').trigger('change');
    /* StripeConnect */    
});

/* StripeConnect */
    $(document).on('change', '.StripeConnectPayoutInterval--js', function(){
        var payoutMonthlyEle = '.StripeConnectPayoutMonthDays--js';
        var payoutWeeklyEle = '.StripeConnectPayoutWeekly--js';
        var payoutDaysEle = '.StripeConnectPayoutDelayDays--js';
        if ('manual' == $(this).val() || '' == $(this).val()) {
            $(payoutMonthlyEle + ', ' + payoutWeeklyEle + ', ' + payoutDaysEle).val("").attr('disabled', 'disabled');
        } else if ('daily' == $(this).val()) {
            $(payoutDaysEle).removeAttr('disabled');
            $(payoutWeeklyEle + ", " + payoutMonthlyEle).val("").attr('disabled', 'disabled');
        } else if ('monthly' == $(this).val()) {
            $(payoutMonthlyEle).removeAttr('disabled');
            $(payoutDaysEle + ", " + payoutWeeklyEle).val("").attr('disabled', 'disabled');
        } else if ('weekly' == $(this).val()) {
            $(payoutWeeklyEle).removeAttr('disabled');
            $(payoutDaysEle + ", " + payoutMonthlyEle).val("").attr('disabled', 'disabled');
        }
    });
/* StripeConnect */