<?php
$controller = strtolower($controller);
$action = strtolower($action);
?> <sidebar class="sidebar no-print">
    <div class="logo-wrapper">
        <?php
        if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
            $logoUrl = UrlHelper::generateUrl('home', 'index');
        } else {
            $logoUrl = UrlHelper::generateUrl();
        }
        ?>
        <?php
        $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, $siteLangId, false);
        $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId);
        $uploadedTime = isset($fileData['afile_updated_at']) ? AttachedFile::setTimeParam($fileData['afile_updated_at']) : '';
        $siteLogo = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'siteLogo', array($siteLangId), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');

        $ratio = '';
        if (isset($fileData['afile_aspect_ratio']) && $fileData['afile_aspect_ratio'] > 0 && isset($aspectRatioArr[$fileData['afile_aspect_ratio']])) {
            $ratio = $aspectRatioArr[$fileData['afile_aspect_ratio']];
        }

        ?>
        <div class="logo-dashboard">
            <a href="<?php echo $logoUrl; ?>">
                <img data-ratio="<?php echo $ratio; ?>" src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>">
            </a>
        </div>

        <?php $isOpened = '';
        if (array_key_exists('openSidebar', $_COOKIE) && !empty(FatUtility::int($_COOKIE['openSidebar'])) && array_key_exists('screenWidth', $_COOKIE) && applicationConstants::MOBILE_SCREEN_WIDTH < FatUtility::int($_COOKIE['screenWidth'])) {
            $isOpened = 'is-opened';
        } ?>
        <div class="js-hamburger hamburger-toggle <?php echo $isOpened; ?>"><span class="bar-top"></span><span class="bar-mid"></span><span class="bar-bot"></span></div>
    </div>
    <div class="sidebar__content custom-scrollbar" id="scrollElement-js" data-simplebar>
        <nav class="dashboard-menu">
            <ul>
                <?php
                if (
                    $userPrivilege->canViewShop(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewProducts(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewProductTags(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewProductOptions(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewTaxCategory(UserAuthentication::getLoggedUserId(), true)
                ) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Shop', $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewShop(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'shop') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Manage_Shop', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'shop'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Manage_Shop', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <!-- <li class="menu__item"><div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_View_Shop', $siteLangId); ?>" target="_blank" href="<?php echo UrlHelper::generateUrl('Shops', 'view', array($shop_id)); ?>"><i class="icn shop"><svg class="svg"><use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-view-shop" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-view-shop"></use></svg>
                   </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_View_Shop', $siteLangId); ?></span></a></    div></li> -->
                    <?php if ($userPrivilege->canViewProducts(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'customcatalogproductform' || $action == 'customproductform' || $action == 'catalog' || $action == 'products' || $action == 'customcatalogproducts')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Products', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('seller', 'products'); //echo UrlHelper::generateUrl('seller', 'catalog'); 
                                                                                                                ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-products" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-products"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Shop_Inventory', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if (User::canAddCustomProduct() && $userPrivilege->canViewProductTags(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'producttags') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Product_Tags', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'productTags'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#product-tags" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#product-tags"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Product_Tags', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php $canAddCustomProd = FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0);
                    if (0 < $canAddCustomProd && $userPrivilege->canViewProductOptions(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'options') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Product_Options', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'options'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-options" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-options"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Product_Options', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0) && $userPrivilege->canViewTaxCategory(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'taxcategories' || $action == 'taxrules')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Tax_Categories', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'taxCategories'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-tax-category" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-tax-category"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Tax_Categories', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewSellerRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'sellerrequests' && $action == 'index') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Requests', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('SellerRequests'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#requests" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#requests"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Requests', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>

                <?php if (
                    $userPrivilege->canViewShippingProfiles(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewShippingPackages(UserAuthentication::getLoggedUserId(), true)
                ) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Shipping', $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewShippingProfiles(UserAuthentication::getLoggedUserId(), true) && !FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) { ?>
                        <li class="menu__item <?php echo ($controller == 'shippingprofile') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Shipping_Profiles', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('shippingProfile'); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-profile" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-profile"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Shipping_Profiles', $siteLangId); ?></span></a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewShippingPackages(UserAuthentication::getLoggedUserId(), true) && FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1) && FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) { ?>
                        <li class="menu__item <?php echo ($controller == 'shippingpackages') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Shipping_Packages', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('shippingPackages'); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-package" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shipping-package"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Shipping_Packages', $siteLangId); ?></span></a>
                            </div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>

                <?php if (
                    $userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId(), true)
                ) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Sales', $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'sales') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Orders', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'Sales'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-sales" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-sales"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Orders', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'ordercancellationrequests') ? 'is-active' : '' ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Order_Cancellation_Requests', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'orderCancellationRequests'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-cancellation-request" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-cancellation-request"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Order_Cancellation_Requests", $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'orderreturnrequests' || $action == 'vieworderreturnrequest')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Order_Return_Requests', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'orderReturnRequests'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-return-request" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-return-request"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Order_Return_Requests", $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>
                <?php if (
                    $userPrivilege->canViewSpecialPrice(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewVolumeDiscount(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewBuyTogetherProducts(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewRelatedProducts(UserAuthentication::getLoggedUserId(), true)
                ) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Promotions', $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewSpecialPrice(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'specialprice') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Special_Price', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'specialPrice'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#special-price" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#special-price"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Special_Price', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewVolumeDiscount(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'volumediscount') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'volumeDiscount'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#volume-discount" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#volume-discount"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewBuyTogetherProducts(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'upsellproducts') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Buy_Together_Products', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'upsellProducts'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#buy-together-products" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#buy-together-products"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Buy_Together_Products', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewRelatedProducts(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'relatedproducts') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Related_Products', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'RelatedProducts'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#related-products" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#related-products"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Related_Products', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php $obj = new Plugin();
                    $pluginData = $obj->getDefaultPluginData(Plugin::TYPE_ADVERTISEMENT_FEED, null, $siteLangId);
                    if (false !== $pluginData && !empty($pluginData) && 0 < $pluginData['plugin_active'] && $userPrivilege->canViewAdvertisementFeed(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == strtolower($pluginData['plugin_code'])) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo $pluginData['plugin_name']; ?>" href="<?php echo UrlHelper::generateUrl($pluginData['plugin_code']); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-promotions" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-promotions"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo $pluginData['plugin_name']; ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>
                <?php if (
                    $userPrivilege->canViewMetaTags(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewUrlRewriting(UserAuthentication::getLoggedUserId(), true)
                ) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_SEO', $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewMetaTags(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'productseo') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Meta_Tags', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'productSeo'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#meta-tags" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#meta-tags"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Meta_Tags', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewUrlRewriting(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'producturlrewriting') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_URL_Rewriting', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'productUrlRewriting'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#url-rewriting" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#url-rewriting"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_URL_Rewriting', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>
                <?php if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE') && $userPrivilege->canViewSubscription(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Subscription', $siteLangId); ?></span></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'subscriptions' || $action == 'viewsubscriptionorder')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_My_Subscriptions', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'subscriptions'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-subscriptions" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-subscriptions"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Subscriptions", $siteLangId); ?></span></a></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'packages')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Subscription_Packages', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('seller', 'Packages'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-subscription-packages" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-subscription-packages"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Subscription_Packages', $siteLangId); ?></span></a></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'selleroffers')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Subscription_Offers', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('seller', 'SellerOffers'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-offers"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Subscription_Offers', $siteLangId); ?></span></a></div>
                    </li>
                    <li class="divider"></li>
                <?php } ?>
                <?php if (
                    $userPrivilege->canViewSalesReport(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewPerformanceReport(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewInventoryReport(UserAuthentication::getLoggedUserId(), true)
                ) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Reports", $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewSalesReport(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'reports' && $action == 'salesreport') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Reports', 'SalesReport'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-sales-report" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-sales-report"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewPerformanceReport(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'reports' && $action == 'productsperformance') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Products_Performance', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Reports', 'ProductsPerformance'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-performance" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-performance"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Products_Performance', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewInventoryReport(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'reports' && $action == 'productsinventory') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Products_Inventory', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Reports', 'productsInventory'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-inventory" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-inventory"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Products_Inventory', $siteLangId); ?></span></a></div>
                        </li>
                        <li class="menu__item <?php echo ($controller == 'reports' && $action == 'productsinventorystockstatus') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Products_Inventory_Stock_Status', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Reports', 'productsInventoryStockStatus'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-inventory-stock" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-product-inventory-stock"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Products_Inventory_Stock_Status', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Profile", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'profileinfo') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_My_Account', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'ProfileInfo'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Account", $siteLangId); ?></span></a></div>
                </li>
                <?php if ($userParentId == UserAuthentication::getLoggedUserId()) { ?>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'users' || $action == 'userpermissions')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Sub_Users', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'Users'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-account"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Sub_Users", $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>
                <?php if ($userPrivilege->canViewMessages(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'account' && ($action == 'messages' || strtolower($action) == 'viewmessages')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Messages', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'Messages'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-messages" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-messages"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?>
                                    <?php if ($todayUnreadMessageCount > 0) { ?>
                                        <span class="msg-count"><?php echo ($todayUnreadMessageCount < 9) ? $todayUnreadMessageCount : '9+'; ?></span>
                                    <?php } ?></span></a></div>
                    </li>
                <?php } ?>
                <?php if ($userParentId == UserAuthentication::getLoggedUserId()) { ?>
                    <li class="menu__item <?php echo ($controller == 'account' && $action == 'credits') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_My_Credits', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'credits'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-credits"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'changeemailpassword') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_UPDATE_CREDENTIALS', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'changeEmailPassword'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-email" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-change-password"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_UPDATE_CREDENTIALS', $siteLangId); ?></span></a></div>
                </li>
                <?php if ($userPrivilege->canViewImportExport(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="divider"></li>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Import_Export', $siteLangId); ?></span></div>
                    </li>
                    <?php if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0)) { ?>
                        <li class="menu__item <?php echo ($controller == 'importexport' && ($action == 'index')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Import_Export', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('ImportExport', 'index'); ?>"><i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-import-export" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-import-export"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Import_Export', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                <?php } ?>
                <?php $this->includeTemplate('_partial/dashboardLanguageArea.php'); ?>
            </ul>
        </nav>
    </div>
</sidebar>