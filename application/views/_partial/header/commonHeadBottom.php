<?php
if (isset($includeEditor) && $includeEditor) { ?>
    <script language="javascript" type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>innovas/scripts/innovaeditor.js"></script>
    <script src="<?php echo CONF_WEBROOT_URL; ?>innovas/scripts/common/webfont.js" type="text/javascript"></script>
<?php  }  ?>
</head>
<?php
$bodyClass = ($controllerName == 'Home') ? 'home' : 'inner';
if ($controllerName == 'Blog') {
    $bodyClass = 'is--blog';
}
if ($controllerName == 'Checkout' || $controllerName == 'SubscriptionCheckout') {
    $bodyClass = 'is-checkout';
}

if (isset($isUserDashboard) && $isUserDashboard && strtolower($controllerName) != 'subscriptioncheckout') {
    $bodyClass = 'is-dashboard my-dashboard';
    $expanded = 'sidebar-is-reduced';
    if (!array_key_exists('openSidebar', $_COOKIE)) {
        setcookie('openSidebar', 1, 0, CONF_WEBROOT_URL);
    }
    if (array_key_exists('openSidebar', $_COOKIE) && 0 < $_COOKIE['openSidebar'] && array_key_exists('screenWidth', $_COOKIE) && applicationConstants::MOBILE_SCREEN_WIDTH < $_COOKIE['screenWidth']) {
        $expanded = 'sidebar-is-expanded';
    }

    $bodyClass = $bodyClass . ' ' . $expanded;
}


if (CommonHelper::demoUrl()) {
    $bodyClass .= ' have-fixed-btn';
}
?>
<body class="<?php echo $bodyClass; ?> ">
    <?php
    $alertClass = '';
    if (Message::getInfoCount() > 0) {
        $alertClass = 'alert--info';
    } elseif (Message::getErrorCount() > 0) {
        $alertClass = 'alert--danger';
    } elseif (Message::getMessageCount() > 0) {
        $alertClass = 'alert--success';
    }
    ?>
    <?php
    if (FatApp::getConfig("CONF_GOOGLE_TAG_MANAGER_BODY_SCRIPT", FatUtility::VAR_STRING, '')) {
        echo FatApp::getConfig("CONF_GOOGLE_TAG_MANAGER_BODY_SCRIPT", FatUtility::VAR_STRING, '');
    }
    ?>
    <div class="system_message alert alert--positioned-top-full <?php echo $alertClass; ?>" style="display:none">
        <div class="close"></div>
        <div class="content">
            <?php
            $haveMsg = false;
            if (Message::getMessageCount() || Message::getErrorCount() || Message::getDialogCount() || Message::getInfoCount()) {
                $haveMsg = true;
                echo html_entity_decode(Message::getHtml());
            } ?>
        </div>
    </div>
    <?php /*?> <div id="quick-view-section" class="quick-view"></div>    <?php */ ?>