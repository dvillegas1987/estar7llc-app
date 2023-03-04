<?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl() && 'subscriptioncheckout' != strtolower($controllerName)) {
    $this->includeTemplate('restore-system/top-header.php');
} ?>

<?php if ($controllerName != 'SubscriptionCheckout') { ?>
    <div class="wrapper">
        <header id="header-dashboard" class="header-dashboard no-print">

            <?php if ((User::canViewSupplierTab() && User::canViewBuyerTab()) || (User::canViewSupplierTab() && User::canViewAdvertiserTab() && $userPrivilege->canViewPromotions(0, true)) || (User::canViewBuyerTab() && User::canViewAdvertiserTab())) { ?>
                <div class="dropdown dashboard-user">
                    <button class="btn btn-outline-brand dropdown-toggle" type="button" id="dashboardDropdown" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                        <?php echo ($activeTab == 'S') ? Labels::getLabel('Lbl_Seller', $siteLangId) : (($activeTab == 'B') ? Labels::getLabel('Lbl_Buyer', $siteLangId) : (($activeTab == 'Ad') ? Labels::getLabel('Lbl_Advertiser', $siteLangId) : '')) ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-fit dropdown-menu-anim" aria-labelledby="dashboardDropdown">
                        <ul class="nav nav-block">
                            <?php if (User::canViewSupplierTab()) { ?>
                                <li class="nav__item <?php echo ($activeTab == 'S') ? 'is-active' : ''; ?>">
                                    <a class="dropdown-item nav__link" href="<?php echo UrlHelper::generateUrl('Seller'); ?>"><?php echo Labels::getLabel('Lbl_Seller', $siteLangId); ?></a>
                                </li>
                            <?php } ?>
                            <?php if (User::canViewBuyerTab()) { ?>
                                <li class="nav__item <?php echo ($activeTab == 'B') ? 'is-active' : ''; ?>">
                                    <a class="dropdown-item nav__link" href="<?php echo UrlHelper::generateUrl('Buyer'); ?>"><?php echo Labels::getLabel('Lbl_Buyer', $siteLangId); ?></a>
                                </li>
                            <?php } ?>
                            <?php if (User::canViewAdvertiserTab() && $userPrivilege->canViewPromotions(0, true)) { ?>
                                <li class="nav__item <?php echo ($activeTab == 'Ad') ? 'is-active' : ''; ?>">
                                    <a class="dropdown-item nav__link" href="<?php echo UrlHelper::generateUrl('Advertiser'); ?>"><?php echo Labels::getLabel('Lbl_Advertiser', $siteLangId); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>
            <div class="header-icons-group">
                <?php 
                    $getOrgUrl = (CONF_DEVELOPMENT_MODE) ? true : false; 
                    $userActiveTab = false;
                    if (User::canViewSupplierTab() && (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] == 'S')) {
                        $userActiveTab = true;
                        $dashboardUrl = UrlHelper::generateUrl('Seller');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Seller', '', array(), '', null, false, $getOrgUrl);
                    } elseif (User::canViewBuyerTab()  && (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] == 'B')) {
                        $userActiveTab = true;
                        $dashboardUrl = UrlHelper::generateUrl('Buyer');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Buyer', '', array(), '', null, false, $getOrgUrl);
                    } elseif (User::canViewAdvertiserTab() && (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] == 'Ad')) {
                        $userActiveTab = true;
                        $dashboardUrl = UrlHelper::generateUrl('Advertiser');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Advertiser', '', array(), '', null, false, $getOrgUrl);
                    } elseif (User::canViewAffiliateTab()  && (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] == 'AFFILIATE')) {
                        $userActiveTab = true;
                        $dashboardUrl = UrlHelper::generateUrl('Affiliate');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Affiliate', '', array(), '', null, false, $getOrgUrl);
                    }

                    if (!$userActiveTab) {
                        $dashboardUrl = UrlHelper::generateUrl('Account');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Account', '', array(), '', null, false, $getOrgUrl);
                    }
                ?>
                <ul class="c-header-links">
                    <li>
                        <a title="<?php echo Labels::getLabel('LBL_Dashboard', $siteLangId); ?>" data-org-url="<?php echo $dashboardOrgUrl; ?>" href="<?php echo $dashboardUrl; ?>">
                            <i class="icn icn--dashboard">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dashboard" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dashboard"></use>
                                </svg></i></a>
                    </li>
                    <li><a title="<?php echo Labels::getLabel('LBL_Home', $siteLangId); ?>" target="_blank" href="<?php echo UrlHelper::generateUrl('Home'); ?>"><i class="icn icn--home">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#back-home" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#back-home"></use>
                                </svg></i></a></li>
                    <?php if ($isShopActive && $shop_id > 0 && $activeTab == 'S') { ?>
                        <li><a title="<?php echo Labels::getLabel('LBL_Shop', $siteLangId); ?>" data-org-url="<?php echo UrlHelper::generateUrl('Shops', 'view', array($shop_id), '', null, false, $getOrgUrl); ?>" target="_blank" href="<?php echo UrlHelper::generateUrl('Shops', 'view', array($shop_id)); ?>"><i class="icn icn--home">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop"></use>
                                    </svg></i></a></li>
                    <?php } ?>
                </ul>
                <?php if ($userPrivilege->canViewMessages(0, true)) { ?>
                    <div class="c-header-icon bell">
                        <a data-org-url="<?php echo UrlHelper::generateUrl('Account', 'Messages', array(), '', null, false, $getOrgUrl); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'Messages'); ?>" title="<?php echo Labels::getLabel('LBL_Messages', $siteLangId); ?>">
                            <i class="icn"><svg class="svg bell-shake-delay">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#notification" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#notification"></use>
                                </svg>
                            </i>
                            <span class="h-badge"><span class="heartbit"></span><?php echo CommonHelper::displayBadgeCount($todayUnreadMessageCount, 9); ?></span></a>
                    </div>
                <?php } ?>
                <div class="short-links">
                    <ul>
                        <?php /*$this->includeTemplate('_partial/headerLanguageArea.php');*/ ?>
                        <?php $this->includeTemplate('_partial/headerUserArea.php', array('isUserDashboard' => $isUserDashboard)); ?>
                    </ul>
                </div>
            </div>
        </header>
        <div class="display-in-print text-center">
            <?php
            $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_INVOICE_LOGO, 0, 0, $siteLangId, false);
            $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId);
            ?>
            <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?> data-ratio="<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?> src="<?php echo UrlHelper::generateFullUrl('Image', 'invoiceLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>">
        </div>
    <?php } ?>