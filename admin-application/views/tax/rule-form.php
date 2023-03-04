<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form');
$frm->setFormTagAttribute('onsubmit', 'setupTaxRule(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$combTaxCount = 0;
?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Tax', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo $taxCategory; ?></h4>
                        <?php
                        $data = [
                            'adminLangId' => $adminLangId,
                            'statusButtons' => false,
                            'deleteButton' => false,
                            'otherButtons' => [
                                [
                                    'attr' => [
                                        'href' => commonHelper::generateUrl('Tax'),
                                        'title' => Labels::getLabel('LBL_Tax_Categories_List', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-arrow-left"></i>'
                                ],
                            ]
                        ];

                        $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        ?>
                    </div>
                    <?php echo $frm->getFormTag(); ?>
                    <div class="sectionbody">
                        <div class="row justify-content-center">
                            <div class="col-lg-12">
                                <div class="tax-rule-container--js">
                                    <?php if (!empty($rules)) {
                                        $index = 1;
                                        foreach ($rules as $rule) {
                                            $ruleId = $rule['taxrule_id'];
                                            $locations = (!empty($ruleLocations) && isset($ruleLocations[$ruleId])) ? $ruleLocations[$ruleId] : array();
                                            $countryIds = [];
                                            $stateIds = [];
                                            $typeIds = [];
                                            if (!empty($locations)) {
                                                $countryIds = array_column($locations, 'taxruleloc_country_id');
                                                $countryIds = array_unique($countryIds);
                                                $stateIds = array_column($locations, 'taxruleloc_state_id');
                                                $stateIds = array_unique($stateIds);
                                                $typeIds = array_column($locations, 'taxruleloc_type');
                                                $typeIds = array_unique($typeIds);
                                            } ?>
                                            <div class="tax-rule-form--js tax-rule-form-<?php echo $index; ?>" data-index="<?php echo $index; ?>">
                                                <div class="p-4">
                                                    <div class="row mb-4">
                                                        <div class="col-sm-12">
                                                            <?php if ($index > 1) { ?>
                                                                <a href="javascript:void(0);" class="themebtn btn-brand remove-tax-rule--js"> <?php echo Labels::getLabel("LBL_Delete_Tax_Rule", $adminLangId); ?></a>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <?php
                                                        $taxruleIdFld = $frm->getField('taxrule_id[]');
                                                        $taxruleIdFld->value = $rule['taxrule_id'];
                                                        $taxruleNameFld = $frm->getField('taxrule_name[' . $adminLangId . '][]');
                                                        $taxruleNameFld->value = $rule['taxrule_name'];
                                                        $taxruleRateFld = $frm->getField('taxrule_rate[]');
                                                        $taxruleRateFld->value = $rule['taxrule_rate'];
                                                        $taxStrFld = $frm->getField('taxrule_taxstr_id[]');
                                                        $taxStrFld->setFieldTagAttribute("onChange", "getCombinedTaxes(this, this.value)");
                                                        $taxStrFld->value = $rule['taxrule_taxstr_id'];

                                                        echo $frm->getFieldHtml('taxrule_id[]'); ?>
                                                        <div class="col-md-6">
                                                            <div class="border rounded p-4  h-100">
                                                                <div class="form-group">
                                                                    <label for="example-text-input" class=""><?php echo $taxruleNameFld->getCaption(); ?></label>
                                                                    <?php echo $frm->getFieldHtml('taxrule_name[' . $adminLangId . '][]'); ?>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="example-text-input"><?php echo $taxruleRateFld->getCaption(); ?></label>
                                                                    <?php echo $frm->getFieldHtml('taxrule_rate[]'); ?>
                                                                </div>
                                                                <?php
                                                                $countryFld = $frm->getField('taxruleloc_country_id[]');
                                                                $countryFld->setFieldTagAttribute("class", "addr_country_id");
                                                                $countryFld->setFieldTagAttribute('onChange', 'getCountryStatesTaxInTaxForm(this, this.value,0)');
                                                                $countryFld->value = $countryIds;
                                                                $typeFld = $frm->getField('taxruleloc_type[]');
                                                                $typeFld->value = $typeIds;
                                                                $stateFld = $frm->getField('taxruleloc_state_id[]');
                                                                $stateFld->value = $stateIds;
                                                                $stateFld->addFieldTagAttribute('multiple', 'true');
                                                                $stateFld->addFieldTagAttribute('class', 'selectpicker');
                                                                $stateFld->addFieldTagAttribute('data-style', 'bg-white rounded-pill px-4 py-2 shadow-sm'); ?>
                                                                <div class="form-group">
                                                                    <label for="example-text-input" class=""><?php echo $countryFld->getCaption(); ?></label>
                                                                    <?php echo $frm->getFieldHtml('taxruleloc_country_id[]'); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="border rounded p-4  h-100">
                                                                <div class="form-group">
                                                                    <label for="example-text-input" class=""><?php echo $typeFld->getCaption(); ?></label>
                                                                    <?php echo $frm->getFieldHtml('taxruleloc_type[]'); ?>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="example-text-input" class=""><?php echo $stateFld->getCaption(); ?></label>
                                                                    <?php echo $frm->getFieldHtml('taxruleloc_state_id[]'); ?>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="example-text-input" class=""><?php echo $taxStrFld->getCaption(); ?></label>
                                                                    <?php echo $frm->getFieldHtml('taxrule_taxstr_id[]'); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
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
                                                                                                    <?php $fld = $frm->getField('taxrule_name['.$langId.'][]');
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
                                                                        <?php }?>
                                                                        </div>
                                                                    <?php } */ ?>
                                                        <div class="col-md-6 combined-tax-details--js"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <script>
                                                var countryId = "<?php echo (isset($countryIds[0])) ? $countryIds[0] : "0"; ?>";
                                                checkStatesDefault(<?php echo $index; ?>, countryId, <?php echo json_encode($stateIds); ?>);
                                            </script>
                                        <?php $index++;
                                        } ?>
                                    <?php } else { ?>
                                        <div class="tax-rule-form--js tax-rule-form-1" data-index="1">
                                            <div class="p-4">
                                                <div class="row mb-4">
                                                    <div class="col-sm-12">
                                                        <h3><?php echo Labels::getLabel("LBL_Tax_Rules", $adminLangId) ?></h3>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <?php $taxruleNameFld = $frm->getField("taxrule_name[" . $adminLangId . "][]");
                                                    $taxruleRateFld = $frm->getField("taxrule_rate[]");
                                                    echo $frm->getFieldHtml('taxrule_id[]');
                                                    ?>
                                                    <div class="col-lg-6">
                                                        <div class="border rounded p-4 h-100">
                                                            <div class="form-group">
                                                                <label for="example-text-input" class="">
                                                                    <?php echo $taxruleNameFld->getCaption(); ?>
                                                                </label>
                                                                <?php echo $frm->getFieldHtml("taxrule_name[" . $adminLangId . "][]"); ?></div>
                                                            <div class="form-group">
                                                                <label for="example-text-input">
                                                                    <?php echo $taxruleRateFld->getCaption(); ?>
                                                                </label>
                                                                <?php echo $frm->getFieldHtml("taxrule_rate[]"); ?>
                                                            </div>
                                                            <?php
                                                            $countryFld = $frm->getField("taxruleloc_country_id[]");
                                                            $countryFld->setFieldTagAttribute("class", "addr_country_id");
                                                            $countryFld->setFieldTagAttribute("onChange", "getCountryStatesTaxInTaxForm(this, this.value,0)");
                                                            $taxStrFld = $frm->getField("taxrule_taxstr_id[]");
                                                            $taxStrFld->setFieldTagAttribute("onChange", "getCombinedTaxes(this, this.value)");
                                                            $typeFld = $frm->getField("taxruleloc_type[]");
                                                            $stateFld = $frm->getField("taxruleloc_state_id[]");
                                                            $stateFld->addFieldTagAttribute("multiple", "true");
                                                            $stateFld->addFieldTagAttribute("class", "selectpicker");
                                                            $stateFld->addFieldTagAttribute("data-style", "bg-white rounded-pill px-4 py-2 shadow-sm"); ?>
                                                            <div class="form-group">
                                                                <label for="example-text-input" class="">
                                                                    <?php echo $countryFld->getCaption(); ?></label>
                                                                <?php echo $frm->getFieldHtml("taxruleloc_country_id[]"); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="border rounded p-4 h-100">
                                                            <div class="form-group">
                                                                <label for="example-text-input" class="">
                                                                    <?php echo $typeFld->getCaption(); ?></label>
                                                                <?php echo $frm->getFieldHtml("taxruleloc_type[]"); ?>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="example-text-input" class="">
                                                                    <?php echo $stateFld->getCaption(); ?>
                                                                </label>
                                                                <?php echo $frm->getFieldHtml("taxruleloc_state_id[]"); ?>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="example-text-input" class="">
                                                                    <?php echo $taxStrFld->getCaption(); ?></label>
                                                                <?php echo $frm->getFieldHtml("taxrule_taxstr_id[]"); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row taxrule-lang-form--js">
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
                                                                                            $fld->value = isset($rule['taxrule_name'][$langId]) ? $rule['taxrule_name'][$langId] : '';
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
                                                    <div class="col-md-6 combined-tax-details--js"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="p-4">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <a href="javascript:void(0);" class="themebtn btn-primary add-rule-form--js"><i class="ion-plus">
                                                </i> <?php echo Labels::getLabel('LBL_Add_More', $adminLangId); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form__actions text-center mb-4">
                        <?php
                        echo $frm->getFieldHtml('taxcat_id');
                        echo $frm->getFieldHtml('btn_submit');
                        ?>
                    </div>
                    </form>
                    <?php echo $frm->getExternalJs(); ?>

                </section>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('.selectpicker').selectpicker();
    });
   
    
</script>
<script>
    $(document).ready(function() {
        var adminLangId = <?php echo $adminLangId; ?>;
        $('.add-rule-form--js').on('click', function() {
            var lastIndex = $('.tax-rule-form--js:last').data('index');
            lastIndex = parseInt(lastIndex);
            var newIndex = lastIndex + 1;
            var ruleFormHtml = '';
            /*$('.tax-rule-form--js:last').after(fcom.getLoader());*/
            fcom.ajax(fcom.makeUrl('Tax', 'addRuleForm', [newIndex]), '', function(res) {
                $('.tax-rule-form--js:last').after(res);
                $('.tax-rule-form-' + newIndex + ' .selectpicker').selectpicker();
            });
        });

        $('body').on('click', '.remove-tax-rule--js', function() {
            $(this).parents('.tax-rule-form--js').remove();
        });

        /* $('.rule-detail-row--js input[name="taxruledet_name['+adminLangId+'][]"]').keyup(function(){
            var className = $(this).parents('tr').attr('class').split(' ').pop();
            $('.'+className+' .replaceText--js').html($(this).val());
        }); */
    });
</script>