<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$arr_flds = array(
    'select_all'=>Labels::getLabel('LBL_Select_all', $adminLangId),
    'product_name' => Labels::getLabel('LBL_Name', $adminLangId),
    'selprod_price' => Labels::getLabel('LBL_Original_Price', $adminLangId),
    'credential_username' => Labels::getLabel('LBL_Seller', $adminLangId),
    'splprice_start_date' => Labels::getLabel('LBL_Start_Date', $adminLangId),
    'splprice_end_date' => Labels::getLabel('LBL_End_Date', $adminLangId),
    'splprice_price' => Labels::getLabel('LBL_Special_Price', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['select_all'], $arr_flds['action']);
}
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive table--hovered splPriceList-js'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => 'hide--mobile'));
foreach ($arr_flds as $column => $lblTitle) {
    if ('select_all' == $column) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$lblTitle.'" type="checkbox" onclick="selectAll($(this))" class="selectAll-js"><i class="input-helper"></i></label>', true);
    } else {
        $th->appendElement('th', array(), $lblTitle);
    }
}

foreach ($arrListing as $sn => $row) {
    $tr = $tbl->appendElement('tr', array());
    $splPriceId = $row['splprice_id'];
    $selProdId = $row['selprod_id'];
    $editListingFrm = new Form('editListingFrm-'.$splPriceId, array('id'=>'editListingFrm-'.$splPriceId));
    foreach ($arr_flds as $column => $lblTitle) {
        $tr->setAttribute('id', 'row-'.$splPriceId);
        $td = $tr->appendElement('td');
        switch ($column) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids['.$splPriceId.']" value='.$selProdId.'><i class="input-helper"></i></label>', true);
                break;
            case 'product_name':
                // last Param of getProductDisplayTitle function used to get title in html form.
                $productName = SellerProduct::getProductDisplayTitle($selProdId, $adminLangId, true);
                $td->appendElement('plaintext', array(), $productName, true);
                break;
             case 'selprod_price':
                $price = CommonHelper::displayMoneyFormat($row[$column], true, true);
                $td->appendElement('plaintext', array(), $price, true);
                break;
            case 'credential_username':
                $td->appendElement('plaintext', array(), $row[$column], true);
                break;
            case 'splprice_start_date':
            case 'splprice_end_date':
                $date = date('Y-m-d', strtotime($row[$column]));
                $attr = array(
                    'readonly' => 'readonly',
                    'placeholder' => $lblTitle,
                    'data-selprodid' => $selProdId,
                    'data-id' => $splPriceId,
                    'data-oldval' => $date,
                    'data-price' => $row['selprod_price'],
                    'id' => $column.'-'.$splPriceId,
                    'class' => 'date_js js--splPriceCol hide sp-input',
                );
                $editListingFrm->addDateField($lblTitle, $column, $date, $attr);

                $td->appendElement('div', array("class" => 'js--editCol edit-hover', "title" => Labels::getLabel('LBL_Click_To_Edit', $adminLangId)), $date, true);
                $td->appendElement('plaintext', array(), $editListingFrm->getFieldHtml($column), true);
                break;
            case 'splprice_price':
                $input = '<input type="text" data-price="'.$row['selprod_price'].'" data-id="'.$splPriceId.'" value="'.$row[$column].'" data-selprodid="'.$selProdId.'" name="'.$column.'" data-oldval="'.$row[$column].'" data-displayoldval="'.CommonHelper::displayMoneyFormat($row[$column], true, true).'" class="js--splPriceCol hide sp-input"/>';
                $td->appendElement('div', array("class" => 'js--editCol edit-hover', "title" => Labels::getLabel('LBL_Click_To_Edit', $adminLangId)), CommonHelper::displayMoneyFormat($row[$column], true, true), true);
                $td->appendElement('plaintext', array(), $input, true);
                
                $discountPrice = $row['selprod_price'] - $row[$column];
                $discountPercentage = round(($discountPrice/$row['selprod_price'])*100, 2);
                $discountPercentage = $discountPercentage."% ".Labels::getLabel('LBL_off', $adminLangId);
                $td->appendElement('div', array("class" => 'ml-3 js--percentVal'), $discountPercentage, true);
                
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement(
                        'a',
                        array('href'=>'javascript:void(0)', 'class'=>'btn btn-clean btn-sm btn-icon',
                        'title'=>Labels::getLabel('LBL_Delete', $adminLangId),"onclick"=>"deleteSellerProductSpecialPrice(".$splPriceId.")"),
                        "<i class='fa fa-trash  icon'></i>",
                        true
                    );
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$column], true);
                break;
        }
    }
}
if (count($arrListing) == 0) {
    $tbl->appendElement('tr', array('class' => 'noResult--js'))->appendElement(
        'td',
        array('colspan'=>count($arr_flds)),
        Labels::getLabel('LBL_No_Record_Found', $adminLangId)
    );
}

$frm = new Form('frmSplPriceListing', array('id'=>'frmSplPriceListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');

echo $frm->getFormTag();
echo $tbl->getHtml(); ?>
</form>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchSpecialPricePaging'));

$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'callBackJsFunc' => 'goToSearchPage','adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
