(function () {
    sendPayment = function (frm, dv = '') {
        var data = fcom.frmData(frm);
        var action = $(frm).attr('action');
        data +='&chargeAjax=0';       
        fcom.ajax(action, data, function (t) {
            // debugger;
            try {
                var json = $.parseJSON(t);
                if (typeof json.status != 'undefined' && 1 > json.status) {
                    $.systemMessage(json.msg, 'alert--danger');
                    return false;
                }
                if (typeof json.html != 'undefined') {
                    $(dv).append(json.html);
                }
                if (json['redirect']) {
                    $(location).attr("href", json['redirect']);
                }
            } catch (e) {
                $(dv).append(t);
            }
        });
    };
})();