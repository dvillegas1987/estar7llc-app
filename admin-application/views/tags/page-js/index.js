$(document).ready(function(){
	searchTags(document.frmTagSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmTagSearchPaging;
		$(frm.page).val(page);
		searchTags(frm);
	}

	reloadList = function() {
		var frm = document.frmTagSearchPaging;
		searchTags(frm);
	}
	addTagFormNew = function(id){
		$.facebox(function() {addTagForm(id)});

	}

	addTagForm = function(id) {
			fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('tags', 'form', [id]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setupTag = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Tags', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				addTagLangForm(t.tagId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	addTagLangForm = function(tagId, langId, autoFillLangData = 0) {
		fcom.displayProcessing();
//		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Tags', 'langForm', [tagId, langId, autoFillLangData]), '', function(t) {
				//$.facebox(t);
				fcom.updateFaceboxContent(t);
			});
//		});
	};

	setupTagLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Tags', 'langSetup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				addTagLangForm(t.tagId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	searchTags = function(form){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		$("#tagListing").html('Loading....');

		fcom.ajax(fcom.makeUrl('Tags','search'),data,function(res){
			$("#tagListing").html(res);
		});
	};

	addTagData = function(e){
        var product_id = $(e.detail.tagify.DOM.originalInput).attr('data-product_id');
        var tag_id = e.detail.tag.id;
        var tag_name = e.detail.tag.title;
        if(tag_id == ''){
            var data = 'tag_id=0&tag_identifier='+tag_name
            fcom.updateWithAjax(fcom.makeUrl('Tags', 'setup'), data, function(t) {                
                whitelist.push({'id':t.tagId,value:tag_name});
                var dataLang = 'tag_id='+t.tagId+'&tag_name='+tag_name+'&lang_id=0';
                fcom.updateWithAjax(fcom.makeUrl('Tags', 'langSetup'), dataLang, function(t2) {
                    fcom.updateWithAjax(fcom.makeUrl('Products', 'updateProductTag'), 'product_id='+product_id+'&tag_id='+t.tagId, function(t3) {
                         var tagifyId = e.detail.tag.__tagifyId;
                         $('[__tagifyid='+tagifyId+']').attr('id', t.tagId);
                     });
                });
            });
        }else{
            fcom.updateWithAjax(fcom.makeUrl('Products', 'updateProductTag'), 'product_id='+product_id+'&tag_id='+tag_id, function(t) { });
        }
    }

    removeTagData = function(e){
        var tag_id = e.detail.tag.id;
        var product_id = $(e.detail.tagify.DOM.originalInput).attr('data-product_id');
        fcom.updateWithAjax(fcom.makeUrl('Products', 'removeProductTag'), 'product_id='+product_id+'&tag_id='+tag_id, function(t) {
        });
    }

	deleteTagRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('Tags','deleteRecord'),data,function(res){
			reloadList();
		});
	};

	deleteSelected = function(){
		if(!confirm(langLbl.confirmDelete)){
			return false;
		}
		$("#frmTagsListing").attr("action",fcom.makeUrl('Tags','deleteSelected')).submit();
	};

	clearTagSearch = function(){
		document.frmTagSearch.reset();
		searchTags(document.frmTagSearch);
	};

})();
