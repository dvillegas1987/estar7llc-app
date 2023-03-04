<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $frmSrch->setFormTagAttribute('onSubmit', 'searchSalesReport(this); return false;');
    $frmSrch->setFormTagAttribute('class', 'form');
    $frmSrch->developerTags['colClassPrefix'] = 'col-lg-2 col-md-';
    $frmSrch->developerTags['fld_default_col'] = 2;
?>

<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
		<div class="content-header row justify-content-between mb-3">
            <div class="col-md-auto">
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId);?></h2>                
            </div>    
			<div class="col-auto">
				<div class="btn-group">
					<?php echo '<a href="javascript:void(0)" onClick="exportSalesReport()" class="btn btn-outline-brand btn-sm">'.Labels::getLabel('LBL_Export', $siteLangId).'</a>';
					if(!empty($orderDate)){
						echo '<a href="'.UrlHelper::generateUrl('Reports', 'SalesReport').'" class="btn btn-outline-brand btn-sm">'.Labels::getLabel('LBL_Back',$siteLangId).'</a>';
					} ?>
				</div>
			</div>
        </div>
        <div class="content-body">
		<?php if (empty($orderDate)) { ?>
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
							<div class="replaced">
								<?php
									$dateFrm = $frmSrch->getField('date_from');
									$dateFrm->developerTags['noCaptionTag'] = true;
								
									$dateTo = $frmSrch->getField('date_to');
									$dateTo->developerTags['noCaptionTag'] = true;

                                        $submitFld = $frmSrch->getField('btn_submit');
                                        $submitFld->developerTags['noCaptionTag'] = true;
                                        $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');

                                        $fldClear = $frmSrch->getField('btn_clear');
                                        $fldClear->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
                                        $fldClear->developerTags['noCaptionTag'] = true;
                                        echo $frmSrch->getFormHtml();
                                    ?>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php  } else {
				echo  $frmSrch->getFormHtml();
			} ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="listing-tbl" id="listingDiv"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
