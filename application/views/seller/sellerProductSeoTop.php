<div class="tabs tabs-sm tabs--scroll clearfix">
    <ul>
        <li
            class="<?php echo ($seoActiveTab == 'GENERAL') ? 'is-active' : ''?>">
            <a href="javascript:void(0)"
                onclick="getProductSeoGeneralForm(<?php echo "$selprod_id" ?>);">
                <?php echo Labels::getLabel('LBL_Basic', $siteLangId);?>
            </a>
        </li>
        <?php $inactive = ($metaId == 0) ? 'fat-inactive' : ''; ?>
        <li class="<?php echo (0 < $selprod_lang_id) ? 'is-active' : ''; echo $inactive; ?>">
            <a href="javascript:void(0);"
                <?php if ($metaId > 0) { ?>
                onclick='editProductMetaTagLangForm(<?php echo $metaId ?>, <?php echo FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) ?>, "<?php echo $metaType; ?>");'
                <?php } ?>>
                <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
            </a>
        </li>
    </ul>
</div>
