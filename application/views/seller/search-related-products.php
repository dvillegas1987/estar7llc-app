<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
    <?php 
    $arr_flds = array(
        // 'select_all'=>'',
        'product_name' => Labels::getLabel('LBL_Product_Name', $siteLangId),
        'related_products' => Labels::getLabel('LBL_Related_Products', $siteLangId)
    );

    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table volDiscountList-js'));
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
                $title = ($canEdit) ? Labels::getLabel('LBL_Click_Here_For_Edit', $siteLangId) : '';
                $td = $tr->appendElement('td', array('class' => (($canEdit) ? 'js-product-edit cursor-pointer' : ''), 'row-id' => $selProdId, 'title' => $title));
            } else {
                $td = $tr->appendElement('td');
            }
            switch ($key) {
                case 'select_all':
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[' . $selProdId . ']" value=' . $selProdId . '><i class="input-helper"></i></label>', true);
                    break;
                case 'product_name':
                    // last Param of getProductDisplayTitle function used to get title in html form.
                    $productName = "<span class='js-prod-name'>" . SellerProduct::getProductDisplayTitle($selProdId, $siteLangId, true) . "</span>";
                    $td->appendElement('plaintext', array(), $productName, true);
                    break;
                case 'related_products':
                    $div = $td->appendElement('div', array("class" => "list-tag-wrapper", "data-scroll-height" => "150", "data-simplebar" => ""));
                    $ul = $div->appendElement("ul", array("class" => "list-tags"));
                    foreach ($relatedProds as $relatedProd) {
                        $options = SellerProduct::getSellerProductOptions($relatedProd['selprod_id'], true, $siteLangId);
                        $variantsStr = '';
                        array_walk($options, function ($item, $key) use (&$variantsStr) {
                            $variantsStr .= ' | ' . $item['option_name'] . ' : ' . $item['optionvalue_name'];
                        });
                        $productName = strip_tags(html_entity_decode(($relatedProd['selprod_title'] != '') ? $relatedProd['selprod_title'] :  $relatedProd['product_name'], ENT_QUOTES, 'UTF-8'));
                        $productName .=  $variantsStr;

                        $li = $ul->appendElement("li");
                        $removeIcon = '';
                        if ($canEdit) {
                            $removeIcon = '<i class="remove_buyTogether remove_param fa fa-times" onClick="deleteSelprodRelatedProduct(' . $selProdId . ', ' . $relatedProd['selprod_id'] . ')"></i>';
                        }
                        $li->appendElement('plaintext', array(), '<span>' . $productName . ' ' . $removeIcon . '</span>', true);
                        $li->appendElement('plaintext', array(), '<input type="hidden" name="product_related[]" value="' . $relatedProd['selprod_id'] . '">', true);
                    }
                    break;
                default:
                    break;
            }
        }
    }

    echo $tbl->getHtml();
    if (count($arrListing) == 0) {
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
    }

    $frm = new Form('frmVolDiscountListing', array('id' => 'frmVolDiscountListing'));
    $frm->setFormTagAttribute('class', 'form');

    echo $frm->getFormTag(); ?>
    </form>
</div>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchVolumeDiscountPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'adminLangId' => $siteLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
