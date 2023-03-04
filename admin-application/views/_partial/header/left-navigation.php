  <!--left panel start here-->
        <span class="leftoverlay"></span>
        <aside class="leftside">

            <div class="sidebar_inner">
                 <div class="profilewrap">
                        <div class="profilecover">
                            <figure class="profilepic"><img id="leftmenuimgtag"  src="<?php echo UrlHelper::generateUrl('image', 'profileImage', array(AdminAuthentication::getLoggedAdminId(), "THUMB", true )); ?>" alt=""></figure>
                            <span class="profileinfo"><?php echo Labels::getLabel('LBL_Welcome', $adminLangId);?> <?php echo $adminName; ?></span>
                        </div>

                        <div class="profilelinkswrap">
                            <ul class="leftlinks">
                                <li class=""><a href="<?php echo UrlHelper::generateUrl('profile');?>"><?php echo Labels::getLabel('LBL_View_Profile', $adminLangId);?></a></li>
                                <li class=""><a href="<?php echo UrlHelper::generateUrl('profile', 'changePassword');?>"><?php echo Labels::getLabel('LBL_Change_Password', $adminLangId);?></a></li>
                                <li class=""><a href="<?php echo UrlHelper::generateUrl('profile', 'logout');?>"><?php echo Labels::getLabel('LBL_Logout', $adminLangId);?></a></li>
                             </ul>
                        </div>
                    </div>
            <ul class="leftmenu">
            <!--Dashboard-->
            <?php if (
                $objPrivilege->canViewAdminDashboard(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li><a href="<?php echo UrlHelper::generateUrl();?>"><?php echo Labels::getLabel('LBL_Dashboard', $adminLangId);?></a></li>
            <?php } ?>
            
            <?php if ($objPrivilege->canViewShops(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li>
                    <a href="<?php echo UrlHelper::generateUrl('Shops');?>"><?php echo Labels::getLabel('LBL_Shops', $adminLangId);?></a>
                </li>
            <?php } ?>
                    
            <!--Products -->
            <?php if (
                $objPrivilege->canViewProductCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBrands(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewAttributes(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewOptions(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewTags(AdminAuthentication::getLoggedAdminId(), true)||
                $objPrivilege->canViewBrandRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerProducts(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Catalog', $adminLangId);?></a>
                <ul>
                    <?php
                    if ($objPrivilege->canViewProductCategories(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ProductCategories');?>"><?php echo Labels::getLabel('LBL_Categories', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php
                    if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('products');?>"><?php echo Labels::getLabel('LBL_Products', $adminLangId);?></a></li>
                    <li><a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'index');?>"><?php echo Labels::getLabel('LBL_Seller_Inventory', $adminLangId);?></a></li>
                    <?php if ($objPrivilege->canViewSellerProducts(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('SellerProducts', 'thresholdProducts'); ?>"><?php echo Labels::getLabel('LBL_Threshold_Products', $adminLangId);?> <?php if ($threshSelProdCount) { ?><span class='badge'>(<?php echo $threshSelProdCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
                    <?php } /* if($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('sellerProducts','catalog');?>"><?php echo Labels::getLabel('LBL_Add_New_Product',$adminLangId);?></a></li>
                    <?php } */ ?>
                    <?php
                    if ($objPrivilege->canViewBrands(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Brands');?>"><?php echo Labels::getLabel('LBL_Brands', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php
                    /* if($objPrivilege->canViewFilterGroups(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('filterGroups');?>">Filters</a></li>
                    <?php }  */?>
                    <?php
                    /* if($objPrivilege->canViewAttributes(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Attributes');?>"><?php echo Labels::getLabel('LBL_Attributes',$adminLangId);?></a></li>
                    <?php } */ ?>
                    <?php
                    /* if($objPrivilege->canViewExtraAttributes(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('extraAttributeGroups');?>">Extra Attributes</a></li>
                    <?php } */ ?>
                    <?php
                    if ($objPrivilege->canViewOptions(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Options');?>"><?php echo Labels::getLabel('LBL_Options', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php
                    if ($objPrivilege->canViewTags(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Tags');?>"><?php echo Labels::getLabel('LBL_Tags', $adminLangId);?></a></li>
                    <?php } ?>
					<?php
                    if ($objPrivilege->canViewBrandRequests(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Brands', 'BrandRequests');?>"><?php echo Labels::getLabel('LBL_Brand_Requests', $adminLangId);?><?php if ($brandReqCount) { ?><span class='badge'>(<?php echo $brandReqCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
					<?php
                    if ($objPrivilege->canViewProductCategories(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ProductCategories', 'requests');?>"><?php echo Labels::getLabel('LBL_Categories_Requests', $adminLangId);?><?php if ($categoryReqCount) { ?><span class='badge'>(<?php echo $categoryReqCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
					<?php if ($objPrivilege->canViewCustomCatalogProductRequests(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('CustomProducts');?>"><?php echo Labels::getLabel('LBL_Custom_Product_Catalog_Requests', $adminLangId);?> <?php if ($custProdReqCount) { ?><span class='badge'>(<?php echo $custProdReqCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
					<?php /* if($objPrivilege->canViewSellerCatalogRequests(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_SELLER_CAN_REQUEST_PRODUCT', FatUtility::VAR_INT, 0)){?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Users','sellerCatalogRequests');?>"><?php echo Labels::getLabel('LBL_Product_Catalog_Requests',$adminLangId);?> <?php if($catReqCount){ ?><span class='badge'>(<?php echo $catReqCount; ?>)</span><?php } ?></a></li>
                    <?php } */ ?>
                </ul>
            </li>
            <?php } ?>
            
            <?php if (
                $objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewRewardsOnPurchase(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewRecomendedWeightages(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewPromotions(AdminAuthentication::getLoggedAdminId(), true)
                ) { 
            ?>
            <li class="haschild">
                <a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Promotions', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li>
                        <a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'specialPrice');?>"><?php echo Labels::getLabel('LBL_Special_Price', $adminLangId);?></a>
                    </li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li>
                        <a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'volumeDiscount');?>"><?php echo Labels::getLabel('LBL_Volume_Discount', $adminLangId);?></a>
                    </li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li>
                        <a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'upsellProducts');?>"><?php echo Labels::getLabel('LBL_Buy_Together_Products', $adminLangId);?></a>
                    </li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li>
                        <a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'relatedProducts');?>"><?php echo Labels::getLabel('LBL_Related_Products', $adminLangId);?></a>
                    </li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('DiscountCoupons'); ?>"><?php echo Labels::getLabel('LBL_Discount_Coupons', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewPromotions(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('promotions'); ?>"><?php echo Labels::getLabel('LBL_PPC_Promotions_Management', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewRewardsOnPurchase(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('RewardsOnPurchase'); ?>"><?php echo Labels::getLabel('LBL_Rewards_on_every_purchase', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewRecomendedWeightages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('SmartRecomendedWeightages'); ?>"><?php echo Labels::getLabel('LBL_Manage_Weightages', $adminLangId);?></a></li>
                        <li><a href="<?php echo UrlHelper::generateUrl('RecomendedTagProducts'); ?>"><?php echo Labels::getLabel('LBL_Recommended_Tag_Products_Weightages', $adminLangId);?></a></li>
                        <?php /*?><li><a href="<?php echo UrlHelper::generateUrl('SmartRecomendedProducts'); ?>">Tag Product Weightages</a></li>
                        <li><a href="<?php echo UrlHelper::generateUrl('ProductBrowsingHistory'); ?>">Products Browsing History</a></li>    <?php */?>
                    <?php } ?>
                </ul>
            </li>
            <?php } ?>
            
            <?php if (
                    $objPrivilege->canViewOrders(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSellerOrders(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewAbandonedCart(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSubscriptionOrders(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewWithdrawRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewOrderCancellationRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewOrderReturnRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewProductReviews(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Orders', $adminLangId);?></a>
                <ul>

                    <?php if ($objPrivilege->canViewOrders(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('orders'); ?>"><?php echo Labels::getLabel('LBL_Orders', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewAbandonedCart(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('AbandonedCart'); ?>"><?php echo Labels::getLabel('LBL_Abandoned_Cart', $adminLangId);?> </a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewSellerOrders(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('SellerOrders'); ?>"><?php echo Labels::getLabel('LBL_Seller_Orders', $adminLangId);?> <?php if (!empty($sellerOrderCount)) { ?><span class='badge'>(<?php echo $sellerOrderCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewSubscriptionOrders(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('SubscriptionOrders'); ?>"><?php echo Labels::getLabel('LBL_Subscription_Orders', $adminLangId);?> </a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewWithdrawRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('WithdrawalRequests'); ?>"><?php echo Labels::getLabel('LBL_Withdrawl_Requests', $adminLangId);?> <?php if ($drReqCount) { ?><span class='badge'>(<?php echo $drReqCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewOrderCancellationRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('OrderCancellationRequests'); ?>"><?php echo Labels::getLabel('LBL_Cancellation_Requests', $adminLangId);?> <?php if ($orderCancelReqCount) { ?><span class='badge'>(<?php echo $orderCancelReqCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewOrderReturnRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('OrderReturnRequests'); ?>"><?php echo Labels::getLabel('LBL_Return/Refund_Requests', $adminLangId);?> <?php if ($orderRetReqCount) { ?><span class='badge'>(<?php echo $orderRetReqCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewProductReviews(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('productReviews');?>"><?php echo Labels::getLabel('LBL_Product_Reviews', $adminLangId);?></a></li>
                    <?php } ?>
                </ul>
            </li> 
            <?php } ?>
            
            <?php if (
                $objPrivilege->canViewUsers(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerApprovalForm(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerApprovalRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerCatalogRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewUserRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCustomCatalogProductRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewAdminUsers(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewMessages(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Users', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewUsers(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Users');?>"><?php echo Labels::getLabel('LBL_Users', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewAdminUsers(AdminAuthentication::getLoggedAdminId(), true) || $objPrivilege->canViewAdminUsers(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('AdminUsers')?>"><?php echo Labels::getLabel('LBL_Admin_Sub_Users', $adminLangId);?></a>
                    </li>
                    <?php } ?> 
                    <?php if ($objPrivilege->canViewMessages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Messages'); ?>"><?php echo Labels::getLabel('LBL_Messages', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewSellerApprovalForm(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Users', 'sellerForm');?>"><?php echo Labels::getLabel('LBL_Seller_Approval_Form', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewSellerApprovalRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Users', 'sellerApprovalRequests');?>"><?php echo Labels::getLabel('LBL_Seller_Approval_Requests', $adminLangId);?> <?php if ($supReqCount) { ?><span class='badge'>(<?php echo $supReqCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewUserRequests(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('userGdprRequests');?>"><?php echo Labels::getLabel('LBL_Users_GDPR_Requests', $adminLangId);?> <?php if ($gdprReqCount) { ?><span class='badge'>(<?php echo $gdprReqCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
                </ul>
            </li>
            <?php } ?>
            <!--FAQ-->
            <!--<li><a href="<?php echo UrlHelper::generateUrl('faqs');?>">Manage FAQ's</a></li>-->
            
            <!--Mobile Application-->
            <?php if (
                $objPrivilege->canViewPushNotification(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewAppThemeSettings(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_MOBILE_APPS', $adminLangId);?></a>
                    <ul>

                        <?php 
                        $active = (new Plugin())->getDefaultPluginData(Plugin::TYPE_PUSH_NOTIFICATION, 'plugin_active');
                        if ($objPrivilege->canViewPushNotification(AdminAuthentication::getLoggedAdminId(), true) && false != $active && !empty($active)) {?>
                            <li>
                                <a href="<?php echo UrlHelper::generateUrl('PushNotifications'); ?>">
                                    <?php echo Labels::getLabel('LBL_PUSH_NOTIFICATION', $adminLangId);?>
                                </a>
                            </li>
                        <?php }
                        if ($objPrivilege->canViewAppThemeSettings(AdminAuthentication::getLoggedAdminId(), true)) {?>
                            <li>
                                <a href="<?php echo UrlHelper::generateUrl('MobileAppSettings', 'appTheme'); ?>">
                                    <?php echo Labels::getLabel('LBL_APP_THEME_SETTINGS', $adminLangId);?>
                                </a>
                            </li>
                        <?php } ?>                        
                    </ul>
                </li>
            <?php } ?>
                
            <?php if ($objPrivilege->canViewSalesReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewUsersReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewProductsReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewCatalogReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewShopsReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewTaxReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewCommissionReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewAffiliatesReport(AdminAuthentication::getLoggedAdminId(), true) ||
            $objPrivilege->canViewAdvertisersReport(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Reports', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewSalesReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('SalesReport'); ?>"><?php echo Labels::getLabel('LBL_Sales', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewUsersReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('UsersReport'); ?>"><?php echo Labels::getLabel('LBL_Buyers/Sellers', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewAffiliatesReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('AffiliatesReport'); ?>"><?php echo Labels::getLabel('LBL_Affiliates', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewAdvertisersReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('AdvertisersReport'); ?>"><?php echo Labels::getLabel('LBL_Advertisers', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewProductsReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ProductsReport'); ?>"><?php echo Labels::getLabel('LBL_Products(Seller_Products)', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewCatalogReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('CatalogReport'); ?>"><?php echo Labels::getLabel('LBL_Products(Catalog_Wise)', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewShopsReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ShopsReport'); ?>"><?php echo Labels::getLabel('LBL_Shops', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewTaxReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('TaxReport'); ?>"><?php echo Labels::getLabel('LBL_Tax', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewCommissionReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('CommissionReport'); ?>"><?php echo Labels::getLabel('LBL_Commission', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('TopProductsReport'); ?>"><?php echo Labels::getLabel('LBL_Top_Products', $adminLangId);?></a></li>
                    <?php }    ?>
                    <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('BadProductsReport'); ?>"><?php echo Labels::getLabel('LBL_Most_Refunded_Products', $adminLangId);?></a></li>
                    <?php }    ?>
                    <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('TopCategoriesReport'); ?>"><?php echo Labels::getLabel('LBL_Top_Categories', $adminLangId);?></a></li>
                    <?php }    ?>
                    <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('BadCategoriesReport'); ?>"><?php echo Labels::getLabel('LBL_Bad_Categories', $adminLangId);?></a></li>
                    <?php }    ?>
                    <?php if ($objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('DiscountCouponsReport'); ?>"><?php echo Labels::getLabel('LBL_Discount_Coupons', $adminLangId);?></a></li>
                    <?php }    ?>
                </ul>
            </li>
            <?php } ?>
            
            <!--CMS-->
            <?php if (
                $objPrivilege->canViewContentPages(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewContentBlocks(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewNavigationManagement(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewZones(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCountries(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewStates(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCollections(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewPolicyPoints(AdminAuthentication::getLoggedAdminId(), true)  ||
                $objPrivilege->canViewEmptyCartItems(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSocialPlatforms(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewShopReportReasons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewOrderCancelReasons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewOrderReturnReasons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewTestimonial(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewImportInstructions(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewEmailTemplates(AdminAuthentication::getLoggedAdminId(), true) || 
                $objPrivilege->canViewSmsTemplate(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewOrderStatus(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewFaqCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewAbusiveWords(AdminAuthentication::getLoggedAdminId(), true)
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Cms', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewNavigationManagement(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('Navigations'); ?>"><?php echo Labels::getLabel('LBL_Navigation_Management', $adminLangId);?></a></li>
                    <?php } ?>
                        
                    <?php if ($objPrivilege->canViewSlides(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('slides'); ?>"><?php echo Labels::getLabel('LBL_Home_Page_Slides_Management', $adminLangId);?></a></li>
                    <?php } ?>
                        
                    <?php if ($objPrivilege->canViewCollections(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('Collections'); ?>"><?php echo Labels::getLabel('LBL_Collection_Management', $adminLangId);?> </a></li>
                    <?php } ?>
                    
                    <?php if ($objPrivilege->canViewBanners(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('Banners'); ?>"><?php echo Labels::getLabel('LBL_Banners', $adminLangId);?></a></li>
                    <?php } ?>
                        
                    <?php if ($objPrivilege->canViewLanguageLabels(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('Labels'); ?>"><?php echo Labels::getLabel('LBL_Language_Labels', $adminLangId);?></a></li>
                    <?php } ?>
                    
                    <?php if ($objPrivilege->canViewEmailTemplates(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('EmailTemplates'); ?>"><?php echo Labels::getLabel('LBL_Email_Templates_Management', $adminLangId);?></a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewSmsTemplate(AdminAuthentication::getLoggedAdminId(), true) && SmsArchive::canSendSms()) {?>
                        <li>
                            <a href="<?php echo UrlHelper::generateUrl('SmsTemplates'); ?>">
                                <?php echo Labels::getLabel('LBL_SMS_TEMPLATE_MANAGEMENT', $adminLangId);?>
                            </a>
                        </li>
                    <?php } ?>       
                    <?php if ($objPrivilege->canViewContentPages(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ContentPages'); ?>"><?php echo Labels::getLabel('LBL_Content_Pages', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewContentBlocks(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ContentBlock'); ?>"><?php echo Labels::getLabel('LBL_Content_Blocks', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewImportInstructions(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ContentBlock', 'importInstructions'); ?>"><?php echo Labels::getLabel('LBL_Import_Instructions', $adminLangId);?></a></li>
                    <?php } ?>
                    
                    <?php if ($objPrivilege->canViewFaqCategories(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('FaqCategories'); ?>"><?php echo Labels::getLabel('LBL_FAQs', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewZones(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('Zones'); ?>"><?php echo Labels::getLabel('LBL_Zone(Regions)_Management', $adminLangId);?></a></li>
                    <?php }?>
                        
                    <?php if ($objPrivilege->canViewCountries(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('Countries'); ?>"><?php echo Labels::getLabel('LBL_Countries_Management', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewStates(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('States'); ?>"><?php echo Labels::getLabel('LBL_States_Management', $adminLangId);?></a></li>
                    <?php } ?>
                        
                    <?php /* if ($objPrivilege->canViewPolicyPoints(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('PolicyPoints'); ?>"><?php echo Labels::getLabel('LBL_Policy_Points_Management', $adminLangId);?></a></li>
                    <?php } */ ?>

                    <?php if ($objPrivilege->canViewEmptyCartItems(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('emptyCartItems'); ?>"><?php echo Labels::getLabel('LBL_Empty_Cart_Items_Management', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewSocialPlatforms(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('SocialPlatform'); ?>"><?php echo Labels::getLabel('LBL_Social_Platforms_Management', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewShopReportReasons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('ShopReportReasons'); ?>"><?php echo Labels::getLabel('LBL_Shop_Report_Reasons_Management', $adminLangId);?></a></li>
                    <?php } ?>
                     
                    <?php if ($objPrivilege->canViewOrderStatus(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('OrderStatus'); ?>"><?php echo Labels::getLabel('LBL_Order_Status_Management', $adminLangId);?></a></li>
                    <?php } ?>
                            
                    <?php if ($objPrivilege->canViewOrderCancelReasons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('OrderCancelReasons'); ?>"><?php echo Labels::getLabel('LBL_Order_Cancel_Reasons_Management', $adminLangId);?> </a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewOrderReturnReasons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('OrderReturnReasons'); ?>"><?php echo Labels::getLabel('LBL_Order_Return_Reasons_Management', $adminLangId);?> </a></li>
                    <?php } ?>
                    <?php if ($objPrivilege->canViewAbusiveWords(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('AbusiveWords'); ?>"><?php echo Labels::getLabel('LBL_Abusive_Keyword', $adminLangId);?></a></li>
                    <?php } ?>    
                    <?php if ($objPrivilege->canViewTestimonial(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('Testimonials'); ?>"><?php echo Labels::getLabel('LBL_Testimonials_Management', $adminLangId);?> </a></li>
                    <?php } ?>

                    <!-- <?php if ($objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true)) {?>
                        <li><a href="<?php echo UrlHelper::generateUrl('SellerDiscountCoupons'); ?>"><?php echo Labels::getLabel('LBL_Seller_Discount_Coupons', $adminLangId);?></a></li>
                    <?php } ?> -->
                </ul>
            </li>
            <?php } ?>
            
            <?php if ($objPrivilege->canViewBlogPostCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBlogPosts(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBlogContributions(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBlogComments(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Blog', $adminLangId);?></a>
                <ul>
                    <?php if ($objPrivilege->canViewBlogPostCategories(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('BlogPostCategories'); ?>"><?php echo Labels::getLabel('LBL_Blog_Post_Categories', $adminLangId);?></a></li>
                    <?php }
                    if ($objPrivilege->canViewBlogPosts(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('BlogPosts'); ?>"><?php echo Labels::getLabel('LBL_Blog_Posts', $adminLangId);?></a></li>
                    <?php }
                    if ($objPrivilege->canViewBlogContributions(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('BlogContributions'); ?>"><?php echo Labels::getLabel('LBL_Blog_Contributions', $adminLangId);?> <?php if ($blogContrCount) { ?><span class='badge'>(<?php echo $blogContrCount; ?>)</span><?php } ?></a></li>
                    <?php }
                    if ($objPrivilege->canViewBlogComments(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('BlogComments'); ?>"><?php echo Labels::getLabel('LBL_Blog_Comments', $adminLangId);?> <?php if ($blogCommentsCount) { ?><span class='badge'>(<?php echo $blogCommentsCount; ?>)</span><?php } ?></a></li>
                    <?php } ?>
                </ul>
            </li>
            <?php } ?>
            
            <?php if (
                $objPrivilege->canViewMetaTags(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewUrlRewrite(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_SEO', $adminLangId);?></a>
                    <ul>
                        <?php if ($objPrivilege->canViewMetaTags(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('MetaTags'); ?>"><?php echo Labels::getLabel('LBL_Meta_Tags_Management', $adminLangId);?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewUrlRewrite(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('UrlRewriting'); ?>"><?php echo Labels::getLabel('LBL_Url_Rewriting', $adminLangId);?></a></li>
                        <?php } ?>
						<?php if ($objPrivilege->canViewImageAttributes(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ImageAttributes'); ?>"><?php echo Labels::getLabel('LBL_Image_Attributes', $adminLangId);?></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <?php if (
                /* $objPrivilege->canViewShippingMethods(AdminAuthentication::getLoggedAdminId(), true) || */
                $objPrivilege->canViewShippingCompanyUsers(AdminAuthentication::getLoggedAdminId(), true) ||
                /* $objPrivilege->canViewShippingDurationLabels(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewManualShippingApi(AdminAuthentication::getLoggedAdminId(), true) || */
                $objPrivilege->canViewShippingPackages(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewShippingManagement(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewPickupAddresses(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewTrackingRelationCode()
            ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Shipping/Pickup', $adminLangId);?></a>
                <ul>
                    <?php /* if ($objPrivilege->canViewShippingMethods(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ShippingMethods'); ?>"><?php echo Labels::getLabel('LBL_Shipping_Methods', $adminLangId);?></a></li>
                    <?php } */ ?>

                    <?php /* if ($objPrivilege->canViewShippingDurationLabels(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ShippingDurations'); ?>"><?php echo Labels::getLabel('LBL_Duration_Labels', $adminLangId);?></a></li>
                    <?php } */ ?>

                    <?php /* if ($objPrivilege->canViewShippingCompanies(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ShippingCompanies'); ?>"><?php echo Labels::getLabel('LBL_Shipping_Companies', $adminLangId);?></a></li>
                    <?php } */ ?>

                    <?php if ($objPrivilege->canViewShippingCompanyUsers(AdminAuthentication::getLoggedAdminId(), true)) {?>
                    <li><a href="<?php echo UrlHelper::generateUrl('ShippingCompanyUsers');?>"><?php echo Labels::getLabel('LBL_Shipping_Company_Users', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewShippingPackages(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('shippingPackages'); ?>"><?php echo Labels::getLabel('LBL_Shipping_Packages', $adminLangId);?></a></li>
                    <?php } ?>

                    <?php if ($objPrivilege->canViewShippingManagement(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('shippingProfile'); ?>"><?php echo Labels::getLabel('LBL_Shipping_Profile', $adminLangId);?></a></li>
                    <?php }?>

                    <?php /* if($objPrivilege->canViewManualShippingApi(AdminAuthentication::getLoggedAdminId(), true)){?>
                        <li><a href="<?php echo UrlHelper::generateUrl('ManualShippingApi'); ?>"><?php echo Labels::getLabel('LBL_Manual_Shipping_Api',$adminLangId);?></a></li>
                    <?php } */ ?>
                    
                    <?php 
                    if ($objPrivilege->canViewTrackingRelationCode()) { ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('TrackingCodeRelation'); ?>"><?php echo Labels::getLabel('LBL_Tracking_Code_Relation', $adminLangId);?></a></li>
                    <?php } ?>
                    
                    <?php if ($objPrivilege->canViewPickupAddresses(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('PickupAddresses'); ?>"><?php echo Labels::getLabel('LBL_Pickup_Addresses', $adminLangId);?></a></li>
                    <?php }?>
                </ul>
            </li>
            <?php } ?>
			
			<?php if ($objPrivilege->canViewTax(AdminAuthentication::getLoggedAdminId(), true)) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Sales_tax', $adminLangId);?></a>
                <ul>
                    <li><a href="<?php echo UrlHelper::generateUrl('TaxStructure'); ?>"><?php echo Labels::getLabel('LBL_Tax_Structure', $adminLangId);?></a></li>
					<li><a href="<?php echo UrlHelper::generateUrl('Tax'); ?>"><?php echo Labels::getLabel('LBL_Tax_Management', $adminLangId);?></a></li>
                </ul>
            </li>
            <?php } ?>         
            <!--System Settings-->
            <?php if (
                $objPrivilege->canViewGeneralSettings(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewPlugins(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewPaymentMethods(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCurrencyManagement(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewCommissionSettings(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewAffiliateCommissionSettings(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewSellerPackages(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewThemeColor(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_System_Settings', $adminLangId);?></a>
                    <ul>

                        <?php if ($objPrivilege->canViewGeneralSettings(AdminAuthentication::getLoggedAdminId(), true)) {?>
                            <li><a href="<?php echo UrlHelper::generateUrl('configurations'); ?>"><?php echo Labels::getLabel('LBL_General_Settings', $adminLangId);?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewPlugins(AdminAuthentication::getLoggedAdminId(), true)) {?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Plugins'); ?>"><?php echo Labels::getLabel('LBL_PLUGINS', $adminLangId);?></a></li>
                        <?php }?>
                        <?php if ($objPrivilege->canViewThemeColor(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ThemeColor'); ?>"><?php echo Labels::getLabel('LBL_Theme_Settings', $adminLangId);?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewCurrencyManagement(AdminAuthentication::getLoggedAdminId(), true)) {?>
                            <li><a href="<?php echo UrlHelper::generateUrl('CurrencyManagement'); ?>"><?php echo Labels::getLabel('LBL_Currency_Management', $adminLangId);?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewCommissionSettings(AdminAuthentication::getLoggedAdminId(), true)) {?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Commission'); ?>"><?php echo Labels::getLabel('LBL_Commission_Settings', $adminLangId);?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewAffiliateCommissionSettings(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('AffiliateCommission'); ?>"><?php echo Labels::getLabel('LBL_Affiliate_Commission_Settings', $adminLangId);?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewSellerPackages(AdminAuthentication::getLoggedAdminId(), true)) {?>
                            <li><a href="<?php echo UrlHelper::generateUrl('SellerPackages'); ?>"><?php echo Labels::getLabel('LBL_Seller_Packages_Management', $adminLangId);?></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <?php if ($objPrivilege->canViewImportExport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li><a href="<?php echo UrlHelper::generateUrl('ImportExport'); ?>"><?php echo Labels::getLabel('LBL_Import_Export', $adminLangId);?></a>
                </li>
            <?php } ?>
           
                <?php /* if (
                $objPrivilege->canViewSuccessStories(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewLanguageLabels(AdminAuthentication::getLoggedAdminId(), true) ||
                //$objPrivilege->canViewHomePageElements(AdminAuthentication::getLoggedAdminId(), true) ||  
                $objPrivilege->canViewSlides(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewBanners(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewFaqCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewThemeColor(AdminAuthentication::getLoggedAdminId(), true) ||
                $objPrivilege->canViewProductTempImages(AdminAuthentication::getLoggedAdminId(), true)
                ) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Misc', $adminLangId);?></a>
                <ul>
                    <?php if($objPrivilege->canViewSuccessStories(AdminAuthentication::getLoggedAdminId(), true)){ ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('SuccessStories'); ?>"><?php echo Labels::getLabel('LBL_Success_Stories',$adminLangId);?></a></li>
                    <?php }  ?>

                    <?php  if($objPrivilege->canViewHomePageElements(AdminAuthentication::getLoggedAdminId(), true)){?>
                    <li><a href="<?php echo UrlHelper::generateUrl('HomePageElements'); ?>">Home Page Elements</a></li>
                    <?php } ?>
                    <?php  if ($objPrivilege->canViewProductTempImages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('ProductTempImages'); ?>"><?php echo Labels::getLabel('LBL_Product_Temp_Images', $adminLangId);?></a></li>
                    <?php }  ?>
                    <?php  if ($objPrivilege->canUploadBulkImages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                        <li><a href="<?php echo UrlHelper::generateUrl('UploadBulkImages'); ?>"><?php echo Labels::getLabel('LBL_Upload_Bulk_Images', $adminLangId);?></a></li>
                    <?php }  ?>
                </ul>
            </li>
            <?php } */ ?>

            <!-- <?php if ($objPrivilege->canViewQuestionBanks(AdminAuthentication::getLoggedAdminId(), true)) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Questionnaires', $adminLangId);?></a>
                <ul>
                    <li><a href="<?php echo UrlHelper::generateUrl('QuestionBanks'); ?>"><?php echo Labels::getLabel('LBL_Question_Banks', $adminLangId);?></a></li>
                    <li><a href="<?php echo UrlHelper::generateUrl('Questionnaires'); ?>"><?php echo Labels::getLabel('LBL_Questionnaires', $adminLangId);?></a></li>
                </ul>
            </li>
            <?php } ?>
            <?php if ($objPrivilege->canViewPolling(AdminAuthentication::getLoggedAdminId(), true)) { ?>
            <li><a href="<?php echo UrlHelper::generateUrl('Polling'); ?>"><?php echo Labels::getLabel('LBL_Polling', $adminLangId);?></a></li>
            <?php } ?> -->
            
            <?php /* if ($objPrivilege->canViewTools(AdminAuthentication::getLoggedAdminId(), true)) { ?>
            <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Tools', $adminLangId);?></a>
                <ul>
                    <li><a href="<?php echo UrlHelper::generateUrl('sentEmails'); ?>"><?php echo Labels::getLabel('LBL_Sent_Emails',$adminLangId);?></a></li>
                    <li><a href="<?php echo UrlHelper::generateUrl('','',array(),CONF_WEBROOT_FRONT_URL).'restore.php?passkey=yokart-restore'; ?>"><?php echo Labels::getLabel('LBL_Restore_Default',$adminLangId);?> </a></li> 
                </ul>
            </li>
            <?php } */ ?>

            <?php if ($objPrivilege->canViewSitemap(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Sitemap', $adminLangId);?></a>
                    <ul>
                        <li><a href="<?php echo UrlHelper::generateUrl('sitemap', 'generate'); ?>"><?php echo Labels::getLabel('LBL_Update_Sitemap', $adminLangId);?></a></li>
                        <li><a href="<?php echo UrlHelper::generateFullUrl('custom', 'sitemap', array(), CONF_WEBROOT_FRONT_URL); ?>" target="_blank"><?php echo Labels::getLabel('LBL_View_HTML', $adminLangId);?></a></li>
                        <li><a href="<?php echo UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL).'sitemap.xml'; ?>" target="_blank"><?php echo Labels::getLabel('LBL_View_XML', $adminLangId);?></a></li>
                    </ul>
                </li>
            <?php } ?>

            <?php if (CommonHelper::demoUrl()) { ?>
                <li>
                    <div class="m-4 text-center">
                        <a class="themebtn btn-brand outline block" href="https://www.yo-kart.com/suggest-feature.html" target="_blank">
                            <?php echo Labels::getLabel('LBL_SUGGEST_A_FEATURE', $adminLangId);?>
                        </a>
                    </div>
                </li>
            <?php } ?>
            <?php /*<li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Support_Link', $adminLangId);?></a>
                <ul>
                    <li><a target="_blank" href="http://www.yo-kart.com/recent-updates.html"><?php echo Labels::getLabel('LBL_Version_Update', $adminLangId);?></a></li>
                    <li><a target="_blank" href="http://faq.yo-kart.com/"><?php echo Labels::getLabel('LBL_FAQs', $adminLangId);?></a></li>
                    <li><a target="_blank" href="http://www.yo-kart.com/addons-integrations.html"><?php echo Labels::getLabel('LBL_Add_Ons', $adminLangId);?></a></li>
                    <li><a href="<?php echo UrlHelper::generateUrl('Support'); ?>"><?php echo Labels::getLabel('LBL_Report_An_Issue', $adminLangId);?></a></li>
                </ul>
            </li>*/ ?>
        </ul>
    </div>
</aside>
        <!--left panel end here-->
