<section class="section">
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0)"
                                onclick="collectionForm(<?php echo $collection_type ?>, <?php echo $collection_layout_type ?>, <?php echo $collection_id ?>, 0);">
                                <?php echo Labels::getLabel('LBL_General', $adminLangId);?></a>
                        </li>
						<li><a class="active"
                                href="javascript:void(0)"
                                <?php if($collection_id > 0){?> onclick="banners(<?php echo $collection_id ?>);" <?php } ?>>
                                <?php echo Labels::getLabel('LBL_Banners', $adminLangId);?></a>
                        </li>
                    </ul>
                    <div class="tabs_panel_wrap" id="banners_list-js"></div>
                </div>
            </div>
        </div>
    </div>
</section>