<div id="body" class="body">
	<div class="bg-second pt-3 pb-3">
      <div class="container">
			<div class="section-head section--white--head justify-content-center mb-0">
				<div class="section__heading">
					<h2 class="mb-0"><?php echo Labels::getLabel('LBL_Shop_By_Categories', $siteLangId);?></h2>
				</div>
			</div> 
		</div>
    </div>
	<section class="section">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-lg-12">
					<ul class="list-collections">
						<?php foreach($categoriesArr as $category){ ?>
						<li class="list-collections__item">
							<a href="<?php echo UrlHelper::generateUrl('category','view',array($category['prodcat_id']));?>">
								<div class="aspect-ratio" style="padding-bottom: 45%">
									<div class="list-collections__image" style="background-image: url(<?php echo UrlHelper::generateFullFileUrl('Category', 'banner', array($category['prodcat_id'], $siteLangId)); ?>);">
									</div>
								</div>
								<h6 class="list-collections__heading"><?php echo $category['prodcat_name']; ?></h6>
							</a>
						</li>
						<?php } ?>
						<?php /* $this->includeTemplate('category/categories-list.php',array('categoriesArr'=>$categoriesArr),false); */ ?>
					</ul>
				</div>
			</div>
		</div>
	</section>	
</div>
