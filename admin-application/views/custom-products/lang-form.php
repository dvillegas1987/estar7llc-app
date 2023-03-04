<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$langFld = $customProductLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "productLangForm(" . $preqId . ", this.value);");
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Product_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="tabs_nav_container  flat">

            <ul class="tabs_nav">
                <li><a <?php echo (0 < $preqId) ? "onclick='productForm( " . $preqId . ", 0 );'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                </li>
                <?php /* <li><a <?php echo (0 < $preqId) ? "onclick='sellerProductForm(" . $preqId . ");'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Inventory/Info', $adminLangId); ?></a>
                </li> */ ?>
                <li><a <?php echo (0 < $preqId) ? "onclick='customCatalogSpecifications( " . $preqId . " );'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specifications', $adminLangId);?></a>
                </li>
                <li class="<?php echo (0 == $preqId) ? 'fat-inactive' : ''; ?>">
                    <a class="active" href="javascript:void(0);" <?php echo (0 < $preqId) ? "onclick='productLangForm(" . $preqId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                        <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                    </a>
                </li>
                <?php if (!empty($productOptions) && count($productOptions) > 0) {
                    ?>
                <li><a <?php echo (0 < $preqId) ? "onClick='customEanUpcForm(" . $preqId . ");'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_setup', $adminLangId); ?></a>
                </li>
                <?php
                } ?>
                <li><a <?php echo (0 < $preqId) ? "onclick='updateStatusForm( " . $preqId . ");'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Change_Status', $adminLangId); ?></a>
                </li>
            </ul>
            <div class="tabs_panel_wrap">
                <?php
                $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                if (!empty($translatorSubscriptionKey) && $product_lang_id != $siteDefaultLangId) { ?> 
                    <div class="row justify-content-end"> 
                        <div class="col-auto mb-4">
                            <input class="btn btn-brand" 
                                type="button" 
                                value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                onClick="productLangForm(<?php echo $preqId; ?>, <?php echo $product_lang_id; ?>, 1)">
                        </div>
                    </div>
                <?php } ?>
                <div class="tabs_panel">
                    <?php
                    //$customProductLangFrm->setFormTagAttribute('onsubmit','setUpSellerProduct(this); return(false);');
                    $customProductLangFrm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);
                    $customProductLangFrm->developerTags['colClassPrefix'] = 'col-md-';
                    $customProductLangFrm->developerTags['fld_default_col'] = 12;
                    echo $customProductLangFrm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</section>