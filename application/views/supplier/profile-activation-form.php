<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <div class="heading3"><?php echo Labels::getLabel('LBL_Seller_Registration', $siteLangId);?></div>
<div class="registeration-process">
    <ul>
        <li><a href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Details', $siteLangId);?></a></li>
        <li class="is--active"><a href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Activation', $siteLangId);?></a></li>
        <li><a href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Confirmation', $siteLangId);?></a></li>
    </ul>
</div> <?php
    $approvalFrm->setFormTagAttribute('onsubmit', 'setupSupplierApproval(this); return(false);');
    $approvalFrm->setFormTagAttribute('class', 'form form--normal');
    $approvalFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
    $approvalFrm->developerTags['fld_default_col'] = 12;
    
    $btn = $approvalFrm->getField('btn_submit');
    $btn->setFieldTagAttribute('class', 'btn btn-brand btn-wide');
    echo $approvalFrm->getFormHtml();

if (isset($_SESSION['registered_supplier']['id'])) { ?>
<div class="row">
	<div class="col-auto">
		<a class="link" href="<?php echo UrlHelper::generateUrl('Supplier', 'registerNewAccount'); ?>">
			<?php echo Labels::getLabel('LBL_Register_With_New_Account?', $siteLangId);?>
		</a>
	</div>
</div>
<?php } ?>