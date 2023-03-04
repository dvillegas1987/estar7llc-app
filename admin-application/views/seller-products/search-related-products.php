<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    // 'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
    'product_name' => Labels::getLabel('LBL_Product_Name', $adminLangId),
    'related_products' => Labels::getLabel('LBL_Related_Products', $adminLangId)
);

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--hovered'));
$thead = $tbl->appendElement('thead');
$th = $thead->appendElement('tr', array('class' => ''));

foreach ($arr_flds as $key => $val) {
    if ('product_name' == $key) {
        $th->appendElement('th', array('width' => '25%'), $val);
    } else {
        $th->appendElement('th', array('width' => '75%'), $val);
    }
}

foreach ($arrListing as $selProdId => $relatedProds) {
    $tr = $tbl->appendElement('tr', array());
    foreach ($arr_flds as $key => $val) {
        $tr->setAttribute('id', 'row-' . $selProdId);
        if ($key == 'product_name') {
            $td = $tr->appendElement('td', array('class' => 'js-product-edit pointer', 'row-id' => $selProdId, 'title' => Labels::getLabel('LBL_Click_Here_For_Edit', $adminLangId)));
        } else {
            $td = $tr->appendElement('td');
        }
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[' . $selProdId . ']" value=' . $selProdId . '><i class="input-helper"></i></label>', true);
                break;
            case 'product_name':
                // last Param of getProductDisplayTitle function used to get title in html form.
                $productName = "<span class='js-prod-name'>" . SellerProduct::getProductDisplayTitle($selProdId, $adminLangId, true) . '</span><br>' . Labels::getLabel('LBL_Seller', $adminLangId) . ': <span class="js-seller-name">' . $relatedProds['credential_username'] . '</span>';
                $td->appendElement('plaintext', array(), $productName, true);
                break;
            case 'related_products':
                $ul = $td->appendElement("ul", array("class" => "list-tags"));
                $userName = $relatedProds['credential_username'];
                unset($relatedProds['credential_username']);
                foreach ($relatedProds as $relatedProd) {
                    $options = SellerProduct::getSellerProductOptions($relatedProd['selprod_id'], true, $adminLangId);
                    $variantsStr = '';
                    array_walk($options, function ($item, $key) use (&$variantsStr) {
                        $variantsStr .= ' | ' . $item['option_name'] . ' : ' . $item['optionvalue_name'];
                    });
                    $productName = strip_tags(html_entity_decode(($relatedProd['selprod_title'] != '') ? $relatedProd['selprod_title'] :  $relatedProd['product_name'], ENT_QUOTES, 'UTF-8'));
                    $productName .=  $variantsStr . " | " . $userName;

                    $li = $ul->appendElement("li");
                    $li->appendElement('plaintext', array(), '<span>' . $productName . ' <i class="remove_buyTogether remove_param fas fa-times" onClick="deleteSelprodRelatedProduct(' . $selProdId . ', ' . $relatedProd['selprod_id'] . ')"></i></span>', true);
                    $li->appendElement('plaintext', array(), '<input type="hidden" name="product_related[]" value="' . $relatedProd['selprod_id'] . '">', true);
                }
                break;
            default:
                break;
        }
    }
}
if (count($arrListing) == 0) {
    $tbl->appendElement('tr', array('class' => 'noResult--js'))->appendElement(
        'td',
        array('colspan' => count($arr_flds)),
        Labels::getLabel('LBL_No_Record_Found', $adminLangId)
    );
}

$frm = new Form('frmRelatedProductsListing', array('id' => 'frmRelatedProductsListing'));
$frm->setFormTagAttribute('class', 'form');

echo $frm->getFormTag();
echo $tbl->getHtml(); ?>
</form>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchRelatedProductsPaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
