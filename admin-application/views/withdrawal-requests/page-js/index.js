$(document).ready(function(){
	searchListing(document.frmReqSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmReqSearchPaging;		
		$(frm.page).val(page);
		searchListing(frm);
	}

	reloadList = function() {
		var frm = document.frmReqSearch;
		searchListing(frm);
	}

	searchListing = function(form){
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}        
		if (!$(form).validate()) {
			return;
		}
		/*]*/
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('WithdrawalRequests','search'),data,function(res){
			$(dv).html(res);
		});
	};
	/*
	updateStatus = function(id,status,statusName){
		data = 'id='+id+'&status='+status;
		if(confirm(langLbl.DoYouWantTo+' '+statusName+' '+langLbl.theRequest)){
			fcom.updateWithAjax(fcom.makeUrl('WithdrawalRequests', 'updateStatus'), data, function(t) {
				reloadList();
			});
		}
	};
        * 
        */
        updateStatusForm = function(id){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('WithdrawalRequests', 'updateStatusForm', [id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};        
        setupStatus = function(frm,pluginName = ''){
		if (!$(frm).validate()) return;
                var data = fcom.frmData(frm);
                var status = frm.withdrawal_status.value;
                var id = frm.withdrawal_id.value; 
                var comment = frm.withdrawal_comments.value;
                if(status == transactionApprovedStatus && pluginName !=''){
                    requestOutside(pluginName,id,status,comment);
                    return;
                }else{
                    var url = fcom.makeUrl('WithdrawalRequests', 'setupUpdateStatus');
                }
                
		fcom.updateWithAjax(url, data, function(t) {
			reloadList();
			$(document).trigger('close.facebox');                    
		});
	};
	requestOutside = function(object, id, status,comment=''){
		data = 'id='+id+'&status='+status+'&comment='+comment;
		/*if(confirm(langLbl.DoYouWantTo+' '+statusName+' '+langLbl.theRequest)){ */
			fcom.updateWithAjax(fcom.makeUrl(object), data, function(t) {
				reloadList();
                                $(document).trigger('close.facebox');
			});
		/* } */
	};
	
	clearTagSearch = function(){
		document.frmReqSearch.reset();
		searchListing(document.frmReqSearch);
	};
        
        viewComment = function(id){
                $.facebox(function() {
                        fcom.ajax(fcom.makeUrl('WithdrawalRequests', 'viewComment', [id]), '', function(t) {
                                $.facebox(t,'faceboxWidth');
                        });
                });
	};        

})();
