<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $selProdId = (!empty($data['voldiscount_selprod_id']) ? $data['voldiscount_selprod_id'] : 0);
    $frm = SellerProduct::volumeDiscountForm($siteLangId);

    //$prodName = $frm->addTextBox(Labels::getLabel('LBL_Product', $siteLangId), 'product_name', '', array('class'=>'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $siteLangId)));
    $prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $siteLangId), 'product_name', [], '', array('class' => 'selProd--js','placeholder' => Labels::getLabel('LBL_Select_Product', $siteLangId)));
    $prodName->requirements()->setRequired();

    $minQty = $frm->getField('voldiscount_min_qty');
    $minQty->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Add_Minimum_Quantity', $siteLangId));
    $minQty->setFieldTagAttribute('disabled', 'disabled');
    $minQty->setFieldTagAttribute('class', 'js-voldiscount_min_qty');

    $disPerc = $frm->getField('voldiscount_percentage');
    $disPerc->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Add_Discount_Percentage', $siteLangId));
    $disPerc->setFieldTagAttribute('disabled', 'disabled');

    $frm->setFormTagAttribute('class', 'form');
    $frm->setFormTagAttribute('id', 'frmAddVolumeDiscount-'.$selProdId);
    $frm->setFormTagAttribute('name', 'frmAddVolumeDiscount-'.$selProdId);
    $frm->setFormTagAttribute('onsubmit', 'updateVolumeDiscountRow(this, '.$selProdId.'); return(false);');

    $frm->addHiddenField('', 'addMultiple', 0);

    $frm->addSubmitButton('', 'btn_update', Labels::getLabel('LBL_Save', $siteLangId), array('class'=>'btn btn-brand btn-block '));

if (!empty($data) && 0 < count($data)) {
    $data['product_name'] = isset($data['product_name']) ? html_entity_decode($data['product_name'], ENT_QUOTES, 'UTF-8') : '';
    $prodName->setFieldTagAttribute('readonly', 'readonly');
    $frm->fill($data);
}
?>
<div class="card-body">
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
<div class="divider m-0"></div>
