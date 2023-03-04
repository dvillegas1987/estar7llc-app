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
                                    <li class="is-active"><a href="javascript:void(0)" onClick="socialPlatformForm(<?php echo $splatform_id;?>);"><?php echo Labels::getLabel('LBL_General', $siteLangId); ?></a></li>
                                    <li class="<?php echo (0 == $splatform_id) ? 'fat-inactive' : ''; ?>">
                                        <a href="javascript:void(0);" <?php echo (0 < $splatform_id) ? "onclick='addLangForm(" . $splatform_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                            <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row form__subcontent">
                            <div class="col-lg-12 col-md-12">
                                <?php
                                $frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
                                $frm->setFormTagAttribute('class', 'form form--horizontal');
                                $frm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
                                $frm->developerTags['fld_default_col'] = 4;
                                $urlFld = $frm->getField('splatform_url');
                                //$urlFld->htmlAfterField = '<span class="form-text text-muted">'.Labels::getLabel('LBL_Example_Url', $siteLangId).'</span>';

                                $submitFld = $frm->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', "btn btn-brand btn-wide");
                                echo $frm->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
