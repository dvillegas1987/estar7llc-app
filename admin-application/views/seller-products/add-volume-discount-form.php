<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$selProdId = (!empty($data['voldiscount_selprod_id']) ? $data['voldiscount_selprod_id'] : 0);
$frm = SellerProduct::volumeDiscountForm($adminLangId);
$prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $adminLangId), 'product_name', [], '', array('class' => 'selProd--js','placeholder' => Labels::getLabel('LBL_Select_Product', $adminLangId)));
//$prodName = $frm->addTextBox(Labels::getLabel('LBL_Product', $adminLangId), 'product_name', '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $adminLangId)));
$prodName->requirements()->setRequired();

$minQty = $frm->getField('voldiscount_min_qty');
$minQty->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Add_Minimum_Quantity', $adminLangId));
if (empty($data)) {
    $minQty->setFieldTagAttribute('disabled', 'disabled');
}

$disPerc = $frm->getField('voldiscount_percentage');
$disPerc->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Add_Discount_Percentage', $adminLangId));
if (empty($data)) {
    $disPerc->setFieldTagAttribute('disabled', 'disabled');
}

$frm->setFormTagAttribute('class', 'web_form');
$frm->setFormTagAttribute('id', 'frmAddVolumeDiscount-' . $selProdId);
$frm->setFormTagAttribute('name', 'frmAddVolumeDiscount-' . $selProdId);
$frm->setFormTagAttribute('onsubmit', 'updateVolumeDiscountRow(this, ' . $selProdId . '); return(false);');

$frm->addHiddenField('', 'addMultiple', 0);

$frm->addSubmitButton('', 'btn_update', Labels::getLabel('LBL_Save_Changes', $adminLangId));

if (!empty($data) && 0 < count($data)) {
    $prodName->setFieldTagAttribute('readonly', 'readonly');
    $frm->fill($data);
}

$selProdStock = isset($data['selprod_stock']) ? $data['selprod_stock'] : 0;
$selProdStockLbl = 0 < $selProdStock ? Labels::getLabel("LBL_CURRENT_STOCK:_{STOCK}", $adminLangId) : '';
$selProdStockLbl = !empty($selProdStockLbl) ? CommonHelper::replaceStringData($selProdStockLbl, ['{STOCK}' => $selProdStock]) : '';
?>
<div class="card-body pt-4 pl-4 pr-4 pb-0">
    <div class="replaced">
        <?php
        echo $frm->getFormTag();
        echo $frm->getFieldHtml('voldiscount_selprod_id');
        echo $frm->getFieldHtml('addMultiple');
        ?>
        <div class="row">
            <div class="col-lg-4 col-md-4">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('product_name'); ?>
                        <div class="js-prod-stock" data-stock="<?php echo $selProdStock; ?>"><?php echo $selProdStockLbl; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('voldiscount_min_qty'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('voldiscount_percentage'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <div class="field-set">
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $frm->getFieldHtml('btn_update'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <?php echo $frm->getExternalJs(); ?>
    </div>
</div>
<div class="divider"></div>