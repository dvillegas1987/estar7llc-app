<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Shop_Collections', $siteLangId); ?></h5>
        <div class="">
            <a href="javascript:void(0)" onClick="shopCollections(this)" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back_to_Collections', $siteLangId);?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="row ">
            <div class="col-md-12">
                <div class="">
                    <div class="tabs tabs-sm tabs--scroll clearfix">
                        <ul>
                            <li><a onclick="getShopCollectionGeneralForm(<?php echo $scollection_id; ?>);" href="javascript:void(0)"><?php echo Labels::getLabel('TXT_Basic', $siteLangId);?></a></li>
                            <li class="is-active">
                                <a href="javascript:void(0);">
                                    <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                </a>
                            </li>
                            <?php
                            /* foreach ($language as $lang_id => $langName) { ?>
                            <li class="<?php echo ($langId == $lang_id)?'is-active':''?>"><a href="javascript:void(0)"
                                <?php if ($scollection_id > 0) { ?>
                                onClick="editShopCollectionLangForm(<?php echo $scollection_id ?>, <?php echo $lang_id;?>)"
                                <?php } ?>>
                                    <?php echo $langName;?></a></li>
                            <?php } */ ?>
                            <li class=""><a
                            <?php if ($scollection_id>0) { ?>
                                onclick="sellerCollectionProducts(<?php echo $scollection_id ?>)"
                            <?php } ?> href="javascript:void(0);"><?php echo Labels::getLabel('TXT_LINK', $siteLangId);?></a></li>
                            <li class=""><a
                            <?php if ($scollection_id > 0) {?>
                                onclick="collectionMediaForm(this, <?php echo $scollection_id; ?>);"
                            <?php } ?> href="javascript:void(0);"><?php echo Labels::getLabel('TXT_Media', $siteLangId);?></a></li>
                        </ul>
                    </div>
                </div>
                <div class="row form__subcontent ">
                    <div class="col-lg-6 col-md-6">
                        <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $langId != $siteDefaultLangId) { ?>
                            <div class="mb-4">
                                <input class="btn btn-brand"
                                    type="button"
                                    value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>"
                                    onClick="editShopCollectionLangForm( <?php echo $scollection_id; ?>, <?php echo $langId; ?>, 1)">
                            </div>
                        <?php }
                            $shopColLangFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
                            $shopColLangFrm->setFormTagAttribute('onsubmit', 'setupShopCollectionlangForm(this); return(false);');
                            $shopColLangFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-';
                            $shopColLangFrm->developerTags['fld_default_col'] = 12;

                            $langFld = $shopColLangFrm->getField('lang_id');
                            $langFld->setfieldTagAttribute('onChange', "editShopCollectionLangForm(" . $scollection_id . ", this.value);");

                            $submitFld = $shopColLangFrm->getField('btn_submit');
                            $submitFld->setFieldTagAttribute('class', "btn btn-brand btn-wide");
                            echo $shopColLangFrm->getFormHtml();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
