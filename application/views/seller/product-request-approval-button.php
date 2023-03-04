<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  
if (0 < $preqId && !User::isCatalogRequestSubmittedForApproval($preqId)) { ?>
    <a href="<?php echo UrlHelper::generateUrl('seller', 'approveCustomCatalogProducts', array($preqId));?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Submit_For_Approval', $siteLangId)?></a>
<?php }  ?>