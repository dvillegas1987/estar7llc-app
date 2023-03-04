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
                <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?> data-ratio= "<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?> src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_'.$siteLangId) ?>">
            </a>
        </div>
        <?php
            $isOpened = '';
            if (array_key_exists('openSidebar', $_COOKIE) && !empty(FatUtility::int($_COOKIE['openSidebar'])) && array_key_exists('screenWidth', $_COOKIE) && applicationConstants::MOBILE_SCREEN_WIDTH < FatUtility::int($_COOKIE['screenWidth'])) {
                $isOpened = 'is-opened';
            }
        ?>
        <div class="js-hamburger hamburger-toggle <?php echo $isOpened; ?>"><span class="bar-top"></span><span class="bar-mid"></span><span class="bar-bot"></span></div>
    </div>
    <div class="sidebar__content custom-scrollbar" id="scrollElement-js" data-simplebar>
        <nav class="dashboard-menu">
            <ul>
                <?php if (User::canViewAffiliateTab()) { ?>
            <li class="menu__item <?php echo ($controller == 'affiliate' && $action == 'referredbyme') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_Sharing", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Affiliate', 'ReferredByMe'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-reffered" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-reffered"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Referral", $siteLangId); ?></span></a></div>
                </li> <?php
        } ?> <li class="menu__item <?php echo ($controller == 'account' && $action == 'profileinfo') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_My_Account", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'ProfileInfo'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Account", $siteLangId); ?></span></a></div>
                </li> <?php if (!User::canViewAffiliateTab()) {
            ?> <li class="menu__item <?php echo ($controller == 'account' && $action == 'messages') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'Messages'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-messages" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svgdash-messages"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?></span></a></div>
                </li> <?php
        } ?> <li class="menu__item <?php echo ($controller == 'account' && $action == 'credits') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_My_Credits", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'credits');?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId);?></span></a></div>
                </li> <?php if (!User::canViewAffiliateTab()) {
            ?> <li class="menu__item <?php echo ($controller == 'account' && $action == 'wishlist') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel("LBL_Wishlist/Favorites", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'wishlist'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-wishlist-favorite" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-wishlist-favorite"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Wishlist/Favorites', $siteLangId); ?></span></a></div>
                </li> <?php
        } ?> 
            <li class="menu__item <?php echo ($controller == 'account' && $action == 'changeemailpassword') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_UPDATE_CREDENTIALS', $siteLangId);?>" href="<?php echo UrlHelper::generateUrl('Account', 'changeEmailPassword');?>">
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
