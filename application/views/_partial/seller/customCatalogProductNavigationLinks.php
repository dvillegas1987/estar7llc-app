<?php $inactive = ($preqId==0) ? 'fat-inactive' : ''; ?>
<ul>
    <li class="<?php echo ($activeTab == 'GENERAL') ? 'is-active' : $inactive; ?>">
        <a onClick="customCatalogProductForm(<?php echo $preqId;?>,<?php echo $preqCatId;?>)" href="javascript:void(0);">
            <?php echo Labels::getLabel('LBL_Basic', $siteLangId);?>
        </a>
    </li>
    <li class="<?php echo ($activeTab == 'INVENTORY') ? 'is-active' : $inactive; ?>">
        <a <?php echo ($preqId) ? "onclick='customCatalogSellerProductForm( ".$preqId." );'" : ""; ?> href="javascript:void(0);">
            <?php echo Labels::getLabel('LBL_Inventory/Info', $siteLangId);?>
        </a>
    </li>
    <li class="<?php echo ($activeTab == 'SPECIFICATIONS') ? 'is-active' : $inactive; ?>">
        <a <?php echo ($preqId) ? "onclick='customCatalogSpecifications( ".$preqId." );'" : ""; ?> href="javascript:void(0);">
            <?php echo Labels::getLabel('LBL_Specifications', $siteLangId);?>
        </a>
    </li>
    <li class="<?php echo ($activeTab == 'PRODUCTLANGFORM' && 0 < $product_lang_id) ? 'is-active' : $inactive; ?>">
        <a href="javascript:void(0);" <?php echo ($preqId) ? " onclick='customCatalogProductLangForm( ".$preqId.",".FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1)." );'" : ""; ?>>
            <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
        </a>
    </li>
    <?php
    if (!empty($productOptions)) { ?>
        <li class="<?php echo ($activeTab == 'CUSTOMEANUPC') ? 'is-active' : $inactive; ?>">
            <a <?php echo ($preqId) ? "onclick='customEanUpcForm( ".$preqId." );'" : ""; ?> href="javascript:void(0);">
                <?php echo Labels::getLabel('LBL_EAN/UPC_setup', $siteLangId); ?>
            </a>
        </li>
    <?php } ?>
    <li class="<?php echo ($activeTab == 'PRODUCTIMAGES') ? 'is-active' : $inactive; ?>">
        <a href="javascript:void(0);" <?php echo ($preqId) ? "onclick='customCatalogProductImages( ".$preqId." );'" : ""; ?>>
            <?php echo Labels::getLabel('Lbl_Product_Images', $siteLangId);?>
        </a>
    </li>
</ul>

<?php if (0 < $preqId && !User::isCatalogRequestSubmittedForApproval($preqId)) {?>
    <a href="<?php echo UrlHelper::generateUrl('seller', 'approveCustomCatalogProducts', array($preqId));?>" class="btn btn-outline-brand btn-sm mleft-auto">
        <strong><?php echo Labels::getLabel('LBL_Submit_For_Approval', $siteLangId)?></strong>
    </a>
<?php } ?>
