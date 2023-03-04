<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($includeEditor) && $includeEditor == true) {
    $extendEditorJs    = 'true';
} else {
    $extendEditorJs    = 'false';
}
if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
    $themeActive = 'true';
} else {
    $themeActive = 'false';
}
$commonHeadData = array(
    'siteLangId' => $siteLangId,
    'siteLangCode' => $siteLangCode,
    'siteLangCountryCode' => $siteLangCountryCode,
    'controllerName' => $controllerName,
    'action' => $action,
    'jsVariables' => $jsVariables,
    'extendEditorJs' => $extendEditorJs,
    'themeDetail' => $themeDetail,
    'themeActive' => $themeActive,
    'currencySymbolLeft' => $currencySymbolLeft,
    'currencySymbolRight' => $currencySymbolRight,
    'isUserDashboard' => $isUserDashboard,
    'canonicalUrl' => isset($canonicalUrl) ? $canonicalUrl: '',
);
if (isset($layoutTemplate) && $layoutTemplate != '') {
    $commonHeadData['layoutTemplate'] = $layoutTemplate;
    $commonHeadData['layoutRecordId'] = $layoutRecordId;
}
if (isset($socialShareContent) && $socialShareContent != '') {
    $commonHeadData['socialShareContent'] = $socialShareContent;
}
if (isset($includeEditor) && $includeEditor == true) {
    $commonHeadData['includeEditor'] = $includeEditor;
}
$this->includeTemplate('_partial/header/commonHeadTop.php', $commonHeadData, false);
/* This is not included in common head, because, commonhead file not able to access the $this->Controller and $this->action[ */
echo $this->writeMetaTags();
/* ] */
if (CommonHelper::demoUrl() && $controllerName == 'Blog') {
    echo '<meta name="robots" content="noindex">';
}
$this->includeTemplate('_partial/header/commonHeadMiddle.php', $commonHeadData, false);

/* This is not included in common head, because, if we are adding any css/js from any controller then that file is not included[ */
echo $this->getJsCssIncludeHtml(!CONF_DEVELOPMENT_MODE);
/* ] */

$this->includeTemplate('_partial/header/commonHeadBottom.php', $commonHeadData, false);
?>
<div class="wrapper">
<header class="header header-blog <?php echo (true === CommonHelper::isAppUser()) ? 'd-none' : ''; ?>">
	<?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) { 
		$this->includeTemplate('restore-system/top-header.php');    
	} ?>
    <?php $this->includeTemplate('_partial/blogNavigation.php'); ?>
</header>
<div class="clear"></div>
