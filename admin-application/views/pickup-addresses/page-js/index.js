$(document).ready(function () {
    searchAddresses();
});

(function () {
    var dv = '#listing';

    searchAddresses = function () {
        var data = '';
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('PickupAddresses', 'search'), data, function (res) {
            $(dv).html(res);
            var oldLabel = $(".label-js").data("listlabel");
            $(".label-js").text(oldLabel);
            $(".js-pickup-addr").addClass('d-none');
            $(".js-add-pickup-addr").removeClass('d-none');
        });
    };

    addAddressForm = function (id, langId) {
        var data = 'langId=' + langId;
        fcom.ajax(fcom.makeUrl('PickupAddresses', 'form', [id, langId]), data, function (res) {
            $(dv).html(res);
            var oldLabel = $(".label-js").text();
            $(".label-js").attr("data-listlabel", oldLabel).text(langLbl.pickupAddressForm);
            $(".js-add-pickup-addr").addClass('d-none');
            $(".js-pickup-addr").removeClass('d-none');
            setTimeout(function () { $('.fromTime-js').change(); }, 500);
        });

    };

    setup = function (frm) {
        if (!$(frm).validate()) return;
        if (1 == $(".availabilityType-js:checked").val()) {
            if (1 > $(".slotDays-js:checked").length) {
                $.mbsmessage(langLbl.selectTimeslotDay, true, 'alert--danger');
                return false;
            }
        } else {
            if ('' == $(".selectAllFromTime-js option:selected").val() || '' == $(".selectAllToTime-js option:selected").val()) {
                $.mbsmessage(langLbl.invalidTimeSlot, true, 'alert--danger');
                return false;
            }
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('PickupAddresses', 'setup'), data, function (t) {
            searchAddresses();
        });
    };

    deleteRecord = function (id) {
        if (!confirm(langLbl.confirmDelete)) { return; }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('PickupAddresses', 'deleteRecord'), data, function (res) {
            searchAddresses();
        });
    };

    getCountryStates = function (countryId, stateId, div, langId) {
        fcom.ajax(fcom.makeUrl('Shops', 'getStates', [countryId, stateId, langId]), '', function (res) {
            $(div).empty();
            $(div).append(res);
        });
    };


    addTimeSlotRow = function (day) {
        var fromTimeHtml = $(".js-from_time_" + day).html();
        var toTimeHtml = $(".js-to_time_" + day).html();
        var count = $('.js-slot-individual .row').length;
        var toTime = $(".js-slot-to-" + day + ":last").val();
        var rowElement = ".js-slot-individual .row-" + count;

        var addRowBtn = $('.js-slot-add-' + day);
        if (0 < addRowBtn.closest('.field-set').length) {
            addRowBtn.remove();
            addRowBtn.closest('.field-set').remove();
        }

        if (0 < $('.addRowBtn' + day + '-js').length) {
            $('.addRowBtn' + day + '-js').remove();
        }        

        var addRowBtnHtml = '<input class="addRowBtn' + day + '-js js-slot-add-' + day + ' d-none" onclick="addTimeSlotRow(' + day + ')" type="button" name="btn_add_row[' + day + ']" value="+">';
        var html = "<div class='row jsDay-"+ day + " row-" + count + " js-added-rows-" + day + "'><div class='col-md-2 jsWeekDay"+ ($(".availabilityType-js:checked").val() == 2 ?' d-none':'')   +" '></div><div class='col-md-4 js-from_time_" + day + "'>" + fromTimeHtml + "</div><div class='col-md-4 js-to_time_" + day + "'>" + toTimeHtml + "</div><div class='col-md-2'><div class='field-set'><div class='caption-wraper'><label class='field_label'></label></div><div class='field-wraper'><div class='field_cover'><input class='' type='button' data-day='" + day + "' name='btn_remove_row' value='x'>" + addRowBtnHtml + "</div></div></div></div></div>";
        $(".js-from_time_" + day).last().parent().after(html);
        $(rowElement + " select").val('').attr('data-row', (count));
        var frmElement = rowElement + " .js-slot-from-" + day;

        $(frmElement + " option").removeClass('d-none');
        $(frmElement + " option").each(function () {
            var toVal = $(this).val();
            if (toVal != '' && toVal <= toTime) {
                $(this).addClass('d-none');
            }
        });
    }

    displayFields = function (day, ele) {
        if ($(ele).prop("checked") == true) {
            $(".js-slot-from-" + day).removeAttr('disabled');
            $(".js-slot-to-" + day).removeAttr('disabled');
            $(".addRowBtnBlock"+day+"-js").removeClass('d-none');
            displayAddRowField(day, ele);
        } else {
            $(".js-slot-from-" + day).attr('disabled', 'true');
            $(".js-slot-to-" + day).attr('disabled', 'true');
            $(".js-slot-add-" + day).addClass('d-none');
            /*$(".js-added-rows-" + day).remove();*/
            $(".jsDay-" + day).find("[name='btn_remove_row']").trigger('click');
            $(".addRowBtnBlock"+day+"-js").addClass('d-none');
            
        }
    }

    displayAddRowField = function (day, ele) {
        var index = $(ele).data('row');
        var rowElement = ".js-slot-individual .row-" + index;
        var frmElement = rowElement + " .js-slot-from-" + day;
        var toElement = rowElement + " .js-slot-to-" + day;

        var fromTime = $(frmElement + " option:selected").val();
        var toTime = $(toElement + " option:selected").val();

        var toElementIndex = $(rowElement).index();
        var nextRowElement = ".js-slot-individual .row:eq(" + (toElementIndex + 1) + ")";
        var nextFrmElement = nextRowElement + " .js-slot-from-" + day;

        if (0 < $(nextFrmElement).length) {
            $(nextFrmElement + " option").removeClass('d-none');
            var nxtFrmSelectedVal = $(nextFrmElement + ' option:selected').val();
            if (nxtFrmSelectedVal <= toTime) {
                $(".js-slot-from-" + day).each(function () {
                    if (index < $(this).data('row') && $(this).val() <= toTime) {
                        var nxtRow = $(this).data('row');
                        $(this).val("");
                        $(".js-slot-individual .row-" + nxtRow + " .js-slot-to-" + day).val("");
                        $("option", this).each(function () {
                            var optVal = $(this).val();
                            if (optVal != '' && optVal <= toTime) {
                                $(this).addClass('d-none');
                            }
                        });
                    }
                });
            }
            $(nextFrmElement + " option").each(function () {
                var nxtFrmVal = $(this).val();
                if (nxtFrmVal != '' && nxtFrmVal <= toTime) {
                    $(this).addClass('d-none');
                }
            });
        }

        if (fromTime == '' && toTime != '') {
            $(toElement).val("");
            $.mbsmessage(langLbl.invalidFromTime, true, 'alert--danger');
            return false;
        }

        if (toTime != '' && toTime <= fromTime) {
            $(toElement).val('').addClass('error');
            var toTime = $(toElement).children("option:selected").val();
        } else {
            $(toElement).removeClass('error');
        }

        $(toElement + " option").removeClass('d-none');
        $(toElement + " option").each(function () {
            var toVal = $(this).val();
            if (toVal != '' && toVal <= fromTime) {
                $(this).addClass('d-none');
            }
        });
        
        var toTimeLastOpt = $(toElement + " option:last").val();

        if (fromTime != '' && toTime != '' && toTime <  toTimeLastOpt) {
            $(rowElement + " .js-slot-add-" + day).removeClass('d-none');
        } else {
            $(rowElement + " .js-slot-add-" + day).addClass('d-none');
        }

    }

    displaySlotTimings = function(ele) {
            var selectedVal = $(ele).val();
            if (selectedVal == 2) {
                $('.js-slot-individual .row').addClass('d-none');
                $('.js-slot-individual .jsDay-' + DAY_SUNDAY + ' .jsWeekDay').addClass('d-none');
                $('.js-slot-individual .jsDay-' + DAY_SUNDAY).removeClass('d-none');             
                $('.js-slot-individual .jsDay-' + DAY_SUNDAY + ' .jsWeekDay input').prop("checked",true).trigger('change');              
            } else {              
                $('.js-slot-individual .row').removeClass('d-none');
                $('.js-slot-individual .jsDay-' + DAY_SUNDAY + ' .jsWeekDay').removeClass('d-none');
            }
    }

    validateTimeFields = function () {
        var from_time = $("[name='tslot_from_all']").children("option:selected").val();
        var to_time = $("[name='tslot_to_all']").children("option:selected").val();

        $("[name='tslot_to_all'] option").removeClass('d-none');
        $("[name='tslot_to_all'] option").each(function () {
            var toVal = $(this).val();
            if (toVal != '' && toVal <= from_time) {
                $(this).addClass('d-none');
            }
        });

        if (to_time != '' && to_time <= from_time) {
            $("[name='tslot_to_all']").val('').addClass('error');
        } else {
            $("[name='tslot_to_all']").removeClass('error');
        }
    }

})();

$(document).on("click", "[name='btn_remove_row']", function () {   
    var day = $(this).data('day');    
    
    //$(this).parent().parent('.row').remove();
    $(this).parentsUntil('.row').parent().remove();

    if (0 < $('.js-added-rows-' + day + ':last [name="btn_remove_row"]').length) {
        var addRowBtnHtml = '<input class="addRowBtn' + day + '-js js-slot-add-' + day + '" onclick="addTimeSlotRow(' + day + ')" type="button" name="btn_add_row[' + day + ']" value="+">';
        if (0 < $('.js-added-rows-' + day + ':last .addRowBtn' + day + '-js').length) {
            $('.js-added-rows-' + day + ':last [name="btn_remove_row"]').after(addRowBtnHtml);
        }
    } else if (0 < $('.addRowBtnBlock' + day + '-js').length) {
        var addRowBtnHtml = '<input class="addRowBtn' + day + '-js js-slot-add-' + day + ' mt-4" onclick="addTimeSlotRow(' + day + ')" type="button" name="btn_add_row[' + day + ']" value="+">';
        $('.addRowBtnBlock' + day + '-js').html(addRowBtnHtml);
    }
})
