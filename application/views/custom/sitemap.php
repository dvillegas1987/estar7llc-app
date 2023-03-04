<div id="body" class="body">
	<div class="bg-second pt-3 pb-3">
		<div class="container">
			<div class="row align-items-center justify-content-between">
				<div class="col-md-8">
					<div class="section-head section--white--head mb-0">
						<div class="section__heading">
							<h2 class="mb-0"><?php echo Labels::getLabel('LBL_SITEMAP',$siteLangId);?></h2>
							<div class="breadcrumbs breadcrumbs--white">
								<?php $this->includeTemplate('_partial/custom/header-breadcrumb.php'); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-4"></div>
			</div>
		</div>
	</div>
	<section class="section">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 ">					
						<div class="cg-main">
							<?php  if(!empty($contentPages)){ ?>
							<h6 class="big-title">
								<?php echo Labels::getLabel('LBL_CONTENT_PAGES',$siteLangId);?>
							</h6>
							 
								<div class="item">
									<ul>
										<?php
                        foreach($contentPages as $contentId=> $contentPageName){
                        ?> <li>
											<a href="<?php echo UrlHelper::generateUrl('cms','view',array($contentId));?>">
												<?php echo $contentPageName;?>
											</a>
										</li>
										<?php }?>
									</ul>

								</div>
							 
							<?php
			}
			if($categoriesArr){
			?>

							<h6 class="big-title">
								<?php echo Labels::getLabel('LBL_Categories', $siteLangId);?>
							</h6>
						 
								<div class="item">
									<?php $this->includeTemplate('_partial/custom/categories-list.php',array('categoriesArr'=>$categoriesArr),false);?>

								</div>
							 

							<?php

			}
			if(!empty($allShops)){ ?>

							<h6 class="big-title">
								<?php echo Labels::getLabel('LBL_Shops',$siteLangId);?>
							</h6>
							 
								<div class="item ">
									<ul>
										<?php foreach($allShops as $shop){
					?>
										<li>
											<a href="<?php echo UrlHelper::generateUrl('Shops','view',array($shop['shop_id']));?>">
												<?php echo $shop['shop_name'];?>
											</a>
										</li>
										<?php }?>
									</ul>
								</div>

							 
							<?php
				}
				
			if(!empty($allBrands)){ ?>

							<h6 class="big-title">
								<?php echo Labels::getLabel('LBL_Brands',$siteLangId);?>
							</h6>
							 
								<div class="item ">
									<ul>
										<?php foreach($allBrands as $brands){
					?>
										<li>
											<a href="<?php echo UrlHelper::generateUrl('Brands','view',array($brands['brand_id']));?>">
												<?php echo $brands['brand_name'];?>
											</a>
										</li>
										<?php }?>
									</ul>
								</div>

							 
							<?php
				}
				?>
						</div>
					
				</div>
			</div>
		</div>


	</section>

</div>