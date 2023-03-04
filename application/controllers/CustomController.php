<?php

class CustomController extends MyAppController
{
    public function contactUs()
    {
        $contactFrm = $this->contactUsForm();
        $this->set('contactFrm', $contactFrm);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(true, true, 'custom/contact-us.php');
    }

    public function contactSubmit()
    {
        $frm = $this->contactUsForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        
        if (false === $post) {
            $message = $frm->getValidationErrors();
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($message));
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl('Custom', 'ContactUs'));
        }

        if (false === MOBILE_APP_API_CALL && !CommonHelper::verifyCaptcha()) {
            $message = Labels::getLabel('MSG_That_captcha_was_incorrect', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl('Custom', 'ContactUs'));
        }

        $email = explode(',', FatApp::getConfig("CONF_CONTACT_EMAIL"));
        foreach ($email as $emailId) {
            $emailId = trim($emailId);
            if (filter_var($emailId, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            $email = new EmailHandler();
            if (!$email->sendContactFormEmail($emailId, $this->siteLangId, $post)) {
                $message = Labels::getLabel('MSG_email_not_sent_server_issue', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
            } else {
                Message::addMessage(Labels::getLabel('MSG_your_message_sent_successfully', $this->siteLangId));
            }

            if (true === MOBILE_APP_API_CALL) {
                $this->set('msg', Labels::getLabel('MSG_your_message_sent_successfully', $this->siteLangId));
                $this->_template->render();
            }

            FatApp::redirectUser(UrlHelper::generateUrl('Custom', 'ContactUs'));
        }
    }

    public function faq()
    {
        $cmsPagesToFaq = FatApp::getConfig('conf_cms_pages_to_faq_page', null, '');
        $cmsPagesToFaq = unserialize($cmsPagesToFaq);
        if (sizeof($cmsPagesToFaq) > 0 && is_array($cmsPagesToFaq)) {
            $contentPageSrch = ContentPage::getSearchObject($this->siteLangId);
            $contentPageSrch->addCondition('cpage_id', 'in', $cmsPagesToFaq);
            $contentPageSrch->addMultipleFields(array('cpage_id', 'cpage_identifier', 'cpage_title'));
            $rs = $contentPageSrch->getResultSet();
            $cpages = FatApp::getDb()->fetchAll($rs);
            $this->set('cpages', $cpages);
        }

        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id and faq_active = ' . applicationConstants::ACTIVE . '  and faq_deleted = ' . applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_type', '=', FaqCategory::FAQ_PAGE);
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        $this->set('recordCount', $srch->recordCount());
        $this->set('siteLangId', $this->siteLangId);
        $this->set('frm', $this->getSearchFaqForm());
        $this->_template->render();
    }

    public function faqDetail($catId = 0, $faqId = 0)
    {
        $cmsPagesToFaq = FatApp::getConfig('conf_cms_pages_to_faq_page');
        $cmsPagesToFaq = unserialize($cmsPagesToFaq);
        if (sizeof($cmsPagesToFaq) > 0 && is_array($cmsPagesToFaq)) {
            $contentPageSrch = ContentPage::getSearchObject($this->siteLangId);
            $contentPageSrch->addCondition('cpage_id', 'in', $cmsPagesToFaq);
            $contentPageSrch->addMultipleFields(array('cpage_id', 'cpage_identifier', 'cpage_title'));
            $rs = $contentPageSrch->getResultSet();
            $cpages = FatApp::getDb()->fetchAll($rs);
            $this->set('cpages', $cpages);
        }
        $this->set('siteLangId', $this->siteLangId);
        $this->set('faqCatId', $catId);
        $this->set('faqId', $faqId);
        $this->set('frm', $this->getSearchFaqForm());
        $this->_template->render();
    }

    public function SearchFaqsDetail($catId = 0, $faqId = 0)
    {
        $searchFrm = $this->getSearchFaqForm();
        $faqMainCat = FatApp::getConfig("CONF_FAQ_PAGE_MAIN_CATEGORY");

        $post = $searchFrm->getFormDataFromArray(FatApp::getPostedData());
        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id AND faq_active = ' . applicationConstants::ACTIVE . '  AND faq_deleted = ' . applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_type', '=', FaqCategory::FAQ_PAGE);
        if ($catId > 0) {
            $srch->addCondition('faqcat_id', '=', $catId);
        }

        if ($faqId > 0) {
            $srch->addCondition('faq_id', '=', $faqId);
        }

        $srch->setPageSize(1);
        $qry = $srch->getQuery();
        // echo $qry; die;
        $question = FatApp::getPostedData('question', FatUtility::VAR_STRING, '');
        if (!empty($question)) {
            $srchCondition = $srch->addCondition('faq_title', 'like', "%$question%");
            $srch->doNotLimitRecords();
        }
        $srch->addOrder('faqcat_display_order', 'asc');
        $srch->addOrder('faq_faqcat_id', 'asc');
        $srch->addOrder('faq_display_order', 'asc');

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $json['recordCount'] = $srch->recordCount();


        if (isset($srchCondition)) {
            $srchCondition->remove();
        }
        $this->set('siteLangId', $this->siteLangId);
        $this->set('list', $records);

        $json['html'] = $this->_template->render(false, false, '_partial/no-record-found.php', true, false);
        if (!empty($records)) {
            $json['html'] = $this->_template->render(false, false, 'custom/search-faqs-detail.php', true, false);
        }

        FatUtility::dieJsonSuccess($json);
    }

    public function searchFaqs($page = 'faq', $catId = 0)
    {
        if ($page == 'faq') {
            $faqPage = FaqCategory::FAQ_PAGE;
            $faqMainCat = FatApp::getConfig("CONF_FAQ_PAGE_MAIN_CATEGORY", null, '');
        } else {
            $faqPage = FaqCategory::SELLER_PAGE;
            $faqMainCat = FatApp::getConfig("CONF_SELLER_PAGE_MAIN_CATEGORY", null, '');
        }

        if (!empty($catId) && $catId > 0) {
            $faqCatId = array($catId);
        } elseif ($faqMainCat) {
            $faqCatId = array($faqMainCat);
        } else {
            $srchFAQCat = FaqCategory::getSearchObject($this->siteLangId);
            $srchFAQCat->setPageSize(1);
            $srchFAQCat->addFld('faqcat_id');
            $srchFAQCat->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
            $srchFAQCat->addCondition('faqcat_type', '=', $faqPage);
            $rs = $srchFAQCat->getResultSet();
            $faqCatId = FatApp::getDb()->fetch($rs, 'faqcat_id');
        }

        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id and faq_active = ' . applicationConstants::ACTIVE . '  and faq_deleted = ' . applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('faqcat_type', '=', $faqPage);
        if ($faqCatId) {
            $srch->addCondition('faqcat_id', 'IN', $faqCatId);
        }

        $question = FatApp::getPostedData('question', FatUtility::VAR_STRING, '');
        if (!empty($question)) {
            $srchCondition = $srch->addCondition('faq_title', 'like', "%$question%");
            $srch->doNotLimitRecords();
        }

        $srch->addOrder('faqcat_display_order', 'asc');
        $srch->addOrder('faq_faqcat_id', 'asc');
        $srch->addOrder('faq_display_order', 'asc');

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $json['recordCount'] = $srch->recordCount();


        if (isset($srchCondition)) {
            $srchCondition->remove();
        }

        $this->set('siteLangId', $this->siteLangId);
        $this->set('faqCatIdArr', $faqCatId);
        $this->set('list', $records);
        
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $json['html'] = $this->_template->render(false, false, '_partial/no-record-found.php', true, false);
        if (!empty($records)) {
            $json['html'] = $this->_template->render(false, false, 'custom/search-faqs.php', true, false);
        }
        FatUtility::dieJsonSuccess($json);
    }

    public function faqCategoriesPanel()
    {
        $searchFrm = $this->getSearchFaqForm();
        $post = $searchFrm->getFormDataFromArray(FatApp::getPostedData());
        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id and faq_active = ' . applicationConstants::ACTIVE . '  and faq_deleted = ' . applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_type', '=', FaqCategory::FAQ_PAGE);
        $srch->setPageSize(1);
        $qry = $srch->getQuery();
        // echo $qry; die;
        $question = FatApp::getPostedData('question', FatUtility::VAR_STRING, '');
        if (!empty($question)) {
            $srchCondition = $srch->addCondition('faq_title', 'like', "%$question%");
        }
        $srch->addOrder('faqcat_display_order', 'asc');
        $srch->addOrder('faq_faqcat_id', 'asc');
        $srch->addOrder('faq_display_order', 'asc');
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $json['recordCount'] = $srch->recordCount();

        $srch->addGroupBy('faqcat_id');
        $srch->addMultipleFields(array('IFNULL(faqcat_name, faqcat_identifier) as faqcat_name', 'faqcat_id'));
        $srch->addFld('COUNT(*) AS faq_count');
        if (isset($srchCondition)) {
            $srchCondition->remove();
        }
        $rsCat = $srch->getResultSet();
        $recordsCategories = FatApp::getDb()->fetchAll($rsCat);
        // CommonHelper::printArray($recordsCategories);
        $faqMainCat = FatApp::getConfig("CONF_FAQ_PAGE_MAIN_CATEGORY", null, '');

        $this->set('siteLangId', $this->siteLangId);
        $this->set('list', $records);
        // commonHelper::printArray($recordsCategories); die;
        $this->set('listCategories', $recordsCategories);
        $this->set('faqMainCat', $faqMainCat);
        $this->set('page', 'faq');
        
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $json['html'] = $this->_template->render(false, false, '_partial/no-record-found.php', true, false);
        if (!empty($records)) {
            $json['html'] = $this->_template->render(false, false, 'custom/search-faqs.php', true, false);
        }
        $json['categoriesPanelHtml'] = $this->_template->render(false, false, 'custom/faq-categories-panel.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    public function faqQuestionsPanel($catId = 0)
    {
        $searchFrm = $this->getSearchFaqForm();
        $post = $searchFrm->getFormDataFromArray(FatApp::getPostedData());
        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id and faq_active = ' . applicationConstants::ACTIVE . '  and faq_deleted = ' . applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_type', '=', FaqCategory::FAQ_PAGE);
        $srch->addCondition('faqcat_id', '=', $catId);
        $srch->addOrder('faqcat_display_order', 'ASC');
        $srch->addOrder('faq_faqcat_id', 'ASC');
        $srch->addOrder('faq_display_order', 'ASC');
        $srch->addMultipleFields(array('faq_title', 'faqcat_id', 'faq_id'));
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $json['recordCount'] = $srch->recordCount();
        $this->set('siteLangId', $this->siteLangId);
        $this->set('listCategories', $records);
        $json['html'] = $this->_template->render(false, false, '_partial/no-record-found.php', true, false);
        if (!empty($records)) {
            $json['html'] = $this->_template->render(false, false, 'custom/search-faqs.php', true, false);
        }
        $json['categoriesPanelHtml'] = $this->_template->render(false, false, 'custom/faq-questions-panel.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    public function becomeSeller()
    {
        /* faqs[ */
        $srch = FaqCategory::getSearchObject($this->siteLangId);
        $srch->joinTable('tbl_faqs', 'LEFT OUTER JOIN', 'faq_faqcat_id = faqcat_id and faq_active = ' . applicationConstants::ACTIVE . '  and faq_featured = ' . applicationConstants::YES . '  and faq_deleted = ' . applicationConstants::NO);
        $srch->joinTable('tbl_faqs_lang', 'LEFT OUTER JOIN', 'faqlang_faq_id = faq_id');
        $srch->addCondition('faqlang_lang_id', '=', $this->siteLangId);
        $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('faqcat_featured', '=', applicationConstants::YES);

        $srch->addOrder('faqcat_display_order', 'asc');
        $srch->addOrder('faq_faqcat_id', 'asc');
        $srch->addOrder('faq_display_order', 'asc');

        $rs = $srch->getResultSet();
        $faqs = FatApp::getDb()->fetchAll($rs);
        /* ] */

        /* success stories[ */
        $storiesSrch = SuccessStories::getSearchObject($this->siteLangId);
        $storiesSrch->doNotCalculateRecords();
        $storiesSrch->doNotLimitRecords();
        $storiesSrch->addCondition('sstory_featured', '=', applicationConstants::YES);
        $storiesSrch->addOrder('RAND()');
        $storiesSrch->addMultipleFields(array('sstory_content', 'sstory_name', 'sstory_site_domain'));
        $sroriesRs = $storiesSrch->getResultSet();
        $stories = FatApp::getDb()->fetchAll($sroriesRs);
        /* ] */


        /* content Blocks[ */
        $becomeSellerPageBlock = array(
            Extrapage::BECOME_SELLER_PAGE_BLOCK1,
            Extrapage::BECOME_SELLER_PAGE_BLOCK2,
            Extrapage::BECOME_SELLER_PAGE_BLOCK3,
            Extrapage::BECOME_SELLER_PAGE_BLOCK4,
            Extrapage::BECOME_SELLER_PAGE_BLOCK5,
            Extrapage::BECOME_SELLER_PAGE_BLOCK6,
            Extrapage::BECOME_SELLER_PAGE_BLOCK7,
        );

        $srch = Extrapage::getSearchObject($this->siteLangId);
        $srch->addCondition('ep.epage_type', 'in', $becomeSellerPageBlock);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $contentBlocks = FatApp::getDb()->fetchAll($rs, 'epage_type');
        //CommonHelper::printArray($contentBlocks);
        /* ] */

        $this->set('faqs', $faqs);
        $this->set('stories', $stories);
        $this->set('contentBlocks', $contentBlocks);
        $this->set('bodyClass', 'is--seller');
        $this->set('showCategoryLinksAndHeaderSearch', false);
        $this->_template->render();
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $parameters = FatApp::getParameters();

        switch ($action) {

            case 'faqDetail':

                $srch = FaqCategory::getSearchObject($this->siteLangId);
                $srch->addCondition('faqcat_active', '=', applicationConstants::ACTIVE);
                $srch->addCondition('faqcat_type', '=', FaqCategory::FAQ_PAGE);
                $srch->addCondition('faqcat_id', '=', $parameters[0]);
                $srch->setPageSize(1);

                $rs = $srch->getResultSet();
                $records = FatApp::getDb()->fetch($rs);

                $nodes[] = array('title' => Labels::getLabel('LBL_Faq', $this->siteLangId), 'href' => UrlHelper::generateUrl('custom', 'Faq'));
                $nodes[] = array('title' => $records['faqcat_name']);

                break;

            case 'faq':
                $nodes[] = array('title' => Labels::getLabel('LBL_Faq', $this->siteLangId), 'href' => UrlHelper::generateUrl('custom', 'Faq'));
                break;

            default:
                $nodes[] = array('title' => FatUtility::camel2dashed($action));
                break;
        }
        return $nodes;
    }

    public function paymentFailed()
    {
        $textMessage = sprintf(Labels::getLabel('MSG_customer_failure_order', $this->siteLangId), UrlHelper::generateUrl('custom', 'contactUs'));
        $this->set('textMessage', $textMessage);
        if (!FatApp::getConfig('CONF_MAINTAIN_CART_ON_PAYMENT_FAILURE', FatUtility::VAR_INT, applicationConstants::NO) && isset($_SESSION['cart_order_id']) && $_SESSION['cart_order_id'] != '') {
            $cartOrderId = $_SESSION['cart_order_id'];
            $orderObj = new Orders();
            $orderDetail = $orderObj->getOrderById($cartOrderId);

            $cartInfo = json_decode($orderDetail['order_cart_data'], true);
            unset($cartInfo['shopping_cart']);

            $db = FatApp::getDb();
            if (!$db->deleteRecords('tbl_user_cart', array('smt' => '`usercart_user_id`=? and `usercart_type`=?', 'vals' => array(UserAuthentication::getLoggedUserId(), CART::TYPE_PRODUCT)))) {
                Message::addErrorMessage($db->getError());
                FatApp::redirectUser(UrlHelper::generateFullUrl('Checkout'));
            }
            /* $cartObj = new Cart();
            foreach ($cartInfo as $key => $quantity) {
                $keyDecoded = json_decode(base64_decode($key), true);

                $selprod_id = 0;


                if (strpos($keyDecoded, Cart::CART_KEY_PREFIX_PRODUCT) !== false) {
                    $selprod_id = FatUtility::int(str_replace(Cart::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
                }
                $cartObj->add($selprod_id, $quantity);
            }
            $cartObj->updateUserCart(); */
        }
        if (CommonHelper::isAppUser()) {
            $this->set('exculdeMainHeaderDiv', true);
            $this->_template->render(false, false);
        } else {
            $this->_template->render();
        }
    }

    public function paymentCancel()
    {
        /* echo FatApp::getConfig('CONF_MAINTAIN_CART_ON_PAYMENT_CANCEL',FatUtility::VAR_INT,applicationConstants::NO);
        echo $_SESSION['cart_order_id']; */
        if (!FatApp::getConfig('CONF_MAINTAIN_CART_ON_PAYMENT_CANCEL', FatUtility::VAR_INT, applicationConstants::NO) && isset($_SESSION['cart_order_id']) && $_SESSION['cart_order_id'] != '') {
            $cartOrderId = $_SESSION['cart_order_id'];
            $orderObj = new Orders();
            $orderDetail = $orderObj->getOrderById($cartOrderId);

            $cartInfo = json_decode($orderDetail['order_cart_data'], true);
            unset($cartInfo['shopping_cart']);
            $db = FatApp::getDb();
            if (!$db->deleteRecords('tbl_user_cart', array('smt' => '`usercart_user_id`=? and `usercart_type`=?', 'vals' => array(UserAuthentication::getLoggedUserId(), CART::TYPE_PRODUCT)))) {
                Message::addErrorMessage($db->getError());
                FatApp::redirectUser(UrlHelper::generateFullUrl('Checkout'));
            }

            /* $cartObj = new Cart();
            foreach ($cartInfo as $key => $quantity) {
                $keyDecoded = json_decode(base64_decode($key), true);

                $selprod_id = 0;


                if (strpos($keyDecoded, Cart::CART_KEY_PREFIX_PRODUCT) !== false) {
                    $selprod_id = FatUtility::int(str_replace(Cart::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
                }
                $cartObj->add($selprod_id, $quantity);
            }
            $cartObj->updateUserCart(); */
        }
        if (isset($_SESSION['order_type']) && $_SESSION['order_type'] == Orders::ORDER_SUBSCRIPTION) {
            FatApp::redirectUser(UrlHelper::generateFullUrl('SubscriptionCheckout'));
        }

        FatApp::redirectUser(UrlHelper::generateFullUrl('Checkout'));
    }

    public function paymentSuccess($orderId, $print = '')
    {
        if (!$orderId) {
            FatUtility::exitWithErrorCode(404);
        }

        $orderObj = new Orders();
        $orderInfo = $orderObj->getOrderById($orderId, $this->siteLangId);

        $user = [];
        if ($orderInfo['order_user_id'] > 0) {
            if (0 < UserAuthentication::getLoggedUserId(true) && $orderInfo['order_user_id'] != UserAuthentication::getLoggedUserId(true)) {
                $message = Labels::getLabel("LBL_INVALID_ORDER", $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError(current($message));
                }
                Message::addErrorMessage($message);
                FatApp::redirectUser(UrlHelper::generateUrl());
            }

            $orderProdData = OrderProduct::getOpArrByOrderId($orderId);
            foreach ($orderProdData as $data) {
                $amount = $data['op_unit_price'] * $data['op_qty'];
                AbandonedCart::saveAbandonedCart($orderInfo['order_user_id'], $data['op_selprod_id'], $data['op_qty'], AbandonedCart::ACTION_PURCHASED, $amount);
            }

            $userObj = new User($orderInfo['order_user_id']);
            $srch = $userObj->getUserSearchObj(['credential_email']);
            $rs = $srch->getResultSet();
            if (!$rs) {
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($srch->getError());
                }
                FatUtility::exitWithErrorCode(404);
            }
            $user = FatApp::getDb()->fetch($rs);
                  
            if($orderInfo['order_type']  == Orders::ORDER_SUBSCRIPTION){
                $cartObj = new SubscriptionCart($orderInfo['order_user_id'], $this->siteLangId);
                $cartObj->clear();
                $cartObj->updateUserSubscriptionCart();
            }else{
                $cartObj = new Cart($orderInfo['order_user_id'], $this->siteLangId, $this->app_user['temp_user_id']);
                $cartObj->clear();
                $cartObj->updateUserCart();
            }
            
        }

        $orderFulFillmentTypeArr = [];
        if ($orderInfo['order_type'] == Orders::ORDER_PRODUCT) {
            if (!empty($user)) {
                $searchReplaceArray = array(
                    '{BUYER-EMAIL}' => '<strong>' . $user['credential_email'] . '</strong>',
                );
                $textMessage = Labels::getLabel('MSG_CUSTOMER_SUCCESS_ORDER_{BUYER-EMAIL}', $this->siteLangId);
                $textMessage = CommonHelper::replaceStringData($textMessage, $searchReplaceArray);
            } else {
                $textMessage = Labels::getLabel('MSG_CUSTOMER_SUCCESS_ORDER', $this->siteLangId);
            }

            $srch = new OrderProductSearch($this->siteLangId);
            $srch->joinShippingCharges();
            $srch->joinAddress();
            $srch->addCondition('op_order_id', '=', $orderId);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();

            $srch->addMultipleFields(
                array('ops.*', 'op_product_type', 'op_invoice_number', 'addr.*', 'ts.*', 'tc.*', 'COALESCE(state_name, state_identifier) as state_name', 'COALESCE(country_name, country_code) as country_name')
            );
            $srch->addGroupBy('opshipping_pickup_addr_id');
            $rs = $srch->getResultSet();
            $orderFulFillmentTypeArr = FatApp::getDb()->fetchAll($rs);
            // CommonHelper::printArray($orderFulFillmentTypeArr, true);
        } elseif ($orderInfo['order_type'] == Orders::ORDER_SUBSCRIPTION) {
            $searchReplaceArray = array(
                '{account}' => '<a href="' . UrlHelper::generateUrl('seller') . '" class="link">' . Labels::getLabel('MSG_My_Account', $this->siteLangId) . '</a>',
                '{subscription}' => '<a href="' . UrlHelper::generateUrl('seller', 'subscriptions') . '" class="link">' . Labels::getLabel('MSG_My_Subscription', $this->siteLangId) . '</a>',
            );
            $textMessage = Labels::getLabel('MSG_subscription_success_order_{account}_{subscription}', $this->siteLangId);
            $textMessage = str_replace(array_keys($searchReplaceArray), array_values($searchReplaceArray), $textMessage);
        } elseif ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $searchReplaceArray = array(
                '{account}' => '<a href="' . UrlHelper::generateUrl('account') . '" class="link">' . Labels::getLabel('MSG_My_Account', $this->siteLangId) . '</a>',
                '{credits}' => '<a href="' . UrlHelper::generateUrl('account', 'credits') . '" class="link">' . Labels::getLabel('MSG_My_Credits', $this->siteLangId) . '</a>',
            );
            $textMessage = Labels::getLabel('MSG_wallet_success_order_{account}_{credits}', $this->siteLangId);
            $textMessage = str_replace(array_keys($searchReplaceArray), array_values($searchReplaceArray), $textMessage);
        } else {
            $message = Labels::getLabel('MSG_INVALID_ORDER_TYPE', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            FatUtility::exitWithErrorCode(404);
        }

        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $textMessage = str_replace('{contactus}', '<a href="' . UrlHelper::generateUrl('custom', 'contactUs') . '" class="link">' . Labels::getLabel('MSG_Store_Owner', $this->siteLangId) . '</a>', Labels::getLabel('MSG_guest_success_order_{contactus}', $this->siteLangId));
        }

        if (UserAuthentication::isGuestUserLogged()) {
            unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]);
        }

        $address = $orderObj->getOrderAddresses($orderInfo['order_id']);
        if (!empty($address)) {
            $orderInfo['billingAddress'] = $address[Orders::BILLING_ADDRESS_TYPE];
            $orderInfo['shippingAddress'] = (!empty($address[Orders::SHIPPING_ADDRESS_TYPE]) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : []);
        }

        $orderInfo['orderProducts'] = $orderObj->getChildOrders(['order_id' => $orderInfo['order_id']], $orderInfo['order_type'], $orderInfo['order_language_id'], true);

        $this->set('textMessage', $textMessage);
        $this->set('orderInfo', $orderInfo);

        $print = ('print' == $print);
        $this->set('print', $print);

        $this->set('orderFulFillmentTypeArr', $orderFulFillmentTypeArr);
        if (CommonHelper::isAppUser() && false ===  MOBILE_APP_API_CALL) {
            $this->set('exculdeMainHeaderDiv', true);
            $this->_template->render(false, false);
        } else {
            $this->_template->render();
        }
    }

    /* public function favoriteShops( $userId ){
    $userId = FatUtility::int($userId);

    $searchForm = $this->getfavoriteShopsForm($this->siteLangId);
    $searchForm->fill(array('user_id'=>$userId));

    $user = new User($userId);
    $userInfo = $user->getUserInfo(array('user_id','user_name','user_city'));

    $this->set('userInfo',$userInfo);
    $this->set('searchForm',$searchForm);
    $this->_template->render();
    }
    */
    /* public function SearchFavoriteShops(){
    $db = FatApp::getDb();
    $data = FatApp::getPostedData();
    $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
    $pagesize = FatApp::getConfig('CONF_PAGE_SIZE',FatUtility::VAR_INT, 10);

    $searchForm = $this->getfavoriteShopsForm($this->siteLangId);
    $post = $searchForm->getFormDataFromArray($data);

    $userId = $post['user_id'];
    if( 1 > $userId ){
    FatUtility::dieWithError( Labels::getLabel('LBL_Invalid_Access_ID',$this->siteLangId));
    }

    $srch = new UserFavoriteShopSearch($this->siteLangId);
    $srch->joinWhosFavouriteUser();
    $srch->joinShops();
    $srch->joinShopCountry();
    $srch->joinShopState();
    $srch->joinFavouriteUserShopsCount();
    $srch->addMultipleFields(array( 'ufs_shop_id as shop_id','IFNULL(shop_name, shop_identifier) as shop_name','IFNULL(state_name, state_identifier) as state_name','country_name','ufs_user_id','user_name','userFavShopcount'));
    $srch->addCondition('ufs_user_id','=',$userId);

    $page = (empty($page) || $page <= 0)?1:$page;
    $page = FatUtility::int($page);
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);

    $rs = $srch->getResultSet();
    $userFavoriteShops = $db->fetchAll( $rs, 'shop_id');

    $totalProdCountToDisplay = 4;
    $prodSrchObj = new ProductSearch( $this->siteLangId );
    $prodSrchObj->setDefinedCriteria();
    $prodSrchObj->setPageSize($totalProdCountToDisplay);

    foreach($userFavoriteShops as $val){
    $prodSrch = clone $prodSrchObj;
    $prodSrch->addShopIdCondition( $val['shop_id'] );
    $prodSrch->addMultipleFields( array( 'selprod_id', 'product_id', 'shop_id','IFNULL(shop_name, shop_identifier) as shop_name',
    'IFNULL(product_name, product_identifier) as product_name',
    'IF(selprod_stock > 0, 1, 0) AS in_stock') );
    $prodRs = $prodSrch->getResultSet();
    $userFavoriteShops[$val['shop_id']]['products'] = $db->fetchAll( $prodRs);
    $userFavoriteShops[$val['shop_id']]['totalProducts'] =     $prodSrch->recordCount();
    }

    $this->set('userFavoriteShops',$userFavoriteShops);
    $this->set('totalProdCountToDisplay',$totalProdCountToDisplay);
    $this->set('pageCount',$srch->pages());
    $this->set('recordCount',$srch->recordCount());
    $this->set('page', $page);
    $this->set('pageSize', $pagesize);
    $this->set('postedData', $post);

    $startRecord = ($page-1)* $pagesize + 1 ;
    $endRecord = $pagesize;
    $totalRecords = $srch->recordCount();
    if ($totalRecords < $endRecord) { $endRecord = $totalRecords; }
    $json['totalRecords'] = $totalRecords;
    $json['startRecord'] = $startRecord;
    $json['endRecord'] = $endRecord;
    $json['html'] = $this->_template->render( false, false, 'custom/search-favorite-shops.php', true, false);
    $json['loadMoreBtnHtml'] = $this->_template->render( false, false, '_partial/load-more-btn.php', true, false);
    FatUtility::dieJsonSuccess($json);
    }
    */

    public function referral($userReferralCode, $sharingUrl)
    {
        //echo 'Issue Pending, i.e if Sharing Url of structure like this: products/view/8, then it is not handeled, so need to add fix of URL.';
        //echo $sharingUrl; die();

        if (!FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE")) {
            Message::addErrorMessage(Labels::getLabel("LBL_Refferal_module_no_longer_active", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl());
        }
        $userSrchObj = User::getSearchObject();
        $userSrchObj->doNotCalculateRecords();
        $userSrchObj->doNotLimitRecords();
        $userSrchObj->addCondition('user_referral_code', '=', $userReferralCode);
        $userSrchObj->addMultipleFields(array('user_id', 'user_referral_code'));
        $rs = $userSrchObj->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!$row || $userReferralCode == '' || $row['user_referral_code'] != $userReferralCode || $sharingUrl == '') {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Referral_code", $this->siteLangId));
        }

        /* NOT HANDLED:, if user entered referral url with referral code and any abc string, then still that computer system will save the referral code and upon signing up will credit points to referral user as per the logic implemented in application. */

        $cookieExpiryDays = FatApp::getConfig("CONF_REFERRER_URL_VALIDITY", FatUtility::VAR_INT, 10);

        $cookieValue = array('data' => $row['user_referral_code'], 'creation_time' => time());
        $cookieValue = serialize($cookieValue);

        CommonHelper::setCookie('referrer_code_signup', $cookieValue, time() + 3600 * 24 * $cookieExpiryDays);
        CommonHelper::setCookie('referrer_code_checkout', $row['user_referral_code'], time() + 3600 * 24 * $cookieExpiryDays);

        /* setcookie( 'referrer_code_signup', $row['user_referral_code'], time()+3600*24*$cookieExpiryDays, CONF_WEBROOT_URL, '', false, true );
        setcookie( 'referrer_code_checkout', $row['user_referral_code'], time()+3600*24*$cookieExpiryDays, CONF_WEBROOT_URL, '', false, true ); */
        FatApp::redirectUser('/' . $sharingUrl);
    }

    private function getSearchFaqForm()
    {
        $frm = new Form('frmSearchFaqs');
        $frm->addTextbox(Labels::getLabel('LBL_Enter_your_question', $this->siteLangId), 'question');
        $frm->addSubmitButton('', 'btn_submit', '');
        return $frm;
    }

    private function getfavoriteShopsForm()
    {
        $frm = new Form('frmSearchfavoriteShops');
        $frm->addHiddenField('', 'user_id');
        return $frm;
    }

    private function contactUsForm()
    {
        $frm = new Form('frmContact');
        $frm->addRequiredField(Labels::getLabel('LBL_Your_Name', $this->siteLangId), 'name', '');
        $frm->addEmailField(Labels::getLabel('LBL_Your_Email', $this->siteLangId), 'email', '');

        $frm->addHiddenField('', 'phone_dcode');
        $fld_phn = $frm->addRequiredField(Labels::getLabel('LBL_Your_Phone', $this->siteLangId), 'phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $fld_phn->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $fld_phn->htmlAfterField='<span class="note">'.Labels::getLabel('LBL_e.g.', $this->siteLangId).': '.implode(', ', ValidateElement::PHONE_FORMATS).'</span>';
        $fld_phn->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));

        $frm->addTextArea(Labels::getLabel('LBL_Your_Message', $this->siteLangId), 'message', '')->requirements()->setRequired();

        CommonHelper::addCaptchaField($frm);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('BTN_SUBMIT', $this->siteLangId));
        return $frm;
    }

    public function sitemap()
    {
        $brandSrch = Brand::getListingObj($this->siteLangId, array('brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'), true);
        $brandSrch->doNotCalculateRecords();
        $brandSrch->doNotLimitRecords();
        $brandSrch->addOrder('brand_name', 'asc');
        $brandRs = $brandSrch->getResultSet();
        $brandsArr = FatApp::getDb()->fetchAll($brandRs);
        $categoriesArr = ProductCategory::getProdCatParentChildWiseArr($this->siteLangId, 0, true, false, true);
        $contentPages = ContentPage::getPagesForSelectBox($this->siteLangId);
        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinSellerSubscription();
        $srch->addOrder('shop_name');
        $shopRs = $srch->getResultSet();
        $allShops = FatApp::getDb()->fetchAll($shopRs, 'shop_id');


        $this->set('allShops', $allShops);
        $this->set('contentPages', $contentPages);
        $this->set('categoriesArr', $categoriesArr);
        $this->set('allBrands', $brandsArr);
        $this->_template->render();
    }

    public function updateUserCookies()
    {
        $_SESSION['cookies_enabled'] = true;
        return true;
    }

    public function requestDemo()
    {
        $this->_template->render(false, false);
    }

    public function feedback()
    {
        $this->_template->render();
    }

    public function downloadLogFile($fileName)
    {
        AttachedFile::downloadAttachment('import-error-log/' . $fileName, $fileName);
    }

    public function deleteErrorLogFiles($hoursBefore = '4')
    {
        if (!ImportexportCommon::deleteErrorLogFiles($hoursBefore)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_hours', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function deleteBulkUploadSubDirs($hoursBefore = '48')
    {
        $obj = new UploadBulkImages();
        $msg = $obj->deleteBulkUploadSubDirs($hoursBefore);
        FatUtility::dieJsonSuccess($msg);
    }

    public function signupAgreementUrls()
    {
        $privacyPolicyLink = FatApp::getConfig('CONF_PRIVACY_POLICY_PAGE', FatUtility::VAR_STRING, '');
        $termsAndConditionsLink = FatApp::getConfig('CONF_TERMS_AND_CONDITIONS_PAGE', FatUtility::VAR_STRING, '');
        $data = array(
            'privacyPolicyLink' => UrlHelper::generateFullUrl('cms', 'view', array($privacyPolicyLink)),
            'faqLink' => UrlHelper::generateFullUrl('custom', 'faq'),
            'termsAndConditionsLink' => UrlHelper::generateFullUrl('cms', 'view', array($termsAndConditionsLink)),
        );
        $this->set('data', $data);
        $this->_template->render();
    }

    public function setupSidebarVisibility($openSidebar = 1)
    {
        setcookie('openSidebar', $openSidebar, 0, CONF_WEBROOT_URL);
    }

    public function updateScreenResolution($width, $height)
    {
        setcookie('screenWidth', $width, 0, CONF_WEBROOT_URL);
        setcookie('screenHeight', $height, 0, CONF_WEBROOT_URL);
    }
}
