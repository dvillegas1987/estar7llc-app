<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="logo-payment">
    <?php   
    $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PAYMENT_PAGE_LOGO, 0, 0, $siteLangId, false);
    $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId);
    ?>
    <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?> data-ratio= "<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?> src="<?php echo UrlHelper::generateFullUrl('Image','paymentPageLogo',array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" />
</div>