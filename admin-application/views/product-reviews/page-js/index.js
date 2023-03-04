$(document).ready(function(){
	searchProductReviews(document.frmSearch);
	
	$('input[name=\'reviewed_for\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {		
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request['term'], user_is_supplier: 1, fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'], value: item['name'], id: item['id'] };
					}));
				},
			});
		},
		select: function(event, ui) {
			console.log('select');
			$("input[name='reviewed_for_id']").val( ui.item.id );
		}
	});
	$('input[name=\'reviewed_for\']').keydown(function(){		
		if(event.keyCode == 13) {
			event.preventDefault();
		}else {
			$('input[name=\'reviewed_for_id\']').val('');
		}	
		
	});
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmReviewSearchPaging;		
		$(frm.page).val(page);
		searchProductReviews(frm);
	}

	reloadList = function() {
		var frm = document.frmReviewSearchPaging;
		searchProductReviews(frm);
	}

	searchProductReviews = function(form){		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('ProductReviews','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	viewReview = function(reviewId){			
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('ProductReviews', 'view', [reviewId]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	updateStatus = function(frm){
		if (!$(frm).validate()) return;	
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ProductReviews', 'updateStatus'), data, function(t) {
			reloadList();
			$(document).trigger('close.facebox');
			//viewReview(t.spreviewId);
		});
	};
	
	clearSearch = function(){
		document.frmSearch.reset();
        $('input[name="reviewed_for_id"]').val('');
		searchProductReviews(document.frmSearch);
	};	

})();