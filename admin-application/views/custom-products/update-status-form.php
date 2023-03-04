<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Product_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="tabs_nav_container responsive flat">
            <ul class="tabs_nav">
                <li><a <?php echo (0 < $preqId) ? "onclick='productForm( ".$preqId.", 0 );'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                </li>
                <?php /* <li><a <?php echo (0 < $preqId) ? "onclick='sellerProductForm( ".$preqId.");'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Inventory/Info', $adminLangId); ?></a>
                </li> */ ?>
                <li><a <?php echo (0 < $preqId) ? "onclick='customCatalogSpecifications( ".$preqId." );'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specifications', $adminLangId);?></a>
                </li>
                <li class="<?php echo (0 == $preqId) ? 'fat-inactive' : ''; ?>">
                    <a href="javascript:void(0);" <?php echo (0 < $preqId) ? "onclick='productLangForm(" . $preqId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                        <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                    </a>
                </li>
                <?php if (!empty($productOptions) && count($productOptions)>0) {
                        ?>
                <li><a <?php echo (0 < $preqId) ? "onClick='customEanUpcForm( ".$preqId.");'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_EAN/UPC_setup', $adminLangId); ?></a>
                </li>
                <?php
                    } ?>
                <li><a class="active" <?php echo ($preqId) ? "onclick='updateStatusForm( ".$preqId.");'" : ""; ?>
                        href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Change_Status', $adminLangId); ?></a>
                </li>
            </ul>
            <div class="tabs_panel_wrap">
                <?php
                    $frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
                    $frm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
                    $frm->developerTags['colClassPrefix'] = 'col-md-';
                    $frm->developerTags['fld_default_col'] = 12;
                    echo $frm->getFormHtml(); ?>
            </div>

        </div>
    </div>
</section>