<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'pickupAddressFrm');
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('onsubmit', 'setPickupAddress(this); return(false);');

$addrLabelFld = $frm->getField('addr_title');
$addrLabelFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_E.g:_My_Office_Address', $siteLangId));

$countryFld = $frm->getField('addr_country_id');
$countryFld->setFieldTagAttribute('id', 'addr_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,' . $stateId . ',\'#addr_state_id\')');

$stateFld = $frm->getField('addr_state_id');
$stateFld->setFieldTagAttribute('id', 'addr_state_id');

$slotTypeFld = $frm->getField('tslot_availability');
$slotTypeFld->setOptionListTagAttribute('class', 'list-inline');
$slotTypeFld->developerTags['rdLabelAttributes'] = array('class' => 'radio');
$slotTypeFld->developerTags['rdHtmlAfterRadio'] = '<i class="input-helper"></i>';
$slotTypeFld->setFieldTagAttribute('onChange', 'displaySlotTimings(this);');
$slotTypeFld->setFieldTagAttribute('class', 'availabilityType-js');


$cancelFld = $frm->getField('btn_cancel');
$cancelFld->setFieldTagAttribute('class', 'btn btn-outline-brand');
$cancelFld->developerTags['col'] = 2;
$cancelFld->developerTags['noCaptionTag'] = true;

$btnSubmit = $frm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
$btnSubmit->developerTags['col'] = 2;
$btnSubmit->developerTags['noCaptionTag'] = true;

$variables = array('language' => $language, 'siteLangId' => $siteLangId, 'shop_id' => $shop_id, 'action' => $action);
$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false); ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Shop_Pickup_Addresses', $siteLangId); ?></h5>
        <div class="btn-group">
            <a href="javascript:void(0)" onClick="pickupAddress()" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back', $siteLangId);?></a>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <?php echo $frm->getFormTag(); ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_title');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_title'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_name');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_name'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_address1');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_address1'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_address2');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_address2'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_country_id');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_country_id'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_state_id');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_state_id'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_city');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_city'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_zip');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_zip'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_phone');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php 
                                        echo $frm->getFieldHtml('addr_phone'); 
                                        echo $frm->getFieldHtml('addr_phone_dcode'); 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('tslot_availability');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('tslot_availability'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="js-slot-individual">
                    <?php
                    $daysArr = TimeSlot::getDaysArr($siteLangId);
                    $row = 0;
                    for ($i = 0; $i < count($daysArr); $i++) {
                        $dayFld = $frm->getField('tslot_day[' . $i . ']');
                        $dayFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
                        $dayFld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';
                        $dayFld->setFieldTagAttribute('onChange', 'displayFields(' . $i . ', this)');
                        $dayFld->setFieldTagAttribute('class', 'slotDays-js');

                        if (!empty($slotData) && isset($slotData['tslot_day'][$i])) {
                            $dayFld->setFieldTagAttribute('checked', 'true');
                            foreach ($slotData['tslot_from_time'][$i] as $key => $time) {
                                $fromTime = date('H:i', strtotime($time));
                                $toTime = date('H:i', strtotime($slotData['tslot_to_time'][$i][$key]));

                                $fromFld = $frm->getField('tslot_from_time[' . $i . '][]');
                                $fromFld->setFieldTagAttribute('class', 'js-slot-from-' . $i . ' fromTime-js');
                                $fromFld->setFieldTagAttribute('data-row', $row);
                                $fromFld->setFieldTagAttribute('onChange', 'displayAddRowField(' . $i . ', this)');
                                $fromFld->value = $fromTime;

                                $toFld = $frm->getField('tslot_to_time[' . $i . '][]');
                                $toFld->setFieldTagAttribute('class', 'js-slot-to-' . $i);
                                $toFld->setFieldTagAttribute('data-row', $row);
                                $toFld->setFieldTagAttribute('onChange', 'displayAddRowField(' . $i . ', this)');
                                $toFld->value = $toTime;
                    ?>
                                <div class="row jsDay-<?php echo $i;?> row-<?php echo $row;
                                                            echo ($key > 0) ? ' js-added-rows-' . $i : '' ?>">
                                    <div class="col-md-2 jsWeekDay">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php
                                                    if ($key == 0) {
                                                        echo $frm->getFieldHtml('tslot_day[' . $i . ']');
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 js-from_time_<?php echo $i; ?>">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php $fld = $frm->getField('tslot_from_time[' . $i . '][]');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('tslot_from_time[' . $i . '][]'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 js-to_time_<?php echo $i; ?>">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php $fld = $frm->getField('tslot_to_time[' . $i . '][]');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('tslot_to_time[' . $i . '][]'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 addRowBtnBlock<?php echo $i; ?>-js">                                  
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                    </label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php if ($key != 0) {  ?>
                                                                <button class="btn btn-outline-brand btn-sm" type="button" name="btn_remove_row" data-day="<?php echo $i; ?>"><i class="fas fa-minus"></i></button>
                                                        <?php }                                                        
                                                        if (count($slotData['tslot_from_time'][$i]) - 1 == $key) { ?>
                                                                <button type="button" name="btn_add_row[<?php echo $i; ?>]" onClick="addTimeSlotRow(<?php echo $i; ?>)" class="btn btn-brand btn-sm js-slot-add-<?php echo $i; ?>"><i class="fas fa-plus"></i></button>
                                                        <?php } ?>                                                        
                                                    </div>
                                                </div>
                                            </div>                                       
                                    </div>
                                </div>
                            <?php
                                $row++;
                            }
                        } else {
                            $fromFld = $frm->getField('tslot_from_time[' . $i . '][]');
                            $fromFld->setFieldTagAttribute('disabled', 'true');
                            $fromFld->setFieldTagAttribute('data-row', $row);
                            $fromFld->setFieldTagAttribute('class', 'js-slot-from-' . $i . ' fromTime-js');
                            $fromFld->setFieldTagAttribute('onChange', 'displayAddRowField(' . $i . ', this)');

                            $toFld = $frm->getField('tslot_to_time[' . $i . '][]');
                            $toFld->setFieldTagAttribute('disabled', 'true');
                            $toFld->setFieldTagAttribute('data-row', $row);
                            $toFld->setFieldTagAttribute('class', 'js-slot-to-'.$i);
                            $toFld->setFieldTagAttribute('onChange', 'displayAddRowField('.$i.', this)');
							?>
                             <div class="row jsDay-<?php echo $i;?> row-<?php echo $row; ?>">
                                    <div class="col-md-2 jsWeekDay">
                                            <div class="field-set">
                                                    <div class="caption-wraper">
                                                       <label class="field_label">
                                                       </label>
                                                    </div>
                                                    <div class="field-wraper">
                                                            <div class="field_cover">
                                                            <?php echo $frm->getFieldHtml('tslot_day['.$i.']'); ?>
                                                            </div>
                                                    </div>
                                            </div>
                                    </div>
                                    <div class="col-md-4 js-from_time_<?php echo $i;?>">
                                            <div class="field-set">
                                                    <div class="caption-wraper">
                                                       <label class="field_label">
                                                            <?php $fld = $frm->getField('tslot_from_time['.$i.'][]');
                                                              echo $fld->getCaption();
                                                            ?>
                                                       </label>
                                                    </div>
                                                    <div class="field-wraper">
                                                            <div class="field_cover">
                                                            <?php echo $frm->getFieldHtml('tslot_from_time['.$i.'][]'); ?>
                                                            </div>
                                                    </div>
                                            </div>
                                    </div>
                                    <div class="col-md-4 js-to_time_<?php echo $i;?>">
                                            <div class="field-set">
                                                    <div class="caption-wraper">
                                                       <label class="field_label">
                                                            <?php $fld = $frm->getField('tslot_to_time['.$i.'][]');
                                                              echo $fld->getCaption();
                                                            ?>
                                                       </label>
                                                    </div>
                                                    <div class="field-wraper">
                                                            <div class="field_cover">
                                                            <?php echo $frm->getFieldHtml('tslot_to_time['.$i.'][]'); ?>
                                                            </div>
                                                    </div>
                                            </div>
                                    </div>
                                    <div class="col-md-2 addRowBtnBlock<?php echo $i; ?>-js">
                                            <div class="field-set">
                                                    <div class="caption-wraper">
                                                       <label class="field_label">
                                                       </label>
                                                    </div>
                                                    <div class="field-wraper">
                                                            <div class="field_cover">
                                                            <?php //echo $frm->getFieldHtml('btn_add_row['.$i.']'); ?>
                                                            <button type="button" name="btn_add_row[<?php echo $i; ?>]" onClick= "addTimeSlotRow(<?php echo $i; ?>)" class="d-none btn btn-brand js-slot-add-<?php echo $i; ?>"><i class="fas fa-plus"></i></button>
                                                            </div>
                                                    </div>
                                            </div>
                                    </div>
                            </div>
                    <?php 
                            $row++;
                        }
                    }
                    ?>
                </div>             
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                </label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php
                                    echo $frm->getFieldHtml('addr_id');
                                    echo $frm->getFieldHtml('btn_submit');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
                <?php echo $frm->getExternalJS(); ?>
            </div>
        </div>
    </div>
</div>

<script language="javascript">
    var DAY_SUNDAY = <?php echo TimeSlot::DAY_SUNDAY; ?>;
    $(document).ready(function() {
        
        getCountryStates($("#addr_country_id").val(), <?php echo ($stateId) ? $stateId : 0; ?>, '#addr_state_id');

        addTimeSlotRow = function(day) {
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

            var addRowBtnHtml = '<button type="button" name="btn_add_row[' + day + ']" onclick="addTimeSlotRow(' + day + ')" class="btn btn-brand btn-sm js-slot-add-' + day + ' addRowBtn' + day + '-js d-none"><i class="fas fa-plus"></i></button>';

            var html = "<div class='row jsDay-"+ day + " row-" + count + " js-added-rows-" + day + "'><div class='col-md-2 jsWeekDay"+ ($(".availabilityType-js:checked").val() == 2 ?' d-none':'')   +" '></div><div class='col-md-4 js-from_time_" + day + "'>" + fromTimeHtml + "</div><div class='col-md-4 js-to_time_" + day + "'>" + toTimeHtml + "</div><div class='col-md-2'><div class='field-set'><div class='caption-wraper'><label class='field_label'></label></div><div class='field-wraper'><div class='field_cover btn-group'><button class='btn btn-outline-brand btn-sm' type='button' name='btn_remove_row' data-day='" + day + "'><i class='fas fa-minus'></i></button>" + addRowBtnHtml + "</div></div></div></div></div>";

            $(".js-from_time_" + day).last().parent().after(html);
            $(rowElement + " select").val('').attr('data-row', (count));
            var frmElement = rowElement + " .js-slot-from-" + day;
            
            $(frmElement + " option").removeClass('d-none');
            $(frmElement + " option").each(function() {
                var toVal = $(this).val();
                if (toVal != '' && toVal <= toTime) {
                    $(this).addClass('d-none');
                }
            });
        }

        displayFields = function(day, ele) {
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

        displayAddRowField = function(day, ele) {
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
                    $(".js-slot-from-" + day).each(function() {
                        if (index < $(this).data('row') && $(this).val() <= toTime) {
                            var nxtRow = $(this).data('row');
                            $(this).val("");
                            $(".js-slot-individual .row-" + nxtRow + " .js-slot-to-" + day).val("");
                            $("option", this).each(function() {
                                var optVal = $(this).val();
                                if (optVal != '' && optVal <= toTime) {
                                    $(this).addClass('d-none');
                                }
                            });
                        }
                    });
                }
                $(nextFrmElement + " option").each(function() {
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
            $(toElement + " option").each(function() {
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

        validateTimeFields = function() {
            var from_time = $("[name='tslot_from_all']").children("option:selected").val();
            var to_time = $("[name='tslot_to_all']").children("option:selected").val();

            $("[name='tslot_to_all'] option").removeClass('d-none');
            $("[name='tslot_to_all'] option").each(function() {
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
        $('.availabilityType-js:checked').trigger('change');
    });

    $(document).on("click", "[name='btn_remove_row']", function() {
        var day = $(this).data('day');
        $(this).parentsUntil('.row').parent().remove();

        if (0 < $('.js-added-rows-' + day + ':last [name="btn_remove_row"]').length) {
            var addRowBtnHtml = '<button type="button" name="btn_add_row[' + day + ']" onclick="addTimeSlotRow(' + day + ')" class="btn btn-brand js-slot-add-' + day + ' addRowBtn' + day + '-js"><i class="fas fa-plus"></i></button>';

            if (0 <  $('.js-added-rows-' + day + ':last .addRowBtn' + day + '-js').length) {
                $('.js-added-rows-' + day + ':last [name="btn_remove_row"]').after(addRowBtnHtml);
            }

        } else if (0 < $('.addRowBtnBlock' + day + '-js').length) {
            var addRowBtnHtml = '<button type="button" name="btn_add_row[' + day + ']" onclick="addTimeSlotRow(' + day + ')" class="btn btn-brand js-slot-add-' + day + ' addRowBtn' + day + '-js mt-4"><i class="fas fa-plus"></i></button>';
            $('.addRowBtnBlock' + day + '-js').html(addRowBtnHtml);
        }
    })
</script>
