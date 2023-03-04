<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<?php 
$frm->setFormTagAttribute('class', 'web_form form_vertical');
$frm->setFormTagAttribute('onsubmit', 'setupProfileProduct(this); return(false);');
$frm->developerTags['fld_default_col'] = 6;
$proFld = $frm->getField("product_name");
$proFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_Product...', $adminLangId));
?>
<div class="portlet__head">
    <div class="portlet__head-label">
        <h3 class="portlet__head-title"><?php echo Labels::getLabel('LBL_Products', $adminLangId); ?></h3>
    </div>
    <div class="portlet__head-toolbar">
        <div class="portlet__head-actions"></div>
    </div>
</div>
<div class="portlet__body" >
    <div class="row">
        <div class="col-md-12">
            <h5><?php echo Labels::getLabel("LBL_Product_will_automatically_remove_from_other_profile", $adminLangId) ;?></h5>
			<?php echo $frm->getFormHtml(); ?></form>
        </div>
    </div>
    <div id="product-listing--js"></div>
</div>