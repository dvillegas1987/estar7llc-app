<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
//$_SESSION['geo_location'] = true;
if ($controllerName != 'GuestUser' && $controllerName != 'Error') {
    $_SESSION['referer_page_url'] = UrlHelper::getCurrUrl();
}
$htmlClass = '';
$actionName = FatApp::getAction();
if ($controllerName == 'Products' && $actionName == 'view') {
    $htmlClass = 'product-view';
}
$additionalAttributes = (CommonHelper::getLayoutDirection() == 'rtl') ? 'direction="rtl" style="direction: rtl;"' : '';
?>
<!DOCTYPE html>
<html lang="<?php echo strtolower($siteLangCode); ?>" data-version="<?php echo CONF_WEB_APP_VERSION; ?>" data-theme="light" dir="<?php echo CommonHelper::getLayoutDirection(); ?>" prefix="og: http://ogp.me/ns#" <?php echo $additionalAttributes; ?> class="<?php echo $htmlClass; ?> <?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) {
                                                                                                                                                                                                                                                                                                echo "sticky-demo-header";
                                                                                                                                                                                                                                                                                            } ?>">

<head>
    <!-- Yo!Kart -->
    <meta charset="utf-8">
    <meta name="author" content="">
    <!-- Mobile Specific Metas ===================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php if (CommonHelper::demoUrl() && $controllerName != 'Home') { ?>
        <meta name="robots" content="noindex" />
    <?php } ?>
    <!-- favicon ================================================== -->
    <meta name="theme-color" content="#<?php echo $themeDetail[ThemeColor::TYPE_PRIMARY]; ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '144-144')); ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="msapplication-navbutton-color" content="#<?php echo $themeDetail[ThemeColor::TYPE_PRIMARY]; ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="msapplication-starturl" content="/">
    <?php
    if (isset($socialShareContent) && !empty($socialShareContent)) { ?>
        <!-- OG Product Facebook Meta [ -->
        <meta property="og:type" content="product" />
        <meta property="og:title" content="<?php echo $socialShareContent['title']; ?>" />
        <meta property="og:site_name" content="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, ''); ?>" />
        <meta property="og:image" content="<?php echo $socialShareContent['image']; ?>" />
        <meta property="og:url" content="<?php echo UrlHelper::getCurrUrl(); ?>" />
        <meta property="og:description" content="<?php echo html_entity_decode($socialShareContent['description'], ENT_QUOTES, 'utf-8'); ?>" />
        <!-- ]   -->
        <!--Here is the Twitter Card code for this product  -->
        <?php if (!empty(FatApp::getConfig("CONF_TWITTER_USERNAME", FatUtility::VAR_STRING, ''))) { ?>
            <meta name="twitter:card" content="product">
            <meta name="twitter:site" content="@<?php echo FatApp::getConfig("CONF_TWITTER_USERNAME", FatUtility::VAR_STRING, ''); ?>">
            <meta name="twitter:title" content="<?php echo $socialShareContent['title']; ?>">
            <meta name="twitter:description" content="<?php echo html_entity_decode($socialShareContent['description'], ENT_QUOTES, 'utf-8'); ?>">
            <meta name="twitter:image:src" content="<?php echo $socialShareContent['image']; ?>">
        <?php }  ?>
        <!-- End Here is the Twitter Card code for this product  -->
    <?php
    } else {
        $metaData = $this->writeMetaTags(true);
        $title = isset($metaData['meta_title']) ? $metaData['meta_title'] . ' ' : ' ';
        $title .= FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '');
        $description = (isset($metaData['meta_description'])) ? $metaData['meta_description'] : $title;
        $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_META_IMAGE, 0, 0, $siteLangId);
        $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
        $image = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'metaImage', array($siteLangId), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    ?>
        <meta property="og:type" content="website" />
        <meta property="og:title" content="<?php echo $title; ?>" />
        <meta property="og:site_name" content="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, ''); ?>" />
        <meta property="og:url" content="<?php echo UrlHelper::getCurrUrl(); ?>" />
        <meta property="og:description" content="<?php echo $description; ?> " />
        <meta property="og:image" content="<?php echo $image; ?>" />
        <?php if (!empty(FatApp::getConfig("CONF_TWITTER_USERNAME", FatUtility::VAR_STRING, ''))) { ?>
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:site" content="@<?php echo FatApp::getConfig("CONF_TWITTER_USERNAME", FatUtility::VAR_STRING, ''); ?>">
            <meta name="twitter:title" content="<?php echo $title; ?>">
            <meta name="twitter:description" content="<?php echo $description; ?>">
            <meta name="twitter:image:src" content="<?php echo $image; ?>">

    <?php }
    } ?>