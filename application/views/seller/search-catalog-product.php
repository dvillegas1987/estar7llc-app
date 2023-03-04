<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
    <?php 
    $arr_flds = array(
        'listserial' => Labels::getLabel('LBL_#', $siteLangId),
        'product_identifier' => Labels::getLabel('LBL_Product', $siteLangId),
        //'attrgrp_name' => Labels::getLabel('LBL_Attribute_Group', $siteLangId),
        'product_model' => Labels::getLabel('LBL_Model', $siteLangId),
        'product_active' => Labels::getLabel('LBL_Status', $siteLangId),
        'product_approved' => Labels::getLabel('LBL_Admin_Approval', $siteLangId)
    );
    $isCustom = $postedData['type'] ?? 0;
    if ($canEdit && $canEditShipProfile && 1 > $isCustom) {
        $arr_flds['product_shipped_by'] = Labels::getLabel('LBL_Shipped_by_me', $siteLangId);
    }
    $tableClass = '';
    if (0 < count($arr_listing)) {
        $tableClass = "table-justified";
    }
    $arr_flds['action'] = '';
    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table ' . $tableClass));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $val) {
        $e = $th->appendElement('th', array(), $val);
    }

    $sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;

    foreach ($arr_listing as $sn => $row) {
        $tr = $tbl->appendElement('tr', array('class' => ''));

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'listserial':
                    $td->appendElement('plaintext', array(), $sr_no, true);
                    break;
                case 'product_identifier':
                    $html = '<div class="item"><figure class="item__pic"><img src="' . UrlHelper::getCachedUrl(UrlHelper::generateUrl('image', 'product', array($row['product_id'], "SMALL", 0, 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg') . '" title="' . $row['product_name'] . '" alt="' . $row['product_name'] . '"></figure>
					<div class="item__description">
						<div class="item__title">' . $row['product_name'] . '</div>
						<div class="item__brand"> (' . $row[$key] . ') </div>
					</div></div>';
                    $td->appendElement('plaintext', array(), $html, true);
                    break;
                case 'attrgrp_name':
                    $td->appendElement('plaintext', array(), CommonHelper::displayNotApplicable($siteLangId, $row[$key]), true);
                    break;
                case 'product_approved':
                    $td->appendElement('span', array('class' => 'label label-inline ' . $approveUnApproveClassArr[$row[$key]]), $approveUnApproveArr[$row[$key]] . '<br>', true);
                    break;
                case 'product_active':
                    $td->appendElement('span', array('class' => 'label label-inline ' . $activeInactiveClassArr[$row[$key]]), $activeInactiveArr[$row[$key]] . '<br>', true);
                    break;
                case 'product_shipped_by':
                    $active = "";
                    if ($row['psbs_user_id']) {
                        $active = 'checked';
                    }

                    $str = Labels::getLabel('LBL_N/A', $siteLangId);
                    if (!$row['product_seller_id'] && $row['product_type'] != Product::PRODUCT_TYPE_DIGITAL) {
                        $statucAct = (!$row['psbs_user_id']) ? 'setShippedBySeller(' . $row['product_id'] . ')' : 'setShippedByAdmin(' . $row['product_id'] . ')';

                        $str = '<label class="toggle-switch" for="switch' . $row['product_id'] . '"><input ' . $active . ' type="checkbox" id="switch' . $row['product_id'] . '" onclick="' . $statucAct . '"/><div class="slider round"></div></label>';
                    }
                    $td->appendElement('plaintext', array(), $str, true);
                    break;
                case 'action':
                    $canAddToStore = true;
                    if ($row['product_approved'] == applicationConstants::NO) {
                        $canAddToStore = false;
                    }
                    $available = Product::availableForAddToStore($row['product_id'], $userParentId);
                    $ul = $td->appendElement("ul", array('class' => 'actions'), '', true);
                    if ($canEdit) {
                        if ($available) {
                            $li = $ul->appendElement("li");
                            $li->appendElement(
                                'a',
                                array('href' => 'javascript:void(0)', 'class' => ($canAddToStore) ? 'icn-highlighted' : 'icn-highlighted disabled', 'onClick' => 'checkIfAvailableForInventory(' . $row['product_id'] . ')', 'title' => Labels::getLabel('LBL_Add_To_Store', $siteLangId), true),
                                '<i class="fa fa-plus-square"></i>',
                                true
                            );
                        }

                        if (0 != $row['product_seller_id']) {
                            $li = $ul->appendElement("li");
                            $li->appendElement('a', array('class' => '', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), "href" => UrlHelper::generateUrl('seller', 'customProductForm', array($row['product_id']))), '<i class="fa fa-edit"></i>', true);

                            $li = $ul->appendElement("li");
                            $li->appendElement("a", array('title' => Labels::getLabel('LBL_Product_Images', $siteLangId), 'onclick' => 'customProductImages(' . $row['product_id'] . ')', 'href' => 'javascript:void(0)'), '<i class="fas fa-images"></i>', true);
                        }

                        if ($canEditShipProfile && $row['product_added_by_admin_id'] && $row['psbs_user_id'] && $row['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
                            $li = $ul->appendElement("li");
                            $li->appendElement("a", array('title' => Labels::getLabel('LBL_Edit_Shipping', $siteLangId), 'onclick' => 'sellerShippingForm(' . $row['product_id'] . ')', 'href' => 'javascript:void(0)'), '<i class="fa fa-truck"></i>', true);
                        }

                        $hasInventory = Product::hasInventory($row['product_id'], UserAuthentication::getLoggedUserId());
                        if ($hasInventory) {
                            $li = $ul->appendElement("li");
                            $li->appendElement(
                                'a',
                                array('href' => 'javascript:void(0)', 'onclick' => 'sellerProducts(' . $row['product_id'] . ')', 'class' => '', 'title' => Labels::getLabel('LBL_View_Inventories', $siteLangId), true),
                                '<i class="fas fa-clipboard-list"></i>',
                                true
                            );
                        }
                    }

                    $li = $ul->appendElement("li");
                    $li->appendElement(
                        'a',
                        array('href' => 'javascript:void(0)', 'onclick' => 'catalogInfo(' . $row['product_id'] . ')', 'class' => '', 'title' => Labels::getLabel('LBL_product_Info', $siteLangId), true),
                        '<i class="fa fa-eye"></i>',
                        true
                    );

                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }

        $sr_no--;
    }
    echo $tbl->getHtml();
    if (count($arr_listing) == 0) {
        $message = Labels::getLabel('LBL_Searched_product_is_not_found_in_catalog', $siteLangId);
        $linkArr = array();
        if (User::canAddCustomProductAvailableToAllSellers()) {
            $linkArr = array(
                0 => array(
                    'href' => UrlHelper::generateUrl('Seller', 'CustomCatalogProductForm'),
                    'label' => Labels::getLabel('LBL_Request_New_Product', $siteLangId),
                )
            );
        }
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'linkArr' => $linkArr, 'message' => $message));
    }

    if (!isset($postedData['type']) || '' == $postedData['type']) {
        $postedData['type'] = -1;
    } ?>
</div>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmCatalogProductSearchPaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'callBackJsFunc' => 'goToCatalogProductSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
