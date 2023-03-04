<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form  layout--' . $formLayout);
$frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
//$frm->developerTags['colClassPrefix'] = 'col-md-';
//$frm->developerTags['fld_default_col'] = 6;

$langFld = $frm->getField('lang_id');
$langFld->setFieldTagAttribute('onChange', "addAddressForm(" . $addressId . ", this.value);");

$addrLabelFld = $frm->getField('addr_title');
$addrLabelFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_E.g:_My_Office_Address', $langId));

$countryFld = $frm->getField('addr_country_id');
$countryFld->setFieldTagAttribute('id', 'addr_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,' . $stateId . ',\'#shop_state\',' . $langId . ')');

$stateFld = $frm->getField('addr_state_id');
$stateFld->setFieldTagAttribute('id', 'shop_state');

$slotTypeFld = $frm->getField('tslot_availability');
$slotTypeFld->setOptionListTagAttribute('class', 'list-inline-checkboxes');
$slotTypeFld->developerTags['rdLabelAttributes'] = array('class' => 'radio');
$slotTypeFld->developerTags['rdHtmlAfterRadio'] = '<i class="input-helper"></i>';
$slotTypeFld->setFieldTagAttribute('onChange', 'displaySlotTimings(this);');
$slotTypeFld->setFieldTagAttribute('class', 'availabilityType-js');

?>

<div class="sectionbody space">
    <div class="row">
        <div class="col-sm-12">
            <div class="tabs_nav_container responsive flat">
                <div class="tabs_panel_wrap">
                    <div class="tabs_panel">
                        <?php echo $frm->getFormTag(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">
                                            <?php $fld = $frm->getField('lang_id');
                                            echo $fld->getCaption();
                                            ?>
                                        </label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $frm->getFieldHtml('lang_id'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
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
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            $daysArr = TimeSlot::getDaysArr($langId);
                            $row = 0;
                            for ($i = 0; $i < count($daysArr); $i++) {

                                $dayFld = $frm->getField('tslot_day[' . $i . ']');
                                $dayFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
                                $dayFld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';
                                $dayFld->setFieldTagAttribute('onChange', 'displayFields(' . $i . ', this)');
                                $dayFld->setFieldTagAttribute('class', 'slotDays-js');

                                $addRowFld = $frm->getField('btn_add_row[' . $i . ']');
                                $addRowFld->setFieldTagAttribute('onClick', 'addTimeSlotRow(' . $i . ')');
                                $addRowFld->setFieldTagAttribute('class', 'js-slot-add-' . $i);

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
                                                        <label class="field_label"> </label>
                                                    </div>
                                                    <div class="field-wraper">
                                                        <div class="field_cover">
                                                            <?php if ($key == 0) {
                                                                echo $frm->getFieldHtml('tslot_day[' . $i . ']');
                                                            } ?>
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
                                                                <input type='button' name='btn_remove_row' value='x' data-day="<?php echo $i; ?>">
                                                            <?php }
                                                            if (count($slotData['tslot_from_time'][$i]) - 1 == $key) {
                                                                echo $frm->getFieldHtml('btn_add_row[' . $i . ']');
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                        $row++;
                                    }
                                } else {
                                    $addRowFld->setFieldTagAttribute('class', 'd-none js-slot-add-' . $i);

                                    $fromFld = $frm->getField('tslot_from_time[' . $i . '][]');
                                    $fromFld->setFieldTagAttribute('disabled', 'true');
                                    $fromFld->setFieldTagAttribute('data-row', $row);
                                    $fromFld->setFieldTagAttribute('class', 'js-slot-from-' . $i . ' fromTime-js');
                                    $fromFld->setFieldTagAttribute('onChange', 'displayAddRowField(' . $i . ', this)');

                                    $toFld = $frm->getField('tslot_to_time[' . $i . '][]');
                                    $toFld->setFieldTagAttribute('disabled', 'true');
                                    $toFld->setFieldTagAttribute('data-row', $row);
                                    $toFld->setFieldTagAttribute('class', 'js-slot-to-' . $i);
                                    $toFld->setFieldTagAttribute('onChange', 'displayAddRowField(' . $i . ', this)');
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
                                                        <?php echo $frm->getFieldHtml('tslot_day[' . $i . ']'); ?>
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
                                                        <?php echo $frm->getFieldHtml('btn_add_row[' . $i . ']'); ?>
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
    </div>
</div>


<script language="javascript">
     var DAY_SUNDAY = <?php echo TimeSlot::DAY_SUNDAY; ?>;
    <?php if ($addressId > 0) { ?>
        $(document).ready(function() {
            $('.availabilityType-js:checked').trigger('change');
            getCountryStates($("#addr_country_id").val(), <?php echo ($stateId) ? $stateId : 0; ?>, '#shop_state', <?php echo $langId; ?>);
           
        });
    <?php } ?>
</script>