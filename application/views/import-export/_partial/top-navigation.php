<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="tabs tabs--small   tabs--scroll clearfix">
    <ul class="arrowTabs">
        <li class="<?php echo !empty($action) && $action=='generalInstructions'?'is-active' : '';?>"><a href="javascript:void(0)" onClick="loadForm('general_instructions')"><?php echo Labels::getLabel('LBL_Instructions', $siteLangId); ?></a></li>
        <li class="<?php echo !empty($action) && $action=='export'?'is-active' : '';?>"><a href="javascript:void(0)" onClick="loadForm('export')"><?php echo Labels::getLabel('LBL_Export', $siteLangId); ?></a></li>
        <?php if ($canEditImportExport) { ?>
            <li class="<?php echo !empty($action) && $action=='import'?'is-active' : '';?>"><a href="javascript:void(0)" onClick="loadForm('import')"><?php echo Labels::getLabel('LBL_Import', $siteLangId); ?></a></li>
            <li class="<?php echo !empty($action) && $action=='settings'?'is-active' : '';?>"><a href="javascript:void(0)" onClick="loadForm('settings')"><?php echo Labels::getLabel('LBL_Settings', $siteLangId); ?></a></li>
            <li class="<?php echo !empty($action) && $action=='inventoryUpdate'?'is-active' : '';?>"><a href="javascript:void(0)" onClick="loadForm('inventoryUpdate')"><?php echo Labels::getLabel('LBL_Inventory_Update', $siteLangId); ?></a></li>
        <?php } ?>
        <?php $canRequest = FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0);
        $canRequestCustomProd = FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0);
        if (0 < $canRequest && 0 < $canRequestCustomProd && $canUploadBulkImages) { ?>
            <li class="<?php echo !empty($action) && $action == 'bulkMedia' ? 'is-active' : '';?>">
                <a href="javascript:void(0)" onClick="loadForm('bulk_media')">
                    <?php echo Labels::getLabel('LBL_Upload_Bulk_Media', $siteLangId); ?>
                </a>
            </li>
        <?php } ?>
    </ul>
</div>
