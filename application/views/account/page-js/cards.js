$(document).ready(function() {});
(function() {
    var controller = 'Account';
    addNewCardForm = function() {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl(controller, 'addCardForm'), '', function(t) {
                $.facebox(t, 'medium-fb-width');
            });
        });

    };

    addNewCard = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        $.mbsmessage(langLbl.processing, false, 'alert--process');
        fcom.ajax(fcom.makeUrl(controller, 'setupNewCard'), data, function(t) {
            t = $.parseJSON(t);
            if (1 > t.status) {
                $.mbsmessage(t.msg, false, 'alert--danger');
                return false;
            }
            $.mbsmessage(t.msg, false, 'alert--success');
            location.reload();
        });
    };

    markAsDefault = function(cardId) {
        $('.savedCards-js .selected').removeClass('selected');
        $("input[value='" + cardId + "']").parents('.card-js').addClass('selected');
        fcom.ajax(fcom.makeUrl(controller, 'markAsDefault', [cardId]), '', function(t) {
            t = $.parseJSON(t);
            if (1 > t.status) {
                $.mbsmessage(t.msg, false, 'alert--danger');
                location.reload();
                return false;
            }
        });
    };

    removeCard = function(cardId) {
        if( !confirm(langLbl.confirmRemove) ){ return; }
        $.mbsmessage(langLbl.processing, false, 'alert--process');
        fcom.ajax(fcom.makeUrl(controller, 'removeCard', [cardId]), '', function(t) {
            t = $.parseJSON(t);
            if (1 > t.status) {
                $.mbsmessage(t.msg, false, 'alert--danger');
                return false;
            }
            $.mbsmessage.close();
            $("input[value='" + cardId + "']").parents('.card-js').remove();
            location.reload();
        });
    };
})();