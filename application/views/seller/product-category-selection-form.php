<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<script type="text/javascript">
var  productId  =  <?php echo $productId ;?>
</script>
<main id="main-area" class="main"   >
	<div class="content-wrapper content-space">
		<div class="content-header row">
			<div class="col-md-auto">
				<?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
				<h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Product_Setup',$siteLangId); ?></h2>
			</div>
		</div>
		<div class="content-body">
			<div class="card">
				<div class="card-body ">
					<div id="listing"></div>
				</div>
			</div>
		</div>
	</div>
</main>