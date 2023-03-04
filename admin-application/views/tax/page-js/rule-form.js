$("document").ready(function() {
	$('select[name="taxrule_taxstr_id[]"]').each(function() {
		$(this).trigger('change');
    });

	$('body').on('change', 'select[name="taxruleloc_type[]"]', function() {
		var parentIndex = $(this).parents('.tax-rule-form--js').data('index');
		var dv = '.tax-rule-form-'+ parentIndex + ' .selectpicker';
		if ($(this).val() == -1) {
			$(dv).selectpicker('val', -1);
			$(dv).attr('disabled', true);
			$(dv + " option[value='-1']").show();
		} else {
			$(dv).removeAttr('disabled');
			$(dv).selectpicker('val', "");
			$(dv + " option[value='-1']").hide();
		}
		$(dv).selectpicker('refresh');
	});
        
        
        $(document).on('change', 'select.selectpicker', function() {
                if($(this).val().length > 0){
                    $(this).siblings('button').removeClass('error');
                }else{
                    $(this).siblings('button').addClass('error');
                }
		
	});

});


(function() {
	setupTaxRule = function(frm) {
        if (!$(frm).validate()){
            $('.selectpicker').each(function(){
                if($(this).hasClass('error')){
                    $(this).siblings('button').addClass('error');
                }
            })
            
            return;            
        } 
		/* var data = fcom.frmData(frm); */
		var dataToSave = [];
		$(".tax-rule-form--js").each(function(index, data) {
			var myIndex = $(data).data('index');
			var className = '.tax-rule-form-'+ myIndex;
			var taxrule_id = $(className + ' input[name="taxrule_id[]"]').val();
			var taxrule_name = [];
			for (var key in langLbl['languages']) {
				taxrule_name[key] = $(className + ' input[name="taxrule_name['+key+'][]"]').val();
			}

			var taxrule_taxstr_id = $(className + ' select[name="taxrule_taxstr_id[]"]').val();
			var type = $(className + ' select[name="taxruleloc_type[]"]').val();
			var taxrule_rate = $(className + ' input[name="taxrule_rate[]"]').val();
			var country_id = $(className + ' select[name="taxruleloc_country_id[]"]').val();
			var type = $(className + ' select[name="taxruleloc_type[]"]').val();
			var states = $(className + ' select[name="taxruleloc_state_id[]"]').val();
			if (type == -1) {
				var states = [-1];
			}

			var taxrule_is_combined = 0;
			var combinedTax = [];

			
			if($(className + ' input[name="taxstr_id[]"]').length) {
				var taxrule_is_combined = 1;
				$(className +" .rule-detail-row--js").each(function(currentIndex, detailData) {
					var taxruledet_id = $(detailData).find(' input[name="taxruledet_id[]"]').val();
					var taxruledet_taxstr_id = $(detailData).find(' input[name="taxstr_id[]"]').val();
					var taxruledet_rate = $(detailData).find(' input[name="taxruledet_rate[]"]').val();
					if( !taxruledet_rate ) {
						return;
					}
					var details = {"taxruledet_id" : taxruledet_id, "taxruledet_taxstr_id" : taxruledet_taxstr_id, "taxruledet_rate" : taxruledet_rate};

					combinedTax.push(details);
				});
			} else {
				var taxruledet_id = 0;
				var details = {"taxruledet_id" : taxruledet_id, "taxruledet_taxstr_id" : taxrule_taxstr_id, "taxruledet_rate" : taxrule_rate};
				combinedTax.push(details);
			}
			var currentData = {"taxrule_id" : taxrule_id, "taxrule_name" : taxrule_name, "taxrule_taxstr_id" : taxrule_taxstr_id, "taxrule_rate" : taxrule_rate, "country_id" : country_id, "type" : type, "states" : states, "taxrule_is_combined" : taxrule_is_combined, "combinedTaxDetails" : combinedTax};
			dataToSave.push(currentData);
		});

		//var data = fcom.frmData(frm);
		var taxCatId = $('input[name="taxcat_id"]').val();
		var groupDetails = {"taxcat_id" : taxCatId, "taxcat_name" : $('input[name="taxcat_name"]').val(), "taxgrp_description": $('textarea[name="taxgrp_description"]').val(), "rules" : dataToSave};

        fcom.updateWithAjax(fcom.makeUrl('tax', 'setupTaxRule'), groupDetails, function(t) {
			if (t.status == 1) {
				if (taxCatId <= 0) {
					window.location.replace(fcom.makeUrl('tax', 'ruleForm',[taxCatId]));
				} else {
					window.location.reload();
				}
            }
        });
    };
    getCountryStatesTaxInTaxForm = function(currentSel, countryId, stateId) {    
	var parentIndex = $(currentSel).parents('.tax-rule-form--js').data('index');
	var dv = '.tax-rule-form-'+ parentIndex + ' .selectpicker';	
    $(dv).empty();
    var firstChild = '<option value = "-1" >All</option>';
    $(dv).append(firstChild);
    if(countryId == -1) {       
        $(dv).attr('disabled', true);
        $('.tax-rule-form-'+ parentIndex + ' select[name="taxruleloc_type[]"]').val(-1);
        $('.tax-rule-form-'+ parentIndex + ' select[name="taxruleloc_type[]"]').attr('disabled', true);
        $(dv).val(-1);
        $(dv).selectpicker('refresh');
        return;
    }
    fcom.displayProcessing();    
    fcom.ajax(fcom.makeUrl('Users', 'getStates', [countryId, stateId]), '', function(res) {
		var locationFld = '.tax-rule-form-'+ parentIndex + ' select[name="taxruleloc_type[]"]';
		$(dv).removeAttr('disabled');
		$(locationFld).removeAttr('disabled');
		$(dv).append(res);
                $(dv).find("option[value='-1']:eq(1)").remove();
		$(dv).selectpicker('refresh');
		if ('' == countryId) {
			$(locationFld).val($(locationFld + " option:first").val()).attr('disabled', 'disabled');
		} else if ('' == $(locationFld).val()) {
			$(locationFld).val($(locationFld + " option:eq(1)").val()); 
                        $(locationFld).trigger('change');                        
		}else if(-1 == $(locationFld).val()){
                    $(locationFld).trigger('change');
                }
                
    });
    $.systemMessage.close();
};
    
})();

function checkStatesDefault(parentIndex, countryId, stateIds) {    
	var dv = '.tax-rule-form-'+ parentIndex + ' .selectpicker';
	fcom.ajax(fcom.makeUrl('Users', 'getStates', [countryId, 0]), '', function(res) {
		$(dv).empty();
		var firstChild = '<option value = "-1" >All</option>';
		$(dv).append(firstChild);
		$(dv).append(res);
                $(dv).find("option[value='-1']:eq(1)").remove();
		$(dv).selectpicker('val', stateIds);
		if (stateIds.indexOf("-1") > -1 ) {
			$(dv).attr('disabled', true);
		}
		$(dv).selectpicker('refresh');
	});
}



function getCombinedTaxes(currentSel, taxStrId) {
	var parentIndex = $(currentSel).parents('.tax-rule-form--js').data('index');
	var className = '.tax-rule-form-'+ parentIndex;
	var taxruleId = $(className + ' input[name="taxrule_id[]"]').val();
	if (taxStrId == 0) {
		$('.tax-rule-form-'+ parentIndex +' .combined-tax-details--js').html('');
		return;
	}
	fcom.ajax(fcom.makeUrl('Tax','getCombinedTaxes', [taxStrId, taxruleId]), '', function(t) {
        $('.tax-rule-form-'+ parentIndex +' .combined-tax-details--js').html(t);
        $('.combinetaxvalue--js').on("keyup", function(){
            if ('' == $(this).val()) {
                $(this).val(0);
            }
        });
	});
};
