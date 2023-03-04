<div class="container container-fluid container--fluid">
    <div class="tabs--inline clearfix">
        <ul class="tabs_nav tabs_nav--internal">
            <li>
                <a class="<?php echo ($seoActiveTab == 'GENERAL') ? 'active' : ''?>"
                    href="javascript:void(0)" onclick="getProductSeoGeneralForm(<?php echo "$selprod_id" ?>);">
                    <?php echo Labels::getLabel('LBL_Basic', $adminLangId);?>
                </a>
            </li>
            <li class="<?php echo (0 == $metaId) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" class="<?php echo ($seoActiveTab != 'GENERAL') ? 'active' : ''; ?>"
                    <?php if (0 < $metaId) { ?>
                        onclick='editProductMetaTagLangForm(<?php echo $metaId ?>,<?php echo FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) ?>);'
                    <?php } ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
        </ul>
    </div>
</div>