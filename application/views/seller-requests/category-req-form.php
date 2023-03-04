<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupCategoryReq(this); return(false);');

if ($auto_update_other_langs_data) {
    $autoUpdateFld = $frm->getField('auto_update_other_langs_data');
    $autoUpdateFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $autoUpdateFld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';
}
$submitFld = $frm->getField('btn_submit');
$submitFld->setFieldTagAttribute('class', 'btn btn-brand');
$submitFld->developerTags['noCaptionTag'] = true;
?>
<div class="box__head">
    <h4><?php echo (FatApp::getConfig('CONF_PRODUCT_CATEGORY_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) ? Labels::getLabel('LBL_Request_New_Category', $siteLangId) : Labels::getLabel('LBL_New_Category', $siteLangId) ?></h4>
</div>
<div class="box__body">
    <div class="row">
        <div class="col-md-12">
            <div class="form__subcontent">
                <?php echo $frm->getFormTag(); ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set">
                            <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('prodcat_name[' . $siteDefaultLangId . ']')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                            <div class="field-wraper">
                                <div class="field_cover"><?php echo $frm->getFieldHtml('prodcat_name[' . $siteDefaultLangId . ']'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set">
                            <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('prodcat_parent')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                            <div class="field-wraper">
                                <div class="field_cover"><?php echo $frm->getFieldHtml('prodcat_parent'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $languages = Language::getAllNames();
                unset($languages[$siteDefaultLangId]);
                $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                if (!empty($translatorSubscriptionKey) && count($languages) > 0) { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set mb-0">
                                <div class="caption-wraper"></div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('auto_update_other_langs_data'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php if (count($languages) > 0) { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <?php foreach ($languages as $langId => $langName) {
                                $layout = Language::getLayoutDirection($langId); ?>
                                <div class="accordion mt-4" id="specification-accordion">
                                    <h6 class="dropdown-toggle" data-toggle="collapse" data-target="#collapseOne<?php echo $langId; ?>" aria-expanded="true" aria-controls="collapseOne<?php echo $langId; ?>"><span onclick="translateData(this, '<?php echo $siteDefaultLangId; ?>', '<?php echo $langId; ?>')">
                                            <?php echo Labels::getLabel('LBL_Category_Name_for', $siteLangId) ?>
                                            <?php echo $langName; ?>
                                        </span>
                                    </h6>
                                    <div id="collapseOne<?php echo $langId; ?>" class="collapse collapse-js-<?php echo $langId; ?>" aria-labelledby="headingOne" data-parent="#specification-accordion">
                                        <div class="p-4 mb-4 bg-gray rounded" dir="<?php echo $layout; ?>">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="field-set">
                                                        <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('prodcat_name[' . $langId . ']')->getCaption(); ?></label>
                                                        </div>
                                                        <div class="field-wraper">
                                                            <div class="field_cover"><?php echo $frm->getFieldHtml('prodcat_name[' . $langId . ']'); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>                    
                <?php } ?>
                <div class="row">
                        <div class="col-6">
                            <div class="field-set">
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('btn_submit'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php echo $frm->getFieldHtml('prodcat_id'); ?>
                </form>
                <?php echo $frm->getExternalJS(); ?>
            </div>
        </div>
    </div>
</div>