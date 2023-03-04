<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form');
$combTaxCount = 0;
?>
<div class="tax-rule-form--js tax-rule-form-<?php echo $index; ?>" data-index="<?php echo $index; ?>">
    <div class="p-4">
        <div class="row mb-4">
            <div class="col-sm-12">
                <?php if ($index > 1) { ?>
                <a href="javascript:void(0);" class="themebtn btn-brand remove-tax-rule--js"><?php echo Labels::getLabel("LBL_Delete_Tax_Rule", $adminLangId);?></a>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <?php $taxruleNameFld = $frm->getField("taxrule_name[".$adminLangId."][]");
              $taxruleRateFld = $frm->getField("taxrule_rate[]");
              echo $frm->getFieldHtml('taxrule_id[]');
            ?>
            <div class="col-lg-6">
                <div class="border rounded p-4 h-100">
                    <div class="form-group">
                        <label for="example-text-input" class="">
                            <?php echo $taxruleNameFld->getCaption();?>
                        </label>
                        <?php echo $frm->getFieldHtml("taxrule_name[".$adminLangId."][]"); ?>
                    </div>
                    <div class="form-group">
                        <label for="example-text-input">
                            <?php echo $taxruleRateFld->getCaption();?>
                        </label>
                        <?php echo $frm->getFieldHtml("taxrule_rate[]"); ?>
                    </div>
                    <?php
                    $countryFld = $frm->getField("taxruleloc_country_id[]");
                    $countryFld->setFieldTagAttribute("class", "addr_country_id");
                    $countryFld->setFieldTagAttribute("onChange", "getCountryStatesTaxInTaxForm(this, this.value,0)");
                    $typeFld = $frm->getField("taxruleloc_type[]");
                    $stateFld = $frm->getField("taxruleloc_state_id[]");
                    $stateFld->addFieldTagAttribute("multiple", "true");
                    $stateFld->addFieldTagAttribute("class", "selectpicker");
                    $stateFld->addFieldTagAttribute("data-style", "bg-white rounded-pill px-4 py-2 shadow-sm");
                    $taxStrFld = $frm->getField("taxrule_taxstr_id[]");
                    $taxStrFld->setFieldTagAttribute("onChange", "getCombinedTaxes(this, this.value)");
                    ?>
                    <div class="form-group">
                        <label for="example-text-input" class="">
                            <?php echo $countryFld->getCaption();?></label>
                        <?php echo $frm->getFieldHtml("taxruleloc_country_id[]");?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="border rounded p-4 h-100">
                    <div class="form-group">
                        <label for="example-text-input" class="">
                            <?php echo $typeFld->getCaption();?></label>
                        <?php echo $frm->getFieldHtml("taxruleloc_type[]");?>
                    </div>
                    <div class="form-group">
                        <label for="example-text-input" class="">
                            <?php echo $stateFld->getCaption();?>
                        </label>
                        <?php echo $frm->getFieldHtml("taxruleloc_state_id[]");?>
                    </div>
                    <div class="form-group">
                        <label for="example-text-input" class="">
                            <?php echo $taxStrFld->getCaption();?></label>
                        <?php echo $frm->getFieldHtml("taxrule_taxstr_id[]");?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row taxrule-lang-form--js">
            <div class="col-md-6 combined-tax-details--js"></div>
            <?php /* if (!empty($otherLanguages)) { ?>
                <div class="col-md-6">
                <?php foreach ($otherLanguages as $langId => $data) {
                    $layout = Language::getLayoutDirection($langId); ?>
                    <div class="accordians_container accordians_container-categories mt-4" defaultLang= "<?php echo $siteDefaultLangId; ?>" language="<?php echo $langId; ?>" id="accordion-language_<?php echo $langId; ?>" onClick="translateData(this)">
                        <div class="accordian_panel">
                            <span class="accordian_title accordianhead accordian_title mb-0" id="collapse_<?php echo $langId; ?>">
                            <?php echo $data." "; echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </span>
                            <div class="accordian_body accordiancontent" style="display: none;">
                                <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                            <?php  $fld = $frm->getField('taxrule_name['.$langId.'][]');
                                                echo $fld->getCaption(); ?>
                                            </label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                            <?php echo $frm->getFieldHtml('taxrule_name['.$langId.'][]'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </div>
            <?php } */ ?>
        </div>
    </div>
</div>
