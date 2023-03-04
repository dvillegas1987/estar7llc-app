<?php

class Navigation
{
    public static function headerTopNavigation($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }

        $siteLangId = CommonHelper::getLangId();

        $headerTopNavigation = FatCache::get('headerTopNavigation_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');

        if ($headerTopNavigation) {
            $headerTopNavigation = unserialize($headerTopNavigation);
        } else {
            $headerTopNavigation = self::getNavigation(Navigations::NAVTYPE_TOP_HEADER);
            FatCache::set('headerTopNavigation_' . $siteLangId, serialize($headerTopNavigation), '.txt');
        }
        $template->set('top_header_navigation', $headerTopNavigation);
    }

    public static function headerNavigation($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }
        
        $siteLangId = CommonHelper::getLangId();
        $template->set('siteLangId', $siteLangId);

        $layout = FatApp::getConfig('CONF_LAYOUT_MEGA_MENU', FatUtility::VAR_INT, 1);
        $includeCategories = ($layout == Navigations::LAYOUT_MEGA_MENU) ? false : true;

        $headerNavigation = FatCache::get('headerNavigation_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($headerNavigation) {
            $headerNavigation = unserialize($headerNavigation);
        } else {
            $headerNavigation = self::getNavigation(Navigations::NAVTYPE_HEADER, $includeCategories);
            FatCache::set('headerNavigation_' . $siteLangId, serialize($headerNavigation), '.txt');
        }

        $isUserLogged = UserAuthentication::isUserLogged();
        if ($isUserLogged) {
            $template->set('userName', ucfirst(CommonHelper::getUserFirstName(UserAuthentication::getLoggedUserAttribute('user_name'))));
        }

        $headerTopNavigation = FatCache::get('headerTopNavigations_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');

        if ($headerTopNavigation) {
            $headerTopNavigation = unserialize($headerTopNavigation);
        } else {
            $headerTopNavigation = self::getNavigation(Navigations::NAVTYPE_TOP_HEADER);
            FatCache::set('headerTopNavigations_' . $siteLangId, serialize($headerTopNavigation), '.txt');
        }
        $headerCategories = [];
        if ($layout == Navigations::LAYOUT_MEGA_MENU) {
            $headerCategories = FatCache::get('headerCategories_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if ($headerCategories) {
                $headerCategories = unserialize($headerCategories);
            } else {
                $headerCategories = ProductCategory::getArray($siteLangId, 0, false, true, false, CONF_USE_FAT_CACHE);
                FatCache::set('headerCategories_' . $siteLangId, serialize($headerCategories), '.txt');
            }
        }
        $template->set('headerCategories', $headerCategories);
        $template->set('top_header_navigation', $headerTopNavigation);
        $template->set('isUserLogged', $isUserLogged);
        $template->set('headerNavigation', $headerNavigation);
    }

    public static function buyerDashboardNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();
        $userId = UserAuthentication::getLoggedUserId();
        /* Unread Message Count [*/
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        /*]*/
        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
        $template->set('todayUnreadMessageCount', $todayUnreadMessageCount);
    }

    public static function topHeaderDashboard($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }

        /* $userData = User::getAttributesById(UserAuthentication::getLoggedUserId());
        $userId = (0 < $userData['user_parent']) ? $userData['user_parent'] : UserAuthentication::getLoggedUserId(); */
        $userId = UserAuthentication::getLoggedUserId();
        /* Unread Message Count [*/
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        /*]*/
        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);
        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $controller = str_replace('Controller', '', FatApp::getController());
        $activeTab = 'B';
        $sellerActiveTabControllers = array('Seller');
        $buyerActiveTabControllers = array('Buyer');

        if (in_array($controller, $sellerActiveTabControllers)) {
            $activeTab = 'S';
        } elseif (in_array($controller, $buyerActiveTabControllers)) {
            $activeTab = 'B';
        } elseif (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])) {
            $activeTab = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'];
        }

        $shop = new Shop(0, $userId);
        $isShopActive = $shop->isActive();

        $template->set('userPrivilege', UserPrivilege::getInstance());
        $template->set('activeTab', $activeTab);
        $template->set('shop_id', $shop_id);
        $template->set('isShopActive', $isShopActive);
        $template->set('todayUnreadMessageCount', $todayUnreadMessageCount);
    }

    public static function advertiserDashboardNavigation($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();
        $userData = User::getAttributesById(UserAuthentication::getLoggedUserId());
        $userParentId = (0 < $userData['user_parent']) ? $userData['user_parent'] : UserAuthentication::getLoggedUserId();
        $template->set('userParentId', $userParentId);
        $template->set('userPrivilege', UserPrivilege::getInstance());
        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
    }

    public static function sellerDashboardNavigation($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }

        $siteLangId = CommonHelper::getLangId();
        $userData = User::getAttributesById(UserAuthentication::getLoggedUserId());
        $userId = (0 < $userData['user_parent']) ? $userData['user_parent'] : UserAuthentication::getLoggedUserId();
        /* Unread Message Count [*/
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount(UserAuthentication::getLoggedUserId(), Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        /*]*/
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();

        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);

        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $shop = new Shop(0, $userId);
        $isShopActive = $shop->isActive();

        $template->set('userParentId', $userId);
        $template->set('userPrivilege', UserPrivilege::getInstance());
        $template->set('shop_id', $shop_id);
        $template->set('isShopActive', $isShopActive);
        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
        $template->set('todayUnreadMessageCount', $todayUnreadMessageCount);
    }

    public static function affiliateDashboardNavigation($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }

        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();

        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
    }

    public static function dashboardTop($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }

        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());

        $activeTab = 'B';
        $sellerActiveTabControllers = array('Seller');
        $buyerActiveTabControllers = array('Buyer');

        if (in_array($controller, $sellerActiveTabControllers)) {
            $activeTab = 'S';
        } elseif (in_array($controller, $buyerActiveTabControllers)) {
            $activeTab = 'B';
        } elseif (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])) {
            $activeTab = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'];
        }

        $jsVariables = array(
            'confirmDelete' => Labels::getLabel('LBL_Do_you_want_to_delete', $siteLangId),
            'confirmDefault' => Labels::getLabel('LBL_Do_you_want_to_set_default', $siteLangId),
        );

        $template->set('jsVariables', $jsVariables);
        $template->set('siteLangId', $siteLangId);
        $template->set('activeTab', $activeTab);
    }

    public static function customPageLeft($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $contentBlockUrlArr = array(Extrapage::CONTACT_US_CONTENT_BLOCK => UrlHelper::generateUrl('Custom', 'ContactUs'));

        $srch = Extrapage::getSearchObject($siteLangId);
        $srch->addCondition('epage_default', '=', 1);
        $srch->addMultipleFields(
            array('epage_id as id', 'epage_type as pageType', 'IFNULL(epage_label,epage_identifier) as pageTitle ')
        );

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $pagesArr = FatApp::getDb()->fetchAll($rs);

        $srch = ContentPage::getSearchObject($siteLangId);
        $srch->addCondition('cpagelang_cpage_id', 'is not', 'mysql_func_null', 'and', true);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $cpagesArr = FatApp::getDb()->fetchAll($rs);

        $template->set('pagesArr', $pagesArr);
        $template->set('cpagesArr', $cpagesArr);
        $template->set('contentBlockUrlArr', $contentBlockUrlArr);
        $template->set('siteLangId', $siteLangId);
    }

    public static function getNavigation($type = 0, $includeCategories = true)
    {
        $siteLangId = CommonHelper::getLangId();
        $headerNavCache = FatCache::get('headerNavCache' . $siteLangId . '-' . $type, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($headerNavCache) {
            return  unserialize($headerNavCache);
        }

        if ($includeCategories) {
            /* Category have products[ */
            $rootCatArr = ProductCategory::getArray($siteLangId, 0, false, true, false, CONF_USE_FAT_CACHE);

            $categoriesMainRootArr = FatCache::get('navigationCatCache' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if ($categoriesMainRootArr) {
                $categoriesMainRootArr = unserialize($categoriesMainRootArr);
            } else {
                $categoriesMainRootArr = array_keys($rootCatArr);
                FatCache::set('navigationCatCache' . $siteLangId, serialize($categoriesMainRootArr), '.txt');
            }

            $catWithProductConditoon = '';
            if ($categoriesMainRootArr) {
                $catWithProductConditoon = " and nlink_category_id in(" . implode(",", $categoriesMainRootArr) . ")";
            }
            /* ] */
        }

        $srch = new NavigationLinkSearch($siteLangId);
        if ($includeCategories) {
            $srch->joinProductCategory($siteLangId);
            $srch->addMultipleFields(array(
                'nav_id', 'IFNULL( nav_name, nav_identifier ) as nav_name',
                'IFNULL( nlink_caption, nlink_identifier ) as nlink_caption', 'nlink_type', 'nlink_cpage_id', 'nlink_category_id', 'IFNULL( prodcat_active, ' . applicationConstants::ACTIVE . ' ) as filtered_prodcat_active', 'IFNULL(prodcat_deleted, ' . applicationConstants::NO . ') as filtered_prodcat_deleted', 'IFNULL( cpage_deleted, ' . applicationConstants::NO . ' ) as filtered_cpage_deleted', 'nlink_target', 'nlink_url', 'nlink_login_protected'
            ));
            $srch->addDirectCondition("((nlink_type = " . NavigationLinks::NAVLINK_TYPE_CATEGORY_PAGE . " AND nlink_category_id > 0 $catWithProductConditoon ) OR (nlink_type = " . NavigationLinks::NAVLINK_TYPE_CMS . " AND nlink_cpage_id > 0 ) OR  ( nlink_type = " . NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE . " ))");
            $srch->addHaving('filtered_prodcat_active', '=', applicationConstants::ACTIVE);
            $srch->addHaving('filtered_prodcat_deleted', '=', applicationConstants::NO);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
        } else {
            $srch->addDirectCondition("((nlink_type != " . NavigationLinks::NAVLINK_TYPE_CATEGORY_PAGE . "  ) OR (nlink_type = " . NavigationLinks::NAVLINK_TYPE_CMS . " AND nlink_cpage_id > 0 ) OR  ( nlink_type = " . NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE . " ))");
            $srch->addMultipleFields(array(
                'nav_id', 'IFNULL( nav_name, nav_identifier ) as nav_name',
                'IFNULL( nlink_caption, nlink_identifier ) as nlink_caption', 'nlink_type', 'nlink_cpage_id', 'nlink_category_id', 'IFNULL( cpage_deleted, ' . applicationConstants::NO . ' ) as filtered_cpage_deleted', 'nlink_target', 'nlink_url', 'nlink_login_protected'
            ));
            $srch->setPageSize(10);
        }

        $srch->joinNavigation();
        $srch->joinContentPages();

        $srch->addOrder('nav_id');
        $srch->addOrder('nlink_display_order');

        $srch->addCondition('nav_type', '=', $type);
        $srch->addCondition('nlink_deleted', '=', applicationConstants::NO);
        $srch->addCondition('nav_active', '=', applicationConstants::ACTIVE);

        $srch->addHaving('filtered_cpage_deleted', '=', applicationConstants::NO);

        $isUserLogged = UserAuthentication::isUserLogged();
        if ($isUserLogged) {
            $cnd = $srch->addCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_BOTH);
            $cnd->attachCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_YES, 'OR');
        }
        if (!$isUserLogged) {
            $cnd = $srch->addCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_BOTH);
            $cnd->attachCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_NO, 'OR');
        }
        $srch->addGroupBy('nav_id');
        $srch->addGroupBy('nlink_id');

        $rs = $srch->getResultSet();
        $rows = FatApp::getDb()->fetchAll($rs);


        $navigation = array();
        $previous_nav_id = 0;

        if ($rows) {
            $rootCatArr = ProductCategory::getArray($siteLangId, 0, false, true, false, CONF_USE_FAT_CACHE);

            foreach ($rows as $key => $row) {
                if ($key == 0 || $previous_nav_id != $row['nav_id']) {
                    $previous_nav_id = $row['nav_id'];
                }
                $navigation[$previous_nav_id]['parent'] = $row['nav_name'];
                $navigation[$previous_nav_id]['pages'][$key] = $row;

                $childrenCats = array();
                if ($includeCategories && $row['nlink_category_id'] > 0) {
                    if (array_key_exists($row['nlink_category_id'], $rootCatArr)) {
                        $childrenCats = $rootCatArr[$row['nlink_category_id']]['children'];
                    } else {
                        $childrenCats = ProductCategory::getArray($siteLangId, $row['nlink_category_id'], false, true, false, CONF_USE_FAT_CACHE);
                    }
                }
                $navigation[$previous_nav_id]['pages'][$key]['children'] = isset($childrenCats) ? $childrenCats : [];
            }
        }

        FatCache::set('headerNavCache' . $siteLangId . '-' . $type, serialize($navigation), '.txt');
        return $navigation;
    }

    public static function footerNavigation($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }

        $siteLangId = CommonHelper::getLangId();
        $footerNavigation = FatCache::get('footerNavigationCache' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($footerNavigation) {
            $footerNavigation = unserialize($footerNavigation);
        } else {
            $footerNavigation = self::getNavigation(Navigations::NAVTYPE_FOOTER);
            FatCache::set('footerNavigationCache' . $siteLangId, serialize($footerNavigation), '.txt');
        }
        $template->set('footer_navigation', $footerNavigation);
    }

    public static function sellerNavigationLeft($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }

        $seller_navigation_left = self::getNavigation(Navigations::NAVTYPE_SELLER_LEFT);
        $template->set('seller_navigation_left', $seller_navigation_left);
    }

    public static function sellerNavigationRight($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }

        $seller_navigation_right = self::getNavigation(Navigations::NAVTYPE_SELLER_RIGHT);
        $template->set('seller_navigation_right', $seller_navigation_right);
    }

    public static function blogNavigation($template)
    {
        if (true === MOBILE_APP_API_CALL) {
            return;
        }

        $siteLangId = CommonHelper::getLangId();
        $blog = new BlogController();
        $srchFrm = $blog->getBlogSearchForm();
        $categoriesArr = BlogPostCategory::getRootBlogPostCatArr($siteLangId);
        $template->set('srchFrm', $srchFrm);
        $template->set('categoriesArr', $categoriesArr);
        $template->set('siteLangId', $siteLangId);
    }
}
