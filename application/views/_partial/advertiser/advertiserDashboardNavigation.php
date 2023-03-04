<?php
$controller = strtolower($controller);
$action = strtolower($action);
?> <sidebar class="sidebar no-print">
    <div class="logo-wrapper"> <?php
        if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
            $logoUrl = UrlHelper::generateUrl('home', 'index');
        } else {
            $logoUrl = UrlHelper::generateUrl();
        }
        ?>
        <?php
        $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, $siteLangId, false);
        $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId);
        $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
        $siteLogo = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'siteLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
        ?>
        <div class="logo-dashboard">
            <a href="<?php echo $logoUrl; ?>">
                <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?> data-ratio= "<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?> src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>">
            </a>
        </div>
        <?php
            $isOpened = '';
            if (isset($_COOKIE['openSidebar']) && !empty(FatUtility::int($_COOKIE['openSidebar'])) && array_key_exists('screenWidth', $_COOKIE) && applicationConstants::MOBILE_SCREEN_WIDTH < FatUtility::int($_COOKIE['screenWidth'])) {
                $isOpened = 'is-opened';
            }
        ?>
        <div class="js-hamburger hamburger-toggle <?php echo $isOpened; ?>"><span class="bar-top"></span><span class="bar-mid"></span><span class="bar-bot"></span></div>
    </div>
    <div class="sidebar__content custom-scrollbar" id="scrollElement-js" data-simplebar>
        <nav class="dashboard-menu">
            <ul>
                <?php if (User::canViewAdvertiserTab() && $userPrivilege->canViewPromotions(0, true)) { ?>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Promotions", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'advertiser' && ($action == 'promotions' || $action == 'viewpromotions')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_My_Promotions", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('advertiser', 'promotions'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-promotions" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-promotions"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Promotions", $siteLangId); ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'advertiser' && ($action == 'promotioncharges' || $action == 'viewpromotions')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_Promotion_Charges", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('advertiser', 'promotionCharges'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-give-money" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-give-money"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Promotion_Charges", $siteLangId); ?></span></a></div>
                </li>
				<?php if ($userParentId == UserAuthentication::getLoggedUserId()) { ?>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'credits') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_My_Credits", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'credits'); ?>"><i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId); ?></span></a></div>
                </li>
				<?php }?>
                <li class="divider"></li>
                <?php } ?>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Profile', $siteLangId);?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'profileinfo') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_My_Account", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'ProfileInfo'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Account", $siteLangId); ?></span></a></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'changeemailpassword') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_UPDATE_CREDENTIALS", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'changeEmailPassword'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-email" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-password"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_UPDATE_CREDENTIALS', $siteLangId);?></span></a></div>
                </li>
                <?php $this->includeTemplate('_partial/dashboardLanguageArea.php'); ?>
            </ul>
        </nav>
    </div>
</sidebar>
