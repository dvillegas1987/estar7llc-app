<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
    <?php $arr_flds = array(
        'sr' => Labels::getLabel('LBL_#', $siteLangId),
        'name' => Labels::getLabel('LBL_Product', $siteLangId),
        'selprod_sku' => Labels::getLabel('LBL_SKU', $siteLangId),
        'selprod_stock' => Labels::getLabel('LBL_Stock_Quantity', $siteLangId)
    );

    $tbl = new HtmlElement('table', array('class' => 'table'));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $val) {
        $e = $th->appendElement('th', array(), $val);
    }

    $sr_no = ($page == 1) ? 0 : ($pageSize * ($page - 1));
    foreach ($arrListing as $sn => $listing) {
        $sr_no++;
        $tr = $tbl->appendElement('tr', array('class' => ''));

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'sr':
                    $td->appendElement('plaintext', array(), $sr_no, true);
                    break;
                case 'name':
                    $txt = '<div class="item__description">';
                    $txt .= '<div class="item__title">' . $listing['product_name'] . '</div>';
                    if ($listing['selprod_title'] != '') {
                        $txt .= '<div class="item__sub_title"><strong>' . Labels::getLabel('LBL_Custom_Title', $siteLangId) . ": </strong>" . $listing['selprod_title'] . '</div>';
                    }
                    $txt .= '<div class="item__specification">' . Labels::getLabel('LBL_Product_SKU', $siteLangId) . ": </strong>" . $listing['selprod_sku'] . '</div>';
                    if ($listing['brand_name'] != '') {
                        $txt .= '<div class="item__brand">' . Labels::getLabel('LBL_Brand', $siteLangId) . ": </strong>" . $listing['brand_name'] . '</div>';
                    }
                    $variantStr = '';
                    if (is_array($listing['options']) && count($listing['options'])) {
                        $txt .= '<div class="item__specification">';
                        $count = count($listing['options']);
                        foreach ($listing['options'] as $op) {
                            $txt .= '' . wordwrap($op['optionvalue_name'], 150, "<br>\n");
                            if ($count != 1) {
                                $variantStr .= ' | ';
                            }
                            $count--;
                        }
                        $txt .= '</div>';
                    }
                    $txt .= '</div>';

                    $td->appendElement('plaintext', array(), $txt, true);
                    break;

                case 'selprod_stock':
                    $td->appendElement('plaintext', array(), $listing['selprod_stock'], true);
                    break;

                default:
                    $td->appendElement('plaintext', array(), $listing[$key], true);
                    break;
            }
        }
    }
    echo $tbl->getHtml();
    if (count($arrListing) == 0) {
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
    } ?>
</div>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductInventorySrchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToProductsInventorySearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
