<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
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
                            <li class="<?php echo (0 < $formLangId) ? 'is-active' : ''; echo $inactive; ?>">
                                <a href="javascript:void(0);">
                                    <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                </a>
                            </li>
							<?php
							/* foreach($language as $langId => $langName){?>
							<li class="<?php echo ($formLangId == $langId)?'is-active':'' ; ?>"><a href="javascript:void(0)" onClick="sellerProductLangForm(<?php echo $langId;?>,<?php echo $selprod_id;?>)">
							<?php echo $langName;?></a></li>
							<?php } */?>
							<li><a href="javascript:void(0)" onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_WARRANTY ; ?>)"><?php echo Labels::getLabel('LBL_Link_Warranty_Policies',$siteLangId); ?></a></li>
							<li><a href="javascript:void(0)" onClick="linkPoliciesForm(<?php echo $product_id,',',$selprod_id,',',PolicyPoint::PPOINT_TYPE_RETURN ; ?>)"><?php echo Labels::getLabel('LBL_Link_Return_Policies',$siteLangId); ?></a></li>
						</ul>
					</div>
				</div>
				<div class="form__subcontent">
                    <?php
                    $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                    $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                    if (!empty($translatorSubscriptionKey) && $formLangId != $siteDefaultLangId) { ?> 
                        <div class="row justify-content-end"> 
                            <div class="col-auto mb-4">
                                <input class="btn btn-brand" 
                                    type="button" 
                                    value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>" 
                                    onClick="sellerProductLangForm(<?php echo $formLangId; ?>, <?php echo $selprod_id; ?>, 1)">
                            </div>
                        </div>
                    <?php } ?>
					<?php
					$frmSellerProdLangFrm->setFormTagAttribute('onsubmit','setUpSellerProductLang(this); return(false);');
					$frmSellerProdLangFrm->setFormTagAttribute('class','form form--horizontal layout--'.$formLayout);
					$frmSellerProdLangFrm->developerTags['colClassPrefix'] = 'col-lg-8 col-';
					$frmSellerProdLangFrm->developerTags['fld_default_col'] = 12;
                    
                    $langFld = $frmSellerProdLangFrm->getField('lang_id');
                    $langFld->setfieldTagAttribute('onChange', "sellerProductLangForm(this.value, " . $selprod_id . ");");

					//$selprod_return_policy_fld = $frmSellerProdLangFrm->getField('selprod_return_policy');

					//$selprod_features_fld = $frmSellerProdLangFrm->getField('selprod_features');

					$newLineTxt = Labels::getLabel('LBL_Enter_Data_Separated_By_New_Line.', $siteLangId );
				//	$returnPolicyTxt = Labels::getLabel('LBL_Product_Return_Policy_text',$siteLangId);
					//$selprod_features_fld->htmlAfterField = '<span class="form-text text-muted">'. $newLineTxt .'</span>';
					//$selprod_return_policy_fld->htmlAfterField  = '<span class="form-text text-muted">'. $newLineTxt .' '. $returnPolicyTxt .'</span>';

					echo $frmSellerProdLangFrm->getFormHtml(); ?>
				</div>
			</div>
		</div>

	</div>
</div>
</div>
