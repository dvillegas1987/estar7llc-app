<link rel="shortcut icon" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId)), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId)), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" sizes="57x57" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '57-57')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" sizes="60x60" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '60-60')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '72-72')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '76-76')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '114-114')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '120-120')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '144-144')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '152-152')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '180-180')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId, '192-192')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId, '32-32')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="icon" type="image/png" sizes="96x96" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId, '96-96')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId, '16-16')), CONF_IMG_CACHE_TIME, '.png'); ?>">
<link rel="manifest" href="<?php echo UrlHelper::generateUrl('Home', 'pwaManifest'); ?>">
<?php
if ($canonicalUrl == '') {
    if (empty(FatApp::getParameters()) && FatApp::getAction() == 'index') {
        $cName = ($controllerName == 'Home') ? '' : $controllerName;
        $canonicalUrl = UrlHelper::generateFullUrl($cName);
    } else {
        $action = empty(FatApp::getAction()) ? 'index' : FatApp::getAction();
        $params = empty(FatApp::getParameters()) ? [] : FatApp::getParameters();
        $canonicalUrl = UrlHelper::generateFullUrl($controllerName, $action, $params);
    }
} ?>

<link rel="canonical" href="<?php echo $canonicalUrl; ?>" />
<style>
    :root {
        <?php if (CommonHelper::isAppUser()) { ?>--brand-color: #<?php echo FatApp::getConfig('CONF_PRIMARY_APP_THEME_COLOR', FatUtility::VAR_STRING, ''); ?>;
        --brand-color-inverse: #<?php echo FatApp::getConfig('CONF_PRIMARY_INVERSE_APP_THEME_COLOR', FatUtility::VAR_STRING, ''); ?>;
        --secondary-color: #<?php echo FatApp::getConfig('CONF_SECONDARY_APP_THEME_COLOR', FatUtility::VAR_STRING, ''); ?>;
        --secondary-color-inverse: #<?php echo FatApp::getConfig('CONF_SECONDARY_INVERSE_APP_THEME_COLOR', FatUtility::VAR_STRING, ''); ?>;
        <?php } else { ?>--brand-color: #<?php echo $themeDetail[ThemeColor::TYPE_BRAND]; ?>;
        --brand-color-inverse: #<?php echo $themeDetail[ThemeColor::TYPE_BRAND_INVERSE]; ?>;
        --secondary-color: #<?php echo $themeDetail[ThemeColor::TYPE_SECONDARY]; ?>;
        --secondary-color-inverse: #<?php echo $themeDetail[ThemeColor::TYPE_SECONDARY_INVERSE]; ?>;
        <?php } ?>--primary-color: #<?php echo $themeDetail[ThemeColor::TYPE_PRIMARY]; ?>;
        --primary-color-inverse: #<?php echo $themeDetail[ThemeColor::TYPE_PRIMARY_INVERSE]; ?>;
        --third-color: #<?php echo $themeDetail[ThemeColor::TYPE_THIRD]; ?>;
        --third-color-inverse: #<?php echo $themeDetail[ThemeColor::TYPE_THIRD_INVERSE]; ?>;

        --body-color: #<?php echo $themeDetail[ThemeColor::TYPE_BODY]; ?>;

        /* --dark-color: ;
        --light-color: ; */

        --gray-color: #<?php echo $themeDetail[ThemeColor::TYPE_GREY]; ?>;
        --gray-light: #<?php echo $themeDetail[ThemeColor::TYPE_GREY_LIGHT]; ?>;

        --border-color: #<?php echo $themeDetail[ThemeColor::TYPE_BORDER]; ?>;
        --border-dark-color: #<?php echo $themeDetail[ThemeColor::TYPE_BORDER_DARK]; ?>;
        --border-light-color: #<?php echo $themeDetail[ThemeColor::TYPE_BORDER_LIGHT]; ?>;

        --font-color: #<?php echo $themeDetail[ThemeColor::TYPE_FONT]; ?>;
        --font-color2: #<?php echo $themeDetail[ThemeColor::TYPE_FONT_SECONDARY]; ?>;
    }
</style>
<script>
    <?php

    $isUserDashboard = ($isUserDashboard) ? 1 : 0;
    echo $str = 'var langLbl = ' . FatUtility::convertToJson($jsVariables, JSON_UNESCAPED_UNICODE) . ';
    var CONF_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 0) . ';
    var CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 3) . ';
    var CONF_ENABLE_GEO_LOCATION = ' . FatApp::getConfig("CONF_ENABLE_GEO_LOCATION", FatUtility::VAR_INT, 0) . ';
    var CONF_MAINTENANCE = ' . FatApp::getConfig("CONF_MAINTENANCE", FatUtility::VAR_INT, 0) . ';
    var extendEditorJs = ' . $extendEditorJs . ';
    var themeActive = ' . $themeActive . ';
    var currencySymbolLeft = "' . $currencySymbolLeft . '";
    var currencySymbolRight = "' . $currencySymbolRight . '";
    var isUserDashboard = "' . $isUserDashboard . '";
    var className = "' . FatApp::getController() . '";
    var actionName = "' . FatApp::getAction() . '";
    if( CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES <= 0  ){
        CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = 3;
    }';
    if (FatApp::getConfig("CONF_ENABLE_ENGAGESPOT_PUSH_NOTIFICATION", FatUtility::VAR_STRING, '')) {
        echo FatApp::getConfig("CONF_ENGAGESPOT_PUSH_NOTIFICATION_CODE", FatUtility::VAR_STRING, '');
        if (UserAuthentication::getLoggedUserId(true) > 0) { ?>
            Engagespot.init()
            Engagespot.identifyUser('YT_<?php echo UserAuthentication::getLoggedUserId(); ?>');
        <?php }
    }

    if (Message::getMessageCount() || Message::getErrorCount() || Message::getDialogCount() || Message::getInfoCount()) { ?>
            (function() {
                if (CONF_AUTO_CLOSE_SYSTEM_MESSAGES == 1) {
                    var time = CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES * 1000;
                    setTimeout(function() {
                        $.systemMessage.close();
                    }, time);
                }
            })();
    <?php }
    $pixelId = FatApp::getConfig("CONF_FACEBOOK_PIXEL_ID", FatUtility::VAR_STRING, '');
    if ('' != $pixelId) { ?>
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo $pixelId; ?>');
        fbq('track', 'PageView');
        var fbPixel = true;
    <?php } ?>
</script>
<?php

if (FatApp::getConfig("CONF_GOOGLE_TAG_MANAGER_HEAD_SCRIPT", FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig("CONF_GOOGLE_TAG_MANAGER_HEAD_SCRIPT", FatUtility::VAR_STRING, '');
}
if (FatApp::getConfig("CONF_HOTJAR_HEAD_SCRIPT", FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig("CONF_HOTJAR_HEAD_SCRIPT", FatUtility::VAR_STRING, '');
}
if (FatApp::getConfig("CONF_DEFAULT_SCHEMA_CODES_SCRIPT", FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig("CONF_DEFAULT_SCHEMA_CODES_SCRIPT", FatUtility::VAR_STRING, '');
}
if (isset($layoutTemplate) && $layoutTemplate != '') { ?>
    <link rel="stylesheet" href="<?php echo UrlHelper::generateUrl('ThemeColor', $layoutTemplate, array($layoutRecordId)); ?>" />
<?php }
