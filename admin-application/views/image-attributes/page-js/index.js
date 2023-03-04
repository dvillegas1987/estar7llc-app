$(document).ready(function() {
    searchUrls(document.frmSearch);
});
(function() {
    var currentPage = 1;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmImgAttrPaging;
        $(frm.page).val(page);
        searchUrls(frm);
    };

    reloadList = function() {
        var frm = document.frmImgAttrPaging;
        searchUrls(frm);
    };

    searchUrls = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('ImageAttributes', 'search'), data, function(res) {
            $(dv).html(res);
			$("#dvForm").hide();
			$("#dvAlert").show();
        });
    };


    setup = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ImageAttributes', 'setup'), data, function(t) {
            /* reloadList(); */
        });
    };
	
	attributeForm = function(record_id){
		fcom.ajax(fcom.makeUrl('ImageAttributes', 'attributeForm', [record_id, moduleType]), '', function(t) {
			$("#dvForm").html(t).show();
			$("#dvAlert").hide();
		});
	};	
	
	discardForm = function(){
		$("#dvForm").hide();
		$("#dvAlert").show();
	};	
    
    clearSearch = function() {
        document.frmSearch.reset();
        searchUrls(document.frmSearch);
    };

})();

$(document).on('change','.language-js',function(){
    var langId = $(this).val();
    var recordId = $('#frmImgAttribute input[name=record_id]').val();
    var module = $('#frmImgAttribute input[name=module_type]').val();
    var option_id = $('.option-js').length ? $('.option-js').val() : 0 ;
    fcom.ajax(fcom.makeUrl('ImageAttributes', 'attributeForm', [recordId, module, langId ,option_id]), '', function(t) {
		$("#dvForm").html(t);
		$('#frmImgAttribute input[name=lang_id]').val(langId);
	});
});

$(document).on('change','.option-js',function(){
    var option_id = $(this).val();
    var recordId = $('#frmImgAttribute input[name=record_id]').val();
    var module = $('#frmImgAttribute input[name=module_type]').val();
    var langId = $('.language-js').val() || 0;
    fcom.ajax(fcom.makeUrl('ImageAttributes', 'attributeForm', [recordId, module, langId ,option_id]), '', function(t) {
        $("#dvForm").html(t);
        $('#frmImgAttribute input[name=lang_id]').val(langId);
    });
});
