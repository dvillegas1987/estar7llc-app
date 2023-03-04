$(document).ready(function(){
	searchAdminUsersRoles(document.frmPrmSrchFrm);
});
(function() {
	var runningAjaxReq = false;
	var dv = '#listing';
	
	reloadList = function() {
		var frm = document.frmPrmSrchFrm;
		searchAdminUsersRoles(frm);
	};	
	
	searchAdminUsersRoles = function(form){		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());		
		fcom.ajax(fcom.makeUrl('Seller','userRoles'),data,function(res){
			$(dv).html(res);			
		});
	};
	
	updatePermission = function(moduleId, permission){
		if(1 > moduleId) {
			if(!(permission = $("select[name='permissionForAll']").val()))
			{
				return false;
			}
		}
	
		data = fcom.frmData(document.frmPrmSrchFrm);				
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'updatePermission',[moduleId,permission]), data, function(t) {
			if(t.moduleId==0)
			{
				searchAdminUsersRoles(document.frmPrmSrchFrm);
			}
		});
	};
	
})();	
