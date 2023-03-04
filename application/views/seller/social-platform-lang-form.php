<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Social_Platforms', $siteLangId); ?></h5>
        <div class="">
            <a href="javascript:void(0)" onClick="searchSocialPlatforms(this)" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back_to_Social_Platforms', $siteLangId);?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="col-lg-12 col-md-12">
            <div class="tabs__content">
                <div class="row ">
                    <div class="col-md-12">
                        <div class="">
                            <div class="tabs tabs-sm tabs--scroll clearfix">
                                <ul>
                                    <li><a href="javascript:void(0)" onClick="addForm(<?php echo $splatform_id;?>);"><?php echo Labels::getLabel('LBL_General', $siteLangId); ?></a></li>
                                    <li class="is-active">
                                        <a href="javascript:void(0);">
                                            <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row form__subcontent">
                            <div class="col-lg-12 col-md-12">
                                <?php
                                $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                                $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                                if (!empty($translatorSubscriptionKey) && $splatform_lang_id != $siteDefaultLangId) { ?>
                                    <div class="row justify-content-end">
                                        <div class="col-auto mb-4">
                                            <input class="btn btn-brand"
                                                type="button"
                                                value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>"
                                                onClick="addLangForm(<?php echo $splatform_id; ?>, <?php echo $splatform_lang_id; ?>, 1)">
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php
                                $langFrm->setFormTagAttribute('onsubmit', 'setupLang(this); return(false);');
                                $langFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
                                $langFrm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
                                $langFrm->developerTags['fld_default_col'] = 4;
                                $langFld = $langFrm->getField('lang_id');
                                $langFld->setfieldTagAttribute('onChange', "addLangForm(" . $splatform_id . ", this.value);");
                                $submitFld = $langFrm->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', "btn btn-brand btn-wide");
                                echo $langFrm->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
