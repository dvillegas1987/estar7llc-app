<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'returnAddressLangFrm');
$frm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 4;
$frm->setFormTagAttribute('onsubmit', 'setReturnAddressLang(this); return(false);');

$address1 = $frm->getField('ura_address_line_1');
$address1->developerTags['col'] = 6;

$address2 = $frm->getField('ura_address_line_2');
$address2->developerTags['col'] = 6;

$submitFld = $frm->getField('btn_submit');
$submitFld->setFieldTagAttribute('class', "btn btn-brand btn-wide");

$langFld = $frm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "returnAddressLangForm(this.value);");

$variables= array('language' => $language,'siteLangId' => $siteLangId,'shop_id' => $shop_id,'action' => $action);
$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false); ?>
<div class="tabs__content tabs__content-js">
    <div class="card">
        <div class="card-body ">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="">
                        <div class="tabs tabs-sm tabs--scroll clearfix">
                            <ul class="setactive-js">
                                <li><a href="javascript:void(0)" onClick="returnAddressForm()"><?php echo Labels::getLabel('LBL_General', $siteLangId); ?></a></li>
                                <li class="is-active">
                                    <a href="javascript:void(0);">
                                        <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                    </a>
                                </li>
                                <?php /* foreach ($language as $langId => $langName) {?>
                                <li <?php echo ($formLangId == $langId)?'class="is-active"':'';?>><a href="javascript:void(0);" onclick="returnAddressLangForm(<?php echo $langId;?>);"><?php echo $langName;?></a></li>
                                <?php } */ ?>
                            </ul>
                        </div>
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
        </div>
    </div>
</div>
