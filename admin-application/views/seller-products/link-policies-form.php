<?php
defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
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
                    <div class="tabs_panel_wrap">
                        <?php /* <ul class="tabs_nav tabs_nav--internal">
                            <li><a href="javascript:void(0)"
                                    onClick="sellerProductForm(<?php echo $product_id;?>,<?php echo $selprod_id;?>)"><?php echo Labels::getLabel('LBL_Basic', $adminLangId); ?></a>
                            </li>
                            <li class="<?php echo (0 == $selprod_id) ? 'fat-inactive' : ''; ?>">
                                <a href="javascript:void(0);" <?php echo (0 < $selprod_id) ? "onclick='sellerProductLangForm(" . $selprod_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                                </a>
                            </li>
                            <li><a class="<?php echo ($ppoint_type == PolicyPoint::PPOINT_TYPE_WARRANTY) ? 'active' : ''; ?>"
                                    href="javascript:void(0)"
                                    onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_WARRANTY ; ?>)"><?php echo Labels::getLabel('LBL_Link_Warranty_Policies', $adminLangId); ?></a>
                            </li>
                            <li><a class="<?php echo ($ppoint_type == PolicyPoint::PPOINT_TYPE_RETURN) ? 'active' : ''; ?>"
                                    href="javascript:void(0)"
                                    onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_RETURN ; ?>)"><?php echo Labels::getLabel('LBL_Link_Return_Policies', $adminLangId); ?></a>
                            </li>
                        </ul> */ ?>
                        <div class="tabs_panel_wrap">
                            <?php echo $frm->getFormHtml(); ?>
                            <div id="listPolicies" class="col-md-12">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>