$(document).ready(function () {
    $(document).on('click', 'ul.linksvertical li a.redirect--js', function (event) {
        event.stopPropagation();
    });
});
(function () {
    updatePayment = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Orders', 'updatePayment'), data, function (t) {
            window.location.reload();
        });
    };
    approve = function (orderPaymentId) {
        if( !confirm(langLbl.confirmUpdate) ){ return; }
        fcom.updateWithAjax(fcom.makeUrl('Orders', 'approvePayment', [orderPaymentId]), '', function (t) {
            location.reload();
        });
    };
    reject = function (orderPaymentId) {
        if( !confirm(langLbl.confirmUpdate) ){ return; }
        fcom.updateWithAjax(fcom.makeUrl('Orders', 'rejectPayment', [orderPaymentId]), '', function (t) {
            location.reload();
        });
    };
    viewPaymemntGatewayResponse = function(data){
        $.facebox(data, 'faceboxWidth');
    };
})();