<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Custom_Catalog_Request', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a <?php echo ($preqId) ? "onClick='productForm( " . $preqId . ", 0 );'" : ""; ?>
                                href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                        </li>
                        <?php /* <li><a <?php echo ($preqId) ? "onClick='sellerProductForm(" . $preqId . ");'" : ""; ?>
                                href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Inventory/Info', $adminLangId); ?></a>
                        </li> */ ?>
                        <li><a class="active" <?php echo ($preqId) ? "onclick='customCatalogSpecifications( " . $preqId . " );'" : ""; ?>
                                href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specifications', $adminLangId);?></a>
                        </li>
                        <li class="<?php echo (0 == $preqId) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $preqId) ? "onclick='productLangForm(" . $preqId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <?php if (count($productOptions) > 0) { ?>
                        <li><a <?php echo ($preqId) ? "onClick='customEanUpcForm(" . $preqId . ");'" : ""; ?>
                                href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_setup', $adminLangId); ?></a>
                        </li>
                        <?php } ?>
                        <li><a <?php echo ($preqId) ? "onClick='updateStatusForm( " . $preqId . ");'" : ""; ?>
                                href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Change_Status', $adminLangId); ?></a>
                        </li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php  $specCount = !empty($productSpecifications) ? count($productSpecifications['prod_spec_name'][CommonHelper::getLangId()]) : 0; ?>
                            <form name="frmProductSpec" method="post" id="frm_fat_id_frmProductSpec"
                                class="form web_form"
                                onsubmit="setupCustomCatalogSpecification(this,<?php echo $preqId; ?>); return(false);">
                                <?php 
                                $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                                if (!empty($translatorSubscriptionKey)) { ?> 
                                    <div class="row justify-content-end"> 
                                        <div class="col-auto mb-4">
                                            <input class="btn btn-brand" 
                                                type="button" 
                                                value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                                onClick="autofillLangData($(this), $('form#frm_fat_id_frmProductSpec'))"
                                                data-action="<?php echo UrlHelper::generateUrl('CustomProducts', 'getTranslatedData'); ?>">
                                        </div>
                                    </div>
                                <?php }

                                $totalSpec = 0;
                                $count = 0;
                                if ($specCount > 0) {
                                    foreach ($productSpecifications['prod_spec_name'][CommonHelper::getLangId()] as $specKey => $specval) {
                                        $totalSpec = $specKey; ?>
                                        <div class="form__cover  nopadding--bottom specification" 
                                            id="specification<?php echo $specKey; ?>">
                                            <?php if (key($productSpecifications['prod_spec_name'][CommonHelper::getLangId()]) != $specKey) { ?>
                                                <div class="divider"></div>
                                                <div class="gap"></div>
                                            <?php } ?>
                                            <?php 
                                            $defaultLang = true;
                                            foreach ($languages as $langId => $langName) {
                                                $class = 'langField_' . $langId;
                                                if (true === $defaultLang) {
                                                    $class .= ' defaultLang';
                                                    $defaultLang = false;
                                                }
                                                ?>
                                                <div class="row align-items-center mb-4">
                                                    <div class="col-md-3">
                                                        <div class="h5 mb-0">
                                                            <?php  echo $langName; ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <?php
                                                            $specName = !empty($productSpecifications['prod_spec_name'][$langId][$specKey])
                                                                        ? $productSpecifications['prod_spec_name'][$langId][$specKey] : '';
                                                            $specValue = !empty($productSpecifications['prod_spec_value'][$langId][$specKey])
                                                                        ? $productSpecifications['prod_spec_value'][$langId][$specKey] : '';
                                                            $specGroup = !empty($productSpecifications['prod_spec_value'][$langId][$specKey])
                                                                        ? $productSpecifications['prod_spec_group'][$langId][$specKey] : '';
                                                        ?>
                                                        <input
                                                            class="psec-name-js <?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>"
                                                            title="<?php echo Labels::getLabel('LBL_Specification_Name', $adminLangId)?>"
                                                            value="<?php echo $specName; ?>"
                                                            placeholder="<?php echo Labels::getLabel('LBL_Specification_Name', $adminLangId)?>"
                                                            type="text"
                                                            name="prod_spec_name[<?php echo $langId ?>][<?php echo $specKey; ?>]">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input
                                                            class="<?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>"
                                                            title="<?php echo Labels::getLabel('LBL_Specification_Value', $adminLangId)?>"
                                                            type="text"
                                                            value="<?php echo $specValue; ?>"
                                                            placeholder="<?php echo Labels::getLabel('LBL_Specification_Value', $adminLangId)?>"
                                                            name="prod_spec_value[<?php echo $langId ?>][<?php echo $specKey; ?>]">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input
                                                            class="<?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>"
                                                            title="<?php echo Labels::getLabel('LBL_Specification_Group', $adminLangId)?>"
                                                            type="text"
                                                            value="<?php echo $specGroup; ?>"
                                                            placeholder="<?php echo Labels::getLabel('LBL_Specification_Group', $adminLangId)?>"
                                                            name="prod_spec_group[<?php echo $langId ?>][<?php echo $specKey; ?>]">
                                                    </div>
                                                    <?php if ($langId == key(array_slice($languages, -1, 1, true))) { ?>
                                                        <div class="col-lg-1 col-md-1 col-sm-4 col-xm-12 align--right">
                                                            <?php if ($count != 0) { ?>
                                                                <button type="button"
                                                                    onclick="removeSpecDiv(<?php echo $specKey ?>);"
                                                                    class="btn btn--secondary ripplelink"
                                                                    title="<?php echo Labels::getLabel('LBL_Remove', $adminLangId)?>">
                                                                    <i class="ion-minus-round"></i>
                                                                </button>
                                                            <?php } ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <?php $count++;
                                    }
                                } else { ?>
                                <div class="form__cover nopadding--bottom specification" id="specification0">
                                    <?php 
                                    $defaultLang = true;
                                    foreach ($languages as $langId => $langName) { 
                                        $class = 'langField_' . $langId;
                                        if (true === $defaultLang) {
                                            $class .= ' defaultLang';
                                            $defaultLang = false;
                                        }
                                        ?>
                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3">
                                                <div class="h5 mb-0">
                                                    <?php  echo $langName; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <input
                                                    class="<?php echo 'layout--' . Language::getLayoutDirection($langId); ?> psec-name-js <?php echo $class; ?>"
                                                    title="<?php echo Labels::getLabel('LBL_Specification_Name', $adminLangId)?>"
                                                    placeholder="<?php echo Labels::getLabel('LBL_Specification_Name', $adminLangId)?>"
                                                    type="text"
                                                    name="prod_spec_name[<?php echo $langId ?>][0]">
                                            </div>
                                            <div class="col-md-3">
                                                <input
                                                    class="<?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>"
                                                    title="<?php echo Labels::getLabel('LBL_Specification_Value', $adminLangId)?>"
                                                    placeholder="<?php echo Labels::getLabel('LBL_Specification_Value', $adminLangId)?>"
                                                    type="text"
                                                    name="prod_spec_value[<?php echo $langId ?>][0]">
                                                
                                            </div>
                                            <div class="col-md-3">
                                                <input
                                                    class="<?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>"
                                                    title="<?php echo Labels::getLabel('LBL_Specification_Group', $adminLangId)?>"
                                                    placeholder="<?php echo Labels::getLabel('LBL_Specification_Group', $adminLangId)?>"
                                                    type="text"
                                                    name="prod_spec_group[<?php echo $langId ?>][0]">
                                                
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                                <div id="addSpecFields"></div>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-4 col-xm-12">
                                        <button 
                                            type="button"
                                            class="btn btn--secondary ripplelink right"
                                            title="<?php echo Labels::getLabel('LBL_Shipping', $adminLangId)?>"
                                            onclick="getCustomCatalogSpecificationForm();">
                                            <i class="ion-plus-round"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-4 col-xm-12">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label"></label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <input title="" type="submit" name="btn_submit" value="<?php echo Labels::getLabel('LBL_Save_Changes', $adminLangId)?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input title="" type="hidden" name="product_id" value="<?php echo $preqId; ?>">
                                <input title="" type="hidden" name="prodspec_id" value="0">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    var buttonClick = <?php echo $totalSpec; ?>;
</script>
