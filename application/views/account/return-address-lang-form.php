<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'returnAddressLangFrm');
$frm->setFormTagAttribute('class','form layout--'.$formLayout);
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'setReturnAddressLang(this); return(false);');
$langFld = $frm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "returnAddressLangForm(this.value);");
?>
<div class="row">	
	<div class="col-md-8">
		 
			<div class="tabs tabs-sm clearfix">
				<ul class="setactive-js">
					<li ><a href="javascript:void(0)" onClick="returnAddressForm()"><?php echo Labels::getLabel('LBL_General',$siteLangId); ?></a></li>
                    <li class="<?php echo (0 < $formLangId) ? 'is-active' : ''; ?>">
                        <a href="javascript:void(0);">
                            <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                        </a>
                    </li>
					<?php /* foreach($languages as $langId => $langName){?>
					<li <?php echo ($formLangId == $langId)?'class="is-active"':'';?>><a href="javascript:void(0);" onclick="returnAddressLangForm(<?php echo $langId;?>);"><?php echo $langName;?></a></li>
				<?php }  */?>								
				</ul>
			</div>
            <?php
            $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
            $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
            if (!empty($translatorSubscriptionKey) && $formLangId != $siteDefaultLangId) { ?> 
                <div class="row justify-content-end"> 
                    <div class="col-auto mb-4">
                        <input class="btn btn-brand" 
                            type="button" 
                            value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>" 
                            onClick="returnAddressLangForm(<?php echo $formLangId; ?>, 1)">
                    </div>
                </div>
            <?php } ?>
		<?php echo $frm->getFormHtml();?>
	</div>
</div>