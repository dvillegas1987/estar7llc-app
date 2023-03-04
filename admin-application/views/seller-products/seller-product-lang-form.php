<?php
defined('SYSTEM_INIT') or die('Invalid Usage.'); 

$langFld = $frmSellerProdLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "sellerProductLangForm(" . $selprod_id . ", this.value);");
?>
<section class="section">
    <div class="sectionhead">

        <h4><?php echo Labels::getLabel('LBL_Product_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <?php /* require_once('sellerCatalogProductTop.php'); */?>
                    <div class="tabs_panel_wrap ">
                        <?php /* <ul class="tabs_nav tabs_nav--internal">
                            <li><a href="javascript:void(0)"
                                    onClick="sellerProductForm(<?php echo $product_id;?>,<?php echo $selprod_id;?>)"><?php echo Labels::getLabel('LBL_Basic', $adminLangId); ?></a>
                            </li>
                            <li class="<?php echo (0 == $selprod_id) ? 'fat-inactive' : ''; ?>">
                                <a class="active" href="javascript:void(0);">
                                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                                </a>
                            </li>
                            <li><a href="javascript:void(0)"
                                    onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_WARRANTY ; ?>)"><?php echo Labels::getLabel('LBL_Link_Warranty_Policies', $adminLangId); ?></a>
                            </li>
                            <li><a href="javascript:void(0)"
                                    onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_RETURN ; ?>)"><?php echo Labels::getLabel('LBL_Link_Return_Policies', $adminLangId); ?></a>
                            </li>
                        </ul>*/ ?>
                        <div class="tabs_panel_wrap">
                            <?php
                            $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                            $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                            if (!empty($translatorSubscriptionKey) && $formLangId != $siteDefaultLangId) { ?> 
                                <div class="row justify-content-end"> 
                                    <div class="col-auto mb-4">
                                        <input class="btn btn-brand" 
                                            type="button" 
                                            value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                            onClick="sellerProductLangForm(<?php echo $selprod_id; ?>, <?php echo $formLangId; ?>, 1)">
                                    </div>
                                </div>
                            <?php } ?> 
                            <div class="tabs_panel">
                                <?php
                                $frmSellerProdLangFrm->setFormTagAttribute('onsubmit', 'setUpSellerProductLang(this); return(false);');
                                $frmSellerProdLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--' . $formLayout);
                                $frmSellerProdLangFrm->developerTags['colClassPrefix'] = 'col-md-';
                                $frmSellerProdLangFrm->developerTags['fld_default_col'] = 12;

                                echo $frmSellerProdLangFrm->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>