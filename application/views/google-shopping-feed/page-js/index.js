var keyName = 'GoogleShoppingFeed';
$(document).ready(function() {
    batchForm();
    search();
});

(function() {
	var dv = '#listing';
	var batchSetup = '#batchSetup';
    
	search = function(){
        if (1 > $(dv).length) {
            return false;
        }
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl(keyName,'search'),'',function(res){
			$(dv).html(res);
		});
    };
    
	batchForm = function(adsBatchId = 0){
        if (1 > $(batchSetup).length) {
            return false;
        }
        $(batchSetup).html(fcom.getLoader());
        $('html, body').animate({scrollTop: $(batchSetup).offset().top - 150 }, 'slow');
		fcom.ajax(fcom.makeUrl(keyName, 'batchForm', [adsBatchId]),'',function(res){
            $(batchSetup).html(res);
            $('.date_js').datepicker('option', {
                minDate: new Date()
            });
		});
    };
    
	serviceAccountForm = function(){
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl(keyName, 'serviceAccountForm'),'',function(res){
                $.facebox(res,'faceboxWidth');
            });
		});
    };
    
    clearForm = function() {
        batchForm();
    };


	setupBatch = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl(keyName, 'setupBatch'), data, function(t) {
            batchForm();
            search();
		});
    }
    
    deleteBatch = function (adsBatchId){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl(keyName, 'deleteBatch', [adsBatchId]), '', function(t) {
            search();
		});
    }

    setuppluginform = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl(keyName, 'setupServiceAccountForm'), data, function(t) {
            $(document).trigger('close.facebox');
            location.reload();
        });
    }

    publishBatch = function (adsBatchId) {
        $.mbsmessage(langLbl.processing,true,'alert--process alert');   
		fcom.updateWithAjax(fcom.makeUrl(keyName, 'publishBatch', [adsBatchId]), '', function(t) {
            if( t.status == 1 ){
				$.mbsmessage(t.msg, true, 'alert--success');
			} else {
                $.mbsmessage(t.msg, true, 'alert--danger');
            }
            search();
        });
    }
})();