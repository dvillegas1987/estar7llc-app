<div class="tabs  align-items-center">
    <?php require_once(CONF_THEME_PATH . '_partial/seller/customCatalogProductNavigationLinks.php'); ?>
</div>
<div class="card">
    <div class="card-body ">
        <div class="row">
            <div class="col-md-12">
                <div class="form__subcontent">
                    <?php  $specCount = 0;
                    if (!empty($productSpecifications) && array_key_exists('prod_spec_name', $productSpecifications)) {
                        $specCount = count($productSpecifications['prod_spec_name'][CommonHelper::getLangId()]);
                    } ?>
                    <form name="frmProductSpec" method="post" id="frm_fat_id_frmProductSpec" class="form form--horizontal" onsubmit="setupCustomCatalogSpecification(this,<?php echo $preqId; ?>); return(false);">
                        <?php 
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        if (!empty($translatorSubscriptionKey)) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>" 
                                        onClick="autofillLangData($(this), $('form#frm_fat_id_frmProductSpec'))"
                                        data-action="<?php echo UrlHelper::generateUrl('seller', 'getTranslatedData'); ?>">
                                </div>
                            </div>
                        <?php }
                        $totalSpec = 0;
                        $count = 0;
                        if ($specCount > 0) {
                            foreach ($productSpecifications['prod_spec_name'][CommonHelper::getLangId()] as $specKey => $specval) {
                                $totalSpec = $specKey; ?>
                        <div class="replaced specification" id="specification<?php echo $specKey; ?>">
                            <?php 
                            $defaultLang = true;
                            foreach ($languages as $langId => $langName) {
                                $class = 'langField_' . $langId;
                                if (true === $defaultLang) {
                                    $class .= ' defaultLang';
                                    $defaultLang = false;
                                }
                                ?>
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label"></label>
                                                </div>
                                                <div class="caption-wraper">
                                                    <h5><?php  echo $langName;?></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 ">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Name', $siteLangId)?></label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php 
                                                    $specValue = isset($productSpecifications['prod_spec_name'][$langId][$specKey]) ? $productSpecifications['prod_spec_name'][$langId][$specKey] : '';
                                                ?>
                                                <input class="psec-name-js <?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>" title="<?php echo Labels::getLabel('LBL_Specification_Name', $siteLangId)?>"
                                                    value="<?php echo $specValue;?>" type="text" name="prod_spec_name[<?php echo $langId ?>][<?php echo $specKey;?>]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Value', $siteLangId)?></label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php 
                                                    $specValue = isset($productSpecifications['prod_spec_value'][$langId][$specKey]) ? $productSpecifications['prod_spec_value'][$langId][$specKey] : '';
                                                ?>
                                                <input class="<?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>" title="<?php echo Labels::getLabel('LBL_Specification_Value', $siteLangId)?>" type="text"
                                                    value="<?php echo $specValue;?>" name="prod_spec_value[<?php echo $langId ?>][<?php echo $specKey;?>]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($langId == key(array_slice($languages, -1, 1, true))) { ?>
                                <div class="col-md-1 align--right">
                                    <?php if ($count != 0) { ?>
                                    <button type="button" onclick="removeSpecDiv(<?php echo $specKey ?>);" class="btn btn-brand ripplelink" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId)?>"><i class="fa fa-minus"></i></button>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                            </div>
                            <?php  } ?>
                        </div>
                        <?php $count++;
                            }
                        } else { ?>
                        <div class="replaced specification" id="specification0">
                            <?php foreach ($languages as $langId=>$langName) { ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label"></label>
                                                </div>
                                                <div class="caption-wraper">
                                                    <h5><?php  echo $langName;?></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Name', $siteLangId)?></label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <input class="<?php echo 'layout--'.Language::getLayoutDirection($langId); ?> psec-name-js" title="<?php echo Labels::getLabel('LBL_Specification_Name', $siteLangId)?>" type="text"
                                                    name="prod_spec_name[<?php echo $langId ?>][0]" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Value', $siteLangId)?></label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <input class="<?php echo 'layout--'.Language::getLayoutDirection($langId); ?>" title="<?php echo Labels::getLabel('LBL_Specification_Value', $siteLangId)?>" type="text"
                                                    name="prod_spec_value[<?php echo $langId ?>][0]" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php  } ?>
                        </div>
                        <?php } ?>
                        <div id="addSpecFields"></div>
						<div class="gap"></div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-4 col-xm-12 align--right">
                                <button type="button" class="btn btn-secondary ripplelink plusButton" title="<?php echo Labels::getLabel('LBL_Shipping', $siteLangId)?>" onclick="getCustomCatalogSpecificationForm();"><i
                                        class="fa fa-plus"></i></button>
                            </div>
                        </div>
						<div class="row">
                        <div class="col-md-12 ">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label"></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <input title="" type="hidden" name="product_id" value="<?php echo $preqId; ?>">
                                        <input title="" type="hidden" name="prodspec_id" value="0">
                                        <input title="" type="submit" name="btn_submit" value="<?php echo Labels::getLabel('LBL_Save_Changes', $siteLangId)?>">
                                    </div>
                                </div>
                            </div>
                        </div> </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var buttonClick = <?php echo $totalSpec; ?>;
</script>
