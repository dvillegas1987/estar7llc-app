<?php

class CartController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);

        /* For API Use */
        $this->set('cartPage', true);
        /* For API Use */
    }

    public function index()
    {
        $cartObj = new Cart();
        $cartObj->unsetCartCheckoutType();
        $cartObj->invalidateCheckoutType();
        $cartObj->removeProductShippingMethod();
        $cartObj->removeProductPickUpAddresses();
        $productsArr = $cartObj->getProducts($this->siteLangId);
        $fulfillmentProdArr = [
            Shipping::FULFILMENT_SHIP => [],
            Shipping::FULFILMENT_PICKUP => [],
        ];
        foreach ($productsArr as $product) {
            switch ($product['fulfillment_type']) {
                case Shipping::FULFILMENT_SHIP:
                    $fulfillmentProdArr[Shipping::FULFILMENT_SHIP][] = $product['selprod_id'];
                    break;
                case Shipping::FULFILMENT_PICKUP:
                    $fulfillmentProdArr[Shipping::FULFILMENT_PICKUP][] = $product['selprod_id'];
                    break;
                default:
                    $fulfillmentProdArr[Shipping::FULFILMENT_SHIP][] = $product['selprod_id'];
                    $fulfillmentProdArr[Shipping::FULFILMENT_PICKUP][] = $product['selprod_id'];
                    break;
            }
        }

        $this->set('shipProductsCount', count($fulfillmentProdArr[Shipping::FULFILMENT_SHIP]));
        $this->set('pickUpProductsCount', count($fulfillmentProdArr[Shipping::FULFILMENT_PICKUP]));
        $this->set('total', $cartObj->countProducts());
        $this->set('hasPhysicalProduct', $cartObj->hasPhysicalProduct());
        $this->_template->render();
    }

    public function listing($fulfilmentType = Shipping::FULFILMENT_SHIP)
    {
        $products['groups'] = array();
        $products['single'] = array();
        $loggedUserId = UserAuthentication::getLoggedUserId(true);
        $cartObj = new Cart($loggedUserId, $this->siteLangId, $this->app_user['temp_user_id'], Cart::PAGE_TYPE_CART);
        if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0)) {
            $cartObj->excludeTax();
        }
        $cartObj->unsetCartCheckoutType();
        $cartObj->invalidateCheckoutType();
        $productsArr = $cartObj->getProducts($this->siteLangId);
        $prodGroupIds = array();
        $fulfillmentProdArr = [
            Shipping::FULFILMENT_SHIP => [],
            Shipping::FULFILMENT_PICKUP => [],
        ];

        /* Save For Later Products Listing [ */
        $srch = new UserWishListProductSearch($this->siteLangId);
        $srch->joinWishLists();
        $srch->joinSellerProducts();
        $srch->joinProducts();
        $srch->joinBrands();
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription($this->siteLangId, true);
        $srch->addSubscriptionValidCondition();
        $srch->joinSellerProductSpecialPrice();
        $srch->addCondition('uwlist_user_id', '=', $loggedUserId);
        $srch->addCondition('uwlist_type', '=', UserWishList::TYPE_SAVE_FOR_LATER);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_active', '=', applicationConstants::YES);

        /* groupby added, beacause if same product is linked with multiple categories, then showing in repeat for each category[ */
        $srch->addGroupBy('selprod_id');
        /* ] */

        $srch->addMultipleFields(array('uwlp_uwlist_id', 'selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'product_id', 'IFNULL(product_name, product_identifier) as product_name', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'IFNULL(splprice_price, selprod_price) AS theprice', 'IFNULL(shop_name, shop_identifier) as shop_name'));
        $srch->addOrder('uwlp_added_on', 'DESC');
        $rs = $srch->getResultSet();
        $saveForLaterProducts = FatApp::getDb()->fetchAll($rs);
        if (count($saveForLaterProducts)) {
            foreach ($saveForLaterProducts as &$arr) {
                $arr['options'] = SellerProduct::getSellerProductOptions($arr['selprod_id'], true, $this->siteLangId);
            }
        }
        /* ] */

        if (0 < count($productsArr) || true === MOBILE_APP_API_CALL || 0 < count($saveForLaterProducts)) {
            foreach ($productsArr as $product) {
                switch ($product['fulfillment_type']) {
                    case Shipping::FULFILMENT_SHIP:
                        $fulfillmentProdArr[Shipping::FULFILMENT_SHIP][] = $product['selprod_id'];
                        break;
                    case Shipping::FULFILMENT_PICKUP:
                        $fulfillmentProdArr[Shipping::FULFILMENT_PICKUP][] = $product['selprod_id'];
                        break;
                    default:
                        $fulfillmentProdArr[Shipping::FULFILMENT_SHIP][] = $product['selprod_id'];
                        $fulfillmentProdArr[Shipping::FULFILMENT_PICKUP][] = $product['selprod_id'];
                        break;
                }
            }

            if (true === MOBILE_APP_API_CALL) {
                $cartObj->removeProductShippingMethod();
                $cartObj->removeUsedRewardPoints();
                
                $loggedUserId = UserAuthentication::getLoggedUserId(true);

                $billingAddressDetail = array();
                $billingAddressId = $cartObj->getCartBillingAddress();
                if ($billingAddressId > 0) {
                    $address = new Address($billingAddressId);
                    $billingAddressDetail = $address->getData(Address::TYPE_USER, $loggedUserId);
                }

                $shippingddressDetail = array();
                $shippingAddressId = $cartObj->getCartShippingAddress();
                if ($shippingAddressId > 0) {
                    $address = new Address($shippingAddressId);
                    $shippingddressDetail = $address->getData(Address::TYPE_USER, $loggedUserId);
                }
                
                $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id'], Cart::PAGE_TYPE_CART);
                $cartObj->setFulfilmentType($fulfilmentType);
                $cartObj->setCartCheckoutType($fulfilmentType);
                $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);

                $this->set('cartSummary', $cartSummary);

                $this->set('cartSelectedBillingAddress', $billingAddressDetail);
                $this->set('cartSelectedShippingAddress', $shippingddressDetail);
                $this->set('isShippingSameAsBilling', $cartObj->getShippingAddressSameAsBilling());
                $this->set('selectedBillingAddressId', $billingAddressId);
                $this->set('selectedShippingAddressId', $shippingAddressId);

                $this->set('cartProductsCount', count($productsArr));
                $this->set('shipProductsCount', count($fulfillmentProdArr[Shipping::FULFILMENT_SHIP]));
                $this->set('pickUpProductsCount', count($fulfillmentProdArr[Shipping::FULFILMENT_PICKUP]));
            }

            $fulFillmentArr = Shipping::getFulFillmentArr($this->siteLangId);
            if (!array_key_exists($fulfilmentType, $fulFillmentArr)) {
                $fulfilmentType = Shipping::FULFILMENT_SHIP;
            }

            $this->set('saveForLaterProducts', $saveForLaterProducts);
            $this->set('products', $productsArr);
            $this->set('prodGroupIds', $prodGroupIds);
            $this->set('fulfilmentType', $fulfilmentType);
            $this->set('fulfillmentProdArr', $fulfillmentProdArr);
            $this->set('hasPhysicalProduct', $cartObj->hasPhysicalProduct());

            $templateName = 'cart/ship-listing.php';
            if ($fulfilmentType == Shipping::FULFILMENT_PICKUP) {
                $templateName = 'cart/pickup-listing.php';
            }
            
        } else {
            $srch = EmptyCartItems::getSearchObject($this->siteLangId);
            $srch->doNotCalculateRecords();
            $srch->addMultipleFields(array('emptycartitem_title', 'emptycartitem_url', 'emptycartitem_url_is_newtab'));
            $rs = $srch->getResultSet();
            $EmptyCartItems = FatApp::getDb()->fetchAll($rs);
            $this->set('EmptyCartItems', $EmptyCartItems);
            $templateName = 'cart/empty-cart.php';
        }

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render(true, true, $templateName);
        }

        $json['html'] = $this->_template->render(false, false, $templateName, true, false);
        $json['cartProductsCount'] = count($productsArr);
        $json['hasPhysicalProduct'] = $cartObj->hasPhysicalProduct();
        $json['shipProductsCount'] = count($fulfillmentProdArr[Shipping::FULFILMENT_SHIP]);
        $json['pickUpProductsCount'] = count($fulfillmentProdArr[Shipping::FULFILMENT_PICKUP]);
        FatUtility::dieJsonSuccess($json);
    }

    public function add()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl());
        }
        $loggedUserId = UserAuthentication::getLoggedUserId(true);
        if (UserAuthentication::isUserLogged()) {
            $user_is_buyer = User::getAttributesById($loggedUserId, 'user_is_buyer');
            if (!$user_is_buyer) {
                $errMsg = Labels::getLabel('MSG_Please_login_with_buyer_account_to_add_products_to_cart', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($errMsg);
                }
                Message::addErrorMessage($errMsg);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(UrlHelper::generateUrl());
            }
        }

        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $quantity = FatApp::getPostedData('quantity', FatUtility::VAR_INT, 1);

        if (true === MOBILE_APP_API_CALL) {
            $productsToAdd = isset($post['addons']) ? json_decode($post['addons'], true) : array();
        } else {
            $productsToAdd = isset($post['addons']) ? $post['addons'] : array();
        }
        $productsToAdd[$selprod_id] = $quantity;

        $this->addProductToCart($productsToAdd, $selprod_id);

        $db = FatApp::getDb();
        $wishlistId = FatApp::getPostedData('uwlist_id', FatUtility::VAR_INT, 0);
        $rowAction = FatApp::getPostedData('rowAction', FatUtility::VAR_INT, 0); // 1 = Remove From Wishlist / 0 = Add To Wishlist
        if (0 < $wishlistId) {
            $srch = UserWishList::getSearchObject($loggedUserId);
            $srch->addMultipleFields(array('uwlist_id'));
            $srch->doNotCalculateRecords();
            $srch->setPageSize(1);
            $srch->addCondition('uwlist_id', '=', $wishlistId);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            if (!is_array($row) || empty($row)) {
                $msg = Labels::getLabel('LBL_INVALID_WISHLIST_ID', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($msg);
                }
                Message::addErrorMessage($msg);
                FatApp::redirectUser(UrlHelper::generateUrl());
            }

            if (0 < $rowAction) {
                if (!$db->deleteRecords(UserWishList::DB_TBL_LIST_PRODUCTS, array('smt' => 'uwlp_uwlist_id = ? AND uwlp_selprod_id = ?', 'vals' => array($wishlistId, $selprod_id)))) {
                    if (true === MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($db->getError());
                    }
                    Message::addErrorMessage($db->getError());
                    FatApp::redirectUser(UrlHelper::generateUrl());
                }
            } else {
                $wListObj = new UserWishList();
                if (!$wListObj->addUpdateListProducts($wishlistId, $selprod_id)) {
                    if (true === MOBILE_APP_API_CALL) {
                        FatUtility::dieJsonError($wListObj->getError());
                    }
                    Message::addErrorMessage($wListObj->getError());
                    FatUtility::dieWithError(Message::getHtml());
                    FatApp::redirectUser(UrlHelper::generateUrl());
                }
            }
        }
        
        $ufpId  = FatApp::getPostedData('ufp_id', FatUtility::VAR_INT, 0);
        if (0 < $ufpId) {
            if (0 < $rowAction) {
                if (!$db->deleteRecords(Product::DB_TBL_PRODUCT_FAVORITE, array('smt' => 'ufp_user_id = ? AND ufp_id = ?', 'vals' => array($loggedUserId, $ufpId)))) {
                    if (true === MOBILE_APP_API_CALL) {
                        FatUtility::dieJsonError($db->getError());
                    }
                    Message::addErrorMessage($db->getError());
                    FatApp::redirectUser(UrlHelper::generateUrl());
                }
            } else {
                $productObj = new product();
                if (!$productObj->addUpdateUserFavoriteProduct($loggedUserId, $selprod_id)) {
                    if (true === MOBILE_APP_API_CALL) {
                        FatUtility::dieJsonError($productObj->getError());
                    }
                    Message::addErrorMessage($productObj->getError());
                    FatUtility::dieWithError(Message::getHtml());
                    FatApp::redirectUser(UrlHelper::generateUrl());
                }
            }
        }

        if (true === MOBILE_APP_API_CALL) {
            $cartObj = new Cart();
            $this->set('cartItemsCount', $cartObj->countProducts());
            $this->set('msg', Labels::getLabel('LBL_Added_Successfully', $this->siteLangId));
            $this->_template->render();
        }
        $this->set('success_msg', CommonHelper::renderHtml(Message::getHtml()));
        $this->_template->render(false, false, 'json-success.php', false, false);
    }

    public function addSelectedToCart()
    {
        $selprod_id_arr = FatApp::getPostedData('selprod_id');
        $selprod_id_arr = !empty($selprod_id_arr) ? array_filter($selprod_id_arr) : array();
        if (!empty($selprod_id_arr) && is_array($selprod_id_arr)) {
            $successCount = 0;
            foreach ($selprod_id_arr as $selprod_id) {
                $srch = SellerProduct::getSearchObject();
                $srch->addCondition('selprod_id', '=', $selprod_id);
                $srch->addMultipleFields(
                    array('selprod_min_order_qty')
                );
                $rs = $srch->getResultSet();
                $db = FatApp::getDb();
                $sellerProductRow = $db->fetch($rs);
                if (empty($sellerProductRow)) {
                    $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
                    if (true === MOBILE_APP_API_CALL) {
                        FatUtility::dieJsonError($message);
                    }
                    Message::addErrorMessage($message);
                    FatUtility::dieWithError(Message::getHtml());
                }

                $minQty = $sellerProductRow['selprod_min_order_qty'];

                $productsToAdd = [$selprod_id => $minQty];
                $this->addProductToCart($productsToAdd, $selprod_id, false);
                $successCount++;
            }

            if (0 < $successCount) {
                $msg = Labels::getLabel('MSG_{ITEMS}_ITEMS_ADDED_TO_CART', $this->siteLangId);
                $msg = CommonHelper::replaceStringData($msg, ['{ITEMS}' => $successCount]);
                Message::addMessage($msg);
            }

            if (true === MOBILE_APP_API_CALL) {
                $this->_template->render();
            }
            $this->_template->render(false, false, 'json-success.php', false, false);
        } else {
            $message = Labels::getLabel('LBL_Invalid_Request_Parameters', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    private function addProductToCart($productsToAdd, $selprod_id, $logMessage = true)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if ($selprod_id < 1) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $ProductAdded = false;
        foreach ($productsToAdd as $productId => $quantity) {
            if ($productId <= 0) {
                $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }
            $srch = new ProductSearch($this->siteLangId);

            $srch->setDefinedCriteria();
            $srch->joinBrands();
            $srch->joinSellerSubscription();
            $srch->addSubscriptionValidCondition();
            $srch->joinProductToCategory();
            $srch->addCondition('pricetbl.selprod_id', '=', $productId);
            $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
            $srch->addMultipleFields(array('selprod_id', 'selprod_code', 'selprod_min_order_qty', 'selprod_stock', 'product_name'));
            $rs = $srch->getResultSet();
            $db = FatApp::getDb();
            $sellerProductRow = $db->fetch($rs);
            if (!$sellerProductRow || $sellerProductRow['selprod_id'] != $productId) {
                $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }
            $productId = $sellerProductRow['selprod_id'];
            $productAdd = true;
            /* cannot add, out of stock products in cart[ */
            if ($sellerProductRow['selprod_stock'] <= 0) {
                $message = Labels::getLabel('LBL_Out_of_Stock_Products_cannot_be_added_to_cart_%s', $this->siteLangId);
                $message = sprintf($message, FatUtility::decodeHtmlEntities($sellerProductRow['product_name']));
                if (true === MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                if ($productId != $selprod_id) {
                    $productErr['addon'][$productId] = $message;
                } else {
                    $productErr['product'] = $message;
                }
            }
            /* ] */

            /* minimum quantity check[ */
            $minimum_quantity = ($sellerProductRow['selprod_min_order_qty']) ? $sellerProductRow['selprod_min_order_qty'] : 1;
            if ($quantity < $minimum_quantity) {
                $productAdd = false;
                $str = Labels::getLabel('LBL_Please_add_minimum_{minimumquantity}', $this->siteLangId);
                $str = str_replace("{minimumquantity}", $minimum_quantity, $str);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($str);
                }
                if ($productId != $selprod_id) {
                    $productErr['addon'][$productId] = $str . " " . FatUtility::decodeHtmlEntities($sellerProductRow['product_name']);
                } else {
                    $productErr['product'] = $str . " " . FatUtility::decodeHtmlEntities($sellerProductRow['product_name']);
                }
            }
            /* ] */

            /* product availability date check covered in product search model[ ] */
            $loggedUserId = UserAuthentication::getLoggedUserId(true);
            $cartObj = new Cart($loggedUserId, $this->siteLangId, $this->app_user['temp_user_id']);

            /* cannot add quantity more than stock of the product[ */
            $selprod_stock = $sellerProductRow['selprod_stock'] - Product::tempHoldStockCount($productId);
            if ($quantity > $selprod_stock) {
                $productAdd = false;
                $message = Labels::getLabel('MSG_Requested_quantity_more_than_stock_available', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                if ($productId != $selprod_id) {
                    $productErr['addon'][$productId] = Message::addInfo($message . " " . $selprod_stock . " " . strip_tags($sellerProductRow['product_name']));
                } else {
                    $productErr['product'] = $message . " " . $selprod_stock . " " . strip_tags($sellerProductRow['product_name']);
                }
            }
            /* ] */
            if ($productAdd) {
                $returnUserId = (true === MOBILE_APP_API_CALL) ? true : false;
                $cartUserId = $cartObj->add($productId, $quantity, 0, $returnUserId);
                if (true === MOBILE_APP_API_CALL) {
                    $this->set('tempUserId', $cartUserId);
                }
                $ProductAdded = true;
            }
        }

        if (isset($productErr)) {
            Message::addInfo($productErr);
            $this->set('msg', CommonHelper::renderHtml(Message::getHtml($productErr)));
            if (!$ProductAdded) {
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError(current($productErr));
                }
                Message::addErrorMessage($productErr);
                FatUtility::dieWithError(Message::getHtml());
            }
            $this->set('alertType', 'alert--info');
        } else {
            $strProduct = '<a href="' . UrlHelper::generateUrl('Products', 'view', array($selprod_id)) . '">' . strip_tags(html_entity_decode($sellerProductRow['product_name'], ENT_QUOTES, 'UTF-8')) . '</a>';
            $strCart = '<a href="' . UrlHelper::generateUrl('Cart') . '">' . Labels::getLabel('Lbl_Shopping_Cart', $this->siteLangId) . '</a>';
            if ($logMessage) {
                Message::addMessage(sprintf(Labels::getLabel('MSG_Success_cart_add', $this->siteLangId), $strProduct, $strCart));
            }
            $this->set('msg', Labels::getLabel("MSG_Added_to_cart", $this->siteLangId));
        }
        $this->set('total', $cartObj->countProducts());
    }

    public function remove()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl());
        }

        if (!isset($post['key'])) {
            $message = Labels::getLabel('LBL_Product_Key_Required', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id']);
        $key = $post['key'];

        if ('all' == $key) {
            $cartObj->clear(true);
            $cartObj->updateUserCart();
        } else {
            if (true === MOBILE_APP_API_CALL) {
                $key = md5($key);
            }
            if (!$cartObj->remove($key)) {
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($cartObj->getError());
                }
                Message::addMessage($cartObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $cartObj->removeUsedRewardPoints();
            $cartObj->removeProductShippingMethod();
            $cartObj->removeCartDiscountCoupon();
        }
        $total = $cartObj->countProducts();
        $this->set('msg', Labels::getLabel("MSG_Item_removed_from_cart", $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $fulfilmentType = FatApp::getPostedData('fulfilmentType', FatUtility::VAR_INT, Shipping::FULFILMENT_SHIP);
            $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id'], Cart::PAGE_TYPE_CART);
            $cartObj->setFulfilmentType($fulfilmentType);
            $cartObj->setCartCheckoutType($fulfilmentType);
            $productsArr = $cartObj->getProducts($this->siteLangId);
            $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
            $this->set('products', $productsArr);
            $this->set('cartSummary', $cartSummary);
            $this->_template->render();
        }
        $this->set('total', $total);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeGroup()
    {
        $post = FatApp::getPostedData();

        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl());
        }

        $prodgroup_id = FatApp::getPostedData('prodgroup_id', FatUtility::VAR_INT, 0);
        if ($prodgroup_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $cartObj = new Cart();
        if (!$cartObj->removeGroup($prodgroup_id)) {
            Message::addMessage($cartObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel("MSG_Product_Combo_removed_successfully", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function update()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl());
        }
        if (empty($post['key'])) {
            $message = Labels::getLabel('LBL_Invalid_Product', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $key = $post['key'];
        if (true === MOBILE_APP_API_CALL) {
            $key = md5($key);
        }
        $quantity = isset($post['quantity']) ? FatUtility::int($post['quantity']) : 1;
        $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id'], Cart::PAGE_TYPE_CART);
        if (!$cartObj->update($key, $quantity)) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($cartObj->getError());
            }
            Message::addErrorMessage($cartObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $cartObj->removeUsedRewardPoints();
        $cartObj->removeProductShippingMethod();

        if (!empty($cartObj->getWarning())) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($cartObj->getWarning());
            }
            Message::addInfo($cartObj->getWarning());
            FatUtility::dieWithError(Message::getHtml());
            /* $this->set( 'msg', $cartObj->getWarning() ); */
        } else {
            $this->set('msg', Labels::getLabel("MSG_cart_updated_successfully", $this->siteLangId));
        }
        if (true === MOBILE_APP_API_CALL) {
            $fulfilmentType = FatApp::getPostedData('fulfilmentType', FatUtility::VAR_INT, Shipping::FULFILMENT_SHIP);
            $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id'], Cart::PAGE_TYPE_CART);
            $cartObj->setFulfilmentType($fulfilmentType);
            $cartObj->setCartCheckoutType($fulfilmentType);
            $productsArr = $cartObj->getProducts($this->siteLangId);
            $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
            $this->set('products', $productsArr);
            $this->set('cartSummary', $cartSummary);
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateGroup()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl());
        }
        $prodgroup_id = FatApp::getPostedData('prodgroup_id', FatUtility::VAR_INT, 0);
        $quantity = FatApp::getPostedData('quantity', FatUtility::VAR_INT, 1);
        if ($prodgroup_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $cartObj = new Cart();
        if (!$cartObj->updateGroup($prodgroup_id, $quantity)) {
            Message::addMessage($cartObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!empty($cartObj->getWarning())) {
            /* Message::addMessage( $cartObj->getWarning() );
            FatUtility::dieWithError( Message::getHtml() );  */
            $this->set('msg', $cartObj->getWarning());
        } else {
            $this->set('msg', Labels::getLabel("MSG_cart_updated_successfully", $this->siteLangId));
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    /*     public function addGroup(){
    $post = FatApp::getPostedData();
    if( empty($post) ){
    Message::addErrorMessage( Labels::getLabel('LBL_Invalid_Request', $this->siteLangId) );
    FatApp::redirectUser( UrlHelper::generateUrl() );
    }
    $json = array();
    $prodgroup_id = FatApp::getPostedData( 'prodgroup_id', FatUtility::VAR_INT, 0 );
    if( $prodgroup_id <= 0 ){
    Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
    FatUtility::dieWithError( Message::getHtml() );
    }

    $db = FatApp::getDb();

    $row = ProductGroup::getAttributesById( $prodgroup_id, array('prodgroup_id', 'prodgroup_active') );
    if( !$row || $row['prodgroup_id'] != $prodgroup_id || $row['prodgroup_active'] != applicationConstants::ACTIVE ){
    Message::addErrorMessage( Labels::getLabel('LBL_Invalid_Request', $this->siteLangId) );
    FatUtility::dieWithError( Message::getHtml() );
    }

    $srch = new ProductSearch( $this->siteLangId, ProductGroup::DB_PRODUCT_TO_GROUP, ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'product_id' );
    $srch->setBatchProductsCriteria();
    $srch->addCondition( ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX.'prodgroup_id', '=', $row['prodgroup_id'] );
    $srch->addMultipleFields( array( 'selprod_id', 'selprod_stock', 'IF(selprod_stock > 0, 1, 0) AS in_stock') );
    $rs = $srch->getResultSet();
    $pg_products = $db->fetchAll($rs);

    if( !$pg_products ){
    Message::addErrorMessage(Labels::getLabel('LBL_No_Products_under_this_Batch/Combo', $this->siteLangId));
    FatUtility::dieWithError( Message::getHtml() );
    }

    $cart_user_id = session_id();
    if ( UserAuthentication::isUserLogged()  ){
    $cart_user_id = UserAuthentication::getLoggedUserId();
    }

    foreach($pg_products as $product){
    if( !$product['in_stock'] || (1 > $product['selprod_stock'] - Product::tempHoldStockCount($product['selprod_id'])) ){
                Message::addErrorMessage(Labels::getLabel('LBL_one_of_the_product_in_batch_is_out_of_stock', $this->siteLangId));
                FatUtility::dieWithError( Message::getHtml() );
                break;
    }

    }

    $cartObj = new Cart();
    $cartObj->add( 0, 1, $row['prodgroup_id'] );

    $this->set( 'msg', Labels::getLabel("MSG_Added_to_cart", $this->siteLangId) );
    $this->set('total', $cartObj->countProducts() );
    $this->_template->render( false, false, 'json-success.php', false, false );
    } */

    public function applyPromoCode()
    {
        UserAuthentication::checkLogin();

        $post = FatApp::getPostedData();
        $loggedUserId = UserAuthentication::getLoggedUserId();

        if (empty($post)) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (empty($post['coupon_code'])) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $couponCode = $post['coupon_code'];

        /* $couponObj = new DiscountCoupons();
        $couponInfo = $couponObj->getCoupon($couponCode,$this->siteLangId);
        */
        $orderId = isset($_SESSION['order_id']) ? $_SESSION['order_id'] : '';
        $couponInfo = DiscountCoupons::getValidCoupons($loggedUserId, $this->siteLangId, $couponCode, $orderId);
        if ($couponInfo == false) {
            $message = Labels::getLabel('LBL_Invalid_Coupon_Code', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $cartObj = new Cart();
        if (!$cartObj->updateCartDiscountCoupon($couponInfo['coupon_code'])) {
            $message = Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $holdCouponData = array(
            'couponhold_coupon_id' => $couponInfo['coupon_id'],
            'couponhold_user_id' => UserAuthentication::getLoggedUserId(),
            /* 'couponhold_usercart_id'=>$cartObj->cart_id, */
            'couponhold_added_on' => date('Y-m-d H:i:s'),
        );

        if (!FatApp::getDb()->insertFromArray(DiscountCoupons::DB_TBL_COUPON_HOLD, $holdCouponData, true, array(), $holdCouponData)) {
            $message = Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $cartObj->removeUsedRewardPoints();
        if (true === MOBILE_APP_API_CALL) {
            $fulfilmentType = FatApp::getPostedData('fulfilmentType', FatUtility::VAR_INT, Shipping::FULFILMENT_SHIP);
            $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id'], Cart::PAGE_TYPE_CART);
            $cartObj->setFulfilmentType($fulfilmentType);
            $cartObj->setCartCheckoutType($fulfilmentType);
            $productsArr = $cartObj->getProducts($this->siteLangId);
            $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
            $this->set('products', $productsArr);
            $this->set('cartSummary', $cartSummary);
            $this->_template->render();
        }
        $this->set('msg', Labels::getLabel("MSG_cart_discount_coupon_applied", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removePromoCode()
    {
        $cartObj = new Cart();
        if (!$cartObj->removeCartDiscountCoupon()) {
            $message = Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $cartObj->removeUsedRewardPoints();
        if (true === MOBILE_APP_API_CALL) {
            $fulfilmentType = FatApp::getPostedData('fulfilmentType', FatUtility::VAR_INT, Shipping::FULFILMENT_SHIP);
            $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id'], Cart::PAGE_TYPE_CART);
            $cartObj->setFulfilmentType($fulfilmentType);
            $cartObj->setCartCheckoutType($fulfilmentType);
            $productsArr = $cartObj->getProducts($this->siteLangId);
            $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
            $this->set('products', $productsArr);
            $this->set('cartSummary', $cartSummary);
            $this->_template->render();
        }
        $this->set('msg', Labels::getLabel("MSG_cart_discount_coupon_removed", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getPromoCouponsForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmPromoCoupons');
        $frm->addTextBox(Labels::getLabel('LBL_Coupon_code', $langId), 'coupon_code', '', array('placeholder' => Labels::getLabel('LBL_Enter_Your_code', $langId)));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Apply', $langId));
        return $frm;
    }

    public function getCartSummary()
    {
        $cartObj = new Cart();
        if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0)) {
            $cartObj->excludeTax();
        }
        $cartObj->invalidateCheckoutType();
        $productsArr = $cartObj->getProducts($this->siteLangId);
        $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('products', $productsArr);
        $this->set('cartSummary', $cartSummary);
        $this->set('totalCartItems', $cartObj->countProducts());
        $this->_template->render(false, false);
    }

    public function removePickupOnlyProducts()
    {
        $cart = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id']);
        if (!$cart->removePickupOnlyProducts()) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($cart->getError());
            }
            Message::addMessage($cart->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel("MSG_Pickup_only_Items_removed_from_cart", $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $total = $cart->countProducts();
            $this->set('data', array('cartItemsCount' => $total));
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeShippedOnlyProducts()
    {
        $cart = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id']);
        if (!$cart->removeShippedOnlyProducts()) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($cart->getError());
            }
            Message::addMessage($cart->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel("MSG_Shipped_only_Items_removed_from_cart", $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $total = $cart->countProducts();
            $this->set('data', array('cartItemsCount' => $total));
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setCartCheckoutType()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId(true);
        $cart = new Cart($loggedUserId, $this->siteLangId, $this->app_user['temp_user_id']);
        if (!$cart->hasPhysicalProduct()) {
            $type = Shipping::FULFILMENT_SHIP;
        } else {
            $type = FatApp::getPostedData('type', FatUtility::VAR_INT, 0);
        }
        $cart = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id']);
        $cart->setCartCheckoutType($type);
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getCartFinancialSummary($fulfilmentType = 0)
    {
        $fulfilmentType = FatUtility::int($fulfilmentType);
        $cart = new Cart();
        if (0 < $fulfilmentType) {
            $cart->setFulfilmentType($fulfilmentType);
            $cart->setCartCheckoutType($fulfilmentType);
        }
        if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0)) {
            $cart->excludeTax();
        }
        $cartSummary = $cart->getCartFinancialSummary($this->siteLangId);
        $this->set('cartSummary', $cartSummary);
        $this->_template->render(false, false, 'cart/_partial/cartSummary.php');
    }

    public function clear(int $type = CART::TYPE_PRODUCT)
    {
        $loggedUserId = UserAuthentication::getLoggedUserId(true);
        if (1 > $loggedUserId) {
            $loggedUserId = session_id();
        }
        $cartObj = new Cart($loggedUserId, $this->siteLangId, $this->app_user['temp_user_id']);    
        $cartObj->clear(true);
        $cartObj->updateUserCart();        
        //FatApp::getDb()->deleteRecords('tbl_user_cart', array('smt' => '`usercart_user_id`=? and usercart_type=?', 'vals' => array($loggedUserId, $type)));
        FatUtility::dieJsonSuccess(Labels::getLabel('LBL_SUCCESS', $this->siteLangId));
    }
}
