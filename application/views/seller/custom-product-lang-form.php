<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>
<div class="tabs ">
    <?php require_once(CONF_THEME_PATH.'seller/sellerCustomProductTop.php');?>
</div>
<div class="card">
    <div class="card-body ">
        <div class="tabs__content form">
            <div class="row">
                <div class="col-md-12">
                    <div class="tabs tabs-sm tabs--scroll clearfix">
                        <ul>
                            <li><a href="javascript:void(0);" onclick="customProductForm(<?php echo $product_id ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
                            <li class="is-active">
                                <a href="javascript:void(0);">
                                    <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                </a>
                            </li>
                            <?php /* foreach ($languages as $langId => $langName) {?>
                            <li class="<?php echo ($langId == $product_lang_id) ? 'is-active' : ''; ?>"><a class="<?php echo ($product_lang_id==$langId) ? ' active' : ''; ?>" href="javascript:void(0);"
                                    <?php echo ($product_id) ? "onclick='customProductLangForm( ".$product_id.",".$langId." );'" : ""; ?>><?php echo $langName;?></a></li>
                            <?php } */ ?>

                        </ul>
                    </div>
                    <div class="form__subcontent">
                        <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $product_lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>" 
                                        onClick="customProductLangForm(<?php echo $product_id; ?>, <?php echo $product_lang_id; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?>
                        <?php
                        $customProductLangFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
                        $customProductLangFrm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
                        $customProductLangFrm->developerTags['fld_default_col'] = 6;
                        //$customProductLangFrm->setFormTagAttribute('onsubmit', 'setupCustomProductLang(this); return(false);');
                        $fld = $customProductLangFrm->getField('product_description');
                        $fld->setWrapperAttribute('class', 'col-lg-12');
                        $fld->developerTags['col'] = 12;
                        $langFld = $customProductLangFrm->getField('lang_id');
                        $langFld->setfieldTagAttribute('onChange', "customProductLangForm(" . $product_id . ", this.value);");
                        echo $customProductLangFrm->getFormHtml();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var frm = $('form[name=frmCustomProductLang]');
    var validator = $(frm).validation({
        errordisplay: 3
    });
    $(frm).submit(function(e) {
        e.preventDefault();
        if (validator.validate() == false) {
            return;
        }
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomProductLang'), data, function(t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            fcom.resetEditorInstance();

            if (t.lang_id > 0) {
                customProductLangForm(t.product_id, t.lang_id);
                return;
            }
            fcom.scrollToTop($("#listing"));

            return;
        });
    });
</script>
