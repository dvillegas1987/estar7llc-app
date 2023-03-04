$(document).ready(function(){

	searchUsers(document.frmUserSearch);

	$(document).on('click',function(){
		$('.autoSuggest').empty();
	});

	$('input[name=\'keyword\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Affiliate', 'autoCompleteJson'),
				data: {keyword: request['term'], fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] +'(' + item['username'] + ')', value: item['username'], name: item['id'] };
					}));
				},
			});
		},
		select: function (event, ui) {
			$("input[name='user_id']").val(ui.item.id);
		}
	});

	$('input[name=\'keyword\']').keyup(function(){
		$('input[name=\'user_id\']').val('');
	});

});

(function() {
	var currentPage = 1;
	var transactionUserId = 0;
	var rewardUserId = 0;

	goToSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmUserSearchPaging;
		$(frm.page).val(page);
		searchUsers(frm);
	};

	searchUsers = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;
		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/

		$("#usersListing").html(fcom.getLoader());

		fcom.ajax(fcom.makeUrl('Affiliate','userSearch'),data,function(res){
			$("#usersListing").html(res);
		});
	};
    goToUserSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmUserSrchPaging;
		$(frm.page).val(page);
		searchUsers(frm);
	}

	reloadUserList = function() {
		searchUsers(document.frmUserSearchPaging, currentPage);
	};
    clearSearch = function(){
		document.frmUserSearch.reset();
        $("input[name='user_id']").val("");
		searchUsers(document.frmUserSearch);
	};
})();
