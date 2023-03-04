<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="tabs ">
    <?php require_once('sellerCatalogProductTop.php');?>
</div>
<div class="card">
<div class="card-body ">	
	<div class="tabs__content form">
		<div class="row">
			<div class="col-md-12">
				<div class="">
					<div class="tabs tabs-sm tabs--scroll clearfix">
						<ul>
							<li><a href="javascript:void(0)" onClick="sellerProductForm(<?php echo $product_id,',',$selprod_id ?>)" ><?php echo Labels::getLabel('LBL_Basic',$siteLangId); ?></a></li>
                            <li class="<?php echo (0 == $selprod_id) ? 'fat-inactive' : ''; ?>">
                                <a href="javascript:void(0);" <?php echo (0 < $selprod_id) ? "onclick='sellerProductLangForm(" . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ", " . $selprod_id . ");'" : ""; ?>>
                                    <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                </a>
                            </li>
							<?php $inactive = ($selprod_id==0)?'fat-inactive':''; ?>
							<li class="<?php echo $inactive; echo ($ppoint_type == PolicyPoint::PPOINT_TYPE_WARRANTY)?'is-active':''; ?>"><a href="javascript:void(0)" <?php if($selprod_id>0){?>  onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_WARRANTY ; ?>)" <?php }?>><?php echo Labels::getLabel('LBL_Link_Warranty_Policies',$siteLangId); ?></a></li>
							<li class="<?php echo $inactive; echo ($ppoint_type == PolicyPoint::PPOINT_TYPE_RETURN)?'is-active':''; ?>"><a href="javascript:void(0)" <?php if($selprod_id>0){?>  onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_RETURN ; ?>)" <?php }?>><?php echo Labels::getLabel('LBL_Link_Return_Policies',$siteLangId); ?></a></li>
						</ul>
					</div>
				</div>
				<div class="form__subcontent">
					<?php echo $frm->getFormHtml(); ?>
					<div id="listPolicies" class="col-md-12">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
