<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
	<div class="content-wrapper content-space">
		<div class="content-header row">
			<div class="col">
				<?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
				<h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Batch_Products',$siteLangId); ?></h2>
			</div>
		</div>
		<div class="content-body">
			<div class="card">
				<div class="card-header">
					<h5 class="card-title"><?php echo Labels::getLabel('LBL_Batch_Listing',$siteLangId); ?></h5>
					<div class="action">	<a href="javascript:void(0)" class="btn btn-brand btn-sm" title="<?php echo Labels::getLabel('LBL_Add/Create_New_Batch', $siteLangId); ?>" onclick="batchForm(0)"><?php echo Labels::getLabel('LBL_Add/Create_New_Batch', $siteLangId); ?> </a>
					</div>
				</div>
				<div class="card-body ">
					<div class="replaced">
						<div class="search search--sort">
							<div class="search__field">
								<?php
								$frmSearch->setFormTagAttribute ( 'id', 'frmSearch' );
								$frmSearch->setFormTagAttribute( 'onsubmit', 'searchBatches(this); return(false);' );
								//$frmSearch->setFormTagAttribute( 'placeholder', 'dsdsd' );
								$fldKeyword = $frmSearch->getField('keyword');
								$fldKeyword->setFieldTagAttribute( 'placeholder', Labels::getLabel('LBL_Search', $siteLangId) );
								echo $frmSearch->getFormTag();
								echo $frmSearch->getFieldHtml('keyword');
								echo $frmSearch->getFieldHtml('page');
								echo $frmSearch->getFieldHtml('btn_submit'); ?>
								<i class="fa fa-search"></i>
								</form>
							</div>
						</div>
					</div>
					<span class="gap"></span>
					<?php //echo $frmSearch->getExternalJS();	?>
					<div id="listing">
						<?php echo Labels::getLabel('LBL_Loading..',$siteLangId); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
