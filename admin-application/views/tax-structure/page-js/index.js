$(document).ready(function() {
    searchTaxStructure();
});
(function() {
    var dv = '#taxStrListing';
    searchTaxStructure = function() {
        var data = '';
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('TaxStructure', 'search'), '', function(res) {
            $(dv).html(res);
        });
    };

    addStructureForm = function(id){
        $.facebox(function() {
            structureForm(id);
        });
    };

    reloadList = function() {
		searchTaxStructure();
	}

    structureForm = function(id){
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('TaxStructure', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupTaxStructure = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('TaxStructure', 'setup'), data, function(t) {
            reloadList();
            $(document).trigger('close.facebox');
        });
    };

    
    translateData = function(item){
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var defaultLang = $(item).attr('defaultLang');
        var taxstrName = $("input[name='taxstr_name["+defaultLang+"]']").val();
        var toLangId = $(item).attr('language');
        var alreadyOpen = $('#collapse_'+toLangId).hasClass('active');
        if(autoTranslate == 0 || taxstrName == "" || alreadyOpen == true){
            return false;
        }
        var data = "taxstrName="+taxstrName+"&toLangId="+toLangId ;
        fcom.updateWithAjax(fcom.makeUrl('TaxStructure', 'translatedData'), data, function(t) {
            if(t.status == 1){
                $("input[name='taxstr_name["+toLangId+"]']").val(t.taxstrName);
            }
        });
    }

})();

$(document).ready(function() {
	$('body').on('change', 'input[name="taxstr_is_combined"]', function() {
		if ($(this). prop("checked") == true) {
			$('#combinedTax-js').show();
			$('#combinedTaxLang-js').show();
		} else {
			$('#combinedTax-js').hide();
			$('#combinedTaxLang-js').hide();
		}
	});
});